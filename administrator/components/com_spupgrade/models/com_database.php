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

class SPUpgradeModelCom_Database extends JModel {

    protected $jAp;
    protected $tableLog;
    protected $destination_db;
    protected $destination_query;
    protected $destination_table;
    protected $table_name;
    protected $source_db;
    protected $source_query;
    protected $user;
    protected $params;
    protected $task;
    protected $general;
    protected $id;

    function __construct($config = array()) {
        parent::__construct($config);
        $this->general = new SPUpgradeGeneral();
        $this->jAp = & JFactory::getApplication();
        $this->tableLog = $this->general->tableLog;
        $this->tableLog = $this->general->getTable('Log', 'SPUpgradeTable');
        $this->destination_db = $this->getDbo();
        $this->destination_query = $this->destination_db->getQuery(true);
        $this->source_db = $this->general->getDboSource();
        $this->source_query = $this->source_db->getQuery(true);
        $this->user = JFactory::getUser();
        $this->params = JComponentHelper::getParams('com_spupgrade');
    }

    public function init($source_db, $task) {
        $this->task = $task;
    }

    public function content($ids = null, $prefix, $name) {
        // Initialize
        $jAp = $this->jAp;
        $general = $this->general;
        $tableLog = $this->tableLog;
        $destination_db = $this->destination_db;
        $destination_query = $this->destination_query;
        $source_db = $this->source_db;
        $source_query = $this->source_query;
        $task = $this->task;
        $user = $this->user;
        $params = $this->params;
        $destination_table = $this->destination_table;
        $table_name = $this->table_name;
        $id = $this->id;

        $source_table_name = $prefix . '_' . $name;
        $destination_table_name = $destination_db->getPrefix() . $name;
        $items = Array();

        $message = ('<h2>' . JText::_($task->extension_name) . ' - ' . JText::_($task->name) . '</h2>');
        $general->writeLog($message);

        // Load items
        $query = 'SELECT source_id
            FROM #__spupgrade_log
            WHERE tables_id = ' . (int) $task->id . ' AND state >= 2
            ORDER BY id ASC';
        $destination_db->setQuery($query);
        $result = $destination_db->query();
        if (!$result) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $destination_db->getErrorMsg()), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->$destination_db()) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }
        $temp = $destination_db->loadResultArray();

        $query = 'select @rownum:=@rownum+1 sp_id, p.* from #__' . $name . ' p, (SELECT @rownum:=0) r';
        $query .= ' ORDER BY sp_id ASC';

        $source_db->setQuery($query);
        $result = $source_db->query();
        if (!$result) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }
        $items2 = $source_db->loadAssocList();

        //Remove unecessary ids
        // @task - improve performance when removing items
        // @task - when empty ids nothing is transferred
        foreach ($items2 as $i => $item) {
            if (!is_null($ids[0])) {
                foreach ($ids as $j => $temp2) {
                    if ($item['sp_id'] == $temp2) {
                        $bool = true;
                        foreach ($temp as $k => $temp1) {
                            if ($temp1 == $temp2) {
                                $bool = false;
                            }
                        }
                        if ($bool)
                            $items[] = $item;
                    }
                }
            } else {
                $bool = true;
                foreach ($temp as $k => $temp1) {
                    if ($temp1 == $item['sp_id']) {
                        $bool = false;
                    }
                }
                if ($bool)
                    $items[] = $item;
            }
        }

        //percentage
        $percTotal = count($items);
        if ($percTotal < 100)
            $percKlasma = 0.1;
        if ($percTotal > 100 && $percTotal < 2000)
            $percKlasma = 0.05;
        if ($percTotal > 2000)
            $percKlasma = 0.01;
        $percTen = $percKlasma * $percTotal;
        $percCounter = 0;
        if ($percTotal == 0) {
            $message = '<p>' . JText::_(COM_SPUPGRADE_NOTHING_TO_TRANSFER) . '</p>';
            $general->writeLog($message);
        }
        // Loop to save items
        foreach ($items as $i => $item) {

            //percentage
            $percCounter += 1;
            if (@($percCounter % $percTen) == 0) {
                $perc = round(( 100 * $percCounter ) / $percTotal);
                $message = $perc . '% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
                $general->writeLog($message);
            }

            //log            
            $tableLog->reset();
            $tableLog->id = null;
            $tableLog->load(array("tables_id" => $task->id, "source_id" => $item['sp_id']));
            $tableLog->created = null;
            $tableLog->note = "";
            $tableLog->source_id = $item['sp_id'];
            $tableLog->destination_id = $item['sp_id'];
            $tableLog->state = 1;
            $tableLog->tables_id = $task->id;

            //Build query
            $query = "INSERT INTO #__" . $name . " (";
            if ($params->get("new_ids", 0) == 2)
                $query = "REPLACE INTO #__" . $name . " (";
            $columnNames = Array();
            $values = Array();
            foreach ($item as $column => $value) {
                if ($column != 'sp_id') {
                    $columnNames[] = $destination_db->quoteName($column);
                    $temp1 = implode(',', $columnNames);
                    $values[] = $destination_db->quote($value);
                    $temp2 = implode(',', $values);
                }
            }
            $query .= $temp1 . ") VALUES (" . $temp2 . ")";
            
            // Create record
            $destination_db->setQuery($query);
            if (!$destination_db->query()) {
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', print_r($item, true), $destination_db->getErrorMsg()) . '</p>';
                $general->writeLog($message);
                $tableLog->note = $message;
                $tableLog->store();
                continue;
            }

            //Log
            $tableLog->state = 4;
            $tableLog->store();
        } //Main loop end
    }

    public function setTable($prefix, $name) {
        $general = $this->general;
        
        //Exit if empty table
        $source_table_name = $prefix . '_' . $name;
        if (is_null($source_table_name))
            return false;

        // Init
        $destination_db = $this->destination_db;
        $destination_query = $this->destination_query;
        $source_db = $this->source_db;
        $source_query = $this->source_query;

        //Define destination table name
        $destination_table_name = $destination_db->getPrefix() . $name;

        // Get tables descriptions
        $query = 'SHOW CREATE TABLE ' . $source_table_name;

        $source_db->setQuery($query);
        $result = $source_db->query();
        $source_table_desc = $source_db->loadObject();

        $query = 'describe ' . $destination_table_name;
        $destination_db->setQuery($query);
        $result = $destination_db->query();
        if (!result) {
            $message = '<b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $destination_db->getErrorMsg()) . '</font></b>';
            $general->writeLog($message);
            return false;
        }
        $destination_table_desc = $destination_db->loadAssocList();
        $query = $source_table_desc->{'Create Table'};
        $query = str_replace('CREATE TABLE `' . $source_table_name, 'CREATE TABLE `' . $destination_table_name, $query);
        if (empty($destination_table_desc)) {
            //Create table
            $destination_db->setQuery($query);
            $result = $destination_db->query();
            if (!result) {
                $message = '<b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $destination_db->getErrorMsg()) . '</font></b>';
                $general->writeLog($message);
                return false;
            }
        } else {
            //Compare tables
            $query = 'describe ' . $source_table_name;
            $source_db->setQuery($query);
            $result = $source_db->query();
            $source_table_desc = $source_db->loadAssocList();
            //$compare_desc = array_diff($destination_table_desc, $source_table_desc);
            //if (!empty($compare_desc)) {                
            if ($destination_table_desc != $source_table_desc) {
                // Different structure
                //@task - Deal option if different structure
                $message = '<b><font color="red">' . JText::sprintf('COM_SPUPGRADE_DATABASE_DIFFERENT_STRUCTURE', $destination_table_name) . '</font></b>';
                $general->writeLog($message);
                return false;
            }
        }

        return true;
    }

}
