<?php
/**
 * Renders a fabrik element drop down
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Renders a fabrik element drop down
 *
 * @package     Joomla
 * @subpackage  Form
 * @since		1.6
 */

class JFormFieldElement extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @var  string
	 */
	var $_name = 'Element';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */

	protected function getOptions()
	{
		$cnns = array(JHTML::_('select.option', '-1', JText::_('COM_FABRIK_PLEASE_SELECT')));
		return;
	}

	/**
	 * Get the input
	 *
	 * @return  string The field input markup
	 */

	protected function getInput()
	{
		static $fabrikelements;
		if (!isset($fabrikelements))
		{
			$fabrikelements = array();
		}
		JDEBUG ? JHtml::_('script', 'media/com_fabrik/js/lib/head/head.js') : JHtml::_('script', 'media/com_fabrik/js/lib/head/head.min.js');
		FabrikHelperHTML::script('administrator/components/com_fabrik/views/namespace.js');
		$c = (int) @$this->form->repeatCounter;
		$table = $this->element['table'];
		if ($table == '')
		{
			$table = $this->form->getValue('params.list_id');
		}
		$include_calculations = (int) $this->element['include_calculations'];
		$published = (int) $this->element['published'];
		$showintable = (int) $this->element['showintable'];
		$highlightpk = (bool) JArrayHelper::getValue($this->element, 'highlightpk', false);

		if ($include_calculations != 1)
		{
			$include_calculations = 0;
		}

		// $$$ hugh - don't know what's going on here, except that this method is getting called twice for every element
		// but first time round, $this->value is not set, so if we cache it, setting loses it's value.
		//if (!array_key_exists($this->id, $fabrikelements)) {
		$opts = new stdClass;
		if ($this->form->repeat)
		{
			//in repeat fieldset/group
			$conn = $this->element['connection'] . '-' . $this->form->repeatCounter;
			$opts->table = 'jform_' . $table . '-' . $this->form->repeatCounter;
		}
		else
		{
			$conn = ($c === false || $this->element['connection_in_repeat'] == 'false') ? $this->element['connection']
				: $this->element['connection'] . '-' . $c;
			$opts->table = ($c === false || $this->element['connection_in_repeat'] == 'false') ? 'jform_' . $table : 'jform_' . $table . '-' . $c;
		}

		$opts->published = $published;
		$opts->showintable = $showintable;
		$opts->excludejoined = (int) $this->element['excludejoined'];
		$opts->livesite = COM_FABRIK_LIVESITE;
		$opts->conn = 'jform_' . $conn;
		$opts->value = $this->value;
		$opts->include_calculations = $include_calculations;
		$opts->highlightpk = (int) $highlightpk;
		$opts = json_encode($opts);
		$script = array();
		$script[] = "var p = new elementElement('$this->id', $opts);";
		$script[] = "FabrikAdmin.model.fields.element['$this->id'] = p;";
		$script = implode("\n", $script);
		$fabrikelements[$this->id] = true;
		FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/element.js', $script);
		//}
		$return = parent::getInput();
		$return .= '<img style="margin-left:10px;display:none" id="' . $this->id
			. '_loader" src="components/com_fabrik/images/ajax-loader.gif" alt="' . JText::_('COM_FABRIK_LOADING') . '" />';
		return $return;
	}
}
