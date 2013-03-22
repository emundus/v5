<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The tiny ACL system model
 */
class AdmintoolsModelAcl extends FOFModel
{
	private $viewACLmap = array(
		'acl'				=> 'security',
		'adminpw'			=> 'security',
		'adminuser'			=> 'maintenance',
		'badword'			=> 'security',
		'cleantmp'			=> 'utils',
		'cpanel'			=> 'utils',
		'dbchcol'			=> 'maintenance',
		'dbprefix'			=> 'maintenance',
		'dbtools'			=> 'maintenance',
		'eom'				=> 'utils',
		'fixperms'			=> 'utils',
		'fixpermsconfig'	=> 'utils',
		'geoblock'			=> 'security',
		'htmaker'			=> 'security',
		'ipautoban'			=> 'security',
		'ipbl'				=> 'security',
		'ipwl'				=> 'security',
		'jupdate'			=> 'utils',
		'log'				=> 'security',
		'masterpw'			=> 'security',
		'redirs'			=> 'utils',
		'scanalert'			=> 'security',
		'scans'				=> 'security',
		'seoandlink'		=> 'utils',
		'waf'				=> 'security',
		'wafconfig'			=> 'security',
		'wafexceptions'		=> 'security',
	);

	public function authorizeViewAccess($view = null, $user_id = null)
	{
		if(empty($view)) {
			$view = $this->input->getCmd('view','cpanel');
		}

		$view = FOFInflector::singularize($view);

		if(!array_key_exists($view, $this->viewACLmap)) {
			$axo = 'security';
		} else {
			$axo = $this->viewACLmap[$view];
		}

		// Joomla! 1.6 ACL
		$user = JFactory::getUser();
		if($user->authorise('core.admin')) {
			return true;
		}
		if (!$user->authorise('admintools.'.$axo, 'com_admintools')) {
			$option = $this->input->getCmd('option','com_foobar');
			$view = $this->input->getCmd('view','cpanel');
			if( ($option == 'com_admintools') && ($view == 'cpanel') ) {
				JFactory::getApplication()->redirect('index.php',JText::_('JERROR_ALERTNOAUTHOR'),'error');
			} else {
				JFactory::getApplication()->redirect('index.php?option=com_admintools',JText::_('JERROR_ALERTNOAUTHOR'),'error');
			}
		}
	}

	/**
	 * Public function to authorize a user's access to a specific Akeeba AXO.
	 * @param string $axo One of Akeeba Backup's AXOs (download, configuration, backup).
	 * @param int $user_id The user ID to control. Use null for current user.
	 */
	public function authorizeUser($axo, $user_id = null)
	{
		// Load the ACLs and cache them for future use
		static $acls = null;

		if(is_null($acls)) {
			$db = $this->getDBO();
			$query = $db->getQuery(true)
				->select(array('*'))
				->from($db->quoteName('#__admintools_acl'));
			$db->setQuery($query);
			$acls = $db->loadObjectList('user_id');
			if(empty($acls)) $acls = array();
		}

		// Get the user ID and the user object
		if(!is_null($user_id)) {
			$user_id = (int)$user_id;
		}

		if(empty($user_id)) {
			$user = JFactory::getUser();
			$user_id = $user->id;
		} else {
			$user = JFactory::getUser($user_id);
		}

		// Check minimum access group
		$minGroup = $this->getMinGroup();
		switch($minGroup)
		{
			case 'manager':
				$minGroup = 23;
				break;

			case 'administrator':
				$minGroup = 24;
				break;

			default:
				$minGroup = 25;
				break;
		}

		if($user->gid < $minGroup) return false;

		// Get the default (group) permissions
		$defaultPerms = $this->getDefaultPermissions($user->gid);

		// Get the user permissions, if any
		if(array_key_exists($user_id, $acls)) {
			$acl = $acls[$user_id];
		} else {
			$acl = null;
		}

		if(is_object($acl)) {
			$userPerms = json_decode($acl->permissions, true);
		} else {
			$userPerms = array();
		}

		// Find out the correct set of permissions (user permissions override default ones)
		$perms = array_merge($defaultPerms, $userPerms);

		// Return the control status of these permissions
		if(array_key_exists($axo, $perms)) {
			return $perms[$axo] == 1;
		} else {
			return true;
		}
	}


	/**
	 * Gets the default permissions for a Joomla! 1.5 user group
	 * @param int $gid The Group ID to test for
	 */
	public function getDefaultPermissions($gid)
	{
		$permissions = array(
			'utils'			=> 0,
			'security'		=> 0,
			'maintenance'	=> 0
		);

		switch($gid)
		{
			case 25:
				// Super administrator
				$permissions = array(
					'utils'			=> 1,
					'security'		=> 1,
					'maintenance'	=> 1
				);
				break;

			case 24:
				$permissions = array(
					'utils'			=> 1,
					'security'		=> 0,
					'maintenance'	=> 1
				);
				break;

			case 23:
				$permissions = array(
					'utils'			=> 1,
					'security'		=> 0,
					'maintenance'	=> 0
				);
				break;
		}

		return $permissions;
	}

	public function &getUserList()
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select(array(
				$db->quoteName('id'),
				$db->quoteName('username'),
				$db->quoteName('usertype'),
			))->from($db->quoteName('#__users'))
			->where($db->quoteName('gid').' >= '.$db->quote('23'))
			->where($db->quoteName('block').' = '.$db->quote('0'));
		$db->setQuery($query);
		$list = $db->loadAssocList();
		for($i=0; $i < count($list); $i++)
		{
			$list[$i]['utils'] = $this->authorizeUser('utils', $list[$i]['id']);
			$list[$i]['security'] = $this->authorizeUser('security', $list[$i]['id']);
			$list[$i]['maintenance'] = $this->authorizeUser('maintenance', $list[$i]['id']);
		}

		return $list;
	}

	public function getMinGroup()
	{
		if(interface_exists('JModel')) {
			$params = JModelLegacy::getInstance('Storage','AdmintoolsModel');
		} else {
			$params = JModel::getInstance('Storage','AdmintoolsModel');
		}
		$min_acl = $params->getValue('minimum_acl_group','super administrator');
		return $min_acl;
	}

	public function setMinGroup($group)
	{
		$group = strtolower($group);
		if(!in_array($group,array('super administrator','administrator','manager'))) {
			$group = 'super administrator';
		}
		if(interface_exists('JModel')) {
			$params = JModelLegacy::getInstance('Storage','AdmintoolsModel');
		} else {
			$params = JModel::getInstance('Storage','AdmintoolsModel');
		}
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$params->set('minimum_acl_group', $group);
		} else {
			$params->setValue('minimum_acl_group', $group);
		}
		$params->save();
	}
}