<?php
function age($naiss) {
		@list($annee, $mois, $jour) = preg_split('[-.]', $naiss);
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$annees = $today['annee'] - $annee;
		if ($today['mois'] <= $mois) {
			if ($mois == $today['mois']) {
			if ($jour > $today['jour'])
				$annees--;
		}
		else
			$annees--;
		}
		return $annees;
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

// @description Generate the letter result
// @params Applicant user ID
// @params Eligibility ID of the evaluation
// @params Code of the programme
// @params Type of output

function letter_pdf ($user_id, $eligibility, $training, $campaign_id, $evaluation_id, $output = true) { 
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$current_user = & JFactory::getUser();
	$user = & JFactory::getUser($user_id);
	$db = &JFactory::getDBO();

	$files = array();

	/*$query = "SELECT * FROM #__emundus_setup_letters WHERE eligibility=".$eligibility." AND training=".$db->Quote($training);
	$db->setQuery($query);
	$letters = $db->loadAssocList();*/
	$evaluations = new EmundusModelEvaluation;
	$letters = $evaluations->getLettersTemplate($eligibility, $training);

	/*$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE id = (select training_id from #__emundus_training_174_repeat where applicant_id=".$user_id." and campaign_id=".$campaign_id.") ORDER BY date_start ASC";
	$db->setQuery($query);
	$courses = $db->loadAssocList();
	*/
	$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE published=1 AND date_start>NOW() AND code=".$db->Quote($training). " ORDER BY date_start ASC";
	$db->setQuery($query);
	$courses = $db->loadAssocList();

	$courses_list = '';
	$courses_fee = ' ';
	foreach ($courses as $c) {
		$ds = !empty($c['date_start']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_start'])) : JText::_('NOT_DEFINED');
		$de = !empty($c['date_end']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_end'])) : JText::_('NOT_DEFINED');
		//$courses_list .= '<li>'.$ds.' - '.$de.'</li>';
		$courses_list .= '<img src="'.JPATH_BASE.DS."media".DS."com_emundus".DS."images".DS."icones".DS."checkbox-unchecked_16x16.png".'" width="8" height="8" align="left" /> ';
		$courses_list .= $ds.' - '.$de.'<br />';
		$courses_fee  .= 'Euro '.$c['price'].',-- ';
	}

	$campaigns = new EmundusModelCampaign;
	$campaign = $campaigns->getCampaignByID($campaign_id);

	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF {

		var $logo = "";
		var $logo_footer = "";
		var $footer = "";

		//Page header
		public function Header() {
			// Logo
			if (is_file($this->logo))
				$this->Image($this->logo, 0, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			// Set font
			$this->SetFont('helvetica', 'B', 16);
			// Title
			$this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		}

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			// footer
			$this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			//logo
			if (is_file($this->logo_footer))
				$this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			
		}
	}
	$emails = new EmundusModelEmails;
	$evaluations = new EmundusModelEvaluation;

	//
	// Evaluation result
	//
	if ($evaluation_id>0) {
		$evaluation = $evaluations->getEvaluationByID($evaluation_id);
		$reason = $evaluations->getEvaluationReasons();
		unset($evaluation[0]["id"]);
		unset($evaluation[0]["user"]);
		unset($evaluation[0]["time_date"]);
		unset($evaluation[0]["student_id"]);
		unset($evaluation[0]["parent_id"]);
		unset($evaluation[0]["campaign_id"]);
		unset($evaluation[0]["comment"]);
		if(empty($evaluation[0]["reason"])) {
			unset($evaluation[0]["reason"]);
			unset($evaluation[0]["reason_other"]);
		} elseif(empty($evaluation[0]["reason_other"])) {
			unset($evaluation[0]["reason_other"]);
		}
		$evaluation_details = EmundusHelperList::getElementsDetailsByName('"'.implode('","', array_keys($evaluation[0])).'"');

		$result = "";
		foreach ($evaluation_details as $ed) {
			if($ed->hidden==0 && $ed->published==1 && $ed->tab_name=="jos_emundus_evaluations") {
				//$result .= '<br>'.$ed->element_label.' : ';
				if($ed->element_name=="reason") {
					$result .= '<ul>';
					foreach ($evaluation as $e) {
						$result .= '<li>'.@$reason[$e[@$ed->element_name]]->reason.'</li>'; //die(print_r(@$reason[$e[@$ed->element_name]]));
					}
					if (@!empty($evaluation[0]["reason_other"]))
						$result .= '<ul><li>'.@$evaluation[0]["reason_other"].'</li></ul>';
					$result .= '</ul>';
				} /*elseif($ed->element_name=="result") {
						$result .= $eligibility[$evaluation[0][$ed->element_name]]->title;
				} else
					$result .= $evaluation[0][$ed->element_name];*/
			}
		}
	}

	//
	// Replacement
	//
	$post = array(  'TRAINING_CODE' => $training, 
					'TRAINING_PROGRAMME' => $campaign['label'],
					'REASON' => $result, 
					'TRAINING_FEE' => $courses_fee, 
					'TRAINING_PERIODE' => $courses_list,
					'USER_NAME' => $current_user->name, 
					'USER_EMAIL' => $current_user->email );
	
//die(var_dump($tags));
	foreach ($letters as $letter) {
		$error = 0;
		$applications = new EmundusModelApplication;
		$attachment = $applications->getAttachmentByID($letter['attachment_id']);
		
		/*$query = "SELECT * FROM #__emundus_setup_attachments WHERE id=".$letter['attachment_id'];
		$db->setQuery($query);
		$attachment = $db->loadAssoc();*/

		// Test if letter type has already been created for that user/campaign/attachment and delete before if true.
		$query = 'SELECT * FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter['attachment_id'].' AND campaign_id='.$campaign_id;
		$db->setQuery($query);
		$file = $db->loadAssoc();
		// test if directory exist
		if (!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
			mkdir(EMUNDUS_PATH_ABS.$user_id, 0777, true);
		}
		if(count($file) > 0) {
			$query = 'DELETE FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter['attachment_id'].' AND campaign_id='.$campaign_id;
			$db->setQuery($query);
			$db->query();

			@unlink(EMUNDUS_PATH_ABS.$user_id.DS.$file['filename']);
		}

		if($letter['template_type'] == 1) { // Static file
			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];
			if(file_exists(JPATH_BASE.$letter['file']))
				copy(JPATH_BASE.$letter['file'], EMUNDUS_PATH_ABS.$user_id.DS.$name);
			else {
				JFactory::getApplication()->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');  
				$error++;
			}

		} elseif($letter['template_type'] == 3) { // Template file .docx
			$tags = $emails->setTagsWord($user_id, $post);
			require_once JPATH_LIBRARIES.DS.'PHPWord.php';

			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];
			if(file_exists(JPATH_BASE.$letter['file'])) {
				$PHPWord = new PHPWord();

				$document = $PHPWord->loadTemplate(JPATH_BASE.$letter['file']);

				for($i=0 ; $i<count($tags['patterns']) ; $i++) {
					$document->setValue($tags['patterns'][$i], $tags['replacements'][$i]);
					//echo $tags['patterns'][$i]." - ".$tags['replacements'][$i]."<br>";
				}

				$document->save(EMUNDUS_PATH_ABS.$user_id.DS.$name);
				unset($document);
			} else {
				JFactory::getApplication()->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');  
				$error++;
			}

		} else { // From HTML
			$tags = $emails->setTags($user_id, $post);
			$htmldata = "";
			$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor($current_user->name);
			$pdf->SetTitle($letter['title']);

			// set margins
			$pdf->SetMargins(5, 40, 5);
			//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			$pdf->footer = $letter["footer"];

			//get logo
			preg_match('#src="(.*?)"#i', $letter['header'], $tab);
			$pdf->logo = JPATH_BASE.DS.$tab[1];
			
			preg_match('#src="(.*?)"#i', $letter['footer'], $tab);
			$pdf->logo_footer = JPATH_BASE.DS.$tab[1];

			//get title
		//	$config =& JFactory::getConfig(); 
		//	$title = $config->getValue('config.sitename');
		//	$title = "";
		//	$pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);

			unset($logo);
			unset($logo_footer);
			
			//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetFont('helvetica', '', 8);

			//$dimensions = $pdf->getPageDimensions();

			//$htmldata .= $letter["header"];
			$htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter["body"])))); 
			//$htmldata .= $letter["footer"];
			//die($htmldata);
			$pdf->AddPage();

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

			@chdir('tmp');
			if($output){
					//$output?'FI':'F'
				$name = date('Y-m-d_H-i-s').$attachment['lbl'].'.pdf';
				$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, $output);

			}else{
				$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, 'F');
			}	
		}

		if($error == 0) {
			$query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id) VALUES ('.$user_id.', '.$letter['attachment_id'].', "'.$name.'","'.$training.' '.date('Y-m-d H:i:s').'", 0, 1, '.$campaign_id.')';
			$db->setQuery($query);
			$db->query();
			$id = $db->insertid();

			$file_info['id'] = $id;
			$file_info['path'] = EMUNDUS_PATH_ABS.$user_id.DS.$name;
			$file_info['attachment_id'] = $letter['attachment_id'];
			$file_info['name'] = $attachment['value'];
			$file_info['url'] = EMUNDUS_PATH_REL.$user_id.'/'.$name;

			$files[] = $file_info;
		}
	}
//die(var_dump($files));
	return $files;
}	


// @description Generate the letter template result
// @params Applicant user ID
// @params Eligibility ID of the evaluation
// @params Code of the programme
// @params Type of output

function letter_pdf_template ($user_id, $letter_id) {
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$current_user = & JFactory::getUser();
	$user = & JFactory::getUser($user_id);
	$db = &JFactory::getDBO();

	$files = array();

	$evaluations = new EmundusModelEvaluation;
	$letters = $evaluations->getLettersTemplateByID($letter_id);

//print_r($letters);
	$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE published=1 AND date_start>NOW() AND code=".$db->Quote($letters[0]['training']). " ORDER BY date_start ASC";
	$db->setQuery($query);
	$courses = $db->loadAssocList();
	
	$courses_list = '';
	$courses_fee = ' ';
	foreach ($courses as $c) {
		$ds = !empty($c['date_start']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_start'])) : JText::_('NOT_DEFINED');
		$de = !empty($c['date_end']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_end'])) : JText::_('NOT_DEFINED');
		//$courses_list .= '<li>'.$ds.' - '.$de.'</li>';
		$courses_list .= '<img src="'.JPATH_BASE.DS."media".DS."com_emundus".DS."images".DS."icones".DS."checkbox-unchecked_16x16.png".'" width="8" height="8" align="left" /> ';
		$courses_list .= $ds.' - '.$de.'<br />';
		$courses_fee  .= 'Euro '.$c['price'].'<br>';
		$programme = $c['label'];
	}

	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF {

		var $logo = "";
		var $logo_footer = "";
		var $footer = "";

		//Page header
		public function Header() {
			// Logo
			if (is_file($this->logo))
				$this->Image($this->logo, 0, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			// Set font
			$this->SetFont('helvetica', 'B', 16);
			// Title
			$this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		}

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			// footer
			$this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			//logo
			if (is_file($this->logo_footer))
				$this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			
		}
	}

	$emails = new EmundusModelEmails;
	//
	// Replacement
	//
	$post = array(  'TRAINING_CODE' 		=> @$letters[0]['training'], 
					'TRAINING_PROGRAMME' 	=> @$programme,
					'REASON'				=> JText::_("DEPEND_OF_EVALUATION"), 
					'TRAINING_FEE' 			=> @$courses_fee, 
					'TRAINING_PERIODE'		=> @$courses_list
				);
	$tags = $emails->setTags($user_id, $post);

	foreach ($letters as $letter) {
		$applications = new EmundusModelApplication;
		$attachment = $applications->getAttachmentByID($letter['attachment_id']);

		if($letter['template_type'] == 1) { // Static file
			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];

			$file = JPATH_BASE.$letter['file']; //die($file);
			if (file_exists($file)) {
				$mime_type = get_mime_type($file);
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

		} elseif($letter['template_type'] == 3) { // Template file .docx
			require_once JPATH_LIBRARIES.DS.'PHPWord.php';

			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];

			$PHPWord = new PHPWord();

			$document = $PHPWord->loadTemplate(JPATH_BASE.$letter['file']);

			for($i=0 ; $i<count($tags['patterns']) ; $i++) {
				$document->setValue($tags['patterns'][$i], $tags['replacements'][$i]);
				//echo $tags['patterns'][$i]." - ".$tags['replacements'][$i]."<br>";
			}

			$document->save(JPATH_BASE.DS.'tmp'.DS.$name);

			$file = JPATH_BASE.DS.'tmp'.DS.$name; //die($file);
			if (file_exists($file)) {
				$mime_type = get_mime_type($file);
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

			unset($document);

		} else { // From HTML
			$htmldata = "";

			$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor($current_user->name);
			$pdf->SetTitle($letter['title']);

			// set margins
			$pdf->SetMargins(5, 40, 5);
			//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			$pdf->footer = $letter["footer"];

			//get logo
			preg_match('#src="(.*?)"#i', $letter['header'], $tab);
			$pdf->logo = JPATH_BASE.DS.$tab[1];
			
			preg_match('#src="(.*?)"#i', $letter['footer'], $tab);
			$pdf->logo_footer = JPATH_BASE.DS.$tab[1];

			//get title
		/*	$config =& JFactory::getConfig(); 
			$title = $config->getValue('config.sitename');
			$title = "";
			$pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);*/
			unset($logo);
			unset($logo_footer);
			
			//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetFont('helvetica', '', 8);

			//$dimensions = $pdf->getPageDimensions();

			//$htmldata .= $letter["header"];
			;
			$htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter["body"])))); 
			//$htmldata .= $letter["footer"];
	//die($htmldata);
			$pdf->AddPage();

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

			@chdir('tmp');
			$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS."demo", 'I');
		}
	}
//die(print_r($files));
	exit();
}	


function application_form_pdf($user_id, $output = true) {
	jimport( 'joomla.html.parameter' );

	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
	require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$m_users = new EmundusModelUsers;
	$user_profile = $m_users->getCurrentUserProfile($user_id);
	
	$menu = new EmundusHelperMenu;

	$current_user =  JFactory::getUser();

	$application = new EmundusModelApplication;
	$forms = $application->getForms($user_id);

	// --- CONFIGURATION --- //
	$group_personal_infos = 14;
	$str_repeat = '//..*..//';
	$htmldata = '';
	$htmldata .= $forms;
	// --------------------- //
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Decision Publique');
	$pdf->SetTitle('Application Form');
	$db = &JFactory::getDBO();
	
	$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user_id.' ORDER BY date_submitted DESC';
	$db->setQuery($query);
	$campaign_id = $db->loadResult();

	$query = 'SELECT id FROM #__usergroups WHERE title="Registered"';
	$db->setQuery($query);
	$registered = $db->loadResult();

	// Users informations
	$query = 'SELECT u.id AS user_id, c.firstname, c.lastname, a.filename AS avatar, p.label AS cb_profile, c.profile, esc.year AS cb_schoolyear, esc.training, u.id, u.registerDate, u.email, epd.gender, epd.nationality, epd.birth_date, ed.user, ecc.date_submitted
				FROM #__users AS u
				LEFT JOIN #__emundus_users AS c ON u.id = c.user_id
				LEFT JOIN #__emundus_uploads AS a ON a.user_id=u.id AND a.attachment_id = '.EMUNDUS_PHOTO_AID.'
				LEFT JOIN #__emundus_setup_profiles AS p ON p.id = c.profile
				LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = '.$campaign_id.'  
				LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = u.id
				LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id
				LEFT JOIN #__emundus_campaign_candidature AS ecc ON (ecc.applicant_id = u.id AND ecc.campaign_id = '.$campaign_id.')
				WHERE u.id='.$user_id. '
				ORDER BY esc.id DESC';
	$db->setQuery($query);
	$item = $db->loadObject();
//die(str_replace("#_", "jos", $query));
	//get logo
	$app 		= JFactory::getApplication();
	$template 	= $app->getTemplate(true);
	$params     = $template->params;
	$image   	= $params->get('logo')->custom->image; 
	$logo 		= preg_match_all("/'([^']*)'/", $image, $matches);
	$logo 		= !empty($matches[1][1]) ? JPATH_ROOT.DS.$matches[1][1] : preg_match_all('/"([^"]*)"/', $image, $matches);
	$logo 		= !empty($logo) ? JPATH_ROOT.DS.$matches[1][1] : "";
	
	
	//get title
	$config =& JFactory::getConfig(); 
	$title = $config->getValue('config.sitename');
	$pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
	unset($logo);
	unset($title);
	
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->SetFont('helvetica', '', 8);
	$pdf->AddPage();
	$dimensions = $pdf->getPageDimensions();
	
/*** Applicant   ***/   
$htmldata .= 
'<style>
.card  { background-color: #cecece; border: none;}
.name  { display: block; font-size: 16pt; margin: 0 0 0 20px; padding:0; }
.maidename  { display: block; font-size: 20pt; margin: 0 0 0 20px; padding:0; }
.nationality { display: block; margin: 0 0 0 20px;  padding:0;}
.sent { display: block; font-family: monospace; margin: 0 0 0 20px; padding:0;}
.birthday { display: block; margin: 0 0 0 20px; padding:0;}
</style>
<div class="card">
<table>
<tr>
<td width="20%">';
if (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar) && !empty($item->avatar))
	$htmldata .= '<img src="'.EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar.'" width="100" align="left" />';
elseif (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar) && !empty($item->avatar))
	$htmldata .= '<img src="'.EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar.'" width="100" align="left" />';
$htmldata .= '
</td>
<td width="80%">

  <div class="name"><strong>'.$item->firstname.' '.strtoupper($item->lastname).'</strong>, '.$item->cb_profile.' ('.$item->cb_schoolyear.')</div>';

if(isset($item->maiden_name))
	$htmldata .= '<div class="maidename">'.JText::_('MAIDEN_NAME').' : '.$item->maiden_name.'</div>';

$htmldata .= '
  <div class="nationality">'.JText::_('ID_CANDIDAT').' : #'.$item->user_id.'</div>
  <div class="nationality">'.JText::_('NATIONALTY').' : '.$item->nationality.'</div>
  <div class="birthday">'.JText::_('BIRTH_DATE').' : '.strftime("%d/%m/%Y", strtotime($item->birth_date)).' ('.age($item->birth_date).')</div>
  <div class="birthday">'.JText::_('EMAIL').' : '.$item->email.'</div>
  <div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.strftime("%d/%m/%Y 	%H:%M", strtotime($item->date_submitted)).'</div>
  <div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime("%d/%m/%Y 	%H:%M", time()).'</div>
</td>
</tr>
</table>
</div>';

if (!empty($htmldata)) {
				$pdf->startTransaction();
				$start_y = $pdf->GetY();
				$start_page = $pdf->getPage();
				$pdf->Bookmark($itemt->label, 0);
				$pdf->writeHTMLCell(0,'','',$start_y,$htmldata,'B', 1); }
/**  END APPLICANT   ****/

	$html_table = '';
	$nb_groupes = 0;
	$nb_lignes = 0;
	$current_group_repeated = 0;

//______________________________________________________//
//		Liste des formulaires et de leurs données		//
//______________________________________________________//
// Récupération des tables qui doivent contenir un enregistrement de candidat
	// Ajout depuis le back office des ID Fabrik List
	$eMConfig = JComponentHelper::getParams('com_emundus');
	$export_pdf = $eMConfig->get('export_pdf'); 
	

	// Le profil dépend de la campagne
	$query = 'SELECT profile_id FROM #__emundus_setup_campaigns WHERE id = '.$campaign_id;
	$db->setQuery( $query );
	$db->query();
	$num_rows = $db->getNumRows();
	($db->getNumRows()==1)?$user_profile=$db->loadResult():$user_profile = $item->profile;


	$menu_list = $menu->buildMenuQuery($user_profile); 
	foreach ($menu_list as $m) {
		$fl[] = $m->table_id;
	}
	$query = 'SELECT ff.id, ff.label
                        FROM #__fabrik_forms ff
                        LEFT JOIN #__fabrik_lists ft ON ft.form_id = ff.id
                        WHERE ft.id IN ( '.implode(',', $fl).' ) OR ft.id IN ( '.$export_pdf.' )
                        OR ft.db_table_name = "jos_emundus_declaration"
                        ORDER BY ff.label';
	$db->setQuery($query); 
	$tableusers = implode(',', $db->loadResultArray());

	//get the form evaluation for the applicant
	$query = 'SELECT evaluation FROM #__emundus_setup_profiles WHERE id = '.$user_profile;
	$db->setQuery( $query );
	$eval_form = $db->loadResult();
	if($current_user->usertype != $registered || ($output == false && !empty($item->user))){
		$query = 'SELECT DISTINCT(fbtables.form_id), fbtables.id, fbtables.label, fbtables.db_table_name, fbtables.created_by_alias
						FROM #__menu AS menu 
						INNER JOIN #__emundus_setup_profiles AS esp ON esp.menutype = menu.menutype
						INNER JOIN #__fabrik_forms AS ff ON ff.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
						LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = ff.id
						WHERE fbtables.form_id IN ('.$tableusers.') AND esp.id = '.$user_profile.'
						OR (fbtables.created_by_alias = "eval" AND (ff.id = '.$eval_form.' OR ff.id = 39)) 
						ORDER BY fbtables.created_by_alias DESC, menu.lft ASC, fbtables.label ASC';
	} else {
		$query = 'SELECT DISTINCT(fbtables.form_id), fbtables.id, fbtables.label, fbtables.db_table_name, fbtables.created_by_alias 
						FROM #__menu AS menu 
						INNER JOIN #__emundus_setup_profiles AS esp ON esp.menutype = menu.menutype
						INNER JOIN #__fabrik_forms AS ff ON ff.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
						LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = ff.id
						WHERE fbtables.form_id IN ('.$tableusers.')
						AND esp.id = '.$user_profile.'
					ORDER BY fbtables.created_by_alias DESC, menu.lft';
	}
	$db->setQuery( $query );

	$tableuser = $db->loadObjectList();
	// var_dump($tableuser);
	/*if(isset($tableuser)) {
		foreach($tableuser as $key => $itemt) {
			if($current_user->usertype != $registered || ($output == false && !empty($item->user))){
				// EVALUATION & COMMENTS (Only for != Registered usertype
				if($itemt->db_table_name == 'jos_emundus_evaluations'){

					$pdf->addPage(); 
					$pdf->startTransaction();
					
					//users comments put by administrators during the process
					$query = 'SELECT u.name, ec.reason, ec.comment FROM #__emundus_comments ec 
							LEFT JOIN #__users u ON u.id = ec.user_id
							WHERE ec.applicant_id ='.$user_id.'
							ORDER BY ec.reason, u.name';
					$db->setQuery($query);
					$comments = $db->loadObjectList();
					$htmldata .= '<h1>'.JText::_('COMMENTS').'</h1>';
					$name ='';
					
					$reason ='';
					if(!empty($comments)){
						foreach($comments as $comment){
							if($comment->reason != $reason || $comment->name != $name)
								$htmldata .= '<h3>'.$comment->reason.' '.JText::_('BY').' '.$comment->name.'</h3>';
							$htmldata .= '<p>'.$comment->comment.'</p>';
							$name = $comment->name;
							$reason = $comment->reason;
						}
						$pdf->Bookmark(JText::_('COMMENTS'), 0);
						$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
						// $pdf->Ln(1);
						$htmldata = '';
					}
					//users' evaluators or groups
					$db->setQuery("SELECT group_id FROM #__emundus_groups_eval WHERE applicant_id=".$user_id." AND group_id != 'NULL'");
					$is_group = $db->loadObjectList();
					$db->setQuery("SELECT user_id FROM #__emundus_groups_eval WHERE applicant_id=".$user_id." AND user_id != 'NULL'");
					$is_eval = $db->loadObjectList();
					
					$htmldata .= '<h1>'.JText::_('EVALUATORS').'</h1>';
					
					//groups of evaluators
					if(!empty($is_group)){
						$g = 0; 
						$i=1;
						foreach($is_group as $group){
							$query = 'SELECT DISTINCT(u.name), esg.label, esg.id
								FROM #__emundus_groups_eval ege
								LEFT JOIN #__emundus_groups eg ON eg.group_id = '.$group->group_id.'
								LEFT JOIN #__users u ON eg.user_id = u.id
								LEFT JOIN #__emundus_setup_groups esg ON esg.id = '.$group->group_id.'
								WHERE ege.applicant_id='.$user_id;
							$db->setQuery($query);
							$groups = $db->loadObjectList();
							foreach($groups as $group){
								if($g != $group->id) $htmldata .= '<h2>'.$group->label.' group</h2><p>';
								$g = $group->id;
								$htmldata .= '- '.$group->name.'';
								$i++;
							}
							$htmldata .= '</p>';
						}
					}
					//evaluators
					if(!empty($is_eval)){
						if(!empty($is_group)) $htmldata .= '<h2>'.JText::_('OTHER_EVALUATORS').'</h2>';
						$i=1;
						foreach($is_eval as $evaluator){
							$query = 'SELECT u.name
									FROM #__emundus_groups_eval ege
									LEFT JOIN #__users u ON u.id = '.$evaluator->user_id.'
									WHERE ege.applicant_id='.$user_id;
							$db->setQuery($query);
							$eval = $db->loadResult();
							$htmldata .= '<p><b>Evaluator '.$i.'</b>';
							$htmldata .= $eval;
							$i++;
						}
						$htmldata .= '</p>';
					}
					
					$pdf->Bookmark(JText::_('EVALUATORS'), 0);
					$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
					// $pdf->Ln(1);
					$htmldata = '';				
				}
			}// EVALUATION & COMMENTS 
			
			$htmldata .= '<br /><h1>';
			$htmldata .= $itemt->label;
			$htmldata .= '</h1>';
			
			// liste des groupes pour le formulaire d'une table
			$query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, fg.params, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated, INSTR(fg.params,"\"repeat_group_button\":1") as repeated_1
						FROM #__fabrik_formgroup ff, #__fabrik_groups fg
						WHERE ff.group_id = fg.id AND
						ff.form_id = "'.$itemt->form_id.'" 
						ORDER BY ff.ordering';
			$db->setQuery( $query );
			$groups = $db->loadObjectList();
	//die( str_replace('#_','jos',$query).'<BR /><BR />');

			// Liste des groupes 
			foreach($groups as $keyg => $itemg) {
				// liste des items par groupe
				$query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
							FROM #__fabrik_elements fe
							WHERE fe.published=1 AND 
								  fe.hidden=0 AND 
								  fe.group_id = "'.$itemg->group_id.'" 
							AND fe.name NOT IN ("id", "parent_id")
							ORDER BY fe.ordering';
				$db->setQuery( $query );
				$elements = EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels"));
/////////////////////////////////////////////////////////
				if(count($elements)>0) {
					$htmldata .= '<fieldset><h2>';
					$htmldata .= $itemg->label;
					$htmldata .= '</h2>';
					foreach($elements as &$iteme) {
						if($iteme->name == 'result_for'){	
							//Attribs columns from jos_fabrik_elements (to have the label of 'result for')
							$paramsdefs = JPATH_BASE.DS.'plugins'.DS.'fabrik_element'.DS.'fabrikdatabasejoin'.DS.'fabrikdatabasejoin.xml'; 
							//$paramsdefs = JPATH_BASE.DS.'components'.DS.'com_fabrik'.DS.'plugins'.DS.'element'.DS.'fabrikdatabasejoin'.DS.'fabrikdatabasejoin.xml';
							$params = new JParameter( $iteme->params, $paramsdefs );
							$params = json_decode($params);
							//$params = $params->_registry['_default']['data'];
							//die(print_r($params->database_join_where_sql));
							$params->database_join_where_sql = preg_replace('#id in#','join_t.id in',$params->database_join_where_sql);
							$query = 'SELECT join_t.`'.$params->join_val_column.'` FROM `'.$itemt->db_table_name.'` t LEFT JOIN `'.$params->join_db_name.'` join_t ON join_t.id = t.`'.$iteme->name.'` '.preg_replace('#{(.*)}#',$item->user_id,$params->database_join_where_sql).' and `student_id`='.$item->user_id;
							$db->setQuery( $query );
							$iteme->content = $db->loadResult();
							unset($params);
						}else{
							if($itemt->created_by_alias != 'eval')
								$query = 'SELECT `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$item->user_id;
							else
								$query = 'SELECT `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE student_id='.$item->user_id;
							$db->setQuery( $query );
							$iteme->content = $db->loadResult();
							//if value != label in forms
							if($iteme->sub_values != $iteme->sub_labels){
								$values = explode('|',$iteme->sub_values);
								$labels = explode('|',$iteme->sub_labels);
								if($iteme->content != ''){
									$iteme->content = $labels[array_search($iteme->content,$values)];
								}
							}
							unset($values);
							unset($labels);
						}
					}
 					unset($iteme);
					
					if ($itemg->group_id == 14) {
						 foreach($elements as &$element) {
							if(!empty($element->label) && $element->label!=' ') {
								if ($element->plugin=='date' && $element->content>0) {
									$date_params = json_decode($element->params);
									$elt = strftime($date_params->date_form_format, strtotime($element->content));
								} else $elt = $element->content;
								$htmldata .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
							}
						 }
				// TABLEAU DE PLUSIEURS LIGNES
					} elseif ($itemg->repeated>0 || $itemg->repeated_1>0){
						unset($col); unset($j);
						foreach($elements as &$element) {
							$col[] = $element->name;
						}
						
						$query = 'SELECT '.implode(", ",$col).' FROM '.$itemt->db_table_name.'_'.$itemg->group_id.'_repeat
								WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$item->user_id.')';
						$db->setQuery($query);
						$repeated_elements = $db->loadObjectList();
//echo str_replace('#_','jos',$query); die();
						foreach ($repeated_elements as $r_element) {
							$j = 0;
							foreach ($r_element as $key => $r_elt) {  
								if ($key != 'id' && $key != 'parent_id' && $key != 'applicant_id' && $key != 'campaign_id' && $key != 'time_date') {
									if(in_array($key,$col)){
										$j=array_search($key,$col);
									}
									if(@$elements[$j]->name==$key){
										if ($elements[$j]->plugin=='date') {
											$date_params = json_decode($elements[$j]->params);
											$elt = strftime($date_params->date_form_format, strtotime($r_elt));
										} elseif($elements[$j]->plugin=='databasejoin') {
											$params = json_decode($elements[$j]->params);
											$select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
											$from = $params->join_db_name;
											$where = $params->join_key_column.'='.$db->Quote($r_elt);
											$query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
											$query = preg_replace('#{thistable}#', $from, $query);
											$query = preg_replace('#{my->id}#', $item->user_id, $query);
											$db->setQuery( $query );
											$elt = $db->loadResult();
										} else
											$elt = $r_elt;
										$htmldata .= '<b>'.$elements[$j]->label.': </b>'.$elt.'<br/>';
									}
								}
								// $j++;
							}
							$htmldata .= '____<br/>';
						}
					// AFFICHAGE EN LIGNE
					} else {  
						foreach($elements as &$element) {
							if(!empty($element->label) && $element->label!=' ') {
								if ($element->plugin=='date' && $element->content>0) {
									$date_params = json_decode($element->params);
									$elt = strftime($date_params->date_form_format, strtotime($element->content));
								} else $elt = $element->content;
								$htmldata .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
							}
						}
					}
					$htmldata .= '</fieldset>';
				}			
/// Add a page
			}
			
			if (!empty($htmldata)) {
				$pdf->startTransaction();
				$start_y = $pdf->GetY();
				$start_page = $pdf->getPage();
				$pdf->Bookmark($itemt->label, 0);
				$pdf->writeHTMLCell(0,'','',$start_y,$htmldata,'B', 1);
				// $pdf->Ln(1);
				$end_page = $pdf->getPage();
				if ($end_page != $start_page) {
					$pdf = $pdf->rollbackTransaction();
					$pdf->addPage(); 
					$pdf->Bookmark($itemt->label, 0);
					$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
					// $pdf->Ln(1);
				}
				$htmldata = '';
			}
///			
		}
	}*/
	
	/* --- Listes des fichiers chargés --- */
	$query = 'SELECT sa.value, u.filename, u.description, u.timedate 
	FROM #__emundus_uploads u 
	LEFT JOIN #__emundus_setup_attachments AS sa ON sa.id=u.attachment_id 
	WHERE user_id='.$user_id. ' ORDER BY sa.ordering, u.timedate';
	$db->setQuery($query);
	$uploads = $db->loadObjectList();
	$nbuploads = count($uploads);
	$titleupload = $nbuploads>0?JText::_('FILES_UPLOADED'):JText::_('FILE_UPLOADED');
	$htmldata .='<div id="file_upload">
	<h2>'.$titleupload.' : '.$nbuploads.'</h2>		
	<table>
	<tr>
		<td><h3>'.JText::_('FILE_TYPE').'</h3></td>
		<td><h3>'.JText::_('FILE_NAME').'</h3></td>
		<td><h3>'.JText::_('DESCRIPTION').'</h3></td>
		<td><h3>'.JText::_('SENT_ON').'</h3></td>
	</tr>';
	foreach($uploads as $upload){
		$path_href=JURI::base().EMUNDUS_PATH_REL.$user_id.'/'.$upload->filename;
		$htmldata .='<tr>
			<td>'.$upload->value.'</td>
			<td><a href="'.$path_href.'" target="_blank">'.$upload->filename.'</a></td>
			<td>'.$upload->description.'</td>
			<td>'.strftime("%d/%m/%Y %H:%M", strtotime($upload->timedate)).'</td>
		</tr>';
	}
	$htmldata .='</table></div>';
	if (!empty($htmldata)) {
		$pdf->startTransaction();
		$start_y = $pdf->GetY();
		$start_page = $pdf->getPage();
		$pdf->Bookmark($itemt->label, 0);
		$pdf->writeHTMLCell(0,'','',$start_y,$htmldata,'B', 1);
		// $pdf->Ln(1);
		$end_page = $pdf->getPage();
		if ($end_page != $start_page) {
			$pdf = $pdf->rollbackTransaction();
			$pdf->addPage(); 
			$pdf->Bookmark($itemt->label, 0);
			$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
			// $pdf->Ln(1);
		}
		$htmldata = '';
	}
	// die('ok');
	@chdir('tmp');
	if($output){
		if($current_user->usertype != $registered){
			//$output?'FI':'F'
			$name = 'application_form_'.date('Y-m-d_H-i-s').'_'.rand(1000,9999).'.pdf';
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.$name, 'FI');
			$query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id) 
						VALUES ('.$item->user_id.',(
										   SELECT id 
										   FROM #__emundus_setup_attachments 
										   WHERE lbl = "_application_form"),"'.$name.'","'.$item->training.' '.date('Y-m-d H:i:s').'",0,0,'.$campaign_id.')';
			$db->setQuery($query);
			$db->query();
		}else{
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'application.pdf', 'F');
		}
	}else{
		$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'application.pdf', 'F');
	}

}
?>