<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Row selection checkbox
 *
 * @since 2.0
 */
class FOFFormHeaderRowselect extends FOFFormHeader
{

	protected function getHeader()
	{
		return '<input type="checkbox" name="checkall-toggle" value="" title="'
			. JText::_('JGLOBAL_CHECK_ALL')
			. '" onclick="Joomla.checkAll(this)" />';
	}

}