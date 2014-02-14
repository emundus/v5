<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement_public.php 89 2014-02-04 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description gestion du document Confidentiality agreement et création automatique d'un compte d'accès au profil Expert pour l'évaluation. 
 */
/*$this->formModel->_arErrors['jos_emundus_uploads___filename'][] = 'woops!';
return false;
*/

$mainframe 	= JFactory::getApplication();
$jinput 	= $mainframe->input;
$baseurl 	= JURI::base();
$db 		= JFactory::getDBO();
$files 		= JRequest::get('FILES');
$key_id 	= JRequest::getVar('keyid', null,'get');
$user_id 	= $jinput->get('jos_emundus_uploads___user_id');
$sid 		= JRequest::getVar('sid', null,'GET');
$cid 		= JRequest::getVar('cid', null,'GET');
$email 		= JRequest::getVar('email', null,'GET');
$attachment_id = $jinput->get('jos_emundus_uploads___attachment_id');

$eMConfig = JComponentHelper::getParams('com_emundus');
$formid = $eMConfig->get('expert_fabrikformid', '110');

include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php'); 
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php'); 
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

$m_users = new EmundusModelUsers;
$m_emails = new EmundusModelEmails;
$m_application = new EmundusModelApplication;

//$params =& $this->getParams();

if (empty($email) || !isset($email)) {
	die("NO_EMAIL_FOUND");
}

$db->setQuery('SELECT student_id, attachment_id, keyid FROM #__emundus_files_request WHERE keyid="'.mysql_real_escape_string($key_id).'"');
$file_request=$db->loadObject();

if($files['jos_emundus_uploads___filename']['size'] == 0){
		$link_upload = $baseurl.'index.php?option=com_fabrik&view=form&formid='.$formid.'&jos_emundus_uploads___user_id[value]='.$sid.'&jos_emundus_uploads___attachment_id[value]='.$file_request->attachment_id.'&sid='.$sid.'&keyid='.$key_id.'&cid='.$campaign_id.'&email='.$email;
		if($files['jos_emundus_uploads___filename']['error'] == 4)
			JError::raiseWarning(500, JText::_('WARNING: No file selected, please select a file','error')); // no file
		else
			JError::raiseWarning(500, JText::_('WARNING: You just upload an empty file, please check out your file','error')); // file empty
		$mainframe->redirect($link_upload);
		exit();
}


if($user_id != $file_request->student_id || $attachment_id != $file_request->attachment_id) {
	// die('data1:'.$file_request->student_id.'-'.$user_id.'-'.$file_request->attachment_id.'-'.$attachment_id.'-'.$key_id.'-'.$db->getErrorMsg());
	header('Location: '.$baseurl.'index.php');
	exit();
}

$student = &JUser::getInstance($user_id);


if(!isset($student)) {
	// die('data2:'.$key_id.'-'.$user_id.'-'.$attachment_id);
	header('Location: '.$baseurl.'index.php');
	exit();
}

$query = 'SELECT profile FROM #__emundus_users WHERE user_id='.$user_id.'';
$db->setQuery( $query );
$profile=$db->loadResult();
//die('data2:'.$profile.'-'.print_r($attachement_params, true).'-'.print_r($upload, true).'-'.$db->getErrorMsg());

// 1. Récupération des informations sur l'étudiant et le fichier qui doit être chargé par la tierce personne
$query = 'SELECT ap.displayed, attachment.lbl 
			FROM #__emundus_setup_attachments AS attachment
			LEFT JOIN #__emundus_setup_attachment_profiles AS ap ON attachment.id = ap.attachment_id AND ap.profile_id='.$profile.'
			WHERE attachment.id ='.$attachment_id.' ';
$db->setQuery( $query );
$attachement_params=$db->loadObject();
//die('data3:'.$attachement_params->displayed.'-'.$attachement_params->lbl.'-'.$attachment_id.'-'.$profile.'-'.$db->getErrorMsg().'-'.$db->getQuery());
// 2. Récupération des données du fichier qui vient d'être uplodé par la tierce personne
$query = 'SELECT id, filename FROM #__emundus_uploads WHERE attachment_id='.$attachment_id.' AND user_id='.$user_id.' ORDER BY id DESC';
$db->setQuery( $query );
$upload=$db->loadObject();
$nom = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($student->name,ENT_NOQUOTES,'UTF-8'))));
if(!isset($attachement_params->displayed) || $attachement_params->displayed === '0') $nom.= "_locked";
$nom .= $attachement_params->lbl.rand().'.'.end(explode('.', $upload->filename));

if(!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
	if (!mkdir(EMUNDUS_PATH_ABS.$user_id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user_id.DS.'index.html')) 
			die(JError::raiseWarning(500, 'Unable to create user file'));
}

// 1. Chargement du document Confidentiality agreement
if (!rename(JPATH_SITE.$upload->filename, EMUNDUS_PATH_ABS.$user_id.DS.$nom))
	die("ERROR_MOVING_UPLOAD_FILE");

$db->setQuery('UPDATE #__emundus_uploads SET filename="'.$nom.'" WHERE id='.$upload->id);
$db->query();
$query = 'UPDATE #__emundus_files_request SET uploaded=1, filename="'.$nom.'" WHERE keyid="'.$key_id.'"';
$db->setQuery( $query );
//$db->Query();///////////////////////////////////////////
// *********** 

// 2. Vérification de l'existance d'un compte utilisateur avec email de l'expert
$query = "SELECT id FROM #__users WHERE email like ".$db->Quote($email);
$db->setQuery( $query );
$uid = $db->loadResult();

$query = 'SELECT id FROM #__emundus_setup_profiles WHERE is_evaluator=1';
$db->setQuery( $query );
$profile=$db->loadResult();

$acl_aro_groups = $m_users->getDefaultGroup($profile);

if ($uid > 0) {
// 2.0. Si oui : Récupération du user->id du compte existant + Action #2.1.1
	$user = JFactory::getUser($uid);

// 2.0.1 Si Expert déjà déclaré comme candidat : 
	$query = 'SELECT count(id) FROM #__emundus_users_profiles WHERE user_id='.$user->id.' AND profile_id='.$profile;
	$db->setQuery( $query );
	$is_evaluator=$db->loadResult();
	// Ajout d'un nouveau profil dans #__emundus_users_profiles + #__emundus_users_profiles_history
	if ($is_evaluator == 0) {
		$query = "INSERT INTO #__emundus_users_profiles (user_id, profile_id) VALUES (".$user->id.", ".$profile.")";
		$db->setQuery( $query );
		$db->Query();

		$query = "INSERT INTO #__emundus_users_profiles_history (user_id, profile_id, var) VALUES (".$user->id.", ".$profile.", 'profile')";
		$db->setQuery( $query );
		$db->Query();
	
	// Modification du profil courant en profil Expert
		$user->groups=$acl_aro_groups;

		$usertype = $m_users->found_usertype($acl_aro_groups[0]);
		$user->usertype=$usertype;

		if ( !$user->save() ) {
		 	JFactory::getApplication()->enqueueMessage(JText::_('CAN_NOT_SAVE_USER').'<BR />'.$user->getError(), 'error');
		}

		$query = "UPDATE #__emundus_users SET profile=".$profile." WHERE user_id=".$user->id;
		$db->setQuery( $query );
		$db->Query();
	}
	

// 2.0.1 Si Expert déjà déclaré comme expert
	// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval) 
	$query = "INSERT INTO `#__emundus_groups_eval` (`applicant_id`, `user_id`, `campaign_id`) VALUES (".$student->id.", ".$user->id.", ".$cid.")";
	$db->setQuery( $query );
	$db->query();

// 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
	$email = $m_emails->getEmail('expert_accept');
	$body = $m_emails->setBody($user, $email->message, "");

	JUtility::sendMail($email->emailfrom, $email->name, $user->email, $email->subject, $body, 1);

	$message = array(
		'user_id_from' => 62, 
		'user_id_to' => $user->id, 
		'subject' => $email->subject, 
		'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
		);
	$m_emails->logEmail($message);

// 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
	$row = array(
	'applicant_id' => $student->id, 
	'user_id' => 62, 
	'reason' => JText::_( 'EXPERT_ACCEPT_TO_EVALUATE' ), 
	'comment_body' => $user->name. ' ' .JText::_( 'ACCEPT_TO_EVALUATE' )
	);
	$m_application->addComment($row);
	$logged = $m_users->encryptLogin( array('username' => $user->username, 'password' => $user->password) );

} else {
// 2.1. Si non : Création d'un compte utilisateur avec profil Expert
	$query = "SELECT * FROM #__jcrm_contacts WHERE email like ".$db->Quote($email);
	$db->setQuery( $query );
	$expert = $db->loadAssoc();

	

	if(count($expert)>0) {
		$name = $expert['first_name'].' '.$expert['last_name'];
		$firstname = $expert['first_name'];
		$lastname = $expert['last_name'];
	} else {
		$name = $email;
		$firstname = "";
		$lastname = "";
	}

	$password 			= JUserHelper::genRandomPassword();
	$user 				= clone(JFactory::getUser(0));
	$user->name 		= $name;
	$user->username 	= $email;
	$user->email 		= $email;
	$user->password 	= md5($password);
	$user->registerDate	= date('Y-m-d H:i:s');
	$user->lastvisitDate= "0000-00-00-00:00:00";
	$user->block 		= 0;
	
	$other_param['firstname']=$firstname;
	$other_param['lastname']=$lastname;
	$other_param['profile']=$profile;
	$other_param['univ_id']="";
	$other_param['groups']="";
	
	$user->groups=$acl_aro_groups;

	$usertype = $m_users->found_usertype($acl_aro_groups[0]);
	$user->usertype=$usertype;
		
	$uid = $m_users->adduser($user, $other_param);

	if (empty($uid) || !isset($uid) || (!mkdir(EMUNDUS_PATH_ABS.$user->id.DS, 0777, true) && !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html'))) {
		return JError::raiseWarning(500, 'ERROR_CANNOT_CREATE_USER_FILE');
		header('Location: '.$baseurl);
		exit();
	}

// 2.1.1. Association de l'ID user Expert avec le candidat (#__emundus_groups_eval) 
	$query = "INSERT INTO `#__emundus_groups_eval` (`applicant_id`, `user_id`, `campaign_id`) VALUES (".$student->id.", ".$uid.", ".$cid.")";
	$db->setQuery( $query );
	$db->query();

// 2.1.2. Envoie des identifiants à l'expert + Envoie d'un message d'invitation à se connecter pour evaluer le dossier
	$email = $m_emails->getEmail('new_account');
	$body = $m_emails->setBody($user, $email->message, $password);

	JUtility::sendMail($email->emailfrom, $email->name, $user->email, $email->subject, $body, 1);

	$message = array(
		'user_id_from' => 62, 
		'user_id_to' => $uid, 
		'subject' => $email->subject, 
		'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
		);
	$m_emails->logEmail($message);

// 2.1.3. Commentaire sur le dossier du candidat : nouvel expert ayant accepté l'évaluation du dossier
	$row = array(
	'applicant_id' => $student->id, 
	'user_id' => 62, 
	'reason' => JText::_( 'EXPERT_ACCEPT_TO_EVALUATE' ), 
	'comment_body' => $user->name. ' ' .JText::_( 'ACCEPT_TO_EVALUATE' )
	);
	$m_application->addComment($row);

	/*$credentials['username'] = $user->username;
    $credentials['password'] = $password;*/
    $logged = $m_users->plainLogin( array('username' => $user->username, 'password' => $password) );
	JFactory::getApplication()->enqueueMessage(JText::_('USER_LOGGED'), 'message');
}
	

	JFactory::getApplication()->enqueueMessage(JText::_('PLEASE_LOGIN'), 'message');
	header('Location: '.$baseurl.'index.php?option=com_users&view=login');
	exit();
?>