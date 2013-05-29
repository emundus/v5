<?php
/*-------------------------------------------------------------------------
# mod_improved_ajax_login - Improved AJAX Login and Register
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php defined('_JEXEC') or die('Restricted access'); ?>
#strongFields {
  display: block;
  overflow: hidden;
  height: 7px;
  margin: 3px 0 2px;
	border-radius: <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+0?>px;
  background-color: #<?php echo $btngrad[1]?>;
	background-image: url(data:image/svg+xml;base64,<?php echo base64_encode("<svg xmlns='http://www.w3.org/2000/svg'><linearGradient id='g' x2='100%' y2='0'><stop stop-color='#{$btngrad[1]}'/><stop offset='100%' stop-color='#{$hovergrad[2]}'/></linearGradient><rect width='100%' height='100%' fill='url(#g)'/></svg>")?>);
	background-image: -moz-linear-gradient(left, #<?php echo $btngrad[1]?>, #<?php echo $hovergrad[2]?>);
  background-image: -o-linear-gradient(left, #<?php echo $btngrad[1]?>, #<?php echo $hovergrad[2]?>);
  background-image: -ms-linear-gradient(left, #<?php echo $btngrad[1]?>, #<?php echo $hovergrad[2]?>);
	background-image: -webkit-gradient(linear, left top, right top, from(#<?php echo $btngrad[1]?>), to(#<?php echo $hovergrad[2]?>));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo $btngrad[1]?>, endColorstr=#<?php echo $hovergrad[2]?>, GradientType=1);
}
#strongFields .strongField.empty {
  background-color: #<?php echo $txtcomb[0]?>;
  -webkit-transition: background-color 1.2s ease-out;
	-moz-transition: background-color 1.2s ease-out;
  -ms-transition: background-color 1.2s ease-out;
  -o-transition: background-color 1.2s ease-out;
	transition: background-color 1.2s ease-out;
}
.strongField.empty,
.strongField {
  display: block;
  background-color: transparent;
  width: 20%;
  height: 7px;
  float: left;
  box-shadow:
    1px 1px 2px rgba(0, 0, 0, 0.4) inset,
    -2px 0 0 #<?php echo $popupcomb[0]?>;
  -webkit-box-shadow:
    1px 1px 2px rgba(0, 0, 0, 0.4) inset,
    -2px 0 0 #<?php echo $popupcomb[0]?>;
}
.loginWndInside {
  position: relative;
  display: inline-block;
  padding: 18px 20px 8px;
  text-align: center;
  border: 1px #<?php shift_color($popupcomb[2], -39)?> solid;
  background-color: #<?php echo $popupcomb[0]?>;
	border-radius: <?php echo $buttoncomb[2]+1?>px;
	-moz-border-radius: <?php echo $buttoncomb[2]+1?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+1?>px;
  box-shadow:
		0px 0px <?php echo $popupcomb[1]-1?>px rgba(0,0,0,0.4);
	-moz-box-shadow:
		0px 0px <?php echo $popupcomb[1]-1?>px rgba(0,0,0,0.4);
	-webkit-box-shadow:
		0px 0px <?php echo $popupcomb[1]-1?>px rgba(0,0,0,0.4);
}
.loginH1 {
  <?php $fonts->printFont('titlefont', 'Text');?>
  padding-bottom: 6px;
  margin: 0 0 9px 0;
  position: relative;
  border-bottom: 1px #<?php echo $popupcomb[2]?> solid;
  box-shadow: 0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
	-moz-box-shadow: 0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
	-webkit-box-shadow: 0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
}
.socialBody {
	background-color: #<?php echo $popupcomb[2]?>;
}
.captchaCnt {
  display: block;
  *width: 215px;
  border: none;
  clear: both;
  padding: 4px 2px 2px 4px;
  overflow: hidden;
  position: relative;
  border-radius: <?php echo $buttoncomb[2]+0?>px;
  -webkit-border-radius: <?php echo $buttoncomb[2]+0?>px;
  margin: 0 0 6px;
  background: #fff;
  box-shadow:
    1px 1px 0 rgba(255, 255, 255, 0.8),
    1px 1px 3px rgba(0, 0, 0, 0.3) inset;
  -webkit-box-shadow:
    1px 1px 0 rgba(255, 255, 255, 0.8),
    1px 1px 3px rgba(0, 0, 0, 0.3) inset;
}
.dj_ie7 .captchaCnt,
.dj_ie8 .captchaCnt {
  border: 1px #<?php echo $popupcomb[2]?> solid\9;
}
#regRequired .red,
.loginMsg .red {
  display: none;
}
#recaptchaImg {
  display: block;
  width: 100%;
  min-height: 57px;
  max-width: 300px;
  margin: 0 auto;
  opacity: 0;
  transition: opacity .33s ease-in-out;
  -o-transition: opacity .33s ease-in-out;
  -ms-transition: opacity .33s ease-in-out;
  -moz-transition: opacity .33s ease-in-out;
  -webkit-transition: opacity .33s ease-in-out;
}
#recaptchaImg.fadeIn {
  min-height: 0;
	opacity: 1;
}
a#loginBtn.selectBtn:hover {
  background-color: transparent;
}
.selectBtn {
  margin: 1px;
  white-space: nowrap;
}
.selectBtn:hover,
.loginBtn:hover {
  *text-decoration: none;
}
.btnIco {
  display: block;
  float: left;
  background: transparent no-repeat 1px center;
  width: 20px;
  border-right: 1px #<?php echo $buttoncomb[1]?> solid;
  box-shadow: 1px 0 0 rgba(255, 255, 255, 0.5);
  -webkit-box-shadow: 1px 0 0 rgba(255, 255, 255, 0.5);
}
.socialIco {
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 18px;
  -webkit-border-radius: 18px;
  background: #<?php echo $popupcomb[2]?>;
  display: inline-block;
  *display: block;
  *float: left;
  margin: 0 9px;
  text-align: left;
}
.socialIco:first-child {
  margin-left: 0;
}
.socialIco:last-child {
  margin-right: 0;
}
.socialImg {
  margin: 4px;
  width: 28px;
  height: 28px;
  border-radius: 14px;
  -webkit-border-radius: 14px;
  box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3), inset 1px 1px 1px #fff;
  -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3), inset 0 1px 1px #fff;
}
.loginBtn,
.socialIco,
.socialImg {
  -webkit-transition: all .3s ease-out;
	-moz-transition: all .3s ease-out;
  -ms-transition: all .3s ease-out;
  -o-transition: all .3s ease-out;
	transition: all .3s ease-out;
}
.socialIco:hover .socialImg {
  border-radius: 7px;
  -webkit-border-radius: 7px;
}
.socialIco:hover {
  background-color: #<?php echo $textfont['Hover']['color'] ?>;
}
.facebookImg {
  background: transparent url(<?php echo $themeurl?>images/fb.png);
}
.googleImg {
  background: transparent url(<?php echo $themeurl?>images/google.png);
}
.twitterImg {
  background: transparent url(<?php echo $themeurl?>images/twitter.png);
}
.windowsImg {
  background: transparent url(<?php echo $themeurl?>images/wl.png);
}
.loginBrd {
  clear: both;
  *text-align: center;
  position: relative;
  margin: 13px 0;
  height: 0;
  padding: 0;
  border: 0;
}
.loginBrd {
  border-bottom: 1px #<?php echo $popupcomb[2]?> solid;
  box-shadow:
		0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
	-moz-box-shadow:
		0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
	-webkit-box-shadow:
		0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
}
.loginOr {
  display: none;
  position: absolute;
  width: 20px;
  height: 15px;
  left: 50%;
  text-align: center;
  margin: -7px 0 0 -13px;
  border: 3px solid #<?php echo $popupcomb[0]?>;
  border-top: 0;
  background: #<?php echo $popupcomb[0]?>;
}
#loginWnd .loginOr {
  display: block;
}

#loginWnd *::selection {
  background-color: #<?php echo $btngrad[2] ?>;
  color: #<?php echo $txtcomb[1] ?>;
}

#loginWnd *::-moz-selection {
  background-color: #<?php echo $btngrad[2] ?>;
  color: #<?php echo $txtcomb[1] ?>;
}

.arrowL,
.arrowR {
  display: block;
  position: absolute;
  top: <?php echo $btnfont['Text']['size']/4+$buttoncomb[0]?>px;
  width: 0;
  height: 0;
  border: 5px transparent solid;
  border-right-color: #<?php shift_color($errorgrad[1]<$errorgrad[2]? $errorgrad[1] : $errorgrad[2], -30)?>;
  border-left-width: 0;
}
.arrowL {
	left: -11px;
}
.arrowR {
  right: -6px;
  border-left-color: #<?php shift_color($errorgrad[1]<$errorgrad[2]? $errorgrad[1] : $errorgrad[2], -30)?>;
  border-width: 5px 0 5px 5px;
}
.Inf .arrowL {
  border-right-color: #<?php shift_color($hintgrad[1]<$hintgrad[2]? $hintgrad[1] : $hintgrad[2], -40)?>;
}
.Inf .arrowR {
  border-left-color: #<?php shift_color($hintgrad[1]<$hintgrad[2]? $hintgrad[1] : $hintgrad[2], -40)?>;
}
.loginMsg {
  display: none;
  z-index: 10000;
  position: absolute;
	border-radius: <?php echo $buttoncomb[2]+0?>px;
	-moz-border-radius: <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+0?>px;
	box-shadow:
		1px 1px 2px rgba(0,0,0,0.4),
		inset 1px 1px 0px rgba(255,255,255,0.3);
	-moz-box-shadow:
		1px 1px 2px rgba(0,0,0,0.4),
		inset 1px 1px 0px rgba(255,255,255,0.3);
	-webkit-box-shadow:
		1px 1px 2px rgba(0,0,0,0.4),
		inset 1px 1px 0px rgba(255,255,255,0.3);
}
.loginMsg.Inf {
  border: 1px solid #<?php shift_color($hintgrad[1]<$hintgrad[2]? $hintgrad[1] : $hintgrad[2], -40)?>;
  background-color: #<?php echo $hintgrad[1]?>;
	background-image: url(data:image/svg+xml;base64,<?php echo base64_encode("<svg xmlns='http://www.w3.org/2000/svg'><linearGradient id='g' x2='0' y2='100%'><stop stop-color='#{$hintgrad[1]}'/><stop offset='100%' stop-color='#{$hintgrad[2]}'/></linearGradient><rect width='100%' height='100%' fill='url(#g)'/></svg>")?>);
	background-image: -moz-linear-gradient(top, #<?php echo $hintgrad[1]?>, #<?php echo $hintgrad[2]?>);
  background-image: -o-linear-gradient(top, #<?php echo $hintgrad[1]?>, #<?php echo $hintgrad[2]?>);
  background-image: -ms-linear-gradient(top, #<?php echo $hintgrad[1]?>, #<?php echo $hintgrad[2]?>);
	background-image: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $hintgrad[1]?>), to(#<?php echo $hintgrad[2]?>));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo $hintgrad[1]?>, endColorstr=#<?php echo $hintgrad[2]?>);
}
.loginMsg.Err {
  border: 1px solid #<?php shift_color($errorgrad[1]<$errorgrad[2]? $errorgrad[1] : $errorgrad[2], -30)?>;
  background-color: #<?php echo $errorgrad[1]?>;
	background-image: url(data:image/svg+xml;base64,<?php echo base64_encode("<svg xmlns='http://www.w3.org/2000/svg'><linearGradient id='g' x2='0' y2='100%'><stop stop-color='#{$errorgrad[1]}'/><stop offset='100%' stop-color='#{$errorgrad[2]}'/></linearGradient><rect width='100%' height='100%' fill='url(#g)'/></svg>")?>);
	background-image: -moz-linear-gradient(top, #<?php echo $errorgrad[1]?>, #<?php echo $errorgrad[2]?>);
  background-image: -o-linear-gradient(top, #<?php echo $errorgrad[1]?>, #<?php echo $errorgrad[2]?>);
  background-image: -ms-linear-gradient(top, #<?php echo $errorgrad[1]?>, #<?php echo $errorgrad[2]?>);
	background-image: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $errorgrad[1]?>), to(#<?php echo $errorgrad[2]?>));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo $errorgrad[1]?>, endColorstr=#<?php echo $errorgrad[2]?>);
}
span.loginInf,
span.loginErr {
  position: relative;
  text-align: left;
  max-width: 360px;
  cursor: default;
  margin-left: 5px;
  padding: <?php echo $buttoncomb[0]+0?>px 8px <?php echo $buttoncomb[0]+0?>px 16px;
  text-decoration: none;
  color: #<?php echo $errorcolor?>;
	text-shadow: 1px 1px 0px rgba(0,0,0,0.7);
}
span.loginInf {
  color: #<?php echo $hintcolor?>;
  text-shadow: 1px 1px 0 rgba(255, 255, 255, 0.5);
}
div.iconErr,
div.iconInf {
  width: 15px;
  position: absolute;
  left: 0;
  background: url(<?php echo $themeurl?>images/info.png) no-repeat scroll left center transparent;
}
div.iconErr {
  background: url(<?php echo $themeurl?>images/error.png) no-repeat left center;
}
.loginInf,
.loginErr,
.loginBtn span,
.loginBtn {
  display: inline-block;
  <?php $fonts->printFont('btnfont', 'Text'); ?>
}
.facebookIco {
  background-image: url(<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/f.png", $btnfont['Text']['color'], "2e3192")?>);
}
.googleIco {
  background-image: url(<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/g.png", $btnfont['Text']['color'], "2e3192")?>);
}
.twitterIco {
  background-image: url(<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/t.png", $btnfont['Text']['color'], "2e3192")?>);
}
.windowsIco {
  background-image: url(<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/w.png", $btnfont['Text']['color'], "2e3192")?>);
}
.loginBtn::-moz-focus-inner {
  border:0;
  padding:0;
}
.loginBtn {
  display: inline-block;
  cursor: pointer;
  text-align: center;
	margin: 0;
	padding: <?php echo $buttoncomb[0]+0?>px;
	border: 1px solid #<?php echo $buttoncomb[1]?>;
	border-radius: <?php echo $buttoncomb[2]+0?>px;
	-moz-border-radius: <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+0?>px;
}
/*.socialIco:hover,*/
.loginBtn,
.loginBtn:hover:active,
.selectBtn:hover .leftBtn {
  background-color: #<?php echo $btngrad[1]?>;
	background-image: url(data:image/svg+xml;base64,<?php echo base64_encode("<svg xmlns='http://www.w3.org/2000/svg'><linearGradient id='g' x2='0' y2='100%'><stop stop-color='#{$btngrad[1]}'/><stop offset='100%' stop-color='#{$btngrad[2]}'/></linearGradient><rect width='100%' height='100%' fill='url(#g)'/></svg>")?>);
	background-image: -moz-linear-gradient(top, #<?php echo $btngrad[1]?>, #<?php echo $btngrad[2]?>);
  background-image: -o-linear-gradient(top, #<?php echo $btngrad[1]?>, #<?php echo $btngrad[2]?>);
  background-image: -ms-linear-gradient(top, #<?php echo $btngrad[1]?>, #<?php echo $btngrad[2]?>);
	background-image: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $btngrad[1]?>), to(#<?php echo $btngrad[2]?>));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo $btngrad[1]?>, endColorstr=#<?php echo $btngrad[2]?>);
	-o-background-size: 100% 100%;
}
.loginBtn,
.selectBtn:active .rightBtn {
	box-shadow:
		1px 1px 2px rgba(0,0,0,0.4),
		inset 1px 1px 0px rgba(255,255,255,0.5);
	-moz-box-shadow:
		1px 1px 2px rgba(0,0,0,0.4),
		inset 1px 1px 0px rgba(255,255,255,0.5);
	-webkit-box-shadow:
		1px 1px 2px rgba(0,0,0,0.4),
		inset 1px 1px 0px rgba(255,255,255,0.5);
}
.leftBtn {
  padding-left: <?php echo $buttoncomb[0]+2?>px;
  padding-right: <?php echo $buttoncomb[0]+2?>px;
	border-radius: <?php echo $buttoncomb[2]+0?>px 1px 1px <?php echo $buttoncomb[2]+0?>px;
	-moz-border-radius: <?php echo $buttoncomb[2]+0?>px 1px 1px <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+0?>px 1px 1px <?php echo $buttoncomb[2]+0?>px;
}
.rightBtn {
  padding-left: <?php echo $buttoncomb[0]-2?>px;
  padding-right: <?php echo $buttoncomb[0]-2?>px;
	border-radius: 0px <?php echo $buttoncomb[2]+0?>px <?php echo $buttoncomb[2]+0?>px 0px;
	-moz-border-radius: 0px <?php echo $buttoncomb[2]+0?>px <?php echo $buttoncomb[2]+0?>px 0px;
	-webkit-border-radius: 0px <?php echo $buttoncomb[2]+0?>px <?php echo $buttoncomb[2]+0?>px 0px;
	border-left-width: 0;
	letter-spacing: -2;
}
.loginBtn:hover,
.selectBtn:hover .rightBtn {
  background-color: #<?php echo $hovergrad[1]?>;
	background-image: url(data:image/svg+xml;base64,<?php echo base64_encode("<svg xmlns='http://www.w3.org/2000/svg'><linearGradient id='g' x2='0' y2='100%'><stop stop-color='#{$hovergrad[1]}'/><stop offset='100%' stop-color='#{$hovergrad[2]}'/></linearGradient><rect width='100%' height='100%' fill='url(#g)'/></svg>")?>);
	background-image: -moz-linear-gradient(top, #<?php echo $hovergrad[1]?>, #<?php echo $hovergrad[2]?>);
  background-image: -o-linear-gradient(top, #<?php echo $hovergrad[1]?>, #<?php echo $hovergrad[2]?>);
  background-image: -ms-linear-gradient(top, #<?php echo $hovergrad[1]?>, #<?php echo $hovergrad[2]?>);
	background-image: -webkit-gradient(linear, left top, left bottom, from(#<?php echo $hovergrad[1]?>), to(#<?php echo $hovergrad[2]?>));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#<?php echo $hovergrad[1]?>, endColorstr=#<?php echo $hovergrad[2]?>);
}
.loginBtn:active:hover,
.selectBtn.active .leftBtn,
.selectBtn:active .leftBtn {
	box-shadow:
		inset 1px 1px 3px rgba(0,0,0,0.5);
	-moz-box-shadow:
		inset 1px 1px 3px rgba(0,0,0,0.5);
	-webkit-box-shadow:
		inset 1px 1px 3px rgba(0,0,0,0.5);
}
.selectBtn.active {
  position: relative;
}
#loginWnd {
  visibility: hidden;
  top: 0;
  position: absolute;
  z-index: 10000;
  padding: <?php echo $popupcomb[1]+0?>px;
  border: 1px #<?php shift_color($popupcomb[2], -52)?> solid;
	border-radius: <?php echo $popupcomb[3]+0?>px;
	-moz-border-radius: <?php echo $popupcomb[3]+0?>px;
	-webkit-border-radius: <?php echo $popupcomb[3]+0?>px;
	background-color: #<?php echo $popupcomb[2]?>;
	box-shadow:
		1px 1px 5px rgba(0, 0, 0, 0.4);
	-moz-box-shadow:
		1px 1px 5px rgba(0, 0, 0, 0.4);
	-webkit-box-shadow:
		1px 1px 5px rgba(0, 0, 0, 0.4);
}
.usermenu .loginWndInside {
  padding: 5px;
}
#upArrow {
  position: absolute;
  top: -15px;
}
.upArrowOutside,
.upArrowInside {
  position: absolute;
  top: -1px;
  display: block;
  width: 2px;
  height: 0;
  border: 10px transparent solid;
  border-bottom-color: #<?php shift_color($popupcomb[2], -39)?>;
  border-top-width: 0;
}
.upArrowInside {
  width: 0;
  top: 0px;
  left: 1px;
	border-bottom-color: #<?php echo $popupcomb[2]?>;
}
.closeBtn {
  position: absolute;
  right: 0;
  top: 0;
  line-height: 0;
  margin: 0;
	padding: 3px 5px;
	border: 1px solid #<?php echo $buttoncomb[1]?>;
	border-radius: 0 <?php echo $buttoncomb[2]+0?>px;
	-moz-border-radius: 0 <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: 0 <?php echo $buttoncomb[2]+0?>px 0 <?php echo $buttoncomb[2]+0?>px;
  box-shadow:
		inset 1px -1px 3px rgba(0,0,0,0.3),
    0 1px 2px rgba(0, 0, 0, 0.4);
	-moz-box-shadow:
		inset 1px -1px 3px rgba(0,0,0,0.3),
    0 1px 2px rgba(0, 0, 0, 0.4);
	-webkit-box-shadow:
		inset 1px -1px 3px rgba(0,0,0,0.3),
    0 1px 2px rgba(0, 0, 0, 0.4);
}
.closeBtn:hover {
  box-shadow:
		inset 0 0 3px rgba(0,0,0,0.3),
    0 1px 2px rgba(0, 0, 0, 0.4);
	-moz-box-shadow:
		inset 0 0 3px rgba(0,0,0,0.3),
    0 1px 2px rgba(0, 0, 0, 0.4);
	-webkit-box-shadow:
		inset 0 0 3px rgba(0,0,0,0.3),
    0 1px 2px rgba(0, 0, 0, 0.4);
}
div.correct {
  background: transparent url(<?php echo $themeurl?>images/ok.png) no-repeat 0 center;
  width: 20px;
  display: inline-block;
}
.loginOr,
.smallTxt,
.checkLbl,
.forgetLnk,
.loginLst a:link,
.loginLst a:visited,
input[type=text].loginTxt,
input[type=password].loginTxt {
  <?php $fonts->printFont('textfont', 'Text');?>
}
.checkLbl {
  float: left;
}
.dj_ie8 input[type=password].loginTxt,
.dj_ie8 input[type=text].loginTxt,
.dj_ie8 input[type=password].loginTxt:focus,
.dj_ie8 input[type=text].loginTxt:focus {
  border: 1px #<?php echo $popupcomb[2]?> solid;
}
#passReg {
  margin-bottom: 0;
}
#passReg:hover ~ #strongFields .strongField.empty,
#passReg:focus ~ #strongFields .strongField.empty {
  background-color: #<?php echo $txtcomb[1]?>;
}
#passStrongness {
  *display: none;
  float: right;
}
.strongField {
  box-shadow:
    inset 0 0 0 1px rgba(0, 0, 0, 0.3),
    inset 2px 2px 1px rgba(255,255,255,0.5);
  -webkit-box-shadow:
    inset 0 0 0 1px rgba(0, 0, 0, 0.3),
    inset 2px 2px 0 rgba(255,255,255,0.5);
}
input[type=password].loginTxt,
input[type=text].loginTxt {
  display: block;
  width: 100%;
  *width: auto;
  height: auto;
  margin: 0 0 14px;
  padding: <?php echo $buttoncomb[0]+1?>px;
  padding-left: 25px;
  background: #<?php echo $txtcomb[0]?> no-repeat;
  border: none;
  *border: 1px #<?php echo $popupcomb[2]?> solid;
	border-radius: <?php echo $buttoncomb[2]+0?>px;
	-moz-border-radius: <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+0?>px;
	box-shadow:
	  1px 1px 0 rgba(255,255,255,0.8),
		inset 1px 1px 3px rgba(0,0,0,0.3);
	-moz-box-shadow:
	  1px 1px 0 rgba(255,255,255,0.8),
		inset 1px 1px 3px rgba(0,0,0,0.3);
	-webkit-box-shadow:
	  1px 1px 0 rgba(255,255,255,0.8),
		inset 1px 1px 3px rgba(0,0,0,0.3);
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
  background-position: 8px center, 8px -150%, 7px -150%;
  background-position: 8px center\9;
  -webkit-transition: background-position 0s ease-out;
	-moz-transition: background-position 0s ease-out;
  -ms-transition: background-position 0s ease-out;
  -o-transition: background-position 0s ease-out;
	transition: background-position 0s ease-out;
}
#strongFields .strongField,
#strongFields .strongField.empty,
input[type=password].loginTxt,
input[type=text].loginTxt {
  -webkit-transition: background-color 0.3s ease-out;
	-moz-transition: background-color 0.3s ease-out;
  -ms-transition: background-color 0.3s ease-out;
  -o-transition: background-color 0.3s ease-out;
	transition: background-color 0.3s ease-out;
}
input[type=password].loginTxt:focus,
input[type=text].loginTxt:focus {
  background-position: 8px 250%, 8px center, 7px -150%;
  -webkit-transition:background 0.3s;
	-moz-transition:background 0.3s;
  -ms-transition:background 0.3s;
  -o-transition:background 0.3s;
	transition:background 0.3s;
}
input[type=password].loginTxt.correct,
input[type=text].loginTxt.correct {
  background-position: 8px -150%, 8px -150%, 7px center;
  background-position: 7px center\9;
  background-image: url(<?php echo $themeurl?>images/ok.png)\9;
}
input[type=password].loginTxt.correct:focus,
input[type=text].loginTxt.correct:focus {
  background-position: 8px center, 8px -150%, 7px 250%;
}
input[type=password].regTxt,
input[type=text].regTxt {
  margin-bottom: 12px;
  width: 190px;
}
#regLyr {
  text-align: left;
}
#regLyr .columnL {
  float: left;
  margin: 0 20px 2px 0;
  *margin: 0 0 2px 0;
  clear: both;
}
#regLyr .columnR {
  float: left;
  *clear: both;
  margin: 0 0 2px 0;
}
button#submitReg.submitBtn {
  margin: 0 0 7px;
  *clear: both;
}
#regLyr .submitBtn{
  width: 190px;
  float: right;
  *float: none;
  *display: block;
  clear: none;
}
.dj_ie8 #regLyr .submitBtn {
  clear: both;
}
#regLyr span.submitBtn:nth-child(2n<?php if ($socialpos == 'bottom') echo '+1' ?>) {
  float: left;
  clear: both;
}
#logLyr br.socialBR {
  display: none;
}
input[type=password].loginTxt:hover,
input[type=text].loginTxt:hover,
input[type=password].loginTxt:focus,
input[type=text].loginTxt:focus {
  background-color: #<?php echo $txtcomb[1]?>;
}
input[name=email2].loginTxt,
input[name=email].loginTxt {
  background-image: url(<?php echo $themeurl?>images/email.png), url(<?php echo $themeurl?>images/email.png), url(<?php echo $themeurl?>images/ok.png);
  background-image: url(<?php echo $themeurl?>images/email.png)\9;
}
input[name=name].loginTxt,
input[name=username].loginTxt {
  background-image: url(<?php echo $themeurl?>images/user.png), url(<?php echo $themeurl?>images/user.png), url(<?php echo $themeurl?>images/ok.png);
  background-image: url(<?php echo $themeurl?>images/user.png)\9;
}
input[name=passwd2].loginTxt,
input[name=passwd].loginTxt {
  background-image: url(<?php echo $themeurl?>images/pass.png), url(<?php echo $themeurl?>images/pass.png), url(<?php echo $themeurl?>images/ok.png);
  background-image: url(<?php echo $themeurl?>images/pass.png)\9;
}
input[name=recaptchaResponse].loginTxt {
  margin: 0;
  background-image: url(<?php echo $themeurl?>images/pen.png), url(<?php echo $themeurl?>images/pen.png), url(<?php echo $themeurl?>images/ok.png);
  background-image: url(<?php echo $themeurl?>images/pen.png)\9;
}
.submitBtn {
  display: block;
  *display: inline;
  width: 100%;
  *width:auto;
  margin-bottom: 10px;
  box-sizing:border-box;
  -moz-box-sizing:border-box;
  -webkit-box-sizing:border-box;
}
.checkLbl,
.forgetLnk:link,
.forgetLnk:visited {
  cursor: pointer;
  font-size: <?php echo $smalltext+0 ?>px;
  font-weight: normal;
  vertical-align: 4px;
	margin:0;
}
.smallTxt {
  display: inline-block;
  margin-bottom: 1px;
  font-size: <?php echo $smalltext+0 ?>px;
  font-weight: normal;
}
.forgetDiv {
  float: right;
  *float: none;
  display: inline-block;
}
a.forgetLnk:link {
  padding: 0;
  margin-left: 10px;
  background: none;
}

a.forgetLnk:hover {
  background-color: transparent;
  text-decoration: underline;
}
.checkBox {
  display: block;
  margin: 1px 0 0 0;
  width: 10px;
  height: 10px;
  border: 1px #<?php shift_color($popupcomb[2], -52)?> solid;
  float: left;
  background: transparent none no-repeat 2px 2px;
	border-radius: <?php echo $buttoncomb[2]+0?>px;
	-moz-border-radius: <?php echo $buttoncomb[2]+0?>px;
	-webkit-border-radius: <?php echo $buttoncomb[2]+0?>px;
  box-shadow:
		1px 1px 2px rgba(0, 0, 0, 0.25);
	-moz-box-shadow:
		1px 1px 2px rgba(0, 0, 0, 0.25);
	-webkit-box-shadow:
		1px 1px 2px rgba(0, 0, 0, 0.25);
}
.checkLbl:hover .checkBox {
  background-color: #<?php echo $txtcomb[1]?>;
	box-shadow:
		1px 1px 2px rgba(0, 0, 0, 0.25) inset;
	-moz-box-shadow:
		1px 1px 2px rgba(0, 0, 0, 0.25) inset;
	-webkit-box-shadow:
		1px 1px 2px rgba(0, 0, 0, 0.25) inset;
}
.checkBox.active {
  background-image: url(<?php echo $themeurl?>images/check.png);
}
.loginLst {
  padding: 0;
  margin: 0;
  list-style: circle inside;
}
.loginLst a:link,
.loginLst a:visited {
  display: block;
  padding: 0 10px 0 20px;
  line-height: 22px;
  text-align: left;
  border-bottom: 1px #<?php echo $popupcomb[2]?> solid;
  box-shadow:
		0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
	-moz-box-shadow:
		0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
	-webkit-box-shadow:
		0px 1px 0px #<?php shift_color($popupcomb[0],20)?>;
  -webkit-transition: padding 0.25s ease-out;
	-moz-transition: padding 0.25s ease-out;
  -ms-transition: padding 0.25s ease-out;
  -o-transition: padding 0.25s ease-out;
	transition: padding 0.25s ease-out;
}
.forgetLnk:link,
.forgetLnk:visited,
.forgetLnk:hover,
.loginLst a.active,
.loginLst a:hover {
  padding: 0 5px 0 25px;
	<?php $fonts->printFont('textfont', 'Hover') ?>
}
#passStrongness,
#regRequired,
#regLyr .req {
  color: #<?php echo $textfont['Hover']['color'] ?>;
}
#regRequired {
  display: block;
}
<?php $circle = $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/circle.png", "010101", "0083e2")?>
<?php $hcircle= $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/circle.png", $textfont['Hover']['color'], "0083e2")?>
.loginLst a{
  background-color: transparent;
  background-repeat: no-repeat;
  background-image: url(<?php echo $circle ?>), url(<?php echo $hcircle ?>);
  background-position: 0 center, -100% 0;
	background-image: url(<?php echo $circle ?>)\9;
  background-position: 0 center\9;
}
.loginLst a.active,
.loginLst a:hover {
  background-position: -100% 0, 0 center;
  background-image: url(<?php echo $hcircle ?>)\9;
}
<?php $settings = $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/settings.png", "010101", "0083e2")?>
<?php $hsettings= $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/settings.png", $textfont['Hover']['color'], "0083e2")?>
.loginLst .settings {
	background-image: url(<?php echo $settings ?>), url(<?php echo $hsettings ?>);
  background-image: url(<?php echo $settings ?>)\9;
}
.loginLst .settings:hover {
  background-image: url(<?php echo $hsettings ?>)\9;
}
<?php $cart = $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/cart.png", "010101", "0083e2")?>
<?php $hcart= $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/cart.png", $textfont['Hover']['color'], "0083e2")?>
.loginLst .cart {
	background-image: url(<?php echo $cart ?>), url(<?php echo $hcart ?>);
  background-image: url(<?php echo $cart ?>)\9;
}
.loginLst .cart:hover {
  background-image: url(<?php echo $hcart ?>)\9;
}
<?php $off = $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/off.png", "010101", "0083e2")?>
<?php $hoff= $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/off.png", $textfont['Hover']['color'], "0083e2")?>
.loginLst .logout {
	background-image: url(<?php echo $off ?>), url(<?php echo $hoff ?>);
  background-image: url(<?php echo $off ?>)\9;
}
.loginLst .logout:hover {
  background-image: url(<?php echo $hoff ?>)\9;
}
.loginLst a.active,
.loginLst a.active:hover{
  background-image: none;
}
.loginLst a:last-child {
  border: 0;
  box-shadow:none;
	-moz-box-shadow:none;
	-webkit-box-shadow:none;
}
.blackBg {
	display:none;
	position:absolute;
	background:#000 <?php if ($bgpattern!=-1) echo "url({$themeurl}images/patterns/".basename($blackoutcomb[1]).')';?>;
	top:0;left:0;
	width:100%;
	height:100%;
	z-index:9999;
  opacity: 1;
  -webkit-transition: opacity 333ms ease-out;
	-moz-transition: opacity 333ms ease-out;
  -ms-transition: opacity 333ms ease-out;
  -o-transition: opacity 333ms ease-out;
	transition: opacity 333ms ease-out;
}
.waitAnim {
  display: block;
	width: 14px;
	height: 14px;
	margin: 4px 2px;
	position: absolute;
	background:  transparent url(<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/wait.png", $btngrad[2], "0083e2")?>) repeat-y 0 0;
}
.forgetDiv .waitAnim {
  margin: 0 0 0 -1px;
}
.loginBtn span {
  display: inline-block;
  cursor: default;
}
.loginBtn .waitAnim{
	display: none;
  background-image: url(<?php echo $this->cacheUrl.$helper->NewColorizeImage(dirname(__FILE__)."/../../themes/$theme/images/wait.png", $btnfont['Text']['color'], "0083e2")?>);
	margin: <?php echo (int)((14-$btnfont['Text']['size'])/2)?>px 0 0 -16px;
}
.fullWidth.selectBtn,
.fullWidth.selectBtn span {
  display: block;
  text-decoration: none;
  z-index: 0;
}
form.fullWidth {
  width: 100%;
}
.dj_ie9 .socialIco,
.dj_ie9 .loginMsg,
.dj_ie9 .loginBtn,
.dj_ie9 .loginBtn:hover,
.dj_ie9 .loginBtn:hover:active,
.dj_ie9 .selectBtn:hover .leftBtn,
.dj_ie9 .selectBtn:hover .rightBtn {
  filter: none;
}
.loginBtn,
.checkLbl,
.forgetLnk,
.loginLst a:link,
.loginLst a:visited {
	-moz-user-select:none;
	-khtml-user-select: none;
	-webkit-user-select: none;
  -ms-user-select: none;
}
@media screen and (max-width: 767px) {
  #regLyr .columnL {
    margin-right: 0;
  }
  #regLyr .columnR {
    clear: both;
  }
  #regLyr button.submitBtn,
  #regLyr .submitBtn{
    float: left;
    clear: both;
  }
  .socialIco {
    margin: 0 5px;
  }
  .captchaCnt {
    width: 184px;
  }
  #recaptchaImg {
    width: 100%;
    min-height: 0;
    transition: none;
    -o-transition: none;
    -ms-transition: none;
    -moz-transition: none;
    -webkit-transition: none;
  }
}