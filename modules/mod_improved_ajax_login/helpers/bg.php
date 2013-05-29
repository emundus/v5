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
if(!class_exists('OfflajnBgHelper')){
  class OfflajnBgHelper {
    
    var $step = "";
    
    var $cache;
    
    var $cacheUrl;
    
    function OfflajnBgHelper($cacheDir, $cacheUrl) {
      $this->cache = $cacheDir;
      $this->cacheUrl = $cacheUrl;
    }
    
    function generateBackground($color, $repeat = "", $pos = "", $isrgba = 0) {
      $css = "";
      
      if(strlen($color) == 6) {
        $css = "background: #".$color.";";
      } else if(strlen($color) == 8) {
      $rgba = $this->hex2rgba($color);
      $bgimg = $this->generateAlphaColor($rgba, $color);
      $alpha = round($rgba[3]/127, 2);
        $css = "background: url('".$bgimg."') ".$repeat." ".$pos.";";
        if($isrgba) $css .= "background: rgba(".$rgba[0].", ".$rgba[1].", ".$rgba[2].", ".$alpha.");";
      }
      return $css;
    }
    
    function generateGradientBackground($color, $width = 1, $height = 1, $direction = "vertical") {
      $css = "";
      $c = explode("-", $color);
      $bgimg = $this->generateGradient($width, $height, $c[1], $c[2], $direction);
      $wk = "";
      $ff = "";
      $r = "";
      if($direction == "vertical") {
        $wk = array("left top", " left bottom");
        $ff = "top";  
        $r = "repeat-x";
      } else if($direction == "horizontal") {
        $wk = array("left top", "right top");
        $ff = "left";
        $r = "repeat-y";
      }
      $css = "background: #".$c[2]." url('".$bgimg."') ".$r.";";
      $css .= "background: -moz-linear-gradient(".$ff.",  #".$c[1].",  #".$c[2].");"; 
      $css .= "background: -webkit-gradient(linear, ".$wk[0].", ".$wk[1].", from(#".$c[1]."), to(#".$c[2]."));";
      $css .= "background: -webkit-linear-gradient(".$ff.", #".$c[1].",#".$c[2].");"; // Chrome10+,Safari5.1+
      $css .= "background: -o-linear-gradient(".$ff.", #".$c[1].",#".$c[2].");"; // Opera 11.10+
      $css .= "background: -ms-linear-gradient(".$ff.", #".$c[1].",#".$c[2].");"; // IE10+
      $css .= "background: linear-gradient(".$ff.", #".$c[1].",#".$c[2].");";  // W3C 
/*      $css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#".$c[1]."', endColorstr='#".$c[2]."');"; */     
  
      return $css;
    }
    
    function generateAlphaColor($color, $c){
      $im = imagecreatetruecolor(1, 1);
      imagealphablending($im, false);
      imagesavealpha($im, true);  
      $transparent = imagecolorallocatealpha($im, 255, 255, 255, 127);
    	imagefilledrectangle($im, 0, 0, 1, 1, $transparent);
      $fillcolor = imagecolorallocatealpha($im, $color[0], $color[1], $color[2], 127-$color[3]);
    	imagefilledrectangle($im, 0, 0, 1, 1, $fillcolor);
      
      $hash = md5($c).'.png';
      imagepng($im, $this->cache.DS.$hash);
      imagedestroy($im);
      return $this->cacheUrl.$hash;
    }
    
    function hex2rgb($color) {
      $color = str_replace('#','',$color);
      $s = strlen($color) / 3;
      $rgb[]=hexdec(str_repeat(substr($color,0,$s),2/$s));
      $rgb[]=hexdec(str_repeat(substr($color,$s,$s),2/$s));
      $rgb[]=hexdec(str_repeat(substr($color,2*$s,$s),2/$s));
      return $rgb;
    }
    
    function hex2rgba($hex) {
      // Remove #.
      if (strpos($hex, '#') === 0) {
          $hex = substr($hex, 1);
      }
  
      if (strlen($hex) == 6) {
          $hex.='ff';
      }
  
      if (strlen($hex) != 8) {
          return false;
      }
  
      // Convert each tuple to decimal.
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      $a = intval(hexdec(substr($hex, 6, 2))/2);
  
      return array($r, $g, $b, $a);
    }
    
    function generateGradient($w, $h, $c1, $c2, $direction) {
      $im = imagecreatetruecolor($w, $h);
      $this->fill($im, $direction, $c1, $c2);
      $hash = md5($w.$h.$c1.$c2.$direction).'.png';
      imagepng($im, $this->cache.DS.$hash);
      imagedestroy($im);
      return $this->cacheUrl.$hash;
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
  }
}
?>