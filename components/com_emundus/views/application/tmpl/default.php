<style type="text/css">
/* identity_card */
	#identity_card .column {
		position: relative;
		float: left;
		margin-bottom : 10px;
	}
	
	.column .title {
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
			width:30%;
			background-color: #F2E5F8;
		}
		#center .content li {
			list-style-type: none;
			margin :0;
			padding:0;
		}
	/* LEFT */
		#left {
			width:30%;
			background-color: #E5F5F8;
		}
		#left .content #photo {
			float : left;
			width:30%;
		}
		#left .content #informations {
			float : right;
			width:68%;
		}
		#left .content #informations li {
			list-style-type: none;
			margin :0;
			padding:0;
		}
		#left .content #photo #ID {
			font: 14px Helvetica, Arial, sans-serif;
			font-weight:bold;
			text-align:center;
			margin-top:4px;
			border:2px solid #8E98A4;
			border-radius: 10px 10px 10px 10px;
		}
	/* RIGHT */
		#right {
			background-color: #FBF6DC;
			width:30%;
		}

/* accordion */
#accordion  {
	clear: both;
	margin: 20px 0 0;
	width:90%;
	/*max-width: 400px;*/
}
#accordion H2 {
	background: #6B7B95;
	color:#fff;
    cursor: pointer;
    font: 12px Helvetica, Arial, sans-serif;
    margin: 0 0 4px 0;
    padding: 3px 5px 1px;
}
#accordion .content {
	background-color: #D3E9F8;
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
		<div class="title"><?php echo JText::_('APPLICANT'); ?></div>
		<div class="content">
			<div id="photo">
				<?php 
				if(in_array ('photo',$this->informations)){
					if(!empty($this->userInformations[0]->filename)){
						echo'<img src="'.JURI::Base().'images/emundus/files/'.$this->user_id.'/'.$this->userInformations[0]->filename.'" width="100">'; 
					}else if(!empty($this->userInformations[0]->gender)){
						echo'<img src="'.JURI::Base().'media/com_emundus/images/icones/'.strtolower($this->userInformations[0]->gender).'_user.png" style="padding:10px 0 0 10px; width:120px;">';
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
	<div id="center" class="column">
		<div class="title"><?php echo JText::_('CAMPAIGN'); ?></div>
		<div class="content">
			<?php
			foreach($this->userCampaigns as $campaign){
				echo'<ul>';
					echo'<li><div id="title">'.JText::_('CAMPAIGN').'</div> : '.$campaign->label.'</li>';
					echo'<li><div id="title">'.JText::_('ACADEMIC_YEAR').'</div> : '.$campaign->year.'</li>';
					echo'<li><div id="title">'.JText::_('DATE_SUBMITTED').'</div> : '.date('Y-m-d',strtotime($campaign->date_submitted)).'</li>';
					if(!empty($campaign->result_sent) && $campaign->result_sent==1){
						echo'<li><div id="title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('SENT').'</li>';
						echo'<li><div id="title">'.JText::_('DATE_RESULT_SENT').'</div> : '.date('Y-m-d',strtotime($campaign->date_result_sent)).'</li>';
					}else{
						echo'<li><div id="title">'.JText::_('RESULT_SENT').'</div> : '.JText::_('NOT_SENT').'</li>';
					}
				echo'</ul>';
			}
			?>
		</div>
	</div>
	<div id="right" class="column">
		<div class="title"><?php echo JText::_('ACTIONS'); ?></div>
		<div class="content">gggg</div>
	</div>
</div>

<div id="accordion">
	<h2><?php echo JText::_('ATTACHEMENTS'); ?></h2>
	<div id="em_application_attachements" class="content">aaaa</div>
	
	<h2><?php echo JText::_('COMMENTS'); ?></h2>
	<div id="em_application_comments" class="content">bbb</div>
	
	<h2><?php echo JText::_('APPLICATION_FORM'); ?></h2>
	<div id="em_application_forms" class="content">cccc</div>
</div>
<script>
window.addEvent('domready', function(){
  new Fx.Accordion($('accordion'), '#accordion h2', '#accordion .content');
});

window.onload = function(){
var column = document.getElementById('center');
if (document.all) // ok I.E
{
H = column.currentStyle.height;
}
else // ok firefox.0.9.2 , pas mozilla.1.0 ni netscape.7.02
{
H = document.defaultView.getComputedStyle(column, null).height;
}
var left = document.getElementById('left');
var right = document.getElementById('right');
left.style.height=H;
right.style.height=H;
}

</script>