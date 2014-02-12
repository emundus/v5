<?php
/**
 * Jcrm View for com_jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 * D¨¦cision Publique
 * Created with Marco's Component Creator for Joomla! 1.5
 * http://www.mmleoni.net/joomla-component-builder
 *
 */


defined('_JEXEC') or die('Restricted access'); 
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Jcrm Component
 *
 * @package	Joomla
 * @subpackage	Jcrm
 */
class JcrmViewContact_form extends JView
{


	function display($tpl = null)
	{
		JHTML::_('behavior.modal'); 
		JHTML::_('behavior.mootools');
		JHTML::stylesheet('autosuggest_inquisitor.css', JURI::Base().'media/com_jcrm/css/');
		//JHTML::stylesheet('light2.css', 'templates/rt_afterburner_j15/css/');
		JHTML::script('bsn.AutoSuggest_2.1.3_comp.js', JURI::Base().'media/com_jcrm/js/', false);

		// Gets the params from URL
        $this->id_cont=JRequest::getVar('id_cont', null, 'get', 'INT', JREQUEST_NOTRIM);
		$this->id_ref=JRequest::getVar('id_ref',null,'get');
		$this->id_account=JRequest::getVar('id_acct',null,'get');
		
		// Gets the data array of jcrmcontacts
		$data_cont= $this->get('Data_cont');
		$this->assignRef('data_cont',$data_cont);
		
		// Gets the id account
		$id_acct= $this->get('id_acct');
		$this->assignRef('id_acct', $id_acct);
		
		// Gets the data array of emundus countries
		$country= $this->get('Country');
		$this->assignRef('country', $country);
		
		// Gets the user
		 $user=JFactory::getUser();
		 $this->assignRef('user', $user);
		 
		 
		// Gets the params of contacts from URL
		$this->fn=JRequest::getVar("fn",null,"get");
		$this->ln=JRequest::getVar("ln",null,"get");
		$this->email=JRequest::getVar("email",null,"get");
		$this->on=JRequest::getVar("on",null,"get");
		
		
		parent::display($tpl);
	}
}
?>
