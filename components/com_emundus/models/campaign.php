<?php
/**
 * eMundus Campaign model
 * 
 * @package    	Joomla
 * @subpackage 	eMundus
 * @link       	http://www.emundus.fr
 * @copyright	Copyright (C) 2008 - 2013 Décision Publique. All rights reserved.
 * @license    	GNU/GPL
 * @author     	Decision Publique - Benjamin Rivalland
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

class EmundusModelCampaign extends JModel
{
	var $_user = null;
	var $_db = null;

	function __construct()
	{
		parent::__construct();
		global $option;
		
		$mainframe =& JFactory::getApplication();
		
		$this->_db =& JFactory::getDBO();
		$this->_user =& JFactory::getUser();
		
		// Get pagination request variables
		$filter_order			= $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'label', 'cmd' );
        $filter_order_Dir		= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
        $limit 					= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart 			= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $limitstart 			= ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
 		$this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}

	function getActiveCampaign()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		//echo str_replace('#_', 'jos',$query).'<br /><br />';
		return $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
	} 
	
	function _buildQuery(){
		$query = 'SELECT id, label, year, description, start_date, end_date FROM #__emundus_setup_campaigns WHERE published = 1 AND NOW()>=start_date AND NOW()<=end_date';
		return $query;
	}
	
	function _buildContentOrderBy()
	{ 
        global $option;

		$mainframe =& JFactory::getApplication();
 
        $orderby = '';
		$filter_order     = $this->getState('filter_order');
       	$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array ('id', 'label', 'year', 'start_date', 'end_date');
        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
        	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}

        return $orderby;
	}

	function getMyCampaign()
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$this->_user->id.' 
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getMySubmittedCampaign()
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$this->_user->id. 'AND ecc.submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getCampaignByApplicant($aid)
	{
		$query = 'SELECT esc.*, esp.menutype, esp.label as profile_label
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.applicant_id='.$aid.' 
					ORDER BY ecc.date_time DESC';
		$this->_db->setQuery( $query ); 
		return $this->_db->loadObjectList();
	}
	
	function getCampaignSubmittedByApplicant($aid)
	{
		$query = 'SELECT esc.* 
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = ecc.campaign_id
					WHERE esc.applicant_id='.$aid. 'AND submitted=1
					ORDER BY ecc.date_submitted DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	function setSelectedCampaign($cid, $aid)
	{
		$query = 'INSERT INTO #__emundus_campaign_candidature (campaign_id, applicant_id) VALUES ('.$cid.', '.$aid.')';
		$this->_db->setQuery( $query );
		try {
			$this->_db->Query();
		} catch (Exception $e) {
			// catch any database errors.
		}
	}

	function isOtherActiveCampaign($aid) {
		$query='SELECT count(id) as cpt 
				FROM #__emundus_setup_campaigns 
				WHERE id NOT IN (
								select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$aid.'
								)';
		$this->_db->setQuery($query); 
		$cpt = $this->_db->loadResult();
		return $cpt>0?true:false;
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
	
	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);    
		}
		return $this->_total;
	}
}
?>