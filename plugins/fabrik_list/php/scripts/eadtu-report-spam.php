<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$db = FabrikWorker::getDbo();
$user = JFactory::getUser();

$ids = JRequest::getVar('ids', array());
$sql = "SELECT id, title FROM eadtu_project WHERE id IN (" . implode(',', $ids) . ")";
$db->setQuery($sql);
$rows = $db->loadObjectList();

//RECORD IN SPAM REPORT TABLE
$date = JFactory::getDate();
$now = $date->toSql();
$userid = $user->get('id');
foreach($rows as $r) {
	$sql = "INSERT INTO eadtu_spam_report (`time_date`, `reporter_id`, `project_id`) VALUES ( '$now', '$userid', '$r->id')";
	$db->setQuery($sql);
	$db->execute();
}

// EMAIL
global $mainframe;

$MailFrom	= $mainframe->getCfg('mailfrom');
$FromName	= $mainframe->getCfg('fromname');
$SiteName	= $mainframe->getCfg('sitename');
$subject = "$SiteName: project reported as spam";


$message = "Dear Admin,<br />
<p>" . $user->get('name') . " has reported the following projects as spam. Please review them and if necessary delete them:</p><ul>";

foreach($rows as $r) {
 	$url = COM_FABRIK_LIVESITE.JRoute::_('index.php?option=com_fabrik&c=form&view=details&Itemid=112&formid=2&rowid='.$r->id.'&listid=2');
	$message .= "<li><a href='$url'>".$r->title."</a></li>";
 }
 $message .= "</ul>
<p><a href='".COM_FABRIK_LIVESITE."'>".COM_FABRIK_LIVESITE."</a></p>";
	$res = JUtility::sendMail( $MailFrom, $FromName, $MailFrom, $subject, $message, true);
$msg = "Spam report sent";

?>