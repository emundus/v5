<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load framework base classes
JLoader::import('joomla.application.component.view');

class AdmintoolsViewAdminpw extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();

		$this->assign('username',		$this->input->get('username','', 'none', 2));
		$this->assign('password',		$this->input->get('password','', 'none', 2));
		$this->assign('adminLocked',	$model->isLocked());
	}
}