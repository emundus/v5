<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login & Register
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
$revision = '1.226';
?>
<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!isset($_REQUEST['view']) && !isset($_REQUEST['task'])) {
  if(version_compare(JVERSION,'1.6.0','ge'))
    header('Location: '.JRoute::_('index.php?option=com_modules&filter_module=mod_improved_ajax_login', false));
  else header('Location: '.JRoute::_('index.php?option=com_modules&filter_type=mod_improved_ajax_login', false));
  exit;
}

if (version_compare(JVERSION,'3.0.0','ge')) {
  class JoomlaController extends JControllerLegacy {}
  class JoomlaView extends JViewLegacy {}
  class JoomlaModel extends JModelLegacy {}
} else {
  jimport( 'joomla.application.component.controller' );
  jimport( 'joomla.application.component.view');
  jimport('joomla.application.component.model');
  class JoomlaController extends JController {}
  class JoomlaView extends JView {}
  class JoomlaModel extends JModel {}
}

// Require the base controller
require_once (JPATH_COMPONENT.'/controller.php');
$controller	= new ImprovedAjaxLoginController();
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
