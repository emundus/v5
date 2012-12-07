<?php 
defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_emundus/style/' );
$eMConfig =& JComponentHelper::getParams('com_emundus');

$profile = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_user = & JFactory::getUser();
$current_g = JRequest::getVar('groups', null, 'POST', 'none',0);
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$finalgrade = JRequest::getVar('finalgrade', null, 'POST', 'none',0);
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
$view_calc = JRequest::getVar('view_calc', null, 'POST', 'none',0);
$debug=$mainframe->getCfg('debug');
$v = JRequest::getVar('view', null, 'GET', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);

$db =& JFactory::getDBO();
$query = 'SELECT sub_values, sub_labels FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
$db->setQuery( $query );
$result = $db->loadRowList();
$sub_values = explode('|', $result[0][0]);
foreach($sub_values as $sv)
	$p_grade[]="/".$sv."/";
$grade = explode('|', $result[0][1]);

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
<input type="hidden" name="view" value="ranking_auto"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>

<fieldset><legend><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?></legend>

<table width="100%">
 <tr align="left">
  <th align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
  <th align="left"><?php echo JText::_('PROFILE'); ?></th>
   <th align="left"><?php echo JText::_('FINAL_GRADE_FILTER'); ?></th>
    <th align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
 </tr>
 <tr>
  <td><input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/>
  	<?php if($debug==1){ ?>
    		<input name="view_calc" type="checkbox" onclick="document.pressed=this.name" value="1" <?php echo $view_calc==1?'checked=checked':''; ?> />
          <?php } ?>
  </td>
  <td>
  <select name="profile" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('ALL'); ?> </option>
	<?php 
	foreach($this->applicants as $app) { 
		echo '<option value="'.$app->id.'"';
			if($app->id == $profile) echo ' selected';
					echo '>'.$app->label.'</option>'; 
	} 
	?>
  </select>
  </td>
  <td>
  <select name="finalgrade" onChange="javascript:submit()">
	<option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
	<?php  
	$groupe ="";
	
	for($i=0; $i<count($grade); $i++) { 
		$val = substr($p_grade[$i],1,1);
		echo '<option value="'.$val.'"';
			if($val == $finalgrade) echo ' selected';
					echo '>'.$grade[$i].'</option>'; 
	} 
	unset($val);
	unset($i);
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
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>'; 
	echo '<span class="editlinktip hasTip" title="'.JText::_('SEND_ELEMENTS').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/XLSFile-selected_48.png" name="export_to_xls" onclick="document.pressed=this.name" /></span>'; 
?>
</div>
<?php 
if($tmpl == 'component') {
		echo '<div><h3><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('RANKING_LIST').'"/>'.JText::_('RANKING_LIST').' : '.$this->schoolyear.'</h3>';
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundusraw.css" );
}else{
		echo '<fieldset><legend><img src="'.JURI::Base().'images/emundus/icones/folder_documents.png" alt="'.JText::_('RANKING_LIST').'"/>'.JText::_('RANKING_LIST').' : '.$this->schoolyear.'</legend>';
}
?>

<div class="evaluation_users">
<?php if(isset($this->users)&&!empty($this->users)){ ?>
<table id="userlist" width="100%">
	<thead>
        <tr>
            <td align="center" colspan="15">
                <?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        <tr>
            <?php 
			foreach($this->table_name as $sv){
				$c_values[]="/".$sv->type."/";
				$c_labels[] = $sv->name;
			}
            foreach ($this->users[0] as $key=>$value){ 
			 	//echo $key.' '.$value.'<br />';
				switch($key){
					case 'user_id':?>
                        <th align="left" width=100>
                            <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
                            <?php echo JHTML::_('grid.sort', JText::_('#'), $key, $this->lists['order_Dir'], $this->lists['order']); ?>
                        </th><?php 
					break;
					default:
						if($key!='final_grade' && $key!='row_id' && $key!='First name' && $key!='Family Name' && $key!='Gender' && $key!='Year of birth' && $key!='Address' && $key!='Postal Code' && $key!='City' && $key!='Country of residence' && $key!='Phone' && $key!='Life partner' && $key!='Email' && $key!='Children' && $key!='Institution delivering previous degree' && $key!='Country of University of origin') {
							$c_name = "";
							$c_name = preg_replace($c_values, $c_labels, $key);
							
							echo '<th><span class="editlinktip hasTip" title="<br /><br />::'.JText::_($c_name).'<br />">'.JHTML::_('grid.sort', substr(JText::_($c_name),0,5).'...', $key, $this->lists['order_Dir'], $this->lists['order']).'</span></th>';
						}
					break;
				}
			}?>
            <th><?php echo JText::_('FINAL_GRADE'); ?></th>
            <?php /*// --------------------- Colonne des evaluators ------------------------
            <th> echo JText::_('ASSESSOR'); </th> */?>
        </tr>
	</thead>
	<tbody><?php 
		$i=1; $j=0;
		foreach($this->users as $evalu){ ?>
			<tr class="row<?php echo $j++%2; ?>"><?php
				foreach ($evalu as $key=>$value){ 
					if($key=='user_id'){ ?>
                        <td> <?php echo $i+$limitstart;?>
                            <input id="cb<?php echo $value; ?>" type="checkbox" name="ud[]" class="emundusraw" value="<?php echo $value; ?>"/>
                            <a class="modal" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8},onClose:function(){delayAct(<?php echo $value; ?>);}}" href="index.php?option=com_emundus&view=application_form&sid=<?php echo $value; ?>&tmpl=component&iframe=1"><img height="16" width="16" align="bottom" title="<?php echo JText::_('DETAILS') ?>" src="<?php echo $this->baseurl; ?>/images/emundus/icones/viewmag_16x16.png">
                            </a>
                            <?php echo "#".$value ; $i++;  ?>  
                      	</td><?php 	
					}else if($key == 'result_for'){ 
                    		if($evalu['result_for']){?>
                        		<td><div class="emundusprofile<?php echo $evalu['result_for']; ?>"><?php echo $this->profiles[$evalu['result_for']]->label; ?></div></td><?php
							}else{
								echo '<td></td>';
							}
					}else if($key == 'Profile'){ ?>
						<td>
                        	<div class="emundusprofile<?php echo $evalu['Profile']; ?>"><?php echo $this->profiles[$evalu['Profile']]->label; ?></div>
                       	</td><?php
					}else if ($key!='final_grade' && $key!='row_id' && $key!='First name' && $key!='Family Name' && $key!='Gender' && $key!='Year of birth' && $key!='Address' && $key!='Postal Code' && $key!='City' && $key!='Country of residence' && $key!='Phone' && $key!='Life partner' && $key!='Email' && $key!='Children' && $key!='Institution delivering previous degree' && $key!='Country of University of origin'){ 
						if(($key!='Name') && ($key!='Nationality') && ($key!='category') && ($key!='overall') && ($key!='r') ){
							if($value || $value === '0') $class = "class=green";
							else $class = "class=red";
							$td = '<td '.$class.'>';
					}
						else $td = '<td>';
						$c_name = preg_replace($c_values, $c_labels, $key);
						echo $td.' <span class="editlinktip hasTip" title="::'.JText::_($c_name).'">'.$value.'</span>';?>	 
						</td><?php
					} 
				} ?>
                		<td><?php 
							echo '<div class="emundusraw">';
							$allowed = array("Super Administrator", "Administrator", "Editor");
							if (in_array($current_user->usertype, $allowed)) {
								if (isset($evalu['final_grade'])) {
									$fg_txt = preg_replace($p_grade, $grade, $evalu['final_grade']);
									echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8},onClose:function(){delayAct('.$evalu['user_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&view=form&fabrik=39&random=0&rowid='.$evalu['row_id'].'&usekey=id&student_id='. $evalu['user_id'].'&tmpl=component&iframe=1" target="_self" class="modal">'; 
									if ($evalu['final_grade']!= -1 && $evalu['final_grade'] != '') {
										if ($evalu['final_grade'] == 2)
											$final_grade = '<img src="'.$this->baseurl.'/images/emundus/icones/fileclose.png" alt="'.JText::_($fg_txt).'" title="'.JText::_($fg_txt).'" width="16" height="16" align="absbottom" /> ';
										elseif ($evalu['final_grade'] == 3 || $evalu['final_grade'] == 1) 
											$final_grade = '<img src="'.$this->baseurl.'/images/emundus/icones/kalarm_16x16.png" alt="'.JText::_($fg_txt).'" title="'.JText::_($fg_txt).'" width="16" height="16" align="absbottom" /> ';
										elseif ($evalu['final_grade'] == 4) 
											$final_grade = '<img src="'.$this->baseurl.'/images/emundus/icones/icq_ffc.png" alt="'.JText::_($fg_txt).'" title="'.JText::_($fg_txt).'" width="16" height="16" align="absbottom" /> ';
										echo $final_grade;
										echo ' <strong>'.$fg_txt.'</strong>'; 
									} 
									echo '</a>';
									echo ' <input type="image" src="'.$this->baseurl.'/images/emundus/icones/b_drop.png" name="delete_eval" onclick="document.pressed=\'delete_eval|'.$evalu['user_id'].'\'" alt="'.JText::_('DELETE_SELECTION_OUTCOME').'" title="'.JText::_('DELETE_SELECTION_OUTCOME').'" />';
								} else 
									echo '<a rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8},onClose:function(){delayAct('.$evalu['user_id'].');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&fabrik=39&tableid=41&rowid='.$evalu['row_id'].'&jos_emundus_final_grade___student_id[value]='.$evalu['user_id'].'&student_id='. $evalu['user_id'].'&tmpl=component&iframe=1" target="_self" class="modal">'.JText::_('SET_FINAL_GRADE').'</a>'; 
							}
							echo '</div>';?>
						</td>	
						<?php
							?>						
			</tr><?php } ?>
	</tbody>
	<tfoot><tr><td colspan="<?php echo count($evalu)+1; ?>"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
</table>
<?php } else echo "No applicant"; ?>
</div>
<?php 
	if($tmpl == 'component') {
		echo '</div>';
	}else{
		echo '</fieldset>';
	}
?>
<div class="emundusraw">
<?php
unset($allowed);
$allowed = array("Super Administrator", "Administrator", "Editor");
if (in_array($current_user->usertype, @$allowed)) {
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
<?php } else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php } ?>
<div class="emundusraw">
<?php
if (@in_array($current_user->usertype, @$allowed)) {
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
  document.getElementById('cb<?php echo $user['user_id']; ?>').checked = checked;
<?php } ?>
}

<?php 
unset($allowed);
$allowed = array("Super Administrator", "Administrator", "Editor");

if (!in_array($current_user->usertype, $allowed)) {
?>
function hidden_all() {
  document.getElementById('checkall').style.visibility='hidden';
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user['user_id']; ?>').style.visibility='hidden';
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
	var button_name=document.pressed.split("|");
	//alert(button_name[0]);
	switch(button_name[0]) {
	    case 'affect': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=setAssessor";
		break;
		case 'unaffect': 
			if (confirm("<?php echo JText::_("CONFIRM_UNAFFECT_ASSESSORS"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=unsetAssessor";
		 	} else 
		 		return false;
		break;
	    /*case 'export_selected_to_xls':
		    document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=export_selected_xls";
		break;s
		case 'export_xls': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=export_to_xls";
		break;*/
		case 'export_zip': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=export_zip";
		break;
		case 'export_to_xls': 
			document.adminForm.action ="index.php?option=com_emundus&task=transfert_view&v=<?php echo $v; ?>&Itemid=<?php echo $itemid; ?>";
		break;
		case 'set_status': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=administrative_check&limitstart=<?php echo $ls; ?>";
		break;
		case 'validate': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=validate&uid="+button_name[1]+"&limitstart=<?php echo $ls; ?>";
		break;
		case 'unvalidate': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=unvalidate&uid="+button_name[1]+"&limitstart=<?php echo $ls; ?>";
		break;
		case 'push_false': 
			if (confirm('<?php echo JText::_( 'PUSH_FALSE_CONFIRM' ); ?>')) 
				document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=push_false&limitstart=<?php echo $ls; ?>";
		break;
		case 'custom_email': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=customEmail";
		break;
		case 'default_email': 
			if (confirm("<?php echo JText::_("CONFIRM_DEFAULT_EMAIL"); ?>")) {
        		document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=defaultEmail";
		 	} else 
		 		return false;
		break;
		case 'view_calc': 
			document.adminForm.action ="index.php?option=com_emundus&view=ranking_auto";
		break;
		case 'search_button': 
			document.adminForm.action ="index.php?option=com_emundus&view=ranking_auto";
		break;
		case 'clear_button': 
			document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=clear";
		break;
		case 'delete_eval': 
			if(confirm('Are you sure ?'))
				document.adminForm.action ="index.php?option=com_emundus&controller=ranking_auto&task=delete_eval&sid="+button_name[1];
			else return false;
		break;
		default: return false;
	}
	return true;
} 
function delayAct(user_id){
	document.adminForm.action = "index.php?option=com_emundus&view=ranking_auto#cb"+user_id;
	setTimeout("document.adminForm.submit()",500);
}
</script>