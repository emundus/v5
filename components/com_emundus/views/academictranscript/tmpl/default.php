<?php 
defined('_JEXEC') or die('Restricted access'); 

JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
JHTML::stylesheet( 'template.css', JURI::Base().'templates/emundus/css/' );

$action = JRequest::getVar('action', null, 'GET', 'none',0);
if ($action == 'DONE') {
	echo '<p><fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/clean.png" alt="'.JText::_('ACTION_DONE').'"/>'.JText::_('ACTION_DONE').'</legend>';
	//echo '<input type="button" value="'.JText::_('CLOSE').'" onclick="window.close()" />';
	echo '</fieldset></p>';
} 
$student_id = JRequest::getVar('student_id', null, 'GET', 'none',0);
$user =& JFactory::getUser();
$student =& JFactory::getUser($student_id);
$status = @$this->learning_agreement_status[$student_id]->status;
echo '<h1>'.$student->name.' #'.$student->id.'</h1>';
?>

<fieldset><legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/documentary_properties_22x22.png" alt="<?php echo JText::_('ACADEMIC_TRANSCRIPT'); ?>"/> <?php echo JText::_('ACADEMIC_TRANSCRIPT'); ?></legend>
<?php
if (count($this->learning_units) > 0) {
?>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>"/>
<table id="userlist">
	<thead>
	<tr>
    	<td><?php echo '<h4>'.JText::_('TEACHING_UNITS').'</h4>'; ?></td>
    	<td><?php echo '<h4>'.JText::_('GRADE').'</h4>'; ?></td>
    	<td><?php echo '<h4>'.JText::_('OBTAINED').'</h4>'; ?></td>
    </tr>
    </thead>
<?php foreach ($this->learning_units as $lu) { ?>
	<tr>
		<td><label for="grade[<?php echo $lu->teaching_unity_id; ?>]"><?php echo $lu->label; ?> [<i><?php echo $lu->code; ?></i>]</label></td>
		<td><input type="input" name="grade[<?php echo $lu->teaching_unity_id.'___'.$lu->code; ?>]" value="<?php echo $lu->grade; ?>" size="3"  /></td>
        <td><input type="checkbox" name="obtained[<?php echo $lu->teaching_unity_id; ?>]" <?php echo $lu->obtained==1?'checked':''; ?> /></td>
	</tr>
<?php } ?>
<?php 
  if ($user->profile <= 5 && $user->profile != 3) {
?>
	<tr>
     <td colspan="3" align="center"><input type="submit" name="update" onclick="document.pressed=this.name" value="<?php echo JText::_( 'SAVE' );?>" ></td>
	</tr>
<?php } ?>
</table>
</form>
<?php
} else echo JText::_( 'NO_TEACHING_UNIT' );
?>
</fieldset>
<script>
function OnSubmitForm() {
	//alert(document.pressed);
	switch(document.pressed) {
		case 'update': 
			//if (confirm("<?php echo JText::_("CONFIRM_VALIDATE"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=academictranscript&task=update";
		 	//} else 
		 	//	return false;
		break;
		default: return false;
	}
	return true;
}
</script>