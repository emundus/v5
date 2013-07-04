<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Installer manifest class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class Com_FabrikInstallerScript
{

	protected $drivers = array('mysql_fab.php', 'mysqli_fab.php');

	/**
	 * Run when the component is installed
	 *
	 * @param   object  $parent  installer object
	 *
	 * @return bool
	 */

	function install($parent)
	{
		return true;
	}

	/**
	 * Check if there is a connection already installed if not create one
	 * by copying over the site's default connection
	 *
	 * @return  bool
	 */

	protected function setConnection()
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$row = new stdClass;
		$row->host = $app->getCfg('host');
		$row->user = $app->getCfg('user');
		$row->password = $app->getCfg('password');
		$row->database = $app->getCfg('db');
		$row->description = 'site database';
		$row->published = 1;
		$row->default = 1;
		$res = $db->insertObject('#__fabrik_connections', $row, 'id');
		return $res;
	}

	/**
	 * Test to ensure that the main component params have a default setup
	 *
	 * @return  bool
	 */

	protected function setDefaultProperties()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id, params')->from('#__extensions')->where('name = ' . $db->quote('fabrik'))
			->where('type = ' . $db->quote('component'));
		$db->setQuery($query);
		$row = $db->loadObject();
		$opts = new stdClass;
		$opts->fbConf_wysiwyg_label = 0;
		$opts->fbConf_alter_existing_db_cols = 0;
		$opts->spoofcheck_on_formsubmission = 0;

		if ($row && ($row->params == '{}' || $row->params == ''))
		{
			$json = $row->params;
			$query = $db->getQuery(true);
			$query->update('#__extensions')->set('params = ' . $db->quote($json))->where('extension_id = ' . (int) $row->extension_id);
			$db->setQuery($query);
			if (!$db->execute())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Move over files into Joomla libraries folder
	 *
	 * @param   object  &$installer  installer
	 * @param   bool    $upgrade     upgrade
	 *
	 * @return  bool
	 */

	protected function moveFiles(&$installer, $upgrade = false)
	{
		jimport('joomla.filesystem.file');
		$componentFrontend = 'components/com_fabrik';
		$docTypes = array('fabrikfeed', 'pdf');
		foreach ($docTypes as $docType)
		{
			$dest = 'libraries/joomla/document/' . $docType;
			if (!JFolder::exists(JPATH_ROOT . '/' . $dest))
			{
				JFolder::create(JPATH_ROOT . '/' . $dest);
			}
			// $$$ hugh - have to use false as last arg (use_streams) on JFolder::copy(), otherwise
			// it bypasses FTP layer, and will fail if web server does not have write access to J! folders
			$moveRes = JFolder::copy($componentFrontend . '/' . $docType, $dest, JPATH_SITE, true, false);
			if ($moveRes !== true)
			{
				echo "<p style=\"color:red\">failed to moved " . $componentFrontend . '/fabrikfeed to ' . $dest . '</p>';
				return false;
			}
		}

		$dest = 'libraries/joomla/database/database';
		$driverInstallLoc = $componentFrontend . '/dbdriver/';
		$moveRes = JFolder::copy($driverInstallLoc, $dest, JPATH_SITE, true, false);
		if ($moveRes !== true)
		{
			echo "<p style=\"color:red\">failed to moved " . $driverInstallLoc . ' to ' . $dest . '</p>';
			return false;
		}
		return true;
	}

	/**
	 * Run when the component is unistalled.
	 *
	 * @param   object  $parent  installer object
	 *
	 * @return  void
	 */

	public function uninstall($parent)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$dest = JPATH_SITE . '/libraries/joomla/document/fabrikfeed';
		if (JFolder::exists($dest))
		{
			if (!JFolder::delete($dest))
			{
				return false;
			}
		}
		$dest = JPATH_SITE . '/libraries/joomla/database/database';
		foreach ($this->drivers as $driver)
		{
			if (JFile::exists($dest . '/' . $driver))
			{
				JFile::delete($dest . '/' . $driver);
			}
		}
	}

	/**
	 * God knows why but install component, uninstall component and install
	 * again and component_id is set to 0 for the menu items grrrrr
	 *
	 * @return  void
	 */

	protected function fixmMenuComponentId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')->from('#__extensions')->where('element =  "com_fabrik"');
		$db->setQuery($query);
		$id = $db->loadResult();
		$query->clear();
		$query->update('#__menu')->set('component_id = ' . $id)->where('path LIKE "fabrik%"');
		$db->setQuery($query)->query();
	}

	/**
	 * Run when the component is updated
	 *
	 * @param   object  $parent  installer object
	 *
	 * @return  bool
	 */

	public function update($parent)
	{
		/*if (!$this->moveFiles($parent, true)) {
		    return false;
		} else {
		    echo "<p style=\"color:green\">Libray files moved</p>";
		}*/
		return true;
	}

	/**
	 * Run before installation or upgrade run
	 *
	 * @param   string  $type    discover_install (Install unregistered extensions that have been discovered.)
	 *  or install (standard install)
	 *  or update (update)
	 * @param   object  $parent  installer object
	 *
	 * @return  void
	 */

	public function preflight($type, $parent)
	{

	}

	/**
	 * Run after installation or upgrade run
	 * @param   string  $type    discover_install (Install unregistered extensions that have been discovered.)
	 *  or install (standard install)
	 *  or update (update)
	 * @param   object  $parent  installer object
	 *
	 * @return  bool
	 */

	public function postflight($type, $parent)
	{
		$db = JFactory::getDbo();

		// Remove old update site
		$db->setQuery("DELETE FROM #__update_sites WHERE location LIKE '%update/component/com_fabrik%'");
		$db->execute();
		if (!$db->execute())
		{
			echo "<P>didnt remove old update site</p>";
		}
		else
		{
			echo "<p style=\"color:green\">removed old update site</p>";
		}
		$db
			->setQuery(
				"UPDATE #__extensions SET enabled = 1 WHERE type = 'plugin' AND (folder LIKE 'fabrik_%' OR (folder='system' AND element = 'fabrik'))");
		$db->execute();
		$this->fixmMenuComponentId();
		if ($type !== 'update')
		{
			if (!$this->setConnection())
			{
				echo "<p style=\"color:red\">Didn't set connection. Aborting installation</p>";
				exit;
				return false;
			}
		}
		echo "<p style=\"color:green\">Default connection created</p>";

		if (!$this->moveFiles($parent))
		{
			echo "<p style=\"color:red\">Unable to move library files. Aborting installation</p>";
			exit;
			return false;
		}
		else
		{
			echo "<p style=\"color:green\">Libray files moved</p>";
		}

		if ($type !== 'update')
		{
			if (!$this->setDefaultProperties())
			{
				echo "<p>couldnt set default properties</p>";
				exit;
				return false;
			}

		}
		echo "<p>Installation finished</p>";
		echo '<p><a target="_top" href="index.php?option=com_fabrik&amp;task=home.installSampleData">Click
here to install sample data</a></p>
	  ';

		// An example of setting a redirect to a new location after the install is completed
		// $parent->getParent()->set('redirect_url', 'http://www.google.com');

		// $upgrade = JModel::getInstance('Upgrade', 'FabrikModel');
	}

}
