<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: confirm_post.php 89 2013-06-08 Benjamin Rivalland
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
/*$query = 'SELECT id, subject, emailfrom, name, message FROM #__emundus_setup_emails WHERE lbl="confirm_post"';
$db->setQuery( $query );
$db->query();
$obj=$db->loadObject();

$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[DEADLINE\]/','/\n/');
$replacements = array ($student->id, $student->name, $student->email, strftime("%A %d %B %Y %H:%M", strtotime($student->candidature_end) ).' (GMT)', '<br />');
*/
$student = & JFactory::getUser();
include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/groups.php');

$emails = new EmundusModelEmails;

$post = array(  'DEADLINE' => strftime("%A %d %B %Y %H:%M", strtotime($student->candidature_end)),
				'APPLICANTS_LIST' => $applicants,
				'EVAL_CRITERIAS' => $criterias,
				'EVAL_PERIOD' => $eval_period 
			);
$tags = $emails->setTags($student->id, array());
$email = $emails->getEmail("confirm_post");

// Apllicant cannot delete this attachments now
$query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id;
$db->setQuery( $query );
try {
	$db->Query();
} catch (Exception $e) {
	// catch any database errors.
}

// Confirm candidature
// Insert data in #__emundus_campaign_candidature
$query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW() WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id;
$db->setQuery($query);
try {
	$db->Query();
} catch (Exception $e) {
	// catch any database errors.
}

$query = 'UPDATE #__emundus_declaration SET time_date=NOW() WHERE user='.$student->id;
$db->setQuery($query);
try {
	$db->Query();
} catch (Exception $e) {
	// catch any database errors.
}

// Mail 
$from = $email->emailfrom;
$from_id = 62;
$fromname =$email->name;
$recipient[] = $student->email;
$subject = $email->subject;
//$body = preg_replace($patterns, $replacements, $email->message);
$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 
$mode = 1;

//$attachment[] = $path_file;
$replyto = $email->emailfrom;
$replytoname = $email->name;

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

unset($recipient);

// get current applicant course
$campaigns = new EmundusModelCampaign;
$campaign = $campaigns->getCampaignByID($student->campaign_id);

// get evaluators groups for current applicant course
$groups = new EmundusModelGroups;
$group_list = $groups->getGroupsIdByCourse($campaign['training']);

// Link groups to current application
$groups->affectEvaluatorsGroups($group_list, $student->id);

// Alert by email evaluators
// get evaluator list
$evaluators = $groups->getUsersByGroups($group_list);

$email = $emails->getEmail("new_applicant");
foreach ($evaluators as $evaluator) {
	$eval_user = & JFactory::getUser($evaluator);
	$tags = $emails->setTags($eval_user->id, $post);
	// Mail 
	$from = $email->emailfrom;
	$from_id = 62;
	$fromname =$email->name;
	$recipient[] = $eval_user->email;
	$subject = $email->subject;
	//$body = preg_replace($patterns, $replacements, $email->message);
	$body = preg_replace($tags['patterns'], $tags['replacements'], $email->message); 
	$mode = 1;

	//$attachment[] = $path_file;
	$replyto = $email->emailfrom;
	$replytoname = $email->name;

	$student->candidature_posted = 1;
	$res = JUtility::sendMail( $from, $fromname, $recipient, $subject, $body, true );
	$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$eval_user->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
	$db->setQuery( $sql );
	try {
		$db->Query();
	} catch (Exception $e) {
		// catch any database errors.
	}
}
?>