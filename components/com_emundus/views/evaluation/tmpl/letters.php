<?php 
defined('_JEXEC') or die('Restricted access'); 

JHTML::_('behavior.modal'); 
JHTML::_('behavior.tooltip'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
JHTML::stylesheet( 'light2.css', JURI::Base().'templates/rt_afterburner/css/' );
JHTML::stylesheet( 'general.css', JURI::Base().'templates/system/css/' );
JHTML::stylesheet( 'system.css', JURI::Base().'templates/system/css/' );

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');

$current_user = JFactory::getUser();
if(!EmundusHelperAccess::isCoordinator($current_user->id)) {
	echo "<script>window.setTimeout('closeme();', 1500); function closeme() { parent.SqueezeBox.close(); }</script>";
	die('<h1><img src="'.$this->baseurl.'/media/com_emundus/images/icones/admin_val.png" width="80" height="80" align="middle" /> '.JText::_("SAVED").'</h1>');
} else {
	$student_id = JRequest::getVar('jos_emundus_evaluations___student_id', null, 'GET', 'INT',0); 
	$evaluations_id = JRequest::getVar('jos_emundus_evaluations___id', null, 'GET', 'INT',0); 
	$itemid = JRequest::getVar('Itemid', null, 'GET', 'INT',0); 
	//$campaign_id = JRequest::getVar('jos_emundus_evaluations___campaign_id[value]', null, 'GET', 'INT',0); 

	include_once(JPATH_BASE.'/components/com_emundus/models/evaluation.php');
	include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');

	$evaluations = new EmundusModelEvaluation;
	$emails = new EmundusModelEmails;

	$evaluation = $evaluations->getEvaluationByID($evaluations_id);
	//$reason = $evaluations->getEvaluationReasons();
	$eligibility = $evaluations->getEvaluationEligibility();
	$result_id = @$eligibility[$evaluation[0]["result"]]->whenneed;

	//die(print_r($eligibility));
	$campaign = EmundusHelperfilters::getCampaignByID($evaluation[0]["campaign_id"]);

	$user = JFactory::getUser($student_id);

	$chemin = EMUNDUS_PATH_REL;

	// Get email 
	if($result_id == 4)
		$email_lb = "candidature_accepted";
	elseif($result_id == 3)
		$email_lb = "candidature_waiting_list";
	elseif($result_id == 2)
		$email_lb = "candidature_rejected";

	$email = $emails->getEmail($email_lb);

	?>
	<!--
	<div class="em_email_block_nav">
		<input type="button" name="'.JText::_('BACK').'" onclick="history.back()" value="<?php echo JText::_( 'BACK' ); ?>" >
	</div>
	-->
	<h1><?php echo JText::_( 'INFORM_APPLICANT' ); ?></h1>

	<div id="attachment_list">
	  <form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" enctype="multipart/form-data" />
	    <?php echo EmundusHelperEmails::createEmailBlock(array('evaluation_result')); ?>
	  </form>
	</div>

	<?php
	if (!empty($eligibility[$evaluation[0]["result"]]->whenneed)) {
		require(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
	$files = letter_pdf($user->id, @$eligibility[$evaluation[0]["result"]]->whenneed, $campaign['training'], $campaign['id'], $evaluations_id, "F");
	}

	echo '<fieldset><legend>'.JText::_('ATTACHMENTS').'</legend>'; 
	echo '<ul class="em_attachments_list">';
	$files_path = "";
	foreach ($files as $file) {
		$files_path .= str_replace('\\', '\\\\', $file['path']);
		echo '<li><a href="'.$file['url'].'" target="_blank"><img src="'.$this->baseurl.'/media/com_emundus/images/icones/pdf.png" alt="'.JText::_('ATTACHMENTS').'" title="'.JText::_('ATTACHMENTS').'" width="22" height="22" align="absbottom" /> '.$file['name'].'</a></li>';
	}
	echo '</ul>';
	echo '</fieldset>';

	?>

	<script>
	function OnSubmitForm() {
		var btn = document.getElementsByName(document.pressed); 
		btn[0].disabled = true;
		btn[0].value = "<?php echo JText::_('SENDING_EMAIL'); ?>";

		//alert(btn+' '+btn.disabled+' : '+btn.value);
		switch(document.pressed) {
			case 'evaluation_result_email': 
				document.adminForm.action ="index.php?option=com_emundus&task=sendmail_applicant&Itemid=<?php echo $itemid ?>";
			break;
			default: return false;
		}
		return true;
	}

	$('mail_body').value = "<?php echo $email->message; ?>";
	$('mail_subject').value = "<?php echo $campaign['label']; ?>";
	$('mail_attachments').value = "<?php echo $files_path; ?>";

	</script>
<?php } ?>