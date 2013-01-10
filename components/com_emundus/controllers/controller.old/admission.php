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
class EmundusControllerAdmission extends JController {

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
			$default = 'admission';
			JRequest::setVar('view', $default );
		}
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			parent::display();
		}
    }
	
	function clear() {
		// Starting a session.
		$session =& JFactory::getSession();
		$session->clear( 'uid' );
		$session->clear( 'profile' );
		$session->clear( 'finalgrade' );
		$session->clear( 'quick_search' );
		$session->clear( 's_elements' );
		$session->clear( 's_elements_values' );
		$session->clear( 'engaged' );
		$session->clear( 'schoolyears' );
		
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	function set_engaged(){
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$db =& JFactory::getDBO();
		
		$sid = JRequest::getVar('sid', null, 'GET', 'int',0);
		$to_set = JRequest::getVar('set', null, 'GET', 'int',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		$query = 'UPDATE #__emundus_final_grade SET engaged='.$to_set.' WHERE student_id='.$sid;
		//die(str_replace('#_','jos',$query));
			
		$db->setQuery($query);
		$db->Query() or die($db->getErrorMsg());
		
		echo JText::_('SAVED');		
	}
	
	function set_info(){
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$db =& JFactory::getDBO();
		
		$sid = JRequest::getVar('sid', null, 'GET', 'int',0);
		$f = JRequest::getVar('f', null, 'GET', 'none',0);
		$info = JRequest::getVar('info', null, 'GET', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		$query = 'UPDATE #__emundus_final_grade SET '.$f.'="'.str_replace('|-|','\n',$info).'" WHERE student_id='.$sid;
		//die($query);
			
		$db->setQuery($query);
		$db->Query() or die($db->getErrorMsg());
		
		echo JText::_('SAVED');		
	}
	
////// STUDENT TO SELECTED PROFILE ///////////////////
// 
// put Student as selected profile : from Joomla Registred profile
	function setPID() {
		//$allowed = array("Super Administrator", "Administrator");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db =& JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$profile_id = JRequest::getVar('profile_id', null, 'POST', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {	
				if($profile_id == 9) {
					$db->setQuery('DELETE FROM #__emundus_confirmed_applicants WHERE user_id='.$id);
					$db->Query() or die($db->getErrorMsg());
				}
				$db->setQuery('UPDATE #__emundus_users SET profile='.$profile_id.' WHERE user_id='.$id);
				$db->Query() or die($db->getErrorMsg());
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('DONE').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	////// UNAFFECT ASSESSOR ///////////////////
	function unsetAssessor($reqids = null) {
		//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$db =& JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$au_id = JRequest::getVar('assessor_user', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {				
				//if(!empty($au_id) && isset($au_id)) {
					$query = 'DELETE FROM #__emundus_confirmed_applicants WHERE evaluator_id='.$au_id.' AND user_id='.$id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				//}
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANTS_UNAFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANT_UNAFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	function delassessor() {
		$user =& JFactory::getUser();
		//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$aid = JRequest::getVar('aid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'GET', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', null, 0);
		
		if(!empty($aid) && is_numeric($aid)) {
			$db =& JFactory::getDBO();
			$query = 'DELETE FROM #__emundus_confirmed_applicants WHERE user_id='.mysql_real_escape_string($uid);
			if(!empty($uid) && is_numeric($uid))
				$query .= ' AND evaluator_id='.mysql_real_escape_string($aid);
			$db->setQuery($query);
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// Export selected application form to XLS ///////////////////
	function export_to_xls () {
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$this->export_all_to_xls ($cid);
	}
	
	////// Export application form  to XLS  ///////////////////
	function export_all_to_xls ($reqids = null) {
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator and Administrator can access this function.'), 'error');
			return;
		}
		$mainframe =& JFactory::getApplication();
		require_once('libraries/emundus/xls_admission.php');

		if ($reqids) {
			$cid = $reqids;
		} else {
			$db	= &JFactory::getDBO();
			$query = 'SELECT ed.user 
					  FROM #__emundus_declaration AS ed
					  LEFT JOIN #__emundus_users AS eu ON eu.user_id=ed.user  
					  WHERE eu.profile in (7,8,10,11)'; //selected + enrolled + alumni + old students
			$db->setQuery( $query );
			$cid = $db->loadResultArray();
		}
//die(print_r($cid));
		selected($cid);

		if (count($cid)>1)
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	////// EMAIL ASSESSORS WITH DEFAULT MESSAGE///////////////////
	function defaultEmail($reqids = null) {
		//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$mainframe =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		// List of evaluators		
		$query = 'SELECT distinct(eca.evaluator_id)  
					FROM `#__emundus_confirmed_applicants` as eca 
					LEFT JOIN `#__emundus_users` as eu on eca.user_id=eu.user_id 
					WHERE eu.profile = 8';
		$db->setQuery( $query );
		$users = $db->loadResultArray();

		// Récupération des données du mail
		$query = 'SELECT id, subject, emailfrom, name, message
						FROM #__emundus_setup_emails
						WHERE lbl="teachers_set"';
		$db->setQuery( $query );
		$db->query();
		$obj=$db->loadObjectList();

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
			$from = $admin->email;
			$from_id = $admin->id;
			$fromname = $admin->name;
		}

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[SITE_URL\]/', '/\n/');
		$error=0;
		foreach ($users as $uid) {
			$user =& JFactory::getUser($uid);
			
			$replacements = array ($user->id, $user->name, $user->email, JURI::base(), '<br />');
			// template replacements
			$body = preg_replace($patterns, $replacements, $obj[0]->message);
			unset($replacements);
			// mail function
			if (JUtility::sendMail($from, $obj[0]->name, $user->email, $obj[0]->subject, $body, 1)) {
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES ('".$from_id."', '".$user->id."', '".$obj[0]->subject."', '".$body."', NOW())";
					$db->setQuery( $sql );
					$db->query();
			} else {
				$error++;
			}
		}
		if ($error>0)	
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_ABORDED'), 'error');
		else 
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// EMAIL GROUP OF ASSESSORS O AN ASSESSOR WITH CUSTOM MESSAGE///////////////////
	function customEmail() {
		$user =& JFactory::getUser();
		//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		$mainframe =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$ag_id = JRequest::getVar('mail_group', null, 'POST', 'none',0);
		$ae_id = JRequest::getVar('mail_user', null, 'POST', 'none',0);
		$subject = JRequest::getVar('mail_subject', null, 'POST', 'none',0);
		$message = JRequest::getVar('mail_body', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
			return;
		}
		
		// List of evaluators
		if (isset($ag_id) && $ag_id > 0) {
			$query = 'SELECT eg.user_id 
						FROM `#__emundus_groups` as eg 
						WHERE eg.group_id='.$ag_id;
			$db->setQuery( $query );
			$users = $db->loadResultArray();
		} 
		elseif (isset($ae_id) && $ae_id > 0)
			$users[] = $ae_id;
		else {
			JError::raiseWarning( 500, JText::_('ERROR') );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
			return;
		}

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
			$from = $admin->email;
			$from_id = $admin->id;
			$fromname = $admin->name;
		}

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[SITE_URL\]/', '/\n/');

		foreach ($users as $uid) {
			$user =& JFactory::getUser($uid);
			
			$replacements = array ($user->id, $user->name, $user->email, JURI::base(), '<br />');
			// template replacements
			$body = preg_replace($patterns, $replacements, $message);
	
			// mail function
			if(JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1)) {
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$user->id."', '".$subject."', '".$body."', NOW())";
				$db->setQuery( $sql );
				$db->query();
				unset($replacements);
			}
			else
				JError::raiseWarning( 500, JText::_('ERROR_EMAIL_NOT SENT'). ' : '. $user->email );
			
		}
			
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
} //END CLASS
?>