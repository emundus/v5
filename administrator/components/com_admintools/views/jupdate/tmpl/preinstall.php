<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>
<form name="adminForm" action="index.php" method="post" id="adminForm" class="form form-horizontal">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="jupdate" />
	<input type="hidden" name="task" value="install" />
	<input type="hidden" name="act" id="act" value="nobackup" />
	<input type="hidden" name="file" value="<?php echo $this->file ?>" />

	<fieldset>
		<div class="control-group">
			<label for="procengine" class="control-label"><?php echo JText::_('ATOOLS_LBL_EXTRACTIONMETHOD'); ?></label>
			<div class="controls">
				<?php echo JHTML::_('select.genericlist', $this->extractionmodes, 'procengine', '', 'value', 'text', $this->ftpparams['procengine']);?>
			</div>
		</div>
	</fieldset>	
	
	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_FTPOPTIONS'); ?></legend>

		<div class="control-group">
			<label for="ftp_host" class="control-label"><?php echo JText::_('ATOOLS_LBL_HOST_TITLE') ?></label>
			<div class="controls">
				<input id="ftp_host" name="ftp_host" value="<?php echo $this->ftpparams['ftp_host']; ?>" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label for="ftp_port" class="control-label"><?php echo JText::_('ATOOLS_LBL_PORT_TITLE') ?></label>
			<div class="controls">
				<input id="ftp_port" name="ftp_port" value="<?php echo $this->ftpparams['ftp_port']; ?>" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label for="ftp_user" class="control-label"><?php echo JText::_('ATOOLS_LBL_USER_TITLE') ?></label>
			<div class="controls">
				<input id="ftp_user" name="ftp_user" value="<?php echo $this->ftpparams['ftp_user']; ?>" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label for="ftp_pass" class="control-label"><?php echo JText::_('ATOOLS_LBL_PASSWORD_TITLE') ?></label>
			<div class="controls">
				<input id="ftp_pass" name="ftp_pass" value="<?php echo $this->ftpparams['ftp_pass']; ?>" type="password" />
			</div>
		</div>
		<div class="control-group">
			<label for="ftp_root" class="control-label"><?php echo JText::_('ATOOLS_LBL_INITDIR_TITLE') ?></label>
			<div class="controls">
				<input id="ftp_root" name="ftp_root" value="<?php echo $this->ftpparams['ftp_root']; ?>" type="text" />
			</div>
		</div>
	</fieldset>
	
	<div class="form-actions">
		<input class="btn btn-primary" type="submit" value="<?php echo JText::_('ATOOLS_LBL_START') ?>" />
		<?php if($this->hasakeeba): ?>
			<input class="btn btn-success" type="submit" value="<?php echo JText::_('ATOOLS_LBL_START_BACKUP') ?>" onclick="backupFirst();" />
		<?php endif; ?>
	</div>
</form>

<script type="text/javascript">
function backupFirst()
{
	document.getElementById('act').setAttribute('value', 'backup');
}
</script>