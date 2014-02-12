<?php
/**
 * Jcrm View for com_jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 * Décision Publique
 * Created with Marco's Component Creator for Joomla! 1.5
 * http://www.mmleoni.net/joomla-component-builder
 *
 */


jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Jcrm Component
 *
 * @package	Joomla
 * @subpackage	Jcrm
 */
class JcrmViewJcrmdeleted extends JView
{

	function display($tpl = null)
	{
	
		// Gets the data array of jcrmaccounts
		$data = $this->get('Data');
		$this->assignRef('data', $data);
		
		// Gets the data array of jcrmcontacts
		$data_cont = $this->get('Cont');
		$this->assignRef('data_cont', $data_cont);

        
		parent::display($tpl);
	}
}
?>
