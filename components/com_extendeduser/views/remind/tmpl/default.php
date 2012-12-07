<?php defined('_JEXEC') or die; 
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_extendeduser/style/' );
?>

<div class="componentheading">
	<?php echo JText::_('FORGOT_YOUR_USERNAME'); ?>
</div>

<form action="index.php?option=com_extendeduser&task=remindusername" method="post" class="josForm form-validate">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2" height="40">
				<p><?php echo JText::_('REMIND_USERNAME_DESCRIPTION'); ?></p>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label for="email" class="hasTip" title="<?php echo JText::_('REMIND_USERNAME_EMAIL_TIP_TITLE'); ?>::<?php echo JText::_('REMIND_USERNAME_EMAIL_TIP_TEXT'); ?>"><?php echo JText::_('REMIND_USERNAME_EMAIL_TIP_TITLE'); ?>:</label>
			</td>
			<td>
				<input id="email" name="email" type="text" size="40" class="required validate-email" />
			</td>
		</tr>
	</table>

	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<button type="submit" class="validate"><?php echo JText::_('SUBMIT'); ?></button>
</form>