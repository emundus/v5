<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: referent_letter.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Envoi automatique d'un email aux professeurs référents choisis par l'étudiant
 */
$baseurl = JURI::root();
$student = JFactory::getUser($_REQUEST['jos_emundus_references___user_raw'][0]);
jimport( 'joomla.utilities.utility' );

$db =& JFactory::getDBO();

// Récupération des données du mail
$query = 'SELECT id, subject, emailfrom, name, message
				FROM #__emundus_setup_emails
				WHERE lbl="referent_letter"';
$db->setQuery( $query );
$db->query();
$obj=$db->loadObjectList();

// Récupération de la pièce jointe : modele de lettre
$query = 'SELECT esp.reference_letter
				FROM #__emundus_users as eu 
				LEFT JOIN #__emundus_setup_profiles as esp on esp.id = eu.profile 
				WHERE eu.user_id = '.$student->id;
$db->setQuery( $query );
$db->query();
$obj_letter=$db->loadRowList();

//////////////////////////  SET FILES REQUEST  /////////////////////////////
// 
// Génération de l'id du prochain fichier qui devra être ajouté par le referent

// 1. Génération aléatoire de l'ID
function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789')
{
    $string = '';
    for ($i = 0; $i < $len; $i++)
    {
        $pos = rand(0, strlen($chars)-1);
        $string .= $chars{$pos};
    }
    return $string;
}

// Reference 1 /////////////////////////////////////////////////////////////
$pgitemtitle1 = 4; //ID provenant de la table emundus_attachments
$query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE user_id='.$student->id.' AND attachment_id='.$pgitemtitle1;
$db->setQuery( $query );
$db->query();
$is_uploaded1=$db->loadResult();

if ($is_uploaded1==0) {
	$key1 = md5(rand_string(20).time());
	// 2. MAJ de la table emundus_files_request
	$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id) 
						  VALUES (NOW(), '.$student->id.', "'.$key1.'", "'.$pgitemtitle1.'")';
	$db->setQuery( $query );
	$db->query();
	
	// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
	$link_upload1 = $baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=68&tableid=71&keyid='.$key1.'&sid='.$student->id;
	$link_html1 = '<p>Click <a href="'.$link_upload1.'">HERE</a> to upload reference letter<br><br>';
	$link_html1 .= 'If link does not work, please copy and paste that hyperlink in your browser : <br>'.$link_upload1.'</p>';
} 
// Reference 2 /////////////////////////////////////////////////////////////
$pgitemtitle2 = 6; //ID provenant de la table emundus_attachments
$query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE user_id='.$student->id.' AND attachment_id='.$pgitemtitle2;
$db->setQuery( $query );
$db->query();
$is_uploaded2=$db->loadResult();

if ($is_uploaded2==0) {
	$key2 = md5(rand_string(20).time());
	// 2. MAJ de la table emundus_files_request
	$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id) 
						  VALUES (NOW(), '.$student->id.', "'.$key2.'", "'.$pgitemtitle2.'")';
	$db->setQuery( $query );
	$db->query();
	
	// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
	$link_upload2 = $baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=68&tableid=71&keyid='.$key2.'&sid='.$student->id;
	$link_html2 = '<p>Click <a href="'.$link_upload2.'">HERE</a> to upload reference letter<br><br>';
	$link_html2 .= 'If link does not work, please copy and paste that hyperlink in your browser : <br>'.$link_upload2.'</p>';
}
// Reference 3 /////////////////////////////////////////////////////////////
$pgitemtitle3 = 21; //ID provenant de la table emundus_attachments
$query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE user_id='.$student->id.' AND attachment_id='.$pgitemtitle3;
$db->setQuery( $query );
$db->query();
$is_uploaded3=$db->loadResult();

if ($is_uploaded3==0) {
	$key3 = md5(rand_string(20).time());
	// 2. MAJ de la table emundus_files_request
	$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id) 
						  VALUES (NOW(), '.$student->id.', "'.$key3.'", "'.$pgitemtitle3.'")';
	$db->setQuery( $query );
	$db->query();
	
	// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
	$link_upload3 = $baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=68&tableid=71&keyid='.$key3.'&sid='.$student->id;
	$link_html3 = '<p>Click <a href="'.$link_upload3.'">HERE</a> to upload reference letter<br><br>';
	$link_html3 .= 'If link does not work, please copy and paste that hyperlink in your browser : <br />'.$link_upload3.'</p>';
}
///////////////////////////////////////////////////////

$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[UPLOAD_URL\]/');

// Mail 
$from = $obj[0]->emailfrom;
$fromname =$obj[0]->name;

$from_id = $obj[0]->id;
$recipient[] = $_REQUEST['jos_emundus_references___Email_1'];
$recipient[] = $_REQUEST['jos_emundus_references___Email_2'];
$recipient[] = $_REQUEST['jos_emundus_references___Email_3'];
$subject = $obj[0]->subject;
$mode = 1;
//$cc = $user->email;
//$bcc = $user->email;
$attachment[] = JPATH_BASE.str_replace("\\", "/", $obj_letter[0][0]);
//die(print_r($obj_letter[0][0]));
$replyto = $obj[0]->emailfrom;
$replytoname = $obj[0]->name;

if ($is_uploaded1==0) {
	$replacements = array ($student->id, $student->name, $student->email, $link_upload1);
	$body1 = preg_replace($patterns, $replacements, $obj[0]->message);
	unset($replacements);
	if(JUtility::sendMail($from, $fromname, $recipient[0], $subject, $body1, $mode, null, null, $attachment, $replyto, $replytoname)) {
		$sql = 'INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) VALUES (62, -1, "'.$subject.'", "'.$db->quote($body1).'", NOW())';
		$db->setQuery( $sql );
		$db->query();
	}
}
if ($is_uploaded2==0) {
	$replacements = array ($student->id, $student->name, $student->email, $link_upload2);
	$body2 = preg_replace($patterns, $replacements, $obj[0]->message);
	unset($replacements);
	if(JUtility::sendMail($from, $fromname, $recipient[1], $subject, $body2, $mode, null, null, $attachment, $replyto, $replytoname)){
		$sql = 'INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) VALUES (62, -1, "'.$subject.'", "'.$db->quote($body2).'", NOW())';
		$db->setQuery( $sql );
		$db->query();
	}
}
if ($is_uploaded3==0) {
	$replacements = array ($student->id, $student->name, $student->email, $link_upload3);
	$body3 = preg_replace($patterns, $replacements, $obj[0]->message);
	unset($replacements);
	if(JUtility::sendMail($from, $fromname, $recipient[2], $subject, $body3, $mode, null, null, $attachment, $replyto, $replytoname)){
		$sql = 'INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) VALUES (62, -1, "'.$subject.'", "'.$db->quote($body3).'", NOW())';
		$db->setQuery( $sql );
		$db->query();
	}
}
//JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);

?>