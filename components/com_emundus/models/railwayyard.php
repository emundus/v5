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
 
class EmundusModelRailwayyard extends JModel
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

		$mainframe = JFactory::getApplication();
 
                $orderby = '';
                $filter_order     = $this->getState('filter_order');
                $filter_order_Dir = $this->getState('filter_order_Dir');
				
                /* Error handling is never a bad thing*/
				$can_be_ordering = array ('id', 'name');
                /* Error handling is never a bad thing*/
                if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
                        $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
                }
 
                return $orderby;
	}

	function getCampaign()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
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

	function _buildQuery()
	{
		$quick_search = JRequest::getVar('s', null, 'POST', 'none', 0);
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);
		$profile = JRequest::getVar('profile', null, 'POST', 'int', 0);
		// Starting a session.
		$session = JFactory::getSession();
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		if(empty($schoolyears) && $session->has( 'schoolyears' )) $schoolyears = $session->get( 'schoolyears' );
		
		$eMConfig = JComponentHelper::getParams('com_emundus');
		
		$user = JFactory::getUser();
		$query = 'SELECT ed.user, u.email, u.name,  eu.firstname, eu.lastname, eu.profile, epd.gender, efg.final_grade, efg.result_for 
					FROM #__emundus_declaration AS ed 
					INNER JOIN #__users AS u ON u.id = ed.user 
					LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = ed.user 
					LEFT JOIN #__emundus_users AS eu ON eu.user_id = ed.user 
					LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id = ed.user 
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id=eu.profile '; 
			
		if(!empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				$tab = explode('.', $s);
				//die(print_r($tab));
				if (count($tab)>1) {
					$query .= 'LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=ed.user ';
					$i++;
				}
			}
		}

			$query .= 'WHERE esp.published=1';
			if(empty($schoolyears)) $query .= ' AND ed.validated=1 AND eu.schoolyear like "%'.$this->getCampaign().'%"';	
			
			$and = true;
			
			if(!empty($profile)) {
				$user_profil=count($this->getApplicantsByProfile($profile))>0?implode(',',$this->getApplicantsByProfile($profile)):0;
				$query .= ' AND eu.user_id IN ('.$user_profil.')';
			}
			if($user->usertype != "Administrator"){
			$user_profilAccess=count($this->getProfileAcces($user->id))>0?implode(',',$this->getProfileAcces($user->id)):0;
				$query .= ' AND eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.$user_profilAccess.')) ';
			}
	
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
					$query.= 'u.id = '.$quick_search.' ';
				else
					$query.= '(eu.lastname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
							OR eu.firstname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
							OR u.email LIKE "%'.mysql_real_escape_string($quick_search).'%" 
							OR u.username LIKE "%'.mysql_real_escape_string($quick_search).'%" )';
			}
			if(isset($schoolyears) &&  !empty($schoolyears)) {
				$s=is_array($schoolyears)?implode(',',$schoolyears):$schoolyears;
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= 'eu.schoolyear="'.$s.'"';
			}
			
			//echo str_replace('#_','jos',$query);

		return $query;
	} 
	
	function getUsers()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		//echo str_replace('#_','jos',$query);
		return $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
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

	function getGroups()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esg.id, esg.label  
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1 
		ORDER BY esg.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	
	function getUsersGroups()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id  
		FROM #__emundus_groups eg';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getElements()
	{
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