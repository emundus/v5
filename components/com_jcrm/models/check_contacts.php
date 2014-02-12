<?php
/**
 * Jcrm Model for Jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Deision Publique
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

/**
 * Jcrm Model
 *
 * @package    Joomla
 * @subpackage 	Jcrm
 */
class JcrmModelCheck_contacts extends JModel{
	
	/**
	 * Reference contacts list for tmp store
	 *
	 * @var array $_data_contacts 
	 * @access private
	 */
	private $_data_contacts=array();
	
	/**
	 * Reference accounts list for tmp store
	 *
	 * @var array $_data_account 
	 * @access private
	 */
	private $_data_account;
	
	/**
	* Pagination object for jcrmaccountslist
	* @var object $_pagination
	* @access private
	*/
	private $_pagination=null;
	
	/**
	 * The words list of names of all jcrmaccounts for tmp store
	 *
	 * @var array $_account_words 
	 * @access private
	 */
	private $_account_words=array();
	
	private $_rank=false;
	/**
    * Constructor
    *
    * @return (void)
    * @access private
    *    
    */
	function __construct(){
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
		$filter_order     = $mainframe->getUserStateFromRequest(  $option.'filter_order', 'filter_order', 'last_name', 'cmd' );
        $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
 
        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
 
	} // end function
	
	
/*-------------------*/    
/* F U N C T I O N S */
/*-------------------*/




	/**
	 * Gets the data of contacts from references
	 *
	 * @return (array) the list of contacts
	 * @access public
	 *
	 */
	public function getContacts(){
		$db= JFactory::getDBO();
		$search_option=JRequest::getVar('search_option','');
		$search=JRequest::getVar('search','');
		// get the data of first contact in references
		$query = "SELECT id ,First_Name_1, Last_Name_1 ,Email_1 ,Organisation_1 ,id_account_1, id_contact_1 FROM `#__emundus_references` where id_contact_1=0 and Last_Name_1<>''";
		if(!empty($search)){
		// gets the element to be searched 
		if(!empty($search_option)&&$search_option=="first_name"){$query.='and  First_Name_1 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="last_name"){$query.='and  Last_Name_1 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="email"){$query.='and  Email_1 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="organisation"){$query.='and  Organisation_1 like "%'.$search.'%"';}
		else {$query.='AND  First_Name_1 like "%'.$search.'%" OR Last_Name_1 like "%'.$search.'%" OR Email_1 like "%'.$search.'%" OR Organisation_1 like "%'.$search.'%"';}
		}
		// Excute the query
		$db->setQuery($query); 
		$contacts = $db->loadObjectlist();
		//Reconstuct an array for temporary use
		foreach($contacts as $contact){
		$tmp_contact['id']=$contact->id.'-1';
		$tmp_contact['first_name']=$contact->First_Name_1;
		$tmp_contact['last_name']=$contact->Last_Name_1;
		$tmp_contact['email']=$contact->Email_1;
		$tmp_contact['organisation']=$contact->Organisation_1;
		$tmp_contact['id_account']=$contact->id_account_1;
		$tmp_contact['id_contact']=$contact->id_contact_1;
		$tmp_contacts[]=$tmp_contact;
		}
		// get the data of second contact in references
		 $query = "SELECT id ,First_Name_2, Last_Name_2,Email_2 ,Organisation_2,id_account_2, id_contact_2 FROM `#__emundus_references` where id_contact_2=0 and Last_Name_2<>'' ";
		 // Gets the element to be searched
		if(!empty($search)){
		if(!empty($search_option)&&$search_option=="first_name"){$query.='and  First_Name_2 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="last_name"){$query.='and  Last_Name_2 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="email"){$query.='and  Email_2 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="organisation"){$query.='and  Organisation_2 like "%'.$search.'%"';}
		else {$query.='AND  First_Name_2 like "%'.$search.'%" OR Last_Name_2 like "%'.$search.'%" OR Email_2 like "%'.$search.'%" OR Organisation_2 like "%'.$search.'%"';}
		}
			
		$db->setQuery($query);
		$contacts = $db->loadObjectlist();
		//Store the data in the temprory array
		foreach($contacts as $contact){
		$tmp_contact['id']=$contact->id.'-2';
		$tmp_contact['first_name']=$contact->First_Name_2;
		$tmp_contact['last_name']=$contact->Last_Name_2;
		$tmp_contact['email']=$contact->Email_2;
		$tmp_contact['organisation']=$contact->Organisation_2;
		$tmp_contact['id_account']=$contact->id_account_2;
		$tmp_contact['id_contact']=$contact->id_contact_2;
		$tmp_contacts[]=$tmp_contact;
		}
		
		 // get the data of third contact in references
		$query = "SELECT id ,First_Name_3, Last_Name_3,Email_3 ,Organisation_3,id_account_3, id_contact_3 FROM `#__emundus_references` where id_contact_3=0 and Last_Name_3<>'' ";
		// Gets the element to be searched 
		if(!empty($search)){
		if(!empty($search_option)&&$search_option=="first_name"){$query.='and  First_Name_3 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="last_name"){$query.='and  Last_Name_3 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="email"){$query.='and  Email_3 like "%'.$search.'%"';}
		elseif(!empty($search_option)&&$search_option=="organisation"){$query.='and  Organisation_3 like "%'.$search.'%"';}
		else {$query.='AND  First_Name_3 like "%'.$search.'%" OR Last_Name_3 like "%'.$search.'%" OR Email_3 like "%'.$search.'%" OR Organisation_3 like "%'.$search.'%"';}
		}
		 $db->setQuery($query);
		$contacts = $db->loadObjectlist();
		foreach($contacts as $contact){
		$tmp_contact['id']=$contact->id.'-3';
		$tmp_contact['first_name']=$contact->First_Name_3;
		$tmp_contact['last_name']=$contact->Last_Name_3;
		$tmp_contact['email']=$contact->Email_3;
		$tmp_contact['organisation']=$contact->Organisation_3;
		$tmp_contact['id_account']=$contact->id_account_3;
		$tmp_contact['id_contact']=$contact->id_contact_3;
		$tmp_contacts[]=$tmp_contact;
		}
	
		if(!empty($tmp_contacts)){$this->_data_contacts=$tmp_contacts;} 
		
		 
		 return $this->_buildContentOrderBy();
	} // end function
	
	
	
	/**
	 * Gets the list of contacts from references after being filtered
	 *
	 * @return (array) the list of contacts
	 * @access public
	 *
	 */
	function _buildContentOrderBy(){
	
		global $mainframe, $option;
		$tmp = array();
		//Gets the filter order and filter order direction
		$filter_order     = $this->getState('filter_order');
		$filter_order_Dir = $this->getState('filter_order_Dir');
		//Do a cicle for the filter order direction
		$sort=($filter_order_Dir=='desc')?SORT_DESC:SORT_ASC;
		//Define the elements can be ordered
 		$can_be_ordering = array ('id', 'first_name', 'last_name','email','organisation');
		//Define the default filter order and filter order direction
		if ($this->_rank) {
			$this->_data_contacts = $this->multi_array_sort($this->_data_contacts, 'last_name', SORT_DESC);
			$rank=1;
			}
		//Define the filter order and order direction from the users
		if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
			$this->_data_contacts = $this->multi_array_sort($this->_data_contacts, $filter_order, $sort);
		} 
		//Limit the numbers of every page
		$t = count($this->_data_contacts);
		$ls = $this->getState('limitstart');
		$l = $this->getState('limit');
		if ($l==0) {$l=$t; $ls=0;}
		else $l = ($ls+$l>$t)?$t-$ls:$l;
	
		for ($i=$ls ; $i<($ls+$l) ; $i++) {
			$tmp[] = $this->_data_contacts[$i];
		}
		return $tmp;
	} // End function
	
	
	/**
	 * Gets list of array after being sorted
	 *
	 * @return (array)
	 * @access public
	 *
	 */
	function multi_array_sort($multi_array=array(),$sort_key,$sort=SORT_ASC){  
        if(is_array($multi_array)){  
            foreach ($multi_array as $key=>$row_array){  
                if(is_array($row_array)){  
                    $key_array[$key] = $row_array[$sort_key]; 
                }else{  
                    return -1;  
                } 
            } 
        }else{  
            return -1;  
        } 
		if(!empty($key_array))
	        array_multisort($key_array,$sort,$multi_array);		
        return $multi_array;  
	} // End function
	
	
	
	
	/**
	 * Gets the soundex name of all names in the jcrmaccounts
	 *
	 * @return (array) the list of soundex name of all names in the accounts
	 * @access public
	 *
	 */
	public function getAccountWords(){
		// Gets the parameter object for the component jcrm 
		$params =JComponentHelper::getParams( 'com_jcrm' );
		// Gets the string of all the words to be excluded
		$words=$params->get( 'excluded_words' );
		$excluded_words=explode(',',$words);
		$db= JFactory::getDBO();
		// Gets the id and name of accounts
		$query='select id, name from `#__jcrm_accounts` where 1';
		$db->setQuery($query);
		$results=$db->loadObjectlist();
		
		foreach($results as $re){
		    // Turn the account name to an array
			$account_names=explode(' ',$re->name);
	     	foreach($account_names as $i=>$account_name){
			foreach($excluded_words as $excluded_word){
			    // Check if the word of accounts name is the same with the words to be excluded
				if($account_name==$excluded_word||$account_name==''||$account_name==null)
				{unset($account_names[$i]);} // end if
      			} // end foreach
			} // end foreach
			foreach($account_names as $name){
			// Turn the words of accounts name left to the name of soundex
			$account_names=soundex($name);
			$this->_account_words[$re->id][]=soundex($name);
			}	// end foreach	
		} // end foreach
		return $this->_account_words;
	} // end function
	
	
	
	/**
	 * Check the organisations of references
	 *
	 * @return (array) $_data_account The data of organistion in the references
	 * @access public
	 * @param (array) $cids All the id of renferences selected to be checked
	 *
	 */
	public function checkOrg($i_index,$cids){
		
		foreach($cids as $id_acct){
		$db= JFactory::getDBO();
		// Turn the id of reference to an array
		 $arr=explode('-',$id_acct); 
		// Gets the words to be excluded and turn it to an array 
		$params =JComponentHelper::getParams( 'com_jcrm' );
		$exclud_words=$params->get( 'excluded_words' );
		$excluded_words=explode(',',$exclud_words);
		// If it's the first contact of reference 
		if($arr[1]==1){
		// Gets the data of the first contact
		$query="select First_Name_1, Last_Name_1,id_account_1,Organisation_1 from `#__emundus_references` where id=".$arr[0];
		$db->setQuery($query);
		$this->_data_account=$db->loadObject();
		$data_account_1=$this->_data_account;
			
			if($data_account_1->id_account_1!=0){
				 echo'<p class="label_org_in">'.JText::_('ORGANISATION_IN_DB').'</p>';	
			}else{  
					// Turn the name of first organisation to an array
					$src_orgs=explode(' ',$data_account_1->Organisation_1);
					foreach($src_orgs as $i=>$src_org){
					 foreach($excluded_words as $excluded_word){
					// Check the word of organisation if the same with the word to be excluded
					if($src_org==$excluded_word){unset($src_orgs[$i]);}
					 } // end foreach
					 } // end foreach
					 // Turn the words left to the name of soundex
					 foreach ($src_orgs as $src_org){
					 $src_orgs_name[]=soundex($src_org);
					 } // end foreach
					 
					 // Call the function of getting the soundex names in the accounts
					 $dest_account_words=$this->getAccountWords();
					 foreach($dest_account_words as $id_account=>$dest_words){
					  $i=0; 
					  // Check the soundex name of organisation if is the same with the soundex names of accounts
					 if (count(@$src_orgs_name) > 0) {
					 foreach($src_orgs_name as $src_org_name){
							if(in_array("$src_org_name",$dest_words)){
								$i++;
								} // end if
								 if($i==count($src_orgs_name)) {$id_accounts[]=$id_account;}
									} // end foreach
							} // end foreach
					}
				if(empty($id_accounts)){
				return $this->_data_account;
				}else{
				// If can find the same soundex name in the accounts, list them
				$list='<p class="label_org_found">'.JText::_("ACCOUNT_IN").'</p>';
				$list.='<select id="jcrm_accounts_found_'.$id_acct.'" name="jcrm_accounts_found">';
				//$list .= '<option value="">'.JText::_("PLEASE_SELECT").'</option>';
				foreach($id_accounts as $id_account){
					$query="select * from `#__jcrm_accounts` where id=".$id_account;
					$db->setQuery($query);
					$result=$db->loadObject();
					$list.='<option value="'.$result->id.'">'.$result->name.'</option>';
					} // end foreach
					$list.='<option value="new_account">'.JText::_("NEW_ACCOUNT").'</option></select>&nbsp<a href="javascript:update_org('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/valide.png" width="20" height="20" title="'.JText::_('CLICK_TO_VALIDATE').'"/></a>&nbsp<a href="javascript:edit_acct('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDITE_ACCOUNT').'"/></a>';
					echo $list;
				} // end else
			} // end else 
		} // end if
		// If it's the second contact of reference 
		if($arr[1]==2){
		$query="select First_Name_2, Last_Name_2,id_account_2,Organisation_2 from `#__emundus_references` where id=".$arr[0];
		$db->setQuery($query);
		$this->_data_account=$db->loadObject();
		$data_account_2=$this->_data_account;
		if($data_account_2->id_account_2!=0){
			    echo'<p class="label_org_in">'.JText::_('ORGANISATION_IN_DB').'</p>';	
			}else{
			
				$src_orgs=explode(' ',$data_account_2->Organisation_2);
					
					foreach($src_orgs as $i=>$src_org){
					 foreach($excluded_words as $excluded_word){
					
						if($src_org==$excluded_word){unset($src_orgs[$i]);}
					 } // end foreach
					} // end foreach
					foreach ($src_orgs as $src_org){
					 	$src_orgs_name[]=soundex($src_org);
					} // end foreach
					
					$dest_account_words=$this->getAccountWords();
					 
					foreach($dest_account_words as $id_account=>$dest_words){
					  $i=0; 
					  if(count(@$src_orgs_name) > 0) {
						 foreach($src_orgs_name as $src_org_name){
								if(in_array("$src_org_name",$dest_words)){
									$i++;
									} // end if
									 if($i==count($src_orgs_name)) {$id_accounts[]=$id_account;}
										} // end foreach
								} // end foreach
					         // $result[]=$db->loadObject();
						}
				if(empty($id_accounts)){
				return $this->_data_account;
				}else{
				$list='<p class="label_org_found">'.JText::_("ACCOUNT_IN").'</p>';
				$list.='<select id="jcrm_accounts_found_'.$id_acct.'" name="jcrm_accounts_found">';
				//$list.='<option value="">'.JText::_("PLEASE_SELECT").'</option>';
				foreach($id_accounts as $id_account){
					$query="select * from `#__jcrm_accounts` where id=".$id_account;
					$db->setQuery($query);
					$result=$db->loadObject();
					$list.='<option value="'.$result->id.'">'.$result->name.'</option>';
					} // end foreach
					$list.='<option value="new_account">'.JText::_("NEW_ACCOUNT").'</option></select>&nbsp<a href="javascript:update_org('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/valide.png" width="20" height="20" title="'.JText::_('CLICK_TO_VALIDATE').'"/></a>&nbsp<a href="javascript:edit_acct('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDIT_ACCOUNT').'"/></a>';
					echo $list;
				} // end else
			} // end else
		} // end if
		// If it's the third contact of reference 
		if($arr[1]==3){
		$query="select First_Name_3, Last_Name_3,id_account_3,Organisation_3 from `#__emundus_references` where id=".$arr[0];
		$db->setQuery($query);
		$this->_id_account=$db->loadObject();
		$data_account_3=$this->_id_account;
		if($data_account_3->id_account_3!=0){
				echo'<p class="label_org_in">'.JText::_('ORGANISATION_IN_DB').'</p>';
			}else{
			
				$src_orgs=explode(' ',$data_account_3->Organisation_3);
					
					foreach($src_orgs as $i=>$src_org){
					 foreach($excluded_words as $excluded_word){
					
						if($src_org==$excluded_word){unset($src_orgs[$i]);}
					 } // end foreach
					 } // end foreach
					 foreach ($src_orgs as $src_org){
					 $src_orgs_name[]=soundex($src_org);
					 } // end foreach
					
					$dest_account_words=$this->getAccountWords();
					
					 foreach($dest_account_words as $id_account=>$dest_words){
					  $i=0; 
					 foreach($src_orgs_name as $src_org_name){
							if(in_array("$src_org_name",$dest_words)){
								$i++;
								} // end if
								 if($i==count($src_orgs_name)) {$id_accounts[]=$id_account;}
									} // end foreach
							} // end foreach
				if(empty($id_accounts)){
				return $this->_id_account;
				}else{
				$list='<p class="label_org_found">'.JText::_("ACCOUNT_IN").'</p>';
				$list.='<select id="jcrm_accounts_found_'.$id_acct.'" name="jcrm_accounts_found"><option value="">'.JText::_("PLEASE_SELECT").'</option>';
				foreach($id_accounts as $id_account){
					$query="select * from `#__jcrm_accounts` where id=".$id_account;
					$db->setQuery($query);
					$result=$db->loadObject();
					$list.='<option value="'.$result->id.'">'.$result->name.'</option>';
					} // end foreach
					$list.='<option value="new_account">'.JText::_("NEW_ACCOUNT").'</option></select>&nbsp<a href="javascript:update_org('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/valide.png" width="20" height="20" title="'.JText::_('CLICK_TO_VALIDATE').'"/></a>&nbsp<a href="javascript:edit_acct('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDITE_ACCOUNT').'"/></a>';
					echo $list;
				} // end else
		
			} // end else
		  } // end if
		} // end foreach
	} // end function
	
	
	
	/**
	 * Check the contacts of references
	 *
	 * @return (void)
	 * @access public
	 * @param (array)  $cids        All the id of references selected to be checked
	 * @param (string) $first_name  The first name of contact selected in the reference
	 * @param (string) $last_name   The last name of contact selected in the reference
	 * @param (string) $email       The email of contact seleted in the reference 
	 * @param (string) $org_name    The organisation name of contact seleted in the reference 
	 *
	 */
	public function checkContacts($i_index,$cids,$first_name,$last_name,$email,$org_name){
		// Explode the id of reference to an array
		foreach($cids as $id_acct){
		$db= JFactory::getDBO();
		$arr=explode('-',$id_acct);
		// Check if is the first contact in the reference
		if($arr[1]==1){
		$query="select First_Name_1, Last_Name_1,id_account_1,Organisation_1 from `#__emundus_references` where id=".$arr[0];
		$db->setQuery($query);
		$this->_data_account=$db->loadObject();
		$data_account_1=$this->_data_account;
			// if the organisation id doesn't equal to 0, research in the jcrmcontacts
			if($data_account_1->id_account_1!=0){
				$query="select id,first_name,last_name,email from `#__jcrm_contacts` where SOUNDEX(last_name)=SOUNDEX('".$data_account_1->Last_Name_1."') and account_id=".$data_account_1->id_account_1;
				$db->setQuery($query);
				$data_jcrm_1=$db->loadObjectlist();
				// If we can't find it in the jcrmcontacts, define the format of view
				if(empty($data_jcrm_1)){
				$url='index.php?option=com_jcrm&view=contact_form&tmpl=component&fn='.$first_name.'&ln='.$last_name.'&email='.$email.'&on='.$org_name.'&id_ref='.$id_acct.'&id_acct='.$data_account_1->id_account_1;
				 $url=str_replace("'","&acute;",$url);
				 $list='<p class="label_contact_notin">'.JText::_("CONTACT_NOTIN").'</p><p class="label_contact_notin">'.JText::_("PLEASE_ADD").'&nbsp<a href="javascript:add_cont('.$i_index.',\''.$url.'\')"><img src="media/com_jcrm/images/add_sm_user.png" title="'.JText::_('ADD_NEW_CONTACT').'"/></a></p>';
				echo $list;
				}else 
				{   // If we can find it, define the format in the view
					$query="SELECT id_contact_1 FROM #__emundus_references WHERE id=".$arr[0];
					$db->setQuery($query);
					$id_contact_1=$db->loadObject();
					if($id_contact_1->id_contact_1!=0&&$id_contact_1->id_contact_1!=null){echo "<p style='color:green'>".JText::_('VALIDATED')."</p>"; }else{
					$list='<p class="label_contact_in">'.JText::_("CONTACT_IN").'</p><select id="jcrm_contacts_found_'.$id_acct.'" name="jcrm_contacts_found">';
					foreach ($data_jcrm_1 as $data_jcrm_cont){
					// list all the results we can find
						$list.='<option value='.$data_jcrm_cont->id.'>'.$data_jcrm_cont->first_name."&nbsp".$data_jcrm_cont->last_name."&nbsp".$data_jcrm_cont->email.'</option>';
						}
						$list.='<option value="new_contact">'.JText::_("NEW_CONTACT").'</option></select>&nbsp<a href="javascript:update_cont('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/valide.png" width="20" height="20" title="'.JText::_('CLICK_TO_VALIDATE').'" /></a>&nbsp<a href=javascript:add_org('.$i_index.',"index.php?option=com_jcrm&view=contact_form&tmpl=component&id_cont='.$data_jcrm_cont->id.'", 1) ><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDIT_ACCOUNT').'"/></a>';
					echo $list;}
				      } // end else
			}else{ echo '<p class="label_check_org">'.JText::_("CHECK_ORGANISATION").'</p>'; 
					} // end else 
		} // end if
	// If is the second contact in the reference
	if($arr[1]==2){
		$query="select First_Name_2, Last_Name_2,id_account_2,Organisation_2 from `#__emundus_references` where id=".$arr[0];
		$db->setQuery($query);
		$this->_data_account=$db->loadObject();
		$data_account_2=$this->_data_account;
		
			if($data_account_2->id_account_2!=0){
				$query="select id,first_name,last_name,email from `#__jcrm_contacts` where SOUNDEX(last_name)=SOUNDEX('".$data_account_2->Last_Name_2."') and account_id=".$data_account_2->id_account_2;
				$db->setQuery($query);
				$data_jcrm_2=$db->loadObjectlist();
				if(empty($data_jcrm_2)){
				$url='index.php?option=com_jcrm&view=contact_form&tmpl=component&fn='.$first_name.'&ln='.$last_name.'&email='.$email.'&on='.$org_name.'&id_ref='.$id_acct.'&id_acct='.$data_account_2->id_account_2;
				$url=str_replace("'","&acute;",$url);
				$list='<p class="label_contact_notin">'.JText::_("CONTACT_NOTIN").'</p><p class="label_contact_notin">'.JText::_("PLEASE_ADD").'&nbsp<a href="javascript:add_cont('.$i_index.',\''.$url.'\')"><img src="media/com_jcrm/images/add_sm_user.png" title="'.JText::_('ADD_NEW_CONTACT').'"/></a></p>';
				echo $list;
				}else {
				$query="SELECT id_contact_2 FROM #__emundus_references WHERE id=".$arr[0];
					$db->setQuery($query);
					$id_contact_2=$db->loadObject();
					if($id_contact_2->id_contact_2!=0&&$id_contact_2->id_contact_2!=null){echo "<p style='color:green'>".JText::_('VALIDATED')."</p>"; }else{
					$list='<p class="label_contact_in">'.JText::_("CONTACT_IN").'</p><select id="jcrm_contacts_found_'.$id_acct.'" name="jcrm_contacts_found">';
					foreach ($data_jcrm_2 as $data_jcrm_cont){
						$list.='<option value='.$data_jcrm_cont->id.'>'.$data_jcrm_cont->first_name."&nbsp".$data_jcrm_cont->last_name."&nbsp".$data_jcrm_cont->email.'</option>';
						} // end foreach
						$list.='<option value="new_contact">'.JText::_("NEW_CONTACT").'</option></select>&nbsp<a href="javascript:update_cont('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/valide.png" width="20" height="20" title="'.JText::_('CLICK_TO_VALIDATE').'" /></a>&nbsp<a href=javascript:add_org('.$i_index.',"index.php?option=com_jcrm&view=contact_form&tmpl=component&id_cont='.$data_jcrm_cont->id.'", 2) ><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDIT_ACCOUNT').'"/></a>';
					echo $list;}
				     
				      } // end else
			}else{ echo '<p class="label_check_org">'.JText::_("CHECK_ORGANISATION").'</p>'; 
					} // end else 
		} // end if
	// if is the third contact of the reference
    if($arr[1]==3){
		$query="select First_Name_3, Last_Name_3,id_account_3,Organisation_3 from `#__emundus_references` where id=".$arr[0];
		$db->setQuery($query);
		$this->_data_account=$db->loadObject();
		
		$data_account_3=$this->_data_account;
			if($data_account_3->id_account_3!=0){
				$query="select id,first_name,last_name,email from `#__jcrm_contacts` where SOUNDEX(last_name)=SOUNDEX('".$data_account_3->Last_Name_3."') and account_id=".$data_account_3->id_account_3;
				$db->setQuery($query);
				$data_jcrm_3=$db->loadObjectlist();
				if(empty($data_jcrm_3)){
				$url='index.php?option=com_jcrm&view=contact_form&tmpl=component&fn='.$first_name.'&ln='.$last_name.'&email='.$email.'&on='.$org_name.'&id_ref='.$id_acct.'&id_acct='.$data_account_3->id_account_3;
				$url=str_replace("'","&acute;",$url);
				 $list='<p class="label_contact_notin">'.JText::_("CONTACT_NOTIN").'</p><p class="label_contact_notin">'.JText::_("PLEASE_ADD").'&nbsp<a href="javascript:add_cont('.$i_index.',\''.$url.'\')"><img src="media/com_jcrm/images/add_sm_user.png" title="'.JText::_('ADD_NEW_CONTACT').'"/></a></p>';
				  echo $list;
				}else {
				$query="SELECT id_contact_3 FROM #__emundus_references WHERE id=".$arr[0];
					$db->setQuery($query);
					$id_contact_3=$db->loadObject();
					if($id_contact_3->id_contact_3!=0&&$id_contact_3->id_contact_3!=null){echo "<p style='color:green'>".JText::_('VALIDATED')."</p>"; }else{
					$list='<p class="label_contact_in">'.JText::_("CONTACT_IN").'</p><select id="jcrm_contacts_found_'.$id_acct.'" name="jcrm_contacts_found">';
					foreach ($data_jcrm_3 as $data_jcrm_cont){
						$list.='<option value='.$data_jcrm_cont->id.'>'.$data_jcrm_cont->first_name."&nbsp".$data_jcrm_cont->last_name."&nbsp".$data_jcrm_cont->email.'</option>';
						} // end foreach
						$list.='<option value="new_contact">'.JText::_("NEW_CONTACT").'</option></select>&nbsp<a href="javascript:update_cont('.$i_index.',\''.$id_acct.'\')"><img src="media/com_jcrm/images/valide.png" width="20" height="20" title="'.JText::_('CLICK_TO_VALIDE').'" /></a>&nbsp<a href=javascript:add_org('.$i_index.',"index.php?option=com_jcrm&view=contact_form&tmpl=component&id_cont='.$data_jcrm_cont->id.'", 3) ><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDIT_ACCOUNT').'"/></a>';
					echo $list;}
				      } // end else
			}else{ echo '<p class="label_check_org">'.JText::_("CHECK_ORGANISATION").'</p>'; 
					} // end else 
		} // end if		
	} // end foreach
	} // end function
	
	
	
	/**
	 * Method to ignore the contact(s) selected of references:set id_contact=0
	 *
	 * @return (void)
	 * @access public
	 * @param (string)  $id   The id of reference selected
	 *
	 */
	 public function ignoreContact($id){
	
			$db= JFactory::getDBO();
			// Turn the list of id to an array
			$arr_id=explode(',',$id);
			foreach($arr_id as $idc){
			// Turn every id to an array
			$id_cont=explode('-',$idc);
			// If is the first contact inn the reference
			if($id_cont[1]==1){
			   // update dans la database
				$query='update `#__emundus_references` set id_contact_1="-1" where id='.$id_cont[0];
				$db->setQuery($query);
				$db->query();
			}// end if
			// If is the second contact inn the reference
			if($id_cont[1]==2){
				$query='update `#__emundus_references` set id_contact_2="-1" where id='.$id_cont[0];
				$db->setQuery($query);
				$db->query();
			} // end if
			// If is the third contact inn the reference
			if($id_cont[1]==3){
				$query='update `#__emundus_references` set id_contact_3="-1" where id='.$id_cont[0];
				$db->setQuery($query);
				$db->query();
			} // end if 
	} // end foreach 
} // end function
	
	
	
	/**
	 * Method to update the organisation id in the references
	 *
	 * @return (void)
	 * @access public
	 * @param (int)    $id_acct  The id of account found in the jcrmaccounts
	 * @param (string) $id_ref   The id of reference to be updated
	 */
	public function insertAccountId($id_acct,$id_ref){
	
			$db= JFactory::getDBO();
			$id_re=explode('-',$id_ref);
		    
			if($id_re[1]==1){
			$query='update `#__emundus_references` set id_account_1="'.$id_acct.'" where id='.$id_re[0];
			$db->setQuery($query);
			$db->query();
			} // end if
			if($id_re[1]==2){
			$query='update `#__emundus_references` set id_account_2="'.$id_acct.'" where id='.$id_re[0];
			$db->setQuery($query);
			$db->query();
			} // end if
			if($id_re[1]==3){
			$query='update `#__emundus_references` set id_account_3="'.$id_acct.'" where id='.$id_re[0];
			$db->setQuery($query);
			$db->query();
			}  // end if
	} // end function
	
	
	
	
	/**
	 * Method to update the contact id in the references
	 *
	 * @return (void)
	 * @access public
	 * @param (int)    $id_cont   The id of contact found in the jcrmcontacts
	 * @param (string) $id_ref   The id of reference to be updated
	 *
	 */
	public function insertContId($id_cont,$id_ref){
	
			$db= JFactory::getDBO();
			$id_re=explode('-',$id_ref);
			
			//echo $id_cont."<br>".$id_ref;
			//print_r($id_re);
			if($id_re[1]==1){
			$query='update `#__emundus_references` set id_contact_1="'.$id_cont.'" where id='.$id_re[0];
			$db->setQuery($query);
			$db->query();
			} // end if
			if($id_re[1]==2){
			$query='update `#__emundus_references` set id_contact_2="'.$id_cont.'" where id='.$id_re[0];
			$db->setQuery($query);
			$db->query();
			} // end if
			if($id_re[1]==3){
			$query='update `#__emundus_references` set id_contact_3="'.$id_cont.'" where id='.$id_re[0];
			$db->setQuery($query);
			$db->query();
			}  // end if
			
	} // end function
	

	/**
	 * Gets the Pagination Object
	 * @return object JPagination
	 * @access public
	 */
	public function getPagination(){
        // Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	} // end function
	
	
	
	/**
	 * Gets the number of published references
	 * @return int 
	 * @access public
	 *
	 */
	function getTotal(){
 	
		if (empty($this->_total)) {
 	   
 	    $this->_total = Count($this->_data_contacts);	
 	}
 	return $this->_total;
  } // end function


  
} // end class
?>