<?php 
//JHTML::_('behavior.modal'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

$user =& JFactory::getUser();
$_db =& JFactory::getDBO();

/*$query = 'select fbtables.id AS table_id, fbtables.form_id, fbtables.label, fbtables.db_table_name, CONCAT(menu.link,"&Itemid=",menu.id) as link, menu.id, menu.title 
FROM #__menu AS menu 
INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype AND profile.id = '.$user->profile.' 
INNER JOIN #__fabrik_forms AS fbforms ON fbforms.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
WHERE menu.published=1 AND menu.parent_id !=1 
ORDER BY menu.ordering';
$_db->setQuery( $query );
$forms = $_db->loadObjectList();*/
$forms = EmundusHelperMenu::buildMenuQuery($user->profile);
foreach ($forms as $form) {
	$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$user->id;
	$_db->setQuery( $query );
	$form->nb = $_db->loadResult();
	$link 	= '<a href="'.$form->link.'">';
	if ($form->nb==0) 
		$class	= 'need_missing';
	else
		$class	= 'need_ok';
	$endlink= '</a>';
?>
	<p class="<?php echo $class; ?>"><?php echo $link.$form->title.$endlink; ?></p>
<?php } 
unset($link);
unset($endlink);
?>