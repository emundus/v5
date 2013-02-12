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
		$db =& JFactory::getDBO();
		if(empty($_GET['profile'])) exit;
		$query = 'SELECT id, schoolyear, description FROM `#__emundus_setup_profiles` WHERE id="'.mysql_real_escape_string($_GET['profile']).'"';
		$db->setQuery($query);
		$pro = $db->loadObject();
		echo '<p class="description">'.$pro->description.'</p><p class="description">'.JText::_( 'YOUR_SCHOOLYEAR' ).'
		<input type="hidden" name="schoolyear" id="schoolyear" value="'.$pro->schoolyear.'"/>'.$pro->schoolyear.'</p>';
	}
}
?>