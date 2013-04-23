<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emundusflow/style/' );
?>
<div id="emundusflow">
<fieldset>
<legend><?php echo JText::_('TITLE'); ?></legend>
<table align="center">
	<tr>
		<td id="flowforms"><?php if($forms<100) echo $forms.'%'; else {?><img src="modules/mod_emundusflow/style/images/ok.png" width="25" length="25" alt="<?php echo $forms.'%';?>" align="middle"  /><?php } ?><?php echo JText::_('FORM_FILLED'); ?></td>
		<td class="flowfleche"></td>
		<td id="flowpj"><?php if($attachments<100) echo $attachments.'%'; else {?><img src="modules/mod_emundusflow/style/images/ok.png" width="25" length="25" alt="<?php echo $attachments.'%';?>" align="middle" /><?php } ?><?php echo JText::_('ATTACHMENT_SENT'); ?></td>
		<td class="flowfleche"></td>
		<td id="flowsent" class="flow"><img src="modules/mod_emundusflow/style/images/<?php echo $sent>0?'ok.png':'no.png'; ?>" width="25" length="25" alt="<?php echo $sent>0?'Sent':'Not sent'; ?>" align="middle" /><?php echo $sent>0?JText::_('APPLICATION_SENT'):'<a href="index.php?option=com_fabrik&c=form&view=form&formid=22&tableid=22">'.JText::_('APPLICATION_NOT_SENT').'</a>'; ?></td>
	</tr>
</table>
</fieldset>
</div>