<?php 
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
JHTML::stylesheet( 'template.css', JURI::Base().'templates/emundus/css/' );
defined('_JEXEC') or die('Restricted access'); 

$action = JRequest::getVar('action', null, 'GET', 'none',0);
if ($action == 'DONE') {
	echo '<script>window.parent.location.reload();window.close();</script>';
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
<fieldset><legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/learning_agreement<?php echo $status?'_validated':'' ?>_22x22.png" alt="<?php echo $status?JText::_('SELECTED_TEACHING_UNITS'):JText::_('SELECT_TEACHING_UNITS'); ?>"/> <?php echo $status?JText::_('SELECTED_TEACHING_UNITS'):JText::_('SELECT_TEACHING_UNITS'); ?></legend>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="student_id" value="<?php echo $student_id; ?>"/>
<table id="teaching_unity">
<?php foreach ($this->teaching_unity as $tu) { ?>
	<tr>
		<td><?php echo '<span class="editlinktip hasTip" title="'.JText::_('TEACHING_UNITS_DETAILS').'::'.JText::_('<b>CODE</b>:'.$tu->code.'<br /><b>UNIVERSITY</b>:'.$tu->university.'<br /><b>SCHOOLYEAR</b>:'.$tu->schoolyear.'<br /><b>SEMESTER</b>:'.$tu->semester.'<br /><b>ECTS</b>:'.$tu->ects.'<br /><b>NOTE</b>:'.$tu->notes.'<br />').'">'.$tu->label.'</span>'; ?></td>
		<td align="center">
		<?php 
        $db =& JFactory::getDBO();
        $query = 'SELECT count(id) FROM `#__emundus_learning_agreement` WHERE `user_id`='.$student_id.' AND `teaching_unity_id`='.$tu->id;
        $db->setQuery($query);
        $count = $db->loadResult();
        ?>
        <?php 
		if ($this->incharge > 0) { ?>
        <input type="checkbox" name="ud[]" value="<?php echo $tu->id; ?>" <?php echo $status?'disabled="disabled"':''; ?>" <?php if($count>0) echo 'checked'; ?> />
        <?php
		} else {?>
        <input type="checkbox" name="ud[]" value="<?php echo $tu->id; ?>" disabled="disabled" <?php if($count>0) echo 'checked'; ?> />
        <?php
		} ?>
        </td>
		<!-- <td align="center"><input type="text" size="3" name="fo[]" value="<?php echo $tu->ordering; ?>" id="orderf<?php echo $tu->id; ?>" /></td> -->
	</tr>
<?php } ?>
	<tr>
     <td colspan="3" align="center">
<?php
  if ($user->profile <= 5 && $user->profile != 3 && $this->incharge > 0) {
    if ($status != 1) {
?>
        <input type="submit" name="update" onclick="document.pressed=this.name" value="<?php echo JText::_( 'SAVE' );?>" >
        <input type="submit" name="validate" onclick="document.pressed=this.name" value="<?php echo JText::_( 'LEARNING_AGREEMENT_VALIDATION' );?>" >
<?php
	} else {
?>
		<input type="submit" name="unvalidate" onclick="document.pressed=this.name" value="<?php echo JText::_( 'LEARNING_AGREEMENT_UNVALIDATION' );?>" >
<?php       
      echo '<span class="editlinktip hasTip" title="'.JText::_('ACADEMIC_TRANSCRIPT').'::'.JText::_('ACADEMIC_TRANSCRIPT_TXT').'"><a href="index.php?option=com_emundus&view=academictranscript&student_id='.$student_id.'&tmpl=component"><img src="'.JURI::Base().'media/com_emundus/images/icones/documentary_properties_22x22.png" alt="'.JText::_('ACADEMIC_TRANSCRIPT').'" align="absbottom" /></a></span> ';
?>
<?php
	}
  }
?>	 </td>
	</tr>
</table>
</form>
</fieldset>
<script>
function OnSubmitForm() {
	//alert(document.pressed);
	switch(document.pressed) {
		case 'update': 
			document.adminForm.action ="index.php?option=com_emundus&controller=learningagreement&task=update";
		break;
		case 'validate': 
			if (confirm("<?php echo JText::_("CONFIRM_VALIDATE"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=learningagreement&task=validate";
		 	} else 
		 		return false;
		break;
		case 'unvalidate': 
			if (confirm("<?php echo JText::_("CONFIRM_UNVALIDATE"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=learningagreement&task=unvalidate";
		 	} else 
		 		return false;
		break;
		default: return false;
	}
	return true;
}

function toggleF(baliseId) {
	if(!document.getElementById('selectedf'+baliseId).checked) {
		document.getElementById('orderf'+baliseId).disabled = true;
	} else {
		document.getElementById('orderf'+baliseId).disabled = false;
	}
}
<?php 
foreach ($this->teaching_unity as $tu) { ?>
if(!document.getElementById('selectedf<?php echo $tu->id; ?>').checked) {
  document.getElementById('orderf<?php echo $tu->id; ?>').disabled = true;
}
<?php } ?>
</script>
</body>