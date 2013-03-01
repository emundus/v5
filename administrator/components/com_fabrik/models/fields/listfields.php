<?php
/**
 * Renders a list of elements found in a fabrik list
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list of elements found in a fabrik list
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldListfields extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Listfields';

	/**
	 * Objects resulting from this elements queries - keyed on idetifying hash
	 *
	 * @var  array
	 */
	protected $results = null;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		if (is_null($this->results))
		{
			$this->results = array();
		}
		$app = JFactory::getApplication();
		$input = $app->input;
		$controller = $input->get('view', $input->get('task'));
		$formModel = false;
		$aEls = array();
		$pluginFilters = trim($this->element['filter']) == '' ? array() : explode('|', $this->element['filter']);
		$c = ElementHelper::getRepeatCounter($this);
		$connection = $this->element['connection'];
		/*
		 * 27/08/2011 - changed from default tableelement to id - for juser form plugin - might cause havock
		 * else where but loading elements by id as default seems more robust (and is the default behaviour in f2.1
		 */
		$valueformat = JArrayHelper::getValue($this->element, 'valueformat', 'id');
		$onlylistfields = (int) JArrayHelper::getValue($this->element, 'onlylistfields', 0);
		$showRaw = (bool) JArrayHelper::getValue($this->element, 'raw', false);
		$highlightpk = (bool) JArrayHelper::getValue($this->element, 'highlightpk', false);
		switch ($controller)
		{
			case 'validationrule':
				$id = $input->getInt('id');
				$pluginManager = FabrikWorker::getPluginManager();
				$elementModel = $pluginManager->getElementPlugin($id);
				$element = $elementModel->getElement();
				$res = $this->loadFromGroupId($element->group_id);
				break;
			case 'visualization':
			case 'element':
			// @TODO this seems like we could refractor it to use the formModel class as per the table and form switches below?
				$connectionDd = ($c === false) ? $connection : $connection . '-' . $c;
				if ($connection == '')
				{

					$groupId = isset($this->form->rawData) ? JArrayHelper::getValue($this->form->rawData, 'group_id', 0)
						: $this->form->getValue('group_id');
					$res = $this->loadFromGroupId($groupId);
				}
				else
				{
					$repeat = ElementHelper::getRepeat($this);
					$tableDd = $this->element['table'];
					$opts = new stdClass;
					$opts->table = ($repeat) ? 'jform_' . $tableDd . '-' . $c : 'jform_' . $tableDd;
					$opts->conn = 'jform_' . $connectionDd;
					$opts->value = $this->value;
					$opts->repeat = $this->value;
					$opts->highlightpk = (int) $highlightpk;
					$opts = json_encode($opts);
					$script = "new ListFieldsElement('$this->id', $opts);\n";
					FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/listfields.js', $script);
					$rows = array(JHTML::_('select.option', '', JText::_('SELECT A CONNECTION FIRST')), 'value', 'text');
					$o = new stdClass;
					$o->table_name = '';
					$o->name = '';
					$o->value = '';
					$o->text = JText::_('COM_FABRIK_SELECT_A_TABLE_FIRST');
					$res[] = $o;
				}
				break;
			case 'listform':
			case 'list':
			case 'module':
			case 'item':
			// Menu item
				if ($controller === 'item')
				{
					$id = $this->form->getValue('request.listid');
				}
				else
				{
					$id = $this->form->getValue('id');
				}
				if (!isset($this->form->model))
				{
					if (!in_array($controller, array('item', 'module')))
					{
						// Seems to work anyway in the admin module page - so lets not raise notice
						JError::raiseNotice(500, 'Model not set in listfields field ' . $this->id);
					}
					return;
				}
				$listModel = $this->form->model;
				if ($id !== 0)
				{
					$formModel = $listModel->getFormModel();
					$valfield = $valueformat == 'tableelement' ? 'name' : 'id';
					$res = $formModel->getElementOptions(false, $valfield, $onlylistfields, $showRaw, $pluginFilters);
				}
				else
				{
					$res = array();
				}
				break;
			case 'form':
				if (!isset($this->form->model))
				{
					JError::raiseNotice(500, 'Model not set in listfields field ' . $this->id);
					return;
				}
				$formModel = $this->form->model;
				$valfield = $valueformat == 'tableelement' ? 'name' : 'id';
				$res = $formModel->getElementOptions(false, $valfield, $onlylistfields, $showRaw, $pluginFilters);
				break;
			case 'group':
				$valfield = $valueformat == 'tableelement' ? 'name' : 'id';
				$id = $this->form->getValue('id');
				$groupModel = JModel::getInstance('Group', 'FabrikFEModel');
				$groupModel->setId($id);
				$formModel = $groupModel->getFormModel();
				$res = $formModel->getElementOptions(false, $valfield, $onlylistfields, $showRaw, $pluginFilters);
				break;
			default:
				return JText::_('THE LISTFIELDS ELEMENT IS ONLY USABLE BY LISTS AND ELEMENTS');
				break;
		}
		$return = '';
		if (is_array($res))
		{
			if ($controller == 'element')
			{
				foreach ($res as $o)
				{
					$s = new stdClass;

					// Element already contains correct key
					if ($controller != 'element')
					{
						$s->value = $valueformat == 'tableelement' ? $o->table_name . '.' . $o->text : $o->value;
					}
					else
					{
						$s->value = $o->value;
					}
					$s->text = FabrikString::getShortDdLabel($o->text);
					$aEls[] = $s;
				}
			}
			else
			{
				foreach ($res as &$o)
				{
					$o->text = FabrikString::getShortDdLabel($o->text);
				}
				$aEls = $res;
			}
			$aEls[] = JHTML::_('select.option', '', '-');

			// For pk fields - we are no longer storing the key with '`' as thats mySQL specific
			$this->value = str_replace('`', '', $this->value);

			// Some elements were stored as names but subsequently changed to ids (need to check for old values an substitute with correct ones)
			if ($valueformat == 'id' && !is_numeric($this->value) && $this->value != '')
			{
				if ($formModel)
				{
					$elementModel = $formModel->getElement($this->value);
					$this->value = $elementModel ? $elementModel->getId() : $this->value;
				}
			}
			$return = JHTML::_('select.genericlist', $aEls, $this->name, 'class="inputbox" size="1" ', 'value', 'text', $this->value, $this->id);
			$return .= '<img style="margin-left:10px;display:none" id="' . $this->id
				. '_loader" src="components/com_fabrik/images/ajax-loader.gif" alt="' . JText::_('LOADING') . '" />';
		}
		return $return;
	}

	/**
	 * Load the element list from the group id
	 *
	 * @param   int  $groupId  Group id
	 *
	 * @since   3.0.6
	 *
	 * @return array
	 */

	protected function loadFromGroupId($groupId)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$controller = $input->get('view', $input->get('task'));
		$valueformat = JArrayHelper::getValue($this->element, 'valueformat', 'id');
		$onlylistfields = (int) JArrayHelper::getValue($this->element, 'onlylistfields', 0);
		$pluginFilters = trim($this->element['filter']) == '' ? array() : explode('|', $this->element['filter']);
		$bits = array();
		$showRaw = (bool) JArrayHelper::getValue($this->element, 'raw', false);
		$groupModel = JModel::getInstance('Group', 'FabrikFEModel');
		$groupModel->setId($groupId);
		$optskey = $valueformat == 'tableelement' ? 'name' : 'id';
		$res = $groupModel->getForm()->getElementOptions(false, $optskey, $onlylistfields, $showRaw, $pluginFilters);
		$hash = $controller . '.' . implode('.', $bits);
		if (array_key_exists($hash, $this->results))
		{
			$res = $this->results[$hash];
		}
		else
		{
			$this->results[$hash] = &$res;
		}
		return $res;
	}
}
