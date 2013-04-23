<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/renew_application.php
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
 
class EmundusViewRenew_application extends JView
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}

    function display($tpl = null)
    {
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if ( !EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access) && !EmundusHelperAccess::isApplicant($this->_user->id) ) die("You are not allowed to access to this page.");
		
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );

		$current_user =& JFactory::getUser();
		$statut = $this->get('statut');
		$this->assignRef('statut', $statut);
		parent::display($tpl);
    }
}
?>