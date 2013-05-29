<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php
defined('_JEXEC') or die( 'Restricted access' );

if ($v15) {
  $lang = JFactory::getLanguage();
  $lang->load('com_user');
} 

function errorResp($err, $msg) {
  if ($_REQUEST['ajax']) JFactory::getApplication()->redirect(JRoute::_('index.php', false));
  $json = array();
  $json['error'] = $err;
  $json['errorMsg'] = JText::_($msg);
  ob_clean();
  die(json_encode($json));
}

// ERROR 1 - check token
$token = $v30? JSession::checkToken() : JRequest::getVar(JUtility::getToken(), '', 'post', 'alnum');
if (!$token) errorResp(1, $v15? 'INVALID_TOKEN' : 'JINVALID_TOKEN');

$email = 0;
$options = array();
$options['remember'] = JRequest::getBool('remember', false, 'method');
$options['return'] = (@$_SESSION['ologin']['https']?'https://':'http://').$_SERVER['HTTP_HOST'].base64_decode(JRequest::getString('return'));
$credentials = array();
$credentials['username'] = JRequest::getVar('username', '', 'method', 'username');

if (!$credentials['username']) $email = JRequest::getVar('email', '', 'method', 'email');
elseif (preg_match('/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,3}$/', $credentials['username'])) $email = $credentials['username'];

if ($email) {
  $db->setQuery('SELECT username FROM #__users WHERE email='.$db->Quote($email));
	$result = $db->loadObject();
  $credentials['username'] = $result->username;
}
$credentials['password'] = JRequest::getString('passwd', '', 'method', JREQUEST_ALLOWRAW);

//preform the login action
if ($mainframe->login($credentials, $options) === false) {
  if ($error = JError::getError()) errorResp(2, $error->toString());
  errorResp(2, 'JGLOBAL_AUTH_INVALID_PASS');
}

if ($v15 && ($error = JError::getError())) errorResp(2, $error->toString());

// SUCCESS
if ($_REQUEST['ajax']) $mainframe->redirect($options['return']);
else {
  ob_clean();
  die($options['return']);
}