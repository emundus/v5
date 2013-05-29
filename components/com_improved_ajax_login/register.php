<?php
/*-------------------------------------------------------------------------
# com_improved_ajax_login - Improved_AJAX_Login
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
defined('_JEXEC') or die( 'Restricted access' );
$filter =& JFilterInput::getInstance();

$lang = JFactory::getLanguage();
if ($v15) $lang->load('com_user');
else $lang->load('com_users');

$check = JRequest::getVar('check');
ob_clean();
if ($check == 'username') {
  $username = JRequest::getString('value');
  $db->setQuery("SELECT id FROM #__users WHERE username LIKE '$username'");
  if ($db->loadRow()) die($v15? JText::sprintf('WARNNAMETRYAGAIN', JText::_('USERNAME')) : JText::_('COM_USERS_REGISTER_USERNAME_MESSAGE'));
  die('success');
} elseif ($check == 'email') {
  $email = $filter->clean( JRequest::getString('value') );
  $db->setQuery("SELECT id FROM #__users WHERE email LIKE '$email'");
  if ($db->loadRow()) die(JText::_($v15? 'WARNREG_EMAIL_INUSE' : 'COM_USERS_REGISTER_EMAIL1_MESSAGE'));
  die('success');
}

$json = array();

$token = version_compare(JVERSION,'3.0.0','ge')?
  JSession::checkToken() : JRequest::getVar(JUtility::getToken(), '', 'post', 'alnum');
if (!$token) {
  $json['error'] = 1;
  $json['message'] = JText::_($v15? 'INVALID_TOKEN' : 'JINVALID_TOKEN');
}

if (@$_SESSION['reCaptcha']) {
  require dirname(__FILE__).'/lib/recaptchalib.php';
  $resp = recaptcha_check_answer(
    $_SESSION['reCaptcha']['private'],
    $_SERVER["REMOTE_ADDR"],
    $_POST["recaptchaChallenge"],
    $_POST["recaptchaResponse"]
  );
  if ($resp->error) {
    $json['error'] = 2;
    $json['field'] = 'recaptchaResponse';
    if ($v15) $m = preg_replace('/'.JText::_('TOKEN').'/i', JText::_('Captcha'), JText::_('INVALID_TOKEN'));
    else {
      $m = explode(':', JText::_('JLIB_FORM_VALIDATE_FIELD_INVALID'));
      $m = $m[0];
    }
    $json['message'] = $m;
    die(json_encode($json));
  }
}

jimport('joomla.application.component.helper');
jimport('joomla.filter.filterinput');
jimport('joomla.mail.helper');
jimport('joomla.user.helper');

$email = $filter->clean( JRequest::getVar( 'email' ) );
if (!JMailHelper::isEmailAddress( $email )) {
  $json['error'] = 3;
  $json['field'] = 'email';
  $json['message'] = $v15? str_replace(':', '', JText::_('PLEASE ENTER A VALID E-MAIL ADDRESS.')) : JText::_('COM_USERS_INVALID_EMAIL');
  die(json_encode($json));
}

$acl = JFactory::getACL();
$user = JFactory::getUser(0);

$usersParams = JComponentHelper::getParams( 'com_users' );
$usertype = $usersParams->get('new_usertype');

$data = array();

$data['name'] 		= JRequest::getString('name');
$data['email'] 		= JRequest::getString('email');
$data['email1'] 	= JRequest::getString('email2');

$jversion = new JVersion;
if( $jversion->isCompatible( '1.6' ) ) {
	$data['gid']	= $usertype;
} else {
	$data['gid'] 	= $acl->get_group_id( '', $usertype, 'ARO' );
}
$data['sendEmail'] 	= 0;
$data['username'] 	= JRequest::getString('username');

$data['password'] 	= JRequest::getString('passwd');
$data['password1'] 	= JRequest::getString('passwd');
$data['password2'] 	= JRequest::getString('passwd2');

if( $jversion->isCompatible( '1.6' ) ) {

	// Joomla 2.5 - core method takes care of everything
	require_once( JPATH_SITE.DS.'components'.DS.'com_users'.DS.'models'.DS.'registration.php' );
	$model = new UsersModelRegistration();
	$activation = $model->register( $data );

	switch( $activation ) {
		case 'useractivate':
			$message = JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE');
			break;
		case 'adminactivate':
			$message = JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY');
			break;
		default:
			$message = JText::_('COM_USERS_REGISTRATION_ACTIVATE_SUCCESS');
	}
} else {
  $usersConfig = JComponentHelper::getParams( 'com_users' );
  $useractivation = $usersConfig->get( 'useractivation' );
	if ( $useractivation == 1 ) {
    $message  = JText::_( 'REG_COMPLETE_ACTIVATE' );
  	$data['block'] = 1;
  	$data['activation']	= JUtility::getHash( JUserHelper::genRandomPassword());
  } else {
    $message = JText::_( 'REG_COMPLETE' );
    $data['block'] = 0;
  }
	// Joomla 1.5
	if (!$user->bind( $data )) {
		JError::raiseWarning('', JText::_( $user->getError()));
		return false;
	}

	if (!$user->save()) {
		JError::raiseWarning('', JText::_( $user->getError()));
		return false;
	}

	require_once(JPATH_SITE.'/components/com_user/controller.php');
	UserController::_sendMail($user, $password);
}

// offlajn_user registration
$social = array(
  'type' => JRequest::getString('socialType'),
  'id'   => JRequest::getString('socialId'));
if ($social['type'] && $social['id']) {
  $db->setQuery("SELECT id FROM #__users WHERE username = '{$data['username']}'");
  $res = $db->loadRow();
  $db->setQuery("INSERT INTO #__offlajn_users(user_id, {$social['type']}_id) VALUES({$res[0]}, '{$social['id']}')");
  $db->query();
}

if ($_REQUEST['ajax']){
  $mainframe->enqueueMessage($message);
  $mainframe->redirect(JRoute::_('index.php', false));
}
$json['error'] = 0;
$json['message'] = $message;
die(json_encode($json));