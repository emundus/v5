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
 
class EmundusViewCandidate_evaluate extends JView
{
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");

		$users =& $this->get('Users');
		$applicantsProfiles =& $this->get('ApplicantsProfiles');
		$elements =& $this->get('Elements');
        $pagination =& $this->get('Pagination');
		
		/* Call the state object */
		$state =& $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		//$lists['limit']     = $state->get( 'limit' );
		//die('-->'.$lists['limit']);
        $this->assignRef( 'lists', $lists );
		
		$this->assignRef('users', $users);
		$this->assignRef('applicantsProfiles', $applicantsProfiles);
		$this->assignRef('elements', $elements);
        $this->assignRef('pagination', $pagination);

		parent::display($tpl);
    }
}
?>