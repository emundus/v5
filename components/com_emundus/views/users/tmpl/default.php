<?php 
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php'); 
$edit = JRequest::getVar('edit', 0, 'GET', 'none', 0);
$spam_suspect = JRequest::getVar('spam_suspect', null, 'POST', 'none',0);
$current_p = JRequest::getVar('rowid', null, 'POST', 'none',0);
$current_fg = JRequest::getVar('final_grade', null, 'POST', 'none',0);
$current_l = JRequest::getVar('s', null, 'POST', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$menu = &JSite::getMenu();
$menuItem = $menu->getActive();
$itemid = $menuItem->id;

if($edit!=1) {
?>
<?php 
$current_user =& JFactory::getUser();
if($tmpl == 'component' || $current_user->get('usertype') == "Manager" || $current_user->get('usertype') == "Publisher") {
	$document =& JFactory::getDocument();
	$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
}
?>
<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<?php
echo'<a class="modal" target="_self" href="'.$this->baseurl.'/index.php?option=com_emundus&view=users&layout=adduser&tmpl=component&Itemid='.$itemid.'" rel="{handler:\'iframe\',size:{x:window.innerWidth*0.4,y:window.innerHeight*0.8}}" 
>
<img src="'.$this->baseurl.'/media/com_emundus/images/icones/add_user.png" alt="'.JText::_('ADD_USER').'" width="50" align="bottom" />
</a>';
?>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<SCRIPT TYPE="text/javascript">
<!--
function submitenter(myfield, e) {
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;
	switch (keycode) {
		case 13 : myfield.form.submit(); return false;
		break;
		//case 0 : top.location.href="index.php?option=com_emundus&controller=users&task=clear"; return false;
		default:
		break;
	}
	
}
//-->
</SCRIPT>

<div class="emundusraw">
<?php
if(!EmundusHelperAccess::isAdministrator($current_user->id)) {
	if ( isset($this->schoolyear) && $this->schoolyear!='' ) {
		$url = JURI::getInstance()->toString();
		echo '
				<dl id="system-message">
				<dt class="message">Message</dt>
				<dd class="message message fade">
					<ul>
						<li>'.JText::_('CURRENT_SCHOOLYEAR').'<input type="text" name="schoolyear" value="'.$this->schoolyear.'"/><input type="submit" name="setSchoolyear" onclick="document.pressed=this.name" value="'.JText::_('UPDATE_SCHOOLYEAR').'"/></li>
					</ul>
				</dd>
				</dl>';
	} else {
			echo '
				<dl id="system-message">
				<dt class="notice">Annonce</dt>
				<dd class="notice message fade">
					<ul>
						<li>'.JText::_('SCHOOLYEAR_NOT_SET').'<input type="text" name="schoolyear" value="'.$this->schoolyear.'"/><input type="submit" name="setSchoolyear" onclick="document.pressed=this.name" value="'.JText::_('SET_SCHOOLYEAR').'"/></li>
					</ul>
				</dd>
				</dl>';
	}
}
?>
</div>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="users"/>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="url" value="<?php echo JURI::getInstance()->toString(); ?>"/>
<input type="hidden" name="s" value="<?php echo $current_l; ?>"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
<fieldset>
<legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?></legend>
<table width="100%">
<tr>  
  <th width="14%" align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
  <th width="9%" align="left"><?php echo JText::_('FINAL_GRADE'); ?></th>
  <th width="9%" align="left"><?php echo JText::_('PROFILE_FILTER'); ?></th>
  <th width="5%" align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
  <th width="6%" align="left"><?php 
  echo '<span class="editlinktip hasTip" title="'.JText::_('SPAM_SUSPECT').'::'.JText::_('SPAM_SUSPECT_DETAILS').'">';
  echo JText::_('SPAM_SUSPECT'); 
  echo '<span>'; 
  ?></th>
  <th width="57%" align="left">&nbsp;</th>
  </tr>
    <tr>
      <td align="left">
      <input type="text" name="s" value="<?php echo $current_l; ?>" onKeyPress="return submitenter(this,event)" />
      </td>
    <td align="left">  
    <?php 
	$db =& JFactory::getDBO();
	$query = 'SELECT params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
	$db->setQuery( $query );
	$result = EmundusHelperFilters::insertValuesInQueryResult($db->loadAssocList(), array("sub_values", "sub_labels"));
	$sub_values = explode('|', $result[0]['sub_values']);
	foreach($sub_values as $sv)
		$p_grade[]="/".$sv."/";
	$grade = explode('|', $result[0]['sub_labels']);
?>
    <select name="final_grade" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
	<?php  
	$groupe ="";
	
	for($i=0; $i<count($grade); $i++) { 
		$val = substr($p_grade[$i],1,1);
		echo '<option value="'.$val.'"';
			if($val == $current_fg) echo ' selected';
					echo '>'.$grade[$i].'</option>'; 
	} 
	unset($val);
	unset($i);
	?>
  </select>
</td>
    <td align="left">  
      <select name="rowid" onChange="javascript:submit()">
        <option value=""> <?php echo JText::_('ALL'); ?> </option>
        <?php 
        foreach($this->profiles as $profile) { echo '<option value="'.$profile->id.'"';
            if($current_p==$profile->id) echo ' selected';
                echo '>'.$profile->label;'</option>'; 
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
   	  <td>
      <input name="spam_suspect" type="checkbox" value="1" <?php echo $spam_suspect==1?'checked=checked':''; ?> /></td>
   	  <td><input type="submit" name="search" onclick="document.pressed=this.name" value="<?php echo JText::_('SEARCH_BTN'); ?>"/>
      <input type="submit" name="clear" onclick="document.pressed=this.name" value="<?php echo JText::_('CLEAR_BTN'); ?>"/></td>
    </tr>
<tr>
</table>
</fieldset>

<div class="emundusraw">
<?php
if(!empty($this->users)) {
	//echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile-selected_48.png" name="export_selected_to_xls" onclick="document.pressed=this.name"></span>'; 
	//echo '<span class="editlinktip hasTip" title="'.sprintf(JText::_('EXPORT_CURRENT_CAMPAIGN'), $this->schoolyear).'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile_48.png" name="export_xls" onclick="document.pressed=this.name" /></span>';
	//echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>'; 
?>
</div>

<fieldset><legend><?php if(!empty($current_p) && isset($this->profiles[$current_p]->label)) echo $this->profiles[$current_p]->label; else echo JText::_('ALL');?></legend>
<table id="userlist">
	<thead>
        <tr>
            <td align="center" colspan="10">
                <?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        <tr>
            <th align="center">
            	<input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/><?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
            </th>
            <th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('USERNAME'), 'username', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('EMAIL'), 'email', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('PROFILE'), 'profile', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('SCHOOLYEAR'), 'schoolyear', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('LAST_VISIT'), 'lastvisitDate', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('REGISTRED_ON'), 'registerDate', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th align="center" class="emundusraw"><?php echo JHTML::_('grid.sort', JText::_('ACTIVE'), 'block', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th align="center"><div class="emundusraw"><?php echo JText::_('EDIT'); ?></div></th>  
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
		<td width="150">
		<?php 
			echo ++$i+$limitstart;
			if($user->id != 62)  ?> <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]" class="emundusraw" value="<?php echo $user->id; ?>"/>
        <?php
			echo '<span class="editlinktip hasTip" title="'.JText::_('MAIL_TO').'::'.$user->email.'">';
			if (isset($user->gender)) {
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_'.$user->gender.'.png" width="22" height="22" align="bottom" /></a></span> ';
				echo '<span class="editlinktip hasTip" title="'.JText::_('APPLICATION_FORM').'::'.JText::_('POPUP_APPLICATION_FORM_DETAILS').'">';
				echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.9,y:window.getHeight()*0.9}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=application_form&sid='. $user->id.'&tmpl=component&Itemid='.$itemid.'" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a> ';
			echo '</span>';
			} else
				echo '<a href="mailto:'.$user->email.'">'.$user->gender.'</a></span> ';
			
			echo '#'.$user->id;
		?>
        </td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong> '.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
        </td>
		<td><?php echo $user->username; ?></td>
		<td><?php echo $user->email; ?></td>
		<td><div class="emundusprofile<?php echo $user->profile; ?>"><?php if(!empty($user->profile) && isset($this->profiles[$user->profile]->label)) echo $this->profiles[$user->profile]->label; else echo '<span class="hasTip" title="'.JText::_('USER_PROFILE_ALERT').'"><font color="red">'.JText::_('NO_PROFILE').'</font></span>'; ?></div></td>
        <td><?php echo $user->schoolyear; ?></td>
        <td <?php echo strpos($user->lastvisitDate,"0000-00-00 00:00:00")===false?'':'class="red"'; ?>><?php echo $user->lastvisitDate; ?></td>
        <td <?php 
		$today = date("Y-m-d H:i:s");
  		$registerDate = $user->registerDate;
 		$diff = strtotime($today) - strtotime($registerDate); // Ecart en secondes
		$diff_jour = $diff/60/60/24;
		if (strpos($user->lastvisitDate,"0000-00-00 00:00:00")===0 && $user->registred_for>7) $alert=1; else $alert=0;
		echo $alert==1?'class="red"':''; ?>><?php echo $user->registerDate; ?></td>
		<td align="center" class="emundusraw"><?php if($user->id != 62) {?><a href="index.php?option=com_emundus&task=<?php echo $user->block>0?'unblockuser':'blockuser'; ?>&uid=<?php echo $user->id; ?>&Itemid=<?php echo $itemid; ?>"><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/<?php echo $user->block>0?'publish_x.png':'tick.png' ?>" alt="<?php echo $user->block>0?JText::_('UNBLOCK_USER'):JText::_('BLOCK_USER'); ?>"/></a><?php } ?></td>
		<td align="center">
        	<div class="emundusraw">
<?php 
        	//if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) { 
?>
				<a class="modal" target="_self" href="index.php?option=com_emundus&view=users&edit=1&rowid=<?php echo $user->id; ?>&tmpl=component&Itemid=<?php echo $itemid; ?>" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9}}"><?php echo JText::_('EDIT'); ?></a><?php 
				 //} ?>
        	</div>
        </td>
  	</tr>
<?php } ?>
  	<tr>
        <td height="1975" colspan="8" align="left">
            <div class="emundusraw">
                <input type="submit" name="delusers" onclick="document.pressed=this.name" value="<?php echo JText::_('DELETE_SELECTED'); ?>" class="emundusdelete" onmouseover="this.className='emundusdelete btnhov'" onmouseout="this.className='emundusdelete'" />
           <?php if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) { ?> |        
                <input type="submit" name="delincomplete" onclick="document.pressed=this.name" value="<?php echo JText::_('DELETE_INCOMPLETE'); ?>" class="emundusdelete" onmouseover="this.className='emundusdelete btnhov'" onmouseout="this.className='emundusdelete'" /> 
				<input type="submit" name="delnonevaluated" onclick="document.pressed=this.name" value="<?php echo JText::_('DELETE_NON_EVALUATED'); ?>" class="emundusdelete" onmouseover="this.className='emundusdelete btnhov'" onmouseout="this.className='emundusdelete'" />
                <input type="submit" name="delrefused" onclick="document.pressed=this.name" value="<?php echo JText::_('DELETE_REFUSED'); ?>" class="emundusdelete" onmouseover="this.className='emundusdelete btnhov'" onmouseout="this.className='emundusdelete'" />
              
            <?php } ?>
            </div>
        </td>
    </tr>
    </tbody>
</table>
</fieldset>
<div class="emundusraw"><?php 
	if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
	?><fieldset>
		  <legend> 
			<span class="editlinktip hasTip" title="<?php echo JText::_('ARCHIVE').'::'.JText::_('ARCHIVE_TIP'); ?>">
				<img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/kbackgammon_engine_22x22.png" alt="<?php JText::_('BATCH'); ?>"/> <?php echo JText::_( 'ARCHIVE' ); ?>
			</span>
		  </legend>
		  <input type="submit" name="archive" onclick="document.pressed=this.name" value="<?php echo JText::_( 'ARCHIVE_SELECTED_USERS' );?>" />
	</fieldset>
<?php } ?>
</div>
<?php } else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php } } ?>
</form>
<div class="emundusraw">
<form action="index.php?option=com_emundus&task=<?php echo $edit==1?'edit':'add'; ?>user&Itemid=<?php echo $itemid; ?>" method="POST" name="adduser"/>
<fieldset><legend><?php echo $edit==1?JText::_('EDIT_USER'):JText::_('ADD_USER'); ?></legend>
<input type="hidden" name="user_id" value="<?php if($edit==1) echo $this->users[0]->id; ?>"/>
<table>
	<tr><th><?php echo JText::_('FIRSTNAME_FORM'); ?></th><td><input type="text" size="30" name="firstname" value="<?php if($edit==1) echo $this->users[0]->firstname; ?>"/></td></tr>
	<tr><th><?php echo JText::_('LASTNAME_FORM'); ?></th><td><input type="text" size="30" name="lastname" value="<?php if($edit==1) echo $this->users[0]->lastname; ?>"/></td></tr>
	<tr><th><?php echo JText::_('LOGIN_FORM'); ?></th><td><input type="text" size="30" name="login" value="<?php if($edit==1) echo $this->users[0]->username; ?>"/></td></tr>
	<tr><th><?php echo JText::_('EMAIL_FORM'); ?></th><td><input type="text" size="30" name="email" value="<?php if($edit==1) echo $this->users[0]->email; ?>"/></td></tr>
    <tr><th><?php echo JText::_('SCHOOLYEAR'); ?></th><td><input type="text" size="30" name="schoolyear" value="<?php if($edit==1) echo $this->users[0]->schoolyear; ?>"/></td></tr>
	
	<tr><th><?php echo JText::_('PROFILE_FORM'); ?></th><td><select name="profile">
			<?php foreach($this->edit_profiles as $profile) { 
					echo '<option value="'.$profile->id;
					echo @$this->users[0]->profile==$profile->id?'" selected':'"';
					echo '>'.$profile->label;'</option>'; 
				} ?></select></td></tr>
     <!-- <tr>
       <th><?php /*echo JText::_('OTHER_PROFILES'); ?></th><td><hr />
			<?php 
			foreach($this->edit_profiles as $profile) { 
					echo '<label><input type="checkbox" name="cb_profiles[]" value="'.$profile->id.'" ';
					if($edit==1) {
						foreach($this->user_profiles as $user_profiles) {
							if($user_profiles->profile_id==$profile->id)
								echo ' checked="checked"';
						}
					}
					echo ' />'.$profile->label.'</label><br />';
				}
			*/ ?></td></tr>-->
	 <tr><th><?php echo JText::_('UNIVERSITY_FROM'); ?></th><td><select name="university_id">
			<?php echo '<option value="0">'.JText::_('PLEASE_SELECT').'</option>';
			foreach($this->universities as $university) { 
				echo '<option value="'.$university->id;
				echo @$this->users[0]->university_id==$university->id?'" selected':'"';
				echo '>'.$university->title;'</option>'; 
			} ?></select></td></tr>
     <tr>
       <th><?php echo JText::_('GROUPS'); ?></th>
       <td>
			<?php foreach($this->groups as $groups) { 
					echo '<label><input type="checkbox" name="cb_groups[]" value="'.$groups->id.'" ';
					if($edit==1) {
						foreach($this->users_groups as $users_groups) {
							if($users_groups->user_id==$this->users[0]->id && $users_groups->group_id==$groups->id)
								echo ' checked="checked"';
						}
					}
					echo ' />'.$groups->label.'</label><br />';
				} 
			?>
        </td>
    </tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="<?php echo JText::_('SAVE'); ?>"/>
		</td>
	</tr>
</table>
</fieldset>
</form>
</div>


<script>
function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->id; ?>').checked = checked;
<?php } ?>
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
			document.adminForm.task.value = "export_to_xls";
			document.adminForm.action ="index.php?option=com_emundus&controller=users&task=export_to_xls";
		break;
		case 'export_selected_to_xls': 
			document.adminForm.task.value = "export_selected_to_xls";
			document.adminForm.action ="index.php?option=com_emundus&controller=users&task=export_selected_to_xls";
		break;
		case 'export_zip': 
			document.adminForm.task.value = "export_zip";
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=export_zip";
		break;
		case "setSchoolyear": 
			document.adminForm.task.value = "setSchoolyear";
			document.adminForm.action ="index.php?option=com_emundus&controller=users&task=setSchoolyear";
		break;
		case 'archive': 
			document.adminForm.task.value = "archive";
			document.adminForm.action ="index.php?option=com_emundus&controller=users&task=archive";
		break;
		case 'delusers': 
			document.adminForm.task.value = "delusers";
			if (confirm("<?php echo JText::_("CONFIRM_DELETE"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&task=delusers";
		 	} else 
		 		return false;
		break;
		case 'delrefused': 
			document.adminForm.task.value = "delrefused";
			if (confirm("<?php echo JText::_("CONFIRM_DELETE"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&task=delrefused";
		 	} else 
		 		return false;
		break;
		case 'delincomplete': 
			document.adminForm.task.value = "delincomplete";
			if (confirm("<?php echo JText::_("CONFIRM_INCOMPLETE"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&task=delincomplete";
		 	} else 
		 		return false;
		break;
		case 'delnonevaluated': 
			document.adminForm.task.value = "delnonevaluated";
			if (confirm("<?php echo JText::_("CONFIRM_NON_EVALUATED"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&task=delnonevaluated";
		 	} else 
		 		return false;
		break;
		case 'search': 
			document.adminForm.task.value = "";
			document.adminForm.action ="index.php?option=com_emundus&view=users&Itemid=<?php echo $itemid; ?>";
		break;
		case 'clear': 
			document.adminForm.task.value = "clear";
			document.adminForm.action ="index.php?option=com_emundus&controller=users&task=clear";
		break;
		default: return false;
	}
	return true;
}
</script>