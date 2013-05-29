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

class JElementOfflajnCombine extends JOfflajnFakeElementBase{
  var	$_name = 'OfflajnCombine';
  
  function universalfetchElement($name, $value, &$node){
    $v = explode('|*|', $value);
    $document =& JFactory::getDocument();
    $this->loadFiles();
    $attr = $node->attributes();
    $i = 0;
    $switcherid = '';
    $hideafter = 0;
    $html = "";
    $html .= '<div id="offlajncombine_outer'.$this->id.'" class="offlajncombine_outer">';
    foreach($node->children() AS $param){
      $a = $param->attributes();
      if($a['type'] == 'offlajnclear'){
        $html.= '<div class="clear"></div>';
        $hideafter = $i;
        continue; 
      }
      $param->addAttribute('name', $name.$i);
      $class = 'JElement'.$a['type'];
      $p = new $class();
      $p->id = $this->generateId($name.$i);
      $html.='<div class="offlajncombinefieldcontainer">';
      if(isset($a['label'])){
        if($a['label'] == '-') $a['label'] = '&nbsp;';
        $html.='<label style="float: left;">'.JTEXT::_($a['label']).'</label>';
      }
      $html .= '<div class="offlajncombinefield">'; 
      if(isset($a['onoff'])) $switcherid = $p->id;
      $html.= $p->universalfetchElement($name.$i, @$v[$i], $param); 
      $html.='</div>';
      $html.='</div>';
      $i++;
    }

    if(isset($a['onoff'])) $html .= '<div class="offlajncombine_hider"></div>';
    $html .= '</div>';        
    $html.= '<input type="hidden" name="'.$name.'" id="'.$this->id.'" value=\''.$value.'\'>';
    DojoLoader::addScript('
      new OfflajnCombine({
        id: "'.$this->id.'",
        num: '.$i.',
        switcherid: "'.$switcherid.'",
        hideafter: "'.$hideafter.'"
      }); 
    ');
    return $html;
  }
}