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
class TableJcrmaccounts extends JTable{
	/** jcb code */
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	/**
	 *
	 * @var int
	 */
	var $parent_id = null;
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
	var $modified_user_id = -1;
	/**
	 *
	 * @var int
	 */
	var $created_by = -1;
	/**
	 *
	 * @var int
	 */
	var $state = 1;
	/**
	 *
	 * @var string
	 */
	var $country_id = null;
	/**
	 *
	 * @var string
	 */
	var $name = null;
	/**
	 *
	 * @var string
	 */
	var $logo_name = null;
	/**
	 *
	 * @var string
	 */
	var $phone_fax = null;
	/**
	 *
	 * @var string
	 */
	var $phone_account = null;
	/**
	 *
	 * @var string
	 */
	var $account_type = null;
	/**
	 *
	 * @var string
	 */
	var $account_speciality = null;
	/**
	 *
	 * @var string
	 */
	var $cours_list = null;
	/**
	 *
	 * @var string
	 */
	var $degrees_list = null;
	/**
	 *
	 * @var string
	 */
	var $research_areas_list = null;
	/**
	 *
	 * @var string
	 */
	var $annual_appropriations = null;
	/**
	 *
	 * @var string
	 */
	var $address_street = null;
	/**
	 *
	 * @var string
	 */
	var $address_street_2 = null;
	/**
	 *
	 * @var string
	 */
	var $address_postalcode = null;
	/**
	 *
	 * @var string
	 */
	var $address_city = null;
	/**
	 *
	 * @var string
	 */
	var $address_state = null;
	/**
	 *
	 * @var string
	 */
	var $address_country = null;
	/**
	 *
	 * @var string
	 */
	var $website = null;
	/**
	 *
	 * @var string
	 */
	var $director_name = null;
	/**
	 *
	 * @var string
	 */
	var $director_email = null;
	/**
	 *
	 * @var string
	 */
	var $location = null;
	/**
	 *
	 * @var string
	 */
	var $economic_information = null;
	/**
	 *
	 * @var string
	 */
	var $number_student_places = null;
	/**
	 *
	 * @var string
	 */
	var $number_students = null;
	/**
	 *
	 * @var string
	 */
	var $code_account = null;
	/**
	 *
	 * @var string
	 */
	var $faculties_list = null;
	/**
	 *
	 * @var string
	 */
	var $areas_of_excellence = null;
	/**
	 *
	 * @var string
	 */
	var $campus_info = null;
	/**
	 *
	 * @var string
	 */
	var $agreements_list = null;
	/**
	 *
	 * @var string
	 */
	var $practical_info = null;
	/**
	 *
	 * @var string
	 */
	var $comment = null;
	/**
	 *
	 * @var int
	 */
	var $partner_esa = null;
	/** jcb code */

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableJcrmaccounts(& $db){
		parent::__construct('#__jcrm_accounts', 'id', $db);
	}
	
	function check(){
		// write here data validation code
		return parent::check();
	}
}