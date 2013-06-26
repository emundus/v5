<style type="text/css">
/* identity_card */
	#identity_card .column {
		position: relative;
		float: left;
		margin-bottom : 10px;
		min-height:310px;
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
	
	#title {
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
		#center .content #title-campaign {
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
	
/* ACCORDION */
	#accordion  {
		clear: both;
		margin: 20px 0 0;
		width:100%;
		/*max-width: 400px;*/
	}
	#accordion H2 {
		background: #6B7B95;
		color:#fff;
		cursor: pointer;
		font: 14px Helvetica, Arial, sans-serif;
		margin: 0 0 4px 0;
		padding: 3px 5px 1px;
	}
	#accordion #attachement_name {
		color:#fff;
		border-bottom:1px solid #8E98A4;
		width:100%;
	}
	#accordion #hiddenMoreInfoAttachement{
		visibility:hidden;
		display:none;
	}
	#accordion li {
		list-style-type: none;
		margin :0;
		padding:0;
		padding-left:5px;
	}

	#em_application_attachements a{
		text-decoration:none;
		font: 14px Helvetica, Arial, sans-serif;
		color:#555562;
	}

	#comment a{
		text-decoration:none;
		font: 14px Helvetica, Arial, sans-serif;
		color:#555562;
	}
	#comment {
		border-bottom:1px solid #8E98A4;
	}
/* INFOBULLE */
div#infobulle{
	background-color: #EAF4F8;
	border: 1px solid #555555;
	width: auto;
	padding: 0px 3px 0px 3px;
	text-align: justify;
	font-size: 140%;
}
div#infobulle li {
	list-style-type: none;
	margin :0;
	padding:0;
}
</style>
<?php  
defined('_JEXEC') or die('Restricted access'); 
$current_user = & JFactory::getUser();
// if(!EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id)) die("ACCESS_DENIED");
	 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
?>

<div id="identity_card">
	<div id="left" class="column">
		<div id="applicant">
			<div class="title"><?php echo JText::_('APPLICANT'); ?></div>
			<div class="content">
				<div id="photo">
					<?php 
					if(in_array ('photo',$this->informations)){
						if(!empty($this->userInformations[0]->filename)){
							echo'<img id="image" src="'.JURI::Base().'images/emundus/files/'.$this->user_id.'/'.$this->userInformations[0]->filename.'" width="50%">'; 
						}else if(!empty($this->userInformations[0]->gender)){
							echo'<img id="image" src="'.JURI::Base().'media/com_emundus/images/icones/'.strtolower($this->userInformations[0]->gender).'_user.png" style="padding:10px 0 0 10px; width:120px;">';
						}
					}
					echo '<div id="ID">'.$this->user_id.'</div>';
					?>
				</div>
				<div id="informations">
					<ul>
						<?php
						if(in_array ('lastname',$this->informations)){
							?>
							<li>
							<?php
									echo '<div id="title">'.JText::_('LASTNAME').'</div> : '.$this->userInformations[0]->lastname;
							?>
							</li>
							<?php 
						}
						if(in_array ('firstname',$this->informations)){
						?>
							<li>
							<?php
								echo '<div id="title">'.JText::_('FIRSTNAME').'</div> : '.$this->userInformations[0]->firstname;
							?>
							</li>
						<?php 
						} 
						if(in_array ('email',$this->informations)){
						?>
							<li>
							<?php
								$email=$this->userInformations[0]->email;
								echo'<a href="mailto:'.$email.'">'.$email.'</a>';
							?>
							</li>
						<?php 
						} 
						if(in_array ('nationality',$this->informations)){
						?>
							<li>
							<?php
								echo '<div id="title">'.JText::_('NATIONALITY').'</div> : '.$this->userInformations[0]->nationality;
							?>
							</li>
						<?php 
						} 
						if(in_array ('birthdate',$this->informations)){
						?>
							<li>
							<?php
								$birthdate = new DateTime($this->userInformations[0]->birthdate);
								// $birthdate_explode = explode("/", $birthdate);
								$today = new DateTime();
								$age = $today->diff($birthdate);
								echo '<div id="title">'.JText::_('AGE').'</div> : '.$age->format('%y');
							?>
							</li>
							<?php 
						} 
						if(in_array ('registerDate',$this->informations)){
							?>
							<li>
							<?php
								echo  '<div id="title">'.JText::_('ACCOUNT_CREATED_ON').'</div> : '.date('Y-m-d',strtotime($this->userInformations[0]->registerDate));
							?>
							</li>
							<?php 
						}
						if(in_array ('profile',$this->informations)){
							?>
							<li>
							<?php
								echo  '<div id="title">'.JText::_('PROFILE').'</div> : '.$this->userInformations[0]->profile;
							?>
							</li>
							<?php 
						} ?>
					</ul>
				</div>
			</div>
		</div>
		<div id="actions">
			<div class="title"><?php echo JText::_('ACTIONS'); ?></div>
			<div class="content">
				<?php
				echo '<span class="editlinktip hasTip" title="'.JText::_('UPLOAD_FILE_FOR_STUDENT').'::'.JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK').'">';
				echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8},onClose:function(){delayAct('.$this->user_id.');}}" href="'.JURI::Base().'/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]='. $this->user_id.'&student_id='. $this->user_id.'&tmpl=component"><img src="'.JURI::Base().'/media/com_emundus/images/icones/attach_16x16.png" alt="'.JText::_('UPLOAD').'" title="'.JText::_('UPLOAD').'" width="16" height="16" align="bottom" /></a></span>';
				
				echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.JURI::Base().'/media/com_emundus/images/icones/ZipFile-selected_48.png" onClick="document.pressed=this.name" name="export_zip" width="32"></span><br />';
				
				echo '<span class="editlinktip hasTip" title="'.JText::_('DOWNLOAD_APPLICATION_FORM').'::">';
				echo '<a href="index.php?option=com_emundus&task=pdf&user='.$this->user_id.'" class="appsent" target="_blank"><img border="0" src="'.JURI::Base().'/media/com_emundus/images/icones/pdf.png" /></a></span>'; 
				?>
			</div>
		</div>
	</div>
	<div id="center" class="column">
		<div class="title"><?php echo JText::_('CAMPAIGN'); ?></div>
		<div class="content">
			<?php
			foreach($this->userCampaigns as $campaign){
				$bulle= '<ul>';
					$bulle.= '<li><div id="title">'.JText::_('ACADEMIC_YEAR').'</div> : '.$campaign->year.'</li>';
					if($campaign->submitted==1){
						$bulle.= '<li><div id="title">'.JText::_('SUBMITTED').'</div> : '.JText::_('JYES').'</li>';
						$bulle.= '<li><div id="title">'.JText::_('DATE_SUBMITTED').'</div> : '.date('Y-m-d',strtotime($campaign->date_submitted)).'</li>';
					}else{
						$bulle.= '<li><div id="title">'.JText::_('SUBMITTED').'</div> : '.JText::_('JNO').'</li>';
					}
					if(!empty($campaign->result_sent) && $campaign->result_sent==1){
						$bulle.= '<li><div id="title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('SENT').'</li>';
						$bulle.= '<li><div id="title">'.JText::_('DATE_RESULT_SENT').'</div> : '.date('Y-m-d',strtotime($campaign->date_result_sent)).'</li>';
					}else{
						$bulle.= '<li><div id="title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('NOT_SENT').'</li>';
					}
				$bulle.= '</ul>';
				?>
				<a onMouseOver="infobulle(this, '<?php echo htmlentities($bulle); ?>');" href="#" title="" >
				<?php
					echo'<div id="title-campaign">'.$campaign->label.'</div>
				</a>';
			}
			?>
		</div>
	</div>
</div>

<div id="accordion">
	<h2><input type="checkbox" name="attachements" id="checkall1" onClick="check_all(this.id)"/><?php echo JText::_('ATTACHEMENTS'); ?></h2>
	<div id="em_application_attachements" class="content">
		<?php 
		$i=0;
		foreach($this->userAttachements as $attachement){
			echo'<div id="attachement_name-'.$i.'">';
				$bulle='<div id="hiddenMoreInfoAttachement-'.$i.'">';
					$bulle.='<ul>';
						$bulle.='<li><div id="title">'.JText::_('ATTACHEMENT_FILENAME').'</div> : '.$attachement->filename.'</li>';
						if(!empty($attachement->description)){
							$bulle.='<li><div id="title">'.JText::_('ATTACHEMENT_DESCRIPTION').'</div> : '.$attachement->description.'</li>';
						}
						$bulle.='<li><div id="title">'.JText::_('ATTACHEMENT_DATE').'</div> : '.date('Y-m-d',strtotime($attachement->timedate)).'</li>';
						$bulle.='<li><div id="title">'.JText::_('CAMPAIGN').'</div> : '.$attachement->campaign_label.'</li>';
						$bulle.='<li><div id="title">'.JText::_('ACADEMIC_YEAR').'</div> : '.$attachement->year.'</li>';
					$bulle.='</ul>';
				$bulle.='</div>';
				
				echo'<div id="attachement_name">';
					echo'<input type="checkbox" name="aid[]" value="'.$attachement->aid.'" />';
					?>
					<a onMouseOver="infobulle(this, '<?php echo htmlentities($bulle); ?>');" href="#" title="" >
					<?php
						echo $attachement->value;
					echo'</a>';
				echo'</div>';
			echo'</div>';
			$i++;
		}
		?>
	</div>
	<h2><input type="checkbox" name="comments" id="checkall2" onClick="check_all(this.id)"/><?php echo JText::_('COMMENTS'); ?></h2>
	<div id="em_application_comments" class="content">
		<?php
		foreach($this->userComments as $comment){
			$bulle='<ul>';
				$bulle.='<li><div id="title">'.JText::_('COMMENT_REASON').'</div> : '.$comment->reason.'</li>';
				$bulle.='<li><div id="title">'.JText::_('COMMENT_DATE').'</div> : '.date('Y-m-d',strtotime($comment->date)).'</li>';
				$bulle.='<li><div id="title">'.JText::_('COMMENT_BY').'</div> : '.$comment->name.'</li>';
			$bulle.='</ul>';
			echo'<div id="comment">';
				echo'<input type="checkbox" name="cid[]" value="'.$comment->id.'" />';
				?>
				<a onMouseOver="infobulle(this, '<?php echo htmlentities($bulle); ?>');" href="#" title="" >
				<?php
					echo $comment->comment;
				echo'</a>
			</div>';
		}
		?>
	</div>
	
	<h2><?php echo JText::_('APPLICATION_FORM'); ?></h2>
	<div id="em_application_forms" class="content">cccc</div>
</div>
<script>
window.addEvent('domready', function(){
  new Fx.Accordion($('accordion'), '#accordion h2', '#accordion .content');
});


function infobulle(element, text){
	var is_ie = ((navigator.userAgent.toLowerCase().indexOf("msie") != -1) && (navigator.userAgent.toLowerCase().indexOf("opera") == -1));
	
	//Suppression du title de l'élément pour éviter une superposition
	element.title = '';
	
	//Création d'une div provisoire
	var infobulle = document.createElement('div');
	infobulle.innerHTML = text;
	infobulle.id = 'infobulle';
	infobulle.style.display = 'none';
	infobulle.style.opacity = '0';
	infobulle.style.filter = 'alpha(opacity=0)';
	document.body.appendChild(infobulle);
	
	infobulle.style.position = 'absolute';
	document.onmousemove = function(e){
		x = (!is_ie ? e.pageX : event.x+document.documentElement.scrollLeft)+15;
		y = (!is_ie ? e.pageY : event.y+document.documentElement.scrollTop)+15;
		
		var windowWidth = (!is_ie ? window.innerWidth : document.documentElement.clientWidth);
		var windowHeight = (!is_ie ? window.innerHeight : document.documentElement.clientHeight);
		var scrollLeft = document.documentElement.scrollLeft;
		var scrollTop = document.documentElement.scrollTop;
		
		//Calcul des dimensions de l'infobulle
		infobulle.style.display = '';
		var infoWidth = infobulle.offsetWidth;
		var infoHeight = infobulle.offsetHeight;
		infobulle.style.display = 'none';
		
		/*On vérifie que l'infobulle ne sorte pas de la fenêtre*/
		if((x+infoWidth) > (windowWidth+scrollLeft)){
			x = (!is_ie ? e.pageX : event.x+document.documentElement.scrollLeft)-infoWidth-5;
		}
		if((y+infoHeight) > (windowHeight+scrollTop)){
			y = (!is_ie ? e.pageY : event.y+document.documentElement.scrollTop)-infoHeight-5;
		}
		
		infobulle.style.left = x+'px';
		infobulle.style.top = y+'px';
		infobulle.style.display = '';
	}

	for(i=0; i<=100; i+=10){
		var time = ((i/20)*30);
		setTimeout('opacity('+i+', \'infobulle\');', time);
	}


	//Ajout de la fermeture lorsque la souris quitte l'élément
	element.onmouseout = function(){
		for(i=0; i<=100; i+=10){
			var time = ((i/20)*30);
			var opacity = (100-i);
			setTimeout('opacity('+opacity+', \'infobulle\', 1);', time);
		}
	};
	
	//Fonction servant à faire varier l'opacité
	opacity = function(opacity, id, close){
			var infobulle = document.getElementById(id);
			infobulle.style.opacity = (opacity/100);
			infobulle.style.filter = 'alpha(opacity='+opacity+')';
			if(opacity == 0 && close){
				document.body.removeChild(infobulle); //Suppression de la div provisoire
				document.onmousemove = '';
			}
	}
}

function check_all(id) {
	var checked = document.getElementById(id).checked;
	var name = document.getElementById(id).name;
	if(name=="attachements"){
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
</script>