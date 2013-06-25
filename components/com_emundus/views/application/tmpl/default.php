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
	/* CENTER */
		#center {
			width:30%;
		}
		#center .content {
			background-color: #F2E5F8;
			min-height :140px;
			padding-left : 5px;
		}
	/* LEFT */
		#left {
			width:30%;
		}
		#left .content {
			background-color: #E5F5F8;
			min-height :140px;
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
	/* RIGHT */
		#right {
			width:30%;
		}
		#right .content {
			background-color: #F4C9C9;
			min-height :140px;
			padding-left : 5px;
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
				?>
			</div>
			<div id="informations">
				<ul>
					<li>
					<?php
						if(in_array ('lastname',$this->informations)){
							echo JText::_('LASTNAME').' : '.$this->userInformations[0]->lastname;
						}
					?>
					</li>
					<li>
					<?php
						if(in_array ('firstname',$this->informations)){
							echo JText::_('FIRSTNAME').' : '.$this->userInformations[0]->firstname;
						}
					?>
					</li>
					<li>
					<?php
						if(in_array ('email',$this->informations)){
							$email=$this->userInformations[0]->email;
							echo'<a href="mailto:'.$email.'">'.$email.'</a>';
						}
					?>
					</li>
					<li>
					<?php
						if(in_array ('nationality',$this->informations)){
							echo JText::_('NATIONALITY').' : '.$this->userInformations[0]->nationality;
						}
					?>
					</li>
					<li>
					<?php
						if(in_array ('birthdate',$this->informations)){
							$birthdate = new DateTime($this->userInformations[0]->birthdate);
							// $birthdate_explode = explode("/", $birthdate);
							$today = new DateTime();
							$age = $today->diff($birthdate);
							echo JText::_('AGE').' : '.$age->format('%y');
						}
					?>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div id="center" class="column">
		<div class="title"><?php echo JText::_('CAMPAIGN'); ?></div>
		<div class="content">ffff</div>
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
</script>