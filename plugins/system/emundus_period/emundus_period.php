<?php
/**
* @version		$Id: emundus_period.php 10709 2010-04-07 09:58:52Z decisionpublique.fr $
* @package		Joomla
* @copyright	Copyright (C) 2008 - 2010 Décision Pulique. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * emundus_period candidature periode check
 *
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemEmundus_period extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemEmundus_period(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( );
	}

	function onAfterInitialise() {	
		$app 		=  JFactory::getApplication();
		$user 		=  JFactory::getUser();

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$applicant_files_path = $eMConfig->get('applicant_files_path', 'images/emundus/files/');
		// Global variables
		define('EMUNDUS_PATH_ABS', JPATH_ROOT.DS.$applicant_files_path);
		define('EMUNDUS_PATH_REL', $applicant_files_path);
		define('EMUNDUS_PHOTO_AID', 10);

		if ( !$app->isAdmin() && isset($user->id) && !empty($user->id) ) {
			$id_applicants 	= $eMConfig->get('id_applicants', '0');
			$applicants 	= explode(',',$id_applicants);
			$r 				= JRequest::getVar('r', null, 'GET', 'none',0);
			
			$baseurl = JURI::base();
			$db =  JFactory::getDBO();
			$app = JFactory::getApplication();
			
			$id = JRequest::getVar('id', null, 'GET', 'none',0);
			$option = JRequest::getVar('option', null, 'GET', 'none',0);
			$task = JRequest::getVar('task', null, 'POST', 'none',0);
			$task_get = JRequest::getVar('task', null, 'GET', 'none',0);
			$view =JRequest::getVar('view', null, 'GET', 'none',0);
			
			$no_profile = (empty($user->profile) || !isset($user->profile))?1:0; 
			if ($no_profile) $user->applicant = 1;
			if ( $r != 1 && $user->applicant==1 && !in_array($user->id, $applicants) ) {	
				if($no_profile && $task != "user.logout" && $task_get != "cancel_renew" && $option != 'com_users' && $option != 'com_content') { 
					die($app->redirect("index.php?option=com_fabrik&view=form&formid=102&random=0&r=1"));
				}
				/*date_default_timezone_set('Europe/London');
				$script_tz = date_default_timezone_get();
				*/
				elseif($user->profile != 8){
					$query = 'SELECT year FROM #__emundus_setup_campaigns WHERE id = '.$user->campaign_id; 
					$db->setQuery($query);
					$schoolyear = $db->loadResult();
					if ( ($id == 29 || $id == 30 || $id == 78 || $id == 46 || ($id >= 19 && $id <= 24)) && $option == 'com_content' || $task == 'user.logout' || $option == 'com_contact' || $option == 'com_users' || $view == 'renew_application') {
							return '';
					} /*elseif($schoolyear != $user->schoolyear){
						die($app->redirect('index.php?option=com_emundus&view=renew_application'));
					} */elseif ( empty($user->candidature_end) ) {
						die($app->redirect('index.php?option=com_content&view=article&id=46'));
					} elseif ( strtotime(date("Y-m-d H:m:i")) > strtotime($user->candidature_end) ) {
						JError::raiseNotice('PERIOD', utf8_encode(JText::sprintf('PERIOD',strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_start) ),strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_end) ))));
						//die($user->candidature_end);
						die($app->redirect('index.php?option=com_content&view=article&id=29'));
					} elseif ( strtotime(date("Y-m-d H:m:i")) < strtotime($user->candidature_start) ) { 
						JError::raiseNotice('PERIOD', utf8_encode(JText::sprintf('PERIOD',strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_start) ),strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_end) ))));
						die($app->redirect('index.php?option=com_content&view=article&id=30')); 
					}
				}
			}
		}
	}
}
