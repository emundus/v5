<?php
/**
 * Application Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelApplication extends JModel
{
	var $_user = null;
	var $_db = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		global $option;
		
		$this->_mainframe = JFactory::getApplication();
		
		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getUser();		
	}
	
	function getApplicantInfos($aid, $param){
		$query = 'SELECT '.implode(",", $param).' 
				FROM #__users
				LEFT JOIN #__emundus_users ON #__emundus_users.user_id=#__users.id
				LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=#__users.id
				LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
				LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=#__users.id AND #__emundus_uploads.attachment_id=10)
				WHERE #__users.id='.$aid;
		$this->_db->setQuery( $query );
		$infos =  $this->_db->loadAssoc();
//echo str_replace("#_", "jos", $query);
//var_dump($infos);
		return $infos;
	}

	function getApplicantDetails($aid, $ids){
		$details = EmundusHelperList::getElementsDetailsByID($ids);
		$select=array();
		foreach ($details as $detail) {
			$select[] = $detail->tab_name.'.'.$detail->element_name.' AS "'.$detail->element_id.'"';
		}

		$query = 'SELECT '.implode(",", $select).' 
				FROM #__users u 
				LEFT JOIN #__emundus_users ON #__emundus_users.user_id=u.id
				LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=u.id
				LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
				LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=u.id AND #__emundus_uploads.attachment_id=10)
				WHERE u.id='.$aid;
		$this->_db->setQuery( $query );
		$values =  $this->_db->loadAssoc();

		foreach ($details as $detail) {
			$detail->element_value = $values[$detail->element_id];
		}
//var_dump($details);
		return $details;
	}
	
	function getUserCampaigns($id){
		
		$query = 'SELECT esc.*, ecc.date_submitted, ecc.submitted, ecc.id as campaign_candidature_id, efg.result_sent, efg.date_result_sent, efg.final_grade
			FROM #__emundus_users eu
			LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
			LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
			LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
			WHERE eu.user_id="'.$id.'"';
			// echo str_replace ('#_', 'jos', $query);
			$this->_db->setQuery( $query );
			return $this->_db->loadObjectList();
	}
	
	function getUserAttachments($id){
		
		$query = 'SELECT eu.id AS aid, esa.*, eu.filename, eu.description, eu.timedate, esc.label as campaign_label, esc.year, esc.training 
            FROM #__emundus_uploads AS eu
            LEFT JOIN #__emundus_setup_attachments AS esa ON  eu.attachment_id=esa.id
			LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=eu.campaign_id
            WHERE eu.user_id = '.$id;'
            ORDER BY esa.ordering';
        $this->_db->setQuery( $query );
        return $this->_db->loadObjectList();
	}
	
	function getUsersComments($id){ 
		
		$query = 'SELECT ec.id, ec.comment_body as comment, ec.reason, ec.date, u.name
				FROM #__emundus_comments ec 
				LEFT JOIN #__users u ON u.id = ec.user_id 
				WHERE ec.applicant_id ="'.$id.'" 
				ORDER BY ec.date DESC ';
		$this->_db->setQuery( $query );
		// echo str_replace ('#_', 'jos', $query);
		return $this->_db->loadObjectList();
	}

	function deleteComment($id){ 
		$query = 'SELECT user_id FROM #__emundus_comments WHERE id="'.$id.'"';
		$this->_db->setQuery( $query );
		$result=$this->_db->loadResult();
		if($result==$this->_user->id){
			$query = 'DELETE FROM #__emundus_comments WHERE id = '.$id;
			$this->_db->setQuery($query);
			// die(str_replace ('#_', 'jos', $query));
			return $this->_db->Query();
		}else{
			return -1;
		}
	}

	function addComment($row){ 
		$query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason, date, comment_body) 
				VALUES('.$row['applicant_id'].','.$row['user_id'].','.$this->_db->Quote($row['reason']).',"'.date("Y.m.d H:i:s").'",'.$this->_db->Quote($row['comment_body']).')';
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function deleteData($id, $table){ 
		$query = 'DELETE FROM `'.$table.'` WHERE id='.$id;
		$this->_db->setQuery($query);

		return $this->_db->Query();
	}

	function deleteAttachment($id){ 
		$query = 'SELECT * FROM #__emundus_uploads WHERE id='.$id;
		$this->_db->setQuery( $query );
		$file = $this->_db->loadAssoc();

		$f = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename']; 
		@unlink($f);
		/*if(!@unlink($f) && file_exists($f)) {
			// JError::raiseError(500, JText::_('FILE_NOT_FOUND').$file);
			//$this->setRedirect($url, JText::_('FILE_NOT_FOUND'), 'error');
			return -1;
		}*/
			
		$query = 'DELETE FROM #__emundus_uploads WHERE id='.$id; 
		$this->_db->setQuery( $query );
		
		return $this->_db->Query();
	}

	function uploadAttachment($data) {
		$query = 'INSERT INTO #__emundus_uploads ('.implode(",", $data["key"]).') VALUES ('.implode(",", $data["value"]).')';
		$this->_db->setQuery( $query );
		$this->_db->Query() or die($this->_db->getErrorMsg());

		return $this->_db->insertid();
	}

	function getAttachmentByID($id) {
		$query = "SELECT * FROM #__emundus_setup_attachments WHERE id=".$id;
		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	function getUploadByID($id) {
		$query = "SELECT * FROM #__emundus_uploads WHERE id=".$id;
		$this->_db->setQuery($query);

		return $this->_db->loadAssoc();
	}

	function getFormsProgress($aid, $pid) {
		$query = 'SELECT distinct(esa.value) 
					FROM #__emundus_setup_attachment_profiles AS profiles 
					LEFT JOIN #__emundus_setup_attachments AS esa ON esa.id=profiles.attachment_id 
					WHERE profiles.profile_id = '.$pid.' AND profiles.attachment_id NOT IN (select attachment_id FROM #__emundus_uploads 
					WHERE user_id = '.$aid.') 
					ORDER BY esa.ordering';
		$this->_db->setQuery($query);
		$attachmentsLst = $this->_db->loadResultArray();
		
		$forms = EmundusHelperMenu::buildMenuQuery($pid);
		
		$nb = 0;
		$formLst = array();
		foreach ($forms as $form) {
			$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$aid;
			$this->_db->setQuery( $query );
			$cpt = $this->_db->loadResult();
			if ($cpt==1) {
				$nb++;
			} else {
				$formLst[] = $form->label;
			}
		}
		
		return  @floor(100*$nb/count($forms));
	}
	
	function getAttachmentsProgress($aid, $pid) {
		$query = 'SELECT 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id)
				FROM #__emundus_setup_attachment_profiles AS profiles 
				LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$aid.'
				WHERE profiles.profile_id = '.$pid.' AND profiles.displayed = 1 AND profiles.mandatory = 1 ';
		$this->_db->setQuery($query);
		
		return floor($this->_db->loadResult());
	}

	function getLogged ($aid) {
		$user = JFactory::getUser();
		$query = 'SELECT s.time, s.client_id, u.id, u.name, u.username
					FROM #__session AS s
					LEFT JOIN #__users AS u on s.userid = u.id 
					WHERE u.id = "'.$aid.'"';
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();

		// Check for database errors
		if ($error = $this->_db->getErrorMsg()) {
			JError::raiseError(500, $error);
			return false;
		};

		foreach($results as $k => $result)
		{
			$results[$k]->logoutLink = '';

			if($user->authorise('core.manage', 'com_users'))
			{
				$results[$k]->editLink = JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid='.$result->id.'&tmpl=component');
				$results[$k]->logoutLink = JRoute::_('index.php?option=com_login&task=logout&uid='.$result->id .'&'. JSession::getFormToken() .'=1');
			}
			$results[$k]->name = $results[$k]->username;
		}

		return $results;
	}

	function getForms($aid) {
		$tableuser = EmundusHelperList::getFormsList($aid); 

		$forms = "";
		if(isset($tableuser)) {
			foreach($tableuser as $key => $itemt) { 
				$forms .= '<br><h3>';
				$forms .= $itemt->label;

				if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && $itemt->db_table_name != "jos_emundus_training"){
					$forms .= ' <a href="index.php?option=com_fabrik&view=form&formid='.$itemt->form_id.'&usekey=user&rowid='.$aid.'" alt="'.JText::_('EDIT').'" target="_blank"><i class="icon edit">'.JText::_('EDIT').'</i></a>';
				}
 
				$forms .= '</h3>';
				// liste des groupes pour le formulaire d'une table
				$query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, INSTR(fg.params,"\"repeat_group_button\":\"1\"") as repeated, INSTR(fg.params,"\"repeat_group_button\":1") as repeated_1
							FROM #__fabrik_formgroup ff, #__fabrik_groups fg
							WHERE ff.group_id = fg.id AND
								  ff.form_id = "'.$itemt->form_id.'" 
							ORDER BY ff.ordering';
				$this->_db->setQuery( $query );
				$groupes = $this->_db->loadObjectList();
				
				/*-- Liste des groupes -- */
				foreach($groupes as $keyg => $itemg) {

					// liste des items par groupe
					$query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
								FROM #__fabrik_elements fe
								WHERE fe.published=1 AND 
									  fe.hidden=0 AND 
									  fe.group_id = "'.$itemg->group_id.'" 
								ORDER BY fe.ordering';
					$this->_db->setQuery( $query );
					$elements = $this->_db->loadObjectList();
					if(count($elements)>0) {
						$forms .= '<fieldset><legend>';
						$forms .= $itemg->label;
						$forms .= '</legend>';
						foreach($elements as &$iteme) {
							$query = 'SELECT `'.$iteme->name .'` FROM `'.$itemt->db_table_name.'` WHERE user='.$aid;
							$this->_db->setQuery( $query );
							$iteme->content = $this->_db->loadResult();
						}
	 					unset($iteme);
						
						if ($itemg->group_id == 14) {

						     foreach($elements as &$element) {
								if(!empty($element->label) && $element->label!=' ') {
									if ($element->plugin=='date' && $element->content>0) {
										$date_params = json_decode($element->params);
										$elt = strftime($date_params->date_form_format, strtotime($element->content));
									} else $elt = $element->content;
									$forms .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
								}
							 }
		
						// TABLEAU DE PLUSIEURS LIGNES
					} elseif ($itemg->repeated > 0 || $itemg->repeated_1 > 0){
						$forms .= '<table class="adminlist">
							  <thead>
							  <tr> ';
						
						//-- Entrée du tableau -- */
						//$nb_lignes = 0;
						$t_elt = array();
						foreach($elements as $key => $element) { 
							$t_elt[] = $element->name;
							if ($element->name != 'id' && $element->name != 'parent_id') 
								$forms .= '<th scope="col">'.$element->label.'</th>';
						}
						unset($element);
						//$table = $itemt->db_table_name.'_'.$itemg->group_id.'_repeat';
						$query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id='.$itemg->group_id.' AND list_id='.$itemt->table_id;
						$this->_db->setQuery($query);
						$table = $this->_db->loadResult();

						if($itemg->group_id == 174)
							$query = 'SELECT `'.implode("`,`", $t_elt).'`, `id` FROM `'.$table.'` 
										WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.') OR applicant_id='.$aid;
						else
							$query = 'SELECT `'.implode("`,`", $t_elt).'`, `id` FROM `'.$table.'` 
									WHERE parent_id=(SELECT id FROM '.$itemt->db_table_name.' WHERE user='.$aid.')';
				//$forms .= $query;
						$this->_db->setQuery($query);
						$repeated_elements = $this->_db->loadObjectList();
						unset($t_elt);
//print_r($repeated_elements);
						$forms .= '</tr></thead><tbody>';
						// -- Ligne du tableau -- 
						foreach ($repeated_elements as $r_element) {
							$linked = false;
							$forms .= '<tr>';
								$j = 0;
								foreach ($r_element as $key => $r_elt) {
									if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {
										if ($elements[$j]->plugin=='date') {
											$date_params = json_decode($elements[$j]->params);
											$elt = strftime($date_params->date_form_format, strtotime($r_elt));
										} elseif($elements[$j]->plugin=='databasejoin') {
												$params = json_decode($elements[$j]->params);
												$select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;
												$from = $params->join_db_name;
												$where = $params->join_key_column.'='.$this->_db->Quote($r_elt);
												$query = "SELECT ".$select." FROM ".$from." WHERE ".$where;
												$query = preg_replace('#{thistable}#', $from, $query);
												$query = preg_replace('#{my->id}#', @$item->user_id, $query);
												$this->_db->setQuery( $query );
												$elt = $this->_db->loadResult();
										} else 
											$elt = $r_elt;
//print_r($this->_mainframe->data);
										if(EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
											//$delete_link = '<div class="comment_icon" id="training_'.$r_element->id.'"><img src="'.JURI::Base().'/media/com_emundus/images/icones/button_cancel.png" onClick="if (confirm('.htmlentities('"'.JText::_("DELETE_CONFIRM").'"').')) {deleteData('.$r_element->id.', \''.$table.'\');}"/></div>';
											$delete_link = !$linked?'<a class=​"ui" name="delete_course" data-title="'.JText::_('DELETE_CONFIRM').'" onClick="$(\'#confirm_type\').val(this.name); $(\'#course_id\').val('.$r_element->id.'); $(\'#course_table\').val(\''.$table.'\'); $(\'.basic.modal.confirm.course\').modal(\'show\');"><i class="trash icon"></i>​</a>​':'';
											$forms .= '<td><div id="em_training_'.$r_element->id.'" class="course '.$r_element->id.'">'.$delete_link.' '.$elt.'</div></td>';
											$linked = true;
										} else {
											$forms .= '<td><div id="em_training_'.$r_element->id.'" class="course '.$r_element->id.'">'.$elt.'</div></td>';
										}
									}
									$j++;
								}
								$forms .= '</tr>';
						}
						$forms .= '</tbody></table>';

					// AFFICHAGE EN LIGNE
					} else { 
						foreach($elements as &$element) {
							if(!empty($element->label) && $element->label!=' ') {
								if ($element->plugin=='date' && $element->content>0) {
									$date_params = json_decode($element->params);
									$elt = strftime($date_params->date_form_format, strtotime($element->content));
								} else $elt = $element->content;
								$forms .= '<b>'.$element->label.': </b>'.$elt.'<br/>';
							}
						}
					}
					$forms .= '</fieldset>';
				}
			}
		}
	}
	return $forms;
	}
	
	function getEmail($user_id){
		$query = 'SELECT *
		FROM #__messages as email
		LEFT JOIN #__users as user ON user.id=email.user_id_from 
		LEFT JOIN #__emundus_users as eu ON eu.user_id=user.id
		WHERE email.user_id_to ='.$user_id.' ORDER BY `date_time` DESC';
		$this->_db->setQuery($query);
		$results['to'] = $this->_db->loadObjectList('message_id');
		
		$query = 'SELECT * 
		FROM #__messages as email
		LEFT JOIN #__users as user ON user.id=email.user_id_to 
		LEFT JOIN #__emundus_users as eu ON eu.user_id=user.id 
		WHERE email.user_id_from ='.$user_id.' ORDER BY `date_time` DESC';
		$this->_db->setQuery($query);		
		$results['from'] = $this->_db->loadObjectList('message_id');
		
		return $results;
	}

}

?>