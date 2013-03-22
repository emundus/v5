<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Generic filter, drop-down based on fixed options
 *
 * @since 2.0
 */
class FOFFormHeaderFilterselectable extends FOFFormHeaderFieldselectable
{

	protected function getHeader()
	{
		return '';
	}

}