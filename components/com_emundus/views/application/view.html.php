<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewApplication extends JView{
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
    function display($tpl = null){	
	
        $document = JFactory::getDocument();
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );

        //$current_user = JFactory::getUser();
        //$allowed = array("Super Users", "Administrator", "Publisher", "Editor", "Author");
        $menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		// if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)) die("ACCESS_DENIED");
				
		$informations = array('lastname',
							'firstname',
							'gender',
							'email',
							'nationality',
							'birthdate',
							'profile',
							'registerDate',
							'photo'
							);
		$this->assignRef('informations', $informations);
		$user_id = "1526";
		$this->assignRef('user_id', $user_id);
		$application = $this->getModel('application');
		$userInformations = $application->getUserInformations($user_id,$informations);
		$this->assignRef('userInformations', $userInformations);
		
		$userCampaigns = $application->getUserCampaigns($user_id);
		$this->assignRef('userCampaigns', $userCampaigns);
		
        parent::display($tpl);
    }
}
?>