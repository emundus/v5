<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_extendeduser/style/' );
?>
<script language="javascript" type="text/javascript">
function submitbutton( pressbutton ) {
	var form = document.userform;
	var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

	if (pressbutton == 'cancel') {
		form.task.value = 'cancel';
		form.submit();
		return;
	}

	// do field validation
	if (form.name.value == "") {
		alert( "<?php echo JText::_( 'NO_NAME_ERR', true );?>" );
	} else if (form.username.value == "") {
		alert( "<?php echo JText::_( 'USERNAME_ERR', true );?>" );
	} else if (r.exec(form.username.value) || form.username.value.length < 3) {
		alert( "<?php printf( JText::_( 'USERNAME_ERR', true ), JText::_( 'USERNAME', true ), 3 );?>" );
	} else if (form.email.value == "") {
		alert( "<?php echo JText::_( 'EMAIL_ERR_NOINFO', true );?>" );
	} else if ((form.password.value != "") && (form.password.value != form.password2.value)){
		alert( "<?php echo JText::_( 'REGWARN_VPASS2', true );?>" );
	} else if (r.exec(form.password.value)) {
		alert( "<?php printf( JText::_( 'REGWARN_PASS', true ), JText::_( 'PASSWORD', true ), 4 );?>" );
	} else {
		form.submit();
	}
}
</script>
<form action="index.php" method="post" name="userform" autocomplete="off">
<div class="componentheading">
	<?php echo JText::_( 'EDIT_YOUR_DETAILS' ); ?>
</div>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
<tr>
	<td width="232">
		<label for="firstname">
			<?php echo JText::_( 'YOUR_FIRSTNAME' ); ?>:
		</label>
	</td>
	<td width="842" colspan="3">
	
		<input class="inputbox" type="text" id="firstname" name="firstname" value="<?php echo $this->user->get('firstname');?>" size="40" />
	</td>
</tr>
<tr>
	<td width="232">
		<label for="lastname">
			<?php echo JText::_( 'YOUR_LASTNAME' ); ?>:
		</label>
	</td>
	<td colspan="3">
	
		<input class="inputbox" type="text" id="lastname" name="lastname" value="<?php echo $this->user->get('lastname');?>" size="40" />
	</td>
</tr>
<tr>
	<td width="232" rowspan="2">
			<?php echo JText::_( 'YOUR_PROFILE' ); ?>:
	</td>
</tr>

<tr>
<?php 
$user =& JFactory::getUser();
if (!$this->isAppSend) { 
	$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
	if (!in_array($this->user->usertype, $allowed)) { ?>
  	<td><img src="media/com_fabrik/images/required.png" alt="required" title="This is a required field"/>
        <select name="profile" id="profile" class="inputbox required validate-profile" disabled="disabled">
          <?php $this->printProfileOptions( $this->user->profile ); ?>
        </select>
    </td>
<?php } else { ?>
	<td> 
        <select name="profile" id="profile" class="inputbox required validate-profile">
          <?php $this->printProfileAllowedOptions( $this->user->profile, $this->user->id ); ?>
        </select>
    </td>
<?php 	
	}
} else{
	echo '<td>'.$this->user->profile_label.'</td>';
}
?>
</tr>
<tr>
<?php if($this->user->applicant==1) { ?>
	<td width="232">
<?php 
	$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
	if (!in_array($this->user->usertype, $allowed))
		echo JText::_( 'YOUR_FUTURE_PROMOTION' ).":"; 
?>
	</td>
<?php } ?>
	<td colspan="3">
	
		<?php echo $this->user->get('schoolyear');?><input type="hidden" id="schoolyear" name="schoolyear" value="<?php echo $this->user->get('schoolyear');?>"/>
	</td>
</tr>
<tr>
	<td>
		<label for="email">
			<?php echo JText::_( 'YOUR_EMAIL' ); ?>:
		</label>
	</td>
	<td colspan="3">
		<input class="inputbox" type="text" id="email" name="email" value="<?php echo $this->user->get('email');?>" size="40" />
	</td>
<tr>
	<td>
			<?php echo JText::_( 'YOUR_LOGIN' ); ?>:
	</td>
	<td colspan="3">
		<?php echo $this->user->get('username');?><input type="hidden" id="username" name="username" value="<?php echo $this->user->get('username');?>"/>
	</td>
</tr>
<tr>
	<td>
		<label for="password">
			<?php echo JText::_( 'NEW_PASSWORD' ); ?>:
		</label>
	</td>
	<td colspan="3">
		<input class="inputbox" type="password" id="password" name="password" value="" size="40" />
	</td>
</tr>
<tr>
	<td>
		<label for="password2">
			<?php echo JText::_( 'VERIFY_PASSWORD' ); ?>:
		</label>
	</td>
	<td colspan="3">
		<input class="inputbox" type="password" id="password2" name="password2" size="40" />
	</td>
</tr>
</table>
<?php  /*if(isset($this->params)) :  echo $this->params->render( 'params' ); endif; */?>
<button class="button" type="submit" onclick="submitbutton( this.form );return false;"><?php echo JText::_('SUBMIT'); ?></button>


<input type="hidden" name="id" value="<?php echo $this->user->get('id');?>" />
<input type="hidden" name="gid" value="<?php echo $this->user->get('gid');?>" />
<input type="hidden" name="option" value="com_extendeduser" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>