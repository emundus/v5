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

if(!class_exists('JElementOfflajnList')) {
  require_once( (dirname(__FILE__)) . DS . 'offlajnlist.php');
}

class JElementOfflajnSkin extends JElementOfflajnList {

	var	$_name = 'OfflajnSkin';

	function universalfetchElement($name, $value, &$node){
    $attrs = $node->attributes();
    $this->loadFiles();
    
    $listnode = new JSimpleXMLElement('list'); 
    $datas = array();
    $listnode->addChild('option',array('value' => 'custom'))->setData(ucfirst('Custom'));
    foreach($node->children() AS $default){
      if (!isset($this->_parent->theme)) $this->_parent->theme = "default";
      $listnode->addChild('option',array('value' => $this->_parent->theme.'_'.$default->name()))->setData(ucfirst($default->name()));
      $datas[$this->_parent->theme.'_'.$default->name()] = array();
      foreach($default->_children AS $c){
        $datas[$this->_parent->theme.'_'.$default->name()][$c->name()] = $c->data();
      }
    }
    
    preg_match('/(.*)\[([a-zA-Z0-9]*)\]$/', $name, $out);
    $control = $out[1];
    $orig_name = $out[2];
    $value = 'custom';
    $html = parent::universalfetchElement($name, $value, $listnode);

    DojoLoader::addScript("
      window.".$orig_name." = new OfflajnSkin({
        name: ".json_encode($orig_name).",
        id: ".json_encode($this->id).",
        data: ".json_encode($datas).",
        control: ".json_encode($control).",
        dependency: '".(isset($attrs['dependency'])?$attrs['dependency']:"")."'
      });
    ");
    
    return $html;  
	}
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnSkin extends JElementOfflajnSkin {}
}