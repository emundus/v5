<?php
/**
 * Jcrm Model for Jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 * Dижcision Publique
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

/**
 * Jcrm Model
 *
 * @package    Joomla
 * @subpackage Jcrm
 */
class JcrmModelOrganisation_form extends JModel{

	/**
	 * Jcrmaccounts data array for tmp store
	 *
	 * @var array $_data_account
	 * @access private
	 */
	private $_data_account;
	
	/**
	 * Emundus countries data array for tmp store
	 *
	 * @var array $_data_country
	 * @access private
	 */
	private $_data_country;
	
	/**
	 * Jcrmcountries data array for tmp store
	 *
	 * @var array $_data_countryjcrm
	 * @access private
	 */
	private $_data_countryjcrm;
	
	
	/**
	 * Parent account id for tmp store
	 *
	 * @var array $_parent_id
	 * @access private
	 */
	private $_parent_id;
	
	
	/**
	 * Jcrmaccount name for tmp store
	 *
	 * @var array $_account_name
	 * @access private
	 */
	private $_account_name;
	
	
	
	/**
	 * Gets the data of jcrmaccounts selected
	 * @return (array) The data of accounts to be displayed to the user
	 * @access public
	 */
	public function getData_acct(){
	    // Load the content if it doesn't already exist
	     if (empty( $this->_data_account )){
			// Gets the id of account selected
			$id = JRequest::getVar('id_acct', null);
			$db = JFactory::getDBO();
			$query="SELECT * From `#__jcrm_accounts` where id=".$id;
			$db->setQuery( $query );
			$this->_data_account = $db->loadObject();
		}
		return $this->_data_account;
	} // end function


	/**
	 * Gets the data of emundus_references selected
	 * @return (array) The data of accounts to be displayed to the user
	 * @access public
	 */
	public function getData_references(){ 
		$id = JRequest::getVar('i_referee', null, 'get');
		$id_references = explode("-", JRequest::getVar('id_ref', null, 'get'));
		
		$db = JFactory::getDBO();
		$query="SELECT user, First_Name_".$id." as firstname, Last_Name_".$id." as lastname, Organisation_".$id." as organisation, 
						Address_".$id." as address_street, Position_".$id." as posution, City_".$id." as address_city, Country_".$id." as address_country, Telephone_".$id." as phone_account, Email_".$id." as email 
				from `#__emundus_references` 
				where id=".$id_references[0];
		$db->setQuery( $query );
		$this->_data_account = $db->loadObject();

		return $this->_data_account;
	} // end function
	
	
	/**
	 * Gets the data of emunduscountries
	 * @return (array) The data to be displayed to the user
	 * @access public
	 */
	public function getCountry(){
		// Gets the contents input by user
		$s = JRequest::getVar('input', '');
		$db= JFactory::getDBO();
		// Gets the data of emundus countries
		$query = 'SELECT id, name_en FROM `#__emundus_country` WHERE name_en like "%'.$s.'%" ORDER BY name_en LIMIT 6';
		$db->setQuery($query);
		return $db->loadAssocList();  
	} // end function
	
	
	
	/**
	 * Gets the data of jcrmcountries
	 * @return (array) The data to be displayed to the user
	 * @access public
	 *
	 */
	public function getCountryJcrm(){
	   $db= JFactory::getDBO();
	   // Gets all datas from jcrmcountries
	   $query=" SELECT * FROM `#__jcrm_countries` where 1 order by id" ;
	   $db->setQuery($query);
	   $this->_data_countryjcrm=$db->loadObjectList();
	   return $this->_data_countryjcrm;
	   
	} // end function
	
	
	
	/**
	 * Gets the name of jcrmaccount selected
	 * @return (string) The name of jcrmaccount selected
	 * @access public
	 *
	 */
	public function getAccountname(){
	   $db= JFactory::getDBO();
	   // Gets the id of account selected
	   $id = JRequest::getVar('id_acct', null);
	   // Gets the parent id of the account selected
	   $query="SELECT parent_id FROM `#__jcrm_accounts` where id=".$id;
	   $db->setQuery($query);
	   $this->_parent_id=$db->loadObject();
	   $id_acct=$this->_parent_id;
      // If the parent id is not null
	  if(!empty( $id_acct)){
	  // Gets the name of the parent account
	   $query="SELECT name FROM `#__jcrm_accounts` where id=".$id_acct->parent_id; 
	   $db->setQuery($query);
	   $this->_account_name=$db->loadObject();
	 // return the name of the parent account
	   return $this->_account_name;
	} // end if
	} // end function
	
	
} // end class
?>