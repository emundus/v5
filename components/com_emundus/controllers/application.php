<?php
/**
 * @version		$Id: application.php 750 2012-01-23 22:29:38Z brivalland $
 * @package		Joomla
 * @copyright	(C) 2008 - 2013 eMundus LLC. All rights reserved.
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
	 * Delete an applicant attachment(s)
	 */
	function delete_attachments() {
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));
		
		$db	= JFactory::getDBO();
		$attachments = JRequest::getVar('attachments', null, 'POST', 'array', 0);
		
		$user_id = JRequest::getVar('sid', null, 'POST', 'none', 0);
		
		$view = JRequest::getVar('view', null, 'POST', 'none', 0);
		$tmpl = JRequest::getVar('tmpl', null, 'POST', 'none', 0);

		$url = !empty($tmpl)?'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl='.$tmpl.'#attachments':'index.php?option=com_emundus&view='.$view.'&sid='.$user_id.'&tmpl=component#attachments';
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
			@unlink($file);
			/*if(!@unlink($file) && file_exists($file)) {
				// JError::raiseError(500, JText::_('FILE_NOT_FOUND').$file);
				$this->setRedirect($url, JText::_('FILE_NOT_FOUND'), 'error');
				return;
			}*/
			
			$query = 'DELETE FROM #__emundus_uploads WHERE id='.$id;
			$db->setQuery( $query );
			$db->query();
		}
		
		$this->setRedirect($url, JText::_('ATTACHMENTS_DELETED'), 'message');
		return;
	}

	/**
	 * Delete an applicant attachment (one by one)
	 */
	function delete_attachment() {
		$user = JFactory::getUser();
		
		if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

		$view = JRequest::getVar('view', null, 'GET', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
				
		$id = JRequest::getVar('id', null, 'GET', 'none',0);
		
		$model = $this->getModel('application');
		$result = $model->deleteAttachment($id);
		echo $result;
		if($result != 1){
			echo JText::_('ATTACHMENT_DELETE_ERROR');
		}
	}

	/**
	 * Upload an applicant attachment (one by one)
	 */
	function upload_attachment() {
		$user = JFactory::getUser();

		if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

		$view = JRequest::getVar('view', null, 'GET', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$aid = JRequest::getVar('attachment_id', null, 'POST', 'none',0);
		$uid = JRequest::getVar('uid', null, 'POST', 'none',0);
		$filename = JRequest::getVar('filename', null, 'POST', 'none',0);
		$campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'none',0);
		$can_be_viewed = JRequest::getVar('can_be_viewed', null, 'POST', 'none',0);
		$can_be_deleted = JRequest::getVar('can_be_deleted', null, 'POST', 'none',0);

		$targetFolder = EMUNDUS_PATH_ABS.$uid; 
		
		
		//echo $stringData . $targetFolder . $_FILES['filename']['name'];

		if (!empty($_FILES)) {
			$msg = "";
			$data = "{";
			switch ($_FILES['filename']['error']) {
			case 0:		$msg .= "File uploaded"; 
						$data .= '"message":"'.$msg.'",';
						$tempFile = $_FILES['filename']['tmp_name'];
						$targetPath = $targetFolder;
						
						// Validate the file type
						$fileTypes = array('jpg','jpeg','gif','png', 'pdf', 'doc', 'docx', 'odt'); // File extensions
						$fileParts = pathinfo($_FILES['filename']['name']);
						
						if (in_array($fileParts['extension'], $fileTypes)) {
							$model = $this->getModel('application');
							$type_attachment = $model->getAttachmentByID($aid);
							
							$filename = date('Y-m-d_H-i-s').$type_attachment['lbl'].'_'.$_FILES['filename']['name'];
							$fileURL = EMUNDUS_PATH_REL.$uid.'/'.$filename;
							$targetFile = rtrim($targetPath,'/') . DS . $filename;

							move_uploaded_file($tempFile, $targetFile);

							$filesize = $_FILES['filename']['size'];

							$attachment["key"] = array("user_id", "attachment_id", "filename", "description", "can_be_deleted", "can_be_viewed", "campaign_id");
							$attachment["value"] = array($uid, $aid, '"'.$filename.'"', '"'.date('Y-m-d H:i:s').'"', $can_be_deleted, $can_be_viewed, $campaign_id);
							
							$id = $model->uploadAttachment($attachment);
						} else {
							$msg .= JText::_('COM_EMUNDUS_FILETYPE_INVALIDE');
						}
						
						$data .= '"message":"'.$msg.'",';
						$data .= '"url":"'.htmlentities($fileURL).'",';
						$data .= '"id":"'.$id.'",';
						$data .= '"filesize":"'.$filesize.'",';
						$data .= '"name":"'.$type_attachment['value'].'",';
						$data .= '"filename":"'.$filename.'",';
						$data .= '"path":"'.str_replace("\\", "\\\\", $targetPath).'",';
						$data .= '"aid":"'.$aid.'",';
						$data .= '"uid":"'.$uid.'"';
						//$data .= '"html":"'.$html.'"';
						
						
				break;
			case 1:		$msg .= "The file is bigger than this PHP installation allows";
						$data .= '"message":"'.$msg.'"';
				break;
			case 2:		$msg .= "The file is bigger than this form allows";
						$data .= '"message":"'.$msg.'"';
				break;
			case 3:		$msg .= "Only part of the file was uploaded";
						$data .= '"message":"'.$msg.'"';
				break;
			case 4:		$msg .= "No file was uploaded";
						$data .= '"message":"'.$msg.'"';
				break;
			case 6:		$msg .= "Missing a temporary folder";
						$data .= '"message":"'.$msg.'"';
				break;
			case 7:		$msg .= "Failed to write file to disk";
						$data .= '"message":"'.$msg.'"';
				break;
			case 8:		$msg .= "File upload stopped by extension";
						$data .= '"message":"'.$msg.'"';
				break;
			default:	$msg .= "Unknown error ".$_FILES['filename']['error'];
						$data .= '"message":"'.$msg.'",';
						$data .= '"message":"'.$html.'"';
				break;
			}
			$data .= "}";
			echo $data;
			//echo json_encode($data);
		}
	}
	
	
	function deletecomment(){
		$user = JFactory::getUser();

		if(!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) die(JText::_("ACCESS_DENIED"));

		$view = JRequest::getVar('view', null, 'GET', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
				
		$comment_id = JRequest::getVar('comment_id', null, 'GET', 'none',0);
		
		$model = $this->getModel('application');
		$result = $model->deleteComment($comment_id);
				
		if($result!=1){
			echo JText::_('COMMENT_DELETE_ERROR');
		}
	}
}
