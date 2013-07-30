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
	var $_eval_elements = null;
	
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
	
		//Set session variables
		$filter_order			= $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'overall', 'cmd' );
        $filter_order_Dir		= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
		$schoolyears			= $mainframe->getUserStateFromRequest( $option."schoolyears", "schoolyears", EmundusHelperFilters::getSchoolyears() );
		$campaigns				= $mainframe->getUserStateFromRequest( $option."campaigns", "campaigns", EmundusHelperFilters::getCurrentCampaignsID() );
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
		foreach($this->getApplicantColumns() as $info_col) $can_be_ordering[] = $info_col['name'];
		//foreach($this->getRankingColumns() as $rank_col) $can_be_ordering[] = $rank_col['name'];
		
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
		$head_values = $this->getApplicantColumns();
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
	
	function _buildSelect(){
		$current_user 			= JFactory::getUser();
		$search					= $this->getState('elements');
		$s_elements				= $this->getState('s_elements');
		$search_other			= $this->getState('elements_other');
		$schoolyears			= $this->getState('schoolyears');
		$gid					= $this->getState('groups');
		if(empty($gid)) $gid=$this->getState('evaluator_group'); 
		$uid					= $this->getState('user');
		$miss_doc				= $this->getState('missing_doc');
		$validate_application	= $this->getState('validate');
		$this->_eval_elements = $this->getElementsByGroups(41);
		$db = JFactory::getDBO();
		
		foreach ($this->_eval_elements as $eval) {
			$eval_columns[] = 'ee.'.$eval->name;
		}

		if (count($search)==0) $search = $s_elements;

		if(!empty($search)) {
			asort($search);
			$i = -1;
			$old_table = '';
			$cols = array();
			foreach ($search as $c) {
				if(!empty($c)){
					$tab = explode('.', $c);
					if($tab[0]=="jos_emundus_training"){
						if (count($tab)>=1) {
							if($tab[0] != $old_table)
								$i++;
							$cols[] = 'j'.$i.'.id as training_id';
							$old_table = $tab[0];
						}
					}else{
						if (count($tab)>=1) {
							if($tab[0] != $old_table)
								$i++;
							$cols[] = 'j'.$i.'.'.$tab[1];
							$old_table = $tab[0];
						}
					}
				}
			}
			if(count($cols>0) && !empty($cols))
				$cols = implode(', ',$cols);
		}
		
		$query = 'SELECT ee.student_id, eu.user_id, eu.firstname, eu.lastname, esp.id as profile, #__emundus_setup_campaigns.label as campaign, #__emundus_setup_campaigns.id as campaign_id, ee.user, ee.id as evaluation_id, efg.date_result_sent, efg.final_grade ';
		if(!empty($cols)) 
			$query .= ', '.$cols;
		if(!empty($eval_columns)) 
			$query .= ', '.implode(",", $eval_columns);
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

		if(!empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				if(!empty($s)){
					$tab = explode('.', $s);
					if (count($tab)>1) {
						if($tab[0]=='jos_emundus_training'){
							$query .= ' LEFT JOIN #__emundus_setup_teaching_unity AS j'.$i.' ON j'.$i.'.code=#__emundus_setup_campaigns.training';
						}else{
							$query .= ' LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=ed.user ';
						}
						$i++;
					}
				}
			}
		}
// echo str_replace('#_','jos',$query);
		return $query;
	}
	
	function _buildFilters(){
		$view_calc = JRequest::getVar('view_calc', null, 'POST', 'none', 0);

		$search					= $this->getState('elements');
		$search_values			= $this->getState('elements_values');
		$search_other			= $this->getState('elements_other');
		$search_values_other	= $this->getState('elements_values_other');
		$finalgrade				= $this->getState('finalgrade');
		$quick_search			= $this->getState('s');
		$schoolyears			= $this->getState('schoolyears');
		$campaigns				= $this->getState('campaigns');
		$gid					= $this->getState('groups');
		if(empty($gid)) $gid=$this->getState('evaluator_group'); 
		$uid					= $this->getState('user');
		$profile				= $this->getState('profile');
		$miss_doc				= $this->getState('missing_doc');
		$complete				= $this->getState('complete');
		$validate_application	= $this->getState('validate');

		// Starting a session.
		$session = JFactory::getSession();
		
		if(empty($profile) && $session->has( 'profile' )) $profile = $session->get( 'profile' );
		if(empty($finalgrade) && $session->has( 'finalgrade' )) $finalgrade = $session->get( 'finalgrade' );
		if(empty($quick_search) && $session->has( 'quick_search' )) $quick_search = $session->get( 'quick_search' );
		if(empty($gid) && $session->has( 'groups' )) $gid = $session->get( 'groups' );
		if(empty($uid) && $session->has( 'evaluator' )) $uid = $session->get( 'evaluator' );
		if(empty($schoolyears) && $session->has( 'schoolyears' )) $schoolyears = $session->get( 'schoolyears' );
		if(empty($campaigns) && $session->has( 'campaigns' )) $campaigns = $session->get( 'campaigns' );
		if(empty($profile)) $profile = JRequest::getVar('profile', null, 'GET', 'none', 0);
		
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		
		if (count($search)==0) {
			$search = $s_elements;
			$search_values = $s_elements_values;
		}
		$query = '';
		$and = true;
		if($schoolyears[0] == "%")
			$query .= ' AND #__emundus_setup_campaigns.year like "%" ';
		elseif(!empty($schoolyears))
			$query .= ' AND #__emundus_setup_campaigns.year IN ("'.implode('","', $schoolyears).'") ';
		else
			$query .= ' AND #__emundus_setup_campaigns.year IN ("'.implode('","', $this->getCurrentCampaign()).'")';


		if(@$campaigns[0] == "%" || empty($campaigns[0]))
			$query .= ' AND #__emundus_setup_campaigns.id like "%" ';
		elseif(!empty($campaigns)) 
			$query .= ' AND #__emundus_setup_campaigns.id IN ("'.implode('","', $campaigns).'") ';
		else	
			$query .= ' AND #__emundus_setup_campaigns.id IN ("'.implode('","', $this->getCurrentCampaignsID()).'")';
		
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
					if($tab[0]=='jos_emundus_training'){
						if (count($tab)>1) {
							$query .= ' AND ';
							$query .= 'j'.$i.'.id like "%'.$search_values[$i].'%"';
							$i++;
						}
					}else{
						if (count($tab)>1) {
							$query .= ' AND ';
							$query .= 'j'.$i.'.'.$tab[1].' like "%'.$search_values[$i].'%"';
							$i++;
						}
					}
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
		
		//$query .= ' ORDER BY eu.user_id';
		// var_dump(str_replace('#__','jos_',$query));
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
// echo str_replace('#_','jos',$query);
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
// echo str_replace('#_', "jos", $query);
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
		$s_elements = $session->get('s_elements');
		if (count($search)==0) $search = $s_elements;
		
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
			///** Ajout des colonnes de moyennes 
			$head_values = $this->getApplicantColumns();
			foreach($head_values as $head) $head_val[] = $head['name'];
			
			foreach($applicants as $key=>$applicant){
				$eval_list=array();
				$eval_list['user_id']=$applicant->user_id;
				$eval_list['name']='<b>'.strtoupper($applicant->lastname).'</b> <br / >'.$applicant->firstname;
				//$eval_list['profile']=$applicant->profile;
				$eval_list['campaign']=$applicant->campaign;
				$eval_list['campaign_id']=$applicant->campaign_id;
				$eval_list['evaluation_id'] = $applicant->evaluation_id;
				$eval_list['final_grade'] = $applicant->final_grade;
				$eval_list['date_result_sent'] = !empty($applicant->date_result_sent) ? date(JText::_('DATE_FORMAT_LC'), strtotime($applicant->date_result_sent)) : JText::_('NOT_SENT');
				
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
							$eval_list[$eval->name] = $sub_val[$val];
						else
							$eval_list[$eval->name] = $val;
						
					} else
						$eval_list[$eval->name] = $val;
				}
				if (!empty($applicant->user)) {
					$evaluator = JFactory::getUser($applicant->user);
					$eval_list['user'] = $evaluator->id;
					$eval_list['user_name'] = $evaluator->name;
				} else {
					$eval_list['user']='';
					$eval_list['user_name']='';
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
		
		$col = JRequest::getVar('elements', null, 'POST', 'array', 0);
		// Starting a session.
		$session = JFactory::getSession();
		$elements = $session->get('s_elements');
		if (count($col)==0) $col = $elements;
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

	// get evaluation columns
	function getEvalColumns(){
		$query = 'SELECT name, label, plugin, params, ordering 
				FROM #__fabrik_elements 
				WHERE group_id=41
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
		$cols[] = array('name' =>'date_result_sent', 'label'=>'DATE_RESULT_SENT_ON'); 
		
		return $cols;
	}
	
	// get ranking columns
	function getRankingColumns(){
		$cols = array();
		$cols[] = array('name' =>'ranking', 'label'=>'RANKING'); 
		
		return $cols;
	}
	
	function getCurrentCampaign(){
		/*$query = 'SELECT DISTINCT year as schoolyear 
				FROM #__emundus_setup_campaigns 
				WHERE published=1 
				ORDER BY schoolyear DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadResultArray();*/
		return EmundusHelperFilters::getCurrentCampaign();
	}

	function getCurrentCampaignsID(){
		$query = 'SELECT id 
				FROM #__emundus_setup_campaigns 
				WHERE published = 1 AND end_date > NOW()
				ORDER BY year DESC';
		$this->_db->setQuery( $query );
		return $this->_db->loadResultArray();
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
}
?>