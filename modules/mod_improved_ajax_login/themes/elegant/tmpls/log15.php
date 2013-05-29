<?php
/*-------------------------------------------------------------------------
# mod_improved_ajax_login - Improved AJAX Login and Register
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php defined('_JEXEC') or die('Restricted access') ?>
<div id="logLyr">

  <?php if (count($modules = JModuleHelper::getModules($params->get('top_module', 'login-top')))): // LOGIN-TOP MODULEPOS ?>
    <?php foreach ($modules as $m): ?>
      <?php echo JModuleHelper::renderModule($m) ?>
    <?php endforeach ?>
    <div class="loginBrd"></div>
  <?php endif ?>

	<form action="<?php echo JRoute::_('index.php?option=com_improved_ajax_login&task=login', true, $params->get('usesecure')) ?>" method="post" name="login" id="ologinForm" class="<?php if (!$loginpopup) echo 'fullWidth' ?>">
    <?php if (!$module->showtitle && !$loginpopup || $loginpopup): ?>
  	<h1 class="loginH1"><?php echo $params->get('header', 'Login to your account') ?></h1>
    <?php endif ?>

    <?php if (@$_SESSION['oauth'] && $socialpos=='top') require dirname(__FILE__).'/social.php' // TOP SOCIALPOS ?>

		<input id="userTxt" class="loginTxt" name="<?php echo $params->get('username', 1)? 'username':'email'?>" type="text"/>
    <input id="passTxt" class="loginTxt" name="passwd" type="password"/>
    <button class="loginBtn submitBtn" id="submitBtn"><span><span class="waitAnim"></span><?php echo JText::_('LOGIN')?></span></button>

    <label class="checkLbl" for="keepSigned">
      <span class="checkBox<?php if ($remember = $params->get('rememberme', 0)) echo ' active'?>"></span>
			<input id="keepSigned" name="remember" type="checkbox" <?php if ($remember) echo 'checked="checked"'?> style="display:none"/>
			&nbsp;<?php echo JText::_('REMEMBER ME') ?>
		</label>

		<div class="forgetDiv">
			<a class="forgetLnk" href="<?php echo JRoute::_('index.php?option=com_user&view=reset') ?>"><?php echo JText::_('FORGOT_YOUR_PASSWORD') ?></a>
    <?php if ($params->get('forgotname', 0)): ?>
      <br />
      <a class="forgetLnk" href="<?php echo JRoute::_('index.php?option=com_user&view=remind') ?>"><?php echo JText::_('FORGOT_YOUR_USERNAME') ?></a>
    <?php endif ?>
		</div>

		<input type="hidden" name="return" value="<?php echo $return ?>" />
    <input type="hidden" name="ajax" value="<?php echo $params->get("ajax", 0) ?>" />
		<?php echo JHTML::_('form.token') ?>
	</form>

  <?php if (@$_SESSION['oauth'] && $socialpos=='bottom') require dirname(__FILE__).'/social.php' // BOTTOM SOCIALPOS ?>

  <?php if (count($modules = JModuleHelper::getModules($params->get('bottom_module', 'login-bottom')))): // LOGIN-BOTTOM MODULEPOS ?>
    <div class="loginBrd"></div>
    <?php foreach ($modules as $m): ?>
      <?php echo JModuleHelper::renderModule($m) ?>
    <?php endforeach ?>
  <?php endif ?>

</div>