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
 
class EmundusModelEval_auto extends JModel
{
	var $_total = null;
	var $_pagination = null;
	var $_applicants = array();
	var $_averages = array();
	var $minmax = array();
	var $_rank=false;

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
		$can_be_ordering = array ('user_id', 'name', 'nationality','profile','university','degrees','school_results','motivation_letter','reference', 'interview', 'english','french','application','overall','r','residence');
		
		if ($this->_rank) {
			$this->_applicants = $this->multi_array_sort($this->_applicants, 'overall', SORT_DESC);
			$rank=1;
			for($i=0 ; $i<count($this->_applicants) ; $i++) {
				$this->_applicants[$i]['r']=$rank;
				$rank++;
			}
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
	
	function setSql($str, $user_id) {
		$part1 = @explode('!', $str);
		$part2 = @explode('___', $part1[0]);
		$part3 = @explode('::', $part1[1]);
		$limit = empty($part3[2])?0:$part3[2];
		$sql = 'SELECT '.@$part2[1].' FROM '.@$part2[0].' WHERE '.@$part3[0].' = '.@$part3[1].' LIMIT '.$limit.',1';
		//$tag = array ('/[user]/');
		//$replace = array ($user->id);
		$sql = str_replace('[user]', $user_id, $sql);
		//echo '<br />'.$sql;
		return $sql;
	}
	
	function getResult($query) {
		$db =& JFactory::getDBO();
		$db->setQuery( $query );
		$val = $db->loadResult();
		$val = str_replace("no", 0, str_replace("yes", 1, $val));
		if ($this->instr($val, "//..*..//")) $val = "array(".str_replace("//..*..//", ",", $val).")";
		//$val = explode("//..*..//", $val); 
		$val = (empty($val)?0:$val);
		return $val;
	}
	
	function getVal($str, $user_id) {
		$part1 = @explode('!', $str);
		$part2 = @explode('___', $part1[0]);
		$part3 = @explode('::', $part1[1]);
		$limit = empty($part3[2])?0:$part3[2];
		if (@$part2[1]=="Duration_employment" || @$part2[1]=='placement' || @$part2[1]=='contract')
			$query = 'SELECT '.@$part2[1].' FROM '.@$part2[0].' WHERE '.@$part3[0].' = '.@$part3[1].' LIMIT 0,1';
		else
			$query = 'SELECT '.@$part2[1].' FROM '.@$part2[0].' WHERE '.@$part3[0].' = '.@$part3[1].' LIMIT '.$limit.',1';
		//$tag = array ('/[user]/');
		//$replace = array ($user->id);
		$query = str_replace('[user]', $user_id, $query);
		//echo $part2[1].'<hr>';
		//if ($user_id==737) echo $query;

		$db =& JFactory::getDBO();
		$db->setQuery( $query ); 
		
		$val = $db->loadResult();
		$val = str_replace("no", 0, str_replace("yes", 1, $val));
		//if ($user_id==1246 && @$part2[1]=='contract') echo $val.' > '.$query.'<hr>';
		// groupes multiples
		if ($this->instr($val, "//..*..//")) {
			$t = explode("//..*..//", $val);
			$val = @$t[$limit];
			//$val = (empty($val)?0:$val);
			//if ($user_id==1246 && @$part2[1]=='contract') echo $val.':'.$limit.':';
		}
		
		return $val;
	}
	
	function instr($haystack, $needle) {
		$pos = strpos($haystack, $needle, 0);
		if ($pos != 0) return true;
		return false;
	} 

	function recursiveSplit($pattern, $string, $str_out, $layer, $user_id) {
		preg_match_all($pattern, $string, $matches);
		// iterate thru matches and continue recursive split
		if (count($matches) > 1) {
			for ($i = 0; $i < count($matches[1]); $i++) {
				if (is_string($matches[1][$i])) {
					if (strlen($matches[1][$i]) > 0) {
						preg_match_all($pattern, $matches[1][$i], $m);
			//echo count($matches[0]).$string.'<br>';
						if (count($m[0]) == 0) {
							$str_out = str_replace($matches[0][$i], $this->getVal($matches[1][$i], $user_id), $str_out); 
							//$str_out = str_replace($matches[0][$i], $this->getResult($this->setSql($matches[1][$i], $user_id)), $str_out); 
						/*	echo "Tpl node: ".$matches[0][$i]."<br />";
							echo "Tagname: <tt>".$matches[1][$i]."</tt><br />";
							echo "STRING: ".$str_out.'<hr>'; */
						} 
	
						$str_out = $this->recursiveSplit($pattern, $matches[1][$i], $str_out, $layer + 1, $user_id);
					} 
				}
			}
		}
		return $str_out;
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
//print_r($key_array); 		
        return $multi_array;  
	}  

	function getCampaign()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT schoolyear FROM #__emundus_setup_profiles WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}
	
	function getProfileAcces($user)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id='.$user;
		$db->setQuery( $query );
		$profiles = $db->loadResultArray();
		
		return $profiles;
	}
	
	function _buildQuery(){
	    $db =& JFactory::getDBO();
		$current_user = & JFactory::getUser();
		$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		$uid = JRequest::getVar('user', null, 'POST', 'none', 0);
		$quick_search = JRequest::getVar('s', null, 'POST', 'none', 0);
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$finalgrade = JRequest::getVar('finalgrade', null, 'POST', 'none', 0);
		$view_calc = JRequest::getVar('view_calc', null, 'POST', 'none', 0);
		$profile = JRequest::getVar('profile', null, 'POST', 'none', 0);
		$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);
		
		// Starting a session.
		$session =& JFactory::getSession();
		
		if(empty($profile) && $session->has( 'profile' )) $profile = $session->get( 'profile' );
		if(empty($finalgrade) && $session->has( 'finalgrade' )) $finalgrade = $session->get( 'finalgrade' );
		if(empty($quick_search) && $session->has( 'quick_search' )) $quick_search = $session->get( 'quick_search' );
		if(empty($gid) && $session->has( 'groups' )) $gid = $session->get( 'groups' );
		if(empty($uid) && $session->has( 'evaluator' )) $uid = $session->get( 'evaluator' );
		if(empty($schoolyears) && $session->has( 'schoolyears' )) $schoolyears = $session->get( 'schoolyears' );
		
		if(empty($profile)) $profile = JRequest::getVar('profile', null, 'GET', 'none', 0);
		
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		
		$eMConfig =& JComponentHelper::getParams('com_emundus');
		$can_see_all = $eMConfig->get('can_evaluators_see_all_applicants', '0');
		
		if (count($search)==0) {
			$search = $s_elements;
			$search_values = $s_elements_values;
		}

	    $query='SELECT type, calc from `#__emundus_evaluation_weight` where calc<>"" and alias in ("1.1","1.2","1.3","1.4","1.5","1","2","3","4","6") order by alias';
		$db->setQuery($query);
		$eval_type=$db->loadObjectlist();
		//print_r($eval_type);

		//////////////////////////////////////////////
		$query = 'SELECT ed.user,efg.Final_grade,efg.id, epd.nationality, epd.Country_1 as residence, eu.profile, ea.Type_1, ea.Institution_1, ea.City_1, ee.to_enhance, ee.comment 
					FROM #__emundus_declaration AS ed
					LEFT JOIN #__emundus_evaluations AS ee ON ee.student_id=ed.user 
					LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id=ed.user
					LEFT JOIN #__users AS u ON u.id=ed.user
					LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = ed.user 
					LEFT JOIN #__emundus_users AS eu ON u.id = eu.user_id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile 
					LEFT JOIN #__emundus_academic AS ea ON ea.user = ed.user';
		if(!empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				if (count($tab)>1) {
					$query .= ' LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=ed.user ';
					$i++;
				}
			}
		}
		
		if(isset($gid) && !empty($gid) || (isset($uid) && !empty($uid)) || $current_user->usertype == 'Author') 
			$query .= ' LEFT JOIN #__emundus_groups_eval AS ege ON ege.applicant_id = epd.user ';
			
		$and = true;
		
		$query .= ' WHERE ed.validated=1';
		if(empty($schoolyears)) $query .= ' AND eu.schoolyear="'.$this->getSchoolyear().'"';
		
		if ($current_user->usertype=='Editor' && $can_see_all!=1 ) {
			$pa = $this->getProfileAcces($current_user->id);
			$query .= ' AND (eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$pa).')) OR eu.user_id IN (select user_id from #__emundus_users where profile in ('.implode(',',$pa).'))) ';
		} else if ($current_user->usertype=='Author' && $can_see_all!=1 ) {
			$query .= ' AND (ege.user_id='.$current_user->id.' OR ege.group_id IN (select group_id from #__emundus_groups where user_id='.$current_user->id.')) ';
		}
		
		if(isset($finalgrade) && !empty($finalgrade)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .=' WHERE '; }
			$query.= 'efg.Final_grade like "%'.$finalgrade.'%"';
		}
		$and = true;
		if(!empty($search)) {
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
		if(isset($schoolyears) &&  !empty($schoolyears)) {
			if($and) $query .= ' AND ';
			else { $and = true; $query .='WHERE '; }
			$query.= 'eu.schoolyear="'.mysql_real_escape_string($schoolyears).'"';
		}

//echo str_replace('#_','jos',$query);

		$db->setQuery($query);
		$applicants=$db->loadObjectlist();
		//print_r($applicants);
		$pattern = "/\{(([^{}]*|(?R))*)\}/";
		
		
		/** Ajout des colonnes de moyennes **/
		foreach($applicants as $applicant){
			$eval_list=array();
			$eval_list['user_id']=$applicant->user;
			$user=& JFactory::getUser($applicant->user);
			$user_id=$applicant->user;
			$query='SELECT * FROM `#__emundus_users` where user_id='.$user_id;
			$db->setQuery($query);
			$data_user_emundus=$db->loadObject();
			$first_name=$data_user_emundus->firstname;
			$last_name=$data_user_emundus->lastname;
				$this->_rank=true;
			$eval_list['name']='<b>'.strtoupper($last_name).'</b> <br / >'.$first_name;
			if(empty($view_calc)) {
				$eval_list['nationality']=$applicant->nationality;
				$eval_list['residence']=$applicant->residence;
				$eval_list['profile']=$applicant->profile;
				$eval_list['highest_diploma']=$applicant->Type_1;
				$eval_list['highest_diploma_univ']=$applicant->Institution_1;
				$eval_list['highest_diploma_city']=$applicant->City_1;
			}
			
			/** Traitement de la chaine Calculation **/
			foreach($eval_type as $eval){
				$str = $this->recursiveSplit($pattern, $eval->calc, $eval->calc, 0, $user_id);
				$str = $this->recursiveSplit($pattern, $str, $str, 0, $user_id);
				if($eval->type != "user_id" && $eval->type != "name" && $eval->type != "nationality" && $eval->type != "profile" && $eval->type != "application") {
					foreach ($this->_averages[$applicant->user] as $key => $value) {
						//$fx = min(1, max(0, ($value-$this->_min[$key])/($this->_max[$key]-$this->_min[$key])));
						$str = str_replace("[".$key."]", $value, $str);
					}
					$grade = !empty($view_calc)?$str:substr(@eval($str), 0, 6);
				} else 
					$grade = !empty($view_calc)?$str:substr(@eval($str), 0, 6);
				$this->_averages[$applicant->user][$eval->type] = $grade;
				
				if($eval->type == "application")
					$grade_appli = $grade;
				else if ($eval->type == "overall")
					$grade_over = $grade;
				else	
					$eval_list[$eval->type] = $grade;		
			}
			$eval_list["application"] = $grade_appli;
			$eval_list["overall"] = $grade_over;
			$eval_list['final_grade']=$applicant->Final_grade;
			$eval_list['row_id']=$applicant->id;
			$eval_list['to_enhance'] = $applicant->to_enhance;
			$eval_list['comment'] = $applicant->comment;
			
			$eval_lists[]=$eval_list;
		}
		if(!empty($eval_lists))
			$this->_applicants=$eval_lists;
		//die(print_r(count($this->_applicants)));
	}
	
	function getUsers()
	{	
		// Lets load the data if it doesn't already exist
		$this->_buildQuery();
		return $this->_buildContentOrderBy();
	} 
	
	//Statut user (dalué ou non)
	function getUsersStatut(){
		$query = 'SELECT count(ee.student_id), ee.student_id
					FROM #__emundus_declaration ed
					LEFT JOIN #__emundus_users eu ON eu.user_id = ed.user
					LEFT JOIN #__emundus_evaluation ee ON ee.student_id = ed.user
					WHERE edvalidated = 1
					AND eu.schoolyear ='. $this->getCampaign();
	}
	
	function array_delete_key($array,$search) {
		$temp = array();
		foreach($array as $key => $value) {
		 if($value!=$search) $temp[$key] = $value;
		}
		return $temp;
	}

	function getEvaluators(){
		$db =& JFactory::getDBO();
		$query = 'SELECT u.id, u.name
		FROM #__users u, #__emundus_users_profiles eup , #__emundus_setup_profiles esp 
		WHERE u.id=eup.user_id AND esp.id=eup.profile_id AND esp.is_evaluator=1';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getApplicants(){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label
		FROM #__emundus_setup_profiles esp 
		WHERE esp.published =1';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	function getProfiles(){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getProfilesByIDs($ids){
		$db =& JFactory::getDBO();
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
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.evaluation_start, esp.evaluation_end, caag.lft
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.acl_aro_groups=19';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getGroups()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esg.id, esg.label  
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1 
		ORDER BY esg.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	
	function getUsersGroups()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id  
		FROM #__emundus_groups eg';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getAuthorUsers()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT u.id, u.gid, u.name 
		FROM #__users u  
		WHERE u.gid=19';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getMobility()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esm.id, esm.label, esm.value
		FROM #__emundus_setup_mobility esm 
		ORDER BY ordering';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getElements(){
		$db =& JFactory::getDBO();
		$query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id as group_id, groupe.label AS group_label,  
			INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label 
				FROM #__fabrik_elements element 
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id 
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id 
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id 
				INNER JOIN #__menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
				WHERE tab.published = 1 
				AND (tab.created_by_alias = "form" OR tab.created_by_alias = "comment")
					AND element.published=1 
					AND element.hidden=0 
					AND element.label!=" " 
					AND element.label!=""  
				ORDER BY menu.ordering, formgroup.ordering, groupe.id, element.ordering';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	function getCriterias()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT name, label  
			FROM #__fabrik_elements element 
			WHERE element.published=1 AND element.hidden=0 
			AND element.label!=" " AND element.label!="" 
			AND element.plugin="fabrikradiobutton" AND element.group_id=41 
			order by ordering';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function getCriteriasList()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT name, label  
			FROM #__fabrik_elements element 
			WHERE element.published=1 AND element.hidden=0 
			AND element.label!=" " AND element.label!="" 
			AND element.plugin="fabrikradiobutton" AND element.group_id=41 
			order by ordering';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getSchoolyear()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT schoolyear FROM #__emundus_setup_profiles WHERE published=1';
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function getTotal()
  {
        // Load the content if it doesn't already exist
      	if (empty($this->_total)) {
  			$this->_total = count($this->_applicants); 
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

	function getPublished(){
		//id des profiles published
		$db =& JFactory::getDBO();
		$query = 'SELECT id FROM #__emundus_setup_profiles WHERE published =1';
		$db->setQuery($query);
		$eval = $db->loadResultArray();
		return $eval;
	}
	
	function getTableColumns(){
		$db =& JFactory::getDBO();
		$query = 'SELECT name, label FROM #__fabrik_elements WHERE group_id=41';
		$db->setQuery( $query );
		return $db->loadObjectList('name');
	}
	
	function getSchoolyears(){
		$db =& JFactory::getDBO();
		$query = 'SELECT DISTINCT(schoolyear) as schoolyear
			FROM #__emundus_users 
			WHERE schoolyear is not null AND schoolyear != "" 
			ORDER BY schoolyear';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

}
?>