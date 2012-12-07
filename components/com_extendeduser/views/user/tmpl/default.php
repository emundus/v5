<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_extendeduser/style/' );
?>
<div class="componentheading">
	<?php echo JText::_( 'WELCOME' ); ?>
</div>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td>
		<?php echo JText::_( 'WELCOME_DESC' ); ?>
	</td>
</tr>
</table>