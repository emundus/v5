<?php
/**
 * Plugin element to render day/month/year dropdowns
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.birthday
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render day/month/year dropdowns
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.birthday
 * @since       3.0
 */

class PlgFabrik_ElementBirthday extends PlgFabrik_Element
{

	public $hasSubElements = true;

	/**
	 * Get db table field type
	 *
	 * @return  string
	 */

	public function getFieldDescription()
	{
		return 'DATE';
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To preopulate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		/**
		 * Jaanus: needed also here to not to show 0000-00-00 in detail view;
		 * see also 58, added && !in_array($value, $aNullDates) (same reason).
		 */
		$db = JFactory::getDbo();
		$aNullDates = array('0000-00-000000-00-00', '0000-00-00 00:00:00', '0000-00-00', '', $db->getNullDate());
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$monthlabels = array(JText::_('January'), JText::_('February'), JText::_('March'), JText::_('April'), JText::_('May'), JText::_('June'),
			JText::_('July'), JText::_('August'), JText::_('September'), JText::_('October'), JText::_('November'), JText::_('December'));
		$monthlabels = array(JText::_('January'), JText::_('February'), JText::_('March'), JText::_('April'), JText::_('May'), JText::_('June'),
			JText::_('July'), JText::_('August'), JText::_('September'), JText::_('October'), JText::_('November'), JText::_('December'));
		$monthnumbers = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
		$daysys = array('01', '02', '03', '04', '05', '06', '07', '08', '09');
		$daysimple = array('1', '2', '3', '4', '5', '6', '7', '8', '9');

		$bits = array();
		/**
		 * $$$ rob - not sure why we are setting $data to the form's data
		 * but in table view when getting read only filter value from url filter this
		 * _form_data was not set to no readonly value was returned
		 * added little test to see if the data was actually an array before using it
		 */
		if (is_array($this->_form->_data))
		{
			$data = $this->_form->_data;
		}
		$value = $this->getValue($data, $repeatCounter);
		$fd = $params->get('details_date_format', 'd.m.Y');
		$dateandage = (int) $params->get('details_dateandage', '0');

		if (!$this->isEditable())
		{
			if (!in_array($value, $aNullDates))
			{
				// Avoid 0000-00-00
				list($year, $month, $day) = strstr($value, '-') ? explode('-', $value) : explode(',', $value);
				$daydisp = str_replace($daysys, $daysimple, $day);
				$monthdisp = str_replace($monthnumbers, $monthlabels, $month);
				$thisyear = date('Y');
				$nextyear = date('Y') + 1;
				$lastyear = date('Y') - 1;

				// $$$ rob - all this below is nice but ... you still need to set a default
				$detailvalue = '';
				$year = JString::ltrim($year, '0');
				if (FabrikWorker::isDate($value))
				{
					$date = JFactory::getDate($value);
					$detailvalue = $date->toFormat($fd);
				}
				if (date('m-d') < $month . '-' . $day)
				{
					$ageyear = $lastyear;
				}
				else
				{
					$ageyear = $thisyear;
				}
				if ($fd == 'd.m.Y')
				{
					$detailvalue = $day . '.' . $month . '.' . $year;
				}
				else
				{
					if ($fd == 'm.d.Y')
					{
						$detailvalue = $month . '/' . $day . '/' . $year;
					}
					if ($fd == 'D. month YYYY')
					{
						$detailvalue = $daydisp . '. ' . $monthdisp . ' ' . $year;
					}
					if ($fd == 'Month d, YYYY')
					{
						$detailvalue = $monthdisp . ' ' . $daydisp . ', ' . $year;
					}
					if ($fd == '{age}')
					{
						$detailvalue = $ageyear - $year;
					}
					if ($fd == '{age} d.m')
					{
						$mdvalue = $daydisp . '. ' . $monthdisp;
					}
					if ($fd == '{age} m.d')
					{
						$mdvalue = $monthdisp . ' ' . $daydisp;
					}
					if ($fd == '{age} d.m' || $fd == '{age} m.d')
					{
						// Always actual age
						$detailvalue = $ageyear - $year;
						if (date('m-d') == $month . '-' . $day)
						{
							$detailvalue .= '<font color = "#CC0000"><b> ' . JText::_('TODAY') . '!</b></font>';
							if (date('m') == '12')
							{
								$detailvalue .= ' / ' . $nextyear . ': ' . ($nextyear - $year);
							}
						}
						else
						{
							$detailvalue .= ' (' . $mdvalue;
							if (date('m-d') < $month . '-' . $day)
							{
								$detailvalue .= ': ' . ($thisyear - $year);
							}
							else
							{
								$detailvalue .= '';
							}
							if (date('m') == '12')
							{
								$detailvalue .= ' / ' . $nextyear . ': ' . ($nextyear - $year);
							}
							else
							{
								$detailvalue .= '';
							}
							$detailvalue .= ')';
						}
					}
					else
					{
						if ($fd != '{age}' && $dateandage == 1)
						{
							$detailvalue .= ' (' . ($ageyear - $year) . ')';
						}
					}
				}
				$value = $this->replaceWithIcons($detailvalue);
				return ($element->hidden == '1') ? "<!-- " . $detailvalue . " -->" : $detailvalue;
			}
			else
			{
				return '';
			}
		}
		else
		{
			// Wierdness for failed validaion
			$value = strstr($value, ',') ? array_reverse(explode(',', $value)) : explode('-', $value);
			$yearvalue = JArrayHelper::getValue($value, 0);
			$monthvalue = JArrayHelper::getValue($value, 1);
			$dayvalue = JArrayHelper::getValue($value, 2);

			$days = array(JHTML::_('select.option', '', $params->get('birthday_daylabel', JText::_('DAY'))));
			for ($i = 1; $i < 32; $i++)
			{
				$days[] = JHTML::_('select.option', $i);
			}
			$months = array(JHTML::_('select.option', '', $params->get('birthday_monthlabel', JText::_('MONTH'))));

			// Siin oli enne $monthlabels, viisin ülespoole
			for ($i = 0; $i < count($monthlabels); $i++)
			{
				$months[] = JHTML::_('select.option', $i + 1, $monthlabels[$i]);
			}
			$years = array(JHTML::_('select.option', '', $params->get('birthday_yearlabel', JText::_('YEAR'))));

			// Jaanus: now we can choose one exact year A.C to begin the dropdown AND would the latest year be current year or some years earlier/later.
			$date = date('Y') + (int) $params->get('birthday_forward', 0);
			$yearopt = $params->get('birthday_yearopt');
			$yearstart = (int) $params->get('birthday_yearstart');
			$yeardiff = $yearopt == 'number' ? $yearstart : $date - $yearstart;
			for ($i = $date; $i >= $date - $yeardiff; $i--)
			{
				$years[] = JHTML::_('select.option', $i);
			}
			$errorCSS = (isset($this->_elementError) && $this->_elementError != '') ? " elementErrorHighlight" : '';
			$attribs = 'class="fabrikinput inputbox' . $errorCSS . '"';
			$str = array();
			$str[] = '<div class="fabrikSubElementContainer" id="' . $id . '">';

			// $name already suffixed with [] as element hasSubElements = true
			$str[] = JHTML::_('select.genericlist', $days, preg_replace('#(\[\])$#', '[0]', $name), $attribs, 'value', 'text', $dayvalue);
			$str[] = $params->get('birthday_separatorlabel', JText::_('/')) . ' '
				. JHTML::_('select.genericlist', $months, preg_replace('#(\[\])$#', '[1]', $name), $attribs, 'value', 'text', $monthvalue);
			$str[] = $params->get('birthday_separatorlabel', JText::_('/')) . ' '
				. JHTML::_('select.genericlist', $years, preg_replace('#(\[\])$#', '[2]', $name), $attribs, 'value', 'text', $yearvalue);
			$str[] = '</div>';
			return implode("\n", $str);
		}
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  when repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           options
	 *
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		// @TODO rename $this->defaults to $this->values
		if (!isset($this->defaults))
		{
			$this->defaults = array();
		}
		if (!array_key_exists($repeatCounter, $this->defaults))
		{
			$groupModel = $this->getGroup();
			$joinid = $groupModel->getGroup()->join_id;
			$formModel = $this->getForm();

			$value = $this->getDefaultOnACL($data, $opts);

			$name = $this->getFullName(false, true, false);
			$rawname = $name . "_raw";
			if ($groupModel->isJoin())
			{
				if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid]))
				{
					if ($groupModel->canRepeat())
					{

						if (array_key_exists($rawname, $data['join'][$joinid]) && array_key_exists($repeatCounter, $data['join'][$joinid][$rawname]))
						{
							$value = $data['join'][$joinid][$rawname][$repeatCounter];
						}
						else
						{
							if (array_key_exists($rawname, $data['join'][$joinid]) && array_key_exists($repeatCounter, $data['join'][$joinid][$name]))
							{
								$value = $data['join'][$joinid][$name][$repeatCounter];
							}
						}
					}
					else
					{
						$joinDefault = JArrayHelper::getValue($data['join'][$joinid], $name, $value);
						$value = JArrayHelper::getValue($data['join'][$joinid], $rawname, $joinDefault);
						/**
						 * $$$ rob if you have 2 tbl joins, one repeating and one not
						 * the none repeating one's values will be an array of duplicate values
						 * but we only want the first value
						 */
						if (is_array($value))
						{
							$value = array_shift($value);
						}
					}
				}
			}
			else
			{
				if ($groupModel->canRepeat())
				{
					// Repeat group NO join
					$thisname = $rawname;
					if (!array_key_exists($name, $data))
					{
						$thisname = $name;
					}
					if (array_key_exists($thisname, $data))
					{
						if (is_array($data[$thisname]))
						{
							// Occurs on form submission for fields at least
							$a = $data[$thisname];
						}
						else
						{
							// Occurs when getting from the db
							$a = FabrikWorker::JSONtoData($data[$thisname], true);
						}
						$value = JArrayHelper::getValue($a, $repeatCounter, $value);
					}

				}
				else
				{
					if (!is_array($data))
					{
						$value = $data;
					}
					else
					{
						$value = JArrayHelper::getValue($data, $name, JArrayHelper::getValue($data, $rawname, $value));
					}
				}
			}

			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			if ($value === '')
			{
				// Query string for joined data
				$value = JArrayHelper::getValue($data, $name, $value);
			}
			/**
			 * @TODO perhaps we should change this to $element->value and store $element->default as the actual default value
			 * stops this getting called from form validation code as it messes up repeated/join group validations
			 */
			if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1)
			{
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}
			$this->defaults[$repeatCounter] = $value;
		}
		return $this->defaults[$repeatCounter];
	}

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
		return $this->_indStoreDBFormat($val);
	}

	/**
	 * Get the value to store the value in the db
	 * Jaanus: stores the value if all its parts (day, month, year) are selected in form, otherwise stores
	 * (or updates data to) null value. NULL is useful in many cases, e.g when using Fabrik for working
	 * with data of such components as EventList, where in #___eventlist_events.enddates (times and endtimes as well)
	 * empty data is always NULL otherwise nulldate is displayed in its views.
	 *
	 * @param   mixed  $val  (array normally but string on csv import)
	 *
	 * @TODO: if NULL value is the first in repeated group then in list view whole group is empty.
	 * Could anyone find a solution? I give up :-(
	 *
	 * @return  string	yyyy-mm-dd
	 */

	private function _indStoreDBFormat($val)
	{
		$params = $this->getParams();
		if ($params->get('empty_is_null') == 1)
		{
			if (is_array($val) && !in_array('', $val))
			{
				return $val[2] . '-' . $val[1] . '-' . $val[0];
			}
		}
		else
		{
			return is_array($val) ? $val[2] . '-' . $val[1] . '-' . $val[0] : '';
		}
	}

	/**
	 * Does the element conside the data to be empty
	 * Used in isempty validation rule
	 *
	 * @param   array  $data           data to test against
	 * @param   int    $repeatCounter  repeat group #
	 *
	 * @return  bool
	 */

	public function dataConsideredEmpty($data, $repeatCounter)
	{
		if (strstr($data, ','))
		{
			$data = explode(',', $data);
		}
		$data = (array) $data;
		foreach ($data as $d)
		{
			if (trim($d) == '')
			{
				return true;
			}
		}
		return false;
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
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->separator = $params->get('birthday_separatorlabel', JText::_('/'));
		return array('FbBirthday', $id, $opts);
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
		$groupModel = $this->getGroup();
		/**
		 * Jaanus: json_decode replaced with FabrikWorker::JSONtoData that made visible also single data in repeated group
		 *
		 * Jaanus: removed condition canrepeat() from renderListData: weird result such as 05",null,
		 * "1940.07.["1940 (2011) when not repeating but still join and merged. Using isJoin() instead
		*/
		$data = $groupModel->isJoin() ? FabrikWorker::JSONtoData($data, true) : array($data);
		$data = (array) $data;

		$format = array();
		foreach ($data as $d)
		{
			$format[] = $this->listFormat($d);
		}
		$data = json_encode($format);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Format a date based on list age/date format options
	 *
	 * @param   string  $d  Date
	 *
	 * @since   3.0.9
	 *
	 * @return string|number
	 */

	private function listFormat($d)
	{
		if (!FabrikWorker::isDate($d))
		{
			return '';
		}
		$params = $this->getParams();

		$monthlabels = array(JText::_('January'), JText::_('February'), JText::_('March'), JText::_('April'), JText::_('May'), JText::_('June'),
				JText::_('July'), JText::_('August'), JText::_('September'), JText::_('October'), JText::_('November'), JText::_('December'));

		$monthnumbers = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
		$daysys = array('01', '02', '03', '04', '05', '06', '07', '08', '09');
		$daysimple = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
		$jubileum = array('0', '25', '75');

		$ft = $params->get('list_date_format', 'd.m.Y');

		$fta = $params->get('list_age_format', 'no');

		/**
		 * $$$ rob default to a format date
		 * $date = JFactory::getDate($d);
		 * $datedisp = $date->toFormat($ft);
		 * Jaanus: sorry, but in this manner the element doesn't work with dates earlier than 1901
		*/

		list($year, $month, $day) = explode('-', $d);
		$daydisp = str_replace($daysys, $daysimple, $day);
		$monthdisp = str_replace($monthnumbers, $monthlabels, $month);
		$nextyear = date('Y') + 1;
		$lastyear = date('Y') - 1;
		$thisyear = date('Y');
		$year = JString::ltrim($year, '0');
		$dmy = $day . '.' . $month . '.' . $year;
		$mdy = $month . '/' . $day . '/' . $year;
		$dmonthyear = $daydisp . '. ' . $monthdisp . ' ' . $year;
		$monthdyear = $monthdisp . ' ' . $daydisp . ', ' . $year;
		if ($ft == "d.m.Y")
		{
			$datedisp = $dmy;
		}
		else
		{
			if ($ft == "m.d.Y")
			{
				$datedisp = $mdy;
			}
			if ($ft == "D. month YYYY")
			{
				$datedisp = $dmonthyear;
			}
			if ($ft == "Month d, YYYY")
			{
				$datedisp = $monthdyear;
			}
		}

		if ($fta == 'no')
		{
			return $datedisp;
		}
		else
		{
			if (date('m-d') == $month . '-' . $day)
			{
				if ($fta == '{age}')
				{
					return '<font color ="#DD0000"><b>' . ($thisyear - $year) . "</b></font>";
				}
				else
				{
					if ($fta == '{age} date')
					{
						return '<font color ="#DD0000"><b>' . $datedisp . ' (' . ($thisyear - $year) . ')</b></font>';
					}
					if ($fta == '{age} this')
					{
						return '<font color ="#DD0000"><b>' . ($thisyear - $year) . ' (' . $datedisp . ')</b></font>';
					}
					if ($fta == '{age} next')
					{
						return '<font color ="#DD0000"><b>' . ($nextyear - $year) . ' (' . $datedisp . ')</b></font>';
					}
				}
			}
			else
			{
				if ($fta == '{age} date')
				{
					if (date('m-d') > $month . '-' . $day)
					{
						return $datedisp . ' (' . ($thisyear - $year) . ')';
					}
					else
					{
						return $datedisp . ' (' . ($lastyear - $year) . ')';
					}
				}
				else
				{
					if ($fta == '{age}')
					{
						if (date('m-d') > $month . '-' . $day)
						{
							return $thisyear - $year;
						}
						else
						{
							return $lastyear - $year;
						}
					}
					else
					{
						if ($fta == '{age} this')
						{
							if (in_array(substr(($thisyear - $year), -1), $jubileum) || in_array(substr(($thisyear - $year), -2), $jubileum))
							{
								return '<b>' . ($thisyear - $year) . ' (' . $datedisp . ')</b>';
							}
							else
							{
								return ($thisyear - $year) . ' (' . $datedisp . ')';
							}
						}
						if ($fta == '{age} next')
						{
							if (in_array(substr(($nextyear - $year), -1), $jubileum) || in_array(substr(($nextyear - $year), -2), $jubileum))
							{
								return '<b>' . ($nextyear - $year) . ' (' . $datedisp . ')</b>';
							}
							else
							{
								return ($nextyear - $year) . ' (' . $datedisp . ')';
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Used by radio and dropdown elements to get a dropdown list of their unique
	 * unique values OR all options - basedon filter_build_method
	 *
	 * @param   bool    $normal     Do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  Table name to use - defaults to element's current table
	 * @param   string  $label      Field to use, defaults to element name
	 * @param   string  $id         Field to use, defaults to element name
	 * @param   bool    $incjoin    Include join
	 *
	 * @return  array  text/value objects
	 */

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$rows = parent::filterValueList($normal, $tableName, $label, $id, $incjoin);
		$return = array();
		foreach ($rows as &$row)
		{
			$txt = $this->listFormat($row->text);
			if ($txt !== '')
			{
				$row->text = strip_tags($txt);
			}
			// Ensure unique values
			if (!array_key_exists($row->text, $return))
			{
				$return[$row->text] = $row;
			}
		}
		$return = array_values($return);
		return $return;
	}

	/**
	 * This builds an array containing the filters value and condition
	 * when using a ranged search
	 *
	 * @param   array  $value  Initial values
	 *
	 * @return  array  (value condition)
	 */

	protected function getRangedFilterValue($value)
	{
		$db = FabrikWorker::getDbo();
		$element = $this->getElement();

		if ($element->filter_type === 'range')
		{
			if (strtotime($value[0]) > strtotime($value[1]))
			{
				$tmp_value = $value[0];
				$value[0] = $value[1];
				$value[1] = $tmp_value;
			}

			if (is_numeric($value[0]) && is_numeric($value[1]))
			{
				$value = $value[0] . ' AND ' . $value[1];
			}
			else
			{
				$today = JFactory::getDate();
				$thisMonth = $today->format('m');
				$thisDay = $today->format('d');

				// Set start date todays month/day of start year
				$startYear = JFactory::getDate($value[0])->format('Y');
				$startDate = JFactory::getDate();
				$startDate->setDate($startYear, $thisMonth, $thisDay)->setTime(0, 0, 0);
				$value[0] = $startDate->toSql();

				// Set end date to today's month/day of year after end year (means search on age between 35 & 35 returns results)
				$endYear = JFactory::getDate($value[1])->format('Y');
				$endDate = JFactory::getDate();
				$endDate->setDate($endYear + 1, $thisMonth, $thisDay)->setTime(23, 59, 59);
				$value[1] = $endDate->toSql();

				$value = $db->quote($value[0]) . ' AND ' . $db->quote($value[1]);
			}

			$condition = 'BETWEEN';
		}
		else
		{
			if (is_array($value) && !empty($value))
			{
				foreach ($value as &$v)
				{
					$v = $db->quote($v);
				}

				$value = ' (' . implode(',', $value) . ')';
			}

			$condition = 'IN';
		}
		return array($value, $condition);
	}
}
