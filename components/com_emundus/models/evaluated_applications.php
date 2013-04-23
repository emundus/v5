<?php
/**
 * @version		$Id: report.php 733 2010-11-09 04:47:07Z brivalland $
 * @package		Emundus
 * @copyright	(C) 2008 - 2010 Décision Publique. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Reporting class
 * @package		Emundus
 */
class EmundusModelEvaluated_applications extends JModel
{
	
	var $_total = null;
	var $_pagination = null;
	
	
	
	function __construct()
	{
		parent::__construct();
		global $option;

		$mainframe =& JFactory::getApplication();
 
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		//die('--->'.$limit);
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
	
	function getCampaign()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}

	function _buildQuery(){
		$eMConfig =& JComponentHelper::getParams('com_emundus');
		$cesaa = $eMConfig->get('can_evaluators_see_all_applicants', '0');
		$user =& JFactory::getUser();
		$db = & JFactory::getDBO();
		
		$quick_search = JRequest::getVar('s', null, 'POST', 'none', 0);
		//$edit = JRequest::getVar('edit', 0, 'GET', 'none', 0);
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$nationality = JRequest::getVar('nationality', null, 'POST', 'none', 0);
		// Starting a session.
		$session =& JFactory::getSession();
		$s_elements = $session->get('s_elements');
		$s_elements_values = $session->get('s_elements_values');
		$final_grade=JRequest::getVar('Final_grade',null,'POST');
		
		$db->setQuery('SELECT element.name, average.coef FROM #__fabrik_elements AS element INNER JOIN #__emundus_setup_average AS average ON average.element_id = element.id');
		$elements = $db->loadObjectList();
		
		$elements_moyenne = array();
		$elements_moyenne_no_oral = array();
		$elements_moyenne_oral = array();
		$mult = 0;
		$mult_no_oral = 0;
		$mult_oral = 0;
		$quotient = $eMConfig->get('quotient', '20');
		
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
		//$elements_means_oral = '((ee.'.implode('+ ee.',$elements_means_oral).')/'.$mult_oral.')*'.$quotient;
		
		$query = 'SELECT ed.user, ed.time_date, a.filename AS avatar, c.lastname, c.firstname, p.label AS cb_profile, c.profile AS profile, c.schoolyear, u.id, u.name, u.registerDate, u.email, epd.gender, epd.nationality, ee.student_id, efg.Final_grade, ('.$elements_means.') AS moyenne ';
		
		if($cesaa == 0) {
			$query.= 'FROM #__emundus_evaluations AS ee
					LEFT JOIN #__emundus_declaration AS ed ON ed.user = ee.student_id
					LEFT JOIN #__users AS u ON u.id = ee.student_id
					LEFT JOIN #__emundus_users AS c ON c.user_id = ee.student_id
					LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = ee.student_id
					LEFT JOIN #__emundus_uploads AS a ON a.user_id=ee.student_id AND a.attachment_id= 10
					LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id = ee.student_id
					LEFT JOIN #__emundus_setup_profiles AS p ON p.id = c.profile ';
			if(!empty($search)) {
				$i = 0;
				foreach ($search as $s) {
					$tab = explode('.', $s);
					if (count($tab)>1) {
						$query .= 'LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=ed.user ';
						$i++;
					}
				}
			}
			$query .= ' WHERE ee.user='.$user->id.'';
		} else {
			$query.= 'FROM #__emundus_declaration AS ed
					LEFT JOIN #__users AS u ON u.id = ed.user
					LEFT JOIN #__emundus_users AS c ON c.user_id = ed.user
					LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = ed.user
					LEFT JOIN #__emundus_evaluations AS ee ON ee.user='.$user->id.' AND ee.student_id=ed.user
					LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id = ee.student_id
					LEFT JOIN #__emundus_setup_profiles AS p ON p.id = c.profile
					LEFT JOIN #__emundus_uploads AS a ON a.user_id = ed.user AND a.attachment_id='.EMUNDUS_PHOTO_AID.' ';
			if(!empty($search)) {
				$i = 0;
				foreach ($search as $s) {
					$tab = explode('.', $s);
					if (count($tab)>1) {
						$query .= 'LEFT JOIN '.$tab[0].' AS j'.$i.' ON j'.$i.'.user=ed.user ';
						$i++;
					}
				}
			}
			$query .= ' WHERE p.published=1 AND c.schoolyear like "%'.$this->getCampaign().'%" AND ed.user IN (Select student_id from #__emundus_evaluations where user='.$user->id.' AND student_id=ed.user)';
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
			$query .= ' AND ';
			if (is_numeric ($quick_search)) 
				$query.= 'u.id = '.$quick_search.' ';
			else
				$query.= '(eu.lastname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
						OR eu.firstname LIKE "%'.mysql_real_escape_string($quick_search).'%" 
						OR u.email LIKE "%'.mysql_real_escape_string($quick_search).'%" 
						OR u.username LIKE "%'.mysql_real_escape_string($quick_search).'%" )';
		}
		if($final_grade!=""){
			$query.=' AND efg.Final_grade="'.$final_grade.'"';
		}
		return $query;
	}
	
	function _buildContentOrderBy(){
	    $orderby = '';
		$filter_order     = $this->getState('filter_order');
       	$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array ('user', 'id', 'lastname', 'nationality', 'schoolyear', 'time_date');
        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
        	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        }
		
		return $orderby;
	
	}
	
	function getUsers(){
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
		//echo str_replace('#_','jos',$query);
		return $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));	
	
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
			WHERE tab.published = 1 AND profile.id=9 AND tab.created_by_alias = "form" AND element.published=1 AND element.hidden=0 AND element.label!=" " AND element.label!="" 
			ORDER BY menu.ordering, formgroup.ordering, element.ordering';
		$db->setQuery( $query );
		//die(print_r($db->loadObjectList('id')));
		return $db->loadObjectList('id');
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
	
	/**
	 * Custom report tasks
	 * @param string The task
	 */
	function tasker( $task = '' )
	{
		$tasker = new reportTasks();
		$tasker->performTask( $task );
		$tasker->redirect();
	}
}
