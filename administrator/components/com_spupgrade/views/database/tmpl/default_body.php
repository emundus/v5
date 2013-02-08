<?php
/**
 * @package		SP Upgrade
 * @subpackage	Components
 * @copyright	SP CYEND - All rights reserved.
 * @author		SP CYEND
 * @link		http://www.cyend.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.modal');
?>
<?php foreach ($this->items as $i => $item): ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
            <?php echo $item->id; ?>
        </td>
        <td>
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>	
        <td class="left">
            <input type="hidden" name="input_prefixes[]" id="input_prefixes" value="<?php echo JText::_($item->prefix); ?>"  >
            <input type="hidden" name="input_names[]" id="input_names" value="<?php echo $item->name; ?>" >
            <?php echo JText::_($item->prefix . '_' . $item->name); ?>
        </td>
        <td class="center">            
            <input type="text" name="input_ids[]" id="input_ids<?php echo $i;?>" value="" class="inputbox" size="45" aria-invalid="false" >
        </td>
        <td class="left">
            <div class="toolbar-list button"><div class="article"><a title="<?php echo JText::_('JCLEAR'); ?>" href="#" onclick="jClearItem('<?php echo $item->prefix; ?>', '<?php echo $item->name; ?>');"><?php echo JText::_('JCLEAR'); ?></a></div></div>
            <div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=table&amp;layout=default&amp;tmpl=component&amp;prefix='.$item->prefix.'&amp;name='.$item->name); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
        </td>
    </tr>
<?php endforeach; ?>

