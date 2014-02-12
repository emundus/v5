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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Jcrm Component
 *
 * @package	Joomla
 * @subpackage	Jcrm
 */
class JcrmViewOrganisation_form extends JView
{

	function display($tpl = null)
	{
		JHTML::_('behavior.modal');
		//JHTML::_('behavior.mootools');	
		JHTML::stylesheet('autosuggest_inquisitor.css', JURI::Base().'media/com_jcrm/css/');
		//JHTML::stylesheet('light2.css', 'templates/rt_afterburner_j15/css/');
		JHTML::script('bsn.AutoSuggest_2.1.3_comp.js', JURI::Base().'media/com_jcrm/js/', false);

		// Gets the params from URL
        $this->id_acct=JRequest::getVar('id_acct', null, 'get', 'INT', JREQUEST_NOTRIM);
		$this->id_ref=JRequest::getVar('id_ref', null, 'get');
		$this->i_referee=JRequest::getVar('i_referee', null, 'get');
		
		// Gets the data array of jcrmaccounts
		$data_acct= $this->get('Data_acct');
		$this->assignRef('data_acct',$data_acct);

		// Gets the data array of jcrmaccounts
		$data_references= $this->get('Data_references');
		$this->assignRef('data_references',$data_references);
		
		// Gets the name of jcrmaccount selected
		$accountname= $this->get('Accountname');
		$this->assignRef('accountname',$accountname);
		
		// Gets the data array of emundus countries
		$country= $this->get('Country');
		$this->assignRef('country', $country);
		
		// Gets the data array of jcrmcountries
		$countryjcrm= $this->get('CountryJcrm');
		$this->assignRef('countryjcrm', $countryjcrm);
	
		// Gets the user
		 $user=JFactory::getUser();
		 $this->assignRef('user', $user);
		 
		 // Gets the name of organisation of references from URL
		 $this->name=JRequest::getVar("name",null,"get");
		 
		parent::display($tpl);
	}
}
?>
