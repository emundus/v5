<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsControllerCpanel extends FOFController
{
	public function execute($task) {
		if($task != 'login') $task = 'browse';
		$this->task = 'browse';
		parent::execute($task);
	}

	public function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			$model = $this->getModel('Jupdate',		'AdmintoolsModel');
			$model2 = $this->getModel('Cpanel',		'AdmintoolsModel');

			$view = $this->getThisView();
			$view->setModel($model,		false);
			$view->setModel($model2,	true);

			$this->getThisModel()->autoMigrate();
			$needDLID = $this->getThisModel()->needsDownloadID();
			$view->assign('needsdlid', $needDLID);

			// Check the last installed version (only the Professional release)
			if(ADMINTOOLS_PRO) {
				$versionLast = null;
				if(file_exists(JPATH_COMPONENT_ADMINISTRATOR.'/admintools.lastversion.php')) {
					include_once JPATH_COMPONENT_ADMINISTRATOR.'/admintools.lastversion.php';
					if(defined('ADMINTOOLS_LASTVERSIONCHECK')) $versionLast = ADMINTOOLS_LASTVERSIONCHECK;
				}
				if(is_null($versionLast)) {
					// FIX 2.1.13: Load the component parameters WITHOUT using JComponentHelper
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);

					$query->select(array($db->quoteName('params')))
						->from($db->quoteName('#__extensions'))
						->where($db->quoteName('type').' = '.$db->Quote('component'))
						->where($db->quoteName('element').' = '.$db->Quote('com_admintools'));
					$db->setQuery($query);
					$rawparams = $db->loadResult();
					$params = new JRegistry();
					if(version_compare(JVERSION, '3.0', 'ge')) {
						$params->loadString($rawparams, 'JSON');
					} else {
						$params->loadJSON($rawparams);
					}

					$versionLast = $params->get('lastversion','');
				}
				if(version_compare(ADMINTOOLS_VERSION, $versionLast, 'ne') || empty($versionLast)) {
					$this->setRedirect('index.php?option=com_admintools&view=postsetup');
					return true;
				}
			}
		}
		return $result;
	}

	public function login()
	{
		$model = $this->getModel('Masterpw');
		$password = $this->input->getVar('userpw','');
		$model->setUserPassword($password);

		$url = 'index.php?option=com_admintools';
		$this->setRedirect($url);
	}
}
