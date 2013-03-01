<?php
/**
 * Fabrik Admin Home Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Fabrik Admin Home Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikModelHome extends JModelAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 */
	protected $text_prefix = 'COM_FABRIK_HOME';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable	A database object
	 */

	public function getTable($type = 'Cron', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabrikWorker::getDbo(true);
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array  $data      Data for the form.
	 * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed	A JForm object on success, false on failure
	 */

	public function getForm($data = array(), $loadData = true)
	{
		return false;
	}

	/**
	 * Get fabrikar.com rss feed
	 *
	 * @return string
	 */

	public function getRSSFeed()
	{
		//  get RSS parsed object
		$options = array();
		$options['rssUrl'] = 'http://feeds.feedburner.com/fabrik';
		$options['cache_time'] = 86400;

		$rssDoc = JFactory::getXMLparser('RSS', $options);
		if ($rssDoc == false)
		{
			$output = JText::_('Error: Feed not retrieved');
		}
		else
		{
			// Channel header and link
			$title = $rssDoc->get_title();
			$link = $rssDoc->get_link();

			$output = '<table class="adminlist">';
			$output .= '<tr><th colspan="3"><a href="' . $link . '" target="_blank">' . JText::_($title) . '</th></tr>';

			$items = array_slice($rssDoc->get_items(), 0, 3);
			$numItems = count($items);
			if ($numItems == 0)
			{
				$output .= '<tr><th>' . JText::_('No news items found') . '</th></tr>';
			}
			else
			{
				$k = 0;
				for ($j = 0; $j < $numItems; $j++)
				{
					$item = $items[$j];
					$output .= '<tr><td class="row' . $k . '">';
					$output .= '<a href="' . $item->get_link() . '" target="_blank">' . $item->get_title() . '</a>';
					$output .= '<br />' . $item->get_date('Y-m-d');
					if ($item->get_description())
					{
						$description = FabrikString::truncate($item->get_description(), array('wordcount' => 50));
						$output .= '<br />' . $description;
					}
					$output .= '</td></tr>';
					$k = 1 - $k;
				}
			}

			$output .= '</table>';
		}
		return $output;
	}

	/**
	 * Install sample data
	 *
	 * @return  void
	 */

	public function installSampleData()
	{
		$cnn = FabrikWorker::getConnection();
		$defaulDb = $cnn->getDb();
		$db = FabrikWorker::getDbo(true);
		$group = $this->getTable('Group');
		$config = JFactory::getConfig();

		$dbTableName = $config->get('dbprefix') . "fb_contact_sample";
		echo "<div style='text-align:left;border:1px dotted #cccccc;padding:10px;'>" . "<h3>Installing data...</h3><ol>";

		$group->name = "Contact Details";
		$group->label = "Contact Details";
		$group->published = 1;
		if (!$group->store())
		{
			return JError::raiseWarning(500, $group->getError());
		}
		$groupId = $db->insertid();

		$sql = "DROP TABLE IF EXISTS $dbTableName;";
		$defaulDb->setQuery($sql);
		$defaulDb->query();

		echo "<li>Group 'Contact Details' created</li>";
		echo "<li>Element 'Email' added to group 'Contact Details'</li>";

		$group = $this->getTable('Group');
		$group->name = "Your Enquiry";
		$group->label = "Your Enquiry";
		$group->published = 1;

		if (!$group->store())
		{
			return JError::raiseWarning(500, $group->getError());
		}
		$group2Id = $db->insertid();
		echo "<li>Group 'Your Enquiry' created</li>";

		echo "<li>Element 'Message' added to group 'Your Enquiry'</li>";

		$form = $this->getTable('Form');
		$form->label = "Contact Us";
		$form->record_in_database = 1;
		$form->intro = "This is a sample contact us form, that is stored in a database table";

		$form->submit_button_label = "Submit";
		$form->published = 1;

		$form->form_template = "default";
		$form->view_only_template = "default";

		if (!$form->store())
		{
			return JError::raiseWarning(500, $form->getError());
		}
		echo "<li>Form 'Contact Us' created</li>";
		$formId = $db->insertid();

		$query = $db->getQuery(true);
		$query->insert('#__{package}_formgroup')->set(array('form_id=' . (int) $formId, 'group_id=' . (int) $groupId, 'ordering=0'));
		$db->setQuery($query);
		if (!$db->query())
		{
			echo $db->getErrorMsg();
			exit;
		}

		$query = $db->getQuery(true);
		$query->insert('#__{package}_formgroup')->set(array('form_id=' . (int) $formId, 'group_id=' . (int) $group2Id, 'ordering=1'));
		$db->setQuery($query);
		if (!$db->query())
		{
			echo $db->getErrorMsg();
			exit;
		}
		echo "<li>Groups added to 'Contact Us' form</li>";
		$listModel = JModel::getInstance('List', 'FabrikModel');
		$list = $this->getTable('List');
		$list->label = "Contact Us Data";
		$list->introduction = "This table stores the data submitted in the contact us form";
		$list->form_id = $formId;
		$list->connection_id = $cnn->getConnection()->id;
		$list->db_table_name = $dbTableName;

		// Store without name quotes as that's db specific
		$list->db_primary_key = $dbTableName . '.id';
		$list->auto_inc = 1;
		$list->published = 1;
		$list->rows_per_page = 10;
		$list->params = $listModel->getDefaultParams();
		$list->template = 'default';

		if (!$list->store())
		{
			JError::raiseWarning(500, $list->getError());
		}
		echo "<li>Table for 'Contact Us' created</li></div>";
		if (!$form->store())
		{
			JError::raiseError(500, $form->getError());
		}
		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		$formModel->setId($form->id);
		$formModel->_form = $form;

		$listModel->setState('list.id', $list->id);
		$listModel->getItem();

		$elements = array('id' => array('plugin' => 'internalid', 'label' => 'id', 'group_id' => $groupId),
			'first_name' => array('plugin' => 'field', 'label' => 'First Name', 'group_id' => $groupId),
			'last_name' => array('plugin' => 'field', 'label' => 'Last Name', 'group_id' => $groupId),
			'email' => array('plugin' => 'field', 'label' => 'Email', 'group_id' => $groupId),
			'message' => array('plugin' => 'textarea', 'group_id' => $group2Id));

		return $listModel->createDBTable($list->db_table_name, $elements);
	}

	/**
	 * Empty all fabrik db tables of their data
	 *
	 * @return  void or JError
	 */

	public function reset()
	{
		$db = FabrikWorker::getDbo(true);
		$prefix = '#__{package}_';
		$tables = array('cron', 'elements', 'formgroup', 'forms', 'form_sessions', 'groups', 'joins', 'jsactions', 'packages', 'lists',
			'validations', 'visualizations');

		foreach ($tables as $table)
		{
			$db->setQuery("TRUNCATE TABLE " . $prefix . $table);
			if (!$db->query())
			{
				return JError::raiseError(500, $db->getErrorMsg() . ": " . $db->getQuery());
			}
		}
	}

	/**
	 * Drop all the lists db tables
	 *
	 * @return  void
	 */

	public function dropData()
	{
		$connModel = JModelLegacy::getInstance('Connection', 'FabrikFEModel');
		$connModel->setId($item->connection_id);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select("connection_id, db_table_name")->from('#__{package}_lists');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			$connModel->setId($row->connection_id);
			$c = $connModel->getConnection($row->connection_id);
			$fabrikDb = $connModel->getDb();
			if (!JError::isError($fabrikDb))
			{
				$fabrikDb->setQuery("DROP $row->db_table_name");
			}
			else
			{
				jexit("error with getting connection id " . $row->connection_id . " for " . $row->db_table_name);
			}
			$fabrikDb->query();
		}
	}

}
