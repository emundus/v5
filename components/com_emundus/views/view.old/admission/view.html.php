<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Décision Publique - Benjamin Rivalland
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewAdmission extends JView
{
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		require_once(JPATH_COMPONENT.DS.'models'.DS.'check.php');
		$model=new EmundusModelCheck;
		
		$current_user =& JFactory::getUser();
		//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_('You are not allowed to access to this page...').$current_user->usertype);
			//return false;
		}
		
		$learning_agreement_status =& $this->get('LearningAgreementStatus');
		$schoolyears =& $this->get('schoolyears');
		$profiles_id =& $this->get('RegistredProfiles');
		$profiles =& $this->get('EditorProfiles');
		$evalUsers =& $this->get('EditorUsers');
		$users =& $this->get('Users');
		$elements =& $this->get('Elements');
        $pagination =& $this->get('Pagination');
		/* Call the state object */
		$state =& $this->get( 'state' );
		
		$applicantsProfiles =& $model->getApplicantsProfiles();
		
		$groups =& $this->get('Groups');
		$groups_eval =& $this->get('GroupsEval');
		$users_groups =& $this->get('UsersGroups');
		
		$this->assignRef('learning_agreement_status', $learning_agreement_status);
		$this->assignRef('schoolyears', $schoolyears);
		$this->assignRef('groups', $groups);
		$this->assignRef('groups_eval', $groups_eval);
		$this->assignRef('users_groups', $users_groups);
		
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		$this->assignRef('profiles_id', $profiles_id);
		$this->assignRef('profiles', $profiles);
		$this->assignRef('evalUsers', $evalUsers);
		$this->assignRef('users', $users);
		$this->assignRef('applicantsProfiles', $applicantsProfiles);
		$this->assignRef('elements', $elements);
        $this->assignRef('pagination', $pagination);

//die(print_r($schoolyears));
		parent::display($tpl);
    }
}
?>