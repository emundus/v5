<?php
defined( '_JEXEC' ) or die();
/**
 * @version 2: isApplicationSend.php 89 2010-10-10 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation de mettre à jour le formulaire
 */
 $user = & JFactory::getUser();
 if ($_REQUEST['view'] == 'form' && $user->usertype == "Registered") {
	global $mainframe;
	$rowid = $_REQUEST['rowid'];
	$itemid = $_REQUEST['Itemid'];
	$i = -1;
	// Si l'url est modifié pour tenter d'afficher les données d'un autre userid
	if ( $user->id != $rowid &&  $rowid != $i &&  $rowid != 0 ) {
		$mainframe->redirect( "index.php?option=com_fabrik&view=details&fabrik=".$_REQUEST['fabrik']."&random=0&rowid=-1&usekey=user&Itemid=".$itemid);
		die("ARG");
	}
	
	// Si l'application Form a été envoyée : affichage vue details
	if($user->candidature_posted > 0)
		$mainframe->redirect( "index.php?option=com_fabrik&view=details&fabrik=".$_REQUEST['fabrik']."&random=0&rowid=-1&usekey=user&Itemid=".$itemid);
		
	$db =& JFactory::getDBO();
	
	$query = 'SELECT db_table_name from #__fabrik_lists where id='.$_REQUEST['tableid'];
	$db->setQuery($query);
	$temundus = $db->loadResult();
	
	$query = 'SELECT COUNT(id) FROM '.$temundus.' WHERE user = '.$user->id;
	$db->setQuery($query);
	$trecorded = $db->loadResult();
	
	if($trecorded == 0 && @$_REQUEST['r'] != 1)
		$mainframe->redirect( "index.php?option=com_fabrik&view=form&fabrik=".$_REQUEST['fabrik']."&tableid=".$_REQUEST['tableid']."&rowid=&r=1&Itemid=".$itemid);

}
 ?>