<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Decision Publique - Benjamin Rivalland
*/
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 

class EmundusViewLearningAgreement extends JView
{
    function display($tpl = null)
    {
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		if (!in_array($current_user->usertype, $allowed)) {
			die("You are not allowed to access to this page.");
		}
		
		$teaching_unity =& $this->get('TeachingUnity');
		$learning_agreement_status =& $this->get('LearningAgreementSatus');
		$incharge = & $this->get('PersonneInCharge');

		$this->assignRef('teaching_unity', $teaching_unity);
		$this->assignRef('learning_agreement_status', $learning_agreement_status);
		$this->assignRef('incharge', $incharge);
		
		parent::display($tpl);
    }
}
?>