<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 
/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerIncomplete extends JController {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'incomplete';
			JRequest::setVar('view', $default );
		}
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			parent::display();
		}
    }
	
	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		EmundusHelperEmails::sendApplicantEmail();
	}

	function clear() {
		EmundusHelperFilters::clear();
	}
	/*
	function clear() {
		// Starting a session.
		$session =& JFactory::getSession();
		$session->clear( 'profile' );
		$session->clear( 'quick_search' );
		$session->clear( 's_elements' );
		$session->clear( 's_elements_values' );
		//$session->clear( 'groups' );
		//$session->clear( 'finalgrade' );
		//$session->clear( 'evaluator' );
		
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$Itemid=JSite::getMenu()->getActive()->id;
		
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
	}*/
	
	function getCampaign()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT schoolyear FROM #__emundus_setup_profiles WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}

	function unvalidate() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session =& JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);
		
		//die(print_r($session->get('s_search')));
		if(!empty($uid) && is_numeric($uid)) {
			$db =& JFactory::getDBO();
			$db->setQuery('UPDATE #__emundus_declaration SET validated = 0 WHERE user = '.mysql_real_escape_string($uid));
			$db->Query();
		}
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('Application form unvalidated'), 'message');
	}
	
	function validate() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session =& JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);
		
		if(!empty($uid) && is_numeric($uid)) {
			$db =& JFactory::getDBO();
			$db->setQuery('UPDATE #__emundus_declaration SET validated = 1 WHERE user = '.mysql_real_escape_string($uid));
			$db->Query();
		}
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('Application form validated'), 'message');
	}
	
	function administrative_check($reqids = null) {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db =& JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$validation_list = JRequest::getVar('validation_list', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$elements_items = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$elements_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
	 	// Starting a session.
		$session =& JFactory::getSession();
		$session->set('s_elements', $elements_items);
		$session->set('s_elements_values', $elements_values);
		
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {
				$db->setQuery('UPDATE #__emundus_declaration SET validated = '.$validation_list.' WHERE user = '.mysql_real_escape_string($id));
				$db->Query() or die($db->getErrorMsg());
			}
		}
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid'.$Itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
	}
	
	
	function push_true(){
		$user =& JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db =& JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$comment = JRequest::getVar('comments', null, 'POST');
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		
		foreach ($ids as $id) {
			if(!empty($comment)) {
				$query = 'INSERT INTO `#__emundus_comments` (applicant_id,user_id,reason,date,comment) 
						VALUES('.$id.','.$user->id.',"Consider application form as complete","'.date("Y.m.d H:i:s").'","'.$comment.'")';
				$db->setQuery( $query );
				$db->query();
			}
			$query = 'INSERT INTO #__emundus_declaration (time_date, user) VALUES("'.date("Y.m.d H:i:s").'", '.$id.')';
			$db->setQuery( $query );
			$db->query();
		}
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&Itemid='.$Itemid, JText::_('ACTION_DONE').' : '.count($ids), 'message');
	}
	
	/**
	 * export selected to xls
	 */
	function export_incompletes_xls() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		//require_once('libraries/emundus/excel.php');
		
		require_once('libraries/emundus/test.php');
		
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		JArrayHelper::toInteger( $cid, 0 );
		if (count( $cid ) == 0) 
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );		
		else 
			export_incompletes_xls($cid);
		exit;
	}

	
	////// Export incomplete application form ///////////////////
	function export_incomplete_to_xls() {
		$user =& JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		require_once('libraries/emundus/excel.php');
		
		$db	= &JFactory::getDBO();
		$query = 'SELECT u.id FROM #__users AS u
			 	  LEFT JOIN #__emundus_users AS eu on eu.user_id=u.id
				  LEFT JOIN #__emundus_setup_profiles AS esp on eu.profile=esp.id
				  WHERE esp.published=1 
				  AND u.block = 0 
				  AND eu.schoolyear like "%'.$this->getCampaign().'%"
				  AND u.id NOT IN (
								   SELECT user 
								   FROM #__emundus_declaration) ';
		$no_filter = array("Super Users", "Administrator");
		if (!in_array($user->usertype, $no_filter)) {
			$model = &$this->getModel('check');
			$query .= ' AND eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$model->getProfileAcces($user->id)).')) ';
		}
		$query .= ' ORDER BY eu.lastname';//  LIMIT 724,1';
		$db->setQuery( $query );
		$cid = $db->loadResultArray();
		
		export_incompletes_xls($cid);
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count($cid)>1)
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('ACTION_DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
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
		$Itemid=JSite::getMenu()->getActive()->id;
		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
			exit;
		}
		zip_file($cid);
		exit;
	}
	
	////// EMAIL GROUP OF ASSESSORS O AN ASSESSOR WITH CUSTOM MESSAGE///////////////////
	/*function customEmail() {
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
		$Itemid=JSite::getMenu()->getActive()->id;
		if ($captcha !== 1) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NOT_A_VALID_POST' ) );
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
			return;
		}
		if (count( $cid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
			return;
		}
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid);
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
		$Itemid=JSite::getMenu()->getActive()->id;
		if ($error>0) {
			JError::raiseWarning( 500, JText::_( 'ACTION_ABORDED' ) );
			return;
		} else {
			$this->setRedirect('index.php?option=com_emundus&view=incomplete&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$Itemid, JText::_('REPORTS_MAILS_SENT'), 'message');
		}
		
	}*/
}
?>