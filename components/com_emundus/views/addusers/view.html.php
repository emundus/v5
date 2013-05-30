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
 
class EmundusViewAddusers extends JView
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
		//$menu=JSite::getMenu()->getActive();
		//$access=!empty($menu)?$menu->access : 0;
		if(!EmundusHelperAccess::isAdministrator($this->_user->id) && !EmundusHelperAccess::isPartner($this->_user->id) && !EmundusHelperAccess::isCoordinator($this->_user->id)) {
			die("You are not allowed to access to this page.");
		}
		
		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
		$model = new EmundusModelUsers(); 
		
		$profiles =& $model->getProfiles();
		$this->assignRef('profiles',$profiles);
		
		$universities =& $model->getUniversities();
		$this->assignRef('universities',$universities);
		
		$groups =& $model->getGroups();
		$this->assignRef('groups',$groups);
				
		unset($model);
		/* Call the state object */
		$state =& $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */

		
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form		= $this->get('Form');
		$this->assignRef('form', $form);
		
		parent::display($tpl);
    }
}
?>