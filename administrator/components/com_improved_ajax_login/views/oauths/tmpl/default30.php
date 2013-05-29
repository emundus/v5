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
	JToolBarHelper::title( JText::_( 'Improved AJAX Login & Register: Social Settings' ), 'generic.png' );
	JToolBarHelper::publishList();
	JToolBarHelper::unpublishList();
	JToolBarHelper::editList();
	$ordering = null;
  //($this->lists['order'] == 'a.ordering');
?>
<form action="<?php echo JRoute::_('index.php?option=com_improved_ajax_login&view=oauths'); ?>" method="post" name="adminForm" id="adminForm">
  <div id="j-main-container">
		<table class="table table-striped" id="weblinkList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JText::_( '#' ); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th class="title">
						<?php echo JText::_('Title'); ?>
					</th>
					<th width="5%" class="nowrap center hidden-phone">
						<?php echo JText::_('Enabled'); ?>
					</th>
					<th width="25%" class="nowrap hidden-phone">
						<?php echo JText::_('Application ID/Key'); ?>
					</th>
					<th width="25%" class="nowrap hidden-phone">
						<?php echo JText::_('Application Secret ID/Key'); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JText::_('ID'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php for ($i=0, $n=count( $this->items ); $i < $n; $i++) :
        $row = &$this->items[$i];
        $row->checked_out = 0;
    		$link 	= JRoute::_( 'index.php?option=com_improved_ajax_login&view=oauth&task=edit&cid[]='. $row->id );
    		$checked 	= JHTML::_('grid.checkedout', $row, $i );
    		$published 	= JHTML::_('grid.published', $row, $i );
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="order nowrap center hidden-phone">
            <?php echo $this->pagination->getRowOffset( $i ); ?>
					</td>
					<td class="center hidden-phone">
            <?php echo $checked; ?>
					</td>
					<td class="nowrap">
            <span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit OAuth' );?>::<?php echo $this->escape($row->name); ?>">
					    <a href="<?php echo $link; ?>">
						    <?php echo $this->escape($row->name); ?>
              </a>
            </span>
					</td>
					<td class="center hidden-phone">
						<span><?php echo $published;?></span>
					</td>
					<td class="nowrap hidden-phone">
						<?php echo $row->app_id; ?>
					</td>
					<td class="nowrap hidden-phone">
						<?php echo $row->app_secret; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php endfor; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
  </div>
</form>