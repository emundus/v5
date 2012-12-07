<?php
/**
 * @package   	eMundus
 * @copyright 	Copyright © 2009-2012 Benjamin Rivalland. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * eMundus is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.controller');

/**
 * Plugins Component Controller
 *
 * @package		Joomla
 * @subpackage	eMundus
 * @since 1.5
 */
class EmundusControllerConfig extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{		
		parent::__construct();
		die();
		$this->registerTask( 'apply', 'save' );
	}

	function display()
	{
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'config';
			JRequest::setVar('view', $default );
		}
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('RESTRICTED');

		$db 	= JFactory::getDBO();
		$task 	= $this->getTask();

		// get params
		$component 	= JComponentHelper::getComponent('com_emundus');
		// create params object 
		$params 	= $component->params;

		$registry = new JRegistry();
		$registry->loadArray(JRequest::getVar('params', '', 'POST', 'ARRAY'));

		if (!$component->check()) {
			JError::raiseError(500, $component->getError() );
		}
		if (!$component->store()) {
			JError::raiseError(500, $component->getError() );
		}
		$component->checkin();
	
		$msg = JText::sprintf('CONFIG_SAVED');		
	
		switch ( $task )
		{
			case 'apply':
				$this->setRedirect( 'index.php?option=com_emundus&view=config', $msg );
				break;

			case 'save':
			default:
				$this->setRedirect( 'index.php?option=com_emundus&view=panel', $msg );
				break;
		}
	}
	
	
}
?>