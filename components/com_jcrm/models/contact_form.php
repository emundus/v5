<?php
/**
 * Jcrm Model for Jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Décision Publique
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
 */
class JcrmModelContact_form extends JModel{

	/**
	 * Jcrmcontacts data array for tmp store
	 *
	 * @var array _data_contact
	 * @access private
	 */
	private $_data_contact;
	
	
	/**
	 * Jcrmaccount id for tmp store
	 *
	 * @var int _id_account
	 * @access private
	 */
	private $_id_account;
	
	
	/**
	 * Emundus countries array for tmp store
	 *
	 * @var int _data_country
	 * @access private
	 */
	private $_data_country;

	
	/**
	 * Gets the data of jcrmcontacts 
	 *
	 * @return (array) the list of contacts published
	 * @access public
	 *
	 */
	public function getData_cont(){
		 // Load the content if it doesn't already exist
	     if (empty( $this->_data_contact )){
			// gets the id of contact selected
			$id = JRequest::getInt('id_cont',  0);
			$db = JFactory::getDBO();
			$query = "SELECT a.name as account_name, c.`id`, c.`date_modified`, c.`date_entered`, c.`modified_user_id`, c.`created_by`, c.`state`, c.`country_code`, c.`department`, c.`primary_address_street`, c.`primary_address_postalcode`, c.`primary_address_state`, c.`primary_address_city`, c.`salutation`, c.`first_name`, c.`last_name`, c.`title`, c.`phone_work`, c.`phone_fax`, c.`email`, c.`website`, c.`mailing`, c.`account_id`, c.`comment`, c.`referent_postgradutate`, c.`referent_esa_name`, c.`active`, c.`contact_type` FROM `#__jcrm_contacts` as c LEFT JOIN `#__jcrm_accounts` AS a on a.id=c.account_id where c.`id` = {$id}";
			$db->setQuery( $query );
			$this->_data_contact = $db->loadObject();
		} // end if
		return $this->_data_contact;
	} // end function
	
	
	/**
	 * Gets the id of jcrmaccount selected 
	 *
	 * @return (int) the id of jcrmaccount selected 
	 * @access public
	 *
	 */
	public function getId_acct(){
	        // Gets the id of contact selected
			$id = JRequest::getInt('id_cont',  0);
			// Gets the id of account of the contact
			$db = JFactory::getDBO();
			$query = "SELECT account_id FROM `#__jcrm_contacts` where `id` = {$id}";
			$db->setQuery( $query );
			$this->_id_account = $db->loadObject();
		
		return $this->_id_account;
	} // end function
	
	
	/**
	 * Gets the data of jcrmaccount selected 
	 *
	 * @param string $search_value
	 * @return (array) the data of jcrmaccounts found 
	 * @access public
	 *
	 */
	public function getAccounts($search_value){
		$db= JFactory::getDBO();
		// build the query without 'where'
		$sql = "SELECT a1.id, CONCAT_WS('->',a1.name,a2.name,a3.name) AS name, CONCAT(a1.address_postalcode,',',a1.address_city,',',a1.address_state) AS info
				FROM  `#__jcrm_accounts` AS a1
				LEFT JOIN #__jcrm_accounts AS a2 ON a1.parent_id= a2.id 
				LEFT JOIN #__jcrm_accounts AS a3 ON a2.parent_id=a3.id";
		// Explode the vlues searched to an array
		$slist = explode(' ', $search_value);
		// build the query of 'where'
		$where = '(1=0) ';
		if (count($slist)>1) {
			$where .= ' OR (';
			for($i=1 ; count($slist) > $i ; $i++)
				$where .= ' a1.name like "%'.$slist[$i].'%" AND';
			$where .= ' 1)';
		}
		else $where .= ' OR a1.name LIKE "%'.$search_value.'%" ';
		// build the whole query and excute
		$query = $sql.' WHERE '.$where.' ORDER BY a1.name limit 10';
		
		$db->setQuery($query); 
		return $db->loadAssocList();
	} // end function
	
	
	
	/**
	 * Gets the emundus countries list 
	 *
	 * @return (array) the list of emundus countries 
	 * @access public
	 *
	 */
	public function getCountry(){
	   $db= JFactory::getDBO();
	   $query=" SELECT * FROM `#__emundus_country` where 1 order by name_en" ;
	   $db->setQuery($query);
	   $this->_data_country=$db->loadObjectList();
	   return $this->_data_country;
	   
	} // end function
	
	
	
	/**
	 * Gets the name of acounts whose id is maximum 
	 *
	 * @return (array) the name of the account 
	 * @access public
	 *
	 */
	public function getMaxorg(){
	
		$db= JFactory::getDBO();
		$query="SELECT name FROM #__jcrm_accounts WHERE id=(SELECT max(id) FROM #__jcrm_accounts WHERE 1)";
		$db->setQuery($query);
		$orgName=$db->loadResult();
		return $orgName;
	} // end function
	
	
	/**
	 * Gets the max id of accounts
	 *
	 * @return (array) the id of the account 
	 * @access public
	 *
	 */
	public function getMaxorgid(){
	
		$db= JFactory::getDBO();
		$query="SELECT id FROM #__jcrm_accounts WHERE id=(SELECT max(id) FROM #__jcrm_accounts WHERE 1)";
		$db->setQuery($query);
		$orgid=$db->loadResult();
		return $orgid;
	} // end function
	
	
} // end class
?>