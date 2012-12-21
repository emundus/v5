<?php
/**
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
/**
 * Joomla User plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	User.joomla
 * @since		1.5
 */
class plgUserEmundus extends JPlugin
{
	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data
	 * @param	boolean		$succes	True if user was succesfully stored in the database
	 * @param	string		$msg	Message
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		if (!$succes) {
			return false;
		}

		$db = JFactory::getDbo();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__session') .
			' WHERE '.$db->quoteName('userid').' = '.(int) $user['id']
		);
		$db->Query();

		return true;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param	array		$user		Holds the new user data.
	 * @param	boolean		$isnew		True if a new user is stored.
	 * @param	boolean		$success	True if user was succesfully stored in the database.
	 * @param	string		$msg		Message.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// Initialise variables.
		$jinput 		= JFactory::getApplication()->input;

		$details 		= $jinput->post->get('jform', null, 'none');
		$app 			= JFactory::getApplication();
		$config			= JFactory::getConfig();
		$mail_to_user 	= $this->params->get('mail_to_user', 1);

		if ($isnew) {
			// @TODO	Suck in the frontend registration emails here as well. Job for a rainy day.

			$db = JFactory::getDBO();

			// Update name and fistname from #__users
			$db->setQuery('UPDATE #__users
					SET name="'.strtoupper($details['name']).' '.ucfirst($details['firstname']).'"
					WHERE id='.$user['id']);
			$db->Query();

			// Insert data in #__emundus_users
			$db->setQuery('SELECT schoolyear FROM #__emundus_setup_profiles WHERE id='.$details['profile']);
			$schoolyear = $db->loadResult();

			$db->setQuery('INSERT INTO #__emundus_users (user_id, firstname, lastname, profile, schoolyear,registerDate)
						VALUES ('.$user['id'].',"'.ucfirst($details['firstname']).'","'.strtoupper($details['name']).'",'.$details['profile'].',"'.$schoolyear.'","'.$user['registerDate'].'")');
			$db->Query();

			// Insert data in #__emundus_users_profiles
			$db->setQuery('INSERT INTO #__emundus_users_profiles (user_id, profile_id)
						VALUES ('.$user['id'].','.$details['profile'].')');
			$db->Query();

			// Insert data in #__emundus_users_profiles_history
			$db->setQuery('INSERT INTO #__emundus_users_profiles_history (user_id, profile_id, var)
						VALUES ('.$user['id'].','.$details['profile'].',"profile")');
			$db->Query();

			$db->setQuery('UPDATE #__users
						SET usertype=(SELECT u.title FROM #__usergroups AS u
						LEFT JOIN #__user_usergroup_map AS uum ON u.id=uum.group_id
						WHERE uum.user_id='.$user['id'].' ORDER BY uum.group_id DESC LIMIT 1) WHERE id='.$user['id']);
			$db->Query();

			if ($app->isAdmin()) {
				if ($mail_to_user) {

					// Load user_joomla plugin language (not done automatically).
					$lang = JFactory::getLanguage();
					$lang->load('plg_user_joomla', JPATH_ADMINISTRATOR);

					// Compute the mail subject.
					$emailSubject = JText::sprintf(
						'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT',
						$user['name'],
						$config->get('sitename')
					);

					// Compute the mail body.
					$emailBody = JText::sprintf(
						'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY',
						$user['name'],
						$config->get('sitename'),
						JUri::root(),
						$user['username'],
						$user['password_clear']
					);

					// Assemble the email data...the sexy way!
					$mail = JFactory::getMailer()
						->setSender(
							array(
								$config->get('mailfrom'),
								$config->get('fromname')
							)
						)
						->addRecipient($user['email'])
						->setSubject($emailSubject)
						->setBody($emailBody);

					if (!$mail->Send()) {
						// TODO: Probably should raise a plugin error but this event is not error checked.
						JError::raiseWarning(500, JText::_('ERROR_SENDING_EMAIL'));
					}
				}
			}
		}
		else {
			// Existing user - nothing to do...yet.
		}
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data
	 * @param	array	$options	Array holding options (remember, autoregister, group)
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options = array())
	{
// Here you would do whatever you need for a login routine with the credentials
		// Remember, this is not the authentication routine as that is done separately.
		// The most common use of this routine would be logging the user into a third party application
		// In this example the boolean variable $success would be set to true if the login routine succeeds
		// ThirdPartyApp::loginUser($user['username'], $user['password']);
		
		$current_user	=& JFactory::getUser();
		$db		 		=& JFactory::getDBO();
		$mainframe 		=  JFactory::getApplication();
		
		$query = '	SELECT count(ed.id) as candidature_posted, eu.firstname, eu.lastname, eu.profile, eu.university_id, esp.label AS profile_label, esp.menutype, esp.published, esp.candidature_start, esp.candidature_end, esp.schoolyear 
						FROM #__emundus_users AS eu 
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile 
						LEFT JOIN #__emundus_declaration AS ed ON ed.user = eu.user_id
						WHERE eu.user_id = '.$current_user->id.' 
						GROUP BY eu.user_id';
		$db->setQuery($query);
		$res = $db->loadObject();
		
		$current_user->firstname 			= @$res->firstname;
		$current_user->lastname	 			= @$res->lastname;
		$current_user->profile	 			= @$res->profile;
		$current_user->profile_label 		= @$res->profile_label;
		$current_user->menutype	 			= @$res->menutype;
		$current_user->university_id		= @$res->university_id;
		$current_user->applicant			= @$res->published;
		$current_user->candidature_start	= @$res->candidature_start;
		$current_user->candidature_end		= @$res->candidature_end;
		$current_user->candidature_posted 	= @$res->candidature_posted;
		$current_user->schoolyear			= @$res->schoolyear;
		
		$mainframe->redirect("index.php");

		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Array holding options (client, ...).
	 *
	 * @return	object	True on success
	 * @since	1.5
	 */
	public function onUserLogout($user, $options = array())
	{
		$my 		= JFactory::getUser();
		$session 	= JFactory::getSession();
		$app 		= JFactory::getApplication();

		// Make sure we're a valid user first
		if ($user['id'] == 0 && !$my->get('tmp_user')) {
			return true;
		}

		// Check to see if we're deleting the current session
		if ($my->get('id') == $user['id'] && $options['clientid'] == $app->getClientId()) {
			// Hit the user last visit field
			$my->setLastVisit();

			// Destroy the php session for this user
			$session->destroy();
		}

		// Force logout all users with that userid
		$db = JFactory::getDBO();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__session') .
			' WHERE '.$db->quoteName('userid').' = '.(int) $user['id'] .
			' AND '.$db->quoteName('client_id').' = '.(int) $options['clientid']
		);
		$db->query();

		return true;
	}

	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Array holding options (remember, autoregister, group).
	 *
	 * @return	object	A JUser object
	 * @since	1.5
	 */
	protected function _getUser($user, $options = array())
	{
		$instance = JUser::getInstance();
		if ($id = intval(JUserHelper::getUserId($user['username'])))  {
			$instance->load($id);
			return $instance;
		}

		//TODO : move this out of the plugin
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);

		$acl = JFactory::getACL();

		$instance->set('id'			, 0);
		$instance->set('name'			, $user['fullname']);
		$instance->set('username'		, $user['username']);
		$instance->set('password_clear'	, $user['password_clear']);
		$instance->set('email'			, $user['email']);	// Result should contain an email (check)
		$instance->set('usertype'		, 'deprecated');
		$instance->set('groups'		, array($defaultUserGroup));

		//If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

		if ($autoregister) {
			if (!$instance->save()) {
				return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		}
		else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		return $instance;
	}
}
