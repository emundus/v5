<?php
/**
 * @version		$Id: email.php 14401 2010-01-26 14:10:00Z guillossou
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Helper
 * @since 1.5
 */
class EmundusHelperEmails{
	function createEmailBlock($params){
		$current_user = JFactory::getUser();
		$email = '<div class="em_email_block">';
		if(in_array('default',$params)){
			$email .= '<fieldset>
				<legend> 
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'::'.JText::_('EMAIL_ASSESSORS_DEFAULT_TIP').'">
						<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replayall_22x22.png" alt="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'"/>'.JText::_('EMAIL_ASSESSORS_DEFAULT').'
					</span>
				</legend>
				<div><input type="submit" class="blue" name="default_email" onclick="document.pressed=this.name" value="'.JText::_( 'SEND_DEFAULT_EMAIL' ).'" ></div>
			</fieldset>';
		}
		if(in_array('custom',$params)){
			$current_eval = JRequest::getVar('user', null, 'POST', 'none',0);
			$current_group = JRequest::getVar('groups', null, 'POST', 'none',0);
			$all_groups = EmundusHelperFilters::getGroups();
			$evaluators = EmundusHelperFilters::getEvaluators();
			$email .= '
			<fieldset><legend> 
						<span class="editlinktip hasTip" title="'.JText::_('EMAIL_SELECTED_ASSESSORS').'::'.JText::_('EMAIL_SELECTED_ASSESSORS_TIP').'">
							<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replay_22x22.png" alt="'.JText::_('EMAIL_ASSESSORS_DEFAULT').'"/> '.JText::_( 'EMAIL_SELECTED_ASSESSORS' ).'
						</span>
					</legend>
				<div><p><dd>
					[NAME] : '.JText::_('TAG_NAME_TIP').'<br />
					[APPLICANTS_LIST] : '.JText::_('TAG_APPLICANTS_LIST_TIP').'<br />
					[SITE_URL] : '.JText::_('SITE_URL_TIP').'<br />
					[EVAL_CRITERIAS] : '.JText::_('EVAL_CRITERIAS_TIP').'<br />
					[EVAL_PERIOD] : '.JText::_('EVAL_PERIOD_TIP').'<br />
					</dd></p><br />
					<label for="mail_subject"> '.JText::_( 'SUBJECT' ).' </label><br/>
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="80" />
				</div><br/>
				<div>
					<select name="mail_group">
						<option value=""> '.JText::_('PLEASE_SELECT_GROUP').' </option>' ;
							foreach($all_groups as $groups) { 
								$email .= '<option value="'.$groups->id.'"';
								if($current_group==$groups->id) $email .= ' selected';
								$email .= '>'.$groups->label.'</option>'; 
							}
					$email .= '</select>
					'.JText::_('OR').'
					<select name="mail_user">
						<option value="">'.JText::_('PLEASE_SELECT_ASSESSOR').' </option>' ;
						foreach($evaluators as $eval_users) { 
							$email .= '<option value="'.$eval_users->id.'"';
							if($current_eval==$eval_users->id) $email .= ' selected';
							$email .= '>'.$eval_users->name.'</option>'; 
						}
					$email .= ' </select>
					<br/><br/>
					<label for="mail_body">'.JText::_( 'MESSAGE' ).' </label><br/>
					<textarea name="mail_body" id="mail_body" rows="10" cols="80" class="inputbox">[NAME], </textarea>
				</div>
				<div><input type="submit" name="custom_email" onclick="document.pressed=this.name" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>
			</fieldset>';
		}
		if(in_array('applicants', $params)){
			$email.= '<fieldset>
				<legend> 
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_SELECTED_APPLICANTS').'::'.JText::_('EMAIL_SELECTED_APPLICANTS_TIP').'">
						<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replay_22x22.png" alt="'.JText::_('EMAIL_SELECTED_APPLICANTS').'"/> '.JText::_( 'EMAIL_SELECTED_APPLICANTS' ).'
					</span>
				</legend>
				<div>
					<p>
					<dd>
					[NAME] : '.JText::_('TAG_NAME_TIP').'<br />
					[SITE_URL] : '.JText::_('SITE_URL_TIP').'<br />
					</dd>
					</p><br />
					<label for="mail_subject">'.JText::_( 'SUBJECT' ).' </label><br/>
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="80" />
				</div>
				<label for="mail_body"> '.JText::_( 'MESSAGE' ).' </label><br/>
				<textarea name="mail_body" id="mail_body" rows="10" cols="80" class="inputbox">[NAME], </textarea>
				<div><input type="submit" name="applicant_email" onclick="document.pressed=this.name" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>
			</fieldset>';
		}
		if(in_array('evaluation_result', $params)){
			$editor = &JFactory::getEditor();
			$mail_body = $editor->display( 'mail_body', '', '99%', '400', '20', '20', false, 'mail_body', null, null );

			$student_id = JRequest::getVar('jos_emundus_evaluations___student_id', null, 'GET', 'INT',0);
			$campaign_id = JRequest::getVar('jos_emundus_evaluations___campaign_id', null, 'GET', 'INT',0);
			$applicant = JFactory::getUser($student_id);
	
			$email.= '<fieldset>
				<legend> 
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_APPLICATION_RESULT').'::'.JText::_('EMAIL_APPLICATION_RESULT_TIP').'">
						<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replay_22x22.png" alt="'.JText::_('EMAIL_TO').'"/> '.JText::_( 'EMAIL_TO' ).' '.$applicant->name.'
					</span>
				</legend>
				<div>
				<p><label for="mail_subject">'.JText::_( 'SUBJECT' ).' </label><br/>
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="80" />
				<p>
					<input name="mail_to" type="hidden" class="inputbox" id="mail_to" value="'.$applicant->id.'" />
					<input name="campaign_id" type="hidden" class="inputbox" id="campaign_id" value="'.$campaign_id.'" size="80" />
				</div>
				<p><label for="mail_body"> '.JText::_( 'MESSAGE' ).' </label><br/>'.$mail_body.'
				</p>
					<input name="mail_attachments" type="hidden" class="inputbox" id="mail_attachments" value="" />
					<input name="mail_type" type="hidden" class="inputbox" id="mail_type" value="evaluation_result" />
				<br><br>
				<p><div><input type="submit" name="evaluation_result_email" onclick="document.pressed=this.name" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>
				</p>
			</fieldset>';
		}
		if(in_array('this_applicant', $params)){
			$email_to = JRequest::getVar('sid', null, 'GET', 'none',0);
			$student = JFactory::getUser($email_to);
			$email.= '<fieldset>
				<legend> 
					<span class="editlinktip hasTip" title="'.JText::_('EMAIL_SELECTED_APPLICANTS').'::'.JText::_('EMAIL_SELECTED_APPLICANTS_TIP').'">
						<img src="'.JURI::Base().'media/com_emundus/images/icones/mail_replay_22x22.png" alt="'.JText::_('EMAIL_SELECTED_APPLICANTS').'"/> '.JText::_( 'EMAIL_SELECTED_APPLICANTS' ).'
					</span>
				</legend>
				<div>
					<p>
					<dd>
					[NAME] : '.JText::_('TAG_NAME_TIP').'<br />
					[SITE_URL] : '.JText::_('SITE_URL_TIP').'<br />
					</dd>
					</p><br />
					<label for="mail_subject">'.JText::_( 'SUBJECT' ).' </label><br/>
					<input name="mail_subject" type="text" class="inputbox" id="mail_subject" value="" size="80" /><br />
					<label for="mail_to">'.JText::_( 'APPLICANT' ).' </label><br/>
					<input name="mail_to" type="text" class="inputbox" id="mail_to" value="'.$student->username.'" size="80" disabled/>
					<input type="hidden" name="ud[]" value="'.$email_to.'" >
				</div>
				<label for="mail_body"> '.JText::_( 'MESSAGE' ).' </label><br/>
				<textarea name="mail_body" id="mail_body" rows="10" cols="80" class="inputbox">[NAME], </textarea>
				<div><input type="submit" name="applicant_email" onclick="document.pressed=this.name" value="'.JText::_( 'SEND_CUSTOM_EMAIL' ).'" ></div>
			</fieldset>';
		}
		$email .= '</div>';
		return $email;
	}
	
	function sendDefaultEmail(){
		$current_user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($current_user->id,$access)) {
			die(JText::_("ACCESS_DENIED"));
		}
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$limitstart = JRequest::getVar('limitstart', null, 'POST', 'none',0);
		$filter_order = JRequest::getVar('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'POST', null, 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', null, 0);
		
		// List of evaluators
		$query = 'SELECT eg.user_id 
					FROM `#__emundus_groups` as eg 
					LEFT JOIN `#__emundus_groups_eval` as ege on ege.group_id=eg.group_id 
					WHERE eg.user_id is not null 
					GROUP BY eg.user_id';
		$db->setQuery( $query );
		$users_1 = $db->loadResultArray();
		
		$query = 'SELECT ege.user_id 
					FROM `#__emundus_groups_eval` as ege 
					WHERE ege.user_id is not null 
					GROUP BY ege.user_id';
		$db->setQuery( $query );
		$users_2 = $db->loadResultArray();
		
		$users = array_unique(array_merge($users_1, $users_2));
		
		/*
		$query = 'SELECT e.email
					FROM #__emundus_users eu
					JOIN #__users e ON e.id = eu.user_id
					WHERE eu.profile IN (2,4,5)';
		$db->setQuery( $query );
		$copy = $db->loadResultArray();
		foreach($copy as $c){
			$cc[] = $c;
		}
		*/
		// Récupération des données du mail
		$query = 'SELECT id, subject, emailfrom, name, message
						FROM #__emundus_setup_emails
						WHERE lbl="assessors_set"';
		$db->setQuery( $query );
		$db->query();
		$obj=$db->loadObjectList();
		
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
		$error=0;
		foreach ($users as $uid) {
			$user = JFactory::getUser($uid);
			
			$query = 'SELECT applicant_id
						FROM #__emundus_groups_eval
						WHERE user_id='.$user->id.' OR group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.')';
			$db->setQuery( $query );
			$db->query();
			$applicants=$db->loadResultArray();
			$list = '<ul>';
			foreach($applicants as $ap) {
				$app = JFactory::getUser($ap);
				$list .= '<li>'.$app->name.' ['.$app->id.']</li>';
			}
			$list .= '</ul>';
			
			$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
					FROM #__emundus_setup_profiles AS esp 
					LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
					WHERE user_id='.$user->id;
			$db->setQuery( $query );
			$db->query();
			$period=$db->loadRow();
			
			$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
			
			$replacements = array ($user->id, $user->name, $user->email, $list, JURI::base(), $eval, $period_str, '<br />');
			
			// template replacements
			$body = preg_replace($patterns, $replacements, $obj[0]->message);
			
			
			// mail function
			if (count($applicants)>0) {
				if (JUtility::sendMail($from, $obj[0]->name, $user->email, $obj[0]->subject, $body, 1, $cc)) {
					$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES ('".$from_id."', '".$user->id."', ".$db->quote($obj[0]->subject).", ".$db->quote($body).", NOW())";
					$db->setQuery( $sql );
					$db->query();
					$sent .= '&rsaquo; '.$user->name.' - '.$user->email.'<br />';
				} else {
					$error++;
				}
			}
			unset($replacements);
			unset($list);
		}
		if ($error>0)	
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, $sent.JText::_('ACTION_ABORDED'), 'error');
		else 
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, $sent.JText::_('ACTION_DONE'), 'message');
	
	}
	
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
		if (isset($ag_id) && $ag_id > 0) {
			$query = 'SELECT eg.user_id 
						FROM `#__emundus_groups` as eg 
						WHERE eg.group_id='.$ag_id;
			$db->setQuery( $query );
			$users = $db->loadResultArray();
		} elseif (isset($ae_id) && $ae_id > 0)
			$users[] = $ae_id;
		else {
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
			
			$query = 'SELECT applicant_id
					  FROM #__emundus_groups_eval
					  WHERE user_id='.$user->id.' OR group_id IN (select group_id from #__emundus_groups where user_id='.$user->id.')';
			$db->setQuery( $query );
			$db->query();
			$applicants=$db->loadResultArray();
			$list = '<ul>';
			
			foreach($applicants as $ap) {
				$app = JFactory::getUser($ap);
				$list .= '<li>'.$app->name.' ['.$app->id.']</li>';
			}
			$list .= '</ul>';
			
			$query = 'SELECT esp.evaluation_start, esp.evaluation_end 
						FROM #__emundus_setup_profiles AS esp 
						LEFT JOIN #__emundus_users AS eu ON eu.profile=esp.id  
						WHERE user_id='.$user->id;
			$db->setQuery( $query );
			$db->query();
			$period=$db->loadRow();
				
			$period_str = strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[0])).' '.JText::_('TO').' '.strftime(JText::_('DATE_FORMAT_LC2'), strtotime($period[1]));
				
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
		}			
		$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid, JText::_('ACTION_DONE'), 'message');
	}
	
	function sendApplicantEmail() {
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id,$access)) {
			die(JText::_("ACCESS_DENIED"));
		}
		
		$mainframe = JFactory::getApplication();

		$db	= JFactory::getDBO();
		$current_user = JFactory::getUser();

		$cids = JRequest::getVar( 'ud', array(), 'post', 'array' );
		foreach ($cids as $cid){
			$params=explode('|',$cid);
			$users_id[] = intval($params[0]);
		}
		
		$captcha	= 1;//JRequest::getInt( JR_CAPTCHA, null, 'post' );

		$subject	= JRequest::getVar( 'mail_subject', null, 'post' );
		$message	= JRequest::getVar( 'mail_body', null, 'post' );

		if ($captcha !== 1) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NOT_A_VALID_POST' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if (count( $users_id ) == 0) {
			JError::raiseWarning( 500, JText::_( 'ERROR_NO_ITEMS_SELECTED' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($subject == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_SUBJECT' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}
		if ($message == '') {
			JError::raiseWarning( 500, JText::_( 'ERROR_YOU_MUST_PROVIDE_A_MESSAGE' ) );
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ));
			return;
		}


		$query = 'SELECT u.id, u.name, u.email' .
					' FROM #__users AS u' .
					' WHERE u.id IN ('.implode( ',', $users_id ).')';
		$db->setQuery( $query );
		$users = $db->loadObjectList();


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
			$from = $admin->name;
			$from_id = $admin->id;
			$fromname = $admin->email;
		}

		// template replacements
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[SITE_URL\]/', '/\n/');

		$nUsers = count( $users );
		for ($i = 0; $i < $nUsers; $i++) {
			$user = &$users[$i];

			// template replacements
			$replacements = array ($user->id, $user->name, $user->email, JURI::base(), '<br />');
			// template replacements
			$body = preg_replace($patterns, $replacements, $message);

			// mail function
			if (JUtility::sendMail($from, $fromname, $user->email, $subject, $body, 1)) {
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
					VALUES ('".$from_id."', '".$user->id."', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
				$db->setQuery( $sql );
				$db->query();
			} else {
				$error++;
			}
		}
		if ($error>0) {
			JError::raiseWarning( 500, JText::_( 'ACTION_ABORDED' ) );
			return;
		} else {
			$this->setRedirect('index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.JRequest::getCmd( 'Itemid' ), JText::_('REPORTS_MAILS_SENT'), 'message');
		}	
	}

}
?>