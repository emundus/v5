<?php 
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php'); 
$edit = JRequest::getVar('edit', 0, 'GET', 'none', 0);
$spam_suspect = JRequest::getVar('spam_suspect', null, 'POST', 'none',0);
$newsletter = JRequest::getVar('newsletter', null, 'POST', 'none',0);
$current_p = JRequest::getVar('rowid', null, 'POST', 'none',0);
$current_fg = JRequest::getVar('final_grade', null, 'POST', 'none',0);
$current_l = JRequest::getVar('s', null, 'POST', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);
$current_campaigns = JRequest::getVar('campaigns', null, 'POST', 'none',0);
$current_groupEval = JRequest::getVar('groups_eval', null, 'POST', 'none',0);

$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$current_user = JFactory::getUser();

/*$state = $this->get( 'state' );
if(empty($schoolyears)) 
	$schoolyears = $this->state_schoolyears;
if(empty($current_l))
	$current_l = $this->state_current_l;
if(empty($current_campaigns))
	$current_campaigns = $this->state_current_campaigns;
if(empty($current_groupEval))
	$current_groupEval = $this->state_current_groupEval;
if(empty($spam_suspect))
	$spam_suspect = $this->state_spam_suspect;
if(empty($newsletter))
	$newsletter = $this->state_newsletter;
if(empty($current_p))
	$current_p = $this->state_current_p;
if(empty($current_fg))
	$current_fg = $this->state_current_fg;
*/
if($edit!=1) {
?>
<?php 
$current_user = JFactory::getUser();
/*if($tmpl == 'component' || $current_user->get('usertype') == "Manager" || $current_user->get('usertype') == "Publisher") {
	$document = JFactory::getDocument();
	$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
}*/
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
/*if(!EmundusHelperAccess::isAdministrator($current_user->id)) {
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
}*/
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
<table width="100%" id="filters">
	<tr>  
		<th width="14%" align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
		<th width="9%" align="left"><?php echo JText::_('FINAL_GRADE'); ?></th>
		<th width="9%" align="left"><?php echo JText::_('PROFILE_FILTER'); ?></th>
		<th width="9%" align="left"><?php echo JText::_('GROUP_EVAL'); ?></th>
	</tr>
	<tr>
		<td align="left">
			<input type="text" name="s" value="<?php echo $current_l; ?>" onKeyPress="return submitenter(this,event)" />
		</td>
		<td align="left">  
			<?php 
			$db = JFactory::getDBO();
			$query = 'SELECT params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
			$db->setQuery( $query );
			$result = EmundusHelperFilters::insertValuesInQueryResult($db->loadAssocList(), array("sub_values", "sub_labels"));
			$sub_values = explode('|', $result[0]['sub_values']);
			foreach($sub_values as $sv)
				$p_grade[]="/".$sv."/";
			$grade = explode('|', $result[0]['sub_labels']);
			?>
			<select name="final_grade" onChange="javascript:submit()">
				<option value="0"> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
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
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php 
				foreach($this->profiles as $profile) { echo '<option value="'.$profile->id.'"';
					if($current_p==$profile->id) echo ' selected';
						echo '>'.$profile->label;'</option>'; 
				} 
				?>
			</select>
		</td>
		<td>
			<select name="groups_eval" onChange="clear_campaigns_filter(this); javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php 
				foreach($this->allGroupEval as $group) { 
				echo '<option value="'.$group->id.'"';
					if($current_groupEval==$group->id) echo ' selected';
						echo '>'.$group->label.'</option>'; 
				}
				?>
			</select> 
		</td>
	</tr>
	<tr>
		<th width="5%" align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
		<th width="5%" align="left"><?php echo JText::_('CAMPAIGNS'); ?></th>
		<th width="6%" align="left"><ul><?php 
			echo '<span class="editlinktip hasTip" title="'.JText::_('SPAM_SUSPECT').'::'.JText::_('SPAM_SUSPECT_DETAILS').'">';
			echo JText::_('SPAM_SUSPECT'); 
			echo '</span>';
			?>
			<span style="margin-left:10px;"><?php echo JText::_('NEWSLETTER'); ?></span>
		</th>
		<th width="57%" align="left"></th>
		<th width="57%" align="left">&nbsp;</th>
		<th width="57%" align="left">&nbsp;</th>
	</tr>
	<tr>
		<td>
			<select name="schoolyears" onChange="clear_groupsEval_filter(this); javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
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
			<select name="campaigns" onChange="clear_groupsEval_filter(this);javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php var_dump($this->current_campaigns);
				foreach($this->current_campaigns as $campaign) { 
				  echo '<option value="'.$campaign->id.'"';
					if($current_campaigns==$campaign->id) echo ' selected';
						echo '>'.$campaign->label.'</option>'; 
				}
				?>
			</select> 
		</td>
		<td>
			<input style="margin-left:40px" name="spam_suspect" type="checkbox" value="1" <?php echo $spam_suspect==1?'checked=checked':''; ?> />
			<input style="margin-left:70px" name="newsletter" type="checkbox" value="1" <?php echo $newsletter==1?'checked=checked':''; ?> />
		</td>
		<td>
			<input type="submit" name="search" onclick="document.pressed=this.name" value="<?php echo JText::_('SEARCH_BTN'); ?>"/>
			<input style="margin-left:10px" type="submit" name="clear" onclick="document.pressed=this.name" value="<?php echo JText::_('CLEAR_BTN'); ?>"/>
		</td>
	</tr>
</table>
<div id="info_filters"></div>
</fieldset>

<div class="emundusraw">

<?php echo $this->export_icones; ?>

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
            <th><?php echo JHTML::_('grid.sort', JText::_('LAST_VISIT'), 'lastvisitDate', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('REGISTRED_ON'), 'registerDate', $this->lists['order_Dir'], $this->lists['order']); ?></th>
			<th><?php echo JText::_('GROUP_EVAL'); ?></th>
			<th><?php echo JText::_('CAMPAIGN'); ?></th>
			<th><?php echo JHTML::_('grid.sort', JText::_('NEWSLETTER'), 'newsletter', $this->lists['order_Dir'], $this->lists['order']); ?></th>
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
        <!--<td><?php //echo $user->schoolyear; ?></td>-->
        <td <?php echo strpos($user->lastvisitDate,"0000-00-00 00:00:00")===false?'':'class="red"'; ?>><?php echo $user->lastvisitDate; ?></td>
        <td <?php 
		$today = date("Y-m-d H:i:s");
  		$registerDate = $user->registerDate;
 		$diff = strtotime($today) - strtotime($registerDate); // Ecart en secondes
		$diff_jour = $diff/60/60/24;
		if (strpos($user->lastvisitDate,"0000-00-00 00:00:00")===0 && $user->registred_for>7) $alert=1; else $alert=0;
		echo $alert==1?'class="red"':''; ?>><?php echo $user->registerDate; ?></td>
		<td align="center"><?php
			if(gettype(@$this->groups_eval[$user->id])=="object"){
				$group_eval = $this->groups_eval[$user->id];
				echo '<a class="info-bulle" href="#">'.$group_eval->label.'<span><ul>';
				foreach($this->groupEvalWithId as $eval){
					if($eval->id==$group_eval->group_id){
						echo '<li>'.strtoupper($eval->lastname).' '.strtolower($eval->firstname).'</li>';
					}
				}
				echo '</ul></span></a>';
			}
		?>
		</td>
		<td align="center"><?php 
			foreach($this->campaigns as $campaign){ 
				if($campaign->applicant_id==$user->id){
					$campaign_end_date = JText::_('CAMPAIGN_END_DATE');
					$campaign_start_date = JText::_('CAMPAIGN_START_DATE');
					echo '<span class="editlinktip hasTip" title="'.$campaign_start_date.' : '.date("Y-m-d", strtotime($campaign->start_date)).'<BR />'.$campaign_end_date.' : '.date("Y-m-d", strtotime($campaign->end_date)).'" >
					<a href="#">'.$campaign->label.'</a></span>';
				} 
			} 
		?>
		</td>
		<td align="center"><?php  echo (json_decode($user->newsletter) == 1 ? JText::_('JYES') : JText::_('JNO')); ?>
		</td>
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
<form action="index.php?option=com_emundus&task=adduser" method="POST" name="adduser"/>
<fieldset><legend><?php echo'<img src="'.$this->baseurl.'/media/com_emundus/images/icones/add_user.png" alt="'.JText::_('ADD_USER').'" width="40" align="bottom" />'; echo JText::_('ADD_USER'); ?></legend>
<table>
	<tr><th><?php echo JText::_('FIRSTNAME_FORM'); ?></th><td><input type="text" size="30" name="firstname" value=""/></td></tr>
	<tr><th><?php echo JText::_('LASTNAME_FORM'); ?></th><td><input type="text" size="30" name="lastname" value=""/></td></tr>
	<tr><th><?php echo JText::_('LOGIN_FORM'); ?></th><td><input type="text" size="30" name="login" value=""/></td></tr>
	<tr><th><?php echo JText::_('EMAIL_FORM'); ?></th><td><input style="padding-left:20px;" type="text" size="30" name="email" value="" onChange="validateEmail(email);"/>
	</td></tr>
	
	<tr><th><?php echo JText::_('PROFILE_FORM'); ?></th><td><select name="profile" onchange="hidden_tr('show_univ','show_group', this);" >
			<?php foreach($this->profiles as $profile) { 
					echo '<option id="'.$profile->acl_aro_groups.'" value="'.$profile->id;
					echo @$this->users[0]->profile==$profile->id?'" selected':'"';
					echo '>'.$profile->label;'</option>'; 
				} ?>
				</select><?php echo'<input type="hidden" id="acl_aro_groups" name="acl_aro_groups" value="" />'; ?></td></tr>
    
	 <tr id="show_univ" style="visibility:hidden;"><th><?php echo JText::_('UNIVERSITY_FROM'); ?></th><td><select name="university_id">
			<?php echo '<option value="0">'.JText::_('PLEASE_SELECT').'</option>';
			foreach($this->universities as $university) { 
				echo '<option value="'.$university->id;
				echo @$this->users[0]->university_id==$university->id?'" selected':'"';
				echo '>'.$university->title;'</option>'; 
			} ?></select></td></tr>
     <tr id="show_group" style="visibility:hidden;">
       <th ><?php echo JText::_('GROUPS'); ?></th>
       <td>
			<?php foreach($this->groups as $groups) { 
					echo '<label><input type="checkbox" name="cb_groups[]" value="'.$groups->id.'"/>'.$groups->label.'</label><br />';
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
<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
</form>


<script type="text/javascript">
function makeArray(items)
{
	try {
		//this converts object into an array in non-ie browsers
		return Array.prototype.slice.call(items);
	}catch (ex) {
		var i = 0,
		len = items.length,
		result = Array(len);
		while(i < len) {
			result[i] = items[i];
			i++;
		}
		return result; 
	}	
}

function clear_campaigns_filter(current_select){
	var selects_object = document.getElementById('filters').getElementsByTagName('select');
	var selects = makeArray(selects_object);
	
	for(var i=0;i<selects.length;i++){
		var select = selects[i];
		var name_s = select.name;
		if(name_s=='schoolyears' || name_s=='campaigns'){
			select.value = 0;
			//window.document.getElementById('info_filters').innerHTML = "<?php echo JText::_('INFO_FILTERS'); ?>";
		}
	}
	return;
}

function clear_groupsEval_filter(current_select){
	var selects_object = document.getElementById('filters').getElementsByTagName('select');
	var selects = makeArray(selects_object);
	
	for(var i=0;i<selects.length;i++){
		var select = selects[i];
		var name_s = select.name;
		if(name_s=='groups_eval'){
			select.value = 0;
			//window.document.getElementById('info_filters').innerHTML = "<?php echo JText::_('INFO_FILTERS'); ?>";
		}
	}
	return;
}

function hidden_tr(div1,div2, profile)
{
	if (profile[profile.selectedIndex].id!=2)
	{
		document.getElementById(div1).style.visibility = "visible";
		document.getElementById(div2).style.visibility = "visible";
	}
	else
	{
		document.getElementById(div1).style.visibility = "hidden";
		document.getElementById(div2).style.visibility = "hidden";
	}
	document.getElementById('acl_aro_groups').value = profile[profile.selectedIndex].id;
	// alert(document.getElementById('acl_aro_groups').value);
}
function validateEmail(email) { 
	var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
	
	if(reg.test(email.value)){
		//document.getElementById('email_valid').innerHTML="* <?php echo JText::_('EMAIL_VALID'); ?>";
		email.style.background="url(<?php echo $this->baseurl ?>/media/com_emundus/images/icones/button_ok.png) no-repeat left";
	}else{
		//document.getElementById('email_valid').innerHTML="* <?php echo JText::_('EMAIL_NOT_VALID'); ?>";
		email.style.background="url(<?php echo $this->baseurl ?>/media/com_emundus/images/icones/button_cancel.png) no-repeat left";
	}
}

function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->id; ?>').checked = checked;
<?php } ?>
}

function is_check() {
	var cpt = 0;
	<?php foreach ($this->users as $user) { ?>
  		if(document.getElementById('cb<?php echo $user->id; ?>').checked == true) cpt++;
	<?php } ?>
	if(cpt > 0) return true;
	else return false;
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
		case 'export_zip': 
			if (is_checked()){
				document.adminForm.task.value = "export_zip";
				document.adminForm.action ="index.php?option=com_emundus&view=users&controller=users&Itemid=592&task=export_zip";
			}else alert("<?php echo JText::_('PLEASE_SELECT_APPLICANT'); ?>");
		break;
		case 'export_to_xls': 
			document.adminForm.task.value = "export_to_xls";
			document.adminForm.action ="index.php?option=com_emundus&controller=users&task=export_to_xls&Itemid=592";
		break;
		case 'setSchoolyear': 
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