<?php 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
defined('_JEXEC') or die('Restricted access'); 
$document   = JFactory::getDocument();
$current_user = JFactory::getUser();
$current_p = JRequest::getVar('groups', null, 'POST', 'none',0);
$current_pid = JRequest::getVar('pid', null, 'POST', 'none',0);
$current_apid = JRequest::getVar('apid', null, 'POST', 'none',0);
$current_las = JRequest::getVar('las', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
$current_ap = JRequest::getVar('profil', null, 'POST', 'none',0);
$current_au = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
// Starting a session.
$session = JFactory::getSession();
// Gettig the orderid if there is one.
$s_elements = $session->get('s_elements');
$s_elements_values = $session->get('s_elements_values');

if (count($search)==0 && isset($s_elements)) {
	$search = $s_elements;
	$search_values = $s_elements_values;
}
?>

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>

<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="learningagreementreferent"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<fieldset>
<legend>
<img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?>
</legend>

<table width="100%">
 <tr>
  <th align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
<?php 
if(EmundusHelperAccess::isAdministrator($user->id)) { 
?>
  <th align="left"><?php echo JText::_('TEACHER_USER_FILTER'); ?></th>
<?php 
}
?>
<th align="left"><?php echo JText::_('PROFILE'); ?></th>
<th align="left"><?php echo JText::_('LEARNING_AGREEMENT'); ?></th>
<th align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
 </tr>
 <tr>
	<td align="left"><input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/></td>

<?php 
if(EmundusHelperAccess::isAdministrator($user->id)) { 
?> 
 <td>
  <select name="user" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL'); ?> </option>
	<?php 
	foreach($this->evalUsers as $eval_users) { 
		echo '<option value="'.$eval_users->id.'"';
			if($current_u==$eval_users->id) echo ' selected';
					echo '>'.$eval_users->name.'</option>'; 
	} 
	?>  
    </select>
  </td>
    <?php
}	
	?>

  <td>
  <select name="pid" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL'); ?> </option>
    <?php 
	foreach($this->profiles_id as $pid) { 
	  if($pid->id != 9 && $pid->id != 999) {
		echo '<option value="'.$pid->id.'"';
			if($current_pid==$pid->id) echo ' selected';
					echo '>'.$pid->label.'</option>'; 
	  }
	}
	?>
  </select>
  </td>
   <td>
  <select name="las" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL'); ?> </option>
    <option value="1" <?php echo $current_las=='1'?'selected':''; ?>> <?php echo JText::_('LEARNING_AGREEMENT_IS_SET'); ?> </option>
    <option value="-1" <?php echo $current_las=='-1'?'selected':''; ?>> <?php echo JText::_('LEARNING_AGREEMENT_NOT_SET'); ?> </option>
  </select>
  </td>
   <td>
  <select name="schoolyears" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL'); ?> </option>
    <?php 
	foreach($this->schoolyears as $s) { 
	  echo '<option value="'.$s.'"';
		if($schoolyears==$s) echo ' selected';
			echo '>'.$s.'</option>'; 
	}
	?>
  </select>
  </td>
 </tr>
</table>
<table width="100%">
 <tr>
  <th align="left">
  	<?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('ELEMENT_FILTER').'</span>'; ?>
    <input type="hidden" value="0" id="theValue" />
  	<a href="javascript:;" onclick="addElement();"><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag+_16x16.png" alt="<?php JText::_('ADD_SEARCH_ELEMENT'); ?>"/></a>
  </th>
 </tr>
 <tr>
  <td>
   <div id="myDiv">
<?php 
if (count($search)>0 && isset($search) && is_array($search)) {

	$i=0;
	foreach($search as $sf) {
		echo '<div id="filter'.$i.'">';
?>
    <select name="elements[]" id="elements">
	<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
	<?php  
	$groupe ="";
	foreach($this->elements as $elements) { 
		$groupe_tmp = $elements->group_label;
		$length = 50;
		$dot_grp = strlen($groupe_tmp)>=$length?'...':'';
		$dot_elm = strlen($elements->element_label)>=$length?'...':'';
		if ($groupe != $groupe_tmp) {
			echo '<option class="emundus_search_grp" disabled="disabled" value="">'.substr(strtoupper($groupe_tmp), 0, $length).$dot_grp.'</option>';
			$groupe = $groupe_tmp;
		}
		echo '<option class="emundus_search_elm" value="'.$elements->table_name.'.'.$elements->element_name.'"';
			//$key = array_search($elements->table_name.'.'.$elements->element_name, $search);
			if($elements->table_name.'.'.$elements->element_name == $search[$i]) echo ' selected';
					echo '>'.substr($elements->element_label, 0, $length).$dot_elm.'</option>'; 
	} 
	?>
  </select>
 
  <input name="elements_values[]" width="30" value="<?php echo $search_values[$i];?>" />
  <a href="#" onclick="removeElement('<?php echo 'filter'.$i; ?>')"><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag-_16x16.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>
<?php 
		$i++; 
		echo '</div>';
	} 
} 
?>  
    </div>
	<input type="submit" name="search_button" onclick="document.pressed=this.name" value="<?php echo JText::_('SEARCH_BTN'); ?>"/>
	<input type="submit" name="clear_button" onclick="document.pressed=this.name" value="<?php echo JText::_('CLEAR_BTN'); ?>"/>
  </td>
 </tr>
</table>
</fieldset>

<div class="emundusraw">
<?php
if(!empty($this->users)) {
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile-selected_48.png" name="export_to_xls" onclick="document.pressed=this.name"></span>'; 
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_ALL_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile_48.png" name="export_all_to_xls" onclick="document.pressed=this.name" /></span>';
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>'; 
?>
</div>

<?php 
	if($tmpl == 'component') {
			echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('SELECTED_APPLICANTS_LIST').'"/>'.JText::_('SELECTED_APPLICANTS_LIST').'</h3>';
			$document = JFactory::getDocument();
			$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
	}else{
			echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('SELECTED_APPLICANTS_LIST').'"/>'.JText::_('SELECTED_APPLICANTS_LIST').'</legend>';
	}
?>

<table id="userlist" width="100%">
	<thead>
	<tr>
		<th>
        <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
		<th><?php echo JText::_('PHOTO'); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JText::_('TEACHER_COORDINATOR'); ?></th>
		<th><?php echo JText::_('LEARNING_AGREEMENT'); ?></th>
        <th><?php echo JText::_('TRANSCRIPT'); ?></th>
       <th><?php echo JText::_('STATUS'); ?></th>
	</tr>
    </thead>
    <tfoot>
		<tr>
			<td colspan="10">
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
		<?php echo ++$i+$limitstart; ?>
        <div class="emundusraw">
        <?php 
			if($user->id != 62)  ?> <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]" value="<?php echo $user->id; ?>"/><br />
        <?php
			echo '<span class="editlinktip hasTip" title="'.JText::_('MAIL_TO').'::'.$user->email.'">';
			if ($user->gender == 'male')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_male.png" width="22" height="22" align="bottom" /></a>';
			elseif ($user->gender == 'female')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_female.png" width="22" height="22" align="bottom" /></a>';
			else
				echo '<a href="mailto:'.$user->email.'">'.$user->gender.'</a>';
			echo '</span>';
			echo '<span class="editlinktip hasTip" title="'.JText::_('APPLICATION_FORM').'::'.JText::_('POPUP_APPLICATION_FORM_DETAILS').'">';
			echo '<a rel="{handler: \'iframe\', size: {x: 750, y: window.
innerHeight}}" href="'.$this->baseurl.'/index.php?option=com_reports&view=report&cid[0]=application_form&sid='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a>';
			echo '</span>';
			echo '<span class="editlinktip hasTip" title="'.JText::_('UPLOAD_FILE_FOR_STUDENT').'::'.JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK').'">';
			echo '<a rel="{handler: \'iframe\', size: {x: 450, y: window.
innerHeight}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=checklist&layout=attachments&sid='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/attach_16x16.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="16" height="16" align="bottom" /></a> ';
			echo '</span></div>';
			echo '#'.$user->id.'</div>';
		?>
        </td>
		 <td align="center" valign="middle">
			<?php 	echo '<span class="editlinktip hasTip" title="'.JText::_('OPEN_PHOTO_IN_NEW_WINDOW').'::">';
					echo '<a href="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$user->id.'/'.$user->avatar.'" target="_blank" class="modal"><img src="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$user->id.'/tn_'.$user->avatar.'" width="60" /></a>'; 
					echo '</span>';
			?>        
		</td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong> '.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
		</td>
		<td>
		<?php 
		$db = JFactory::getDBO();
		$query = 'SELECT eca.id, eca.user_id, eca.evaluator_id  
					FROM #__emundus_confirmed_applicants as eca 
					WHERE eca.user_id='.$user->id;
		$db->setQuery( $query );
		$teacher = $db->loadObjectList('id');
		
		//$allowed = array("Super Users", "Administrator");
		//print_r($teacher);
		foreach($teacher as $t) {
			if(!empty($t->evaluator_id) && isset($t->evaluator_id)) {
				$img = '';
				if(EmundusHelperAccess::isAdministrator($user->id)) { 
					$img = '<span class="editlinktip hasTip" title="'.JText::_('DELETE_COORDINATOR_TEACHER').'::'.JText::_('DELETE_COORDINATOR_TEACHER_TXT').'"><a href="index.php?option=com_emundus&controller=learningagreementreferent&task=delAssessor&aid='.$t->evaluator_id.'&uid='.$t->user_id.'&limitstart='.$ls.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'"><img src="'.JURI::Base().'media/com_emundus/images/icones/clear_left_16x16.png" alt="'.JText::_('DELETE_COORDINATOR_TEACHER').'" align="absbottom" /></a></span> ';
				}
				echo JFactory::getUser($t->evaluator_id)->name.' '.$img.'<br />';	
			}
		}
		if (count($teacher)==0)
			echo '<span class="hasTip" title="'.JText::_('COORDINATOR_TEACHER_FILTER_ALERT').'"><font color="red">'.JText::_('NO_COORDINATOR_TEACHER').'</font></span>';
		?>
		</td>	
		<td>
		<?php 
			if ($current_user->profile <= 5) {
				$status = @$this->learning_agreement_status[$user->id]->status;
				if ($status == 1) {
					echo '<span class="editlinktip hasTip" title="'.JText::_('VIEW_LEARNING_AGREEMENT').'::'.JText::_('VIEW_LEARNING_AGREEMENT_TXT').'"><a rel="{handler: \'iframe\', size: {x: 350, y: window.
innerHeight}}" href="index.php?option=com_emundus&view=learningAgreement&student_id='.$user->id.'&tmpl=component" target="_self" class="modal"><img src="'.JURI::Base().'media/com_emundus/images/icones/learning_agreement_validated_22x22.png" alt="'.JText::_('VIEW_LEARNING_AGREEMENT').'" align="absbottom" /></a></span> ';
				} else {
					echo '<span class="editlinktip hasTip" title="'.JText::_('EDIT_LEARNING_AGREEMENT').'::'.JText::_('EDIT_LEARNING_AGREEMENT_TXT').'"><a rel="{handler: \'iframe\', size: {x: 350, y: window.
innerHeight}}" href="index.php?option=com_emundus&view=learningAgreement&student_id='.$user->id.'&tmpl=component" target="_self" class="modal"><img src="'.JURI::Base().'media/com_emundus/images/icones/learning_agreement_set_22x22.png" alt="'.JText::_('EDIT_LEARNING_AGREEMENT').'" align="absbottom" /></a></span> ';
				}
			}
		?>
		</td>
        <td>
		<?php 
			if ($current_user->profile <= 5) {
				$status = @$this->learning_agreement_status[$user->id]->status;
				if ($status == 1) {
					echo '<span class="editlinktip hasTip" title="'.JText::_('ACADEMIC_TRANSCRIPT').'::'.JText::_('ACADEMIC_TRANSCRIPT_TXT').'"><a rel="{handler: \'iframe\', size: {x: 350, y: window.
innerHeight}}" href="index.php?option=com_emundus&view=academicTranscript&student_id='.$user->id.'&tmpl=component" target="_self" class="modal"><img src="'.JURI::Base().'media/com_emundus/images/icones/documentary_properties_22x22.png" alt="'.JText::_('ACADEMIC_TRANSCRIPT').'" align="absbottom" /></a></span> ';
				} 
			}
		?>
		</td>
        <td><?php echo '<div class="emundusprofile'.$user->profile.'">'.$this->profiles_id[$user->profile]->label.'</div>'; ?>
		</td>
	</tr>
<?php 
	$j++;
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
<div class="emundusraw">
<?php
//unset($allowed);
if(EmundusHelperAccess::isAdministrator($user->id)) { 
?>

<fieldset><legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/kbackgammon_engine_22x22.png" alt="<?php JText::_('BATCH'); ?>"/> <?php echo JText::_('AFFECT_TO_TEACHER_OR_LOCAL'); ?></legend>
<table width="100%">
 <tr>
  <th><?php echo JText::_('TEACHER_USER_FILTER'); ?></th>
 </tr>
 <tr>
   <td><select name="assessor_user">
     <option value=""> <?php echo JText::_('PLEASE_SELECT'); ?></option>
     <?php 
	foreach($this->evalUsers as $eval_users) { 
		echo '<option value="'.$eval_users->id.'"';
			if($current_au==$eval_users->id) echo ' selected';
					echo '>'.$eval_users->name.'</option>'; 
	} 
	?>
   </select>

   </td>
  <td>
  	<input type="submit" name="affect" onclick="document.pressed=this.name" value="<?php echo JText::_('AFFECT_SELECTED_TO_TEACHER'); ?>" />
    <input type="submit" name="unaffect" onclick="document.pressed=this.name" value="<?php echo JText::_('UNAFFECT_SELECTED_TO_TEACHER'); ?>" />
    <!-- <input type="submit" name="set_applicant" onclick="document.pressed=this.name" value="<?php echo JText::_('SET_SELECTED_AS_APPLICANT'); ?>" /> -->
  </td>
 </tr>
</table>
</fieldset>

<fieldset>
<legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/kbackgammon_engine_22x22.png" alt="<?php JText::_('BATCH'); ?>"/> <?php echo JText::_('MARK_SELECTED_AS_ENROLLED_STUDENT'); ?></legend>
<!--    <input type="submit" name="registration" onclick="document.pressed=this.name" value="<?php echo JText::_('MARK_SELECTED_AS_ENROLLED_STUDENT_BTN'); ?>" />
    <input type="submit" name="unregistration" onclick="document.pressed=this.name" value="<?php echo JText::_('MARK_SELECTED_AS_SELECTED_APPLICANT_BTN'); ?>" /> -->
<table width="100%">
 <tr>
  <th><?php echo JText::_('PROFILE'); ?></th>
 </tr>
 <tr>
   <td><select name="profile_id">
     <option value=""> <?php echo JText::_('PLEASE_SELECT'); ?></option>
     <?php 
	foreach($this->profiles_id as $apid) { 
		echo '<option value="'.$apid->id.'"';
			if($current_apid==$apid->id) echo ' selected';
					echo '>'.$apid->label.'</option>'; 
	} 
	?>
	</select>
	<input type="submit" name="setPID" onclick="document.pressed=this.name" value="<?php echo JText::_('AFFECT_SELECTED_TO_PID'); ?>" />
  </td>
 </tr>
</table>
</fieldset>
</div>
<?php
}
?>

<?php 
} else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php } ?>
<div class="emundusraw">
<?php
if(EmundusHelperAccess::isAdministrator($user->id)) { 
?>
  <fieldset>
  <legend> 
  	<span class="editlinktip hasTip" title="<?php echo JText::_('EMAIL_TEACHER_DEFAULT').'::'.JText::_('EMAIL_TEACHER_DEFAULT_TIP'); ?>">
		<img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/mail_replayall_22x22.png" alt="<?php JText::_('EMAIL_TEACHER_DEFAULT'); ?>"/> <?php echo JText::_( 'EMAIL_TEACHER_DEFAULT' ); ?>
	</span>
  </legend>
  <input type="submit" name="default_email" onclick="document.pressed=this.name" value="<?php echo JText::_( 'SEND_DEFAULT_EMAIL' );?>" >
  </fieldset>
  
  <fieldset>
  <legend> 
  	<span class="editlinktip hasTip" title="<?php echo JText::_('EMAIL_SELECTED_TEACHERS').'::'.JText::_('EMAIL_SELECTED_TEACHERS_TIP'); ?>">
		<img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/mail_replay_22x22.png" alt="<?php JText::_('EMAIL_TEACHERS_DEFAULT'); ?>"/> <?php echo JText::_( 'EMAIL_SELECTED_TEACHERS' ); ?>
	</span>
  </legend>
  <div>
   <p>
  <dd>
  [NAME] : <?php echo JText::_('TAG_NAME_TIP'); ?><br />
  [SITE_URL] : <?php echo JText::_('SITE_URL_TIP'); ?><br />
  </dd>
  </p><br />
  <label for="mail_subject"> <?php echo JText::_( 'SUBJECT' );?> </label><br/>
    <input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="80" />
  </div><br/>
  <label for="mail_subject"> <?php echo JText::_( 'TEACHER_USER_FILTER' );?> </label><br/>
    <select name="mail_user">
	<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
	<?php 
	foreach($this->evalUsers as $eval_users) { 
		echo '<option value="'.$eval_users->id.'"';
			if($current_au==$eval_users->id) echo ' selected';
					echo '>'.$eval_users->name.'</option>'; 
	} 
	?>
    </select>
  <br/><br/>
    <label for="mail_body"> <?php echo JText::_( 'MESSAGE' );?> </label><br/>
    <textarea name="mail_body" id="mail_body" rows="10" cols="80" class="inputbox">[NAME], </textarea>
  <input type="submit" name="custom_email" onclick="document.pressed=this.name" value="<?php echo JText::_( 'SEND_CUSTOM_EMAIL' );?>" >
  </fieldset>
  </div>
</form>
<?php
}
?>


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

function addElement() {
  var ni = document.getElementById('myDiv');
  var numi = document.getElementById('theValue');
  var num = (document.getElementById('theValue').value -1)+ 2;
  numi.value = num;
  var newdiv = document.createElement('div');
  var divIdName = 'my'+num+'Div';
  newdiv.setAttribute('id',divIdName);
  newdiv.innerHTML = '<select name="elements[]" id="elements"><option value=""> <?php echo JText::_("PLEASE_SELECT"); ?> </option><?php $groupe =""; $i=0; foreach($this->elements as $elements) { $groupe_tmp = $elements->group_label; $length = 50; $dot_grp = strlen($groupe_tmp)>=$length?'...':''; $dot_elm = strlen($elements->element_label)>=$length?'...':''; if ($groupe != $groupe_tmp) { echo "<option class=\"emundus_search_grp\" disabled=\"disabled\" value=\"\">".substr(strtoupper($groupe_tmp), 0, $length).$dot_grp."</option>"; $groupe = $groupe_tmp; } echo "<option class=\"emundus_search_elm\" value=\"".$elements->table_name.'.'.$elements->element_name."\">".substr(htmlentities($elements->element_label, ENT_QUOTES), 0, $length).$dot_elm."</option>"; $i++; } ?></select><input name="elements_values[]" width="30" /> <a href=\'#\' onclick=\'removeElement("'+divIdName+'")\'><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag-_16x16.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>';
  ni.appendChild(newdiv);
}

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

function cptCheck() {
	var cpt = 0;
<?php foreach ($this->users as $user) { ?>
  	if(document.getElementById('cb<?php echo $user->id; ?>').checked)
		cpt++;
<?php } ?>
	return cpt;
}

function OnSubmitForm() {
	switch(document.pressed) {
		case 'affect': 
			if (document.adminForm.assessor_user.value != "" && cptCheck() > 0)
				document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=setAssessor";
			else {
				alert("<?php echo JText::_("PLEASE_SELECT_FROM_TEACHER_LIST"); ?>");
				return false;
			}
		break;
		case 'unaffect': 
			if (document.adminForm.assessor_user.value != "" && cptCheck() > 0) {
				if (confirm("<?php echo JText::_("CONFIRM_UNAFFECT_TEACHERS"); ?>")) {
					document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=unsetAssessor";
				} else 
					return false;
			} else {
					alert("<?php echo JText::_("PLEASE_SELECT_FROM_TEACHER_LIST"); ?>");
					return false;
			}
		break;
		case 'custom_email': 
			if (document.adminForm.mail_user.value != "") {
				document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=customEmail";
			} else {
				alert("<?php echo JText::_("PLEASE_SELECT_FROM_TEACHER_LIST"); ?>");
				return false;
			}
		break;
		case 'default_email': 
			if (confirm("<?php echo JText::_("CONFIRM_DEFAULT_EMAIL"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=defaultEmail";
		 	} else 
		 		return false;
		break;
		case 'registration': 
			if (cptCheck() > 0) {
				if (confirm("<?php echo JText::_("CONFIRM_STUDENT_REGISTRATION"); ?>")) {
					document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=registration";
				} else 
					return false;
			} else return false;
		break;
		case 'unregistration': 
			if (cptCheck() > 0) {
				if (confirm("<?php echo JText::_("CONFIRM_STUDENT_UNREGISTRATION"); ?>")) {
					document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=unregistration";
				} else 
					return false;
			} else return false;
		break;
		case 'set_applicant': 
			if (cptCheck() > 0) {
				if (confirm("<?php echo JText::_("CONFIRM_SET_APPLICANT"); ?>")) {
					document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=setApplicant";
				} else 
					return false;
			} else return false;
		break;
		case 'setPID': 
			if (cptCheck() > 0) {
				if (confirm("<?php echo JText::_("CONFIRM_SET_PID"); ?>")) {
					document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=setPID";
				} else 
					return false;
			} else return false;
		break;
		case 'search_button': 
			document.adminForm.submit();
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=clear";
		break;
		case 'export_to_xls': 
			document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=export_to_xls";
		break;
		case 'export_all_to_xls': 
			document.adminForm.action ="index.php?option=com_emundus&controller=learningagreementreferent&task=export_all_to_xls";
		break;
		case 'export_zip': 
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=export_zip";
		break;
		default: return false;
	}
	return true;
}
</SCRIPT>