<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsViewAdminuser extends FOFViewHtml
{
	private function randomFalseUsername()
	{
		$usernames = array(
			'42', 'clinteastwood', 'chucknorris', 'rantanplan', 'pinky',
			'brain', 'beavis', 'tux', 'larry', 'stevenseagal',
			'jeanclaudevandamme', 'jackiechan'
		);
		
		$id = 42;
		
		$dontadd = JFactory::getUser($id)->username;
		
		$ret = $dontadd;
		
		while($ret == $dontadd) {
			$rand = rand(0, count($usernames) - 1);
			$ret = $usernames[$rand];
		}
		
		return $ret;
	}
	
	protected function onBrowse($tpl = null)
	{
		$model = $this->getModel();
		$this->assign('hasDefaultAdmin',		$model->hasDefaultAdmin());
		$this->assign('getDefaultUsername',		$model->getDefaultUsername());
		$this->assign('fakeUsername',			$this->randomFalseUsername());
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			JHTML::_('behavior.framework');
		} else {
			JHTML::_('behavior.mootools');
		}
	}
}