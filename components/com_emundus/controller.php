<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Jonas Lerebours - Benjamin Rivalland
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 
/**
 * eMundus Component Controller
 *
 * @package    eMundus
 * @subpackage Components
 */
class EmundusController extends JController {
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}

	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			if ($this->user->usertype == "Registered" && JRequest::getVar('view', null, 'GET' ) != 'renew_application') {
				$checklist = $this->getView( 'checklist', 'html' );die(JRequest::getVar('view', null, 'GET' ));
				$checklist->setModel( $this->getModel( 'checklist'), true );
				$checklist->display();
			} else {
				$default = 'users';
			}
			JRequest::setVar('view', $default );
		}
		
		parent::display();
		
    }
	
	function clear() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		EmundusHelperFilters::clear();
	}
	
	function getCampaign()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
		$db->setQuery( $query );
		$syear = $db->loadRow();
		
		return $syear[0];
	}
	
	function pdf(){
		$user = JFactory::getUser();
		$student_id = JRequest::getVar('user', null, 'GET', 'none',0);
		//$allowed = array("Super Users", "Administrator", "Editor", "Author", "Registered");
		if (!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id) && !EmundusHelperAccess::isPartner($user->id) && !EmundusHelperAccess::isEvaluator($user->id) && !EmundusHelperAccess::isApplicant($user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		require(JPATH_LIBRARIES.DS.'emundus'.DS.'pdf.php');
		unset($allowed);
		//$allowed = array("Super Users", "Administrator", "Editor", "Author");
		if(EmundusHelperAccess::isAdministrator($user->id) || EmundusHelperAccess::isCoordinator($user->id) || EmundusHelperAccess::isPartner($user->id) || EmundusHelperAccess::isEvaluator($user->id)) { 
			application_form_pdf(!empty($student_id)?$student_id:$user->id);
		}else{
			application_form_pdf($user->id);
			exit;
		}
		exit();
	}

	function delete() {
		$student_id = JRequest::getVar('sid', null, 'GET', 'none',0);
		$layout = JRequest::getVar('layout', null, 'GET', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		
		if ($student_id > 0 && JFactory::getUser()->usertype != 'Registered') 
			$user = JFactory::getUser($student_id);
		else
			$user = JFactory::getUser();
			
		if (isset($layout))
			$url = 'index.php?option=com_emundus&view=checklist&layout=attachments&sid='.$user->id.'&tmpl=component&Itemid='.$itemid;
		else
			$url ='index.php?option=com_emundus&view=checklist&Itemid='.$itemid;
			
		$chemin = EMUNDUS_PATH_ABS;
		//$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$id 	= JRequest::get('get');
		$id 	= $id['aid'];
		//$allowed = array("Super Users", "Administrator", "Editor");
		$user = JFactory::getUser();
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (EmundusHelperAccess::isAllowedAccessLevel($user->id,$access))
			$query 	= 'SELECT filename FROM #__emundus_uploads WHERE user_id = '.mysql_real_escape_string($user->id).' AND id = '.mysql_real_escape_string($id);
		else
			$query 	= 'SELECT filename FROM #__emundus_uploads WHERE user_id = '.mysql_real_escape_string($user->id).' AND can_be_deleted = 1 AND id = '.mysql_real_escape_string($id);
		$db->setQuery( $query );
		$filename = $db->loadResult();
		if (empty($filename)) { 
			$this->setRedirect($url, JText::_('Error'), 'error');
		} elseif (is_file($chemin.$user->id.DS.$filename)) {
			if (unlink($chemin.$user->id.DS.$filename)) {
				$query 	= 'DELETE FROM #__emundus_uploads WHERE id = '.mysql_real_escape_string($id).' AND user_id = '.mysql_real_escape_string($user->id);
				$db->setQuery( $query );
				$db->Query() or die($db->getErrorMsg());
				if (is_file($chemin.$user->id.DS.'tn_'.$filename)) unlink($chemin.$user->id.DS.'tn_'.$filename);
				$this->setRedirect($url, JText::_('File has been succesfully deleted'), 'message');
			} else {
				$this->setRedirect($url, JText::_('Error occured while deleting file'), 'error');
			}
		} else {
			$query 	= 'DELETE FROM #__emundus_uploads WHERE id = '.mysql_real_escape_string($id).' AND user_id = '.mysql_real_escape_string($user->id);
			$db->setQuery( $query );
			$db->Query() or die($db->getErrorMsg());
			$this->setRedirect($url, JText::_('File was not existing, thanks for checking that your other attachments are correctly uploaded'), 'notice');
		}
	}

	function upload() {
		$student_id = JRequest::getVar('sid', null, 'GET', 'none',0);
		$layout = JRequest::getVar('layout', null, 'GET', 'none',0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		//die($student_id.' : '.JFactory::getUser()->usertype);
		if ($student_id > 0 && JFactory::getUser()->usertype != 'Registered') 
			$user = JFactory::getUser($student_id);
		else
			$user = JFactory::getUser();
		// if($user->get('usertype') != 'Registered') {
			// $this->setRedirect('index.php?option=com_emundus', JText::_('Only students can access this function.'), 'error');
			// return;
		// }
		$chemin 		= EMUNDUS_PATH_ABS;
		$post 			= JRequest::get('post');
		$attachments 	= $post['attachment'];
		$descriptions 	= $post['description'];
		$labels		 	= $post['label'];
		$files 			= JRequest::get('files');
		$files 			= $files['nom'];
		
		//$can_be_deleted = JRequest::getVar('can_be_deleted', 1, 'POST', 'none',0);
		//$can_be_viewed  = JRequest::getVar('can_be_viewed', 1, 'POST', 'none',0);
		
		//$user 			= JFactory::getUser();
		$db 			= JFactory::getDBO();
		$query 			= '';
		$nb = 0;

		if(!file_exists(EMUNDUS_PATH_ABS.$user->id)) {	
			if (!mkdir(EMUNDUS_PATH_ABS.$user->id) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html')) 
					return JError::raiseWarning(500, 'Unable to create user file');
		}

		for($i = 0; $i<count($files['name']);$i++) {
			if (empty($files['name'][$i])) continue;
			$test = 'SELECT UPPER(allowed_types) FROM #__emundus_setup_attachments WHERE id = '.$attachments[$i];
			$db->setQuery( $test );
			$ext = $db->loadResult() or die($db->getErrorMsg());
			if (strpos($ext, strtoupper(end(explode(".", $files['name'][$i]))))===FALSE) {
				JFactory::getApplication()->enqueueMessage(JText::_("File ").$files['name'][$i].JText::_(" type is not allowed, please send a doc with type:").$ext."\n", 'error');  
				continue;
			}
			
			//size > 0
			if (($files['size'][$i])==0) {
				JFactory::getApplication()->enqueueMessage(JText::_("File ").$files['name'][$i].JText::_(" size is not allowed, please check out your filesize")."\n", 'error');  
				continue;
			}
			
			if (!empty($files['error'][$i])) {   
				switch ($files['error'][$i]) {
					case 1:
					   JFactory::getApplication()->enqueueMessage(JText::_("File ").$files['name'][$i].JText::_(" is bigger than the authorized size!"), 'error');   
					   break;
					case 2:
					   JFactory::getApplication()->enqueueMessage(JText::_("File ").$files['name'][$i].JText::_(" is too big!\n"), 'error');   
					   break;
					case 3:
					   JFactory::getApplication()->enqueueMessage(JText::_("File ").$files['name'][$i].JText::_(" upload has been interrupted.\n"), 'error');   
					   break;
					case 4:
					   JFactory::getApplication()->enqueueMessage(JText::_("File ").$files['name'][$i].JText::_(" is not correct.\n"), 'error');   
					   break;
					default:
				}
			} elseif (isset($files['name'][$i])&&($files['error'][$i] == UPLOAD_ERR_OK)) {
				$paths = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($user->name,ENT_NOQUOTES,'UTF-8'))));
				$paths .= $labels[$i].rand().'.'.end(explode(".", $files['name'][$i]));
				if (move_uploaded_file(	$files['tmp_name'][$i], $chemin.$user->id.DS.$paths)) {
					$can_be_deleted = @$post['can_be_deleted_'.$attachments[$i]]!=''?$post['can_be_deleted_'.$attachments[$i]]:JRequest::getVar('can_be_deleted', 1, 'POST', 'none',0);
					$can_be_viewed = @$post['can_be_viewed_'.$attachments[$i]]!=''?$post['can_be_viewed_'.$attachments[$i]]:JRequest::getVar('can_be_viewed', 1, 'POST', 'none',0);
					$query .= '('.$user->id.', '.$attachments[$i].', \''.$paths.'\', \''.$descriptions[$i].'\', '.$can_be_deleted.', '.$can_be_viewed.', '.$user->campaign_id.'),';
					$nb++;
				}
				if ($labels[$i]=="_photo") {
					$checkdouble_query = 'SELECT count(user_id) FROM #__emundus_uploads WHERE attachment_id=(SELECT id FROM #__emundus_setup_attachments WHERE lbl="_photo") AND user_id='.$user->id;
					$db->setQuery($checkdouble_query);
					if ($db->loadResult()) {
						$query = '';
					} else {
						$pathToThumbs = EMUNDUS_PATH_ABS.$user->id.DS.$paths;
						$file_src = EMUNDUS_PATH_ABS.$user->id.DS.$paths;
						//$img = imagecreatefromjpeg(EMUNDUS_PATH_ABS.$user->id.DS.$paths);
						list($w_src, $h_src, $type) = getimagesize($file_src);  // create new dimensions, keeping aspect ratio
						//$ratio = $w_src/$h_src;
						//if ($w_dst/$h_dst > $ratio) {$w_dst = floor($h_dst*$ratio);} else {$h_dst = floor($w_dst/$ratio);}
					
						switch ($type){
							case 1:   //   gif -> jpg
							$img = imagecreatefromgif($file_src);
							break;
						  case 2:   //   jpeg -> jpg
							$img = imagecreatefromjpeg($file_src);
							break;
						  case 3:  //   png -> jpg
							$img = imagecreatefrompng($file_src);
							break;
						 }
						//$width = imagesx( $img );
						//$height = imagesy( $img );
						$new_width = 200;
						$new_height = floor( $h_src * ( $new_width / $w_src ) );
						$tmp_img = imagecreatetruecolor( $new_width, $new_height );
						imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $w_src, $h_src );
						imagejpeg( $tmp_img, $chemin.$user->id.DS.'tn_'.$paths);
						$user->avatar = $paths;
					}
				}
			}
		}
		if(!empty($query)) {
			$query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id) VALUES '.substr($query,0,-1);
			$db->setQuery( $query );
			$db->Query() or die($db->getErrorMsg());
			JFactory::getApplication()->enqueueMessage($nb.($nb>1?' '.JText::_("FILE_BEEN_UPLOADED"):' '.JText::_("FILES_BEEN_UPLOADED")), 'message');
		}
	if (isset($layout))
		$this->setRedirect('index.php?option=com_emundus&view=checklist&layout=attachments&sid='.$user->id.'&tmpl=component&Itemid='.$itemid);
	else
		$this->setRedirect('index.php?option=com_emundus&view=checklist&Itemid='.$itemid);
	}

/***********************************
 ** Update profile for Applicants
 ***********************************/
function updateprofile() {
		$user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('Only administrator can access this function.'), 'error');
			return;
		}
		$attachment_id = JRequest::getVar('aid', $default=null, $hash= 'POST', $type= 'array', $mask=0);
		$attachment_selected = JRequest::getVar('as', $default=null, $hash= 'POST', $type= 'array', $mask=0);
		$attachment_displayed = JRequest::getVar('ad', $default=null, $hash= 'POST', $type= 'array', $mask=0);
		$attachment_required = JRequest::getVar('ar', $default=null, $hash= 'POST', $type= 'array', $mask=0);
		$attachment_bank_needed = JRequest::getVar('ab', $default=null, $hash= 'POST', $type= 'array', $mask=0);
		$profile_id = JRequest::getVar('pid', $default=null, $hash= 'POST', $type= 'none', $mask=0);
		if($profile_id != JRequest::getVar('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0) || !is_numeric($profile_id) || floor($profile_id) != $profile_id || $profile_id <= 0) {
			$this->setRedirect('index.php', 'Error', 'error');
			return;
		}
		if(!empty($attachment_selected)) {
			$attachments = array();
			foreach($attachment_id as $id) {
				$a->selected = @in_array($id, $attachment_selected);
				$a->displayed= @in_array($id, $attachment_displayed);
				$a->required = @in_array($id, $attachment_required);
				$a->bank_needed = @in_array($id, $attachment_bank_needed);
				$attachments[$id] = $a;
				unset($a);
			}
			
		}

		$db = JFactory::getDBO();
// ATTACHMENTS
		$db->setQuery('DELETE FROM #__emundus_setup_attachment_profiles WHERE profile_id = '.$profile_id);
		$db->Query() or die($db->getErrorMsg());
		if(isset($attachments)) {
			$query = 'INSERT INTO #__emundus_setup_attachment_profiles (`profile_id`, `attachment_id`, `displayed`, `mandatory`, `bank_needed`) VALUES';
			foreach($attachments as $id => $attachment) {
				if(!$attachment->selected) continue;
				$query .= '('.$profile_id.', '.$id.', ';
				$query .= $attachment->displayed?'1':'0';
				$query .= ', ';
				$query .= $attachment->required?'1':'0';
				$query .= ', ';
				$query .= $attachment->bank_needed?'1':'0';
				$query .= '),';
			}
			$db->setQuery( substr($query, 0, -1) );
			$db->Query() or die($db->getErrorMsg());
		}
// FORMS
		$Itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
		$this->setRedirect('index.php?option=com_emundus&view=profile&rowid='.$profile_id.'&Itemid='.$Itemid, '', '');
}
	/*function adduser() {
		$current_user = JFactory::getUser();
		//$Itemid=JSite::getMenu()->getActive()->id;
		$Itemid="9";
		if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) {
			$this->setRedirect('index.php', JText::_('Only administrator can access this function.'), 'error');
			return;
		}
		$mainframe = JFactory::getApplication();
		// Get required system objects
		$user 		= clone(JFactory::getUser(0));
		$pathway 	= $mainframe->getPathway();
		$config		= JFactory::getConfig();
		$authorize	= JFactory::getACL();
		$document   = JFactory::getDocument();
		$db = JFactory::getDBO();
		jimport( 'joomla.user.helper' );
		$usersConfig = JComponentHelper::getParams( 'com_users' );
		
		// Get the user data.
		$requestData = JRequest::getVar('jform', array(), 'post', 'array');
		//die(print_r($requestData));
		
		$passwd = JUserHelper::genRandomPassword();
		$requestData['password'] = $passwd;
		$requestData['password2'] = $passwd;
		
		
		$query = 'SELECT acl_aro_groups FROM `#__emundus_setup_profiles` WHERE id='.$requestData['profile'];
		$db->setQuery($query);
		$res = $db->loadObject();
		
		$requestData['groups'] = $res->acl_aro_groups;
		
		$model = $this->getModel('profile');
		$tacl = $model->getProfile($requestData['profile']); 
		// Bind the post array to the user object
		$requestData['gid']=$tacl->acl_aro_groups;
		//$newuser['gid']=$authorize->get_group_id( '', $newuser['usertype'], 'ARO' );
		if (!$user->bind( $requestData )) {
			JError::raiseError( 500, $user->getError());
			$this->setRedirect('index.php?option=com_emundus&view=users&Itemid='.$Itemid);
		}
		// Set some initial user values
		$user->set('id', 0);
		
		$user->set('registerDate', date('Y-m-d H:i:s'));

		// If there was an error with registration, set the message and display form
		if ( !$user->save() ) {
		 	$document->setTitle( JText::_( 'Registration' ) );
			$this->setRedirect('index.php?option=com_emundus&view=users&Itemid='.$Itemid,$user->getError(),'error');
			return;
		}
	
		if (!mkdir(EMUNDUS_PATH_ABS.$user->id.DS) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html')) {
			return JError::raiseWarning(500, 'Unable to create user file');
		}
		
		$groups = $requestData['groups'];
		foreach($groups as $grp) {
			$query = 'INSERT INTO `#__emundus_groups` (`user_id`, `group_id`)
						VALUES ('.$user->id.', '.$grp.')';
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
		}
		
		
		// Envoi de la confirmation de création de compte par email
		$model = $this->getModel('emails');
		$email = $model->getEmail('new_account');
		//$email = $model->getEmail('register');
		$body = $model->setBody($user, $email->message, $passwd);
		
		JUtility::sendMail($email->emailfrom, $email->name, $user->email, $email->subject, $body, 1);

		$this->setRedirect('index.php?option=com_emundus&view=users&Itemid='.$Itemid, JText::_('Users successfully added'), 'message');
	}*/
	function adduser(){
		// add to jos_emundus_users; jos_users; jos_emundus_groups; jos_users_profiles; jos_users_profiles_history
		$current_user = JFactory::getUser();
		$db = JFactory::getDBO();
		
		if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) {
			$this->setRedirect('index.php', JText::_('Only administrator can access this function.'), 'error');
			return;
		}
		$itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
		$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
		$firstname = JRequest::getVar('firstname', null, 'POST', 'none',0);
		$lastname = JRequest::getVar('lastname', null, 'POST', 'none',0);
		$username = JRequest::getVar('login', null, 'POST', 'none',0);
		$name = strtolower($firstname).' '.strtoupper($lastname);
		$email = JRequest::getVar('email', null, 'POST', 'none',0);
		$profile = JRequest::getVar('profile', null, 'POST', 'none',0);
		$acl_aro_groups = JRequest::getVar('acl_aro_groups', null, 'POST', 'none',0);
		$univ_id = JRequest::getVar('university_id', null, 'POST', 'none',0);
		$groups = JRequest::getVar('cb_groups', null, 'POST', 'array',0);
		//$params='{"admin_language":"","language":"","editor":"","helpsite":"","timezone":""}';
		$password = JUserHelper::genRandomPassword();
		// die(var_dump($acl_aro_groups));		
		$user = clone(JFactory::getUser(0));
		$user->name=$name;
		$user->username=$username;
		$user->email=$email;
		$user->password=md5($password);
		$user->registerDate=date('Y-m-d H:i:s');
		$user->lastvisitDate=date('Y-m-d H:i:s');
		$user->block=0;
		
		$other_param['firstname']=$firstname;
		$other_param['lastname']=$lastname;
		$other_param['profile']=$profile;
		$other_param['univ_id']=$univ_id;
		$other_param['groups']=$groups;
		
		$model = $this->getModel('users');
		$acl_aro_groups = $model->getDefaultGroup($profile);
		$user->groups=$acl_aro_groups;

		$usertype = $model->found_usertype($acl_aro_groups[0]);
		$user->usertype=$usertype;
		
		$model->adduser($user, $other_param);

		if (!mkdir(EMUNDUS_PATH_ABS.$user->id.DS) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$user->id.DS.'index.html')) {
			return JError::raiseWarning(500, 'Unable to create user file');
		}

		// Envoi de la confirmation de création de compte par email
		$model = $this->getModel('emails');
		$email = $model->getEmail('new_account');

		$body = $model->setBody($user, $email->message, $password);
		
		JUtility::sendMail($email->emailfrom, $email->name, $user->email, $email->subject, $body, 1);
		
		$this->setRedirect('index.php?option=com_emundus&view=users&Itemid='.$itemid);
	}

	function delusers($reqids = null) {
		$Itemid=JSite::getMenu()->getActive()->id;
		$user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($user->id)&& !EmundusHelperAccess::isCoordinator($user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		$db = JFactory::getDBO();
		$ids = JRequest::getVar('ud', null, 'POST', 'array', 0);
		if(empty($ids) && !empty($reqids)) {
			$ids = $reqids;
		}
		JArrayHelper::toInteger( $ids, null );
		if(!empty($ids)) {
			// die('Would have been deleted : <pre>'.print_r($ids, true).'</pre>'); // EN CAS DE TESTS PAS LA PEINE DE SUPPRIMER TOUT LE MONDE ;)
			if(in_array(62, $ids)) JError::raiseError(500, JText::_('You cannot delete administrator'));
			$db->setQuery('DELETE FROM #__emundus_users WHERE user_id IN ('.implode(',',$ids).')');
			$db->Query() or die($db->getErrorMsg());
			
			foreach ($ids as $id) {
				$userToDelete = JUser::getInstance($id);
				if (!$userToDelete->delete())
					die(JText::_('CANNOT_DELETE_USER'). ' : '.$userToDelete->name);
			}
		}
		$this->setRedirect('index.php?option=com_emundus&view=users&Itemid='.$Itemid, JText::_('Users and their data have been successfully deleted. Total : ').count($ids), 'message');
	}


	function edituser() {
		$db = JFactory::getDBO();
		$current_user = JFactory::getUser();
		$Itemid=JSite::getMenu()->getActive()->id; 
		//$Itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);die($Itemid);
		if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id)) {
			$this->setRedirect('index.php', JText::_('ACCESS_DENIED'), 'error');
			return;
		}
		$authorize	= JFactory::getACL();
		$newuser['id'] = JRequest::getVar('user_id', null, 'POST', '', 0);
		$newuser['firstname'] = JRequest::getVar('firstname', null, 'POST', '', 0);
		$newuser['lastname'] = JRequest::getVar('lastname', null, 'POST', '', 0);
		$newuser['username'] = JRequest::getVar('login', null, 'POST', '', 0);
		$newuser['name'] = $newuser['firstname'].' '.$newuser['lastname'];
		$newuser['email'] = JRequest::getVar('email', null, 'POST', '', 0);
		$newuser['schoolyear'] = JRequest::getVar('schoolyear', null, 'POST', '', 0);
		$newuser['profile'] = JRequest::getVar('profile', null, 'POST', '', 0);
		$newuser['university_id'] = JRequest::getVar('university_id', null, 'POST', '', 0);

		$model = $this->getModel('profile');
		$tacl = $model->getProfile($newuser['profile']);
		// Bind the post array to the user object
		$newuser['gid']=$tacl->acl_aro_groups;
		//$newuser['gid']=$authorize->get_group_id( '', $newuser['usertype'], 'ARO' );

		$user = JUser::getInstance($newuser['id']);
		
		if (!$user->bind( $newuser )) {
			JError::raiseError( 500, $user->getError());
			$this->setRedirect('index.php?option=com_emundus&view=users&edit=1&tmpl=component&rowid='.$newuser['id'].'&Itemid='.$Itemid);
		}
		if ( !$user->save() ) {
		 	//$document->setTitle( JText::_( 'Registration' ) );
			$this->setRedirect('index.php?option=com_emundus&view=users&edit=1&tmpl=component&rowid='.$newuser['id'].'&Itemid='.$Itemid,$user->getError(),'error');
			return;
		}
		$db = JFactory::getDBO();
		$db->setQuery('SELECT COUNT(*) FROM #__emundus_users WHERE user_id = '.mysql_real_escape_string($newuser['id']));
		$compte = $db->loadResultArray();
		$compte = $compte[0];
		
		if($compte>0) {
			$db->setQuery('UPDATE #__emundus_users SET firstname = "'.mysql_real_escape_string($newuser['firstname']).'", 
														lastname = "'.mysql_real_escape_string($newuser['lastname']).'", 
														profile = "'.mysql_real_escape_string($newuser['profile']).'", 
														schoolyear = "'.mysql_real_escape_string($newuser['schoolyear']).'", 
														university_id = "'.mysql_real_escape_string($newuser['university_id']).'"
									WHERE user_id = '.mysql_real_escape_string($newuser['id']));
			$db->Query();
		} else {
			$theuser = JUSER::getInstance($newuser['id']);
// @TODO Get schoolyear from affected campaign
			$db->setQuery('SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE profile_id = '.mysql_real_escape_string($newuser['profile']).' ORDER BY id DESC');
			$schoolyear = $db->loadResult();
			$query = 'INSERT INTO `#__emundus_users` (`user_id`, `registerDate`, `firstname`, `lastname`, `profile`, `schoolyear`, `disabled`, `disabled_date`, `cancellation_date`, `cancellation_received`)
							VALUES ('.$theuser->id.', "'.$theuser->registerDate.'", "'.mysql_real_escape_string($newuser['firstname']).'", "'.mysql_real_escape_string($newuser['lastname']).'", 
										'.mysql_real_escape_string($newuser['profile']).', "'.$schoolyear.'", 0, "0000-00-00 00:00:00", "0000-00-00", "0000-00-00")';
			$db->setQuery($query);
			$db->Query();
		}
		//
		// Affectation a/aux groupe(s)
		$groups = JRequest::getVar('cb_groups', null, 'POST', 'array', 0);		
			$db->setQuery('DELETE FROM `#__emundus_groups` WHERE user_id='.mysql_real_escape_string($newuser['id']));
			$db->Query() or die($db->getErrorMsg());
			foreach($groups as $grp) {
				$query = 'INSERT INTO `#__emundus_groups` (`user_id`, `group_id`)
							VALUES ('.$user->id.', '.$grp.')';
				$db->setQuery($query);
				$db->Query() or die($db->getErrorMsg());
			}

		if($user->profile == 999) {
			$this->_blockuser($user->id);
		}else{
			$this->affectGroup($user->id,$user->profile);
		}
		
		
		//
		// Affectation a/aux profil(s) secondaires
		$profiles = JRequest::getVar('cb_profiles', null, 'POST', 'array', 0);
			$db->setQuery('DELETE FROM `#__emundus_users_profiles` WHERE user_id='.mysql_real_escape_string($newuser['id']));
			$db->Query() or die($db->getErrorMsg());
			$profile_tmp='';
			foreach($profiles as $p) {
				if($profile_tmp != $p){
					$query = 'INSERT INTO `#__emundus_users_profiles` (`user_id`, `profile_id`)
								VALUES ('.$user->id.', '.$p.')';
					$db->setQuery($query);
					$db->Query() or die($db->getErrorMsg());
				}
				$profile_tmp=$p;
			}
		$Itemid=JSite::getMenu()->getActive()->id;
		$this->setRedirect('index.php?option=com_emundus&view=users&rowid='.$newuser['id'].'&edit=1&tmpl=component&Itemid='.$Itemid, JText::_('Users successfully updated'), 'message');
	}
	
	function affectGroup($user_id,$profile_id){
		$db = JFactory::getDBO();
		$query='SELECT acl_aro_groups FROM #__emundus_setup_profiles WHERE id='.$profile_id;
		$db->setQuery($query);
		$group_id = $db->loadResult();
		
		$query1 = 'UPDATE #__user_usergroup_map 
		SET group_id='.$group_id.'  
		WHERE user_id='.$user_id;
		$db->setQuery($query1);
		$db->Query() or die($db->getErrorMsg());
	}
		
	function get_id(){
		$db = JFactory::getDBO();
		$value = $_GET['link'];
		switch ($value){
			case "fill":
				$query = "SELECT id
					FROM #__menu
					WHERE menutype = 'menu_profile9'
					AND ordering = (
					SELECT MIN( ordering )
					FROM #__menu
					WHERE menutype = 'menu_profile9'
					AND id <>20 )";
				$db->setQuery($query);
				$itemid = $db->loadResult();
				$end_link='fabrik&view=form&fabrik=12&tableid=11&rowid=-1&usekey=user&random=0&Itemid='.$itemid;			
			break;
			case "upload":
				$query = "SELECT id
					FROM jos_menu
					WHERE menutype = 'menu_profile9'
					AND ordering = (
					SELECT MAX( ordering )
					FROM jos_menu
					WHERE menutype = 'menu_profile9'
					AND parent = 0 )";
				$db->setQuery($query);
				$itemid = $db->loadResult();
				$end_link='emundus&view=checklist&Itemid='.$itemid;
			break;
			case "send":
				if (!$this->need) {
					//$Itemid=JSite::getMenu()->getActive()->id;
					$end_link='fabrik&view=form&fabrik=22&tableid=22&rowid=&r=1&Itemid='.$itemid;
				}else{
					$end_link='fabrik&view=form&fabrik=16&tableid=15&rowid=&r=1&Itemid=860';
				}
			break;
		}
		$this-> setRedirect('index.php?option=com_'.$end_link);
	}
	
	/**
	 * Get application form elements to display in XLS file
	 */
	function send_elements() {
		$view = JRequest::getVar('v', null, 'GET');
		// Starting a session.
		$session = JFactory::getSession();
		$cid = $session->get( 'uid' );
		$quick_search = $session->get( 'quick_search' );
		
		$user = JFactory::getUser();
		//$allowed = array("Super Users", "Administrator", "Editor");
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($user->id, $access)) {
			die(JText::_('ACCESS_DENIED'));
		}
		require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_xls'.DS.'xls_'.$view.'.php');
		$elements = JRequest::getVar('ud', null, 'POST', 'array', 0);
		export_xls($cid, $elements); 
		exit();
	}
	
	function transfert_view($reqids=array()){
		//$allowed = array("Super Users", "Administrator", "Editor");
		$view = JRequest::getVar('v', null, 'GET');
		
		$profile = JRequest::getVar('profile', null, 'POST', 'none', 0);
		$finalgrade = JRequest::getVar('finalgrade', null, 'POST', 'none', 0);
		$quick_search = JRequest::getVar('s', null, 'POST', 'none',0);
		$gid = JRequest::getVar('groups', null, 'POST', 'none', 0);
		$evaluator = JRequest::getVar('user', null, 'POST', 'none', 0);
		$engaged = JRequest::getVar('engaged', null, 'POST', 'none', 0);
		$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none', 0);
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$miss_doc = JRequest::getVar('missing_doc', null, 'POST', 'none',0);
		$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
		$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
		$comments = JRequest::getVar('comments', null, 'POST', 'none', 0);
		$complete = JRequest::getVar('complete', null, 'POST', 'none',0);
		$validate = JRequest::getVar('validate', null, 'POST', 'none',0);
		$cid = JRequest::getVar('ud', null, 'POST', 'array', 0);
		/*
		foreach($cids_params as $cid_params){
			$params=explode('|',$cid_params);
			$cid[]=$params[0];
		}*/
		
		// Starting a session.
		$session = JFactory::getSession();
		if($cid) $session->set( 'uid', $cid );
		if($profile) $session->set( 'profile', $profile );
		if($finalgrade) $session->set( 'finalgrade', $finalgrade );
		if($quick_search) $session->set( 'quick_search', $quick_search );
		if($gid) $session->set( 'groups', $gid );
		if($evaluator) $session->set( 'evaluator', $evaluator );
		if($engaged) $session->set( 'engaged', $engaged );
		if($schoolyears) $session->set( 'schoolyears', $schoolyears );
		if($miss_doc) $session->set( 'missing_doc', $miss_doc );
		if($search) $session->set( 's_elements', $search );
		if($search_values) $session->set( 's_elements_values', $search_values );
		if($comments) $session->set( 'comments', $comments );
		if($complete) $session->set( 'complete', $complete );
		if($validate) $session->set( 'validate', $validate );
		
		$this->setRedirect('index.php?option=com_emundus&view=export_select_columns&v='.$view.'&Itemid='.$itemid);
	}
	
	
	/**
	 * Check if user can or not open PDF file
	 */
	function getfile() {
		//$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',"checklist");
		//$view = JRequest::getVar('view', null, 'GET');
		$url = $_GET['u']; 
		$urltab = explode('/', $url);
		/*$file = explode('.', $urltab[4]);
		$ext = $file[1];*/
		$cpt = count($urltab);
		$uid = $urltab[$cpt-2]; 
		$current_user = JFactory::getUser(); 
//die( var_dump((!EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id))) && (EmundusHelperAccess::isApplicant($current_user->id) && $current_user->id != $uid));
//die( var_dump( !EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id) && (EmundusHelperAccess::isApplicant($current_user->id) && $current_user->id != $uid) ));

		if( !EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id) && (EmundusHelperAccess::isApplicant($current_user->id) && $current_user->id != $uid) ) { 
			JError::raiseWarning( 500, JText::_( 'ACCESS_DENIED' ) );
		} else {
			$file = JPATH_BASE.DS.$url;
			if (file_exists($file)) {
				$mime_type = $this->get_mime_type($file);
				header('Content-type: application/'.$mime_type);
				header('Content-Disposition: inline; filename='.basename($file));
				header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: pre-check=0, post-check=0, max-age=0');
				header('Pragma: anytextexeptno-cache', true);
				header('Cache-control: private');
				header('Expires: 0');
				//header('Content-Transfer-Encoding: binary');
				//header('Content-Length: ' . filesize($file));
				//header('Accept-Ranges: bytes');

				ob_clean();
				flush();
				readfile($file);
				exit;
			} else {
				JError::raiseWarning( 500, JText::_( 'FILE_NOT_FOUND' ).' '.$file );
				//$this->setRedirect('index.php?option=com_emundus&view='.$view.'&Itemid='.$Itemid);
			}
		}
	}

	function get_mime_type($filename, $mimePath = '../etc') {
	   $fileext = substr(strrchr($filename, '.'), 1);
	   if (empty($fileext)) return (false);
	   $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
	   $lines = file("$mimePath/mime.types");
	   foreach($lines as $line) {
	      if (substr($line, 0, 1) == '#') continue; // skip comments
	      $line = rtrim($line) . " ";
	      if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
	      return ($matches[1]);
	   }
	   return (false); // no match at all
	} 
	
	function sendmail($nb_email_per_batch = null){
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db	= JFactory::getDBO();
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$keyid = JRequest::getVar('keyid', null, 'GET', 'none',0);
		//$allowed = array("Super Users", "Administrator");
		$eMConfig = JComponentHelper::getParams('com_emundus');

		$model = $this->getModel('emailalert');
		
		if(EmundusHelperAccess::isAdministrator($user->id) && EmundusHelperAccess::isCoordinator($user->id) && EmundusHelperAccess::isPartner($user->id) && EmundusHelperAccess::isEvaluator($user->id)) {
			if ($nb_email_per_batch == null)
				$nb_email_per_batch = $eMConfig->get('nb_email_per_batch', '30');
					
			//Selection des mails à envoyer : table jos_emundus_emailtosend
			$query = '	SELECT m.user_id_from, m.user_id_to, m.subject, m.message, u.email 
						FROM #__messages m, #__users u 
						WHERE m.user_id_to = u.id
						AND m.state = 1
						LIMIT 0,'.$nb_email_per_batch;
			$db->setQuery( $query );
			$db->query();
			
			if($db->getNumRows() == 0){
				$this->setRedirect('index.php?option=com_fabrik&view=table&tableid=90&Itemid='.$itemid);
			}else{
				$mail=$db->loadObjectList();
				
				foreach($mail as $m){
					$mail_subject = $m->subject;
					//$from = JFactory::getUser($m->user_id_from);
					$emailfrom = $app->getCfg('mailfrom');
					$fromname = $app->getCfg('fromname');
					$recipient = $m->email;
					$body = $m->message;
					if(JUtility::sendMail( $emailfrom, $fromname, $recipient, $mail_subject, $body, true)){
						usleep(100);
						$query = 'UPDATE #__messages SET state = 0 WHERE user_id_to ='.$m->user_id_to;
						$db->setQuery($query);
						$db->Query();
					}
				}
				$this->setRedirect('index.php?option=com_emundus&task=sendmail&keyid='.$keyid.'&Itemid='.$itemid);
			}
		}
	}

	function sendmail_applicant(){ 
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$sid = JRequest::getVar('mail_to', null, 'POST', 'INT',0);
		$campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'INT',0);
		$model = $this->getModel('emails');
		$email = $model->sendmail();

		$model = $this->getModel('campaign');
		$email = $model->setResultLetterSent($sid, $campaign_id);
		

		$this->setRedirect('index.php?option=com_emundus&view=application&Itemid='.$itemid.'&sid='.$sid.'&tmpl=component');
	}
	
	/*
	** @description Validate / Unvalidate a column from table (used in administrative validation). Ajax request
	** @return string HTML to display in page for action block indexed by user ID.
	*/
	function ajax_validation() {
		//$menu=JSite::getMenu()->getActive(); die(print_r($menu));
		//$access=!empty($menu)?$menu->access : 0;
		$user = JFactory::getUser();
		if (!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		$uid = JRequest::getVar('uid', null, 'GET', 'INT');
		$validate = JRequest::getVar('validate', null, 'GET', 'INT');
		$cible = JRequest::getVar('cible', null, 'GET', 'CMD'); 
		$data = explode('.', $cible);
		if(count($data)>3)	$and = ' AND `campaign_id`='.$data[3];
		else $and = '';
		if($data[0] == "jos_emundus_final_grade") $column = "student_id";
		else $column = 'user';

//		print_r($data);
		if(!empty($uid) && is_numeric($uid)) {
			$value = abs((int)$validate-1);
			$db = JFactory::getDBO();
			$query = 'UPDATE `'.$data[0].'` SET `'.$data[1].'`='.$db->Quote($value).' WHERE `'.$column.'` = '.$db->Quote((int)$uid). $and; 
			$db->setQuery($query);
			$db->Query();
			if ($value > 0){
				$img = 'tick.png';
				$btn = 'unvalidate|'.$uid;
				$alt = JText::_('VALIDATED').'::'.JText::_('VALIDATED_NOTE');
			} else {
				$img = 'publish_x.png';
				$btn = 'validate|'.$uid;
				$alt = JText::_('UNVALIDATED').'::'.JText::_('UNVALIDATED_NOTE');
			}
			echo '<span class="hasTip" title="'.$alt.'">
					<input type="image" src="'.JURI::Base().'/media/com_emundus/images/icones/'.$img.'" onclick="validation('.$uid.', \''.$value.'\', \''.$cible.'\');" ></span> ';
		} else echo JText::_('ERROR'); 
		
	}
	
}
?>