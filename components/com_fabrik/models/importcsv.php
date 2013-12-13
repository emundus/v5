<?php
/**
 * Import CSV class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport('joomla.application.component.modelform');

/**
 * Import CSV class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikFEModelImportcsv extends JModelForm
{

	/**
	 * Cleaned heading names
	 *
	 * @var array
	 */
	public $headings = null;

	/**
	 * CSV data
	 *
	 * @var array
	 */
	public $data = null;

	/**
	 * List of new headings found in csv file when importing
	 *
	 * @var array
	 */
	public $newHeadings = array();

	/**
	 * List of matched headings found in csv file when importing
	 *
	 * @var array
	 */
	public $matchedHeadings = array();

	/**
	 * List's join objects
	 *
	 * @var array
	 */
	public $joins = null;

	/**
	 * List model to import into
	 *
	 * @var object
	 */
	public $listModel = null;

	/**
	 * Number of records added
	 *
	 * @var int
	 */
	public $updatedCount = 0;

	/**
	 * CSV file name
	 *
	 * @var string
	 */
	protected $csvFile = null;

	/**
	 * Delimiter to split data by
	 *
	 * @var string
	 */
	protected $fieldDelimiter = null;

	/**
	 * Directory to which the csv file is imported
	 *
	 * @var string
	 */
	protected $baseDir = null;

	/**
	 * Import the csv file
	 *
	 * @return  boolean
	 */

	public function import()
	{
		$this->readCSV($this->getCSVFileName());
		$this->findExistingElements();
		$this->setSession();
		return true;
	}

	/**
	 * Gets the name of the csv file from the uploaded jform
	 *
	 * @return string csv file name
	 */

	protected function getCSVFileName()
	{
		if (is_null($this->csvFile))
		{
			$session = JFactory::getSession();
			if ($session->has('com_fabrik.csv.filename'))
			{
				$this->csvFile = $session->get('com_fabrik.csv.filename');
			}
			else
			{
				$this->csvFile = 'fabrik_csv_' . md5(uniqid());
				$session->set('com_fabrik.csv.filename', $this->csvFile);
			}
		}
		return $this->csvFile;
	}

	/**
	 * Loads the Joomla form for importing the csv file
	 *
	 * @param   array  $data      form data
	 * @param   bool   $loadData  load form data
	 *
	 * @return  object	form
	 */

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		JForm::addFormPath(COM_FABRIK_BASE . 'administrator/components/com_fabrik/models/forms');
		$form = $this->loadForm('com_fabrik.import', 'import', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$form->model = $this;
		return $form;
	}

	/**
	 * Checks uploaded file, and uploads it
	 *
	 * @return  true  csv file uploaded ok, false error (JError warning raised)
	 */

	public function checkUpload()
	{
		if (!(bool) ini_get('file_uploads'))
		{
			JError::raiseWarning(500, JText::_('COM_FABRIK_ERR_UPLOADS_DISABLED'));
			return false;
		}
		$userfile = JRequest::getVar('jform', null, 'files');
		if (!$userfile)
		{
			JError::raiseWarning(500, JText::_('COM_FABRIK_IMPORT_CSV_NO_FILE_SELECTED'));
			return false;
		}
		jimport('joomla.filesystem.file');

		$allowed = array('txt', 'csv', 'tsv');
		if (!in_array(JFile::getExt($userfile['name']['userfile']), $allowed))
		{
			JError::raiseError(500, 'File must be a csv file');
			return false;
		}

		$tmp_name = $this->getCSVFileName();
		$tmp_dir = $this->getBaseDir();

		$to = JPath::clean($tmp_dir . '/' . $tmp_name);

		$resultdir = JFile::upload($userfile['tmp_name']['userfile'], $to);
		if ($resultdir == false && !JFile::exists($to))
		{
			JError::raiseWarning(500, JText::_('Upload Error'));
			return false;
		}
		return true;
	}

	/**
	 * Get the field delimiter from post
	 * and set in session 'com_fabrik.csv.fielddelimiter' for later use
	 *
	 * @return  string	delimiter character
	 */

	protected function getFieldDelimiter()
	{
		$data = $this->getFormData();
		if (is_null($this->fieldDelimiter))
		{
			$this->fieldDelimiter = ',';
			$session = JFactory::getSession();
			if ($session->has('com_fabrik.csv.fielddelimiter'))
			{
				$this->fieldDelimiter = $session->get('com_fabrik.csv.fielddelimiter');
			}
			$tabDelimiter = JArrayHelper::getValue($data, 'tabdelimited');
			$this->fieldDelimiter = $tabDelimiter == 1 ? "\t" : JArrayHelper::getValue($data, 'field_delimiter', $this->fieldDelimiter);
			$session->set('com_fabrik.csv.fielddelimiter', $this->fieldDelimiter);
		}
		return $this->fieldDelimiter;
	}

	/**
	 * Get form data
	 *
	 * @return  array
	 */

	protected function getFormData()
	{
		$app = JFactory::getApplication();
		$filter = JFilterInput::getInstance();
		$post = $filter->clean($_POST, 'array');
		return $app->input->get('jform', $post, 'array');
	}

	/**
	 * Read the CSV file, store results in $this->headings and $this->data
	 *
	 * @param   string  $file  to read
	 *
	 * @return null
	 */

	public function readCSV($file)
	{
		$baseDir = $this->getBaseDir();
		$this->headings = array();
		$this->data = array();
		$data = $this->getFormData();
		$field_delimiter = $this->getFieldDelimiter();
		$text_delimiter = stripslashes(JArrayHelper::getValue($data, 'text_delimiter', '"'));
		$csv = new Csv_Bv($baseDir . '/' . $file, $field_delimiter, $text_delimiter, '\\');
		$csv->inPutFormat = JArrayHelper::getValue($data, 'inPutFormat', 'csv');

		// Will skip empty rows. TRUE by default. (Shown here for example only).
		$csv->SkipEmptyRows(true);

		// Remove leading and trailing \s and \t. TRUE by default.
		$csv->TrimFields(true);

		$model = $this->getlistModel();
		$tableParams = $model->getParams();
		$mode = $tableParams->get('csvfullname');

		while ($arr_data = $csv->NextLine())
		{
			if (empty($this->headings))
			{
				foreach ($arr_data as &$heading)
				{
					// Remove UFT8 Byte-Order-Mark if present
					if (substr($heading, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf))
					{
						$heading = JString::substr($heading, 3);
					}
					if ($mode != 2)
					{
						// $$$ rob replacing with this as per thread - http://fabrikar.com/forums/showthread.php?p=83304
						$heading = str_replace(' ', '_', $heading);
					}
				}
				if (!$this->getSelectKey())
				{
					// If no table loaded and the user asked to automatically add a key then put id at the beginning of the new headings
					$idheading = 'id';
					if (in_array($idheading, $arr_data))
					{
						$idheading .= rand(0, 9);
					}
					array_unshift($arr_data, $idheading);
				}
				$this->headings = $arr_data;
			}
			else
			{
				if (function_exists('iconv'))
				{
					foreach ($arr_data as &$d)
					{
						/**
						 * strip any none uft-8 characters from the import data
						 * if we don't do this then the site's session is destroyed and you are logged out
						 */
						$d = iconv("utf-8", "utf-8//IGNORE", $d);
					}
				}
				if (!$this->getSelectKey())
				{
					array_unshift($arr_data, '');
				}
				if (count($arr_data) == 1 && $arr_data[0] == '')
				{
					// CSV import from excel saved as unicode has blank record @ end
				}
				else
				{
					$this->data[] = $arr_data;
				}
			}
		}
		fclose($csv->mHandle);
		/*
		 * $$$ hugh - remove the temp file, but don't clear session
		 * $$$ rob 07/11/2011 - NO!!! as import in admin reads the file twice.
		 * once for getting the headings and a second time for importing/
		 * $this->removeCSVFile(false);
		 */
	}

	/**
	 * Return the first line of the imported data
	 *
	 * @return  array
	 */

	public function getSample()
	{
		return $this->data[0];
	}

	/**
	 * Possibly setting large data in the session is a bad idea
	 *
	 * @deprecated
	 *
	 * @return  void
	 */

	public function setSession()
	{
		$session = JFactory::getSession();
		$session->set('com_fabrik.csvdata', $this->data);
		$session->set('com_fabrik.matchedHeadings', $this->matchedHeadings);
	}

	/**
	 * Get the directory to which the csv file is imported
	 *
	 * @return  string	path
	 */

	protected function getBaseDir()
	{
		if (!isset($this->baseDir))
		{
			$config = JFactory::getConfig();
			$tmp_dir = $config->get('tmp_path');
			$this->baseDir = JPath::clean($tmp_dir);
		}
		return $this->baseDir;
	}

	/**
	 * Used by import csv cron plugin to override default base dir location
	 *
	 * @param   string  $dir  (folder path)
	 *
	 * @since	3.0.3.1
	 *
	 * @return  void
	 */

	public function setBaseDir($dir)
	{
		$this->baseDir = $dir;
	}

	/**
	 * Deletes the csv file and optionally removes its path from the session
	 *
	 * @param   bool  $clearSession  should we clear the session
	 *
	 * @return void
	 */

	public function removeCSVFile($clearSession = true)
	{
		$baseDir = $this->getBaseDir();
		$userfile_path = $baseDir . '/' . $this->getCSVFileName();
		if (JFile::exists($userfile_path))
		{
			JFile::delete($userfile_path);
		}
		if ($clearSession)
		{
			$this->clearSession();
		}
	}

	/**
	 * Clear session
	 *
	 * @return void
	 */

	public function clearSession()
	{
		$session = JFactory::getSession();
		$session->clear('com_fabrik.csv.filename');
		$session->clear('com_fabrik.csv.fielddelimiter');
	}

	/**
	 * Get the list model
	 *
	 * @return object table model
	 */

	public function getlistModel()
	{
		$app = JFactory::getApplication();
		if (!isset($this->listModel))
		{
			$this->listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
			$this->listModel->setId($app->input->getInt('listid'));
		}
		return $this->listModel;
	}

	/**
	 * Determine if the imported data has exisiting correlating elements
	 *
	 * @return  null
	 */

	public function findExistingElements()
	{
		$model = $this->getlistModel();
		$model->getFormGroupElementData();
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$pluginManager->getPlugInGroup('list');
		$aUsedElements = array();
		$formModel = $model->getFormModel();
		$tableParams = $model->getParams();
		$mode = $tableParams->get('csvfullname');
		$intKey = 0;
		$groups = $formModel->getGroupsHiarachy();

		$elementMap = array();

		// $$ hugh - adding $rawMap so we can tell prepareCSVData() if data is already raw
		$rawMap = array();
		foreach ($this->headings as $heading)
		{
			$found = false;
			foreach ($groups as $groupModel)
			{
				$elementModels = $groupModel->getMyElements();
				foreach ($elementModels as $elementModel)
				{
					$element = $elementModel->getElement();
					switch ($mode)
					{
						case 0:
							$name = $element->name;
							break;
						case 1:
							$name = $elementModel->getFullName(false, false, false);
							break;
						case 2:
							$name = $element->label;
							break;
					}
					$hkey = $elementModel->getFullName(false, false, false);
					if (JString::strtolower(trim($heading)) == JString::strtolower(trim($name)))
					{
						if (!array_key_exists($hkey, $this->matchedHeadings))
						{
							// Heading found in table
							$this->matchedHeadings[$hkey] = $element->name;
							$this->aUsedElements[strtolower($heading)] = $elementModel;
							$elementMap[$intKey] = clone ($elementModel);
							$rawMap[$intKey] = false;
							$found = true;

							// Break out of the group foreach
							break;
						}
					}
					$hkey .= '_raw';
					if (JString::strtolower(trim($heading)) == JString::strtolower(trim($name)) . '_raw')
					{
						if (!array_key_exists($hkey, $this->matchedHeadings))
						{
							// Heading found in table
							$this->matchedHeadings[$hkey] = $element->name . '_raw';
							$this->aUsedElements[strtolower($heading) . '_raw'] = $elementModel;
							$found = true;
							$elementMap[$intKey] = clone ($elementModel);
							$rawMap[$intKey] = true;

							// Break out of the group foreach
							break;
						}
					}
					// Joined element params
					if ($elementModel->isJoin())
					{
						$hkey = ($elementModel->getJoinModel()->getJoin()->table_join . '___params');
						if ($hkey === $heading)
						{
							if (!array_key_exists($hkey, $this->matchedHeadings))
							{
								$found = true;

								// Break out of the group foreach
								break;
							}
						}
					}
				}
			}
			// Moved after repeat group otherwise elements in second group are never found
			if (!$found && !in_array($heading, $this->newHeadings) && trim($heading) !== '')
			{
				$this->newHeadings[] = $heading;
			}
			$intKey++;
		}
		foreach ($elementMap as $key => $elementModel)
		{
			$element = $elementModel->getElement();
			$elementModel->prepareCSVData($this->data, $key, $rawMap[$key]);
		}
	}

	/**
	 * Work out which published elements are not included
	 *
	 * @return array element models whose defaults should be added to each of the imported
	 * data's array. Keyed on element name.
	 */

	protected function defaultsToAdd()
	{
		$model = $this->getListModel();
		$elements = $model->getElements();
		$defaultsToAdd = array();
		$elementKeys = array_keys($elements);
		foreach ($elementKeys as $e)
		{
			$e2 = str_replace('`', '', $e);
			if (!array_key_exists($e2, $this->matchedHeadings) && !array_key_exists($e2 . '_raw', $this->matchedHeadings))
			{
				$elementModel = $elements[$e];
				$defaultsToAdd[FabrikString::safeColNameToArrayKey($e)] = $elementModel;
			}
		}
		return $defaultsToAdd;
	}

	/**
	 * Insert data into a fabrik table
	 *
	 * @deprecated use insertData instead
	 *
	 * @return null
	 */

	public function makeTableFromCSV()
	{
		$this->insertData();
	}

	/**
	 * Insert data into a Fabrik list
	 *
	 * @return null
	 */

	public function insertData()
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$jform = $app->input->get('jform', array(), 'array');
		$dropData = (int) JArrayHelper::getValue($jform, 'drop_data', 0);
		$overWrite = (int) JArrayHelper::getValue($jform, 'overwrite', 0);
		$model = $this->getlistModel();
		$model->importingCSV = true;
		$item = $model->getTable();
		$formModel = $model->getFormModel();

		// $$$ rob 27/17/212 we need to reset the form as it was first generated before its elements were created.
		$formModel->reset();

		if ($dropData)
		{
			$model->truncate();
		}
		$item = $model->getTable();
		$tableParams = $model->getParams();
		$csvFullName = $tableParams->get('csvfullname', 0);

		$key = FabrikString::shortColName($item->db_primary_key);

		// Get a list of exisitng primary key vals
		$db = $model->getDb();
		$query = $db->getQuery(true);
		$query->select($item->db_primary_key)->from($item->db_table_name);
		$db->setQuery($query);
		$aExistingKeys = $db->loadColumn();

		$this->addedCount = 0;
		$updatedCount = 0;
		$joins = $this->getJoins();

		// $$$ rob we are no longer removing the element joins from $joins
		// so lets see if any of $joins are table joins.
		$tableJoinsFound = false;
		for ($x = 0; $x < count($joins); $x++)
		{
			if ((int) $joins[$x]->list_id !== 0)
			{
				$tableJoinsFound = true;
			}
		}
		$joindata = array();

		$defaultsToAdd = $this->defaultsToAdd();
		foreach ($this->data as $data)
		{
			$aRow = array();
			$pkVal = null;
			$i = 0;
			foreach ($this->matchedHeadings as $headingKey => $heading)
			{
				switch ($csvFullName)
				{
					case 0:
						break;
					case 1:
						$heading = array_pop(explode(".", $heading));
						break;
					case 2:
						break;
				}

				// Test _raw key and use that
				if (JString::substr($heading, JString::strlen($heading) - 4, JString::strlen($heading)) == '_raw')
				{
					$pktestHeading = JString::substr($heading, 0, JString::strlen($heading) - 4);
				}
				else
				{
					$pktestHeading = $heading;
				}
				/*
				 * $$$rob isset($pkVal) because: It could be that you have two elements (short names) with the
				 * same name (if trying to import joined data, in this case I'm
				 * presuming that the master table's pkval is the first one you come to
				 */

				if ($pktestHeading == $key && !isset($pkVal))
				{
					$pkVal = $data[$i];
				}
				$aRow[str_replace('.', '___', $headingKey)] = $data[$i];
				$i++;
			}

			// $$$ rob add in per row default values for missing elements
			foreach ($defaultsToAdd as $k => $elementModel)
			{
				/* Added check as defaultsToAdd ALSO contained element keys for those elements which
				 * are created from new csv columns, which previously didn't exist in the list
				 */
				if (!array_key_exists($k, $aRow))
				{
					$aRow[$k] = $elementModel->getDefaultValue($aRow);
				}
				if (!array_key_exists($k . '_raw', $aRow))
				{
					$aRow[$k . '_raw'] = $aRow[$k];
				}
			}
			$model->getFormGroupElementData();

			// Take any _raw values and replace their real elements with their data
			foreach ($aRow as $k => $val)
			{
				if (JString::substr($k, JString::strlen($k) - 4, JString::strlen($k)) == '_raw')
				{
					$noneraw = JString::substr($k, 0, JString::strlen($k) - 4);
					if (array_key_exists($noneraw, $aRow))
					{
						$aRow[$noneraw] = $val;
						unset($aRow[$k]);
					}
				}
			}
			if ($overWrite && in_array($pkVal, $aExistingKeys))
			{
				$formModel->_rowId = $pkVal;
				$updatedCount++;
			}
			else
			{
				$formModel->_rowId = 0;
				$this->addedCount++;
			}

			// $$$ rob - if raw and none raw or just raw found then insert the raw data
			// into the none raw key. Otherwise if just importing raw data no data stored
			foreach ($aRow as $k => $val)
			{
				if (JString::substr($k, JString::strlen($k) - 4, JString::strlen($k)) == '_raw')
				{
					$noneraw = JString::substr($k, 0, JString::strlen($k) - 4);
					$aRow[$noneraw] = $val;
					unset($aRow[$k]);
				}
			}

			if (!$tableJoinsFound)
			{
				$formModel->_formData = $aRow;
				if (!in_array(false, FabrikWorker::getPluginManager()->runPlugins('onImportCSVRow', $model, 'list')))
				{
					$formModel->processToDB();
				}
			}
			else
			{
				// Merge multi line csv into one entry & defer till we've passed everything
				$joindata = $this->_fakeJoinData($joindata, $aRow, $pkVal, $formModel);
			}
		}
		if ($tableJoinsFound)
		{
			$this->insertJoinedData($joindata);
		}
		$this->removeCSVFile();
		$this->updatedCount = $updatedCount;
	}

	/**
	 * Get the update message to show the user, # elements added, rows update and rows added
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */

	public function updateMessage()
	{
		$elementsCreated = $this->countElementsCreated();
		if ($elementsCreated == 0)
		{
			$msg = JText::sprintf('COM_FABRIK_CSV_ADDED_AND_UPDATED', $this->addedCount, $this->updatedCount);
		}
		else
		{
			$msg = JText::sprintf('COM_FABRIK_CSV_ADD_ELEMENTS_AND_RECORDS_AND_UPDATED', $elementsCreated, $this->addedCount, $this->updatedCount);
		}
		return $msg;
	}

	/**
	 * Calculate the number of elements that have been added during the import
	 *
	 * @since  3.0.8
	 *
	 * @return number
	 */
	protected function countElementsCreated()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$listid = $input->getInt('fabrik_list', $input->get('listid'));
		if ($listid == 0)
		{
			$elementsCreated = count($this->newHeadings);
		}
		else
		{
			$elementsCreated = 0;
			$newElements = $input->get('createElements', array(), 'array');
			foreach ($newElements as $k => $v)
			{
				if ($v == 1)
				{
					$elementsCreated ++;
				}
			}
		}
		return $elementsCreated;
	}

	/**
	 * Once we have itterated over all of the csv file and recreated
	 * the join data, we can finally allow the lists form to proces it
	 *
	 * @param   array  $joindata  data
	 *
	 * @return  void
	 */

	private function insertJoinedData($joindata)
	{
		// Ensure that the main row data doesn't contain and joined data (keep [join][x] though
		$model = $this->getListModel();
		$app = JFactory::getApplication();
		$table = $model->getTable();
		$dbname = $table->db_table_name;
		foreach ($joindata as &$j)
		{
			foreach ($j as $k => $v)
			{
				if (!is_array($v))
				{
					if (array_shift(explode('___', $k)) != $table->db_table_name)
					{
						unset($j[$k]);
					}
				}
			}
		}
		$formModel = $model->getFormModel();
		$groups = $formModel->getGroupsHiarachy();
		$groupids = array();
		foreach ($groups as $group)
		{
			if ($group->isJoin())
			{
				$groupids[$group->getGroup()->join_id] = $group->getGroup()->id;
			}
		}
		foreach ($joindata as $data)
		{
			// Reset the table's name back to the main table
			$table->db_table_name = $dbname;
			$fabrik_repeat_group = array();
			$js = JArrayHelper::getValue($data, 'join', array());
			foreach ($js as $jid => $jdata)
			{
				// Work out max num of repeated data to insert
				$counter = 0;
				foreach ($jdata as $v)
				{
					if (count($v) > $counter)
					{
						$counter = count($v);
					}

				}
				$groupid = $groupids[$jid];
				$fabrik_repeat_group[$groupid] = $counter;
			}
			// $$$ rob here we're setting up fabrik_repeat_group to allow the form to 'know' how many repeated records to insert.
			$app->input->set('fabrik_repeat_group', $fabrik_repeat_group);
			$formModel->_formData = $data;
			if (!in_array(false, FabrikWorker::getPluginManager()->runPlugins('onImportCSVRow', $model, 'list')))
			{
				$formModel->processToDB();
			}
		}
	}

	/**
	 * As each csv row is in a single line we need to fake the join data before
	 * sending it of to be processed by the form model
	 * Look at the table model and get all table joins
	 * then insert data into the row
	 * NOTE: will probably only work for a 1:1 join result
	 *
	 * @param   array   $joindata    Merged join data
	 * @param   array   $aRow        Row
	 * @param   mixed   $pkVal       Primary key value
	 * @param   object  &$formModel  Form model
	 *
	 * @return  array	updated join data
	 */

	private function _fakeJoinData($joindata, $aRow, $pkVal, &$formModel)
	{
		$origData = $aRow;
		$app = JFactory::getApplication();
		$overWrite = $app->input->getInt('overwrite', 0, 'post');
		$joins = $this->getJoins();
		$groups = $formModel->getGroups();
		if (!empty($joins))
		{

			// A new record that will need to be inserted
			if (!array_key_exists($pkVal, $joindata))
			{
				$joindata[$pkVal] = array();
			}
			foreach ($aRow as $k => $v)
			{
				if (!array_key_exists($k, $joindata[$pkVal]))
				{
					$joindata[$pkVal][$k] = $v;
				}
			}
			if (!array_key_exists('join', $joindata[$pkVal]))
			{
				$joindata[$pkVal]['join'] = array();
			}
			foreach ($joins as $join)
			{
				// Only iterate over table joins (exclude element joins)
				if ((int) $join->element_id != 0)
				{
					continue;
				}
				$repeat = $groups[$join->group_id]->canRepeat();
				$keys = $this->getJoinPkRecords($join);
				if ($overWrite && in_array($pkVal, $keys))
				{
					// Not sure 2nd test is right here
					$origData[$join->table_key] = $pkVal;
					$updatedCount++;
				}
				else
				{
					$origData[$join->table_join . '___' . $join->table_key] = 0;
					$this->addedCount++;
				}
				$origData[$join->table_join . '___' . $join->table_join_key] = $pkVal;
				foreach ($origData as $key => $val)
				{
					$t = array_shift(explode('___', $key));
					if ($t == $join->table_join)
					{
						if ($repeat)
						{
							$joindata[$pkVal]['join'][$join->id][$key][] = $val;
						}
						else
						{
							$joindata[$pkVal]['join'][$join->id][$key] = $val;
						}
					}
				}
			}
		}
		return $joindata;
	}

	/**
	 * Get Join Primary Key values
	 *
	 * @param   object  $join  join row
	 *
	 * @return  array
	 */

	private function getJoinPkRecords($join)
	{
		$model = $this->getlistModel();
		$formModel = $model->getFormModel();
		if (!isset($this->joinpkids))
		{
			$this->joinpkids = array();
		}
		if (!array_key_exists($join->id, $this->joinpkids))
		{
			$db = $model->getDb();
			$query = $db->getQuery(true);
			$query->select($join->table_key)->from($join->table_join);
			$db->setQuery($query);
			$this->joinpkids[$join->id] = $db->loadColumn();
		}
		return $this->joinpkids[$join->id];
	}

	/**
	 * Get list model joins
	 *
	 * @return  array	joins
	 */

	public function getJoins()
	{
		if (!isset($this->joins))
		{
			$model = $this->getlistModel();

			// Move the join table data into their own array space
			$this->joins = $model->getJoins();
		}
		return $this->joins;
	}

	/**
	 * Create an error message
	 *
	 * @return  string
	 */

	public function makeError()
	{
		$str = JText::_('COM_FABRIK_CSV_FIELDS_NOT_IN_TABLE');
		foreach ($this->newHeadings as $heading)
		{
			$str .= $heading . ', ';
		}
		return $str;
	}

	/**
	 * Get an array of headings that should be added as part of the  import
	 *
	 * @return array
	 */

	public function getNewHeadings()
	{
		return $this->newHeadings;
	}

	/**
	 * Determine if the chooselementtypes view should contain a column where
	 * the user selects the field to be the pk
	 *
	 * @return  bool	true if column shown
	 */

	public function getSelectKey()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		// $$$ rob 30/01/2012 - if in csvimport cron plugin then we have to return true here
		// otherwise a blank column is added to the import data meaniing overwrite date dunna workie
		if ($input->getBool('cron_csvimport'))
		{
			return true;
		}
		// $$$ rob 13/03/2012 - reimporting into exisiting list - should return true
		if ($input->getInt('listid') !== 0)
		{
			return true;
		}
		$model = $this->getlistModel();
		if (trim($model->getTable()->db_primary_key) !== '')
		{
			return false;
		}
		$post = $input->get('jform', array(), 'array');
		if (JArrayHelper::getValue($post, 'addkey', 0) == 1)
		{
			return false;
		}
		return true;
	}

	/**
	 * Get the csv files headings
	 *
	 * @return  array
	 */

	public function getHeadings()
	{
		return $this->headings;
	}
}

/**
 * This class will parse a csv file in either standard or MS Excel format.
 * Two methods are provided to either process a line at a time or return the whole csv file as an array.
 *
 * It can deal with:
 * - Line breaks within quoted fields
 * - Character seperator (usually a comma or semicolon) in quoted fields
 * - Can leave or remove leading and trailing \s or \t
 * - Can leave or skip empty rows.
 * - Windows and Unix line breaks dealt with automatically. Care must be taken with Macintosh format.
 *
 * Also, the escape character is automatically removed.
 *
 * NOTICE:
 * - Quote character can be escaped by itself or by using an escape character, within a quoted field (i.e. "" or \" will work)
 *
 * $csv = & new Csv_Bv('test.csv', ';', '"' , '\\');
 * $csv->SkipEmptyRows(TRUE); // Will skip empty rows. TRUE by default. (Shown here for example only).
 * $csv->TrimFields(TRUE); // Remove leading and trailing \s and \t. TRUE by default.
 *
 * while ($arr_data = $csv->NextLine()) {
 *
 *         echo "<br><br>Processing line ". $csv->RowCount() . "<br>";
 *         echo implode(' , ', $arr_data);
 *
 * }
 *
 * echo "<br><br>Number of returned rows: ".$csv->RowCount();
 * echo "<br><br>Number of skipped rows: ".$csv->SkippedRowCount();
 *
 * ----
 * OR using the csv2array function.
 * ----
 *
 * $csv = & new Csv_Bv('test.csv', ';', '"' , '\\');
 * $csv->SkipEmptyRows(TRUE); // Will skip empty rows. TRUE by default. (Shown here for example only).
 * $csv->TrimFields(TRUE); // Remove leading and trailing \s and \t. TRUE by default.
 *
 * $_arr = $csv->csv2Array();
 *
 * echo "<br><br>Number of returned rows: ".$csv->RowCount();
 * echo "<br><br>Number of skipped rows: ".$csv->SkippedRowCount();
 *
 *
 * WARNING:
 * - Macintosh line breaks need to be dealt with carefully. See the PHP help files for the function 'fgetcsv'
 *
 * The coding standards used in this file can be found here: http://www.dagbladet.no/development/phpcodingstandard/
 *
 *    All commets and suggestions are welcomed.
 *
 * SUPPORT: Visit http://vhd.com.au/forum/
 *
 * CHANGELOG:
 *
 * - Fixed skipping of last row if the last row did not have a new line. Thanks to Florian Bruch and Henry Flurry. (2006_05_15)
 * - Changed the class name to Csv_Bv for consistency. (2006_05_15)
 * - Fixed small problem where line breaks at the end of file returned a warning (2005_10_28)
 *
 * @version    Release: 1.2
 * @category   Joomla
 * @package    Fabrik
 * @author     Ben Vautier <classes@vhd.com.au>
 * @copyright  2006 Ben Vautier
 * @since      3.0
 *
 */

class Csv_Bv
{
	/**
	 * Seperator character
	 * @var char
	 */
	protected $mFldSeperator;

	/**
	 * Enclose character
	 * @var char
	 */
	protected $mFldEnclosure;

	/**
	 * Escape character
	 * @var char
	 */
	protected $mFldEscapor;

	/**
	 * Length of the largest row in bytes.Default is 4096
	 * @var int
	 */
	protected $mRowSize;

	/**
	 * Holds the file pointer
	 * @var resource
	 */
	public $mHandle;

	/**
	 * Counts the number of rows that have been returned
	 * @var int
	 */
	protected $mRowCount;

	/**
	 * Counts the number of empty rows that have been skipped
	 * @var int
	 */
	protected $mSkippedRowCount;

	/**
	 * Determines whether empty rows should be skipped or not.
	 * By default empty rows are returned.
	 * @var boolean
	 */
	protected $mSkipEmptyRows;

	/**
	 * Specifies whether the fields leading and trailing \s and \t should be removed
	 * By default it is TRUE.
	 * @var boolean
	 */
	protected $mTrimFields;

	/**
	 * $$$ rob 15/07/2011
	 *  'excel' or 'csv', if excel then convert 'UTF-16LE' to 'UTF-8' with iconv when reading in lines
	 * @var string
	 */
	public $inPutFormat = 'csv';

	/**
	 * Constructor
	 *
	 * Only used to initialise variables.
	 *
	 * @param   string  $file       file path
	 * @param   string  $seperator  Only one character is allowed (optional)
	 * @param   string  $enclose    Only one character is allowed (optional)
	 * @param   string  $escape     Only one character is allowed (optional)
	 */

	public function Csv_Bv($file, $seperator = ',', $enclose = '"', $escape = '')
	{

		$this->mFldSeperator = $seperator;
		$this->mFldEnclosure = $enclose;
		$this->mFldEscapor = $escape;

		$this->mSkipEmptyRows = true;
		$this->mTrimFields = true;
		$this->htmlentity = true;
		$this->mRowCount = 0;
		$this->mSkippedRowCount = 0;

		$this->mRowSize = 4096;

		// Open file
		$this->mHandle = @fopen($file, "r") or trigger_error('Unable to open csv file', E_USER_ERROR);
	}

	/**
	 * uft 8 decode
	 *
	 * @param   string  $string  decode strong
	 *
	 * @return unknown|mixed
	 */

	protected function charset_decode_utf_8($string)
	{
		/* Only do the slow convert if there are 8-bit characters */
		/* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
		if (!preg_match("/[\200-\237]/", $string) and !preg_match("/[\241-\377]/", $string))
		{
			return $string;
		}

		// Decode three byte unicode characters
		$pattern = "/([\340-\357])([\200-\277])([\200-\277])/e";
		$string = preg_replace($pattern, "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'", $string);

		// Decode two byte unicode characters
		$string = preg_replace("/([\300-\337])([\200-\277])/e", "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string);

		return $string;
	}

	/**
	 * csv::NextLine() returns an array of fields from the next csv line.
	 *
	 * The position of the file pointer is stored in PHP internals.
	 *
	 * Empty rows can be skipped
	 * Leading and trailing \s and \t can be removed from each field
	 *
	 * @return  array  of fields
	 */

	public function NextLine()
	{

		if (feof($this->mHandle))
		{
			return false;
		}

		$arr_row = fgetcsv($this->mHandle, $this->mRowSize, $this->mFldSeperator, $this->mFldEnclosure);

		$this->mRowCount++;

		// Skip empty rows if asked to
		if ($this->mSkipEmptyRows)
		{
			if ($arr_row[0] === '' && count($arr_row) === 1)
			{
				$this->mRowCount--;
				$this->mSkippedRowCount++;

				$arr_row = $this->NextLine();

				// This is to avoid a warning when empty lines are found at the bvery end of a file.
				if (!is_array($arr_row))
				{
					// This will only happen if we are at the end of a file.
					return false;
				}
			}
		}
		if (is_array($arr_row))
		{
			if ($this->inPutFormat == 'excel' || $this->inPutFormat == 'fabrikexcel')
			{
				$encFrom = $this->inPutFormat == 'fabrikexcel' ? 'UTF-16LE' : 'Windows-1252';

				foreach ($arr_row as $k => $v)
				{
					$arr_row[$k] = trim($arr_row[$k]);
					if ($arr_row[$k] !== '')
					{
						$arr_row[$k] = iconv($encFrom, 'UTF-8', $arr_row[$k] . "\0");
						$arr_row[$k] = str_replace('""', '"', $arr_row[$k]);
						$arr_row[$k] = preg_replace("/^\"(.*)\"$/sim", "$1", $arr_row[$k]);
					}
				}
			}
		}
		// Remove leading and trailing spaces \s and \t
		if ($this->mTrimFields && is_array($arr_row))
		{
			array_walk($arr_row, array($this, 'ArrayTrim'));
		}

		/**
		 * Remove escape character if it is not empty and different from the enclose character
		 * otherwise fgetcsv removes it automatically and we don't have to worry about it.
		 */
		if ($this->mFldEscapor !== '' && $this->mFldEscapor !== $this->mFldEnclosure && is_array($arr_row))
		{
			array_walk($arr_row, array($this, 'ArrayRemoveEscapor'));
		}

		// Remove leading and trailing spaces \s and \t
		if ($this->htmlentity && is_array($arr_row))
		{

			array_walk($arr_row, array($this, 'charset_decode_utf_8'));
		}

		return $arr_row;
	}

	/**
	 * csv::Csv2Array will return the whole csv file as 2D array
	 *
	 * @return  array
	 */

	public function Csv2Array()
	{
		$arr_csv = array();
		while ($arr_row = $this->NextLine())
		{
			$arr_csv[] = $arr_row;
		}
		return $arr_csv;
	}

	/**
	 * csv::ArrayTrim will remove \s and \t from an array
	 *
	 * It is called from array_walk.
	 *
	 * @param   string  &$item  string to trim
	 * @param   string  $key    not used
	 *
	 * @return  void
	 */

	protected Function ArrayTrim(&$item, $key)
	{
		// Space and tab
		$item = trim($item, " \t");
	}

	/**
	 * csv::ArrayRemoveEscapor will escape the enclose character
	 * It is called from array_walk.
	 *
	 * @param   string  &$item  string to trim
	 * @param   string  $key    not used
	 *
	 * @return  void
	 */

	protected function ArrayRemoveEscapor(&$item, $key)
	{
		$item = str_replace($this->mFldEscapor . $this->mFldEnclosure, $this->mFldEnclosure, $item);
	}

	/**
	 * Htmlenties a string
	 *
	 * @param   string  &$item  string to trim
	 * @param   string  $key    not used
	 *
	 * @return  void
	 */

	protected function htmlentity(&$item, $key)
	{
		$item = htmlentities($item);
	}

	/**
	 * csv::RowCount return the current row count
	 *
	 * @access public
	 * @return int
	 */
	public function RowCount()
	{
		return $this->mRowCount;
	}

	/**
	 * csv::RowCount return the current skipped row count
	 *
	 * @return int
	 */

	public function SkippedRowCount()
	{
		return $this->mSkippedRowCount;
	}

	/**
	 * csv::SkipEmptyRows, sets whether empty rows should be skipped or not
	 *
	 * @param   bool  $bool  skip empty rows
	 *
	 * @return void
	 */

	public function SkipEmptyRows($bool = true)
	{
		$this->mSkipEmptyRows = $bool;
	}

	/**
	 * csv::TrimFields, sets whether fields should have their \s and \t removed.
	 *
	 * @param   bool  $bool  set trim fields state
	 *
	 * @return  null
	 */

	public function TrimFields($bool = true)
	{
		$this->mTrimFields = $bool;
	}

}
