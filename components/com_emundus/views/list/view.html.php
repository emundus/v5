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
jimport( 'joomla.utilities.date' );
JHTML::addIncludePath(JPATH_COMPONENT.DS.'helpers');
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewList extends JView
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
		$document =& JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."components/com_emundus/style/emundus.css" );
		//$allowed = array("Super Users", "Administrator", "Editor");
		
		$menu = JSite::getMenu();
		$current_menu  = $menu->getActive();
		$menu_params = $menu->getParams($current_menu->id);
		
		$blocks_list = explode(',', $menu_params->get('em_blocks_names'));
		
		$filter_comment = JRequest::getVar('comments', null, 'POST', 'none', 0);
		// Starting a session.
		$session =& JFactory::getSession();
		if(empty($filter_comment) && $session->has( 'comments' )) $filter_comment = $session->get( 'comments' );
		
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) {
			die("You are not allowed to access to this page.");
		}
		
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip'); 
		JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
		JHTML::stylesheet( 'menu_style.css', JURI::Base().'media/com_emundus/css/' );
		
		//$isallowed = EmundusHelperAccess::isAllowed($this->_user->usertype,$allowed);
		//$this->assignRef( 'isallowed', $isallowed );

		//Filters
		
/*		$filts = array('profile', 'evaluator', 'evaluator_group', 'schoolyear', 'missing_doc', 'complete', 'finalgrade', 'validate', 'other');*/
		$tables 		= explode(',', $menu_params->get('em_tables_id'));
		$filts_names 	= explode(',', $menu_params->get('em_filters_names'));
		$filts_values 	= explode(',', $menu_params->get('em_filters_values'));
		$filts_types 	= explode(',', $menu_params->get('em_filters_options'));
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
		
		//Comment checkbox
		$show_comments = EmundusHelperList::createShowCommentBlock();
		$this->assignRef('show_comments', $show_comments);
		
		//User list
		if (($users =& $this->get('Users')) === false){
			JController::setRedirect('index.php?');
		exit();
		}
		$this->assignRef( 'users', $users );
		
		//Check rights
		/*$rights	= $menu_params->get('em_groups');
		$accessibility = false;
		foreach ($this->_user->groups as $group)
			if ($rights == $group)
				$accessibility = true;
		if ($accessibility === false) die("Can not reach this page : Permission denied");*/
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die("Can not reach this page : Permission denied");
		}
		
		//Call the state object 
		if (($state =& $this->get( 'state' )) === false)
			JController::setRedirect('index.php?');
		// Get the values from the state object that were inserted in the model's construct function 
		$lists['order_Dir']				= $state->get( 'filter_order_Dir' );
		$lists['order']					= $state->get( 'filter_order' );
		$lists['schoolyears']			= $state->get( 'schoolyears' );
		$lists['elements']				= $state->get( 'elements' );
		$lists['elements_values']		= $state->get( 'elements_values' );
		$lists['elements_other']		= $state->get( 'elements_other' );
		$lists['elements_values_other']	= $state->get( 'elements_values_other' );
		$lists['finalgrade']			= $state->get( 'finalgrade' );
		$lists['s']						= $state->get( 's' );
		$lists['groups']				= $state->get( 'groups' );
		$lists['user']					= $state->get( 'user' );
		$lists['profile']				= $state->get( 'profile' );
		$lists['missing_doc']			= $state->get( 'missing_doc' );
		$lists['complete']				= $state->get( 'complete' );
		$lists['validate']				= $state->get( 'validate' );
		
        $this->assignRef( 'lists', $lists );
		
        $pagination =& $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
		
		$current_schoolyear =& implode(', ',$this->get('CurrentCampaign'));
		$this->assignRef( 'current_schoolyear', $current_schoolyear );
		
		//Export
		$options = array('zip', 'xls');
		if($this->_user->profile!=16)
			$export_icones =& EmundusHelperExport::export_icones($options);
		$this->assignRef('export_icones', $export_icones);
		unset($options);
		
		$user =& JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		//Administrative validation
		if (EmundusHelperAccess::isAllowedAccessLevel($user->id,$access) && in_array('batch', $blocks_list)) $batch = EmundusHelperList::createBatchBlock();
		else $batch = '';
		$this->assignRef('batch', $batch);
		
		//Evaluators
		$evaluators = EmundusHelperFilters::getEvaluators();
		$this->assignRef( 'evaluators', $evaluators );
		$groups = EmundusHelperFilters::getGroups();
		$this->assignRef( 'groups', $groups );
		if(EmundusHelperAccess::isAllowedAccessLevel($user->id,$access) && in_array('evaluator', $blocks_list)) {
			if($this->_user->profile!=16) $affectEval =& EmundusHelperList::affectEvaluators();
		}
		else $affectEval = '';
		$this->assignRef('affectEval', $affectEval);
		
		//Statut
		$options = array('incomplete');
		if (in_array('incomplete', $blocks_list))
			$incomplete = EmundusHelperList::createApplicationStatutblock($options);
		else $incomplete = '';
        $this->assignRef('incomplete', $incomplete);
		$options = array('complete');
		if (in_array('complete', $blocks_list))
			$complete = EmundusHelperList::createApplicationStatutblock($options);
		else $complete = '';
        $this->assignRef('complete', $complete);
		unset($options);
		
		//Email
		if(EmundusHelperAccess::isAllowedAccessLevel($user->id,$access) && in_array('email_evaluator', $blocks_list)){
			if($this->_user->profile!=16){
				$options = array('default', 'custom');
				$email_evaluator =& EmundusHelperEmails::createEmailBlock($options);
			}
		}
		else $email_evaluator = '';
		$this->assignRef('email_evaluator', $email_evaluator);
		if(EmundusHelperAccess::isAllowedAccessLevel($user->id,$access) && in_array('email_applicant', $blocks_list)){
			if($this->_user->profile!=16){
				$options = array('applicants','default');
				$email_applicant =& EmundusHelperEmails::createEmailBlock($options);
				unset($options);
			}
		}
		else $email_applicant = '';
		$this->assignRef('email_applicant', $email_applicant);		
		
		//List
		$options = array('checkbox', 'gender', 'details');
		$actions =& EmundusHelperList::createActionsBlock($users, $options);
		$this->assignRef('actions', $actions);
		unset($options); 
		
		//Profile
		$profile = EmundusHelperList::createProfileBlock($users,'profile');
		$this->assignRef('profile', $profile);
		
		//Application comments
		$options = array('evaluator', 'date', 'reason', 'comment');
		$app_comments = EmundusHelperList::createApplicationCommentBlock($users,$options);
		if($filter_comment == 1)
			$this->assignRef('app_comments', $app_comments);
		
		// Columns
		$appl_cols =& $this->get('ApplicantColumns');
		$filter_cols =& $this->get('SelectList'); 
		
		if($filter_comment == 1)
			$filter_cols[] = array('name' =>'application_comments', 'label'=>'APPLICATION_COMMENTS');
		
		$header_values = EmundusHelperList::aggregation($appl_cols, $filter_cols);
		$this->assignRef( 'header_values', $header_values );
		
		// Javascript
        JHTML::script( 'joomla.javascript.js', JURI::Base().'includes/js/' );
		$onSubmitForm =& EmundusHelperJavascript::onSubmitForm();
		$this->assignRef('onSubmitForm', $onSubmitForm);
		$addElement =& EmundusHelperJavascript::addElement();
		$this->assignRef('addElement', $addElement);
		$addElementFinalGrade =& EmundusHelperJavascript::addElementFinalGrade($tables);
		$this->assignRef('addElementFinalGrade', $addElementFinalGrade);
		$delayAct =& EmundusHelperJavascript::delayAct();
		$this->assignRef('delayAct', $delayAct);
		
		parent::display($tpl);
    }
}
?>