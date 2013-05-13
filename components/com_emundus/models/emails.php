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
		$this->_db =& JFactory::getDBO();
		$this->_user =& JFactory::getUser();
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
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/', '/\[USERNAME\]/', '/\[PASSWORD\]/', '/\[ACTIVATION_URL\]/', '/\[SITE_URL\]/');
		$replacements = array ($user->id, $user->name, $user->email, '<br />', $user->username, $passwd, JURI::base()."index.php?option=com_user&task=activate&activation=".$user->get('activation'), JURI::base());
		
		$strval = html_entity_decode(preg_replace($patterns, $replacements, $str), ENT_QUOTES);
		
		return $strval;
	}

	function sendMail($type=null)
	{
		$mail_type = JRequest::getVar('mail_type', null, 'POST', 'VARCHAR',0); 
		
		if (!isset($type)) $type = $mail_type;
		if($type == "evaluation_result") { 
			$mode = 1; // HTML
			$mail_cc = null;
			$mail_subject = JRequest::getVar('mail_subject', null, 'POST', 'VARCHAR', 0);
			$mail_body = JRequest::getVar('mail_body', null, 'POST', 'VARCHAR', JREQUEST_ALLOWHTML);
			$mail_from_id = $this->_user->id;
			$mail_from_name = $this->_user->name;
			$mail_from = $this->_user->email;
			$mail_to_id = JRequest::getVar('mail_to', null, 'POST', 'VARCHAR', 0);
			$student =& JFactory::getUser($student_id);
			$mail_to_name = $student->name;
			$mail_to = $student->email;
			$mail_attachments = JRequest::getVar('mail_attachments', null, 'POST', 'VARCHAR', 0); 
			
			if (!empty($mail_attachments)) $mail_attachments = explode(',', $mail_attachments);
			
			$sent = JUtility::sendMail($mail_from, $mail_from_name, $mail_to, $mail_subject, $mail_body, $mode, $mail_cc, null, @$mail_attachments);
		}
		else return false;

		if($sent) {
			$query = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES ('".$mail_from_id."', '".$mail_to_id."', ".$this->_db->quote($mail_subject).", ".$this->_db->quote($mail_body).", NOW())";
			$this->_db->setQuery( $query );
			$this->_db->query();

			// @TODO set evaluation result var to done in jos_emundus_campaign_applicant
			// @TODO Set body replacement
		}
		return true;
	}
}
?>