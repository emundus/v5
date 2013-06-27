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
class EmundusControllerApplication extends JController
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
		$db	= JFactory::getDBO();
		$cid = JRequest::getVar('uid', null, 'POST', 'array', 0);
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
	function delete_attachments() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));
		
		$db	= JFactory::getDBO();
		$attachments = JRequest::getVar('attachments', null, 'POST', 'array', 0);
		
		$user_id = JRequest::getVar('sid', null, 'POST', 'none', 0);
		
		$view = JRequest::getVar('view', null, 'POST', 'none', 0);

		$url = !empty($tmpl)?'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl='.$tmpl.'#attachments':'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'#attachments';
		// die(var_dump($attachments));
		JArrayHelper::toInteger( $attachments, 0 );
		if (count( $attachments ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$mainframe->redirect($url);
			exit;
		} 
		foreach ($attachments as $id) {
			$query = 'SELECT filename FROM #__emundus_uploads WHERE id='.$id;
			$db->setQuery( $query );
			$filename = $db->loadResult();
			
			$file = EMUNDUS_PATH_ABS.$user_id.DS.$filename;
			if(!@unlink($file) && file_exists($file)) {
				// JError::raiseError(500, JText::_('FILE_NOT_FOUND').$file);
				$this->setRedirect($url, JText::_('FILE_NOT_FOUND'), 'error');
				return;
			}

			$query = 'DELETE FROM #__emundus_uploads WHERE id='.$id;
			$db->setQuery( $query );
			$db->query();
		}
		
		$this->setRedirect($url, JText::_('ATTACHMENTS_DELETED'), 'message');
		return;
	}
	
	
	function deletecomment(){
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$view = JRequest::getVar('view', null, 'GET', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
				
		$comment_id = JRequest::getVar('comment_id', null, 'GET', 'none',0);
		
		$model = $this->getModel('application');
		$result = $model->deleteComment($comment_id);
				
		if($result!=1){
			echo JText::_('SQL_ERROR');
		}
	}
}
