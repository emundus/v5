<?php
/**
 * View to edit a list.
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a list.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.5
*/
class FabrikViewList extends JView
{

	/**
	 * Display a json object representing the table data.
	 *
	 * @return  void
	 */

	function display()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$model->setId($input->getInt('listid'));
		$this->setModel($model, true);
		$item = $model->getTable();
		$params = $model->getParams();
		$model->render();
		$this->emptyDataMessage = $params->get('empty_data_msg');
		$rowid = $input->getInt('rowid');
		list($this->headings, $groupHeadings, $this->headingClass, $this->cellClass) = $this->get('Headings');
		$data = $model->getData();
		$nav = $model->getPagination();
		$c = 0;
		foreach ($data as $groupk => $group)
		{
			foreach ($group as $i => $x)
			{
				$o = new stdClass;
				if (is_object($data[$groupk]))
				{
					$o->data = JArrayHelper::fromObject($data[$groupk]);
				}
				else
				{
					$o->data = $data[$groupk][$i];
				}
				$o->cursor = $i + $nav->limitstart;
				$o->total = $nav->total;
				$o->id = "list_" . $item->id."_row_" . @$o->data->__pk_val;
				$o->class = "fabrik_row oddRow" . $c;
				if (is_object($data[$groupk]))
				{
					$data[$groupk] = $o;
				}
				else
				{
					$data[$groupk][$i] = $o;
				}
				$c = 1 - $c;
			}
		}

		// $$$ hugh - heading[3] doesn't exist any more?  Trying [0] instead.
		$d = array('id' => $item->id, 'rowid' => $rowid, 'model'=>'list', 'data'=>$data,
				'headings' => $this->headings,
				'formid'=> $model->getTable()->form_id,
				'lastInsertedRow' => JFactory::getSession()->get('lastInsertedRow', 'test'));
		$d['nav'] = $nav->getProperties();
		$d['htmlnav'] = $params->get('show-table-nav', 1) ? $nav->getListFooter($model->getId(), $this->getTmpl()) : '';
		$d['calculations'] = $model->getCalculations();
		$msg = $app->getMessageQueue();
		if (!empty($msg))
		{
			$d['msg'] = $msg[0]['message'];
		}

		echo json_encode($d);
	}

	/**
	 * Get the view template name
	 *
	 * @return string template name
	 */

	private function getTmpl()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		$model = $this->getModel();
		$item = $model->getTable();
		$params = $model->getParams();
		if ($app->isAdmin())
		{
			$tmpl = $params->get('admin_template');
			if ($tmpl == -1 || $tmpl == '')
			{
				$tmpl = $input->get('layout', $item->template);
			}
		}
		else
		{
			$tmpl = $input->get('layout', $item->template);
		}
		return $tmpl;
	}

}
