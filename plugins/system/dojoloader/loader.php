<?php
/*-------------------------------------------------------------------------
# com_smartslider - Smart Slider
# -------------------------------------------------------------------------
# @ author    Roland Soós
# @ copyright Copyright (C) 2013 Nextendweb.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.nextendweb.com
-------------------------------------------------------------------------*/
?><?php

jimport( 'joomla.filesystem.file' );

class DojoLoader{

  var $path;
  
  var $scope;
  
  var $scripts;
  
  var $script;
  
  function __construct($version = '1.6.1', $scope = 'o'){
    $this->version = $version;
    $this->scope = $scope;
    $this->script = array();
    $this->scripts = array();
    $this->files = array();
    $this->path = dirname(__FILE__).DIRECTORY_SEPARATOR.'dojo'.DIRECTORY_SEPARATOR.$this->version.DIRECTORY_SEPARATOR;
  }
  
	function &getInstance($version = '1.6.1', $scope = 'o'){
		static $instances;
		if (!isset( $instances )) {
			$instances = array();
		}
    
    if(!$version) return $instances;

		if (empty($instances[$version.$scope])){
			$instance = new DojoLoader($version, $scope);

			$instances[$version.$scope] =& $instance;
		}

		return $instances[$version.$scope];
	}
  
  // Require - static
  function r($library, $version = null, $scope = 'o'){
    if($version == null)
      $l = DojoLoader::getInstance();
    else
      $l = DojoLoader::getInstance($version, $scope);
    $l->load($library);
  }
  
  function addScript($script, $version = null, $scope = 'o'){
    if($version == null)
      $l = DojoLoader::getInstance();
    else
      $l = DojoLoader::getInstance($version, $scope);
    $l->_addScript($script);
  }
  
  function _addScript($script){
    $this->script[] = $script;
  }
  
  function addScriptFile($file, $version = null, $scope = 'o') {
    DojoLoader::addAbsoluteScriptFile(JPATH_SITE.$file, $version, $scope);
  }
  
  function addAbsoluteScriptFile($file, $version = null, $scope = 'o'){
    if ($version == null) $l = DojoLoader::getInstance();
    else $l = DojoLoader::getInstance($version, $scope);
    $l->_addScriptFile($file);
  }
  
  function _addScriptFile($file){
    $this->files[$file] = 1;
  }
  
  function load($l){
    $jspath = str_replace('.' ,DIRECTORY_SEPARATOR, $l).'.js';
    $this->scripts[$l] = $jspath;
  }
  
  function build(){
    if(defined('WP_ADMIN')){
      $document =& JFactory::getDocument();
      $document->addScript($this->_build());
    }else{
      $body = JResponse::getBody();
  		$body = preg_replace('/<head>/', '<head><script src="'.$this->_build().'" type="text/javascript"></script>', $body, 1);
      JResponse::setBody($body);
    }
  }
  
  function _build(){
    $keys = array_keys($this->scripts);
    $script = implode("\n",$this->script);
    $fkeys = array_keys($this->files);
    
    $folder = $this->checkFolders();
    
    $pathfolder = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'dojo'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR;
    
    $hashcode = '';
    for($i=0; $i < count($fkeys); $i++){
      $hashcode.= filemtime($fkeys[$i]);
    }
    
    $hash = md5(implode('', $keys).implode('', $fkeys).$script.$hashcode).'.js';
    
    $path = $pathfolder.$hash;
    
    if(!JFile::exists($path)){
      $t = '
        (function(){';
      if(!isset($_POST['offlajnformrenderer'])){
        $t.='
            djConfig = {
              modulePaths: {
                "dojo": "'.$this->urlToDojo().'dojo",
                "dijit": "'.$this->urlToDojo().'dijit",
                "dojox": "'.$this->urlToDojo().'dojox"
              }
              
              '.($this->scope != '' ? ',
              scopeMap: [
                [ "dojo", "'.$this->scope.'dojo" ],
                [ "dijit", "'.$this->scope.'dijit" ],
                [ "dojox", "'.$this->scope.'dojox" ]
              ]' : '').'
            };
            if(typeof '.$this->scope.'dojo === "undefined"){
        ';
        $t.= JFile::read($this->path.'dojo'.DIRECTORY_SEPARATOR.'dojo.js');
        $t.= "} \n";
      }
      if($this->scope != ''){
        $t.= "\nvar dojo = ".$this->scope."dojo;\n";
        $t.= "\nvar dijit = ".$this->scope."dijit;\n";
        $t.= "\nvar dojox = ".$this->scope."dojox;\n";
      }
      for($i=0; $i < count($keys); $i++){
        $t.= $this->read($this->scripts[$keys[$i]])."\n";
      }
      for($i=0; $i < count($fkeys); $i++){
        $t.= $this->readAbs($fkeys[$i])."\n";
      }
      $t.='dojo.addOnLoad(function(){'.$script.'});
      ';
      $t.= 'djConfig = {};})();';
      JFile::write($path, $t);
    }
    return JUri::root(true).'/media/dojo/'.$folder.'/'.$hash;
  }
  
  function checkDependencies($script){
    $dep = '';
    preg_match_all ( '/dojo\.require\("([_\.a-zA-Z0-9]*?)"\);/' , $script , $out);
    if(isset($out[1])){
      foreach($out[1] AS $o){
        if(!isset($this->scripts[$o])){
          $this->load($o);
          $dep.=$this->read($this->scripts[$o]);
        }
      }
    }
    return $dep;
  }
  
  function readAbs($s){
    $t = JFile::read($s);
    return $this->checkDependencies($t)."\n".$t;
  }
  
  function read($s){
    $t = JFile::read($this->path.$s);
    if($s == 'dojo/dojo.js') return $t;
    return $this->checkDependencies($t)."\n".$t;
  }
  
  function urlToDojo(){
    if(version_compare(JVERSION,'1.6.0','ge'))
      return JUri::root(true).'/plugins/system/dojoloader/dojo/'.$this->version.'/';
    return JUri::root(true).'/plugins/system/dojo/'.$this->version.'/';
  }
  
  function checkFolders() {
    $date = date('Ymd');
    $folders = array();
    $path = JPATH_SITE.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'dojo';
    if(isset($_GET['nextendclearcache']) || !JFolder::exists($path.DIRECTORY_SEPARATOR.$date)) {
      $folders = JFolder::folders($path, '', '', 1);
      if(is_array($folders)){
        foreach($folders as $folder) {
          JFolder::delete($folder);
        }
      }
      JFolder::create($path.DIRECTORY_SEPARATOR.$date, 0777);
    }
    return $date;
  }
}
?>