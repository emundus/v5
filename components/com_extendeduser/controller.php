<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.utilities.utility' );

jimport('joomla.application.component.controller');

class UserController extends JController
{

	function display()
	{
		parent::display();
	}

	function edit()
	{
		global $option;

		$mainframe = JFactory::getApplication();

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		if ( $user->get('guest')) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		JRequest::setVar('layout', 'form');

		parent::display();
	}
	

	function save()
	{
		//preform token check (prevent spoofing)
		$token	= JUtility::getToken();
		if(!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$user	 =& JFactory::getUser();
		$userid = JRequest::getVar( 'id', 0, 'post', 'int' );

		// preform security checks
		if ($user->get('id') == 0 || $userid == 0 || $userid <> $user->get('id')) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		//clean request
		$post = JRequest::get( 'post' );
		$post['profile']	= JRequest::getVar('profile', '', 'post', 'profile');
		$post['username']	= JRequest::getVar('username', '', 'post', 'username');
		$post['password']	= JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['password2']	= JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);
		//die($post['profile']);

		// do a password safety check
		if(strlen($post['password'])) { // so that "0" can be used as password e.g.
			if($post['password'] != $post['password2']) {
				$msg	= JText::_('PASSWORDS DO NOT MATCH');
				$this->setRedirect($_SERVER['HTTP_REFERER'], $msg);
				return false;
			}
		}
		//is Applicant profile
		$db =& JFactory::getDBO();
		$query = 'SELECT published FROM #__emundus_setup_profiles WHERE id='.$post['profile'];
		$db->setQuery($query);
		$v = $db->loadResult();
		$user->applicant=$v;
		$post['published']=$v;
		
		// store data
		$model = &$this->getModel('useredit');

		if ($model->store($post)) {
			$msg	= JText::_( 'SETTINGS_HAVE_BEEN_SAVED' );
		} else {
			//$msg	= JText::_( 'Error saving your settings.' );
			$msg	= $model->getError();
		}

		$this->setRedirect( $_SERVER['HTTP_REFERER'], $msg );
	}

	function cancel()
	{
		$this->setRedirect( 'index.php' );
	}
	
	function quicklogin($username, $password, &$mainframe){
		
		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$return = base64_decode($return);
		}

		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = $return;
		
		$credentials = array();
		$credentials['username'] = $username;
		$credentials['password'] = $password;
		$error = $mainframe->login($credentials, $options);
		
		return $error;
		
	}
	
	/**
	 * login.
	 */
	function login() {
		
		$mainframe = JFactory::getApplication();

		$extuser =& JTable::getInstance('ExtUser', 'Table');
		
		$username = JRequest::getVar('username', '', 'post', 'username');
		$password = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);
		$register = JRequest::getVar('register', null, 'POST', 'none',0);
		if($register != 'register'){
			//preform the login action
			$error = $this->quicklogin($username, $password, $mainframe);
			if(!JError::isError($error)){
				$user	 =& JFactory::getUser();
				$db		 =& JFactory::getDBO();
				$db->setQuery('SELECT user.firstname, user.lastname, user.profile, user.university_id, profile.label AS profile_label, 
							  user.schoolyear, profile.candidature_start, profile.candidature_end, profile.menutype, profile.published 
							  FROM #__emundus_users AS user 
							  LEFT JOIN #__emundus_setup_profiles AS profile ON profile.id = user.profile 
							  WHERE user.user_id = '.$user->get('id').'');
				$res = $db->loadObject();
				$user->firstname = $res->firstname;
				$user->lastname	 = $res->lastname;
				$user->profile	 = $res->profile;
				$user->profile_label = $res->profile_label;
				$user->menutype	 = $res->menutype;
				$user->university_id	 = $res->university_id;
				$user->applicant	 = $res->published;
				$user->passwd	 = $password;
				$db->setQuery('SELECT filename FROM #__emundus_uploads WHERE user_id = '.$user->get('id').' AND attachment_id='.EMUNDUS_PHOTO_AID);
				$user->avatar = $db->loadResult();
				switch ($user->usertype) {
					case 'Registered':
						// Vérification que le dossier à été posté ou non
						$query = 'SELECT count(*) FROM #__emundus_declaration WHERE user = '.$user->id;
						$db->setQuery( $query );
						$user->candidature_posted = $db->loadResult();
						$user->schoolyear= $res->schoolyear;
						$user->candidature_start = $res->candidature_start;
						$user->candidature_end = $res->candidature_end;
						
						$mainframe->redirect( 'index.php' );
						break;
					default:
						$mainframe->redirect( 'index.php' );
				}
			}else{
				$mainframe->redirect( 'index.php?option=com_extendeduser&view=login' );
			}
		}else{
			$mainframe->redirect( 'index.php?option=com_extendeduser&view=register' );
		}
	}

	function logout()
	{
		$mainframe = JFactory::getApplication();

		$session =& JFactory::getSession();
		$session->clear('extuser');
		
		//preform the logout action
		$error = $mainframe->logout();

		if(!JError::isError($error))
		{
			if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
				$return = base64_decode($return);
			}

			// Redirect if the return url is not registration or login
			if ( $return && !( strpos( $return, 'com_user' )) ) {
				$mainframe->redirect( $return );
			}
		} else {
			parent::display();
		}
	}

	/**
	 * Prepares the registration form
	 * @return void
	 */
	function register()
	{
		$mainframe = JFactory::getApplication();
		
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if (!$usersConfig->get( 'allowUserRegistration' )) {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		JRequest::setVar('view', 'register');

		parent::display();
	}


	function get_time_registration($user){
		$db =& JFactory::getDBO();
		$db->setQuery('SELECT substr(registerDate,1,10) FROM #__emundus_users WHERE user_id ='.$user->id);
		$res = $db->loadResult();
		$d = date('Y-m-d');  
		date_default_timezone_set('Europe/Paris');
		if($res != $d)
			return false;
		return true;
	}

	/**
	 * Save user registration and notify users and admins if required
	 * @return void
	 */
	function register_save()
	{	
		$mainframe = JFactory::getApplication();
		$db =& JFactory::getDBO();
		
		$post = JRequest::get('post');

		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}
		// check allowed profiles on registration
		$model = &$this->getModel('useredit');
		$allowed_profiles = $model->getApplicantsProfiles();
		$profile_data = JRequest::get( 'profile' );
		@$is_allowed==false;
		foreach ($allowed_profiles as $ap) {
			if($ap == $profile_data['profile1']) $is_allowed=true;
		}
		if(!$is_allowed) {
			JError::raiseError(403, 'Request Forbidden');
		}
		// Get required system objects
		$user 		= clone(JFactory::getUser());
		$pathway 	=& $mainframe->getPathway();
		$config		=& JFactory::getConfig();
		$authorize	=& JFactory::getACL();
		$document   =& JFactory::getDocument();

		// If user registration is not allowed, show 403 not authorized.
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		// Initialize new usertype setting
		$newUsertype = $usersConfig->get( 'new_usertype' );
		if (!$newUsertype || empty($newUsertype)) {
			$newUsertype = 'Registered';
		}

		// Bind the post array to the user object
		if (!$user->bind( JRequest::get('post'), 'usertype' )) {
			JError::raiseError( 500, $user->getError());
			return;
		}

		$user->set('name', $post['firstname']." ".$post['lastname']);
		// Set some initial user values
		$user->set('id', 0);
		$user->set('usertype', $newUsertype);
		$user->set('gid', $authorize->getGroupsByUser( '', $newUsertype, 'ARO' ));
		$user->set('registerDate', date('Y-m-d H:i:s'));

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get( 'useractivation' );
		if ($useractivation == '1') {
			jimport('joomla.user.helper');
			$user->set('activation', md5( JUserHelper::genRandomPassword()) );
			$user->set('block', '1');
		}

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.html.php');
		$view = new UserViewRegister();
		$message = new stdClass();
		
		$extuser =& JTable::getInstance('ExtUser', 'Table');
				
		//echo "binding extuser";
		if (!$extuser->bind($post)) {
			//exit;
			return JError::raiseWarning(500, $extuser->getError());
		}
		
		$view->assign('repop', $post);
		
		//echo "validating extuser";
		if(!$extuser->validateMe()){
			//exit;
			$message->title	= JText::_( 'REGISTRATION_ERROR' );
			$message->text	= JText::_( $extuser->getError() );
			$view->assign('message', $message);
			$view->display();
			return false;
		}
		
		//echo "saving user";
		// If there was an error with registration, set the message and display form
		if ( !$user->save() ) {
			//exit;
		 	// Page Title
		 	$document->setTitle( JText::_( 'REGISTRATION' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'New' ) );

			$message->title	= JText::_( 'REGISTRATION_ERROR' );
			$message->text	= JText::_( $user->getError() );

			$view->assign('message', $message);
			$view->display();

			return false;
		}
		//die($profile_data['profile1']);
		$extuser->set('registerDate', $user->get('registerDate'));
		$extuser->set('user_id', $user->get('id'));
		$extuser->set('profile', $profile_data['profile1']);

		//echo "storing extuser";
		if (!$extuser->store()) {
			//exit;
			return JError::raiseWarning(500, $extuser->getError());
		}
		if (!mkdir(EMUNDUS_PATH_ABS.$user->get('id').DS) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->get('id').DS.'index.html')) {
			return JError::raiseWarning(500, 'Unable to create user file');
		}
		
		// Setup profile list for applicant
		$row =& JTable::getInstance('usersprofiles', 'Table');
		$rowhistory =& JTable::getInstance('usersprofileshistory', 'Table');
		
		$up['user_id'] = $user->get('id');
		$up['profile_id'] = $profile_data;
		
		$row->set('user_id', $user->get('id'));
		$row->set('profile_id', $up['profile_id']['profile1']);

		$rowhistory->set('user_id', $user->get('id'));
		$rowhistory->set('profile_id', $up['profile_id']['profile1']);
		$rowhistory->set('var', 'profile');

		$extuser->check();
		$row->check();
		$rowhistory->check();
		$extuser_id = $extuser->id;
		$row_id = $row->id;
		$rowhistory_id = $rowhistory->id;
		
		if (!$row->store()) {
			return JError::raiseError(500, $row->getError() );
		} elseif (!$rowhistory->store()) {
			return JError::raiseError(500, $rowhistory->getError() );
		}
		
		if(!empty($up['profile_id']['profile2']) && $up['profile_id']['profile1']!=$up['profile_id']['profile2']){
			$row2 =& JTable::getInstance('usersprofiles', 'Table');
			$rowhistory2 =& JTable::getInstance('usersprofileshistory', 'Table');
			$row2->set('user_id', $user->get('id'));
			$row2->set('profile_id', $up['profile_id']['profile2']);
			$rowhistory2->set('user_id', $user->get('id'));
			$rowhistory2->set('profile_id', $up['profile_id']['profile2']);
			$rowhistory2->set('var', 'profile2');
			if (!$row2->store()) {
				return JError::raiseError(500, $row2->getError() );
			} elseif (!$rowhistory2->store()) {
				return JError::raiseError(500, $rowhistory2->getError() );
			}
		}
		if(!empty($up['profile_id']['profile3']) && $up['profile_id']['profile1']!=$up['profile_id']['profile3'] && $up['profile_id']['profile2']!=$up['profile_id']['profile3']){
			$row3 =& JTable::getInstance('usersprofiles', 'Table');
			$rowhistory3 =& JTable::getInstance('usersprofileshistory', 'Table');
			$row3->set('user_id', $user->get('id'));
			$row3->set('profile_id', $up['profile_id']['profile3']);
			$rowhistory3->set('user_id', $user->get('id'));
			$rowhistory3->set('profile_id', $up['profile_id']['profile3']);
			$rowhistory3->set('var', 'profile2');
			if (!$row3->store()) {
				return JError::raiseError(500, $row3->getError() );
			} elseif (!$rowhistory3->store()) {
				return JError::raiseError(500, $rowhistory3->getError() );
			}
		}
		// Send registration confirmation mail
		$db->setQuery('SELECT id, subject, emailfrom, name, message FROM #__emundus_setup_emails WHERE lbl="register"');
		$email=$db->loadObject();
		$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/', '/\[USERNAME\]/', '/\[PASSWORD\]/', '/\[ACTIVATION_URL\]/');
		$replacements = array ($user->id, $user->name, $user->email, '<br />', $user->username, $password, JURI::base()."index.php?option=com_extendeduser&task=activate&activation=".$user->get('activation'));
		$body = html_entity_decode(preg_replace($patterns, $replacements, $email->message), ENT_QUOTES);
		$mail = JUtility::sendMail($email->emailfrom, $email->name, $user->email, $email->subject, $body, 1);

		// _____________________________________________________ //
		// ------------- Delete if email not sent ------------- //
		// ___________________________________________________ //
		if (!$mail) {
			if($this->get_time_registration($user)){
				$extuser->delete($extuser_id);
				$row->delete($row_id);
				$rowhistory->delete($rowhistory_id);
				$db->setQuery('DELETE FROM #__users WHERE id = '.$user->id);
				$db->Query();
			}
		}

		// Everything went fine, set relevant message depending upon user activation state and display message
		// Page Title
		$document->setTitle( JText::_( 'REGISTRATION' ) );
		// Breadcrumb
		$pathway->addItem( JText::_( 'REGISTRATION' ));
		
		
		unset($row);unset($rowhistory);unset($up);
		if(isset($row2) && isset($rowhistory2)) unset($row2);unset($rowhistory2);
		if(isset($row3) && isset($rowhistory3))unset($row3);unset($rowhistory3);
		
		$message->text = JText::_( 'EMAIL_VALIDATION_LEFT' );		
		$message->title	= JText::_( 'REGISTRATION_SUCCESS' );
		$session =& JFactory::getSession();
		$extuser->sessSave($session);
		$view->assign('message', $message);
		echo '<img src="components/com_extendeduser/style/images/ok.png" alt="ok">' ;
		$view->display('message');
		echo '<a href="index.php?option=com_content&view=article&id=42">'.JText::_('DID_NOT_RECEIVE_EMAIL').'</a>';
	}
	
	/**
	 * Sets a proper registration date when all prerequisites are met. IE having a validated
	 * mobile phone and email.
	 */
	function setRegDate($activation = false, $extuser = false)
	{
		$db = & JFactory::getDBO();

		$query = 'SELECT id'
		. ' FROM #__users'
		. ' WHERE activation = '.$db->Quote($activation)
		. ' AND block = 1'
		. ' AND lastvisitDate = '.$db->Quote('0000-00-00 00:00:00');
		;

		$db->setQuery( $query );
		$id = intval( $db->loadResult() );

		if ($id){
			$user =& JUser::getInstance( (int) $id );

			$user->set('block', '0');
			$user->set('activation', '');
			
			if (!$user->save()){
				JError::raiseWarning( "SOME_ERROR_CODE", $user->getError() );
				return false;
			}
			
		}else{
			JError::raiseWarning( "SOME_ERROR_CODE", JText::_('REG_ACTIVATE_NOT_FOUND') );
			return false;
		}
		return true;
		
	}
	
	function activate()
	{
		$mainframe = JFactory::getApplication();

		// Initialize some variables
		$db			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$document   =& JFactory::getDocument();
		$pathway 	=& $mainframe->getPathWay();

		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$userActivation			= $usersConfig->get('useractivation');
		$allowUserRegistration	= $usersConfig->get('allowUserRegistration');

		// Check to see if they're logged in, because they don't need activating!
		if ($user->get('id')) {
			// They're already logged in, so redirect them to the home page
			$mainframe->redirect( 'index.php' );
		}

		if ($allowUserRegistration == '0' || $userActivation == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.html.php');
		$view = new UserViewRegister();

		$message = new stdClass();

		// Do we even have an activation string?
		$activation = JRequest::getVar('activation', '', '', 'alnum' );
		$activation = $db->getEscaped( $activation );

		if (empty( $activation )){
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
			$view->assign('message', $message);
			$view->display('message');
			return;
		}

		// Lets activate this user
		jimport('joomla.user.helper');
		//if (JUserHelper::activateUser($activation))

		if($this->setRegDate($activation)){
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_COMPLETE' );
		}else{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
		}

		$view->assign('message', $message);
		$view->display('message');
	}

	/**
	 * Password Reset Request Method
	 *
	 * @access	public
	 */
	function requestreset()
	{
		// Verify the submission
		if(!JRequest::getVar(JUtility::getToken(), 0, 'post', 'alnum')) {
			JError::raiseError(403, 'Request Forbidden');
		}
		// Get the input
		$email		= JRequest::getVar('email', null, 'post', 'string');

		// Get the model
		$model = &$this->getModel('Reset');

		// Request a reset
		if ($model->requestReset($email) === false)
		{
			$message = JText::sprintf('PASSWORD_RESET_REQUEST_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_extendeduser&view=reset&d='.date("Ymdhms"), $message);
			return false;
		}

		$this->setRedirect('index.php?option=com_extendeduser&view=reset&layout=confirm');
	}

	/**
	 * Password Reset Confirmation Method
	 *
	 * @access	public
	 */
	function confirmreset()
	{
		// Verify the submission
		if(!JRequest::getVar(JUtility::getToken(), 0, 'post', 'alnum')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		// Get the input
		$token = JRequest::getVar('token', null, 'post', 'alnum');

		// Get the model
		$model = &$this->getModel('Reset');

		// Verify the token
		if ($model->confirmReset($token) === false)
		{
			$message = JText::sprintf('PASSWORD_RESET_CONFIRMATION_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_extendeduser&view=reset&layout=confirm', $message);
			return false;
		}

		$this->setRedirect('index.php?option=com_extendeduser&view=reset&layout=complete');
	}

	/**
	 * Password Reset Completion Method
	 *
	 * @access	public
	 */
	function completereset()
	{
		// Verify the submission
		if(!JRequest::getVar(JUtility::getToken(), 0, 'post', 'alnum')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		// Get the input
		$password1 = JRequest::getVar('password1', null, 'post', 'string', JREQUEST_ALLOWRAW);
		$password2 = JRequest::getVar('password2', null, 'post', 'string', JREQUEST_ALLOWRAW);

		// Get the model
		$model = &$this->getModel('Reset');

		// Reset the password
		if ($model->completeReset($password1, $password2) === false)
		{
			$message = JText::sprintf('PASSWORD_RESET_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_extendeduser&view=reset&layout=complete', $message);
			return false;
		}

		$message = JText::_('PASSWORD_RESET_SUCCESS');
		$this->setRedirect('index.php', $message);
	}

	/**
	 * Username Reminder Method
	 *
	 * @access	public
	 */
	function remindusername()
	{
		// Verify the submission
		if(!JRequest::getVar(JUtility::getToken(), 0, 'post', 'alnum')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		// Get the input
		$email = JRequest::getVar('email', null, 'post', 'string');

		// Get the model
		$model = &$this->getModel('Remind');

		// Send the reminder
		if ($model->remindUsername($email) === false)
		{
			$message = JText::sprintf('USERNAME_REMINDER_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_extendeduser&view=remind&d='.date("Ymdhms"), $message);
			return false;
		}

		$message = JText::sprintf('USERNAME_REMINDER_SUCCESS', $email);
		$this->setRedirect('index.php?option=com_extendeduser&view=login', $message);
		
		// $message = '';
		// if ($model->remindUsername($email) === false) {
			// $message = JText::sprintf('USERNAME_REMINDER_FAILED', JText::_('COULD_NOT_FIND_EMAIL'));
			// $this->setRedirect('index.php?option=com_extendeduser&view=remind&d='.date("Ymdhms"), $message);
		// } else {
			// $message = JText::sprintf('USERNAME_REMINDER_SUCCESS', $email);
			// $this->setRedirect('index.php?option=com_extendeduser&view=login', $message);
		// }	
		
	}

	function _sendMail(&$user, $password)
	{
		$mainframe = JFactory::getApplication();

		$db		=& JFactory::getDBO();
		
		$name 		= $user->get('name');
		$email 		= $user->get('email');
		$username 	= $user->get('username');

		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$sitename 		= $mainframe->getCfg( 'sitename' );
		$useractivation = $usersConfig->get( 'useractivation' );
		$mailfrom 		= $mainframe->getCfg( 'mailfrom' );
		$fromname 		= $mainframe->getCfg( 'fromname' );
		$siteURL		= JURI::base();

		$subject 	= sprintf ( JText::_( 'ACCOUNT DETAILS FOR' ), $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		if ( $useractivation == 1 ){
			$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."index.php?option=com_extendeduser&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
		} else {
			$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
		}

		$message = html_entity_decode($message, ENT_QUOTES);
		// Send email to user
		if ($mailfrom != "" && $fromname != "") {
			$adminName2 = $fromname;
			$adminEmail2 = $mailfrom;
		} else {
			$query = 'SELECT name, email' .
					' FROM #__users' .
					' WHERE LOWER( usertype ) = "superadministrator"' .
					' OR LOWER( usertype ) = "super administrator"';
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			$row2 			= $rows[0];
			$adminName2 	= $row2->name;
			$adminEmail2 	= $row2->email;
		}

		/*
		echo "message: $message <br>";
		echo "subject: $subject <br>";
		echo "email: $email <br>";
		echo "adminName2: $adminName2 <br>";
		echo "adminEmail2: $adminEmail2 <br>";
		exit;
		*/
		
		JUtility::sendMail($adminEmail2, $adminName2, $email, $subject, $message, $mode=1);
		// Send notification to all administrators
		$subject2 = sprintf ( JText::_( 'ACCOUNT DETAILS FOR' ), $name, $sitename);
		$message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $adminName2, $sitename, $name, $email, $username);
		$subject2 = html_entity_decode($subject2, ENT_QUOTES);
		$message2 = html_entity_decode($message2, ENT_QUOTES);

		// get superadministrators id
		$authorize =& JFactory::getACL();
		$admins = $authorize->get_group_objects( 25, 'ARO' );

		foreach ( $admins['users'] AS $id )
		{
			$query = 'SELECT email, sendEmail' .
					' FROM #__users' .
					' WHERE id = '. (int) $id;
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			$row = $rows[0];

			if ($row->sendEmail) {
				JUtility::sendMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
			}
		}
	}
	
	/**** Change user profile 
	 * Can be used to allow user to change profile during session
	****/
	function changeprofile() {
		$mainframe = JFactory::getApplication();
		$user =& JFactory::getUser();
		
		/* $firstname = $user->firstname;
		$lastname = $user->lastname;
		$university_id = $user->university_id;
		$username = $user->username;
		$password = $user->passwd; */
		
		if ($user->applicant == 0) {
		// TO DO : se connecter à la table #__emundus_users_profiles pour vérifier que l'utilisateur est paramétré pour accéder à un autre profil
			$db =& JFactory::getDBO();
			$session =& JFactory::getSession();
			$pid = JRequest::getVar('pid', null, 'GET', null, 0);
			
			
			$query = 'UPDATE #__emundus_users SET profile='.$pid.' WHERE user_id='.$user->id;
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
			
			$query = 'SELECT * FROM #__emundus_setup_profiles WHERE id='.$pid;
			$db->setQuery($query);
			$row = $db->loadAssocList();

			$profile_label = $row[0]['label'];
			$menutype = $row[0]['menutype'];

			$user->profile = $pid;
			$user->profile_label = $profile_label;
			$user->menutype = $menutype;
		
			$this->setRedirect('index.php?option=com_extendeduser&task=edit', JText::_('PROFILE_SET_AS').' '.$profile_label, 'message');
		} else {
			$session =& JFactory::getSession();
			$session->destroy();
			$this->setRedirect('index.php?option=com_extendeduser&task=edit', JText::_('ERROR'), 'notice');
		}
	}
}

?>
