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

Nextendjimport('joomla.html.parameter');
Nextendjimport('joomla.html.parameter.element');
foreach(JFolder::folders(OFFLAJNADMINPARAMPATH, '.', false, false) AS $f){
  if(file_exists(OFFLAJNADMINPARAMPATH.DS.$f.DS.$f.'.php'))
    require_once(OFFLAJNADMINPARAMPATH.DS.$f.DS.$f.'.php');
}

if(version_compare(JVERSION,'1.6.0','ge')) {

  if (!class_exists('OfflajnBaseJParameter')) {
    class OfflajnBaseJParameter extends JParameter {}
  }

  class OfflajnJParameter extends OfflajnBaseJParameter{
    public function __construct($data = '', $path = ''){
      parent::__construct($data, $path);
      $this->addElementPath(JFolder::folders(OFFLAJNADMINPARAMPATH, '.', false, true));
    }
    
    public function render($name = 'params', $group = '_default'){
  		if (!isset($this->_xml[$group])) {
  			return false;
  		}
  
  		$params = $this->getParams($name, $group);
  		$html = '<ul class="adminformlist parsed">';
  
  		if ($description = $this->_xml[$group]->attributes('description')) {
  			// Add the params description to the display
  			$desc	= JText::_($description);
  			$html.= '<li><p class="paramrow_desc">'.$desc.'</p></li>';
  		}
      $i=1;
  		foreach ($params as $param) {
			  $class = ($i%2)? 'blue' : '';
        //if(trim($param[0]) == '' || $param[1] == '' || $param[1]==''){
        if((strlen($param[0])== 0 || strlen($param[1])== 0) && ( false === strpos($param[3] ,'LEVEL'))){
          $class = 'hide';
          $i--;
        }
        if (strlen($param[2])!= 0) $class.=" hasOfflajnTip"; //check if there is a description
				$html.= '<li class="'.$class.'" title="'.JText::_($param[2]).'" >'.$param[0];
				$html.= $param[1].'</li>';
  			$i++;
  		}
  
  		if (count($params) < 1) {
  			$html.= "<li><p class=\"noparams\">".JText::_('JLIB_HTML_NO_PARAMETERS_FOR_THIS_ITEM')."</p></li>";
  		}
      $html.="</ul>";
  		return $html;
  	}
    
    function getRaw(){
      return $this->_raw;
    }
    
    function setRaw($raw){
      $this->_raw = $raw;
    }
    
    function getDataArray(){
      return (array)$this->_registry['_default']['data'];
    }
    
    public function & getXML(){
      return $this->_xml;
    }
    
    function loadJSON($data){
  		return $this->loadString($data, 'JSON');
  	}
    
    function loadIni($data){
  		return $this->loadString($data, 'ini');
  	}
  }
}else{
  class OfflajnJParameter extends JParameter{
  	function __construct($data, $path = ''){
  		parent::__construct($data, $path);
      $this->addElementPath(JFolder::folders(OFFLAJNADMINPARAMPATH, '.', false, true));
  	}
    
    function getRaw(){
      return $this->_raw;
    }
    
    function setRaw($raw){
      $this->_raw = $raw;
    }
    
  	function render($name = 'params', $group = '_default')
  	{
  		if (!isset($this->_xml[$group])) {
  			return false;
  		}
  
  		$params = $this->getParams($name, $group);
  		$html = array();
  		$html[] = '<table width="100%" class="paramlist admintable parsed" cellspacing="0">';
  
  		if ($description = $this->_xml[$group]->attributes('description')) {
  			// add the params description to the display
  			$desc	= JText::_($description);
  			$html[]	= '<tr><td class="paramlist_description" colspan="2">'.$desc.'</td></tr>';
  		}
      $i=1;
  		foreach ($params as $param){
  		  $class = ($i%2)? 'blue' : '';
        if((strlen($param[0])== 0 || strlen($param[1])== 0) && ( false === strpos($param[3] ,'LEVEL'))){
          $class = 'hide';
          $i--;
        }
        if (strlen($param[2])!= 0) $class.=" hasOfflajnTip"; //check if there is a description
  			$html[] = '<tr class="'.$class.'" title="'.JText::_($param[2]).'">';
  			
  			if ($param[0]) {
  				$html[] = '<td width="40%" class="paramlist_key"><span class="editlinktip">'.$param[0].'</span></td>';
  				$html[] = '<td class="paramlist_value">'.$param[1].'</td>';
  			} else {
  				$html[] = '<td class="paramlist_value" colspan="2">'.$param[1].'</td>';
  			}
  
  			$html[] = '</tr>';
  			$i++;
  		}
  
  		if (count($params) < 1) {
  			$html[] = "<tr><td colspan=\"2\"><i>".JText::_('There are no Parameters for this item')."</i></td></tr>";
  		}
  
  		$html[] = '</table>';
  
  		return implode("\n", $html);
  	}
    
    function &getXML(){
      return $this->_xml;
    }
  }
}