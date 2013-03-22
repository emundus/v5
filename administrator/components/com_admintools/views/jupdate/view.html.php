<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load framework base classes
JLoader::import('joomla.application.component.view');

if(!class_exists('JoomlaCompatView')) {
	if(interface_exists('JView')) {
		abstract class JoomlaCompatView extends JViewLegacy {}
	} else {
		class JoomlaCompatView extends JView {}
	}
}

class AdmintoolsViewJupdate extends JoomlaCompatView
{
	public function display($tpl = null)
	{
		// Set the toolbar title
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_JUPDATE'),'admintools');

		$task = JRequest::getCmd('task','default');
		$force = ($task == 'force');

		switch($task)
		{
			case 'default':
			default:
				// Get the update information
				$updates = $this->getModel('jupdate');
				$updateinfo = $updates->getUpdateInfo($force);
				$this->assign('updateinfo',			$updateinfo );

				JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');

				$this->setLayout('default');

				break;

			case 'preinstall':
				$updates = $this->getModel('jupdate');
				$file = JRequest::getString('file','');
				$ftpparams			= $updates->getFTPParams();
				$extractionmodes	= $updates->getExtractionModes();
				$j17				= JRequest::getInt('j17',0);

				$this->assign('hasakeeba',		$updates->hasAkeebaBackup());
				$this->assign('file',			$file);
				$this->assign('ftpparams',		$ftpparams);
				$this->assign('extractionmodes',$extractionmodes);
				
				$this->setLayout('preinstall');

				break;

			case 'install':
				$session = JFactory::getSession();
				$password = $session->get('update_password', '', 'admintools');
				$file = JRequest::getString('file','');

				if(empty($password))
				{
					$password = JRequest::getVar('password','','default','none',2);
				}
				$this->assign('password', $password );
				$this->assign('file',			$file);

				$this->setLayout('install');

				break;
		}


		// Load CSS
		$document = JFactory::getDocument();
		$document->addScript(rtrim(JURI::base(),'/').'/../media/com_admintools/js/json2.js');
		$document->addScript(rtrim(JURI::base(),'/').'/../media/com_admintools/js/encryption.js');
		$document->addScript(rtrim(JURI::base(),'/').'/../media/com_admintools/js/backend.js');
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			JHTML::_('behavior.framework');
		} else {
			JHTML::_('behavior.mootools');
		}

		parent::display();
	}
}