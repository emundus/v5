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
defined('_JEXEC') or die('Restricted access');

$fonts = new OfflajnFontHelper($params);
echo $fonts->parseFonts();

$GLOBALS['googlefontsloaded'] = array();
foreach($params->toArray() AS $k => $p){
  if (strpos($k, 'grad')) $p = explode('-', $p);
  elseif (strpos($k, 'comb')) $p = explode('|*|', $p);

  if ($k != 'params') $$k = $p;
}

if(!function_exists('shift_color')){
  function shift_color($hex, $s) {
  	$c = hexdec($hex);
  	$r = (($c >> 16) & 255)+$s;
  	$g = (($c >> 8) & 255)+$s;
  	$b = ($c & 255)+$s;
  	if ($r>255) $r=255; elseif ($r<0) $r=0;
  	if ($g>255) $g=255; elseif ($g<0) $g=0;
  	if ($b>255) $b=255; elseif ($b<0) $b=0;
  	printf('%02X%02X%02X', $r, $g, $b);
  }
}
?>

#loginComp {
  display: inline-block;
  margin-bottom: 25px;
}
#loginComp #loginBtn {
  display: none;
}

.selectBtn {
  display: inline-block;
  *display: inline;
  z-index: 10000;
  user-select: none;
  -moz-user-select: none;
  -webkit-user-select: auto;
  -ms-user-select: none; 
}
.selectBtn:hover,
.selectBtn:active,
.selectBtn:focus {
  background: none;
}
#logoutForm,
#loginForm {
  display: inline-block;
  margin: 0;
}