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

class JElementOfflajnColor extends JOfflajnFakeElementBase
{
  var $_moduleName = '';
  
	var	$_name = 'OfflajnColor';

	function universalfetchElement($name, $value, &$node){
  	$this->loadFiles();
		$size = 'size="12"';
    $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);
    $id = $this->generateId($name);
    
    $alpha = $node->attributes('alpha') == 1 ? true : false;
    $width = (!$alpha) ? "wa" : "";
    
    $url='';
    if(defined('WP_ADMIN')){
      $url = smartslider_url('joomla/');
    }else{
      $url = JURI::root(true);
    }

    DojoLoader::addScript(
    '
    var el = dojo.byId("'.$id.'");
    jQuery.fn.jPicker.defaults.images.clientPath="'.$url.'/'.$this->_furl.'../offlajncolor/offlajncolor/jpicker/images/";
    el.alphaSupport='.($alpha ? 'true' : 'false').'; 
    el.c = jQuery("#'.$id.'").jPicker({
        window:{
          expandable: true,
          alphaSupport: '.($alpha ? 'true' : 'false').'}
        });
    dojo.connect(el, "change", function(){
      this.c[0].color.active.val("hex", this.value);
    });
    ');
		return '<div class="offlajncolor"><input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="color '.$width.'" '.$size.' /></div>';
	}
	
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnColor extends JElementOfflajnColor {}
}