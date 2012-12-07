<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Decision Publique - Jonas Lerebours
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 

class EmundusViewProfile extends JView
{
    function display($tpl = null)
    {
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator");
		if (!in_array($current_user->usertype, $allowed)) {
			die("You are not allowed to access to this page.");
		}
		$p = JRequest::getVar('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0);
		$model = &$this->getModel();
		$profile = $model->getProfile($p);
		if($p < 7 && $p > 9) {
			die("This is not an applicant profile.");
		}
		$attachments = $model->getAttachments($p);
		$forms = $model->getForms($p);
		$this->assignRef('profile', $profile);
		$this->assignRef('forms', $forms);
		$this->assignRef('attachments', $attachments);
		parent::display($tpl);
    }
}
?>