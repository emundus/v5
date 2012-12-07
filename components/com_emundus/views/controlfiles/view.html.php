<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland - http://www.decisionpublique.fr
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');

class EmundusViewControlfiles extends JView
{
    function display($tpl = null)
    {
		$current_user =& JFactory::getUser();	
		$allowed = array("Super Administrator", "Administrator");
		if (!in_array($current_user->usertype, $allowed)) die("You are not allowed to access to this page.");

		$files =& $this->get('Files');
		$listFiles =& $this->get('listFiles');
		
		$this->assignRef('files', $files); 
		$this->assignRef('listFiles', $listFiles); 
        
		$total =& $this->get('Total'); 
		
		/* Call the state object */
		$state =& $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		
		$this->assignRef( 'lists', $lists );
        $this->assignRef('total', $total);
	
		parent::display($tpl);
    }
}
?>