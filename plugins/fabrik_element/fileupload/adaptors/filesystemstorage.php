<?php
/**
 * Server File System Storage adaptor for Fabrik file upload element
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ROOT . '/plugins/fabrik_element/fileupload/adaptor.php';

/**
 * Server File System Storage adaptor for Fabrik file upload element
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class Filesystemstorage extends FabrikStorageAdaptor
{

	/**
	 * Does a file exist
	 *
	 * @param   string  $filepath  file path to test
	 *
	 * @return bool
	 */

	public function exists($filepath)
	{
		return JFile::exists($filepath);
	}

	/**
	 * Does a folder exist
	 *
	 * @param   string  $path  folder path to test
	 *
	 * @return bool
	 */

	public function folderExists($path)
	{
		return JFolder::exists($path);
	}

	/**
	 * Create empty index.html for security
	 *
	 * @param   strng  $path  path to folder
	 *
	 * @return bool success
	 */

	public function createIndexFile($path)
	{
		$index_file = $path . '/index.html';
		if (!$this->exists($index_file))
		{
			$content = JText::_('PLG_ELEMENT_FILEUPLOAD_INDEX_FILE_CONTENT');
			return JFile::write($index_file, $content);
		}
		return true;
	}

	/**
	 * Create a folder
	 *
	 * @param   string  $path  folder path
	 *
	 * @return bool
	 */

	public function createFolder($path)
	{
		if (JFolder::create($path))
		{
			return $this->createIndexFile($path);
		}
		return false;
	}

	/**
	 * Clean the file path
	 *
	 * @param   string  $path  path to clean
	 *
	 * @return  string  cleaned path
	 */

	public function clean($path)
	{
		return JPath::clean($path);
	}

	/**
	 * Clean a fle name
	 *
	 * @param   string  $filename       file name to clean
	 * @param   int     $repeatCounter  repeat group counter
	 *
	 * @return  string  cleaned name
	 */

	public function cleanName($filename, $repeatCounter)
	{
		// Replace any non-alnum chars (except _ and - and .) with _
		$filename_o = preg_replace('#[^a-zA-Z0-9_\-\.]#', '_', $filename);

		// $$$peamak: add random filename
		$params = $this->getParams();
		if ($params->get('random_filename') == 1)
		{
			$length = $params->get('length_random_filename');
			$key = "";
			$possible = "0123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRTVWXYZ";
			$i = 0;
			while ($i < $length)
			{
				$char = JString::substr($possible, mt_rand(0, JString::strlen($possible) - 1), 1);
				$key .= $char;
				$i++;
			}
			$file_e = JFile::getExt($filename_o);
			$file_f = preg_replace('/.' . $file_e . '$/', '', $filename_o);
			$filename = $file_f . '_' . $key . '.' . $file_e;
		}
		else
		{
			$filename = $filename_o;
		}
		return $filename;
	}

	/**
	 * Delete a file
	 *
	 * @param   string  $filepath  file to delete
	 *
	 * @return  void
	 */

	public function delete($filepath)
	{
		JFile::delete($filepath);
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param   string  $tmpFile   The name of the php (temporary) uploaded file
	 * @param   string  $filepath  The path (including filename) to move the uploaded file to
	 *
	 * @return  boolean True on success
	 */

	public function upload($tmpFile, $filepath)
	{
		$this->uploadedFilePath = $filepath;
		if (JFile::upload($tmpFile, $filepath))
		{
			return $this->createIndexFile(dirname($filepath));
		}
		return false;
	}

	/**
	 * Set a file's permissions
	 *
	 * @param   string  $filepath  file to set permissions for
	 *
	 * @return  string
	 */

	public function setPermissions($filepath)
	{
		return JPath::setPermissions($filepath);
	}

	/**
	 * Write a file
	 *
	 * @param   string  $file    file name
	 * @param   string  $buffer  the buffer to write
	 *
	 * @return  bool
	 */

	public function write($file, $buffer)
	{
		return JFile::write($file, $buffer);
	}

	/**
	 * Read a file
	 *
	 * @param   string  $filepath  file path
	 *
	 * @return  mixed  Returns file contents or boolean False if failed
	 */

	public function read($filepath)
	{
		return JFile::read($filepath);
	}

	/**
	 * Get the file's URL
	 *
	 * @param   string  $file  file path
	 *
	 * @return  string  URL
	 */

	public function getFileUrl($file)
	{
		$livesite = COM_FABRIK_LIVESITE;
		$livesite = rtrim($livesite, '/\\');
		$file = JString::ltrim($file, '/\\');
		return str_replace("\\", "/", $livesite . '/' . $file);
	}

	/**
	 * Get the thumbnail URL for the file given
	 *
	 * @param   string  $file  url
	 *
	 * @return string thumbnail url
	 */

	public function _getThumb($file)
	{
		return $this->_getSmallerFile($file, 'thumb');
	}

	/**
	 * Get the path (relative to site root?) to the smaller file
	 *
	 * @param   string  $file  large file path
	 * @param   string  $type  type (thumb or crop)
	 *
	 * @return  string
	 */

	protected function _getSmallerFile($file, $type)
	{

		$params = $this->getParams();
		$w = new FabrikWorker;

		// $$$ rob wasnt working when getting thumb path on upload
		$ulDir = JPath::clean($params->get('ul_directory'));
		$ulDir = str_replace("\\", "/", $ulDir);

		// If we're deleting a file, See http://fabrikar.com/forums/showthread.php?t=31715
		$file = str_replace("\\", "/", $file);

		// Replace things like $my->id may barf on other stuff
		$afile = str_replace(JURI::root(), '', $file);
		$afile = JString::ltrim($afile, "/");
		$ulDir = JString::ltrim($ulDir, "/");
		$ulDir = JString::rtrim($ulDir, "/");
		$ulDirbits = explode('/', $ulDir);
		$filebits = explode('/', $afile);

		$match = array();
		$replace = array();
		for ($i = 0; $i < count($filebits); $i++)
		{
			if (array_key_exists($i, $ulDirbits) && $filebits[$i] != $ulDirbits[$i])
			{
				$match[] = $ulDirbits[$i];
				$replace[] = $filebits[$i];
			}
		}

		$ulDir = str_replace($match, $replace, $ulDir);

		// $$$ rob wasnt working when getting thumb path on upload
		$typeDir = $type == 'thumb' ? $params->get('thumb_dir') : $params->get('fileupload_crop_dir');
		$thumbdir = str_replace($match, $replace, $typeDir);
		$ulDir = $w->parseMessageForPlaceHolder($ulDir);
		$thumbdir = $w->parseMessageForPlaceHolder($thumbdir);
		$file = str_replace($ulDir, $thumbdir, $file);
		$file = $w->parseMessageForPlaceHolder($file);
		$f = basename($file);
		$dir = dirname($file);
		$ext = JFile::getExt($f);

		// Remove extension
		$fclean = str_replace('.' . $ext, '', $f);
		if ($type == 'thumb')
		{
			// $f replaced by $fclean, $ext
			$file = $dir . '/' . $params->get('thumb_prefix') . $fclean . $params->get('thumb_suffix') . '.' . $ext;
		}
		else
		{
			$file = $dir . '/' . $f;
		}
		return $file;
	}

	/**
	 * Get the cropped file name
	 *
	 * @param   string  $file  large file name
	 *
	 * @return  string  cropped file name
	 */

	public function _getCropped($file)
	{
		return $this->_getSmallerFile($file, 'crop');
	}

	/**
	 * Convert a full url into a full server path
	 *
	 * @param   string  $url  URL
	 *
	 * @see /plugins/fabrik_element/fileupload/storageAdaptor#urlToPath($url)
	 *
	 * @return string  path
	 */

	public function urlToPath($url)
	{
		return str_replace(COM_FABRIK_LIVESITE, COM_FABRIK_BASE, $url);
	}

	/**
	 * Do a final transform on the path name
	 *
	 * @param   string  &$filepath  path to parse
	 *
	 * @return  void
	 */

	public function finalFilePathParse(&$filepath)
	{
		/* $$$ hugh - ooop!  Double Plus Ungood if JPATH_SITE is just /,
		 * which happens on some shared hosts which are chrooted (jailed)
		 * 'cos then we just strip out all the /'s in gthe path!
		 *$filepath = str_replace(JPATH_SITE, '', $filepath);
		 */
		$filepath = preg_replace('#^' . preg_quote(JPATH_SITE, '#') . '#', '', $filepath);
	}

	/**
	 * Get file info using getid3
	 *
	 * @param   string  $filepath  file path
	 *
	 * @return mixed array|false
	 */

	public function getFileInfo($filepath)
	{
		if (!$this->exists($filepath))
		{
			$filepath = $this->getFullPath($filepath);
		}
		if ($this->exists($filepath))
		{
			// $$$ hugh - turn of E_DEPRECATED to avoid warnings about eregi() in getid3
			// LOL!  E_DEPRECATED only available in 5.3.0+, pitches Notice in anything earlier.  :)
			if (version_compare(PHP_VERSION, '5.3.0') >= 0)
			{
				$current_level = error_reporting();
				error_reporting($current_level & ~E_DEPRECATED);
			}
			require_once COM_FABRIK_FRONTEND . '/libs/phpmimetypeclass/class.mime.php';
			$mime = new MIMETypes;
			$thisFileInfo['filesize'] = filesize($filepath);
			$thisFileInfo['filename'] = basename($filepath);
			$thisFileInfo['mime_type'] = $mime->getMimeType($filepath);
			return $thisFileInfo;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the complete folder path, including the server root
	 *
	 * @param   string  $filepath  the file path
	 *
	 * @return  string
	 */

	public function getFullPath($filepath)
	{
		if (!(preg_match('#^' . preg_quote(COM_FABRIK_BASE, '#') . '#', $filepath)))
		{
			return COM_FABRIK_BASE . '/' . $filepath;
		}
		return $filepath;
	}
}
