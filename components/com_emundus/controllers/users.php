<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 
/**
 * users Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      2.0.0
 */
class EmundusControllerUsers extends JController {
	
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'users';
			JRequest::setVar('view', $default );
		}
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			parent::display();
		}
    }

	function _blockuser($uid) {
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only administrator can access this function.'), 'error');
			return;
		}
		//$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$rowid = JRequest::getVar('rowid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		if(!empty($uid) && is_numeric($uid)) {
			if($uid == 62) JError::raiseError(500, JText::_('You cannot delete administrator'));
			$db =& JFactory::getDBO();
			$db->setQuery('UPDATE #__users SET block = 1 WHERE id = '.mysql_real_escape_string($uid));
			$db->Query();
			$db->setQuery('UPDATE #__emundus_users SET disabled = 1, disabled_date = NOW() WHERE user_id = '.mysql_real_escape_string($uid));
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=users&rowid='.$rowid.'&limitstart='.$limitstart.'&Itemid='.$itemid, JText::_('ACTION_DONE'), 'message');
	}
	
	function _unblockuser($uid) {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		$user =& JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only administrator can access this function.'), 'error');
			return;
		}
		//$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$rowid = JRequest::getVar('rowid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		if(!empty($uid) && is_numeric($uid)) {
			if($uid == 62) JError::raiseError(500, JText::_('You cannot delete administrator'));
			$db =& JFactory::getDBO();
			$db->setQuery('UPDATE #__users SET block = 0 WHERE id = '.mysql_real_escape_string($uid));
			$db->Query();
			$db->setQuery('UPDATE #__emundus_users SET disabled = 0 WHERE user_id = '.mysql_real_escape_string($uid));
			$db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=users&rowid='.$rowid.'&limitstart='.$limitstart.'&Itemid='.$itemid, JText::_('ACTION_DONE'), 'message');
	}
	
	function clear() {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		unset($_SESSION['s_elements']);
		unset($_SESSION['s_elements_values']);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$Itemid = JRequest::getVar('Itemid', null, 'POST', null, 0);
		$this->setRedirect('index.php?option=com_emundus&view=users&Itemid='.$Itemid.'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	
	function setSchoolyear(){
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		$mainframe =& JFactory::getApplication();
		$user =& JFactory::getUser();
		if($user->profile <= 2) {
			$url = JRequest::getVar('url', null, 'POST', 'none',0);
			$db =& JFactory::getDBO();
			$schoolyear = JRequest::getVar('schoolyear', null, 'POST', 'none',0);
			$query = 'UPDATE #__emundus_setup_profiles 
					SET schoolyear="'.$schoolyear.'" 
					WHERE published=1';
			$db->setQuery( $query );
			$db->query();
			//die ($query);
			$this->setRedirect($url);
		}
		echo $itemid;
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd('view').'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	
	function archive() {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		$db =& JFactory::getDBO();
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		
		if(!empty($ids)) {
			foreach ($ids as $id) {				
				$query = 'UPDATE #__emundus_users SET profile=999 WHERE user_id='.$id;
				$db->setQuery($query);
				$db->Query() or die($db->getErrorMsg());
				
				$this->_blockuser($id);
			}
		}
		
		$this->setRedirect('index.php?option=com_emundus&view=users&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}

	////// Export selected application form to XLS ///////////////////
	function export_selected_to_xls () {
		
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$this->export_to_xls ($cid);
	}
	
	////// EXPORT XLS ///////////////////
	function export_to_xls($reqids = null) {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		$mainframe =& JFactory::getApplication();
		require_once('libraries/emundus/excel.php');
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$syear = JRequest::getVar('schoolyear', null, 'POST', null, 0);
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator and Administrator can access this function.'), 'error');
			return;
		}
		if ($reqids) {
			current_campaign($reqids);
		} else {
		$query = 'SELECT count(id)
				FROM #__emundus_users 
				WHERE schoolyear like "%'.$syear.'%"';
		$db->setQuery( $query );
		$cpt = $db->loadResult();
		
			if($cpt == 0) {
				$this->setRedirect('index.php?option=com_emundus&view=users&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('NO_RESULT').'<br />'.sprintf(JText::_('NO_APPLICANT_FOR_THIS_CAMPAIGN'),$syear), 'notice');
			} else {
				current_campaign();
				$this->setRedirect('index.php?option=com_emundus&view=users&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
			}
		}
	}
	
	
	function savefilters(){
		$constraints = JRequest::getVar('constraints', null, 'POST', 'none',0);
		$name = JRequest::getVar('name', null, 'POST', 'none',0);
		$current_user = JFactory::getUser();
		$user_id = $current_user->id;
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		if(empty($itemid)){
			$itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
		}
		$db =& JFactory::getDBO();
		$time_date = (date('Y-m-d H:i:s'));
		
		$query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$user_id.",'".$name."',".$db->quote($constraints).",".$itemid.")";
		$db->setQuery( $query );
		$result=$db->Query();// or die($db->getErrorMsg());
		// echo $result;
		if($result!=1){
			echo JText::_('SQL_ERROR');
		}else{
			echo JText::_('FILTER_SAVED');
		}
	}
	
	function lastSavedFilter(){
		$db =& JFactory::getDBO();
		$query="SELECT MAX(id) FROM #__emundus_filters";
		$db->setQuery( $query );
		$result=$db->loadResult();
		echo $result;
	}
	
	function getConstraintsFilter(){
		$filter_id = JRequest::getVar('filter_id', null, 'POST', 'none',0);
		$db =& JFactory::getDBO();
		$query="SELECT constraints FROM #__emundus_filters WHERE id=".$filter_id;
		// echo $query;
		$db->setQuery( $query );
		$result=$db->loadResult();
		echo $result;
	}
	
	function deletefilters(){
		$filter_id = JRequest::getVar('filter_id', null, 'POST', 'none',0);
		$db =& JFactory::getDBO();
		$query="DELETE FROM #__emundus_filters WHERE id=".$filter_id;
		// echo $query.'<BR />';
		$db->setQuery( $query );
		$result=$db->Query();// or die($db->getErrorMsg());
		
		if($result!=1){
			echo JText::_('SQL_ERROR');
		}else{
			echo JText::_('FILTER_DELETED');
		}
	}
	
} //END CLASS
?>