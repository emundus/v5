<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // Le modifieur 'G' est disponible depuis PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

	function export_csv($uids, $element_id) {
		$memory_limit = ini_get('memory_limit');
		if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
		    if ($matches[2] == 'M') {
		        $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
		    } else if ($matches[2] == 'K') {
		        $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
		    }
		}
			$current_user =& JFactory::getUser();
			$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
			if (!in_array($current_user->usertype, $allowed)) die( JText::_('RESTRICTED_ACCESS') );

			@set_time_limit(10800);
			global $mainframe;
			$baseurl = JURI::base();
			$db	= &JFactory::getDBO();
			jimport( 'joomla.user.user' );
			error_reporting(0);
			
			$filename = 'emundus_applicants_'.date('Y.m.d').'.csv';
			$realpath = EMUNDUS_PATH_REL.'tmp/'.$filename;
			$fp = fopen($realpath, 'w');

			$query = 'SELECT sub_values, sub_labels FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
			$db->setQuery( $query );
			$result = $db->loadRowList();
			$sub_values = explode('|', $result[0][0]);
			foreach($sub_values as $sv)
				$patterns[]="/".$sv."/";
			$grade = explode('|', $result[0][1]);
			

			require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
			require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
			require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
			
			$model = new EmundusModelEvaluation;
			$model->getUsers();
			$users=$model->_applicants;
			
			$profile = EmundusHelperList::getProfiles();
			
			$col = new EmundusModelEvaluation;
			$column = $col->getEvalColumns();
			
			/// ****************************** ///
			// Elements selected by administrator
			/// ****************************** ///
			$query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.label AS element_label, INSTR(groupe.attribs,"repeat_group_button=1") AS group_repeated, tab.db_table_name AS table_name
						FROM #__fabrik_elements element	
						INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
						INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
						INNER JOIN #__fabrik_tables AS tab ON tab.form_id = formgroup.form_id
						INNER JOIN #__menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("tableid=",menu.link)+8, 3), "&", 1)
						WHERE tab.state = 1 
						AND (tab.created_by_alias = "form" OR tab.created_by_alias = "comment")
						AND element.state=1 
						AND element.hidden=0 
						AND element.label!=" " 
						AND element.label!="" 
						AND element.id IN ("'.implode('","', $element_id).'") 
						ORDER BY menu.ordering, formgroup.ordering, groupe.id, element.ordering'; 
			$db->setQuery( $query );
			//die(str_replace("#_","jos",$query));
			$elements = $db->loadObjectList();		
			
			// @TODO : g?n?rer une chaine de caract?re avec tous les user_id
			
			// Starting a session.
			$session =& JFactory::getSession();
			if($uids != ''){
				foreach($users as $key=>$value){
					if(in_array($value['user_id'],$uids)){
						$us[] = $users[$key];
					}
				}
				$user_id = $uids;
				$users = $us;
				$session->clear( 'uid' );
			}else{
				foreach($users as $user){
					$user_id[] = $user['user_id'];
				}
			}
			
			$session->clear( 'profile' );
			$session->clear( 'finalgrade' );
			$session->clear( 'quick_search' );
			$session->clear( 'groups' );
			$session->clear( 'evaluator' );
			unset($us);
			
			$select = '';
			$table = '';
			
			foreach($elements as $element) {
				if(!array_key_exists($element->element_name,$users[0]) || (($element->element_name == 'user_id' || $element->element_name == 'comment') && $element->table_name == 'jos_emundus_comments'))	{
					if($element->table_name == 'jos_emundus_comments')
						$select_comment .= '`'.$element->table_name.'`.`'.$element->element_name.'`,';
					else
						$select .= '`'.$element->table_name.'`.`'.$element->element_name.'`,';
					if($table != $element->table_name){
						if($element->table_name == 'jos_emundus_comments') $join_comment .= ' LEFT JOIN `'.$element->table_name.'` ON `'.$element->table_name.'`.`applicant_id`=`#__users`.`id`';
						else $join .= ' LEFT JOIN `'.$element->table_name.'` ON `'.$element->table_name.'`.`user`=`#__users`.`id`';
					}
				$table = $element->table_name;
				}
			}
			$query = 'SELECT ';
			$query .= $select;
			$query .= ' `#__users`.`id` AS user
						FROM `#__users` 
						LEFT JOIN `#__emundus_users` ON `#__emundus_users`.`user_id`=`#__users`.`id`';
			$query .= $join;
			$query .= 'WHERE `#__users`.`usertype`="Registered" and `#__users`.`id` IN ('.implode(',', $user_id).') 
						ORDER BY `#__emundus_users`.`user_id`,`#__emundus_users`.`lastname`,`#__emundus_users`.`firstname`';
			//die(str_replace('#_','jos',$query));
			$db->setQuery( $query );
			$valeurs = $db->loadObjectList('user');			
			
			$query='';
			$query = 'SELECT ';
			$query .= $select_comment;
			$query .= ' `#__users`.`id` AS user
						FROM `#__users` 
						LEFT JOIN `#__emundus_users` ON `#__emundus_users`.`user_id`=`#__users`.`id`';
			$query .= $join_comment;
			$query .= ' WHERE `#__users`.`usertype`="Registered" and `#__users`.`id` IN ('.implode(',', $user_id).') 
						ORDER BY `#__emundus_users`.`user_id`,`#__emundus_users`.`lastname`,`#__emundus_users`.`firstname`';
			$db->setQuery( $query );
			$comments = $db->loadObjectList();
			
			$colonne_by_id = array();
			for ($i=ord("A");$i<=ord("Z");$i++) {
				$colonne_by_id[]=chr($i);
			}
			for ($i=ord("A");$i<=ord("Z");$i++) {
				for ($j=ord("A");$j<=ord("Z");$j++) {
					$colonne_by_id[]=chr($i).chr($j);
					if(count($colonne_by_id) == count($users)) break;
				}
			}
			
		// ********************************************
		//				En-tete de colonnes
		// ********************************************
		
		foreach($users[0] as $key=>$value){
			if($column[$key]){
					$newline[] = $column[$key]['label'];
				}elseif($key != 'final_grade' && $key != 'row_id' && $key != 'user'){
					$newline[] = ucfirst($key);
				}
		}
			
		$tab_com = '';
		$count = 0;
		foreach($elements as $element) {
			//Only one header of comment
			if($element->table_name == 'jos_emundus_comments'){ 
				if($tab_com != $element->table_name){
					$newline[] = 'Comments,';
					$tab_com = $element->table_name;
				}
				$count++;
			}elseif(!array_key_exists($element->element_name, $users[0]))	{
				$newline[] = $element->element_label;
			}
		}
		fputcsv($fp, $newline);	
		$i=2;
			

		$line = 0;
		foreach ($users as $user){
				unset($newline);
		// ********************************************
		//		Colonnes correspondants au model
		// ********************************************
		
				foreach($user as $key=>$value) {
					if($key == 'user_id'){
						$newline[] = $value;
					}elseif($key == 'profile'){
						$value = $profile[$user['profile']]->label;
					}elseif($key == 'name'){
						$value = preg_replace('/<[^>]+>/', '', $value);
					}
					if($key != 'final_grade' && $key != 'row_id' && $key != 'user' && $key != 'user_id'){
						$value = !empty($value)?$value:'';
						$newline[] = $value;
					}
				}
				
			// ********************************************
			//				Application form
			// ********************************************

				$comment_val= '';
				$tab_com = '';
				$c = 0;
				foreach($elements as $element){
					if(!array_key_exists($element->element_name,$users[0]) || (($element->element_name == 'user_id' || $element->element_name == 'comment') && $element->table_name == 'jos_emundus_comments'))	{
						$el = $element->element_name;
						if($element->table_name != 'jos_emundus_comments') $value = $valeurs[$user['user_id']]->$el;
						if ($element->group_repeated>0) 
							$value = str_replace("//..*..//", "\n ----- \n", $valeurs[$user['user_id']]->$el);
						if($element->element_label == "Telephone" || $element->element_label == "Zip code" || $element->element_label == "Fax number") 
							$value =" ".$value;
						if($element->element_label == 'email'){
							$newline[] = $value;
						}
						//have comment, date and reason in the same square (many rows of comments)
						if($element->table_name == 'jos_emundus_comments'){
							$c++;
							foreach($comments as $comment){
								if($comment->user == $user['user_id']) {
									if($element->element_name == 'user_id'){
										$query = 'SELECT name FROM #__users WHERE id ='.$comment->$el;
										$db->setQuery( $query );
										$tab_value[] = $db->loadResult();
									}else{
										$tab_value[] = $comment->$el;
									}
								}
							}

							if($c == $count){
								//have comment, date and reason in the same case (many rows of comments)
								$nb_com = count($tab_value)/$count;
								for($j = 0; $j < $nb_com; $j++){
									$value .= $tab_value[$j];
									if($count ==1 && !empty($value)) $value .= "\n";
									if($count ==2 && !empty($value)) $value .= ' || '.$tab_value[$j+$nb_com]."\n";
									if($count ==3 && !empty($value)) $value .= ' || '.$tab_value[$j+$nb_com].' || '.$tab_value[$j+($nb_com*2)]."\n";
									if($count ==4 && !empty($value)) $value .= ' || '.$tab_value[$j+$nb_com].' || '.$tab_value[$j+($nb_com*2)].' || '.$tab_value[$j+($nb_com*3)]."\n";
								}
							}
						}
						if($element->table_name != 'jos_emundus_comments' || $c == $count){
							$newline[] = $value;
							//clean comment square for follow applicant
							$tab_value ='';
							$value = '';
						}
					}
				}				
				$i++;
				fputcsv($fp, $newline);

				$mem_overflow = ($memory_limit-memory_get_usage()<1048576)?true:false;
				if ($mem_overflow) {
					die('Serveur limit memory is low. You probably should export files for 50 applicants maximum at once. In that case, export in CSV format !');
				}
			}

			fclose($fp);

	//////////////////////////////////////////////
			$mtime = ($mtime = filemtime($realpath)) ? $mtime : gmmktime();
			$size = intval(sprintf("%u", filesize($realpath)));
			// Maybe the problem is we are running into PHPs own memory limit, so:
			if (intval($size + 1) > return_bytes(ini_get('memory_limit')) && intval($size * 1.5) <= 1073741824) { //Not higher than 1GB
			  ini_set('memory_limit', intval($size * 1.5));
			}
			// Maybe the problem is Apache is trying to compress the output, so:
			//@apache_setenv('no-gzip', 1);
			@ini_set('zlib.output_compression', 0);
			// Maybe the client doesn't know what to do with the output so send a bunch of these headers:
			header("Content-type: application/force-download");
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="'.$filename.'"; modification-date="' . date('r', $mtime) . '";');
			// Set the length so the browser can set the download timers
			header("Content-Length: " . $size);
			// If it's a large file we don't want the script to timeout, so:
			set_time_limit(480);
			// If it's a large file, readfile might not be able to do it in one go, so:
			$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
			if ($size > $chunksize) {
			  $handle = fopen($realpath, 'rb');
			  $buffer = '';
			  while (!feof($handle)) {
				$buffer = fread($handle, $chunksize);
				echo $buffer;
				ob_flush();
				flush();
			  }
			  fclose($handle);
			} else {
			  readfile($realpath);
			}
			
			unlink($realpath);

	exit;
	}

?>