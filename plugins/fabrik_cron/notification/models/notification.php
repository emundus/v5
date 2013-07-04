<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.notification
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * The cron notification plugin model.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.notification
 * @since       3.0
 */

class fabrikModelNotification extends JModel
{

	/**
	 * Get the current logged in users notifications
	 *
	 * @return array
	 */

	function getUserNotifications()
	{
		$rows = $this->getRows();
		if (!$rows)
		{
			$this->makeDbTable();
			$rows = $this->getRows();
		}

		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		foreach ($rows as &$row)
		{
			/*
			 * {observer_name, creator_name, event, record url
			 * dear %s, %s has %s on %s
			 */
			$event = JText::_($row->event);
			list($listid, $formid, $rowid) = explode('.', $row->reference);

			$listModel->setId($listid);
			$data = $listModel->getRow($rowid);
			$row->url = JRoute::_('index.php?option=com_fabrik&view=details&listid=' . $listid . '&formid=' . $formid . '&rowid=' . $rowid);
			$row->title = $row->url;
			foreach ($data as $key => $value)
			{
				$k = JString::strtolower(array_pop(explode('___', $key)));
				if ($k == 'title')
				{
					$row->title = $value;
				}
			}
		}
		return $rows;
	}

	protected function makeDbTable()
	{
		$db = FabrikWorker::getDbo();

		// Attempt to create the db table?
		$sql = JFile::read(COM_FABRIK_BASE . '/plugins/fabrik_cron/notification/sql/install.mysql.uft8.sql');
		$sqls = explode(";", $sql);
		if (!empty($sqls))
		{
			foreach ($sqls as $sql)
			{
				if (trim($sql) !== '')
				{
					$db->setQuery($sql);

					if (!$db->execute())
					{
						JError::raiseError(500, $db->getErrorMsg());
					}
				}
			}
		}
	}

	protected function getRows()
	{
		$user = JFactory::getUser();
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__{package}_notification')->where('user_id = ' . (int) $user->get('id'));
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * Delete a notification
	 *
	 * @return  void
	 */

	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');
		$ids = JRequest::getVar('cid', array());
		JArrayHelper::toInteger($ids);
		if (empty($ids))
		{
			return;
		}
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__{package}_notification')->where('id IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);
		$db->execute();
	}

}
