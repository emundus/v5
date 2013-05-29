<?php
/*-------------------------------------------------------------------------
# mod_accordion_menu - Accordion Menu - Offlajn.com
# -------------------------------------------------------------------------
# @ author    Roland Soos
# @ copyright Copyright (C) 2012 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
if(isset($_REQUEST['offlajnformrenderer'])){
  if(isset($_REQUEST['key'])){
    if(defined('WP_ADMIN')){
      OfflajnJPluginHelper::importPlugin('system', 'dojoloader');
    }else{
      JPluginHelper::importPlugin('system', 'dojoloader');
    }
    if(isset($_SESSION['theme']) && isset($_SESSION['theme']['forms'][$_REQUEST['key']])){
      $env = $_SESSION['theme'];
      $theme = $_SESSION['theme']['forms'][$_REQUEST['key']];
      
      $lang = JFactory::getLanguage();
      $lang->load($env['module'], JPATH_SITE.DS.'modules'.DS.$env['module']);
      
      if($theme == 'default2') $theme='default';
      $origtheme = $theme;
      require_once($_SESSION['OFFLAJNADMINPARAMPATH'].DS.'offlajndashboard'.DS.'offlajndashboard.php');
      
      $xml = $env['themesdir'].$theme.'/theme.xml';
      if($theme == 'default') $theme.=2;
      $params = new OfflajnJParameter('', $xml, 'module' );
      $params->theme = $theme;
      
      $_xml = &$params->getXML();
      for($x = 0; count($_xml['_default']->_children) > $x; $x++){
        $node = &$_xml['_default']->_children[$x];
        if(isset($node->_attributes['folder'])){
          $node->_attributes['folder'] = str_replace('/', DS, '/modules/'.$env['module'].'/themes/'.$theme.$node->_attributes['folder']);
        }
      }
      //$params->setRaw($env['raw']);
      if(@$env['formdata']['theme'] == $origtheme){
        $params->bind($env['formdata']);
      }
      echo $params->render($env['c']);
      plgSystemDojoloader::customBuild();
      
      $document = JFactory::getDocument();
      $document->_metaTags = array();
      $head = $document->getBuffer('head');
      
      echo preg_replace('/<(meta|title).*/','',$head);
      
      exit;
    }else if(isset($_REQUEST['control']) && isset($_SESSION[$_REQUEST['control']]) && isset($_SESSION[$_REQUEST['control']]['forms'][$_REQUEST['key']])){
      $env = $_SESSION[$_REQUEST['control']];
      $type = $_SESSION[$_REQUEST['control']]['forms'][$_REQUEST['key']];
      
      $lang = JFactory::getLanguage();
      $lang->load($env['module'], JPATH_SITE.DS.'modules'.DS.$env['module']);
      
      require_once($_SESSION['OFFLAJNADMINPARAMPATH'].DS.'offlajndashboard'.DS.'offlajndashboard.php');
      
      $xml = $env['typesdir'].$type.'/config.xml';
      $params = new OfflajnJParameter('', $xml, 'module' );
      $params->type = $type;
      
      $params->bind($env['formdata']);
      echo $params->render($env['c']);
      plgSystemDojoloader::customBuild();
      
      $document = JFactory::getDocument();
      $document->_metaTags = array();
      $head = $document->getBuffer('head');
      
      echo preg_replace('/<(meta|title).*/','',$head);
      exit;
    }else if(isset($_SESSION['slidertype']) && isset($_SESSION['slidertype']['forms'][$_REQUEST['key']])){
      $env = $_SESSION['slidertype'];
      $type = $_SESSION['slidertype']['forms'][$_REQUEST['key']];
      
      require_once($_SESSION['OFFLAJNADMINPARAMPATH'].DS.'offlajndashboard'.DS.'offlajndashboard.php');
      
      $xml = $env['typesdir'].'/'.$type.'/type.xml';
      $params = new OfflajnJParameter('', $xml, 'module' );

      if($type == $env['formdata']['type']){
        $params->bind($env['formdata']);
      }
      
      echo $params->render($env['c']);
      plgSystemDojoloader::customBuild();
      
      $document = JFactory::getDocument();
      
      if(defined('WP_ADMIN')){
        foreach($document->_styleSheets AS $k => $s){
          unset($document->_styleSheets[$k]);
          $document->_styleSheets[smartslider_translate_url($k)] = $s;
        }
        foreach($document->_scripts AS $k => $s){
          $document->_scripts[smartslider_translate_url($k)] = $s;
          unset($document->_scripts[$k]);
        }
      }
      
      $document->_metaTags = array();
      $head = $document->getBuffer('head');
      
      echo preg_replace('/<(meta|title).*/','',$head);
      exit;
    }else if(isset($_SESSION['slidertype']) && isset($_SESSION['slidertype']['forms'][$_REQUEST['key2']])
      && isset($_SESSION['slidertype'][$_SESSION['slidertype']['forms'][$_REQUEST['key2']]]['theme'][$_REQUEST['key']])){
      $env = $_SESSION['slidertype'];
      $type = $_SESSION['slidertype']['forms'][$_REQUEST['key2']];
      $theme = $_SESSION['slidertype'][$_SESSION['slidertype']['forms'][$_REQUEST['key2']]]['theme'][$_REQUEST['key']];
      
      require_once($_SESSION['OFFLAJNADMINPARAMPATH'].DS.'offlajndashboard'.DS.'offlajndashboard.php');
      
      $xml = $env['typesdir'].'/'.$type.'/'.$theme.'/theme.xml';
      $params = new OfflajnJParameter('', $xml, 'module' );
      
      if($type == $env['formdata']['type'] && $theme == $env['formdata']['theme']){
        $params->bind($env['formdata']);
      }
      
      echo $params->render($env['c']);
      plgSystemDojoloader::customBuild();
      
      $document = JFactory::getDocument();
      
      if(defined('WP_ADMIN')){
        foreach($document->_styleSheets AS $k => $s){
          $document->_styleSheets[smartslider_translate_url($k)] = $s;
          unset($document->_styleSheets[$k]);
        }
        foreach($document->_scripts AS $k => $s){
          $document->_scripts[smartslider_translate_url($k)] = $s;
          unset($document->_scripts[$k]);
        }
      }
      $document->_metaTags = array();
      $head = $document->getBuffer('head');
      
      echo preg_replace('/<(meta|title).*/','',$head);
      exit;
    }
  }
  echo 'Error';exit;
}
?>