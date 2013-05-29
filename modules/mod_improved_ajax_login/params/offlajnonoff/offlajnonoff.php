<?php
/*------------------------------------------------------------------------
# offlajnonoff - Offlajn On/Off Parameter
# ------------------------------------------------------------------------
# author    Jeno Kovacs & Andras Molnar
# copyright Copyright (C) 2011 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

class JElementOfflajnOnOff extends JOfflajnFakeElementBase{
  var $_moduleName = '';
  var	$_name = 'offlajnonoff';

  function universalfetchElement($name, $value, &$node){    
    $document =& JFactory::getDocument();
    $this->loadFiles();
    $attributes = $node->attributes();
    $html = "";
    $imgs = "";
    $mode = "";
    if(isset($attributes['mode'])) $mode = $attributes['mode'];
    $url = JURI::base().'../modules/'.$this->_moduleName.'/params/'.$this->_name.'/images/';
    if(defined('WP_ADMIN'))
      $url = smartslider_translate_js_url($url);
      
    if($mode == "") {
      $html = '<div id="offlajnonoff'.$this->id.'" class="gk_hack onoff'.($value ? '':' onoff-off').'"></div>';
    } else if($mode == "button") {
      $html = '<div id="offlajnonoff'.$this->id.'" class="gk_hack onoffbutton'.($value ? ' selected':'').'">
                <div class="gk_hack onoffbutton_img" style="background-image: url('.$url.$attributes['imsrc'].');"></div>
      </div>';
    }
    $html .= '<input type="hidden" name="'.$name.'" id="'.$this->id.'" value="'.$value.'" />';
 
    DojoLoader::addScript('
      new OfflajnOnOff({
        id: "'.$this->id.'",
        mode: "'.$mode.'",
        imgs: '.json_encode($imgs).'
      }); 
    ');  
    return $html;
  } 
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnOnOff extends JElementOfflajnOnOff {}
}