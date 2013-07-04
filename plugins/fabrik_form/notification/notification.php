<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.notification
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Allows users to subscribe to updates to a given row and receive emails
 * of those updates. Used in conjunction with the cron notification plug-in
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.notification
 * @since       3.0
 */

class plgFabrik_FormNotification extends plgFabrik_Form
{

	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int  $c  plugin counter
	 *
	 * @return  string  html
	 */

	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * Sets up HTML to be injected into the form's bottom
	 *
	 * @param   object  $params     params
	 * @param   object  $formModel  form model
	 *
	 * @return void
	 */

	public function getBottomContent($params, $formModel)
	{
		$user = JFactory::getUser();
		if ($user->get('id') == 0)
		{
			$this->html = JText::_('PLG_CRON_NOTIFICATION_SIGN_IN_TO_RECEIVE_NOTIFICATIONS');
			return;
		}
		if ($params->get('send_mode') == '1')
		{
			return;
		}
		$opts = new stdClass;
		$opts->listid = $formModel->getListModel()->getId();
		$opts->formid = $formModel->getId();
		$opts->rowid = $formModel->_rowId;
		$opts->senderBlock = JRequest::getCmd('view') == 'form' ? 'form_' : 'details_';
		$opts->senderBlock .= $formModel->getId();
		$opts = json_encode($opts);
		$id = uniqid('fabrik_notification');
		if ($params->get('notification_ajax', 0) == 1)
		{
			FabrikHelperHTML::script('components/com_fabrik/plugins/form/notification/notify.js');
			$script = "head.ready(function() {
				var notify = new Notify('$id', $opts);
 			});";

			FabrikHelperHTML::addScriptDeclaration($script);
		}
		// See if the checkbox should be checked
		$db = FabrikWorker::getDbo();
		$ref = $this->getRef($formModel->getListModel()->getId());
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')->from('#__{package}_notification')->where('user_id = ' . (int) $user->get('id') . ' AND reference = ' . $ref);
		$db->setQuery($query);
		$found = $db->loadResult();
		$checked = $found ? 'checked="checked"' : '';
		$this->html = '
		<label><input id="' . $id . '" ' . $checked . ' type="checkbox" name="fabrik_notification" class="input" value="1"  />
		 ' . JText::_('PLG_CRON_NOTIFICATION_NOTIFY_ME') . '</label>';
	}

	/**
	 * Toggle notification
	 *
	 * @return  void
	 */

	public function toggleNotification()
	{
		// $$$ rob yes this looks odd but its right - as the js mouseup event is fired before the checkbox checked value changes
		$notify = JRequest::getVar('notify') == 'true' ? false : true;
		$params = $this->getParams();
		$this->process($notify, 'observer', $params);
	}

	/**
	 * Get notification reference
	 *
	 * @param   int  $listid  default list id
	 *
	 * @return string
	 */

	protected function getRef($listid = 0)
	{
		$db = FabrikWorker::getDbo();
		return $db->quote(JRequest::getInt('listid', $listid) . '.' . JRequest::getInt('formid', 0) . '.' . JRequest::getInt('rowid', 0));
	}

	/**
	 * Process the plugin
	 *
	 * @param   bool       $add     add or remove notification
	 * @param   string     $why     reason for notification
	 * @param   JRegistry  $params  params
	 *
	 * @return  void
	 */

	protected function process($add = true, $why = 'author', $params)
	{

		$db = FabrikWorker::getDbo();
		$user = JFactory::getUser();
		$userid = (int) $user->get('id');
		$ref = $this->getRef();
		$query = $db->getQuery(true);
		$fields = array('reference = ' . $ref);

		if ($params->get('send_mode', 0) == '0')
		{
			$fields[] = 'user_id = ' . $userid;

			// Was using ON DUPLICATE KEY but that is mySQL specific and I couldn't see how an update could have occurred
			if ($add)
			{
				echo JText::_('PLG_CRON_NOTIFICATION_ADDED');
				$fields[] = 'reason = ' . $db->quote($why);
				$query->insert('#__{package}_notification')->set($fields);
				$db->setQuery($query);
				$db->execute();

			}
			else
			{
				$query->delete('#__{package}_notification')->where($fields);
				echo JText::_('PLG_CRON_NOTIFICATION_REMOVED');
				$db->setQuery($query);
				$db->execute();
			}
		}
		else
		{
			$sendTo = (array) $params->get('sendto');
			$userids = $this->getUsersInGroups($sendTo);

			foreach ($userids as $userid)
			{
				$query->clear('set');
				$fields2 = array_merge($fields, array('user_id = ' . $userid));
				$query->insert('#__{package}_notification')->set($fields2);
				$db->setQuery($query);
				$db->execute();
			}

		}
	}

	/**
	 * Test if the notifications should be fired
	 *
	 * @param   object  $formModel  form model
	 * @param   JRegistry  $params  params
	 *
	 * @return  bool
	 */

	protected function triggered($formModel, $params)
	{
		if ($params->get('send_mode', 0) == 0)
		{
			$user = JFactory::getUser();
			return $user->get('id') == 0 ? false : true;
		}
		else
		{
			$triggerEl = $formModel->getElement($params->get('trigger'), true);
			$trigger = $triggerEl->getFullName();
			$data = $formModel->getData();
			return JArrayHelper::getValue($data, $trigger) == $params->get('trigger_value') ? true : false;
		}
	}

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onAfterProcess($params, &$formModel)
	{
		if ($params->get('notification_ajax', 0) == 1)
		{
			return;
		}
		$user = JFactory::getUser();
		$userid = $user->get('id');
		$notify = JRequest::getInt('fabrik_notification', 0);

		if (!$this->triggered($formModel, $params))
		{
			return;
		}

		$why = JRequest::getInt('rowid') == 0 ? 'author' : 'editor';
		$this->process($notify, $why, $params);

		// Add entry indicating the form has been updated this record will then be used by the cron plugin to
		// see which new events have been generated and notify subscribers of said events.
		$db = FabrikWorker::getDbo();
		$event = JRequest::getInt('rowid') == 0 ? $db->quote(JText::_('RECORD_ADDED')) : $db->quote(JText::_('RECORD_UPDATED'));
		$date = JFactory::getDate();
		$date = $db->quote($date->toSql());
		$ref = $this->getRef();
		$msg = $notify ? JText::_('PLG_CRON_NOTIFICATION_ADDED') : JText::_('PLG_CRON_NOTIFICATION_REMOVED');
		$app = JFactory::getApplication();
		$app->enqueueMessage($msg);
		$query = $db->getQuery(true);
		$fields = array('reference = ' . $ref, 'event = ' . $event, 'date_time = ' . $date);
		if ($params->get('send_mode') == '0')
		{
			$fields = array('user_id = ' . $userid);
			$query->insert('#__{package}_notification_event')->set($fields);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$sendTo = (array) $params->get('sendto');
			$userids = $this->getUsersInGroups($sendTo);
			$query->clear();
			foreach ($userids as $userid)
			{
				$query->clear('set');
				$fields2 = array_merge($fields, array('user_id = ' . $userid));
				$query->insert('#__{package}_notification_event')->set($fields2);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

}
