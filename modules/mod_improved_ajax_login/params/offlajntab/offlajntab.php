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

Nextendjimport( 'joomla.utilities.simplexml' );
Nextendjimport( 'joomla.html.parameter' );

class JElementOfflajnTab extends JOfflajnFakeElementBase{

  var $_name = 'offlajntab';
 
  function universalFetchElement($name, $value, &$node){
    $n = new JSimpleXML();
    $n->loadString(method_exists($node, 'toString') ? $node->toString() : $node->asXML());
    $params = new OfflajnJParameter('');
    $params->setXML($n->document);
    $attr = $node->attributes();
    if(!isset($attr['position']) || $attr['position'] != 'first') $attr['position'] = 'last';
    
    if(!version_compare(JVERSION,'1.6.0','ge')){ // Joomla 1.5 < 
      preg_match('/(.*)\[([a-zA-Z0-9]*)\]$/', $name, $out);
      $control = $out[1];
      $name = $out[2];
      $params->bind($this->_parent->_raw);
      $params->_raw = & $this->_parent->_raw;
    }else{ // Joomla 1.7 > 
      $control = $name;
      if($value != '')
        $params->bind($value);
    }
    plgSystemOfflajnParams::addNewTab($this->generateId($name), parent::getLabel(), $params->render($control), (string)$attr['position']);
    return '';
  }
  
  function getLabel(){
    return '';
  }
  
}

function sprint_r($var) {
    ob_start();
    print_r($var);
    $output=ob_get_contents();
    ob_end_clean();
    return $output;
 }

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnTab extends JElementOfflajnTab {}
}
?>