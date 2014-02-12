<?php
/**
 * Jcrm Model for Jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Created with Marco's Component Creator for Joomla! 1.5
 * http://www.mmleoni.net/joomla-component-builder
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * Jcrm Model
 *
 * @package    Joomla
 * @subpackage 	Jcrm
 * 
 */
class JcrmModelAddressbook extends JModel{
	
	/**
	 * Jcrmcontactslist data array for tmp store
	 *
	 * @var array $_data_contactslist 
	 * @access private
	 */
	private $_data_contactslist;
	
	/**
	 * Jcrmaccount id of a contact selected for tmp store
	 *
	 * @var int $_id_account 
	 * @access private
	 */
	private $_id_account;
	
	/**
	 * Jcrmaccounts children list data array for tmp store
	 *
	 * @var array $_data_accounts_children 
	 * @access private
	 */
	private $_data_accounts_children;
	
	/**
	 * Jcrmaccountslist data array for tmp store
	 *
	 * @var array $_data_child 
	 * @access private
	 */
	private $_data_child;
	
	/**
	 * Jcrmcontacts children list data array for tmp store
	 *
	 * @var array $_child_contact 
	 * @access private
	 */
	private $_child_contact;
	
	/**
	* Pagination object for jcrmaccountslist
	* @var object
	* @access private
	*/
	private $_pagination = null;
	
	/**
	* Pagination object for jcrmcontactslist
	* @var object
	* @access private
	*/
	private $_pagination_contacts = null;

	/**
    * Constructor
    *
    * @return (void)
    * @access private
    *    
    */
	function __construct(){
		parent::__construct();

		$app = JFactory::getApplication();

        // Get pagination request variables
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);	
		// In case limit has been changed, adjust it
		$limit_contacts = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart_contacts = JRequest::getVar('limitstart', 0, '', 'int');     
        $limitstart_contacts = ($limit_contacts != 0 ? (floor($limitstart_contacts / $limit_contacts) * $limit_contacts) : 0);
        $this->setState('limit_contacts', $limit_contacts);
        $this->setState('limitstart_contacts', $limitstart_contacts);


	} // end function
	
/*-------------------*/    
/* F U N C T I O N S */
/*-------------------*/

	
	/**
	 * Gets the id of account from a selected contact
	 *
	 * @return (object) the id of account
	 * @access public
	 *
	 */
	public function getId_acct(){
	     if (empty( $this->_id_account )){
			$id = JRequest::getInt('id_cont',  0);
			$db = JFactory::getDBO();
			$query = "SELECT account_id FROM `#__jcrm_contacts` where `id` = ".$id;
			$db->setQuery( $query );
			$this->_id_account = $db->loadObject();
		}
		return $this->_id_account;
	}// end function
	
	
	/**
	 * Gets the list of jcrmcontacts
	 *
	 * @return (object) the list of jcrmcontacts
	 * @access public
	 *
	 */
    public function getContactslist(){
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data_contactslist )){
			// Gets the id of account from url
		    $id=JRequest::getInt('id_acct',0);
			$recordSet = $this->getTable('jcrmcontacts');
			
			$db = JFactory::getDBO();
			if($id!=0){
			$query = "SELECT * FROM `#__jcrm_contacts` as con where state=1 AND con.account_id=".$id;}
			else{
			$query = "SELECT t1.* FROM `#__jcrm_contacts` as t1".$this->_buildQueryWhere_contacts().$this->_buildQueryOrderBy_contacts();}
			// To seprate in some pages 
			$this->_data_contactslist = $this->_getList( $query, $this->getState('limitstart_contacts'), $this->getState('limit_contacts'));		
		}
		return $this->_data_contactslist;
	} // end function
	
	
	/**
	 * Gets the part of where in the query of search
	 *
	 * @return (string) the part of 'where' in the query of search contacts
	 * @access private
	 *
	 */
	private function _buildQueryWhere_contacts() {
	    
		// get the keywords input by the user and the alphabet selected 
		$search_cont=JRequest::getVar('search_cont','');
		$alf=JRequest::getVar('alf',null,'get');
		
		// make the attributs for search to an array
		$allowedSearch = explode(',', 'country_code,account_name,department,primary_address_street,primary_address_postalcode,primary_address_state,primary_address_city,salutation,first_name,last_name,title,phone_work,phone_fax,email,contact_type');
		
		// make the query where 
		$where = ' WHERE ( (1=0) ';
		foreach($allowedSearch as $field){
			$where .= " OR (t1.`$field` LIKE '%" . addSlashes($search_cont) . "%') and (t1.last_name LIKE '".$alf."%') ";
		}
		$where .= ') AND (state=1)';
	    return $where;
	} // end function


	
    /**
	 * Gets the part of 'order by' in the query 
	 *
	 * @return (string) the part of order by in the query
	 * @access private
	 *
	 */	
	private function _buildQueryOrderBy_contacts() {
	    $app = JFactory::getApplication();
		// default field for records list
		$default_order_field_cont = 'last_name'; 
		// Array of allowable order fields
	    $allowedOrders = explode(',', 'id,date_modified,date_entered,modified_user_id,created_by,state,country_code,account_name,department,primary_address_street,primary_address_postalcode,primary_address_state,primary_address_city,salutation,first_name,last_name,title,phone_work,phone_fax,email,website,mailing,account_id,comment,referent_postgradutate,referent_esa_name,active,contact_type'); // array('id', 'ordering', 'published'); 
		// retrive ordering info
		$filter_order_cont= $app->getUserStateFromRequest('com_jcrmfilter_order', 'filter_order_cont', $default_order_field_cont);
		$filter_order_Dir_cont = strtoupper($app->getUserStateFromRequest('com_jcrmfilter_order_Dir', 'filter_order_Dir_cont', 'ASC'));
	    // validate the order direction, must be ASC or DESC
	    if ($filter_order_Dir_cont != 'ASC' && $filter_order_Dir_cont != 'DESC') {
			$filter_order_Dir_cont = 'ASC';
	    }
	    // if order column is unknown use the default
	    if ((isSet($allowedOrders)) && !in_array($filter_order_cont, $allowedOrders)){
			$filter_order_cont = $default_order_field_cont;
	    }
		// comment out if use switch
		$prefix = 't1'; 
	    // return the ORDER BY clause        
	    return " ORDER BY ".$prefix.".`".$filter_order_cont."` ".$filter_order_Dir_cont;
	}  // end function
	
	
	
	/**
	 * Gets the number of jcrmaccounts published records
	 * @return int
	 */
	function getTotal(){
 	// Load the content if it doesn't already exist
 	if (empty($this->_total)) {
 	    $query = $this->_buildQuery();
 	    $this->_total = $this->_getListCount($query);	
 	}
 	return $this->_total;
	
    } // end function
	
	
	/**
	 * Gets the Pagination Object of jcrmaccountslist
	 * @return object JPagination
	 * @access public
	 */
	public function getPagination(){
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	} // end function
	
	
	
	/**
	 * Gets the number of published jcrmcontacts records
	 * @return int 
	 * @access public
	 *
	 */
    public function getTotal_contacts(){
	
 	// Load the content if it doesn't already exist
 	if (empty($this->_total_contacts)) {
			$db = JFactory::getDBO();
			$query = "SELECT t1.* FROM `#__jcrm_contacts` as t1".$this->_buildQueryWhere_contacts().$this->_buildQueryOrderBy_contacts();
 	    $this->_total_contacts = $this->_getListCount($query);	
 	}
 	return $this->_total_contacts;
    } // end function
  
  
	/**
	 * Gets the Pagination Object of jcrmcontactslist
	 * @return object JPagination
	 * @access public
	 */
	function getPagination_contacts(){
 	// Load the content if it doesn't already exist
 	if (empty($this->_pagination_contacts)) {
 	    jimport('joomla.html.pagination');
 	    $this->_pagination_contacts = new JPagination($this->getTotal_contacts(), $this->getState('limitstart_contacts'), $this->getState('limit_contacts') );
 	}
 	return $this->_pagination_contacts;
    } // end function
	
	
	/**
	 * Save the data of jcrmcontact to the database
	 * @return object JError if failed
	 * @access public
	 *
	 */
	public function save_contact(){
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcrm'.DS.'tables');
		$db= JFactory::getDBO();
		$row = JTable::getInstance('jcrmcontacts', 'Table');
		if (!$row->bind( JRequest::get( 'post' ) )) {
			return JError::raiseWarning( 500, $row->getError() );
		} 
		if (!$row->store()) {
			return JError::raiseError(500, $row->getError() );
		} 
	} // end function
	
	
	
	/**
	 * Save the data of jcrmaccount to the database
	 * @return object JError if failed
	 * @access public
	 *
	 */
	public function save_account(){
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcrm'.DS.'tables');
		$db= JFactory::getDBO();
		$row = JTable::getInstance('jcrmaccounts', 'Table');
		if (!$row->bind( JRequest::get( 'post' ) )) {
			return JError::raiseWarning( 500, $row->getError() );
		} 
		if (!$row->store()) {
			return JError::raiseError(500, $row->getError() );
		} 
	} // end function
	
	
	/**
	 * Delete a record of jcrmaccount:'set the state=0'
	 * @return object JError if failed
	 * @access public
	 *
	 */
	public function delete_account($id){

	    JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcrm'.DS.'tables');
		$db= JFactory::getDBO();
		$row = JTable::getInstance('jcrmaccounts', 'Table');
		if (!$row->load( $id )) {
			return JError::raiseWarning( 500, $row->getError() );
		}
		$row->state = 0;
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		
	} // end function
	
	
	/**
	 * Delete a record of jcrmcontact:'set state=0'
	 * @return object JError if failed
	 * @access public
	 *
	 */
	public function delete_contact($id){
	    JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcrm'.DS.'tables');
		$db= JFactory::getDBO();
		$row = JTable::getInstance('jcrmcontacts', 'Table');
		if (!$row->load( $id )) {
			return JError::raiseWarning( 500, $row->getError() );
		}
		$row->state = 0;
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
	}  // end function
	
	
	/**
	 * Gets the query for jcrmaccountslist
	 * @return string query
	 * @access private
	 *
	 */
	private function _buildQuery(){
		// use alias t1 for easier JOINs writing
		$query = 'SELECT t1.* FROM `#__jcrm_accounts` as t1 ' . $this->_buildQueryWhere() .$this->_buildQueryOrderBy();
		return $query;
	}  // end function

	
	
	/**
	 * Returns the 'order by' part of the query to publish the jcrmaccounts
	 * @return string the 'order by'  part of the query
	 * @access private
	 */
	private function _buildQueryOrderBy() {
	    $app = JFactory::getApplication();

		// default field for records list
		$default_order_field = 'name'; 
		// Array of allowable order fields
	    $allowedOrders = explode(',', 'id,parent_id,date_modified,date_entered,modified_user_id,created_by,country_id,name,logo_name,phone_fax,phone_account,account_type,account_speciality,cours_list,degrees_list,research_areas_list,annual_appropriations,address_street,address_street_2,address_postalcode,address_city,address_state,address_country,website,director_name,director_email,location,economic_information,number_student_places,number_students,code_account,faculties_list,areas_of_excellence,campus_info,agreements_list,practical_info,comment,partner_esa'); // array('id', 'ordering', 'published'); 

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

	    // return the ORDER BY clause        
	    return " ORDER BY ".$prefix.".`".$filter_order."` ".$filter_order_Dir;
	} // end function



	/**
	 * Returns the 'where' part of the query of jcrmaccounts
	 * @return string the 'where'  part of the query
	 * @access private
	 */
	private function _buildQueryWhere() {
	    $app = JFactory::getApplication();
		// Gets the keywords input by user for search
		$search=JRequest::getVar('search','');
		$allowedSearch=JRequest::getVar('elements','');
		
		if($allowedSearch){
		$where = ' WHERE (state=1) AND (parent_id is null OR parent_id=0)   ';
		$i=0;
		// build the part 'where' for the query
		foreach($allowedSearch as $a){
		if(!empty($a)){
		$where.="and t1.`$a` LIKE '%" . addSlashes($search[$i]) . "%'";
		$i++;}else{$allowedSearch = explode(',', 'country_id,name,logo_name,phone_fax,phone_account,account_type,account_speciality,annual_appropriations,address_street,address_street_2,address_postalcode,address_city,address_state,address_country,website,director_name,director_email,location,economic_information,number_student_places,number_students,code_account,faculties_list');
		$where = ' WHERE ( (1=0) ';
		 foreach($allowedSearch as $field){
			
			$where .= " OR (t1.`$field` LIKE '%" . addSlashes($search[0]) . "%') ";
		}
		$where .= ') AND (state=1) AND (parent_id is null OR parent_id=0)';}
			}
		}
		// if the user input nothing
		if(empty($allowedSearch)){
		$allowedSearch = explode(',', 'country_id,name,logo_name,phone_fax,phone_account,account_type,account_speciality,annual_appropriations,address_street,address_street_2,address_postalcode,address_city,address_state,address_country,website,director_name,director_email,location,economic_information,number_student_places,number_students,code_account,faculties_list');
		$where = ' WHERE ( (1=0) ';
		 foreach($allowedSearch as $field){
			
			$where .= " OR (t1.`$field` LIKE '%" . addSlashes($search) . "%') ";
		}
		$where .= ') AND (state=1) AND (parent_id is null OR parent_id=0)';
		}
	    return $where;
	} // end function
	
	
	
	/**
	 * Returns the jcrmaccountslist to be published
	 * @return array The list of jcrmaccounts to be published
	 * @access public
	 */
	public function getAccounts(){
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	} // end function
	
	
	
	/**
	 * Returns the jcrmaccounts children list to be published
	 * @return array the list to be published
	 * @access public
	 *
	 */
	public function getChild(){
	       $id_acct=JRequest::getInt('id_acct', 0);
		   if(empty($this->_data_child)){
		  
		   $db= JFactory::getDBO();
		   $query="SELECT * FROM #__jcrm_accounts WHERE parent_id=".$id_acct." AND state=1";
		   $db->setQuery($query);
		   $this->_data_child = $db->loadObjectList();       
		}
		return $this->_data_child;
	   }  // end function
	   
	   
	   
	/**
	 * Returns the jcrmcontacts children list to be published
	 * @return array the list to be published
	 * @access public
	 *
	 */
	public function getContacts($id_acct){
	    
		   if(empty($this->_child_contact)){
		  
		   $db= JFactory::getDBO();
		   $query="SELECT * FROM #__jcrm_contacts WHERE account_id=".$id_acct." AND state=1";
		   $db->setQuery($query);
		   $this->_child_contact = $db->loadObjectList();
		       
		}
		return $this->_child_contact;
	} // end function
	
	
	
	/**
	 * Returns the jcrmaccounts children list to be published
	 * @return (array) the list to be published
	 * @access public
	 * @param int $id_acct The id of account selected
	 * 
	 */
	public function getChildAccount($id_acct){
	       //$id_acct=JRequest::getInt('id_acct',null);
		   if(empty($this->_data_accounts_children)){
		  
		   $db= JFactory::getDBO();
		   $query="SELECT * FROM #__jcrm_accounts WHERE parent_id=".$id_acct." AND state=1";
		   $db->setQuery($query);
		   $this->_data_accounts_children = $db->loadObjectList();
		       
		}
		return $this->_data_accounts_children;
	   }  // end function
	

	
} //end class
?>