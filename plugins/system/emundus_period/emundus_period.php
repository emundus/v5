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
		$jinput = JFactory::getApplication()->input;
		$user = & JFactory::getUser();
		
		// Global variables
		define('EMUNDUS_PATH_ABS', JPATH_ROOT.DS.'images'.DS.'emundus'.DS.'files'.DS);
		define('EMUNDUS_PATH_REL', './images/emundus/files/');
		define('EMUNDUS_PHOTO_AID', 10);
		
		if ($user->usertype == 'Registered' ) {
			$baseurl = JURI::base();
			$db = & JFactory::getDBO();
			$app =& JFactory::getApplication();
			
			$id = @$jinput->get('id');
			$option = @$jinput->get('option');
			$task = @$jinput->get('task');
			$view = @$jinput->get('view');

			date_default_timezone_set('Europe/London');
			$script_tz = date_default_timezone_get();
			
			$db->setQuery('SELECT schoolyear FROM #__emundus_setup_profiles WHERE id = '.$user->profile);
			$schoolyear = $db->loadResult();
			
			if($user->profile != 8){
				if ( ($id == 29 || $id == 30 || $id == 78 || ($id >= 19 && $id <= 24)) && $option == 'com_content' || $task == 'logout' || $option == 'com_contact' || $view == 'renew_application') {
						return '';
				}elseif($schoolyear != $user->schoolyear){
					die($app->redirect('index.php?option=com_emundus&view=renew_application'));
				}elseif ( strtotime(date("Y-m-d H:m:i")) > strtotime($user->candidature_end) ) {
					JError::raiseNotice('PERIOD', utf8_encode(JText::sprintf('PERIOD',strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_start) ),strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_end) ))));
					die($app->redirect('index.php?option=com_content&id=29'));
				} elseif ( strtotime(date("Y-m-d H:m:i")) < strtotime($user->candidature_start) ) { 
					JError::raiseNotice('PERIOD', utf8_encode(JText::sprintf('PERIOD',strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_start) ),strftime("%A %d %B %Y %H:%M", strtotime($user->candidature_end) ))));
					die($app->redirect('index.php?option=com_content&id=30')); 
				}
			}
		}
	}

}
