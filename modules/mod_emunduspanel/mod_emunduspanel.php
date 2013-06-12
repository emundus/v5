<?php
/**
* @version		$Id: mod_emunduspanel.php 7692 2011-05-26 20:41:29Z mguillossou $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emunduspanel/style/' );

$user = JFactory::getUser();
	if(isset($user->menutype)) $user_menutype = $user->menutype;
	else $user_menutype = 'mainmenu';
	$folder = $params->get('folder', '');
	$text = $params->get($user_menutype, '');
	$img = $params->get($user_menutype.'_img', '');
	$is_text = $params->get($user_menutype.'_text', '');
	$img = explode(',',$img);
	$col = 0;
	$t__ = '';
	$i = 1;
	
	if(is_array($text) && !empty($text)){
		foreach($text as $t){
			if(count($text) != $i) $t__ .= $t.',';
			else $t__ .= $t;
			$i++;
		}
	}else{
		$t__ = $text;
	}
	if (!empty($t__)) {
		$db	= JFactory::getDBO();
		if($user_menutype == 'mainmenu')
			$query = 'SELECT m.menutype, m.title, m.alias, m.link, m.id FROM #__menu m WHERE m.id IN ('.$t__.') ORDER BY m.parent_id, m.ordering, m.level, m.menutype, m.id';
		else
			$query = 'SELECT m.menutype, m.title, m.alias, m.link, m.id FROM #__menu m WHERE m.id IN ('.$t__.') ORDER BY m.parent_id DESC, m.ordering, m.level, m.menutype, m.id ASC';
		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (count($res > 0)) {
			$tab = array();
			$tab_temp = array();
			echo '<div class="emundus_home_page">';
			$j = 0;
			foreach($res as $r){
				$src = $folder.''.$img[$j];
				$str = '<a href="'.$r->link.'&Itemid='.$r->id.'"><img src="'.$src.'" /></a>';
				if($is_text == '1')
					$str .= '<br/><a class="text" href="'.$r->link.'&Itemid='.$r->id.'">'.$r->title.'</a>';
				$tab[] = $str;
				$j++;
			}
			/*if($is_text == '1')
				$tab = array_merge($tab,$tab_temp);*/
			$col = count($tab);
	
			echo '</div>';
		}
	}	

require(JModuleHelper::getLayoutPath('mod_emunduspanel'));