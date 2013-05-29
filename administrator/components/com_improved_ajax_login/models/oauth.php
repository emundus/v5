<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login & Register
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class ImprovedAjaxLoginModelOAuth extends JoomlaModel
{

	var $_id = null;
	var $_data = null;

	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit', true);
		if ($edit) $this->setId((int)$array[0]);
	}

	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	function &getData()
	{
		$this->_loadData();
		return $this->_data;
	}

	function store($data)
	{
		$row =& $this->getTable();

		// Bind the form fields to the web link table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Make sure the web link table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	function publish($cid = array(), $publish = 1)
	{
		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
      if ($publish == "1") {
        $this->_db->setQuery("SELECT id FROM #__offlajn_oauths WHERE id IN ($cids) AND app_id NOT IN('') AND app_secret NOT IN('')");
        $res = $this->_db->loadResultArray();
        if (!$res || count($res)!=count($cid)) {
          JError::raiseWarning("", "If 'Application Key/ID' or 'Application Secret Key/ID' is empty then you can't enable it.");
          if (!$res) return false;
        }
      }
			$query = 'UPDATE #__offlajn_oauths'
				. ' SET published = '.(int) $publish
				. ' WHERE id IN ( '.$cids.' ) ';
      if ($publish==1) $query.= "AND app_id NOT IN('') AND app_secret NOT IN('') ";
			$this->_db->setQuery( $query );
      $result = $this->_db->query();
			if (!$result) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT * FROM #__offlajn_oauths AS oo WHERE oo.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$oauth = new stdClass();
			$oauth->id					= 0;
			$oauth->name				= "";
			$oauth->app_id      = null;
			$oauth->app_secret  = null;
			$oauth->published		= 0;
			$oauth->create_app	= "";
			$this->_data					= $oauth;
			return (boolean) $this->_data;
		}
		return true;
	}

}