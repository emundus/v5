<?php
/**
 * @version		$Id: report.php 733 2009-10-03 brivalland $
 * @package		Reports
 * @copyright	(C) 2009 Décision Publique. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Application_form class
 * @package		Application_form
 */
class EmundusModelApplication_form extends JModel
{
    var $_user_data;
	var $_applicant_id;
	
	function getUser(){
		$user_id=JRequest::getVar('sid',null,'GET');
		$db=& JFactory::getDBO();
		if(empty($this->_user_data)){
		$query='SELECT u.id AS user_id, a.filename AS avatar, c.lastname, c.firstname, c.profile, p.label AS cb_profile, c.profile, c.schoolyear AS cb_schoolyear, u.id, u.registerDate, u.email, epd.gender, epd.nationality, epd.birth_date, efg.Final_grade, ed.user, ed.time_date FROM #__users AS u 
		LEFT JOIN #__emundus_users AS c ON c.user_id = u.id
		LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = u.id
		LEFT JOIN #__emundus_uploads AS a ON a.user_id=u.id AND a.attachment_id = 10
		LEFT JOIN #__emundus_setup_profiles AS p ON p.id = c.profile
		LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id
		LEFT JOIN #__emundus_final_grade AS efg ON efg.student_id = u.id
		WHERE u.id='.$user_id;
		$db->setQuery($query);
		$this->_user_data=$db->loadObject();
		}
		return $this->_user_data;	
	}
	
	function getCanEvaluate(){
		$db=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$user_id=JRequest::getVar('sid',null,'GET');
		
	    $query='SELECT count(ege.applicant_id) FROM 
				#__emundus_groups_eval AS ege 
				LEFT JOIN #__emundus_declaration as ed ON ed.user=ege.applicant_id 
				where ed.validated=1 AND ed.user='.$user_id.' AND ((ege.user_id='.$user->id.' AND ege.applicant_id='.$user_id.') OR ege.group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.'))';
	    $db->setQuery($query);
		$canEvaluate=$db->loadResult();
		//die(str_replace('#_','jos',$query));
		return $canEvaluate>0?true:false;
	}
	
	function getAsBeenEvaluated(){
		$db=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$user_id=JRequest::getVar('sid',null,'GET');
	    $query='SELECT count(ee.student_id) FROM 
				#__emundus_evaluations AS ee 
				where ee.student_id='.$user->id;
	    $db->setQuery($query);
		$isEvaluated=$db->loadObjectList();
		return $isEvaluated>0?true:false;
	}
	
	function getComments(){ 
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();
		if(!EmundusHelperAccess::isAdministrator($user->get('id')) OR !EmundusHelperAccess::isCoordinator($user->get('id'))) {
			JError::raiseWarning('403', JText::_('RESTRICT_ACCES_ON_COMMENTS')); 
			return;
		}
		
		$comment = JRequest::getVar('comment', null, 'GET', 'none',0);
		$id = JRequest::getVar('sid', null, 'GET', 'none',0);
		
		$query = 'SELECT ec.id, ec.comment, ec.user_id, ec.date, u.name, ec.applicant_id
				FROM #__emundus_comments ec 
				LEFT JOIN #__users u ON u.id = ec.user_id 
				WHERE ec.applicant_id ='.$id.'
				ORDER BY ec.date DESC';
		$db->setQuery( $query );
		$comments = $db->loadObjectList();
        $tab = '<h4>Last comments</h4>';
        foreach($comments as $comment){
            $url = 'index.php?option=com_emundus&controller=application_form&task=delete_comment&comment_id='.$comment->id.'&uid='.$comment->user_id.'&tmpl=component';	
            $tab .= '<div id="delete_comment_'.$comment->id.'_'.$comment->applicant_id.'"><span><b>Sent by: '.$comment->name.' on: '.$comment->date.'</b></span><br />';
            $tab .= '<span><img src="images/cancel_f2.png" name="delete_comment" id="delete_comment_'.$comment->id.'" height="16px" onClick="delete_com('.$comment->applicant_id.','.$comment->id.',\''.$url.'\')" onMouseOver="this.style.cursor=\'pointer\'"/>'.$comment->comment.'</span></div><br />';
        }	
        return $tab;	
	}
}
