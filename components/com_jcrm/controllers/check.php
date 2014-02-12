<?php
/**
 * com_jcrm controller of check contacts
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Déision Publique 
 *
 */
 // Define it's a Joomla file
defined( '_JEXEC' ) or die( 'Restricted access' );

// import the component of controller in Joomla
jimport('joomla.application.component.controller');
/**
 * jcrm Component Controller
 *
 * @package    Joomla
 * @subpackage Jcrm
 */
class JcrmControllerCheck extends JController
{


  /**
	 * Method to display the view
	 *
	 * @access	public
	 * @return (void)
	 */
  function display($cachable = false, $urlparams = false) {
		// include the file of css
		JHTML::stylesheet( 'template.css','components/com_jcrm/views/check_contacts/tmpl/' );
		JHTML::_('behavior.modal');
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'addressbook';
			JRequest::setVar('view', $default);
		}
		
		$user = JFactory::getUser();
		if ($user->usertype == "Registered") {
			$checklist = $this->getView( 'checklist', 'html' );
			$checklist->setModel( $this->getModel( 'checklist'), true );
			$checklist->display();
		} else {
			parent::display();
		}
	} // end function
	
	
	
	/**
	 * Method to check all references selected
	 *
	 * @access	public
     * @return (void)
	 */
	function check(){
	     // Gets all id of contacts selected of references
	     $cids = JRequest::getVar( 'cid', array(0), 'get', 'array' );
		 // Gets the name of organisation selected
		 $name_org=JRequest::getVar('name_org',null,'get');
		 $name_org=str_replace('"', '', $name_org);
		 $name_org=str_replace("'", "&acute;", $name_org);
		 
		  $id_acct=JRequest::getVar('id_acct',null,'get');
		  $i_index=JRequest::getVar('i',null,'get');
		  if(!empty($id_acct)){
		    $this->insertAccountId($id_acct,$cids);  
		  }
		 // Call the function of check organisation in the model
		 $model = $this->getModel('check_contacts');
		 $return=$model->checkOrg($i_index,$cids);
		 $list="";
		 foreach ($cids as $id_contact){
		 // Explode every id of contact to an array
		 $id_cont=explode('-',$id_contact);
		 // If is the first contact in the reference
		 if($id_cont[1]==1){
		 // Gets the results from the model and define the format in view
		 if(isset($return->id_account_1) && $return->id_account_1==0){ 
		   $list.='<p class="label_org_notin">'.JText::_('ORGANISATION_NOT_IN_DB').'</p><p class="label_org_notin">'.JText::_('CIICK_TO_ADD').'&nbsp<a href="javascript:add_org('.$i_index.',\''.$name_org.'\',\''.$id_contact.'\', 1)"><img src="media/com_jcrm/images/add_sm_group.png" width="20" height="20" title="'.JText::_('ADD_NEW_ORGANISATION').'" /></a></p>';
			} // end if
		 } // end if
		 // If is the second contact in the reference
		 if($id_cont[1]==2){
		 if(isset($return->id_account_2) && $return->id_account_2==0){
		   $list.='<p class="label_org_notin">'.JText::_('ORGANISATION_NOT_IN_DB').'</p><p class="label_org_notin">'.JText::_('CIICK_TO_ADD').'&nbsp<a href="javascript:add_org('.$i_index.',\''.$name_org.'\',\''.$id_contact.'\', 2)"><img src="media/com_jcrm/images/add_sm_group.png" width="20" height="20" title="'.JText::_('ADD_NEW_ORGANISATION').'" /></a></p>';
			} // end if
		} // end if
		 // If is the third contact in the reference
		 if($id_cont[1]==3){
		 if(isset($return->id_account_3)&&$return->id_account_3==0){ 
		   $list.='<p class="label_org_notin">'.JText::_('ORGANISATION_NOT_IN_DB').'</p><p class="label_org_notin">'.JText::_('CIICK_TO_ADD').'&nbsp<a href="javascript:add_org('.$i_index.',\''.$name_org.'\',\''.$id_contact.'\', 3)"><img src="media/com_jcrm/images/add_sm_group.png" width="20" height="20" title="'.JText::_('ADD_NEW_ORGANISATION').'" /></a></p>';
			} // end if
		 } // end if
	    }  // end foreach
		 
		 echo $list;
		 
	} // end function
	


	/**
	 * Method to check contacts selected
	 *
	 * @access	public
	 * @return (void)
	 */
	function check_cont(){
	    // Gets all the params from the URL
		$cids = JRequest::getVar( 'cid', array(0), 'get', 'array' );
		$id_cont=JRequest::getVar( 'id_cont', null, 'get');
		$first_name=JRequest::getVar( 'fn', null, 'get');
		$last_name=JRequest::getVar( 'ln', null, 'get');
		$email=JRequest::getVar( 'email', null, 'get');
		$org_name=JRequest::getVar( 'on', null, 'get');
		$org_name=str_replace("'", "&quote;", $org_name);
		$org_name=JRequest::getVar( 'on', null, 'get');
		$org_name=str_replace('"', "", $org_name);
		$i_index=JRequest::getVar( 'i', null, 'get');
		if($id_cont!=null){
			$this->insertContId($id_cont,$cids);
		}
		// Call the function to chack all contacts in the model
		$model = $this->getModel('check_contacts');
		$model->checkContacts($i_index,$cids,$first_name,$last_name,$email,$org_name);
	} // end function
	
	
	/**
	 * Method to ignore the contacts selected
	 *
	 * @access	public
	 * @return (void)
	 */
	function ignore_cont(){
	    // Gets the id of contact selected from URL
		$id= JRequest::getVar('id_cont',null,'get');
		// Call the function to ignore the contact in model
		$model= $this->getModel('check_contacts');
		$model->ignoreContact($id);
		// set a redirection to the page and send a message to the user
		$message= JText::_("CONTACT_IGNORED");
		$url="index.php?option=com_jcrm&view=check_contacts";
		$this->setRedirect($url,$message);
		
	} // end function
	
	
	/**
	 * Method to update the organisation id in the references
	 *
	 * @access	public
	 * @return (void)
	 */
	public function insertAccountId($id_acct,$cid){
		
			// Call the function to update the id in model
			foreach($cid as $id_ref){
			$model=$this->getModel('check_contacts');
			$model->insertAccountId($id_acct,$id_ref);
			}
	
	} // end function
	
	
	/**
	 * Method to update the contact id in the references
	 *
	 * @access	public
	 * @return (void)
	 */
	function insertContId($id_cont,$cids){
				
			foreach($cids as $id_ref){
			// Call the function to update the id in model
			$model=$this->getModel('check_contacts');
			$model->insertContId($id_cont,$id_ref);
			}
	
	} // end function
	
	
	
	/**
	 * Method to export all the jcrmcontacts to different types
	 *
	 * @param array $reqids All the id of jcrmcontacts selected
	 * @access	public
	 * @return (void)
	 */
	function export_contacts($reqids = null) {
		// Gets the user
		$user = JFactory::getUser();
		// Set the users allowed
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		// if not allowed, set a redirection
		if (!in_array($user->usertype, $allowed)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator and Administrator can access this function.'), 'error');
			return;
		}
		// get the global configuration parameters
		global $mainframe;
		// Gets the file type to be exported
		$file_type=JRequest::getVar('type',null,'get');
		// If is the type of excel
		if($file_type=='xls'){
		// include the file of xls and call the function to export
		require_once('libraries/jcrm/xls_contacts.php');
		selected($reqids);
		}else 
		// If is the type of csv
		if($file_type=='csv'){
		// include the file of csv and call the function to export
		require_once('libraries/jcrm/csv_contacts.php');
		selected($reqids);
		}else
		// If is the type of text
		if($file_type=='txt'){
		// include the file of text and call the function to export
		require_once('libraries/jcrm/txt_contacts.php');
		/* $url="index.php?option=com_jcrm&view=addressbook";
		 $this->setRedirect($url, JText::_('DATAS_EXPORTED'), 'message'); */
		selected($reqids);
		 
		}else
		// If is the type of pdf
		if($file_type=='pdf'){
		// include the file of pdf and call the function to export
		require_once('libraries/jcrm/pdf_contacts.php');
		selected($reqids);
		$this->_helper->viewRenderer->setNoRender(true);
	    $this->_helper->layout->disableLayout(); 
	    $this->getResponse()->setHeader('Content-type','application/pdf');  
		

		
		
		}
	} // end function
	
	
	/**
	 * Method to export every jcrmcontact to the type of vcf 
	 *
	 * 
	 * @access	public
	 * @return (void)
	 */
	function export_vcf(){
	    // Gets the user
	    $user = JFactory::getUser();
	    // Set the users allowed
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor");
		// if not allowed, set a redirection
		if (!in_array($user->usertype, $allowed)) {
			$this->setRedirect('index.php', JText::_('Only Coordinator and Administrator can access this function.'), 'error');
			return;
		}
		// Gets the id of contact to be exported
		$id_cont=JRequest::getVar('id_cont','0','get');
		global $mainframe;
		// include the file of vcf and call the function to export
		require_once('libraries/jcrm/vcf_contacts.php');
		selected($id_cont);
		} // end function
	
	
} // end class
?>