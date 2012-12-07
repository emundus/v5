<?php
/**
* @version		$Id: view.html.php 8425 2007-08-17 05:30:29Z tcp $
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Users component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class UserViewUser extends JView
{
	function display( $tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$layout	= $this->getLayout();
		if( $layout == 'form') {
			$this->_displayForm($tpl);
			return;
		}
		
		if ( $layout == 'login' ) {
			parent::display($tpl);
			return;
		}

		$user =& JFactory::getUser();
		// Set pathway information
		$this->assignRef('user'   , $user);

		parent::display($tpl);
	}

	function _displayForm($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$user     =& JFactory::getUser();
		$document =& JFactory::getDocument();
		// $db		  =& JFactory::getDBO();
		// Get the parameters of the active menu item
		//$menu = &JMenu::getInstance('Connexion');
		//$item = $menu->getActive();

		// Set page title
		//$document->setTitle( $item->name );

		// check to see if Frontend User Params have been enabled
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$check = $usersConfig->get('frontend_userparams');

		if ($check == '1' || $check == 1 || $check == NULL)
		{
			$params		= $user->getParameters();
			$result		= $user->authorize( 'com_user', 'edit' );
			// TODO: We should really act on a $result = 0 which is not authorised to change details
			$setupFile	= 'users_'.preg_replace( '#[^A-Z0-9]#i', '_', strtolower( $result ) );

			if (file_exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.$setupFile.'.xml' )) {
				$params->loadSetupFile( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.$setupFile.'.xml' );
			} else {
				$params->loadSetupFile( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.'users.xml' );
			}
		}
		
		$isAppSend =& $this->get('ifApplicationSend');
		$this->assignRef('isAppSend'  , $isAppSend);
		$this->assignRef('user'  , $user);
		$this->assignRef('params', $params);

		
		parent::display($tpl);
	}
	/**
	 * Now we generate the actual markup.
	 */
	function printProfileOptions($selected){
		$db = &JFactory::getDBO();
		$query	= 'SELECT id, label FROM #__emundus_setup_profiles WHERE published = 1';
		$db->setQuery($query);
		$profiles = $db->loadObjectList();
		echo '<option value="">'.JText::_('PLEASE_SELECT').'</option>';
		foreach($profiles as $item){
			if($item->id == $selected)
				echo '<option value="'.$item->id.'" selected="selected">'.$item->label.'</option>';
			else
				echo '<option value="'.$item->id.'">'.$item->label.'</option>';
		}
	}
	
	function printProfileAllowedOptions($selected, $user_id){
		$db = &JFactory::getDBO();
		$query	= 'SELECT esp.id, esp.label FROM #__emundus_setup_profiles AS esp 
		LEFT JOIN #__emundus_users_profiles AS eup ON esp.id=eup.profile_id 
		WHERE user_id='.$user_id;
		$db->setQuery($query);
		$profiles = $db->loadObjectList();
		foreach($profiles as $item){
			if($item->id == $selected)
				echo '<option value="'.$item->id.'" selected="selected">'.$item->label.'</option>';
			else
				echo '<option value="'.$item->id.'">'.$item->label.'</option>';
		}
	}
}
