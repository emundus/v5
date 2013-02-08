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

// import the Joomla modellist library
jimport('joomla.application.component.model');

class SPUpgradeModelCom_Contact extends SPUpgradeModelCom {

    public function categories($ids = null) {
        //initialize
        $this->destination_table = $this->general->getTable('Category', 'JTable');        
        $this->task->section = ' WHERE section LIKE "COM_CONTACT_DETAILS"';
        $this->task->state = 4; //state for success
        
        parent::categories($ids);
    }

    public function contact_details($ids = null) {
        //initialize
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . $this->task->extension_name . DS . 'tables');        
        $this->destination_table = $this->general->getTable('Contact', 'ContactTable');        
        $this->table_name = 'contact_details';
        $this->task->category = 6;
        $this->id = 'id';
        $this->task->query = 'SELECT * 
            FROM #__' . $this->table_name . '
            WHERE ' . $this->id . ' > 0';
        
        $this->items($ids);        
    }

}
