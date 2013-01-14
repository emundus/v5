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
 
class EmundusViewEval_auto extends JView
{
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		
		$current_user =& JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Publisher", "Editor", "Author", "Observator");
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) die("You are not allowed to access to this page.");
		
		//require_once(JPATH_COMPONENT.DS.'models'.DS.'check.php');
		//$model=new EmundusModelCheck;
				
		$elements =& $this->get('Elements');
		$this->assignRef('elements', $elements);
		
		$evalUsers =& $this->get('Evaluators');
		$this->assignRef('evalUsers', $evalUsers);
		
		$groups =& $this->get('Groups');
		$this->assignRef('groups', $groups);
		
		$groups_eval =& $this->get('GroupsEval');
		$this->assignRef('groups_eval', $groups_eval);
		
		$profiles =& $this->get('Profiles');
		$this->assignRef('profiles', $profiles);
		
		$published =& $this->get('Published');
		$this->assignRef('published',$published);
		
		$schoolyears =& $this->get('schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
		$schoolyear =& $this->get('Schoolyear');
		$this->assignRef('schoolyear', $schoolyear);
		
		$table_name =& $this->get('TableColumns');
		$this->assignRef('table_name',$table_name);
		
		$users=& $this->get('Users');
		$this->assignRef( 'users', $users );
		
		$users_groups =& $this->get('UsersGroups');
		$this->assignRef('users_groups', $users_groups);
		
		$applicants =& $this->get('Applicants');
		$this->assignRef('applicants', $applicants);
		
		$pagination =& $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
		
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