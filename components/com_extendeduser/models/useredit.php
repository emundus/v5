<?php
/**
 * @version		$Id: user.php 14401 2010-01-26 14:10:00Z louis $
 * @package		Joomla
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * User Component UserEdit Model
 *
 * @package		Joomla
 * @subpackage	Useredit
 * @since 1.5
 */
class UserModelUseredit extends JModel
{
	/**
	 * User id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * User data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setId($id);
	}

	/**
	 * Method to set the weblink identifier
	 *
	 * @access	public
	 * @param	int Weblink identifier
	 */
	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a user
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the weblink data
		if ($this->_loadData()) {
			//do nothing
		}

		return $this->_data;
	}
	
	function getIfApplicationSend()
	{
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();		
		$db->setQuery('SELECT count(id) FROM #__emundus_declaration WHERE user = '.$user->id);
		$isAppSend = $db->loadResult();
		if($isAppSend>0) return true;
		else return false;
	}
	
	/**
	 * Method to get list of applicant's profile
	 *
	 * @access	public
	 * @return	array
	 * @since	1.5
	 */
	function getApplicantsProfiles()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label FROM #__emundus_setup_profiles esp WHERE esp.published=1 ORDER BY esp.label';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	/**
	 * Method to store the user data
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$user		= JFactory::getUser();
		$username	= $user->get('username');

		// Bind the form fields to the user table
		if (!$user->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
//die(print_r($user));
		// Store the web link table to the database
		if (!$user->save()) {//die(print_r($data));
			$this->setError( $user->getError() );
			return false;
		}

		$session =& JFactory::getSession();
		$session->set('user', $user);

		// check if username has been changed
		if ( $username != $user->get('username') )
		{
			$table = $this->getTable('session', 'JTable');
			$table->load($session->getId());
			$table->username = $user->get('username');
			$table->store();

		}
		$db =& JFactory::getDBO();
		$query = "UPDATE #__emundus_users SET profile=".$data['profile']." WHERE user_id=".$user->id;
		$db->setQuery($query);
		$db->query();
		
		$query = 'SELECT * FROM #__emundus_setup_profiles WHERE id='.$data['profile'];
		$db->setQuery($query);
		$row = $db->loadAssocList();

		$profile_label = $row[0]['label'];
		$menutype = $row[0]['menutype'];

		$user->profile = $data['profile'];
		$user->profile_label = $profile_label;
		$user->menutype = $menutype;
		
		$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
		if (!in_array($user->usertype, $allowed)) { die();
			$query = "DELETE FROM #__emundus_users_profiles WHERE user_id=".$user->id;
			$db->setQuery($query);
			$db->query();
		}
		
		if (isset($data['profile2']) && $data['profile2'] != '') {
			
			if($data['profile'] != $data['profile2'] && isset($data['profile2']) ) {
				$row =& JTable::getInstance('usersprofiles', 'Table');
				$row->set('user_id', $user->get('id'));
				$row->set('profile_id', $data['profile2']);
				if (!$row->store()) {
					return JError::raiseError(500, $row->getError() );
				}
				unset($row);
			} else {JFactory::getApplication()->enqueueMessage( JText::_('ERROR'), 'error' ); return false;}
		}

		if (isset($data['profile3']) && $data['profile3'] != '') {
			if($data['profile2'] != $data['profile3'] && $data['profile1'] != $data['profile3']) {
				$row =& JTable::getInstance('usersprofiles', 'Table');
				$row->set('user_id', $user->get('id'));
				$row->set('profile_id', $data['profile3']);
				if (!$row->store()) {
					return JError::raiseError(500, $row->getError() );
				}
				unset($row);
			} else {JFactory::getApplication()->enqueueMessage( JText::_('ERROR'), 'error' ); return false;}
		}
		
		return true;
	}

	/**
	 * Method to load user data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_data =& JFactory::getUser();
			return (boolean) $this->_data;
		}
		return true;
	}
	
	/** Method to get allowed profiles for user
	 *
	 * @access	private
	 * @return	array
	 * @since	1.5
	 */
	function getProfilesAllowed($user_id)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id FROM #__emundus_users_profiles AS eup';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	function getProfiles()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft 
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__core_acl_aro_groups caag on esp.acl_aro_groups=caag.id 
		ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
	
}
?>
