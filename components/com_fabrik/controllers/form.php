<?php
/**
 * Fabrik From Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik From Controller
 *
 * @static
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikControllerForm extends JController
{

	/**
	 * Is the view rendered from the J content plugin
	 *
	 * @var  bool
	 */
	public $isMambot = false;

	/**
	 * Id used from content plugin when caching turned on to ensure correct element rendered
	 *
	 * @var  int
	 */
	public $cacheId = 0;

	/**
	 * Magic method to convert the object to a string gracefully.
	 *
	 * $$$ hugh - added 08/05/2012.  No idea what's going on, but I had to add this to stop
	 * the classname 'FabrikControllerForm' being output at the bottom of the form, when rendered
	 * through a Fabrik form module.  See:
	 *
	 * https://github.com/Fabrik/fabrik/issues/398
	 *
	 * @return  string  empty string.
	 */

	public function __toString()
	{
		return '';
	}

	/**
	 * Inline edit control
	 *
	 * @since   3.0b
	 *
	 * @return  null
	 */

	public function inlineedit()
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType = $document->getType();
		$viewLayout = $input->get('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

		// @TODO check for cached version
		$view->inlineEdit();
	}

	/**
	 * Display the view
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 */

	public function display($cachable = false, $urlparams = false)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$session = JFactory::getSession();
		$document = JFactory::getDocument();
		$viewName = $input->get('view', 'form');
		$modelName = $viewName;
		if ($viewName == 'emailform')
		{
			$modelName = 'form';
		}

		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		// Push a model into the view (may have been set in content plugin already
		$model = !isset($this->_model) ? $this->getModel($modelName, 'FabrikFEModel') : $this->_model;
		$model->isMambot = $this->isMambot;
		$model->packageId = $app->input->getInt('packageId');

		// Test for failed validation then page refresh
		$model->getErrors();
		if (!JError::isError($model) && is_object($model))
		{
			$view->setModel($model, true);
		}
		$view->isMambot = $this->isMambot;

		// Get data as it will be needed for ACL when testing if current row is editable.
		$model->getData();

		// If we can't edit the record redirect to details view
		if ($model->checkAccessFromListSettings() <= 1)
		{
			$app = JFactory::getApplication();
			$input = $app->input;
			if ($app->isAdmin())
			{
				$url = 'index.php?option=com_fabrik&task=details.view&formid=' . $input->getInt('formid') . '&rowid=' . $input->get('rowid', '', 'string');
			}
			else
			{
				$url = 'index.php?option=com_' . $package . '&view=details&formid=' . $input->getInt('formid') . '&rowid=' . $input->get('rowid', '', 'string');
			}
			$msg = $model->aclMessage();
			$this->setRedirect(JRoute::_($url), $msg, 'notice');
			return;
		}
		// Display the view
		$view->error = $this->getError();

		if (in_array($input->get('format'), array('raw', 'csv', 'pdf')))
		{
			$view->display();
		}
		else
		{
			$user = JFactory::getUser();
			$uri = JFactory::getURI();
			$uri = $uri->toString(array('path', 'query'));
			$cacheid = serialize(array($uri, $input->post, $user->get('id'), get_class($view), 'display', $this->cacheId));
			$cache = JFactory::getCache('com_' . $package, 'view');
			ob_start();
			$cache->get($view, 'display', $cacheid);
			$contents = ob_get_contents();
			ob_end_clean();

			// Workaround for token caching
			$token = JSession::getFormToken();
			$search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="' . $token . '" value="1" />';
			echo preg_replace($search, $replacement, $contents);
		}
		return $this;
	}

	/**
	 * Process the form
	 *
	 * @return  null
	 */

	public function process()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		if ($input->get('format', '') == 'raw')
		{
			error_reporting(error_reporting() ^ (E_WARNING | E_NOTICE));
		}
		$model = $this->getModel('form', 'FabrikFEModel');
		$viewName = $input->get('view', 'form');
		$view = $this->getView($viewName, JFactory::getDocument()->getType());

		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		$model->setId($input->getInt('formid', 0));
		$model->packageId = $input->getInt('packageId');
		$this->isMambot = $input->get('isMambot', 0);
		$form = $model->getForm();
		$model->_rowId = $input->get('rowid', '');

		/**
		 * $$$ hugh - need this in plugin manager to be able to treat a "Copy" form submission
		 * as 'new' for purposes of running plugins.  Rob's comment in model process() seems to
		 * indicate that origRowId was for this purposes, but it doesn't work, 'cos always has a value.
		 */
		if ($input->get('Copy', '') != '')
		{
			$model->copyingRow(true);
		}

		// Check for request forgeries
		if ($model->spoofCheck())
		{
			JSession::checkToken() or die('Invalid Token');
		}

		$validated = $model->validate();
		if (!$validated)
		{
			// If its in a module with ajax or in a package or inline edit
			if ($input->get('fabrik_ajax'))
			{
				if ($input->getInt('elid', 0) !== 0)
				{
					// Inline edit
					$eMsgs = array();
					$errs = $model->getErrors();

					// Only raise errors for fields that are present in the inline edit plugin
					$toValidate = array_keys($input->get('toValidate', array(), 'array'));
					foreach ($errs as $errorKey => $e)
					{
						if (in_array($errorKey, $toValidate) && count($e[0]) > 0)
						{
							array_walk_recursive($e, array('FabrikString', 'forHtml'));
							$eMsgs[] = count($e[0]) === 1 ? '<li>' . $e[0][0] . '</li>' : '<ul><li>' . implode('</li><li>', $e[0]) . '</ul>';
						}
					}
					if (!empty($eMsgs))
					{
						$eMsgs = '<ul>' . implode('</li><li>', $eMsgs) . '</ul>';
						header('HTTP/1.1 500 ' . JText::_('COM_FABRIK_FAILED_VALIDATION') . $eMsgs);
						jexit();
					}
					else
					{
						$validated = true;
					}
				}
				else
				{
					// Package / model
					echo $model->getJsonErrors();
				}
				if (!$validated)
				{
					return;
				}
			}
			if (!$validated)
			{
				$this->savepage();
				if ($this->isMambot)
				{
					$this->setRedirect($this->getRedirectURL($model, false));
				}
				else
				{
					/**
					 * $$$ rob - http://fabrikar.com/forums/showthread.php?t=17962
					 * couldn't determine the exact set up that triggered this, but we need to reset the rowid to -1
					 * if reshowing the form, otherwise it may not be editable, but rather show as a detailed view
					 */
					if ($input->get('usekey') !== '')
					{
						JRequest::setVar('rowid', -1);
					}
					$view->display();
				}
				return;
			}
		}
		// Reset errors as validate() now returns ok validations as empty arrays
		$model->clearErrors();

		$model->process();
		if ($input->getInt('elid', 0) !== 0)
		{
			// Inline edit show the edited element - ignores validations for now
			echo $model->inLineEditResult();
			return;
		}

		// Check if any plugin has created a new validation error
		if ($model->hasErrors())
		{
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}

		$listModel = $model->getListModel();
		$listModel->set('_table', null);

		$url = $this->getRedirectURL($model);
		$msg = $this->getRedirectMessage($model);

		// @todo -should get handed off to the json view to do this
		if ($input->getInt('fabrik_ajax') == 1)
		{
			// $$$ hugh - adding some options for what to do with redirect when in content plugin
			// Should probably do this elsewhere, but for now ...
			$redirect_opts = array('msg' => $msg, 'url' => $url, 'baseRedirect' => $this->baseRedirect, 'rowid' => $input->get('rowid', '', 'string'));
			if (!$this->baseRedirect && $this->isMambot)
			{
				$session = JFactory::getSession();
				$context = $model->getRedirectContext();
				$redirect_opts['redirect_how'] = $session->get($context . 'redirect_content_how', 'popup');
				$redirect_opts['width'] = (int) $session->get($context . 'redirect_content_popup_width', '300');
				$redirect_opts['height'] = (int) $session->get($context . 'redirect_content_popup_height', '300');
				$redirect_opts['x_offset'] = (int) $session->get($context . 'redirect_content_popup_x_offset', '0');
				$redirect_opts['y_offset'] = (int) $session->get($context . 'redirect_content_popup_y_offset', '0');
				$redirect_opts['title'] = $session->get($context . 'redirect_content_popup_title', '');
				$redirect_opts['reset_form'] = $session->get($context . 'redirect_content_reset_form', '1') == '1';
			}
			elseif ($this->isMambot)
			{
				// $$$ hugh - special case to allow custom code to specify that
				// the form should not be cleared after a failed AJAX submit
				$session = JFactory::getSession();
				$context = 'com_fabrik.form.' . $model->get('id') . '.redirect.';
				$redirect_opts['reset_form'] = $session->get($context . 'redirect_content_reset_form', '1') == '1';
			}
			// Let form.js handle the redirect logic (will also send out a
			echo json_encode($redirect_opts);
			return;
		}

		if ($input->get('format') == 'raw')
		{
			JRequest::setVar('view', 'list');
			$this->display();
			return;
		}
		else
		{
			$this->setRedirect($url, $msg);
		}
	}

	/**
	 * Get redirect message
	 *
	 * @param   object  $model  form model
	 *
	 * @since   3.0
	 *
	 * @deprecated - use form model getRedirectMessage instead
	 *
	 * @return  string  redirect message
	 */

	protected function getRedirectMessage($model)
	{
		return $model->getRedirectMessage();
	}

	/**
	 * Get redirect URL
	 *
	 * @param   object  $model       form model
	 * @param   bool    $incSession  set url in session?
	 *
	 * @since 3.0
	 *
	 * @deprecated - use form model getRedirectUrl() instead
	 *
	 * @return   string  redirect url
	 */

	protected function getRedirectURL($model, $incSession = true)
	{
		$res = $model->getRedirectURL($incSession, $this->isMambot);
		$this->baseRedirect = $res['baseRedirect'];
		return $res['url'];
	}

	/**
	 * Validate via ajax
	 *
	 * @return  null
	 */

	public function ajax_validate()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = $this->getModel('form', 'FabrikFEModel');
		$model->setId($input->getInt('formid', 0));
		$model->getForm();
		$model->_rowId = $input->get('rowid', '', 'string');
		$model->validate();
		$data = array('modified' => $model->_modifiedValidationData);

		// Validating entire group when navigating form pages
		$data['errors'] = $model->_arErrors;
		echo json_encode($data);
	}

	/**
	 * Save a form's page to the session table
	 *
	 * @return  null
	 */

	public function savepage()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = $this->getModel('Formsession', 'FabrikFEModel');
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setId($input->getInt('formid'));
		$model->savePage($formModel);
	}

	/**
	 * Clear down any temp db records or cookies
	 * containing partially filled in form data
	 *
	 * @return  null
	 */

	public function removeSession()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$sessionModel = $this->getModel('formsession', 'FabrikFEModel');
		$sessionModel->setFormId($input->getInt('formid', 0));
		$sessionModel->setRowId($input->getInt('rowid', 0));
		$sessionModel->remove();
		$this->display();
	}

	/**
	 * Called via ajax to page through form records
	 *
	 * @return  null
	 */

	public function paginate()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = $this->getModel('Form', 'FabrikFEModel');
		$model->setId($input->getInt('formid'));
		$model->paginateRowId($input->get('dir'));
		$this->display();
	}

	/**
	 * Delete a record from a form
	 *
	 * @return  null
	 */

	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$model = $this->getModel('list', 'FabrikFEModel');
		$ids = array($input->get('rowid', 0));

		$listid = $input->getInt('listid');
		$limitstart = $input->getInt('limitstart' . $listid);
		$length = $input->getInt('limit' . $listid);

		$oldtotal = $model->getTotalRecords();
		$model->setId($listid);
		$model->deleteRows($ids);

		$total = $oldtotal - count($ids);

		$ref = $input->get('fabrik_referrer', 'index.php?option=com_' . $package . '&view=list&listid=' . $listid, 'string');
		if ($total >= $limitstart)
		{
			$newlimitstart = $limitstart - $length;
			if ($newlimitstart < 0)
			{
				$newlimitstart = 0;
			}
			$ref = str_replace("limitstart$listid=$limitstart", "limitstart$listid=$newlimitstart", $ref);
			$app = JFactory::getApplication();
			$context = 'com_' . $package . '.list.' . $model->getRenderContext() . '.';
			$app->setUserState($context . 'limitstart', $newlimitstart);
		}
		if ($input->get('format') == 'raw')
		{
			JRequest::setVar('view', 'list');
			$this->display();
		}
		else
		{
			// @TODO: test this
			$app->redirect($ref, count($ids) . " " . JText::_('COM_FABRIK_RECORDS_DELETED'));
		}
	}
}
