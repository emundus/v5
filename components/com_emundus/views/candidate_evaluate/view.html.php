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
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {
		$document = JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
		
		//$current_user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Publisher", "Editor", "Author");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) die("You are not allowed to access to this page.");

		$users = $this->get('Users');
		$applicantsProfiles = $this->get('ApplicantsProfiles');
		$elements = $this->get('Elements');
        $elements = $this->get('Elements');
		
		/* Call the state object */
		$state = $this->get( 'state' );
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