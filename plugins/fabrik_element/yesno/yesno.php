<?php
/**
 * Plugin element to yes/no radio options - render as tick/cross in list view
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.yesno
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';
require_once JPATH_SITE . '/plugins/fabrik_element/radiobutton/radiobutton.php';

/**
 * Plugin element to yes/no radio options - render as tick/cross in list view
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.yesno
 * @since       3.0
 */

class plgFabrik_ElementYesno extends plgFabrik_ElementRadiobutton
{

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TINYINT(%s)';

	/**
	 * Db table field size
	 *
	 * @var string
	 */
	protected $fieldSize = '1';

	/**
	 * This really does get just the default value (as defined in the element's settings)
	 *
	 * @param   array  $data  to use as parsemessage for placeholder
	 *
	 * @return unknown_type
	 */

	function getDefaultValue($data = array())
	{
		if (!isset($this->_default))
		{
			$params = $this->getParams();
			$this->_default = $params->get('yesno_default', 0);
		}
		return $this->_default;
	}

	public function renderListData($data, &$thisRow)
	{
		FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'list', false);

		// Check if the data is in csv format, if so then the element is a multi drop down
		if ($data == '1')
		{
			return FabrikHelperHTML::image("1.png", 'list', @$this->tmpl, array('alt' => JText::_('JYES')));
		}
		else
		{
			return FabrikHelperHTML::image("0.png", 'list', @$this->tmpl, array('alt' => JText::_('JNO')));
		}
	}

	/**
	 * Shows the data formatted for the table view with format = pdf
	 * note pdf lib doesnt support transparent pngs hence this func
	 *
	 * @param   string  $data     cell data
	 * @param   object  $thisRow  row data
	 *
	 * @return string formatted value
	 */

	function renderListData_pdf($data, $thisRow)
	{
		FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'list', false);
		if ($data == '1')
		{
			return FabrikHelperHTML::image("1_8bit.png", 'list', $this->tmpl, array('alt' => JText::_('JYES')));
		}
		else
		{
			return FabrikHelperHTML::image("0_8bit.png", 'list', $this->tmpl, array('alt' => JText::_('JNO')));
		}
	}

	/**
	 * Shows the data formatted for CSV export
	 *
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData_csv($data, &$thisRow)
	{
		if ($data == '1')
		{
			return JText::_('JYES');
		}
		else
		{
			return JText::_('JNO');
		}
	}

	/**
	 * Get sub option values
	 *
	 * @return  array
	 */

	protected function getSubOptionValues()
	{
		return array(0, 1);
	}

	/**
	 * Get sub option labels
	 *
	 * @return  array
	 */

	protected function getSubOptionLabels()
	{
		return array(JText::_('JNO'), JText::_('JYES'));
	}

	/**
	 * Run after unmergeFilterSplits to ensure filter dropdown labels are correct
	 *
	 * @param   array  &$rows  filter options
	 *
	 * @return  null
	 */

	protected function reapplyFilterLabels(&$rows)
	{
		$element = $this->getElement();
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		foreach ($rows as &$row)
		{
			if ($row->value !== '')
			{
				$k = array_search($row->value, $values);
				if ($k !== false)
				{
					$row->text = $labels[$k];
				}
			}
		}
		$rows = array_values($rows);
	}

	/**
	 * @param array of scripts previously loaded (load order is important as we are loading via head.js
	 * and in ie these load async. So if you this class extends another you need to insert its location in $srcs above the
	 * current file
	 *
	 * get the class to manage the form element
	 * if a plugin class requires to load another elements class (eg user for dbjoin then it should
	 * call FabrikModelElement::formJavascriptClass('plugins/fabrik_element/databasejoin/databasejoin.js', true);
	 * to ensure that the file is loaded only once
	 */

	function formJavascriptClass(&$srcs, $script = '')
	{
		$elementList = 'media/com_fabrik/js/elementlist.js';
		if (!in_array($elementList, $srcs))
		{
			$srcs[] = $elementList;
		}
		$elementList = 'plugins/fabrik_element/radiobutton/radiobutton.js';
		if (!in_array($elementList, $srcs))
		{
			$srcs[] = $elementList;
		}
		parent::formJavascriptClass($srcs, $script);
	}

	/**
	 * format the read only output for the page
	 * @param string $value
	 * @param string label
	 * @return string value
	 */

	protected function getReadOnlyOutput($value, $label)
	{
		FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'form', false);
		$img = $value == '1' ? "1.png" : "0.png";
		return FabrikHelperHTML::image($img, 'form', @$this->tmpl, array('alt' => $label));
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbYesno('$id', $opts)";
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_ElementList::getFilter()
	 */

	public function getFilter($counter = 0, $normal = true)
	{
		$listModel = $this->getlistModel();
		$table = $listModel->getTable();
		$elName = $this->getFullName(false, true, false);
		$htmlid = $this->getHTMLId() . 'value';
		$elName = FabrikString::safeColName($elName);
		$v = 'fabrik___filter[list_' . $listModel->getRenderContext() . '][value]';
		$v .= ($normal) ? '[' . $counter . ']' : '[]';
		$default = $this->getDefaultFilterVal($normal, $counter);
		$rows = $this->filterValueList($normal);
		$return = array();
		$element = $this->getElement();
		if ($element->filter_type == 'hidden')
		{
			$return[] = '<input type="text" name="' . $v . '" class="inputbox fabrik_filter" value="' . $default . '" id="' . $htmlid . '" />';
		}
		else
		{
			$return[] = JHTML::_('select.genericlist', $rows, $v, 'class="inputbox fabrik_filter" size="1" ', 'value', 'text', $default, $htmlid);
		}
		if ($normal)
		{
			$return[] = $this->getFilterHiddenFields($counter, $elName);
		}
		else
		{
			$return[] = $this->getAdvancedFilterHiddenFields();
		}
		return implode("\n", $return);
	}

	/**
	 * Create an array of label/values which will be used to populate the elements filter dropdown
	 * returns only data found in the table you are filtering on
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array	filter value and labels
	 */

	protected function filterValueList_Exact($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$o = new stdClass;
		$o->value = '';
		$o->text = $this->filterSelectLabel();
		$opt = array($o);
		$rows = parent::filterValueList_Exact($normal, $tableName, $label, $id, $incjoin);
		foreach ($rows as &$row)
		{
			if ($row->value == 1)
			{
				$row->text = JText::_('JYES');
			}
			if ($row->value == 0)
			{
				$row->text = JText::_('JNO');
			}
		}
		$rows = array_merge($opt, $rows);
		return $rows;
	}

	/**
	 * Create an array of label/values which will be used to populate the elements filter dropdown
	 * returns all possible options
	 *
	 * @param   bool    $normal     do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  table name to use - defaults to element's current table
	 * @param   string  $label      field to use, defaults to element name
	 * @param   string  $id         field to use, defaults to element name
	 * @param   bool    $incjoin    include join
	 *
	 * @return  array	filter value and labels
	 */

	protected function filterValueList_All($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$rows = array(JHTML::_('select.option', '', $this->filterSelectLabel()), JHTML::_('select.option', '0', JText::_('JNO')),
			JHTML::_('select.option', '1', JText::_('JYES')));
		return $rows;
	}

	/**
	 * Get the condition statement to use in the filters hidden field
	 *
	 * @return  string	=, begins or contains
	 */

	protected function getFilterCondition()
	{
		return '=';
	}

}
