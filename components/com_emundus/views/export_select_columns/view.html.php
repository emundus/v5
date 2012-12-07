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
 
class EmundusViewExport_select_columns extends JView
{
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		
		$current_user =& JFactory::getUser();
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");
		
		$elements =& $this->get('Elements');
		$this->assignRef('elements', $elements);
		
		parent::display($tpl);
    }
}
?>