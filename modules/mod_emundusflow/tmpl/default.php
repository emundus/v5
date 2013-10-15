<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emundusflow/style/' );
?>
<div id="emundusflow">
<fieldset>
<legend></legend>
<table align="center">
	<tr>
		<td id="flowforms"><?php if($forms<100) echo $forms.'%'; else {?><img src="modules/mod_emundusflow/style/images/ok.png" width="25" length="25" alt="<?php echo $forms.'%';?>" align="middle"  /><?php } ?><?php echo JText::_('FORM_FILLED'); ?></td>
		<td class="flowfleche"></td>
		<td id="flowpj"><?php if($attachments<100) echo $attachments.'%'; else {?><img src="modules/mod_emundusflow/style/images/ok.png" width="25" length="25" alt="<?php echo $attachments.'%';?>" align="middle" /><?php } ?><?php echo JText::_('ATTACHMENT_SENT'); ?></td>
		<td class="flowfleche"></td>
		<td id="flowsent" class="flow">
		<?php 
			if ($sent>0 && $user->candidature_incomplete == 0) {
				// Apply again
				$query='SELECT count(id) as cpt FROM #__emundus_setup_campaigns 
						WHERE id NOT IN (
							select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user->id.'
						)';
				$db->setQuery($query);
				$cpt = $db->loadResult();
				echo '<img src="modules/mod_emundusflow/style/images/ok.png" width="25" length="25" alt="'.JText::_('APPLICATION_SENT').'" align="middle" />'.JText::_('APPLICATION_SENT')." : ".$user->campaign_name;
				if($cpt>0)
					echo ' <a href="index.php?option=com_emundus&view=renew_application"><img src="'.JURI::Base().'media/com_emundus/images/icones/renew.png" width="25" length="25" align="middle" />'.JText::_('RENEW_APPLICATION').'</a>';
			} else {
				echo '<img src="modules/mod_emundusflow/style/images/no.png" width="25" length="25" alt="'.JText::_('APPLICATION_NOT_SENT').'" align="middle" /><a href="index.php?option=com_fabrik&c=form&view=form&formid=22&tableid=22&usekey=user&rowid=-1" title="'.JText::_('APPLICATION_NOT_SENT').'">'.JText::_('APPLICATION_NOT_SENT').'</a>'." : ".$user->campaign_name;
			}
		?>
		</td>
	</tr>
</table>
</fieldset>
</div>