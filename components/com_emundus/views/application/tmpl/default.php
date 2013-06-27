<style type="text/css">
/* identity_card */
	#identity_card .column {
		position: relative;
		float: left;
		margin-bottom : 10px;
		/*min-height:310px;*/
	}
	
	.title {
		margin:0;
		padding:5px;
		font: 14px Helvetica, Arial, sans-serif;
		color:#fff;
		background-color:#9DADC6;
		border:1px solid #8E98A4;
		border-bottom:0;
		text-align:center;
	}
	
	.sub_title {
		display : inline;
		font-weight:bold;
	}
	/* CENTER */
		#center {
			width:50%;
		}
		#center .content li {
			list-style-type: none;
			margin :0;
			padding:0;
		}
		#center .content .title-campaign {
			border-bottom:1px solid #9DADC6;
			font-weight: bold;
			margin-bottom:2px;
		}
		#center .content a{
			text-decoration:none;
			color : #555562;
		}
	/* LEFT */
		#left {
			width:50%;
		}
		/* APPLICANT */
			#applicant {
				height: auto;
				overflow:hidden;
			}
			#applicant .content #photo {
				float : left;
				width:30%;
			}
			#applicant .content #image {
				display: block;
				margin-left: auto;
				margin-right: auto 
			}
			#applicant .content #informations {
				float : right;
				width:68%;
			}
			#applicant .content li {
				list-style-type: none;
				margin :0;
				padding:0;
			}
			#applicant .content #photo #ID {
				font: 14px Helvetica, Arial, sans-serif;
				font-weight:bold;
				text-align:center;
				margin-top:4px;
				border:2px solid #8E98A4;
				border-radius: 10px 10px 10px 10px;
				display: block;
				margin-left: auto;
				margin-right: auto 
			}
		/* ACTIONS */
			#actions {
				height: auto;
				margin-top:5px;
			}
			#actions .title{
				margin:0;
				padding:5px;
				font: 14px Helvetica, Arial, sans-serif;
				color:#fff;
				background-color:#9DADC6;
				border-bottom:1px solid #8E98A4;
				border-bottom:0;
				text-align:center;
			}
			#actions span{
				display:inline-block;
			}
			#actions .content li {
				list-style-type: none;
				margin :0;
				padding:0;
				display:inline;
				margin-right:5px;
			}
	
/* ACCORDION */
	#accordion  {
		clear: both;
		margin: 20px 0 0;
		width:100%;
		/*max-width: 400px;*/
	}
	#accordion h2 {
		background: #6B7B95;
		color:#fff;
		cursor: pointer;
		font: 14px Helvetica, Arial, sans-serif;
		margin: 0 0 4px 0;
		padding: 3px 5px 1px;
	}
	#accordion .attachment_name {
		color:#fff;
		border-bottom:1px solid #8E98A4;
		width:100%;
	}
	#accordion #hiddenMoreInfoAttachment{
		visibility:hidden;
		display:none;
	}
	#accordion li {
		list-style-type: none;
		margin :0;
		padding:0;
		padding-left:5px;
	}

	#em_application_Attachments a{
		text-decoration:none;
		font: 14px Helvetica, Arial, sans-serif;
		color:#555562;
	}

	.comment a{
		text-decoration:none;
		font: 14px Helvetica, Arial, sans-serif;
		color:#555562;
	}
	.comment {
		-moz-border-radius:7px;
		-webkit-border-radius:7px;
		border-radius:7px;
		background:#EEF0FB;
	}
	
	.comment_content {
		background:#E9ECFF;
		border-bottom:1px solid #8E98A4;
		padding-left:5px;
		padding-bottom:5px;
		font-size:16px;
	}
	.comment_details li{
		display:inline;
	}
	
/* tooltip */
div#tooltip{
	background-color: #EAF4F8;
	border: 1px solid #555555;
	width: auto;
	padding: 0px 3px 0px 3px;
	text-align: justify;
	font-size: 140%;
}
div#tooltip li {
	list-style-type: none;
	margin :0;
	padding:0;
}
</style>
<?php  
defined('_JEXEC') or die('Restricted access'); 

$itemid 	= JRequest::getVar('Itemid', null, 'GET', 'none',0);
$view 		= JRequest::getVar('view', null, 'GET', 'none',0);
$task 		= JRequest::getVar('task', null, 'GET', 'none',0);
 
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
							echo'<img id="image" src="'.JURI::Base().EMUNDUS_PATH_REL.$this->student->id.'/'.$this->userInformations["filename"].'" width="50%">'; 
						}else if(!empty($this->userInformations["gender"])){
							echo'<img id="image" src="'.JURI::Base().'media/com_emundus/images/icones/'.strtolower($this->userInformations["gender"]).'_user.png" style="padding:10px 0 0 10px; width:120px;">';
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
							/*echo '<li><div class="sub_title">'.JText::_('ACCOUNT_CREATED_ON').'</div> : '.date(JText::_('DATE_FORMAT_LC2'), strtotime($this->student->registerDate)).'</li>';
							echo '<li><div class="sub_title">'.JText::_('LAST_VISIT').'</div> : '.date(JText::_('DATE_FORMAT_LC2'), strtotime($this->student->lastvisitDate)).'</li>';*/
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
						$info.= '<li><div class="sub_title">'.JText::_('DATE_SUBMITTED').'</div> : '.date('Y-m-d',strtotime($campaign->date_submitted)).'</li>';
					}else{
						$info.= '<li><div class="sub_title">'.JText::_('SUBMITTED').'</div> : '.JText::_('JNO').'</li>';
					}
					if(!empty($campaign->result_sent) && $campaign->result_sent==1){
						$info.= '<li><div class="sub_title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('SENT').'</li>';
						$info.= '<li><div class="sub_title">'.JText::_('DATE_RESULT_SENT').'</div> : '.date('Y-m-d',strtotime($campaign->date_result_sent)).'</li>';
					}else{
						$info.= '<li><div class="sub_title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('NOT_SENT').'</li>';
					}
				$info.= '</ul>';
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
	<h2><?php echo JText::_('ACCOUNT'); ?></h2>
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
							echo '<img src="'.JURI::Base().'/media/com_emundus/images/icones/green.png" alt="'.JText::_('ONLINE').'" title="'.JText::_('ONLINE').'" />';
						else
							echo '<img src="'.JURI::Base().'/media/com_emundus/images/icones/red.png" alt="'.JText::_('OFFLINE').'" title="'.JText::_('OFFLINE').'" />';
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<h2><?php echo JText::_('ATTACHMENTS').' - '.$this->attachmentsProgress." % ".JText::_("SENT"); ?>
			<a onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('UPLOAD_FILE_FOR_STUDENT')."</div><BR />".JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK'); ?>');"
		<?php
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
				echo 'class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8},onClose:function(){delayAct('.$this->student->id.');}}" href="'.JURI::Base().'/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $this->student->id.'&student_id='. $this->student->id.'&tmpl=component">
					<img src="'.JURI::Base().'/media/com_emundus/images/icones/attach_22x22.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" />
					</a> ';
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
		?>
			<input type="image" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('DELETE_SELECTED_ATTACHMENTS')."</div>"; ?>');" onClick="document.pressed=this.name" name="delete_attachments" src="<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/delete_attachments.png" width="2%"/>
	</h2>
	<div id="em_application_attachments" class="content">
		<?php
		if(count($this->userAttachments) > 0) { 
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
				echo '<input type="checkbox" name="attachments" id="checkall1" onClick="check_all(this.id)"/><label for="checkall1"><strong>'.JText::_('SELECT_ALL').'</strong></label>';;
			$i=0;
			foreach($this->userAttachments as $attachment){
				$path = $attachment->id == 27?EMUNDUS_PATH_REL."archives/".$this->student->id.'/'.$attachment->filename:EMUNDUS_PATH_REL.$this->student->id.'/'.$attachment->filename;
				$img_missing = (!file_exists($path))?'<img src="media/com_emundus/images/icones/agt_update_critical.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
				
				$img_locked = (strpos($attachment->filename, "_locked") > 0)?'<img src="'.$this->baseurl.'media/com_emundus/images/icones/encrypted.png" />':"";

				$info = '<div id="hiddenMoreInfoAttachment-'.$i.'">';
					$info .= '<ul>';		                	
					$info .= '<li><div class="sub_title">'. $img_locked . JText::_('ATTACHMENT_FILENAME').'</div> : '.$attachment->filename.'</li>';
					if(!empty($attachment->description)){
						$info.='<li><div class="sub_title">'.JText::_('ATTACHMENT_DESCRIPTION').'</div> : '.$attachment->description.'</li>';
					}
					$info .= '<li><div class="sub_title">'.JText::_('ATTACHMENT_DATE').'</div> : '.date('Y-m-d',strtotime($attachment->timedate)).'</li>';
					$info .= '<li><div class="sub_title">'.JText::_('CAMPAIGN').'</div> : '.$attachment->campaign_label.'</li>';
					$info .= '<li><div class="sub_title">'.JText::_('ACADEMIC_YEAR').'</div> : '.$attachment->year.'</li>';
					$info .= '</ul>';
				$info .= '</div>';
				
				echo '<div class="attachment_name">';
				if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
					echo '<input type="checkbox" name="aid[]" id="aid'.$attachment->aid.'" value="'.$attachment->aid.'" />';
				echo '<a href="'.JURI::Base().$path.'" target="_blank" onMouseOver="tooltip(this, \''.htmlentities($info).'\');"';
				echo '<label for="aid_'.$i.'">'. $img_locked.' '.$img_missing.' '.$attachment->value.'</label>';
				echo '</a> ';
					//echo '<input type="image" onMouseOver="tooltip(this, \'<div>'.JText::_('DELETE_ATTACHMENT').'</div>\');" onClick="document.pressed=this.name" name="delete_attachments" src="'.JURI::Base().'/media/com_emundus/images/icones/delete_attachments.png" width="5%" />';
				echo '</div>';
				$i++;
			}
		} else echo JText::_('NO_ATTACHMENT');
		?>
	</div>
	
	<h2><?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?>
		<a onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('DOWNLOAD_APPLICATION_FORM')."</div>"; ?>');"
		<?php
			echo 'href="index.php?option=com_emundus&task=pdf&user='.$this->student->id.'" class="appsent" target="_blank">
				<img border="0" src="'.JURI::Base().'/media/com_emundus/images/icones/pdf.png" />
			</a>'; ?>
		<input type="image" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('EXPORT_SELECTED_TO_ZIP')."</div>"; ?>');"
		<?php
			echo 'src="'.JURI::Base().'/media/com_emundus/images/icones/ZipFile-selected_48.png" onClick="document.pressed=this.name" name="export_zip" width="2%">'; ?>
	</h2>
	<div id="em_application_forms" class="content"><?php echo $this->forms; ?></div>

	<h2><!--<input type="checkbox" name="comments" id="checkall2" onClick="check_all(this.id)"/>--><?php echo JText::_('COMMENTS'); ?>
		<input type="image" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('DELETE_SELECTED_COMMENTS')."</div>"; ?>');" onClick="document.pressed=this.name" name="delete_comments" src="<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/delete_comments.png" width="2%"/>
		<input type="image" onMouseOver="tooltip(this, '<?php echo "<div id=title>".JText::_('ADD_COMMENT')."</div>"; ?>');" onClick="document.pressed=this.name" name="add_comment" src="<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/add_comment.png" width="2%"/>
	</h2>
	<div id="em_application_comments" class="content">
		<?php
		if(count($this->userComments) > 0) { 
			$i=0;
			foreach($this->userComments as $comment){
				
				echo'<div class="comment">';
					echo'<input style="display:none;" type="checkbox" name="cid[]" id="cid_'.$i.'" value="'.$comment->id.'" />';
					echo '<div class="comment_content">'.$comment->comment.'</div>';
					echo'<div class="comment_details">';
					echo '<ul>';
						echo '<li><div class="sub_title">'.JText::_('COMMENT_REASON').'</div> : '.$comment->reason.'</li>';
						echo '<li><div class="sub_title"> - '.JText::_('COMMENT_DATE').'</div> '.date('Y-m-d',strtotime($comment->date)).'</li>';
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
<input type="hidden" name="aid" value="<?php echo $this->student->id; ?>" />
<input type="hidden" value="" name="task">
<input type="hidden" value="<?php echo $itemid; ?>" name="itemid">
<input type="hidden" value="<?php echo $view; ?>" name="view">
</form>
<script>
window.addEvent('domready', function(){
  new Fx.Accordion($('accordion'), '#accordion h2', '#accordion .content');
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
		var checkbox = document.getElementsByName('aid[]');
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
			case "delete_comments": 
				document.applicant_form.task.value = "delete_comments";
				if (confirm("<?php echo JText::_("CONFIRM_DELETE_SELETED_COMMENTS"); ?>")) {
	        		document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&task=delete_comments&Itemid=<?php echo $itemid; ?>";
			 	} else 
			 		return false;
			break;
			case "add_comment": 
				document.applicant_form.task.value = "add_comment";
				if (confirm("<?php echo JText::_("CONFIRM_ADD_COMMENT"); ?>")) {
	        		document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&task=add_comment&Itemid=<?php echo $itemid; ?>";
			 	} else 
			 		return false;
			break;
			default: return false;
		}
		return true;
	}
} 
</script>