<?php
/**
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
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
 
class EmundusModelIncomplete extends JModel
{
	var $_total = null;
	var $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		global $option;
		
		$mainframe = JFactory::getApplication();
		
		// Get current menu parameters
		$current_user = JFactory::getUser();
		$menu=JSite::getMenu();
		$current_menu  = $menu->getActive();

		/* 
		** @TODO : gestion du cas Itemid absent à prendre en charge dans la vue
		*/
		if (empty($current_menu))
			return false;
		$menu_params = $menu->getParams($current_menu->id);

		$filts_names = explode(',', $menu_params->get('em_filters_names'));
		$filts_values = explode(',', $menu_params->get('em_filters_values'));
		$filts_details = array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'campaign'			=> NULL,
							    'programme'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL);
		$i = 0;
		foreach ($filts_names as $filt_name)
			if (array_key_exists($i, $filts_values))
				$filts_details[$filt_name] = $filts_values[$i++];
			else
				$filts_details[$filt_name] = '';
		unset($filts_names); unset($filts_values);
		
		//Set session variables
		$filter_order			= $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'overall', 'cmd' );
        $filter_order_Dir		= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
		$schoolyears			= $mainframe->getUserStateFromRequest( $option.'schoolyears', 'schoolyears', $this->getCurrentCampaign() );
		$campaigns				= $mainframe->getUserStateFromRequest( $option.'campaigns', 'campaigns', $this->getCurrentCampaignsID() );
		$programmes 			= $mainframe->getUserStateFromRequest($option . 'programmes', 'programmes' );
		$elements				= $mainframe->getUserStateFromRequest( $option.'elements', 'elements' );
		$elements_values		= $mainframe->getUserStateFromRequest( $option.'elements_values', 'elements_values' );
		$elements_other			= $mainframe->getUserStateFromRequest( $option.'elements_other', 'elements_other' );
		$elements_values_other	= $mainframe->getUserStateFromRequest( $option.'elements_values_other', 'elements_values_other' );
		$finalgrade				= $mainframe->getUserStateFromRequest( $option.'finalgrade', 'finalgrade', $filts_details['finalgrade'] );
		$s						= $mainframe->getUserStateFromRequest( $option.'s', 's' );
		$groups					= $mainframe->getUserStateFromRequest( $option.'groups', 'groups', $filts_details['evaluator_group'] );
		$user					= $mainframe->getUserStateFromRequest( $option.'user', 'user', $filts_details['evaluator'] );
		$profile				= $mainframe->getUserStateFromRequest( $option.'profile', 'profile', $filts_details['profile'] );
		$missing_doc			= $mainframe->getUserStateFromRequest( $option.'missing_doc', 'missing_doc', $filts_details['missing_doc'] );
		$complete				= $mainframe->getUserStateFromRequest( $option.'complete', 'complete', $filts_details['complete'] );
		$validate				= $mainframe->getUserStateFromRequest( $option.'validate', 'validate', $filts_details['validate'] );
		 // Get pagination request variables
        $limit 					= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart 			= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $limitstart 			= ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
 		$this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
		$this->setState('schoolyears', $schoolyears);
		$this->setState('campaigns', $campaigns);
		$this->setState('programmes', $programmes);
		$this->setState('elements', $elements);
		$this->setState('elements_values', $elements_values);
		$this->setState('elements_other', $elements_other);
		$this->setState('elements_values_other', $elements_values_other);
		$this->setState('finalgrade', $finalgrade);
		$this->setState('s', $s);
		$this->setState('groups', $groups);
		$this->setState('user', $user);
		$this->setState('profile', $profile);
		$this->setState('missing_doc', $missing_doc);
		$this->setState('complete', $complete);
		$this->setState('validate', $validate);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
		
		$col_elt	= $this->getState('elements');
		$col_other	= $this->getState('elements_other');
		
		$this->elements_id = $menu_params->get('em_elements_id');
		$this->elements_values = explode(',', $menu_params->get('em_elements_values'));

		$this->elements_default = array();
		$default_elements = EmundusHelperFilters::getElementsName($this->elements_id);	
		if (!empty($default_elements))
			foreach ($default_elements as $def_elmt) {
				$this->elements_default[] = $def_elmt->tab_name.'.'.$def_elmt->element_name;
			}
		if (count($col_elt) == 0) $col_elt = array();
		if (count($col_other) == 0) $col_other = array();
		if (count($this->elements_default) == 0) $this->elements_default = array();

		$this->col = array_merge($col_elt, $col_other, $this->elements_default);

		$elements_names = '"'.implode('", "', $this->col).'"'; 
		$result = EmundusHelperList::getElementsDetails($elements_names); 
		$result = EmundusHelperFilters::insertValuesInQueryResult($result, array("sub_values", "sub_labels")); 
		$this->details = new stdClass();
		foreach ($result as $res) {
			$this->details->{$res->tab_name.'__'.$res->element_name} = array('element_id'	=> $res->element_id,
																			'plugin'		=> $res->element_plugin,
																			'attribs'		=> $res->params,
																			'sub_values'	=> $res->sub_values,
																			'sub_labels'	=> $res->sub_labels,
																			'group_by'		=> $res->tab_group_by);
		}
	}
	
	
	function _buildContentOrderBy()
	{
        global $option;

		$mainframe = JFactory::getApplication();
 
        $orderby = '';
		$filter_order     = $this->getState('filter_order');
       	$filter_order_Dir = $this->getState('filter_order_Dir');
				
		$can_be_ordering = array ('user', 'id', 'lastname', 'nationality', 'registerDate','profile', 'date_time', 'year', 'label', 'jos_emundus_setup_campaigns.year');
        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
        	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}
 
        return $orderby;
	}

	function getCampaign()
	{
	/*	$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];*/
		return EmundusHelperFilters::getCampaign();
	}
	
	function getCurrentCampaign(){
		return EmundusHelperFilters::getCurrentCampaign();
	}

	function getCurrentCampaignsID(){
		return EmundusHelperFilters::getCurrentCampaignsID();
	}

	function getProfileAcces($user)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id='.$user;
		$db->setQuery( $query );
		$profiles = $db->loadResultArray();
		
		return $profiles;
	}

	
	function setSelect($search) {
		$cols = array();
		if(!empty($search)) {
			asort($search);
			$i = 0;
			$old_table = '';
			foreach ($search as $c){
				if(!empty($c)){
					$tab = explode('.', $c);
					if($tab[0]=='jos_emundus_training'){
						$cols[] = ' search_'.$tab[0].'.label as '.$tab[1].' ';
					}else{
						if ($this->details->{$tab[0].'__'.$tab[1]}['group_by'])
							$this->subquery[$tab[0].'__'.$tab[1]] = $this->setSubQuery($tab[0], $tab[1]);
						else $cols[] = $c.' AS '.$tab[0].'__'.$tab[1];
					}
				}
				$i++;
			}
			if(count($cols > 0) && !empty($cols))
				$cols = implode(', ',$cols);
		}
		return $cols;
	}
	
	function isJoined($tab, $joined) {
		foreach ($joined as $j)
			if ($tab == $j) return true;
		return false;
	}
	
	function setJoins($search, &$query, &$joined) {
		$tables_list = array();
		if(!empty($search)) {
			$old_table = '';
			$i=0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab) > 1) {
					if($tab[0] != $old_table && !$this->isJoined($tab[0], $joined)){
						if ($tab[0] == 'jos_emundus_groups_eval' || $tab[0] == 'jos_emundus_comments' )
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.applicant_id=#__users.id ';
						elseif ($tab[0] == 'jos_emundus_evaluations' || $tab[0] == 'jos_emundus_final_grade' || $tab[0] == 'jos_emundus_academic_transcript'
								|| $tab[0] == 'jos_emundus_bank' || $tab[0] == 'jos_emundus_files_request' || $tab[0] == 'jos_emundus_mobility')
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.student_id=#__users.id ';
						elseif($tab[0]=="jos_emundus_training")
							$query .= ' LEFT JOIN #__emundus_setup_teaching_unity AS search_'.$tab[0].' ON search_'.$tab[0].'.code=#__emundus_setup_campaigns.training ';
						else
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.user=#__users.id ';
						$joined[] = $tab[0];
					}
					$old_table = $tab[0];
				}
				$i++;
			}
		}
		return $tables_list;
	}
	
	function _buildSelect(&$tables_list, &$tables_list_other, &$tables_list_default){
		$current_user 			= JFactory::getUser();
		$search					= $this->getState('elements');
		$search_other			= $this->getState('elements_other');
		$schoolyears			= $this->getState('schoolyears');
		$programmes 			= $this->getState('programmes');
		$gid					= $this->getState('groups');
		$uid					= $this->getState('user');
		$miss_doc				= $this->getState('missing_doc');
		$validate_application	= $this->getState('validate');

		$cols = $this->setSelect($search);
		$cols_other = $this->setSelect($search_other);
		$cols_default = $this->setSelect($this->elements_default);
		
		$joined = array('jos_emundus_users', 'jos_users', 'jos_emundus_setup_profiles', 'jos_emundus_final_grade');
		
		$query = 'SELECT #__emundus_users.user_id, #__emundus_users.user_id as user, #__emundus_users.lastname, #__emundus_users.firstname, #__emundus_users.schoolyear as schoolyear, #__users.name, #__users.registerDate, #__users.email, #__emundus_setup_profiles.id as profile, #__emundus_campaign_candidature.date_time, #__emundus_campaign_candidature.campaign_id, #__emundus_setup_campaigns.year, #__emundus_setup_campaigns.label';
		if(!empty($cols)) $query .= ', '.$cols;
		if(!empty($cols_other)) $query .= ', '.$cols_other;
		if(!empty($cols_default)) $query .= ', '.$cols_default;
		$query .= '	FROM #__emundus_campaign_candidature
					LEFT JOIN #__emundus_users ON #__emundus_users.user_id=#__emundus_campaign_candidature.applicant_id
					LEFT JOIN #__emundus_setup_campaigns ON #__emundus_setup_campaigns.id=#__emundus_campaign_candidature.campaign_id
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id
					LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
					LEFT JOIN #__emundus_final_grade ON #__emundus_final_grade.student_id=#__emundus_users.user_id';
		
		$this->setJoins($search, $query, $joined);
		$this->setJoins($search_other, $query, $joined);
		$this->setJoins($this->elements_default, $query, $joined);	

		if(((isset($gid) && !empty($gid)) || (isset($uid) && !empty($uid))) && !$this->isJoined('jos_emundus_groups_eval', $joined)) 
			$query .= ' LEFT JOIN #__emundus_groups_eval ON #__emundus_groups_eval.applicant_id=#__users.id ';
			
		if(!empty($miss_doc) && !$this->isJoined('jos_emundus_uploads', $joined))
			$query .= ' LEFT JOIN #__emundus_uploads ON #__emundus_uploads.user_id=#__users.id';
		
		if(!empty($validate_application) && !$this->isJoined('jos_emundus_declaration', $joined))
			$query .= ' LEFT JOIN #__emundus_declaration ON #__emundus_declaration.user=#__users.id';
			
		$query .= ' WHERE #__users.block = 0 AND (#__emundus_campaign_candidature.submitted = 0 OR #__emundus_campaign_candidature.submitted IS NULL)';
		if(empty($schoolyears)) 
			$query .= ' AND #__emundus_campaign_candidature.year IN ("'.implode('","',$this->getCurrentCampaign()).'")';

		if (!empty($programmes) && isset($programmes) && $programmes[0] != "%") 
            $query .= ' AND #__emundus_setup_campaigns.training IN ("' . implode('","', $programmes) . '")';
		
		if (!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)){
			$pa = EmundusHelperAccess::getProfileAccess($current_user->id);
			$query .= ' AND (#__emundus_users.user_id IN (
								SELECT user_id 
								FROM #__emundus_users_profiles 
								WHERE profile_id in ('.implode(',',$pa).')) OR #__emundus_users.user_id IN (
									SELECT user_id 
									FROM #__emundus_users 
									WHERE profile in ('.implode(',',$pa).'))
							) ';
		}
		return $query;
	}
	
	function _buildFilters($tables_list, $tables_list_other, $tables_list_default){
		//$eMConfig = JComponentHelper::getParams('com_emundus');
		$search					= $this->getState('elements');
		$search_values			= $this->getState('elements_values');
		$search_other			= $this->getState('elements_other');
		$search_values_other	= $this->getState('elements_values_other');
		$finalgrade				= $this->getState('finalgrade');
		$quick_search			= $this->getState('s');
		$schoolyears			= $this->getState('schoolyears');
		$campaigns				= $this->getState('campaigns');
		$programmes		 		= $this->getState('programmes');
		$gid					= $this->getState('groups');
		$uid					= $this->getState('user');
		$profile				= $this->getState('profile');
		$miss_doc				= $this->getState('missing_doc');
		$complete				= $this->getState('complete');
		$validate_application	= $this->getState('validate');
		
		$query = '';
		$and = true;
		
		if(isset($finalgrade) && !empty($finalgrade)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .=' WHERE '; }
			$query.= '#__emundus_final_grade.Final_grade like "%'.$finalgrade.'%"';
		}
		
		$query = EmundusHelperFilters::setWhere($search, $search_values, $query);
		$query = EmundusHelperFilters::setWhere($search_other, $search_values_other, $query);
		$query = EmundusHelperFilters::setWhere($this->elements_default, $this->elements_values, $query);
		
		if($schoolyears[0] == "%")
			$query .= ' ';
		elseif(!empty($schoolyears))
			$query .= ' AND #__emundus_setup_campaigns.year IN ("'.implode('","', $schoolyears).'") ';
		else
			$query .= ' AND #__emundus_setup_campaigns.year IN ("'.implode('","', $this->getCurrentCampaign()).'")';


		if(@$campaigns[0] == "%" || empty($campaigns[0]))
			$query .= ' ';
		elseif(!empty($campaigns)) 
			$query .= ' AND #__emundus_setup_campaigns.id IN ("'.implode('","', $campaigns).'") ';
		else	
			$query .= ' AND #__emundus_setup_campaigns.id IN ("'.implode('","', $this->getCurrentCampaignsID()).'")';

		if(isset($quick_search) && !empty($quick_search)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			if (is_numeric ($quick_search)) 
				$query.= '#__users.id='.$quick_search.' ';
			else
				$query.= '(#__emundus_users.lastname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
						OR #__emundus_users.firstname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
						OR #__users.email LIKE "%'.mysql_real_escape_string($quick_search).'%" 
						OR #__users.username LIKE "%'.mysql_real_escape_string($quick_search).'%" )';
		}	
		
		if(isset($gid) && !empty($gid)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= ' (#__emundus_groups_eval.group_id='.mysql_real_escape_string($gid).' OR #__emundus_groups_eval.user_id IN (select user_id FROM #__emundus_groups WHERE group_id='.mysql_real_escape_string($gid).'))';
		}
		if(isset($uid) && !empty($uid)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= ' (#__emundus_groups_eval.user_id='.mysql_real_escape_string($uid).' OR #__emundus_groups_eval.group_id IN (select e.group_id FROM #__emundus_groups e WHERE e.user_id='.mysql_real_escape_string($uid).'))';
		}
		
		if(isset($profile) && !empty($profile)){
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= ' (#__emundus_setup_profiles.id = '.$profile.' OR #__emundus_final_grade.result_for = '.$profile.' OR #__emundus_users.user_id IN (select user_id from #__emundus_users_profiles where profile_id = '.$profile.'))';
		}
		
		if(isset($miss_doc) &&  !empty($miss_doc)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= $miss_doc.' NOT IN (SELECT attachment_id FROM #__emundus_uploads eup WHERE #__emundus_uploads.user_id = #__users.id)';
		}
		
		if(isset($complete) &&  !empty($complete)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			if($complete == 1)
				$query.= ' #__users.id IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id)';
			else 
				$query.= ' #__users.id NOT IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id)';
		}
		
		if(isset($validate_application) &&  !empty($validate_application)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			if($validate_application == 1)
				$query.= ' #__emundus_declaration.validated = 1';
			else 
				$query.= ' #__emundus_declaration.validated = 0';
		}
		$query.= ' GROUP BY #__emundus_campaign_candidature.applicant_id';
		
		return $query;
	}
	
	function _buildQuery(){
		$search = $this->getState('elements');
		$search_other = $this->getState('elements_other');
		
		$tables_list = array();
		$tables_list_other = array();
		$tables_list_default = array();

		$query = $this->_buildSelect($tables_list, $tables_list_other, $tables_list_default);
		
		/** add filters to the query **/
		$query .= $this->_buildFilters($tables_list, $tables_list_other, $tables_list_default);

		return $query;
	}
	
	function getUsers()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
// echo str_replace('#_', 'jos',$query).'<br /><br />';
		return $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
	} 


	function getProfiles()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
					FROM #__emundus_setup_profiles esp 
					INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
					ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getProfilesByIDs($ids)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.id IN ('.implode(',',$ids).')
		ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getAuthorProfiles()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.acl_aro_groups=19';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getApplicantsProfiles()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label FROM #__emundus_setup_profiles esp WHERE esp.published=1 ORDER BY esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getApplicantsProfilesByID($user)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT eup.profile_id FROM #__emundus_users_profiles eup WHERE eup.user_id='.$user;
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getApplicantsByProfile($profile)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT eup.user_id FROM #__emundus_users_profiles eup WHERE eup.profile_id='.$profile;
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getAuthorUsers()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT u.id, u.gid, u.name FROM #__users u WHERE u.gid=19';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getMobility()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esm.id, esm.label, esm.value FROM #__emundus_setup_mobility esm ORDER BY ordering';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getElements(){
		$db = JFactory::getDBO();
		$query = 'SELECT element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin,
				 groupe.label AS group_label, INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated,
				 tab.db_table_name AS table_name, tab.label AS table_label
			FROM jos_fabrik_elements element	
				 INNER JOIN jos_fabrik_groups AS groupe ON element.group_id = groupe.id
				 INNER JOIN jos_fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				 INNER JOIN jos_fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				 INNER JOIN jos_menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
				 INNER JOIN jos_emundus_setup_profiles AS profile ON profile.menutype = menu.menutype
			WHERE tab.published = 1 AND profile.id =9 AND tab.created_by_alias = "form" AND element.published=1 AND element.hidden=0 AND element.label!=" " AND element.label!="" 
			ORDER BY menu.ordering, formgroup.ordering, element.ordering';
		$db->setQuery( $query );
		//die(print_r($db->loadObjectList('id')));
		return $db->loadObjectList('id');
	}
	
	function getTotal(){
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);    
		}
		return $this->_total;
	}
	
	function getPagination(){
		$this->getUsers();
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	function getSchoolyears(){
		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT(schoolyear) as schoolyear
		FROM #__emundus_users 
		WHERE schoolyear is not null AND schoolyear != "" 
		ORDER BY schoolyear';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
}
?>