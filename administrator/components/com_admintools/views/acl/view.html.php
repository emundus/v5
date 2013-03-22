<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewAcl extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		// Get the users from manager and above
		if(interface_exists('JModel')) {
			$model = JModelLegacy::getInstance('Acl','AdmintoolsModel');
		} else {
			$model = JModel::getInstance('Acl','AdmintoolsModel');
		}
		$list = $model->getUserList();
		$this->assignRef('userlist', $list);
		$this->assign('minacl', $model->getMinGroup());
	}
}