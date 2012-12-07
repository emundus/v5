<?php 
JHTML::_('behavior.modal'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_emundus/style/' );
defined('_JEXEC') or die('Restricted access'); 
$current_s = JRequest::getVar('rowid', false, '', 'STRING');
$current_p = JRequest::getVar('pid', null, '', 'INT');
$tmpl = JRequest::getVar('tmpl', null);
?>

<?php if(empty($tmpl)) { ?>
<form action="index.php" method="POST">
	<input type="hidden" name="option" value="com_emundus"/>
	<input type="hidden" name="view" value="trombi"/>
<table>
<tr><th><?php echo JText::_('SCHOOLYEAR_SELECT'); ?></th><td>
	<select name="rowid" onChange="javascript:submit()">
		<option value=""> --- </option>
<?php foreach($this->schoolyears as $schoolyear) { ?>
		<option value="<?php echo $schoolyear; ?>" <? if($current_s==$schoolyear) echo 'selected'; ?>><?php echo $schoolyear; ?></option>
<?php } ?>
	</select>
</td></tr>
<tr><th><?php echo JText::_('PROFILE_FILTER'); ?></th><td>
	<select name="pid" onChange="javascript:submit()">
		<option value=""> --- </option>
<?php foreach($this->profiles as $profile) { ?>
		<option value="<?php echo $profile->id; ?>" <? if($current_p==$profile->id) echo 'selected'; ?>><?php echo $profile->label; ?></option>
<?php } ?>
	</select>
</td></tr>
<tr><th><?php echo JText::_('FINAL_GRADE_FILTER'); ?></th><td><select name="fg" id="fg" onChange="javascript:submit()">
      <option value=""></option>
      <option value="4" <?php if (JRequest::getVar( 'fg', null, 'post' )=='4') echo 'selected'; ?>><?php echo JText::_( 'ACCEPTED'); ?></option>
      <option value="3" <?php if (JRequest::getVar( 'fg', null, 'post' )=='3') echo 'selected'; ?>><?php echo JText::_( 'WAITING_LIST'); ?></option>
      <option value="2" <?php if (JRequest::getVar( 'fg', null, 'post' )=='2') echo 'selected'; ?>><?php echo JText::_( 'REJECTED'); ?></option>
    </select></td></td></tr>
</table>
</form>
<p class="print"><a target="_blank" href="index.php?option=com_emundus&view=trombi&rowid=<?php echo $current_s; ?>&pid=<?php echo $current_p; ?>&tmpl=component"><?php echo JText::_('PRINTABLE_VERSION'); ?></a></p>

<?php } ?>
<?php if(!empty($this->users)) { $j = 0; ?>
<table>
<?php $current_s = 'azmlfkdshfgjkhbsmqjskdfhmsljdbqksdjbv';
foreach ($this->users as $user) {  ?>
	<?php if($current_s != $user->schoolyear) { $current_s = $user->schoolyear; if($j != 0) echo str_repeat('<td></td>',6-$j);?>
		</table>
		<h1><?php echo empty($current_s)?JText::_('OTHER_USERS'):JText::_('STUDENTS_SCHOOLYEAR').' '.$current_s; ?></h1>
		<table cellspacing="15px">
	<?php } if($j == 0) echo '<tr>'; ?>
	<td align="center">
		<?php $photo = empty($user->filename)?'images/emundus/icones/clock.png':EMUNDUS_PATH_REL.$user->id.'/tn_'.$user->filename; ?>
		<p align="center"><img src="<?php echo $photo ; ?>" alt="Photo" <?php if(current(getimagesize($photo))>100) echo 'width="100"'; ?> /></p>
		<p align="center"><?php echo $user->lastname; ?><br/><?php echo $user->firstname; ?></p>
		<p align="center"><?php echo $user->nationality; ?></p>
	</td>
<?php if($j == 5) { 
				echo '</tr>';
				$j = 0;
			} else $j++;
	} if($j != 0) echo str_repeat('<td>&nbsp;</td>',6-$j); ?>
	</table>
<?php } else {?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php } ?>
