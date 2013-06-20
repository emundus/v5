<?php
/**
 * Profile Model for eMundus Component
 * 
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelEmails extends JModel
{
	var $_db = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getUser();
	}

	/**
	* Replace specifics tags in a string
	* @return string The body message with tag replacement
	*/
	function getEmail($lbl)
	{
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE lbl="'.mysql_real_escape_string($lbl).'"';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}
	
	function setBody($user, $str, $passwd='')
	{
		/*$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/', '/\[USERNAME\]/', '/\[PASSWORD\]/', '/\[ACTIVATION_URL\]/', '/\[SITE_URL\]/');
		$replacements = array ($user->id, $user->name, $user->email, '<br />', $user->username, $passwd, JURI::base()."index.php?option=com_user&task=activate&activation=".$user->get('activation'), JURI::base());*/
		$constants = $this->setConstants($user->id, null, $passwd);
		$strval = html_entity_decode(preg_replace($constants['patterns'], $constants['replacements'], $str), ENT_QUOTES);
		
		return $strval;
	}

	function setConstants($user_id, $post=null, $passwd='')
	{
		$current_user = & JFactory::getUser();
		$user = & JFactory::getUser($user_id);

		$patterns = array('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[USERNAME\]/', '/\[USER_ID\]/', '/\[USER_NAME\]/', '/\[USER_EMAIL\]/','/\n/', '/\[USER_USERNAME\]/', '/\[PASSWORD\]/', '/\[ACTIVATION_URL\]/', '/\[SITE_URL\]/','/\[APPLICANT_ID\]/', '/\[APPLICANT_NAME\]/', '/\[APPLICANT_EMAIL\]/', '/\[APPLICANT_USERNAME\]/', '/\[CURRENT_DATE\]/');
		$replacements = array($user->id, $user->name, $user->email, $user->username, $current_user->id, $current_user->name, $current_user->email, '<br />', $current_user->username, $passwd, JURI::base()."index.php?option=com_user&task=activate&activation=".$user->get('activation'), JURI::base(), $user->id, $user->name, $user->email, $user->username, date("F j, Y"));

		if(count($post) > 0) {
			foreach ($post as $key => $value) {
				$patterns[] = '/\['.$key.'\]/';
				$replacements[] = $value;
			}
		}

		$constants = array('patterns' => $patterns , 'replacements' => $replacements);

		return $constants;
	}

	function setTags($user_id, $post=null, $passwd='')
	{
		$db = &JFactory::getDBO();
		$user = & JFactory::getUser($user_id);

		$query = "SELECT tag, request FROM #__emundus_setup_tags";
		$db->setQuery($query);
		$tags = $db->loadAssocList();

		$constants = $this->setConstants($user_id, $post, $passwd);

		$patterns = array();
		$replacements = array(); 
		foreach ($tags as $tag) {
			$patterns[] = '/\['.$tag['tag'].'\]/'; 
			$value = preg_replace($constants['patterns'], $constants['replacements'], $tag['request']); 
			$request = explode('|', $value);
			if (count($request) > 1) {
				$query = 'SELECT '.$request[0].' FROM '.$request[1].' WHERE '.$request[2];
				$db->setQuery($query);
				$replacements[] = $db->loadResult();
			} else
				$replacements[] = $request[0];
		}

		$tags = array('patterns' => $patterns , 'replacements' => $replacements);

		return $tags;
	}

	function sendMail($type=null)
	{
		$mail_type = JRequest::getVar('mail_type', null, 'POST', 'VARCHAR',0); 
		
		if (!isset($type)) $type = $mail_type;
		if($type == "evaluation_result") { 
			$mode = 1; // HTML
			$mail_cc = null;
			$mail_subject = JRequest::getVar('mail_subject', null, 'POST', 'VARCHAR', 0);
			$mail_from_id = $this->_user->id;
			$mail_from_name = $this->_user->name;
			$mail_from = $this->_user->email;
			$mail_to_id = JRequest::getVar('mail_to', null, 'POST', 'VARCHAR', 0); 
			$student = JFactory::getUser($mail_to_id);
			$mail_to_name = $student->name;
			$mail_to = $student->email;
			$mail_body = $this->setBody($student, JRequest::getVar('mail_body', null, 'POST', 'VARCHAR', JREQUEST_ALLOWHTML), $passwd='');
			$mail_attachments = JRequest::getVar('mail_attachments', null, 'POST', 'VARCHAR', 0); 
			
			if (!empty($mail_attachments)) $mail_attachments = explode(',', $mail_attachments);
			
			$sent = JUtility::sendMail($mail_from, $mail_from_name, $mail_to, $mail_subject, $mail_body, $mode, $mail_cc, null, @$mail_attachments);
		}
		else return false;
//die(print_r($mail_attachments));
		if($sent) {
			$query = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES ('".$mail_from_id."', '".$mail_to_id."', ".$this->_db->quote($mail_subject).", ".$this->_db->quote($mail_body).", NOW())";
			$this->_db->setQuery( $query );
			$this->_db->query();
		}
		return true;
	}
}
?>