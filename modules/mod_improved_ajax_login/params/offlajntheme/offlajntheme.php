<?php
/*-------------------------------------------------------------------------
# com_improved_ajax_login - Improved_AJAX_Login
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.parameter' );

@JOfflajnParams::load('offlajnlist');

class JElementOfflajnTheme extends JElementOfflajnList{

  var $_moduleName = '';
  
  var $_name = 'offlajntheme';
 
  function universalFetchElement($name, $value, &$node){
    $this->jf = false;
    if($_REQUEST['option'] == 'com_joomfish'){
      $this->jf = true;
    }
    $this->loadFiles();
    $this->loadFiles('offlajnlist');
    $this->themesdir = dirname(__FILE__).'/../../themes/';
    $themesdir = dirname(__FILE__).'/../../themes/';
    $document =& JFactory::getDocument();
    if($value == 1) {
      $value = "default";
      $this->firstRun = 1;
    }
    return $this->generateThemeSelector($name, $value, $node);
  }
  
  function generateThemeSelector($name, $value, &$lnode){
    $themes = JFolder::folders($this->themesdir);
    $this->themeParams = array('default' => '');
    $this->themeScripts = array('default' => '');

    //$stack = & JsStack::getInstance();
    
    $themeparams = null;
    
    $data = $this->_parent->toArray();
    
    preg_match('/(.*)\[([a-zA-Z0-9]*)\]$/', $name, $out);
    
    $out[1] = str_replace(array("[", "]"), '', $out[1]);
    
    @$control = $out[1];
    @$orig_name = $out[2];
    
    $formdata = array();
    $c = $control;
    if(version_compare(JVERSION,'1.6.0','ge')) {
      if(isset($data[$orig_name]) && is_array($data[$orig_name]) ){
        $formdata = $data[$orig_name];
      }
      $c = $name;
    }else{
      $formdata = $data;
    }
    
    $_SESSION['theme'] = array(
      'themesdir' => $this->themesdir,
      'formdata' => $formdata,
      'c' => $c,
      'module' => $this->_moduleName,
      'name' => $name,
      'raw' => $this->_parent->getRaw()
    );

    if ( is_array($themes) ){
    	foreach($themes as $theme){
        $lnode->addChild('option',array('value' => $theme))->setData(ucfirst($theme));
        /*
    		$xml = $this->themesdir.$theme.'/theme.xml';
        
                 
        $this->params = new OfflajnJParameter('', $xml, 'module' );
        $this->params->theme = $theme;
    		
        $_xml = &$this->params->getXML();
        for($x = 0; count($_xml['_default']->_children) > $x; $x++){
          $node = &$_xml['_default']->_children[$x];
          if(isset($node->_attributes['folder'])){
            $node->_attributes['folder'] = str_replace('/', DS, '/modules/'.$this->_moduleName.'/themes/'.$theme.$node->_attributes['folder']);
          }
        }
        
        $stack->startStack();
   
        $this->params->setRaw($this->_parent->getRaw());
        
        $this->themeParams[$theme] = $this->params->render($c);
        
        $this->themeScripts[$theme] = $stack->endStack(true);*/
        
    		if($theme == 'default') $theme.=2;
        
        $key = md5($theme);
        $_SESSION['theme']['forms'][$key] = $theme;
          
        $this->themeParams[$theme] = $key;
    	}
    }
    if(version_compare(JVERSION,'1.6.0','ge')) {
      $name.= '['.$orig_name.']';
    }

    $themeField = parent::universalfetchElement($name, is_array($value) ? $value["theme"] : $value, $lnode);
/*
    if($this->params->get('admindebug', 0) == 1){
      $themeField.= "<br />";
      $xml = '';
      $skin = 0;
      foreach(version_compare(JVERSION,'1.6.0','ge') ? $themeparams : $this->params->toArray() as $key => $value){
        if($skin == 0){
          if($key == 'fontskin'){
            $skin = 1;
          }
          continue;
        }else if($skin == 1){
          if($key == 'cache'){
            $skin = 0;
            continue;
          }
        }
        $xml.= "&lt;".$key."&gt;".$value."&lt;/".$key."&gt;\n";
      }
      $themeField.= "<textarea style='width: 100%; min-height: 300px;'>".$xml."</textarea>";
    }*/
    /*ob_start();
    if(version_compare(JVERSION,'1.6.0','ge')) {
      include('themeselector16.tmpl.php');
    }else{
      include('themeselector.tmpl.php');
    }
    $this->themeSelector = ob_get_contents();
    ob_end_clean();*/

    //global $offlajnParams;
    //$offlajnParams['last'][] = $this->themeSelector;
    
    $id = $this->generateId($control).'theme';
    plgSystemOfflajnParams::addNewTab($id, 'Theme Parameters', '');

    DojoLoader::addScript('
      var theme = new ThemeConfigurator({
        id: "'.$id.'-details",
        selectTheme: "'.$this->generateId($name).'",
        themeSelector: '.json_encode($this->themeSelector).',
        themeParams: '.json_encode($this->themeParams).',
        themeScripts: '.json_encode($this->themeScripts).',
        joomfish: '.(int)$this->jf.',
        control: "'.$control.'",
        firstRun: "'.$this->firstRun.'"
      });
    ');
    return $themeField;
  }
  
  function setModuleName(){
    preg_match('/modules\/(.*?)\//', $this->_parent->_xml['_default']->_attributes['addpath'], $matches);
    $this->_moduleName = $matches[1];
  }
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnTheme extends JElementOfflajnTheme {}
}
?>