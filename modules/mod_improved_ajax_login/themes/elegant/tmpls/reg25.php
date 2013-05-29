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
<div id="regLyr">

  <?php if (count($modules = JModuleHelper::getModules($params->get('reg_top', 'reg-top')))): // REG-TOP MODULEPOS ?>
    <?php foreach ($modules as $m): ?>
      <?php echo JModuleHelper::renderModule($m) ?>
    <?php endforeach ?>
    <div class="loginBrd"></div>
  <?php endif ?>

  <h1 class="loginH1">
    <?php echo JText::_('MOD_LOGIN_REGISTER') ?>
    <?php if (isset($_SESSION['reCaptcha'])): ?>
      <span class="smallTxt" id="regRequired" style="margin:0 0 -3px">* <?php echo JText::_("COM_USERS_REGISTER_REQUIRED") ?></span>
    <?php endif ?>
  </h1>

  <?php if (isset($_SESSION['oauth']) && $socialpos=='top') require dirname(__FILE__).'/social.php' // TOP SOCIALPOS ?>

	<form action="<?php echo JRoute::_('index.php?option=com_improved_ajax_login&task=register', true, $params->get('usesecure')) ?>" method="post" name="register" id="regForm">
    <div class="columnL">
      <label class="smallTxt" for="nameReg"><?php echo JText::_("COM_USERS_REGISTER_NAME_LABEL") ?> <span class="req">*</span></label>
			<input id="nameReg" class="loginTxt regTxt required" name="name" type="text" title="<?php echo JText::_("COM_USERS_REGISTER_NAME_DESC") ?>"
      /><div style="display:none"><?php echo JText::_("COM_USERS_REGISTER_NAME_MESSAGE") ?></div>
    </div>
    <div class="columnR">
      <label class="smallTxt" for="userReg"><?php echo JText::_("COM_USERS_REGISTER_USERNAME_LABEL") ?> <span class="req">*</span></label>
      <div class="waitAnim" style="margin:6px 7px; display:none">
      </div><input id="userReg" class="loginTxt regTxt required" name="username" type="text" title="<?php echo JText::_("COM_USERS_REGISTER_USERNAME_DESC") ?>"
      /><div style="display:none"><?php echo str_replace('< ', '&lt; ', JText::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2)) ?></div>
    </div>
    <div class="columnL">
      <label class="smallTxt" for="passReg"><?php echo JText::_("COM_USERS_REGISTER_PASSWORD1_LABEL") ?> <span class="req">*</span></label>
      <label class="smallTxt" for="passReg" id="passStrongness"></label>
      <input id="passReg" class="loginTxt regTxt required" name="passwd" type="password" title="<?php echo JText::_("COM_USERS_DESIRED_PASSWORD") ?>"
      autocomplete="off" /><div style="display:none"><?php echo JText::_("COM_USERS_REGISTER_PASSWORD2_MESSAGE") ?></div>
      <label id="strongFields" for="passReg">
        <i class="empty strongField"></i><i class="empty strongField"></i><i class="empty strongField"></i><i class="empty strongField"></i><i class="empty strongField"></i>
      </label>
    </div>
    <div class="columnR">
      <label class="smallTxt" for="pass2Reg"><?php echo JText::_("COM_USERS_REGISTER_PASSWORD2_LABEL") ?> <span class="req">*</span></label>
      <input id="pass2Reg" class="loginTxt regTxt required" name="passwd2" type="password" title="<?php echo JText::_("COM_USERS_REGISTER_PASSWORD2_DESC") ?>"
      autocomplete="off" /><div style="display:none"><?php echo JText::_('COM_USERS_REGISTER_PASSWORD1_MESSAGE') ?></div>
    </div>
    <div class="columnL">
      <label class="smallTxt" for="mailReg"><?php echo JText::_("COM_USERS_REGISTER_EMAIL1_LABEL") ?> <span class="req">*</span></label>
      <div class="waitAnim" style="margin:6px 7px; display:none">
      </div><input id="mailReg" class="loginTxt regTxt required" name="email" type="text" title="<?php echo JText::_("COM_USERS_REGISTER_EMAIL1_DESC") ?>"
      /><div style="display:none"><?php echo JText::_('COM_USERS_INVALID_EMAIL') ?></div>
    </div>
    <div class="columnR">
      <label class="smallTxt" for="mail2Reg"><?php echo JText::_("COM_USERS_REGISTER_EMAIL2_LABEL") ?> <span class="req">*</span></label>
      <div class="waitAnim" style="margin:6px 7px; display:none">
      </div><input id="mail2Reg" class="loginTxt regTxt required" name="email2" type="text" title="<?php echo JText::_("COM_USERS_REGISTER_EMAIL2_DESC") ?>"
      /><div style="display:none"><?php echo JText::_('COM_USERS_REGISTER_EMAIL2_MESSAGE') ?></div>
    </div>

    <?php if (@$_SESSION['reCaptcha']): ?>
      <input type="hidden" id="recaptchaChallenge" name="recaptchaChallenge" />
      <label for="recaptchaResponse" class="captchaCnt">
        <span id="refreshBtn" class="closeBtn loginBtn"><img src="<?php echo JRoute::_("modules/{$module->module}/themes/elegant/images/refresh.png")?>" alt="r" width="8" height="10"/></span>
      </label>
      <div class="columnL">
        <label class="smallTxt" for="recaptchaResponse"><?php echo JText::_('COM_USERS_CAPTCHA_LABEL') ?>: <span class="req">*</span></label>
        <input id="recaptchaResponse" class="loginTxt regTxt required" name="recaptchaResponse" type="text" title="<?php echo JText::_("COM_USERS_CAPTCHA_DESC") ?>"
        autocomplete="off" />
      </div>
      <div class="columnR">
        <label class="smallTxt">&nbsp;</label><br />
        <button class="loginBtn submitBtn" id="submitReg"><span><span class="waitAnim"></span><?php echo JText::_('JREGISTER') ?></span></button>
      </div>
    <?php else: ?>
    <div class="columnL">
      <span class="smallTxt" id="regRequired">* <?php echo JText::_("COM_USERS_REGISTER_REQUIRED") ?></span>
    </div>
    <button class="loginBtn submitBtn" id="submitReg"><span><span class="waitAnim"></span><?php echo JText::_('JREGISTER') ?></span></button>
    <?php endif ?>

    <?php echo JHTML::_('form.token') ?>
    <input type="hidden" name="ajax" value="" />
    <input type="hidden" name="socialType" value="" />
    <input type="hidden" name="socialId" value="" />
	</form>

  <br style="clear:both" />
  <?php if (@$_SESSION['oauth'] && $socialpos=='bottom') require dirname(__FILE__).'/social.php' // BOTTOM SOCIALPOS ?>

  <?php if (count($modules = JModuleHelper::getModules($params->get('reg_bottom', 'reg-bottom')))): // REG-BOTTOM MODULEPOS ?>
    <div class="loginBrd"></div>
    <?php foreach ($modules as $m): ?>
      <?php echo JModuleHelper::renderModule($m) ?>
    <?php endforeach ?>
  <?php endif ?>

</div>