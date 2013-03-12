<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
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
 
class EmundusViewIncomplete extends JView
{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );

		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access))  die("You are not allowed to access to this page.");

		$users =& $this->get('Users');
		
		//Filters
		$tables = array(); // 		= explode(',', $menu_params->get('em_tables_id'));
		$filts_names 	= array('profile', 'schoolyear', 'missing_doc', 'other');
		$filts_values = array(); // 	= explode(',', $menu_params->get('em_filters_values'));
		$filts_types = array(); // 	= explode(',', $menu_params->get('em_filters_options'));
		$filts_details 	= array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL);
		$filts_options 	= array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL);
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
		
		$filters =& EmundusHelperFilters::createFilterBlock($filts_details, $filts_options, $tables);
		$this->assignRef('filters', $filters);
		unset($filts_details); unset($filts_options);
		
		$applicantsProfiles =& $this->get('ApplicantsProfiles');
		$elements =& $this->get('Elements');
        $pagination =& $this->get('Pagination');
		
		/* Call the state object */
		$state =& $this->get( 'state' );
		/* Get the values from the state object that were inserted in the model's construct function */
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
		
		$schoolyears =& $this->get('schoolyears');
		$this->assignRef('schoolyears', $schoolyears);
		
        $this->assignRef( 'lists', $lists );
		
		$this->assignRef('users', $users);
		$this->assignRef('applicantsProfiles', $applicantsProfiles);
		$this->assignRef('elements', $elements);
        $this->assignRef('pagination', $pagination);
		
		$options = array('complete');
		$statut = EmundusHelperList::createApplicationStatutblock($options);
        $this->assignRef('statut', $statut);
		unset($options);
		
		//Email
		if(EmundusHelperAccess::isAdministrator($this->_user->id) || EmundusHelperAccess::isCoordinator($this->_user->id)) {
			if($this->_user->profile!=16){
				$options = array('applicants');
				$email_applicant =& EmundusHelperEmails::createEmailBlock($options);
				unset($options);
			}
		}
		else $email_applicant = '';
		$this->assignRef('email_applicant', $email_applicant);	
		
		// Javascript
        JHTML::script( 'joomla.javascript.js', JURI::Base().'includes/js/' );
		$onSubmitForm =& EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);
		$addElement =& EmundusHelperJavascript::addElement();
		$this->assignRef('addElement', $addElement);
		$delayAct =& EmundusHelperJavascript::delayAct();
		$this->assignRef('delayAct', $delayAct);
		
		parent::display($tpl);
    }
}
?>