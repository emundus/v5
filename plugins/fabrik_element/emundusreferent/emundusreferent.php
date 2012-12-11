<?php
/**
 * Plugin element to manage referent letter request
 * @package fabrikar
 * @author Benjamin Rivalland
 * @copyright (C) eMundus(r)
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementEmundusreferent extends plgFabrik_ElementList {

	var $_pluginName = 'emundusreferent';
	var $_user;
	var $_attachment_id;

	/**
	 * Constructor
	 */

	function initUser()
	{
		$this->_user = & JFactory::getUser();
		$accessibility = false;
		foreach ($this->_user->groups as $group)
			if ($group > 1)
				$accessibility = true;
		if ($accessibility === false) die("Can not reach this page : Permission denied");
	}

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderTableData($data, $oAllRowsData)
	{
		$params =& $this->getParams();
		$data = $this->numberFormat($data);

		return parent::renderTableData($data, $oAllRowsData);
	}


	/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 			= $this->getHTMLName($repeatCounter);
		$id 			= $this->getHTMLId($repeatCounter);
		$params 		=& $this->getParams();
		$element 		=& $this->getElement();
		$size 			= $element->width;

		$this->initUser();
		$bits = array();
		$this->_attachment_id = $params->get('attachment_id');
		$info = $this->getReferentRequestInfo($this->_attachment_id);

		if (is_array($this->getForm()->_data)) {
			$data 	=& $this->getForm()->_data;
		}
		$value = $info[0]['sent'];
		//$value 	= $this->getValue($data, $repeatCounter);
		if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
		}
		if ($element->hidden == '1') {
			$type = "hidden";
		}

		if (JRequest::getCmd('task') == 'processForm') {
			$value = $this->unNumberFormat($value);
		}
		$value = $this->numberFormat($value);
		if (!$this->_editable) {
			$value = $this->_replaceWithIcons($value);
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		$bits['class'] = "fabrikinput inputbox ".@$type;
		$bits['type']		= @$type;
		$bits['name']		= $name;
		$bits['id']			= $id;
		if (version_compare( phpversion(), '5.2.3', '<')) {
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else {
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}
		$bits['size']	= $size;

		//cant be used with hidden element types
		if ($element->hidden != '1') {
			if ($params->get('readonly')) {
				$bits['readonly'] = "readonly";
				$bits['class'] .= " readonly";
			}
			if ($params->get('disable')) {
				$bits['class'] .= " disabled";
				$bits['disabled'] = 'disabled';
			}
		}
		
		if ($this->isReferentLetterUploaded($this->_attachment_id))
			$str = '<span class="emundusreferent_uploaded">'.JText::_('REFERENCE_LETTER_UPLOADED').'<span>';
		else {
			$str = "<div id=\"".$id."_error\"></div>";
			$txt_button = ($value>0)?JText::_('SEND_EMAIL_AGAIN'):JText::_('SEND_EMAIL');
			$str .= "<div id=\"".$id."_response\"><input type=\"button\" class=\"fabrikinput button\" id=\"".$id."_btn\" name=\"$name\" value=\"$txt_button\" /></div>";
			$str .= "<input ";
			foreach ($bits as $key=>$val) {
				$str.= "$key = \"$val\" ";
			}
			$str .= " />\n";
			$str .= "<img src=\"" . COM_FABRIK_LIVESITE . "media/com_fabrik/images/ajax-loader.gif\" class=\"loader\" id=\"".$id."_loader\" alt=\"" . JText::_('Loading') . "\" style=\"display:none;padding-left:10px;\" />";
		}
		
		return $str;
	}
	

/*	function email_check($email) {
		require(JPATH_LIBRARIES.DS.'emundus'.DS.'email.class.php');
		$email = new Email( $email, 30, false );
		//return $email->checkEmail_results;
		echo '0|<span class="emundusreferent_error">'.JText::_('EMAIL_ERROR').'</span>';
		return array(3);
		//if (preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email))
			//return true;
	}
*/
	function email()
	{
		$baseurl 		= JURI::root();
		$db 			=& JFactory::getDBO();
		
		$recipient = JRequest::getVar('email');
		$attachment_id = JRequest::getVar('id');

		if( !empty($attachment_id) ){ 
			require(JPATH_LIBRARIES.DS.'emundus'.DS.'email.class.php');
			$email = new Email( $recipient );
			$results = $email->checkEmail_results;
			if ($results[checkEmailSyntax] == 0) {
				echo '0|<span class="emundusreferent_error">'.JText::_('EMAIL_FORMAT_ERROR').'</span>';
				die();
			}
			if( !($results['gethostbyname']==1 && $results['customCheckEmailWith_Dnsrr']==1 ) && 
				!($results['checkEmailWith_Dnsrr']==1 && $results['customCheckEmailWith_Mxrr']==1 ) ){
					echo '0|<span class="emundusreferent_error">'.JText::_('EMAIL_DOMAIN_ERROR').'</span>';
			}
		} else {
			echo '0|<span class="emundusreferent_error">'.JText::_('EMAIL_ERROR').'</span>';
			die();
		}
		
		// Récupération des données du mail
		$query = 'SELECT id, subject, emailfrom, name, message
						FROM #__emundus_setup_emails
						WHERE lbl="referent_letter"';
		$db->setQuery( $query );
		$db->query();
		$obj=$db->loadObjectList() or die('ERROR: '.$query);
		
		// Récupération de la pièce jointe : modele de lettre
		$query = 'SELECT esp.reference_letter
						FROM #__emundus_users as eu 
						LEFT JOIN #__emundus_setup_profiles as esp on esp.id = eu.profile 
						WHERE eu.user_id = '.$this->_user->id;
		$db->setQuery( $query );
		$db->query() or die('ERROR: '.$query);
		$obj_letter=$db->loadRowList();
		
		
		// Reference  /////////////////////////////////////////////////////////////
		if (!$this->isReferentLetterUploaded($attachment_id)) {
			$key1 = md5($this->rand_string(20).time());
			// MAJ de la table emundus_files_request
			$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id) 
								  VALUES (NOW(), '.$this->_user->id.', "'.$key1.'", '.$attachment_id.')';
			$db->setQuery( $query );
			$db->query() or die('ERROR: '.$query);

			// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
			$link_upload1 = $baseurl.'index.php?option=com_fabrik&c=form&view=form&fabrik=68&tableid=71&keyid='.$key1.'&sid='.$this->_user->id;

			///////////////////////////////////////////////////////
			$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[UPLOAD_URL\]/');
			
			// Mail 
			$from = $obj[0]->emailfrom;
			$fromname =$obj[0]->name;
			$from_id = $obj[0]->id;
			
			$subject = $obj[0]->subject;
			$mode = 1;
			//$cc = $user->email;
			//$bcc = $user->email;
			$attachment[] = JPATH_BASE.str_replace("\\", "/", $obj_letter[0][0]);
			//die(print_r($obj_letter[0][0]));
			$replyto = $obj[0]->emailfrom;
			$replytoname = $obj[0]->name;
		
			$replacements = array ($this->_user->id, $this->_user->name, $this->_user->email, $link_upload1);
			$body1 = preg_replace($patterns, $replacements, $obj[0]->message);
			unset($replacements);
			
			if(JUtility::sendMail($from, $fromname, $recipient, $subject, $body1, $mode, null, null, $attachment, $replyto, $replytoname)) {
				$sql = 'INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) VALUES (62, -1, "'.$subject.'", "'.$db->quote($body1).'", NOW())';
				$db->setQuery( $sql );
				$db->query();
				echo '1|<span class="emundusreferent_sent">'.JText::_('EMAIL_SENT').'</span>';
			} else
				echo '0|<span class="emundusreferent_error">'.JText::_('EMAIL_ERROR').'</span>';
		} else
			echo '1|<span class="emundusreferent_uploaded">'.JText::_('REFERENCE_LETTER_UPLOADED').'</span>';
		
	}
	
	//////////////////////////  SET FILES REQUEST  /////////////////////////////
	// 
	// Génération de l'id du prochain fichier qui devra être ajouté par le referent
	
	// 1. Génération aléatoire de l'ID
	private function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789')
	{
		$string = '';
		for ($i = 0; $i < $len; $i++)
		{
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}
		return $string;
	}

	protected function getReferentRequestInfo($attachment_id)
	{
		$db =& JFactory::getDBO();
		$query = "SELECT count(id) as sent, SUM(uploaded) uploaded FROM #__emundus_files_request WHERE student_id=".$this->_user->id." AND attachment_id=".$attachment_id;
		$db->setQuery( $query );
		$data = $db->loadAssocList(); 
		return $data;
	}
	
	protected function isReferentLetterUploaded($attachment_id)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE user_id='.$this->_user->id.' AND attachment_id='.$attachment_id;
		$db->setQuery( $query ); 
		$db->query();
		return ($db->loadResult()>0?true:false);
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts =& $this->getElementJSOptions($repeatCounter);
		$params =& $this->getParams();
		
		$opts->email = $params->get('email');
		$opts->sending = JText::_('SENDING_EMAIL');
		$opts->sendmail = JText::_('SEND_EMAIL');
		$opts->sendmailagain = JText::_('SEND_EMAIL_AGAIN');
		$opts->emailelement = $params->get('email');
		$opts->id = $params->get('attachment_id');
		
		$opts = json_encode($opts);
		return "new fbEmundusreferent('$id', $opts)";
	}

	/**
	 * load the javascript class that manages interaction with the form element
	 * should only be called once
	 * @return string javascript class file
	 */

	function formJavascriptClass()
	{
		JHTML::stylesheet('emundusreferent.css', 'plugins/fabrik_element/emundusreferent/css/');
		FabrikHelperHTML::script('plugins/fabrik_element/emundusreferent/javascript.js');
	}

	/**
	 * defines the type of database table field that is created to store the element's data
	 */

	function getFieldDescription()
	{
		$p = $this->getParams();
		$group =& $this->getGroup();
		if ($group->isJoin() == 0 && $group->canRepeat()) {
			return "TEXT";
		}

		return "INT(6)";
	}

}
?>