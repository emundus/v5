<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 
/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerAcademicTranscript extends JController {

	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'academicTranscript';
			JRequest::setVar('view', $default );
		}
		$user =& JFactory::getUser();
		if ($this->ACR($allowed)) {
			parent::display();
		}
    }
	
	
	function ACR($allowed){
		$user =& JFactory::getUser();
		if (!in_array($user->usertype, $allowed)) {
			$this->setRedirect('index.php', JText::_('You are not allowed to access to this page.'), 'error');
			return false;
		}
		return true;
	}

	////// UPDATE LEARNING AGREEMENT ///////////////////
	function update() {
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		$this->ACR($allowed);
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$student_id = JRequest::getVar('student_id', null, 'POST', 'none', 0);
		$grades = JRequest::getVar('grade', null, 'POST', 'array', 0);
		$obtained = JRequest::getVar('obtained', null, 'POST', 'array', 0);
		//die(print_r($obtained));
		
		$db->setQuery('DELETE FROM `#__emundus_academic_transcript` WHERE student_id='.$student_id);
		$db->Query() or die($db->getErrorMsg());
		
		$i=0;
		foreach($grades as $grade) {
			$tui = explode('___', key($grades));
			
			if (isset($grade) && $grade != '') {
				$o = $obtained[$tui[0]]=='on'?1:0;
				$query = 'INSERT INTO `#__emundus_academic_transcript` (`code`, `teaching_unit_id`, `student_id`, `user_id`, `grade`, `obtained`)
							VALUES ("'.$tui[1].'", '.$tui[0].', '.$student_id.', '.$user->id.', '.$grade.', '.$o.')';
				$db->setQuery($query);
				$db->Query() or die($db->getErrorMsg());
			}
			next($grades); 
			$i++;
		}
		$this->setRedirect('index.php?option=com_emundus&view=academictranscript&student_id='.$student_id.'&tmpl=component&action=DONE');
	}
	
/*	////// VALIDATE LEARNING AGREEMENT ///////////////////
	function validate() {
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		$this->ACR($allowed);
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$student_id = JRequest::getVar('student_id', null, 'POST', 'none', 0);
		//UPDATE Selected units
		$db->setQuery('DELETE FROM `#__emundus_learning_agreement` WHERE user_id='.$student_id);
		$db->Query() or die($db->getErrorMsg());
		foreach($ids as $id) {
			$query = 'INSERT INTO `#__emundus_learning_agreement` (`user_id`, `teacher_id`, `teaching_unity_id`)
						VALUES ('.$student_id.', '.$user->id.', '.$id.')';
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
		}
		//VALIDATE Learning agreement
		$query = 'INSERT INTO `#__emundus_learning_agreement_status` (`user_id`, `teacher_id`, `status`)
						VALUES ('.$student_id.', '.$user->id.', 1)';
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
		$this->setRedirect('index.php?option=com_emundus&view=learningAgreement&action=DONE&tmpl=component');
	}

	////// UNVALIDATE LEARNING AGREEMENT ///////////////////
	function unvalidate() {
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		$this->ACR($allowed);
		$db =& JFactory::getDBO();
		$student_id = JRequest::getVar('student_id', null, 'POST', 'none', 0);
		$query = 'DELETE FROM `#__emundus_learning_agreement_status` WHERE `user_id`='.$student_id.' AND `status`=1';
		$db->setQuery($query);
		$db->Query() or die($db->getErrorMsg());
		$this->setRedirect('index.php?option=com_emundus&view=learningAgreement&action=DONE&tmpl=component');
	}*/
	
} //END CLASS
?>