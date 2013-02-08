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
?>
<tr>
	<td colspan="6"><?php echo JText::_('COM_SPUPGRADE_USER_CORE');?></td>
</tr>
<tr>
    <th width="1%">
        <?php echo JText::_('JGLOBAL_FIELD_ID_LABEL'); ?>
    </th>
    <th width="20">
        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
    </th>
    <th class="left">
        <?php echo JText::_('COM_SPUPGRADE_FIELD_TABLE_NAME_LABEL'); ?>
    </th>
    <th class="center">
        <?php echo JText::_('COM_SPUPGRADE_FIELD_TABLE_IDS_LABEL'); ?>
    </th>
</th>
<th width="10%">

</th>
</tr>

