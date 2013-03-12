<?php 
defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::_( 'behavior.mootools' );
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
$eMConfig =& JComponentHelper::getParams('com_emundus');

/*$final_grade_values = $eMConfig->get('final_grade_values');
$final_grade_labels = $eMConfig->get('final_grade_labels');
*/

$current_user = JFactory::getUser();
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
$current_ap = JRequest::getVar('profil', null, 'POST', 'none',0);
$current_au = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);

// Starting a session.
$session =& JFactory::getSession();
// Gettig the orderid if there is one.
$s_elements = $session->get('s_elements');
$s_elements_values = $session->get('s_elements_values');

if (count($search)==0) {
	$search = $s_elements;
	$search_values = $s_elements_values;
}

$db =& JFactory::getDBO();
$query = 'SELECT sub_values, sub_labels FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
$db->setQuery( $query );
$result = $db->loadRowList();
$sub_values = explode('|', $result[0][0]);
foreach($sub_values as $sv)
	$p_grade[]="/".$sv."/";
$grade = explode('|', $result[0][1]);
?>

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component&Itemid='.$itemid; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>

<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="railwayyard"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />

<fieldset><legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?></legend>
<table width="100%">
    <tr align="left">
        <th align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
        <th align="left"><?php echo JText::_('PROFILE'); ?></th>
    <th align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
    </tr>
    <tr>
        <td>
        	<input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/>
        </td>
        <td>
            <select name="profile" onChange="javascript:submit()">
            <option value=""> <?php echo JText::_('ALL'); ?> </option>
            <?php 
			$profil_list=$this->profiles;
			if(count($profil_list)>0){
                foreach($this->profiles as $applicantsProfiles) { 
                    echo '<option value="'.$applicantsProfiles->id.'"';
                    if($current_p==$applicantsProfiles->id) echo ' selected';
                        echo '>'.$applicantsProfiles->label.'</option>'; 
                } 
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
<table width="100%">
 <tr align="left">
  <th align="left">
  	<?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('ELEMENT_FILTER').'</span>'; ?>
    <input type="hidden" value="0" id="theValue" />
  	<a href="javascript:;" onclick="addElement();"><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag+_16x16.png" alt="<?php JText::_('ADD_SEARCH_ELEMENT'); ?>"/></a>
  </th>
 </tr>
 <tr align="left">
  <td align="left">
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

<?php
if(!empty($this->users)) {
	if($tmpl == 'component') {
			echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('').'"/>'.JText::_('').'</h3>';
			$document =& JFactory::getDocument();
			$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
	}else{
			echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('').'"/>'.JText::_('').'</legend>';
	}
?>

<table id="userlist" width="100%">
	<thead>
	<tr>
	    <td align="center" colspan="15">
	    	<?php echo $this->pagination->getResultsCounter(); ?>
	    </td>
    </tr>
	<tr>
		<th>
        <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICANT_NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
<?php
foreach ($this->profiles as $p) {
	echo '<th>'.$p->label.'</th>';
}
?>
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
			echo ++$i+$limitstart;
			if($user->user != 62)  ?> <input id="cb<?php echo $user->user; ?>" type="checkbox" name="ud[]" value="<?php echo $user->user; ?>"/>
        <?php
			echo '<span class="editlinktip hasTip" title="'.JText::_('MAIL_TO').'::'.$user->email.'">';
			if ($user->gender == 'male')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_male.png" width="22" height="22" align="bottom" /></a> ';
			elseif ($user->gender == 'female')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/user_female.png" width="22" height="22" align="bottom" /></a> ';
			else
				echo '<a href="mailto:'.$user->email.'">'.$user->gender.'</a> ';
			echo '</span>';
			echo '<span class="editlinktip hasTip" title="'.JText::_('APPLICATION_FORM').'::'.JText::_('POPUP_APPLICATION_FORM_DETAILS').'">';
			echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=application_form&sid='. $user->user.'&tmpl=component&Itemid='.$itemid.'" target="_blank" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a> ';
			echo '</span>#'.$user->user.'</div>';
		?>
        </td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong><br />'.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
		</td>
<?php 
		
		$query = 'SELECT profile_id FROM #__emundus_users_profiles WHERE user_id='.$user->user;
		$db->setQuery($query);
		$pl = $db->loadObjectList('profile_id');
		foreach ($this->profiles as $p) {
			//if(isset($pl[$p->id])) die(print_r($pl[$p->id]->profile_id));
			$class = $user->profile==$p->id?'class="green"':'';
			$state = ($user->final_grade>0 && $user->result_for==$p->id)?'disabled="disabled"':'';
			echo '<td '.$class.'"><input name="checkbox" type="checkbox" '.$state.' id="'.$user->user.'-'.$p->id.'" ';
			//if (@$pl[($p->id)]->profile_id>0){
			if (isset($pl[$p->id]->profile_id)){
				echo 'checked="checked"';
			} 
			echo ' />';
			echo $user->result_for==$p->id?@preg_replace($p_grade, $grade, JText::_($user->final_grade)):'';
			echo '<div id="a-'.$user->user.'-'.$p->id.'"></div></td>';
			$url = 'index.php?option=com_emundus&controller=railwayyard&format=raw&task=set_profile&sid='.$user->user.'&pid='.$p->id.'&set=';
?>
<script>
 window.addEvent( 'domready', function() {
	$('<?php echo $user->user.'-'.$p->id; ?>').addEvent( 'click', function() {
	 	$('a-<?php echo $user->user.'-'.$p->id; ?>').empty().addClass('ajax-loading');
			var cb = document.getElementById("<?php echo $user->user.'-'.$p->id; ?>");
			if(cb.checked)
				set=1;
			else
				set=0;
			var a = new Ajax( '<?php echo $url; ?>'+set, {
				method: 'get',
				update: $('a-<?php echo $user->user.'-'.$p->id; ?>')
			}).request();
		}); 
	});
</script>
<?php } ?>
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

<?php } else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php } ?>

</form>

<script>
function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->user; ?>').checked = checked;
<?php } ?>
}

<?php 
if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id) && !EmundusHelperAccess::isPartner($user->id)) { 
?>
function hidden_all() {
  document.getElementById('checkall').style.visibility='hidden';
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->user; ?>').style.visibility='hidden';
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

function OnSubmitForm() {
	var button_name=document.pressed.split("|");
	// alert(button_name[0]);
	switch(button_name[0]) {
		/*case 'export_to_xls': 
			document.adminForm.action ="index.php?option=com_emundus&task=export_to_xls&view=railwayyard&Itemid=<?php echo $itemid; ?>";
		break;
		case 'export_zip': 
			document.adminForm.action ="index.php?option=com_emundus&task=export_zip&view=railwayyard&Itemid=<?php echo $itemid; ?>";
		break;
		case 'custom_email': 
			document.adminForm.action ="index.php?option=com_emundus&task=customEmail&Itemid=<?php echo $itemid; ?>";
		break;*/
		case 'search_button': 
			document.adminForm.action ="index.php?option=com_emundus&view=railwayyard&Itemid=<?php echo $itemid; ?>";
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&task=clear&Itemid=<?php echo $itemid; ?>";
		break;
		default: return false;
	}
	return true;
} 
</script>