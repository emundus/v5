﻿<?php
defined('_JEXEC') or die('Restricted access'); 

$itemid 	= JRequest::getVar('Itemid', null, 'GET', 'none',0);
$view 		= JRequest::getVar('view', null, 'GET', 'none',0);
$task 		= JRequest::getVar('task', null, 'GET', 'none',0);
$tmpl 		= JRequest::getVar('tmpl', null, 'GET', 'none',0);
 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');

echo'<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
		<div class="emundusraw">';
			echo $this->email;
echo'	</div>
	<input type="hidden" name="task" value=""/>
	</form>';

?>
<script type="text/javascript">
function OnSubmitForm() { 
	if(typeof document.pressed !== "undefined") { 
		document.adminForm.task.value = "";
		var button_name=document.pressed.split("|"); 
		// alert(button_name[0]);
		switch(button_name[0]) {
			case 'applicant_email': 
				document.adminForm.task.value = "applicantEmail";
				document.adminForm.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=applicantEmail";
			break;
			default: return false;
		}
		return true;
	}
}
</script>