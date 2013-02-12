<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Extension that we need in order to handle all the new information that we collect.
 */
class TableUsersprofiles extends JTable
{

	var $id 		= null;
	var $date_time	= null;
	var $user_id 		= null;
	var $profiles_id	= null;
	var $start_date	= "CURRENT_TIMESTAMP";
	var $end_date	= "0000-00-00 00:00:00";
	/**
	 * We extend/override the parent.
	 *
	 * @param unknown_type $db
	 */
	function __construct(&$db){
		parent::__construct( '#__emundus_users_profiles', 'id', $db );
	}
	
	function check(){
		// write here data validation code
		return parent::check();
	}
}
?>
