<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewSeoandlink extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$config = $model->getConfig();

		$this->assign('salconfig',			$config);
	}
}