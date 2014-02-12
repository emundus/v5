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
class JcrmModelJcrmdeleted extends JModel{

	/**
	 * Jcrmaccounts data array for tmp store
	 *
	 * @var array $_data_account
	 * @access private
	 */
	private $_data_account;
	
	
	/**
	 * Jcrmcontacts data array for tmp store
	 *
	 * @var array $_data_cont
	 * @access private
	 */
	private $_data_contact;
	
	
	
	/**
	 * Gets the data of jcrmaccounts deleted (state=0)
	 * @return (array)  The accounts deleted (state=0) to be displayed to the user
	 * @access public
	 */
	public function getData(){
		// Load the content if it doesn't already exist
		if (empty( $this->_data_account )){
			// Gets all datas from jcrmaccounts
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__jcrm_accounts` where `state` = 0";
			$db->setQuery( $query );
			$this->_data_account = $db->loadObjectList();
		} // end if
		return $this->_data_account;
	} // end function
	
	
	/**
	 * Gets the data of jcrmcontacts deleted(state=0)
	 * @return (array)  The data to be displayed to the user
	 * @access public
	 */
	public function getCont(){
	// Load the content if it doesn't already exist
		if (empty( $this->_data_contact )){
			// Gets all datas from jcrmcontacts
			$db = JFactory::getDBO();
			$query = "SELECT * FROM `#__jcrm_contacts` where `state` = 0";
			$db->setQuery( $query );
			$this->_data_contact = $db->loadObjectList();
		} // end if
		return $this->_data_contact;
	} // end function
	
	
	/**
	 * Restore the data of jcrmaccounts selected:set state=1
	 * @param (array) $cids All the id of accounted to be restored
	 * @return (object)  JError
	 * @access public
	 *
	 */
	public function restore_acct($cids){
		// Adds a filesystem path where JTable should search for table class files
	    JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcrm'.DS.'tables');
		$db= JFactory::getDBO();
		// Returns a reference to the a Table object
		$row = JTable::getInstance('jcrmaccounts', 'Table');
		if (count( $cids )) {
			foreach($cids as $cid) {
		// Load the data form jcrmaccounts
		if (!$row->load( $cid )) {
			return JError::raiseWarning( 500, $row->getError() );
		} // end if
		// Update the state in jcrmaccounts for restore
		$row->state = 1;
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
				} // end if
			} // end foreach
		} // end if
	
    } // end function
	
	
	/**
	 * Restore the data of jcrmcontacts selected:set state=1
	 * @param (array) $dels All the id of contacts to be restored
	 * @return (object)  JError
	 * @access public
	 */
	public function restore_cont($dels){
		// Adds a filesystem path where JTable should search for table class files
	    JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jcrm'.DS.'tables');
		$db= JFactory::getDBO();
		// Returns a reference to the a Table object
		$row = JTable::getInstance('jcrmcontacts', 'Table');
		if (count( $dels )) {
			foreach($dels as $del) {
		// Load the data form jcrmcontacts
		if (!$row->load( $del )) {
			return JError::raiseWarning( 500, $row->getError() );
		}  // end if
		// Update the state in jcrmcontacts for restore
		$row->state = 1;
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
				} // end if
			} // end foreach
		} // end if
	} // end function
	
	
	/**
	 * Delete the data of jcrmaccounts selected from database
	 * @param (array) $cids All the id of accounts to be deleted
	 * @return (boolean) 
	 * @access public
	 */
	public function deleteAcct($cids){
		// Returns a reference to the a Table object
		$row = JTable::getInstance('jcrmaccounts', 'Table');
		if (count( $cids )) {
			foreach($cids as $cid) {
			// Delete the data of accounts selected
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				} // end if
			} // end foreach
		} // end if
		return true;
	} // end function
	
	
	/**
	 * Delete the data of jcrmaccounts selected from database
	 * @param (array) $cids All the id of accounts to be deleted
	 * @return (boolean) 
	 * @access public
	 */
	public function deleteCont($dels){
		// Returns a reference to the a Table object
		$row = JTable::getInstance('jcrmcontacts', 'Table');
		if (count( $dels )) {
			foreach($dels as $del) {
			// Delete the data of accounts selected
				if (!$row->delete( $del)) {
					$this->setError( $row->getErrorMsg() );
					return false;
				} // end if
			} // end foreach
		} // end if
		return true;
	} // end function
} // end class
?>