<?php
/**
 * Users Model for eMundus World Component
 * 
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Jonas Lerebours
 */
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' ); 
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');

class EmundusModelUsers extends JModel
{
	var $_total = null;
	var $_pagination = null;
	protected $data;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		global $option;

		$mainframe = JFactory::getApplication();

 
        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
 
        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
 		$filter_order           = $mainframe->getUserStateFromRequest(  $option.'filter_order', 'filter_order', 'lastname', 'cmd' );
        $filter_order_Dir       = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );		
		
		$schoolyears			= $mainframe->getUserStateFromRequest( $option.'schoolyears', 'schoolyears', $this->getCurrentCampaign() );
		$campaign				= $mainframe->getUserStateFromRequest( $option.'campaigns', 'campaigns', $this->getCurrentCampaignsID() );
		$final_grade			= $mainframe->getUserStateFromRequest( $option.'finalgrade', 'finalgrade', @$filts_details['finalgrade'] );
		$s						= $mainframe->getUserStateFromRequest( $option.'s', 's' );
		$evaluator_group			= $mainframe->getUserStateFromRequest( $option.'evaluator_group', 'evaluator_group', @$filts_details['evaluator_group'] );
		$profile_users					= $mainframe->getUserStateFromRequest( $option.'profile_users', 'profile_users', @$filts_details['profile_users'] );
		$spam_suspect			= $mainframe->getUserStateFromRequest( $option.'spam_suspect', 'spam_suspect');
		$newsletter				= $mainframe->getUserStateFromRequest( $option.'newsletter', 'newsletter');
		
		
        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
		
		$this->setState('schoolyears', $schoolyears);
		$this->setState('campaign', $campaign);
		$this->setState('final_grade', $final_grade);
		$this->setState('s', $s);
		$this->setState('evaluator_group', $evaluator_group);
		$this->setState('profile_users', $profile_users);
		$this->setState('spam_suspect', $spam_suspect);
		$this->setState('newsletter', $newsletter);

	}
	
	function _buildContentOrderBy()
	{
        global $option;

		$mainframe = JFactory::getApplication();
 
                $orderby = '';
                $filter_order     = $this->getState('filter_order');
                $filter_order_Dir = $this->getState('filter_order_Dir');
				
                $can_be_ordering = array ('user', 'id', 'lastname', 'email', 'profile', 'block', 'lastvisitDate', 'registerDate', 'newsletter');
                /* Error handling is never a bad thing*/
                if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
                        $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
                } else
					$orderby = ' ORDER BY e.profile, e.lastname';
 
                return $orderby;
	}
	
	function _buildQuery()
	{	
		$final_grade	= $this->getState('finalgrade');
		$s				= $this->getState('s');
		$campaigns		= $this->getState('campaign');
		$schoolyears	= $this->getState('schoolyears');
		$groupEval		= $this->getState('evaluator_group');
		$spam_suspect	= $this->getState('spam_suspect');
		$pid			= $this->getState('profile_users');
		$newsletter		= $this->getState('newsletter');
		
		$uid = JRequest::getVar('rowid', null, 'GET', 'none', 0);
		$edit = JRequest::getVar('edit', 0, 'GET', 'none', 0);
		$search = JRequest::getVar('s', null, 'POST', 'none', 0);
		$list_user="";
		
		if(!empty($schoolyears) && (empty($campaigns) || $campaigns[0]=='%') && $schoolyears[0]!='%'){
			$list_user="";
			$applicant_schoolyears = $this->getUserListWithSchoolyear($schoolyears);
			$i=0;
			$nb_element = count($applicant_schoolyears);
			if($nb_element==0){
				$list_user.="EMPTY";
			}else{
				foreach($applicant_schoolyears as $applicant){
					if(++$i === $nb_element){
						$list_user.=$applicant;
					}else if($applicant!=NULL){
						$list_user.=$applicant.", ";
					}
				}
			}
		}else if(!empty($campaigns) && $campaigns[0]!='%' && (empty($schoolyears) || $schoolyears[0]=='%') ){
			$list_user="";
			$applicant_campaigns = $this->getUserListWithCampaign($campaigns);
			$i=0;
			$nb_element = count($applicant_campaigns);
			if($nb_element==0){
				$list_user.="EMPTY";
			}else{
				foreach($applicant_campaigns as $applicant){
					if(++$i === $nb_element){
						$list_user.=$applicant;
					}else if($applicant!=NULL){
						$list_user.=$applicant.", ";
					}
				}
			}
		}else if(!empty($campaigns) && $campaigns[0]!='%' &&  !empty($schoolyears) && $schoolyears[0]!='%'){
			$applicant_schoolyears = $this->getUserListWithSchoolyear($schoolyears);
			$i=0;
			$list_user='';
			foreach($schoolyears as $schoolyear){
				foreach($campaigns as $campaign){
					$compare = $this->compareCampaignANDSchoolyear($campaign,$schoolyear);
					// var_dump($compare.$campaign);
					if($compare!=0){
						$applicant_campaigns = $this->getUserListWithCampaign($campaign);
						$nb_element = count($applicant_campaigns);
						foreach($applicant_campaigns as $applicant){
							$list_user.=$applicant.", ";
						}
					}
				}
			}
			if($list_user==''){
				$list_user='EMPTY';
			}else{
				$taille = strlen($list_user);
				$list_user=substr($list_user,0,$taille-2);
			}
		}
		
		if(!empty($groupEval)){
			$list_user="";
			$applicant_groupEval = $this->getUserListWithGroupsEval($groupEval);
			$i=0;
			$nb_element = count($applicant_groupEval);
			if($nb_element==0){
				$list_user.="EMPTY";
			}else{
				foreach($applicant_groupEval as $applicant){
					if(++$i === $nb_element){
						$list_user.=$applicant;
					}else if($applicant!=NULL){
						$list_user.=$applicant.", ";
					}
				}
			}
		}
		
		$query = 'SELECT u.id, e.lastname, e.firstname, u.name, u.email, u.username, u.block,  e.profile, up.profile_value as newsletter, u.registerDate, u.lastvisitDate, 
					epd.nationality, epd.gender, e.university_id,
					TO_DAYS(NOW()) - TO_DAYS(u.registerDate) as registred_for
					FROM #__users AS u 
					LEFT JOIN #__emundus_users AS e ON u.id = e.user_id 
					LEFT JOIN #__emundus_personal_detail AS epd ON u.id = epd.user
					LEFT JOIN #__user_profiles AS up ON ( u.id = up.user_id AND up.profile_key = "emundus_profile.newsletter")
					';
					
		if(isset($final_grade) && !empty($final_grade)) {
			$query .= 'LEFT JOIN #__emundus_final_grade AS efg ON u.id = efg.student_id ';
		}
		
		if($edit==1) $query.= 'WHERE u.id='.mysql_real_escape_string($uid);
		else {
			$and = false;
			if(isset($pid) && !empty($pid) && is_numeric($pid)) {
				$query.= 'WHERE e.profile = "'.mysql_real_escape_string($pid).'"';
				$and = true;
			}
			if(isset($final_grade) && !empty($final_grade)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= 'efg.Final_grade = "'.mysql_real_escape_string($final_grade).'"';
				$and = true;
			}
			if(isset($search) && !empty($search)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query .= '(e.lastname LIKE "%'.mysql_real_escape_string($search).'%" 
							OR e.firstname LIKE "%'.mysql_real_escape_string($search).'%" 
							OR u.email LIKE "%'.mysql_real_escape_string($search).'%" 
							OR e.schoolyear LIKE "%'.mysql_real_escape_string($search).'%" 
							OR u.username LIKE "%'.mysql_real_escape_string($search).'%" 
							OR u.id LIKE "%'.mysql_real_escape_string($search).'%")';
			}
			/*if(isset($schoolyears) &&  !empty($schoolyears)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query.= 'e.schoolyear="'.mysql_real_escape_string($schoolyears).'"';
			}*/
			if(isset($spam_suspect) &&  !empty($spam_suspect) && $spam_suspect == 1) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query .= 'u.lastvisitDate="0000-00-00 00:00:00" AND TO_DAYS(NOW()) - TO_DAYS(u.registerDate) > 7';
			}
			
			if(!empty($list_user)) {
				if($and) { 
					$query .= ' AND ';
				}else{ 
					$and = true; $query .='WHERE '; 
				}
				
				if($list_user=='EMPTY'){
					$query.= 'u.id IN (null) ';
				}else{
					$query.= 'u.id IN ( '.$list_user.' )';
				}
			}
			
			if(isset($newsletter) &&  !empty($newsletter)) {
				if($and) $query .= ' AND ';
				else { $and = true; $query .='WHERE '; }
				$query .= 'profile_value like "%'.$newsletter.'%"';
			}
		}
		// echo str_replace('#_','jos',$query).'<BR />';
		return $query;
	} 
	
	function getUsers()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
//echo str_replace ('#_', 'jos', $query);
		return $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
	} 

	function getProfiles(){
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		ORDER BY esp.id, caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
		
	function getEditProfiles(){
		$db = JFactory::getDBO();
		$current_user = JFactory::getUser();
		$current_group = 0;
		foreach ($current_user->groups as $group) {
			if ($group > $current_group) $current_group = $group;
		}
		$query ='SELECT id, label FROM jos_emundus_setup_profiles WHERE '.$current_group.' >= acl_aro_groups GROUP BY id';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	
	function getUsersProfiles(){
		$user = JFactory::getUser();
		$uid = JRequest::getVar('rowid', $user->id, 'get','int');
		$db = JFactory::getDBO();
		$query = 'SELECT eup.profile_id FROM #__emundus_users_profiles eup WHERE eup.user_id='.$uid;
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function getUniversities()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT c.id, c.title 
		FROM #__categories c 
		WHERE published=1 AND extension = "com_contact" AND alias != "bank" 
		ORDER BY c.title';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getGroups()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esg.id, esg.label  
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1 
		ORDER BY esg.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
	function getCampaigns()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT sc.id, cc.applicant_id, sc.start_date, sc.end_date, sc.label, sc.year
		FROM #__emundus_setup_campaigns AS sc 
		LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
		WHERE sc.published=1';
		//echo str_replace('#_','jos',$query);
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function getCampaignsPublished()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__emundus_setup_campaigns AS sc WHERE sc.published=1 ORDER BY sc.start_date DESC, sc.label ASC';
		//echo str_replace('#_','jos',$query);
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function getCampaignsCandidature($aid = 0)
	{
		$db = JFactory::getDBO();
		$uid = ($aid!=0)?$aid:JRequest::getVar('rowid', null, 'GET', 'none', 0);
		$query = 'SELECT * FROM #__emundus_campaign_candidature AS cc  WHERE applicant_id='.$uid;
		//echo str_replace('#_','jos',$query);
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getUserListWithSchoolyear($schoolyears)
	{
		$year = is_string($schoolyears)?$schoolyears:"'".implode("','", $schoolyears)."'";
		$db = JFactory::getDBO();
		$query = 'SELECT cc.applicant_id
		FROM #__emundus_campaign_candidature AS cc 
		LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
		WHERE sc.published=1 AND sc.year IN ('.$year.') ORDER BY sc.year DESC';
//echo str_replace('#_','jos',$query);
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getUserListWithCampaign($campaign)
	{
		/*$list_campaign ="";
		$i=0;
		$nb_element = count($campaign);
		foreach($campaign as $c){
			if(++$i === $nb_element){
				$list_campaign .= $c;
			}else{
				$list_campaign .= $c.", ";
			}
		}*/
		
		$db = JFactory::getDBO();
		if(!is_array($campaign)){
			$query = 'SELECT cc.applicant_id
			FROM #__emundus_campaign_candidature AS cc 
			LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
			WHERE sc.published=1 AND sc.id IN ('.$campaign.')';
		}else{
			$query = 'SELECT cc.applicant_id
			FROM #__emundus_campaign_candidature AS cc 
			LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
			WHERE sc.published=1 AND sc.id IN ('.implode(",", $campaign).')';
		}
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function compareCampaignANDSchoolyear($campaign,$schoolyear){
		$db = JFactory::getDBO();
		$query = 'SELECT COUNT(*)
		FROM #__emundus_setup_campaigns AS sc 
		WHERE id='.$campaign.' AND year="'.$schoolyear.'"';
		// echo str_replace('#_','jos',$query);
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	function getCurrentCampaign(){
		return EmundusHelperFilters::getCurrentCampaign();
	}

	function getCurrentCampaignsID(){
		return EmundusHelperFilters::getCurrentCampaignsID();
	}
	
	function getCurrentCampaigns()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT sc.id, sc;label
		FROM #__emundus_setup_campaigns AS sc
		WHERE sc.published=1 AND end_date > NOW()';
		$db->setQuery( $query );
		// echo str_replace('#_','jos',$query);
		return $db->loadResultArray();
	}
	
	
	function getNewsletter()
	{	
		$db = JFactory::getDBO();
		$query = 'SELECT user_id, profile_value
		FROM #__user_profiles
		WHERE profile_key = "emundus_profile.newsletter"';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getGroupEval()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT esg.id, eu.user_id, eu.firstname, eu.lastname, u.email, esg.label 
				FROM #__emundus_setup_groups as esg
				LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
				LEFT JOIN #__emundus_users as eu on eu.user_id=eg.user_id
				LEFT JOIN #__users as u on u.id=eu.user_id
				WHERE esg.published=1';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getGroupsEval()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT ege.id, ege.applicant_id, ege.user_id, ege.group_id, esg.label 
		FROM #__emundus_groups_eval as ege
		LEFT JOIN #__emundus_setup_groups as esg ON esg.id = ege.group_id 
		WHERE esg.published=1';
		$db->setQuery( $query );
		return $db->loadObjectList('applicant_id');
	}
	
	function getUserListWithGroupsEval($groups)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT eg.user_id
		FROM #__emundus_groups as eg
		LEFT JOIN #__emundus_setup_groups as esg ON esg.id=eg.group_id
		WHERE esg.published=1 AND eg.group_id='.$groups;
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getUsersGroups()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT eg.user_id, eg.group_id  
		FROM #__emundus_groups eg';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function getSchoolyears()
	{
		$db = JFactory::getDBO();
		$query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1 GROUP BY schoolyear';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);    
		}
		return $this->_total;
	}

	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

/**
	 * Method to get the registration form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		//die( JPATH_COMPONENT.DS.'forms'.DS.'registration.xml' );
		// Get the form.
		$form = JForm::getInstance('com_emundus.registration', JPATH_COMPONENT.DS.'models'.DS.'forms'.DS.'registration.xml', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		return $this->getData();
	}
	
	/**
	 * Method to get the registration form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for users plugins to extend the data.
	 *
	 * @return	mixed		Data object on success, false on failure.
	 * @since	1.6
	 */
	public function getData()
	{
		if ($this->data === null) {

			$this->data	= new stdClass();
			$app	= JFactory::getApplication();
			$params	= JComponentHelper::getParams('com_users');

			// Override the base user data with any data in the session.
			$temp = (array)$app->getUserState('com_users.registration.data', array());
			foreach ($temp as $k => $v) {
				$this->data->$k = $v;
			}

			// Get the groups the user should be added to after registration.
			$this->data->groups = array();

			// Get the default new user group, Registered if not specified.
			$system	= $params->get('new_usertype', 2);

			$this->data->groups[] = $system;

			// Unset the passwords.
			unset($this->data->password1);
			unset($this->data->password2);

			// Get the dispatcher and load the users plugins.
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('user');

			// Trigger the data preparation event.
			$results = $dispatcher->trigger('onContentPrepareData', array('com_users.registration', $this->data));

			// Check for errors encountered while preparing the data.
			if (count($results) && in_array(false, $results, true)) {
				$this->setError($dispatcher->getError());
				$this->data = false;
			}
		}

		return $this->data;
	}
	
	function adduser($user, $other_params){
		// add to jos_emundus_users; jos_users; jos_emundus_groups; jos_users_profiles; jos_users_profiles_history
		$mainframe 	= JFactory::getApplication();
		$db 		= JFactory::getDBO();
		$pathway 	= $mainframe->getPathway();
		$config		= JFactory::getConfig();
		$authorize	= JFactory::getACL();
		$document   = JFactory::getDocument();
		
		if ( !$user->save() ) {
		 	JFactory::getApplication()->enqueueMessage(JText::_('CAN_NOT_SAVE_USER').'<BR />'.$user->getError(), 'error');
		}else{			
			$firstname=$other_params['firstname'];
			$lastname=$other_params['lastname'];
			$profile=$other_params['profile'];
			$groups=$other_params['groups'];
			$univ_id=$other_params['univ_id'];
			
			if(empty($univ_id)){
				$query="INSERT INTO `#__emundus_users` VALUES ('',".$user->id.",'".date('Y-m-d H:i:s')."','".$firstname."','".$lastname."',".$profile.",'',0,'','','',0)";
				$db->setQuery($query);
				$db->Query() or die($db->getErrorMsg());
			}else{
				$query="INSERT INTO `#__emundus_users` VALUES ('',".$user->id.",'".date('Y-m-d H:i:s')."','".$firstname."','".$lastname."',".$profile.",'',0,'','','','".$univ_id."')";
				$db->setQuery($query);
				$db->Query() or die($db->getErrorMsg());
			}
			
			foreach($groups as $group){
				$query="INSERT INTO `#__emundus_groups` VALUES ('',".$user->id.",".$group.")";
				$db->setQuery($query);
				$db->Query() or die($db->getErrorMsg());
			}
			
			$query="INSERT INTO `#__emundus_users_profiles` VALUES ('','".date('Y-m-d H:i:s')."',".$user->id.",".$profile.",'','')";
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
			
			$query="INSERT INTO `#__emundus_users_profiles_history` VALUES ('','".date('Y-m-d H:i:s')."',".$user->id.",".$profile.",'profile')";
			$db->setQuery($query);
			$db->Query() or die($db->getErrorMsg());
			
			JFactory::getApplication()->enqueueMessage(JText::_('USER_SUCCESSFULLY_ADDED'), 'message');

			return $user->id;
		}
		
	}
	
	function found_usertype($acl_aro_groups){
		$db = JFactory::getDBO();
		$query="SELECT title FROM #__usergroups WHERE id=".$acl_aro_groups;
		$db->setQuery($query);
		return $db->loadResult();		
	}

	function getDefaultGroup($pid){
		$db = JFactory::getDBO();
		$query="SELECT acl_aro_groups FROM #__emundus_setup_profiles WHERE id=".$pid;
		$db->setQuery($query);
		return $db->loadResultArray();		
	}

  /**
   *
   * PLAIN LOGIN
   *
   */
  function plainLogin($credentials) {
    // Get the application object.
    $app = JFactory::getApplication();

    // Get the log in credentials.
 /*   $credentials = array();
    $credentials['username'] = $this->_user;
    $credentials['password'] = $this->_passw;*/
    
    $options = array();
    return $app->login($credentials, $options);
    
  }

  /**
   *
   * ENCRYPT LOGIN
   *
   */
  function encryptLogin($credentials) {
    // Get the application object.
    $app = JFactory::getApplication();
    
    $db =& JFactory::getDBO();
    $query = 'SELECT `id`, `username`, `password`'
      . ' FROM `#__users`'
      . ' WHERE username=' . $db->Quote( $credentials['username'] )
      . '   AND password=' . $db->Quote( $credentials['password'] )
      ;
    $db->setQuery( $query );
    $result = $db->loadObject();
    
    if($result) {
      JPluginHelper::importPlugin('user');
      
      $options = array();
      $options['action'] = 'core.login.site';
      
      $response['username'] = $result->username;
      $result = $app->triggerEvent('onUserLogin', array((array)$response, $options));
    }

  }

}
?>