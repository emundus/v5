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

class SPUpgradeModelCom_Users extends JModel {

    protected $jAp;
    protected $tableLog;
    protected $destination_db;
    protected $destination_query;
    protected $source_db;
    protected $source_query;
    protected $user;
    protected $params;
    protected $task;
    protected $general;

    function __construct($config = array()) {
        parent::__construct($config);
        $this->general = new SPUpgradeGeneral();
        $this->jAp = & JFactory::getApplication();
        $this->tableLog = $this->general->getTable('Log', 'SPUpgradeTable');
        $this->destination_db = $this->getDbo();
        $this->destination_query = $this->destination_db->getQuery(true);
        $this->source_db = $this->general->getDboSource();
        $this->source_query = $this->source_db->getQuery(true);
        $this->user = JFactory::getUser(0);
        $this->params = JComponentHelper::getParams('com_spupgrade');
    }

    public function init($task) {
        $this->task = $task;
    }

    
    public function users($ids = null) {
        // Initialize
        $jAp = $this->jAp;
        $general = $this->general;
        $tableLog = $this->tableLog;
        $destination_db = $this->destination_db;
        $destination_query = $this->destination_query;
        $source_db = $this->source_db;
        $source_query = $this->source_query;
        $params = $this->params;
        $task = $this->task;        
        $destination_table = $general->getTable('User', 'JTable');
        $user = $this->user;
        
        $message = ('<h2>' . JText::_(COM_USERS) . ' - ' . JText::_(COM_USERS_USERS) . '</h2>');
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
            FROM #__users
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

            //Remove unesseary fields
            unset($item['gid']);

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
            $tableLog->destination_id = $item['id'];
            $tableLog->state = 1;
            $tableLog->tables_id = $task->id;

            // Special treatment for admin
            if ($item['id'] == 62) {
                $item['username'] = $item['username'] . 'v15';
                $item['email'] = $item['email'] . 'v15';
                $message = '<p>' . JText::_('COM_SPUPGRADE_MSG_OLD_ADMIN') . '<br/>';
                $general->writeLog($message);
                $message = 'username: ' . $item['username'] . '<br/>';
                $general->writeLog($message);
                $message = 'email: ' . $item['email'] . '</p>';
                $general->writeLog($message);
            }

            //handle params
            $item_params = explode("\n", $item['params']);
            foreach ($item_params as $key => $param) {
                $attribs = explode("=", $param);
                if (count($attribs) > 1) {
                    if ($attribs[0] == 'timezone') {
                        $new_params[$attribs[0]] = '';
                    } else {
                        $new_params[$attribs[0]] = $attribs[1];
                    }
                }
            }
            $item['params'] = json_encode($new_params);

            //Handle user group
            if ($item['usertype'] == "Super Administrator")
                $item['usertype'] = "Super Users";
            $destination_db->setQuery(
                    'SELECT id' .
                    ' FROM #__usergroups' .
                    ' WHERE title LIKE ' . $destination_db->quote($item['usertype'])
            );
            $destination_db->query();
            $group_id = $destination_db->loadResult();

            // Create record
            if ($params->get("new_ids", 0) == 2) {
                $query = "REPLACE INTO #__users";   
            } else {
                $query = "INSERT INTO #__users";
            }            
            $query .= " (";
            foreach ($item as $key => $value) {
                $query .= $destination_db->nameQuote($key) . ",";
            }
            $query = chop($query, ",");
            $query .=")";
            $query .= " VALUES (";
            foreach ($item as $key => $value) {
                $query .= $destination_db->quote($value) . ",";
            }
            $query = chop($query, ",");
            $query .=")";

            $destination_db->setQuery($query);
            if (!$destination_db->query()) {                
                if ($params->get("new_ids", 0) == 1) {
                    $destination_db->setQuery(
                            "INSERT INTO #__users" .
                            " (email)" .
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
                            "SELECT id FROM #__users" .
                            " WHERE email LIKE " . $destination_db->quote('sp_transfer')
                    );
                    $destination_db->query();
                    $tableLog->destination_id = $destination_db->loadResult();
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_NEW_IDS', $item['id'], $tableLog->destination_id) . '</p>';
                    $item['id'] = $tableLog->destination_id;
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    $query = "UPDATE #__users";
                    $query .= " SET ";
                    foreach ($item as $key => $value) {
                        $query .= $destination_db->nameQuote($key) . "=" . $destination_db->quote($value) . ",";
                    }
                    $query = chop($query, ",");
                    $query .= " WHERE `id` =" . (int) $item['id'];
                    $destination_db->setQuery($query);
                    if (!$destination_db->query()) {
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                        $general->writeLog($message);
                        $tableLog->note = $message;
                        $tableLog->store();
                        continue;
                    }
                } elseif ($params->get("new_ids", 0) == 0) {
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    $tableLog->store();
                    continue;
                }
            }

            // check for existing username
            $query = 'SELECT id'
                    . ' FROM #__users '
                    . ' WHERE username = ' . $destination_db->Quote($item['username'])
                    . ' AND id != ' . (int) $item['id'];
            $destination_db->setQuery($query);
            $xid = intval($destination_db->loadResult());
            if ($xid && $xid != intval($item['id'])) {
                $item['username'] .= '-sp-' . rand();
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_DUPLICATE_USERNAME', $item['id'], $item['username']) . '</p>';
                $general->writeLog($message);
                $tableLog->note = $message;
                $query = "UPDATE #__users";
                $query .= " SET ";
                foreach ($item as $key => $value) {
                    $query .= $destination_db->nameQuote($key) . "=" . $destination_db->quote($value) . ",";
                }
                $query = chop($query, ",");
                $query .= " WHERE `id` =" . (int) $item['id'];
                $destination_db->setQuery($query);
                if (!$destination_db->query()) {
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    // delete record
                    $destination_db->setQuery(
                            "DELETE FROM #__users" .
                            " WHERE id = " . $destination_db->quote($item['id'])
                    );
                    if (!$destination_db->query()) {
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                        $general->writeLog($message);
                        continue;
                    }
                }
            }

            // check for existing email
            $query = 'SELECT id'
                    . ' FROM #__users '
                    . ' WHERE email = ' . $destination_db->Quote($item['email'])
                    . ' AND id != ' . (int) $item['id']
            ;
            $destination_db->setQuery($query);
            $xid = intval($destination_db->loadResult());
            if ($xid && $xid != intval($this->id)) {
                $item['email'] .= '-sp-' . rand();
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_DUPLICATE_EMAIL', $item['id'], $item['email']) . '</p>';
                $general->writeLog($message);
                $tableLog->note = $message;
                $query = "UPDATE #__users";
                $query .= " SET ";
                foreach ($item as $key => $value) {
                    $query .= $destination_db->nameQuote($key) . "=" . $destination_db->quote($value) . ",";
                }
                $query = chop($query, ",");
                $query .= " WHERE `id` =" . (int) $item['id'];
                $destination_db->setQuery($query);
                if (!$destination_db->query()) {
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    // delete record
                    $destination_db->setQuery(
                            "DELETE FROM #__users" .
                            " WHERE id = " . $destination_db->quote($item['id'])
                    );
                    if (!$destination_db->query()) {
                        $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                        $general->writeLog($message);
                        continue;
                    }
                }
            }

            // User Usergroup Map
            if ($params->get("new_ids", 0) == 2) {
                $query = "DELETE FROM #__user_usergroup_map WHERE user_id = ".$destination_db->quote($item['id']);
                $destination_db->setQuery($query);
                if (!$destination_db->query()) {
                    $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_DELETE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
                    $general->writeLog($message);
                    $tableLog->note = $message;
                    $tableLog->store();
                    continue;
                }
            }  
            $query = "INSERT INTO #__user_usergroup_map";
            $query .= " (user_id,group_id)" .
                    " VALUES (" . $destination_db->quote($item['id']) . ',' . $destination_db->quote($group_id) . ")";
            $destination_db->setQuery($query);
            if (!$destination_db->query()) {
                $message = '<p>' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CREATE', $item['id'], $destination_db->getErrorMsg()) . '</p>';
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
}
