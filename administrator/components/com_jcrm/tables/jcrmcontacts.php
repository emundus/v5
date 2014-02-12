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

/**
 * Jcrm Table
 *
 * @package    Joomla.Components
 * @subpackage 	Jcrm
 */
class TableJcrmcontacts extends JTable{
	/** jcb code */
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	/**
	 *
	 * @var datetime
	 */
	var $date_modified = "0000-00-00 00:00:00";
	/**
	 *
	 * @var datetime
	 */
	var $date_entered = "CURRENT_TIMESTAMP";
	/**
	 *
	 * @var int
	 */
	var $modified_user_id = null;
	/**
	 *
	 * @var int
	 */
	var $created_by = null;
	/**
	 *
	 * @var int
	 */
	var $state = null;
	/**
	 *
	 * @var string
	 */
	var $country_code = null;
	/**
	 *
	 * @var string
	 */
	var $account_name = null;
	/**
	 *
	 * @var string
	 */
	var $department = null;
	/**
	 *
	 * @var string
	 */
	var $primary_address_street = null;
	/**
	 *
	 * @var string
	 */
	var $primary_address_postalcode = null;
	/**
	 *
	 * @var string
	 */
	var $primary_address_state = null;
	/**
	 *
	 * @var string
	 */
	var $primary_address_city = null;
	/**
	 *
	 * @var string
	 */
	var $salutation = null;
	/**
	 *
	 * @var string
	 */
	var $first_name = null;
	/**
	 *
	 * @var string
	 */
	var $last_name = null;
	/**
	 *
	 * @var string
	 */
	var $title = null;
	/**
	 *
	 * @var string
	 */
	var $phone_work = null;
	/**
	 *
	 * @var string
	 */
	var $phone_fax = null;
	/**
	 *
	 * @var string
	 */
	var $email = null;
	/**
	 *
	 * @var string
	 */
	var $website = null;
	/**
	 *
	 * @var int
	 */
	var $mailing = null;
	/**
	 *
	 * @var int
	 */
	var $account_id = null;
	/**
	 *
	 * @var string
	 */
	var $comment = null;
	/**
	 *
	 * @var int
	 */
	var $referent_postgradutate = null;
	/**
	 *
	 * @var string
	 */
	var $referent_esa_name = null;
	/**
	 *
	 * @var int
	 */
	var $active = null;
	/**
	 *
	 * @var string
	 */
	var $contact_type = null;
	/** jcb code */

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableJcrmcontacts(& $db){
		parent::__construct('#__jcrm_contacts', 'id', $db);
	}
	
	function check(){
		// write here data validation code
		return parent::check();
	}
}