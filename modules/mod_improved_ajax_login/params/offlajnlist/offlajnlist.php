<?php
/*------------------------------------------------------------------------
# offlajnlist - Offlajn List Parameter
# ------------------------------------------------------------------------
# author    Jeno Kovacs 
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

class JElementOfflajnList extends JOfflajnFakeElementBase {

  var $_name = 'OfflajnList';
  
  var $selectedIndex = 0;
  
  var $fullText = ''; // fix for width problem

  public function universalfetchElement($name, $value, &$node) {
    $attrs = $node->attributes();
    $this->value = $value;
    $this->fullText = "<br />";
    $document =& JFactory::getDocument();
    $html = "";
    $el = "";
    $this->options = array();
    $this->elements = array();
    $this->items = array();
    $this->vals = array();
    $fireshow = isset($attrs['fireshow'])? $attrs['fireshow'] : 0;
    $this->loadFiles('offlajnlist', 'offlajnlist');
    $this->loadFiles('offlajnscroller', 'offlajnlist');
    $this->name = $this->generateId($name);
    $el .= '<div class="content">';
    $this->onchange = "";
    
    $this->items = $node->children();
    $el .= $this->makeItemDivs();

		$el .= '</div>';
		$html .= '<div id="offlajnlistcontainer'.$this->name.'" class="gk_hack offlajnlistcontainer">';
		$html .= '<div class="gk_hack offlajnlist">';
    if ((!is_array($value) && strlen($value)) || (!version_compare(JVERSION,'1.6.0','ge')) ) {
      if($value == "" || !isset($this->elements[$value]) ) { 
        @$html .= '<span class="offlajnlistcurrent">'.$this->elements[$this->vals[0]].$this->fullText.'</span>';
      } else {
        @$html .= '<span class="offlajnlistcurrent">'.$this->elements[$value].$this->fullText.'</span>';
     }    
    } else {
      $html .= '<span class="offlajnlistcurrent">'.$this->elements[$this->vals[0]].$this->fullText.'</span>';
    }
		$html .= '<div class="offlajnlistbtn"><span></span></div>';
    $html .= '</div>';
    
    $html .= '<input type="hidden" name="'.$name.'" id="'.$this->generateId($name).'" value="'.$this->options[$this->selectedIndex]['value'].'"/>';
    $html .= '</div>';
    $n = strtolower($this->_name);
    
    $height = isset($attrs['height'])? $attrs['height'] : (count($this->options) > 10 ? 10 : 0);
    
    DojoLoader::addScript('
      new OfflajnList({
        name: "'.$this->name.'",
        elements: '.json_encode($el).',
        options: '.json_encode($this->options).',
        selectedIndex: '.$this->selectedIndex.',
        height: '.json_encode($height).',
        fireshow: '.$fireshow.'
      });
    ');  
    return "<div style='position:relative;'>".$html."</div>";
	}
	 
  //get the items for the multi select list
    
 public function makeItemDivs() {
   $el = "";
   $i = 0;
   foreach ($this->items as $option) {
  	  $val	= (!method_exists($option, 'getAttribute') ? $option->attributes('value') : $option->getAttribute('value'));
  	  $this->vals[] = $val;
  		$text	= $option->data();
  		$this->elements[$val] = JTEXT::_($text);
      $this->fullText.=$this->elements[$val]."<br />";
  		$this->options[$i]['value'] = $val;
  		$this->options[$i]['text'] = JTEXT::_($text);
      if($this->value == $val)$this->selectedIndex = count($this->options)-1;
  		$el .= '<div class="listelement">'.JTEXT::_($text).'</div>';	
      $i++;	
  	} 
    return $el;
  }
}

if(version_compare(JVERSION,'1.6.0','ge')) {
  class JFormFieldOfflajnList extends JElementOfflajnList {}
}