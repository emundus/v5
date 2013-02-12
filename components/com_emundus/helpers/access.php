<?php
/**
 * @version		$Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperAccess{
	
	function isAllowed($usertype,$allowed){
		return in_array($usertype, $allowed);
	}
	
	function isAllowedAccessLevel($user_id,$current_menu_access){
		$user_access_level=JAccess::getAuthorisedViewLevels($user_id);
		return in_array($current_menu_access, $user_access_level);
	}
	
	function isAdministrator($user_id){
		return EmundusHelperAccess::isAllowedAccessLevel($user_id,8);
	}
	
	function isCoordinator($user_id){
		return EmundusHelperAccess::isAllowedAccessLevel($user_id,7);
	}
	function isPartner($user_id){
		return EmundusHelperAccess::isAllowedAccessLevel($user_id,6);
	}
	
	function isEvaluator($user_id){
		return EmundusHelperAccess::isAllowedAccessLevel($user_id,5);
	}
	
	function isApplicant($user_id){
		return EmundusHelperAccess::isAllowedAccessLevel($user_id,4);
	}
	
	/**
	 * Get the eMundus groups for a user.
	 *
	 *
	 * @param	int	$user			The user id.
	 *
	 * @return	array	The array of groups for user.
	 * @since	4.0
	*/
	function getProfileAccess($user){
		$db =& JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id='.$user;
		$db->setQuery( $query );
		$profiles = $db->loadResultArray();
		return $profiles;
	}
	
}
?>