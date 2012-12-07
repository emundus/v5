<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
*/

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
jimport( 'joomla.application.component.view');

class EmundusViewEmailalert extends JView{

	function display($tpl = null)
	{	
		if (!$this->get('Key')) die("You are not allowed to access to this page.");
		
		$users = $this->get('mailtosend');
		$this->assignRef('users', $users);
		
		parent::display($tpl);
	}
}
?>