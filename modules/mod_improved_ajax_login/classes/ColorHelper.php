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
if(!class_exists('OfflajnColor')){
  class OfflajnColor
  {
      /**
       *
       * Converts hexadecimal colors to RGB.
       *
       * @param string $hex Hexadecimal value. Accepts values with 3 or 6 numbers,
       * with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
       *
       * @return array RGB values: 0 => R, 1 => G, 2 => B
       *
       */
      public function hex2rgb($hex)
      {
          // Remove #.
          if (strpos($hex, '#') === 0) {
              $hex = substr($hex, 1);
          }
  
          if (strlen($hex) == 3) {
              $hex .= $hex;
          }
  
          if (strlen($hex) != 6) {
              return false;
          }
  
          // Convert each tuple to decimal.
          $r = hexdec(substr($hex, 0, 2));
          $g = hexdec(substr($hex, 2, 2));
          $b = hexdec(substr($hex, 4, 2));
  
          return array($r, $g, $b);
      }
      
      public function hex2rgba($hex)
      {
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
      
      public function hex82hex($hex)
      {
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
          return array(substr($hex, 0, 6), substr($hex, 6, 2));
      }
  
      /**
       *
       * Converts hexadecimal colors to HSV.
       *
       * @param string $hex Hexadecimal value. Accepts values with 3 or 6 numbers,
       * with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
       *
       * @return array HSV values: 0 => H, 1 => S, 2 => V
       *
       */
      public function hex2hsv($hex)
      {
          return $this->rgb2hsv($this->hex2rgb($hex));
      }
  
      /**
       *
       * Converts hexadecimal colors to HSL.
       *
       * @param string $hex Hexadecimal value. Accepts values with 3 or 6 numbers,
       * with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
       *
       * @return array HSL values: 0 => H, 1 => S, 2 => L
       *
       */
      public function hex2hsl($hex)
      {
          return $this->rgb2hsl($this->hex2rgb($hex));
      }
  
      /**
       *
       * Converts RGB colors to hexadecimal.
       *
       * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
       *
       * @return string Hexadecimal value with six digits, e.g., CCCCCC.
       *
       */
      public function rgb2hex($rgb)
      {
          if(count($rgb) < 3) {
              return false;
          }
  
          list($r, $g, $b) = $rgb;
  
          // From php.net.
          $r = 0x10000 * max(0, min(255, $r));
          $g = 0x100 * max(0, min(255, $g));
          $b = max(0, min(255, $b));
  
          return strtoupper(str_pad(dechex($r + $g + $b), 6, 0, STR_PAD_LEFT));
      }
  
      /**
       *
       * Converts RGB to HSV.
       *
       * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
       *
       * @return array HSV values: 0 => H, 1 => S, 2 => V
       *
       */
      public function rgb2hsv($rgb)
      {
          // RGB values = 0 Ăˇ 255
          $var_R = ($rgb[0] / 255);
          $var_G = ($rgb[1] / 255);
          $var_B = ($rgb[2] / 255);
  
          // Min. value of RGB
          $var_Min = min($var_R, $var_G, $var_B);
  
          // Max. value of RGB
          $var_Max = max($var_R, $var_G, $var_B);
  
          // Delta RGB value
          $del_Max = $var_Max - $var_Min;
  
          $V = $var_Max;
  
          // This is a gray, no chroma...
          if ( $del_Max == 0 ) {
             // HSV results = 0 Ăˇ 1
             $H = 0;
             $S = 0;
          } else {
             // Chromatic data...
             $S = $del_Max / $var_Max;
  
             $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
             $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
             $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;
  
             if ($var_R == $var_Max) {
                 $H = $del_B - $del_G;
             } else if ($var_G == $var_Max) {
                 $H = (1 / 3) + $del_R - $del_B;
             } else if ($var_B == $var_Max) {
                 $H = (2 / 3) + $del_G - $del_R;
             }
  
             if ($H < 0) {
                 $H += 1;
             }
             if ($H > 1) {
                 $H -= 1;
             }
          }
  
          // Returns agnostic values.
          // Range will depend on the application: e.g. $H*360, $S*100, $V*100.
          return array($H, $S, $V);
      }
  
      /**
       *
       * Converts RGB to HSL.
       *
       * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
       *
       * @return array HSL values: 0 => H, 1 => S, 2 => L
       *
       */
      public function rgb2hsl($rgb)
      {
          // Where RGB values = 0 Ăˇ 255.
          $var_R = $rgb[0] / 255;
          $var_G = $rgb[1] / 255;
          $var_B = $rgb[2] / 255;
  
          // Min. value of RGB
          $var_Min = min($var_R, $var_G, $var_B);
          // Max. value of RGB
          $var_Max = max($var_R, $var_G, $var_B);
          // Delta RGB value
          $del_Max = $var_Max - $var_Min;
  
          $L = ($var_Max + $var_Min) / 2;
  
          if ( $del_Max == 0 ) {
              // This is a gray, no chroma...
              // HSL results = 0 Ăˇ 1
              $H = 0;
              $S = 0;
          } else {
              // Chromatic data...
              if ($L < 0.5) {
                  $S = $del_Max / ($var_Max + $var_Min);
              } else {
                  $S = $del_Max / ( 2 - $var_Max - $var_Min );
              }
  
              $del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
              $del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
              $del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;
  
              if ($var_R == $var_Max) {
                  $H = $del_B - $del_G;
              } else if ($var_G == $var_Max) {
                  $H = ( 1 / 3 ) + $del_R - $del_B;
              } else if ($var_B == $var_Max) {
                  $H = ( 2 / 3 ) + $del_G - $del_R;
              }
  
              if ($H < 0) {
                  $H += 1;
              }
              if ($H > 1) {
                  $H -= 1;
              }
          }
  
          return array($H, $S, $L);
      }
  
      /**
       *
       * Converts HSV colors to hexadecimal.
       *
       * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
       *
       * @return string Hexadecimal value with six digits, e.g., CCCCCC.
       *
       */
      public function hsv2hex($hsv)
      {
          return $this->rgb2hex($this->hsv2rgb($hsv));
      }
  
      /**
       *
       * Converts HSV to RGB.
       *
       * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
       *
       * @return array RGB values: 0 => R, 1 => G, 2 => B
       *
       */
      public function hsv2rgb($hsv)
      {
          $H = $hsv[0];
          $S = $hsv[1];
          $V = $hsv[2];
  
          // HSV values = 0 Ăˇ 1
          if ($S == 0) {
              $R = $V * 255;
              $G = $V * 255;
              $B = $V * 255;
          } else {
              $var_h = $H * 6;
              // H must be < 1
              if ( $var_h == 6 ) {
                  $var_h = 0;
              }
              // Or ... $var_i = floor( $var_h )
              $var_i = floor( $var_h );
              $var_1 = $V * ( 1 - $S );
              $var_2 = $V * ( 1 - $S * ( $var_h - $var_i ) );
              $var_3 = $V * ( 1 - $S * ( 1 - ( $var_h - $var_i ) ) );
  
              switch($var_i) {
                  case 0:
                      $var_r = $V;
                      $var_g = $var_3;
                      $var_b = $var_1;
                      break;
                  case 1:
                      $var_r = $var_2;
                      $var_g = $V;
                      $var_b = $var_1;
                      break;
                  case 2:
                      $var_r = $var_1;
                      $var_g = $V;
                      $var_b = $var_3;
                      break;
                  case 3:
                      $var_r = $var_1;
                      $var_g = $var_2;
                      $var_b = $V;
                      break;
                  case 4:
                      $var_r = $var_3;
                      $var_g = $var_1;
                      $var_b = $V;
                      break;
                  default:
                      $var_r = $V;
                      $var_g = $var_1;
                      $var_b = $var_2;
              }
  
              //RGB results = 0 Ăˇ 255
              $R = $var_r * 255;
              $G = $var_g * 255;
              $B = $var_b * 255;
          }
  
          return array($R, $G, $B);
      }
  
      /**
       *
       * Converts HSV colors to HSL.
       *
       * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
       *
       * @return array HSL values: 0 => H, 1 => S, 2 => L
       *
       */
      public function hsv2hsl($hsv)
      {
          return $this->rgb2hsl($this->hsv2rgb($hsv));
      }
  
      /**
       *
       * Converts hexadecimal colors to HSL.
       *
       * @param array $hsl HSL values: 0 => H, 1 => S, 2 => L
       *
       * @return string Hexadecimal value. Accepts values with 3 or 6 numbers,
       * with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
       *
       */
      public function hsl2hex($hsl)
      {
          return $this->rgb2hex($this->hsl2rgb($hsl));
      }
  
      /**
       *
       * Converts HSL to RGB.
       *
       * @param array $hsv HSL values: 0 => H, 1 => S, 2 => L
       *
       * @return array RGB values: 0 => R, 1 => G, 2 => B
       *
       */
      public function hsl2rgb($hsl)
      {
          list($H, $S, $L) = $hsl;
  
          if ($S == 0) {
              // HSL values = 0 Ăˇ 1
              // RGB results = 0 Ăˇ 255
              $R = $L * 255;
              $G = $L * 255;
              $B = $L * 255;
          } else {
              if ($L < 0.5) {
                  $var_2 = $L * (1 + $S);
              } else {
                  $var_2 = ($L + $S) - ($S * $L);
              }
  
              $var_1 = 2 * $L - $var_2;
  
              $R = 255 * $this->_hue2rgb($var_1, $var_2, $H + (1 / 3));
              $G = 255 * $this->_hue2rgb($var_1, $var_2, $H);
              $B = 255 * $this->_hue2rgb($var_1, $var_2, $H - (1 / 3));
          }
  
          return array($R, $G, $B);
      }
  
      /**
       *
       * Support method for hsl2rgb(): converts hue ro RGB.
       *
       * @param
       *
       * @param
       *
       * @param
       *
       * @return int
       *
       */
      protected function _hue2rgb($v1, $v2, $vH)
      {
          if ($vH < 0) {
              $vH += 1;
          }
  
          if ($vH > 1) {
              $vH -= 1;
          }
  
          if ((6 * $vH) < 1) {
              return ($v1 + ($v2 - $v1) * 6 * $vH);
          }
  
          if ((2 * $vH) < 1) {
              return $v2;
          }
  
          if ((3 * $vH) < 2) {
              return ($v1 + ($v2 - $v1) * (( 2 / 3) - $vH) * 6);
          }
  
          return $v1;
      }
  
      /**
       *
       * Converts hexadecimal colors to HSL.
       *
       * @param array $hsl HSL values: 0 => H, 1 => S, 2 => L
       *
       * @return array HSV values: 0 => H, 1 => S, 2 => V
       *
       */
      public function hsl2hsv($hsl)
      {
          return $this->rgb2hsv($this->hsl2rgb($hsl));
      }
  
      /**
       *
       * Updates HSV values.
       *
       * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
       *
       * @param array $values Values to update: 0 => value to add to H (0 to 360),
       * 1 and 2 => values to multiply S and V (0 to 100). Example:
       *
       * {{{code:php
       *     // Update saturation to 80% in the provided HSV.
       *     $hsv = array(120, 0.75, 0.75);
       *     $new_hsv = $color->updateHsv($hsv, array(null, 80, null));
       * }}}
       *
       */
      public function updateHsv($hsv, $values)
      {
          if (isset($values[0])) {
              $hsv[0] = max(0, min(360, ($hsv[0] + $values[0])));
          }
  
          if (isset($values[1])) {
              $hsv[1] = max(0, min(1, ($hsv[1] * ($values[1] / 100))));
          }
  
          if (isset($values[2])) {
              $hsv[2] = max(0, min(1, ($hsv[2] * ($values[2] / 100))));
          }
  
          return $hsv;
      }
  
      /**
       *
       * Updates HSL values.
       *
       * @param array $hsl HSL values: 0 => H, 1 => S, 2 => L
       *
       * @param array $values Values to update: 0 => value to add to H (0 to 360),
       * 1 and 2 => values to multiply S and V (0 to 100). Example:
       *
       * {{{code:php
       *     // Update saturation to 80% in the provided HSL.
       *     $hsl = array(120, 0.75, 0.75);
       *     $new_hsl = $color->updateHsl($hsl, array(null, 80, null));
       * }}}
       *
       */
      public function updateHsl($hsl, $values)
      {
          if (isset($values[0])) {
              $hsl[0] = max(0, min(1, ($hsl[0] + $values[0]/360)));
          }
  
          if (isset($values[1])) {
              $hsl[1] = max(0, min(1, ($hsl[1] * ($values[1] / 100))));
          }
  
          if (isset($values[2])) {
              $hsl[2] = max(0, min(1, ($hsl[2] * ($values[2] / 100))));
          }
  
          return $hsl;
      }
  }
}
?>