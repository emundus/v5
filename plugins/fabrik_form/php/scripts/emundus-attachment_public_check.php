<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: attachement_public_check.php 89 2012-11-05 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008-2013 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation d'upload par un tier
 */

$jinput 	= JFactory::getApplication()->input;
$key_id 	= $jinput->get->get('keyid');
$sid 		= $jinput->get->get('sid');
$email 		= JRequest::getVar('email', null,'GET');
$campaign_id= JRequest::getVar('cid', null,'GET');
$formid 	= JRequest::getVar('formid', null,'GET');

$baseurl 	= JURI::base();

$db 		=& JFactory::getDBO();

$query = 'SELECT id, attachment_id, filename FROM #__emundus_files_request 
			WHERE keyid ="'.$key_id.'" AND student_id='.$sid.' AND uploaded=0';
$db->setQuery( $query );
$obj=$db->loadObject();

if (isset($obj)) {
	$s = $jinput->get->get('s');
	if ($s != 1) {
		$link_upload = $baseurl.'index.php?option=com_fabrik&view=form&formid='.$formid.'&jos_emundus_uploads___user_id='.$sid.'&jos_emundus_uploads___attachment_id='.$obj->attachment_id.'&sid='.$sid.'&keyid='.$key_id.'&email='.$email.'&cid='.$campaign_id.'&s=1';
		header('Location: '.$link_upload);
		exit();
	} else {
		$up_uid = $jinput->get('jos_emundus_uploads___user_id');
		$up_attachment = $jinput->get('jos_emundus_uploads___attachment_id');
		$student_id = !empty($up_uid)?$jinput->get('jos_emundus_uploads___user_id'):$jinput->get->get('jos_emundus_uploads___user_id');
		$attachment_id = !empty($up_attachment)?$jinput->get('jos_emundus_uploads___attachment_id'):$jinput->get->get('jos_emundus_uploads___attachment_id');
		if (empty($student_id) || empty($key_id) || empty($attachment_id) || $attachment_id != $obj->attachment_id || !is_numeric($sid) || $sid != $student_id) { 
			//print_r($_REQUEST); echo '<hr>'.$attachment_id.' :: '.$student_id;
			$baseurl = JURI::base();
			JError::raiseWarning(500, JText::_('ERROR: please try again','error'));
			header('Location: '.$baseurl);
			exit();
		} 
		$student=JUser::getInstance($sid);
		echo '<h1>'.$student->name.'</h1>';
	}
} else {
	header('Location: '.$baseurl.'index.php?option=com_content&view=article&id=28');
	exit();
}


?>