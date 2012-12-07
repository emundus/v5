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
 
class EmundusViewChecklist extends JView
{
    function display($tpl = null)
    {
		$forms = $this->get('FormsList');
		$attachments = $this->get('AttachmentsList');
		$sent = $this->get('sent');
		$greeting = $this->get('Greeting');
		$need = $this->get('Need');
		$instructions = $this->get('Instructions');
		$this->assignRef('title', $greeting->title);
		$this->assignRef('text', $greeting->text);
		$this->assignRef('need', $need);
		$this->assignRef('sent', $sent);
		$this->assignRef('forms', $forms);
		$this->assignRef('attachments', $attachments);
		$this->assignRef('instructions', $instructions);
		
		$result = $this->get('Result');
		$this->assignRef('result', $result);
		
		parent::display($tpl);
    }
}
?>