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
// include the file css
/**
 * HTML View class for the Jcrm Component
 *
 * @package		Joomla
 * @subpackage	Jcrm
 */
class JcrmViewAddressbook extends JView
{

		
	function display($tpl = null){
		
	   // JHTML::_('behavior.mootools');	
	    JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip');
		JHTML::stylesheet('jcrm.css', 'media/com_jcrm/css/');
		JHTML::stylesheet('template.css', 'components/com_jcrm/views/addressbook/tmpl/');

		// Gets the data array of jcrmaccounts
		$data = $this->get('accounts');
		$this->assignRef('data', $data);
		
		// Gets the data array of jcrmcontacts
		$data_cont= $this->get('contact');
		$this->assignRef('data_cont', $data_cont);
		
		// Gets the data array of jcrmcontacts
		$data_contactslist= $this->get('contactslist');
		$this->assignRef('data_contactslist', $data_contactslist);
		
		// Gets the data array of jcrmaccounts
		$data_child= $this->get('child');
		$this->assignRef('data_child', $data_child);
		
		// Gets the params from the URL
	    $this->id_cont=JRequest::getInt('id_cont',0);
		$this->id_acct=JRequest::getInt('id_acct',0);
		$this->page=JRequest::getInt('limitstart',0);
		$this->task=JRequest::getWord('task',0);
		
		// Save the accounts
		/*$save_account = $this->save_account;
		$this->assignRef("save_account",$save_account);*/
		
		// Gets the pages of jcrmaccounts
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
		
		// Gets the pages of jcrmcontacts
		$pagination_contacts = $this->get('pagination_contacts');
		$this->assignRef('pagination_contacts', $pagination_contacts);
		
		$app = JFactory::getApplication();
		// Define the dafault order field
		$default_order_field = 'name';
		$default_order_field_contacts = 'last_name';
		$lists['order_Dir'] = $app->getUserStateFromRequest('com_jcrmfilter_order_Dir', 'filter_order_Dir', 'ASC');
		$lists['order'] = $app->getUserStateFromRequest('com_jcrmfilter_order', 'filter_order', $default_order_field);
		
		// Gets the data of search
		$this->search_acct=JRequest::getVar('search','');
		$this->search_cont=JRequest::getVar('search_cont','');
		$this->search_elements=JRequest::getVar('elements','');
		
		//Gets the filter order	
		$lists['order_Dir_cont'] = $app->getUserStateFromRequest('com_jcrmfilter_order_Dir', 'filter_order_Dir_cont', 'ASC');
		$lists['order_cont'] = $app->getUserStateFromRequest('com_jcrmfilter_order', 'filter_order_cont', $default_order_field_contacts);
		$this->assignRef('lists', $lists);
		
		parent::display($tpl);
	}
}
?>
