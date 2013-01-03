<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: attachement_public_check.php 89 2008-10-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation d'upload par un tier
 */
 if ($_REQUEST['view'] == 'form') {
 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

	$mainframe =& JFactory::getApplication();
	$user =& JFactory::getUser();
	
	if($user->get('usertype') != "Registered") return;
	
	$db =& JFactory::getDBO();
	
	$query = 'SELECT 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id)
				FROM #__emundus_setup_attachment_profiles AS profiles 
				LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$user->id.'
				WHERE profiles.profile_id = '.$user->profile.' AND profiles.displayed = 1 AND profiles.mandatory = 1 ';
	$db->setQuery($query);
	$attachments = floor($db->loadResult());
	
	$forms =EmundusHelperMenu::buildMenuListQuery($user->profile);
	$nb = 0;
	foreach ($forms as $form) {
		$query = 'SELECT count(*) FROM '.$form.' WHERE user = '.$user->id;
		$db->setQuery( $query );
		$form = $db->loadResult();
		if ($form==1) $nb++;
	}
	$forms = floor(100*$nb/count($forms));
	
	//on récupère l'id de la page checklist et on redirige vers celle-ci 
	//si l'étudiant n'a encore pas le droit d'envoyer son application form
	$query = 'SELECT id
					FROM #__menu
					WHERE menutype = "'.$user->menutype.'"
					AND alias = "checklist"
					AND parent_id = 1';
	$db->setQuery($query);
	$itemid = $db->loadResult();
	if($attachments < 100 || $forms < 100 ){
		$mainframe->redirect( "index.php?option=com_emundus&view=checklist&Itemid=".$itemid,JText::_('INCOMPLETE_APPLICATION'));
	}
}
 ?>