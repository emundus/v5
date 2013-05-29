<?php
/*------------------------------------------------------------------------
# offlajnonswitcher - Offlajn On/Off Parameter
# ------------------------------------------------------------------------
# author    Jeno Kovacs
# copyright Copyright (C) 2011 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

class JElementOfflajnSwitcher extends JOfflajnFakeElementBase{
  var $_moduleName = '';
  var	$_name = 'offlajnswitcher';

  function universalfetchElement($name, $value, &$node){    
    $document =& JFactory::getDocument();
    $this->loadFiles();
    $units = array();
    $values = array();
    $mode = 0;
    $url = JURI::base().'../modules/'.$this->_moduleName.'/params/'.$this->_name.'/images/';
    $attributes = $node->attributes();
    $html = '<div class="offlajnswitcher">
            <div class="offlajnswitcher_inner" id="offlajnswitcher_inner'.$this->id.'"></div>
    </div>';
    $html .= '<input type="hidden" name="'.$name.'" id="'.$this->id.'" value="'.$value.'" />';
    //$units = explode(" ", $attributes['units']);
     foreach ($node->children() as $child) {
      if(isset($child->_attributes['imsrc']) && $child->_attributes['imsrc']) {
        $units[] = $child->_attributes['imsrc'];
        $mode = 1;
      } else {
        $units[] = $child->data();
      }
      $values[] = $child->_attributes['value']; 
     }
    
    DojoLoader::addScript('dojo.addOnLoad(function(){ 
      new OfflajnSwitcher({
        id: "'.$this->id.'",
        units: '.json_encode($units).',
        values: '.json_encode($values).',
        map: '.json_encode(array_flip($values)).',
        mode: '.json_encode($mode).',
        url: '.json_encode($url).'
      }); 
    });');  
    return $html;
  } 
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnSwitcher extends JElementOfflajnSwitcher {}
}