<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emundusflow/style/' );
?>
<div id="emundusflow_new">
	<div class="part">
		<div class="bar">
			<div class="emflow_legend">
				<?php echo JText::_('FORM_FILLED').' '.$forms; ?>%
			</div>
			<div class="status">
				<?php
					if($forms=="100"){
						echo'<img src="'.JURI::Base().'media/com_emundus/images/icones/green.png" align="middle" />'; 
					}else{
						echo'<img src="'.JURI::Base().'media/com_emundus/images/icones/red.png" align="middle" />'; 
					}
				?>
			</div>
			<div class="progress">
				<div class="progression gf-menu" style="width: <?php echo $forms ?>%">
					<div title="<?php echo $forms ?>%" class="precent">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="part">
		<div class="bar">
			<div class="emflow_legend">
				<?php echo JText::_('ATTACHMENT_SENT').' '.$attachments; ?>%
			</div>
			<div class="status">
				<?php 
					if($attachments>="100"){
						echo'<img src="'.JURI::Base().'media/com_emundus/images/icones/green.png" align="middle" />'; 
					}else{
						echo'<img src="'.JURI::Base().'media/com_emundus/images/icones/red.png" align="middle" />'; 
					}
				?>
			</div>
			<div class="progress">
				<div class="progression gf-menu" style="width: <?php echo $attachments ?>%">
					<div title="<?php echo $attachments ?>%" class="precent">
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php 
		if ($sent>0) {
			// Apply again
			$query='SELECT count(id) as cpt FROM #__emundus_setup_campaigns 
					WHERE id NOT IN (
						select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user->id.'
					)';
			$db->setQuery($query);
			$cpt = $db->loadResult();
			//echo '<img src="modules/mod_emundusflow/style/images/ok.png" width="25" length="25" alt="'.JText::_('APPLICATION_SENT').'" align="middle" />'.JText::_('APPLICATION_SENT');
			echo'<div class="part">
					<div class="bar">
						<div class="emflow_legend">
							'.JText::_('APPLICATION_SENT').'
						</div>
						<div class="status">
							<img src="'.JURI::Base().'media/com_emundus/images/icones/green.png" align="middle" />
						</div>
						<div class="progress">
							<div class="progression gf-menu" style="width: 100%">
								<div title="100%" class="precent">
								</div>
							</div>
						</div>
					</div>		
				</div>';
			if($cpt>0)
				echo '<div class="renew_application">
						<a href="index.php?option=com_emundus&view=renew_application"><img src="'.JURI::Base().'media/com_emundus/images/icones/renew_20x20.png" align="middle" />
							'.JText::_('RENEW_APPLICATION').'
						</a>
					</div>';
		} else {
			//echo '<img src="modules/mod_emundusflow/style/images/no.png" width="25" length="25" alt="'.JText::_('APPLICATION_NOT_SENT').'" align="middle" /><a href="index.php?option=com_fabrik&c=form&view=form&formid=22&tableid=22" title="'.JText::_('APPLICATION_NOT_SENT').'">'.JText::_('APPLICATION_NOT_SENT').'</a>';
			echo'<div class="part">
					<div class="bar">
						<div class="emflow_legend">
							'.JText::_('APPLICATION_NOT_SENT').'
						</div>
						<div class="status">
							<img src="'.JURI::Base().'media/com_emundus/images/icones/red.png" align="middle" />
						</div>
						<div class="progress">
							<div class="progression gf-menu" style="width: 0%">
								<div title="0%" class="precent">
								</div>
							</div>
						</div>
					</div>
				</div>';
		}
	?>
</div>