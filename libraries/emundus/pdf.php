<?php
function age($naiss) {
		list($annee, $mois, $jour) = preg_split('[-.]', $naiss);
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
function application_form_pdf($user_id, $output = true) {
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');

	$current_user = & JFactory::getUser();
	// --- CONFIGURATION --- //
	$group_personal_infos = 14;
	$str_repeat = '//..*..//';
	$htmldata = '';
	// --------------------- //
	set_time_limit(0);
	require(JPATH_LIBRARIES.'/emundus/tcpdf/config/lang/eng.php');
	require(JPATH_LIBRARIES.'/emundus/tcpdf/tcpdf.php');
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Decision Publique');
	$pdf->SetTitle('Application Form');
	$db = &JFactory::getDBO();
	
	$query = 'SELECT id FROM #__usergroups WHERE title="Registered"';
	$db->setQuery($query);
	$registered = $db->loadResult();

	// Users informations
	$query = 'SELECT u.id AS user_id, c.firstname, c.lastname, a.filename AS avatar, p.label AS cb_profile, c.profile, p.schoolyear AS cb_schoolyear, u.id, u.registerDate, u.email, epd.gender, epd.nationality, epd.birth_date, ed.user, ed.time_date
				FROM #__users AS u
				LEFT JOIN #__emundus_users AS c ON u.id = c.user_id
				LEFT JOIN #__emundus_uploads AS a ON a.user_id=u.id AND a.attachment_id = '.EMUNDUS_PHOTO_AID.'
				LEFT JOIN #__emundus_setup_profiles AS p ON p.id = c.profile
				LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = u.id
				LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id
				WHERE u.id='.$user_id;
	$db->setQuery($query);
	$item = $db->loadObject();
	
	//get logo
	$query = 'SELECT m.content FROM #__modules m WHERE m.id = 90';
	$db->setQuery($query);
	$logo = $db->loadResult();
	preg_match('#src="(.*?)"#i', $logo,$tab);
	$logo = $tab[1];
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
	$pdf->SetFont('helvetica', '', 9);
	$pdf->AddPage();
	$dimensions = $pdf->getPageDimensions();
	
/*** Applicant   ***/   
$htmldata .= 
'<style>
.card  { background-color: #cecece; border: none;}
.name  { display: block; font-size: 16pt; margin: 0 0 0 20px; padding:0; }
.maidenname  { display: block; font-size: 20pt; margin: 0 0 0 20px; padding:0; }
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
  <div class="birthday">'.JText::_('BIRTH_DATE').' : '.strftime(JText::_('DATE_FORMAT_LC3'),strtotime($item->birth_date)).' ('.age($item->birth_date).')</div>
  <div class="birthday">'.JText::_('EMAIL').' : '.$item->email.'</div>
  <div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.$item->time_date.'</div>
  <div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime(JText::_('DATE_FORMAT_LC3'),time()).'</div>
</td>
</tr>
</table>
</div>';
/**  END APPLICANT   ****/

	$html_table = '';
	$nb_groupes = 0;
	$nb_lignes = 0;
	$current_group_repeated = 0;

//______________________________________________________//
//		Liste des formulaires et de leurs données		//
//______________________________________________________//
// Récupération des tables qui doivent contenir un enregistrement de candidat
	$eMConfig =& JComponentHelper::getParams('com_emundus');
	$query = 'SELECT ff.id, ff.label
                        FROM #__fabrik_forms ff
                        LEFT JOIN #__fabrik_lists ft ON ft.form_id = ff.id
                        WHERE ft.created_by_alias IN ( "form", "eval" )
                        OR ft.db_table_name = "jos_emundus_declaration"
                        ORDER BY ff.label';
	$db->setQuery($query);
	$tableusers = implode(',', $db->loadResultArray());
	
	//get profile or result for 
	$query = 'SELECT result_for FROM #__emundus_final_grade WHERE student_id = '.$user_id;	
	$db->setQuery( $query );
	$db->query();
	$num_rows = $db->getNumRows();
	($db->getNumRows()==1)?$user_profile=$db->loadResult():$user_profile = $item->profile;
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
						ORDER BY fbtables.created_by_alias DESC, menu.ordering ASC, fbtables.label ASC';
	}else{
		$query = 'SELECT DISTINCT(fbtables.form_id), fbtables.id, fbtables.label, fbtables.db_table_name, fbtables.created_by_alias 
						FROM #__menu AS menu 
						INNER JOIN #__emundus_setup_profiles AS esp ON esp.menutype = menu.menutype
						INNER JOIN #__fabrik_forms AS ff ON ff.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
						LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = ff.id
						WHERE fbtables.form_id IN ('.$tableusers.')
						AND esp.id = '.$user_profile.'
					ORDER BY fbtables.created_by_alias DESC, menu.ordering';
	}
	$db->setQuery( $query );
	$tableuser = $db->loadObjectList();
	if(isset($tableuser)) {
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
						$pdf->Ln(2);
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
								$htmldata .= '- '.$group->name.'<br />';
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
							$htmldata .= '<p><b>Evaluator '.$i.'</b><br />';
							$htmldata .= $eval;
							$i++;
						}
						$htmldata .= '</p>';
					}
					
					$pdf->Bookmark(JText::_('EVALUATORS'), 0);
					$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
					$pdf->Ln(2);
					$htmldata = '';				
				}
			}// EVALUATION & COMMENTS 
			
			$htmldata .= '<br /><h1>';
			$htmldata .= $itemt->label;
			$htmldata .= '</h1>';
			
			// liste des groupes pour le formulaire d'une table
			$query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, fg.params, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated
						FROM #__fabrik_formgroup ff, #__fabrik_groups fg
						WHERE ff.group_id = fg.id AND
						ff.form_id = "'.$itemt->form_id.'" 
						ORDER BY ff.ordering';
			$db->setQuery( $query );
			$groups = $db->loadObjectList();
			
			/*-- Liste des groupes -- */
			foreach($groups as $keyg => $itemg) {
				// liste des items par groupe
				$query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
							FROM #__fabrik_elements fe
							WHERE fe.published=1 AND 
								  fe.hidden=0 AND 
								  fe.group_id = "'.$itemg->group_id.'" 
							ORDER BY fe.ordering';
				$db->setQuery( $query );
				$elements = EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels"));
				if(count($elements)>0) {
					$htmldata .= '<fieldset><h2>';
					$htmldata .= $itemg->label;
					$htmldata .= '</h2>';
					foreach($elements as &$iteme) {
						if($iteme->name == 'result_for'){	
							//Attribs columns from jos_fabrik_elements (to have the label of 'result for')
							$paramsdefs = JPATH_BASE.DS.'components'.DS.'com_fabrik'.DS.'plugins'.DS.'element'.DS.'fabrikdatabasejoin'.DS.'fabrikdatabasejoin.xml';
							$params = new JParameter( $iteme->params, $paramsdefs );
							$params = $params->_registry['_default']['data'];
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
								$elt = ($element->plugin=='fabrikdate' && $element->content>0)?strftime(JText::_('DATE_FORMAT_LC3'),strtotime ($element->content)):$element->content;
								$htmldata .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
							}
						 }
				// TABLEAU DE PLUSIEURS LIGNES
					} elseif ($itemg->repeated>0){
						$nbl = count(explode('//..*..//', $elements[0]->content));
						$nbc = count($elements);
						//die($nbl.' '.$nbc.' '.print_r(explode('//..*..//', $elements[0]->content)));
						
						for ($i=0 ; $i<$nbl ; $i++){
							for ($j=0 ; $j<$nbc ; $j++){
								$element = explode('//..*..//', $elements[$j]->content);
								$elt = ($elements[$j]->plugin=='fabrikdate' && $element[$i]>0)?strftime(JText::_('DATE_FORMAT_LC3'),strtotime ($element[$i])):$element[$i];
								$htmldata .= '<b>'.$elements[$j]->label.': </b>'.$elt.'<br/>';
							}
							$htmldata .= '____<br />';
						}
					// AFFICHAGE EN LIGNE
					} else { 
						foreach($elements as &$element) {
							if(!empty($element->label) && $element->label!=' ') {
								$elt = ($element->plugin=='fabrikdate' && $element->content>0)?strftime(JText::_('DATE_FORMAT_LC3'),strtotime ($element->content)):$element->content;
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
				$pdf->Ln(2);
				$end_page = $pdf->getPage();
				if ($end_page != $start_page) {
					$pdf = $pdf->rollbackTransaction();
					$pdf->addPage(); 
					$pdf->Bookmark($itemt->label, 0);
					$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
					$pdf->Ln(2);
				}
				$htmldata = '';
			}
///			
		}	
	}
	
	@chdir('tmp');
	if($output){
		if($current_user->usertype != $registered){
			//$output?'FI':'F'
			$name = 'application_form_'.date('Y-m-d_H-i-s').'_'.rand(1000,9999).'.pdf';
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.$name, 'FI');
			$query = 'INSERT INTO #__emundus_uploads (user_id,attachment_id,filename,description,can_be_deleted,can_be_viewed) VALUES ('.$item->user_id.',(
										   SELECT id 
										   FROM #__emundus_setup_attachments 
										   WHERE lbl = "_application_form"),"'.$name.'","'.date('Y-m-d H:i:s').'",0,0)';
			$db->setQuery($query);
			$db->query();
		}else{
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'application.pdf', 'FI');
		}
	}else{
		$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'application.pdf', 'F');
	}
}
?>