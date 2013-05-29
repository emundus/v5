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
if(!class_exists('OfflajnHelper7')){

require_once(dirname(__FILE__).DS.'ColorHelper.php');

  class OfflajnHelper7{
  
    var $cache;
    
    var $step = 1;
    
    var $c;
    
    function OfflajnHelper7($cacheDir){
      $this->cache = $cacheDir;
      $this->c = new OfflajnColor();
    }
    
    function generateRoundedCorner($r, $hex){
      $r = $r*2-4;
      $rgb = $this->rgb2array($hex);
  
      $imnew = imagecreatetruecolor($r*2+10, $r*2+10);
      imagealphablending($imnew, false);
      
      imagesavealpha($imnew, true);
      $transparent = imagecolorallocatealpha($imnew, 255, 255, 255, 127);
  		imagefilledrectangle($imnew, 0, 0, $r*2+10, $r*2+10, $transparent);
  
  
      $white = imagecolorallocate($imnew, 0, 0, 0);
  
      $this->imageSmoothArc($imnew, $r, $r, $r*2, $r*2, $white, 0, M_PI*2);
  
      for($x=0; $x<$r*2+10; $x++){
          for($y=0; $y<$r*2+10; $y++){
              $rgba = ImageColorAt($imnew, $x, $y);
              $A = ($rgba >> 24) & 0xFF;
              if($A == 127){
                imagesetpixel($imnew, $x, $y, imagecolorallocatealpha($imnew, $rgb[0], $rgb[1], $rgb[2], 0));
              }else{
                imagesetpixel($imnew, $x, $y, imagecolorallocatealpha($imnew, $rgb[0], $rgb[1], $rgb[2], -$A+127));
              }
          }
      }
      $img = array();
      $imtl = imagecreatetruecolor($r, $r);
      imagealphablending($imtl, false);
      imagesavealpha($imtl, true);
      $transparent = imagecolorallocatealpha($imtl, 255, 255, 255, 127);
  		imagefilledrectangle($imtl, 0, 0, $r, $r, $transparent);
      imagecopyresampled($imtl, $imnew, 0, 0, 0, 0, $r, $r, $r, $r);
      $img[] = $hash = md5($r.$hex).'tl.png';
      imagepng($imtl, $this->cache.DS.$hash);
      imagedestroy($imtl);
      
      $imtr = imagecreatetruecolor($r, $r);
      imagealphablending($imtr, false);
      imagesavealpha($imtr, true);
      $transparent = imagecolorallocatealpha($imtr, 255, 255, 255, 127);
  		imagefilledrectangle($imtr, 0, 0, $r, $r, $transparent);
      imagecopyresampled($imtr, $imnew, 0, 0, $r+2, 0, $r, $r, $r, $r);
      $img[] = $hash = md5($r.$hex).'tr.png';
      imagepng($imtr, $this->cache.DS.$hash);
      imagedestroy($imtr);
      
      $imbl = imagecreatetruecolor($r, $r);
      imagealphablending($imbl, false);
      imagesavealpha($imbl, true);
      $transparent = imagecolorallocatealpha($imbl, 255, 255, 255, 127);
  		imagefilledrectangle($imbl, 0, 0, $r, $r, $transparent);
      imagecopyresampled($imbl, $imnew, 0, 0, 0, $r+2, $r, $r, $r, $r);
      $img[] = $hash = md5($r.$hex).'bl.png';
      imagepng($imbl, $this->cache.DS.$hash);
      imagedestroy($imbl);
      
      $imbr = imagecreatetruecolor($r, $r);
      imagealphablending($imbr, false);
      imagesavealpha($imbr, true);
      $transparent = imagecolorallocatealpha($imbr, 255, 255, 255, 127);
  		imagefilledrectangle($imbr, 0, 0, $r, $r, $transparent);
      imagecopyresampled($imbr, $imnew, 0, 0, $r+2, $r+2, $r, $r, $r, $r);
      $img[] = $hash = md5($r.$hex).'br.png';
      imagepng($imbr, $this->cache.DS.$hash);
      imagedestroy($imbr);
      imagedestroy($imnew);
      return $img; 
    }
    
    function ColorizeImage($img, $hex){
      $im = imagecreatefrompng($img);
      $height = imagesy($im);
      $width = imagesx($im);
      $imnew = imagecreatetruecolor($width, $height);
      imagealphablending($imnew, false);
      imagesavealpha($imnew, true);  
      $transparent = imagecolorallocatealpha($imnew, 255, 255, 255, 127);
  		imagefilledrectangle($imnew, 0, 0, $width, $height, $transparent);
      $rgb = $this->rgb2array($hex);
      for($x=0; $x<$width; $x++){
          for($y=0; $y<$height; $y++){
              $rgba = ImageColorAt($im, $x, $y);
              $R = (($rgba >> 16) & 0xFF) + $rgb[0];
              $G = (($rgba >> 8) & 0xFF) + $rgb[1];
              $B = ($rgba & 0xFF) + $rgb[2];
              $A = ($rgba >> 24) & 0xFF; 
              if ($R > 255) $R = 255;
              if ($G > 255) $G = 255;
              if ($B > 255) $B = 255;
              imagesetpixel($imnew, $x, $y, imagecolorallocatealpha($imnew, $R, $G, $B, $A));
          }
      }
      $hash = md5($img.$hex).'.png';
      imagepng($imnew, $this->cache.DS.$hash);
      imagedestroy($imnew);
      imagedestroy($im);
      return $hash; 
    }
    
    function NewColorizeImage($img, $targetColor, $baseColor){
      $targetHexArr = $this->c->hex82hex($targetColor);
      $targetColor = $targetHexArr[0];
      $alpha = $targetHexArr[1];
      $c1 = $this->c->hex2hsl($baseColor);
      $c2 = $this->c->hex2hsl($targetColor);
      $im = imagecreatefrompng($img);
      $height = imagesy($im);
      $width = imagesx($im);
      $imnew = imagecreatetruecolor($width, $height);
      imagealphablending($imnew, false);
      imagesavealpha($imnew, true);  
      $transparent = imagecolorallocatealpha($imnew, 255, 255, 255, 127);
  		imagefilledrectangle($imnew, 0, 0, $width, $height, $transparent);
      $rgb = $this->rgb2array($targetColor);
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
              $A = 0xFE-(($rgba >> 24)*2) & 0xFF;
              $A = (int)($A * (hexdec($alpha)/0xFE));
              if($A > 0xFF) $A = 0xFF;
              $A = (int)((0xFE-$A)/2);
              imagesetpixel($imnew, $x, $y, imagecolorallocatealpha($imnew, $rgb[0], $rgb[1], $rgb[2], $A));
          }
      }
      $hash = md5($img.$targetColor.$alpha).'.png';
      imagepng($imnew, $this->cache.DS.$hash);
      imagedestroy($imnew);
      imagedestroy($im);
      return $hash; 
    }
    
    function generateAlphaColor($c){
      $im = imagecreatetruecolor(1, 1);
      imagealphablending($im, false);
      imagesavealpha($im, true);  
      $transparent = imagecolorallocatealpha($im, 255, 255, 255, 127);
  		imagefilledrectangle($im, 0, 0, 1, 1, $transparent);
      $color = $this->c->hex2rgba($c);
      $fillcolor = imagecolorallocatealpha($im, $color[0], $color[1], $color[2], 127-$color[3]);
  		imagefilledrectangle($im, 0, 0, 1, 1, $fillcolor);
      
      $hash = md5($c).'.png';
      imagepng($im, $this->cache.DS.$hash);
      imagedestroy($im);
      return $hash;
    }
    
    function generateGradient($w, $h, $c1, $c2, $direction){
      $im = imagecreatetruecolor($w, $h);
      $this->fill($im, $direction, $c1, $c2);
      $hash = md5($w.$h.$c1.$c2.$direction).'.png';
      imagepng($im, $this->cache.DS.$hash);
      imagedestroy($im);
      return $hash;
    }
    
    function fill($im,$direction,$start,$end) {
          
          switch($direction) {
              case 'horizontal':
                  $line_numbers = imagesx($im);
                  $line_width = imagesy($im);
                  list($r1,$g1,$b1) = $this->hex2rgb($start);
                  list($r2,$g2,$b2) = $this->hex2rgb($end);
                  break;
              case 'vertical':
                  $line_numbers = imagesy($im);
                  $line_width = imagesx($im);
                  list($r1,$g1,$b1) = $this->hex2rgb($start);
                  list($r2,$g2,$b2) = $this->hex2rgb($end);
                  break;
              case 'ellipse':
                  $width = imagesx($im);
                  $height = imagesy($im);
                  $rh=$height>$width?1:$width/$height;
                  $rw=$width>$height?1:$height/$width;
                  $line_numbers = min($width,$height);
                  $center_x = $width/2;
                  $center_y = $height/2;
                  list($r1,$g1,$b1) = $this->hex2rgb($end);
                  list($r2,$g2,$b2) = $this->hex2rgb($start);
                  imagefilledrectangle($im, 0, 0, $width, $height, imagecolorallocate( $im, $r1, $g1, $b1 ));
                  break;
              case 'ellipse2':
                  $width = imagesx($im);
                  $height = imagesy($im);
                  $rh=$height>$width?1:$width/$height;
                  $rw=$width>$height?1:$height/$width;
                  $line_numbers = sqrt(pow($width,2)+pow($height,2));
                  $center_x = $width/2;
                  $center_y = $height/2;
                  list($r1,$g1,$b1) = $this->hex2rgb($end);
                  list($r2,$g2,$b2) = $this->hex2rgb($start);
                  break;
              case 'circle':
                  $width = imagesx($im);
                  $height = imagesy($im);
                  $line_numbers = sqrt(pow($width,2)+pow($height,2));
                  $center_x = $width/2;
                  $center_y = $height/2;
                  $rh = $rw = 1;
                  list($r1,$g1,$b1) = $this->hex2rgb($end);
                  list($r2,$g2,$b2) = $this->hex2rgb($start);
                  break;
              case 'circle2':
                  $width = imagesx($im);
                  $height = imagesy($im);
                  $line_numbers = min($width,$height);
                  $center_x = $width/2;
                  $center_y = $height/2;
                  $rh = $rw = 1;
                  list($r1,$g1,$b1) = $this->hex2rgb($end);
                  list($r2,$g2,$b2) = $this->hex2rgb($start);
                  imagefilledrectangle($im, 0, 0, $width, $height, imagecolorallocate( $im, $r1, $g1, $b1 ));
                  break;
              case 'square':
              case 'rectangle':
                  $width = imagesx($im);
                  $height = imagesy($im);
                  $line_numbers = max($width,$height)/2;
                  list($r1,$g1,$b1) = $this->hex2rgb($end);
                  list($r2,$g2,$b2) = $this->hex2rgb($start);
                  break;
              case 'diamond':
                  list($r1,$g1,$b1) = $this->hex2rgb($end);
                  list($r2,$g2,$b2) = $this->hex2rgb($start);
                  $width = imagesx($im);
                  $height = imagesy($im);
                  $rh=$height>$width?1:$width/$height;
                  $rw=$width>$height?1:$height/$width;
                  $line_numbers = min($width,$height);
                  break;
              default:
          }
          
          for ( $i = 0; $i < $line_numbers; $i=$i+1+$this->step ) {
              // old values :
              $old_r=@$r;
              $old_g=@$g;
              $old_b=@$b;
              // new values :
              $r = ( $r2 - $r1 != 0 ) ? intval( $r1 + ( $r2 - $r1 ) * ( $i / $line_numbers ) ): $r1;
              $g = ( $g2 - $g1 != 0 ) ? intval( $g1 + ( $g2 - $g1 ) * ( $i / $line_numbers ) ): $g1;
              $b = ( $b2 - $b1 != 0 ) ? intval( $b1 + ( $b2 - $b1 ) * ( $i / $line_numbers ) ): $b1;
              // if new values are really new ones, allocate a new color, otherwise reuse previous color.
              // There's a "feature" in imagecolorallocate that makes this function
              // always returns '-1' after 255 colors have been allocated in an image that was created with
              // imagecreate (everything works fine with imagecreatetruecolor)
              if ( "$old_r,$old_g,$old_b" != "$r,$g,$b") 
                  $fill = imagecolorallocate( $im, $r, $g, $b );
              switch($direction) {
                  case 'vertical':
                      imagefilledrectangle($im, 0, $i, $line_width, $i+$this->step, $fill);
                      break;
                  case 'horizontal':
                      imagefilledrectangle( $im, $i, 0, $i+$this->step, $line_width, $fill );
                      break;
                  case 'ellipse':
                  case 'ellipse2':
                  case 'circle':
                  case 'circle2':
                      imagefilledellipse ($im,$center_x, $center_y, ($line_numbers-$i)*$rh, ($line_numbers-$i)*$rw,$fill);
                      break;
                  case 'square':
                  case 'rectangle':
                      imagefilledrectangle ($im,$i*$width/$height,$i*$height/$width,$width-($i*$width/$height), $height-($i*$height/$width),$fill);
                      break;
                  case 'diamond':
                      imagefilledpolygon($im, array (
                          $width/2, $i*$rw-0.5*$height,
                          $i*$rh-0.5*$width, $height/2,
                          $width/2,1.5*$height-$i*$rw,
                          1.5*$width-$i*$rh, $height/2 ), 4, $fill);
                      break;
                  default:    
              }        
          }
      }
      
      // #ff00ff -> array(255,0,255) or #f0f -> array(255,0,255)
      function hex2rgb($color) {
          $color = str_replace('#','',$color);
          $s = strlen($color) / 3;
          $rgb[]=hexdec(str_repeat(substr($color,0,$s),2/$s));
          $rgb[]=hexdec(str_repeat(substr($color,$s,$s),2/$s));
          $rgb[]=hexdec(str_repeat(substr($color,2*$s,$s),2/$s));
          return $rgb;
      }
    
    /* Param: ff0000 */
    function rgb2array($rgb) {
      return array(
          base_convert(substr($rgb, 0, 2), 16, 10),
          base_convert(substr($rgb, 2, 2), 16, 10),
          base_convert(substr($rgb, 4, 2), 16, 10),
      );
    }
  
    function imageSmoothArcDrawSegment (&$img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $start, $stop, $seg)
    {
        // Originally written from scratch by Ulrich Mierendorff, 06/2006
        // Rewritten and improved, 04/2007, 07/2007
        
        // Please do not use THIS function directly. Scroll down to imageSmoothArc(...).
        
        $fillColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], $color[3] );
        
        $xStart = abs($a * cos($start));
        $yStart = abs($b * sin($start));
        $xStop  = abs($a * cos($stop));
        $yStop  = abs($b * sin($stop));
        $dxStart = 0;
        $dyStart = 0;
        $dxStop = 0;
        $dyStop = 0;
        if ($xStart != 0)
            $dyStart = $yStart/$xStart;
        if ($xStop != 0)
            $dyStop = $yStop/$xStop;
        if ($yStart != 0)
            $dxStart = $xStart/$yStart;
        if ($yStop != 0)
            $dxStop = $xStop/$yStop;
        if (abs($xStart) >= abs($yStart)) {
            $aaStartX = true;
        } else {
            $aaStartX = false;
        }
        if ($xStop >= $yStop) {
            $aaStopX = true;
        } else {
            $aaStopX = false;
        }
        //$xp = +1; $yp = -1; $xa = +1; $ya = 0;
        for ( $x = 0; $x < $a; $x += 1 ) {
            /*$y = $b * sqrt( 1 - ($x*$x)/($a*$a) );
            
            $error = $y - (int)($y);
            $y = (int)($y);
            
            $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );*/
            
            $_y1 = $dyStop*$x;
            $_y2 = $dyStart*$x;
            if ($xStart > $xStop)
            {
                $error1 = $_y1 - (int)($_y1);
                $error2 = 1 - $_y2 + (int)$_y2;
                $_y1 = $_y1-$error1;
                $_y2 = $_y2+$error2;
            }
            else
            {
                $error1 = 1 - $_y1 + (int)$_y1;
                $error2 = $_y2 - (int)($_y2);
                $_y1 = $_y1+$error1;
                $_y2 = $_y2-$error2;
            }
            /*
            if ($aaStopX)
                $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
            if ($aaStartX)
                $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
            */
            
            if ($seg == 0 || $seg == 2)
            {
                $i = $seg;
                if (!($start > $i*M_PI/2 && $x > $xStart)) {
                    if ($i == 0) {
                        $xp = +1; $yp = -1; $xa = +1; $ya = 0;
                    } else {
                        $xp = -1; $yp = +1; $xa = 0; $ya = +1;
                    }
                    if ( $stop < ($i+1)*(M_PI/2) && $x <= $xStop ) {
                        $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                        $y1 = $_y1; if ($aaStopX) imageSetPixel($img, $cx+$xp*($x)+$xa, $cy+$yp*($y1+1)+$ya, $diffColor1);
                        
                    } else {
                        $y = $b * sqrt( 1 - ($x*$x)/($a*$a) );
                        $error = $y - (int)($y);
                        $y = (int)($y);
                        $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                        $y1 = $y; if ($x < $aaAngleX ) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y1+1)+$ya, $diffColor);
                    }
                    if ($start > $i*M_PI/2 && $x <= $xStart) {
                        $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                        $y2 = $_y2; if ($aaStartX) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y2-1)+$ya, $diffColor2);
                    } else {
                        $y2 = 0;
                    }
                    if ($y2 <= $y1) imageLine($img, $cx+$xp*$x+$xa, $cy+$yp*$y1+$ya , $cx+$xp*$x+$xa, $cy+$yp*$y2+$ya, $fillColor);
                }
            }
            
            if ($seg == 1 || $seg == 3)
            {
                $i = $seg;
                if (!($stop < ($i+1)*M_PI/2 && $x > $xStop)) {
                    if ($i == 1) {
                        $xp = -1; $yp = -1; $xa = 0; $ya = 0;
                    } else {
                        $xp = +1; $yp = +1; $xa = 1; $ya = 1;
                    }
                    if ( $start > $i*M_PI/2 && $x < $xStart ) {
                        $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                        $y1 = $_y2; if ($aaStartX) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y1+1)+$ya, $diffColor2);
                        
                    } else {
                        $y = $b * sqrt( 1 - ($x*$x)/($a*$a) );
                        $error = $y - (int)($y);
                        $y = (int) $y;
                        $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                        $y1 = $y; if ($x < $aaAngleX ) imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y1+1)+$ya, $diffColor);
                    }
                    if ($stop < ($i+1)*M_PI/2 && $x <= $xStop) {
                        $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                        $y2 = $_y1; if ($aaStopX)  imageSetPixel($img, $cx+$xp*$x+$xa, $cy+$yp*($y2-1)+$ya, $diffColor1);
                    } else {
                        $y2 = 0;
                    }
                    if ($y2 <= $y1) imageLine($img, $cx+$xp*$x+$xa, $cy+$yp*$y1+$ya, $cx+$xp*$x+$xa, $cy+$yp*$y2+$ya, $fillColor);
                }
            }
        }
        
        ///YYYYY
        
        for ( $y = 0; $y < $b; $y += 1 ) {
            /*$x = $a * sqrt( 1 - ($y*$y)/($b*$b) );
            
            $error = $x - (int)($x);
            $x = (int)($x);
            
            $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
            */
            $_x1 = $dxStop*$y;
            $_x2 = $dxStart*$y;
            if ($yStart > $yStop)
            {
                $error1 = $_x1 - (int)($_x1);
                $error2 = 1 - $_x2 + (int)$_x2;
                $_x1 = $_x1-$error1;
                $_x2 = $_x2+$error2;
            }
            else
            {
                $error1 = 1 - $_x1 + (int)$_x1;
                $error2 = $_x2 - (int)($_x2);
                $_x1 = $_x1+$error1;
                $_x2 = $_x2-$error2;
            }
    /*
            if (!$aaStopX)
                $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
            if (!$aaStartX)
                $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
    */
            
            if ($seg == 0 || $seg == 2)
            {
                $i = $seg;
                if (!($start > $i*M_PI/2 && $y > $yStop)) {
                    if ($i == 0) {
                        $xp = +1; $yp = -1; $xa = 1; $ya = 0;
                    } else {
                        $xp = -1; $yp = +1; $xa = 0; $ya = 1;
                    }
                    if ( $stop < ($i+1)*(M_PI/2) && $y <= $yStop ) {
                        $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                        $x1 = $_x1; if (!$aaStopX) imageSetPixel($img, $cx+$xp*($x1-1)+$xa, $cy+$yp*($y)+$ya, $diffColor1);
                    } 
                    if ($start > $i*M_PI/2 && $y < $yStart) {
                        $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                        $x2 = $_x2; if (!$aaStartX) imageSetPixel($img, $cx+$xp*($x2+1)+$xa, $cy+$yp*($y)+$ya, $diffColor2);
                    } else {
                        $x = $a * sqrt( 1 - ($y*$y)/($b*$b) );
                        $error = $x - (int)($x);
                        $x = (int)($x);
                        $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                        $x1 = $x; if ($y < $aaAngleY && $y <= $yStop ) imageSetPixel($img, $cx+$xp*($x1+1)+$xa, $cy+$yp*$y+$ya, $diffColor);
                    }
                }
            }
            
            if ($seg == 1 || $seg == 3)
            {
                $i = $seg;
                if (!($stop < ($i+1)*M_PI/2 && $y > $yStart)) {
                    if ($i == 1) {
                        $xp = -1; $yp = -1; $xa = 0; $ya = 0;
                    } else {
                        $xp = +1; $yp = +1; $xa = 1; $ya = 1;
                    }
                    if ( $start > $i*M_PI/2 && $y < $yStart ) {
                        $diffColor2 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error2 );
                        $x1 = $_x2; if (!$aaStartX) imageSetPixel($img, $cx+$xp*($x1-1)+$xa, $cy+$yp*$y+$ya,  $diffColor2);
                    } 
                    if ($stop < ($i+1)*M_PI/2 && $y <= $yStop) {
                        $diffColor1 = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error1 );
                        $x2 = $_x1; if (!$aaStopX)  imageSetPixel($img, $cx+$xp*($x2+1)+$xa, $cy+$yp*$y+$ya, $diffColor1);
                    } else {
                        $x = $a * sqrt( 1 - ($y*$y)/($b*$b) );
                        $error = $x - (int)($x);
                        $x = (int)($x);
                        $diffColor = imageColorExactAlpha( $img, $color[0], $color[1], $color[2], 127-(127-$color[3])*$error );
                        $x1 = $x; if ($y < $aaAngleY  && $y < $yStart) imageSetPixel($img,$cx+$xp*($x1+1)+$xa,  $cy+$yp*$y+$ya, $diffColor);
                    }
                }
            }
        }
    }
    
    
    function imageSmoothArc ( &$img, $cx, $cy, $w, $h, $color, $start, $stop)
    {
        // Originally written from scratch by Ulrich Mierendorff, 06/2006
        // Rewritten and improved, 04/2007, 07/2007
        // compared to old version:
        // + Support for transparency added
        // + Improved quality of edges & antialiasing
        
        // note: This function does not represent the fastest way to draw elliptical
        // arcs. It was written without reading any papers on that subject. Better
        // algorithms may be twice as fast or even more.
        
        // what it cannot do: It does not support outlined arcs, only filled
        
        // Parameters:
        // $cx      - Center of ellipse, X-coord
        // $cy      - Center of ellipse, Y-coord
        // $w       - Width of ellipse ($w >= 2)
        // $h       - Height of ellipse ($h >= 2 )
        // $color   - Color of ellipse as a four component array with RGBA
        // $start   - Starting angle of the arc, no limited range!
        // $stop    - Stop     angle of the arc, no limited range!
        // $start _can_ be greater than $stop!
        // If any value is not in the given range, results are undefined!
        
        // This script does not use any special algorithms, everything is completely
        // written from scratch; see http://de.wikipedia.org/wiki/Ellipse for formulas.
        
        while ($start < 0)
            $start += 2*M_PI;
        while ($stop < 0)
            $stop += 2*M_PI;
        
        while ($start > 2*M_PI)
            $start -= 2*M_PI;
        
        while ($stop > 2*M_PI)
            $stop -= 2*M_PI;
        
        
        if ($start > $stop)
        {
            $this->imageSmoothArc ( $img, $cx, $cy, $w, $h, $color, $start, 2*M_PI);
            $this->imageSmoothArc ( $img, $cx, $cy, $w, $h, $color, 0, $stop);
            return;
        }
        
        $a = 1.0*round ($w/2);
        $b = 1.0*round ($h/2);
        $cx = 1.0*round ($cx);
        $cy = 1.0*round ($cy);
        
        $aaAngle = atan(($b*$b)/($a*$a)*tan(0.25*M_PI));
        $aaAngleX = $a*cos($aaAngle);
        $aaAngleY = $b*sin($aaAngle);
        
        $a -= 0.5; // looks better...
        $b -= 0.5;
        
        for ($i=0; $i<4;$i++)
        {
            if ($start < ($i+1)*M_PI/2)
            {
                if ($start > $i*M_PI/2)
                {
                    if ($stop > ($i+1)*M_PI/2)
                    {
                        $this->imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY , $color, $start, ($i+1)*M_PI/2, $i);
                    }
                    else
                    {
                        $this->imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $start, $stop, $i);
                        break;
                    }
                }
                else
                {
                    if ($stop > ($i+1)*M_PI/2)
                    {
                        $this->imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $i*M_PI/2, ($i+1)*M_PI/2, $i);
                    }
                    else
                    {
                        $this->imageSmoothArcDrawSegment($img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $color, $i*M_PI/2, $stop, $i);
                        break;
                    }
                }
            }
        }
    }
  }
}
?>