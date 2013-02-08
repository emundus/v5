<?php

/**
 * @package		SP Upgrade
 * @subpackage	Components
 * @copyright	SP CYEND - All rights reserved.
 * @author		SP CYEND
 * @link		http://www.cyend.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

//jimport('joomla.application.component.model');

/**
 * SP CYEND general class
 *
 */
class SPUpgradeGeneral {

    public $source_db;
    public $source_path;

    function __construct() {
        $config = JFactory::getConfig();
        $params = JComponentHelper::getParams('com_spupgrade');
        $option = array(); //prevent problems 
        $option['driver'] = $params->get("driver", 'mysqli');            // Local Database driver name     
        $option['host'] = $params->get("host", 'localhost');    // Database host name
        $option['user'] = $params->get("source_user_name", '');       // User for database authentication
        $option['password'] = $params->get("source_password", '');   // Password for database authentication
        $option['database'] = $params->get("source_database_name", '');      // Database name
        $option['prefix'] = $this->modPrefix($params->get("source_db_prefix", ''));             // Database prefix (may be empty)
        $this->source_path = $params->get("source_path", '');      // source directory path

        $this->source_db = & JDatabase::getInstance($option);
    }

    public function getDboSource() {
        $config = JFactory::getConfig();
        $params = JComponentHelper::getParams('com_spupgrade');
        $option = array(); //prevent problems 
        $option['driver'] = $params->get("driver", 'mysqli');            // Local Database driver name     
        $option['host'] = $params->get("host", 'localhost');    // Database host name
        $option['user'] = $params->get("source_user_name", '');       // User for database authentication
        $option['password'] = $params->get("source_password", '');   // Password for database authentication
        $option['database'] = $params->get("source_database_name", '');      // Database name
        $option['prefix'] = $this->modPrefix($params->get("source_db_prefix", ''));             // Database prefix (may be empty)

        $this->source_db = & JDatabase::getInstance($option);
        return $this->source_db;
    }

    private function modPrefix($prefix) { //Add underscore if not their
        if (!strpos($prefix, '_'))
            $prefix = $prefix . '_';
        return $prefix;
    }

    public function writeLog($message, $mode = 'a') {
        $fileName = JPATH_COMPONENT_ADMINISTRATOR . '/log.htm';
        $handle = fopen($fileName, $mode);
        if ($handle) {
            fwrite($handle, $message);
            fflush($handle);
            fclose($handle);
        }
        return true;
    }

    public function testConnection() {
        //Check connection
        $query = "SELECT id from #__users";
        $this->source_db->setQuery($query);
        if (!$this->source_db->query())
            return false;
        return true;
    }
    
    public function testPathConnection() {
        $source_path = $this->source_path;
        if(empty($source_path)) 
            return false;
        return JFile::exists($source_path.'/index.php');
    }

    public function print_r($msg) {
        $return = '<pre>' . print_r($msg, true) . '</pre>';
        echo $return;
        return $return;
    }

    public function getTable($type = 'Tables', $prefix = 'JTable', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getModel($name = 'Tables', $prefix = 'SPUpgradeModel') {
        //$model = parent::getModel($name, $prefix, array('ignore_request' => true));
        $model = JModel::getInstance($name, $prefix, array('ignore_request' => true));
        return $model;
    }

    public function getOldId($id, $task_id) {
        $tableLog = $this->getTable('Log', 'SPUpgradeTable');
        $tableLog->reset();
        $tableLog->id = null;
        $tableLog->load(array("tables_id" => $task_id, "source_id" => $id));

        return $tableLog->destination_id;
    }

}
