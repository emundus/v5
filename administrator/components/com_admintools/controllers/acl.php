<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsControllerAcl extends FOFController
{
	public function  __construct($config = array()) {
		parent::__construct($config);

		$this->modelName = 'acl';
	}

	public function execute($task) {
		if(!in_array($task, array('toggle','mingroup'))) $task = 'browse';
		parent::execute($task);
	}

	public function toggle()
	{
		$userID = $this->input->getInt('id', 0);
		$axo = $this->input->getCmd('axo','');

		$canDo = true;
		if(empty($userID) || empty($axo)) {
			$canDo = false;
		} else {
			$user = JFactory::getUser($userID);
			if(($user->gid < 23) || ($user->gid > 25)) $canDo = false;
		}

		if(!in_array($axo,array('utils','security','maintenance'))) {
			$canDo = false;
		}

		if(!$canDo) {
			$this->setRedirect('index.php?option=com_admintools&view=acl');
			return JError::raiseWarning(403, 'Invalid parameters');
			$this->redirect();
		}

		if(interface_exists('JModel')) {
			$model = JModelLegacy::getInstance('Acl','AdmintoolsModel');
		} else {
			$model = JModel::getInstance('Acl','AdmintoolsModel');
		}
		$permissions = array();
		$permissions['utils'] = $model->authorizeUser('utils',$userID) ? 1 : 0;
		$permissions['security'] = $model->authorizeUser('security',$userID) ? 1 : 0;
		$permissions['maintenance'] = $model->authorizeUser('maintenance',$userID) ? 1 : 0;

		$permissions[$axo] = $permissions[$axo] ? 0 : 1;

		$p = json_encode($permissions);

		$db = JFactory::getDBO();
		$sql = 'REPLACE INTO `#__admintools_acl` VALUES('.$db->Quote($userID).','.$db->Quote($p).')';
		$db->setQuery($sql);
		$db->execute();

		$this->setRedirect('index.php?option=com_admintools&view=acl');
		$this->redirect();
	}

	public function mingroup()
	{
		$group = $this->input->getString('minacl','');
		$model = $this->getThisModel();
		$model->setMinGroup($group);
		$this->setRedirect('index.php?option=com_admintools&view=acl');
		$this->redirect();
	}
}