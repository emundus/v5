<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_extendeduser/style/' );
?>
<script type="text/javascript">
<!--
	Window.onDomReady(function(){
		document.formvalidator.setHandler('passverify', function (value) { return ($('password').value == value); }	);
		//writeSchoolYears(document.getElementById('profile1'));
		//document.getElementById('profile1').addEvent( 'change', function() { writeSchoolYears(document.getElementById('profile1')); });
	});
// -->
</script>
<?php if(isset($this->message)) $this->display('message'); ?>
<br>
<form action="<?php echo JRoute::_( 'index.php?option=com_extendeduser' ); ?>" method="post" id="josForm" name="josForm" class="form-validate">
<div class="componentheading">
	<?php echo JText::_( 'REGISTRATION' ); ?>
</div>
<table align="center" cellpadding="0" cellspacing="0" border="0" class="contentpane">
<tr>
	<td width="22%" colspan="3" height="40">
		<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'TEXT_BOTTOM' ); ?>
	</td>
</tr>
<tr>
	<td><img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/> 
		<label id="profilemsg" for="profile">
			<?php echo JText::_( '1ST_CHOICE' ); ?>:
		</label><br />
        <select name="profile1" id="profile1" class="inputbox required validate-profile">
  			<?php $this->printProfileOptions( isset($this->repop)?$this->repop[ 'profile1' ]:'' ); ?>
  		</select>
	</td>
  	<td>
		<label id="profilemsg" for="profile2">
			<?php echo JText::_( '2ND_CHOICE' ); ?>:
		</label><br />
        <select name="profile2" id="profile2" class="inputbox">
  			<?php $this->printProfileOptions( isset($this->repop)?$this->repop[ 'profile2' ]:'' ); ?>
  		</select>
  	</td>
    <td>
		<label id="profilemsg" for="profile3">
			<?php echo JText::_( '3RD_CHOICE' ); ?>:
		</label><br />
        <select name="profile3" id="profile3" class="inputbox">
  			<?php $this->printProfileOptions( isset($this->repop)?$this->repop[ 'profile3' ]:'' ); ?>
  		</select>
  	</td>
</tr>
<tr><td colspan="3" class="description" id="profileaff"></td></tr>
<tr><td colspan="3">
	<?php	$db =& JFactory::getDBO();
			$query = 'SELECT id, schoolyear, description FROM `#__emundus_setup_profiles` WHERE id=9';
			$db->setQuery($query);
			$pro = $db->loadObject();
			echo '<input type="hidden" name="schoolyear" id="schoolyear" value="'.$pro->schoolyear.'"/>';
	?>
</td></tr>
<tr>
	<td height="40">
		<label id="firstnamemsg" for="firstname">
			<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'FIRSTNAME' ); ?>
		</label>
	</td>
  	<td>
  		<input type="text" name="firstname" id="firstname" size="30" value="<?php if(isset($this->repop)) echo $this->repop[ 'firstname' ];?>" class="inputbox required" maxlength="50" />  
  	</td>
</tr>
<tr>
	<td height="40">
		<label id="lastnamemsg" for="lastname">
			<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'LASTNAME' ); ?>
		</label>
	</td>
  	<td>
  		<input type="text" name="lastname" id="lastname" size="30" value="<?php if(isset($this->repop)) echo $this->repop[ 'lastname' ];?>" class="inputbox required" maxlength="50" />  
  	</td>
</tr>
<tr>
<tr>
	<td height="40">
		<label id="emailmsg" for="email">
			<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'EMAIL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" id="email" name="email" size="30" value="<?php if(isset($this->repop)) echo $this->repop[ 'email' ];?>" class="inputbox required validate-email" maxlength="100" />  
	</td>
</tr>
<tr>
	<td height="40">
		<label id="usernamemsg" for="username">
			<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'USERNAME' ); ?>
		</label>
	</td>
	<td>
		<input type="text" id="username" name="username" size="30" value="<?php if(isset($this->repop)) echo $this->repop[ 'username' ];?>" class="inputbox required validate-username" maxlength="25" />
	</td>
	<td>
		&nbsp;&nbsp;<?php echo JText::_( 'USERNAME_FORM' ); ?>
	</td>
</tr>
<tr>
	<td height="40">
		<label id="pwmsg" for="password">
			<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'PASSWORD' ); ?>
		</label>
	</td>
	
  	<td>
  		<input class="inputbox required validate-password" type="password" id="password" name="password" size="30" value="" />  
  	</td>
	<td>
		&nbsp;&nbsp;<?php echo JText::_( 'PASSWORD_FORM' ); ?>
	</td>
</tr>
<tr>
	<td height="40">
		<label id="pw2msg" for="password2">
			<img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/><?php echo JText::_( 'VERIFY_PASSWORD' ); ?>
		</label>
	</td>
	
	<td>
		<input class="inputbox required validate-passverify" type="password" id="password2" name="password2" size="30" value="" />  
	</td>
</tr>
</table>
<br>
<button class="button validate" type="submit"><?php echo JText::_('Register'); ?></button>



<input type="hidden" name="task" value="register_save" />
<input type="hidden" name="id" value="0" />
<input type="hidden" name="gid" value="0" />
<input type="hidden" name="profile" value="9" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
