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

jimport('joomla.filesystem.file');

class JElementOfflajnFont extends JOfflajnFakeElementBase
{
  var $_moduleName = '';
  
	var	$_name = 'OfflajnFont';
	
	var $_node = '';
	
	var $_google = array();
	var $_googleName = array();
	var $_googlefonts = array();

	function universalfetchElement($name, $value, &$node){
    $this->_node = &$node;
    $this->_googlefonts = array();
    $this->_google = array();
    $this->_googleName = array();
    $this->init();
    $html = "";
    $attrs = $node->attributes();
    $alpha = isset($attrs['alpha'])? $attrs['alpha'] : 0;
    $tabs = explode('|', $attrs['tabs']);
    
    $s = json_decode($value);
    if(isset($attrs['tabs']) && $attrs['tabs'] != "")  @$def = (array)$s->{$tabs[0]};
    $elements = array();
    
    $stack = & JsStack::getInstance();
    $stack->startStack();
    
    // TABS
    $elements['tab']['name'] = $name.'tab';
    $elements['tab']['id'] = $this->generateId($elements['tab']['name']);
    
    
    $tabxml = new JSimpleXML();
    $tabxml->loadString('<param/>');
    $tabxml = $tabxml->document;
    $tabxml->addAttribute('name', $elements['tab']['name']);
    $tabxml->addAttribute('type', 'offlajnradio');
    $tabxml->addAttribute('mode', 'button');
    foreach($tabs AS $t){
      $tabxml->addChild('option', array('value'=>$t))->setData($t);
    }
    $tab = new JElementOfflajnRadio();
    $tab->id = $elements['tab']['id'];
    $elements['tab']['html'] = $tab->universalfetchElement($elements['tab']['name'], $tabs[0], $tabxml);
    // END TABS
    
    // TYPE
    $elements['type']['name'] = $name.'type';
    $elements['type']['id'] = $this->generateId($elements['type']['name']);
    $typexml = new JSimpleXML();
    $typexml->loadString('<param/>');
    $typexml = $typexml->document;
    $typexml->addAttribute('name', $elements['type']['name']);
    $typexml->addAttribute('type', 'offlajnlist');
    $typexml->addChild('option', array('value'=>'0'))->setData('Alternative fonts');
    foreach($this->_google AS $t){
      $typexml->addChild('option', array('value'=>$t))->setData($t);
      $stack->startStack();
      // FAMILY
      $elements['type'][$t]['name'] = $name.'family';
      $elements['type'][$t]['id'] = $this->generateId($elements['type'][$t]['name']);
      $familyxml = new JSimpleXML();
      $familyxml->loadString('<param/>');
      $familyxml = $familyxml->document;
      $familyxml->addAttribute('name', $elements['type'][$t]['name']);
      $familyxml->addAttribute('type', 'offlajnlist');
      $familyxml->addAttribute('height', '10');
      $familyxml->addAttribute('fireshow', '1');
      foreach($this->_googlefonts[$t] AS $f){
        if(strlen($f) > 0)
          $familyxml->addChild('option', array('value'=>$f))->setData($f);
      }
      $family = new JElementOfflajnList();
      $family->id = $elements['type'][$t]['id'];
      $elements['type'][$t]['html'] = $family->universalfetchElement($elements['type'][$t]['name'], isset($def['family'])?$def['family']:'Open Sans', $familyxml);
      $elements['type'][$t]['script'] = $stack->endStack(true);
      // END FAMILY
    }
    $type = new JElementOfflajnList();
    $type->id = $elements['type']['id'];
    $elements['type']['html'] = $type->universalfetchElement($elements['type']['name'], isset($def['type'])?$def['type']:'0', $typexml);
    // END TYPE
    
    // SIZE
    $elements['size']['name'] = $name.'size';
    $elements['size']['id'] = $this->generateId($elements['size']['name']);
    
    $sizexml = new JSimpleXML();
    $sizexml->loadString('<param size="1" validation="int" mode="increment" scale="1" allowminus="0"><unit value="px" imsrc="">px</unit><unit value="em" imsrc="">em</unit></param>');
    $sizexml = $sizexml->document;
    $sizexml->addAttribute('name', $elements['size']['name']);
    $sizexml->addAttribute('type', 'offlajntext');
    $size = new JElementOfflajnText();
    $size->id = $elements['size']['id'];
    $elements['size']['html'] = $size->universalfetchElement($elements['size']['name'], isset($def['size'])?$def['size']:'14||px', $sizexml);
    // END SIZE
    
    // COLOR
    $elements['color']['name'] = $name.'color';
    $elements['color']['id'] = $this->generateId($elements['color']['name']);
    
    $colorxml = new JSimpleXML();
    $colorxml->loadString('<param/>');
    $colorxml = $colorxml->document;
    $colorxml->addAttribute('name', $elements['color']['name']);
    $colorxml->addAttribute('type', 'offlajncolor');
    $color = new JElementOfflajnColor();
    $color->id = $elements['color']['id'];
    $elements['color']['html'] = $color->universalfetchElement($elements['color']['name'], isset($def['color'])?$def['color']:'000000', $colorxml);
    // END COLOR
    
    // bold
    $elements['bold']['name'] = $name.'bold';
    $elements['bold']['id'] = $this->generateId($elements['bold']['name']);
    
    $boldxml = new JSimpleXML();
    $boldxml->loadString('<param mode="button" imsrc="bold.png" actsrc="bold_act.png" description=""/>');
    $boldxml = $boldxml->document;
    $boldxml->addAttribute('name', $elements['bold']['name']);
    $bold = new JElementofflajnonoff();
    $bold->id = $elements['bold']['id'];
    $elements['bold']['html'] = $bold->universalfetchElement($elements['bold']['name'], isset($def['bold'])?$def['bold']:0, $boldxml);
    // END bold
    
    // italic
    $elements['italic']['name'] = $name.'italic';
    $elements['italic']['id'] = $this->generateId($elements['italic']['name']);
    
    $italicxml = new JSimpleXML();
    $italicxml->loadString('<param mode="button" imsrc="italic.png" actsrc="italic_act.png" description=""/>');
    $italicxml = $italicxml->document;
    $italicxml->addAttribute('name', $elements['italic']['name']);
    $italic = new JElementofflajnonoff();
    $italic->id = $elements['italic']['id'];
    $elements['italic']['html'] = $italic->universalfetchElement($elements['italic']['name'], isset($def['italic'])?$def['italic']:0, $italicxml);
    // END italic
    
    // underline
    $elements['underline']['name'] = $name.'underline';
    $elements['underline']['id'] = $this->generateId($elements['underline']['name']);
    
    $underlinexml = new JSimpleXML();
    $underlinexml->loadString('<param mode="button" imsrc="underline.png" actsrc="underline_act.png" description=""/>');
    $underlinexml = $underlinexml->document;
    $underlinexml->addAttribute('name', $elements['underline']['name']);
    $underline = new JElementofflajnonoff();
    $underline->id = $elements['underline']['id'];
    $elements['underline']['html'] = $underline->universalfetchElement($elements['underline']['name'], isset($def['underline'])?$def['underline']:0, $underlinexml);
    // END underline
    
    // ALIGN
    $elements['align']['name'] = $name.'align';
    $elements['align']['id'] = $this->generateId($elements['align']['name']);
    
    $alignxml = new JSimpleXML();
    $tsxml = <<<EOD
<param type="offlajnradio" mode="image">
  <option value="left" imsrc="left_align.png"></option>
  <option value="center" imsrc="center_align.png"></option>
  <option value="right" imsrc="right_align.png"></option>
</param>
EOD;
    $alignxml->loadString($tsxml);
    $alignxml = $alignxml->document;
    $alignxml->addAttribute('name', $elements['align']['name']);
    $align = new JElementOfflajnRadio();
    $align->id = $elements['align']['id'];
    $elements['align']['html'] = $align->universalfetchElement($elements['align']['name'], isset($def['align'])?$def['align']:'left', $alignxml);
    // ALIGN
    
    // Alternative font
    $elements['afont']['name'] = $name.'afont';
    $elements['afont']['id'] = $this->generateId($elements['afont']['name']);
    
    $afontxml = new JSimpleXML();
    $afontxml->loadString('<param onoff="1"><unit value="1" imsrc="">ON</unit><unit value="0" imsrc="">OFF</unit></param>');
    $afontxml = $afontxml->document;
    $afontxml->addAttribute('name', $elements['afont']['name']);
    $afontxml->addAttribute('type', 'offlajntext');
    $afontxml->addAttribute('size', '10');
    $afont = new JElementOfflajnText();
    $afont->id = $elements['afont']['id'];
    $elements['afont']['html'] = $afont->universalfetchElement($elements['afont']['name'], isset($def['afont'])?$def['afont']:'Arial||1', $afontxml);
    // END Alternative font
    
    // TEXT SHADOW
    $elements['tshadow']['name'] = $name.'tshadow';
    $elements['tshadow']['id'] = $this->generateId($elements['tshadow']['name']);
    
    $tshadowxml = new JSimpleXML();
    $tsxml = <<<EOD
<param>
  <param size="1" validation="float" type="offlajntext"><unit value="px" imsrc="">px</unit></param>
  <param size="1" validation="float" type="offlajntext"><unit value="px" imsrc="">px</unit></param>
  <param size="1" validation="float" type="offlajntext"><unit value="px" imsrc="">px</unit></param>
  <param type="offlajncolor" alpha="$alpha"/>
  <param type="offlajnswitcher" onoff="1"><unit value="1" imsrc="">ON</unit><unit value="0" imsrc="">OFF</unit></param>
</param>
EOD;
    $tshadowxml->loadString($tsxml);
    $tshadowxml = $tshadowxml->document;
    $tshadowxml->addAttribute('name', $elements['tshadow']['name']);
    $tshadowxml->addAttribute('type', 'offlajncombine');
    $tshadow = new JElementOfflajnCombine();
    $tshadow->id = $elements['tshadow']['id'];
    $elements['tshadow']['html'] = $tshadow->universalfetchElement($elements['tshadow']['name'], isset($def['tshadow'])?$def['tshadow']:'0|*|0|*|0|*|000000|*|0', $tshadowxml);
    // TEXT SHADOW
    
    // LINE HEIGHT
    $elements['lineheight']['name'] = $name.'lineheight';
    $elements['lineheight']['id'] = $this->generateId($elements['lineheight']['name']);
    
    $lineheightxml = new JSimpleXML();
    $lineheightxml->loadString('<param></param>');
    $lineheightxml = $lineheightxml->document;
    $lineheightxml->addAttribute('name', $elements['lineheight']['name']);
    $lineheightxml->addAttribute('type', 'offlajntext');
    $lineheightxml->addAttribute('size', '5');
    $lineheight = new JElementOfflajnText();
    $lineheight->id = $elements['lineheight']['id'];
    $elements['lineheight']['html'] = $lineheight->universalfetchElement($elements['lineheight']['name'], isset($def['lineheight'])?$def['lineheight']:'normal', $lineheightxml);
    // END LINE HEIGHT
    
    $this->loadFiles();
    
    $id = $this->generateId($name);
    
    $script = $stack->endStack(true);
    
    $settings = array();
    if($value == '' || $value[0] != '{'){
      foreach($tabs AS $t){
        $settings[$t] = new StdClass();
      }
      $settings = json_encode($settings);
    }else{
      $settings = $value;
    }
    
    $document = JFactory::getDocument();
    DojoLoader::addScript('
        new FontConfigurator({
          id: "'.$this->id.'",
          defaultTab: "'.$tabs[0].'",
          origsettings: '.$settings.',
          elements: '.json_encode($elements).',
          script: '.json_encode($script).'
        });
    ');
    $html.="<a style='float: left;' id='".$id."change' href='#' class='font_select'></a>&nbsp;&nbsp;";
    if($this->_parent->get('admindebug', 0) == 1){
      $html.='<span>Raw font data: </span><input type="text" name="'.$name.'" id="'.$id.'" value="'.str_replace('"',"'",$value).'" />';
    }else{
      if($value != "")
        if($value[0] != '{') $value = $settings;
      $html.='<input type="hidden" name="'.$name.'" id="'.$id.'" value=\''.str_replace("'",'"',$value).'\' />';
    }
    return $html;
	}
	
	function init(){
	  $p = dirname(__FILE__).DS.'google/';
    $google = JFolder::files($p, '.txt');
    foreach($google as $g){
      $this->_google[] = JFile::stripExt($g);
      preg_match_all('/((?:^|[A-Z])[a-z]+)/',JFile::stripExt($g),$matches);
      $this->_googleName[] = implode(' ', $matches[1]);
      $fp = @fopen($p.$g, 'r');
      if ($fp) {
        $this->_googlefonts[JFile::stripExt($g)] = explode("\r\n", fread($fp, filesize($p.$g)));
      }
      fclose($fp);
    }
  }
	
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnFont extends JElementOfflajnFont {}
}