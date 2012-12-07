<?php 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');

JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_emundus/style/' );

$document   =& JFactory::getDocument();

defined('_JEXEC') or die('Restricted access'); 
$current_user = JFactory::getUser();
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
$current_au = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$validation_list = JRequest::getVar('validation_list', null, 'GET', 'none',0);
$v = JRequest::getVar('view', null, 'GET', 'none',0);
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

$db = JFactory::getDBO();
//$document->setTitle( JText::_( 'ADMINISTRATIVE_VALIDATION' ) );
?>

<link rel="stylesheet" type="text/css" href= "<?php echo JURI::Base().'/images/emundus/menu_style.css'; ?>" media="screen"/>

<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="menu/includes/ie6.css" media="screen"/>
<![endif]-->

<!-- <div class="componentheading"><?php echo JText::_( 'ADMINISTRATIVE_VALIDATION' ); ?></div> -->

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="check"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

<fieldset><legend><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?></legend>

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
        <option value=""> <?php echo JText::_('ALL'); ?> </option><?php 
            foreach($this->applicantsProfiles as $applicantsProfiles) { 
                echo '<option value="'.$applicantsProfiles->id.'"';
                if($current_p==$applicantsProfiles->id) echo ' selected';
                echo '>'.$applicantsProfiles->label.'</option>'; 
            } ?> 
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
  	<a href="javascript:;" onclick="addElement();"><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag+_16x16.png" alt="<?php JText::_('ADD_SEARCH_ELEMENT'); ?>"/></a>
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
<div class="emundusraw">
<?php
if(!empty($this->users)) {
 if($current_user->profile!=16){
	/*echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/XLSFile-selected_48.png" name="export_complete" onclick="document.pressed=this.name"></span>'; 	
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_COMPLETED_TO_XLS').'"><a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=export_select_columns&tmpl=component&as=1&v='.$v.'" target="_self" class="modal"><img src="'.$this->baseurl.'/images/emundus/icones/XLSFile_48.png" name="export_complete_to_xls" onclick="document.pressed=this.name" /></a></span>'; 
	//echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_INCOMPLETED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/XLSFile-incomplete_48.png" name="export_incomplete_to_xls" onclick="document.pressed=this.name" /></span>'; */
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>'; 
	echo '<span class="editlinktip hasTip" title="'.JText::_('SEND_ELEMENTS').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/XLSFile-selected_48.png" name="export_to_xls" onclick="document.pressed=this.name" /></span>'; 
}
?>
</div>
<?php 
	if($tmpl == 'component') {
		echo '<div><h3><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</h3>';
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundusraw.css" );
	}else{
		echo '<fieldset><legend><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</legend>';
	}
?>
<table id="userlist" width="100%">
	<thead>
	<tr>
	    <td align="center" colspan="15">
	    	<?php echo $this->pagination->getResultsCounter(); ?>
	    </td>
    </tr>
	<tr align="left">
		<th width="120">
		<?php if($current_user->profile!=16){ ?>
        <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
		<?php } ?>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
		<th><?php echo JText::_('PHOTO'); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NATIONALITY'), 'nationality', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICANT_FOR'), 'profile', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('SCHOOL_YEAR'), 'c.schoolyear', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
		<th><?php echo JHTML::_('grid.sort', JText::_('SEND_ON'), 'time_date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICATION_FORM_VALIDATION'), 'validated', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        
	</tr>
    </thead>
	<tfoot>
		<tr>
        	<td colspan="10"><?php echo $this->statut; ?></td>
		</tr>
        <tr>
			<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
<?php 
$i=0;
$j=0;
foreach ($this->users as $user) { ?>
	<tr class="row<?php echo $j++%2; ?>">
        <td>
        <div class="emundusraw">
		<?php 
			echo ++$i+$limitstart;
			if($current_user->profile!=16) {
				if($user->id != 62)  ?> <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]" value="<?php echo $user->id; ?>"/>
		<?php
			}
			echo '<span class="editlinktip hasTip" title="'.JText::_('MAIL_TO').'::'.$user->email.'">';
			if ($user->gender == 'male')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/images/emundus/icones/user_male.png" width="22" height="22" align="bottom" /></a>';
			elseif ($user->gender == 'female')
				echo '<a href="mailto:'.$user->email.'"><img src="'.$this->baseurl.'/images/emundus/icones/user_female.png" width="22" height="22" align="bottom" /></a>';
			else
				echo '<a href="mailto:'.$user->email.'">'.$user->gender.'</a>';
			echo '</span>';
			echo '<span class="editlinktip hasTip" title="'.JText::_('APPLICATION_FORM').'::'.JText::_('POPUP_APPLICATION_FORM_DETAILS').'">';
			echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=application_form&sid='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/images/emundus/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a>';
			echo '</span>';
			if($current_user->profile!=16) {
				echo '<span class="editlinktip hasTip" title="'.JText::_('UPLOAD_FILE_FOR_STUDENT').'::'.JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK').'">';
				echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&fabrik=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $user->id.'&student_id='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/images/emundus/icones/attach_16x16.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="16" height="16" align="bottom" /></a> ';
			}
			echo '</span>#'.$user->id.'</div>';
		?>
<div id="container" class="emundusraw"> 
	<ul id="emundus_nav">
		<?php // Tableau des pi�ces jointes envoy�es
		$query = 'SELECT attachments.id, uploads.filename, uploads.description, attachments.lbl, attachments.value
					FROM #__emundus_uploads AS uploads
					LEFT JOIN #__emundus_setup_attachments AS attachments ON uploads.attachment_id=attachments.id
					WHERE uploads.user_id = '.$user->id.'
					ORDER BY attachments.ordering';
		$db->setQuery( $query );
		$filestypes=$db->loadObjectList();
		echo '<li><a href="#"><img src="'.$this->baseurl.'/images/emundus/icones/pdf.png" alt="'.JText::_('ATTACHMENTS').'" title="'.JText::_('ATTACHMENTS').'" width="22" height="22" align="absbottom" /></a>
		<ul>';
		foreach ( $filestypes as $row ) {
			echo '<li>';
			if ($row->description != '')
				$link = $row->value.' (<em>'.$row->description.'</em>)';
			else
				$link = $row->value;
			echo '<a href="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$user->id.'/'.$row->filename.'" target="_new">'.$link.'</a>';
			echo '</li>';
		}
		echo '</ul>
</li>';
		//
		// Tableau des formulaires
		// contenu dans $forms[$profile_id]
		/*$query = 'SELECT fbtables.id, fbtables.form_id, fbtables.label, fbtables.db_table_name, profile.id AS profile
					FROM #__fabrik_lists AS fbtables 
					INNER JOIN #__menu AS menu ON fbtables.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
					WHERE fbtables.state = 1 AND fbtables.created_by_alias = "form" ORDER BY profile.id, menu.ordering';
		$db->setQuery( $query );
		$temps = $db->loadObjectList();
		$forms = array();*/
		/*foreach($temps as $temp) {
			$p = $temp->profile;
			$forms[$p][] = $temp;
			unset($temp);
		}
		unset($temps);
		$tableuser = $forms[$user->profile];*/
		$tableuser = EmundusHelperList::getFormsList($user->id);
		echo '<li><a href="#"><img src="'.$this->baseurl.'/images/emundus/icones/folder_documents.png" alt="'.JText::_('FORMS').'" title="'.JText::_('FORMS').'" width="22" height="22" align="absbottom" /></a>
	<ul>';
		foreach ( $tableuser as $row ) {
echo '<li>';
echo '<a href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&fabrik='.$row->form_id.'&random=0&rowid='.$user->id.'&usekey=user" target="_blank">'.$row->label.'</a>';
echo '</li>';
		}
		echo '</ul>
		</li>';
		?>
	</ul>
</div> 
        </td>
        <td align="center" valign="middle">
    <?php 	echo '<span class="editlinktip hasTip" title="'.JText::_('OPEN_PHOTO_IN_NEW_WINDOW').'::">';
					echo '<a href="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$user->id.'/'.$user->avatar.'" target="_blank" class="modal"><img src="'.$this->baseurl.'/'.EMUNDUS_PATH_REL.$user->id.'/tn_'.$user->avatar.'" width="60" /></a>'; 
					echo '</span>';
?></td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong><br />'.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
		</td>
      <td><?php echo $user->nationality; ?></td>
      <td>
	  <div class="emundusprofile<?php echo $user->profile; ?>"><?php echo $this->profiles[$user->profile]->label; ?></div>
	  <?php 
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
      <td align="left" valign="middle"><?php echo $user->schoolyear; ?></td>
		<td><?php echo strftime(JText::_('DATE_FORMAT_LC2'), strtotime($user->time_date)); ?></td>
		<td align="center">
        <?php
		$allowed = array("Super Administrator", "Administrator", "Editor");
		if (in_array($current_user->usertype, $allowed)) {
			 echo '<span class="hasTip" title="'.JText::_('APPLICATION_FORM_VALIDATION_NOTE').'">'; ?>
			 <input type="image" name="<?php echo $user->validated>0?'unvalidate|'.$user->id:'validate|'.$user->id; ?>" src="<?php echo $this->baseurl; ?>/components/com_emundus/style/images/<?php echo $user->validated>0?'yes_icone.png':'no_icone.png' ?>"  width='30' height='30' onclick="document.pressed=this.name" >
        <?php echo '</span>'; 
		} else { ?>
			<img src="<?php JURI::Base(); ?>/components/com_emundus/style/images/<?php echo $user->validated>0?'yes_icone.png':'no_icone.png' ?>" width='30' height='30' alt="<?php echo $user->validated>0?JText::_('VALIDATE_APPLICATION_FORM'):JText::_('UNVALIDATE_APPLICATION_FORM'); ?>"/>
		<?php 
        }
		?>
		</td>	
	</tr>
<?php } ?>
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
if (in_array($current_user->usertype, $allowed)) {
	//batch block
	echo $this->batch;
?>
  <fieldset>
  <legend> 
  	<span class="editlinktip hasTip" title="<?php echo JText::_('EMAIL_SELECTED_APPLICANTS').'::'.JText::_('EMAIL_SELECTED_APPLICANTS_TIP'); ?>">
		<img src="<?php JURI::Base(); ?>images/emundus/icones/mail_replay_22x22.png" alt="<?php JText::_('EMAIL_SELECTED_APPLICANTS'); ?>"/> <?php echo JText::_( 'EMAIL_SELECTED_APPLICANTS' ); ?>
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
  </div>
    <label for="mail_body"> <?php echo JText::_( 'MESSAGE' );?> </label><br/>
    <textarea name="mail_body" id="mail_body" rows="10" cols="80" class="inputbox">[NAME], </textarea>
    
  <input type="submit" name="custom_email" onclick="document.pressed=this.name" value="<?php echo JText::_( 'SEND_CUSTOM_EMAIL' );?>" >
  </fieldset>
  </div>
</form>
<?php
}
} else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php 
@$j++;
} 
?>

<script>
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

<?php 
unset($allowed);
$allowed = array("Super Administrator", "Administrator", "Editor");

if (!in_array($current_user->usertype, $allowed)) {
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
  //var form = document.getElementById('adminForm')[0];
  form.filter_order.value = order;
  form.filter_order_Dir.value = dir;
  document.adminForm.submit( task );
}

function OnSubmitForm() {
	var button_name=document.pressed.split("|");
	// alert(button_name[0]);
	switch(button_name[0]) {
		
		case 'export_zip': 
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=export_zip";
		break;
		case 'export_to_xls': 
			document.adminForm.action ="index.php?option=com_emundus&task=transfert_view&v=<?php echo $v; ?>&Itemid=<?php echo $itemid; ?>";
		break;
		case 'set_status':
			if(is_check()){ 
				if (confirm('<?php echo JText::_( 'SET_STATUT_CONFIRM' ); ?>')){ document.adminForm.action ="index.php?option=com_emundus&controller=check&task=administrative_check&limitstart=<?php echo $ls; ?>";
				}else{ return false;}
			}else{
				alert("<?php echo JText::_('NO_APPLICANT_SELECTED'); ?>");
				return false;
			}	
		break;
		case 'validate': 
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=validate&uid="+button_name[1]+"&limitstart=<?php echo $ls; ?>";
		break;
		case 'unvalidate': 
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=unvalidate&uid="+button_name[1]+"&limitstart=<?php echo $ls; ?>";
		break;
		case 'push_false': 
			if(is_check()){
				if(document.getElementById('comments').value == document.getElementById('comments').defaultValue) document.getElementById('comments').value = null;
				if (confirm('<?php echo JText::_( 'PUSH_FALSE_CONFIRM' ); ?>')) document.adminForm.action ="index.php?option=com_emundus&controller=check&task=push_false&limitstart=<?php echo $ls; ?>&Itemid=<?php echo $itemid; ?>";
				else return false;
			}else{
				alert('NO_APPLICANT_SELECTED');
				return false;
			}
		break;
		case 'custom_email': 
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=customEmail";
		break;
		case 'search_button': 
			document.adminForm.action ="index.php?option=com_emundus&view=check";
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&controller=check&task=clear";
		break;
		default: return false;
	}
	return true;
} 
</script>