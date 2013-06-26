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
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
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

        $menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		
		if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id)) die("ACCESS_DENIED");
		
		$aid = JRequest::getVar('aid', null, 'GET', 'none', 0);
		$student = JFactory::getUser($aid);

		$this->assignRef('student', $student);

		$profile = JUserHelper::getProfile($aid);
		$this->assignRef('profile', $profile->emundus_profile);

		$application = $this->getModel('application');

		$details_id = "82, 87, 89"; // list of Fabrik elements ID
		$userDetails = $application->getApplicantDetails($aid, $details_id);
		$this->assignRef('userDetails', $userDetails);

		$infos = array('#__emundus_uploads.filename', '#__users.email', '#__emundus_setup_profiles.label as profile', '#__emundus_personal_detail.gender');
		$userInformations = $application->getApplicantInfos($aid, $infos);
		$this->assignRef('userInformations', $userInformations);
		
		$userCampaigns = $application->getUserCampaigns($aid);
		$this->assignRef('userCampaigns', $userCampaigns);
		
		$userAttachements = $application->getUserAttachements($aid);
		$this->assignRef('userAttachements', $userAttachements);
		
		$userComments = $application->getUsersComments($aid);
		$this->assignRef('userComments', $userComments);
		
        parent::display($tpl);
    }
}
?>