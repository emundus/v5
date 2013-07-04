<?php
/**
 * Fabrik List Controller
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
 * Fabrik List Controller
 *
 * @static
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikControllerList extends JController
{

	/**
	 * Id used from content plugin when caching turned on to ensure correct element rendered
	 *
	 * @var  int
	 */
	public $cacheId = 0;

	/**
	 * Display the view
	 *
	 * @param   object  $model      list model
	 * @param   array   $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  null
	 */

	public function display($model = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$input = $app->input;
		$viewName = $input->get('view', 'list');
		$modelName = $viewName;
		$layout = $input->getWord('layout', 'default');
		$viewType = $document->getType();
		if ($viewType == 'pdf')
		{
			// In PDF view only shown the main component content.
			$input->set('tmpl', 'component');
		}
		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);
		$view->setLayout($layout);

		// Push a model into the view
		if (is_null($model) || $model == false)
		{
			$model = $this->getModel($modelName, 'FabrikFEModel');
		}
		if (!JError::isError($model) && is_object($model))
		{
			$view->setModel($model, true);
		}
		// Display the view
		$view->error = $this->getError();

		/**
		 * F3 cache with raw view gives error
		 * $$$ hugh - added list_disable_caching option, to disable caching on a per list basis, due to some funky behavior
		 * with pre-filtered lists and user ID's, which should be handled by the ID being in the $cacheid, but happens anyway.
		 * $$$ hugh @TODO - we really shouldn't cache for guests (user ID 0), unless we can come up with a way of creating a unique
		 * cache ID for guests.  We can't use their IP, as it could be two different machines behind a NAT'ing firewall.
		 */
		if ($model->getParams()->get('list_disable_caching', '0') === '1'
			|| in_array($input->get('format'), array('raw', 'csv', 'pdf', 'json', 'fabrikfeed')))
		{
			$view->display();
		}
		else
		{
			// Build unique cache id on url, post and user id
			$user = JFactory::getUser();
			$uri = JFactory::getURI();
			$uri = $uri->toString(array('path', 'query'));
			$cacheid = serialize(array($uri, $input->post, $user->get('id'), get_class($view), 'display', $this->cacheId));
			$cache = JFactory::getCache('com_fabrik', 'view');
			$cache->get($view, 'display', $cacheid);
		}
	}

	/**
	 * Reorder the data in the list
	 *
	 * @return  null
	 */

	public function order()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$modelName = $input->get('view', 'list');
		$model = $this->getModel($modelName, 'FabrikFEModel');
		$model->setId($input->getInt('listid'));
		$model->setOrderByAndDir();

		// $$$ hugh - unset 'resetfilters' in case it was set on QS of original list load.
		$input->set('resetfilters', 0);
		$input->set('clearfilters', 0);
		$this->display();
	}

	/**
	 * Clear filters
	 *
	 * @return  null
	 */

	public function clearfilter()
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_FABRIK_FILTERS_CLEARED'));
		/**
		 * $$$ rob 28/12/20111 changed from clearfilters as clearfilters removes jpluginfilters (filters
		 * set by content plugin which we want to remain sticky. Otherwise list clear button removes the
		 * content plugin filters
		 * $app->input->set('resetfilters', 1);
		 */

		/**
		 * $$$ rob 07/02/2012 if reset filters set in the menu options then filters not cleared
		 * so instead use replacefilters which doesnt look at the menu item parameters.
		 */
		$app->input->set('replacefilters', 1);
		$this->filter();
	}

	/**
	 * Filter the list data
	 *
	 * @return null
	 */

	public function filter()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$modelName = $input->get('view', 'list');
		$model = $this->getModel($modelName, 'FabrikFEModel');
		$model->setId($input->getInt('listid'));
		FabrikHelperHTML::debug('', 'list model: getRequestData');
		$request = $model->getRequestData();
		$model->storeRequestData($request);

		// $$$ rob pass in the model otherwise display() rebuilds it and the request data is rebuilt
		return $this->display($model);
	}

	/**
	 * Delete rows from list
	 *
	 * @return  null
	 */

	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		$model = $this->getModel('list', 'FabrikFEModel');
		$ids = $input->get('ids', array(), 'array');
		$listid = $input->getInt('listid');
		$limitstart = $input->getInt('limitstart' . $listid);
		$length = $input->getInt('limit' . $listid);

		$model->setId($listid);
		$oldtotal = $model->getTotalRecords();
		$model->deleteRows($ids);

		$total = $oldtotal - count($ids);

		$ref = $input->get('fabrik_referrer', 'index.php?option=com_' . $package . '&view=list&listid=' . $listid, 'string');

		// $$$ hugh - for some reason fabrik_referrer is sometimes empty, so a little defensive coding ...
		if (empty($ref))
		{
			$ref = $input->server->get('HTTP_REFERER', 'index.php?option=com_' . $package . '&view=list&listid=' . $listid, '', 'string');
		}
		if ($total >= $limitstart)
		{
			$newlimitstart = $limitstart - $length;
			if ($newlimitstart < 0)
			{
				$newlimitstart = 0;
			}
			$ref = str_replace('limitstart' . $listid . '=  . $limitstart', 'limitstart' . $listid . '=' . $newlimitstart, $ref);
			$context = 'com_' . $package . '.list.' . $model->getRenderContext() . '.';
			$app->setUserState($context . 'limitstart', $newlimitstart);
		}
		if ($input->get('format') == 'raw')
		{
			$input->set('view', 'list');
			$this->display();
		}
		else
		{
			// @TODO: test this
			$app->redirect($ref, count($ids) . ' ' . JText::_('COM_FABRIK_RECORDS_DELETED'));
		}
	}

	/**
	 * Empty a table of records and reset its key to 0
	 *
	 * @return  null
	 */

	public function doempty()
	{
		$model = $this->getModel('list', 'FabrikFEModel');
		$model->truncate();
		$this->display();
	}

	/**
	 * Run a list plugin
	 *
	 * @return  null
	 */

	public function doPlugin()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		$cid = $input->get('cid', array(0), 'array');
		$cid = $cid[0];
		$model = $this->getModel('list', 'FabrikFEModel');
		$model->setId($input->getInt('listid', $cid));
		/**
		 * $$$ rob need to ask the model to get its data here as if the plugin calls $model->getData
		 * then the other plugins are recalled which makes the current plugins params incorrect.
		 */
		$model->setLimits();
		$model->getData();

		// If showing n tables in article page then ensure that only activated table runs its plugin
		if ($input->getInt('id') == $model->get('id') || $input->get('origid', '') == '')
		{
			$msgs = $model->processPlugin();
			if ($input->get('format') == 'raw')
			{
				$input->set('view', 'list');
				$model->setRenderContext($model->getId());
				$context = 'com_' . $package . '.list' . $model->getRenderContext() . '.msg';
				$session = JFactory::getSession();
				$session->set($context, implode("\n", $msgs));
			}
			else
			{
				foreach ($msgs as $msg)
				{
					$app->enqueueMessage($msg);
				}
			}
		}
		// 3.0 use redirect rather than calling view() as that gave an sql error (joins seemed not to be loaded for the list)
		$format = $input->get('format', 'html');
		$defaultRef = 'index.php?option=com_' . $package . '&view=list&listid=' . $model->getId() . '&format=' . $format;
		if ($format !== 'raw')
		{
			$ref = $input->post->get('fabrik_referrer', $defaultRef, 'string');

			// For some reason fabrik_referrer is sometimes empty, so a little defensive coding ...
			if (empty($ref))
			{
				$ref = $input->server->get('HTTP_REFERER', $defaultRef, 'string');
			}
		}
		else
		{
			$ref = $defaultRef;
		}
		$app->redirect($ref);
	}

	/**
	 * Called via ajax when element selected in advanced search popup window
	 *
	 * @return  null
	 */

	public function elementFilter()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $input->getInt('id');
		$model = $this->getModel('list', 'FabrikFEModel');
		$model->setId($id);
		echo $model->getAdvancedElementFilter();
	}

}
