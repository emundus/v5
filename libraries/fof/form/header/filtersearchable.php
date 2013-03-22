<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Generic filter, text box entry with optional buttons
 *
 * @since 2.0
 */
class FOFFormHeaderFiltersearchable extends FOFFormHeaderFieldsearchable
{

	protected function getHeader()
	{
		return '';
	}

}