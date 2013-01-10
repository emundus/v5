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
class EmundusControllerEval extends JController {

	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'eval';
			JRequest::setVar('view', $default );
		}
		
		$user =& JFactory::getUser();
		if ($user->usertype == "Registered") {
			$checklist =& $this->getView( 'checklist', 'html' );
			$checklist->setModel( $this->getModel( 'checklist'), true );
			$checklist->display();
		} else {
			parent::display();
		}
    }
	
	function clear() {
		unset($_SESSION['s_elements']);
		unset($_SESSION['s_elements_values']);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$this->setRedirect('index.php?option=com_emundus&view=eval&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}

	function getCampaign()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT schoolyear FROM #__emundus_setup_profiles WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}

	////// Export complete application form with evaluation///////////////////
	function export_to_xls($reqids = null) {
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$mainframe =& JFactory::getApplication();
		require_once('libraries/emundus/xls_eval.php');

		$db	= &JFactory::getDBO();
		$query = 'SELECT distinct(ee.student_id) 
			 	  FROM #__emundus_evaluations AS ee
				  INNER JOIN #__emundus_users AS eu ON eu.user_id=ee.student_id 
				  WHERE eu.schoolyear like "%'.$this->getCampaign().'%"';
		$db->setQuery( $query );
		$cid = $db->loadResultArray();

		export_xls($cid);

		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=eval&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=eval&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	////// UNAFFECT ASSESSOR ///////////////////
	function unsetAssessor($reqids = null) {
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$db =& JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$ag_id = JRequest::getVar('assessor_group', null, 'POST', 'none',0);
		$au_id = JRequest::getVar('assessor_user', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			foreach ($ids as $id) {				
				if(!empty($ag_id) && isset($ag_id)) {
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND group_id='.$ag_id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
				elseif(!empty($au_id) && isset($au_id)) {
					$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.$id.' AND user_id='.$au_id;
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
			}
		}
		if (count($ids)>1)
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANTS_UNAFFECTED').count($ids), 'message');
		elseif (count($ids)==1)
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('MESSAGE_APPLICANT_UNAFFECTED').count($ids), 'message');
		else
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir);
	}
	
	function delassessor() {
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isPartner($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('You are not allowed to access to this page.'), 'error');
			return;
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$aid = JRequest::getVar('aid', null, 'GET', null, 0);
		$pid = JRequest::getVar('pid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		$filter_order = JRequest::getVar('filter_order', null, 'GET', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', null, 0);
		
		if(!empty($aid) && is_numeric($aid)) {
			$db =& JFactory::getDBO();
			$query = 'DELETE FROM #__emundus_groups_eval WHERE applicant_id='.mysql_real_escape_string($aid);
			if(!empty($pid) && is_numeric($pid))
				$query .= ' AND group_id='.mysql_real_escape_string($pid);
			if(!empty($uid) && is_numeric($uid))
				$query .= ' AND user_id='.mysql_real_escape_string($uid);
			$db->setQuery($query);
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// EMAIL ASSESSORS WITH DEFAULT MESSAGE///////////////////
	function defaultEmail($reqids = null) {
		$current_user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$mainframe =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		// List of evaluators
		$query = 'SELECT eg.user_id 
					FROM `#__emundus_groups` as eg 
					LEFT JOIN `#__emundus_groups_eval` as ege on ege.group_id=eg.group_id 
					WHERE eg.user_id is not null 
					GROUP BY eg.user_id';
		$db->setQuery( $query );
		$users_1 = $db->loadResultArray();
		
		$query = 'SELECT ege.user_id 
					FROM `#__emundus_groups_eval` as ege 
					WHERE ege.user_id is not null 
					GROUP BY ege.user_id';
		$db->setQuery( $query );
		$users_2 = $db->loadResultArray();

		$users = array_merge_recursive($users_1, $users_2);

		// Récupération des données du mail
		$query = 'SELECT id, subject, emailfrom, name, message
						FROM #__emundus_setup_emails
						WHERE lbl="assessors_set"';
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
		
		// Evaluations criterias
		$query = 'SELECT id, label, sub_labels
						FROM #__fabrik_elements
						WHERE group_id=41 AND (plugin like "fabrikradiobutton" OR plugin like "fabrikdropdown")';
		$db->setQuery( $query );
		$db->query();
		$eval_criteria=$db->loadObjectList();
		
		$eval = '<ul>';
		foreach($eval_criteria as $e) {
			$eval .= '<li>'.$e->label.' ('.$e->sub_labels.')</li>';
		}
		$eval .= '</ul>';

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[APPLICANTS_LIST\]/', '/\[SITE_URL\]/', '/\[EVAL_CRITERIAS\]/', '/\[EVAL_PERIOD\]/', '/\n/');
		$error=0;
		foreach ($users as $uid) {
			$user =& JFactory::getUser($uid);
			
			$query = 'SELECT applicant_id
						FROM #__emundus_groups_eval
						WHERE user_id='.$user->id.' OR group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.')';
			$db->setQuery( $query );
			$db->query();
			$applicants=$db->loadResultArray();

			if (count($applicants) > 0) {
				$list = '<ul>';
				foreach($applicants as $ap) {
					$app =& JFactory::getUser($ap);
					$list .= '<li>'.$app->name.' ['.$app->id.']</li>';
				}
				$list .= '</ul>';
				
				$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
						FROM #__emundus_setup_profiles AS esp 
						LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
						WHERE user_id='.$user->id;
				$db->setQuery( $query );
				$db->query();
				$period=$db->loadRow();
				
				$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
				
				$replacements = array ($user->id, $user->name, $user->email, $list, JURI::base(), $eval, $period_str, '<br />');
				// template replacements
				$body = preg_replace($patterns, $replacements, $obj[0]->message);
				unset($replacements);
				unset($list);
				// mail function
				if (JUtility::sendMail($from, $obj[0]->name, $user->email, $obj[0]->subject, $body, 1)) {
				//if ($body === 0) {
					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES ('".$from_id."', '".$user->id."', '".$obj[0]->subject."', '".$body."', NOW())";
					$db->setQuery( $sql );
					$db->query();
				} else {
					$error++;
				}
			}
		}
		if ($error>0)	
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_ABORDED'), 'error');
		else 
			$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
	////// EMAIL GROUP OF ASSESSORS WITH CUSTOM MESSAGE///////////////////
	function customEmail() {
		$current_user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		$mainframe =& JFactory::getApplication();
		$db =& JFactory::getDBO();
		$subject = JRequest::getVar('mail_subject', null, 'POST', 'none',0);
		$ag_id = JRequest::getVar('mail_group', null, 'POST', 'none',0);
		$message = JRequest::getVar('mail_body', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		// List of evaluators
		$query = 'SELECT eg.user_id 
					FROM `#__emundus_groups` as eg 
					WHERE eg.group_id='.$ag_id;
		$db->setQuery( $query );
		$users = $db->loadResultArray();

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

		// Evaluations criterias
		$query = 'SELECT id, label, sub_labels
		FROM #__fabrik_elements
		WHERE group_id=41 AND (plugin like "fabrikradiobutton" OR plugin like "fabrikdropdown")';
		$db->setQuery( $query );
		$db->query();
		$eval_criteria=$db->loadObjectList();
		
		$eval = '<ul>';
		foreach($eval_criteria as $e) {
			$eval .= '<li>'.$e->label.' ('.$e->sub_labels.')</li>';
		}
		$eval .= '</ul>';

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[APPLICANTS_LIST\]/', '/\[SITE_URL\]/', '/\[EVAL_CRITERIAS\]/', '/\[EVAL_PERIOD\]/', '/\n/');

		foreach ($users as $uid) {
			$user =& JFactory::getUser($uid);
			
			$query = 'SELECT applicant_id
					  FROM #__emundus_groups_eval
					  WHERE user_id='.$user->id.' OR group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.')';
			$db->setQuery( $query );
			$db->query();
			$applicants=$db->loadResultArray();
			$list = '<ul>';
			
			foreach($applicants as $ap) {
				$app =& JFactory::getUser($ap);
				$list .= '<li>'.$app->name.' ['.$app->id.']</li>';
			}
			$list .= '</ul>';
			
			$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
						FROM #__emundus_setup_profiles AS esp 
						LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
						WHERE user_id='.$user->id;
			$db->setQuery( $query );
			$db->query();
			$period=$db->loadRow();
				
			$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
				
			$replacements = array ($user->id, $user->name, $user->email, $list, JURI::base(), $eval, $period_str, '<br />');
			// template replacements
			$body = preg_replace($patterns, $replacements, $message);
	
			// mail function
			JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1);

			$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
				VALUES ('".$from_id."', '".$user->id."', '".$subject."', '".$body."', NOW())";
			$db->setQuery( $sql );
			$db->query();
			
			unset($replacements);
		}
			
		$this->setRedirect('index.php?option=com_emundus&view=groups&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir, JText::_('ACTION_DONE'), 'message');
	}
	
} //END CLASS
?>