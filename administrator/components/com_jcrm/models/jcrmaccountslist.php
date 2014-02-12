<?php

/**
 * Jcrm Model for Jcrm Component
 * 
 * @package    Jcrm
 * @subpackage com_jcrm
 * @license  GNU/GPL v2
 *
 * Created with Marco's Component Creator for Joomla! 1.5
 * http://www.mmleoni.net/joomla-component-builder
 *
 */


// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * Jcrm Model
 *
 * @package    Joomla.Components
 * @subpackage 	Jcrm
 */
class JcrmModelJcrmaccountslist extends JModel{
	/**
	 * Jcrmaccountslist data array
	 *
	 * @var array
	 */
	private $_data;

	/**
	* Pagination object
	* @var object
	*/
	private $_pagination = null;

	/*
	 * Constructor
	 *
	 */
	function __construct(){
		parent::__construct();

		$app =& JFactory::getApplication();

        // Get pagination request variables
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
 
        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

	}

	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	private function _buildQuery(){
		// use alias t1 for easier JOINs writing
		$query = 'SELECT t1.* FROM `#__jcrm_accounts` t1 ' . $this->_buildQueryWhere() . $this->_buildQueryOrderBy();
		return $query;
	}

	/**
	 * Returns the 'order by' part of the query
	 * @return string the order by''  part of the query
	 */
	private function _buildQueryOrderBy() {
	    $app =& JFactory::getApplication();

		// default field for records list
		$default_order_field = 'id'; 
		// Array of allowable order fields
	    $allowedOrders = explode(',', 'id,parent_id,date_modified,date_entered,modified_user_id,created_by,state,country_id,name,logo_name,phone_fax,phone_account,account_type,account_speciality,cours_list,degrees_list,research_areas_list,annual_appropriations,address_street,address_street_2,address_postalcode,address_city,address_state,address_country,website,director_name,director_email,location,economic_information,number_student_places,number_students,code_account,faculties_list,areas_of_excellence,campus_info,agreements_list,practical_info,comment,partner_esa'); // array('id', 'ordering', 'published'); 

		// retrive ordering info
		$filter_order = $app->getUserStateFromRequest('com_jcrmfilter_order', 'filter_order', $default_order_field);
		$filter_order_Dir = strtoupper($app->getUserStateFromRequest('com_jcrmfilter_order_Dir', 'filter_order_Dir', 'ASC'));

	    // validate the order direction, must be ASC or DESC
	    if ($filter_order_Dir != 'ASC' && $filter_order_Dir != 'DESC') {
			$filter_order_Dir = 'ASC';
	    }

	    // if order column is unknown use the default
	    if ((isSet($allowedOrders)) && !in_array($filter_order, $allowedOrders)){
			$filter_order = $default_order_field;
	    }
		// comment out if use switch
		$prefix = 't1'; 
		/*
		// strip comment if you use JOIN in select statement
		switch ($filter_order){
			case 'field1FromTable2':
			case 'field2FromTable2':
				$prefix = 't2';
				break;
			case 'field1FromOtherTable3':
				$prefix = 't3';
				break;
			default:
				$prefix = 't1';
				break;
		}
		*/

	    // return the ORDER BY clause        
	    return " ORDER BY {$prefix}.`{$filter_order}` {$filter_order_Dir}";
	}	
	private function _buildQueryWhere() {
	    $app =& JFactory::getApplication();
	
		$search = $app->getUserStateFromRequest('com_jcrmsearch', 'search', '');
		
	    if (!$search) return '';
		
		$allowedSearch = explode(',', 'country_id,name,logo_name,phone_fax,phone_account,account_type,account_speciality,annual_appropriations,address_street,address_street_2,address_postalcode,address_city,address_state,address_country,website,director_name,director_email,location,economic_information,number_student_places,number_students,code_account,faculties_list'); // array('id', 'ordering', 'published'); 
		$where = ' WHERE (0=1) ';
		foreach($allowedSearch as $field){
			if (!$field) return '';
			$where .= " OR (t1.`$field` LIKE '%" . addSlashes($search) . "%') ";
		}
	    return $where;
	}
	
	
	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	public function getData(){
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

	/**
	 * Gets the number of published records
	 * @return int
	 */
	public function getTotal(){
		$db =& JFactory::getDBO();
		$db->setQuery( 'SELECT COUNT(*) FROM `#__jcrm_accounts` t1 ' . $this->_buildQueryWhere() );
		return $db->loadResult();
	}
	
	/**
	 * Gets the Pagination Object
	 * @return object JPagination
	 */
	public function getPagination(){
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	/**
	 * Methods to get records data for specific fields
	 * use returned recorset to populate view in specific
	 * select to manage related tables
	 * @return object list with options array
	 */
	
	public function &getGenericFieldName($fieldName){
		$db =& JFactory::getDBO();
		$db->setQuery( 'SELECT id AS value `$fieldName` AS text FROM `#__jcrm_accounts` ORDER BY `$fieldName`');
		$options = array();
		foreach( $db->loadObjectList() as $r){
			$options[] = JHTML::_('select.option',  $r->value, $r->text );
        }
		return $options;

	}
	
	public function &getParent_idFieldData(){
		$db =& JFactory::getDBO();
		$db->setQuery( 'SELECT `id` AS value, `parent_id` AS text FROM `#__jcrm_accounts` ORDER BY parent_id');
		$options = array();
		foreach( $db->loadObjectList() as $r){
			$options[] = JHTML::_('select.option',  $r->value, $r->text );
		}
		return $options;
	}


	public function &getNameFieldData(){
		$db =& JFactory::getDBO();
		$db->setQuery( 'SELECT `id` AS value, `name` AS text FROM `#__jcrm_accounts` ORDER BY name');
		$options = array();
		foreach( $db->loadObjectList() as $r){
			$options[] = JHTML::_('select.option',  $r->value, $r->text );
		}
		return $options;
	}


	

	
}