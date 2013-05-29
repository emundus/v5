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
// init user menu
if (!$guest && ($usermenu = $params->get('usermenu', 0))) {
  require_once JPATH_SITE.'/modules/mod_menu/helper.php';
  $menuparams = new JObject(array(
    'menutype' => $usermenu,
    'startLevel' => 0,
    'endLevel' => 0,
    'showAllChildren' => 1
  ));
  $menulist = modMenuHelper::getList($menuparams);
} ?>
<div id="<?php echo $module->instanceid ?>">

<?php if ($guest): // LOGIN ?>
  <?php if ($loginpopup): ?>
    <a id="loginBtn" class="selectBtn" onclick="return false" href="<?php echo JRoute::_('index.php?option=com_users&view=login') ?>">
      <?php if (!$params->get('wndcenter', 0)): ?>
    		<span class="loginBtn leftBtn">
    			<?php echo JText::_('JLOGIN') ?>
    		</span><span class="loginBtn rightBtn">&nbsp;<img src="<?php echo JRoute::_("modules/{$module->module}/themes/elegant/images/arrow.png")?>" alt="\/" width="10" height="7"/>&nbsp;</span>
      <?php else: ?>
        <span class="loginBtn"><?php echo JText::_('JLOGIN') ?></span>
      <?php endif ?>
    </a>
  <?php else: ?>
    <?php require dirname(__FILE__).'/log25.php' // LOGIN FORM ?>
    <div class="loginBrd"></div>
  <?php endif ?>

	<?php if ($allowUserRegistration): // REGISTRATION ?>
    <?php if (@$module->view == 'reg'): ?>
      <?php require dirname(__FILE__).'/reg25.php' // REGISTRATION FORM ?>
    <?php else: ?>
  	  <a id="regBtn" class="selectBtn <?php if (!$loginpopup) echo 'fullWidth' ?>" href="<?php echo $regpage ?>">
      <?php if ($regp[0] == 'joomla' && !$params->get('wndcenter', 0)): ?>
    		<span class="loginBtn leftBtn">
    			<?php echo JText::_('JREGISTER') ?>
    		</span><span class="loginBtn rightBtn">&nbsp;<img src="<?php echo JRoute::_("modules/{$module->module}/themes/elegant/images/arrow.png")?>" alt="\/" width="10" height="7"/>&nbsp;</span>
      <?php else: ?>
        <span class="loginBtn"><?php echo JText::_('JREGISTER') ?></span>
      <?php endif ?>
  		</a>
    <?php endif ?>
  <?php endif ?>

	<div id="loginWnd">
    <div class="loginWndInside">
      <div id="upArrow">
        <div style="position:relative">
					<div class="upArrowOutside"></div>
					<div class="upArrowInside"></div>
        </div>
			</div>
			<button id="xBtn" class="closeBtn loginBtn"><img src="<?php echo JRoute::_("modules/{$module->module}/themes/elegant/images/x.png")?>" alt="x" width="8" height="10"/></button>
      <?php if ($loginpopup) require dirname(__FILE__).'/log25.php' // LOGIN FORM ?>
      <?php if ($allowUserRegistration && @$module->view != 'reg' && ($regp[0] == 'joomla' || @$_SESSION['oauth']['twitter'])) require dirname(__FILE__).'/reg25.php' // REGISTER FORM ?>
    </div>
	</div>

<?php else: // LOGOUT ?>
  <a id="userBtn" class="selectBtn" onclick="return false" href="<?php echo $mypage ?>">
	  <span class="loginBtn leftBtn">
			<?php echo $params->get('name')? $user->get('name') : $user->get('username')?>
		</span><span class="loginBtn rightBtn">&nbsp;<img src="<?php echo JRoute::_("modules/{$module->module}/themes/elegant/images/arrow.png")?>" alt="\/" width="10" height="7"/>&nbsp;</span>
	</a>
	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.logout', true, $params->get('usesecure')) ?>" method="post" name="login" id="logoutForm">
		<input type="hidden" name="return" value="<?php echo $return ?>" />
  	<?php echo JHtml::_('form.token') ?>
    <noscript>
  			<button class="loginBtn"><?php echo JText::_('JLOGOUT') ?></button>
    </noscript>
	</form>

	<div id="loginWnd" class="usermenu">
    <div class="loginWndInside">
      <div id="upArrow">
        <div style="position:relative">
					<div class="upArrowOutside"></div>
					<div class="upArrowInside"></div>
        </div>
			</div>
			<div class="loginLst">
        <?php if($params->get('profile', 1)):?>
				<a class="settings" href="<?php echo $mypage ?>"><?php echo JText::_('COM_USERS_PROFILE_MY_PROFILE') ?></a>
        <?php endif ?>
				<?php if ($mycart): ?>
				<a class="cart" href="<?php echo JRoute::_($mycartURL) ?>" ><?php echo $mycart ?></a>
				<?php endif ?>
				<?php if ($usermenu): ?>
					<?php foreach ($menulist as $mi): ?>
					<a class="mitem" href="<?php echo JRoute::_($mi->flink) ?>" ><?php echo $mi->title ?></a>
					<?php endforeach ?>
        <?php endif ?>
				<a class="logout" href="#" ><?php echo JText::_('JLOGOUT') ?></a>
			</div>
    </div>
	</div>
<?php endif ?>
</div>