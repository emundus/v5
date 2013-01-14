<?php
/**
 * @version		$Id: controller.php 750 2008-07-16 22:29:38Z eddieajau $
 * @package		Emundus
 * @copyright	(C) 2005 - 2008 JXtended LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Custom report controller
 * @package		Emundus
 */
class EmundusControllerCandidate_evaluate extends JController
{		
	function clear() {
		unset($_SESSION['s_elements']);
		unset($_SESSION['s_elements_values']);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
	}
	
	function export_zip() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		require_once('libraries/emundus/zip.php');
		$db	= &JFactory::getDBO();
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger( $cid, 0 );

		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			exit;
		}
		zip_file($cid);
		exit;
	}
	
	/**
	 * export applicants partner list to xls
	 */
	function export_to_xls() {
		$mainframe =& JFactory::getApplication();
		require_once('libraries/emundus/excel.php');

		$current_user =& JFactory::getUser();
		
		$db	= &JFactory::getDBO();
		$query = 'SELECT ege.applicant_id 
					FROM `#__emundus_groups_eval` as ege 
					WHERE ege.user_id='.$current_user->id.' OR 
					ege.group_id IN (SELECT group_id FROM #__emundus_groups WHERE user_id='.$current_user->id.')';
		$db->setQuery( $query );
		$cid = $db->loadResultArray();
//die(print_r($cid));
		if(count($cid)>0)
			export_complete($cid);
		else
			return JError::raiseWarning( 500, JText::_( 'Error, none applicant found' ) );
	}
	
		////// EMAIL /////////////
	function customEmail() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		
		$mainframe =& JFactory::getApplication();

		$db	= &JFactory::getDBO();
		$current_user =& JFactory::getUser();

		$cid		= JRequest::getVar( 'ud', array(), 'post', 'array' );
		$captcha	= 1;//JRequest::getInt( JR_CAPTCHA, null, 'post' );

		$subject	= JRequest::getVar( 'mail_subject', null, 'post' );
		$message	= JRequest::getVar( 'mail_body', null, 'post' );
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session =& JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);

		if ($captcha !== 1) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NOT_A_VALID_POST' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}

		JArrayHelper::toInteger( $cid, 0 );


		$query = 'SELECT u.id, u.name, u.email' .
					' FROM #__users AS u' .
					' WHERE u.id IN ('.implode( ',', $cid ).')';
		$db->setQuery( $query );
		$users = $db->loadObjectList();


		// setup mail
		if (isset($current_user->email)) {
			$from = $current_user->email;
			$from_id = $current_user->id;
			$fromname=$current_user->name;
		} elseif ($mainframe->getCfg( 'mailfrom' ) != '' && $mainframe->getCfg( 'fromname' ) != '') {
			$from = $mainframe->getCfg( 'mailfrom' );
			$fromname = $mainframe->getCfg( 'fromname' );
			$from_id = 62;
		} else {
			$query = 'SELECT id, name, email' .
				' FROM #__users' .
				// administrator
				' WHERE gid = 25 LIMIT 1';
			$db->setQuery( $query );
			$admin = $db->loadObject();
			$from = $admin->name;
			$from_id = $admin->id;
			$fromname = $admin->email;
		}

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[SITE_URL\]/', '/\n/');

		$nUsers = count( $users );
		for ($i = 0; $i < $nUsers; $i++) {
			$user = &$users[$i];

			// template replacements
			$replacements = array ($user->id, $user->name, $user->email, JURI::base(), '<br />');
			// template replacements
			$body = preg_replace($patterns, $replacements, $message);

			// mail function
			//JMail( $from, $fromname, $user->email, $subject, $body ,1);
			if (JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1)) {
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$user->id."', '".$subject."', '".$body."', NOW())";
				$db->setQuery( $sql );
				$db->query();
			} else {
				$error++;
			}
		}
		if ($error>0) {
			JError::raiseWarning( 500, JText::_( 'ACTION_ABORDED' ) );
			return;
		} else {
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('REPORTS_MAILS_SENT'), 'message');
		}
		
	}
}
