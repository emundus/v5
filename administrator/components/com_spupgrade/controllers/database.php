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

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * SPUpgrades Controller
 */
class SPUpgradeControllerDatabase extends JControllerAdmin {

    function transfer() {
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
  
        $general = new SPUpgradeGeneral();

        //Validate Input IDs        
        $input_ids = JRequest::getVar('input_ids', array(), '', 'array');
        $input_ids = $this->validateInputIDs($input_ids);
        if (!$input_ids) {
            $this->setRedirect('index.php?option=com_spupgrade&view=database', JText::_('COM_SPUPGRADE_MSG_ERROR_INVALID_IDS'), 'error');
            return false;
        }

        //Initial tasks
        //Disable warnings
        error_reporting(E_ERROR | E_PARSE);
        set_time_limit(0);

        //monitor log
        //$message= '<META HTTP-EQUIV="REFRESH" CONTENT="15">';
        $message = '';
        $general->writeLog($message, 'w'); // create monitor log
        $message = ('<h1>' . JText::_(COM_SPUPGRADE_START) . '</h1>');
        $general->writeLog($message);

        //monitor log        
        $message= '<META HTTP-EQUIV="REFRESH" CONTENT="15">';
        //$message = '';
        $general->writeLog($message, 'w'); // create monitor log        
        $general->writeLog($message);
        $message = '<link rel="stylesheet" href="'.JURI::base().'templates/bluestork/css/template.css" type="text/css" />';
        $general->writeLog($message);
        $message = '<div class="m">';
        $general->writeLog($message);
        $message = '<p>* This log will be automatically refreshed every 15 seconds.</p>';
        $general->writeLog($message);
        $message = ('<h1>' . JText::_(COM_SPUPGRADE_START) . '</h1>');
        $general->writeLog($message);

        // Connect to source db
        if (!$general->testConnection()) {
            $this->setRedirect('index.php?option=com_spupgrade', JText::_("COM_SPUPGRADE_MSG_ERROR_CONNECTION"), 'error');
            return false;
        }

        // Main Loop within extensions
        //Get ids
        $ids = JRequest::getVar('cid', array(), '', 'array');
        $input_prefixes = JRequest::getVar('input_prefixes', array(), '', 'array');
        $input_names = JRequest::getVar('input_names', array(), '', 'array');

        // Get the model.
        $model = $general->getModel('Database');

        //Main Loop
        foreach ($ids as $i => $id) {
            $table_name = $input_prefixes[$id-1].'_'.$input_names[$id-1];
            $item = $model->getItem($table_name);
            if (is_null($item)) {
                //Insert new item in tables
                $item = $model->newItem($table_name);
            }
            if (is_null($item)) {
                $message = ('<p>' . JText::plural('COM_SPUPGRADE_DATABASE_FAILED', $table_name) . '</p>');
                $general->writeLog($message);
                continue;
            }

            $modelContent = $this->getModel('com_database');
            $modelContent->init($source_db, $item);
            if (!$modelContent->setTable($input_prefixes[$id-1], $input_names[$id-1])) {
                $message = ('<p>' . JText::plural('COM_SPUPGRADE_DATABASE_FAILED', $table_name) . '</p>');
                $general->writeLog($message);
                continue;
            }            
            $modelContent->content($input_ids[$id - 1], $input_prefixes[$id-1], $input_names[$id-1]);
        }

        // Finish
        error_reporting(E_ALL);
        set_time_limit(30);
        $message = ('<h1>' . JText::_('COM_SPUPGRADE_COMPLETED') . '</h1>');
        $general->writeLog($message);    
        $this->setRedirect('index.php?option=com_spupgrade&view=monitoring_log', JText::_("COM_SPUPGRADE_COMPLETED"));
    }

    function validateInputIDs($input_ids) {
        $return = Array();
        foreach ($input_ids as $i => $ids) {
            if ($ids != "") {
                $ranges = explode(",", $ids);
                foreach ($ranges as $j => $range) {
                    if (preg_match("/^[0-9]*$/", $range)) {
                        $return[$i][] = $range;
                    } else {
                        if (preg_match("/^[0-9]*-[0-9]*$/", $range)) {
                            $nums = explode("-", $range);
                            if ($nums[0] >= $nums[1])
                                return false;
                            for ($k = $nums[0]; $k <= $nums[1]; $k++) {
                                $return[$i][] = $k;
                            }
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
        if (count($return) == 0) {
            return true;
        } else {
            return $return;
        }
    }

}
