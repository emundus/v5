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

class SPUpgradeModelCom_Banners extends SPUpgradeModelCom {

    public function categories($ids = null) {
        //initialize
        $this->destination_table = $this->general->getTable('Category', 'JTable');        
        $this->task->section = ' WHERE section LIKE "COM_BANNER"';
        $this->task->state = 4; //state for success
        
        parent::categories($ids);
    }
    
    public function banner_clients($ids = null) {
        //initialize
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . $this->task->extension_name . DS . 'tables');        
        $this->destination_table = $this->general->getTable('Client', 'BannersTable');        
        $this->table_name = 'bannerclient';
        $this->task->category = 12;
        $this->id = 'cid';
        $this->task->query = 'SELECT * 
            FROM #__' . $this->table_name . '
            WHERE ' . $this->id . ' > 0';
        
        $this->items($ids);        
    }

    public function banners($ids = null) {
        //initialize
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . $this->task->extension_name . DS . 'tables');        
        $this->destination_table = $this->general->getTable('Banner', 'BannersTable');
        $this->table_name = 'banner';
        $this->task->category = 12;
        $this->id = 'bid';
        $this->task->query = 'SELECT * 
            FROM #__' . $this->table_name . '
            WHERE ' . $this->id . ' > 0';
        
        $this->items($ids);        
    }
}
