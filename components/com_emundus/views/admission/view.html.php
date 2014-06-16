<?php

/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Décision Publique - Benjamin Rivalland
 */
// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewAdmission extends JView {

    function display($tpl = null) {
        $document = & JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . "components/com_emundus/style/emundus.css");
        require_once(JPATH_COMPONENT . DS . 'models' . DS . 'check.php');
        $model = new EmundusModelCheck;

        $current_user = & JFactory::getUser();
        $allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
        if (!in_array($current_user->usertype, $allowed)) {
            die(JText::_('You are not allowed to access to this page...') . $current_user->usertype);
            //return false;
        }

        $learning_agreement_status = & $this->get('LearningAgreementStatus');
        $this->assignRef('learning_agreement_status', $learning_agreement_status);
        
        $schoolYears = & $this->get('AllCampaigns');
        $this->assignRef('schoolYears', $schoolYears);
        
        $schoolyear =& $this->get('Campaign');
        $this->assignRef('schoolyear', $schoolyear);
        
        $profiles_id = & $this->get('RegistredProfiles');
        $this->assignRef('profiles_id', $profiles_id);
        
        $profiles = & $this->get('EditorProfiles');
        $this->assignRef('profiles', $profiles);
        
        $evalUsers = & $this->get('EditorUsers');
        $this->assignRef('evalUsers', $evalUsers);
        
        $users = & $this->get('Users');
        $this->assignRef('users', $users);
        
        $elements = & $this->get('Elements');
        $this->assignRef('elements', $elements);
        
        $pagination = & $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
        
        /* Call the state object */
        $state = & $this->get('state');

        $applicantsProfiles = & $model->getApplicantsProfiles();
        $this->assignRef('applicantsProfiles', $applicantsProfiles);

        $groups = & $this->get('Groups');
        $this->assignRef('groups', $groups);
        
        $groups_eval = & $this->get('GroupsEval');
        $this->assignRef('groups_eval', $groups_eval);
        
        $users_groups = & $this->get('UsersGroups');
        $this->assignRef('users_groups', $users_groups);


        /* Get the values from the state object that were inserted in the model's construct function */
        $lists['order_Dir'] = $state->get('filter_order_Dir');
        $lists['order'] = $state->get('filter_order');
        $this->assignRef('lists', $lists);

//die(print_r($schoolyears));
        parent::display($tpl);
    }

}

?>