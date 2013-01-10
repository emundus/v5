<?php 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_emundus/style/' );
defined('_JEXEC') or die('Restricted access'); 
$document   =& JFactory::getDocument();
$current_user = JFactory::getUser();
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_g = JRequest::getVar('groups', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
$current_ap = JRequest::getVar('profil', null, 'POST', 'none',0);
$current_au = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$db =& JFactory::getDBO();
?>

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<?php 
// Starting a session.
$session =& JFactory::getSession();
// Gettig the orderid if there is one.
$s_elements = $session->get('s_elements');
$s_elements_values = $session->get('s_elements_values');

if (count($search)==0 && isset($s_elements)) {
	$search = $s_elements;
	$search_values = $s_elements_values;
}
?>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="groups"/>
<input type="hidden" name="limitstart" value="<?php echo $ls; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<fieldset>
<legend>
<img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?>
</legend>

<table width="100%">
 <tr align="left" valign="bottom">
  <th align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
  <th align="left"><?php echo JText::_('PROFILE'); ?></th>
  <th align="left"><?php echo JText::_('ASSESSOR_GROUP_FILTER'); ?></th>
  <th align="left"><?php echo JText::_('ASSESSOR_USER_FILTER'); ?></th>
    <th align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
 </tr>
 <tr align="left" valign="bottom">
	<td><input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/></td>
    <td><select name="profile" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL_PROFILES'); ?> </option>
	<?php 
	foreach($this->applicantsProfiles as $applicantsProfiles) { 
		echo '<option value="'.$applicantsProfiles->id.'"';
			if($current_p==$applicantsProfiles->id) echo ' selected';
					echo '>'.$applicantsProfiles->label.'</option>'; 
	} 
	?>  
    </select>
    </td>
   <td>
  <select name="groups" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL'); ?> </option>
	<?php 
	foreach($this->groups as $groups) { 
		echo '<option value="'.$groups->id.'"';
			if($current_g==$groups->id) echo ' selected';
					echo '>'.$groups->label.'</option>'; 
	} 
	?>
  </select>
  </td>
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
<table width="100%" align="left">
 <tr>
  <th align="left" valign="bottom">
  	<?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('ELEMENT_FILTER').'</span>'; ?>
    <input type="hidden" value="0" id="theValue" />
  	<a href="javascript:;" onclick="addElement();"><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag+_16x16.png" alt="<?php JText::_('ADD_SEARCH_ELEMENT'); ?>"/></a>
  </th>
 </tr>
 <tr>
  <td align="left" valign="bottom">
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
  <a href="#" onclick="removeElement('<?php echo 'filter'.$i; ?>')"><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag-_16x16.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>
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
<?php
if(!empty($this->users)) {
?>

<?php 
	if($tmpl == 'component') {
			echo '<div><h3><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('VALIDATED_APPLICANTS_LIST').'"/>'.JText::_('VALIDATED_APPLICANTS_LIST').'</h3>';
			$document =& JFactory::getDocument();
			$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundusraw.css" );
	}else{
			echo '<fieldset><legend><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('VALIDATED_APPLICANTS_LIST').'"/>'.JText::_('VALIDATED_APPLICANTS_LIST').'</legend>';
	}
?>

<table id="userlist" width="100%">
	<thead>
	<tr>
	    <td align="center" colspan="6">
	    	<?php echo $this->pagination->getResultsCounter(); ?>
	    </td>
    </tr>
	<tr>
		<th>
        <input type="checkbox" id="checkall" onClick="javascript:check_all()"/>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NATIONALITY'), 'nationality', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICANT_FOR'), 'profile', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('SEND_ON'), 'time_date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JText::_('ASSESSOR'); ?></th>
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
        <div class="emundusraw">
		<?php 
			echo ++$i+$ls;
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
			echo '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-innerWidth*0.2,y:window.
innerHeight-40}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=application_form&sid='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/images/emundus/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a> ';
			echo '</span>#'.$user->id.'</div>';
		?>
        </td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong> '.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
		</td>
      <td><?php echo $user->nationality; ?></td>
      <td><?php 
	   $query = 'SELECT esp.id, esp.label
					FROM #__emundus_users_profiles AS eup
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id=eup.profile_id
					WHERE eup.user_id = '.$user->id.'
					ORDER BY eup.id';
		$db->setQuery( $query );
		$profiles=$db->loadObjectList();
		echo '<ul>';
		foreach($profiles as $p){
			if ($p->id == $user->profile)
				echo '<li class="bold">'.$p->label.' ('.JText::_('FIRST_CHOICE').')</li>';
			else
				echo '<li>'.$p->label.'</li>';
		}
		echo '</ul>';
	   ?></td>
		<td><?php echo strftime(JText::_('DATE_FORMAT_LC3'), strtotime($user->time_date)); ?></td>
		<td>
		<?php 
		$query = 'SELECT ege.id, ege.group_id, ege.user_id  
					FROM #__emundus_groups_eval ege  
					WHERE ege.applicant_id='.$user->id;
		$db->setQuery( $query );
		$assessors = $db->loadObjectList('id');
		
		//print_r($assessors);
		//$allowed = array("Super Administrator", "Administrator", "Editor");
		foreach($assessors as $ass) {
			if(!empty($ass->group_id) && isset($ass->group_id)) {
				$uList = '<ul>';
				foreach($this->users_groups as $ug) {
					if ($ug->group_id == $ass->group_id) {
						$usr =& JUser::getInstance($ug->user_id);
						$uList .= '<li>'.$usr->name.'</li>';
					}
				}
				$uList .= '</ul>';
				
				if(EmundusHelperAccess::isAdministrator($user->get('id')) ||  EmundusHelperAccess::isCoordinator($user->get('id')) ||  EmundusHelperAccess::isPartner($user->get('id'))) { 
					$img = '<span class="editlinktip hasTip" title="'.JText::_('DELETE_ASSESSOR').' : '.$this->groups[$ass->group_id]->label.'::'.JText::_('DELETE_ASSESSOR_TXT').'"><a href="index.php?option=com_emundus&controller=groups&task=delassessor&aid='.$user->id.'&pid='.$ass->group_id.'&uid='.$ass->user_id.'&limitstart='.$ls.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'"><img src="'.JURI::Base().'images/emundus/icones/clear_left_16x16.png" alt="'.JText::_('DEL_ASSESSOR').'" align="absbottom" /></a></span> ';
				} else $img="";
				echo '<span class="editlinktip hasTip" title="'.JText::_('GROUP_MEMBERS').'::'.$uList.'">'.$this->groups[$ass->group_id]->label.'</span> '.$img.'<br />'; 
				unset($uList);
			} elseif(!empty($ass->user_id) && isset($ass->user_id)) {
				$img = '<span class="editlinktip hasTip" title="'.JText::_('DELETE_ASSESSOR').' : '.$this->evalUsers[$ass->user_id]->name.'::'.JText::_('DELETE_ASSESSOR_TXT').'"><a href="index.php?option=com_emundus&controller=groups&task=delassessor&aid='.$user->id.'&pid='.$ass->group_id.'&uid='.$ass->user_id.'&limitstart='.$ls.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'"><img src="'.JURI::Base().'images/emundus/icones/clear_left_16x16.png" alt="'.JText::_('DEL_ASSESSOR').'" align="absbottom" /></a></span> ';
				echo $this->evalUsers[$ass->user_id]->name.' '.$img.'<br />';	
			}
		}
		if (count($assessors)==0)
			echo '<span class="hasTip" title="'.JText::_('ASSESSOR_FILTER_ALERT').'"><font color="red">'.JText::_('NO_ASSESSOR').'</font></span>';
		?>
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
if(EmundusHelperAccess::isAdministrator($user->get('id')) ||  EmundusHelperAccess::isCoordinator($user->get('id'))) { 
?>

<fieldset><legend><img src="<?php JURI::Base(); ?>images/emundus/icones/kbackgammon_engine_22x22.png" alt="<?php JText::_('BATCH'); ?>"/> <?php echo JText::_('AFFECT_TO_ASSESSORS'); ?></legend>
<table width="100%">
 <tr>
  <th><?php echo JText::_('ASSESSOR_GROUP_FILTER'); ?></th>
  <th><?php echo JText::_('ASSESSOR_USER_FILTER'); ?></th>
  <th>&nbsp;</th>
 </tr>
 <tr>
   <td>
  <select name="assessor_group">
	<option value=""> <?php echo JText::_('NONE'); ?> </option>
	<?php 
	foreach($this->groups as $groups) { 
		echo '<option value="'.$groups->id.'"';
			if($current_ap==$groups->id) echo ' selected';
					echo '>'.$groups->label.'</option>'; 
	} 
	?>
  </select>
  </td>
  <td>
  <select name="assessor_user">
	<option value=""> <?php echo JText::_('NONE'); ?> </option>
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
  	<input type="submit" name="affect" class="green" onclick="document.pressed=this.name" value="<?php echo JText::_('AFFECT_SELECTED'); ?>" />
    <input type="submit" name="unaffect" class="red" onclick="document.pressed=this.name" value="<?php echo JText::_('UNAFFECT_SELECTED'); ?>" />
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
if(EmundusHelperAccess::isAdministrator($user->get('id')) ||  EmundusHelperAccess::isCoordinator($user->get('id'))) { 
?>
  <fieldset>
  <legend> 
  	<span class="editlinktip hasTip" title="<?php echo JText::_('EMAIL_ASSESSORS_DEFAULT').'::'.JText::_('EMAIL_ASSESSORS_DEFAULT_TIP'); ?>">
		<img src="<?php JURI::Base(); ?>images/emundus/icones/mail_replayall_22x22.png" alt="<?php JText::_('EMAIL_ASSESSORS_DEFAULT'); ?>"/> <?php echo JText::_( 'EMAIL_ASSESSORS_DEFAULT' ); ?>
	</span>
  </legend>
  <input type="submit" class="blue" name="default_email" onclick="document.pressed=this.name" value="<?php echo JText::_( 'SEND_DEFAULT_EMAIL' );?>" >
  </fieldset>
  
  <fieldset>
  <legend> 
  	<span class="editlinktip hasTip" title="<?php echo JText::_('EMAIL_SELECTED_ASSESSORS').'::'.JText::_('EMAIL_SELECTED_ASSESSORS_TIP'); ?>">
		<img src="<?php JURI::Base(); ?>images/emundus/icones/mail_replay_22x22.png" alt="<?php JText::_('EMAIL_ASSESSORS_DEFAULT'); ?>"/> <?php echo JText::_( 'EMAIL_SELECTED_ASSESSORS' ); ?>
	</span>
  </legend>
  <div>
   <p>
  <dd>
  [NAME] : <?php echo JText::_('TAG_NAME_TIP'); ?><br />
  [APPLICANTS_LIST] : <?php echo JText::_('TAG_APPLICANTS_LIST_TIP'); ?><br />
  [SITE_URL] : <?php echo JText::_('SITE_URL_TIP'); ?><br />
  [EVAL_CRITERIAS] : <?php echo JText::_('EVAL_CRITERIAS_TIP'); ?><br />
  [EVAL_PERIOD] : <?php echo JText::_('EVAL_PERIOD_TIP'); ?><br />
  </dd>
  </p><br />
  <label for="mail_subject"> <?php echo JText::_( 'SUBJECT' );?> </label><br/>
    <input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="80" />
  </div><br/>
  <div>
    <select name="mail_group">
        <option value=""> <?php echo JText::_('PLEASE_SELECT_GROUP'); ?> </option>
        <?php 
        foreach($this->groups as $groups) { 
            echo '<option value="'.$groups->id.'"';
                if($current_g==$groups->id) echo ' selected';
                        echo '>'.$groups->label.'</option>'; 
        } 
        ?>
    </select>
    <?php echo JText::_('OR'); ?>
    <select name="mail_user">
	<option value=""> <?php echo JText::_('PLEASE_SELECT_ASSESSOR'); ?> </option>
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
  </div>
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
if(!EmundusHelperAccess::isAdministrator($user->get('id')) OR !EmundusHelperAccess::isCoordinator($user->get('id'))) { 
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
  newdiv.innerHTML = '<select name="elements[]" id="elements"><option value=""> <?php echo JText::_("PLEASE_SELECT"); ?> </option><?php $groupe =""; $i=0; foreach($this->elements as $elements) { $groupe_tmp = $elements->group_label; $length = 50; $dot_grp = strlen($groupe_tmp)>=$length?'...':''; $dot_elm = strlen($elements->element_label)>=$length?'...':''; if ($groupe != $groupe_tmp) { echo "<option class=\"emundus_search_grp\" disabled=\"disabled\" value=\"\">".substr(strtoupper($groupe_tmp), 0, $length).$dot_grp."</option>"; $groupe = $groupe_tmp; } echo "<option class=\"emundus_search_elm\" value=\"".$elements->table_name.'.'.$elements->element_name."\">".substr(htmlentities($elements->element_label, ENT_QUOTES), 0, $length).$dot_elm."</option>"; $i++; } ?></select><input name="elements_values[]" width="30" /> <a href=\'#\' onclick=\'removeElement("'+divIdName+'")\'><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag-_16x16.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>';
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


function OnSubmitForm() {
	//alert(document.pressed);
	
	switch(document.pressed) {
		case 'affect': 
			document.adminForm.action ="index.php?option=com_emundus&controller=groups&task=setAssessor";
		break;
		case 'unaffect': 
			if (confirm("<?php echo JText::_("CONFIRM_UNAFFECT_ASSESSORS"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=groups&task=unsetAssessor";
		 	} else 
		 		return false;
		break;
		case 'custom_email': 
			document.adminForm.action ="index.php?option=com_emundus&controller=groups&task=customEmail";
		break;
		case 'default_email': 
			if (confirm("<?php echo JText::_("CONFIRM_DEFAULT_EMAIL"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=groups&task=defaultEmail";
		 	} else 
		 		return false;
		break;
		case 'search_button': 
			document.adminForm.submit();
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&controller=groups&task=clear";
		break;
		default: return false;
	}
	return true;
}
</SCRIPT>