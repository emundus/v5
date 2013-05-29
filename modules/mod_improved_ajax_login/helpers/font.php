<?php
/*-------------------------------------------------------------------------
# mod_improved_ajax_login - Improved AJAX Login and Register
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
if(!class_exists('OfflajnFontHelper')){
  
  class OfflajnFontHelper {
    function OfflajnFontHelper($params){
      $this->_params = &$params;
      $this->_parser = new OfflajnValueParser();
    }
    
    function parseFonts(){
      $fonts = array();
      foreach($this->_params->toArray() AS $k => $f){
        if(strpos($k, 'font')!==false && isset($f[0]) && $f[0] == '{'){
          $f = json_decode($f, true);
  
          $tabs = array_keys($f);
          
          $default_tab = $tabs[0];
          $f['default_tab'] = $default_tab;
          if(version_compare(JVERSION,'3.0','ge'))
            $this->_params->set($k,$f);
          else
            $this->_params->setValue($k,$f);
          if(!isset($f[$default_tab]['bold'])) $f[$default_tab]['bold'] = 400;
          $weight = $f[$default_tab]['bold'] ? 700 : 400;
          if(!isset($f[$default_tab]['italic'])) $f[$default_tab]['italic'] = '';
          $italic = $f[$default_tab]['italic'] ? 'italic' : '';
          $subset = $this->_getSubset(isset($f[$default_tab]['subset']) ? $f[$default_tab]['subset'] : 'latin');

          foreach($f AS $k => $t){
          if($k == 'default_tab') continue;
          if(isset($t['type']) && $t['type'] == '0' || !isset($t['type']) && $f[$default_tab]['type'] == '0') continue;
          
          
            $_family = isset($t['family']) ? $t['family'] : $f[$default_tab]['family'];
            $_subset = (isset($t['subset']) ? $this->_getSubset($t['subset']) : $subset);
            $_weight = (isset($t['bold']) ? ($t['bold'] ? 700 : 400) : $weight);
            $_italic = (isset($t['italic']) ? ($t['italic'] ? 'italic' : '') : $italic);
            if(!isset($fonts[$_family]))
              $fonts[$_family] = array('subset' => array());
            $fonts[$_family]['subset'][] = $_subset;
            $fonts[$_family]['options'][] = $_weight.$_italic;
          }
        }
      }
    
      $query = '';
      foreach($fonts AS $k => $font){
        if($k == '') continue;
        if($query != '') $query.='|';
        $query.= $k.':'.implode(',',array_unique($font['options']));
      }
      if($query == '') return ''; 
      $url = 'https://fonts.googleapis.com/css?family='.$query;
      
      return "@import url('".$url."');\n";;
    }
    
    /*
    Ha $loadDefaultTab true, akkor az aktuális tab hiányzó értékeibe beletölti a default tabból az értékeket.
    Ha a $justValue true, akkor csak az adott css tulajdonság értékét jeleníti meg.
    */
    
    function _printFont($name, $tab, $excl = null, $incl=null, $loadDefaultTab = false, $justValue = false){
      global $ratio;
      if(!$ratio) $ratio = 1;
      $f = $this->_params->get($name);
      if(!$tab) $tab = $f['default_tab'];
      $t = $f[$tab];
      if($loadDefaultTab && $tab != $f['default_tab']){
        foreach($f[$f['default_tab']] AS $k => $v){
          if(!isset($t[$k])) $t[$k] = $v;
        }
      }
      $family = '';
      if(isset($t['type']) && $t['type'] != '0' && isset($t['family'])) $family = "'".$t['family']."'";
      if(isset($t['afont']) && $t['afont'] != ''){
        $afont = $this->_parser->parse($t['afont']);
        if($afont[1]){
          if($family != '') $family.= ',';
          $family.=$afont[0];
        }
      }
      if((!$excl || !in_array('font-family', $excl)) && (!$incl || in_array('font-family', $incl)))
        if($family != '') 
          if(!$justValue) echo 'font-family: '.$family.";\n";
            else echo $family;
      
      if((!$excl || !in_array('font-size', $excl)) && (!$incl || in_array('font-size', $incl)))
        if(isset($t['size']) && $t['size'] != '') 
          if(!$justValue){
            $s = $this->_parser->parse($t['size']);
            $s[0] = intval($s[0]*$ratio);
            echo 'font-size: '.implode('',$s).";\n";
          }else{
            $s = $this->_parser->parse($t['size']);
            $s[0] = intval($s[0]*$ratio);
            echo implode('',$s);
          }
      
      if((!$excl || !in_array('color', $excl)) && (!$incl || in_array('color', $incl)))
        if(isset($t['color']) && $t['color'] != '') 
          if(!$justValue) echo 'color: #'.$t['color'].";\n";
            else echo '#'.$t['color'];
      
      if((!$excl || !in_array('font-weight', $excl)) && (!$incl || in_array('font-weight', $incl)))
        if(isset($t['bold'])) 
          if(!$justValue) echo 'font-weight: '.($t['bold'] == '1' ? 'bold' : 'normal').";\n";
            else echo ($t['bold'] == '1' ? 'bold' : 'normal');
      
      if((!$excl || !in_array('font-style', $excl)) && (!$incl || in_array('font-style', $incl)))
        if(isset($t['italic'])) 
          if(!$justValue) echo 'font-style: '.($t['italic'] == '1' ? 'italic' : 'normal').";\n";
            else echo ($t['italic'] == '1' ? 'italic' : 'normal');
      
      if((!$excl || !in_array('text-decoration', $excl)) && (!$incl || in_array('text-decoration', $incl)))
        if(isset($t['underline'])) 
          if(!$justValue) echo 'text-decoration: '.($t['underline'] == '1' ? 'underline' : 'none').";\n";
            else echo ($t['underline'] == '1' ? 'underline' : 'none');
      
      if((!$excl || !in_array('text-align', $excl)) && (!$incl || in_array('text-align', $incl)))
        if(isset($t['align'])) 
          if(!$justValue) echo 'text-align: '.$t['align'].";\n";
            else echo $t['align'];
      
      if((!$excl || !in_array('text-shadow', $excl)) && (!$incl || in_array('text-shadow', $incl)))
        echo isset($t['tshadow']) ? $this->getTextShadow($t['tshadow']) : '';
      
      if((!$excl || !in_array('line-height', $excl)) && (!$incl || in_array('line-height', $incl)))
        if(isset($t['lineheight'])) 
          if(!$justValue){
            if($ratio == 1)
              echo 'line-height: '.$t['lineheight'].";\n";
            else{
              $lht = $t['lineheight'];
              $lh = intval($t['lineheight']);
              if($lh > 0){
                $lhu = str_replace($lh,'',$t['lineheight']);
                $lh = intval($lh*$ratio);
                echo 'line-height: '.$lh.$lhu.";\n";
              }else{
                echo 'line-height: '.$t['lineheight'].";\n";
              }
            }
          }else echo $t['lineheight'];
    }
    
    function printFont($name, $tab, $loadDefaultTab = false){
      $this->_printFont($name, $tab, null, null, $loadDefaultTab);
    }
    
    function printFontExcl($name, $tab, $excl, $loadDefaultTab = false){
      $this->_printFont($name, $tab, $excl, null, $loadDefaultTab);
    }
    
    function printFontIncl($name, $tab, $incl, $loadDefaultTab = false){
      $this->_printFont($name, $tab, null, $incl, $loadDefaultTab);
    }
    
    function getTextShadow($s){
      $ts = $this->_parser->parse($s,'');
      if(!$ts[4]) return '';
      if (strlen($ts[3]) > 6) {
        preg_match('/(..)(..)(..)(..)/', $ts[3], $m);
        $ts[3] = 'rgba('.hexdec($m[1]).','.hexdec($m[2]).','.hexdec($m[3]).','.round(hexdec($m[4])/255.0, 2).')';
      } else $ts[3] = '#'.$ts[3];
      while(count($ts) > 4) array_pop($ts);
      return 'text-shadow: '.implode(' ',$ts).";\n";
    }
    
    function _getSubset($subset){
      if($subset == 'LatinExtended'){
        $subset = 'latin,latin-ext';
      }else if($subset == 'CyrillicExtended'){
        $subset = 'cyrillic,cyrillic-ext';
      }else if($subset == 'GreekExtended'){
        $subset = 'greek,greek-ext';
      }
      return $subset;
    }

    function printPropertyValue($name, $tab, $prop, $loadDefaultTab = false){
      $this->_printFont($name, $tab, null, array($prop), $loadDefaultTab, true);
    }
  }
}

?>