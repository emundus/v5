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
		$replacements = array($user->id, $user->name, $user->email, $user->username, $current_user->id, $current_user->name, $current_user->email, ' ', $current_user->username, $passwd, JURI::base()."index.php?option=com_user&task=activate&activation=".$user->get('activation'), JURI::base(), $user->id, $user->name, $user->email, $user->username, date("F j, Y"));

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

	function setTagsWord($user_id, $post=null, $passwd='')
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
			$patterns[] = $tag['tag']; 
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

			$message = array(
						'user_id_from' => $this->_user->id, 
						'user_id_to' => $mail_to_id, 
						'subject' => $mail_subject, 
						'message' => $mail_body
						);
			$this->logEmail($message);

		} elseif($type == "expert") { 
			require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
			$campaign = EmundusHelperfilters::getCampaignByID($campaign_id);
			$application = new EmundusModelApplication;
			$eMConfig = JComponentHelper::getParams('com_emundus');
			$formid = $eMConfig->get('expert_fabrikformid', '110');
			$documentid = $eMConfig->get('expert_document_id', '36');
			
			$mode = 1; // HTML
			$mail_cc = null;
			$mail_subject = JRequest::getVar('mail_subject', null, 'POST', 'VARCHAR', 0);
			$student_id = JRequest::getVar('student_id', null, 'POST', 'VARCHAR', 0);
			$campaign_id = JRequest::getVar('campaign_id', null, 'POST', 'VARCHAR', 0);
			$mail_from_id = $this->_user->id;
			$mail_from_name = $this->_user->name;
			$mail_from = $this->_user->email;
			/*$mail_to_id = JRequest::getVar('mail_to', null, 'POST', 'VARCHAR', 0); 
			$student = JFactory::getUser($mail_to_id);
			$mail_to_name = $student->name;*/
			$mail_to = explode(',', JRequest::getVar('mail_to', null, 'POST', 'VARCHAR', 0));
			$mail_body = $this->setBody($student, JRequest::getVar('mail_body', null, 'POST', 'VARCHAR', JREQUEST_ALLOWHTML), $passwd='');
			//
			// Replacement
			//
			$post = array(  'TRAINING_PROGRAMME' 	=> $campaign['label'], 
							'CAMPAIGN_START' 		=> $campaign['start_date'], 
							'CAMPAIGN_END' 			=> $campaign['end_date'], 
							'EVAL_DEADLINE' 		=> date("d/M/Y", mktime(0, 0, 0, date("m")+2, date("d"), date("Y")))
						);
			$tags = $this->setTags($student_id, $post);
			$mail_body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);

			$mail_attachments = JRequest::getVar('mail_attachments', null, 'POST', 'VARCHAR', 0); 
	
			if (!empty($mail_attachments)) 
				$mail_attachments = explode(',', $mail_attachments);
			
			foreach ($mail_to as $m_to) {
				$key1 = md5($this->rand_string(20).time());
				// 2. MAJ de la table emundus_files_request
				$attachment_id = $documentid; // document avec clause de confidentialité
				$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id, campaign_id, email) VALUES (NOW(), '.$student_id.', "'.$key1.'", "'.$attachment_id.'", '.$campaign_id.', '.$this->_db->quote($m_to).')';
				$this->_db->setQuery( $query );
				$this->_db->query();
				
				// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
				$link_accept = JURI::base().'index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&tableid=71&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id;
				$link_refuse = JURI::base().'index.php?option=com_emundus&task=decline&keyid='.$key1.'&sid='.$student_id.'&email='.$m_to.'&cid='.$campaign_id;

				$post = array(  'EXPERT_ACCEPT_LINK' 	=> $link_accept, 
								'EXPERT_REFUSE_LINK' 	=> $link_refuse
						);
				$tags = $this->setTags($student_id, $post);
				$mail_body = preg_replace($tags['patterns'], $tags['replacements'], $mail_body);

				$sent = JUtility::sendMail($mail_from, $mail_from_name, $m_to, $mail_subject, $mail_body, $mode, $mail_cc, null, @$mail_attachments);
				
				if ($sent) { 
					$row = array(
						'applicant_id' => $student_id, 
						'user_id' => $this->_user->id, 
						'reason' => JText::_( 'INFORM_EXPERTS' ), 
						'comment_body' => JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to
						);
					$message = array(
						'user_id_from' => $this->_user->id, 
						'user_id_to' => '', 
						'subject' => $mail_subject, 
						'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$m_to.'</i><br>'.$mail_body
						);
					$this->logEmail($message);
					
				} else {
					$row = array(
						'applicant_id' => $student_id, 
						'user_id' => $this->_user->id, 
						'reason' => JText::_( 'INFORM_EXPERTS' ), 
						'comment_body' => JText::_('ERROR').' '.JText::_('MESSAGE').' '.JText::_('NOT_SENT').' '.JText::_('TO').' '.$m_to
						);
				}

				$application->addComment($row);
			}
			
		} else 
			return false;

		return true;
	}

	// @description Log email send by the system or via the system
	// @param $row array of data
	function logEmail($row) {
		$query = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) 
						VALUES (".$this->_db->quote($row['user_id_from']).", ".$this->_db->quote($row['user_id_to']).", ".$this->_db->quote($row['subject']).", ".$this->_db->quote($row['message']).", NOW())";
		$this->_db->setQuery( $query );
		$this->_db->query();

	}

	//////////////////////////  SET FILES REQUEST  /////////////////////////////
	// 
	// Génération de l'id du prochain fichier qui devra être ajouté par le referent

	// 1. Génération aléatoire de l'ID
	function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789')
	{
	    $string = '';
	    for ($i = 0; $i < $len; $i++)
	    {
	        $pos = rand(0, strlen($chars)-1);
	        $string .= $chars{$pos};
	    }
	    return $string;
	}
	
}
?>