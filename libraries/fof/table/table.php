<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Normally this shouldn't be required. Some PHP versions, however, seem to
 * require this. Why? No idea whatsoever. If I remove it, FOF crashes on some
 * hosts. Same PHP version on another host and no problem occurs. Any takers?
 */
if (class_exists('FOFTable', false))
{
	return;
}

/**
 * FrameworkOnFramework Table class. The Table is one part controller, one part
 * model and one part data adapter. It's supposed to handle operations for single
 * records.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class FOFTable extends JObject
{
	/**
	 * Cache array for instances
	 *
	 * @var    array
	 */
	private static $instances = array();

	/**
	 * Include paths for searching for FOFTable classes.
	 *
	 * @var    array
	 */
	private static $_includePaths = array();

	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 */
	protected $_tbl = '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 */
	protected $_tbl_key = '';

	/**
	 * JDatabaseDriver object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $_db;

	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 */
	protected $_trackAssets = false;

	/**
	 * Does the resource support joomla tags?
	 *
	 * @var    boolean
	 */
	protected $_has_tags = false;

	/**
	 * Tag helper
	 *
	 * @var    JHelperTags
	 */
	protected $_tagsHelper = null;

	/**
	 * The rules associated with this record.
	 *
	 * @var    JAccessRules  A JAccessRules object.
	 */
	protected $_rules;

	/**
	 * Indicator that the tables have been locked.
	 *
	 * @var    boolean
	 */
	protected $_locked = false;

	/**
	 * If this is set to true, it triggers automatically plugin events for
	 * table actions
	 *
	 * @var    boolean
	 */
	protected $_trigger_events = false;

	/**
	 * Table alias used in queries
	 *
	 * @var    string
	 */
	protected $_tableAlias = false;

	/**
	 * Array with alias for "special" columns such as ordering, hits etc etc
	 *
	 * @var    array
	 */
	protected $_columnAlias = array();

	/**
	 * If set to true, it enabled automatic checks on fields based on columns properties
	 *
	 * @var    boolean
	 */
	protected $_autoChecks = false;

	/**
	 * Array with fields that should be skipped by automatic checks
	 *
	 * @var    array
	 */
	protected $_skipChecks = array();

	/**
	 * Does the table actually exist? We need that to avoid PHP notices on
	 * table-less views.
	 *
	 * @var    boolean
	 */
	protected $_tableExists = true;

	/**
	 * The asset key for items in this table. It's usually something in the
	 * com_example.viewname format. They asset name will be this key appended
	 * with the item's ID, e.g. com_example.viewname.123
	 *
	 * @var type
	 */
	protected $_assetKey = '';

	/**
	 * The input data
	 *
	 * @var    FOFInput
	 */
	protected $input = null;

	/**
	 * Extended query including joins with other tables
	 *
	 * @var    JDatabaseQuery
	 */
	protected $_queryJoin = null;

	/**
	 * The prefix for the table class
	 *
	 * @var		string
	 */
	protected $_tablePrefix = '';

	/**
	 * The known fields for this table
	 *
	 * @var		array
	 */
	protected $knownFields = array();

	/**
	 * Returns a static object instance of a particular table type
	 *
	 * @param   string  $type    The table name
	 * @param   string  $prefix  The prefix of the table class
	 * @param   array   $config  Optional configuration variables
	 *
	 * @return FOFTable
	 */
	public static function &getAnInstance($type = null, $prefix = 'JTable', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		// Guess the component name
		if (!array_key_exists('input', $config))
		{
			$config['input'] = new FOFInput;
		}

		if ($config['input'] instanceof FOFInput)
		{
			$tmpInput = $config['input'];
		}
		else
		{
			$tmpInput = new FOFInput($config['input']);
		}

		$option = $tmpInput->getCmd('option', '');
		$tmpInput->set('option', $option);
		$config['input'] = $tmpInput;

		if (!in_array($prefix, array('Table', 'JTable')))
		{
			preg_match('/(.*)Table$/', $prefix, $m);
			$option = 'com_' . strtolower($m[1]);
		}

		if (array_key_exists('option', $config))
		{
			$option = $config['option'];
		}

		$config['option'] = $option;

		if (!array_key_exists('view', $config))
		{
			$config['view'] = $config['input']->getCmd('view', 'cpanel');
		}

		if (is_null($type))
		{
			if ($prefix == 'JTable')
			{
				$prefix = 'Table';
			}

			$type = $config['view'];
		}

		$type       = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		$configProvider = new FOFConfigProvider;
		$configProviderKey = $option . '.views.' . FOFInflector::singularize($type) . '.config.';

		if (!array_key_exists($tableClass, self::$instances))
		{
			if (!class_exists($tableClass))
			{
				$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($config['option']);

				$searchPaths = array(
					$componentPaths['main'] . '/tables',
					$componentPaths['admin'] . '/tables'
				);

				if (array_key_exists('tablepath', $config))
				{
					array_unshift($searchPaths, $config['tablepath']);
				}

				$altPath = $configProvider->get($configProviderKey . 'table_path', null);

				if ($altPath)
				{
					array_unshift($searchPaths, $componentPaths['admin'] . '/' . $altPath);
				}

				JLoader::import('joomla.filesystem.path');
				$path = JPath::find(
					$searchPaths, strtolower($type) . '.php'
				);

				if ($path)
				{
					require_once $path;
				}
			}

			if (!class_exists($tableClass))
			{
				$tableClass = 'FOFTable';
			}

			$component = str_replace('com_', '', $config['option']);
			$tbl_common = $component . '_';

			if (!array_key_exists('tbl', $config))
			{
				$config['tbl'] = strtolower('#__' . $tbl_common . strtolower(FOFInflector::pluralize($type)));
			}

			$altTbl = $configProvider->get($configProviderKey . 'tbl', null);

			if ($altTbl)
			{
				$config['tbl'] = $altTbl;
			}

			if (!array_key_exists('tbl_key', $config))
			{
				$keyName           = FOFInflector::singularize($type);
				$config['tbl_key'] = strtolower($tbl_common . $keyName . '_id');
			}

			$altTblKey = $configProvider->get($configProviderKey . 'tbl_key', null);

			if ($altTblKey)
			{
				$config['tbl_key'] = $altTblKey;
			}

			if (!array_key_exists('db', $config))
			{
				$config['db'] = JFactory::getDBO();
			}

			// Assign the correct table alias
			if (array_key_exists('table_alias', $config))
			{
				$table_alias = $config['table_alias'];
			}
			else
			{
				$configProviderTableAliasKey = $option . '.tables.' . FOFInflector::singularize($type) . '.tablealias';
				$table_alias = $configProvider->get($configProviderTableAliasKey, false	);
			}

			$instance = new $tableClass($config['tbl'], $config['tbl_key'], $config['db']);
			$instance->setInput($tmpInput);
			$instance->setTablePrefix($prefix);
			$instance->setTableAlias($table_alias);

			// Determine and set the asset key for this table
			$assetKey = 'com_' . $component . '.' . strtolower(FOFInflector::singularize($type));
			$assetKey = $configProvider->get($configProviderKey . 'asset_key', $assetKey);
			$instance->setAssetKey($assetKey);

			if (array_key_exists('trigger_events', $config))
			{
				$instance->setTriggerEvents($config['trigger_events']);
			}

			if (array_key_exists('has_tags', $config))
			{
				$instance->setHasTags($config['has_tags']);
			}

			$altHasTags = $configProvider->get($configProviderKey . 'has_tags', null);
			if ($altHasTags)
			{
				$instance->setHasTags($altHasTags);
			}

			$configProviderFieldmapKey = $option . '.tables.' . FOFInflector::singularize($type) . '.field';
			$aliases = $configProvider->get($configProviderFieldmapKey, $instance->_columnAlias);
			$instance->_columnAlias = array_merge($instance->_columnAlias, $aliases);

			self::$instances[$tableClass] = $instance;
		}

		return self::$instances[$tableClass];
	}

	/**
	 * Force an instance inside class cache. Setting arguments to null nukes all or part of the cache
	 *
	 * @param    string|null       $key        TableClass to replace. Set it to null to nuke the entire cache
	 * @param    FOFTable|null     $instance   Instance to replace. Set it to null to nuke $key instances
	 *
	 * @return   bool              Did I correctly switch the instance?
	 */
	public static function forceInstance($key = null, $instance = null)
	{
		if(is_null($key))
		{
			self::$instances = array();

			return true;
		}
		elseif($key && isset(self::$instances[$key]))
		{
			// I'm forcing an instance, but it's not a FOFTable, abort! abort!
			if(!$instance || ($instance && $instance instanceof FOFTable))
			{
				self::$instances[$key] = $instance;

				return true;
			}
		}

		return false;
	}

	/**
	 * Class Constructor.
	 *
	 * @param   string           $table  Name of the database table to model.
	 * @param   string           $key    Name of the primary key field in the table.
	 * @param   JDatabaseDriver  &$db    Database driver
	 */
	public function __construct($table, $key, &$db)
	{
		$this->_tbl     = $table;
		$this->_tbl_key = $key;
		$this->_db      = $db;

		// Initialise the table properties.

		if ($fields = $this->getTableFields())
		{
			// Do I have anything joined?
			$j_fields = $this->getQueryJoinFields();

			if ($j_fields)
			{
				$fields = array_merge($fields, $j_fields);
			}

			$this->setKnownFields(array_keys($fields), true);
			$this->reset();
		}
		else
		{
			$this->_tableExists = false;
		}

		// If we are tracking assets, make sure an access field exists and initially set the default.
		$asset_id_field	= $this->getColumnAlias('asset_id');
		$access_field	= $this->getColumnAlias('access');

		if (in_array($asset_id_field, $this->getKnownFields()))
		{
			JLoader::import('joomla.access.rules');
			$this->_trackAssets = true;
		}

		// If the acess property exists, set the default.
		if (in_array($access_field, $this->getKnownFields()))
		{
			$this->$access_field = (int) JFactory::getConfig()->get('access');
		}
	}

	/**
	 * Replace the entire known fields array
	 *
	 * @param   array    $fields      A simple array of known field names
	 * @param   boolean  $initialise  Should we initialise variables to null?
	 *
	 * @return  void
	 */
	public function setKnownFields($fields, $initialise = false)
	{
		$this->knownFields = $fields;

		if ($initialise)
		{
			foreach ($this->knownFields as $field)
			{
				$this->$field = null;
			}
		}
	}

	/**
	 * Get the known fields array
	 *
	 * @return  array
	 */
	public function getKnownFields()
	{
		return $this->knownFields;
	}

	/**
	 * Add a field to the known fields array
	 *
	 * @param   string   $field       The name of the field to add
	 * @param   boolean  $initialise  Should we initialise the variable to null?
	 *
	 * @return  void
	 */
	public function addKnownField($field, $initialise = false)
	{
		if (!in_array($field, $this->knownFields))
		{
			$this->knownFields[] = $field;

			if ($initialise)
			{
				$this->$field = null;
			}
		}
	}

	/**
	 * Remove a field from the known fields array
	 *
	 * @param   string  $field  The name of the field to remove
	 *
	 * @return  void
	 */
	public function removeKnownField($field)
	{
		if (in_array($field, $this->knownFields))
		{
			$pos = array_search($field, $this->knownFields);
			unset($this->knownFields[$pos]);
		}
	}


	/**
	 * Sets the events trigger switch state
	 *
	 * @param   boolean  $newState  The new state of the switch (what else could it be?)
	 *
	 * @return  void
	 */
	public function setTriggerEvents($newState = false)
	{
		$this->_trigger_events = $newState ? true : false;
	}

	/**
	 * Gets the events trigger switch state
	 *
	 * @return  boolean
	 */
	public function getTriggerEvents()
	{
		return $this->_trigger_events;
	}

	/**
	 * Gets the has tags switch state
	 *
	 * @return bool
	 */
	public function hasTags()
	{
		return $this->_has_tags;
	}

	/**
	 * Sets the has tags switch state
	 *
	 * @param   bool  $newState
	 */
	public function setHasTags($newState = false)
	{
		$this->_has_tags = false;

		// Tags are available only in 3.1+
		if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.1', 'ge'))
		{
			$this->_has_tags = $newState ? true : false;

			if ($this->_has_tags && !$this->_tagsHelper)
			{
				$this->_tagsHelper = new JHelperTags();
				$this->_tagsHelper->typeAlias = $this->_assetKey;
			}
		}
	}

	/**
	 * Set the class prefix
	 *
	 * @param string $prefix The prefix
	 */
	public function setTablePrefix($prefix)
	{
		$this->_tablePrefix = $prefix;
	}

	/**
	 * Sets fields to be skipped from automatic checks.
	 *
	 * @param   array/string  $skip  Fields to be skipped by automatic checks
	 *
	 * @return void
	 */
	public function setSkipChecks($skip)
	{
		$this->_skipChecks = (array) $skip;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the FOFTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		if (!$this->_tableExists)
		{
			$result = false;

            return $this->onAfterLoad($result);
		}

		if (empty($keys))
		{
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;

			if (isset($this->$keyName))
			{
				$keyValue = $this->$keyName;
			}
			else
			{
				$keyValue = null;
			}

			// If empty primary key there's is no need to load anything

			if (empty($keyValue))
			{
				$result = true;

				return $this->onAfterLoad($result);
			}

			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl . '.*');
		$query->from($this->_tbl);

		// Joined fields are ok, since I initialized them in the constructor
		$fields = $this->getKnownFields();

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.

			if (!in_array($field, $fields))
			{
				throw new UnexpectedValueException(sprintf('Missing field in table %s : %s.', $this->_tbl, $field));
			}

			// Add the search tuple to the query.
			$query->where($this->_db->qn($this->_tbl . '.' . $field) . ' = ' . $this->_db->q($value));
		}

		// Do I have any joined table?
		$j_query = $this->getQueryJoin();

		if ($j_query)
		{
			if ($j_query->select && $j_query->select->getElements())
			{
				$query->select($this->normalizeSelectFields($j_query->select->getElements(), true));
			}

			if ($j_query->join)
			{
				foreach ($j_query->join as $join)
				{
					$t = (string) $join;

					// Joomla doesn't provide any access to the "name" variable, so I have to work with strings...

					if (stripos($t, 'inner') !== false)
					{
						$query->innerJoin($join->getElements());
					}
					elseif (stripos($t, 'left') !== false)
					{
						$query->leftJoin($join->getElements());
					}
					elseif (stripos($t, 'right') !== false)
					{
						$query->rightJoin($join->getElements());
					}
					elseif (stripos($t, 'outer') !== false)
					{
						$query->outerJoin($join->getElements());
					}
				}
			}
		}

		$this->_db->setQuery($query);

		$row = $this->_db->loadAssoc();

		// Check that we have a result.
		if (empty($row))
		{
			$result = true;

			return $this->onAfterLoad($result);
		}

		// Bind the object with the row and return.
		$result = $this->bind($row);

		$this->onAfterLoad($result);

		return $result;
	}

	/**
	 * Based on fields properties (nullable column), checks if the field is required or not
	 *
	 * @return boolean
	 */
	public function check()
	{
		if (!$this->_autoChecks)
		{
			return true;
		}

		$fields = $this->getTableFields();

        // No fields? Why in the hell am I here?
        if(!$fields)
        {
            return false;
        }

        $result       = true;
        $known        = $this->getKnownFields();
        $skipFields[] = $this->_tbl_key;

        if(in_array($this->getColumnAlias('created_on'), $known))   $skipFields[] = $this->getColumnAlias('created_on');
        if(in_array($this->getColumnAlias('created_by'), $known))   $skipFields[] = $this->getColumnAlias('created_by');
        if(in_array($this->getColumnAlias('modified_on'), $known))  $skipFields[] = $this->getColumnAlias('modified_on');
        if(in_array($this->getColumnAlias('modified_by'), $known))  $skipFields[] = $this->getColumnAlias('modified_by');
        if(in_array($this->getColumnAlias('locked_by'), $known))    $skipFields[] = $this->getColumnAlias('locked_by');
        if(in_array($this->getColumnAlias('locked_on'), $known))    $skipFields[] = $this->getColumnAlias('locked_on');

        // Let's merge it with custom skips
        $skipFields = array_merge($skipFields, $this->_skipChecks);

		foreach ($fields as $field)
		{
			$fieldName = $field->Field;

			// Field is not nullable but it's null, set error

			if ($field->Null == 'NO' && $this->$fieldName == '' && !in_array($fieldName, $skipFields))
			{
				$text = str_replace('#__', 'COM_', $this->getTableName()) . '_ERR_' . $fieldName;
				$this->setError(JText::_(strtoupper($text)));
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return void
	 */
	public function reset()
	{
		if (!$this->onBeforeReset())
		{
			return false;
		}

		// Get the default values for the class from the table.
		$fields   = $this->getTableFields();
		$j_fields = $this->getQueryJoinFields();

		if ($j_fields)
		{
			$fields = array_merge($fields, $j_fields);
		}

		foreach ($fields as $k => $v)
		{
			// If the property is not the primary key or private, reset it.

			if ($k != $this->_tbl_key && (strpos($k, '_') !== 0))
			{
				$this->$k = $v->Default;
			}
		}

		if (!$this->onAfterReset())
		{
			return false;
		}
	}

	/**
	 * Generic check for whether dependancies exist for this object in the db schema
	 *
	 * @param   integer  $oid    The primary key of the record to delete
	 * @param   array    $joins  Any joins to foreign table, used to determine if dependent records exist
	 *
	 * @return  boolean  True if the record can be deleted
	 */
	public function canDelete($oid = null, $joins = null)
	{
		$k = $this->_tbl_key;

		if ($oid)
		{
			$this->$k = intval($oid);
		}

		if (is_array($joins))
		{
			$db      = $this->_db;
			$query   = $db->getQuery(true)
				->select($db->qn('master') . '.' . $db->qn($k))
				->from($db->qn($this->_tbl) . ' AS ' . $db->qn('master'));
			$tableNo = 0;

			foreach ($joins as $table)
			{
				$tableNo++;
				$query->select(
					array(
						'COUNT(DISTINCT ' . $db->qn('t' . $tableNo) . '.' . $db->qn($table['idfield']) . ') AS ' . $db->qn($table['idalias'])
					)
				);
				$query->join('LEFT', $db->qn($table['name']) .
					' AS ' . $db->qn('t' . $tableNo) .
					' ON ' . $db->qn('t' . $tableNo) . '.' . $db->qn($table['joinfield']) .
					' = ' . $db->qn('master') . '.' . $db->qn($k)
				);
			}

			$query->where($db->qn('master') . '.' . $db->qn($k) . ' = ' . $db->q($this->$k));
			$query->group($db->qn('master') . '.' . $db->qn($k));
			$this->_db->setQuery((string) $query);

			if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge'))
			{
				try
				{
					$obj = $this->_db->loadObject();
				}
				catch (JDatabaseException $e)
				{
					$this->setError($e->getMessage());
				}
			}
			else
			{
				if (!$obj = $this->_db->loadObject())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}

			$msg = array();
			$i   = 0;

			foreach ($joins as $table)
			{
				$k = $table['idalias'];

				if ($obj->$k > 0)
				{
					$msg[] = JText::_($table['label']);
				}

				$i++;
			}

			if (count($msg))
			{
				$option  = $this->input->getCmd('option', 'com_foobar');
				$comName = str_replace('com_', '', $option);
				$tview   = str_replace('#__' . $comName . '_', '', $this->_tbl);
				$prefix  = $option . '_' . $tview . '_NODELETE_';

				foreach ($msg as $key)
				{
					$this->setError(JText::_($prefix . $key));
				}

				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * Method to bind an associative array or object to the FOFTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the FOFTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function bind($src, $ignore = array())
	{
		if (!$this->onBeforeBind($src))
		{
			return false;
		}

		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			throw new InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getKnownFields() as $k)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->$k = $src[$k];
				}
			}
		}

		// Set rules for assets enabled tables
		if ($this->_trackAssets)
		{
			// Bind the rules.

			if (isset($src['rules']) && is_array($src['rules']))
			{
				$this->setRules($src['rules']);
			}
		}

		// Bind tags
		if ($this->_has_tags && isset($src['tags']))
		{
			$this->metadata = array();
			$this->metadata['tags'] = $src['tags'];
			$this->metadata = json_encode($this->metadata);
		}

		return true;
	}

	/**
	 * Method to store a row in the database from the FOFTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * FOFTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		if ($this->_has_tags)
		{
			$metadata = $this->metadata;
		}

		if (!$this->onBeforeStore($updateNulls))
		{
			return false;
		}

		$k = $this->_tbl_key;

		if ($this->$k == 0)
		{
			$this->$k = null;
		}

		$asset_id_field	= $this->getColumnAlias('asset_id');
		if (in_array($asset_id_field, $this->getKnownFields()))
		{
			if (!empty($this->$asset_id_field))
			{
				$currentAssetId = $this->$asset_id_field;
			}

			// The asset id field is managed privately by this class.
			if ($this->_trackAssets)
			{
				unset($this->$asset_id_field);
			}
		}

		// Manage tags, if present
		if ($this->_has_tags)
		{
			// TODO: JHelperTags sucks in Joomla! 3.1, it requires that tags are
			// stored in the metadata property. Not our case, therefore we need
			// to add it in a fake object. We sent a PR to Joomla! CMS to fix
			// that. Once it's accepted, we'll have to remove the attrocity
			// here...
			$tagsTable = clone($this);
			$tagsTable->metadata = $metadata;
			$this->_tagsHelper->preStoreProcess($tagsTable);
		}

		// Create the object used for inserting/udpating data to the database
		$fields     = $this->getTableFields();
		$properties = $this->getKnownFields();
		$keys       = array();

		foreach ($properties as $property)
		{
			// 'input' property is a reserved name

			if (isset($fields[$property]))
			{
				$keys[] = $property;
			}
		}

		$updateObject = array();
		foreach ($keys as $key)
		{
			$updateObject[$key] = $this->$key;
		}
		$updateObject = (object)$updateObject;

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			$this->_db->updateObject($this->_tbl, $updateObject, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$this->_db->insertObject($this->_tbl, $updateObject, $this->_tbl_key);
		}
		$this->bind($updateObject);

		// Now the real tags storing process
		if ($this->_has_tags)
		{
			// Check if the content type exists, and create it if it does not
			$this->checkContentType();

			// TODO: This little guy here fails because JHelperTags
			// need a JTable object to work, while our is FOFTable
			// Need probably to write our own FOFHelperTags
			// Thank you com_tags
			if (!$this->_tagsHelper->postStoreProcess($tagsTable))
			{
				$this->setError('Error storing tags');
				return false;
			}
		}

		if ($this->_locked)
		{
			$this->_unlock();
		}

		/*
		 * Asset Tracking
		 */
		if (in_array($asset_id_field, $this->getKnownFields()) && $this->_trackAssets)
		{
			$parentId = $this->_getAssetParentId();
			$name     = $this->_getAssetName();
			$title    = $this->_getAssetTitle();

			$asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
			$asset->loadByName($name);

			// Re-inject the asset id.
			$this->$asset_id_field = $asset->id;

			// Check for an error.
			$error = $asset->getError();

			if ($error)
			{
				$this->setError($error);

				return false;
			}

			// Specify how a new or moved node asset is inserted into the tree.
			if (empty($this->$asset_id_field) || $asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;

			if ($this->_rules instanceof JAccessRules)
			{
				$asset->rules = (string) $this->_rules;
			}

			if (!$asset->check() || !$asset->store($updateNulls))
			{
				$this->setError($asset->getError());

				return false;
			}

			// Create an asset_id or heal one that is corrupted.
			if (empty($this->$asset_id_field) || (($currentAssetId != $this->$asset_id_field) && !empty($this->$asset_id_field)))
			{
				// Update the asset_id field in this table.
				$this->$asset_id_field = (int) $asset->id;

				$query = $this->_db->getQuery(true);
				$query->update($this->_db->qn($this->_tbl));
				$query->set('asset_id = ' . (int) $this->$asset_id_field);
				$query->where($this->_db->qn($k) . ' = ' . (int) $this->$k);
				$this->_db->setQuery($query);

				$this->_db->execute();
			}

			$result = true;
		}

		$result = $this->onAfterStore();

		return $result;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the
	 *                           ordering values.
	 *
	 * @return  mixed    Boolean  True on success.
	 *
	 * @throws  UnexpectedValueException
	 */
	public function move($delta, $where = '')
	{
		if (!$this->onBeforeMove($delta, $where))
		{
			return false;
		}

		// If there is no ordering field set an error and return false.
		$ordering_field = $this->getColumnAlias('ordering');

		if (!in_array($ordering_field, $this->getKnownFields()))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', $this->_tbl));
		}

		// If the change is none, do nothing.
		if (empty($delta))
		{
			$result = $this->onAfterMove();

			return $result;
		}

		$k     = $this->_tbl_key;
		$row   = null;
		$query = $this->_db->getQuery(true);

        // If the table is not loaded, return false
        if (empty($this->$k))
        {
            return false;
        }

		// Select the primary key and ordering values from the table.
		$query->select(array($this->_db->qn($this->_tbl_key), $this->_db->qn($ordering_field)));
		$query->from($this->_tbl);

		// If the movement delta is negative move the row up.

		if ($delta < 0)
		{
			$query->where($this->_db->qn($ordering_field) . ' < ' . $this->_db->q((int) $this->$ordering_field));
			$query->order($this->_db->qn($ordering_field) . ' DESC');
		}

		// If the movement delta is positive move the row down.

		elseif ($delta > 0)
		{
			$query->where($this->_db->qn($ordering_field) . ' > ' . $this->_db->q((int) $this->$ordering_field));
			$query->order($this->_db->qn($ordering_field) . ' ASC');
		}

		// Add the custom WHERE clause if set.

		if ($where)
		{
			$query->where($where);
		}

		// Select the first row with the criteria.
		$this->_db->setQuery($query, 0, 1);
		$row = $this->_db->loadObject();

		// If a row is found, move the item.

		if (!empty($row))
		{
			// Update the ordering field for this instance to the row's ordering value.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($ordering_field) . ' = ' . $this->_db->q((int) $row->$ordering_field));
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($this->$k));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the ordering field for the row to this instance's ordering value.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($ordering_field) . ' = ' . $this->_db->q((int) $this->$ordering_field));
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($row->$k));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the instance value.
			$this->$ordering_field = $row->$ordering_field;
		}
		else
		{
			// Update the ordering field for this instance.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($ordering_field) . ' = ' . $this->_db->q((int) $this->$ordering_field));
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($this->$k));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		$result = $this->onAfterMove();

		return $result;
	}

    /**
     * Change the ordering of the records of the table
     *
     * @param   string   $where  The WHERE clause of the SQL used to fetch the order
     *
     * @return  boolean  True is successful
     *
     * @throws  UnexpectedValueException
     */
	public function reorder($where = '')
	{
		if (!$this->onBeforeReorder($where))
		{
			return false;
		}

		// If there is no ordering field set an error and return false.

		$order_field = $this->getColumnAlias('ordering');

		if (!in_array($order_field, $this->getKnownFields()))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', $this->_tbl_key));
		}

		$k = $this->_tbl_key;

		// Get the primary keys and ordering values for the selection.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key . ', ' . $this->_db->qn($order_field));
		$query->from($this->_tbl);
		$query->where($this->_db->qn($order_field) . ' >= ' . $this->_db->q(0));
		$query->order($this->_db->qn($order_field));

		// Setup the extra where and ordering clause data.

		if ($where)
		{
			$query->where($where);
		}

		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		// Compact the ordering values.

		foreach ($rows as $i => $row)
		{
			// Make sure the ordering is a positive integer.

			if ($row->$order_field >= 0)
			{
				// Only update rows that are necessary.

				if ($row->$order_field != $i + 1)
				{
					// Update the row ordering field.
					$query = $this->_db->getQuery(true);
					$query->update($this->_tbl);
					$query->set($this->_db->qn($order_field) . ' = ' . $this->_db->q($i + 1));
					$query->where($this->_tbl_key . ' = ' . $this->_db->q($row->$k));
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
			}
		}

		$result = $this->onAfterReorder();

		return $result;
	}

	/**
	 * Check out (lock) a record
	 *
	 * @param   integer  $userId  The locking user's ID
	 * @param   integer  $oid     The primary key value of the record to lock
	 *
	 * @return  boolean  True on success
	 */
	public function checkout($userId, $oid = null)
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');
		$fldLockedOn = $this->getColumnAlias('locked_on');

		if (!(in_array($fldLockedBy, $this->getKnownFields())
			|| in_array($fldLockedOn, $this->getKnownFields())))
		{
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

        // No primary key defined, stop here
        if (!$this->$k)
        {
            return false;
        }

		$date = JFactory::getDate();

		if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge'))
		{
			$time = $date->toSql();
		}
		else
		{
			$time = $date->toMysql();
		}

		$query = $this->_db->getQuery(true)
			->update($this->_db->qn($this->_tbl))
			->set(
				array(
					$this->_db->qn($fldLockedBy) . ' = ' . $this->_db->q((int) $userId),
					$this->_db->qn($fldLockedOn) . ' = ' . $this->_db->q($time)
				)
			)
			->where($this->_db->qn($this->_tbl_key) . ' = ' . $this->_db->q($this->$k));
		$this->_db->setQuery((string) $query);

		$this->$fldLockedBy = $userId;
		$this->$fldLockedOn = $time;

		return $this->_db->execute();
	}

	/**
	 * Check in (unlock) a record
	 *
	 * @param   integer  $oid  The primary key value of the record to unlock
	 *
	 * @return  boolean  True on success
	 */
	public function checkin($oid = null)
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');
		$fldLockedOn = $this->getColumnAlias('locked_on');

		if (!(in_array($fldLockedBy, $this->getKnownFields())
			|| in_array($fldLockedOn, $this->getKnownFields())))
		{
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

		if ($this->$k == null)
		{
			return false;
		}

		$query = $this->_db->getQuery(true)
			->update($this->_db->qn($this->_tbl))
			->set(
				array(
					$this->_db->qn($fldLockedBy) . ' = 0',
					$this->_db->qn($fldLockedOn) . ' = ' . $this->_db->q($this->_db->getNullDate())
				)
			)
			->where($this->_db->qn($this->_tbl_key) . ' = ' . $this->_db->q($this->$k));
		$this->_db->setQuery((string) $query);

		$this->$fldLockedBy = 0;
		$this->$fldLockedOn = '';

		return $this->_db->execute();
	}

	/**
	 * Is a record locked?
	 *
	 * @param   integer  $with     The userid to preform the match with. If an item is checked
	 *                             out by this user the function will return false.
	 * @param   integer  $against  Junk inherited from JTable; ignore
	 *
	 * @return  boolean  True if the record is locked by another user
	 */
	public function isCheckedOut($with = 0, $against = null)
	{
		$fldLockedBy = $this->getColumnAlias('locked_by');

		if (isset($this) && is_a($this, 'FOFTable') && is_null($against))
		{
			$against = $this->get($fldLockedBy);
		}

		// Item is not checked out, or being checked out by the same user

		if (!$against || $against == $with)
		{
			return false;
		}

		$session = JTable::getInstance('session');

		return $session->exists($against);
	}

	/**
	 * Copy (duplicate) one or more records
	 *
	 * @param   integer|array  $cid  The primary key value (or values) or the record(s) to copy
	 *
	 * @return  boolean  True on success
	 */
	public function copy($cid = null)
	{
		JArrayHelper::toInteger($cid);
		$k = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				$this->setError("No items selected.");

				return false;
			}
		}

		$created_by  = $this->getColumnAlias('created_by');
		$created_on  = $this->getColumnAlias('created_on');
		$modified_by = $this->getColumnAlias('modified_by');
		$modified_on = $this->getColumnAlias('modified_on');

		$locked_byName = $this->getColumnAlias('locked_by');
		$checkin       = in_array($locked_byName, $this->getKnownFields());

		foreach ($cid as $item)
		{
			// Prevent load with id = 0

			if (!$item)
			{
				continue;
			}

			$this->load($item);

			if ($checkin)
			{
				// We're using the checkin and the record is used by someone else

				if ($this->isCheckedOut($item))
				{
					continue;
				}
			}

			if (!$this->onBeforeCopy($item))
			{
				continue;
			}

			$this->$k           = null;
			$this->$created_by  = null;
			$this->$created_on  = null;
			$this->$modified_on = null;
			$this->$modified_by = null;

			// Let's fire the event only if everything is ok
			if ($this->store())
			{
				$this->onAfterCopy($item);
			}

			$this->reset();
		}

		return true;
	}

	/**
	 * Publish or unpublish records
	 *
	 * @param   integer|array  $cid      The primary key value(s) of the item(s) to publish/unpublish
	 * @param   integer        $publish  1 to publish an item, 0 to unpublish
	 * @param   integer        $user_id  The user ID of the user (un)publishing the item.
	 *
	 * @return  boolean  True on success, false on failure (e.g. record is locked)
	 */
	public function publish($cid = null, $publish = 1, $user_id = 0)
	{
		JArrayHelper::toInteger($cid);
		$user_id = (int) $user_id;
		$publish = (int) $publish;
		$k       = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				$this->setError("No items selected.");

				return false;
			}
		}

		if (!$this->onBeforePublish($cid, $publish))
		{
			return false;
		}

		$enabledName   = $this->getColumnAlias('enabled');
		$locked_byName = $this->getColumnAlias('locked_by');

		$query = $this->_db->getQuery(true)
			->update($this->_db->qn($this->_tbl))
			->set($this->_db->qn($enabledName) . ' = ' . (int) $publish);

		$checkin = in_array($locked_byName, $this->getKnownFields());

		if ($checkin)
		{
			$query->where(
				' (' . $this->_db->qn($locked_byName) .
					' = 0 OR ' . $this->_db->qn($locked_byName) . ' = ' . (int) $user_id . ')', 'AND'
			);
		}

		$cids = $this->_db->qn($k) . ' = ' .
			implode(' OR ' . $this->_db->qn($k) . ' = ', $cid);

		$query->where('(' . $cids . ')');

		$this->_db->setQuery((string) $query);

		if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge'))
		{
			try
			{
				$this->_db->execute();
			}
			catch (JDatabaseException $e)
			{
				$this->setError($e->getMessage());
			}
		}
		else
		{
			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		if (count($cid) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1)
			{
				$this->checkin($cid[0]);

				if ($this->$k == $cid[0])
				{
					$this->published = $publish;
				}
			}
		}

		$this->setError('');

		return true;
	}

	/**
	 * Delete a record
	 *
	 * @param   integer  $oid  The primary key value of the item to delete
	 *
	 * @return  boolean  True on success
	 */
	public function delete($oid = null)
	{
		if ($oid)
		{
			$this->load($oid);
		}

		if (!$this->onBeforeDelete($oid))
		{
			return false;
		}

		$k  = $this->_tbl_key;
		$pk = (is_null($oid)) ? $this->$k : $oid;

		// If no primary key is given, return false.

		if ($pk === null)
		{
			throw new UnexpectedValueException('Null primary key not allowed.');
		}

		// If tracking assets, remove the asset first.

		if ($this->_trackAssets)
		{
			// Get and the asset name.
			$this->$k = $pk;
			$name     = $this->_getAssetName();

			// Do NOT touch JTable here -- we are loading the core asset table which is a JTable, not a FOFTable
			$asset    = JTable::getInstance('Asset');

			if ($asset->loadByName($name))
			{
				if (!$asset->delete())
				{
					$this->setError($asset->getError());

					return false;
				}
			}
			else
			{
				$this->setError($asset->getError());

				return false;
			}
		}

		// If this resource has tags, delete the tags first
		if ($this->_has_tags)
		{
			if (!$this->_tagsHelper->deleteTagData($this, $pk))
			{
				$this->setError('Error deleting Tags');

				return false;
			}
		}

		// Delete the row by primary key.
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_tbl);
		$query->where($this->_tbl_key . ' = ' . $this->_db->q($pk));
		$this->_db->setQuery($query);

		// Check for a database error.
		$this->_db->execute();

		$result = $this->onAfterDelete($oid);

		return $result;
	}

	/**
	 * Register a hit on a record
	 *
	 * @param   integer  $oid  The primary key value of the record
	 * @param   boolean  $log  Should I log the hit?
	 *
	 * @return  boolean  True on success
	 */
	public function hit($oid = null, $log = false)
	{
		if (!$this->onBeforeHit($oid, $log))
		{
			return false;
		}

		// If there is no hits field, just return true.
		$hits_field = $this->getColumnAlias('hits');

		if (!in_array($hits_field, $this->getKnownFields()))
		{
			return true;
		}

		$k  = $this->_tbl_key;
		$pk = (is_null($oid)) ? $this->$k : $oid;

		// If no primary key is given, return false.

		if ($pk === null)
		{
			$result = false;
		}
		else
		{
			// Check the row in by primary key.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set($this->_db->qn($hits_field) . ' = (' . $this->_db->qn($hits_field) . ' + 1)');
			$query->where($this->_tbl_key . ' = ' . $this->_db->q($pk));
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Set table values in the object.
			$this->hits++;

			$result = true;
		}

		if ($result)
		{
			$result = $this->onAfterHit($oid);
		}

		return $result;
	}

	/**
	 * Export the item as a CSV line
	 *
	 * @param   string  $separator  CSV separator. Tip: use "\t" to get a TSV file instead.
	 *
	 * @return  string  The CSV line
	 */
	public function toCSV($separator = ',')
	{
		$csv = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			if ($k[0] == '_')
			{
				// Internal field
				continue;
			}

			$csv[] = '"' . str_replace('"', '""', $v) . '"';
		}

		$csv = implode($separator, $csv);

		return $csv;
	}

	/**
	 * Exports the table in array format
	 *
	 * @return  array
	 */
	public function getData()
	{
		$ret = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			if (($k[0] == '_') || ($k[0] == '*'))
			{
				// Internal field
				continue;
			}

			$ret[$k] = $v;
		}

		return $ret;
	}

	/**
	 * Get the header for exporting item list to CSV
	 *
	 * @param   string  $separator  CSV separator. Tip: use "\t" to get a TSV file instead.
	 *
	 * @return  string  The CSV file's header
	 */
	public function getCSVHeader($separator = ',')
	{
		$csv = array();

		foreach (get_object_vars($this) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			if ($k[0] == '_')
			{
				// Internal field
				continue;
			}

			$csv[] = '"' . str_replace('"', '\"', $k) . '"';
		}

		$csv = implode($separator, $csv);

		return $csv;
	}

	/**
	 * Get the columns from a database table.
	 *
	 * @param   string  $tableName  Table name. If null current table is used
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 */
	public function getTableFields($tableName = null)
	{
		static $cache = array();
		static $tables = array();

		// Make sure we have a list of tables in this db

		if (empty($tables))
		{
			$tables = $this->_db->getTableList();
		}

		if (!$tableName)
		{
			$tableName = $this->_tbl;
		}

		if (!array_key_exists($tableName, $cache))
		{
			// Lookup the fields for this table only once.
			$name = $tableName;

			$prefix = $this->_db->getPrefix();

			if (substr($name, 0, 3) == '#__')
			{
				$checkName = $prefix . substr($name, 3);
			}
			else
			{
				$checkName = $name;
			}

			if (!in_array($checkName, $tables))
			{
				// The table doesn't exist. Return false.
				$cache[$tableName] = false;
			}
			elseif (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge'))
			{
				$fields = $this->_db->getTableColumns($name, false);

				if (empty($fields))
				{
					$fields = false;
				}

				$cache[$tableName] = $fields;
			}
			else
			{
				$fields = $this->_db->getTableFields($name, false);

				if (!isset($fields[$name]))
				{
					$fields = false;
				}

				$cache[$tableName] = $fields[$name];
			}
		}

		return $cache[$tableName];
	}

	public function getTableAlias()
	{
		return $this->_tableAlias;
	}

	public function setTableAlias($string)
	{
		$string = preg_replace('#[^A-Z0-9_]#i', '', $string);
		$this->_tableAlias = $string;
	}

	/**
	 * Method to return the real name of a "special" column such as ordering, hits, published
	 * etc etc. In this way you are free to follow your db naming convention and use the
	 * built in Joomla functions.
	 *
	 * @param   string  $column  Name of the "special" column (ie ordering, hits etc etc)
	 *
	 * @return  string  The string that identify the special
	 */
	public function getColumnAlias($column)
	{
		if (isset($this->_columnAlias[$column]))
		{
			$return = $this->_columnAlias[$column];
		}
		else
		{
			$return = $column;
		}

		$return = preg_replace('#[^A-Z0-9_]#i', '', $return);

		return $return;
	}

	/**
	 * Method to register a column alias for a "special" column.
	 *
	 * @param   string  $column       The "special" column (ie ordering)
	 * @param   string  $columnAlias  The real column name (ie foo_ordering)
	 *
	 * @return  void
	 */
	public function setColumnAlias($column, $columnAlias)
	{
		$column = strtolower($column);

		$column                      = preg_replace('#[^A-Z0-9_]#i', '', $column);
		$this->_columnAlias[$column] = $columnAlias;
	}

	/**
	 * Get a JOIN query, used to join other tables
	 *
	 * @param   boolean  $asReference  Return an object reference instead of a copy
	 *
	 * @return  JDatabaseQuery  Query used to join other tables
	 */
	public function getQueryJoin($asReference = false)
	{
		if ($asReference)
		{
			return $this->_queryJoin;
		}
		else
		{
			if ($this->_queryJoin)
			{
				return clone $this->_queryJoin;
			}
			else
			{
				return null;
			}
		}
	}

	/**
	 * Sets the query with joins to other tables
	 *
	 * @param   JDatabaseQuery  $query  The JOIN query to use
	 *
	 * @return  void
	 */
	public function setQueryJoin(JDatabaseQuery $query)
	{
		$this->_queryJoin = $query;
	}

	/**
	 * Extracts the fields from the join query
	 *
	 * @return   array    Fields contained in the join query
	 */
	protected function getQueryJoinFields()
	{
		$query = $this->getQueryJoin();

		if (!$query)
		{
			return array();
		}

		// Get joined tables. Ignore FROM clause, since it should not be used (the starting point is the table "table")
		$tables = array();
		$joins  = $query->join;

		foreach ($joins as $join)
		{
			$tables = array_merge($tables, $join->getElements());
		}

		// Clean up table names

		for ($i = 0; $i < count($tables); $i++)
		{
			preg_match('#\#__.*?\s#', $tables[$i], $matches);
			$tables[$i] = str_replace(' ', '', $matches[0]);
		}

		// Get table fields
		$fields = array();

		foreach ($tables as $table)
		{
			$t_fields = $this->getTableFields($table);

			if ($t_fields)
			{
				$fields = array_merge($fields, $t_fields);
			}
		}

		// Remove any fields that aren't in the joined select
		$j_select = $query->select;

		if ($j_select && $j_select->getElements())
		{
			$j_fields = $this->normalizeSelectFields($j_select->getElements());
		}

		// Flip the array so I can intesect the keys
		$fields = array_intersect_key($fields, $j_fields);

		// Now I walk again the array to change the key of columns that have an alias

		foreach ($j_fields as $column => $alias)
		{
			if ($column != $alias)
			{
				$fields[$alias] = $fields[$column];
				unset($fields[$column]);
			}
		}

		return $fields;
	}

	/**
	 * Normalizes the fields, returning an array with all the fields.
	 * Ie array('foobar, foo') becomes array('foobar', 'foo')
	 *
	 * @param   array    $fields    Array with column fields
	 * @param   boolean  $useAlias  Should I use the column alias or use the extended syntax?
	 *
	 * @return  array  Normalized array
	 */
	protected function normalizeSelectFields($fields, $extended = false)
	{
		$return = array();

		foreach ($fields as $field)
		{
			$t_fields = explode(',', $field);

			foreach ($t_fields as $t_field)
			{
				// Is there any alias for this column?
				preg_match('#\sas\s`?\w+`?#i', $t_field, $match);
				$alias = empty($match) ? '' : $match[0];
				$alias = preg_replace('#\sas\s?#i', '', $alias);

				// Grab the "standard" name
				// @TODO Check this pattern since it's blind copied from forums
				preg_match('/([\w]++)`?+(?:\s++as\s++[^,\s]++)?+\s*+($)/i', $t_field, $match);
				$column = $match[1];
				$column = preg_replace('#\sas\s?#i', '', $column);

				// Trim whitespace
				$alias  = preg_replace('#^[\s-`]+|[\s-`]+$#', '', $alias);
				$column = preg_replace('#^[\s-`]+|[\s-`]+$#', '', $column);

				// Do I want the column name with the original name + alias?

				if ($extended && $alias)
				{
					$alias = $column . ' AS ' . $alias;
				}

				if (!$alias)
				{
					$alias = $column;
				}

				$return[$column] = $alias;
			}
		}

		return $return;
	}

	/**
	 * The event which runs before binding data to the table
	 *
	 * NOTE TO 3RD PARTY DEVELOPERS:
	 *
	 * When you override the following methods in your child classes,
	 * be sure to call parent::method *AFTER* your code, otherwise the
	 * plugin events do NOT get triggered
	 *
	 * Example:
	 * protected function onAfterStore(){
	 *       // Your code here
	 *     return parent::onAfterStore() && $your_result;
	 * }
	 *
	 * Do not do it the other way around, e.g. return $your_result && parent::onAfterStore()
	 * Due to  PHP short-circuit boolean evaluation the parent::onAfterStore()
	 * will not be called if $your_result is false.
	 *
	 * @param   object|array  &$from  The data to bind
	 *
	 * @return  boolean  True on success
	 */
	protected function onBeforeBind(&$from)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeBind' . ucfirst($name), array(&$this, &$from));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after loading a record from the database
	 *
	 * @param   boolean  &$result  Did the load succeeded?
	 *
	 * @return  void
	 */
	protected function onAfterLoad(&$result)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			FOFPlatform::getInstance()->runPlugins('onAfterLoad' . ucfirst($name), array(&$this, &$result));
		}
	}

	/**
	 * The event which runs before storing (saving) data to the database
	 *
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow saving
	 */
	protected function onBeforeStore($updateNulls)
	{
		// Do we have a "Created" set of fields?
		$created_on  = $this->getColumnAlias('created_on');
		$created_by  = $this->getColumnAlias('created_by');
		$modified_on = $this->getColumnAlias('modified_on');
		$modified_by = $this->getColumnAlias('modified_by');
		$locked_on   = $this->getColumnAlias('locked_on');
		$locked_by   = $this->getColumnAlias('locked_by');
		$title       = $this->getColumnAlias('title');
		$slug        = $this->getColumnAlias('slug');

		$hasCreatedOn = in_array($created_on, $this->getKnownFields());
		$hasCreatedBy = in_array($created_by, $this->getKnownFields());

		if ($hasCreatedOn && $hasCreatedBy)
		{
			$hasModifiedOn = in_array($modified_on, $this->getKnownFields());
			$hasModifiedBy = in_array($modified_by, $this->getKnownFields());

			if (empty($this->$created_by) || ($this->$created_on == '0000-00-00 00:00:00') || empty($this->$created_on))
			{
				$uid = FOFPlatform::getInstance()->getUser()->id;

				if ($uid)
				{
					$this->$created_by = FOFPlatform::getInstance()->getUser()->id;
				}
				JLoader::import('joomla.utilities.date');
				$date = new JDate();

				if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge'))
				{
					$this->$created_on = $date->toSql();
				}
				else
				{
					$this->$created_on = $date->toMysql();
				}
			}
			elseif ($hasModifiedOn && $hasModifiedBy)
			{
				$uid = FOFPlatform::getInstance()->getUser()->id;

				if ($uid)
				{
					$this->$modified_by = FOFPlatform::getInstance()->getUser()->id;
				}
				JLoader::import('joomla.utilities.date');
				$date = new JDate();

				if (FOFPlatform::getInstance()->checkVersion(JVERSION, '3.0', 'ge'))
				{
					$this->$modified_on = $date->toSql();
				}
				else
				{
					$this->$modified_on = $date->toMysql();
				}
			}
		}

		// Do we have a set of title and slug fields?
		$hasTitle = in_array($title, $this->getFields());
		$hasSlug  = in_array($slug, $this->getFields());

		if ($hasTitle && $hasSlug)
		{
			if (empty($this->$slug))
			{
				// Create a slug from the title
				$this->$slug = FOFStringUtils::toSlug($this->$title);
			}
			else
			{
				// Filter the slug for invalid characters
				$this->$slug = FOFStringUtils::toSlug($this->$slug);
			}

			// Make sure we don't have a duplicate slug on this table
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select($db->qn($slug))
				->from($this->_tbl)
				->where($db->qn($slug) . ' = ' . $db->q($this->$slug))
				->where('NOT ' . $db->qn($this->_tbl_key) . ' = ' . $db->q($this->{$this->_tbl_key}));
			$db->setQuery($query);
			$existingItems = $db->loadAssocList();

			$count   = 0;
			$newSlug = $this->$slug;

			while (!empty($existingItems))
			{
				$count++;
				$newSlug = $this->$slug . '-' . $count;
				$query   = $db->getQuery(true)
					->select($db->qn($slug))
					->from($this->_tbl)
					->where($db->qn($slug) . ' = ' . $db->q($newSlug))
					->where($db->qn($this->_tbl_key) . ' = ' . $db->q($this->{$this->_tbl_key}), 'AND NOT');
				$db->setQuery($query);
				$existingItems = $db->loadAssocList();
			}

			$this->$slug = $newSlug;
		}

		// Execute onBeforeStore<tablename> events in loaded plugins
		if ($this->_trigger_events)
		{
			$name       = FOFInflector::pluralize($this->getKeyName());
			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeStore' . ucfirst($name), array(&$this, $updateNulls));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after storing (saving) data to the database
	 *
	 * @return  boolean  True to allow saving without an error
	 */
	protected function onAfterStore()
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterStore' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before moving a record
	 *
	 * @param   boolean  $updateNulls  Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow moving
	 */
	protected function onBeforeMove($updateNulls)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeMove' . ucfirst($name), array(&$this, $updateNulls));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after moving a record
	 *
	 * @return  boolean  True to allow moving without an error
	 */
	protected function onAfterMove()
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterMove' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before reordering a table
	 *
	 * @param   string  $where  The WHERE clause of the SQL query to run on reordering (record filter)
	 *
	 * @return  boolean  True to allow reordering
	 */
	protected function onBeforeReorder($where = '')
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeReorder' . ucfirst($name), array(&$this, $where));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after reordering a table
	 *
	 * @return  boolean  True to allow the reordering to complete without an error
	 */
	protected function onAfterReorder()
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterReorder' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete($oid)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeDelete' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record which was deleted
	 *
	 * @return  boolean  True to allow the deletion without errors
	 */
	protected function onAfterDelete($oid)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterDelete' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before hitting a record
	 *
	 * @param   integer  $oid  The PK value of the record to hit
	 * @param   boolean  $log  Should we log the hit?
	 *
	 * @return  boolean  True to allow the hit
	 */
	protected function onBeforeHit($oid, $log)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeHit' . ucfirst($name), array(&$this, $oid, $log));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after hitting a record
	 *
	 * @param   integer  $oid  The PK value of the record which was hit
	 *
	 * @return  boolean  True to allow the hitting without errors
	 */
	protected function onAfterHit($oid)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterHit' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The even which runs before copying a record
	 *
	 * @param   integer  $oid  The PK value of the record being copied
	 *
	 * @return  boolean  True to allow the copy to take place
	 */
	protected function onBeforeCopy($oid)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeCopy' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The even which runs after copying a record
	 *
	 * @param   integer  $oid  The PK value of the record which was copied (not the new one)
	 *
	 * @return  boolean  True to allow the copy without errors
	 */
	protected function onAfterCopy($oid)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterCopy' . ucfirst($name), array(&$this, $oid));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before a record is (un)published
	 *
	 * @param   integer|array  &$cid     The PK IDs of the records being (un)published
	 * @param   integer        $publish  1 to publish, 0 to unpublish
	 *
	 * @return  boolean  True to allow the (un)publish to proceed
	 */
	protected function onBeforePublish(&$cid, $publish)
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforePublish' . ucfirst($name), array(&$this, &$cid, $publish));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs after the object is reset to its default values.
	 *
	 * @return  boolean  True to allow the reset to complete without errors
	 */
	protected function onAfterReset()
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onAfterReset' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * The even which runs before the object is reset to its default values.
	 *
	 * @return  boolean  True to allow the reset to complete
	 */
	protected function onBeforeReset()
	{
		if ($this->_trigger_events)
		{
			$name = FOFInflector::pluralize($this->getKeyName());

			$result     = FOFPlatform::getInstance()->runPlugins('onBeforeReset' . ucfirst($name), array(&$this));

			if (in_array(false, $result, true))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	 * Replace the input object of this table with the provided FOFInput object
	 *
	 * @param   FOFInput  $input  The new input object
	 *
	 * @return  void
	 */
	public function setInput(FOFInput $input)
	{
		$this->input = $input;
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 *
	 * @deprecated  2.1
	 */
	public function getFields()
	{
		return $this->getTableFields();
	}

	/**
	 * Add a filesystem path where FOFTable should search for table class files.
	 * You may either pass a string or an array of paths.
	 *
	 * @param   mixed  $path  A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find FOFTable classes in.
	 */
	public static function addIncludePath($path = null)
	{
		// If the internal paths have not been initialised, do so with the base table path.
		if (empty(self::$_includePaths))
		{
			self::$_includePaths = array(__DIR__);
		}

		// Convert the passed path(s) to add to an array.
		settype($path, 'array');

		// If we have new paths to add, do so.
		if (!empty($path) && !in_array($path, self::$_includePaths))
		{
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				array_unshift(self::$_includePaths, $dir);
			}
		}

		return self::$_includePaths;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return $this->_assetKey . '.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.  In
	 * tracking the assets a title is kept for each asset so that there is some
	 * context available in a unified access manager.  Usually this would just
	 * return $this->title or $this->name or whatever is being used for the
	 * primary name of the row. If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 */
	protected function _getAssetTitle()
	{
		return $this->_getAssetName();
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID,
	 * which will default to 1 if none exists.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   FOFTable  $table  A FOFTable object for the asset parent.
	 * @param   integer   $id     Id to look up
	 *
	 * @return  integer
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// For simple cases, parent to the asset root.
		$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$rootId = $assets->getRootId();

		if (!empty($rootId))
		{
			return $rootId;
		}

		return 1;
	}

	/**
	 * This method sets the asset key for the items of this table. Obviously, it
	 * is only meant to be used when you have a table with an asset field.
	 *
	 * @param   string  $assetKey  The name of the asset key to use
	 *
	 * @return  void
	 */
	public function setAssetKey($assetKey)
	{
		$this->_assetKey = $assetKey;
	}

	/**
	 * Method to get the database table name for the class.
	 *
	 * @return  string  The name of the database table being modeled.
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @return  string  The name of the primary key for the table.
	 */
	public function getKeyName()
	{
		return $this->_tbl_key;
	}

	/**
	 * Method to get the JDatabaseDriver object.
	 *
	 * @return  JDatabaseDriver  The internal database driver object.
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to set the JDatabaseDriver object.
	 *
	 * @param   JDatabaseDriver  $db  A JDatabaseDriver object to be used by the table object.
	 *
	 * @return  boolean  True on success.
	 */
	public function setDBO(JDatabaseDriver $db)
	{
		$this->_db = $db;

		return true;
	}

	/**
	 * Method to set rules for the record.
	 *
	 * @param   mixed  $input  A JAccessRules object, JSON string, or array.
	 *
	 * @return  void
	 */
	public function setRules($input)
	{
		if ($input instanceof JAccessRules)
		{
			$this->_rules = $input;
		}
		else
		{
			$this->_rules = new JAccessRules($input);
		}
	}

	/**
	 * Method to get the rules for the record.
	 *
	 * @return  JAccessRules object
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * Method to check if the record is treated as an ACL asset
	 *
	 * @return  boolean [description]
	 */
	public function isAssetsTracked()
	{
		return $this->_trackAssets;
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a FOFTable
	 * instance to the database table.  The method will check a row in once the
	 * data has been stored and if an ordering filter is present will attempt to
	 * reorder the table rows based on the filter.  The ordering filter is an instance
	 * property name.  The rows that will be reordered are those whose value matches
	 * the FOFTable instance for the property specified.
	 *
	 * @param   mixed   $src             An associative array or object to bind to the FOFTable instance.
	 * @param   string  $orderingFilter  Filter for the order updating
	 * @param   mixed   $ignore          An optional array or space separated list of properties
	 *                                   to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		// Attempt to bind the source to the instance.
		if (!$this->bind($src, $ignore))
		{
			return false;
		}

		// Run any sanity checks on the instance and verify that it is ready for storage.
		if (!$this->check())
		{
			return false;
		}

		// Attempt to store the properties to the database table.
		if (!$this->store())
		{
			return false;
		}

		// Attempt to check the row in, just in case it was checked out.
		if (!$this->checkin())
		{
			return false;
		}

		// If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
		if ($orderingFilter)
		{
			$filterValue = $this->$orderingFilter;
			$this->reorder($orderingFilter ? $this->_db->qn($orderingFilter) . ' = ' . $this->_db->q($filterValue) : '');
		}

		// Set the error to empty and return true.
		$this->setError('');

		return true;
	}

	/**
	 * Method to get the next ordering value for a group of rows defined by an SQL WHERE clause.
	 * This is useful for placing a new item last in a group of items in the table.
	 *
	 * @param   string  $where  WHERE clause to use for selecting the MAX(ordering) for the table.
	 *
	 * @return  mixed  Boolean false an failure or the next ordering value as an integer.
	 */
	public function getNextOrder($where = '')
	{
		// If there is no ordering field set an error and return false.
		$ordering = $this->getColumnAlias('ordering');
		if (!in_array($ordering, $this->getKnownFields()))
		{
			throw new UnexpectedValueException(sprintf('%s does not support ordering.', get_class($this)));
		}

		// Get the largest ordering value for a given where clause.
		$query = $this->_db->getQuery(true);
		$query->select('MAX('.$this->_db->qn($ordering).')');
		$query->from($this->_tbl);

		if ($where)
		{
			$query->where($where);
		}

		$this->_db->setQuery($query);
		$max = (int) $this->_db->loadResult();

		// Return the largest ordering value + 1.
		return ($max + 1);
	}

	/**
	 * Method to lock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  RuntimeException
	 */
	protected function _lock()
	{
		$this->_db->lockTable($this->_tbl);
		$this->_locked = true;

		return true;
	}

	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 */
	protected function _unlock()
	{
		$this->_db->unlockTables();
		$this->_locked = false;

		return true;
	}

	/**
	 * Check if a UCM content type exists for this resource, and
	 * create it if it does not
	 */
	protected function checkContentType()
	{
		$contentType = new JTableContenttype($this->_db);

		$alias = $this->getContentType();

		// Fetch the extension name
		$component = JComponentHelper::getComponent($component);

		// Fetch the name using the menu item
		$query = $this->_db->getQuery(true);
		$query->select('title')->from('#__menu')->where('component_id = ' . (int) $component->id);
		$this->_db->setQuery($query);
		$component_name = JText::_($this->_db->loadResult());

		$name = $component_name . ' ' . ucfirst($view);

		// Create a new content type for our resource
		if (!$contentType->load(array('type_alias' => $alias)))
		{
			$contentType->type_title = $name;
			$contentType->type_alias = $alias;
			$contentType->table = json_encode(
				array(
					'special' => array(
						'dbtable' => $this->_tbl,
						'key'     => $this->_tbl_key,
						'type'    => $name,
						'prefix'  => $this->_tablePrefix,
						'config' => 'array()'
					),
					'common' => array(
						'dbtable' => '#__ucm_content',
						'key' => 'ucm_id',
						'type' => 'CoreContent',
						'prefix' => 'JTable',
						'config' => 'array()'
					)
				)
			);

			$contentType->field_mappings = json_encode(
				array(
					'common' => array(
						0 => array(
							"core_content_item_id" => $this->_tbl_key,
							"core_title"           => $this->getUcmCoreAlias('title'),
							"core_state"           => $this->getUcmCoreAlias('enabled'),
							"core_alias"           => $this->getUcmCoreAlias('alias'),
							"core_created_time"    => $this->getUcmCoreAlias('created_on'),
							"core_modified_time"   => $this->getUcmCoreAlias('created_by'),
							"core_body"            => $this->getUcmCoreAlias('body'),
							"core_hits"            => $this->getUcmCoreAlias('hits'),
							"core_publish_up"      => $this->getUcmCoreAlias('publish_up'),
							"core_publish_down"    => $this->getUcmCoreAlias('publish_down'),
							"core_access"          => $this->getUcmCoreAlias('access'),
							"core_params"          => $this->getUcmCoreAlias('params'),
							"core_featured"        => $this->getUcmCoreAlias('featured'),
							"core_metadata"        => $this->getUcmCoreAlias('metadata'),
							"core_language"        => $this->getUcmCoreAlias('language'),
							"core_images"          => $this->getUcmCoreAlias('images'),
							"core_urls"            => $this->getUcmCoreAlias('urls'),
							"core_version"         => $this->getUcmCoreAlias('version'),
							"core_ordering"        => $this->getUcmCoreAlias('ordering'),
							"core_metakey"         => $this->getUcmCoreAlias('metakey'),
							"core_metadesc"        => $this->getUcmCoreAlias('metadesc'),
							"core_catid"           => $this->getUcmCoreAlias('cat_id'),
							"core_xreference"      => $this->getUcmCoreAlias('xreference'),
							"asset_id"             => $this->getUcmCoreAlias('asset_id')
						)
					),
					'special' => array(
						0 => array(
						)
					)
				)
			);

			$contentType->router = '';

			$contentType->store();
		}
	}

	/**
	 * Utility methods that fetches the column name for the field.
	 * If it does not exists, returns a "null" string
	 *
	 * @return string The column name
	 */
	protected function getUcmCoreAlias($alias)
	{
		$alias = $this->getColumnAlias($alias);

		if (in_array($alias, $this->getKnownFields()))
		{
			return $alias;
		}

		return "null";
	}

	/**
	 * Get the content type for ucm
	 *
	 * @return string The content type alias
	 */
	public function getContentType()
	{
		$component = $this->input->get('option');
		$view = FOFInflector::singularize($this->input->get('view'));
		$alias = $component . '.' . $view;

		return $alias;
	}
}
