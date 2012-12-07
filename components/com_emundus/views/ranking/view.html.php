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
 
class EmundusViewRanking extends JView
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
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
		$allowed = array("Super Administrator", "Administrator", "Editor");
		
		if (!EmundusHelperAccess::isAllowed($this->_user->usertype,array("Super Administrator", "Administrator", "Publisher", "Editor", "Author", "Observator")))
			die("You are not allowed to access to this page.");
		
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip'); 
		JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );
		JHTML::stylesheet( 'menu_style.css', JURI::Base().'media/com_emundus/css/' );
		
		$isallowed = EmundusHelperAccess::isAllowed($this->_user->usertype,$allowed);
		$this->assignRef( 'isallowed', $isallowed );
		
		$tables 		= array(41);
		$filts_details	= array('profile'			=> '',
								'evaluator'			=> '',
								'evaluator_group'	=> '',
								'schoolyear'		=> '',
								'missing_doc'		=> NULL,
								'complete'			=> NULL,
								'finalgrade'		=> '',
								'validate'			=> NULL,
								'other'				=> '');
		$filts_options 	= array('profile'			=> NULL,
							  	'evaluator'			=> NULL,
							  	'evaluator_group'	=> NULL,
							  	'schoolyear'		=> NULL,
							  	'missing_doc'		=> NULL,
							  	'complete'			=> NULL,
							  	'finalgrade'		=> NULL,
							  	'validate'			=> NULL,
							  	'other'				=> NULL);
		//$filts = array('profile', 'evaluator', 'evaluator_group', 'schoolyear', 'finalgrade', 'other');
		$filters =& EmundusHelperFilters::createFilterBlock($filts_details, $filts_options, $tables);
		$this->assignRef('filters', $filters);
		
		$users=& $this->get('Users');
		$this->assignRef( 'users', $users );

		$engaged =& EmundusHelperList::getEngaged($users);
		$this->assignRef( 'engaged', $engaged );
		
		//Call the state object 
		$state =& $this->get( 'state' );
		// Get the values from the state object that were inserted in the model's construct function 
		$lists['order_Dir'] = $state->get( 'filter_order_Dir' );
		$lists['order']     = $state->get( 'filter_order' );
        $this->assignRef( 'lists', $lists );
		
        $pagination =& $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
		
		$current_schoolyear =& implode(', ',$this->get('CurrentCampaign'));
		$this->assignRef( 'current_schoolyear', $current_schoolyear );
		
		// Columns
		$appl_cols =& $this->get('ApplicantColumns');
		$filter_cols =& $this->get('SelectList'); 
		$eval_cols =& $this->get('EvalColumns');
		$rank_cols =& $this->get('RankingColumns');
			
		$header_values = EmundusHelperList::aggregation($appl_cols, $filter_cols, $eval_cols, $rank_cols);
		$this->assignRef( 'header_values', $header_values );
		
		//Export
		$options = array('zip', 'xls');
		if($this->_user->profile!=16)
			$export_icones =& EmundusHelperExport::export_icones($options);
		$this->assignRef('export_icones', $export_icones);
		unset($options);
		
		//Email
		if($isallowed){
			if($this->_user->profile!=16){
				$options = array('applicants');
				$email =& EmundusHelperEmails::createEmailBlock($options);
				unset($options);
			}
		}
		$this->assignRef('email', $email);
		
		//List
		$selection =& EmundusHelperList::createSelectionBlock($users);
		$this->assignRef('selection', $selection);
		
		$options = array('checkbox', 'details', 'selection_outcome');
		$actions =& EmundusHelperList::createActionsBlock($users, $options);
		$this->assignRef('actions', $actions);
		unset($options);
		
		
		//Profile
		$profile = EmundusHelperList::createProfileBlock($users,'profile');
		$this->assignRef('profile', $profile);
		$result_for = EmundusHelperList::createProfileBlock($users,'result_for');
		$this->assignRef('result_for', $result_for);
		$final_grade = EmundusHelperFilters::getFinal_grade();
		$sub_labels = explode('|', $final_grade['final_grade']['sub_labels']);
		$sub_values = explode('|', $final_grade['final_grade']['sub_values']);
		$fg = array_combine($sub_values, $sub_labels);
		$this->assignRef('fg', $fg);

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