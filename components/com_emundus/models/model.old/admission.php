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
 
class EmundusModelAdmission extends JModel
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
		
		$filter_order     = $mainframe->getUserStateFromRequest(  $option.'filter_order', 'filter_order', 'lastname', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
 
        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}
	
	
	function _buildContentOrderBy()
	{
        global $option;

		$mainframe =& JFactory::getApplication();
 
                $orderby = '';
                $filter_order     = $this->getState('filter_order');
                $filter_order_Dir = $this->getState('filter_order_Dir');
				
                $can_be_ordering = array ('user', 'id', 'lastname', 'nationality', 'time_date', 'engaged', 'schoolyear', 'profile', 'LabelProfile');
                /* Error handling is never a bad thing*/
                if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
                        $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
                }
 
                return $orderby;
	}


	function _buildQuery()
	{
		$current_user =& JFactory::getUser();
		$uid = JRequest::getVar('user', null, 'POST', 'none', 0);
		$pid = JRequest::getVar('profile', null, 'POST', 'none', 0);
		$learning_agreement = JRequest::getVar('las', null, 'POST', 'none', 0);
		$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);
		$quick_search = JRequest::getVar('s', null, 'POST', 'none', 0);
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$engaged = JRequest::getVar('engaged', null, 'POST', 'none', 0);
		$selected = JRequest::getVar( 'checkselected', array(), 'post', 'array' );
		
		// Starting a session.
		$session =& JFactory::getSession();
		
		if(empty($profile) && $session->has( 'profile' )) $profile = $session->get( 'profile' );
		if(empty($quick_search) && $session->has( 'quick_search' )) $quick_search = $session->get( 'quick_search' );
		if(empty($schoolyears) && $session->has( 'schoolyears' )) $schoolyears = $session->get( 'schoolyears' );
		if(empty($engaged) && $session->has( 'engaged' )) $engaged = $session->get( 'engaged' );
		if(empty($selected) && $session->has( 'selected' )) $selected = $session->get( 'selected' );
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');

		if (count($search)==0) {
			$search = $s_elements;
			$search_values = $s_elements_values;
		}
		
		$query = 'SELECT efg.student_id as user, a.filename AS avatar, eu.firstname, eu.lastname, u.email, epd.gender, epd.nationality, eu.profile, eu.schoolyear,  
				efg.info1, efg.info2, efg.engaged, efg.result_for, esp.label as LabelProfile, 
					u.id, u.name,  u.username, u.usertype, u.registerDate, u.block, efg.time_date
					FROM #__emundus_final_grade efg 
					LEFT JOIN #__emundus_users AS eu ON efg.student_id = eu.user_id 
					LEFT JOIN #__emundus_uploads AS a ON a.user_id = eu.user_id AND a.attachment_id = '.EMUNDUS_PHOTO_AID.'
					LEFT JOIN #__emundus_personal_detail AS epd ON efg.student_id = epd.user  
					LEFT JOIN #__users AS u ON efg.student_id = u.id 
					LEFT JOIN #__emundus_learning_agreement_status AS elas ON elas.user_id = epd.user
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = efg.result_for '; 
		if((isset($uid) && !empty($uid)) || $current_user->profile > 5) 
			$query .= 'LEFT JOIN #__emundus_confirmed_applicants AS eca ON eca.user_id = epd.user ';
			
			if(!empty($search)) {
				$i = 0;
				foreach ($search as $s) {
					$tab = explode('.', $s);
					//die(print_r($tab));
					if (count($tab)>1) {
						$query .= 'LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=efg.student_id ';
						$i++;
					}
				}
			
			}
			
			$query .= 'WHERE (efg.final_grade!=2) ';// final_grade=accepted
			$and = true;
			if ($current_user->profile > 5) 
				$query .= 'AND eca.evaluator_id='.$current_user->id.' ';
			
			$no_filter = array("Super Administrator", "Administrator");
			if (!in_array($current_user->usertype, $no_filter)) 
				$query .= ' AND eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$this->getProfileAcces($current_user->id)).')) ';
			
			if(!empty($search)) {
				$i = 0;
				foreach ($search as $s) {
					$tab = explode('.', $s);
					if (count($tab)>1) {
						if($and) $query .= ' AND ';
						else { $and = true; $query .='WHERE '; }
						$query .= 'j'.$i.'.'.$tab[1].' like "%'.$search_values[$i].'%"';
						$i++;
					}
				}
			
			}
			if(isset($quick_search) && !empty($quick_search)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				if (is_numeric ($quick_search)) 
					$query.= 'u.id = '.$quick_search.' ';
				else
					$query.= '(eu.lastname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
							OR eu.firstname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
							OR u.email LIKE "%'.mysql_real_escape_string($quick_search).'%" 
							OR u.username LIKE "%'.mysql_real_escape_string($quick_search).'%" )';
			}

			if(isset($uid) && !empty($uid)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= 'eca.evaluator_id='.mysql_real_escape_string($uid);
			}
			if(isset($pid) && !empty($pid)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= '(eu.profile='.mysql_real_escape_string($pid).' OR efg.result_for='.$pid.')';
			}
			if(isset($learning_agreement) &&  !empty($learning_agreement)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= $learning_agreement==1?'elas.status=1':'elas.status IS NULL';
			}
			if(isset($schoolyears) &&  !empty($schoolyears)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= 'eu.schoolyear="'.mysql_real_escape_string($schoolyears).'"';
			}
			if(isset($engaged) &&  !empty($engaged)) {
				//die(print_r($engaged));
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				if($engaged == 1){
					$query.= 'efg.engaged="'.mysql_real_escape_string($engaged).'"';
				}elseif($engaged == 2){
					$query.= 'efg.engaged=""';
				}
			}
			if(isset($selected) &&  !empty($selected)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= 'eu.profile=8';
			}
			
		//echo '<br>'.str_replace("#_", "jos", $query);

		return $query;
	} 
	
	function getUsers(){
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		
		//echo '<br>'.str_replace("#_", "jos", $query);
		$this->_applicants = $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
		return $this->_applicants;
		
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
	
	function getProfileAcces($user){
		$db =& JFactory::getDBO();
		$query = 'SELECT esg.profile_id FROM #__emundus_setup_groups as esg
					LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
					WHERE esg.published=1 AND eg.user_id='.$user;
		$db->setQuery( $query );
		$profiles = $db->loadResultArray();
		
		return $profiles;
	}
	
	function getProfiles(){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		ORDER BY esp.id, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getProfilesByIDs($ids){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.id IN ('.implode(',',$ids).')
		ORDER BY esp.id, caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getAuthorProfiles(){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.evaluation_start, esp.evaluation_end, caag.lft
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.acl_aro_groups=19';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getEditorProfiles(){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.evaluation_start, esp.evaluation_end, caag.lft
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.acl_aro_groups=20';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getRegistredProfiles(){
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.evaluation_start, esp.evaluation_end, caag.lft
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		WHERE esp.acl_aro_groups=18 
		ORDER BY esp.id';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getUsersGroups(){
		$db =& JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id  
		FROM #__emundus_groups eg';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getAuthorUsers(){
		$db =& JFactory::getDBO();
		$query = 'SELECT u.id, u.gid, u.name 
		FROM #__users u 
		WHERE u.gid=19';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	function getEditorUsers(){
		$db =& JFactory::getDBO();
		$query = 'SELECT u.id, u.gid, u.name 
		FROM #__users u 
		WHERE u.gid=20';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getElements(){
		$db =& JFactory::getDBO();
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
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
  }
  
  function getLearningAgreementStatus(){
	$query = 'SELECT id, user_id, teacher_id, status FROM #__emundus_learning_agreement_status';
	$this->_db->setQuery( $query );
	return $this->_db->loadObjectList('user_id');
  }

}
?>