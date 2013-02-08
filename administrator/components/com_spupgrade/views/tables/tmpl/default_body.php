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
            <?php echo $i; ?>
        </td>
        <td>
            <?php if ($item->extension_name != 'com_media') : ?>
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>            
            <?php else: ?>
                <?php if ($this->pathConnection) : ?>
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>            
                <?php endif; ?>
            <?php endif; ?>            
        </td>	
        <td class="left">
            <?php echo JText::_($item->extension_name); ?>
            <?php echo ' -> '; ?>
            <?php echo JText::_($item->extension_name . '_' . $item->name); ?>
        </td>
        <td class="left">
            <?php echo JText::_($item->extension_name . '_' . $item->name . '_desc'); ?>
        </td>
        <td class="center">             
            <?php if ($item->extension_name == 'com_media') : ?>
                <?php if (($item->extension_name . '_' . $item->name) == 'com_media_images') : ?>
                    <?php if (!$this->pathConnection) : ?>
                        <?php echo JText::_('COM_SPUPGRADE_PATH_ERROR'); ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (($item->extension_name . '_' . $item->name) == 'com_media_template') : ?>
                    <?php if ($this->pathConnection) : ?>
                        <label class="hasTip" title="<?php echo JText::_('COM_SPUPGRADE_TEMPLATE_NAME_DESC'); ?>"><?php echo JText::_('COM_SPUPGRADE_TEMPLATE_NAME_LABEL'); ?></label><br/>
                        <input type="text" name="input_template" id="input_template" value="" class="inputbox" size="45" aria-invalid="false">
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" name="input_ids[]" id="input_ids<?php echo $i;?>" value="" class="inputbox" size="45" aria-invalid="false">
            <?php endif; ?>            
            <input type="hidden" name="task_ids[]" value="<?php echo $item->id; ?>" >
        </td>
        <td class="left">

            <?php if ($item->extension_name != 'com_media') : ?>                
            <div class="toolbar-list button"><div class="article"><a title="<?php echo JText::_('JCLEAR'); ?>" href="#" onclick="jClearItem('<?php echo $i; ?>');"><?php echo JText::_('JCLEAR'); ?></a></div></div>
            <?php endif; ?>

            <?php if (($item->extension_name . '_' . $item->name) == 'com_users_users') : ?>                
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_content_sections') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_content_categories') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_content_content') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_contact_categories') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>                
            <?php if (($item->extension_name . '_' . $item->name) == 'com_contact_contact_details') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_weblinks_categories') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_weblinks_weblinks') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_newsfeeds_categories') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_newsfeeds_newsfeeds') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_banners_categories') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_banners_banner_clients') : ?>
                <?php $pk = 'cid'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=bannerclient&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_banners_banners') : ?>
                <?php $pk = 'bid'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=banner&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_menus_menu_types') : ?>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_menus_menu') : ?>
                <select id="all_menus" name="all_menus" class="hasTip" aria-invalid="false" title="<?php echo JText::_('COM_SPUPGRADE_MENUS_ALL_TIP'); ?>">
                    <optgroup id="all_menus" label="<?php echo JText::_('COM_SPUPGRADE_MENUS_ALL'); ?>">
                        <option value="0" selected="selected"><?php echo JText::_('JYES'); ?></option>
                        <option value="1"><?php echo JText::_('JNO'); ?></option>
                    </optgroup>
                </select>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
            <?php if (($item->extension_name . '_' . $item->name) == 'com_modules_modules') : ?>
                <select id="all_modules" name="all_modules" class="hasTip" aria-invalid="false" title="<?php echo JText::_('COM_SPUPGRADE_MODULES_ALL_TIP'); ?>">                    
                    <optgroup id="all_modules" label="<?php echo JText::_('COM_SPUPGRADE_MODULES_ALL'); ?>">
                        <option value="0" selected="selected"><?php echo JText::_('JYES'); ?></option>
                        <option value="1"><?php echo JText::_('JNO'); ?></option>
                    </optgroup>
                </select>
                <?php $pk = 'id'; ?><div class="toolbar-list button"><div class="article"><a class="modal" title="<?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?>" href="<?php echo JRoute::_('index.php?option=com_spupgrade&amp;view=component&amp;layout=default&amp;tmpl=component&amp;pk=' . $pk . '&amp;extension_name=' . $item->extension_name . '&amp;name=' . $item->name . '&amp;cid=' . $i); ?>" onclick="return false;" rel="{handler: 'iframe', size: {x: 900, y: 400}}"><?php echo JText::_('COM_SPUPGRADE_CHOOSE'); ?></a></div></div>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>

