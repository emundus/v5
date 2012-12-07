<?php defined('_JEXEC') or die; 
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_extendeduser/style/' );
?>

<div class="componentheading">
	<?php echo JText::_('RESET_YOUR_PASSWORD'); ?>
</div>

<form action="index.php?option=com_extendeduser&task=completereset" method="post" class="josForm form-validate">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2" height="40">
				<p><?php echo JText::_('RESET_PASSWORD_COMPLETE_DESCRIPTION'); ?></p>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label for="password1" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_PASSWORD1_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_PASSWORD1_TIP_TEXT'); ?>"><?php echo JText::_('Password'); ?>:</label>
			</td>
			<td>
				<input id="password1" name="password1" type="password" class="required validate-password" />
			</td>
		</tr>
		<tr>
			<td height="40">
				<label for="password2" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_PASSWORD2_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_PASSWORD2_TIP_TEXT'); ?>"><?php echo JText::_('Verify Password'); ?>:</label>
			</td>
			<td>
				<input id="password2" name="password2" type="password" class="required validate-password" />
			</td>
		</tr>
	</table>

	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<button type="submit" class="validate"><?php echo JText::_('SUBMIT'); ?></button>
</form>