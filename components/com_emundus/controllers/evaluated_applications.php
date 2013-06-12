<?php
/**
 * @version		$Id: controller.php 750 2008-07-16 22:29:38Z eddieajau $
 * @package		Reports
 * @copyright	(C) 2005 - 2008 JXtended LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );

/**
 * Custom report controller
 * @package		Reports
 */
class EmundusControllerEvaluated_applications extends JController
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
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
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
	
	function delete_eval() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor", "Author");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		
		$sid = JRequest::getVar('sid', null, 'GET', 'int', 0);
		$db	= JFactory::getDBO();
		$query = 'DELETE FROM #__emundus_evaluations WHERE student_id='.$sid.' AND user='.$user->id;
		$db->setQuery($query);
		$db->query();
		
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
	}
}
