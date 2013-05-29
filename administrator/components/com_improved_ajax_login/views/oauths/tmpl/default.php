<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login & Register
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<?php
	// Set toolbar items for the page
	JToolBarHelper::title(   JText::_( 'Improved AJAX Login & Register: Social Settings' ), 'generic.png' );
	JToolBarHelper::publishList();
	JToolBarHelper::unpublishList();
  JToolBarHelper::editListX();
	$ordering = null;
  //($this->lists['order'] == 'a.ordering');
?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( '#' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th class="title">
				<?php echo JText::_('Title'); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JText::_('Enabled'); ?>
			</th>
			<th width="25%" class="title">
				<?php echo JText::_('Application ID/Key'); ?>
			</th>
			<th width="25%"  class="title">
				<?php echo JText::_('Application Secret ID/Key'); ?>
			</th>
			<th width="20">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="9">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
    $row->checked_out = 0;

		$link 	= JRoute::_( 'index.php?option=com_improved_ajax_login&view=oauth&task=edit&cid[]='. $row->id );

		$checked 	= JHTML::_('grid.checkedout', $row, $i );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit OAuth' );?>::<?php echo $this->escape($row->name); ?>">
					<a href="<?php echo $link; ?>">
						<?php echo $this->escape($row->name); ?></a></span>
			</td>
			<td align="center">
				<span class="" title="test"><?php echo $published;?></span>
			</td>
			<td align="center">
				<?php echo $row->app_id; ?>
			</td>
			<td align="center">
				<?php echo $row->app_secret; ?>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>
</div>

	<input type="hidden" name="option" value="com_improved_ajax_login" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
