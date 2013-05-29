<?php
/*-------------------------------------------------------------------------
# com_smartslider - Smart Slider
# -------------------------------------------------------------------------
# @ author    Roland Soós
# @ copyright Copyright (C) 2012 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(version_compare(JVERSION,'3.0.0','ge')) require_once(JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'nextendjoomla3compat'.DIRECTORY_SEPARATOR.'nextendjoomla3compat.php');

require_once(dirname(__FILE__).DS.'imageuploader.php');

require_once(dirname(__FILE__).DS.'formrenderer.php');

class  plgSystemOfflajnParams extends JPlugin
{
	function plgSystemOfflajnParams(& $subject, $config){
		parent::__construct($subject, $config);
	}

  function addNewTab($id, $title, $text, $position = 'last', $class=''){
    global $offlajnParams;
    if($position != 'first') $position = 'last';
    $offlajnParams[$position][] = self::renderNewTab($id, $title, $text, $class);
  }
  
  function renderNewTab($id, $title, $text, $class=''){
    ob_start();
    if(version_compare(JVERSION,'1.6.0','ge'))
      include(dirname(__FILE__).DS.'tab16.tpl.php');
    else
      include(dirname(__FILE__).DS.'tab15.tpl.php');
      
    return ob_get_clean();
  }
  
  function getElementById(&$dom, $id){
    $xpath = new DOMXPath($dom);
    return $xpath->query("//*[@id='$id']")->item(0);
  }

	function onAfterDispatch(){
    global $offlajnParams, $offlajnDashboard;
    $app = JFactory::getApplication();
    if (!defined('OFFLAJNADMIN') || isset($_REQUEST['output']) && $_REQUEST['output'] == 'json') {
        return;
    }
    
    $doc = JFactory::getDocument();
    $c = $doc->getBuffer('component');
    
		$dom = new DomDocument();
    @$dom->loadHtml('<?xml encoding="UTF-8"><div>'.mb_convert_encoding($c, 'HTML-ENTITIES', "UTF-8").'</div>');
		$lis = array();

    $moduleparams = "";
    if(version_compare(JVERSION,'3.0.0','ge') && !$this->getElementById($dom, 'module-sliders')) {

      // Joomla 3.0.3 fix
      if(version_compare(JVERSION,'3.0.3','ge')) {
        $moduleparams = $this->getElementById($dom, 'collapse0');
      }else{
        $moduleparams = $this->getElementById($dom, 'options-basic');
      }

      if($moduleparams){
        $element = $dom->createElement('div');
        $element->setAttribute ('id','content-box');
        $moduleparams->appendChild($element);
        $moduleparams = $element;
        $element = $dom->createElement('div');
        $element->setAttribute ('id','module-sliders');
        $element->setAttribute ('class','pane-sliders');
        $moduleparams->appendChild($element);
        $moduleparams = $element;
      }
    }elseif(version_compare(JVERSION,'1.6.0','ge')) {
      $moduleparams = $this->getElementById($dom, 'module-sliders');
    }else{
      $moduleparams = $this->getElementById($dom, 'menu-pane');
    }
    if($moduleparams){
      $removed = array();
      while($cNode = $moduleparams->firstChild){
        $removed[] = $moduleparams->removeChild($cNode);
      } 
      if(version_compare(JVERSION,'1.6.0','ge')) {
        array_splice($removed, 0, 2);
      }else{
        array_splice($removed, 0, 1);
      }
      $html = '<div>';
      $html.= isset($offlajnDashboard) ? $offlajnDashboard : '';
      $html.= isset($offlajnParams['first']) && is_array($offlajnParams['first']) ? implode("\n",$offlajnParams['first']) : '';
      $html.= isset($offlajnParams['last']) && is_array($offlajnParams['last']) ? implode("\n",$offlajnParams['last']) : '';
      $html.= '</div>';
      $tabsDom = new DomDocument();
      @$tabsDom->loadHTML('<?xml encoding="UTF-8">'.mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8").'');
  
      $node = $dom->importNode( $tabsDom->getElementsByTagName('div')->item(0), true );
      while($cNode = $node->firstChild){
        if(@$cNode->tagName == 'div')
          $moduleparams->appendChild($cNode);
        else
          $node->removeChild($cNode);
      }
      
      if(count($removed) > 0){
        foreach($removed as $r){
          if($r instanceof DOMElement){
            $r->setAttribute("class", $r->getAttribute("class")." legacy");
            $moduleparams->appendChild($r);
          }
        }
      }
      
      if(!version_compare(JVERSION,'1.6.0','ge')) {
        $tables = $dom->getElementsByTagName('table');
        foreach ($tables as $table) {
          $table->setAttribute("cellspacing", "0");
        }
      }

      $params = $moduleparams->getElementsByTagName('h3');
      foreach ($params as $param) {
        $span = $param->getElementsByTagName('span')->item(0);
        $titleWords = explode(" ", $span->textContent);
        $titleWords[count($titleWords)-1] = "<b>".$titleWords[count($titleWords)-1]."</b>";
        $newTitle = implode(' ', $titleWords);
        
        $span->removeChild($span->firstChild);
        $newText = $dom->createCDATASection($newTitle);
        $span->appendChild($newText);
      }
      
      $j=0;
      foreach ($moduleparams->childNodes as $param) {
        $param->setAttribute("id", "offlajnpanel-".$j);
        $j++;
      }
    }
    
    if (!isset($doc->_script['text/javascript'])) $doc->_script['text/javascript'] = array();
    $doc->_script['text/javascript'] = preg_replace("/window.addEvent.*?pane-toggler.*?\}\);.*?\}\);/i", '',  $doc->_script['text/javascript']);
    
    $doc->_script['text/javascript'].='
      window.addEvent("domready", function(){
        if(document.formvalidator)
          document.formvalidator.isValid = function() {return true;};
      });';
  
    if(version_compare(JVERSION,'3.0.0','ge')) {
      if($moduleparams && $moduleparams->parentNode){
        function getInnerHTML($Node){
             $Document = new DOMDocument();    
             $Document->appendChild($Document->importNode($Node,true));
             return $Document->saveHTML();
        }
        $nc = getInnerHTML($moduleparams->parentNode);
      }else{
        $nc = $dom->saveHTML();
      }
      $nc = preg_replace("/.*?<body>/si", '',  $nc, 1);
      $nc = preg_replace("/<\/body>.*/si", '', $nc, 1);

      $pattern = '/<div\s*class="tab-pane"\s*id="options-basic".*?>/';
      
      if(version_compare(JVERSION,'3.0.3','ge')) {
        $pattern = '/<div\s*class="accordion-body collapse in"\s*id="collapse0".*?>/';
      }
      
      preg_match($pattern, $c, $matches);
      if(count($matches) > 0){
        $c = str_replace($matches[0], $matches[0].$nc, $c);
      }else{
        $c = $nc;
      }
    }else{
      $c = $dom->saveHtml();
      $c = preg_replace("/.*?<body><div>/si", '',  $c, 1);
      $c = preg_replace("/<\/div><\/body>.*/si", '',  $c, 1);
    }
    
    
    
    $doc->setBuffer($c, 'component');
	}
	
	function onAfterInitialise()
	{
		$app = JFactory::getApplication();

		if(!$app->isAdmin() || !isset($_SESSION['offlajnurl']) || !isset($_SESSION['offlajnurl'][$_SERVER['REQUEST_URI']])){
			return;
		}

		$template_style_id = 2;

		$db = JFactory::getDbo();
		if(version_compare(JVERSION,'1.6.0','ge')) {
		  $db->setQuery('SELECT template, params FROM #__template_styles WHERE `client_id` = 1 AND `id`= '. (int)$template_style_id.' ORDER BY id ASC');
		  $row = $db->loadObject();
		  
  		if(!$row){
  			return;
  		}
  		
  		if(empty($row->template)){
  			return;
  		}
  		
  		if(file_exists(JPATH_THEMES. DS. $row->template)){
  		  $tmpl = $app->getTemplate(true);
  		  $tmpl->template = $row->template;
    		$tmpl->params = new JRegistry($row->params);
  		}
		}else{
		  if($app->getTemplate() != 'khepri'){
  		  $db->setQuery('UPDATE #__templates_menu SET template = "khepri" WHERE menuid = 0 AND client_id = 1');
  		  $db->query();
        header('LOCATION: '.$_SERVER['REQUEST_URI']);
        exit;
  		}
		}
	}
}
