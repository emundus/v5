<?php
function sendCustomEmail(){
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCESS_DENIED"));
		}
		$mainframe = JFactory::getApplication();
		
		$db = JFactory::getDBO();
		$ag_id = JRequest::getVar('mail_group', null, 'POST', 'none',0);
		$ae_id = JRequest::getVar('mail_user', null, 'POST', 'none',0);
		$subject = JRequest::getVar('mail_subject', null, 'POST', 'none',0);
		$message = JRequest::getVar('mail_body', null, 'POST', 'none',0);
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);
		$select_id = JRequest::getVar('ud', array(), 'POST', 'array');
		$filters_users = JRequest::getVar('filters_users', null, 'POST', 'none', 0);
		$filters_users  = explode(', ',$filters_users);
		$addressee = JRequest::getVar('addressee', null, 'POST', 'none',0);
		

		global $option;
		$campaigns = $mainframe->getUserStateFromRequest( $option."campaigns", "campaigns");
		for($i=0; $i<count($campaigns); $i++){
			if($campaigns[$i]=='%'){
				unset($campaigns[$i]);
			}
		}
		
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
			return;
		}
		
		// List of evaluators
		if(isset($addressee) && $addressee==1){
			$query = 'SELECT user_id
					FROM #__emundus_users
					WHERE profile=6';
			$db->setQuery( $query );
			$users = $db->loadResultArray();
		}else if(isset($addressee) && $addressee==2){
			if (isset($ag_id) && $ag_id > 0) {
				$query = 'SELECT eg.user_id 
							FROM `#__emundus_groups` as eg 
							WHERE eg.group_id='.$ag_id;
				$db->setQuery( $query );
				$users = $db->loadResultArray();
			}else{
				JError::raiseWarning( 500, JText::_('ERROR_YOU_MUST_SELECT_AN_EVALUATOR') );
				$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
				return;
			}
		}elseif(isset($addressee) && $addressee==3){
			if (isset($ae_id) && $ae_id > 0){
				$users[] = $ae_id;
			}else{
				JError::raiseWarning( 500, JText::_('ERROR_YOU_MUST_SELECT_AN_EVALUATOR') );
				$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
				return;
			}
		}else{
			JError::raiseWarning( 500, JText::_('ERROR_YOU_MUST_SELECT_AN_EVALUATOR') );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
			return;
		}
		
		// setup mail
		if (isset($current_user->email)) {
			$from = $current_user->email;
			$from_id = $current_user->id;
			$fromname=$current_user->name;
		} elseif ($mainframe->getCfg( 'mailfrom' ) != '' && $mainframe->getCfg( 'fromname' ) != '') {
			$from = $mainframe->getCfg( 'mailfrom' );
			$fromname = $mainframe->getCfg( 'fromname' );
			$from_id = 62;

		} else {
			$query = 'SELECT id, name, email' .
				' FROM #__users' .
				// administrator
				' WHERE gid = 25 LIMIT 1';
			$db->setQuery( $query );
			$admin = $db->loadObject();
			$from = $admin->email;
			$from_id = $admin->id;
			$fromname = $admin->name;
		}

		// Evaluations criterias
		$query = 'SELECT id, label, sub_labels
		FROM #__fabrik_elements
		WHERE group_id=41 AND (plugin like "fabrikradiobutton" OR plugin like "fabrikdropdown")';
		$db->setQuery( $query );
		$db->query();
		$eval_criteria=$db->loadObjectList();
		
		$eval = '<ul>';
		foreach($eval_criteria as $e) {
			$eval .= '<li>'.$e->label.' ('.$e->sub_labels.')</li>';
		}
		$eval .= '</ul>';

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[APPLICANTS_LIST\]/', '/\[SITE_URL\]/', '/\[EVAL_CRITERIAS\]/', '/\[EVAL_PERIOD\]/', '/\n/');

		//send to selected people
		foreach ($users as $uid) {
			$user = JFactory::getUser($uid);
			
			if(empty($select_id)){ // if !checkbox
				$query = 'SELECT ee.student_id, ee.campaign_id
							FROM #__emundus_evaluations as ee
							WHERE ee.user <>'.$user->id;
				$db->setQuery( $query );
				$db->query();
				$evaluated_applicant=$db->loadObjectList();
							
				$query = 'SELECT ege.applicant_id, ege.campaign_id
							FROM #__emundus_groups_eval as ege
							WHERE (ege.user_id='.$user->id.' 
							OR ege.group_id IN (SELECT group_id FROM #__emundus_groups WHERE user_id='.$user->id.') )';
				$db->setQuery( $query );
				$db->query();
				$applicants=$db->loadObjectList(); // [APPLICANTS_LIST]
				
				foreach($applicants as $ap) {
					$bool[$ap->applicant_id][$ap->campaign_id] = false;
				}
				
				$query = 'SELECT ege.applicant_id
							FROM #__emundus_groups_eval as ege
							LEFT JOIN #__emundus_evaluations as ee ON ee.student_id=ege.applicant_id AND ee.campaign_id=ege.campaign_id 
							WHERE (ege.user_id='.$user->id.' 
							OR ege.group_id IN (SELECT group_id FROM #__emundus_groups WHERE user_id='.$user->id.') ) AND ee.student_id IS NULL';
				$db->setQuery( $query );
				$db->query();
				$non_evaluated_applicant=$db->loadResultArray();
				
				$model=$this->getModel('campaign');
				
				
				$list = '<ul>';
				foreach($applicants as $ap) {
					foreach($evaluated_applicant as $e_applicant){
						if(empty($filters_users) || !empty($filters_users) && in_array($ap->applicant_id,$filters_users)){
							if(empty($campaigns) || (!empty($campaigns) && in_array($ap->campaign_id,$campaigns))){
								if( (($ap->applicant_id==$e_applicant->student_id) && ($ap->campaign_id==$e_applicant->campaign_id)) || (in_array($ap->applicant_id,$non_evaluated_applicant)) && $bool[$ap->applicant_id][$ap->campaign_id]==false){
									$bool[$ap->applicant_id][$ap->campaign_id] = true;
									$app = JFactory::getUser($ap->applicant_id);		
									$campaign=$model->getCampaignByID($ap->campaign_id);
									$list .= '<li>'.$app->name.' ['.$app->id.'] - '.$campaign["label"].' ['.$campaign["year"].']</li>';
								}
							}
						}
					}	
				}
				$list .= '</ul>';
				
				
			}else{ // if checkbox
				foreach ($select_id as $select){
					$params=explode('|',$select);
					$selected[$params[0]][$params[1]]=true;
				}
				$query = 'SELECT ee.student_id, ee.campaign_id
							FROM #__emundus_evaluations as ee
							WHERE ee.user <>'.$user->id;
				$db->setQuery( $query );
				$db->query();
				$evaluated_applicant=$db->loadObjectList();
							
				$query = 'SELECT ege.applicant_id, ege.campaign_id
							FROM #__emundus_groups_eval as ege
							WHERE (ege.user_id='.$user->id.' 
							OR ege.group_id IN (SELECT group_id FROM #__emundus_groups WHERE user_id='.$user->id.') )';
				$db->setQuery( $query );
				$db->query();
				$applicants=$db->loadObjectList(); // [APPLICANTS_LIST]
				
				foreach($applicants as $ap) {
					$bool[$ap->applicant_id][$ap->campaign_id] = false;
				}
				
				$query = 'SELECT ege.applicant_id
							FROM #__emundus_groups_eval as ege
							LEFT JOIN #__emundus_evaluations as ee ON ee.student_id=ege.applicant_id AND ee.campaign_id=ege.campaign_id 
							WHERE (ege.user_id='.$user->id.' 
							OR ege.group_id IN (SELECT group_id FROM #__emundus_groups WHERE user_id='.$user->id.') ) AND ee.student_id IS NULL';
				$db->setQuery( $query );
				$db->query();
				$non_evaluated_applicant=$db->loadResultArray();
				
				$model=$this->getModel('campaign');
				$list = '<ul>';
				foreach(@$applicants as $ap) {
					foreach(@$evaluated_applicant as $e_applicant){
						if(!empty($selected[$ap->applicant_id][$ap->campaign_id])){
							if( (($ap->applicant_id==$e_applicant->student_id) && ($ap->campaign_id==$e_applicant->campaign_id)) || (in_array($ap->applicant_id,$non_evaluated_applicant)) && $bool[$ap->applicant_id][$ap->campaign_id]==false){
								$bool[$ap->applicant_id][$ap->campaign_id] = true;
								$app = JFactory::getUser($ap->applicant_id);		
								$campaign=$model->getCampaignByID($ap->campaign_id);
								$list .= '<li>'.$app->name.' ['.$app->id.'] - '.$campaign["label"].' ['.$campaign["year"].']</li>';
							}
						}
					}	
				}
				$list .= '</ul>';
			}
			
			$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
						FROM #__emundus_setup_profiles AS esp 
						LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
						WHERE user_id='.$user->id;
			$db->setQuery( $query );
			$db->query();
			$period=$db->loadRow();
				
			$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
			
			if($list=='<ul></ul>'){
					JError::raiseNotice( 100, JText::_('EMPTY_EVAL_LIST').' : '.$user->name.'<BR />'.JText::_('EMAIL_TO_EVAL_NOT_SEND') );
			}else{
				$replacements = array ($user->id, $user->name, $user->email, $list, JURI::base(), $eval, $period_str, '<br />');
				// template replacements
				$body = preg_replace($patterns, $replacements, $message);
				
				// mail function
				if(JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1)){
					usleep(1000);
					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES ('".$from_id."', '".$user->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
					$db->setQuery( $sql );
					$db->query();
				}
				unset($replacements);
				JFactory::getApplication()->enqueueMessage(JText::_('EMAIL_TO_EVAL_SEND').' : '.$user->name);
			}
		}			
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}
?>