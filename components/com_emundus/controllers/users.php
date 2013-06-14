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
			$default = 'users';
			JRequest::setVar('view', $default );
		} 
		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) 
			parent::display();
		else echo JText::_('ACCESS_DENIED');
    }


	function blockuser() {
		$user = JFactory::getUser();
		$Itemid=JSite::getMenu()->getActive()->id;
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$rowid = JRequest::getVar('rowid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		if(!empty($uid) && is_numeric($uid)) {
			if($uid == 62) JError::raiseError(500, JText::_('You cannot delete administrator'));
			
			$this->_db->setQuery('UPDATE #__users SET block = 1 WHERE id = '.mysql_real_escape_string($uid));
			$this->_db->Query();
			$this->_db->setQuery('UPDATE #__emundus_users SET disabled = 1, disabled_date = NOW() WHERE user_id = '.mysql_real_escape_string($uid));
			$this->_db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=users&rowid='.$rowid.'&limitstart='.$limitstart.'&Itemid='.$Itemid, JText::_('User successfully blocked'), 'message');
	}

	function unblockuser() {
		$user = JFactory::getUser();
		$Itemid=JSite::getMenu()->getActive()->id;
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		$uid = JRequest::getVar('uid', null, 'GET', null, 0);
		$rowid = JRequest::getVar('rowid', null, 'GET', null, 0);
		$limitstart = JRequest::getVar('limitstart', null, 'GET', null, 0);
		if(!empty($uid) && is_numeric($uid)) {
			if($uid == 62) JError::raiseError(500, JText::_('You cannot delete administrator'));
			
			$this->_db->setQuery('UPDATE #__users SET block = 0 WHERE id = '.mysql_real_escape_string($uid));
			$this->_db->Query();
			$this->_db->setQuery('UPDATE #__emundus_users SET disabled = 0 WHERE user_id = '.mysql_real_escape_string($uid));
			$this->_db->Query();
		}
		$this->setRedirect('index.php?option=com_emundus&view=users&rowid='.$rowid.'&limitstart='.$limitstart.'&Itemid='.$Itemid, JText::_('User successfully unblocked'), 'message');
	}
	

	function delincomplete() {
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		
		$query = 'SELECT u.id FROM #__users AS u LEFT JOIN #__emundus_declaration AS d ON u.id=d.user WHERE u.usertype = "Registered" AND d.user IS NULL';
		$this->_db->setQuery($query);
		$this->delusers($this->_db->loadResultArray());
	}

	function delrefused() {
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		
		$this->_db->setQuery('SELECT student_id FROM #__emundus_final_grade WHERE Final_grade=2 AND type_grade ="candidature"');
		$users = $this->_db->loadResultArray();
		$this->delusers($this->_db->loadResultArray());
	}
	
	function delnonevaluated() { /* ----------------- */
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		
		$this->_db->setQuery('SELECT u.id FROM #__users AS u LEFT JOIN #__emundus_final_grade AS efg ON u.id=efg.student_id WHERE u.usertype = "Registered" AND efg.student_id IS NULL');
		$users = $this->_db->loadResultArray();
		$this->delusers($this->_db->loadResultArray());
	}
	
	function clear() {
		EmundusHelperFilters::clear();
		
		//$itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
	
	
	function archive() {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$itemid=JSite::getMenu()->getActive()->id;
		
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		
		if(!empty($ids)) {
			foreach ($ids as $id) {				
				$query = 'UPDATE #__emundus_users SET profile=999 WHERE user_id='.$id;
				$this->_db->setQuery($query);
				$this->_db->Query() or die($this->_db->getErrorMsg());
				
				$this->blockuser($id);
			}
		}
		
		$this->setRedirect('index.php?option=com_emundus&view=users&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
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
		
		$time_date = (date('Y-m-d H:i:s'));
		
		$query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$user_id.",'".$name."',".$this->_db->quote($constraints).",".$itemid.")";
		$this->_db->setQuery( $query );
		$result=$this->_db->Query();// or die($this->_db->getErrorMsg());
		// echo $result;
		if($result!=1){
			echo JText::_('SQL_ERROR');
		}else{
			echo JText::_('FILTER_SAVED');
		}
	}
	
	function lastSavedFilter(){
		
		$query="SELECT MAX(id) FROM #__emundus_filters";
		$this->_db->setQuery( $query );
		$result=$this->_db->loadResult();
		echo $result;
	}
	
	function getConstraintsFilter(){
		$filter_id = JRequest::getVar('filter_id', null, 'POST', 'none',0);
		
		$query="SELECT constraints FROM #__emundus_filters WHERE id=".$filter_id;
		// echo $query;
		$this->_db->setQuery( $query );
		$result=$this->_db->loadResult();
		echo $result;
	}
	
	function deletefilters(){
		$filter_id = JRequest::getVar('filter_id', null, 'POST', 'none',0);
		
		$query="DELETE FROM #__emundus_filters WHERE id=".$filter_id;
		// echo $query.'<BR />';
		$this->_db->setQuery( $query );
		$result=$this->_db->Query();// or die($this->_db->getErrorMsg());
		
		if($result!=1){
			echo JText::_('SQL_ERROR');
		}else{
			echo JText::_('FILTER_DELETED');
		}
	}
	
	////// EXPORT SELECTED XLS ///////////////////
	function export_selected_xls(){
	     $cids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		 $page= JRequest::getVar('limitstart',0,'get');
		 if(!empty($cids)){
		 	$this->export_to_xls($cids);
		}else {
			$this->setRedirect("index.php?option=com_emundus&view=users&limitstart=".$page,JText::_("NO_ITEM_SELECTED"),'error');
		}
	}
	
   ////// EXPORT ALL XLS ///////////////////	
	function export_account_to_xls($reqids=array(),$el=array()) {
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_xls'.DS.'xls_users.php');
		export_xls($cid, array()); 
	}
	
	function export_zip() {
		require_once('libraries/emundus/zip.php');
		$db	= JFactory::getDBO();
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
	
} //END CLASS
?>