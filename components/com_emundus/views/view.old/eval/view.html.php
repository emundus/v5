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
 
class EmundusViewEval extends JView
{
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		require_once(JPATH_COMPONENT.DS.'models'.DS.'check.php');
		$model=new EmundusModelCheck;
		$applicantsProfiles =& $model->getApplicantsProfiles();
		
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");
		
		//$profiles =& $this->get('Profiles');
		$profiles =& $this->get('AuthorProfiles');
		$evalUsers =& $this->get('AuthorUsers');
		$users =& $this->get('Users');
        $pagination =& $this->get('Pagination');
		/* Call the state object */
		$state =& $this->get( 'state' );
		
		$groups =& $this->get('Groups');
		$users_groups =& $this->get('UsersGroups');
		$criterias =& $this->get('Criterias');

		$this->assignRef('groups', $groups);
		$this->assignRef('users_groups', $users_groups);
		
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		$this->assignRef('profiles', $profiles);
		$this->assignRef('evalUsers', $evalUsers);
		$this->assignRef('users', $users);
		$this->assignRef('applicantsProfiles', $applicantsProfiles);
        $this->assignRef('pagination', $pagination);
		$this->assignRef('criterias', $criterias);

		parent::display($tpl);
    }
}
?>