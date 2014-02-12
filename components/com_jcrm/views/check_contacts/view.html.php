<?php
/**
 * Jcrm View for com_jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 * Décision Publique http://www.decisionpublique.fr
 *
 */


jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Jcrm Component
 *
 * @package Joomla
 * @subpackage	Jcrm
 */
class JcrmViewCheck_contacts extends JView
{


	function display($tpl = null)
	{
		
		// Gets the data array of contacts from references
        $data_contacts = $this->get('contacts');
		$this->assignRef('data_contacts', $data_contacts);
		
		// Gets the words of all jcrmaccounts names
		$account_words= $this->get('AccountWords');
		$this->assignRef('account_words', $account_words);
		
		// Gets the user
		 $user=JFactory::getUser();
		 $this->assignRef('user', $user);
		 
		 // Gets the pages
		$pagination = $this->get('Pagination');
		$this->assignRef('pagination', $pagination);
		
		$state = $this->get( 'state' );
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
      
		$lists['search']=JRequest::getVar('search','');
		$lists['search_option']=JRequest::getVar('search_option','');
		$this->assignRef( 'lists', $lists );
		
		  
		parent::display($tpl);
	}
}
?>
