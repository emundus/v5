<?php
/**
 * Users Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
jimport( 'joomla.application.application' );
 
class EmundusModelRanking extends JModel
{
	var $_total = null;
	var $_pagination = null;
	var $_applicants = array();

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
	
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
 
        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$filter_order     = $mainframe->getUserStateFromRequest(  $option.'filter_order', 'filter_order', 'overall', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
		
 		$this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}
	
	function _buildContentOrderBy(){
		global $option;

		$mainframe =& JFactory::getApplication();

		$tmp = array();
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');

		$sort=($filter_order_Dir=='desc')?SORT_DESC:SORT_ASC;
 		$can_be_ordering = array();
		foreach($this->getEvalColumns() as $eval_col)
			$can_be_ordering[] = $eval_col['name'];	
		foreach($this->getApplicantColumns() as $appli_col)
			$can_be_ordering[] = $appli_col['name'];
		foreach($this->getRankingColumns() as $appli_col)
			$can_be_ordering[] = $appli_col['name'];
		
		$select_list = $this->getSelectList();
		if(!empty($select_list))
			foreach($this->getSelectList() as $cols) $can_be_ordering[] = $cols['name'];
		
		$this->_applicants = $this->multi_array_sort($this->_applicants, 'name', SORT_ASC);
		$rank=1;
		for($i=0 ; $i<count($this->_applicants) ; $i++) {
			$this->_applicants[$i]['ranking']=$rank;
			$rank++;
		}

		if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
			$this->_applicants = $this->multi_array_sort($this->_applicants, $filter_order, $sort);
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
	
	function multi_array_sort($multi_array=array(),$sort_key,$sort=SORT_ASC){  
        if(is_array($multi_array)){  
            foreach ($multi_array as $key=>$row_array){
                if(is_array($row_array)){  
                    $key_array[$key] = $row_array[$sort_key]; 
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

	function getProfileAcces($user){
		$db =& JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id='.$user;
		$db->setQuery( $query );
		$profiles = $db->loadResultArray();
		return $profiles;
	}
	
	
	function getCurrentCampaign(){
		$query = 'SELECT DISTINCT schoolyear 
				FROM #__emundus_setup_profiles 
				WHERE published=1 
				ORDER BY schoolyear';
		$this->_db->setQuery( $query );
		return $this->_db->loadResultArray();
	}
	
	/** GET THE GENERAL RANKING **/
	/*function getGlobalRanking($query){
		$this->_db->setQuery($query);
		$this->_applicants = $this->_db->loadAssocList();
		$count = 0;
		
		/** get global mean of all evaluations for each applicants **\/
		for($i=0 ; $i<count($this->_applicants) ; $i++) {
			if($i == 0 || $this->_applicants[$i]['user_id'] != $this->_applicants[$i-1]['user_id']) $count++;
			$this->_applicants[$i]['overall'] = $this->getMeanEval('overall',$this->_applicants[$i]['user_id']);
		}
		
		/** sort by **\/
		$this->_applicants = $this->multi_array_sort($this->_applicants, 'overall', SORT_DESC);
		$rank=0;
		
		/** get the global ranking for all applicants sort **\/
		for($i=0 ; $i < $count ; $i++) {
			if($i == 0 || $this->_applicants[$i]['user_id'] != $this->_applicants[$i-1]['user_id']) $rank++;
			$this->_applicants[$i]['ranking_all']=$rank.' / '.$count;
		}
		return $this->_applicants;
	}*/
	
	
	function _buildSelect(){
		$session =& JFactory::getSession();
		$current_user = & JFactory::getUser();
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_other = JRequest::getVar('elements_other', null, 'POST', 'array', 0);
		$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);
		$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		$uid = JRequest::getVar('user', null, 'POST', 'none', 0);
		$s_elements = $session->get('s_elements');
		$s_elements_other = $session->get('s_elements_other');
		
		if (count($search)==0) $search = $s_elements;
		if (count($search_other)==0) $search_other = $s_elements_other;
		
		if(!empty($search)) {
			asort($search);
			$i = -1;
			$old_table = '';
			$cols = array();
			foreach ($search as $c) {
				if(!empty($c)){
					$tab = explode('.', $c);
					if (count($tab)>=1) {
						if($tab[0] != $old_table)
							$i++;
						$cols[] = 'j'.$i.'.'.$tab[1];
						$old_table = $tab[0];
					}
				}
			}
			if(count($cols>0) && !empty($cols))
				$cols = implode(', ',$cols);
		}
		
		if(!empty($search_other)) {
			asort($search_other);
			$i = -1;
			$cols_other = array();
			foreach ($search_other as $cother) {
				$tab = explode('.', $cother);
				if(!empty($cother)){
					$cols_other[] = 'efg.'.$tab[1];
				}
			}
			if(count($cols_other>0) && !empty($cols_other))
				$cols_other = implode(', ',$cols_other);
		}
		
		$query = 'SELECT DISTINCT(eu.user_id), CONCAT_WS(" ", UPPER(eu.lastname), eu.firstname) as name, esp.id as profile,
				efg.id, efg.Final_grade, efg.engaged, efg.result_for, efg.scholarship';
		if(!empty($cols)) $query .= ', '.$cols;
		if(!empty($cols_other)) $query .= ', '.$cols_other;
		$query .= '	FROM #__emundus_declaration AS ed 
					LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id=ed.user
					LEFT JOIN #__users AS u ON u.id=ed.user
					LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = ed.user 
					LEFT JOIN #__emundus_users AS eu ON u.id = eu.user_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile 
					LEFT JOIN #__emundus_academic AS ea ON ea.user = ed.user';
	
		if(!empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				if(!empty($s)){
					$tab = explode('.', $s);
					if (count($tab)>1) {
						$query .= ' LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=ed.user ';
						$i++;
					}
				}
			}
		}
		
		if(isset($gid) && !empty($gid) || (isset($uid) && !empty($uid))) 
			$query .= ' LEFT JOIN #__emundus_groups_eval AS ege ON ege.applicant_id = epd.user ';
			
		$query .= ' WHERE ed.validated=1';
		if(empty($schoolyears)) $query .= ' AND eu.schoolyear IN ("'.implode('","',$this->getCurrentCampaign()).'")';
		if ($current_user->usertype=='Editor'){
			$pa = $this->getProfileAcces($current_user->id);
			$query .= ' AND (eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$pa).')) OR eu.user_id IN (select user_id from #__emundus_users where profile in ('.implode(',',$pa).'))) ';
		}
		//die(str_replace("#_", "jos", $query));
		return $query;
	}
	
	function _buildFilters(){
		
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$search_other = JRequest::getVar('elements_other', null, 'POST', 'array', 0);
		$search_values_other = JRequest::getVar('elements_values_other', null, 'POST', 'array', 0);
		$finalgrade = JRequest::getVar('finalgrade', null, 'POST', 'none', 0);
		$quick_search = JRequest::getVar('s', null, 'POST', 'none', 0);
		$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'array', 0);
		$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		$uid = JRequest::getVar('user', null, 'POST', 'none', 0);
		$profile = JRequest::getVar('profile', null, 'POST', 'none', 0);
		$miss_doc = JRequest::getVar('missing_doc', null, 'POST', 'none',0);
		
		// Starting a session.
		$session =& JFactory::getSession();
		
		if(empty($profile) && $session->has( 'profile' )) $profile = $session->get( 'profile' );
		if(empty($finalgrade) && $session->has( 'finalgrade' )) $finalgrade = $session->get( 'finalgrade' );
		if(empty($quick_search) && $session->has( 'quick_search' )) $quick_search = $session->get( 'quick_search' );
		if(empty($schoolyears) && $session->has( 'schoolyears' )) $schoolyears = $session->get( 'schoolyears' );
		if(empty($profile)) $profile = JRequest::getVar('profile', null, 'GET', 'none', 0);
		
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		$s_elements_other = $session->get('s_elements_other');
		$s_elements_values_other = $session->get('s_elements_values_other');
		
		$eMConfig =& JComponentHelper::getParams('com_emundus');
		
		if (count($search)==0) {
			$search = $s_elements;
			$search_values = $s_elements_values;
		}
		
		$query = '';
		$and = true;
		
		if(isset($finalgrade) && !empty($finalgrade)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= 'efg.Final_grade like "%'.$finalgrade.'%"';
		}
		
		//adv_filter
		
		if(!empty($search_values)) {
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab)>1) {
					$query .= ' AND ';
					$query .= 'j'.$i.'.'.$tab[1].' like "%'.$search_values[$i].'%"';
					$i++;
				}
			}
		}
		
		if(!empty($search_values_other)) {
			$i = 0;
			foreach ($search_other as $sother) {
				$tab = explode('.', $sother);
				if (count($tab)>1 and !empty($search_values_other[$i])) {
					$query .= ' AND ';
					$query .= 'efg.'.$tab[1].' like "%'.$search_values_other[$i].'%"';
				}
				$i++;
			}
		}
		
		if(isset($schoolyears) &&  !empty($schoolyears)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= 'eu.schoolyear IN ("'.implode('","',$schoolyears).'") ';
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
						OR u.username LIKE "%'.mysql_real_escape_string($quick_search).'%" )';
		}	
		
		if(isset($gid) && !empty($gid)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= 'ege.group_id='.mysql_real_escape_string($gid).' OR ege.user_id IN (select user_id FROM #__emundus_groups WHERE group_id='.mysql_real_escape_string($gid).')';
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
		
		return $query;
	}
	
	function _buildQuery(){
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_other = JRequest::getVar('elements_other', null, 'POST', 'array', 0);
		$current_user = & JFactory::getUser();
		
		$query = $this->_buildSelect();
		
		/** get the query whithout filters to get the global ranking **/
		//$all_applis = $this->getGlobalRanking($query);
		
		/** add filters to the query **/
		$query .= $this->_buildFilters();
		
		$this->_db->setQuery($query);
		$applicants=$this->_db->loadObjectlist();
		
		$evals = $this->getEvalColumns();
		$head_values = $this->getApplicantColumns();
		if(!empty($applicants)){
			foreach($applicants as $applicant){
				$eval_list=array();
				foreach($head_values as $head){
					$head_val[] = $head['name'];
					$eval_list[$head['name']] = $applicant->$head['name'];
				}
				/** add an advance filter columns only if not already exist **/
				if(!empty($search)){
					foreach($search as $c){
						if(!empty($c)){
							$name = explode('.',$c);
							if(!in_array($name[1],$head_val)){
								$eval_list[$name[1]] = $applicant->$name[1];
							}
						}
					}
				}
				
				if(!empty($search_other)){
					foreach($search_other as $c){
						if(!empty($c)){
							$name = explode('.',$c);
							if(!in_array($name[1],$head_val)){
								$eval_list[$name[1]] = $applicant->$name[1];
							}
						}
					}
				}
				
				/** affect means columns **/
				foreach($evals as $eval)
					$eval_list[$eval['name']] = $this->getMeanEval($eval['name'],$applicant->user_id);
				
				/** put the general ranking **/
				//foreach($all_applis as $all)
					//if($all['user_id'] == $applicant->user_id) $eval_list['ranking_all'] = $all['ranking_all'];
				 
				$eval_list['engaged'] = $applicant->engaged;
				$eval_list['final_grade']=$applicant->Final_grade;
				$eval_list['row_id']=$applicant->id;
				$eval_lists[]=$eval_list;
			}
			if(!empty($eval_lists))
				$this->_applicants=$eval_lists;
		}else
			$this->_applicants=$applicants;
	}
	
	function getUsers(){	
		// Lets load the data if it doesn't already exist
		$this->_buildQuery();
		return $this->_buildContentOrderBy();
	} 
	
	/** get means of evaluation for an applicant **/
	function getMeanEval($note,$user_id){
		$query = 'SELECT AVG( '.$note.' ) FROM #__emundus_evaluations WHERE student_id = '.$user_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
	
	function getSelectList(){
		
		$col_elt = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$col_other = JRequest::getVar('elements_other', null, 'POST', 'array', 0);

		if (count($col_elt)==0) $col_elt = array();
		if (count($col_other)==0) $col_other = array();
		$col = array_merge($col_elt, $col_other);

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
		$query = 'SELECT name, label, params, ordering 
				FROM #__fabrik_elements 
				WHERE group_id=41
				AND hidden != 1
				AND plugin = "fabrikcalc"
				ORDER BY ordering';
		$this->_db->setQuery( $query );
		return $this->_db->loadAssocList();
	}
	
	// get applicant columns
	function getApplicantColumns(){
		$cols = array();
		$cols[] = array('name' =>'user_id', 'label'=>'USER_ID');
		$cols[] = array('name' =>'name', 'label'=>'NAME'); 
		$cols[] = array('name' =>'profile', 'label'=>'PROFILE'); 
		$cols[] = array('name' =>'result_for', 'label'=>'RESULT_FOR'); 
		$cols[] = array('name' =>'engaged', 'label'=>'ENGAGED'); 
		$cols[] = array('name' =>'Final_grade', 'label'=>'FINAL_GRADE');
		$cols[] = array('name' =>'scholarship', 'label'=>'SCHOLARSHIP');
		return $cols;
	}
	
	// get ranking columns
	function getRankingColumns(){
		$cols = array();
		$cols[] = array('name' =>'ranking', 'label'=>'RANKING'); 
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