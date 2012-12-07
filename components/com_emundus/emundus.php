<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
*/
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname    = 'EmundusController'.$controller;
$controller   = new $classname( );
 
$user =& JFactory::getUser();
$name = JRequest::getWord('view');

if ($user->guest && $name != 'emailalert') {
	$controller->setRedirect('index.php', JText::_("You must login to see the content."), 'error');
} else { 
	// Perform the Request task
	$controller->execute( JRequest::getWord( 'task' ) ); 
}
// Redirect if set by the controller
$controller->redirect();
?>