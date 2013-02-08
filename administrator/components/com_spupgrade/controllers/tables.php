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
class SPUpgradeControllerTables extends JControllerAdmin {
    

    function transfer() {
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $general = new SPUpgradeGeneral();

        //Get task ids
        $ids = JRequest::getVar('cid', array(), '', 'array');                
        $task_ids = JRequest::getVar('task_ids', array(), '', 'array');
        //Validate Input IDs        
        $input_ids = JRequest::getVar('input_ids', array(), '', 'array');                                        
        $input_ids = $this->validateInputIDs($input_ids, $task_ids);
        if (!$input_ids) {
            $this->setRedirect('index.php?option=com_spupgrade', JText::_('COM_SPUPGRADE_MSG_ERROR_INVALID_IDS'), 'error');
            return false;
        }

        //Initial tasks
        //Disable warnings
        error_reporting(E_ERROR | E_PARSE);
        set_time_limit(0);

        //monitor log        
        $message= '<META HTTP-EQUIV="REFRESH" CONTENT="15">';
        //$message = '';
        $general->writeLog($message, 'w'); // create monitor log        
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
        
        // Get the model.
        $model = $general->getModel();
        //Main Loop
        foreach ($ids as $i => $id) {
            if (!($item = $model->getItem($id)))
                JError::raiseWarning(500, $model->getError());
            $modelContent = $general->getModel($item->extension_name);
            $modelContent->init($item);            
            //Exit if images
            echo $modelContent->{$item->name}($input_ids[$id]);               
        }

        // Finish
        //enable warnings
        error_reporting(E_ALL);
        set_time_limit(30);        
        $message = ('<h1>' . JText::_('COM_SPUPGRADE_COMPLETED') . '</h1>');
        $general->writeLog($message);
        $message = '</div>';
        $general->writeLog($message);
        $this->setRedirect('index.php?option=com_spupgrade&view=monitoring_log', JText::_("COM_SPUPGRADE_COMPLETED"));
    }

    function validateInputIDs($input_ids, $task_ids) {        
        $return = Array();
        foreach ($input_ids as $i => $ids) {                        
            if ($ids != "") {
            $task_id = $task_ids[$i];  
                $ranges = explode(",", $ids);                
                foreach ($ranges as $j => $range) {
                    if (preg_match("/^[0-9]*$/", $range)) {
                        $return[$task_id][] = $range;
                    } else {
                        if (preg_match("/^[0-9]*-[0-9]*$/", $range)) {
                            $nums = explode("-", $range);
                            if ($nums[0] >= $nums[1])
                                return false;
                            for ($k = $nums[0]; $k <= $nums[1]; $k++) {
                                $return[$task_id][] = $k;
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
