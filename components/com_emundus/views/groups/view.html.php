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
 
class EmundusViewGroups extends JView
{
    function display($tpl = null){
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");
		
		$applicantsProfiles =& $this->get('ApplicantsProfiles');
		$this->assignRef('applicantsProfiles', $applicantsProfiles);
		
		$elements =& $this->get('Elements');
		$this->assignRef('elements', $elements);
		
		$evalUsers =& $this->get('Evaluators');
		$this->assignRef('evalUsers', $evalUsers);
		
		$groups =& $this->get('Groups');
		$this->assignRef('groups', $groups);
		
		$groups_eval =& $this->get('GroupsEval');
		$this->assignRef('groups_eval', $groups_eval);
		
	   	$pagination =& $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
		
		$profiles =& $this->get('AuthorProfiles');
		$this->assignRef('profiles', $profiles);
		
		$users =& $this->get('Users');
		$this->assignRef('users', $users);
		
		$users_groups =& $this->get('UsersGroups');
		$this->assignRef('users_groups', $users_groups);
       
	   	$schoolyears =& $this->get('schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
		/* Call the state object */
		$state =& $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		
		parent::display($tpl);
    }
}
?>