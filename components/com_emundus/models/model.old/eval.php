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
 
class EmundusModelEval extends JModel
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
				
		 $can_be_ordering = array ('id', 'name', 'lastname', 'evaluator_lastname', 'mean', 'mean_oral', 'mean_application', 'time_date', 'criteria01', 'criteria02', 'criteria03', 'criteria04', 'criteria05','oral','oral_french','oral_english');
         /* Error handling is never a bad thing*/
         if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
         	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
         }
 
        return $orderby;
	}

	function getCampaign()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT schoolyear FROM #__emundus_setup_profiles published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}

	function _buildQuery()
	{
		$quick_search = JRequest::getVar('s', null, 'POST', 'none', 0);
		$profile = JRequest::getVar('profile', null, 'POST', 'int', 0);
		$eval_in_progress = JRequest::getVar('eval_in_progress', null, 'POST', 'int', 0);
		$eMConfig =& JComponentHelper::getParams('com_emundus');
		
		/////// MOYENNE  ////////////////////////////
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT element.name, average.coef  
		FROM #__fabrik_elements AS element 
		INNER JOIN #__emundus_setup_average AS average ON average.element_id = element.id');
		$elements = $db->loadObjectList();
		$elements_moyenne = array();
		$elements_moyenne_no_oral = array();
		$elements_moyenne_oral = array();
		$mult = 0;
		$mult_no_oral = 0;
		$mult_oral = 0;
		$quotient = $eMConfig->get('quotient', '20');
		/*foreach($elements_moyenne as &$element) {
			$mult += $element->coef;
			$query = 'SELECT sub_values FROM #__fabrik_elements WHERE name like "'.$element->name.'"';
			$db->setQuery($query);
			$row_value = $db->loadRow();
			$ptmp = explode('|', $row_value[0]);
			
			$element = 'CAST(REPLACE(ee.'.$element->name.',",", ".") AS DECIMAL(6,3))/'.number_format(max($ptmp),3).'*'.number_format($element->coef,3);
		}
		$elements_moyenne = '(('.implode('+',$elements_moyenne).')/'.$mult.')*'.$quotient;
		*/
		$test = 0;
		$test_f = 0;
		$test_t = 0;
		foreach($elements as $element) {
			$query = 'SELECT sub_values FROM #__fabrik_elements WHERE name like "'.$element->name.'"';
			$db->setQuery($query);
			$row_value = $db->loadRow();
			$ptmp = explode('|', $row_value[0]);
			if(strrpos($element->name, "oral") === false) {
				$mult_no_oral += $element->coef;
				$elements_means_application[] = $element->name.'/'.max($ptmp).'*'.$element->coef;
			} else {
				$mult_oral += $element->coef;
				$elements_means_oral[] = $element->name.'/'.max($ptmp).'*'.$element->coef;
			}
			$mult += $element->coef;
			$elements_means[] = $element->name.'/'.max($ptmp).'*'.$element->coef;
		}
		$elements_means = '((ee.'.implode('+ ee.',$elements_means).')/'.$mult.')*'.$quotient;
		$elements_means_application = '((ee.'.implode('+ ee.',$elements_means_application).')/'.$mult_no_oral.')*'.$quotient;
		$elements_means_oral = '((ee.'.implode('+ ee.',$elements_means_oral).')/'.$mult_oral.')*'.$quotient;
		
		//////////////////////////////////////////////
		$user =& JFactory::getUser();
		$query = 'SELECT ee.id as rowid, u.id, u.email, u.name,  eu.firstname, eu.lastname, epd.gender, epd.nationality, ed.country, 
					ee.time_date, ee.user as evaluator_id, ee.student_id, ee.'.implode(', ee.',$this->getCriteriasList()).', 
					eue.firstname AS evaluator_firstname, eue.lastname evaluator_lastname, 
						 ('.$elements_means.') AS mean,
						 ('.$elements_means_application.') AS mean_application,
						 ('.$elements_means_oral.') AS mean_oral
					FROM #__emundus_evaluations AS ee 
					INNER JOIN #__users AS u ON u.id = ee.student_id 
					LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id=u.id 
					LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = ee.student_id 
					LEFT JOIN #__emundus_users AS eu ON eu.user_id = ee.student_id 
					LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id
					LEFT JOIN #__emundus_users AS eue ON eue.user_id = ee.user '; 
 					//echo str_replace('#_','jos',$query);
			$query .= 'WHERE eu.schoolyear like "%'.$this->getCampaign().'%"';
			$and = true;
			
			$no_filter = array("Super Users", "Administrator");
			if (!in_array($user->usertype, $no_filter)) 
				$query .= ' AND eu.user_id IN (select user_id from #__emundus_users_profiles where profile_id in ('.implode(',',$this->getProfileAcces($user->id)).')) ';
				
			if(!empty($profile)) 
				$query .= ' AND eu.profile = '.$profile.' OR efg.result_for='.$profile;
				
			if(!empty($eval_in_progress)) 
				$query .= $eval_in_progress==1?' AND ('.$elements_means_application.'>0 AND '.$elements_means_oral.'>0)':' AND ('.$elements_means_application.'=0 OR '.$elements_means_oral.'=0)';

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
	
	function getProfiles()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getProfilesByIDs($ids)
	{
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
	
	function getElements()
	{
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
?>