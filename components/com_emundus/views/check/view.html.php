<?php
 /**
 * @package     Joomla
 * @subpackage  eMundus
 * @link       http://www.decisionpublique.fr
 * @copyright   Copyright (C) 2013 eMundus. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewCheck extends JView
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {
		$document = JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
		
		$menu = JSite::getMenu();
		$current_menu  = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);
		$access = !empty($current_menu)?$current_menu->access:0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)) die(JText::_('ACCESS_DENIED'));

		$users = $this->get('Users');
		
		//Filters
		$tables 		= explode(',', $menu_params->get('em_tables_id'));
		$filts_names 	= explode(',', $menu_params->get('em_filters_names'));
		$filts_values	= explode(',', $menu_params->get('em_filters_values'));
		$filts_types  	= explode(',', $menu_params->get('em_filters_options'));
		$filts_details 	= array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL,
								'adv_filter'		=> '');
		$filts_options 	= array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL,
								'adv_filter'		=> NULL);
		$validate_id  	= explode(',', $menu_params->get('em_validate_id'));
		$actions  		= explode(',', $menu_params->get('em_actions'));
		$i = 0;
		foreach ($filts_names as $filt_name) {
			if (array_key_exists($i, $filts_values))
				$filts_details[$filt_name] = $filts_values[$i];
			else
				$filts_details[$filt_name] = '';
			if (array_key_exists($i, $filts_types))
				$filts_options[$filt_name] = $filts_types[$i];
			else
				$filts_options[$filt_name] = '';
			$i++;
		}
		unset($filts_names); unset($filts_values); unset($filts_types);
		
		$filters = EmundusHelperFilters::createFilterBlock($filts_details, $filts_options, $tables);
		$this->assignRef('filters', $filters);
		unset($filts_details); unset($filts_options);

		//Export
		$options = array('zip', 'xls');
		if($this->_user->profile!=16) // devra être remplacé par un paramétrage au niveau du menu
			$export_icones = EmundusHelperExport::export_icones($options);
		$this->assignRef('export_icones', $export_icones);
		unset($options);
		
		$applicantsProfiles = $this->get('ApplicantsProfiles');
		$elements = $this->get('Elements');
        $elements = $this->get('Elements');
		$profiles = $this->get('Profiles');
		
		/* Call the state object */
		$state = $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		
		$schoolyears = $state->schoolyears;
		$this->assignRef('schoolyears', $schoolyears);
        
		$this->assignRef( 'lists', $lists );
		
		$this->assignRef('users', $users);
		$this->assignRef('applicantsProfiles', $applicantsProfiles);
		$this->assignRef('elements', $elements);
		$pagination = $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
		$this->assignRef('profiles', $profiles);
		
		$batch = EmundusHelperList::createBatchBlock();
		$this->assignRef('batch', $batch);
		
		$options = array('incomplete');
		$statut = EmundusHelperList::createApplicationStatutblock($options);
        $this->assignRef('statut', $statut);
		//die(print_r($users));
		//List
		//$options = array('checkbox', 'photo', 'gender', 'details', 'upload', 'attachments', 'forms');
		$actions = EmundusHelperList::createActionsBlock($users, $actions);
		$this->assignRef('actions', $actions);
		
		//$options = array('jos_emundus_declaration.validated', 'jos_emundus_declaration.certified_copies_received', 'jos_emundus_declaration.languages_result_received'); 
		$validate = EmundusHelperList::createValidateBlock($users, $validate_id);
		$this->assignRef('validate', $validate);
		
		$param= array('submitted'		=> 1,
					  'year'			=> implode('","', $schoolyears));
		$campaigns = EmundusHelperList::createApplicantsCampaignsBlock($users, $param); 
		$this->assignRef('campaigns', $campaigns);

		//Email
		if(EmundusHelperAccess::isAdministrator($this->_user->id) || EmundusHelperAccess::isCoordinator($this->_user->id)) {
			if($this->_user->profile!=16){
				$options = array('applicants');
				$email_applicant = EmundusHelperEmails::createEmailBlock($options);
				unset($options);
			}
		}
		else $email_applicant = '';
		$this->assignRef('email_applicant', $email_applicant);	
		
		// Javascript
		$onSubmitForm = EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);
		$addElement = EmundusHelperJavascript::addElement();
		$this->assignRef('addElement', $addElement);
		$addElementOther = EmundusHelperJavascript::addElementOther($tables);
		$this->assignRef('addElementOther', $addElementOther);
		$delayAct = EmundusHelperJavascript::delayAct();
		$this->assignRef('delayAct', $delayAct);
		
		unset($options);
		
		parent::display($tpl);
    }
}
?>