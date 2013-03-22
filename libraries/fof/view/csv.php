<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');

/**
 * FrameworkOnFramework CSV View class
 *
 * FrameworkOnFramework is a set of classes whcih extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
class FOFViewCsv extends FOFViewHtml
{

	protected $csvHeader = true;
	protected $csvFilename = null;

	function __construct($config = array())
	{
		parent::__construct($config);

		if (array_key_exists('csv_header', $config))
		{
			$this->csvHeader = $config['csv_header'];
		}
		else
		{
			$this->csvHeader = $this->input->getBool('csv_header', true);
		}

		if (array_key_exists('csv_filename', $config))
		{
			$this->csvFilename = $config['csv_filename'];
		}
		else
		{
			$this->csvFilename = $this->input->getString('csv_filename', '');
		}

		if (empty($this->csvFilename))
		{
			$view = $this->input->getCmd('view', 'cpanel');
			$view = FOFInflector::pluralize($view);
			$this->csvFilename = strtolower($view);
		}
	}

	protected function onDisplay($tpl = null)
	{
		// Load the model
		$model = $this->getModel();

		$items = $model->getItemList();
		$this->assignRef('items', $items);

		$document = JFactory::getDocument();
		$document->setMimeEncoding('text/csv');
		JResponse::setHeader('Pragma', 'public');
		JResponse::setHeader('Expires', '0');
		JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		JResponse::setHeader('Cache-Control', 'public', false);
		JResponse::setHeader('Content-Description', 'File Transfer');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="' . $this->csvFilename . '"');

		if (is_null($tpl))
		{
			$tpl = 'csv';
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			JError::setErrorHandling(E_ALL, 'ignore');
		}

		$hasFailed = false;
		try
		{
			$result = $this->loadTemplate($tpl, true);
		}
		catch (Exception $e)
		{
			$hasFailed = true;
		}

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			if ($result instanceof Exception)
			{
				$hasFailed = true;
			}
			JError::setErrorHandling(E_WARNING, 'callback');
		}

		if ($hasFailed)
		{
			// Default CSV behaviour in case the template isn't there!
			if (empty($items))
				return;

			if ($this->csvHeader)
			{
				$item = array_pop($items);
				$keys = get_object_vars($item);
				$items[] = $item;
				reset($items);

				$csv = array();
				foreach ($keys as $k => $v)
				{
					$csv[] = '"' . str_replace('"', '""', $k) . '"';
				}
				echo implode(",", $csv) . "\r\n";
			}

			foreach ($items as $item)
			{
				$csv = array();
				$keys = get_object_vars($item);
				foreach ($item as $k => $v)
				{
					$csv[] = '"' . str_replace('"', '""', $v) . '"';
				}
				echo implode(",", $csv) . "\r\n";
			}
			return false;
		}
	}

}