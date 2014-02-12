<?php
/**
 * Jcrm entry point file for jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Décision Publique
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}

// Create the controller
$classname	= 'JcrmController'.$controller;
$controller = new $classname();

$user = JFactory::getUser();
if ($user->guest) {
	$controller->setRedirect('index.php', JText::_("You must login to see the content."), 'error');
} else { 
	// Perform the Request task
	$controller->execute( JRequest::getWord( 'task' ) ); 
}


// Redirect if set by the controller
$controller->redirect();

?>
