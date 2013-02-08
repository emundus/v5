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

class SPUpgradeModelCom_Modules extends SPUpgradeModelCom {

    public function modules($ids = null) {
        //initialize
        $this->destination_table = $this->general->getTable('Module', 'JTable');
        $this->table_name = 'modules';
        $this->task->category = null;
        $this->id = 'id';
        $this->task->query = '
            SELECT * 
            FROM #__'.$this->table_name.'
            WHERE client_id = 0
            ';  
        
        $this->items($ids);        
    }
    
}
