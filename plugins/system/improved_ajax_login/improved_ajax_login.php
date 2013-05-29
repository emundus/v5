<?php
/*------------------------------------------------------------------------
# plg_improved_ajax_login - Improved AJAX Login
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
defined('_JEXEC') or die('Restricted access');

$option = JRequest::getCmd('option');
if ($option == 'com_improved_ajax_login' && !JFactory::getApplication()->isAdmin())
{
  $task = JRequest::getCmd('task');
  if ($task == 'login' || $task == 'register') return;

  $mainframe = JFactory::getApplication();
  $db = JFactory::getDBO();
  $v15 = version_compare(JVERSION,'1.6.0','lt');
  $v30 = version_compare(JVERSION,'3.0.0','ge');

  require JPATH_SITE.'/components/com_improved_ajax_login/oauth.php';
}

jimport('joomla.plugin.plugin');

class plgSystemImproved_Ajax_Login extends JPlugin
{

  function plgSystemImproved_Ajax_Login(&$subject, $config)
  {
    parent::__construct($subject, $config);
  }

  function onAfterDispatch()
  {
    if (!$this->params->get('override', 1)) return;

    $app = JFactory::getApplication();
    if ($app->isAdmin()) return;

    jimport('joomla.application.module.helper');
    $option = JRequest::getCmd('option');
    $view = JRequest::getCmd('view');

    if (($option == 'com_user' && $view == 'login') || ($option == 'com_users' && $view == 'login'))
    {
      $module = JModuleHelper::getModule('improved_ajax_login');
      if (!$module) return;

      $user = JFactory::getUser();
      if (!$user->guest)
      {
          $app->redirect($option=='com_user'?'index.php?option=com_user&view=user':'index.php?option=com_users&view=profile');
          $app->close();
      }

      $module->view = 'log';
      $this->render($module);
    }
    elseif (($option == 'com_user' && $view == 'register') || ($option == 'com_users' && $view == 'registration'))
    {
      $module = JModuleHelper::getModule('improved_ajax_login');
      if (!$module) return;

      if ($option == com_user)
      {
        $params = new JParameter( $module->params );
        $regpage = $params->get('regpage');
      }
      else
      {
        $params = json_decode($module->params);
        $regpage = $params->moduleparametersTab->regpage;
      }
      $regpage = explode('|*|', $regpage);
      if (@$regpage[0] != 'joomla') return;

      $module->view = 'reg';
      $this->render($module);
    }
  }

  function render($module)
  {
    $contents = '<div id="loginComp">';
    $contents.= JModuleHelper::renderModule($module);
    $contents.= '</div>';
    $document = JFactory::getDocument();
    $document->setBuffer($contents, 'component');
  }

}