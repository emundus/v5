<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * Fabrik Plugin Manager Class
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikFEModelPluginmanager extends JModel
{

	/** @var array plugins */
	public $plugIns = array();

	protected $group = null;

	/** @var int # of plugins run */
	protected $runPlugins = 0;

	/** @var array element lists */
	protected $elementLists = array();

	/** @var array containing out put from run plugins */
	var $_data = array();

	/**
	 * Array of array of form plugins - keyed on group id
	 *
	 * @var  array
	 */
	protected $formPlugins = array();
	
	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Get a html drop down list of the elment types with this objs element type selected as default
	 *
	 * @param   string  $default       selected option
	 * @param   string  $name          html name for drop down
	 * @param   string  $extra         extra info for drop down
	 * @param   string  $defaultlabel  html element type list
	 *
	 * @return  string
	 */

	public function getElementTypeDd($default, $name = 'plugin', $extra = 'class="inputbox elementtype"  size="1"', $defaultlabel = '')
	{
		$hash = $default . $name . $extra . $defaultlabel;
		if (!array_key_exists($hash, $this->elementLists))
		{
			if ($defaultlabel == '')
			{
				$defaultlabel = JText::_('COM_FABRIK_PLEASE_SELECT');
			}
			$a = array(JHTML::_('select.option', '', $defaultlabel));
			$elementstypes = $this->_getList();
			$elementstypes = array_merge($a, $elementstypes);
			$this->elementLists[$hash] = JHTML::_('select.genericlist', $elementstypes, $name, $extra, 'value', 'text', $default);
		}
		return $this->elementLists[$hash];
	}

	/**
	 * Can the pluginmanager be used
	 *
	 * @deprecated
	 *
	 * @return  true
	 */

	public function canUse()
	{
		return true;
	}

	/**
	 * Get an unordered list (<ul>) of plugins
	 *
	 * @param   string  $group  plugin group
	 * @param   string  $id     ul id
	 *
	 * @return  string  <ul>
	 */

	public function getList($group, $id)
	{
		$str = '<ul id="' . $id . '">';
		$elementstypes = $this->_getList();
		foreach ($elementstypes as $plugin)
		{
			$str .= '<li>' . $plugin->text . '</li>';
		}
		$str .= '</ul>';
		return $str;
	}

	/**
	 * Get a list of plugin ids/names for usin in a drop down list
	 * if no group set defaults to element list
	 *
	 * @param   object  $query       query
	 * @param   int     $limitstart  limit start
	 * @param   int     $limit       # of records to retunr
	 *
	 * @return  array	plugin list
	 */

	protected function _getList($query = null, $limitstart = 0, $limit = 0)
	{
		$db = FabrikWorker::getDbo(true);
		if (is_null($this->group))
		{
			$this->group = 'element';
		}
		$query = $db->getQuery(true);
		$folder = $db->quote('fabrik_' . $this->group);
		$query->select('element AS value, name AS text')->from('#__extensions')->where('folder =' . $folder);
		$db->setQuery($query);
		$elementstypes = $db->loadObjectList();
		return $elementstypes;
	}

	/**
	 * Get a certain group of plugins
	 *
	 * @param   string  $group  plugin group to load
	 *
	 * @return  array	plugins
	 */

	public function &getPlugInGroup($group)
	{
		if (array_key_exists($group, $this->plugIns))
		{
			return $this->plugIns[$group];
		}
		else
		{
			return $this->loadPlugInGroup($group);
		}
	}

	/**
	 * Add to the document head all element js files
	 * used in calendar to ensure all element js files are loaded from unserialized form
	 *
	 * @return void
	 */

	public function loadJS()
	{
		JHtml::_('script', 'media/com_fabrik/js/head/head.min.js');
		$plugins = JFolder::folders(JPATH_SITE . '/plugins/fabrik_element', '.', false, false);
		$files = array();
		foreach ($plugins as $plugin)
		{
			$files[] = JPATH_SITE . '/plugins/fabrik_element/' . $plugin . '/' . $plugin . '.js';
		}
		foreach ($files as $f)
		{
			$f = str_replace("\\", "/", str_replace(JPATH_SITE, '', $f));
			$file = basename($f);
			$folder = dirname($f);
			$folder = FabrikString::ltrimword($folder, '/') . '/';
			FabrikHelperHTML::script($folder . $file);
		}
	}

	/**
	 * Loads ABSTRACT version of a plugin group
	 *
	 * @param   string  $group  plugin type - element/form/list/cron/validationrule supported
	 *
	 * @return  array
	 */

	protected function &loadPlugInGroup($group)
	{
		// $$$ rob 16/12/2011 - this was setting $this->plugIns, but if you had 2 lists as admin modules
		// and the first list had plugins, then the second list would remove that plugin when this method was run
		$folder = 'fabrik_' . $group;
		$this->_AbstractplugIns[$group] = array();
		$plugins = JPluginHelper::getPlugin($folder);
		foreach ($plugins as $plugin)
		{
			$this->_AbstractplugIns[$group][$plugin->name] = $plugin;
		}
		return $this->_AbstractplugIns[$group];
	}

	/**
	 * Load an indivdual plugin
	 *
	 * @param   string  $className  plugin name e.g. fabrikfield
	 * @param   string  $group      plugin type element/ form or list
	 *
	 * @return  object	plugin
	 */

	public function getPlugIn($className = '', $group = '')
	{
		if ($className != '' && (array_key_exists($group, $this->plugIns) && array_key_exists($className, $this->plugIns[$group])))
		{
			return $this->plugIns[$group][$className];
		}
		else
		{
			// $$$ rob 04/06/2011 hmm this was never caching the plugin so we were always loading it
			// return $this->loadPlugIn($className, $group);
			$this->plugIns[$group][$className] = $this->loadPlugIn($className, $group);
			return $this->plugIns[$group][$className];
		}
	}

	/**
	 * Load in the actual plugin objects for a given group
	 *
	 * @param   string  $group  plugin group
	 *
	 * @return  array	plugins
	 */

	public function getPlugInGroupPlugins($group)
	{
		$plugins = $this->getPlugInGroup($group);
		$r = array();
		foreach ($plugins as $plugin)
		{
			$r[] = $this->loadPlugIn($plugin->name, $group);
		}
		return $r;
	}

	/**
	 * Load plugin
	 *
	 * @param   string  $className  plugin name e.g. fabrikfield
	 * @param   string  $group      plugin type element/ form or list
	 *
	 * @return  mixed	false if not loaded - otherwise plugin object
	 */

	public function loadPlugIn($className = '', $group = '')
	{
		if ($group == 'table')
		{
			$group = 'list';
		}
		$group = JString::strtolower($group);
		/* $$$ rob ONLY import the actual plugin you need otherwise ALL $group plugins are loaded regardless of whether they
		* are used or not memory changes:
		* Application 0.322 seconds (+0.081); 22.92 MB (+3.054) - pluginmanager: form email imported
		* Application 0.242 seconds (+0.005); 20.13 MB (+0.268) - pluginmanager: form email imported
		*/
		$ok = JPluginHelper::importPlugin('fabrik_' . $group, $className);
		$dispatcher = JDispatcher::getInstance();
		if ($className != '')
		{
			$file = JPATH_PLUGINS . '/fabrik_' . $group . '/' . $className . '/' . $className . '.php';
			if (JFile::exists($file))
			{
				require_once $file;
			}
			else
			{
				$file = JPATH_PLUGINS . '/fabrik_' . $group . '/' . $className . '/models/' . $className . '.php';
				if (JFile::exists($file))
				{
					require_once $file;
				}
				else
				{
					JError::raiseNotice(500, 'plugin manager: did not load ' . $group . '.' . $className);
					return false;
				}
			}
		}
		$class = 'plgFabrik_' . JString::ucfirst($group) . JString::ucfirst($className);
		$conf = array();
		$conf['name'] = JString::strtolower($className);
		$conf['type'] = JString::strtolower('fabrik_' . $group);
		$plugIn = new $class($dispatcher, $conf);

		// Needed for viz
		$client = JApplicationHelper::getClientInfo(0);
		$lang = JFactory::getLanguage();
		$folder = 'fabrik_' . $group;
		$langFile = 'plg_' . $folder . '_' . $className;
		$langPath = $client->path . '/plugins/' . $folder . '/' . $className;
		$lang->load($langFile, $langPath, null, false, false) || $lang->load($langFile, $langPath, $lang->getDefault(), false, false);
		return $plugIn;
	}

	/**
	 * Unset a form's element plugins
	 *
	 * @since   3.1b
	 *
	 * @param   JModel  $formModel  Form model
	 *
	 * @return  void
	 */

	public function clearFormPlugins($formModel)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$sig = $package . '.' . $formModel->get('id');
		unset($this->formPlugins[$sig]);

	}
	/**
	 * Load all the forms element plugins
	 *
	 * @param   object  &$form  form model
	 *
	 * @return  array	of group objects with plugin objects loaded in group->elements
	 */

	public function getFormPlugins(&$form)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$profiler = JProfiler::getInstance('Application');
		if (!isset($this->formPlugins))
		{
			$this->formPlugins = array();
		}

		// Ensure packages load their own form
		$sig = $package . '.' . $form->get('id');
		JDEBUG ? $profiler->mark('pluginmanager:getFormPlugins:start - ' . $sig) : null;
		if (!array_key_exists($sig, $this->formPlugins))
		{
			$this->formPlugins[$sig] = array();
			$lang = JFactory::getLanguage();
			$folder = 'fabrik_element';
			$client = JApplicationHelper::getClientInfo(0);
			$groupIds = $form->getGroupIds();
			if (empty($groupIds))
			{
				// New form
				return array();
			}
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$select = '*, e.name AS name, e.id AS id, e.published AS published, e.label AS label,'
			. 'e.plugin, e.params AS params, e.access AS access, e.ordering AS ordering';
			$query->select($select);
			$query->from('#__{package}_elements AS e');
			$query->join('INNER', '#__extensions AS p ON p.element = e.plugin');
			$query->where('group_id IN (' . implode(',', $groupIds) . ')');
			$query->where('p.folder = "fabrik_element"');

			// Ignore trashed elements
			$query->where('e.published != -2');
			$query->order("group_id, e.ordering");
			$db->setQuery($query);

			$elements = $db->loadObjectList();

			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->getErrorMsg());
			}

			// Don't assign the elements into Joomla's main dispatcher as this causes out of memory errors in J1.6rc1
			$dispatcher = new JDispatcher;
			$groupModels = $form->getGroups();
			$group = 'element';
			foreach ($elements as $element)
			{
				JDEBUG ? $profiler->mark('pluginmanager:getFormPlugins:' . $element->id . '' . $element->plugin) : null;
				require_once JPATH_PLUGINS . '/fabrik_element/' . $element->plugin . '/' . $element->plugin . '.php';
				$class = 'PlgFabrik_Element' . $element->plugin;
				$pluginModel = new $class($dispatcher, array());
				if (!is_object($pluginModel))
				{
					continue;
				}
				$pluginModel->_xmlPath = COM_FABRIK_FRONTEND . '/plugins/' . $group . '/' . $element->plugin . '/' . $element->plugin . '.xml';

				$pluginModel->setId($element->id);
				$groupModel = $groupModels[$element->group_id];

				$langFile = 'plg_' . $folder . '_' . $element->plugin;
				$langPath = $client->path . '/plugins/' . $folder . '/' . $element->plugin;
				$lang->load($langFile, $langPath, null, false, false) || $lang->load($langFile, $langPath, $lang->getDefault(), false, false);

				$listModel = $form->getListModel();
				$pluginModel->setContext($groupModel, $form, $listModel);
				$pluginModel->bindToElement($element);
				$groupModel->elements[$pluginModel->getId()] = $pluginModel;
			}
			foreach ($groupModels as $groupid => $g)
			{
				$this->formPlugins[$sig][$groupid] = $g;
			}
		}
		return $this->formPlugins[$sig];
	}

	/**
	 * Short cut to get an element plugin
	 *
	 * @param   int  $id  element id
	 *
	 * @return object  element plugin
	 */

	public function getElementPlugin($id)
	{
		return $this->getPluginFromId($id);
	}

	/**
	 * Get a plugin based on its id
	 *
	 * @param   int     $id    plugin id
	 * @param   string  $type  plugin type
	 *
	 * @return object  plugin
	 */

	public function getPluginFromId($id, $type = 'Element')
	{
		$el = FabTable::getInstance($type, 'FabrikTable');
		$el->load($id);
		$o = $this->loadPlugIn($el->plugin, $type);
		$o->setId($id);
		switch ($type)
		{
			default:
				$o->getTable();
				break;
			case 'Element':
				$o->getElement();
				break;
		}
		return $o;
	}

	/**
	 * not used
	 *
	 * @param   string  $group          name of plugin group to load
	 * @param   array   $lists          list of default element lists
	 * @param   array   &$elementModel  list of default and plugin element lists
	 *
	 * @deprecated
	 *
	 * @return  void
	 */

	protected function loadLists($group, $lists, &$elementModel)
	{
	}

	/**
	 * Run form & element plugins - yeah!
	 *
	 * @param   string  $method        to check and call - corresponds to stage of form processing
	 * @param   object  &$parentModel  model calling the plugin form/list
	 * @param   string  $type          plugin type to call form/list
	 *
	 * @return  array	of bools: false if error found and processed, otherwise true
	 */

	public function runPlugins($method, &$parentModel, $type = 'form')
	{
		if ($type == 'form')
		{
			/**
			 * $$$ rob allow for table plugins to hook into form plugin calls - methods are mapped as:
			 * form method = 'onLoad' => table method => 'onFormLoad'
			 */
			$tmethod = 'onForm' . FabrikString::ltrimword($method, 'on');
			$listModel = $parentModel->getListModel();
			$this->runPlugins($tmethod, $listModel, 'list');
		}
		$params = $parentModel->getParams();
		$return = array();
		$usedPlugins = (array) $params->get('plugins');
		$usedLocations = (array) $params->get('plugin_locations');
		$usedEvents = (array) $params->get('plugin_events');
		$states = (array) $params->get('plugin_state');
		$this->_data = array();
		if ($type != 'list')
		{
			if (method_exists($parentModel, 'getGroupsHiarachy'))
			{
				$groups = $parentModel->getGroupsHiarachy();
				foreach ($groups as $groupModel)
				{
					$elementModels = $groupModel->getPublishedElements();
					foreach ($elementModels as $elementModel)
					{
						if (method_exists($elementModel, $method))
						{
							$elementModel->$method($parentModel);
						}
					}
				}
			}
		}
		$c = 0;
		$runPlugins = 0;
		/**
		 * if true then a plugin has returned true from runAway()
		 * which means that any other plugin in the same group should not be run.
		 */
		$runningAway = false;
		foreach ($usedPlugins as $usedPlugin)
		{
			if ($runningAway)
			{
				// "I soiled my armour I was so scared!"
				break;
			}
			$state = JArrayHelper::getValue($states, $c, 1);
			if ($state == false)
			{
				$c++;
				continue;
			}
			if ($usedPlugin != '')
			{
				$plugin = $this->getPlugIn($usedPlugin, $type);

				// Testing this if statement as onLoad was being called on form email plugin when no method availbale
				if (method_exists($plugin, $method))
				{
					$plugin->renderOrder = $c;
					$modelTable = $parentModel->getTable();
					$pluginParams = $plugin->setParams($params, $c);
					$location = JArrayHelper::getValue($usedLocations, $c);
					$event = JArrayHelper::getValue($usedEvents, $c);
					if ($plugin->canUse($parentModel, $location, $event))
					{
						$pluginArgs = array();
						if (func_num_args() > 3)
						{
							$t = func_get_args();
							$pluginArgs = array_splice($t, 3);
						}
						$preflightMethod = $method . '_preflightCheck';
						$preflightCheck = method_exists($plugin, $preflightMethod) ? $plugin->$preflightMethod($pluginParams, $parentModel, $pluginArgs)
							: true;
						if ($preflightCheck)
						{
							$ok = $plugin->$method($pluginParams, $parentModel, $pluginArgs);
							if ($ok === false)
							{
								$return[] = false;
							}
							else
							{
								$thisreturn = $plugin->customProcessResult($method, $parentModel);
								$return[] = $thisreturn;
								$m = $method . '_result';
								if (method_exists($plugin, $m))
								{
									$this->_data[] = $plugin->$m($c);
								}
							}
							$runPlugins++;
							if ($plugin->runAway($method))
							{
								$runningAway = true;
							}
							$mainData = $this->_data;
							if ($type == 'list' && $method !== 'observe')
							{
								$this->runPlugins('observe', $parentModel, 'list', $plugin, $method);
							}
							$this->_data = $mainData;
						}
					}
				}
				$c++;
			}
		}
		$this->runPlugins = $runPlugins;
		return array_unique($return);
	}

	/**
	 * Test if a plugin is installed
	 *
	 * @param   string  $group   plugin group
	 * @param   string  $plugin  plguin name
	 *
	 * @return  bool
	 */

	public function pluginExists($group, $plugin)
	{
		$plugins = $this->loadPlugInGroup($group);
		if (in_array($plugin, array_keys($plugins)))
		{
			return true;
		}
		return false;
	}

}
