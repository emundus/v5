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
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
$user =& JFactory::getUser();
//$db =& JFactory::getDBO();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

//$registered = $db->loadResult();
if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)){
	$student_id = JRequest::getVar('rowid', null, 'get');
	$student = JUser::getInstance($student_id);
	echo '<a href="index.php?option=com_emundus&view=application&sid='.$student_id.'"><h1>'.$student->name.'</h1></a>';
	JHTML::stylesheet( 'template_css.php?c=29&view=form" type="text/css', JURI::Base().'components/com_fabrik/views/form/tmpl/labels-above/' ); 
	JHTML::stylesheet( 'fabrik.css', JURI::Base().'media/com_fabrik/css/' );
	JHTML::stylesheet( 'light2.css', JURI::Base().'templates/rt_afterburner/css/' );
}
if ($jinput->get('view') == 'form' && $user->usertype == "Registered") {
	$itemid = $jinput->get('Itemid');
	
	// Si l'application Form a été envoyée : affichage vue details
	if($user->candidature_posted > 0)
		$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid);
}
?>