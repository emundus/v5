<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: isApplicationSent.php 89 2012-12-12 Benjamin Rivalland
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
$user =& JFactory::getUser();
$db =& JFactory::getDBO();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$query = 'SELECT id FROM #__usergroups
 		WHERE title="Registered"';
$db->setQuery($query);
$registered = $db->loadResult();
if ($jinput->get('view') == 'form' && $user->usertype == $registered) {
	$itemid = $jinput->get('Itemid');
	
	// Si l'application Form a été envoyée : affichage vue details
	if($user->candidature_posted > 0)
		$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid);
}
?>