<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.ftp
 * @copyright   Copyright (C) 2005 Hugh Messenger. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * FTP Form results to a given location
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.ftp
 * @since       3.0.7
 */

class plgFabrik_FormFtp extends plgFabrik_Form
{

	/**
	 * Posted form keys that we don't want to include in the message
	 * This is basically the fileupload elements
	 *
	 * @var array
	 */
	protected $dontEmailKeys = null;

	/**
	 * Process the plugin, called when form is submitted
	 *
	 * @param   object  $params      Plugin parameters
	 * @param   object  &$formModel  Form model
	 *
	 * @return  bool
	 */

	public function onAfterProcess($params, &$formModel)
	{
		jimport('joomla.mail.helper');

		$app = JFactory::getApplication();
		$input = $app->input;
		$user = JFactory::getUser();
		$config	= JFactory::getConfig();
		$db = JFactory::getDbo();

		$this->formModel = $formModel;
		$formParams	= $formModel->getParams();
		$ftpTemplate = JPath::clean(JPATH_SITE . '/plugins/fabrik_form/ftp/tmpl/' . $params->get('ftp_template', ''));

		$this->data = array_merge($formModel->_formData, $this->getEmailData());

		if (!$this->shouldProcess('ftp_conditon', null, $formModel))
		{
			return;
		}

		$contentTemplate = $params->get('ftp_template_content');
		$content = $contentTemplate != '' ? $this->_getConentTemplate($contentTemplate) : '';

		if (JFile::exists($ftpTemplate))
		{
			if (JFile::getExt($ftpTemplate) == 'php')
			{
				$message = $this->_getPHPTemplateFtp($ftpTemplate);
				if ($message === false)
				{
					return;
				}
			}
			else
			{
				$message = $this->_getTemplateFtp($ftpTemplate);
			}
			$message = str_replace('{content}', $content, $message);
		}
		else
		{
			$message = $contentTemplate != '' ? $content : $this->_getTextFtp();
		}

		$cc = null;
		$bcc = null;
		$w = new FabrikWorker();

		// $$$ hugh - test stripslashes(), should be safe enough.
		$message = stripslashes($message);

		$editURL = COM_FABRIK_LIVESITE . "index.php?option=com_fabrik&amp;view=form&amp;fabrik=" . $formModel->get('id') . "&amp;rowid=" . $input->get('rowid', '', 'string');
		$viewURL = COM_FABRIK_LIVESITE . "index.php?option=com_fabrik&amp;view=details&amp;fabrik=" . $formModel->get('id') . "&amp;rowid=" . $input->get('rowid', '', 'string');
		$editlink = "<a href=\"$editURL\">" . JText::_('EDIT') . "</a>";
		$viewlink = "<a href=\"$viewURL\">" . JText::_('VIEW') . "</a>";
		$message = str_replace('{fabrik_editlink}', $editlink, $message);
		$message = str_replace('{fabrik_viewlink}', $viewlink, $message);
		$message = str_replace('{fabrik_editurl}', $editURL, $message);
		$message = str_replace('{fabrik_viewurl}', $viewURL, $message);


		$ftp_filename = $params->get('ftp_filename', '');
		$ftp_filename = $w->parseMessageForPlaceholder($ftp_filename, $this->data, false);
		$ftp_eval_filename = (int) $params->get('ftp_eval_filename', '0');
		if ($ftp_eval_filename)
		{
			$ftp_filename = @eval($ftp_filename);
			FabrikWorker::logEval($email_to_eval, 'Caught exception on eval in ftp filename eval : %s');
		}
		if (empty($ftp_filename))
		{
			$ftp_filename = 'fabrik_ftp_' . md5( uniqid() ) . '.txt';
			// JError::raiseNotice(500, JText::sprintf('PLG_FTP_NO_FILENAME', $email));
		}

		$ftp_host = $w->parseMessageForPlaceholder($params->get('ftp_host', ''), $this->data, false);
		$ftp_port = $w->parseMessageForPlaceholder($params->get('ftp_port', '21'), $this->data, false);
		$ftp_chdir = $w->parseMessageForPlaceholder($params->get('ftp_chdir', ''), $this->data, false);
		$ftp_user = $w->parseMessageForPlaceholder($params->get('ftp_user', ''), $this->data, false);
		$ftp_password = $w->parseMessageForPlaceholder($params->get('ftp_password', ''), $this->data, false);

		$tmp_dir = rtrim($config->getValue('config.tmp_path'), '/');
		if (empty($tmp_dir) || !JFolder::exists($tmp_dir))
		{
			JError::raiseError(500, 'PLG_FORM_FTP_NO_JOOMLA_TEMP_DIR');
			return false;
		}
		$tmp_file = $tmp_dir . '/fabrik_ftp_' . md5(uniqid());
		$message = $w->parseMessageForPlaceholder($message, $this->data, true, false);
		if (JFile::write($tmp_file, $message))
		{
			$conn_id = ftp_connect($ftp_host, $ftp_port);
			if ($conn_id)
			{
				if (@ftp_login($conn_id, $ftp_user, $ftp_password))
				{
					if (!empty($ftp_chdir))
					{
						if (!ftp_chdir($conn_id, $ftp_chdir))
						{
							JError::raiseNotice(500, JText::_('PLG_FORM_FTP_COULD_NOT_CHDIR'));
							JFile::delete($tmp_file);
							return false;
						}
					}
					if (!ftp_put($conn_id, $ftp_filename, $tmp_file, FTP_ASCII))
					{
						JError::raiseNotice(500, JText::_('PLG_FORM_FTP_COULD_NOT_SEND_FILE'));
						JFile::delete($tmp_file);
						return false;
					}
				}
				else
				{
					JError::raiseNotice(500, JText::_('PLG_FORM_FTP_COULD_NOT_LOGIN'));
					JFile::delete($tmp_file);
					return false;
				}
			}
			else
			{
				JError::raiseError(500, 'PLG_FORM_FTP_COULD_NOT_CONNECT');
				JFile::delete($tmp_file);
				return false;
			}
		}
		else
		{
			JError::raiseError(500, 'PLG_FORM_FTP_COULD_NOT_WRITE_TEMP_FILE');
			JFile::delete($tmp_file);
			return false;
		}
		JFile::delete($tmp_file);
		return true;
	}

	/**
	 * Use a php template for advanced email templates, partularly for forms with repeat group data
	 *
	 * @param   string  $tmpl  Path to template
	 *
	 * @return  string  Email message
	 */

	protected function _getPHPTemplateFtp($tmpl)
	{
		// Start capturing output into a buffer
		ob_start();
		$result = require $tmpl;
		$message = ob_get_contents();
		ob_end_clean();
		if ($result === false)
		{
			return false;
		}
		return $message;
	}


	/**
	 * Get an array of keys we dont want to email to the user
	 *
	 * @return  array
	 */

	protected function getDontEmailKeys()
	{
		if (is_null($this->dontEmailKeys))
		{
			$this->dontEmailKeys = array();
			foreach ($_FILES as $key => $file)
			{
				$this->dontEmailKeys[] = $key;
			}
		}
		return $this->dontEmailKeys;
	}

	/**
	 * Template email handling routine, called if email template specified
	 *
	 * @param   string  $ftpTemplate  Path to template
	 *
	 * @return  string  Email message
	 */

	protected function _getTemplateFtp($ftpTemplate)
	{
		jimport('joomla.filesystem.file');
		return JFile::read($ftpTemplate);
	}

	/**
	 * Get content item template
	 *
	 * @param   int  $contentTemplate  Content template
	 *
	 * @return  string  Content item html (translated with Joomfish if installed)
	 */

	protected function _getConentTemplate($contentTemplate)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('introtext, ' . $db->quoteName('fulltext'))->from('#__content')->where('id = ' . (int) $contentTemplate);
			$db->setQuery($query);
			$res = $db->loadObject();
		}
		else
		{
			JModel::addIncludePath(COM_FABRIK_BASE . 'components/com_content/models');
			$articleModel = JModel::getInstance('Article', 'ContentModel');
			$res = $articleModel->getItem($contentTemplate);
		}
		return $res->introtext . ' ' . $res->fulltext;
	}

	/**
	 * Default template handling routine, called if no template specified
	 *
	 * @return  string  Email message
	 */

	protected function _getTextFtp()
	{
		$data = $this->getEmailData();
		$config = JFactory::getConfig();
		$ignore = $this->getDontEmailKeys();
		$message = "";
		$pluginManager = FabrikWorker::getPluginManager();
		$groupModels = $this->formModel->getGroupsHiarachy();
		foreach ($groupModels as &$groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as &$elementModel)
			{
				$element = $elementModel->getElement();

				// @TODO - how about adding a 'renderEmail()' method to element model, so specific element types  can render themselves?
				$key = (!array_key_exists($element->name, $data)) ? $elementModel->getFullName(false, true, false) : $element->name;
				if (!in_array($key, $ignore))
				{
					$val = '';
					if (is_array(JArrayHelper::getValue($data, $key)))
					{
						// Repeat group data
						foreach ($data[$key] as $k => $v)
						{
							if (is_array($v))
							{
								$val = implode(", ", $v);
							}
							$val .= count($data[$key]) == 1 ? ": $v<br />" : ($k++) . ": $v<br />";
						}
					}
					else
					{
						$val = JArrayHelper::getValue($data, $key);
					}
					$val = FabrikString::rtrimword($val, "<br />");
					$val = stripslashes($val);

					// Set $val to default value if empty
					if ($val == '')
					{
						$val = " - ";
					}
					// Don't add a second ":"
					$label = trim(strip_tags($element->label));
					$message .= $label;
					if (strlen($label) != 0 && JString::strpos($label, ':', JString::strlen($label) - 1) === false)
					{
						$message .= ':';
					}
					$message .= "<br />" . $val . "<br /><br />";
				}
			}
		}
		$message = JText::_('Email from') . ' ' . $config->get('sitename') . '<br />' . JText::_('Message') . ':'
			. "<br />===================================<br />" . "<br />" . stripslashes($message);
		return $message;
	}

}
