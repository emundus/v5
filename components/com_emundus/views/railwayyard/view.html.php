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
 
class EmundusViewRailwayyard extends JView
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
		
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		//$allowed = array("Super Users", "Administrator", "Publisher", "Editor");
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) die("You are not allowed to access to this page.");
		
		require_once(JPATH_COMPONENT.DS.'models'.DS.'check.php');
		$model=new EmundusModelCheck;
		
		$users = $this->get('Users');
        $elements = $this->get('Elements');
		/* Call the state object */
		$state = $this->get( 'state' );
		
		$elements = $this->get('Elements');
		
		$applicantsProfiles = $model->getApplicantsProfiles();

		$this->assignRef('groups', $groups);
		$this->assignRef('users_groups', $users_groups);
		
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		
		$schoolyears = $this->get('schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
        $this->assignRef( 'lists', $lists );
		$this->assignRef('profiles', $applicantsProfiles);
		$this->assignRef('elements', $elements);
		$this->assignRef('users', $users);
        $this->assignRef('pagination', $pagination);

		parent::display($tpl);
    }
}
?>