<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus-redirect.php 89 2012-07-02 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */
$db =& JFactory::getDBO();
$user = & JFactory::getUser();
$jinput = JFactory::getApplication()->input;
$formid = $jinput->get('formid');

$query = 'SELECT CONCAT(link,"&Itemid=",id) 
		FROM #__menu 
		WHERE published=1 AND menutype = "'.$user->menutype.'" 
		AND parent_id != 1
		AND ordering = 1+(
				SELECT menu.ordering 
				FROM `#__menu` AS menu 
				WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'" 
				AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)='.$formid.')';

$db->setQuery( $query );
$link = $db->loadResult();

if(empty($link)) {
	$query = 'SELECT CONCAT(link,"&Itemid=",id) 
	FROM #__menu 
	WHERE published=1 AND menutype = "'.$user->menutype.'" AND type!="separator" AND published=1 AND alias = "checklist"';
	$db->setQuery( $query );
	$link = $db->loadResult();
}

header('Location: '.$link);
exit();
 ?>