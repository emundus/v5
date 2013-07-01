<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Joomla! 3 view renderer class
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFRenderJoomla3 extends FOFRenderStrapper
{

	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct()
	{
		$this->priority	 = 55;
		$this->enabled	 = FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge');
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	public function preRender($view, $task, $input, $config = array())
	{
		$format	 = $input->getCmd('format', 'html');

		if (empty($format))
		{
			$format	 = 'html';
		}

		if ($format != 'html')
		{
			return;
		}

		if (!FOFPlatform::getInstance()->isCli())
		{
			// Wrap output in a Joomla-versioned div
			$version = new JVersion;
			$version = str_replace('.', '', $version->RELEASE);
			echo "<div class=\"joomla-version-$version\">\n";
		}

		// Render the submenu and toolbar
		if ($input->getBool('render_toolbar', true))
		{
			$this->renderButtons($view, $task, $input, $config);
			$this->renderLinkbar($view, $task, $input, $config);
		}
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	public function postRender($view, $task, $input, $config = array())
	{
		$format = $input->getCmd('format', 'html');

		if ($format != 'html' || FOFPlatform::getInstance()->isCli())
		{
			return;
		}

		echo "</div>\n";
	}

}
