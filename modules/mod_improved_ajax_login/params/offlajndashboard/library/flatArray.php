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

if(!function_exists('offflat_array')){

  /* Multidimensional to flat array */
  function offflat_array($array){
    if(!is_array($array)) return array();
   $out=array();
   foreach($array as $k=>$v){
    if(is_array($array[$k]) && offisAssoc($array[$k])){
     $out+=offflat_array($array[$k]);
    }else{
     $out[$k]=$v;
    }
   }
   return $out;
  }
  
  function offisAssoc($arr)
  {
      return array_keys($arr) !== range(0, count($arr) - 1);
  }

}

?>