<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.picklist
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to two lists - one to select from the other to select into
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.picklist
 * @since       3.0
 */

class PlgFabrik_ElementPicklist extends PlgFabrik_ElementList
{

	/**
	* Method to set the element id
	*
	* @param   int  $id  element ID number
	*
	* @return  void
	*/

	public function setId($id)
	{
		parent::setId($id);
		$params = $this->getParams();

		// Set elementlist params from picklist params
		$params->set('allow_frontend_addto', (bool) $params->get('allowadd', false));
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
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$params = $this->getParams();
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		$arSelected = (array) $this->getValue($data, $repeatCounter);
		$errorCSS = (isset($this->_elementError) && $this->_elementError != '') ? " elementErrorHighlight" : '';
		$attribs = 'class="picklistcontainer' . $errorCSS . "\"";
		$style = ".frompicklist, .topicklist{\n" . "background-color:#efefef;\n" . "padding:5px !important;\n" . "}\n" . "\n"
			. "div.picklistcontainer{\n" . "width:40%;\n" . "margin-right:10px;\n" . "margin-bottom:10px;\n" . "float:left;\n" . "}\n" . "\n"
			. ".frompicklist li, .topicklist li, li.picklist{\n" . "background-color:#FFFFFF;\n" . "margin:3px;\n" . "padding:5px !important;\n"
			. "cursor:move;\n" . "}\n" . "\n" . "li.emptyplicklist{\n" . "background-color:transparent;\n" . "cursor:pointer;\n" . "}";
		FabrikHelperHTML::addStyleDeclaration($style);
		$i = 0;
		$aRoValues = array();
		$fromlist = array();
		$tolist = array();
		$fromlist[] = JText::_('PLG_FABRIK_PICKLIST_FROM') . ':<ul id="' . $id . '_fromlist" class="frompicklist">';
		$tolist[] = JText::_('PLG_FABRIK_PICKLIST_TO') . ':<ul id="' . $id . '_tolist" class="topicklist">';
		foreach ($arVals as $v)
		{
			if (!in_array($v, $arSelected))
			{
				$fromlist[] = '<li id="' . $id . '_value_' . $v . '" class="picklist">' . $arTxt[$i] . '</li>';
			}
			$i++;
		}
		$i = 0;
		$lookup = array_flip($arVals);
		foreach ($arSelected as $v)
		{
			if ($v == '' || $v == '-' || $v = '[""]')
			{
				continue;
			}
			$k = JArrayHelper::getValue($lookup, $v);
			$tmptxt = addslashes(htmlspecialchars(JArrayHelper::getValue($arTxt, $k)));
			$tolist[] = '<li id="' . $id . '_value_' . $v . '" class="' . $v . '">' . $tmptxt . '</li>';
			$aRoValues[] = $tmptxt;
			$i++;
		}
		if (FArrayHelper::emptyIsh($arSelected))
		{
			$fromlist[] = '<li class="emptyplicklist">' . JText::_('PLG_ELEMENT_PICKLIST_DRAG_OPTIONS_HERE') . '</li>';
		}
		if (FArrayhelper::emptyIsh($aRoValues))
		{
			$tolist[] = '<li class="emptyplicklist">' . JText::_('PLG_ELEMENT_PICKLIST_DRAG_OPTIONS_HERE') . '</li>';
		}

		$fromlist[] = '</ul>';
		$tolist[] = '</ul>';

		$str = '<div ' . $attribs . '>' . implode("\n", $fromlist) . '</div>';
		$str .= '<div class="picklistcontainer">' . implode("\n", $tolist) . '</div>';
		$arSelected = htmlspecialchars(json_encode($arSelected), ENT_COMPAT, 'UTF-8');
		$str .= $this->getHiddenField($name, $arSelected, $id);
		if (!$this->isEditable())
		{
			return implode(', ', $aRoValues);
		}
		$str .= $this->getAddOptionFields($repeatCounter);
		return $str;
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
		$element = $this->getElement();
		$data = $this->getFormModel()->_data;
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		$params = $this->getParams();
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->allowadd = (bool) $params->get('allowadd', false);
		$opts->defaultVal = $this->getValue($data, $repeatCounter);

		$opts->hovercolour = $params->get('picklist-hovercolour', '#AFFFFD');
		$opts->bghovercolour = $params->get('picklist-bghovercolour', '#FFFFDF');
		JText::script('PLG_ELEMENT_PICKLIST_ENTER_VALUE_LABEL');
		return array('FbPicklist', $id, $opts);
	}

	/**
	 * if the search value isnt what is stored in the database, but rather what the user
	 * sees then switch from the search string to the db value here
	 * overwritten in things like checkbox and radio plugins
	 *
	 * @param   string  $value  FilterVal
	 *
	 * @return  string
	 */

	protected function prepareFilterVal($value)
	{
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		for ($i = 0; $i < count($arTxt); $i++)
		{
			if (JString::strtolower($arTxt[$i]) == JString::strtolower($val))
			{
				$val = $arVals[$i];
				return $val;
			}
		}
		return $val;
	}

	/**
	 * Builds an array containing the filters value and condition
	 *
	 * @param   string  $value      Initial value
	 * @param   string  $condition  Intial $condition
	 * @param   string  $eval       How the value should be handled
	 *
	 * @return  array	(value condition)
	 */

	public function getFilterValue($value, $condition, $eval)
	{
		$value = $this->prepareFilterVal($value);
		$return = parent::getFilterValue($value, $condition, $eval);
		return $return;
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
		$data = (array) $data;
		foreach ($data as $d)
		{
			if ($d != '' && $d != '[""]')
			{
				return false;
			}
		}
		return true;
	}


}
