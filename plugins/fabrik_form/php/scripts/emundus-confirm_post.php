<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: confirm_post.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email à l'étudiant lors de la validation de son dossier de candidature
 */

$db =& JFactory::getDBO();
$query = 'SELECT id, subject, emailfrom, name, message FROM #__emundus_setup_emails WHERE lbl="confirm_post"';
$db->setQuery( $query );
$db->query();
$obj=$db->loadObject();

$student = & JFactory::getUser();

$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[DEADLINE\]/','/\n/');
$replacements = array ($student->id, $student->name, $student->email, strftime("%A %d %B %Y %H:%M", strtotime($student->candidature_end) ).' (GMT)', '<br />');

//cannot delete this attachments now
$query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id;
$db->setQuery( $query );
try {
	$db->Query();
} catch (Exception $e) {
	// catch any database errors.
}

// Confirm candidature
// Insert data in #__emundus_campaign_candidature
$db->setQuery('UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW() WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id);
try {
	$db->Query();
} catch (Exception $e) {
	// catch any database errors.
}

// Mail 
$from = $obj->emailfrom;
$from_id = 62;
$fromname =$obj->name;
$recipient[] = $student->email;
$subject = $obj->subject;
$body = preg_replace($patterns, $replacements, $obj->message);
$mode = 1;

$attachment[] = $path_file;
$replyto = $obj->emailfrom;
$replytoname = $obj->name;

$student->candidature_posted = 1;
$res = JUtility::sendMail( $from, $fromname, $recipient, $subject, $body, true );
$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
				VALUES ('".$from_id."', '".$student->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
$db->setQuery( $sql );
try {
	$db->Query();
} catch (Exception $e) {
	// catch any database errors.
}
?>