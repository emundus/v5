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
// load tooltip behavior
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) { 
        var myDomain = location.protocol + '//' +
            location.hostname +
            location.pathname.substring(0, location.pathname.lastIndexOf('/')) +
            //'/index.php?option=com_spupgrade&view=monitoring_log';                    
        '/components/com_spupgrade/log.htm';                    
        window.open(myDomain,'SP Upgrade','width=640,height=480, scrollbars=1');
        Joomla.submitform(task);                
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_spupgrade&view=tables'); ?>" method="post" name="adminForm" id="adminForm">        
    <div class="submenu-box">
        <?php if ($this->dbTestConnection) : ?>
            <div class="m" style="text-align: center;font-size: medium;background-color: #00FF00;">
                <?php echo JText::_('COM_SPUPGRADE_MSG_SUCCESS_CONNECTION'); ?>
            </div>
        <?php else : ?>
            <div class="m" style="text-align: center;font-size: medium;background-color: #FF0000;">
                <?php echo JText::_('COM_SPUPGRADE_MSG_ERROR_CONNECTION'); ?>
            </div>
        <?php endif; ?>        
    </div>
    <div class="clr"></div>
    <table class="adminlist">
        <thead><?php echo $this->loadTemplate('head'); ?></thead>
        <tfoot><?php echo $this->loadTemplate('foot'); ?></tfoot>
        <tbody><?php echo $this->loadTemplate('body'); ?></tbody>
    </table>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>