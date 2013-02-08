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

class SPUpgradeModelCom_Content extends SPUpgradeModelCom {

    public function sections($ids = null) {        
        // Initialize
        $jAp = $this->jAp;
        $general = $this->general;
        $tableLog = $this->tableLog;
        $destination_db = $this->destination_db;
        $destination_query = $this->destination_query;
        $source_db = $this->source_db;
        $source_query = $this->source_query;
        $destination_table = $general->getTable('Category', 'JTable');
        $user = $this->user;
        $params = $this->params;
        $task = $this->task;

        $message = ('<h2>' . JText::_(COM_CONTENT) . ' - ' . JText::_(COM_CONTENT_SECTIONS) . '</h2>');
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

        $query = 'SELECT * 
            FROM #__sections
            WHERE id > 0';
        if (!is_null($temp[0]))
            $query .= ' AND id NOT IN (' . implode(',', $temp) . ')';
        if (!is_null($ids[0]))
            $query .= ' AND id IN (' . implode(',', $ids) . ')';
        $query .= ' ORDER BY id ASC';
        $source_db->setQuery($query);
        $result = $source_db->query();
        if (!$result) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }
        $items = $source_db->loadAssocList();

        //Find total number of categories
        $query = 'SELECT id' .
                ' FROM #__categories' .
                ' ORDER BY id DESC';
        $source_db->setQuery($query);
        $result = $source_db->query();
        if (!$result) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }
        $catnum = $source_db->loadResult();

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
            $tableLog->load(array("tables_id" => $task->id, "source_id" => $item['id']));
            $tableLog->created = null;
            $tableLog->note = "";
            $tableLog->source_id = $item['id'];
            $item['id'] = $catnum + $i + 1; //find destination id
            $tableLog->destination_id = $item['id'];
            $tableLog->state = 1;
            $tableLog->tables_id = $task->id;

            //access difference
            if ($item['access'] > 2) {
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_ACCESSLEVEL', $item['id']) . '</p>';
                $general->writeLog($message);
                continue;
            }
            if ($item['access'] == 2)
                $item['access'] = 3;
            if ($item['access'] == 1)
                $item['access'] = 2;
            if ($item['access'] == 0)
                $item['access'] = 1;

            // Create record
            $destination_db->setQuery(
                    "INSERT INTO #__categories" .
                    " (id)" .
                    " VALUES (" . $destination_db->quote($item['id']) . ")"
            );
            if (!$destination_db->query()) {
                if ($params->get("new_ids", 0) == 1) {
                    $destination_db->setQuery(
                            "INSERT INTO #__categories" .
                            " (title)" .
                            " VALUES (" . $destination_db->quote('sp_transfer') . ")"
                    );
                    if (!$destination_db->query()) {
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                        $general->writeLog($message);
                        $tableLog->note = $message;
                        $tableLog->store();
                        continue;
                    }
                    $destination_db->setQuery(
                            "SELECT id FROM #__categories" .
                            " WHERE title LIKE " . $destination_db->quote('sp_transfer')
                    );
                    $destination_db->query();
                    $tableLog->destination_id = $destination_db->loadResult();
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_NEW_IDS', $item['id'], $tableLog->destination_id) . '</p>';
                    $item['id'] = $tableLog->destination_id;
                    $general->writeLog($message);
                    $tableLog->note = $message;
                } elseif ($params->get("new_ids", 0) == 0) {
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    $tableLog->store();
                    continue;
                }
            }

            // Reset
            $destination_table->reset();

            //Replace existing item
            if ($params->get("new_ids", 0) == 2)
                $destination_table->load($item['id']);

            // Bind
            if (!$destination_table->bind($item)) {
                // delete record
                $destination_db->setQuery(
                        "DELETE FROM #__categories" .
                        " WHERE id = " . $destination_db->quote($item['id'])
                );
                if (!$destination_db->query()) {
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                    $general->writeLog($message);
                }
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_BIND', $item['id'], $destination_table->getError()) . '</p>';
                $general->writeLog($message);
                $tableLog->note = $message;
                $tableLog->store();
                continue;
            }

            //no parent
            $destination_table->asset_id = null;
            $destination_table->parent_id = 1;
            $destination_table->lft = null;
            $destination_table->rgt = null;
            $destination_table->level = null;
            $destination_table->path = null;
            $destination_table->extension = "com_content";
            $destination_table->language = '*';

            if ($item['image'] != "") {
                $destination_table->params = '{"category_layout":"","image":"images\/stories\/' .
                        $item['image'] .
                        '"}';
            }

            // Store
            if (!$destination_table->store()) {
                if ($params->get("duplicate_alias", 0)) {
                    $destination_table->alias .= '-sp-' . rand(100, 999);
                    if (!$destination_table->store()) {
                        // delete record
                        $destination_db->setQuery(
                                "DELETE FROM #__categories" .
                                " WHERE id = " . $destination_db->quote($item['id'])
                        );
                        if (!$destination_db->query()) {
                            $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                            $general->writeLog($message);
                        }
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_STORE', $item['id'], $destination_table->getError()) . '</p>';
                        $general->writeLog($message);
                        $tableLog->note = $message;
                        $tableLog->store();
                        continue;
                    }
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_DUPLICATE_ALIAS', $item['id'], $destination_table->alias) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                } else {
                    // delete record
                    $destination_db->setQuery(
                            "DELETE FROM #__categories" .
                            " WHERE id = " . $destination_db->quote($item['id'])
                    );
                    if (!$destination_db->query()) {
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                        $general->writeLog($message);
                    }
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_STORE', $item['id'], $destination_table->getError()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    $tableLog->store();
                    continue;
                }
            }

            //Log
            $tableLog->state = 4;
            $tableLog->store();
        } //Main loop end
        // Rebuild the hierarchy.
        if (!$destination_table->rebuild()) {
            $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_REBUILD', $destination_table->getError()) . '</p>';
            $general->writeLog($message);
            return false;
        }

        // Clear the component's cache
        $cache = JFactory::getCache('com_categories');
        $cache->clean();
    }

    public function categories($ids = null) {
        //initialize
        $this->destination_table = $this->general->getTable('Category', 'JTable');        
        $this->task->section = ' WHERE section NOT LIKE "com_%"';
        $this->task->state = 2; //state for success
        
        parent::categories($ids);
        
        //Fix categories
        $this->categories_fix($ids);
    }

    public function content($ids = null) {
        //initialize
        $this->destination_table = $this->general->getTable('Content', 'JTable');
        $this->table_name = 'content';
        $this->task->category = 4;
        $this->id = 'id';
        $this->task->query = 'SELECT * 
            FROM #__' . $this->table_name . '
            WHERE ' . $this->id . ' > 0';
        
        $this->items($ids);        
    }
    
    private function categories_fix($ids = null) {
        // Initialize
        $jAp = $this->jAp;
        $general = $this->general;
        $tableLog = $this->tableLog;
        $destination_db = $this->destination_db;
        $destination_query = $this->destination_query;
        $source_db = $this->source_db;
        $source_query = $this->source_query;
        $destination_table = $general->getTable('Category', 'JTable');
        $user = $this->user;
        $params = $this->params;
        $task = $this->task;

        $message = ('<h2>' . JText::_('COM_CONTENT') . ' - ' . JText::_('COM_CONTENT_CATEGORIES'). ' - ' . JText::_('COM_SPUPGRADE_FIX') . '</h2>');
        $general->writeLog($message);

        // Load items
        $query = 'SELECT destination_id
            FROM #__spupgrade_log
            WHERE tables_id = ' . (int) $task->id . ' AND ( state = 2 OR state = 3 )';
        if (!is_null($ids[0]))
            $query .= ' AND source_id IN (' . implode(',', $ids) . ')';
        $query .= ' ORDER BY id ASC';
        $destination_db->setQuery($query);
        $result = $destination_db->query();
        if (!$result) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $source_db->getErrorMsg()) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }
        $temp = $destination_db->loadResultArray();

        // Return if empty
        if (is_null($temp[0])) {
            $message = '<p>' . JText::_(COM_SPUPGRADE_NOTHING_TO_FIX) . '</p>';
            $general->writeLog($message);
            return;
        }

        $query = 'SELECT * 
            FROM #__categories
            WHERE extension LIKE "com_content"
            AND parent_id > 0';
        $query .= ' AND id IN (' . implode(',', $temp) . ')';
        $query .= ' ORDER BY id ASC';
        $destination_db->setQuery($query);
        $result = $destination_db->query();
        if (!$result) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $destination_db->getErrorMsg()), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_QUERY', $destination_db->getErrorMsg()) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }
        $items = $destination_db->loadAssocList();

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

            //parent_id
            if ($item['parent_id'] > 0) {
                $tableLog->reset();
                $tableLog->id = null;
                $tableLog->load(array("tables_id" => 3, "source_id" => $item['parent_id']));
                if ($tableLog->source_id == $tableLog->destination_id) {
                    $tableLog->load(array("tables_id" => $task->id, "destination_id" => $item['id']));
                    $tableLog->state = 4;
                    $tableLog->store();
                    continue;
                }
                $item['parent_id'] = $tableLog->destination_id;
            } else {
                $tableLog->load(array("tables_id" => $task->id, "destination_id" => $item['id']));
                $tableLog->state = 4;
                $tableLog->store();
                continue;
            }

            //log            
            $tableLog->reset();
            $tableLog->id = null;
            $tableLog->load(array("tables_id" => $task->id, "destination_id" => $item['id']));
            $tableLog->created = null;
            $tableLog->state = 3;
            $tableLog->tables_id = $task->id;

            // Reset
            $destination_table->reset();

            // Bind
            if (!$destination_table->bind($item)) {
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_BIND', $item['id'], $destination_table->getError()) . '</p>';
                $general->writeLog($message);
                $tableLog->note = $message;
                $tableLog->store();
                continue;
            }

            // Store
            if (!$destination_table->store()) {
                if ($params->get("duplicate_alias", 0)) {
                    $destination_table->alias .= '-sp-' . rand(100, 999);
                    if (!$destination_table->store()) {
                        // delete record
                        $destination_db->setQuery(
                                "DELETE FROM #__categories" .
                                " WHERE id = " . $destination_db->quote($item['id'])
                        );
                        if (!$destination_db->query()) {
                            $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                            $general->writeLog($message);
                        }
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_STORE', $item['id'], $destination_table->getError()) . '</p>';
                        $general->writeLog($message);
                        $tableLog->note = $message;
                        $tableLog->store();
                        continue;
                    }
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_DUPLICATE_ALIAS', $item['id'], $destination_table->alias) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                } else {
                    // delete record
                    $destination_db->setQuery(
                            "DELETE FROM #__categories" .
                            " WHERE id = " . $destination_db->quote($item['id'])
                    );
                    if (!$destination_db->query()) {
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                        $general->writeLog($message);
                    }
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_STORE', $item['id'], $destination_table->getError()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->state = 1;
                    $tableLog->note = $message;
                    $tableLog->store();
                    continue;
                }
            }

            //Log
            $tableLog->state = 4;
            $tableLog->store();
        } //Main loop end   
        // Rebuild the hierarchy.
        if (!$destination_table->rebuild()) {
            $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_REBUILD', $destination_table->getError()) . '</p>';
            $general->writeLog($message);
            return false;
        }

        // Clear the component's cache
        $cache = JFactory::getCache('com_categories');
        $cache->clean();
    }
}
