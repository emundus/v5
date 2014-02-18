<?php
/**
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class EmundusHelperMenu{

	function buildMenuQuery($profile) {
		$_db = JFactory::getDBO();
		$query = 'SELECT fbtables.id AS table_id, fbtables.form_id, fbtables.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) AS link, menu.id, menu.title 
		FROM #__menu AS menu 
		INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$profile.' 
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
		LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
		WHERE menu.published=1 AND menu.parent_id !=1 
		ORDER BY menu.lft';
		$_db->setQuery( $query );
		return $_db->loadObjectList();
	}

	function buildMenuListQuery($profile) {
		$_db = JFactory::getDBO();
		$query = 'SELECT fbtables.db_table_name
		FROM #__menu AS menu 
		INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$profile.' 
		INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
		LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
		WHERE fbtables.published = 1 AND menu.parent_id !=1
		ORDER BY menu.lft';
		$_db->setQuery( $query );
		return $_db->loadResultArray();
	}

}
?>