<?php
/**
 * Access element
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.access
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Access element
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.access
 * @since       3.0
 */

class PlgFabrik_ElementAccess extends PlgFabrik_Element
{

	/**
	 * If the element 'Include in search all' option is set to 'default' then this states if the
	 * element should be ignored from search all.
	 * @var bool  True, ignore in extended search all.
	 */
	protected $ignoreSearchAllDefault = true;

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		// $$$ hugh - nope!
		// return $val[0];
		return $val;
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
		$name = $this->getHTMLName($repeatCounter);

		$arSelected = array('');

		if (isset($data[$name]))
		{

			if (!is_array($data[$name]))
			{
				$arSelected = explode(',', $data[$name]);
			}
			else
			{
				$arSelected = $data[$name];
			}
		}
		$gtree = $this->getOpts();
		if (!$this->isEditable())
		{
			$row = new stdClass;
			return $this->renderListData($arSelected[0], $row);
		}
		return JHTML::_('select.genericlist', $gtree, $name, 'class="inputbox" size="6"', 'value', 'text', $arSelected[0]);
	}

	/**
	 * Get list dropdown options
	 *
	 * @param   bool  $allowAll  add an show all option
	 *
	 * @return  array
	 */

	private function getOpts($allowAll = true)
	{
		$db = JFactory::getDbo();
		$db
			->setQuery(
				'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' . ' FROM #__usergroups AS a'
					. ' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' . ' GROUP BY a.id' . ' ORDER BY a.lft ASC');
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		if ($allowAll)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
		}
		return $options;
	}

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$gtree = $this->getOpts();
		$filter = JFilterInput::getInstance(null, null, 1, 1);
		foreach ($gtree as $o)
		{
			if ($o->value == $data)
			{
				return JString::ltrim($filter->clean($o->text, 'word'), '&nbsp;');
			}
		}
	}

	/**
	 * Get database field description
	 *
	 * @return  string  db field type
	 */

	public function getFieldDescription()
	{
		$p = $this->getParams();
		if ($this->encryptMe())
		{
			return 'BLOB';
		}
		return "INT(3)";
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		return array('FbAccess', $id, $opts);
	}

}
