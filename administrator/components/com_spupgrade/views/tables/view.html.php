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
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * SPUpgrades View
 */
class SPUpgradeViewTables extends JView {

    public $dbTestConnection;

    /**
     * SPUpgrades view display method
     * @return void
     */
    function display($tpl = null) {
        // Get data from the model
        $this->dbTestConnection = $this->get('TestConnection');
        $this->pathConnection = $this->get('PathConnection');
        
        $items = $this->get('Items');
        $pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        // Assign data to the view
        $this->items = $items;
        $this->pagination = $pagination;
        
        // Set the toolbar
        $this->addToolBar();

        //Set JavaScript
        $this->addJS();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar() {
        $canDo = SPUpgradeHelper::getActions();

        JToolBarHelper::title(JText::_('COM_SPUPGRADE_TABLES_TITLE'), 'install.png');

        if ($canDo->get('core.admin')) {
            $bar = JToolBar::getInstance('toolbar');
            if ($this->dbTestConnection) {
                $bar->appendButton('Confirm', 'COM_SPUPGRADE_CONFIRM_MSG', 'move', 'COM_SPUPGRADE_TRANSFER', 'tables.transfer', true);
                JToolBarHelper::divider();
            }            
            JToolBarHelper::preferences('com_spupgrade');
        }
        $bar = & JToolBar::getInstance('toolbar');
        $bar->appendButton('Help', 'help', 'JTOOLBAR_HELP', 'http://cyend.com/extensions/extensions/components/documentation/24-user-guide-how-to-migrate-to-joomla-16', 640, 480);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_SPUPGRADE_ADMINISTRATION'));
    }

    private function addJS() {
        //Handle chosed items
        $rows = "";
        foreach ($this->items as $item) {
            $rows .= "rows[" . $item->id . "]='" . $item->extension_name . "_" . $item->name . "';\n";
        }

        //Choose items
        $js = "
		function jSelectItem(cid, name, id_arr) {
            var chklength = id_arr.length;
             var input_ids = 'input_ids'+cid;
            var input_id;
            for(k=0;k<chklength;k++) {
                input_id = document.getElementById(input_ids);
                if (input_id.value == '') {
                    input_id.value = id_arr[k];
                } else {
                    input_id.value = input_id.value + ',' + id_arr[k];
                }                
            }
            SqueezeBox.close();
    	}";

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($js);

        //Clear selected items
        $js2 = "
		function jClearItem(cid) {
            var input_ids = 'input_ids'+cid;
            document.getElementById(input_ids).value = '';            
    	}";

        $doc->addScriptDeclaration($js2);
    }

}
