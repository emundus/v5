<?php
/**
 * Import view
 *
* @package     Joomla.Administrator
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for importing csv file.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       1.6
 */

class FabrikViewImport extends JView
{

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->addToolBar();
		parent::display($tpl);
	}

	/**
	 * CSV file has been uploaded but we need to ask the user what to do with the new fields
	 *
	 * @return  void
	 */

	public function chooseElementTypes()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		$this->chooseElementTypesToolBar();
		$session = JFactory::getSession();
		$this->data = $session->get('com_fabrik.csvdata');
		$this->matchedHeadings = $session->get('com_fabrik.matchedHeadings');
		$this->newHeadings = $this->get('NewHeadings');
		$this->headings = $this->get('Headings');
		$pluginManager = $this->getModel('pluginmanager');
		$this->table = $this->get('ListModel')->getTable();
		$this->elementTypes = $pluginManager->getElementTypeDd('field', 'plugin[]');
		$this->sample = $this->get('Sample');
		$this->selectPKField = $this->get('SelectKey');
		$jform = $input->get('jform', array(), 'array');
		foreach ($jform as $key => $val)
		{
			$this->$key = $val;
		}
		parent::display('chooseElementTypes');
	}

	/**
	 * Add the 'choose element type' page toolbar
	 *
	 * @return  void
	 */

	protected function chooseElementTypesToolBar()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		$version = new JVersion;
		$icon = version_compare($version->RELEASE, '3.0') >= 0 ? 'arrow-right-2' : 'forward.png';
		JToolBarHelper::custom('import.makeTableFromCSV', $icon, $icon, 'COM_FABRIK_CONTINUE', false);
		JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */

	protected function addToolBar()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		$version = new JVersion;
		$icon = version_compare($version->RELEASE, '3.0') >= 0 ? 'arrow-right-2' : 'forward.png';
		JToolBarHelper::custom('import.doimport', $icon, $icon, 'COM_FABRIK_CONTINUE', false);
		JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

}
