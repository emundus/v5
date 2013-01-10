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
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {
		//$current_user =& JFactory::getUser();
		//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) {
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