<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

$script = <<<ENDSCRIPT
	window.addEvent( 'domready' ,  function() {
		pingUpdate();
	});
ENDSCRIPT;
$document = JFactory::getDocument();
$document->addScriptDeclaration($script,'text/javascript');
?>
<div id="update-progress">
	<h1>
		<?php echo JText::_('ATOOLS_LBL_JUPDATE_INPROGRESS') ?>
	</h1>
	<div id="extprogress">
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('ATOOLS_LBL_JUPDATE_BYTESREAD'); ?></span>
			<span class="extvalue" id="extbytesin"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('ATOOLS_LBL_JUPDATE_BYTESEXTRACTED'); ?></span>
			<span class="extvalue" id="extbytesout"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('ATOOLS_LBL_JUPDATE_FILESEXTRACTED'); ?></span>
			<span class="extvalue" id="extfiles"></span>
		</div>
	</div>
</div>

<script type="text/javascript">
	var admintools_update_password = '<?php echo $this->password; ?>';
	var admintools_ajax_url = '<?php echo JURI::base() ?>components/com_admintools/restore.php';
	var admintools_file = '<?php echo $this->file; ?>';
</script>