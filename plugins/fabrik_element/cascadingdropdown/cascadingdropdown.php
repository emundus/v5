<?php
/**
 * Plugin element to render cascading dropdown
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.cascadingdropdown
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/plugins/fabrik_element/databasejoin/databasejoin.php';

/**
 * Plugin element to render cascading dropdown
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.cascadingdropdown
 * @since       3.0
 */

class plgFabrik_ElementCascadingdropdown extends plgFabrik_ElementDatabasejoin
{

	/**
	 * J Paramter name for the field containing the label value
	 *
	 * @var string
	 */
	protected $labelParam = 'cascadingdropdown_label';

	/**
	 * J Parameter name for the field containiing the concat label
	 *
	 * @var string
	 */
	protected $concatLabelParam = 'cascadingdropdown_label_concat';

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		if ($this->getDisplayType() == 'auto-complete')
		{
			$autoOpts = array();
			$autoOpts['observerid'] = $this->getWatchId($repeatCounter);
			FabrikHelperHTML::autoComplete($id, $this->getElement()->id, 'cascadingdropdown', $autoOpts);
		}
		FabrikHelperHTML::script('media/com_fabrik/js/lib/Event.mock.js');
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->showPleaseSelect = $this->showPleaseSelect();
		$opts->watch = $this->getWatchId($repeatCounter);
		$opts->displayType = $params->get('cdd_display_type', 'dropdown');
		$opts->id = $this->getId();

		// This bizarre chunk of code handles the case of setting a CDD value on the QS on a new form
		$rowid = $input->getInt('rowid', 0);
		$fullName = $this->getFullName(false, true, true);
		$watchName = $this->getWatchFullName();
		$qsValue = $input->get($fullName, '');
		$qsWatchValue = $input->get($watchName, '');

		// $$$ hugh - Rob, is there a better way of finding out if validation has failed than looking at _arErrors?
		$opts->def = empty($this->getFormModel()->_arErrors) && $this->isEditable() && $rowid == 0 && !empty($qsValue) && !empty($qsWatchValue) ? $qsValue : $this->getValue(array(), $repeatCounter);

		// $$$ hugh - for reasons utterly beyond me, after failed validation, getValue() is returning an array.
		if (is_array($opts->def))
		{
			$opts->def = $opts->def[0];
		}
		$watchGroup = $this->getWatchElement()->getGroup()->getGroup();
		$group = $this->getGroup()->getGroup();
		$opts->watchInSameGroup = $watchGroup->id === $group->id;
		$opts->editing = ($this->isEditable() && JRequest::getInt('rowid', 0) != 0);
		$opts->showDesc = $params->get('cdd_desc_column', '') === '' ? false : true;
		$this->elementJavascriptJoinOpts($opts);
		$opts = json_encode($opts);
		return "new FbCascadingdropdown('$id', $opts)";
	}

	/**
	 * Get the field name to use as the column that contains the join's label data
	 *
	 * @param   bool  $useStep  use step in element name
	 *
	 * @return	string join label column either returns concat statement or quotes `tablename`.`elementname`
	 */

	public function getJoinLabelColumn($useStep = false)
	{
		$params = $this->getParams();
		$app = JFactory::getApplication();
		$join = $this->getJoin();
		$db = $this->getDb();
		if (($params->get('cascadingdropdown_label_concat') != '') && $app->input->get('overide_join_val_column_concat') != 1)
		{
			$val = str_replace("{thistable}", $join->table_join_alias, $params->get('cascadingdropdown_label_concat'));
			return 'CONCAT(' . $val . ')';
		}
		$label = FabrikString::shortColName($join->_params->get('join-label'));

		if ($label == '')
		{
			// This is being raised with checkbox rendering and using dropdown filter, everything seems to be working with using hte element name though!
			// JError::raiseWarning(500, 'Could not find the join label for ' . $this->getElement()->name . ' try unlinking and saving it');
			$label = $this->getElement()->name;
		}
		$joinTableName = $join->table_join_alias;
		return $useStep ? $joinTableName . '___' . $label : $db->quoteName($joinTableName) . '.' . $db->quoteName($label);
	}

	/**
	 * Reset cached data, needed when rendering table if CDD
	 * is in repeat group, so we can build optionVals
	 *
	 * @deprecated - not used
	 *
	 * @return  void
	 */

	protected function _resetCache()
	{
		unset($this->_optionVals);
		unset($this->_sql);
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$db = $this->getDb();
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$element = $this->getElement();
		$name = $this->getHTMLName($repeatCounter);
		$default = (array) $this->getValue($data, $repeatCounter);

		/* $$$ rob don't bother getting the options if editable as the js event is going to get them.
		 * However if in readonly mode the we do need to get the options
		 * $$$ hugh - need to rethink this approach, see ticket #725. When editing, we need
		 * to build options and selection on server side, otherwise daisy chained CDD's don't
		 * work due to timing issues in JS between onComplete and get_options calls.
		 * $tmp = 	$this->isEditable() ? array() : $this->_getOptions($data);
		 * So ... we want to get options if not editable, or if editing an existing row.
		 * See also small change to attachedToForm() in JS, and new 'editing' option in
		 * elementJavascript() above, so the JS won't get options on init when editing an existing row
		 */
		$tmp = array();
		$rowid = $app->input->getInt('rowid', 0);
		$show_please = $this->showPleaseSelect();

		// $$$ hugh testing to see if we need to load options after a validation failure, but I don't think we do, as JS will reload via AJAX
		//if (!$this->isEditable() || ($this->isEditable() && $rowid != 0) || !empty($this->getFormModel()->_arErrors))
		if (!$this->isEditable() || ($this->isEditable() && $rowid != 0))
		{
			$tmp = $this->_getOptions($data, $repeatCounter);
		}
		else
		{
			if ($show_please)
			{
				$tmp[] = JHTML::_('select.option', '', $this->_getSelectLabel());
			}
		}
		$imageOpts = array('alt' => JText::_('PLG_ELEMENT_CALC_LOADING'), 'style' => 'display:none;padding-left:10px;', 'class' => 'loader');
		$this->loadingImg = FabrikHelperHTML::image("ajax-loader.gif", 'form', @$this->tmpl, $imageOpts);

		// Get the default label for the drop down (use in read only templates)
		$defaultLabel = '';
		$defaultValue = '';
		foreach ($tmp as $obj)
		{
			if (in_array($obj->value, $default))
			{
				$defaultValue = $obj->value;
				$defaultLabel = $obj->text;
				break;
			}
		}
		$id = $this->getHTMLId($repeatCounter);
		$class = "fabrikinput inputbox";
		if (count($tmp) == 1)
		{
			$class .= " readonly";
			$disabled = 'readonly="readonly"';
		}
		else
		{
			$disabled = '';
		}

		$w = new FabrikWorker;
		foreach ($default as &$d)
		{
			$d = $w->parseMessageForPlaceHolder($d);
		}
		// Not yet implemented always going to use dropdown for now
		$displayType = $params->get('cdd_display_type', 'dropdown');
		$html = array();
		if ($this->canUse())
		{
			// $$$ rob display type not set up in parameters as not had time to test fully yet

			switch ($displayType)
			{
				case 'checkbox':
					$this->renderCheckBoxList($data, $repeatCounter, $html, $tmp, $default);
					break;
				case 'radio':
					$this->renderRadioList($data, $repeatCounter, $html, $tmp, $defaultValue);
					break;
				case 'multilist':
					$this->renderMultiSelectList($data, $repeatCounter, $html, $tmp, $default);
					$defaultLabel = implode("\n", $html);
					break;
				case 'auto-complete':
					$this->renderAutoComplete($data, $repeatCounter, $html, $default);
					break;
				default:
				case 'dropdown':
					$attribs = 'class="' . $class . '" ' . $disabled . ' size="1"';
					$html[] = JHTML::_('select.genericlist', $tmp, $name, $attribs, 'value', 'text', $default, $id);
					break;
			}
			$html[] = $this->loadingImg;
			$html[] = ($displayType == "radio") ? "</div>" : '';
		}

		if (!$this->isEditable())
		{
			if ($params->get('cascadingdropdown_readonly_link') == 1)
			{
				$listid = (int) $params->get('cascadingdropdown_table');
				if ($listid !== 0)
				{
					$query = $db->getQuery(true);
					$query->select('form_id')->from('#__{package}_lists')->where('id = ' . $listid);
					$db->setQuery($query);
					$popupformid = $db->loadResult();
					$url = 'index.php?option=com_fabrik&view=details&formid=' . $popupformid . '&listid=' . $listid . '&rowid=' . $defaultValue;
					$defaultLabel = '<a href="' . JRoute::_($url) . '">' . $defaultLabel . '</a>';
				}
			}
			return $defaultLabel . $this->loadingImg;
		}

		if ($params->get('cdd_desc_column', '') !== '')
		{
			$html[] = '<div class="dbjoin-description">';
			for ($i = 0; $i < count($this->_optionVals); $i++)
			{
				$opt = $this->_optionVals[$i];
				$display = in_array($opt->value, $default) ? '' : 'none';
				$c = $i + 1;
				$html[] = '<div style="display:' . $display . '" class="notice description-' . $c . '">' . $opt->description . '</div>';
			}
			$html[] = '</div>';
		}
		return implode("\n", $html);
	}

	/**
	 * Does the element store its data in a join table (1:n)
	 *
	 * @return	bool
	 */

	public function isJoin()
	{
		$params = $this->getParams();
		if (in_array($this->getDisplayType(), array('checkbox', 'multilist')))
		{
			return true;
		}
		else
		{
			return parent::isJoin();
		}
	}

	/**
	 * Get the display type (list,checkbox,mulitselect etc)
	 *
	 * @since  3.0.7
	 *
	 * @return  string
	 */

	protected function getDisplayType()
	{
		return $this->getParams()->get('cdd_display_type', 'dropdown');
	}

	/**
	 * Get a list of the HTML options used in the database join drop down / radio buttons
	 *
	 * @param   array  $data           From current record (when editing form?)
	 * @param   int    $repeatCounter  Repeat group counter
	 * @param   bool   $incWhere       Do we include custom where in query
	 * @param   array  $opts           Additional optiosn passed intto _getOptionVals()
	 *
	 * @return  array	option objects
	 */

	protected function _getOptions($data = array(), $repeatCounter = 0, $incWhere = true, $opts = array())
	{
		$this->getDb();
		$tmp = $this->_getOptionVals($data, $repeatCounter);
		return $tmp;
	}

	/**
	 * Gets the options for the drop down - used in package when forms update
	 *
	 * @return  void
	 */

	public function onAjax_getOptions()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$filterview = $app->input->get('filterview', '');
		$this->loadMeForAjax();
		$params = $this->getParams();

		/**
		 * $$$ hugh -added test for $filterview, and only do filtery stuff if we are being
		 * calledin a filter context, not in a regular form display context.
		 */

		if (!empty($filterview) && $this->getFilterBuildMethod() == 1)
		{
			// Get distinct records which have already been selected: http://fabrikar.com/forums/showthread.php?t=30450
			$listModel = $this->getListModel();
			$db = $listModel->getDb();
			$query = $db->getQuery(true);
			$obs = $this->getWatchElement();
			$obsName = $obs->getElement()->name;
			$obsValue = $input->get($obs->getFullName(false, true, false) . '_raw');
			$element = $this->getElement();
			$tblName = $listModel->getTable()->db_table_name;
			$query->select('DISTINCT ' . $element->name)->from($tblName)->where($obsName . ' = ' . $db->quote($obsValue));
			$db->setQuery($query);
			$ids = $db->loadColumn();
			$key = $this->queryKey();
			if (is_array($ids))
			{
				array_walk($ids, create_function('&$val', '$db = JFactory::getDbo();$val = $db->quote($val);'));
				$this->_autocomplete_where = empty($ids) ? '1 = -1' : $key . ' IN (' . implode(',', $ids) . ')';
			}
		}

		$filter = JFilterInput::getInstance();
		$data = $filter->clean($_POST, 'array');
		$opts = $this->_getOptionVals($data);

		// OK its due to list filters so lets test if we are in the table view (posted from filter.js)
		if ($filterview == 'table')
		{
			$params->set('cascadingdropdown_showpleaseselect', true);
		}
		$this->_replaceAjaxOptsWithDbJoinOpts($opts);
		echo json_encode($opts);
	}

	/**
	 * Test for db join element - if so update option labels with related join labels
	 *
	 * @param   array  &$opts  standard options
	 *
	 * @return  void
	 */

	protected function _replaceAjaxOptsWithDbJoinOpts(&$opts)
	{
		$groups = $this->getFormModel()->getGroupsHiarachy();
		$watch = $this->getWatchFullName();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$fullName = $elementModel->getFullName(false, true, true);
				if ($fullName == $watch)
				{
					$element = $elementModel->getElement();
					if (get_parent_class($elementModel) == 'FabrikModelFabrikDatabasejoin')
					{
						$data = array();
						$joinopts = $elementModel->_getOptions($data);
					}
				}
			}
		}
		if (isset($joinopts))
		{
			$matrix = array();
			foreach ($joinopts as $j)
			{
				$matrix[$j->value] = $j->text;
			}
			foreach ($opts as &$opt)
			{
				if (array_key_exists($opt->text, $matrix))
				{
					$opt->text = $matrix[$opt->text];
				}
			}
		}
	}

	/**
	 * Get array of option values
	 *
	 * @param   array  $data           Data
	 * @param   int    $repeatCounter  Repeat group counter
	 * @param   bool   $incWhere       Do we add custom where statement into sql
	 * @param   array  $opts           Addtional options passed into _buildQuery()
	 *
	 * @return  array	option values
	 */

	protected function _getOptionVals($data = array(), $repeatCounter = 0, $incWhere = true, $opts = array())
	{
		$params = $this->getParams();
		if (!isset($this->_optionVals))
		{
			$this->_optionVals = array();
		}
		$db = $this->getDb();
		$opts = array();
		$opts['repeatCounter'] = $repeatCounter;
		$sql = $this->_buildQuery($data, $incWhere, $opts);
		$sqlKey = (string) $sql;
		$db->setQuery($sql);
		if (array_key_exists($sqlKey, $this->_optionVals))
		{
			return $this->_optionVals[$sqlKey];
		}

		FabrikHelperHTML::debug($db->getQuery(), 'cascadingdropdown _getOptionVals');
		$this->_optionVals[$sqlKey] = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			JError::raiseError(501, $db->getErrorMsg());
		}

		$eval = $params->get('cdd_join_label_eval', '');
		if (trim($eval) !== '')
		{
			foreach ($this->_optionVals[$sqlKey] as $key => &$opt)
			{
				// Allow removing an option by returning false
				if (eval($eval) === false)
				{
					unset($this->_optionVals[$sqlKey][$key]);
				}
			}
		}

		if ($this->showPleaseSelect())
		{
			array_unshift($this->_optionVals[$sqlKey], JHTML::_('select.option', '', $this->_getSelectLabel()));
		}
		return $this->_optionVals[$sqlKey];
	}

	/**
	 * Do you add a please select option to the cdd list
	 *
	 * @since 3.0b
	 *
	 * @return  bool
	 */

	protected function showPleaseSelect()
	{
		$params = $this->getParams();
		$app = JFactory::getApplication();
		if (!$this->canUse())
		{
			return false;
		}
		if (!$this->isEditable() && $app->input->get('method') !== 'ajax_getOptions')
		{
			return false;
		}
		if (in_array($this->getDisplayType(), array('checkbox', 'multilist', 'radio')))
		{
			return false;
		}
		return (bool) $params->get('cascadingdropdown_showpleaseselect', true);
	}

	/**
	 * Get the full name of the element to observe. When this element changes
	 * state, the cdd should perform an ajax lookup to update its options
	 *
	 * @return  string
	 */

	protected function getWatchFullName()
	{
		$listModel = $this->getlistModel();
		$elementModel = $this->getWatchElement();
		return $elementModel->getFullName();
	}

	/**
	 * Get the HTML id for the watch element
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	protected function getWatchId($repeatCounter = 0)
	{
		$listModel = $this->getlistModel();
		$elementModel = $this->getWatchElement();
		return $elementModel->getHTMLId($repeatCounter);
	}

	/**
	 * Get the element to watch. Changes to this element will trigger the cdd's lookup
	 *
	 * @return  plgFabrik_Element
	 */

	protected function getWatchElement()
	{
		if (!isset($this->watchElement))
		{
			$watch = $this->getParams()->get('cascadingdropdown_observe');
			if ($watch == '')
			{
				JError::raiseError(500, 'No watch element set up for cdd' . $this->getElement()->id);
			}

			$this->watchElement = $this->getFormModel()->getElement($watch, true);
			if (!$this->watchElement)
			{
				// This element is a child element, so $watch is in the parent element (in another form)
				$pluginManager = FabrikWorker::getPluginManager();
				$parent = $pluginManager->getElementPlugin($watch);

				// These are the possible watch elements
				$children = $parent->getElementDescendents();

				// Match the possible element ids with the current form's element ids
				$elids = $this->getFormModel()->getElementIds();
				$matched = array_values(array_intersect($elids, $children));

				// Load the matched element
				$this->watchElement = $pluginManager->getElementPlugin($matched[0]);
			}
		}
		return $this->watchElement;
	}

	/**
	 * Create the sql query used to get the join data
	 *
	 * @param   array  $data      data
	 * @param   bool   $incWhere  include where
	 * @param   array  $opts      query options
	 *
	 * @return  mixed	JDatabaseQuery or false if query can't be built
	 */

	protected function _buildQuery($data = array(), $incWhere = true, $opts = array())
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$sig = isset($this->_autocomplete_where) ? $this->_autocomplete_where . '.' . $incWhere : $incWhere;
		$sig .= '.' . serialize($opts);
		$repeatCounter = JArrayHelper::getValue($opts, 'repeatCounter', 0);
		$db = FabrikWorker::getDbo();
		if (isset($this->_sql[$sig]))
		{
			return $this->_sql[$sig];
		}
		$params = $this->getParams();
		$element = $this->getElement();

		$watch = $this->getWatchFullName();
		$whereval = null;
		$groups = $this->getForm()->getGroupsHiarachy();

		$formModel = $this->getFormModel();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$fullName = $elementModel->getFullName(true, true, true);
				if ($fullName == $watch)
				{
					// Test for ajax update
					if ($input->get('fabrik_cascade_ajax_update') == 1)
					{

						// Allow for multiple values - e.g. when observing a db join rendered as a checkbox
						$whereval = $input->get('v', array(), 'array');
					}
					else
					{
						if (isset($formModel->_data) || isset($formModel->_formData))
						{
							if (isset($formModel->_data))
							{
								$whereval = $elementModel->getValue($formModel->_data, $repeatCounter);
							}
							else
							{
								$whereval = $elementModel->getValue($formModel->_formData, $repeatCounter);
							}
							/* $$$ hugh - temporary bandaid to fix 'array' issue in view=details
							 * @TODO fix underlying cause in database join getValue
							 * http://fabrikar.com/forums/showthread.php?p=63512#post63512
							 */
							if (is_array($whereval))
							{
								$whereval = JArrayHelper::getValue($whereval, 0);
							}
							// $$$ hugh - if not set, set to '' to avoid selecting entire table
							elseif (!isset($whereval))
							{
								$whereval = '';
							}
						}
						else
						{
							// $$$ hugh - prolly rendering table view ...
							$watch_raw = $watch . '_raw';
							if (isset($data[$watch_raw]))
							{
								$whereval = $data[$watch_raw];
							}
							else
							{
								// $$$ hugh ::sigh:: might be coming in via swapLabelsForvalues in pre_process phase
								// and join array in data will have been flattened.  So try regular element name for watch.
								$no_join_watch_raw = $elementModel->getFullName(false, true, false) . '_raw';
								if (isset($data[$no_join_watch_raw]))
								{
									$whereval = $data[$no_join_watch_raw];
								}
								else
								{
									// $$$ hugh - if watched element has no value, we have been selecting all rows from CDD table
									// but should probably select none.

									// Unless its a cdd autocomplete list filter - seems sensible to populate that with the values matching the search term
									if ($app->input->get('method') !== 'autocomplete_options')
									{
										$whereval = '';
									}
								}
							}
						}
					}
					continue 2;
				}
			}
		}

		$where = '';
		$wherekey = $params->get('cascadingdropdown_key');
		if (!is_null($whereval) && $wherekey != '')
		{
			$whereBits = explode('___', $wherekey);
			$wherekey = array_pop($whereBits);
			if (is_array($whereval))
			{
				foreach ($whereval as &$v)
				{
					$v = $db->quote($v);
				}
				$where .=  count($whereval) == 0 ? '1 = -1' : $wherekey . ' IN (' . implode(',', $whereval) . ')';
			}
			else
			{
				$where .= $wherekey . ' = ' .$db->quote($whereval);
			}

		}
		$filter = $params->get('cascadingdropdown_filter');

		/* $$$ hugh - temporary hack to work around this issue:
		 * http://fabrikar.com/forums/showthread.php?p=71288#post71288
		 * ... which is basically that if they are using {placeholders} in their
		 * filter query, there's no point trying to apply that filter if we
		 * aren't in form view, for instance when building a search filter
		 * or in table view when the cdd is in a repeat group, 'cos there won't
		 * be any {placeholder} data to use.
		 * So ... for now, if the filter contains {...}, and view!=form ... skip it
		 * $$$ testing fix for the bandaid, ccd JS should not be submitting data from form
		 */
		if (trim($filter) != '')
		{
			$where .= ($where == '') ? ' ' : ' AND ';
			$where .= $filter;
		}
		$w = new FabrikWorker;

		// $$$ hugh - add some useful stuff to search data
		if (!is_null($whereval))
		{
			$placeholders = array('whereval' => $whereval, 'wherekey' => $wherekey);
		}
		else
		{
			$placeholders = array();
		}
		$join = $this->getJoin();
		$where = str_replace("{thistable}", $join->table_join_alias, $where);

		if (!empty($this->_autocomplete_where))
		{
			$where .= $where !== '' ? ' AND ' . $this->_autocomplete_where : $this->_autocomplete_where;

		}
		$data = array_merge($data, $placeholders);
		$where = $w->parseMessageForPlaceHolder($where, $data);

		$table = $this->getDbName();

		$key = $this->queryKey();
		$orderby = 'text';
		$tables = $this->getForm()->getLinkedFabrikLists($params->get('join_db_name'));
		$listModel = JModel::getInstance('List', 'FabrikFEModel');
		$val = $params->get('cascadingdropdown_label_concat');
		if (!empty($val))
		{
			$val = str_replace("{thistable}", $join->table_join_alias, $val);
			$val = $w->parseMessageForPlaceHolder($val, $data);
			$val = 'CONCAT(' . $val . ')';
			$orderby = $val;
		}
		else
		{
			$val = FabrikString::safeColName($params->get($this->labelParam));
			$val = preg_replace("#^`($table)`\.#", $db->quoteName($join->table_join_alias) . '.', $val);
			foreach ($tables as $tid)
			{
				$listModel->setId($tid);
				$listModel->getTable();
				$formModel = $this->getForm();
				$formModel->getGroupsHiarachy();

				$orderby = $val;

				// See if any of the tables elements match the db joins val/text
				foreach ($groups as $groupModel)
				{
					$elementModels = $groupModel->getPublishedElements();
					foreach ($elementModels as $elementModel)
					{
						$element = $elementModel->_element;
						if ($element->name == $val)
						{
							$val = $elementModel->modifyJoinQuery($val);
						}
					}
				}
			}
		}
		$val = str_replace($db->quoteName($table), $db->quoteName($join->table_join_alias), $val);

		$query = $db->getQuery(true);
		$query->select('DISTINCT(' . $key . ') AS value, ' . $val . 'AS text');

		$desc = $params->get('cdd_desc_column', '');
		if ($desc !== '')
		{
			$query->select(FabrikString::safeColName($desc) . ' AS description');
		}

		$query->from($db->quoteName($table) . ' AS ' . $db->quoteName($join->table_join_alias));
		$query = $this->buildQueryJoin($query);
		$where = FabrikString::rtrimword($where);
		if ($where !== '')
		{
			$query->where($where);
		}
		if (!JString::stristr($where, 'order by'))
		{
			$query->order($orderby . ' ASC');
		}
		$this->_sql[$sig] = $query;
		FabrikHelperHTML::debug($this->_sql[$sig]);
		return $this->_sql[$sig];
	}

	/**
	 * Get the the field name used for the foo AS value part of the query
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */

	protected function queryKey()
	{
		$db = FabrikWorker::getDbo();
		$join = $this->getJoin();
		$table = $this->getDbName();
		$params = $this->getParams();
		$key = FabrikString::safeColName($params->get('cascadingdropdown_id'));
		$key = str_replace($db->quoteName($table), $db->quoteName($join->table_join_alias), $key);
		return $key;
	}
	/**
	 * Get the element name or concat statement used to build the dropdown labels or
	 * table data field
	 *
	 * @return  string
	 */

	protected function getValColumn()
	{
		$params = $this->getParams();
		$join = $this->getJoin();
		if ($params->get('cascadingdropdown_label_concat') == '')
		{
			// $$$ rob testing this - if 2 cdd's to same db think we need this change:
			/* $bits = explode('___', $params->get($this->labelParam));
			return $join->table_join_alias . '___' . $bits[1]; */

			return $this->getLabelParamVal();
		}
		else
		{
			$val = str_replace("{thistable}", $join->table_join_alias, $params->get('cascadingdropdown_label_concat'));
			return 'CONCAT(' . $val . ')';
		}
	}

	/**
	 * Load connection object
	 *
	 * @return  object	connection table
	 */

	protected function &_loadConnection()
	{
		$params = $this->getParams();
		$id = $params->get('cascadingdropdown_connection');
		$cid = $this->getlistModel()->getConnection()->getConnection()->id;
		if ($cid == $id)
		{
			$this->cn = $this->getlistModel()->getConnection();
		}
		else
		{
			$this->cn = JModel::getInstance('Connection', 'FabrikFEModel');
			$this->cn->setId($id);
		}
		return $this->cn->getConnection();
	}

	/**
	 * Get the cdd's database name
	 *
	 * @return  db name or false if unable to get name
	 */

	protected function getDbName()
	{
		if (!isset($this->dbname) || $this->dbname == '')
		{
			$params = $this->getParams();
			$id = $params->get('cascadingdropdown_table');
			if ($id == '')
			{
				JError::raiseWarning(500, 'Unable to get table for cascading dropdown (ignore if creating a new element)');
				return false;
			}
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('db_table_name')->from('#__{package}_lists')->where('id = ' . (int) $id);
			$db->setQuery($query);
			$this->dbname = $db->loadResult();
		}
		return $this->dbname;
	}

	/**
	 * If used as a filter add in some JS code to watch observed filter element's changes
	 * when it changes update the contents of this elements dd filter's options
	 *
	 * @param   bool    $normal     is the filter a normal (true) or advanced filter
	 * @param   string  $container  container
	 *
	 * @return  void
	 */

	public function filterJS($normal, $container)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$observerid = $this->getWatchId();
		$observerid .= 'value';
		if ($element->filter_type == 'auto-complete')
		{
			FabrikHelperHTML::autoCompleteScript();
			$htmlid = $this->getHTMLId() . 'value';
			$opts = new stdClass;
			$opts->observerid = $observerid;
			$app = JFactory::getApplication();
			$package = $app->getUserState('com_fabrik.package', 'com_fabrik');
			$opts->url = COM_FABRIK_LIVESITE . '/index.php?option=com_' . $package . '&format=raw&view=plugin&task=pluginAjax&g=element&element_id='
				. $element->id . '&plugin=cascadingdropdown&method=autocomplete_options&package=' . $package;
			$opts = json_encode($opts);

			FabrikHelperHTML::addScriptDeclaration("head.ready(function() { new FabCddAutocomplete('$htmlid', $opts); });");
		}
		if ($element->filter_type == 'dropdown')
		{
			$default = $this->getDefaultFilterVal($normal);
			$observed = $this->_getObserverElement();
			$filterid = $this->getHTMLId() . 'value';
			$formModel = $this->getForm();
			FabrikHelperHTML::script('plugins/fabrik_element/cascadingdropdown/filter.js');
			$opts = new stdClass;
			$opts->formid = $formModel->get('id');
			$opts->filterid = $filterid;
			$opts->elid = $this->getId();
			$opts->def = $default;
			$opts->filterobj = 'Fabrik.filter_' . $container;
			$opts = json_encode($opts);
			return "Fabrik.filter_{$container}.addFilter('$element->plugin', new CascadeFilter('$observerid', $opts));\n";
		}
	}

	/**
	 * Get the field name for the joined tables' pk
	 *
	 *  @since  3.0.7
	 *
	 * @return  string
	 */

	protected function getJoinValueFieldName()
	{
		$params = $this->getParams();
		$full = $params->get('cascadingdropdown_id');
		$bits = explode('___', $full);
		return JArrayHelper::getValue($bits, 1, $full);
	}

	/**
	 * Get the observed element's element model
	 *
	 * @return mixed element model or false
	 */

	protected function _getObserverElement()
	{
		$params = $this->getParams();
		$observer = $params->get('cascadingdropdown_observe');
		$formModel = $this->getForm();
		$groups = $formModel->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getMyElements();
			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();
				if ($observer == $element->name)
				{
					return $elementModel;
				}
			}
		}
		return false;
	}

	/**
	 * Run before the element is saved
	 *
	 * @param   object  &$row  that is going to be updated
	 *
	 * @return null
	 */

	public function beforeSave(&$row)
	{
		/*
		 * do nothing, just here to prevent join element method from running
		 * instead (which removed join table
		 * entry if not pluginname==databasejoin
		 */
		return true;
	}

	/**
	 * Get an array of element html ids and their corresponding
	 * js events which trigger a validation.
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  array  html ids to watch for validation
	 */

	public function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array('id' => $id, 'triggerEvent' => 'change');
		return array($ar);
	}

	/**
	 * When copying elements from an existing table
	 * once a copy of all elements has been made run them through this method
	 * to ensure that things like watched element id's are updated
	 *
	 * @param   array  $elementMap  copied element ids (keyed on original element id)
	 *
	 * @return  mixed JError:void
	 */

	public function finalCopyCheck($elementMap)
	{
		$element = $this->getElement();
		unset($this->_params);
		$params = $this->getParams();
		$oldObeserveId = $params->get('cascadingdropdown_observe');
		if (!array_key_exists($oldObeserveId, $elementMap))
		{
			JError::raiseWarning(E_ERROR, 'cascade dropdown: no id ' . $oldObeserveId . ' found in ' . implode(",", array_keys($elementMap)));
		}
		$newObserveId = $elementMap[$oldObeserveId];
		$params->set('cascadingdropdown_observe', $newObserveId);

		// Save params
		$element->params = $params->toString();
		if (!$element->store())
		{
			return JError::raiseWarning(500, $element->getError());
		}
	}

	/**
	 * Return an array of parameter names which should not get updated if a linked element's parent is saved
	 * notably any paramter which references another element id should be returned in this array
	 * called from admin element model updateChildIds()
	 * see cascadingdropdown element for example
	 *
	 * @return  array	parameter names to not alter
	 */

	public function getFixedChildParameters()
	{
		return array('cascadingdropdown_observe');
	}

	/**
	 * Get select option label
	 *
	 * @return  string
	 */

	protected function _getSelectLabel()
	{
		return $this->getParams()->get('cascadingdropdown_noselectionlabel', JText::_('COM_FABRIK_PLEASE_SELECT'));
	}

	/**
	 * Should the 'label' field be quoted.  Overridden by databasejoin and extended classes,
	 * which may use a CONCAT'ed label which musn't be quoted.
	 *
	 * @since	3.0.6
	 *
	 * @return boolean
	 */

	protected function quoteLabel()
	{
		$params = $this->getParams();
		return $params->get('cascadingdropdown_label_concat', '') == '';
	}

	/**
	* If filterValueList_Exact incjoin value = false, then this method is called
	* to ensure that the query produced in filterValueList_Exact contains at least the database join element's
	* join
	*
	* @return  string  required join text to ensure exact filter list code produces a valid query.
	*/

	protected function _buildFilterJoin()
	{
		$params = $this->getParams();
		$joinTable = FabrikString::safeColName($this->getDbName());
		$join = $this->getJoin();
		$joinTableName = FabrikString::safeColName($join->table_join_alias);
		$joinKey = $this->getJoinValueColumn();
		$elName = FabrikString::safeColName($this->getFullName(false, true, false));
		return 'INNER JOIN ' . $joinTable . ' AS ' . $joinTableName . ' ON ' . $joinKey . ' = ' . $elName;
	}

}
