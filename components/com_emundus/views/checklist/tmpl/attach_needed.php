<?php 
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );

$user = JFactory::getUser();
$_db = JFactory::getDBO();

$query='SELECT id, link FROM #__menu WHERE alias="checklist"';
$_db->setQuery( $query );
$itemid = $_db->loadAssoc();

$query='SELECT esa.value, esap.id, esa.id as _id
	FROM #__emundus_setup_attachment_profiles esap
	JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
	WHERE esap.displayed = 1 AND esap.mandatory = 1 AND esap.profile_id ='.$user->profile.'  
	ORDER BY esa.ordering';

$_db->setQuery( $query );
$forms = $_db->loadObjectList();
?>
</ul>
<?php 
foreach ($forms as $form) {
	$query = 'SELECT count(id) FROM #__emundus_uploads up
				WHERE up.user_id = '.$user->id.' AND up.attachment_id = '.$form->_id;
	$_db->setQuery( $query );
	$cpt = $_db->loadResult();
	$link 	= '<a href="'.$itemid['link'].'&Itemid='.$itemid['id'].'#a'.$form->_id.'">';
	if ($cpt==0)
		$class	= 'need_missing';
	else
		$class	= 'need_ok';
	$endlink= '</a>';
?>
    <li class="em_module <?php echo $class; ?>"><div class="em_form"><?php echo $link.$form->value.$endlink; ?></div></li>
<?php } ?>
</li>
<?php
unset($link);
unset($endlink);
?>