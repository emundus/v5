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
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
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
		
		$schoolyears = $this->get('Schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
		$profiles = $this->get('Profiles');
		$this->assignRef('profiles', $profiles);
		
		$groups = $this->get('Groups');
		$this->assignRef('groups', $groups);
		
		$campaigns = $this->get('Campaigns');
		$this->assignRef('campaigns', $campaigns);
		
	/*	$newsletter = $this->get('Newsletter');
		$this->assignRef('newsletter', $newsletter);
	*/	
		$groups_eval = $this->get('GroupsEval');
		$this->assignRef('groups_eval', $groups_eval);
		
		$groupEvalWithId = $this->get('GroupEvalWithId');
		$this->assignRef('groupEvalWithId', $groupEvalWithId);
		
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
		
		$menu = JSite::getMenu()->getActive();
		$access =! empty($menu)?$menu->access : 0;
		$state			= EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)  ? '' : NULL;
		$filts_details	= array('profile_users'			=> $state,
								'evaluator_group'	=> $state,
								'schoolyear'		=> $state,
								'campaigns'			=> $state,
								'finalgrade'		=> $state,
								'newsletter'		=> $state,
								'spam_suspect'		=> $state,
								'not_adv_filter'	=> $state,
								);
		$filts_options 	= array('profile_users'		=> NULL,
							  	'evaluator_group'	=> NULL,
							  	'schoolyear'		=> NULL,
							  	'campaigns'			=> NULL,
							  	'finalgrade'		=> NULL,
							  	'newsletter'		=> NULL,
							  	'spam_suspect'		=> NULL,
							  	'not_adv_filter'	=> NULL,
								);
		$filters = EmundusHelperFilters::createFilterBlock($filts_details, $filts_options, array());
		$this->assignRef('filters', $filters);
		unset($options);
		
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form		= $this->get('Form');
		$this->assignRef('form', $form);
		
		parent::display($tpl);
    }
}
?>