<?php
/**
 * @package    Joomla
 * @subpackage emundus
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
		// require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
		
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
		
		$aid = JRequest::getVar('sid', null, 'GET', 'none', 0);
		$student = JFactory::getUser($aid);

		$this->assignRef('student', $student);
		$this->assignRef('current_user', $this->_user);


		$profile = JUserHelper::getProfile($aid);
		$this->assignRef('profile', $profile->emundus_profile);

		$application = $this->getModel('application');

		$details_id = "82, 87, 89"; // list of Fabrik elements ID
		$userDetails = $application->getApplicantDetails($aid, $details_id);
		$this->assignRef('userDetails', $userDetails);

		$infos = array('#__emundus_uploads.filename', '#__users.email', '#__emundus_setup_profiles.label as profile', '#__emundus_personal_detail.gender', '#__emundus_personal_detail.birth_date as birthdate');
		$userInformations = $application->getApplicantInfos($aid, $infos);
		$this->assignRef('userInformations', $userInformations);
		
		$userCampaigns = $application->getUserCampaigns($aid);
		$this->assignRef('userCampaigns', $userCampaigns);
		
		$userAttachments = $application->getUserAttachments($aid);
		$this->assignRef('userAttachments', $userAttachments);
		
		$userComments = $application->getUsersComments($aid);
		$this->assignRef('userComments', $userComments);

		$formsProgress = $application->getFormsProgress($aid, 9);
		$this->assignRef('formsProgress', $formsProgress);

		$attachmentsProgress = $application->getAttachmentsProgress($aid, 9);
		$this->assignRef('attachmentsProgress', $attachmentsProgress);

		$logged = $application->getlogged($aid);
		$this->assignRef('logged', $logged);

		$forms = $application->getforms($aid);
		$this->assignRef('forms', $forms);
		
		$emailFrom = $application->getEmailFrom($aid);
		$this->assignRef('emailFrom', $emailFrom);
		
		$emailTo = $application->getEmailTo($aid);
		$this->assignRef('emailTo', $emailTo);

		//var_dump($logged);
        parent::display($tpl);
    }
}
?>