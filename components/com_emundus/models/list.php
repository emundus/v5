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
jimport( 'joomla.application.application' );
jimport( 'joomla.html.parameter' );
 
class EmundusModelList extends JModel
{
	var $_total = null;
	var $_pagination = null;
	var $_applicants = array();
	var $subquery = array();

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{

		parent::__construct();
		global $option;

		$mainframe =& JFactory::getApplication();

		// Get current menu parameters
		$menu = &JSite::getMenu();
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
		$default_elements =& EmundusHelperFilters::getElementsName($this->elements_id);
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
		} //echo '<pre>'; print_r($this->details);  echo '</pre>'; 
	}
	
	function _buildContentOrderBy(){
		global $option;
//echo '<pre>'; print_r($this->_applicants);
		$mainframe =& JFactory::getApplication();

		$tmp = array();
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');

		$sort=($filter_order_Dir=='desc')?SORT_DESC:SORT_ASC;
 		$can_be_ordering = array();
			
		foreach($this->getApplicantColumns() as $appli_col)
			$can_be_ordering[] = $appli_col['name'];
			
		$select_list = $this->getSelectList();
		if(!empty($select_list))
			foreach($this->getSelectList() as $cols) $can_be_ordering[] = $cols['name'];
		
		if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
			$this->_applicants = $this->multi_array_sort($this->_applicants, $filter_order, $sort);
		} 
		$t 	= count($this->_applicants);
		$ls = $this->getState('limitstart');
		$l 	= $this->getState('limit');
		if ($l == 0) {$l = $t; $ls = 0;}
		else $l = ($ls + $l > $t) ? $t - $ls : $l;
	
		for ($i = $ls ; $i < ($ls + $l) ; $i++) {
			$tmp[] = $this->_applicants[$i];
		}
		return $tmp;
	}
	
	function multi_array_sort($multi_array=array(),$sort_key,$sort=SORT_ASC){  
        if(is_array($multi_array)){  
            foreach ($multi_array as $key=>$row_array){  
                if(is_array($row_array)){  
                    @$key_array[$key] = $row_array[$sort_key]; 
                }else{  
                    return -1;  
                } 
            } 
        }else{  
            return -1;  
        } 
		if(!empty($key_array))
	        array_multisort($key_array,$sort,$multi_array);
        return $multi_array;  
	}  
	
	function getCurrentCampaign(){
		$query = 'SELECT DISTINCT year as schoolyear 
				FROM #__emundus_setup_campaigns 
				WHERE published=1 
				ORDER BY schoolyear DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadResultArray();
	}
	
	function setSubQuery($tab, $elem) {
		$search 				= JRequest::getVar('elements'				, NULL, 'POST', 'array', 0);
		$search_values 			= JRequest::getVar('elements_values'		, NULL, 'POST', 'array', 0);
		$search_other 			= JRequest::getVar('elements_other'			, NULL, 'POST', 'array', 0);
		$search_values_other 	= JRequest::getVar('elements_values_other'	, NULL, 'POST', 'array', 0);
		
		$db =& JFactory::getDBO();
		
		$query = 'SELECT DISTINCT(#__emundus_users.user_id), '.$tab.'.'.$elem.' AS '.$tab.'__'.$elem;
		$query .= '	FROM #__emundus_users 
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id';
		
		// subquery JOINS
		$joined = array('jos_emundus_users');
		$this->setJoins($search, $query, $joined);
		$this->setJoins($search_other, $query, $joined);
		$this->setJoins($this->elements_default, $query, $joined);
		
		// subquery WHERE
		$query .= ' WHERE '.$this->details->{$tab.'__'.$elem}['group_by'].'=#__users.id';

		$query = EmundusHelperFilters::setWhere($search, $search_values, $query);
		$query = EmundusHelperFilters::setWhere($search_other, $search_values_other, $query);
		$query = EmundusHelperFilters::setWhere($this->elements_default, $this->elements_values, $query);

		//str_replace("#_", "jos", $query);
		$db->setQuery( $query );
		$obj = $db->loadObjectList();
		$list = array();
		$tmp = '';
		foreach ($obj as $unit) {
			if ($tmp != $unit->user_id)
				$list[$unit->user_id] = EmundusHelperList::getBoxValue($this->details->{$tab.'__'.$elem}, $unit->{$tab.'__'.$elem}, $elem);
			else
				$list[$unit->user_id] .= ','.EmundusHelperList::getBoxValue($this->details->{$tab.'__'.$elem}, $unit->{$tab.'__'.$elem}, $elem);
			$tmp = $unit->user_id;
		}
		return $list;
	}
	
	function setSelect($search) {
		$cols = array();
		if(!empty($search)) {
			asort($search);
			$i = -1;
			$old_table = '';
			foreach ($search as $c)
				if(!empty($c)){
					$tab = explode('.', $c);
					if ($this->details->{$tab[0].'__'.$tab[1]}['group_by'])
						$this->subquery[$tab[0].'__'.$tab[1]] = $this->setSubQuery($tab[0], $tab[1]);
					else $cols[] = $c.' AS '.$tab[0].'__'.$tab[1];
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
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab) > 1) {
					if($tab[0] != $old_table && !$this->isJoined($tab[0], $joined)){
						if ($tab[0] == 'jos_emundus_groups_eval' || $tab[0] == 'jos_emundus_comments' )
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.applicant_id=#__users.id ';
						elseif ($tab[0] == 'jos_emundus_evaluations' || $tab[0] == 'jos_emundus_final_grade' || $tab[0] == 'jos_emundus_academic_transcript'
								|| $tab[0] == 'jos_emundus_bank' || $tab[0] == 'jos_emundus_files_request' || $tab[0] == 'jos_emundus_mobility')
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.student_id=#__users.id ';
						else
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.user=#__users.id ';
						$joined[] = $tab[0];
					}
					$old_table = $tab[0];
				}
			}
		}
		return $tables_list;
	}
	
	function _buildSelect(&$tables_list, &$tables_list_other, &$tables_list_default){
		$current_user = & JFactory::getUser();
		$search					= $this->getState('elements');//$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_other			= $this->getState('elements_other');//$search_other = JRequest::getVar('elements_other', null, 'POST', 'array', 0);
		$schoolyears			= $this->getState('schoolyears');//$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'array', 0);
		$gid					= $this->getState('groups');//$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		$uid					= $this->getState('user');//$uid = JRequest::getVar('user', null, 'POST', 'none', 0);
		$miss_doc				= $this->getState('missing_doc');//$miss_doc = JRequest::getVar('missing_doc', null, 'POST', 'none',0);
		$validate_application	= $this->getState('validate');//$validate_application = JRequest::getVar('validate', null, 'POST', 'none',0);
		
		$cols = $this->setSelect($search);
		$cols_other = $this->setSelect($search_other);
		$cols_default = $this->setSelect($this->elements_default);
		
		$joined = array('jos_emundus_users', 'jos_users', 'jos_emundus_setup_profiles', 'jos_emundus_final_grade');
		
		$query = 'SELECT DISTINCT(#__emundus_users.user_id), #__emundus_users.user_id as user, #__users.name, #__users.email, #__emundus_setup_profiles.id as profile';
		if(!empty($cols)) $query .= ', '.$cols;
		if(!empty($cols_other)) $query .= ', '.$cols_other;
		if(!empty($cols_default)) $query .= ', '.$cols_default;
		$query .= '	FROM #__emundus_users 
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
			
		$query .= ' WHERE #__users.block = 0 ';
		if(empty($schoolyears)) $query .= ' AND #__emundus_users.schoolyear IN ("'.implode('","',$this->getCurrentCampaign()).'")';
		
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
		//$eMConfig =& JComponentHelper::getParams('com_emundus');
		
		$search					= $this->getState('elements');
		$search_values			= $this->getState('elements_values');
		$search_other			= $this->getState('elements_other');
		$search_values_other	= $this->getState('elements_values_other');
		$finalgrade				= $this->getState('finalgrade');
		$quick_search			= $this->getState('s');
		$schoolyears			= $this->getState('schoolyears');
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
			else { $and = true; $query .='WHERE '; }
			$query.= '#__emundus_final_grade.Final_grade like "%'.$finalgrade.'%"';
		}
		
		$query = EmundusHelperFilters::setWhere($search, $search_values, $query);
		$query = EmundusHelperFilters::setWhere($search_other, $search_values_other, $query);
		$query = EmundusHelperFilters::setWhere($this->elements_default, $this->elements_values, $query);
		
		if(isset($schoolyears) &&  !empty($schoolyears)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= '#__emundus_users.schoolyear IN ("'.implode('","',$schoolyears).'") ';
		}
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
		return $query;
	}
	
	/**
	* @description : Generate values for array of data for all applicants
	* @param	array	$search	filters elements
	* @param	array	$eval_list	reference of result list
	* @param	array	$head_val	header name
	* @param	object	$applicant	array of applicants indexed by database column
	**/
	function setEvalList($search, &$eval_list, $head_val, $applicant) {
	//print_r($applicant); die();
		if(!empty($search)){
			foreach($search as $c){
				if(!empty($c)){
					$name = explode('.',$c);
					if(!in_array($name[0].'__'.$name[1],$head_val)){
						$print_val = '';
						if ($this->details->{$name[0].'__'.$name[1]}['group_by']
							&& array_key_exists($name[0].'__'.$name[1], $this->subquery)
							&& array_key_exists($applicant->user_id, $this->subquery[$name[0].'__'.$name[1]])){
							$$eval_list[$name[0].'__'.$name[1]] = EmundusHelperList::createHtmlList(explode(",", $this->subquery[$name[0].'__'.$name[1]][$applicant->user_id]));
						} elseif (!$this->details->{$name[0].'__'.$name[1]}['group_by']){
							$eval_list[$name[0].'__'.$name[1]] = EmundusHelperList::getBoxValue($this->details->{$name[0].'__'.$name[1]}, $applicant->{$name[0].'__'.$name[1]}, $name[1]);
						}
						$eval_list[$name[0].'__'.$name[1]] = $applicant->{$name[0].'__'.$name[1]};
					}
				}
			}
		}
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

		//echo str_replace("#_", "jos", $query);
		$this->_db->setQuery($query);
		$applicants = $this->_db->loadObjectlist();

		$head_values = $this->getApplicantColumns();

		if(!empty($applicants)){
			foreach($applicants as $applicant){
				$eval_list=array();
				foreach($head_values as $head){
					$head_val[] = $head['name'];
					$eval_list[$head['name']] = $applicant->$head['name'];
					$eval_list['user'] = $applicant->user_id;
				}
				// add an advance filter columns only if not already exist 
				$this->setEvalList($search, $eval_list, $head_val, $applicant);
				$this->setEvalList($search_other, $eval_list, $head_val, $applicant);
				$this->setEvalList($this->elements_default, $eval_list, $head_val, $applicant);

				$eval_lists[]=$eval_list;
			}
			if(!empty($eval_lists))
				$this->_applicants=$eval_lists;
		}else
			$this->_applicants=$applicants;
			
		//echo '<pre>'; print_r($this->_applicants);
	}
	
	function getUsers(){	
		// Lets load the data if it doesn't already exist
		$this->_buildQuery();
		return $this->_buildContentOrderBy();
	} 
	
	function getSelectList(){
		$lists = '';
		
		if(!empty($this->col)){
			foreach($this->col as $c){
				if(!empty($c)){
					$tab = explode('.', $c);
					$names = @$tab[1];
					$tables = $tab[0];
	
					$query = 'SELECT distinct(fe.name), fe.label, ft.db_table_name as table_name
						FROM #__fabrik_elements fe
						LEFT JOIN #__fabrik_formgroup ff ON ff.group_id = fe.group_id
						LEFT JOIN #__fabrik_lists ft ON ft.form_id = ff.form_id
						WHERE fe.name = "'.$names.'"
						AND ft.db_table_name = "'.$tables.'"';
					$this->_db->setQuery( $query );
					$cols[] = $this->_db->loadObject();
				}
			}
			if(!empty($cols)){
				foreach($cols as $c){
					if(!empty($c)){
						$list = array();
						$list['name'] = @$c->table_name.'__'.$c->name;
						$list['label'] = @ucfirst($c->label);
						$lists[]=$list;
					}
				}
			}
		}
		return $lists;
	}
	
	// get applicant columns
	function getApplicantColumns(){
		$cols = array();
		$cols[] = array('name' =>'user_id', 'label'=>'User id');
		$cols[] = array('name' =>'user', 'label'=>'User id');
		$cols[] = array('name' =>'name', 'label'=>'Name');
		$cols[] = array('name' =>'email', 'label'=>'Email'); 
		$cols[] = array('name' =>'profile', 'label'=>'Profile'); 

		return $cols;
	}
	
	function getPagination(){
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	function getTotal(){
        // Load the content if it doesn't already exist
      	if (empty($this->_total)) $this->_total = count($this->_applicants);
  		return $this->_total;
	}

}
?>