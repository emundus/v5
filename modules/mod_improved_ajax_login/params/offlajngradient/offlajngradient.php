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

@JOfflajnParams::load('offlajnonoff');

class JElementOfflajnGradient extends JOfflajnFakeElementBase
{
  var $_moduleName = '';
  
	var	$_name = 'Gradient';

	function universalfetchElement($name, $value, &$node)
	{
		$size = ( $node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '' );
    $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);
    $attrs = $node->attributes();
    
    $oo = @$attrs['onoff']==='0'? 0 : 1;

    $document =& JFactory::getDocument();
    DojoLoader::addScriptFile('/modules/'.$this->_moduleName.'/params/offlajnonoff/offlajnonoff/offlajnonoff.js');
    DojoLoader::addScriptFile('/modules/'.$this->_moduleName.'/params/offlajngradient/offlajngradient/offlajngradient.js');
    $document->addStyleSheet(JURI::base().'../modules/'.$this->_moduleName.'/params/offlajngradient/offlajngradient/offlajngradient.css');
    $document->addStyleSheet(JURI::base().'../modules/'.$this->_moduleName.'/params/offlajncolor/offlajncolor/offlajncolor.css');
    DojoLoader::addScript('jQuery.fn.jPicker.defaults.images.clientPath="'.JURI::base().'../modules/'.$this->_moduleName.'/params/offlajncolor/offlajncolor/jpicker/images/";');
    
    $id = $this->generateId($name);
    
    $v = explode('-', $value);
    $f = "";
    $onoff = new JElementOfflajnOnOff();
    $onoff->id = $onoff->generateId($id.'onoff');
    $f.= $onoff->universalfetchElement($onoff->id,$v[0],new JSimpleXMLElement('param'));
    $f.= '<div class="gradient_container"><div id="gradient'.$id.'" class="gradient_bg"><input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'"/>';
    $f.= '<div class="gradient_left"><input type="text" name="a'.$name.'[start]" id="'.$id.'start" value="'.@$v[1].'" class="color" '.$size.' /></div>';
    $f.= '<div class="gradient_right"><input type="text" name="a'.$name.'[stop]" id="'.$id.'stop" value="'.@$v[2].'" class="color" '.$size.' /></div>';
    $f.= '<div style="clear: both;"></div></div></div><div style="clear: both;"></div>';


    DojoLoader::addScript('
      new OfflajnGradient({
        hidden: dojo.byId("'.$id.'"),
        switcher: dojo.byId("'.$onoff->id.'"),
        onoff: '.$oo.',
        start: dojo.byId("'.$id.'start"),
        end: dojo.byId("'.$id.'stop")
      });
    ');
    
		return $f;
	}
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnGradient extends JElementOfflajnGradient {}
}

