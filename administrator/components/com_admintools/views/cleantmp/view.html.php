<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Load framework base classes
JLoader::import('joomla.application.component.view');

if(!class_exists('JoomlaCompatView')) {
	if(interface_exists('JView')) {
		abstract class JoomlaCompatView extends JViewLegacy {}
	} else {
		class JoomlaCompatView extends JView {}
	}
}

class AdmintoolsViewCleantmp extends JoomlaCompatView
{
	public function display($tpl = null)
	{
		$model = $this->getModel();
		$state = $model->getState('scanstate',false);

		$total = max(1, $model->totalFolders);
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
		}

		$this->assign('more', $more);
		$this->setLayout('default');

		if(version_compare(JVERSION, '3.0', 'ge')) {
			JHTML::_('behavior.framework');
		} else {
			JHTML::_('behavior.mootools');
		}

		$this->assign('percentage',		$percent);
		
		if($more) {
			$script = "window.addEvent( 'domready' ,  function() {\n";
			$script .= "document.forms.adminForm.submit();\n";
			$script .= "});\n";
			JFactory::getDocument()->addScriptDeclaration($script);
		}

		parent::display();
	}
}