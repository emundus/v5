<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewDbprefix extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$this->assign('isDefaultPrefix',		$model->isDefaultPrefix());
		$this->assign('currentPrefix',			$model->getCurrentPrefix());
		$this->assign('newPrefix',				$model->getRandomPrefix(4));
		
		return true;
	}
}