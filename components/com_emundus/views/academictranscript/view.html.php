<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Decision Publique - Benjamin Rivalland
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 

class EmundusViewAcademicTranscript extends JView
{
    function display($tpl = null)
    {
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		if (!in_array($current_user->usertype, $allowed)) {
			die("You are not allowed to access to this page.");
		}
		
		$learning_units =& $this->get('StudentLearningUnits');

		$this->assignRef('learning_units', $learning_units);
		
		parent::display($tpl);
    }
}
?>