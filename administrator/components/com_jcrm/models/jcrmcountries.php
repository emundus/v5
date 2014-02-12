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

jimport('joomla.application.component.model');

/**
 * Jcrm Model
 *
 * @package    Joomla.Components
 * @subpackage 	Jcrm
 */
class JcrmModelJcrmcountries extends JModel{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct(){
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier for the record
	 *
	 * @access	public
	 * @param	int primary key identifier
	 * @return	void
	 */
	public function setId($id){
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a record
	 * @return object with data
	 */
	public function &getData(){
		// Load the data
		if (empty( $this->_data )) {
			$query = 'SELECT * FROM `#__jcrm_countries` WHERE `id` = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data =& $this->getTable();
		}
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	public function store(){	
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );
		// HTML content must be required!
		//$data['my_html_field'] = JRequest::getVar( 'my_html_field', '', 'post', 'string', JREQUEST_ALLOWHTML );
		
// mcm code 
		$data['id'] = JRequest::getVar('id', '', 'post', 'int');
// mcm code 


		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError( $row->_db->getErrorMsg() );
			return false;
		}

		return true;
	}

	/**
	 * Method to delete record(s)
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	public function delete(){
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids )) {
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * Method to move record(s)
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */			
	public function move($direction){
		$row =& $this->getTable();
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
				
	/**
	 * Method to save the new order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	public function saveorder($cid = array(), $order){
		$row =& $this->getTable();

		// update ordering values
		$n = count($cid);
		for( $i=0; $i < $n; $i++ )
		{
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}			

	/**
	 * Methods to get options arrays for specific fields
	 * @return object with data
	 */
	
	public function &getGenericFieldName(){
		$options = array(
            JHTML::_('select.option',  'val1', 'text 1' ),
            JHTML::_('select.option',  'val2', 'text 2' )
        );    
		return $options;
	}
	
	

}