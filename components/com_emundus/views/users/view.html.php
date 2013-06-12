<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
 */
 
class EmundusViewUsers extends JView
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
    function display($tpl = null)
    {
		//$menu=JSite::getMenu()->getActive();
		//$access=!empty($menu)?$menu->access : 0;
		if(!EmundusHelperAccess::isAdministrator($this->_user->id) && !EmundusHelperAccess::isPartner($this->_user->id) && !EmundusHelperAccess::isCoordinator($this->_user->id)) {
			die("You are not allowed to access to this page.");
		}
		$edit_profiles = $this->get('EditProfiles');
		$this->assignRef('edit_profiles',$edit_profiles);
		
		$schoolyear = $this->get('Schoolyear');
		$this->assignRef('schoolyear', $schoolyear);
		
		$schoolyears = $this->get('Schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
		$profiles = $this->get('Profiles');
		$this->assignRef('profiles', $profiles);
		
		$groups = $this->get('Groups');
		$this->assignRef('groups', $groups);
		
		$campaigns = $this->get('Campaigns');
		$this->assignRef('campaigns', $campaigns);
		
		$current_campaigns = $this->get('CurrentCampaigns');
		$this->assignRef('current_campaigns', $current_campaigns);
		
	/*	$newsletter = $this->get('Newsletter');
		$this->assignRef('newsletter', $newsletter);
	*/	
		$groups_eval = $this->get('GroupsEval');
		$this->assignRef('groups_eval', $groups_eval);
		
		$groupEvalWithId = $this->get('GroupEvalWithId');
		$this->assignRef('groupEvalWithId', $groupEvalWithId);
		
		$allGroupEval = $this->get('AllGroupsEval');
		$this->assignRef('allGroupEval', $allGroupEval);
		
		$users = $this->get('Users');
		$this->assignRef('users', $users);
		
		$users_groups = $this->get('UsersGroups');
		$this->assignRef('users_groups', $users_groups);
		
		$user_profiles = $this->get('UsersProfiles');
		$this->assignRef('user_profiles', $user_profiles);
		
		$universities = $this->get('Universities');
		$this->assignRef('universities', $universities);
        
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
		
		$options = array('xls');
		$export_icones = EmundusHelperExport::export_icones($options);
		$this->assignRef('export_icones', $export_icones);

		/* Call the state object */
		$state = $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		$this->assignRef( 'lists', $lists );
		
		@$this->assignRef('state_schoolyears', $state->get('schoolyears'));
		@$this->assignRef('state_current_l', $state->get('s'));
		@$this->assignRef('state_current_campaigns', $state->get('campaigns'));
		@$this->assignRef('state_current_groupEval', $state->get('groups_eval'));
		@$this->assignRef('state_spam_suspect', $state->get('spam_suspect'));
		@$this->assignRef('state_newsletter', $state->get('newsletter'));
		@$this->assignRef('state_current_p', $state->get('rowid'));
		@$this->assignRef('state_current_fg', $state->get('finalgrade'));
		
		
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form		= $this->get('Form');
		$this->assignRef('form', $form);
		
		parent::display($tpl);
    }
}
?>