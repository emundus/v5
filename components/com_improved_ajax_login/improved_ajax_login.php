<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login & Register
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
$revision = '1.226';
?>
<?php
defined('_JEXEC') or die( 'Restricted access' );
error_reporting(E_ALL ^ E_NOTICE);

$mainframe = JFactory::getApplication();
$db = JFactory::getDBO();
$v15 = version_compare(JVERSION,'1.6.0','lt');
$v30 = version_compare(JVERSION,'3.0.0','ge');

$task = JRequest::getWord('task');

if ($task == 'login') {
  require dirname(__FILE__).'/login.php';
} elseif ($task == 'register') {
  require dirname(__FILE__).'/register.php';
} else {
  require dirname(__FILE__).'/oauth.php';
}