<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

defined('_JEXEC') or die();

if(version_compare(JVERSION, '3.0', 'ge')) {
	JHTML::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}

$gtree	= array(
	JHTML::_('select.option',  'super administrator', JText::_('super administrator') ),
	JHTML::_('select.option',  'administrator', JText::_('administrator') ),
	JHTML::_('select.option',  'manager', JText::_('manager') )
);
?>
<form action="index.php" method="post" name="adminForm2" id="adminForm2">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="acl" />
	<input type="hidden" name="task" id="task" value="mingroup" />
	<fieldset>
	<legend><?php echo JText::_('ATOOLS_ACL_GROUP_MINGROUP')?></legend>
		<label for="mingroup"><?php echo JText::_('ATOOLS_ACL_MINGROUP')?></label>
		<?php echo JHTML::_('select.genericlist',   $gtree, 'minacl', 'class="inputbox"', 'value', 'text', $this->minacl ); ?>
		<input type="submit" class="input" value="<?php echo JText::_('ATOOLS_ACL_SUBMIT') ?>" />
	</fieldset>
</form>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="acl" />
	<input type="hidden" name="task" id="task" value="" />
	<fieldset>
	<legend><?php echo JText::_('ATOOLS_ACL_GROUP_ACL')?></legend>
	<table class="adminlist">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('ATOOLS_ACL_USERNAME')?>
				</th>
				<th>
					<?php echo JText::_('ATOOLS_ACL_USERGROUP')?>
				</th>
				<th width="100">
					<?php echo JText::_('ATOOLS_ACL_UTILS_TITLE')?>
				</th>
				<th width="100">
					<?php echo JText::_('ATOOLS_ACL_SECURITY_TITLE')?>
				</th>
				<th width="100">
					<?php echo JText::_('ATOOLS_ACL_MAINTENANCE_TITLE')?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php $m = 1; ?>
		<?php foreach($this->userlist as $user):?>
		<?php $m = 1 - $m; ?>
			<tr class="row<?php echo $m ?>" id="user<?php echo (int)$user['id']; ?>">
				<td><strong><?php echo $this->escape($user['username']) ?></strong></td>
				<td><?php echo $this->escape(JText::_($user['usertype'])) ?></td>
				<td align="center">
					<a href="index.php?option=com_admintools&view=acl&task=toggle&axo=utils&id=<?php echo $user['id'] ?>">
					<?php if($user['utils']) :?>
						<img src="images/tick.png" width="16" height="16" border="0" alt="<?php echo JText::_('Yes'); ?>">
					<?php else: ?>
						<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_('Yes'); ?>">
					<?php endif; ?>
				</td>
				<td align="center">
					<a href="index.php?option=com_admintools&view=acl&task=toggle&axo=security&id=<?php echo $user['id'] ?>">
					<?php if($user['security']) :?>
						<img src="images/tick.png" width="16" height="16" border="0" alt="<?php echo JText::_('Yes'); ?>">
					<?php else: ?>
						<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_('Yes'); ?>">
					<?php endif; ?>
				</td>
				<td align="center">
					<a href="index.php?option=com_admintools&view=acl&task=toggle&axo=maintenance&id=<?php echo $user['id'] ?>">
					<?php if($user['maintenance']) :?>
						<img src="images/tick.png" width="16" height="16" border="0" alt="<?php echo JText::_('Yes'); ?>">
					<?php else: ?>
						<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_('Yes'); ?>">
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</fieldset>
</form>