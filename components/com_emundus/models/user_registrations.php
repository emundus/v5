<?php
/**
 * @version		$Id: report.php 24 2009-03-31 04:07:57Z eddieajau $
 * @package		Emundus
 * @copyright	Copyright (C) 2008 - 2009 JXtended LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');

/**
 * Reporting class
 * @package		Emundus
 */
class EmundusModelUser_registrations extends JModel
{

		
	var $_total = null;
	var $_pagination = null;
	
	
	function __construct()
	{
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();
 
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		//die('--->'.$limit);
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
 
        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$filter_order     = $mainframe->getUserStateFromRequest(  $option.'filter_order', 'filter_order', 'lastname', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
 
        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
 
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}
	
	
	function _buildQuery(){
	
	        $db = &$this->getDBO();
			$query='SELECT YEAR(a.registerDate) AS user_year, MONTH(a.registerDate) AS user_month, 	DAY(a.registerDate) AS user_day, COUNT(a.id) AS user_count
			FROM #__users As a GROUP BY YEAR(registerDate), MONTH(registerDate), DAY(registerDate)';
			
			return $query;
	}
	
	function _buildContentOrderBy(){
	
	
		$filter_order = 'YEAR(a.registerDate)';	
		$filter_order_Dir='desc';
		
		
		$order_by=' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		return $order_by;
	}
	
	function getUser(){
		
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		//echo str_replace('#_','jos',$query);
		return $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));	
	}
	
	function getTotal()
  {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);    
        }
        return $this->_total;
  }
	
	function getPagination()
  {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
  }
	
}