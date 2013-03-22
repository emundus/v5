<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewDbchcol extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$this->setLayout('choose');
	}
}