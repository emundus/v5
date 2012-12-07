<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement_public_check.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation d'upload par un tier
 */
$key_id=JRequest::getVar('keyid', null,'get');
$sid=JRequest::getVar('sid', null,'get');

$baseurl = JURI::base();

$db =& JFactory::getDBO();
$query = 'SELECT id, attachment_id, filename FROM #__emundus_files_request 
			WHERE keyid ="'.$key_id.'" AND student_id='.$sid.' AND uploaded=0';
$db->setQuery( $query );
$obj=$db->loadObject();

if (isset($obj)) {
	$s=JRequest::getVar('s', null,'get');
	if ($s != 1) {
		$link_upload = $baseurl.'index.php?option=com_fabrik&c=form&view=form&fabrik=68&tableid=71&rowid=&jos_emundus_uploads___user_id[value]='.$sid.'&jos_emundus_uploads___attachment_id[value]='.$obj->attachment_id.'&sid='.$sid.'&keyid='.$key_id.'&s=1';
		header('Location: '.$link_upload);
		exit();
	} else {
		$student_id=JRequest::getVar('jos_emundus_uploads___user_id', null,'get');
		$attachment_id=JRequest::getVar('jos_emundus_uploads___attachment_id', null,'get');
		if (empty($student_id) || empty($key_id) || empty($attachment_id) || $attachment_id['value']!=$obj->attachment_id || !is_numeric($sid) || $sid!=$student_id['value']) {
			$baseurl = JURI::base();
			header('Location: '.$baseurl);
			exit();
		} 
		$student=JUser::getInstance($sid);
		echo '<h1>Student : '.$student->name.'</h1>';
	}
} else {
	header('Location: '.$baseurl.'index.php?option=com_content&view=article&id=28');
	exit();
}


?>