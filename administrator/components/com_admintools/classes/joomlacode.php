<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

defined('_JEXEC') or die();

class JoomlacodeScanner
{
	private $project = '';
	private $packages = array();
	private $releases = array();
	private $files = array();

	public $useCache = false;

	public function setProject($project)
	{
		$this->project = $project;
	}

	public function getProject()
	{
		return $this->project;
	}

	protected function getCacheFilename($url)
	{
		return JPATH_ADMINISTRATOR.'/cache/atjc_'.md5($url).'.cache';
	}

	protected function cacheExists($url)
	{
		// Check if file exists
		if(function_exists('jimport')) {
			JLoader::import('joomla.filesystem.file');
			$filename = $this->getCacheFilename($url);
			$check = JFile::exists( $filename );
			if(!$check) return false;

			// Check if file is newer than 6 hours
			JLoader::import('joomla.utilities.date');
			$mtime = @filemtime($filename);
			$date = new JDate();
			$now = $date->toUnix();
			return (abs($now - $mtime) < 21600);
		} else {
			// Check if file exists
			$filename = $this->getCacheFilename($url);
			$check = file_exists( $this->getCacheFilename($url) );
			if(!$check) return false;

			// Check if file is newer than 6 hours
			$mtime = @filemtime($filename);
			$now = time();
			return (abs($now - $mtime) < 21600);
		}
	}

	protected function readCache($url)
	{
		if(function_exists('jimport')) {
			JLoader::import('joomla.filesystem.file');
			return JFile::read( $this->getCacheFilename($url) );
		} else {
			return file_get_contents( $this->getCacheFilename($url) );
		}
	}

	protected function writeCache($url, $data)
	{
		if(function_exists('jimport')) {
			JLoader::import('joomla.filesystem.file');
			return JFile::write( $this->getCacheFilename($url), $data );
		} else {
			return file_put_contents( $this->getCacheFilename($url), $data );
		}
	}

	protected function getPageContent($url)
	{
		if($this->useCache)
		{
			if($this->cacheExists($url))
			{
				return $this->readCache($url);
			}
		}

		if(function_exists('curl_exec'))
		{
			// Use cURL
			$curl_options = array(
				CURLOPT_AUTOREFERER		=> true,
				CURLOPT_FAILONERROR		=> true,
				CURLOPT_FOLLOWLOCATION	=> true,
				CURLOPT_HEADER			=> false,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_SSL_VERIFYPEER	=> false,
				CURLOPT_CONNECTTIMEOUT	=> 5,
				CURLOPT_MAXREDIRS		=> 20
			);
			$ch = curl_init($url);
			@curl_setopt($ch, CURLOPT_CAINFO, JPATH_COMPONENT_ADMINISTRATOR.'/assets/cacert.pem');
			foreach($curl_options as $option => $value)
			{
				@curl_setopt($ch, $option, $value);
			}
			$data = curl_exec($ch);
		}
		elseif( ini_get('allow_url_fopen') )
		{
			// Use fopen() wrappers
			$options = array( 'http' => array(
				'max_redirects' => 10,          // stop after 10 redirects
				'timeout'       => 20         // timeout on response
			) );
			$context = stream_context_create( $options );
			$data = @file_get_contents( $url, false, $context );
		}
		else
		{
			$data = false;
		}

		if($this->useCache)
		{
			if($data !== false)
			{
				$this->writeCache($url, $data);
			}
		}

		return $data;
	}

	private function getMorePages($data)
	{
		$pages = array();
		$regex = '#<a href="(.*_br_pkgrls_page=[0-9]*)">(.*)</a>#iU';
		preg_match_all($regex, $data, $m);
		if(!empty($m[0]))
		{
			$count = count($m[1]);
			for($i = 0; $i < $count; $i++)
			{
				if(!is_numeric($m[2][$i])) continue;
				$pages[ $m[2][$i] ] = $m[1][$i];
			}
		}
		return $pages;
	}

	public function getPackages()
	{
		if(empty($this->packages))
		{
			$url = 'http://joomlacode.org/gf/project/'.urlencode($this->project).'/frs';
			$data = $this->getPageContent($url);
			$pages = $this->getMorePages($data);

			while(!empty($pages))
			{
				$url = 'http://joomlacode.org'.html_entity_decode(array_pop($pages));
				$data .= $this->getPageContent($url);
			}

			// Parse packages
			$this->packages = array();
			$regex = '#<a href="(/gf/project/[\w]*/frs/\?action=FrsReleaseBrowse\&amp\;frs_package_id=[0-9]*)">.*&nbsp;(.*)</a>#iU';
			preg_match_all($regex, $data, $m);

			if(!empty($m))
			{
				$count = count($m[0]);
				for($i = 0; $i < $count; $i++)
				{
					$this->packages[ $m[2][$i] ] = $m[1][$i];
				}
			}

			ksort($this->packages);
		}

		return $this->packages;
	}

	public function getReleases($packageName)
	{
		if(!isset($this->releases[$packageName]))
		{
			$this->releases[$packageName] = array();

			$packages = $this->getPackages();
			if(isset($packages[ $packageName ]))
			{
				$url = 'http://joomlacode.org'.html_entity_decode($packages[ $packageName ]);
				$data = $this->getPageContent($url);
				$pages = $this->getMorePages($data);

				while(!empty($pages))
				{
					$url = 'http://joomlacode.org'.html_entity_decode(array_pop($pages));
					$data .= $this->getPageContent($url);
				}

				$regex = '#<a href="(/gf/project/[\w]*/frs/\?action=FrsReleaseView\&amp\;release_id=[0-9]*)">(.*)</a>#iU';
				preg_match_all($regex, $data, $m);
				if(!empty($m))
				{
					$count = count($m[0]);
					for($i = 0; $i < $count; $i++)
					{
						$this->releases[$packageName][ $m[2][$i] ] = $m[1][$i];
					}
				}
			}

			ksort($this->releases[$packageName]);
		}

		return $this->releases[$packageName];
	}

	public function getFiles($packageName, $releaseName)
	{
		if(!isset($this->files[$packageName][$releaseName]))
		{
			$this->files[$packageName][$releaseName] = array();
			$releases = $this->getReleases($packageName);
			if(isset($releases[$releaseName]))
			{
				$url = 'http://joomlacode.org'.html_entity_decode($releases[$releaseName]);
				$data = $this->getPageContent($url);
				$pages = $this->getMorePages($data);

				while(!empty($pages))
				{
					$url = 'http://joomlacode.org'.html_entity_decode(array_pop($pages));
					$data .= $this->getPageContent($url);
				}

				$regex = '#<a href="(/gf/download/frsrelease/[0-9]*/[0-9]*/.*)">(.*)</a>#iU';
				preg_match_all($regex, $data, $m);
				if(!empty($m))
				{
					$count = count($m[0]);
					for($i = 0; $i < $count; $i++)
					{
						$filename = 'http://joomlacode.org'.$m[1][$i];
						$this->files[$packageName][$releaseName][ $m[2][$i] ] = $filename;
					}
				}
			}

			ksort($this->files[$packageName][$releaseName]);
		}

		return $this->files[$packageName][$releaseName];
	}
}