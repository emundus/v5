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
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		//print_r($access);
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)){
			die("You are not allowed to access to this page.");
		}
		$p = JRequest::getVar('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0);
		$model = $this->getModel();
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