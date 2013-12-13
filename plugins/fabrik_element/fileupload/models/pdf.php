<?php
/**
 * Fileupload adaptor to render uploaded PDFs
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.fileupload
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Fileupload adaptor to render uploaded PDFs
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.fileupload
 * @since       3.0
*/

class PdfRender
{

	/**
	 * Render output
	 *
	 * @var  string
	 */
	public $output = '';

	/**
	 * File extension for PDF thumbnails
	 * @var  string
	 */
	protected $pdf_thumb_type = 'png';

	/**
	 * Is the element in a list view
	 * @var  bool
	 */
	protected $inTableView = false;

	/**
	 * When in form or detailed view, do we want to show the full image or thumbnail/link?
	 *
	 * @param   object  &$model   Element model
	 * @param   object  &$params  Element params
	 * @param   string  $file     Element's data
	 *
	 * @return bool
	 */

	private function getThumbnail(&$model, &$params, $file)
	{
		if ($this->inTableView || ($params->get('make_thumbnail') == '1' && $params->get('fu_show_image') == 1))
		{
			if (!$params->get('make_thumbnail', false))
			{
				return false;
			}
			else
			{
				$thumb_url = $model->getStorage()->_getThumb($file);
				$thumb_file = $model->getStorage()->urlToPath($thumb_url);
				$thumb_url_info = pathinfo($thumb_url);
				if (JString::strtolower($thumb_url_info['extension'] == 'pdf'))
				{
					$thumb_url = $thumb_url_info['dirname'] . '/' . $thumb_url_info['filename'] . '.' . $this->pdf_thumb_type;
					$thumb_file_info = pathinfo($thumb_file);
					$thumb_file = $thumb_file_info['dirname'] . '/' . $thumb_file_info['filename'] . '.' . $this->pdf_thumb_type;
				}
				if ($model->getStorage()->exists($thumb_file))
				{
					return $thumb_url;
				}
				else
				{
					// If file specific thumb doesn't exist, try the generic per-type image in media folder
					$thumb_file = COM_FABRIK_BASE . 'media/com_fabrik/images/pdf.png';
					if (JFile::exists($thumb_file))
					{
						return $thumb_file;
					}
					else
					{
						// Nope, nothing we can use as a thumb
						return false;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Render PDF in the list view
	 *
	 * @param   object  &$model   Element model
	 * @param   object  &$params  Element params
	 * @param   string  $file     Row data for this element
	 * @param   object  $thisRow  All row's data
	 *
	 * @return  void
	 */

	public function renderListData(&$model, &$params, $file, $thisRow)
	{
		$this->inTableView = true;
		$this->render($model, $params, $file);
	}

	/**
	 * Render PDF in the form view
	 *
	 * @param   object  &$model   Element model
	 * @param   object  &$params  Element params
	 * @param   string  $file     Row data for this element
	 *
	 * @return  void
	 */

	public function render(&$model, &$params, $file)
	{
		jimport('joomla.filesystem.file');
		$filename = basename($file);
		$filename = strip_tags($filename);
		$ext = JFile::getExt($filename);

		if (!strstr($file, 'http://') && !strstr($file, 'https://'))
		{
			// $$$rob only add in livesite if we dont already have a full url (eg from amazons3)
			// $$$ hugh trim / or \ off the start of $file
			$file = JString::ltrim($file, '/\\');
			$file = COM_FABRIK_LIVESITE . $file;
		}
		$file = str_replace("\\", "/", $file);
		$file = $model->storage->preRenderPath($file);
		$this->output = '<a class="download-archive fabrik-filetype-' . $ext . '" title="' . $filename . '" href="' . $file . '">';
		if ($thumb_file = $this->getThumbnail($model, $params, $file))
		{
			$filename = '<img src="' . $thumb_file . '" alt="' . $filename . '" />';
		}
		$this->output .= $filename . '</a>';
	}
}
