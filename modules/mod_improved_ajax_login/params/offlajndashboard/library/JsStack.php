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

class JsStack{
  var $stack = '';
  
  var $index = '';
  
  var $document = null;
  
  function &getInstance(){
    static $instance;
    if(empty($instance)){
      $instance = new JsStack();
    }
    return $instance;
  }
  
  function __construct(){
    if(!is_array($this->stack)) $this->stack = array();
    $this->index = -1;
    $this->loader =& DojoLoader::getInstance();
  }
  
  function startStack(){
    $this->index++;
    $this->stack[$this->index] = count($this->loader->script);
  }
  
  function endStack($remove = false){
    $nscript = '';
    while(count($this->loader->script) != $this->stack[$this->index]){
      $nscript = array_pop($this->loader->script).$nscript;
    }
    unset($this->stack[$this->index]);
    $this->index--;
    return 'dojo.addOnLoad(function(){'.$nscript.'});';
  }
}
?>