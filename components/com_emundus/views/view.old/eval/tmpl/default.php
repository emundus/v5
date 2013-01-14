<?php 
defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_emundus/style/' );
$eMConfig =& JComponentHelper::getParams('com_emundus');
$quotient = $eMConfig->get('quotient', '20');
		
$current_user = JFactory::getUser();
$eval_in_progress = JRequest::getVar('eval_in_progress', null, 'POST', 'none',0);
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);

$db =& JFactory::getDBO();
?>

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<fieldset>
<legend>
<img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?>
</legend>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="eval"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<table width="100%">
 <tr align="left" valign="bottom">
  <th align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
 </tr>
 <tr align="left" valign="bottom">
  <td>
  <input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/>
  <select name="profile" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL_PROFILES'); ?> </option>
	<?php 
	foreach($this->applicantsProfiles as $applicantsProfiles) { 
		echo '<option value="'.$applicantsProfiles->id.'"';
			if($current_p==$applicantsProfiles->id) echo ' selected';
					echo '>'.$applicantsProfiles->label.'</option>'; 
	} 
	?>  
    </select> 
    <select name="eval_in_progress" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('EVALUATIONS_IN_PROGRESS_SELECT'); ?> </option>
    <option value="-1" <?php echo $eval_in_progress==='-1'?'selected':''; ?>> <?php echo JText::_('EVALUATIONS_IN_PROGRESS'); ?> </option>
    <option value="1" <?php echo $eval_in_progress==='1'?'selected':''; ?>> <?php echo JText::_('EVALUATIONS_COMPLETE'); ?> </option>
    </select>
  <br />
	<input type="submit" name="search_button" onclick="document.pressed=this.name" value="<?php echo JText::_('SEARCH_BTN'); ?>"/>
	<input type="submit" name="clear_button" onclick="document.pressed=this.name" value="<?php echo JText::_('CLEAR_BTN'); ?>"/>
  </td>
 </tr>
</table>
</fieldset>
<?php
if(!empty($this->users)) {
?>
<span class="editlinktip hasTip" title="<?php echo JText::_('EXPORT_ALL_TO_XLS'); ?>"><input type="image" name="export_xls" class="emundusraw" src="<?php echo $this->baseurl; ?>/images/emundus/icones/XLSFile_48.png" onclick="document.pressed=this.name" ></span>
<?php 
	if($tmpl == 'component') {
			echo '<div><h3><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('EVALUATION_LIST').'"/>'.JText::_('EVALUATION_LIST').'</h3>';
			$document =& JFactory::getDocument();
			$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundusraw.css" );
	}else{
			echo '<fieldset><legend><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('EVALUATION_LIST').'"/>'.JText::_('EVALUATION_LIST').'</legend>';
	}
?>

<table id="userlist">
	<thead>
    <tr>
		<td align="center" colspan="16">
			<?php echo $this->pagination->getResultsCounter(); ?>
		</td>
	</tr>
	<tr>
		<th>
        <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICANT_NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('Country'), 'country', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('NATIONALITY'), 'nationality', $this->lists['order_Dir'], $this->lists['order']); ?></th>
<?php
//die(print_r($this->criterias));
foreach ($this->criterias as $crt) {
	echo '<th><span class="editlinktip hasTip" title="<br /><br />::'.$crt->label.'<br />">'.JHTML::_('grid.sort', substr($crt->label,0,4).'...', $crt->name, $this->lists['order_Dir'], $this->lists['order']).'</span></th>';
}
?>
        <th><?php echo JHTML::_('grid.sort', JText::_('MEAN'), 'mean', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('MEAN_APPLICATION'), 'mean_application', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('MEAN_ORAL'), 'mean_oral', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('EVALUATOR'), 'evaluator_lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('EVALUATED_ON'), 'time_date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
	</tr>
    </thead>
    <tfoot>
		<tr>
			<td colspan="15">
			<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>

<?php 
$i=0;
$j=0;
foreach ($this->users as $user) { ?>
	<tr class="row<?php echo $j++%2; ?>">
        <td>
        <div class="emundusraw">
		<?php 
			echo ++$i+$limitstart;
			if($user->id != 62)  ?> <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]" value="<?php echo $user->id; ?>"/>
        <?php
			echo '<span class="editlinktip hasTip" title="'.JText::_('MAIL_TO').'::'.$user->email.'">';
			if ($user->gender == 'male')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/images/emundus/icones/user_male.png" width="22" height="22" align="bottom" /></a> ';
			elseif ($user->gender == 'female')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/images/emundus/icones/user_female.png" width="22" height="22" align="bottom" /></a> ';
			else
				echo '<a href="mailto:'.$user->email.'">'.$user->gender.'</a> ';
			echo '</span>';
			echo '<span class="editlinktip hasTip" title="'.JText::_('APPLICATION_FORM').'::'.JText::_('POPUP_APPLICATION_FORM_DETAILS').'">';
			echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=application_form&sid='. $user->id.'&tmpl=component" target="_blank" class="modal"><img src="'.$this->baseurl.'/images/emundus/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a> ';
			echo '</span>#'.$user->id.'</div>';
		?>
        </td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong><br />'.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
		</td>
        <td><?php echo $user->nationality ?></td>
        <td><?php echo $user->country ?></td>
<?php 
        foreach ($this->criterias as $crt) {
			$query = 'SELECT id, sub_values, sub_labels FROM #__fabrik_elements WHERE name like "'.$crt->name.'"';
			//die($query);
			$db->setQuery($query);
			$row_value = $db->loadRow();
			$ptmp = explode('|', $row_value[1]);
			
			foreach($ptmp as $pat)
				$preg[] = '/'.$pat.'/';
			
			$replacements = explode('|', $row_value[2]);
			$class = (!empty($user->{$crt->name}) || $user->{$crt->name}==='0')?"class=green":"class=red";
			echo '<td '.$class.'>'.preg_replace($preg, $replacements,$user->{$crt->name}).'</td>';
			unset($preg);
			unset($replacements);
		}
?>
        <td><?php echo '<strong>'.number_format($user->mean, 3, ',', '').'</strong> / '.$quotient; ?></td>
        <td><?php echo '<strong>'.number_format($user->mean_application, 3, ',', '').'</strong> / '.$quotient; ?></td>
        <td><?php echo '<strong>'.number_format($user->mean_oral, 3, ',', '').'</strong> / '.$quotient; ?></td>
        <td><?php 
		echo '<strong>'.strtoupper($user->evaluator_lastname).'</strong><br />'.$user->evaluator_firstname; 
		echo '<br /><a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&fabrik=29&random=0&rowid='.$user->rowid.'&usekey=id&student_id='. $user->id.'&tmpl=component" target="_self" class="modal">'.JText::_( 'UPDATE_EVALUATION' ).'</a>'; 
		?></td>
		<td><?php echo strftime(JText::_('DATE_FORMAT_LC3'), strtotime($user->time_date)); ?></td>
	</tr>
<?php 
} 
?>
</table>
<?php 
	if($tmpl == 'component') {
		echo '</div>';
	}else{
		echo '</fieldset>';
	}
?>


<?php } else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php } ?>

</form>

<script>
function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->id; ?>').checked = checked;
<?php } ?>
}

<?php 
//$allowed = array("Super Users", "Administrator", "Editor");
if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id) && !EmundusHelperAccess::isPartner($user->id)) {
?>
function hidden_all() {
  document.getElementById('checkall').style.visibility='hidden';
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->id; ?>').style.visibility='hidden';
<?php } ?>
}
hidden_all();
<?php 
}
?>

function removeElement(divNum) {
  var d = document.getElementById('myDiv');
  var olddiv = document.getElementById(divNum);
  d.removeChild(olddiv);
}

function tableOrdering( order, dir, task ) {
  var form = document.adminForm;
  form.filter_order.value = order;
  form.filter_order_Dir.value = dir;
  document.adminForm.submit( task );
}


function OnSubmitForm() {
	//alert(document.pressed);
	switch(document.pressed) {
		case 'export_xls': 
			document.adminForm.action ="index.php?option=com_emundus&controller=eval&task=export_to_xls";
		break;
		case 'search_button': 
			document.adminForm.submit();
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&controller=eval&task=clear";
		break;
		default: return false;
	}
	return true;
}
</SCRIPT>