<?php
/**
* @version		$Id: view.html.php 8682 2007-08-31 18:36:45Z jinx $
* @package		Joomla
* @subpackage	Registration
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
 * HTML View class for the Registration component
 *
 * @author		David Gal <david.gal@joomla.org>
 * @package		Joomla
 * @subpackage	Registration
 * @since 1.0
 */
class UserViewRegister extends JView
{
	/**
	 * Not much, or nothing new here.
	 */
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		
		$pathway  =& $mainframe->getPathway();
		$document =& JFactory::getDocument();

	 	// Page Title
	 	$document->setTitle( JText::_( 'REGISTRATION' ) );
		$pathway->addItem( JText::_( 'NEW' ));

		// Load the form validation behavior
		JHTML::_('behavior.formvalidation');

		$user =& JFactory::getUser();
		if ( !$user->get('guest')) return;
		$this->assignRef('user', $user);
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration')) 
			parent::display($tpl);
			
		$can_register =& $this->get('Year');
		$this->assignRef('can_register', $can_register);
	}
	
	/**
	 * Now we generate the actual markup.
	 */
	function printProfileOptions($selected){
		$db = &JFactory::getDBO();
		$date = strtotime(date("Y-m-d H:m:i"));
		$query	= 'SELECT esp.id, esp.label, esp.candidature_start, esp.candidature_end FROM #__emundus_setup_profiles esp
				WHERE published = 1
				AND now() BETWEEN esp.candidature_start AND esp.candidature_end';
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
}
?>
