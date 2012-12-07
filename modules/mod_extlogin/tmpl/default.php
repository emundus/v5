<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_extlogin/style/' );
$usersConfig = &JComponentHelper::getParams( 'com_users' );

if($type == 'logout') : ?>
<form action="index.php" method="post" name="login" id="form-login-nav">
<?php  /**?>

<?php/** if($user->get('usertype') == "Registered" && $user->profile == 9) { ?>
<table>
<tr><th colspan="2"><?php// echo JText::_("CANDIDATURE_PERIOD"); ?></th></tr>
<?php
$tdate = date_parse($user->candidature_start);
?>
<tr><th><?php echo JText::_("FROM");?></th>
	<td><?php echo modExtLoginHelper::getDateTimeValue(mktime($tdate['hour'], $tdate['minute'], $tdate['second'], $tdate['month'], $tdate['day'], $tdate['year'])); ?></td>
</tr>
<?php 
$tdate = date_parse($user->candidature_end);
?>
<tr><th><?php echo JText::_("TO");?></th>
	<td><?php echo modExtLoginHelper::getDateTimeValue(mktime($tdate['hour'], $tdate['minute'], $tdate['second'], $tdate['month'], $tdate['day'], $tdate['year'])); ?></td></tr>
<?php 
$timeleft = modExtLoginHelper::timeleft(@$timeleft, gmmktime($tdate['hour'], $tdate['minute'], $tdate['second'], $tdate['month'], $tdate['day'], $tdate['year'])); 
?>
<tr><th><?php echo JText::_("TIME_LEFT");?></th>
	<td><?php echo $timeleft[0].' '.JText::_("DAY").', '.$timeleft[1].' '.JText::_("HOURS").' '.JText::_("AND").' '.$timeleft[2].' '.JText::_("MINUTES"); ?></td></tr>
</table>
<?php } */?>
<span class="logout">
	<?php if (isset($user->avatar)) { ?>
		<span id="log_photo"><img src="<?php echo EMUNDUS_PATH_REL.$user->id.'/tn_'.$user->avatar; ?>" width="30" align="middle" /></span>
	<?php } ?>
  
	<span class="logout-button">
		<button value="<?php echo JText::_( 'BUTTON_LOGOUT'); ?>" name="Submit" type="submit" title="<?php echo JText::_('BUTTON_LOGOUT'); ?>"><?php echo JText::_( 'BUTTON_LOGOUT'); ?></button>
	</span>
	<span class="log_updateprofile">
        <button value="<?php echo JText::_( 'PROFILE'); ?>" name="profile" type="button" onclick="self.location.href='index.php?option=com_extendeduser&task=edit'" title="<?php echo JText::_('UPDATE_PROFILE'); ?>"><?php echo JText::_( 'PROFILE'); ?></button>
    </span>
	<?php if ($params->get('greeting')) { ?><span id="log_username"><?php echo $user->get('firstname'); ?> </span><?php } ?>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.logout" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</span>
	
</form>
<?php else : ?>
	<?php if(JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
			$langScript = 	'var JLanguage = {};'.
							' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
							' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
							' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
							' var modlogin = 1;';
			$document = &JFactory::getDocument();
			$document->addScriptDeclaration( $langScript );
			JHTML::_('script', 'openid.js');
	endif; ?>



		
<form action="index.php" method="post" name="login" id="form-login-nav" >
	<?php echo $params->get('pretext'); ?>

	<span class="username">
		<input type="text" name="username" size="18" value="<?php echo JText::_( 'USERNAME_CONNEXION' ); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_( 'USERNAME_CONNEXION' ); ?>';" onfocus="if(this.value=='<?php echo JText::_( 'USERNAME_CONNEXION' ); ?>') this.value='';" />
	</span>
	
	<span class="password">
		<input type="password" name="passwd" size="10" value="<?php echo JText::_( 'Password' ); ?>" onblur="if(this.value=='') this.value='<?php echo JText::_( 'Password' ); ?>';" onfocus="if(this.value=='<?php echo JText::_( 'Password' ); ?>') this.value='';" />
	</span>
	
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
		<!-- <input type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" /> -->
	<?php endif; 
		if ($usersConfig->get('allowUserRegistration')) { ?>
            <span class="login-button">
                <button value="register" name="register" type="button" title="<?php echo JText::_('REGISTER'); ?>" onclick="location.href='index.php?option=com_extendeduser&view=register'"><?php echo JText::_( 'REGISTER' ); ?></button>
            </span><?php
        } ?>
		<span class="login-button">
			<button value="<?php echo JText::_( 'LOGIN' ); ?>" name="Submit" type="submit" title="<?php echo JText::_('LOGIN'); ?>"><?php echo JText::_( 'LOGIN'); ?></button>
		</span><?php 
		
		if ($params->get('lost_password') ) { ?>
			<span class="lostpassword">
				<a href="<?php echo JRoute::_( 'index.php?option=com_extendeduser&view=reset' ); ?>" title="<?php echo JText::_('FORGOT_PASSWORD'); ?>"></a>
			</span><?php 
		} 
		if ( $params->get('lost_username')) { ?>
            <span class="lostusername">
                <a href="<?php echo JRoute::_( 'index.php?option=com_extendeduser&view=remind' ); ?>" title="<?php echo JText::_('FORGOT_USERNAME'); ?>"></a>
            </span><?php 
		} 
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') && $usersConfig->get('registration')) { ?>
            <span class="registration">
                <a href="<?php echo JRoute::_( 'index.php?option=com_extendeduser&view=register' ); ?>" title="<?php echo JText::_('REGISTER'); ?>"></a>
            </span><?php 
		} ?>
	<?php echo $params->get('posttext'); ?>

	<input type="hidden" name="option" value="com_extendeduser" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php 
	
	
 endif; ?>
