<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.model');

if(!class_exists('JoomlaCompatModel')) {
	if(interface_exists('JModel')) {
		abstract class JoomlaCompatModel extends JModelLegacy {}
	} else {
		class JoomlaCompatModel extends JModel {}
	}
}
class AdmintoolsModelJupdate extends JoomlaCompatModel
{
	/** @var object Latest Joomla! version information */
	private $jversion = null;

	/** @var JoomlacodeScanner */
	private $jc = null;
	
	private $sqlVersions = array(
		'1.7.0'		=> '1.7.0-2013-06-06-2',
		'1.7.1'		=> '1.7.1-2013-09-20',
		'1.7.2'		=> '1.7.1-2013-09-20',
		'1.7.3'		=> '1.7.3-2013-11-10',
		'1.7.4'		=> '1.7.4-2013-12-12',
		'1.7.5'		=> '1.7.4-2013-12-12',
		'1.7.6'		=> '1.7.4-2013-12-12',
		'2.5.0'		=> '2.5.0-2013-01-14',
		'2.5.1'		=> '2.5.1-2013-01-26',
		'2.5.2'		=> '2.5.2-2013-02-03',
		'2.5.3'		=> '2.5.2-2013-02-03',
		'2.5.4'		=> '2.5.2-2013-02-03', // Wild guess
		'2.5.5'		=> '2.5.2-2013-02-03', // Wild guess
	);
	
	/**
	 * Gets the latest known Joomla! versions
	 * @return array
	 */
	public function getAllUpdates($force = false)
	{
		// Check if it's stored in the static variable
		if(!empty($this->jversion) && !$force) return $this->jversion;

		// Check the cache age
		if(!class_exists('AdmintoolsModelStorage')) {
			require_once JPATH_ADMINISTRATOR.'/components/com_admintools/models/storage.php';
		}
		if(interface_exists('JModel')) {
			$params = JModelLegacy::getInstance('Storage','AdmintoolsModel');
		} else {
			$params = JModel::getInstance('Storage','AdmintoolsModel');
		}
		$lastdate = $params->getValue('lastjupdatecheck', '2005-09-01');
		
		JLoader::import('joomla.utilities.date');
		$date = new JDate($lastdate);
		$now = new JDate();
		
		if( (abs($now->toUnix() - $date->toUnix()) < 21600) && !$force )
		{
			// Checked before 6 hours or more recently. Try to fetch from cache.
			$jversion_data = $params->getValue('latestjversion', '{}');
			$this->jversion = @json_decode($jversion_data, true);
			if(empty($this->jversion)) {
				$this->jversion = null;
			} else {
				if(!array_key_exists('sts', $this->jversion)) {
					$this->jversion = null;
				}
			}
		}

		if(!empty($this->jversion) && !$force) return $this->jversion;
		
		require_once JPATH_ADMINISTRATOR.'/components/com_admintools/classes/joomlacode.php';
		
		$ret = array(
			// Currently installed version (used to reinstall)
			'installed' => array(
				'version'	=> '',
				'package'	=> ''
			),
			// Current branch
			'current'	=> array(
				'version'	=> '',
				'package'	=> ''
			),
			// Upgrade to STS release
			'sts'		=> array(
				'version'	=> '',
				'package'	=> ''
			),
			// Upgrade to LTS release
			'lts'		=> array(
				'version'	=> '',
				'package'	=> ''
			)
		);
		
		if(!is_object($this->jc)) $this->jc = new JoomlacodeScanner();
		$jc = $this->jc;
		$jc->useCache = !$force;
		$jc->setProject('joomla');

		$packages = $jc->getPackages();

		$packages = array_keys($packages);
		$temp = array();
		foreach($packages as $package) {
			$version = $this->sanitiseVersion(substr($package,6));
			$temp[$version] = $package;
		}
		$packages = $temp;
		
		$versions = $this->getVersions(JVERSION, $packages);
		
		// First, get the FRS package for the currently installed release
		$installed_version = $this->sanitiseVersion(JVERSION);
		if(array_key_exists($installed_version, $packages)) {
			$package = $packages[$installed_version];
			$ret['installed']['version'] = $installed_version;
			$ret['installed']['package'] = $this->getFileForPackage($package);
		}
		
		if($versions['current']) {
			$version = $versions['current'];
			if(array_key_exists($version, $packages)) {
				$package = $packages[$version];
				$ret['current']['version'] = $version;
				$ret['current']['package'] = $this->getFileForPackage($package);
			}
		}
		
		if($versions['sts']) {
			$version = $versions['sts'];
			if(array_key_exists($version, $packages)) {
				$package = $packages[$version];
				$ret['sts']['version'] = $version;
				$ret['sts']['package'] = $this->getFileForPackage($package);
			}
		}
		
		if($versions['lts']) {
			$version = $versions['lts'];
			if(array_key_exists($version, $packages)) {
				$package = $packages[$version];
				$ret['lts']['version'] = $version;
				$ret['lts']['package'] = $this->getFileForPackage($package);
			}
		}
		
		$this->jversion = $ret;
		$jversion_data = json_encode($this->jversion);

		// Save to cache
		$date = new JDate();
		$params->setValue('latestjversion', $jversion_data );
		$params->setValue('lastjupdatecheck', $date->toUnix(false));
		$params->save();

		// Return the new data
		return $this->jversion;
	}
	
	/**
	 * Joomla! has a lousy track record in naming its alpha, beta and release
	 * candidate releases. The convention used seems to be "what the hell the
	 * current package maintainer thinks looks better". This method tries to
	 * figure out what was in the mind of the maintainer and translate the
	 * funky version number to an actual PHP-format version string.
	 * 
	 * Joomla! package maintainers, for the love of the deity you believe in,
	 * PLEASE pick a goddamn standard and bloody stick to it!!! I'm tired of
	 * amending this method in every single release >:(
	 * 
	 * @param type $version
	 * @return string 
	 */
	private function sanitiseVersion($version)
	{
		$test = strtolower($version);
		$alphaQualifierPosition = strpos($test, 'alpha-');
		$betaQualifierPosition = strpos($test, 'beta-');
		$rcQualifierPosition = strpos($test, 'rc-');
		$rcQualifierPosition2 = strpos($test, 'rc');
		
		if($alphaQualifierPosition !== false) {
			$betaRevision = substr($test, $alphaQualifierPosition + 6);
			if(!$betaRevision) $betaRevision = 1;
			$test = substr($test,0,$alphaQualifierPosition).'.a'.$betaRevision;
		} elseif($betaQualifierPosition !== false) {
			$betaRevision = substr($test, $betaQualifierPosition + 5);
			if(!$betaRevision) $betaRevision = 1;
			$test = substr($test,0,$betaQualifierPosition).'.b'.$betaRevision;
		} elseif($rcQualifierPosition !== false) {
			$betaRevision = substr($test, $rcQualifierPosition + 5);
			if(!$betaRevision) $betaRevision = 1;
			$test = substr($test,0,$rcQualifierPosition).'.rc'.$betaRevision;
		} elseif($rcQualifierPosition2 !== false) {
			$betaRevision = substr($test, $rcQualifierPosition2 + 5);
			if(!$betaRevision) $betaRevision = 1;
			$test = substr($test,0,$rcQualifierPosition2).'.rc'.$betaRevision;
		}
		
		return $test;
	}

	/**
	 * Returns information about whether we need to update Joomla!
	 * @staticvar string $updateInfo
	 * @return string
	 */
	public function getUpdateInfo($force = false)
	{
		static $updateInfo = null;

		if(!empty($updateInfo) && !$force) return $updateInfo;

		$updateInfo = (object)array(
			'status'	=> null,
			'installed'	=> null,
			'current'	=> null,
			'sts'		=> null,
			'lts'		=> null
		);

		$data = $this->getAllUpdates($force);
		if(empty($data)) return $updateInfo;
		
		$updateInfo = (object)array_merge($data, array('status' => null));

		// We trigger an update warning only when the current branch has an
		// update.
		$updateInfo->status = !empty($updateInfo->current['version']) && ($updateInfo->current['version'] != JVERSION);

		return $updateInfo;
	}
	
	/**
	 * Returns the URL of the file which must be used for a given FRS package
	 * @param string $frsPackage
	 * @return string
	 */
	protected function getFileForPackage($frsPackage)
	{
		if(!is_object($this->jc)) $this->jc = new JoomlacodeScanner();
		$jc = $this->jc;
		$jc->useCache = true;
		$jc->setProject('joomla');
		
		$releases = $jc->getReleases($frsPackage);
		$releases = array_keys($releases);
		
		$theURL = null;
		$currentVersion = $this->sanitiseVersion(JVERSION);
		
		foreach($releases as $release) {
			$files = $jc->getFiles($frsPackage, $release);
			if(!empty($files)) foreach($files as $filename => $url)
			{
				// Only process .ZIP files
				if(strtoupper(substr($filename,-4)) != '.ZIP' ) continue;
				
				$basename = basename(strtolower($filename), '.zip');
				
				// Is this an update package?
				if(stristr($basename, 'patch')) {
					// Remove joomla_
					$basename = str_replace('joomla_','',$basename);
					// Grab the version
					list($oldVersion, $junk) = explode('_',$basename,2);
					$oldVersion = $this->sanitiseVersion($oldVersion);
					
					if($oldVersion == $currentVersion) {
						$theURL = $url;
					}
				} else {
					if(empty($theURL)) {
						$theURL = $url;
					}
				}
				
			}
		}
		
		return $theURL;
	}
	
	/**
	 * Gets the appropriate current branch, LTS and STS versions for a given
	 * Joomla! version.
	 * 
	 * @param string $jVersion
	 * @param array $packages
	 * @param bool $testing
	 * 
	 * @return array
	 */
	protected function getVersions($jVersion, $packages, $testing = false)
	{
		$ret = array(
			'current'	=> null,
			'lts'		=> null,
			'sts'		=> null,
		);
		
		$baseVersion = substr($jVersion, 0, 3);
		switch($baseVersion) {
			case '1.5':
				$current_minimum = '1.5';
				$current_maximum = '1.5.999';
				$sts_minimum = false;
				$lts_minimum = false;
				break;
				
			case '1.6':
				$current_minimum = '1.6';
				$current_maximum = '1.6.999';
				$sts_minimum = '1.7';
				$sts_maximum = '1.7.999';
				$lts_minimum = '2.5';
				break;
			
			case '1.7':
				$current_minimum = '1.7';
				$current_maximum = '1.7.999';
				$sts_minimum = null;
				$lts_minimum = '2.5';
				break;
			
			default:
				$majorVersion = substr($jVersion, 0, 1);
				$minorVersion = substr($jVersion, 2, 1);
				
				$current_minimum = $baseVersion;
				$current_maximum = $baseVersion.'.999';
				
				if($minorVersion == '5') {
					// This is an LTS release, it can be superseded by .0 or .1 STS releases on the next branch...
					$sts_minimum = ($majorVersion+1).'.0';
					$sts_maximum = ($majorVersion+1).'.1.999';
					// ...or a .5 LTS on the next branch
					$lts_minimum = ($majorVersion+1).'.5';
				} else {
					// This is an STS release, it can be superseded by a .1 STS release on the same branch...
					$sts_minimum = $majorVersion.'.1';
					$sts_maximum = $majorVersion.'.1.999';
					// ...or a .5 LTS on the same branch
					$lts_minimum = $majorVersion.'.5';
				}
				break;
		}
		
		$current = '0.0';
		if($current_minimum) foreach($packages as $version => $package) {
			if(
				version_compare($version, $current_minimum, 'ge')
				&& version_compare($version, $current_maximum, 'lt')
				&& version_compare($version, $current, 'ge')
			) {
				if(in_array(substr($version,4,1), array('a','b','r')) && !$testing) continue;
				$current = $version;
			}
		}
		if($current == '0.0') $current = null;
		$ret['current'] = $current;

		$sts = '0.0';
		if($sts_minimum) foreach($packages as $version => $package) {
			if(
				version_compare($version, $sts_minimum, 'ge') &&
				version_compare($version, $sts_maximum, 'lt') &&
				version_compare($version, $sts, 'ge')
			) {
				if(in_array(substr($version,4,1), array('a','b','r')) && !$testing) continue;
				$sts = $version;
			}
		}
		if($sts == '0.0') $sts = null;
		$ret['sts'] = $sts;

		$lts = '0.0';
		if($lts_minimum) foreach($packages as $version => $package) {
			if(
				version_compare($version, $lts_minimum, 'ge') &&
				(substr($version, 2, 1) == '5') &&
				version_compare($version, $lts, 'ge')
			) {
				if(in_array(substr($version,4,1), array('a','b','r')) && !$testing) continue;
				$lts = $version;
			}
		}
		if($lts == '0.0') $lts = null;
		$ret['lts'] = $lts;

		return $ret;
	}
	
	/**
	 * Handles downloading an update to the temp directory
	 */
	public function download($item = 'current')
	{
		// Get version info
		$versionInfo = $this->getUpdateInfo();

		if(!in_array($item, array('current','installed','sts','lts'))) {
			return false;
		}
		
		$packageURL = $versionInfo->{$item}['package'];
		$basename = basename($packageURL);

		// Find the path to the temp directory and the local package
		$jreg = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$tempdir = $jreg->get('tmp_path');
		} else {
			$tempdir = $jreg->getValue('config.tmp_path');
		}
		$target = $tempdir.DIRECTORY_SEPARATOR.$basename;

		// Do we have a cached file?
		JLoader::import('joomla.filesystem.file');
		$exists = JFile::exists($target);

		if(!$exists)
		{
			// Not there, let's fetch it
			return $this->downloadPackage($packageURL, $target);
		}
		else
		{
			// Is it a 0-byte file? If so, re-download please.
			$filesize = @filesize($target);
			if(empty($filesize)) return $this->downloadPackage($packageURL, $target);

			// Yeap, it's there, skip downloading
			return $basename;
		}
	}

	/**
	 * Attempt to download a big package file intelligently, using cURL or fopen()
	 * URL wrappers. In the cURL mode, if the target is directly writtable it uses
	 * very few memory. Otherwise, make sure you have at least a dozen Mb free memory.
	 * @param $url string The URL to download from
	 * @param $target string The absolute path where to store the file
	 */
	private function downloadPackage($url, $target)
	{
		JLoader::import('helpers.download', JPATH_COMPONENT_ADMINISTRATOR);
		$result = AdmintoolsHelperDownload::download($url, $target);
		if(!$result) return false;
		return basename($target);
	}

	/**
	 * Generates a pseudo-random password
	 * @param int $length The length of the password in characters
	 * @return string The requested password string
	 */
	private function makeRandomPassword( $length = 32 )
	{
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		$maxchars = strlen($chars);
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;

		while ($i <= $length) {
			$num = rand() % $maxchars;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}

		return $pass;
	}

	/**
	 * Checks if the site has Akeeba Backup 3.1 or later installed
	 * @return bool
	 */
	public function hasAkeebaBackup()
	{
		// Is the component installed, at all?
		JLoader::import('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_akeeba')) return false;
		// Make sure the component is enabled
		$component = JComponentHelper::getComponent( 'com_akeeba', true );
		if(!$component->enabled) return false;
		// Make sure it's the correct version (release date was after September 3rd, 2010)
		include_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_akeeba'.DIRECTORY_SEPARATOR.'version.php';
		JLoader::import('joomla.utilities.date');
		if(defined('AKEEBA_DATE')) {
			$date = new JDate(AKEEBA_DATE);
			return $date->toUnix() > 1283490000;
		}
		// In any other case, no go
		return false;
	}

	/**
	 * Get the site's FTP parameters from Joomla!'s configuration
	 * @return array
	 */
	public function getFTPParams()
	{
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			return array(
				'procengine'	=> $config->get('ftp_enable', 0) ? 'ftp' : 'direct',
				'ftp_host'		=> $config->get('ftp_host', 'localhost'),
				'ftp_port'		=> $config->get('ftp_port', '21'),
				'ftp_user'		=> $config->get('ftp_user', ''),
				'ftp_pass'		=> $config->get('ftp_pass', ''),
				'ftp_root'		=> $config->get('ftp_root', ''),
				'tempdir'		=> $config->get('tmp_path', '')
			);
		} else {
			return array(
				'procengine'	=> $config->getValue('config.ftp_enable', 0) ? 'ftp' : 'direct',
				'ftp_host'		=> $config->getValue('config.ftp_host', 'localhost'),
				'ftp_port'		=> $config->getValue('config.ftp_port', '21'),
				'ftp_user'		=> $config->getValue('config.ftp_user', ''),
				'ftp_pass'		=> $config->getValue('config.ftp_pass', ''),
				'ftp_root'		=> $config->getValue('config.ftp_root', ''),
				'tempdir'		=> $config->getValue('config.tmp_path', '')
			);
		}
	}

	/**
	 * Gets an options list for extraction modes
	 * @return array
	 */
	public function getExtractionModes()
	{
		$options = array();
		$options[] = JHTML::_('select.option', 'direct', JText::_('ATOOLS_LBL_EXTRACTIONMETHOD_DIRECT'));
		$options[] = JHTML::_('select.option', 'ftp', JText::_('ATOOLS_LBL_EXTRACTIONMETHOD_FTP'));
		return $options;
	}

	public function createRestorationINI()
	{
		// Get a password
		$password = $this->makeRandomPassword(32);
		JRequest::setVar('password', $password);
		$session = JFactory::getSession();
		$session->set('update_password', $password ,'admintools');

		// Do we have to use FTP?
		$procengine = JRequest::getCmd('procengine','direct');

		// Get the absolute path to site's root
		$siteroot = JPATH_SITE;

		// Get the package name
		$file = JRequest::getString('file','');
		if(empty($file)) return false;
		$jreg = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$tempdir = $jreg->get('tmp_path');
		} else {
			$tempdir = $jreg->getValue('config.tmp_path');
		}
		$file  = $tempdir.DIRECTORY_SEPARATOR.$file;

		$data = "<?php\ndefined('_AKEEBA_RESTORATION') or die();\n";
		$data .= '$restoration_setup = array('."\n";
		$data .= <<<ENDDATA
	'kickstart.security.password' => '$password',
	'kickstart.tuning.max_exec_time' => '5',
	'kickstart.tuning.run_time_bias' => '75',
	'kickstart.tuning.min_exec_time' => '0',
	'kickstart.procengine' => '$procengine',
	'kickstart.setup.sourcefile' => '$file',
	'kickstart.setup.destdir' => '$siteroot',
	'kickstart.setup.restoreperms' => '0',
	'kickstart.setup.filetype' => 'zip',
	'kickstart.setup.dryrun' => '0'
ENDDATA;

		if($procengine == 'ftp')
		{
			$ftp_host	= JRequest::getVar('ftp_host','');
			$ftp_port	= JRequest::getVar('ftp_port', '21');
			$ftp_user	= JRequest::getVar('ftp_user', '');
			$ftp_pass	= JRequest::getVar('ftp_pass', '', 'default', 'none', 2); // Password should be allowed as raw mode, otherwise !@<sdf34>43H% would be trimmed to !@43H% which is plain wrong :@
			$ftp_root	= JRequest::getVar('ftp_root', '');

			// Is the tempdir really writable?
			$writable = @is_writeable($tempdir);
			if($writable) {
				// Let's be REALLY sure
				$fp = @fopen($tempdir.'/test.txt','w');
				if($fp === false) {
					$writable = false;
				} else {
					fclose($fp);
					unlink($tempdir.'/test.txt');
				}
			}

			// If the tempdir is not writable, create a new writable subdirectory
			if(!$writable) {
				JLoader::import('joomla.client.ftp');
				JLoader::import('joomla.client.helper');
				JLoader::import('joomla.filesystem.folder');

				$FTPOptions = JClientHelper::getCredentials('ftp');
				if(version_compare(JVERSION,'3.0','ge')) {
					$ftp = JClientFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
				} else {
					$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
				}
				$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir.'/admintools'), '/');
				if(!@mkdir($tempdir.'/admintools')) $ftp->mkdir($dest);
				if(!@chmod($tempdir.'/admintools', 511)) $ftp->chmod($dest, 511);

				$tempdir .= '/admintools';
			}

			// Just in case the temp-directory was off-root, try using the default tmp directory
			$writable = @is_writeable($tempdir);
			if(!$writable) {
				$tempdir = JPATH_ROOT.'/tmp';

				// Does the JPATH_ROOT/tmp directory exist?
				if(!is_dir($tempdir)) {
					JLoader::import('joomla.filesystem.folder');
					JLoader::import('joomla.filesystem.file');
					JFolder::create($tempdir, 511);
					JFile::write($tempdir.'/.htaccess',"order deny, allow\ndeny from all\nallow from none\n");
				}

				// If it exists and it is unwritable, try creating a writable admintools subdirectory
				if(!is_writable($tempdir)) {
					JLoader::import('joomla.client.ftp');
					JLoader::import('joomla.client.helper');
					JLoader::import('joomla.filesystem.folder');

					$FTPOptions = JClientHelper::getCredentials('ftp');
					if(version_compare(JVERSION,'3.0','ge')) {
						$ftp = JClientFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
					} else {
						$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
					}
					$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir.'/admintools'), '/');
					if(!@mkdir($tempdir.'/admintools')) $ftp->mkdir($dest);
					if(!@chmod($tempdir.'/admintools', 511)) $ftp->chmod($dest, 511);

					$tempdir .= '/admintools';
				}
			}

			// If we still have no writable directory, we'll try /tmp and the system's temp-directory
			$writable = @is_writeable($tempdir);
			if(!$writable) {
				if(@is_dir('/tmp') && @is_writable('/tmp')) {
					$tempdir = '/tmp';
				} else {
					// Try to find the system temp path
					$tmpfile = @tempnam("dummy","");
					$systemp = @dirname($tmpfile);
					@unlink($tmpfile);
					if(!empty($systemp)) {
						if(@is_dir($systemp) && @is_writable($systemp)) {
							$tempdir = $systemp;
						}
					}
				}
			}

			$data.=<<<ENDDATA
	,
	'kickstart.ftp.ssl' => '0',
	'kickstart.ftp.passive' => '1',
	'kickstart.ftp.host' => '$ftp_host',
	'kickstart.ftp.port' => '$ftp_port',
	'kickstart.ftp.user' => '$ftp_user',
	'kickstart.ftp.pass' => '$ftp_pass',
	'kickstart.ftp.dir' => '$ftp_root',
	'kickstart.ftp.tempdir' => '$tempdir'
ENDDATA;
		}

		$data .= ');';

		// Remove the old file, if it's there...
		JLoader::import('joomla.filesystem.file');
		$configpath = JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'restoration.php';
		if( JFile::exists($configpath) )
		{
			JFile::delete($configpath);
		}

		// Write new file. First try with JFile.
		$result = JFile::write( $configpath, $data );
		// In case JFile used FTP but direct access could help
		if(!$result) {
			if(function_exists('file_put_contents')) {
				$result = @file_put_contents($configpath, $data);
				if($result !== false) $result = true;
			} else {
				$fp = @fopen($configpath, 'wt');
				if($fp !== false) {
					$result = @fwrite($fp, $data);
					if($result !== false) $result = true;
					@fclose($fp);
				}
			}
		}
		return $result;
	}

	/**
	 * Post-update clean up
	 * @param string $file The update filename
	 */
	public function finalize($file)
	{
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.filesystem.folder');

		// Where is our temp directory?
		$jreg = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$tempdir = $jreg->get('tmp_path');
		} else {
			$tempdir = $jreg->getValue('config.tmp_path');
		}

		// Remove the update file
		if(!empty($file)) {
			if(!@unlink($tempdir.'/'.$file)) JFile::delete($tempdir.'/'.$file);
		}

		// Delete the temp-dir we may have created
		if(is_dir($tempdir.'/admintools')) {
			JFolder::delete($tempdir.'/admintools');
		}
		
		$this->runUpdateScripts();
	}
	
	private function runUpdateScripts()
	{
		JLoader::import('joomla.installer.install');
		$installer = JInstaller::getInstance();
		
		$installer->setPath('source', JPATH_ROOT);
		$installer->setPath('extension_root', JPATH_ROOT);

		if (!$installer->setupInstall())
		{
			$installer->abort(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));
			
			return false;
		}
		
		$installer->extension = JTable::getInstance('extension');
		$installer->extension->load(700);
		$installer->setAdapter($installer->extension->type);
		
		$manifest = $installer->getManifest();
		
		$manifestPath = JPath::clean($installer->getPath('manifest'));
		$element = preg_replace('/\.xml/', '', basename($manifestPath));
		
		// Run the script file
		$scriptElement = $manifest->scriptfile;
		$manifestScript = (string) $manifest->scriptfile;
		
		if ($manifestScript)
		{
			$manifestScriptFile = JPATH_ROOT . '/' . $manifestScript;
			
			if (is_file($manifestScriptFile))
			{
				// load the file
				include_once $manifestScriptFile;
			}
			
			$classname = 'JoomlaInstallerScript';
			
			if (class_exists($classname))
			{
				$manifestClass = new $classname($this);
			}
		}
		
		ob_start();
		ob_implicit_flush(false);
		if ($manifestClass && method_exists($manifestClass, 'preflight'))
		{
			if ($manifestClass->preflight('update', $this) === false)
			{
				$installer->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}
		
		$msg = ob_get_contents(); // create msg object; first use here
		ob_end_clean();
		
		// Get a database connector object
		$db = JFactory::getDbo();
		
		// Check to see if a file extension by the same name is already installed
		// If it is, then update the table because if the files aren't there
		// we can assume that it was (badly) uninstalled
		// If it isn't, add an entry to extensions
		$query = $db->getQuery(true);
		$query->select($query->qn('extension_id'))
			->from($query->qn('#__extensions'));
		$query->where($query->qn('type') . ' = ' . $query->q('file'))
			->where($query->qn('element') . ' = ' . $query->q('joomla'));
		$db->setQuery($query);
		try
		{
			$db->execute();
		}
		catch (JException $e)
		{
			// Install failed, roll back changes
			$installer->abort(
				JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_UPDATE'), $db->stderr(true))
			);
			return false;
		}
		$id = $db->loadResult();
		$row = JTable::getInstance('extension');
		
		if ($id)
		{
			// Load the entry and update the manifest_cache
			$row->load($id);
			// Update name
			$row->set('name', 'files_joomla');
			// Update manifest
			$row->manifest_cache = $installer->generateManifestCache();
			if (!$row->store())
			{
				// Install failed, roll back changes
				$installer->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_UPDATE'), $db->stderr(true))
				);
				return false;
			}
		}
		else
		{
			// Add an entry to the extension table with a whole heap of defaults
			$row->set('name', 'files_joomla');
			$row->set('type', 'file');
			$row->set('element', 'joomla');
			// There is no folder for files so leave it blank
			$row->set('folder', '');
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', 0);
			$row->set('params', '');
			$row->set('system_data', '');
			$row->set('manifest_cache', $installer->generateManifestCache());

			if (!$row->store())
			{
				// Install failed, roll back changes
				$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_ROLLBACK', $db->stderr(true)));
				return false;
			}

			// Set the insert id
			$row->set('extension_id', $db->insertid());

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$installer->pushStep(array('type' => 'extension', 'extension_id' => $row->extension_id));
		}
		
		/*
		 * Let's run the queries for the file
		 */
		if ($manifest->update)
		{
			$result = $installer->parseSchemaUpdates($manifest->update->schemas, $row->extension_id);
			if ($result === false)
			{
				// Install failed, rollback changes
				$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_UPDATE_SQL_ERROR', $db->stderr(true)));
				return false;
			}
		}
		
		// Start Joomla! 1.6
		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'update'))
		{
			if ($manifestClass->update($installer) === false)
			{
				// Install failed, rollback changes
				$installer->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		$msg .= ob_get_contents(); // append messages
		ob_end_clean();
		
		// Lastly, we will copy the manifest file to its appropriate place.
		$manifest = array();
		$manifest['src'] = $installer->getPath('manifest');
		$manifest['dest'] = JPATH_MANIFESTS . '/files/' . basename($installer->getPath('manifest'));
		if (!$installer->copyFiles(array($manifest), true))
		{
			// Install failed, rollback changes
			$installer->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_COPY_SETUP'));
			return false;
		}

		// Clobber any possible pending updates
		$update = JTable::getInstance('update');
		$uid = $update->find(
			array('element' => $element, 'type' => 'file', 'client_id' => '', 'folder' => '')
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// And now we run the postflight
		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'postflight'))
		{
			$manifestClass->postflight('update', $this);
		}

		$msg .= ob_get_contents(); // append messages
		ob_end_clean();

		if ($msg != '')
		{
			$installer->set('extension_message', $msg);
		}

		
		return true;
	}
	
	/**
	 * Checks the #__schemas table for consistency before upgrading Joomla!
	 */
	public function checkSchemasTable()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where($db->quoteName('extension_id').' = '.$db->quote(700));
		$db->setQuery($query);
		
		$lastSchema = $db->loadResult();
		if(empty($lastSchema)) {
			$lastSchema = '1.6.0-0-0-0';
		}
		$schemaParts = explode('-', $lastSchema);
		$lastVersion = $schemaParts[0];
		
		if(version_compare($lastVersion, JVERSION, 'lt')) {
			if(array_key_exists(JVERSION, $this->sqlVersions)) {
				$newVersion = $this->sqlVersions[JVERSION];
			} else {
				$x = $this->sqlVersions;
				$newVersion = array_pop($x);
			}
			if($newVersion == $lastSchema) return;
			$update = (object)array(
				'extension_id'	=> '700',
				'version_id'	=> $newVersion
			);
			$x = $db->updateObject('#__schemas', $update, 'extension_id');
		}
	}
}