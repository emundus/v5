<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Jonas Lerebours
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');

class EmundusViewTrombi extends JView
{
    function display($tpl = null)
    {
		$current_user =& JFactory::getUser();	
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");
		
		$schoolyears = & $this->get('Schoolyears');
		$profiles =& $this->get('Profiles');
		$users =& $this->get('Users');
		$this->assignRef('users', $users);
		$this->assignRef('profiles', $profiles);
		$this->assignRef('schoolyears', $schoolyears);
		parent::display($tpl);
		

    }
}
?>