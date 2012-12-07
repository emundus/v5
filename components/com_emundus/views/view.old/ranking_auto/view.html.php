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
 
class EmundusViewRanking_auto extends JView
{
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");
		
		//require_once(JPATH_COMPONENT.DS.'models'.DS.'check.php');
		//$model=new EmundusModelCheck;

		$elements =& $this->get('Elements');
		$this->assignRef('elements', $elements);
		
		$users=& $this->get('Users');
		$this->assignRef( 'users', $users );
		
		$evalUsers =& $this->get('Evaluators');
		$this->assignRef('evalUsers', $evalUsers);
		
		$groups =& $this->get('Groups');
		$this->assignRef('groups', $groups);
		
		$groups_eval =& $this->get('GroupsEval');
		$this->assignRef('groups_eval', $groups_eval);
		
		$users_groups =& $this->get('UsersGroups');
		$this->assignRef('users_groups', $users_groups);
		
		$applicants_nm=& $this->get('Minmax');
		$this->assignRef('applicants_nm', $applicants_nm);
		
		$profiles =& $this->get('Profiles');
		$this->assignRef('profiles', $profiles);
		
		$applicants =& $this->get('Applicants');
		$this->assignRef('applicants', $applicants);

        $pagination =& $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
		
		$table_name =& $this->get('TableColumns');
		$this->assignRef('table_name',$table_name);
		
		$schoolyears =& $this->get('schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
		/* Call the state object */
		$state =& $this->get( 'state' );

		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		
		$this->assignRef('schoolyear', $schoolyear);
		$schoolyear =& $this->get('Schoolyear');

		parent::display($tpl);
    }
}
?>