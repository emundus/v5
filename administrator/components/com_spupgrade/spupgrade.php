<?php

/**
 * @package		SP Upgrade
 * @subpackage	Components
 * @copyright	SP CYEND - All rights reserved.
 * @author		SP CYEND
 * @link		http://www.cyend.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_spupgrade')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

include_once JPATH_COMPONENT . '/general.php';
include_once JPATH_COMPONENT . '/models/com.php';
jimport('joomla.filesystem.file');

// require helper file
JLoader::register('SPUpgradeHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'spupgrade.php');

// import joomla controller library
jimport('joomla.application.component.controller');                                           

// Get an instance of the controller prefixed by SPUpgrade
$controller = JController::getInstance('SPUpgrade');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
