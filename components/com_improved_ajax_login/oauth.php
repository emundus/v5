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
require_once(dirname(__FILE__).'/lib/OfflajnOAuth.php');

$user = $newuser = 0;
$data = $_SESSION['oauth'][$task];

// OAuth 1.0
if (isset($_GET['redirect'])) {
  $oa = new OfflajnOAuth($task);
  $oa->getToken();  // redirection
}
if (isset($_REQUEST['oauth_verifier'])) {
  $oa = new OfflajnOAuth($task);
  $user = $oa->getUser();
}

// OAuth 2.0
if (isset($_GET['code'])) {
  if (!ini_get('allow_url_fopen') && ini_set('allow_url_fopen', 1) === false)
    $_SESSION['ologin']['curl'] = 1;

  $oa = new OfflajnOAuth2($task, $_GET['code'], $_SESSION['ologin']['curl']);
  $user = $oa->getUser();
} elseif (isset($_GET['error_message'])) die($_GET['error_message']);

if ($user) {
  $newuser = 0;
  if (!$user->getJUser()) {
    $newuser = 1;
    $email = $user->getEmail();
    // manual registration
    if ($_SESSION['ologin']['regpage'] == 'joomla' || !$email) oexit("
      lgn = opener.ologin;
      dojo = opener.odojo;
      function fillout() {
        lgn.regForm['name'].value = '{$user->name}';
        dojo.addClass(lgn.regForm['name'], 'correct');
        lgn.regForm['username'].value = '{$user->searchUserName()}';
        lgn.checkUsername({currentTarget: lgn.regForm['username']});
        lgn.regForm['email'].value = '$email';
        dojo.addClass(lgn.regForm['email'], 'correct');
        lgn.regForm['email2'].value = '$email';
        dojo.addClass(lgn.regForm['email2'], 'correct');
        lgn.regForm['socialType'].value = '$task';
        lgn.regForm['socialId'].value = '{$user->id}';
      }
      if (lgn.btn && lgn.logLyr.style.display == 'block') {
        lgn.closeWnd(0);
        opener.setTimeout(dojo.hitch(lgn, 'onclickLoginBtn', {currentTarget:lgn.reg}), lgn.dur+50);
        opener.setTimeout(fillout, 2*lgn.dur);
      } else {
        if (!lgn.btn) lgn.onclickLoginBtn({currentTarget:lgn.reg});
        fillout();
      }
      window.close();
    ");
    // auto registration
    $user->register();
  }
  if ($user->juser->block ) oexit('
    var lgn = window.opener.ologin;
    new window.opener.WW.LoginMsg({
      parent: lgn.socialBtn,
      wnd: lgn.wnd,
      pos: lgn.left? "L" : "R",
      ico: "Err",
      msg: "'.JText::_($v15? 'LOGIN_BLOCKED' : 'JERROR_NOLOGIN_BLOCKED').'"
  	});
    window.close();
  ');
  if ($user->login()) oexit("
    var lgn = window.opener.ologin;
    var returnUrl = ($newuser && lgn.socialProfile)? lgn.socialProfile : lgn.returnUrl;
    if (lgn.useAJAX) lgn.ajaxLoadPage({url: returnUrl});
    else window.opener.location.href = returnUrl;
    window.close();
  ");
}
oexit('winsow.close();');

function oexit($script) {
  ob_flush(); ?>
  <!DOCTYPE html>
  <html>
    <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8" />
      <title>Login</title>
      <script type="text/javascript">
        <?php echo $script ?>
      </script>
    </head>
    <body>
    </body>
  </html>
  <?php
  exit;
}