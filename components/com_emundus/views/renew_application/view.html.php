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
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );

		$current_user =& JFactory::getUser();
		$statut = $this->get('statut');
		$this->assignRef('statut', $statut);
		parent::display($tpl);
    }
}
?>