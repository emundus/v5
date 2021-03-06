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
jimport( 'joomla.application.application' );
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
 
class EmundusModelEvaluation extends JModel
{
	var $_db = null;
	var $_user = null;
	var $_total = null;
	var $_pagination = null;
	var $_applicants = array();
	var $_request = array();
	var $_eval_elements = null;
	var $_applicantColumns;
	var $_actions;
	
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct(){
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();
		
		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getUser();

		$menu = JSite::getMenu();
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
		$schoolyears			= $mainframe->getUserStateFromRequest( $option."schoolyears", "schoolyears", "%" );
		$campaigns				= $mainframe->getUserStateFromRequest( $option."campaigns", "campaigns", "%" );
		$programmes 			= $mainframe->getUserStateFromRequest($option . 'programmes', 'programmes' );
		$elements				= $mainframe->getUserStateFromRequest( $option.'elements', 'elements' );
		$elements_values		= $mainframe->getUserStateFromRequest( $option.'elements_values', 'elements_values' );
		$elements_other			= $mainframe->getUserStateFromRequest( $option.'elements_other', 'elements_other' );
		$elements_values_other	= $mainframe->getUserStateFromRequest( $option.'elements_values_other', 'elements_values_other' );
		$finalgrade				= $mainframe->getUserStateFromRequest( $option.'finalgrade', 'finalgrade', @$filts_details['finalgrade'] );
		$s						= $mainframe->getUserStateFromRequest( $option.'s', 's' );
		$groups					= $mainframe->getUserStateFromRequest( $option.'groups', 'groups', @$filts_details['evaluator_group'] );
		$evaluator_group		= $mainframe->getUserStateFromRequest( $option.'evaluator_group', 'evaluator_group', @$filts_details['evaluator_group'] );
		$user					= $mainframe->getUserStateFromRequest( $option.'user', 'user', @$filts_details['evaluator'] );
		$profile				= $mainframe->getUserStateFromRequest( $option.'profile', 'profile', @$filts_details['profile'] );
		$missing_doc			= $mainframe->getUserStateFromRequest( $option.'missing_doc', 'missing_doc', @$filts_details['missing_doc'] );
		$complete				= $mainframe->getUserStateFromRequest( $option.'complete', 'complete', @$filts_details['complete'] );
		$validate				= $mainframe->getUserStateFromRequest( $option.'validate', 'validate', @$filts_details['validate'] );
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
		$this->setState('evaluator_group', $evaluator_group);
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
		$this->joined = array();

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
		//echo '<pre>'; print_r($this->details);  echo '</pre>'; 
	}
	
	function _buildContentOrderBy(){
		global $option;

		$mainframe = JFactory::getApplication();

		$tmp = array();
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');

		$sort=($filter_order_Dir=='desc')?SORT_DESC:SORT_ASC;
		$can_be_ordering = array();
		foreach($this->getEvalColumns() as $eval_col) $can_be_ordering[] = $eval_col['name'];
		foreach($this->_applicantColumns as $info_col) $can_be_ordering[] = $info_col['name'];
		//foreach($this->getRankingColumns() as $rank_col) $can_be_ordering[] = $rank_col['name'];
		$can_be_ordering[] = 'evaluator';
		$can_be_ordering[] = 'assoc_evaluators';

		$select_list = $this->getSelectList();
		if(!empty($select_list))
			foreach($this->getSelectList() as $cols) $can_be_ordering[] = $cols['name'];
		
		$this->_applicants = EmundusHelperList::multi_array_sort($this->_applicants, 'overall', SORT_DESC);
		$rank=1;
		for($i=0 ; $i<count($this->_applicants) ; $i++) {
			$this->_applicants[$i]['ranking']=$rank;
			$rank++;
		}
		
		if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
			$this->_applicants = EmundusHelperList::multi_array_sort($this->_applicants, $filter_order, $sort);
		} 
		$t = count($this->_applicants);
		$ls = $this->getState('limitstart');
		$l = $this->getState('limit');
		if ($l==0) {$l=$t; $ls=0;}
		else $l = ($ls+$l>$t)?$t-$ls:$l;
	
		for ($i=$ls ; $i<($ls+$l) ; $i++) {
			$tmp[] = $this->_applicants[$i];
		}
		return $tmp;
	} 
	
	function getProfileAcces($user){
		$query = 'SELECT esg.profile_id 
				FROM #__emundus_setup_groups as esg
				LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
				WHERE esg.published=1 
				AND eg.user_id='.$user;
		$this->_db->setQuery( $query );
		return $this->_db->loadResultArray();
	}
	
	function union($myGroup,$myAffect){
		$session = JFactory::getSession();
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$s_elements = $session->get('s_elements');
		
		if (count($search)==0) $search = $s_elements;
		$head_values = $this->_applicantColumns;
		foreach($head_values as $head) $head_val[] = $head['name'];
		
		if(!empty($myGroup)) {
			foreach($myGroup as $mg){
				$obj = new stdClass();
				$obj->user_id = $mg->user_id;
				$obj->firstname = $mg->firstname;
				$obj->lastname = $mg->lastname;
				$obj->profile = $mg->profile;
				$obj->user = $mg->user;
				if(!empty($search)){
					foreach($search as $c){
						$name = explode('.',$c);
						if(!in_array($name[1],$head_val)){
							$obj->$name[1] = $mg->$name[1];
						}
					}
				}
				$applicants[]=$obj;
			}
		}
		foreach($myAffect as $ma){
			$double = false;
			if(!empty($myGroup)) {
				foreach($myGroup as $mg){
					if($ma->user == $mg->user && $ma->user_id == $mg->user_id) $double = true;
				}
			}
			if($double) continue;
			else{	
				$obj = new stdClass();
				$obj->user_id = $ma->user_id;
				$obj->firstname = $ma->firstname;
				$obj->lastname = $ma->lastname;
				$obj->profile = $ma->profile;
				$obj->user = $ma->user;
				if(!empty($search)){
					foreach($search as $c){
						$name = explode('.',$c);
						if(!in_array($name[1],$head_val)){
							$obj->$name[1] = $mg->$name[1];
						}
					}
				}
				$applicants[]=$obj;
			}
		}
		return $applicants;
	}

	function setSubQuery($tab, $elem) {
		$search 				= JRequest::getVar('elements'				, NULL, 'POST', 'array', 0);
		$search_values 			= JRequest::getVar('elements_values'		, NULL, 'POST', 'array', 0);
		$search_other 			= JRequest::getVar('elements_other'			, NULL, 'POST', 'array', 0);
		$search_values_other 	= JRequest::getVar('elements_values_other'	, NULL, 'POST', 'array', 0);
		
		$db = JFactory::getDBO();
		
		$query = 'SELECT DISTINCT(#__emundus_users.user_id), '.$tab.'.'.$elem.' AS '.$tab.'__'.$elem;
		$query .= '	FROM #__emundus_users 
					LEFT JOIN #__users ON #__users.id=#__emundus_users.user_id';
		
		// subquery JOINS
		$this->joined[] = 'jos_emundus_users';
		$this->setJoins($search, $query, $this->joined);
		$this->setJoins($search_other, $query, $this->joined);
		$this->setJoins($this->elements_default, $query, $this->joined);
		
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
			//$i = -1;
			//$old_table = '';
			$cols = array();
			foreach ($search as $c) {
				if(!empty($c)){
					$tab = explode('.', $c);
				  if(!in_array($tab[0].'.'.$tab[1], $cols)) {
					if($tab[0]=="jos_emundus_training"){
						if (count($tab)>=1) {
							$cols[] = 'search_'.$tab[0].'.label as '.$tab[1].' ';
						}
					}else{
						if (count($tab)>=1) {
							$cols[] = $tab[0].'.'.$tab[1];
						}
					}
				  }
				}
			}
			if(count($cols>0) && !empty($cols))
				$cols = implode(', ',$cols);
		}
		return $cols;
	}
	
	function isJoined($tab, $joined) {
		foreach ($joined as $j)
			if ($tab == $j) return true;
		return false;
	}
	
	function setJoins($search, $query, $joined) {
		if(!empty($search)) {
			$old_table = '';
			foreach ($search as $s) { 
				$tab = explode('.', $s);
				if (count($tab) > 1) {
					if($tab[0] != $old_table && !$this->isJoined($tab[0], $joined)){
						if ($tab[0] == 'jos_emundus_groups_eval' || $tab[0] == 'jos_emundus_comments' )
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.applicant_id=u.id ';
						elseif ($tab[0] == 'jos_emundus_evaluations' || $tab[0] == 'jos_emundus_final_grade' || $tab[0] == 'jos_emundus_academic_transcript'
								|| $tab[0] == 'jos_emundus_bank' || $tab[0] == 'jos_emundus_files_request' || $tab[0] == 'jos_emundus_mobility')
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.student_id=u.id ';
						elseif ($tab[0]=='jos_emundus_training'){
							$query .= ' LEFT JOIN #__emundus_setup_teaching_unity AS search_'.$tab[0].' ON search_'.$tab[0].'.code=#__emundus_setup_campaigns.training';
						} else
							$query .= ' LEFT JOIN '.$tab[0].' ON '.$tab[0].'.user=u.id ';
						$this->joined[] = $tab[0];
					}
					$old_table = $tab[0];
				}
			}
		}

		return $query;
	}
	
	function _buildSelect(){
		$eMConfig 				= JComponentHelper::getParams('com_emundus');
		$current_user 			= JFactory::getUser();
		$search					= $this->getState('elements');
		$s_elements				= $this->getState('s_elements');
		$search_other			= $this->getState('elements_other');
		$schoolyears			= $this->getState('schoolyears');
		$gid					= $this->getState('groups');
		if(empty($gid)) 
			$gid 				= $this->getState('evaluator_group'); 
		$uid					= $this->getState('user');
		$miss_doc				= $this->getState('missing_doc');
		$validate_application	= $this->getState('validate');
		$evaluation_groups_id 	= $eMConfig->get('evaluation_groups_id', '41');
		$this->_eval_elements 	= $this->getElementsByGroups($evaluation_groups_id);

		$db = JFactory::getDBO();
		
		foreach ($this->_eval_elements as $eval) {
			$eval_columns[] = 'ee.'.$eval->name;
		}

		if (count($search)==0) $search = $s_elements;

		$cols = $this->setSelect($search);
		$cols_other = $this->setSelect($search_other);
		$cols_default = $this->setSelect($this->elements_default);
	
		$this->joined[] = '#__emundus_users';
		$this->joined[] = '#__users';
		$this->joined[] = '#__emundus_evaluations';
		$this->joined[] = '#__emundus_setup_profiles';
		$this->joined[] = '#__emundus_personal_detail';
		$this->joined[] = '#__emundus_declaration';
		$this->joined[] = '#__emundus_final_grade';
			
		$query = 'SELECT ee.student_id, eu.user_id, eu.firstname, eu.lastname, u.email, esp.id as profile, #__emundus_setup_campaigns.label as campaign, #__emundus_setup_campaigns.id as campaign_id, ee.user, ee.id as evaluation_id, efg.date_result_sent, efg.final_grade  ';	

		if(!empty($cols)) $query .= ', '.$cols;
		if(!empty($cols_other)) $query .= ', '.$cols_other;
		if(!empty($cols_default)) $query .= ', '.$cols_default;
		if(!empty($eval_columns)) $query .= ', '.implode(",", $eval_columns);

		$query .= '	FROM #__emundus_campaign_candidature ecc
			LEFT JOIN  #__emundus_users eu ON eu.user_id = ecc.applicant_id 
			LEFT JOIN #__emundus_setup_campaigns ON #__emundus_setup_campaigns.id=ecc.campaign_id
			LEFT JOIN #__users u ON u.id = ecc.applicant_id
			LEFT JOIN #__emundus_evaluations ee ON (ee.student_id = ecc.applicant_id AND ee.campaign_id=ecc.campaign_id)
			LEFT JOIN #__emundus_setup_profiles esp ON esp.id = eu.profile
			LEFT JOIN #__emundus_personal_detail epd ON epd.user = ecc.applicant_id
			LEFT JOIN #__emundus_declaration ed ON ed.user = ecc.applicant_id
			LEFT JOIN #__emundus_final_grade AS efg ON (efg.student_id=ecc.applicant_id AND efg.campaign_id=ecc.campaign_id)';		
		if(!empty($miss_doc))
			$query .= ' LEFT JOIN #__emundus_uploads AS eup ON eup.user_id=u.id';

		$query = $this->setJoins($search, $query, $this->joined);  
		$query = $this->setJoins($search_other, $query, $this->joined);   
		$query = $this->setJoins($this->elements_default, $query, $this->joined);  

 //echo "<hr>".str_replace('#_','jos',$query);
		return $query;
	}
	
	function _buildFilters(){
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$evaluators_can_see_other_eval = $eMConfig->get('evaluators_can_see_other_eval', '0');
		
		$view_calc = JRequest::getVar('view_calc', null, 'POST', 'none', 0);

        $layout					= JRequest::getVar('layout', null, 'GET', 'none', 0);
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
		if(empty($gid)) $gid=$this->getState('evaluator_group'); 
		$uid					= $this->getState('user');
		$profile				= $this->getState('profile');
		$miss_doc				= $this->getState('missing_doc');
		$complete				= $this->getState('complete');
		$validate_application	= $this->getState('validate');
        $aid 					= JRequest::getVar('aid', null, 'GET', 'none', 0);

		// Starting a session.
		$session = JFactory::getSession();
		
		if(empty($profile) && $session->has( 'profile' )) $profile = $session->get( 'profile' );
		if(empty($finalgrade) && $session->has( 'finalgrade' )) $finalgrade = $session->get( 'finalgrade' );
		if(empty($quick_search) && $session->has( 'quick_search' )) $quick_search = $session->get( 'quick_search' );
		if(empty($gid) && $session->has( 'groups' )) $gid = $session->get( 'groups' );
		if(empty($uid) && $session->has( 'evaluator' )) $uid = $session->get( 'evaluator' );
		if(empty($schoolyears) && $session->has( 'schoolyears' )) $schoolyears = $session->get( 'schoolyears' );
		if(empty($campaigns) && $session->has( 'campaigns' )) $campaigns = $session->get( 'campaigns' );
		if(empty($programmes) && $session->has( 'programmes' )) $programmes = $session->get( 'programmes' );
		if(empty($profile)) $profile = JRequest::getVar('profile', null, 'GET', 'none', 0);
		
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		
		if (count($search)==0) {
			$search = $s_elements;
			$search_values = $s_elements_values;
		}
		$query = '';
        if(isset($aid) && !empty($aid))
            $query .= ' AND eu.user_id ='.$aid;
		$and = true;
        if($layout != "evaluation"){
        	if(!$evaluators_can_see_other_eval && EmundusHelperAccess::isEvaluator($this->_user->id)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= ' (ee.user IS NULL OR ee.user = '.$this->_user->id.') ';
            }

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

            if (!empty($programmes) && isset($programmes) && $programmes[0] != "%") 
           		$query .= ' AND #__emundus_setup_campaigns.training IN ("' . implode('","', $programmes) . '")';

            if(isset($finalgrade) && !empty($finalgrade)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .=' WHERE '; }
                $query.= 'efg.final_grade like "%'.$finalgrade.'%"';
            }

            if(!empty($search_values)) {
                $i = 0;
                foreach ($search as $s) {
                    if(!empty($s)){
                        $tab = explode('.', $s);
                        if (count($tab)>1 && !empty($search_values[$i])) {
                            if($tab[0]=='jos_emundus_training'){
                                $query .= ' AND ';
                                $query .= 'search_'.$tab[0].'.id like "%'.$search_values[$i].'%"';
                            }else{
                                $query .= ' AND ';
                                $query .= $tab[0].'.'.$tab[1].' like "%'.$search_values[$i].'%"';
                            }
                        }
                        $i++;
                    }
                }
            }

            if(isset($quick_search) && !empty($quick_search)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                if (is_numeric ($quick_search))
                    $query.= 'u.id='.$quick_search.' ';
                else
                    $query.= '(eu.lastname LIKE "%'.mysql_real_escape_string($quick_search).'%"
                            OR eu.firstname LIKE "%'.mysql_real_escape_string($quick_search).'%"
                            OR u.email LIKE "%'.mysql_real_escape_string($quick_search).'%"
                            OR u.username LIKE "%'.mysql_real_escape_string($quick_search).'%")';
            }

            if(isset($gid) && !empty($gid)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= '(ege.group_id='.mysql_real_escape_string($gid).' OR ege.user_id IN (select user_id FROM #__emundus_groups WHERE group_id='.mysql_real_escape_string($gid).'))';
            }
            if(isset($uid) && !empty($uid)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= '(ege.user_id='.mysql_real_escape_string($uid).' OR ege.group_id IN (select e.group_id FROM #__emundus_groups e WHERE e.user_id='.mysql_real_escape_string($uid).'))';
            }

            if(isset($profile) && !empty($profile)){
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= '(esp.id = '.$profile.' OR efg.result_for = '.$profile.' OR eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id = '.$profile.'))';
            }

            if(isset($miss_doc) &&  !empty($miss_doc)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= $miss_doc.' NOT IN (SELECT attachment_id FROM #__emundus_uploads eup WHERE eup.user_id = u.id)';
            }
        }

//var_dump(str_replace('#__','jos_',$query));
		return $query;
	}
	
	function _buildQuery_myGroup(){
		$query = $this->_buildSelect(); 
		$query .= ' WHERE ed.validated=1 AND ecc.submitted=1'; 
		$pa = $this->getProfileAcces($this->_user->id);
		$query .= ' AND (eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id IN ('.implode(',',$pa).')) OR eu.user_id IN (select user_id from #__emundus_users where profile IN ('.implode(',',$pa).'))) ';
		$query .= $this->_buildFilters();
		$this->_db->setQuery($query);
		$applicants=$this->_db->loadObjectlist();
		return $applicants;
	}
	
	function _buildQuery_myAffect(){
		$query = $this->_buildSelect();
		$query .= ' LEFT JOIN #__emundus_groups_eval AS ege ON ege.applicant_id = epd.user AND ege.campaign_id = ecc.campaign_id';
		$query .= ' WHERE ed.validated=1';
		//$pa = $this->getProfileAcces($this->_user->id);
		$query .= ' AND (ege.user_id='.$this->_user->id.' OR ege.group_id IN (select group_id from #__emundus_groups where user_id='.$this->_user->id.'))';
		$query .= $this->_buildFilters();
		$this->_db->setQuery($query);
 //echo str_replace('#_','jos',$query);
		$applicants=$this->_db->loadObjectlist();
		return $applicants;
	}
	
	function _buildQuery_all(){
		$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		if(empty($gid)) $gid = JRequest::getVar('evaluator_group', null, 'POST', 'none', 0);
		$uid = $this->getState('user');

		// Starting a session.
		$session = JFactory::getSession();
		if(empty($uid) && $session->has( 'evaluator' )) $uid = $session->get( 'evaluator' );
		if(empty($gid) && $session->has( 'groups' )) $gid = $session->get( 'groups' );
		
		$query = $this->_buildSelect();
		if((isset($gid) && !empty($gid)) || (isset($uid) && !empty($uid))) {
			$query .= ' LEFT JOIN #__emundus_groups_eval AS ege ON ege.applicant_id = epd.user AND ege.campaign_id = ecc.campaign_id';
		}
		$query .= ' WHERE ed.validated = 1';
		$query .= $this->_buildFilters();
 //echo "<hr>".str_replace('#_', "jos", $query);
		$this->_db->setQuery($query);
		return $this->_db->loadObjectlist();
	}
	
	function _buildQuery(){	
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$evaluators_can_see = $eMConfig->get('evaluators_can_see', '0');
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		if(empty($gid)) $gid = JRequest::getVar('evaluator_group', null, 'POST', 'none', 0);
		$uid = JRequest::getVar('user', null, 'POST', 'none', 0);
		
		// Starting a session.
		$session = JFactory::getSession();
		if(empty($gid) && $session->has( 'groups' )) $gid = $session->get( 'groups' );
		if(empty($uid) && $session->has( 'evaluator' )) $uid = $session->get( 'evaluator' );
		//$s_elements = $session->get('s_elements');
		//if (count($search)==0) $search = $s_elements;
		
		if ( EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) ){
			$applicants = $this->_buildQuery_all();
		} elseif( EmundusHelperAccess::isEvaluator($this->_user->id) ){ //echo $evaluators_can_see.'<hr>';
			if($evaluators_can_see == 4)
				$applicants = $this->_buildQuery_all();
			elseif($evaluators_can_see == 3)
				$applicants = $this->union($this->_buildQuery_myGroup(), $this->_buildQuery_myAffect());
			elseif($evaluators_can_see == 2)
				$applicants = $this->_buildQuery_myGroup();
			elseif($evaluators_can_see == 1)
				$applicants = $this->_buildQuery_myAffect();
			else
				$applicants = array();	
		} elseif ( EmundusHelperAccess::isPartner($this->_user->id) ){
			$applicants = $this->_buildQuery_myGroup();
		} else{
			$applicants = array();	
		}


		if(!empty($applicants)) {

			// Application Comments
			$params = array("user_name", "date", "reason", "comment");
			$applicationComment = EmundusHelperList::createApplicationCommentBlock($applicants, $params);

			$eMConfig = JComponentHelper::getParams('com_emundus');
			$expert_document_id = $eMConfig->get('expert_document_id', '36');
			///** Ajout des colonnes de moyennes 
			$head_values = $this->_applicantColumns;
			foreach($head_values as $head) $head_val[] = $head['name'];
			
			foreach($applicants as $key=>$applicant){
				$eval_list 						= array();
				$eval_list['user_id'] 			= $applicant->user_id;
				$eval_list['name'] 				= '<b>'.strtoupper($applicant->lastname).'</b> <br / >'.$applicant->firstname;
				$eval_list['email_applicant'] 	= $applicant->email;
				//$eval_list['profile']=$applicant->profile;
				$eval_list['campaign'] 			= $applicant->campaign;
				$eval_list['campaign_id'] 		= $applicant->campaign_id;
				$eval_list['evaluation_id'] 	= $applicant->evaluation_id;
				$eval_list['final_grade'] 		= $applicant->final_grade;
				
				if (in_array('expert', $this->_actions)) {
					$request = $this->isFileRequestSent($applicant->user_id, $expert_document_id, $applicant->campaign_id);
					$eval_list['request'] 		= !empty($request) ? $request : 0;

				}
				if (in_array('letter', $this->_actions)) {
					$eval_list['date_result_sent'] = !empty($applicant->date_result_sent) ? $applicant->date_result_sent : 0;
				}

				$eval_list['application_comment'] = $applicationComment[$applicant->user_id];

				
	//var_dump($this->col);			
				if(!empty($search)){
					foreach($search as $c){
						if(!empty($c)){
							$name = explode('.', $c);
							if(!in_array(@$name[1], $head_val) && !empty($name[1])){
								$eval_list[$name[1]] = $applicant->$name[1];
							}
						}
					}
				} 

				if(!empty($this->col)){
					foreach($this->col as $c){
						if(!empty($c)){
							$name = explode('.', $c);
							if(!in_array(@$name[1], $head_val) && !empty($name[1])){
								$eval_list[$name[1]] = $applicant->$name[1];
							}
						}
					}
				} 
				// evaluation list
				foreach($this->_eval_elements as $eval){ 
					$val = $applicant->{$eval->name};

					$params = json_decode($eval->params);
					
					if($eval->plugin=='databasejoin') {		
						$select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
						$from = $params->join_db_name;
						$where = $params->join_key_column.'='.$this->_db->Quote($val);
						$query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
						$query = preg_replace('#{thistable}#', $from, $query);
						$query = preg_replace('#{my->id}#', $applicant->user_id, $query); 
						$this->_db->setQuery( $query );
						$elt = $this->_db->loadResult();
						$eval_list[$eval->name] = $elt;
					} elseif($eval->plugin == 'radiobutton' || $eval->plugin == 'dropdown') {
						//var_dump($params->sub_options->sub_values); }
						$sub_values = $params->sub_options->sub_values; 
						$sub_labels = $params->sub_options->sub_labels; 
						$i = 0;
						foreach($sub_values as $sub_value){
							$sub_val[$sub_value] = $sub_labels[$i];
							$i++;
						}
						
						// var_dump($eval);
						if(in_array($val, array_keys($sub_val)))
							$eval_list[$eval->name] = @$sub_val[$val];
						else
							$eval_list[$eval->name] = $val;
						
					} else
						$eval_list[$eval->name] = $val;
				}
				if (!empty($applicant->user)) {
					$evaluator = JFactory::getUser($applicant->user);
					$eval_list['user'] = $evaluator->id;
					$eval_list['evaluator'] = $evaluator->name;
				} else {
					$eval_list['user']='';
					$eval_list['evaluator']='';
				}
				
				// ranking list
				/*foreach($all_applis as $all)
					if($all['user_id'] == $applicant->user_id && $all['user'] == $applicant->user)
						$eval_list['General ranking'] = $all['General ranking'];*/
						//$eval_list['global_mean'] = $all['global_mean'];
				$eval_lists[]=$eval_list;
			}
			if(!empty($eval_lists))
				$this->_applicants=$eval_lists;	
		} else
			$this->_applicants=$applicants;
		// var_dump($this->_applicants);
	}
	
	function getUsers(){
		$this->_buildQuery();
		return $this->_buildContentOrderBy();
	}
	
	function getSelectList(){
		
		/*$col_elt = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$col_other = JRequest::getVar('elements_other', null, 'POST', 'array', 0);
		// Starting a session.
		$session = JFactory::getSession();
		$elements = $session->get('s_elements');
		$elements_other = $session->get('s_elements_other');

		if (count($col_elt)==0 || empty($col_elt)) $col_elt = is_array($elements)?$elements:array();
		if (count($col_other)==0 || empty($col_other)) $col_other = is_array($elements_other)?$elements_other:array();

		$col = array_merge($col_elt, $col_other);

		var_dump($col); var_dump($this->col);
*/
		$col = $this->col;

		$lists = '';
		
		if(!empty($col)){
			foreach($col as $c){
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
					$col = $this->_db->loadObject();
					$cols[] = $col;
				}
			}
			if(!empty($cols)){
				foreach($cols as $c){
					if(!empty($c)){
						$list = array();
						$list['name'] = @$c->name;
						$list['label'] = @ucfirst($c->label);
						$lists[]=$list;
					}
				}
			}
		}
		return $lists;
	}

	// get the actions menu option
	function getActions(){
		$menu = JSite::getMenu();
		$current_menu  = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);

		$this->_actions = explode(',', $menu_params->get('em_actions'));

		return $this->_actions;
	}

	// get evaluation columns
	function getEvalColumns(){
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$evaluation_groups_id = $eMConfig->get('evaluation_groups_id', '41');
		$query = 'SELECT name, label, plugin, params, ordering 
				FROM #__fabrik_elements 
				WHERE group_id IN ('.$evaluation_groups_id.')
				AND hidden != 1
				AND show_in_list_summary=1
				ORDER BY ordering';
		$this->_db->setQuery( $query );
		return EmundusHelperFilters::insertValuesInQueryResult($this->_db->loadAssocList('name'), array("sub_values", "sub_labels"));
	}

	// get elements by groups
	// @params string List of Fabrik groups comma separated
	function getElementsByGroups($groups){
		return EmundusHelperFilters::getElementsByGroups($groups);
	}
	
	// get applicant columns
	function getApplicantColumns(){
		$cols = array();
		$cols[] = array('name' =>'user_id', 'label'=>'USER_ID');
		$cols[] = array('name' =>'name', 'label'=>'NAME'); 
		//$cols[] = array('name' =>'profile', 'label'=>'PROFILE'); 
		$cols[] = array('name' =>'campaign', 'label'=>'CAMPAIGN'); 
		if (in_array('expert', $this->_actions)) {
			$cols[] = array('name' =>'request', 'label'=>'REQUEST'); 
		}
		if (in_array('letter', $this->_actions)) {
			$cols[] = array('name' =>'date_result_sent', 'label'=>'DATE_RESULT_SENT_ON'); 
		}
		$cols[] = array('name' =>'application_comment', 'label'=>'APPLICATION_COMMENT'); 
		$this->_applicantColumns = $cols;

		return $cols;
	}
	
	// get ranking columns 
	function getRankingColumns(){
		$cols = array();
		$cols[] = array('name' =>'ranking', 'label'=>'RANKING'); 
		
		return $cols;
	}
	
	function getCurrentCampaign(){
		return EmundusHelperFilters::getCurrentCampaign();
	}

	function getCurrentCampaignsID(){
		return EmundusHelperFilters::getCurrentCampaignsID();
	}

	function getPublished(){
		//id des profiles published
		$query = 'SELECT id FROM #__emundus_setup_profiles WHERE published =1';
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
	}
	
	function getEvaluationByID($id){
		$query = 'SELECT * FROM #__emundus_evaluations AS ee 
		LEFT JOIN #__emundus_evaluations_repeat_reason AS eerr ON eerr.parent_id=ee.id
		WHERE ee.id = '.$id;
//		echo str_replace("#_", "jos", $query);
		$this->_db->setQuery($query);
		return $this->_db->loadAssocList();
	}

	// @description Get file request 
	function isFileRequestSent($student_id, $attachment_id, $campaign_id){
		$query = 'SELECT efr.time_date FROM #__emundus_files_request AS efr 
		WHERE efr.student_id = '.$student_id.'  
		AND efr.campaign_id = '.$campaign_id.' 
		AND efr.attachment_id = '.$attachment_id.'
		ORDER BY efr.time_date DESC';
//		echo str_replace("#_", "jos", $query);
		$this->_db->setQuery($query);
		$this->_request = $this->_db->loadResult();

		return $this->_request;
	}

	function getEvaluationReasons(){
		$query = 'SELECT esr.id, esr.reason, esrc.course FROM #__emundus_setup_reasons AS esr
				LEFT JOIN #__emundus_setup_reasons_repeat_course AS esrc ON esrc.parent_id=esr.id
				ORDER BY esr.ordering';
	//echo str_replace("#_", "jos", $query);
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('id');
	}

	function getEvaluationEligibility(){
		$query = 'SELECT * FROM #__emundus_setup_checklist WHERE whenneed > 1 AND page="checklist"';
//		echo str_replace("#_", "jos", $query);
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('whenneed');
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
	
	function getProfiles(){
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function is_evaluator_of_this($evaluation){
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$query='SELECT COUNT(*) FROM #__emundus_evaluations WHERE id='.$evaluation.' AND user='.$user->id;
		$db->setQuery($query);
		return $db->loadResult();
	}

	function getCampaignCandidature($applicant_id, $campaign_id) {
		$query = "SELECT * FROM #__emundus_campaign_candidature WHERE applicant_id=".$applicant_id." AND campaign_id=".$campaign_id;
		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	function getFinalGrade($applicant_id, $campaign_id) {
		$query = "SELECT * FROM #__emundus_final_grade WHERE student_id=".$applicant_id." AND campaign_id=".$campaign_id;
		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	function getEvaluationDocuments($applicant_id, $campaign_id, $result) {
		$query = 'SELECT *, eu.id as id, esa.id as attachment_id FROM #__emundus_uploads eu 
					LEFT JOIN #__emundus_setup_attachments esa ON esa.id=eu.attachment_id 
					WHERE eu.user_id='.$applicant_id.' AND campaign_id='.$campaign_id.' 
					AND eu.attachment_id IN (
						SELECT DISTINCT(esl.attachment_id) FROM #__emundus_setup_letters esl WHERE esl.eligibility='.$result.'
						) 
					ORDER BY eu.timedate';

		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	function getLettersTemplate($eligibility, $training) {
		$query = "SELECT * FROM #__emundus_setup_letters WHERE eligibility=".$eligibility." AND training=".$this->_db->Quote($training);
		$this->_db->setQuery($query); 
		return $this->_db->loadAssocList();
	}

	function getLettersTemplateByID($id) {
		$query = "SELECT * FROM #__emundus_setup_letters WHERE id=".$id;
		$this->_db->setQuery($query); 
		return $this->_db->loadAssocList();
	}

	function getExperts($applicant_id, $select, $table) {
		$query = "SELECT ".$select." FROM ".$table." WHERE user=".$applicant_id;
		$this->_db->setQuery($query); 
		return $this->_db->loadAssocList();
	}
}
?>