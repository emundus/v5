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
jimport( 'joomla.utilities.date' );
 
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewEvaluation extends JView
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
	
	function display($tpl = null){
		//if (!EmundusHelperAccess::isAllowed($this->_user->usertype,array("Super Users", "Administrator", "Publisher", "Editor", "Author", "Observator"))) 
		//	die("You are not allowed to access to this page.");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)) die(JText::_('ACCESS_DENIED'));
		
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip'); 
		JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
		JHTML::stylesheet( 'menu_style.css', JURI::Base().'media/com_emundus/css/' );
		
		$eMConfig =& JComponentHelper::getParams('com_emundus');
		$eval_access = $eMConfig->get('can_evaluators_see_all_applicants', '0');
		$this->assignRef( 'eval_access', $eval_access );
		
		$multi_eval = $eMConfig->get('multi_eval', '0');
		$this->assignRef( 'multi_eval', $multi_eval );
		
		//$allowed = array("Super Users", "Administrator", "Editor");
		//$isallowed = EmundusHelperAccess::isAllowed($this->_user->usertype,$allowed);
		//$this->assignRef( 'isallowed', $isallowed );
		
		//Filters
		$evaluators = EmundusHelperFilters::getEvaluators();
		$this->assignRef( 'evaluators', $evaluators );
		
		$groups = EmundusHelperFilters::getGroups();
		$this->assignRef( 'groups', $groups );
		$profiles = EmundusHelperFilters::getApplicants();
		$this->assignRef( 'profiles', $profiles );
		
		$profiles_label = $this->get('Profiles');
		$this->assignRef( 'profiles_label', $profiles_label );

		$menu = JSite::getMenu()->getActive();
		$access =! empty($menu)?$menu->access : 0;
		$state			= EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access)  ? '' : NULL;
		$filts_details	= array(//'profile'			=> $state,
								'evaluator'			=> $state,
								'evaluator_group'	=> $state,
								'schoolyear'		=> $state,
								'campaign'			=> $state,
								'missing_doc'		=> $state,
								'complete'			=> NULL,
								'finalgrade'		=> $state,
								'validate'			=> NULL,
								'other'				=> $state);
		$filts_options 	= array(//'profile'			=> NULL,
							  	'evaluator'			=> NULL,
							  	'evaluator_group'	=> NULL,
							  	'schoolyear'		=> NULL,
							  	'campaign'			=> NULL,
							  	//'missing_doc'		=> NULL,
							  	'complete'			=> NULL,
							  	'finalgrade'		=> NULL,
							  	'validate'			=> NULL,
							  	'other'				=> NULL);
		/*$filts_details 	= array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'campaign'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL);
		$filts_options 	= array('profile'			=> NULL,
							   'evaluator'			=> NULL,
							   'evaluator_group'	=> NULL,
							   'schoolyear'			=> NULL,
							   'campaign'			=> NULL,
							   'missing_doc'		=> NULL,
							   'complete'			=> NULL,
							   'finalgrade'			=> NULL,
							   'validate'			=> NULL,
							   'other'				=> NULL);*/
		/*if($isallowed)
			$options = array('profile', 'evaluator', 'evaluator_group', 'schoolyear', 'finalgrade', 'missing_doc');
		else 
			$options = array();*/
		$filters =& EmundusHelperFilters::createFilterBlock($filts_details, $filts_options, array());
		$this->assignRef('filters', $filters);
		unset($options);
		
		$elements = EmundusHelperFilters::getElements();
		$this->assignRef( 'elements', $elements );
		
		// Columns
		$appl_cols =& $this->get('ApplicantColumns');
		
		$filter_cols =& $this->get('SelectList'); 
		
		$eval_cols =& $this->get('EvalColumns');
		$eval_cols['evaluator'] = array('name' =>'evaluator', 'label'=>JText::_('EVALUATOR')); 
		
		$rank_cols =& $this->get('RankingColumns');


		if ( EmundusHelperAccess::isAdministrator($this->_user->id) || EmundusHelperAccess::isCoordinator($this->_user->id) )
			$rank_cols[] = array('name' =>'assoc_evaluators', 'label'=>JText::_('ASSOCIATED_EVAL')); 
			
		$header_values = EmundusHelperList::aggregation($appl_cols, $filter_cols, $eval_cols, $rank_cols);
		$this->assignRef( 'header_values', $header_values );
		
		// Current call
		$current_schoolyear =& implode(', ',$this->get('CurrentCampaign'));
		$this->assignRef( 'current_schoolyear', $current_schoolyear );
		
		//Call the state object 
		$state =& $this->get( 'state' );
		// Get the values from the state object that were inserted in the model's construct function 
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		
		//List
		$users =& $this->get('Users');
		$this->assignRef('users', $users);

		$pagination =& $this->get('Pagination');
        $this->assignRef('pagination', $pagination);

		//Evaluation
		if($this->_user->profile==16)
			$options = array('view');
		else
			$options = array('view', 'add', 'edit', 'delete');
		$evaluation =& EmundusHelperList::createEvaluationBlock($users, $options);
		$this->assignRef('evaluation', $evaluation);
		unset($options);
		
		$published =& $this->get('Published');
		$this->assignRef('published', $published);
		
		$users_groups =& EmundusHelperList::getUsersGroups();
		$this->assignRef('users_groups', $users_groups);
		
		$options = array('checkbox', 'gender', 'details', 'evaluation');
		$actions =& EmundusHelperList::createActionsBlock($users, $options);
		$this->assignRef('actions', $actions);
		unset($options);
		
		//Evaluators
		$evalUsers =& $this->get('AuthorUsers');
		$this->assignRef('evalUsers', $evalUsers);
		
		$options = array('delete');

		if (EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, $access))
			$evaluator =& EmundusHelperList::createEvaluatorBlock($users, $options);
		$this->assignRef('evaluator', $evaluator);
		unset($options);

		if ( EmundusHelperAccess::isAdministrator($this->_user->id) || 
			 EmundusHelperAccess::isCoordinator($this->_user->id) ) 
				$affectEval =& EmundusHelperList::affectEvaluators();
		$this->assignRef('affectEval', $affectEval);

		//Comments
		$comment =& EmundusHelperList::createCommentBlock($users);
		$this->assignRef('comment', $comment);
		
		//Export
		$options = array('zip', 'xls');
		if($this->_user->profile!=16)
			$export_icones =& EmundusHelperExport::export_icones($options);
		$this->assignRef('export_icones', $export_icones);
		unset($options);
		
		//Emails
		if ( EmundusHelperAccess::isAdministrator($this->_user->id) || 
			 EmundusHelperAccess::isCoordinator($this->_user->id) ) {
				$options = array('default', 'custom');
				$email =& EmundusHelperEmails::createEmailBlock($options);
				unset($options);

		}
		$this->assignRef('email', $email);
		
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