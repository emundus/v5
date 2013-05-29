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
if(!class_exists('OfflajnImageHelper')){
    require_once(dirname(__FILE__).DS.'color.php');
    
    class OfflajnImageHelper{
        var $cache;
        
        var $cacheUrl;
    
        var $step = 1;
        
        var $c;
        
        function OfflajnImageHelper($cacheDir, $cacheUrl){
          $this->cache = $cacheDir;
          $this->cacheUrl = $cacheUrl;
          $this->c = new OfflajnColorHelper();
        }
        
        function colorizeImage($img, $targetColor, $baseColor){
          $targetHexArr = $this->c->hex82hex($targetColor);
          $targetColor = $targetHexArr[0];
          $alpha = hexdec($targetHexArr[1]);
          $c1 = $this->c->hex2hsl($baseColor);
          $c2 = $this->c->hex2hsl($targetColor);
          $im = imagecreatefrompng($img);
          $height = imagesy($im);
          $width = imagesx($im);
          $imnew = imagecreatetruecolor($width, $height);
          imagesavealpha($imnew, true);  
          imagealphablending($imnew, false);
          $transparent = imagecolorallocatealpha($imnew, 255, 255, 255, 127);
          imagefilledrectangle($imnew, 0, 0, $width, $height, $transparent);
          $rgb = $this->c->rgb2array($targetColor);
          for($x=0; $x<$width; $x++){
              for($y=0; $y<$height; $y++){
                  $rgba = ImageColorAt($im, $x, $y);
                  $rgb = array((($rgba >> 16) & 0xFF), (($rgba >> 8) & 0xFF), $rgba & 0xFF);
                  $hsl = $this->c->rgb2hsl($rgb);
                  $a[0] = $hsl[0] + ($c2[0] - $c1[0]);
                  $a[1] = $hsl[1] * ($c2[1] / $c1[1]);
                  if($a[1] > 1) $a[1] = 1;
                  $a[2] = exp(log($hsl[2]) * log($c2[2]) / log($c1[2]) );
                  if($a[2] > 1) $a[2] = 1;
                  $rgb = $this->c->hsl2rgb($a);
                  $A = 0xFF-(($rgba >> 24)*2) & 0xFF;
                  $A = (int)($A * ($alpha/0xFF));
                  if($A > 0xFF) $A = 0xFF;
                  $A = (int)((0xFF-$A)/2);
                  imagesetpixel($imnew, $x, $y, imagecolorallocatealpha($imnew, $rgb[0], $rgb[1], $rgb[2], $A));
              }
          }
          $hash = md5($img.$targetColor.$alpha).'.png';
          imagepng($imnew, $this->cache.DS.$hash);
          imagedestroy($imnew);
          imagedestroy($im);
          return $this->cacheUrl.$hash; 
        }
    }
}
?>