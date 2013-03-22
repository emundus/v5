<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewFixperms extends FOFViewHtml
{
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$state = $model->getState('scanstate',false);

		$total = $model->totalFolders;
		$done = $model->doneFolders;

		if($state)
		{
			if($total > 0)
			{
				$percent = min(max(round(100 * $done / $total),1),100);
			}

			$more = true;
		}
		else
		{
			$percent = 100;
			$more = false;

			JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
		}

		$this->assign('more', $more);
		$this->assign('percentage', $percent);
		$this->setLayout('default');

		if($more) {
			$script = "window.addEvent( 'domready' ,  function() {\n";
			$script .= "document.forms.adminForm.submit();\n";
			$script .= "});\n";
			JFactory::getDocument()->addScriptDeclaration($script);
		}
	}
	
	public function onRun()
	{
		$this->onBrowse();
	}
}