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

$current_user =& JFactory::getUser();

$student_id = JRequest::getVar('jos_emundus_evaluations___student_id', null, 'GET', 'INT',0); 
$evaluations_id = JRequest::getVar('jos_emundus_evaluations___id', null, 'GET', 'INT',0); 
$itemid = JRequest::getVar('Itemid', null, 'GET', 'INT',0); 
//$campaign_id = JRequest::getVar('jos_emundus_evaluations___campaign_id[value]', null, 'GET', 'INT',0); 

include_once(JPATH_BASE.'/components/com_emundus/models/evaluation.php');
$evaluations = new EmundusModelEvaluation;
$evaluation = $evaluations->getEvaluationByID($evaluations_id);
$reason = $evaluations->getEvaluationReasons();

$campaign = EmundusHelperfilters::getCampaignByID($evaluation[0]["campaign_id"]);

unset($evaluation[0]["id"]);
unset($evaluation[0]["user"]);
unset($evaluation[0]["time_date"]);
unset($evaluation[0]["student_id"]);
unset($evaluation[0]["parent_id"]);
unset($evaluation[0]["campaign_id"]);
unset($evaluation[0]["comment"]);
if(empty($evaluation[0]["reason"])) {
	unset($evaluation[0]["reason"]);
	unset($evaluation[0]["reason_other"]);
} elseif(empty($evaluation[0]["reason_other"])) {
	unset($evaluation[0]["reason_other"]);
}

$evaluation_details = EmundusHelperList::getElementsDetailsByName('"'.implode('","', array_keys($evaluation[0])).'"');

if ($student_id > 0 && JFactory::getUser()->usertype != 'Registered') 
	$user =& JFactory::getUser($student_id);
else
	$user =& JFactory::getUser();

	$chemin = EMUNDUS_PATH_REL;

//print_r($_GET);
/*
echo "<pre>";
print_r($campaign);
echo "</pre>";
*/

// generate message body
$result = $user->name.', <br>';
foreach ($evaluation_details as $ed) {
	if($ed->hidden==0 && $ed->published==1 && $ed->tab_name=="jos_emundus_evaluations") {
		$result .= '<br>'.$ed->element_label.' : ';
		if($ed->element_name=="reason") {
			$result .= '<ul>';
			foreach ($evaluation as $e) {
				$result .= '<li>'.$reason[$e["reason"]]->text.'</li>';
			}
			$result .= '</ul>';
		} else
			$result .= $evaluation[0][$ed->element_name];
	}
}

// @TODO generate PDF letter depending on evaluation result
// @TODO make letter template and use them
?>
<h1><?php echo JText::_( 'INFORM_APPLICANT' ); ?></h1>

<div id="attachment_list">
  <form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" enctype="multipart/form-data" />
    <?php echo EmundusHelperEmails::createEmailBlock(array('evaluation_result')); ?>
  </form>
</div>
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
$('mail_body').value = "<?php echo $result; ?>";
$('mail_subject').value = "<?php echo $campaign['label']; ?>";
</script>
