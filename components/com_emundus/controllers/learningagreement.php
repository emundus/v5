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
class EmundusControllerLearningAgreement extends JController {

	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'learningAgreement';
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
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		$student_id = JRequest::getVar('student_id', null, 'POST', 'none', 0);
		//die(print_r($ids));
		$db->setQuery('DELETE FROM `#__emundus_learning_agreement` WHERE user_id='.$student_id);
		$db->Query() or die($db->getErrorMsg());
		foreach($ids as $id) {
			$query = 'INSERT INTO `#__emundus_learning_agreement` (`user_id`, `teacher_id`, `teaching_unity_id`)
						VALUES ('.$student_id.', '.$user->id.', '.$id.')';
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
		}
		$this->setRedirect('index.php?option=com_emundus&view=learningagreement&student_id='.$student_id.'&action=DONE&tmpl=component');
	}
	
	////// VALIDATE LEARNING AGREEMENT ///////////////////
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
		$this->setRedirect('index.php?option=com_emundus&view=learningagreement&student_id='.$student_id.'&action=DONE&tmpl=component');
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
		$this->setRedirect('index.php?option=com_emundus&view=learningagreement&student_id='.$student_id.'&action=DONE&tmpl=component');
	}
	
} //END CLASS
?>