<?php /** $Id: list.php 24 2009-03-31 04:07:57Z eddieajau $ */ defined('_JEXEC') or die(); ?>
<?php
	JHTML::_('behavior.tooltip');
	JHTML::script('checkall.js', 'administrator/components/com_reports/media/js/');
	jimport('joomla.utilities.date');
	//$dFormat = $this->params->get('dformat', '%c');
?>

<h3><?php echo $mainframe->getCfg('sitename');?></h3>
<form action="" method="post" name="adminForm">
	<fieldset>
		<legend>
			<?php echo JText::_("USER_REGISTRATIONS"); ?> &bull;
			<?php echo date('Y-m-d H:i:s'); ?>
			[<a href="<?php echo JRoute::_('index.php?option=com_emundus&view=user_registrations&tmpl=component');?>" target="_blank">
				<?php echo JText::_('Reports Print View');?>
				<img src="<?php echo $this->baseurl;?>/components/com_reports/media/images/external.png" alt="<?php echo JText::_('Reports Print View');?>" /></a>]
		</legend>

		<table id="userlist" class="adminlist">
			<thead>
				<tr>
					<th class="title" width="10%">
						<?php echo JText::_('Year'); ?>
					</th>
					<th class="title" width="10%">
						<?php echo JText::_('Month'); ?>
					</th>
					<th class="title" width="10%">
						<?php echo JText::_('Day'); ?>
					</th>
					<th class="title">
						<?php echo JText::_('Count'); ?>
					</th>
					<th class="title" width="40%">
						&nbsp;
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
				$i = 0;
				if(!empty($this->items)){
				foreach ($this->items as $item) : ?>
				<tr class="row<?php echo $i++%2; ?>">
					<td>
						<?php echo $item->user_year; ?>
					</td>
					<td>
						<?php echo $item->user_month; ?>
					</td>
					<td>
						<?php echo $item->user_day; ?>
					</td>
					<td>
						<?php echo $item->user_count; ?>
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			<?php endforeach; } ?>
			</tbody>
		</table>
	</fieldset>

	<input type="hidden" name="task"	value="" />
	<input type="hidden" name="view"	value="user_registrations" />
	<input type="hidden" name="id"		value="" />

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir'] ; ?>" />
</form>
