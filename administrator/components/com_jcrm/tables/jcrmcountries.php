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
class TableJcrmcountries extends JTable{
	/** jcb code */
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	/**
	 *
	 * @var string
	 */
	var $name = null;
	/**
	 *
	 * @var string
	 */
	var $zone = null;
	/**
	 *
	 * @var string
	 */
	var $continent = null;
	/** jcb code */

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableJcrmcountries(& $db){
		parent::__construct('#__jcrm_countries', 'id', $db);
	}
	
	function check(){
		// write here data validation code
		return parent::check();
	}
}