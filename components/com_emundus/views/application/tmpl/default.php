<link rel="stylesheet" type="text/css" href= "<?php echo JURI::Base().'media/com_emundus/css/emundus_application.css'; ?>" media="screen"/>
<?php
defined('_JEXEC') or die('Restricted access'); 

$itemid 	= JRequest::getVar('Itemid', null, 'GET', 'none',0);
$view 		= JRequest::getVar('view', null, 'GET', 'none',0);
$task 		= JRequest::getVar('task', null, 'GET', 'none',0);
$tmpl 		= JRequest::getVar('tmpl', null, 'GET', 'none',0);
 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');

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

?>
<form action="" name="applicant_form" method="POST" onsubmit="return OnSubmitForm();" >
<div id="identity_card">
	<div id="left" class="column">
		<div id="applicant">
			<div class="title"><?php echo JText::_('APPLICANT'); ?></div>
			<div class="content">
				<input id="cb<?php echo $this->student->id; ?>" type="checkbox" checked="" value="<?php echo $this->student->id; ?>" name="uid[]" style="display: none;">
				<div id="photo">
					<?php 
						if(!empty($this->userInformations["filename"])) {
							echo'<img id="image" style="border:0;" src="'.JURI::Base().EMUNDUS_PATH_REL.$this->student->id.'/'.$this->userInformations["filename"].'" width="50%">'; 
						}else if(!empty($this->userInformations["gender"])){
							echo'<img id="image" style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/'.strtolower($this->userInformations["gender"]).'_user.png" style="padding:10px 0 0 10px; width:120px;">';
						}
					echo '<div id="ID">'.$this->student->id.'</div>';
					?>
				</div>
				<div id="informations">
					<ul>
					<?php
						foreach ($this->profile as $key => $value) {
							echo '<li><div id="'.$key.'" class="sub_title">'.JText::_(strtoupper($key)).'</div> : '.$value.'</li>';
						}
						foreach ($this->userDetails as $details) {
							//$params = json_decode($details->params); print_r($params);
							if ($details->element_plugin == "date") {
								$params = json_decode($details->params);
								$value = strftime($params->date_form_format, strtotime($details->element_value));
								if ($details->element_name == "birth_date") {
									$value .= ' ('.age($this->userInformations['birthdate']).' '.JText::_('YEARS_OLD').')';
								}
							} else
								$value = $details->element_value;
							echo '<li><div id="'.$details->element_name.'" class="sub_title">'.$details->element_label.'</div> : '.$value.'</li>';
						}
							echo '<li><a href="mailto:'.$this->student->email.'">'.$this->student->email.'</a></li>';
							echo '<li><div class="sub_title">'.JText::_('PROFILE').'</div> : '.$this->userInformations['profile'].'</li>';
					?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div id="center" class="column">
		<div class="title"><?php echo JText::_('CAMPAIGN'); ?></div>
		<div class="content">
			<?php
			foreach($this->userCampaigns as $campaign){
				$info= '<ul>';
					$info.= '<li><div class="sub_title">'.JText::_('ACADEMIC_YEAR').'</div> : '.$campaign->year.'</li>';
					if($campaign->submitted==1){
						$info.= '<li><div class="sub_title">'.JText::_('SUBMITTED').'</div> : '.JText::_('JYES').'</li>';
						$info.= '<li><div class="sub_title">'.JText::_('DATE_SUBMITTED').'</div> : '.JHtml::_('date', $campaign->date_submitted, JText::_('DATE_FORMAT_LC2')).'</li>';
					}else{
						$info.= '<li><div class="sub_title">'.JText::_('SUBMITTED').'</div> : '.JText::_('JNO').'</li>';
					}
					if(!empty($campaign->result_sent) && $campaign->result_sent==1){
						$info.= '<li><div class="sub_title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('SENT').'</li>';
						$info.= '<li><div class="sub_title">'.JText::_('DATE_RESULT_SENT').'</div> : '.JHtml::_('date', $campaign->date_result_sent, JText::_('DATE_FORMAT_LC2')).'</li>';
					}else{
						$info.= '<li><div class="sub_title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('NOT_SENT').'</li>';
					}
				$info.= '</ul>';
				
				echo'<div class="icon">';
					if($campaign->submitted==0){
						$contenu ='<div class="sub_title">'.JText::_('SUBMITTED').'</div> : '.JText::_('JNO').'</li>';
						?>
							<a onMouseOver="tooltip(this, '<?php echo htmlentities($contenu); ?>');" href="#" title="" >
						<?php
						echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/publish_x.png" style="margin-right:20px;" />';
						echo '</a>';
					}else{
						if($campaign->result_sent==0){
							$contenu ='<div class="sub_title">'.JText::_("SUBMITTED").'</div> : '.JText::_("JYES").'</li>';
							?>
							<a onMouseOver="tooltip(this, '<?php echo htmlentities($contenu); ?>');" href="#" title="" >
							<?php
								echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/tick.png" />
							</a>';
							$contenu ='<div class="sub_title">'.JText::_("RESULT_SENT").'</div> : '.JText::_("JNO").'</li>';
							?>
							<a onMouseOver="tooltip(this, '<?php echo htmlentities($contenu); ?>');" href="#" title="" >
							<?php
								echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/email_not_send.png" />
							</a>';
						}else if($campaign->result_sent==1){
							$contenu = '<div class="sub_title">'.JText::_("SUBMITTED").'</div> : '.JText::_("JYES").'</li>';
							?>
							<a onMouseOver="tooltip(this, '<?php echo htmlentities($contenu); ?>');" href="#" title="" >
							<?php
								echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/tick.png" />
							</a>';
							$contenu = '<div class="sub_title">'.JText::_("RESULT_SENT").'</div> : '.JText::_("JYES").'</li>';
							?>
							<a onMouseOver="tooltip(this, '<?php echo htmlentities($contenu); ?>');" href="#" title="" >
							<?php
							echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/email_send.png" />';
						}
					}
				echo'</div>';
				?>
				<a onMouseOver="tooltip(this, '<?php echo htmlentities($info); ?>');" href="#" title="" >
				<?php	
					echo'<div class="title-campaign">'.$campaign->label.'</div>
				</a>';
			}
			?>
		</div>
	</div>
</div>

<div id="accordion">
	<h2 onClick="setCookie('current_display',0,20);"><?php echo JText::_('ACCOUNT'); ?></h2>
	<div id="em_application_forms" class="content">
	<table class="adminlist">
			<thead>
				<tr>
					<th><strong><?php echo JText::_('USERNAME'); ?></strong></th>
					<th><strong><?php echo JText::_('ACCOUNT_CREATED_ON');?></strong></th>
					<th><strong><?php echo JText::_('LAST_VISIT');?></strong></th>
					<th><strong><?php echo JText::_('STATUS');?></strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					
					<td class="center">
						<?php 
						if($this->current_user->authorise('core.manage', 'com_users'))
							echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="'.JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid='.$this->student->id.'&tmpl=component').'">'. $this->student->username .'</a>';
						else
							echo $this->student->username;
						?>
					</td>
					<td class="center">
						<?php echo JHtml::_('date', $this->student->registerDate, JText::_('DATE_FORMAT_LC2')); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('date', $this->student->lastvisitDate, JText::_('DATE_FORMAT_LC2')); ?>
					</td>
					<td class="center">
						<?php 
						if (isset($this->logged[0]->logoutLink)) 
							echo '<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/green.png" alt="'.JText::_('ONLINE').'" title="'.JText::_('ONLINE').'" />';
						else
							echo '<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/red.png" alt="'.JText::_('OFFLINE').'" title="'.JText::_('OFFLINE').'" />';
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<h2 onClick="setCookie('current_display',1,20);">
		<?php echo JText::_('ATTACHMENTS').' - '.$this->attachmentsProgress." % ".JText::_("SENT"); ?>
	</h2>	
	
	<div id="em_application_attachments" class="content">
		<div class="actions">
			<input type="image" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('DELETE_SELECTED_ATTACHMENTS')."</div>"; ?>');" onClick="document.pressed=this.name" name="delete_attachments" src="<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/delete_attachments2.png" />
			<a onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('UPLOAD_FILE_FOR_STUDENT')."</div><BR />".JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK'); ?>');"
			<?php
				if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
					echo 'class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8},onClose:function(){delayAct('.$this->student->id.');}}" href="'.JURI::Base().'/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $this->student->id.'&student_id='. $this->student->id.'&tmpl=component&iframe=1">
						<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/attachment.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="30px"/>
						</a> ';
				if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
			?>
		</div>
		
		<?php
		if(count($this->userAttachments) > 0) { 
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
				echo '<div id="checkall-attachment"><input type="checkbox" name="attachments" id="checkall1" onClick="check_all(this.id)"/><label for="checkall1"><strong>'.JText::_('SELECT_ALL').'</strong></label></div>';
			$i=0;
			foreach($this->userAttachments as $attachment){
				$path = $attachment->id == 27?EMUNDUS_PATH_REL."archives/".$attachment->filename:EMUNDUS_PATH_REL.$this->student->id.'/'.$attachment->filename;
				$img_missing = (!file_exists($path))?'<img style="border:0;" src="media/com_emundus/images/icones/agt_update_critical.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
				$img_dossier = (is_dir($path))?'<img style="border:0;" src="media/com_emundus/images/icones/dossier.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
				$img_locked = (strpos($attachment->filename, "_locked") > 0)?'<img src="media/com_emundus/images/icones/encrypted.png" />':"";

				$info = '<div id="hiddenMoreInfoAttachment-'.$i.'">';
					$info .= '<ul>';		                	
					$info .= '<li><div class="sub_title">'. $img_locked . JText::_('ATTACHMENT_FILENAME').'</div> : '.$attachment->filename.'</li>';
					if(!empty($attachment->description)){
						$info.='<li><div class="sub_title">'.JText::_('ATTACHMENT_DESCRIPTION').'</div> : '.$attachment->description.'</li>';
					}
					$info .= '<li><div class="sub_title">'.JText::_('ATTACHMENT_DATE').'</div> : '.JHtml::_('date', $attachment->timedate, JText::_('DATE_FORMAT_LC2')).'</li>';
					$info .= '<li><div class="sub_title">'.JText::_('CAMPAIGN').'</div> : '.$attachment->campaign_label.'</li>';
					$info .= '<li><div class="sub_title">'.JText::_('ACADEMIC_YEAR').'</div> : '.$attachment->year.'</li>';
					$info .= '</ul>';
				$info .= '</div>';
				
				echo '<div class="attachment_name">';
				if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
					echo '<input type="checkbox" name="attachments[]" id="aid'.$attachment->aid.'" value="'.$attachment->aid.'" />';
				echo '<a href="'.JURI::Base().$path.'" target="_blank" onMouseOver="tooltip(this, \''.htmlentities($info).'\');"';
				echo '<label for="aid_'.$i.'">'.$img_dossier.' '. $img_locked.' '.$img_missing.' '.$attachment->value.'</label>';
				echo '</a> ';
					//echo '<input type="image" onMouseOver="tooltip(this, \'<div>'.JText::_('DELETE_ATTACHMENT').'</div>\');" onClick="document.pressed=this.name" name="delete_attachments" src="'.JURI::Base().'/media/com_emundus/images/icones/delete_attachments.png" width="5%" />';
				echo '</div>';
				$i++;
			}
		} else echo JText::_('NO_ATTACHMENT');
		?>
	</div>
	
	<h2><?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?>
	</h2>
	
	<div id="em_application_forms" class="content">
		<div class="actions">
			<a onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('DOWNLOAD_APPLICATION_FORM')."</div>"; ?>');"
			<?php
				echo 'href="index.php?option=com_emundus&task=pdf&user='.$this->student->id.'" class="appsent" target="_blank"  onClick="setCookie(\'current_display\',2,20);">
					<img border="0" src="'.JURI::Base().'/media/com_emundus/images/icones/pdf_form_64x64.png" width="30px" /></a> '; ?>
			<input type="image" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('EXPORT_TO_ZIP')."</div>"; ?>');"
			<?php
				echo 'src="'.JURI::Base().'/media/com_emundus/images/icones/zip2.png" onClick="setCookie(\'current_display\',2,20);document.pressed=this.name" name="export_zip" />'; ?>
		</div>
		<?php echo $this->forms; ?>
	</div>
		
	<h2 id="em_application_forms_title"><!--<input type="checkbox" name="comments" id="checkall2" onClick="check_all(this.id)"/>-->
		<?php echo JText::_('COMMENTS'); ?>
	</h2>
	
	<div id="em_application_comments" class="content">
		<div class="actions">
			<?php
			echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8},onClose:function(){delayAct('.$this->student->id.');}}" href="'.JURI::Base().'/index.php?option=com_fabrik&c=form&view=form&formid=89&tableid=92&rowid=&jos_emundus_comments___applicant_id[value]='. $this->student->id.'&student_id='. $this->student->id.'&tmpl=component&iframe=1">'; ?>
			<img style="border:0;" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('ADD_COMMENT')."</div>"; ?>');" onClick="setCookie('current_display',3,20); document.pressed=this.name" name="add_comment" src="<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/add_comment.png" width="30px" />
			</a>
		</div>
		<?php
		if(count($this->userComments) > 0) { 
			$i=0;
			foreach($this->userComments as $comment){
				
				echo'<div class="comment" id="comment-box_'.$comment->id.'">';
					echo '<div class="comment_content" id="comment_content_'.$comment->id.'">
							<div class="comment_icon" id="comment_'.$comment->id.'">
								<img src="'.JURI::Base().'/media/com_emundus/images/icones/button_cancel.png" onClick="if (confirm('.htmlentities('"'.JText::_("DELETE_COMMENT_CONFIRM").'"').')) {deleteComment('.$comment->id.');}"/>
							</div>
							'.$comment->comment.'
							</div>';
					echo'<div class="comment_details">';
					echo '<ul>';
						echo '<li><div class="sub_title">'.JText::_('COMMENT_REASON').'</div> : '.$comment->reason.'</li>';
						echo '<li><div class="sub_title"> - '.JText::_('COMMENT_DATE').'</div> '.JHtml::_('date', $comment->date, JText::_('DATE_FORMAT_LC2')).'</li>';
						echo '<li><div class="sub_title">'.JText::_('COMMENT_BY').'</div> '.$comment->name.'</li>';
					echo '</ul>';
					echo'</div>';
				echo'</div>';
				$i++;
			}
		} else echo JText::_('NO_COMMENT');
		?>
	</div>

</div>
<input type="hidden" name="sid" value="<?php echo $this->student->id; ?>" />
<input type="hidden" value="" name="task">
<input type="hidden" value="<?php echo $itemid; ?>" name="itemid">
<input type="hidden" value="<?php echo $view; ?>" name="view">
<input type="hidden" value="<?php echo $tmpl; ?>" name="tmpl">
</form>
<script>
var current_display = 2;

window.addEvent('domready', function(){
	application = new Fx.Accordion($('accordion'), '#accordion h2', '#accordion .content', {
		display: getCookie('current_display'),
		alwaysHide: true
	});
});

function tooltip(element, text){
	var is_ie = ((navigator.userAgent.toLowerCase().indexOf("msie") != -1) && (navigator.userAgent.toLowerCase().indexOf("opera") == -1));
	
	//Suppression du title de l'élément pour éviter une superposition
	element.title = '';
	
	//Création d'une div provisoire
	var tooltip = document.createElement('div');
	tooltip.innerHTML = text;
	tooltip.id = 'tooltip';
	tooltip.style.display = 'none';
	tooltip.style.opacity = '0';
	tooltip.style.filter = 'alpha(opacity=0)';
	document.body.appendChild(tooltip);
	
	tooltip.style.position = 'absolute';
	document.onmousemove = function(e){
		x = (!is_ie ? e.pageX : event.x+document.documentElement.scrollLeft)+15;
		y = (!is_ie ? e.pageY : event.y+document.documentElement.scrollTop)+15;
		
		var windowWidth = (!is_ie ? window.innerWidth : document.documentElement.clientWidth);
		var windowHeight = (!is_ie ? window.innerHeight : document.documentElement.clientHeight);
		var scrollLeft = document.documentElement.scrollLeft;
		var scrollTop = document.documentElement.scrollTop;
		
		//Calcul des dimensions de l'tooltip
		tooltip.style.display = '';
		var infoWidth = tooltip.offsetWidth;
		var infoHeight = tooltip.offsetHeight;
		tooltip.style.display = 'none';
		
		/*On vérifie que l'tooltip ne sorte pas de la fenêtre*/
		if((x+infoWidth) > (windowWidth+scrollLeft)){
			x = (!is_ie ? e.pageX : event.x+document.documentElement.scrollLeft)-infoWidth-5;
		}
		if((y+infoHeight) > (windowHeight+scrollTop)){
			y = (!is_ie ? e.pageY : event.y+document.documentElement.scrollTop)-infoHeight-5;
		}
		
		tooltip.style.left = x+'px';
		tooltip.style.top = y+'px';
		tooltip.style.display = '';
	}

	for(i=0; i<=100; i+=10){
		var time = ((i/20)*30);
		setTimeout('opacity('+i+', \'tooltip\');', time);
	}


	//Ajout de la fermeture lorsque la souris quitte l'élément
	element.onmouseout = function(){
		for(i=0; i<=100; i+=10){
			var time = ((i/20)*30);
			var opacity = (100-i);
			setTimeout('opacity('+opacity+', \'tooltip\', 1);', time);
		}
	};
	
	//Fonction servant à faire varier l'opacité
	opacity = function(opacity, id, close){
			var tooltip = document.getElementById(id);
			tooltip.style.opacity = (opacity/100);
			tooltip.style.filter = 'alpha(opacity='+opacity+')';
			if(opacity == 0 && close){
				document.body.removeChild(tooltip); //Suppression de la div provisoire
				document.onmousemove = '';
			}
	}
}

function check_all(id) {
	var checked = document.getElementById(id).checked;
	var name = document.getElementById(id).name;
	if(name=="attachments"){
		var checkbox = document.getElementsByName('attachments[]');
		for (i=0;i< checkbox.length;i++){
			checkbox[i].checked=checked;
		}
	}
	if(name=="comments"){
		var checkbox = document.getElementsByName('cid[]');
		for (i=0;i< checkbox.length;i++){
			checkbox[i].checked=checked;
		}
	}
}
function OnSubmitForm() { 
	if(typeof document.pressed !== "undefined") { 
		document.applicant_form.task.value = "";
		var button_name=document.pressed.split('|');
		switch(button_name[0]) {
			case "export_zip": 
				document.applicant_form.task.value = "export_zip";
				document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=export_zip";
			break;
			case "export_to_xls": 
				document.applicant_form.task.value = "transfert_view";
				document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=transfert_view&v=<?php echo $view; ?>";
			break;
			case "applicant_email": 
				document.applicant_form.task.value = "applicantEmail";
				document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=applicantEmail";
			break;
			case "default_email": 
				if (confirm("<?php echo JText::_("CONFIRM_DEFAULT_EMAIL"); ?>")) {
					document.applicant_form.task.value = "defaultEmail";
					document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=defaultEmail";
				} else 
					return false;
			break;
			case "delete_attachments": 
				document.applicant_form.task.value = "delete_attachments";
				if (confirm("<?php echo JText::_("CONFIRM_DELETE_SELETED_ATTACHMENTS"); ?>")) {
	        		document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&task=delete_attachments&Itemid=<?php echo $itemid; ?>&sid=<?php echo $this->student->id; ?>";
			 	} else 
			 		return false;
			break;
			default: return false;
		}
		return true;
	}
}

function getXMLHttpRequest() {
	var xhr = null;
	 
	if (window.XMLHttpRequest || window.ActiveXObject) {
		if (window.ActiveXObject) {
			try {
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
		} else {
			xhr = new XMLHttpRequest();
		}
	} else {
		alert("Votre navigateur ne supporte pas l\'objet XMLHTTPRequest...");
		return null;
	}
	 
	return xhr;
}
		
function deleteComment(comment_id){
	var xhr = getXMLHttpRequest();
	xhr.onreadystatechange = function()
	{
		if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
		{
			if(xhr.responseText!="SQL Error"){
				var comment = (($('comment_'+comment_id).parentNode).parentNode).id;
				var comment_content = ($('comment_'+comment_id).parentNode).id;
				var comment_icon = $('comment_'+comment_id);
				var i;
				for (i=0;i<comment_icon.childNodes.length;i++)
				{
					comment_icon.childNodes[i].src = "<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/trash.png";
					comment_icon.childNodes[i].onclick = null;
				}
				$(comment).style.background="#B0B4B3";
				$(comment_content).style.background="#B0B4B3";
				$(comment).style.color="#FFFFFF";
				$(comment_content).style.color="#FFFFFF";
				$(comment).style.textDecoration="line-through";
				$(comment_content).style.textDecoration="line-through";
			}else{
				alert(xhr.responseText);
			}
		}
	};
	xhr.open("GET", "index.php?option=com_emundus&controller=application&format=raw&task=deletecomment&Itemid=<?php echo $itemid; ?>&comment_id="+comment_id, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send("&comment_id="+comment_id);
}

function delayAct(user_id){
	document.applicant_form.action = "index.php?option=com_emundus&view=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&sid=<?php echo $this->student->id; ?> <?php if(!empty($tmpl)){ echo'&tmpl='.$tmpl; }?>";
	setTimeout("document.applicant_form.submit()",10) 
}

function setCookie(pLabel, pVal, psec)
{
	var tExpDate=new Date();
	tExpDate.setTime( tExpDate.getTime()+(psec*1000) );
	document.cookie= pLabel + "=" +escape(pVal)+ ( (psec==null) ? "" : ";expires="+ tExpDate.toGMTString() );
}	

function getCookie(c_name)
{
	var c_value = document.cookie;
	var c_start = c_value.indexOf(" " + c_name + "=");
	if (c_start == -1){
		c_start = c_value.indexOf(c_name + "=");
	}
	if (c_start == -1){
		c_value = null;
	}else{
		c_start = c_value.indexOf("=", c_start) + 1;
		var c_end = c_value.indexOf(";", c_start);
		if (c_end == -1){
			c_end = c_value.length;
		}
		c_value = unescape(c_value.substring(c_start,c_end));
	}
	return c_value;
}
</script>