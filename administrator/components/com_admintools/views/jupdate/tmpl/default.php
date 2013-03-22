<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// DO NOT REMOVE THE akeeba-bootstrap CLASS! THIS VIEW IS NOT RENDERED THROUGH FOF!
?>
<div class="akeeba-bootstrap">
<?php if($this->updateinfo->status !== true): ?>
<div id="joomla-update-information">
	<?php if(is_null($this->updateinfo->status)): ?>
	<p class="alert alert-error">
		<?php echo JText::_('ATOOLS_LBL_JUPDATE_NO_AUTOUPDATE') ?>
	</p>
	<?php else: ?>
	<table cellspacing="0" border="0" width="100%" class="table table-striped">
		<tr>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_YOURVERSION') ?>
			</td>
			<td width="80"><?php echo JVERSION ?></td>
			<td width="65%">
				<?php if($this->updateinfo->installed['package']):?>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_FULLPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->installed['package'] ?>">
					<?php echo basename($this->updateinfo->installed['package']) ?>
				</a>
				<?php else: ?>
				&nbsp;
				<?php endif; ?>
			</td>
		</tr>
		
		<?php if($this->updateinfo->sts['version'] && ($this->updateinfo->sts['version'] != $this->updateinfo->installed['version'])): ?>
		<tr>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_LATESTVERSION') ?> 
				<span class="label label-warning">(STS)</span>
			</td>
			<td>
				<span class="label label-warning">
				<?php echo $this->updateinfo->sts['version']?>
				</span>
			</td>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_UPGRADEPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->sts['package'] ?>">
					<?php echo basename($this->updateinfo->sts['package']) ?>
				</a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if($this->updateinfo->lts['version'] && ($this->updateinfo->lts['version'] != $this->updateinfo->installed['version'])): ?>
		<tr>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_LATESTVERSION') ?>
				<span class="label label-success">(LTS)</span>
			</td>
			<td>
				<span class="label label-success">
				<?php echo $this->updateinfo->lts['version']?>
				</span>
			</td>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_UPGRADEPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->lts['package'] ?>">
					<?php echo basename($this->updateinfo->lts['package']) ?>
				</a>
			</td>
		</tr>
		<?php endif; ?>
		
	</table>
	<?php endif; ?>
</div>

<div id="joomla-update-buttonbar">
	<?php if(!empty($this->updateinfo->installed['version'])): ?>
	<button class="btn btn-primary" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=installed'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_REINSTALL',$this->updateinfo->installed['version']) ?>
	</button>
	<?php endif; ?>

	<?php if($this->updateinfo->sts['version'] && ($this->updateinfo->sts['version'] != $this->updateinfo->installed['version'])): ?>
	<button class="btn btn-warning" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=sts'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_UPGRADE',$this->updateinfo->sts['version']) ?> (STS)
	</button>
	<?php endif; ?>

	<?php if($this->updateinfo->lts['version'] && ($this->updateinfo->lts['version'] != $this->updateinfo->installed['version'])): ?>
	<button class="btn btn-success" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=lts'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_UPGRADE',$this->updateinfo->lts['version']) ?> (LTS)
	</button>
	<?php endif; ?>
	
	<button class="btn" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=force'">
		<span class="icon icon-retweet"></span>
		<?php echo JText::_('ATOOLS_LBL_UPDATE_FORCE') ?>
	</button>
</div>
	
<?php else: ?>

<div id="joomla-update-information">
	<table cellspacing="0" border="0" width="100%" class="table table-striped">
		<tr>
			<td><?php echo JText::_('ATOOLS_LBL_JUPDATE_YOURVERSION') ?></td>
			<td width="80"><?php echo JVERSION ?></td>
			<td width="65%">
				<?php if($this->updateinfo->installed['package']):?>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_FULLPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->installed['package'] ?>">
					<?php echo basename($this->updateinfo->installed['package']) ?>
				</a>
				<?php else: ?>
				&nbsp;
				<?php endif; ?>
			</td>
		</tr>
		<?php if($this->updateinfo->current['version']): ?>
		<tr>
			<td><?php echo JText::_('ATOOLS_LBL_JUPDATE_LATESTVERSION') ?></td>
			<td>
				<?php echo $this->updateinfo->current['version']?>
			</td>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_UPGRADEPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->current['package'] ?>">
					<?php echo basename($this->updateinfo->current['package']) ?>
				</a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if($this->updateinfo->sts['version']): ?>
		<tr>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_LATESTVERSION') ?>
				<span class="label label-warning">(STS)</span>
			</td>
			<td>
				<?php echo $this->updateinfo->sts['version']?>
			</td>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_UPGRADEPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->sts['package'] ?>">
					<?php echo basename($this->updateinfo->sts['package']) ?>
				</a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if($this->updateinfo->lts['version']): ?>
		<tr>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_LATESTVERSION') ?>
				<span class="label label-success">(LTS)</span>
			</td>
			<td>
				<?php echo $this->updateinfo->lts['version']?>
			</td>
			<td>
				<?php echo JText::_('ATOOLS_LBL_JUPDATE_UPGRADEPACKAGEURL') ?>:
				<a href="<?php echo $this->updateinfo->lts['package'] ?>">
					<?php echo basename($this->updateinfo->lts['package']) ?>
				</a>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</div>

<div id="joomla-update-buttonbar">
	<?php if(!empty($this->updateinfo->installed['version'])): ?>
	<button class="btn" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=installed'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_REINSTALL',$this->updateinfo->installed['version']) ?>
	</button>
	<?php endif; ?>
	
	<?php if(!empty($this->updateinfo->current['version'])): ?>
	<button class="btn btn-primary" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=current'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_UPGRADE',$this->updateinfo->current['version']) ?>
	</button>
	<?php endif; ?>
	
	<?php if(!empty($this->updateinfo->sts['version'])): ?>
	<button class="btn btn-warning" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=sts'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_UPGRADE',$this->updateinfo->sts['version']) ?> (STS)
	</button>
	<?php endif; ?>
	
	<?php if(!empty($this->updateinfo->lts['version'])): ?>
	<button class="btn btn-success" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=download&item=lts'">
		<?php echo JText::sprintf('ATOOLS_LBL_JUPDATE_UPGRADE',$this->updateinfo->lts['version']) ?> (LTS)
	</button>
	<?php endif; ?>

	<button class="btn" onclick="window.location='index.php?option=com_admintools&view=jupdate&task=force'">
		<span class="icon icon-retweet"></span>
		<?php echo JText::_('ATOOLS_LBL_UPDATE_FORCE') ?>
	</button>
	
</div>

<?php endif; ?>

<p>&nbsp;</p>
<div class="alert alert-info">
	<p>
		<?php echo JText::_('COM_ADMINTOOLS_JUPDATE_MSG_CLICKRELOADIFOUTOFDATE'); ?>
	</p>
</div>
	
<?php if(
		($this->updateinfo->sts['version'] && ($this->updateinfo->sts['version'] != $this->updateinfo->installed['version']))
		|| ($this->updateinfo->lts['version'] && ($this->updateinfo->lts['version'] != $this->updateinfo->installed['version']))
): ?>
<p></p>
<div id="joomla-update-stsltsinfo" class="well">
	<?php if($this->updateinfo->sts['version'] && ($this->updateinfo->sts['version'] != $this->updateinfo->installed['version'])): ?>
	<p id="joomla-update-stsinfo">
		<span class="label label-warning">STS</span>
		<?php echo JText::_('COM_ADMINTOOLS_JUPDATE_STSINFO') ?>
	</p>
	<?php endif; ?>
	<?php if($this->updateinfo->lts['version'] && ($this->updateinfo->lts['version'] != $this->updateinfo->installed['version'])): ?>
	<p id="joomla-update-stsinfo">
		<span class="label label-warning">LTS</span>
		<?php echo JText::_('COM_ADMINTOOLS_JUPDATE_LTSINFO') ?>
	</p>
	<?php endif; ?>
</div>
<div class="alert alert-warning">
	<h5>
		<?php echo JText::_('COM_ADMINTOOLS_JUPDATE_STSLTSINFO_DISCLAIMER') ?>
	</h5>
	<p>
		<?php echo JText::_('COM_ADMINTOOLS_JUPDATE_STSLTSINFO') ?>
	</p>
</div>
<?php endif; ?>
</div>