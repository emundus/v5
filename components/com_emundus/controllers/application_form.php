<?php
/**
 * @version		$Id: application_form.php 750 2012-01-23 22:29:38Z brivalland $
 * @package		Joomla
 * @copyright	(C) 2005 - 2008 JXtended LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
/**
 * Custom report controller
 * @package		Emundus
 */
class EmundusControllerApplication_form extends JController
{
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'application_form';
			JRequest::setVar('view', $default );
		}
		parent::display();
	    }
	
	/**
	 * export ZIP
	 */
	 
	function export_zip() {
		require_once('libraries/emundus/zip.php');
		$db	= &JFactory::getDBO();
		$cid = JRequest::getVar('cid', null, 'POST', 'array', 0);
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
	 * Delete an applicant attachment
	 */
	function delete_attachment() {
		$mainframe =& JFactory::getApplication();
		$user =& JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) die("You are not allowed to access to this action.");
		
		$mainframe =& JFactory::getApplication();
		$db	= &JFactory::getDBO();
		//$aid = JRequest::getVar( 'aid', array(), 'post', 'array' );
		$aid = JRequest::getVar('aid', null, 'POST', 'array', 0);

		$sid = JRequest::getVar( 'sid', null, 'post', '' );
		$tmpl = JRequest::getVar( 'tmpl', null, 'post', '' );

		$url = !empty($tmpl)?'index.php?option=com_emundus&view=application_form&sid='.$sid.'&tmpl='.$tmpl.'#attachments':'index.php?option=com_emundus&view=application_form&sid='.$sid.'#attachments';
		
		JArrayHelper::toInteger( $aid, 0 );
		if (count( $aid ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$mainframe->redirect($url);
			exit;
		} 
		foreach ($aid as $id) {
			$query = 'SELECT filename FROM #__emundus_uploads WHERE id='.$id;
			$db->setQuery( $query );
			$filename = $db->loadResult();
			
			$file = EMUNDUS_PATH_ABS.$sid.DS.$filename;
			if(!@unlink($file) && file_exists($file)) {
				JError::raiseError(500, JText::_('FILE_NOT_FOUND').$file);
				$mainframe->redirect($url);
				exit;
			}

			$query = 'DELETE FROM #__emundus_uploads WHERE id='.$id;
			$db->setQuery( $query );
			$db->query();
		}
		$mainframe->redirect($url);
		exit;
	}
	
	function set_comment(){
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();
		//$allowed = array("Super Users", "Administrator", "Editor");
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		
		$comment = JRequest::getVar('comment', null, 'GET', 'none',0);
		$id = JRequest::getVar('uid', null, 'GET', 'none',0);
		if(!empty($comment)){
			$query = 'INSERT INTO `#__emundus_comments` (applicant_id,user_id,reason,date,comment) 
							VALUES('.$id.','.$user->id.',"Additional comments","'.date("Y.m.d H:i:s").'","'.$comment.'")';
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
			echo JText::_('SAVED');	
		}
	}
	
	function delete_comment(){
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();
		//$allowed = array("Super Users", "Administrator", "Editor");
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator can access this function.'), 'error');
			return;
		}
		
		$comment = JRequest::getVar('comment_id', null, 'GET', 'none',0);
		$user_id = JRequest::getVar('uid', null, 'GET', 'none',0);
		if($user->id == $user_id){
			$query = 'DELETE FROM #__emundus_comments 
					WHERE id = '.$comment;
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
                        echo JText::_('DELETE_COM_OK');
		}else{
			echo JText::_('DELETE_NOT_ALLOWED');
		}
	}
	
	function get_comment(){
            $user =& JFactory::getUser();
            $model = $this->getModel('application_form');
            $comments = $model->getComments();
            //$allowed = array("Super Users", "Administrator", "Editor");
            if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
                    die("You are not allowed to access to this action.");
            }
            echo $comments;
	}
}
