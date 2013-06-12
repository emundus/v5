<!--[if lte IE 6]>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/rt_afterburner_j15/js/ie_suckerfish.js"></script>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/rt_afterburner_j15/css/styles.ie.css" type="text/css" />
<![endif]-->
<!--[if lte IE 7]>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/rt_afterburner_j15/css/styles.ie7.css" type="text/css" />
<![endif]-->
<?php  
defined('_JEXEC') or die('Restricted access'); 

$current_user = & JFactory::getUser();
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
//$allowed = array("Super Users", "Administrator", "Publisher", "Editor", "Author");
if(!EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id)) die("ACCESS_DENIED");
	 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
 //die(print_r($this->comments));

$document   = JFactory::getDocument();

$sid = JRequest::getVar('sid', null, 'GET', 'none',0);
$view = JRequest::getVar('view', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);

function age($naiss) {
	@list($annee, $mois, $jour) = split('[-.]', $naiss);
	$today['mois'] = date('n');
	$today['jour'] = date('j');
	$today['annee'] = date('Y');
	$annees = $today['annee'] - $annee;
	if ($today['mois'] <= $mois) {
		if ($mois == $today['mois']) {
		if ($jour > $today['jour'])
			$annees--;
	}
	else
		$annees--;
	}
	return $annees;
}
	//$dFormat	 = $this->params->get( 'dformat', '%c' );
	
	$db = JFactory::getDBO();
	$query = 'SELECT params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
	$db->setQuery( $query );
	$result = EmundusHelperFilters::insertValuesInQueryResult($db->loadAssocList(), array("sub_values","sub_labels"));
	$sub_values = explode('|', $result[0]['sub_values']);
	foreach($sub_values as $sv)
		$p_grade[]="/".$sv."/";
	$grade = explode('|', $result[0]['sub_labels']);

?>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" enctype="multipart/form-data"/>


<div class="emundus_finalgrade<?php echo $this->user->Final_grade; ?>">
<h3 class="final_grade"><?php echo preg_replace($p_grade, $grade, $this->user->Final_grade);?><h3>
</div>

<table id="userlist" width="100%" class="adminlist">
    <thead>
      <tr>
        <?php if($current_user->profile!="16"){ ?><th width="16px"> </th> <?php } ?>
        <th class="title" width="70"></th>
        <th class="title"> <?php echo JText::_('NAME'); ?> </th>
		<th class="title"> <?php echo JText::_('EMAIL'); ?> </th>
        <?php  if(isset($this->user->maiden_name)) echo'<th class="title">'. JText::_('MAIDEN_NAME').'</th>'; ?> 
        <th class="title"> <?php echo JText::_('NATIONALITY'); ?> </th>
        <th class="title"> <?php echo JText::_('AGE'); ?> </th>
        <th class="title"> <?php echo JText::_('SCHOOLYEAR'); ?> </th>
        <th class="title"> <?php echo JText::_('PROFILE'); ?> </th>
        <th class="title"> <?php echo JText::_('APPLICATION_SENT_ON'); ?> </th>
      </tr>
    </thead>
    <tbody>
    
	<?php $i = 0; ?>

      <tr class="row<?php echo $i++%2; ?>">
        <?php if($current_user->profile!="16"){ ?> <td align="center" valign="middle">
    <?php 
			echo '<input id="cb'.$this->user->user_id.'" name="cid[]" value="'.$this->user->user_id.'" checked type="checkbox">'; 
			echo '<span class="editlinktip hasTip" title="'.JText::_('UPLOAD_FILE_FOR_STUDENT').'::'.JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK').'">';
			echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8},onClose:function(){delayAct('.$this->user->user_id.');}}" href="'.$this->baseurl.'/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $this->user->user_id.'&student_id='. $this->user->user_id.'&tmpl=component"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/attach_16x16.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="16" height="16" align="bottom" /></a> ';
			echo '<script>var elm = document.getElementById("cb'.$this->user->user_id.'"); elm.style.display = "none";</script>';
			if ($this->user->time_date!='')
				echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/ZipFile-selected_48.png" onClick="document.pressed=this.name" name="export_zip" width="32"></span><br />'; 
				
			echo '<span class="editlinktip hasTip" title="'.JText::_('DOWNLOAD_APPLICATION_FORM').'::">';
			echo '<a href="index.php?option=com_emundus&task=pdf&user='.$this->user->user_id.'" class="appsent" target="_blank"><img border="0" src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" /></a>'; 
			echo '</span><br />';
	?>
	<?php 
		 if ($this->user->profile <= 5 && $this->user->profile != 3 && $this->user->profile != 999 && $this->user->profile != 16) {
			echo '<span class="editlinktip hasTip" title="'.JText::_('ADD_ATTACHMENT').'">';
			echo '<a href="'.$this->baseurl.'index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $this->user->user_id.'&student_id='. $this->user->user_id.'&tmpl=component" target="_self" class="modal"><img border="0" src="'.$this->baseurl.'media/com_emundus/images/icones/attach_16x16.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="16" height="16" align="bottom" /></a>';
			echo '</span><br />';
		 }
	?>
        <?php echo '#'.$this->user->user_id; ?>
        </td> <?php } ?>
        <td align="center" valign="middle">
		<?php 
			echo '<span class="editlinktip hasTip" title="'.JText::_('PHOTO_ENLARGE').'::">';
			echo '<a href="'.EMUNDUS_PATH_REL.$this->user->user_id.DS.$this->user->avatar.'" target="_blank" class="modal"><img border="0" src="'.EMUNDUS_PATH_REL.$this->user->user_id.DS.'tn_'.$this->user->avatar.'" width="70" /></a>'; 
			echo '</span>';
		?>
        </td>
        <td align="center" valign="middle"><?php echo '<strong>'.strtoupper($this->user->lastname).'</strong> '.$this->user->firstname; ?> </td>
        <?php  if(isset($this->user->maiden_name)) echo'<td align="left" valign="middle">'.$this->user->maiden_name.'</td>';  ?>
        <td align="center" valign="middle"><?php echo $this->user->email; ?></td>
		<td align="center" valign="middle"><?php echo $this->user->nationality; ?></td>
        <td align="center" valign="middle"><?php echo age($this->user->birth_date); ?></td>
        <td align="center" valign="middle"><?php echo $this->user->cb_schoolyear; ?> </td>
        <td align="left" valign="middle">
		<div class="emundusprofile<?php echo $this->user->profile; ?>"><?php echo $this->user->cb_profile; ?></div>
		<?php 
	   $query = 'SELECT esp.id, esp.label
					FROM #__emundus_users_profiles AS eup
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id=eup.profile_id
					WHERE eup.user_id = '.$this->user->user_id.'
					ORDER BY eup.id';
		$db->setQuery( $query );
		$profiles=$db->loadObjectList();
		foreach($profiles as $p){
			if ($p->id == $this->user->profile)
				echo '- <b>'.$p->label.'</b> <em>('.JText::_('FIRST_CHOICE').')</em><br />';
			else
				echo '- '.$p->label.'<br />';
		}
	   ?></td>
        <td align="center" valign="middle">
		<?php echo $this->user->time_date!=''?strftime("%d/%m/%Y %H:%M", strtotime($this->user->time_date)):JText::_('NOT_SENT'); ?></td>
      </tr>
      
    </tbody>
  </table>  
<?php 
//////////////////////////////////
/// APPLICANT STATUS
/////////////////////////////////////////
$query = 'SELECT COUNT(*) FROM #__emundus_declaration WHERE user = '.$this->user->user_id;
$db->setQuery( $query );
$sent = $db->loadResult();

if ($sent == 0) {
	$query = 'SELECT 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id)
				FROM #__emundus_setup_attachment_profiles AS profiles 
				LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$this->user->user_id.'
				WHERE profiles.profile_id = '.$this->user->profile.' AND profiles.displayed = 1 AND profiles.mandatory = 1 ';
	$db->setQuery($query);
	$attachments = floor($db->loadResult());
	
	$query = 'SELECT distinct(esa.value) 
				FROM #__emundus_setup_attachment_profiles AS profiles 
				LEFT JOIN #__emundus_setup_attachments AS esa ON esa.id=profiles.attachment_id 
				WHERE profiles.profile_id = '.$this->user->profile.' AND profiles.attachment_id NOT IN (select attachment_id FROM #__emundus_uploads 
				WHERE user_id = '.$this->user->user_id.') 
				ORDER BY esa.ordering';
	$db->setQuery($query);
	$attachmentsLst = $db->loadResultArray();
	
	$forms = EmundusHelperMenu::buildMenuQuery($this->user->profile);
	
	$nb = 0;
	$formLst = array();
	foreach ($forms as $form) {
		$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$this->user->user_id;
		$db->setQuery( $query );
		$cpt = $db->loadResult();
		if ($cpt==1) {
			$nb++;
		} else {
			$formLst[] = $form->label;
		}
	}
	$forms = @floor(100*$nb/count($forms));
	
	?>
	
	<table width="100%" class="adminlist" align="center">
		<tr>
			<th bgcolor="#FFA4A4" id="flowpj"><?php echo $attachments.'% '.JText::_('ATTACHMENTS_SENT'); ?></th>
			<th bgcolor="#FFA4A4" id="flowforms"><?php echo $forms.'% '.JText::_('FORMS_FILLED'); ?></th>
		</tr>
        <tr>
			<td align="left" valign="top" bgcolor="#efefef" id="flowpj_list">
            <h3><?php echo JText::_('MISSING_ATTACHMENTS'); ?></h3>
			<?php 
			echo '<ul>';
			foreach ($attachmentsLst as $lst) {
				echo '<li>'.$lst.'</li>';
			}
			echo '</ul>';
			unset($attachmentsLst);unset($lst);
			?>
            </td>
			<td align="left" valign="top" bgcolor="#efefef" id="flowforms_list">
            
			<?php 
			if (count($formLst) > 0) {
				echo '<h3>'.JText::_('MISSING_FORMS').'</h3>';
				echo '<ul>';
				foreach ($formLst as $lst) {
					echo '<li>'.$lst.'</li>';
				}
				echo '</ul>';
			}
			unset($formLst);unset($lst);
			
			?>
          </td>
	  </tr>
	</table>

<?php } ?>
  <table width="100%" align="center" class="adminlist">    
  <thead>
	<th class="title"> <?php echo JText::_('DOCUMENTS'); ?></th>
  </thead>
  <tbody>
  	<tr>
        <td valign="top" id="attachements" align="left">
          <fieldset style="float:left; width:45%"><legend><?php echo JText::_('ATTACHMENTS'); ?></legend>
		  <?php 				
          // Tableau des pièce jointes 
          $query = 'SELECT upload.id AS aid, attachment.id, upload.filename, upload.description, attachment.value
            FROM #__emundus_uploads AS upload
            LEFT JOIN #__emundus_setup_attachments AS attachment ON  upload.attachment_id=attachment.id
            WHERE upload.user_id = '.$this->user->user_id.'
            ORDER BY attachment.ordering';
          $db->setQuery( $query );
          $filestypes=$db->loadObjectList();
          
          $can_delete = array("Super Users", "Administrator");
          foreach ( $filestypes as $row ) {
            $link = $row->value;
            if (!empty($row->description)) 
                $link .= ' <em>('.$row->description.')</em>';
            if(strpos($row->filename, "_locked_")>0) 
                $link = '<img src="'.$this->baseurl.'media/com_emundus/images/icones/encrypted.png" />'.$link;
            if($row->id == 27){
				if(!file_exists(EMUNDUS_PATH_REL.'archives'.DS.$row->filename))
                	$link = '<img src="media/com_emundus/images/icones/agt_update_critical.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> '.$link;
			}else{
				if(!file_exists(EMUNDUS_PATH_REL.$this->user->user_id.DS.$row->filename))
                	$link = '<img src="media/com_emundus/images/icones/agt_update_critical.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> '.$link;
			}	
            
            if (in_array($current_user->usertype, $can_delete)) 
                echo '<input id="aid'.$row->aid.'" name="aid[]" value="'.$row->aid.'" type="checkbox"> '; 
            if($row->id == 27) echo '<a href="'.EMUNDUS_PATH_REL.'archives/'.$row->filename.'" target="_blank">'.$link.'</a><br/>';
			else echo '<a href="'.EMUNDUS_PATH_REL.$this->user->user_id.'/'.$row->filename.'" target="_blank">'.$link.'</a><br/>';
          }
          ?>
          <?php
            if (in_array($current_user->usertype, $can_delete)) 
			 if($current_user->profile!="16"){
                echo '<input type="submit" name="delete_attachment" value="'.JText::_( 'DELETE_ATTACHMENT' ).'" onClick="document.pressed=this.name">';
          }
		  ?>
          </fieldset>
		   <?php if($current_user->profile!="16"){  ?>
          <fieldset style="float:right; width:30%; margin-right:15%;"><legend><?php echo JText::_('COMMENTS'); ?></legend>
			  <?php  // Table of comments
				  echo '<div id="comment_tab">';
                                  echo $this->comments;
				  echo '</div>';
				/*  echo '<label><b>Add a comment</b></label><br /><textarea id="comment_'.$this->user->user_id.'" rows="8" cols="35" name="comments"></textarea>';
				  echo '<button type="button" id="btn_comment_'.$this->user->user_id.'"><img width="20" heigth="20" src="images/apply_f2.png"></button>';
				  echo '<div id="c_'.$this->user->user_id.'"></div>';
				  $url_set = 'index.php?option=com_emundus&controller=application_form&task=set_comment&uid='.$this->user->user_id.'&format=raw';
				  $url_get = 'index.php?option=com_emundus&controller=application_form&task=get_comment&sid='.$this->user->user_id.'&format=raw';*/
              ?>    
          </fieldset>
		  <?php } ?>
            <script>              
                window.addEvent( 'domready', function() {
                    $('<?php echo 'btn_comment_'.$this->user->user_id; ?>').addEvent( 'click', function() {
                        $('c_<?php echo $this->user->user_id; ?>').empty().addClass('ajax-loading');
                         var a = new Ajax( '<?php echo $url_set; ?>'+'&comment='+document.getElementById("<?php echo 'comment_'.$this->user->user_id; ?>").value, { 
                             method: 'get', 
                             update: $('c_<?php echo $this->user->user_id; ?>'),
                             onSuccess: function(){
                                var b = new Ajax( '<?php echo $url_get; ?>', { method: 'post', update: $('comment_tab')}).request();
                             }
                        }).request();
                         
                    }); 
                });
            </script>
      </td>
  </tr>
  <tr>
	 <td valign="top">
	<?php 
	/*if($current_user->profile!="16"){
	if($this->can_evaluate){ 
		if($this->is_evaluated > 0)
			$url = 'index.php?option=com_fabrik&c=form&view=form&fabrik=29&tableid=31&rowid='.$this->is_evaluated.'&usekey=id&jos_emundus_evaluations___student_id[value]='.$this->user->user_id.'&student_id='.$this->user->user_id.'&tmpl=component';
		else
			$url = 'index.php?option=com_fabrik&c=form&view=form&formid=29&tableid=31&rowid=&jos_emundus_evaluations___student_id[value]='.$this->user->user_id.'&student_id='.$this->user->user_id.'&tmpl=component';
		echo '<iframe src="'.$url.'" scrolling="auto" frameborder="0" height="850" width="100%" ></iframe>';
	} }*/
	?>
	</td>
    </tr>
  </tbody>
</table>
<?php
//______________________________________________________//
//		Liste des formulaires et de leurs données		//
//______________________________________________________//
// Récupération des tables qui doivent contenir un enregistrement de candidat
/*
	if( ($this->user->profile >= 7 && $this->user->profile <= 11) || $this->user->profile == 999)
		$query = 'SELECT fbtables.id, fbtables.form_id, fbtables.label, fbtables.db_table_name
					FROM #__fabrik_lists AS fbtables 
					INNER JOIN #__menu AS menu ON fbtables.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					WHERE fbtables.state = 1 AND fbtables.created_by_alias = "form" AND menu.menutype = "menu_profile9"  
					ORDER BY menu.ordering';
	else
		$query = 'SELECT fbtables.id, fbtables.form_id, fbtables.label, fbtables.db_table_name
					FROM #__fabrik_lists AS fbtables 
					INNER JOIN #__menu AS menu ON fbtables.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					INNER JOIN #__emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
					WHERE fbtables.state = 1 AND fbtables.created_by_alias = "form" AND profile.id = '.$this->user->profile.'
					ORDER BY menu.ordering';
					
	$db->setQuery( $query );
	$tableuser = $db->loadObjectList();*/
	$tableuser = EmundusHelperList::getFormsList($this->user->id);
	if(isset($tableuser)) {
		foreach($tableuser as $key => $itemt) {
			echo '<br><h3>';
			echo $itemt->label;
			echo '</h3>';
			// liste des groupes pour le formulaire d'une table
			$query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, INSTR(fg.params,"\"repeat_group_button\":1") as repeated
						FROM #__fabrik_formgroup ff, #__fabrik_groups fg
						WHERE ff.group_id = fg.id AND
							  ff.form_id = "'.$itemt->form_id.'" 
						ORDER BY ff.ordering';
			$db->setQuery( $query );
			$groupes = $db->loadObjectList();
			
			/*-- Liste des groupes -- */
			foreach($groupes as $keyg => $itemg) {

				// liste des items par groupe
				$query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
							FROM #__fabrik_elements fe
							WHERE fe.published=1 AND 
								  fe.hidden=0 AND 
								  fe.group_id = "'.$itemg->group_id.'" 
							ORDER BY fe.ordering';
				$db->setQuery( $query );
				$elements = $db->loadObjectList();
				if(count($elements)>0) {
					echo '<fieldset><legend>';
					echo $itemg->label;
					echo '</legend>';
					foreach($elements as &$iteme) {
						$query = 'SELECT `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$this->user->user_id;
						$db->setQuery( $query );
						$iteme->content = $db->loadResult();
					}
 					unset($iteme);
					
					if ($itemg->group_id == 14) {
?>
     <?php 
     foreach($elements as &$element) {
		if(!empty($element->label) && $element->label!=' ') {
			if ($element->plugin=='date' && $element->content>0) {
				$date_params = json_decode($element->params);
				$elt = strftime($date_params->date_form_format, strtotime($element->content));
			} else $elt = $element->content;
			echo '<b>'.$element->label.': </b>'.$elt.'<br/>';
		}
	 }
	 ?>

<?php			
	// TABLEAU DE PLUSIEURS LIGNES
			} elseif ($itemg->repeated>0){
				echo '<table class="adminlist">
					  <thead>
					  <tr> ';
				
				$query = 'SELECT * FROM '.$itemt->db_table_name.'_'.$itemg->group_id.'_repeat
								WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$this->user->user_id.')';
				$db->setQuery($query);
				$repeated_elements = $db->loadObjectList();
				//-- Entrée du tableau -- */
				$nb_lignes = 0;
				foreach($elements as &$element) { 
					echo '<th scope="col">'.$element->label.'</th>';
					$element->content = explode('//..*..//', $element->content);
					if(count($element->content)>$nb_lignes) $nb_lignes = count($element->content);
				}
				unset($element);
				echo '</tr></thead><tbody>';
				// -- Ligne du tableau -- 
				foreach ($repeated_elements as $r_element) {
					echo '<tr>';
						$j = 0;
						foreach ($r_element as $key => $r_elt) {
							if ($key != 'id' && $key != 'parent_id' && isset($elements[$j - 2])) {
								if ($elements[$j - 2]->plugin=='date') {
									$date_params = json_decode($elements[$j - 2]->params);
									$elt = strftime($date_params->date_form_format, strtotime($r_elt));
								} else $elt = $r_elt;
								echo '<td>'.$elt.'</td>';
							}
							$j++;
						}
						echo '</tr>';
					}
				echo '</tbody></table>';

			// AFFICHAGE EN LIGNE
			} else { 
				foreach($elements as &$element) {
					if(!empty($element->label) && $element->label!=' ') {
						if ($element->plugin=='date' && $element->content>0) {
							$date_params = json_decode($element->params);
							$elt = strftime($date_params->date_form_format, strtotime($element->content));
						} else $elt = $element->content;
						echo '<b>'.$element->label.': </b>'.$elt.'<br/>';
					}
				}
			}
			echo '</fieldset>';
		}
	}
}
}

?>

  <input type="hidden" name="view" value="<?php echo $view; ?>" />
  <input type="hidden" name="sid" value="<?php echo $sid; ?>" />
  <input type="hidden" name="tmpl" value="<?php echo $tmpl; ?>" />
  </fieldset>
</form>

<script>
function OnSubmitForm() {
	var button_name=document.pressed.split("|");
	//alert(button_name[0]);
	switch(button_name[0]) {
		case 'delete_attachment': 
		if(confirm("<?php echo "Confirm delete of selected attachment(s)"; ?>")){
			document.adminForm.action ="index.php?option=com_emundus&view=application_form&controller=application_form&task=delete_attachment";
		break;}
		case 'export_zip': 
			document.adminForm.action ="index.php?option=com_emundus&controller=application_form&task=export_zip";
		break;
		default: return false;
	}
	return true;
} 
function delayAct(user_id){
	document.adminForm.action = this.location.href;
	setTimeout("document.adminForm.submit()",500);
}

function delete_com(aid, cid, url){
	if(confirm("<?php echo "Confirm delete this comment"; ?>")){
		var a = new Ajax( url, { method: 'get', update: $('delete_comment_'+cid+'_'+aid)}).request();
	}
}

</script>