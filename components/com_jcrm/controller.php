<?php
/**
 * com_jcrm default controller for addressbook
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * D¨¦cision Publique 
 *
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * jcrm Component Controller
 *
 * @package    Joomla
 * @subpackage Jcrm
 */
class JcrmController extends JController
{
	function __construct($config = array()){
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @return (void)
	 */
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'addressbook';
			JRequest::setVar('view', $default);
		}
		
		$user = $this->_user;
		if ($user->usertype == "Registered") {
			$checklist = $this->getView( 'checklist', 'html' );
			$checklist->setModel( $this->getModel( 'checklist'), true );
			$checklist->display();
		} else {
			parent::display();
		}
	} // end function
	
	
	/**
	 * Method to save the data of jcrmcontact
	 *
	 * @access	public
	 * @return (void)
	 */
	function save_contact(){
		// Get the id of reference
		$id_ref=JRequest::getVar('id_ref',null,'get');
		// Call the function of save contact in model
		$model= $this->getModel('addressbook');
		$model->save_contact();
		
		$db = JFactory::getDBO();
		// Get the id of jcrmcontact
		$id_cont=JRequest::getVar('id_cont');
		// get the max id of the jcrmcontact
		$query = "SELECT max(id) as id FROM `#__jcrm_contacts`";
		$db->setQuery( $query );
		$contact_id=$db->loadObject();
		
		if(!empty($id_ref)){
		$id_r=explode('-',$id_ref);
		// If is the first contact of reference
		if($id_r[1]==1){
		$query='update `#__emundus_references` set id_contact_1="'.$contact_id->id.'" where id='.$id_r[0];
		$db->setQuery($query);
		$db->query();
		} // END IF
		// If is the second contact of reference
		if($id_r[1]==2){
		$query='update `#__emundus_references` set id_contact_2="'.$contact_id->id.'" where id='.$id_r[0];
		$db->setQuery($query);
		$db->query();
		} // END IF
		// If is the third contact of reference
		if($id_r[1]==3){
		$query='update `#__emundus_references` set id_contact_3="'.$contact_id->id.'" where id='.$id_r[0];
		$db->setQuery($query);
		$db->query();
		} // END IF
		
        } // END IF	
	} // end function
	
	
	
	/**
	 * Method to save the data of jcrmaccount
	 *
	 * @access	public
	 * @return (void)
	 */
	function save_account(){
		
		
		// get the id of reference
		$id_ref=JRequest::getVar("id_ref",null,"get");
		// call the function of save account in model
		$model= $this->getModel('addressbook');
		$model->save_account();
		// get the max id of jcrmaccount
		$db = JFactory::getDBO();
		$query = "SELECT max(id) as id FROM `#__jcrm_accounts`";
		$db->setQuery( $query );
		$account_id=$db->loadObject();
		if(!empty($id_ref)){
		// explode the id of reference to a array
		$id_f=explode("-",$id_ref);
		// If is the first account in reference
		if($id_f[1]==1){
		$query='update `#__emundus_references` set id_account_1="'.$account_id->id.'" where id='.$id_f[0];
		$db->setQuery($query);
		$db->query();
		} // END IF
		// If is the second account in reference
		if($id_f[1]==2){
		$query='update `#__emundus_references` set id_account_2="'.$account_id->id.'" where id='.$id_f[0];
		$db->setQuery($query);
		$db->query();
		} // END IF
		// If is the third account in reference
		if($id_f[1]==3){
		$query='update `#__emundus_references` set id_account_3="'.$account_id->id.'" where id='.$id_f[0];
		$db->setQuery($query);
		$db->query();
		} // END IF
		} // END IF
		
	} // end function
	
	
	/**
	 * Method to delete the data of jcrmaccount
	 *
	 * @access	public
	 * @return (void)
	 */
	function delete_account(){
		// get the id selected of jcrmaccount and the number of page
		$id = JRequest::getVar('id_acct', null, 'GET', 'INT', JREQUEST_NOTRIM);
		$id_ac = JRequest::getVar('id_ac', null, 'GET', 'INT', JREQUEST_NOTRIM);
		$limit= JRequest::getVar('limitstart', null, 'GET', 'INT', JREQUEST_NOTRIM);
		// call the function of delete account in model
	    $model=$this->getModel('addressbook');
		$model->delete_account($id);
		$url='index.php?option=com_jcrm&view=addressbook&id_acct='.$id_ac.'&limitstart='.$limit.'&Itemid=982#label_accountslist';
		// set a redirection of the site and give a message to the user
		$this->setRedirect($url, JText::_('ACCOUNT_DELETE'), 'message');
	} // end function
	
	
	
	/**
	 * Method to delete the data of jcrmcontact
	 *
	 * @access	public
	 * @return (void)
	 */
	function delete_contact(){
		// get the id selected of contacts and the number of page
		$id = JRequest::getVar('id_cont', null, 'GET', 'INT', JREQUEST_NOTRIM);
		$limitstart= JRequest::getVar('limitstart',null,'GET','INT',JREQUEST_NOTRIM);
		// call the function of delete contact in model
	    $model=$this->getModel('addressbook');
		$model->delete_contact($id);
		$url="index.php?option=com_jcrm&view=addressbook&limitstart=".$limitstart;
		// set a redirection of the page and send a message to the user
		$this->setRedirect($url, JText::_('CONTACT_DELETE'), 'message');
	} // end function
	
	
	/**
	 * Method to delete the data of jcrmcontact in the jcrmaccount
	 *
	 * @access	public
	 * @return (void)
	 */
	function delete_contactchild(){
		// get the id selected of the contact to be deleted and the number of page
		$id = JRequest::getVar('id_cont', null, 'GET', 'INT', JREQUEST_NOTRIM);
		$limitstart = JRequest::getVar('limitstart', 0, 'GET', 'INT', JREQUEST_NOTRIM);
		// call the function of get the id of its account and the function of delete contact
	    $model=$this->getModel('addressbook');
		$id_acct = $model->getId_acct();
		$model->delete_contact($id);
		$url='index.php?option=com_jcrm&view=addressbook&id_acct='.$id_acct->account_id.'&limitstart='.$limitstart.'#label_accountslist';
		// set a redirection of the page and send a message to the user
		$this->setRedirect($url, JText::_('CONTACT_DELETE'), 'message');
	} // end function
	
	
	/**
	 * Method to define the format of jcrmaccounts list in the view
	 *
	 * @access	public
	 * @return (void)
	 */
	function listaccounts(){ 
	 // Get the value and field of search
     $search_value=JRequest::getVar('search_value');
	 $search_field=JRequest::getVar('search_field');
	 $info_field=JRequest::getVar('info_field');
	 // Call the function of get accounts in model
     $model = $this->getModel('contact_form');
     $accounts = $model->getAccounts($search_value);
	
	 // Define the format json in the view
	 $json = "{\"results\": [";
		$first = true;
		foreach ($accounts as $d) {
			if ($first) 
				$first = false;
			else
				$json .= ",";
			$name_acct=explode("->",$d[$search_field]);
			 $d[$search_field] = str_replace("\n",  "",  $d[$search_field]);
			$d[$search_field] = str_replace("\r",  "",  $d[$search_field]);
			$json .= "{\"id\": \"".$d['id']."\", \"value\": \"".$d[$search_field]."\", \"info\": \"".$d[$info_field]."\"}";
		} // end foreach
		$json .= "]}";
		echo $json;
	} // end function
	
	
	/**
	 * Method to define the format of countries list in the view
	 *
	 * @access	public
	 * @return (void)
	 */
	function listcountries(){ 
	// get the value of field to be searched
	 $search_field=JRequest::getVar('search_field');
	 // call the function of get list of countries in the model
     $model = $this->getModel('organisation_form');
     $country = $model->getCountry();
	 // define the format of json
	 $json = "{\"results\": [";
		$first = true;
		foreach ($country as $c) {
			if ($first) 
				$first = false;
			else
				$json .= ",";
			
			$json .= "{\"id\": \"".$c['id']."\", \"value\": \"".$c[$search_field]."\"}";
		} // end foreach
		$json .= "]}";
		echo $json;
	} // end function
	
	
	
	/**
	 * Method to restore the data of the accounts deleted and the contacts deleted
	 *
	 * @access	public
	 * @return (void)
	 */
    function restore(){
	// Get all the id of accounts deleted and contacts deleted
	$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
	$dels = JRequest::getVar( 'del', array(0), 'post', 'array' );
	 
	// Call the function of restore accounts and the function to restore contacts
	$model=$this->getModel('jcrmdeleted');
		if(count($cids)){$model->restore_acct($cids);}
		if(count($dels)){$model->restore_cont($dels);}
		$url='index.php?option=com_jcrm&view=jcrmdeleted';
		// set a redirection to the page and send a message to the user
		$this->setRedirect($url, JText::_('DATAS_RESTORE'), 'message');
	} // end function
	
	
	
    /**
	 * Method to delete the data of the accounts selected and the contacts selected
	 *
	 * @access	public
	 * @return (void)
	 */
	function delete(){
	   // get the all the id of accounts and contacts to be deleted
	   $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
	   $dels = JRequest::getVar( 'del', array(0), 'post', 'array' );
	
	   // Call the function of delete account and delete contact in the model
	   $model = $this->getModel('jcrmdeleted');
	    if(count($cids)){ $model->deleteAcct($cids);}
		if(count($dels)){ $model->deleteCont($dels);}
		// set a redirection of page and send a message to the user
		$msg = JText::_( 'DATAS_DELETED' );
		$this->setRedirect( 'index.php?option=com_jcrm&view=jcrmdeleted', $msg );
	} // end function
	
	
	/**
	 * Method to define the format the jcrmcontactslist in the view
	 *
	 * @access	public
	 * @return (void)
	 */
    function getContacts(){
		// Get the id of account in the url
		$id_acct=JRequest::getVar('id_acct',null);
		// call the function to get the data of account
		$model=$this->getModel('addressbook');
		$return=$model->getContacts($id_acct);
		$return2=$model->getChildAccount($id_acct);
		if(!empty($return)){
		$list='<ul>';
		foreach($return as $dataC){
		 $link_edit="index.php?option=com_jcrm&view=contact_form&id_cont=".$dataC->id."&tmpl=component&Itemid=982";
		 // define the format in the view
		 $list.='<li><a href="#label_accountslist" onclick="delete_contact('.$dataC->id.')"><img src="media/com_jcrm/images/delete_user.png" title="'.JText::_('DELETE_CONTACT').'"/></a>
		 <a class="modal" target="_self" rel="{handler:\'iframe\', size: {x: window.innerWidth-innerWidth*0.5, y: window.innerHeight-innerHeight*0.1},onClose:function(){delayCheck()}}" href="'.$link_edit.'"><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDIT_CONTACT').'"/></a>
		 <a href="javascript:void(0)" onClick="window.open(\'mailto:'.$dataC->email.'\')"><img src="/media/com_jcrm/images/mailto.jpg" title="'.JText::_('SEND_EMAIL_TO').'" width="17" height="17" align="bottom"/></a>'.$dataC->first_name.' '.$dataC->last_name.'</li>';
		} // end foreach
		$list.="</ul>";
		echo $list;
		} // end if
		else echo "<p style='color:#FF0000'><b>".JText::_('NO_CONTACT')."</b></p>";
		if(!empty($return2)){
		$list='<ul>';
		foreach($return2 as $dataC){
		 $link_edit="index.php?option=com_jcrm&view=organisation_form&id_acct=".$dataC->id."&tmpl=component&Itemid=982";
		 // define the format in the view
		 $list.='<li><a href="#label_accountslist" onclick="delete_account('.$dataC->id.')"><img src="media/com_jcrm/images/delete_group.png" title="'.JText::_('DELETE_ACCOUNT').'"/></a>
		 <a class="modal" target="_self" rel="{handler:\'iframe\', size: {x: window.innerWidth-innerWidth*0.5, y: window.innerHeight-innerHeight*0.1},onClose:function(){delayCheck()}}" href="'.$link_edit.'"><img src="media/com_jcrm/images/edit.png" title="'.JText::_('EDIT_ACCOUNT').'"/></a>
		 '.$dataC->name.'</li>';
		} // end foreach
		$list.="</ul>";
		echo $list;
		} // end if
		else echo "<p style='color:#FF0000'><b>".JText::_('NO_ACCOUNT')."</b></p>";
		
    } // end function
	
	
	/**
	 * Method to get the name of the accounts whose id is maximum
	 *
	 * @access	public
	 * @return (void)
	 */
	function maxOrg(){
		//Calls the model'contact_form'
		$model=$this->getModel('contact_form');
		//Calls the function in the model
		$orgName=$model->getMaxorg();
		if(!empty($orgName)){
		echo $orgName;
		}	
	} // end function
	
	
	/**
	 * Method to get the maximum id of the accounts 
	 *
	 * @access	public
	 * @return (void)
	 */
	function maxOrgid(){
		//Calls the model'contact_form'
		$model=$this->getModel('contact_form');
		//Calls the function in the model
		$orgId=$model->getMaxorgid();
		if(!empty($orgId)){
		echo $orgId;
		}	
	} // end function
	
	
} // end class
?>
