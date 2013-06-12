<?php 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');

JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );

$document   = JFactory::getDocument();

defined('_JEXEC') or die('Restricted access'); 
$current_user = JFactory::getUser();
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
//$current_ap = JRequest::getVar('profil', null, 'POST', 'none',0);
$current_au = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);

// Starting a session.
$session = JFactory::getSession();
// Gettig the orderid if there is one.
$s_elements = $session->get('s_elements');
$s_elements_values = $session->get('s_elements_values');

if (count($search)==0) {
	$search = $s_elements;
	$search_values = $s_elements_values;
}

	$eMConfig = JComponentHelper::getParams('com_emundus');
	$quotient = $eMConfig->get('quotient', '20');
	
	$db = JFactory::getDBO();
	$query = 'SELECT sub_values, sub_labels FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
	$db->setQuery( $query );
	$result = $db->loadRowList();
	$sub_values = explode('|', $result[0][0]);
	foreach($sub_values as $sv)
		$p_grade[]="/".$sv."/";
	$grade = explode('|', $result[0][1]);

	$db->setQuery('SELECT average.coef, element.name FROM #__fabrik_elements AS element INNER JOIN #__emundus_setup_average AS average ON average.element_id = element.id');
	$elements_moyenne = $db->loadObjectList();
	$mult = 0;
	foreach($elements_moyenne as &$element) {
		$mult += $element->coef;
		$element = $element->name.'*'.$element->coef;
	}
	$elements_moyenne = '('.implode('+',$elements_moyenne).')/'.$mult;
	$query = "SELECT element.name, element.label 
	FROM #__fabrik_elements AS element 
	WHERE element.group_id = 41 AND element.hidden = 0 AND element.published=1";
	$db->setQuery( $query );
	$elements_evaluation = $db->loadObjectList();
	$elements_evaluation_liste = $db->loadResultArray();
	
	$query = 'SELECT fbtables.id, fbtables.form_id, fbtables.label, fbtables.db_table_name, profile.id AS profile
					FROM #__fabrik_lists AS fbtables 
					INNER JOIN #__menu AS menu ON fbtables.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
					WHERE fbtables.state = 1 AND fbtables.created_by_alias = "form" ORDER BY profile.id, menu.ordering';
	$db->setQuery( $query );
	$temps = $db->loadObjectList();
	$forms = array();
	foreach($temps as $temp) {
		$p = $temp->profile;
		$forms[$p][] = $temp;
		unset($temp);
	}
	unset($temps);
?>
<link rel="stylesheet" type="text/css" href= "<?php echo JURI::Base().'/images/emundus/menu_style.css'; ?>" media="screen"/>

<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="menu/includes/ie6.css" media="screen"/>
<![endif]-->

<!-- <div class="componentheading"><?php echo JText::_( 'ADMINISTRATIVE_VALIDATION' ); ?></div> -->

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="evaluated_applications"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

<fieldset><legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?></legend>

<table width="100%">
 <tr align="left">
  <th align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
 </tr>
 <tr>
  <td><input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/>
  <select name="Final_grade" id="Final_grade" onChange="javascript:document.adminForm.submit()">
      <option value=""><?php echo JText::_("PLEASE_SELECT") ?></option>
      <option value="4" <?php if (JRequest::getVar( 'Final_grade', null, 'post' )=='4') echo 'selected'; ?>><?php echo JText::_( 'SELECTED'); ?></option>
      <option value="3" <?php if (JRequest::getVar( 'Final_grade', null, 'post' )=='3') echo 'selected'; ?>><?php echo JText::_( 'WAITING_LIST'); ?></option>
      <option value="2" <?php if (JRequest::getVar( 'Final_grade', null, 'post' )=='2') echo 'selected'; ?>><?php echo JText::_( 'NON_SELECTED'); ?></option>
    </select></td>
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
<div class="emundusraw">
<?php
if(!empty($this->users)) {
	//echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile-selected_48.png" name="export_complete" onclick="document.pressed=this.name"></span>'; 
	//echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_COMPLETED_TO_XLS').'"><a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view=select_elements&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile_48.png" name="export_complete_to_xls" onclick="document.pressed=this.name" /></a></span>'; 
	//echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_INCOMPLETED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile-incomplete_48.png" name="export_incomplete_to_xls" onclick="document.pressed=this.name" /></span>'; 
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>'; 
?>
</div>
<?php 
	if($tmpl == 'component') {
			echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</h3>';
			$document = JFactory::getDocument();
			$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
	}else{
			echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</legend>';
	}
?>

<table id="userlist" width="100%">
	<thead>
	<tr>
		<td align="center" colspan="10">
			<?php echo $this->pagination->getResultsCounter(); ?>
		</td>
	</tr>
	<tr align="left">
		<th width="120">
        <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
		<th><?php echo JText::_('PHOTO'); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NATIONALITY'), 'nationality', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', 'SCHOOL_YEAR', 'c.schoolyear', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
		<th><?php echo JHTML::_('grid.sort', JText::_('SEND_ON'), 'time_date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th class="title"> <?php echo JHTML::_( 'grid.sort', JText::_( 'EVALUATION'), 'time_date', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>
	</tr>
    </thead>
	<tfoot>
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
			if($user->id != 62)  ?> <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]" value="<?php echo $user->id; ?>"/>
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
			echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_emundus&view==application_form&sid='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/viewmag_16x16.png" alt="'.JText::_('DETAILS').'" title="'.JText::_('DETAILS').'" width="16" height="16" align="bottom" /></a>';
			echo '</span>';
			//echo '<span class="editlinktip hasTip" title="'.JText::_('UPLOAD_FILE_FOR_STUDENT').'::'.JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK').'">';
			//echo '<a href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $user->id.'&student_id='. $user->id.'&tmpl=component" target="_self" class="modal"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/attach_16x16.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="16" height="16" align="bottom" /></a> ';
			echo '</span></div>#'.$user->id;
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
		echo '<li><a href="#"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.JText::_('ATTACHMENTS').'" title="'.JText::_('ATTACHMENTS').'" width="22" height="22" align="absbottom" /></a>
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
		$query = 'SELECT fbtables.id, fbtables.form_id, fbtables.label, fbtables.db_table_name, profile.id AS profile
					FROM #__fabrik_lists AS fbtables 
					INNER JOIN #__menu AS menu ON fbtables.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
					WHERE fbtables.state = 1 AND fbtables.created_by_alias = "form" ORDER BY profile.id, menu.ordering';
		$db->setQuery( $query );
		$temps = $db->loadObjectList();
		$forms = array();
		foreach($temps as $temp) {
			$p = $temp->profile;
			$forms[$p][] = $temp;
			unset($temp);
		}
		unset($temps);
		$tableuser = $forms[$user->profile];
		echo '<li><a href="#"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('FORMS').'" title="'.JText::_('FORMS').'" width="22" height="22" align="absbottom" /></a>
	<ul>';
		foreach ( $tableuser as $row ) {
echo '<li>';
echo '<a href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid='.$row->form_id.'&random=0&rowid='.$user->id.'&usekey=user" target="_blank">'.$row->label.'</a>';
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
      <td align="left" valign="middle"><?php echo $user->schoolyear; ?></td>
		<td><?php echo strftime(JText::_('DATE_FORMAT_LC2'), strtotime($user->time_date)); ?></td>
		<td><?php  
// Tableau des evaluations
$fg = preg_replace($p_grade, $grade, $user->Final_grade);
if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isPartner($user->id) && !EmundusHelperAccess::isEvaluator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
	$query = 'SELECT '.implode(',',$elements_evaluation_liste).',id, user FROM #__emundus_evaluations WHERE student_id  ='.$user->id.' AND user = '.$current_user->id;
	$db->setQuery( $query ); 
	$row = $db->loadObject();
	$evaluation=(count($row) > 0);
	if ($evaluation) {
		echo '<span class="editlinktip hasTip" title="Comment::'.$row->comment.'">';
		foreach ($elements_evaluation as $element) {
			$query = 'SELECT id, sub_values, sub_labels FROM #__fabrik_elements WHERE name like "'.$element->name.'"';
			$db->setQuery($query);
			$row_value = $db->loadRow();
			$ptmp = explode('|', $row_value[1]);
			foreach($ptmp as $pat)
				$patterns[] = '/'.$pat.'/';
			$replacements = explode('|', $row_value[2]);
			//print_r($replacements);
			//print_r($patterns);
			
			$lenom = $element->name;
			if($element->name != "comment") echo $element->label.':<b>'.preg_replace($patterns, $replacements, $row->$lenom).'</b><br/>';
			$patterns = array();
			$replacements = array();
		}
		unset($element);
	}
	if($evaluation) {
			echo '</span>';
			$link = '<br /><a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid=29&random=0&rowid='.$row->id.'&usekey=id&student_id='. $user->id.'&tmpl=component" target="_self" class="modal">'.JText::_( 'UPDATE_EVALUATION' ).'</a>'; 
			echo '<br/><strong>'.JText::_('MEAN').' = '.number_format($user->moyenne, 3, ',', '').'/'.$quotient.' </strong>'; 
			if($row->user == $current_user->id)
				echo '<input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/b_drop.png" name="delete_eval" onclick="document.pressed=\'delete_eval|'.$user->id.'\'" alt="'.JText::_('DELETE_EVAL').'" title="'.JText::_('DELETE_EVAL').'" />';
	} else {
			$link = '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&formid=29&tableid=31&rowid=&jos_emundus_evaluations___student_id[value]='. $user->id.'&student_id='. $user->id.'&tmpl=component" target="_self" class="modal">'.JText::_( 'EVALUATION').'</a>'; 
	}
	echo '<div class="emundusraw">';
	echo $link;
	echo '</div>';
	?>
	<div class="emundus_finalgrade<?php echo $user->Final_grade; ?>">
		<h4><?php echo $fg; ?></h4>
    </div>
    <?php 
echo '<div class="emundusraw">';
if(EmundusHelperAccess::isAdministrator($user->id)) {
	if (isset($user->Final_grade)) {
		echo '<a href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&formid=39&random=0&rowid='.$user->id.'&usekey=student_id&student_id='.$user->id.'&tmpl=component" target="_self" class="modal">'; 
		if ($user->Final_grade!= -1 && $user->Final_grade != '') {
			if ($user->Final_grade< 3)
				$final_grade = '<img src="'.$this->baseurl.'/media/com_emundus/images/icones/fileclose.png" alt="'.JText::_($fg).'" title="'.JText::_($fg).'" width="16" height="16" align="absbottom" /> ';
			elseif ($user->Final_grade == 3) 
				$final_grade = '<img src="'.$this->baseurl.'/media/com_emundus/images/icones/kalarm_16x16.png" alt="'.JText::_($fg).'" title="'.JText::_($fg).'" width="16" height="16" align="absbottom" /> ';
			elseif ($user->Final_grade == 4) 
				$final_grade = '<img src="'.$this->baseurl.'/media/com_emundus/images/icones/icq_ffc.png" alt="'.JText::_($fg).'" title="'.JText::_($fg).'" width="16" height="16" align="absbottom" /> ';
			
		echo $final_grade;
		//echo ' <strong>'.preg_replace($p_grade, $grade, $evalu['final_grade']).' </strong>'; 
		} 
		echo '</a>';
	} else 
		echo '<a href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&formid=39&tableid=41&rowid='.$user->id.'&usekey=student_id&jos_emundus_final_grade___student_id[value]='.$user->id.'&student_id='.$user->id.'&tmpl=component" target="_self" class="modal">'.JText::_('SET_FINAL_GRADE').'</a>'; 
}
echo '</div>';

}?>
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

</form>
<?php
} else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php 
@$j++;
} 
?>
<div style="height:300px;">&nbsp; </div>
<script>
function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user->id; ?>').checked = checked;
<?php } ?>
}

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
  //var form = document.getElementById('adminForm')[0];
  form.filter_order.value = order;
  form.filter_order_Dir.value = dir;
  document.adminForm.submit( task );
}

function OnSubmitForm() {
	var button_name=document.pressed.split("|");
	switch(button_name[0]) {
		case 'export_complete': 
			document.adminForm.action ="index.php?option=com_emundus&controller=evaluated_applications&task=export_complete";
		break;
		case 'export_zip': 
			document.adminForm.action ="index.php?option=com_emundus&controller=evaluated_applications&task=export_zip";
		break;
		case 'custom_email': 
			document.adminForm.action ="index.php?option=com_emundus&controller=evaluated_applications&task=customEmail";
		break;
		case 'search_button': 
			document.adminForm.action ="index.php?option=com_emundus&view=evaluated_applications";
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&controller=evaluated_applications&task=clear";
		break;
		case 'delete_eval': 
			if(confirm('Are you sure ?'))
				document.adminForm.action ="index.php?option=com_emundus&controller=evaluated_applications&task=delete_eval&sid="+button_name[1];
			else return false;
		break;
		default: return false;
	}
	return true;
} 
</script>