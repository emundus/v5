<?php
/**
 * @version		$Id: example.php 14401 2010-01-26 14:10:00Z louis $
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


/**
 * Example User Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgUserEmundus extends JPlugin {

	/**
	 * Example store user method
	 *
	 * Method is called before user data is stored in the database
	 *
	 * @param 	array		holds the old user data
	 * @param 	boolean		true if a new user is stored
	 */
	public function onUserBeforeStore($user, $isnew){
		global $mainframe;
	}

	/**
	 * Example store user method
	 *
	 * Method is called after user data is stored in the database
	 *
	 * @param 	array		holds the new user data
	 * @param 	boolean		true if a new user is stored
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	public function onUserAfterStore($user, $isnew, $success, $msg){
		//global $mainframe;
		$db	=& JFactory::getDBO();
		$id = $user['id'];
		
		//$id = $current_user->id;
			
		//new user
		if ($isnew){
			//id du mail de relance
			$query = '	SELECT id
						FROM #__emundus_setup_emails
						WHERE lbl = "reminder"';
			$db->setQuery( $query );
			$mail_id=$db->loadResult(); 
			
			//nombre de jours entre chaque relance
			$eMConfig =& JComponentHelper::getParams('com_emundus');
			$periode = $eMConfig->get('reminder', '30');
			//date du jour
			$date = date('Y-m-d G:i:s');
			
			$query = '	INSERT INTO #__emundus_emailalert (user_id,email_id,date_time,periode) 
						VALUES ('.$id.','.$mail_id.',"'.$date.'",'.$periode.')';
			$db->setQuery( $query );
			$db->query();
		}
	}

	/**
	 * Example store user method
	 *
	 * Method is called before user data is deleted from the database
	 *
	 * @param 	array		holds the user data
	 */
	public function onUserBeforeDelete($user){
		global $mainframe;
	}

	
	/**
	 * Example store user method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param 	array		holds the user data
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	public function onUserAfterDelete($user, $succes, $msg){
		$allowed = array("Super Administrator", "Administrator", "Editor");
		$id = $user['id'];
		$chemin = EMUNDUS_PATH_ABS;
		$dir = $chemin.$id.DS;
		$db 	=& JFactory::getDBO();
		
		if (in_array(JFactory::getUser()->usertype, @$allowed)){
			//delete user
			$db->setQuery('DELETE FROM #__emundus_users WHERE user_id ='.$id);
			$db->Query() or die($db->getErrorMsg());
			
			$db->setQuery('DELETE FROM #__emundus_emailalert WHERE user_id ='.$id);
			$db->Query() or die($db->getErrorMsg());
			
			//delete users' attachments
			$query 	= 'SELECT filename FROM #__emundus_uploads WHERE user_id = '.mysql_real_escape_string($id);
			$db->setQuery( $query );
			$filename = $db->loadResult();
			
			if (!empty($filename)) { 
				if (is_file($dir.$filename)) {
					if (unlink($dir.$filename)) {
						$query 	= 'DELETE FROM #__emundus_uploads WHERE user_id = '.mysql_real_escape_string($id);
						$db->setQuery( $query );
						$db->Query();
						if (is_file($dir.'tn_'.$filename)) unlink($dir.'tn_'.$filename);
					} 
				} else {
					$query 	= 'DELETE FROM #__emundus_uploads WHERE user_id = '.mysql_real_escape_string($id);
					$db->setQuery( $query );
					$db->Query();
				}
			}
			
			//delete users' information from all tables
			$db->setQuery('SHOW TABLES');
			$tables = $db->LoadResultArray();
			
			foreach($tables as $table) {
				if(strpos($table, 'emundus_')===FALSE) continue;
				if(strpos($table, 'setup_')>0 || strpos($table, '_country')>0 || strpos($table, '_users')>0) continue;
				if(strpos($table, '_files_request')>0 || strpos($table, '_evaluations')>0 || strpos($table, '_final_grade')>0) {
					$db->setQuery('DELETE FROM '.$table.' WHERE student_id ='.$id);
				} elseif(strpos($table, '_uploads')>0) { 
					$db->setQuery('DELETE FROM '.$table.' WHERE user_id ='.$id);
				} elseif(strpos($table, '_groups_eval')>0) { 
					$db->setQuery('DELETE FROM '.$table.' WHERE (user_id ='.$id.' OR applicant_id ='.$id);
				} elseif(strpos($table, '_groups')>0) { 
					$db->setQuery('DELETE FROM '.$table.' WHERE user_id ='.$id);
				} else {
					$db->setQuery('DELETE FROM '.$table.' WHERE user ='.$id);
				}
				$db->Query();
			}
				
			//delete application
			if(is_dir($dir)) {
				$dh = opendir($dir);
				while (false !== ($obj = readdir($dh))) {
					if($obj == '.' || $obj == '..') continue;
					if(!@unlink($dir.$obj)) { 
						JFactory::getApplication()->enqueueMessage(JText::_("File not found")." : ".$obj."\n", 'error');  
					}
				}
				closedir($dh);
				@rmdir($dir);
			}
		}else{
			die(JText::_('You cannot delete this user'));
		}
			
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 * @access	public
	 * @param 	array 	holds the user data
	 * @param 	array    extra options
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options = array()){
		die();
		// Here you would do whatever you need for a login routine with the credentials
		// Remember, this is not the authentication routine as that is done separately.
		// The most common use of this routine would be logging the user into a third party application
		// In this example the boolean variable $success would be set to true if the login routine succeeds
		// ThirdPartyApp::loginUser($user['username'], $user['password']);
		
		
		$current_user	 =& JFactory::getUser();
		$db		 =& JFactory::getDBO();
		
		
		$db->setQuery('	SELECT eu.firstname, eu.lastname, eu.profile, eu.university_id, esp.label AS profile_label, esp.menutype, esp.published, esp.candidature_start, esp.candidature_end, esp.schoolyear
						FROM #__emundus_users AS eu 
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile 
						WHERE eu.user_id = '.$current_user->id);
		$res = $db->loadObject();
		
		$current_user->firstname 		= @$res->firstname;
		$current_user->lastname	 		= @$res->lastname;
		$current_user->profile	 		= @$res->profile;
		$current_user->profile_label 	= @$res->profile_label;
		$current_user->menutype	 		= @$res->menutype;
		$current_user->university_id	= @$res->university_id;
		$current_user->applicant		= @$res->published;
		$current_user->candidature_start= @$res->candidature_start;
		$current_user->candidature_end	= @$res->candidature_end;
		
		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @access public
	 * @param array holds the user data
	 * @return boolean True on success
	 * @since 1.5
	 */
	public function onUserLogout($user){
		// Initialize variables
		$success = false;

		// Here you would do whatever you need for a logout routine with the credentials
		//
		// In this example the boolean variable $success would be set to true
		// if the logout routine succeeds

		// ThirdPartyApp::loginUser($user['username'], $user['password']);

		return $success;
	}
}
