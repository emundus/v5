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

if(!isset($_REQUEST['offlajnformrenderer']) && (!isset($_SESSION['offlajnurl']) || !isset($_SESSION['offlajnurl'][$_SERVER['REQUEST_URI']]))){
  $_SESSION['offlajnurl'][$_SERVER['REQUEST_URI']] = true;
  if($_SERVER['REQUEST_METHOD']!='POST'){
    header('LOCATION: '.$_SERVER['REQUEST_URI']);
    exit;
  }
}
if(version_compare(JVERSION,'3.0.0','l') && !function_exists('Nextendjimport')){
  function Nextendjimport($key, $base = null){
    return jimport($key);
  }
}
  
jimport( 'joomla.form.helper' );
jimport( 'joomla.form.formfield' );
jimport( 'joomla.filesystem.folder' );
Nextendjimport( 'joomla.utilities.simplexml' );

@ini_set('memory_limit','260M');
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
define("OFFLAJNADMINPARAMPATH", dirname(__FILE__).DS.'..');
$_SESSION['OFFLAJNADMINPARAMPATH'] = OFFLAJNADMINPARAMPATH;

if(version_compare(JVERSION,'1.6.0','ge')) JFormHelper::addFieldPath(JFolder::folders(OFFLAJNADMINPARAMPATH, '.', false, true));
//else if(isset($this)) $this->addElementPath(JFolder::folders(OFFLAJNADMINPARAMPATH, '.', false, true));

include_once(dirname(__FILE__).DS.'library'.DS.'fakeElementBase.php');
include_once(dirname(__FILE__).DS.'library'.DS.'parameter.php');
include_once(dirname(__FILE__).DS.'library'.DS.'flatArray.php');
include_once(dirname(__FILE__).DS.'library'.DS.'JsStack.php');

class JElementOfflajnDashboard extends JOfflajnFakeElementBase
{
	var	$_name = 'OfflajnDashboard';
  var $attr;
	
	function loadDashboard(){
    $logoUrl = JURI::base(true).'/../modules/'.$this->_moduleName.'/params/offlajndashboard/images/dashboard-offlajn.png';
    $supportTicketUrl = JURI::base(true).'/../modules/'.$this->_moduleName.'/params/offlajndashboard/images/support-ticket-button.png';
    $supportUsUrl = JURI::base(true).'/../modules/'.$this->_moduleName.'/params/offlajndashboard/images/support-us-button.png';
    global $offlajnDashboard;
    ob_start();
    include('offlajndashboard.tmpl.php');
    $offlajnDashboard = ob_get_contents();
    ob_end_clean();	
  }
  
	function universalfetchElement($name, $value, &$node){
    define("OFFLAJNADMIN", "1");
  	$this->loadFiles();
  	$this->loadFiles('legacy', 'offlajndashboard');
    $j17 = 0;
    if(version_compare(JVERSION,'1.6.0','ge')) $j17 = 1;
    $style = "";
	  $opened_ids = json_decode(stripslashes(@$_COOKIE[$this->_moduleName."lastState"]));
	  if ($opened_ids){
      foreach ( $opened_ids as $id) {
      $style.= '#content-box #'.$id.' div.content{'
      	. 'opacity: 1;'
      	. 'height: 100%;'
      	. '}'; 
      }
    }
	  $document =& JFactory::getDocument();

    $document->addStyleDeclaration( $style );	  
    DojoLoader::r('dojo.uacss');

    DojoLoader::addScript('
      var offlajnParams = new OfflajnParams({
        joomla17 : '.$j17.',
        moduleName : "'.$this->_moduleName.'"
      });
    ');

    $lang =& JFactory::getLanguage();
    $lang->load($this->_moduleName, dirname(__FILE__).DS.'..'.DS.'..');
  	$xml = dirname(__FILE__).DS.'../../'.$this->_moduleName.'.xml';
  	if(!file_exists($xml)){
      $xml = dirname(__FILE__).DS.'../../install.xml';
      if(!file_exists($xml)){
        return;
      }
    }
    if(version_compare(JVERSION,'3.0','ge')){
      $xmlo = JFactory::getXML($xml);
      $xmld = $xmlo;
    }else{
      jimport( 'joomla.utilities.simplexml' );
      $xmlo = JFactory::getXMLParser('Simple');
      $xmlo->loadFile($xml);
      $xmld = $xmlo->document;
    }
    
    if(isset($xmld->hash) && $xmld->hash[0]){
      if(version_compare(JVERSION,'3.0','ge')){
        $hash = (string)$xmld->hash[0];
      }else
        $hash = (string)$xmld->hash[0]->data();
    }
      
    $this->attr = $node->attributes();
    
    if (!isset($hash)) {
      $this->generalInfo = '<iframe src="http://offlajn.com/index2.php?option=com_offlajn_update_info&amp;v='.(version_compare(JVERSION,'3.0','ge') ?  (string)$xmld->version : $xmld->version[0]->data()).'" frameborder="no" style="border: 0;" width="100%"></iframe>';
      $this->relatedNews = '<iframe id="related-news-iframe" src="http://offlajn.com/index2.php?option=com_offlajn_related_news&amp;tag='.@$this->attr['blogtags'].'" frameborder="no" style="border: 0;" width="100%" ></iframe>';    
    } else {
      $this->generalInfo = '<iframe src="http://offlajn.com/index2.php?option=com_offlajn_update_info&amp;hash='.base64_url_encode($hash).'&amp;v='.(version_compare(JVERSION,'3.0','ge') ? (string)$xmld->version : $xmld->version[0]->data()).'&amp;u='.JURI::root().'" frameborder="no" style="border: 0;" width="100%"></iframe>';
      $this->relatedNews = '<iframe id="related-news-iframe" src="http://offlajn.com/index2.php?option=com_offlajn_related_news&amp;tag='.@$this->attr['blogtags'].'" frameborder="no" style="border: 0;" width="100%" ></iframe>';    
    }
    $this->loadDashboard();
    if(!version_compare(JVERSION,'1.6.0','ge')){
      preg_match('/(.*)\[([a-zA-Z0-9]*)\]$/', $name, $out);
      @$control = $out[1];
      
      $x = file_get_contents($xml);
      preg_match('/<fieldset.*?>(.*)<\/fieldset>/ms', $x, $out);
      
      $params = str_replace(array('<field', '</field'),array('<param','</param'),$out[0]);
      $n = new JSimpleXML();
      $n->loadString($params);
      $attrs = $n->document->attributes();
      if(($_REQUEST['option'] == 'com_modules') || ($_REQUEST['option'] == 'com_advancedmodules')){
        $n->document->removeChild($n->document->param[0]);
        $params = new OfflajnJParameter('');
        $params->setXML($n->document);
        $params->_raw = & $this->_parent->_raw;
        $params->bind($this->_parent->_raw);
        echo $params->render($control);
      }
    }
    if(!isset($hash) || $hash == '') return;
	  return "";
	} 
}


function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '-_,');
}

if(version_compare(JVERSION,'1.6.0','ge')) {
        class JFormFieldOfflajnDashboard extends JElementOfflajnDashboard {}
}

if (!function_exists('json_encode')){
  function json_encode($a=false){
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)){
      if (is_float($a)){
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)){
      if (key($a) !== $i){
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList){
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }else{
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}


if (!function_exists('json_decode')) {
  function json_decode($json) {
    $comment = false;
    $out     = '$x=';
    for ($i=0; $i<strlen($json); $i++) {
      if (!$comment) {
        if (($json[$i] == '{') || ($json[$i] == '[')) {
          $out .= 'array(';
        }
        elseif (($json[$i] == '}') || ($json[$i] == ']')) {
          $out .= ')';
        }
        elseif ($json[$i] == ':') {
          $out .= '=>';
        }
        elseif ($json[$i] == ',') {
          $out .= ',';
        }
        elseif ($json[$i] == '"') {
          $out .= '"';
        }
        /*elseif (!preg_match('/\s/', $json[$i])) {
          return null;
        }*/
      }
      else $out .= $json[$i] == '$' ? '\$' : $json[$i];
      if ($json[$i] == '"' && $json[($i-1)] != '\\') $comment = !$comment;
    }
    eval($out. ';');
    return $x;
  }
}