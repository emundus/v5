<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php jimport( 'joomla.html.editor' ); $editor =& JFactory::getEditor(); ?>
<?php jimport( 'joomla.html.html' ); ?>
<?php $data =& $this->data; ?>
<script type="text/javascript">

	function submitbutton(pressbutton)	{
		var form = document.adminForm;
	
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
	
		// remove this code
		alert ('<?php echo 'Remember to add js check in ' . __FILE__ . ' after line n. ' . __LINE__; ?>');
		submitform( pressbutton );
		return;
		// end remove this code
	
		// do field validation
		if (form.My_Field_Name.value == "") {
			alert( "<?php echo JText::_( 'Field must have a name', true ); ?>" );
		} else if (form.My_Field_Name.value.match(/[a-zA-Z0-9]*/) != form.My_Field_Name.value) {
			alert( "<?php echo JText::_( 'Field name contains bad caracters', true ); ?>" );
		} else if (form.My_Field_Name_typefield.options[form.My_Field_Name_typefield.selectedIndex].value == "0") {
			alert( "<?php echo JText::_( 'You must select a field type', true ); ?>" );		
		} else {
			submitform( pressbutton );
		}
	}

</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'DETAILS' ); ?></legend>
		<table class="admintable">
<!-- jcb code -->
<tr>
	<td width="100" align="right" class="key">
		<label for="date_modified">
			<?php echo JText::_( 'DATE_MODIFIED' ); ?>:
		</label>
	</td>
	<td>
		<?php echo JHTML::calendar($this->data->date_modified, 'date_modified', 'date_modified'); ?>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="date_entered">
			<?php echo JText::_( 'DATE_ENTERED' ); ?>:
		</label>
	</td>
	<td>
		<?php echo JHTML::calendar($this->data->date_entered, 'date_entered', 'date_entered'); ?>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="modified_user_id">
			<?php echo JText::_( 'MODIFIED_USER_ID' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="modified_user_id" id="modified_user_id" size="32" maxlength="12" value="<?php echo htmlspecialchars($this->data->modified_user_id, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="created_by">
			<?php echo JText::_( 'CREATED_BY' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="created_by" id="created_by" size="32" maxlength="12" value="<?php echo htmlspecialchars($this->data->created_by, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="state">
			<?php echo JText::_( 'STATE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="state" id="state" size="32" maxlength="1" value="<?php echo htmlspecialchars($this->data->state, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="country_code">
			<?php echo JText::_( 'COUNTRY_CODE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="country_code" id="country_code" size="32" maxlength="19" value="<?php echo htmlspecialchars($this->data->country_code, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="account_name">
			<?php echo JText::_( 'ACCOUNT_NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="account_name" id="account_name" size="32" maxlength="93" value="<?php echo htmlspecialchars($this->data->account_name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="department">
			<?php echo JText::_( 'DEPARTMENT' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="department" id="department" size="32" maxlength="153" value="<?php echo htmlspecialchars($this->data->department, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="primary_address_street">
			<?php echo JText::_( 'PRIMARY_ADDRESS_STREET' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="primary_address_street" id="primary_address_street" size="32" maxlength="162" value="<?php echo htmlspecialchars($this->data->primary_address_street, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="primary_address_postalcode">
			<?php echo JText::_( 'PRIMARY_ADDRESS_POSTALCODE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="primary_address_postalcode" id="primary_address_postalcode" size="32" maxlength="82" value="<?php echo htmlspecialchars($this->data->primary_address_postalcode, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="primary_address_state">
			<?php echo JText::_( 'PRIMARY_ADDRESS_STATE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="primary_address_state" id="primary_address_state" size="32" maxlength="10" value="<?php echo htmlspecialchars($this->data->primary_address_state, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="primary_address_city">
			<?php echo JText::_( 'PRIMARY_ADDRESS_CITY' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="primary_address_city" id="primary_address_city" size="32" maxlength="29" value="<?php echo htmlspecialchars($this->data->primary_address_city, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="salutation">
			<?php echo JText::_( 'SALUTATION' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="salutation" id="salutation" size="32" maxlength="69" value="<?php echo htmlspecialchars($this->data->salutation, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="first_name">
			<?php echo JText::_( 'FIRST_NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="first_name" id="first_name" size="32" maxlength="27" value="<?php echo htmlspecialchars($this->data->first_name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="last_name">
			<?php echo JText::_( 'LAST_NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="last_name" id="last_name" size="32" maxlength="128" value="<?php echo htmlspecialchars($this->data->last_name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="title">
			<?php echo JText::_( 'TITLE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="title" id="title" size="32" maxlength="224" value="<?php echo htmlspecialchars($this->data->title, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="phone_work">
			<?php echo JText::_( 'PHONE_WORK' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="phone_work" id="phone_work" size="32" maxlength="138" value="<?php echo htmlspecialchars($this->data->phone_work, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="phone_fax">
			<?php echo JText::_( 'PHONE_FAX' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="phone_fax" id="phone_fax" size="32" maxlength="96" value="<?php echo htmlspecialchars($this->data->phone_fax, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="email">
			<?php echo JText::_( 'EMAIL' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="email" id="email" size="32" maxlength="63" value="<?php echo htmlspecialchars($this->data->email, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="website">
			<?php echo JText::_( 'WEBSITE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="website" id="website" size="32" maxlength="56" value="<?php echo htmlspecialchars($this->data->website, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="mailing">
			<?php echo JText::_( 'MAILING' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="mailing" id="mailing" size="32" maxlength="1" value="<?php echo htmlspecialchars($this->data->mailing, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="account_id">
			<?php echo JText::_( 'ACCOUNT_ID' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="account_id" id="account_id" size="32" maxlength="12" value="<?php echo htmlspecialchars($this->data->account_id, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="comment">
			<?php echo JText::_( 'COMMENT' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="comment" id="comment" size="32" maxlength="68" value="<?php echo htmlspecialchars($this->data->comment, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="referent_postgradutate">
			<?php echo JText::_( 'REFERENT_POSTGRADUTATE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="referent_postgradutate" id="referent_postgradutate" size="32" maxlength="1" value="<?php echo htmlspecialchars($this->data->referent_postgradutate, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="referent_esa_name">
			<?php echo JText::_( 'REFERENT_ESA_NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="referent_esa_name" id="referent_esa_name" size="32" maxlength="22" value="<?php echo htmlspecialchars($this->data->referent_esa_name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="active">
			<?php echo JText::_( 'ACTIVE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="active" id="active" size="32" maxlength="1" value="<?php echo htmlspecialchars($this->data->active, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="contact_type">
			<?php echo JText::_( 'CONTACT_TYPE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="contact_type" id="contact_type" size="32" maxlength="64" value="<?php echo htmlspecialchars($this->data->contact_type, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<!-- jcb code -->

		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_jcrm" />
<input type="hidden" name="id" value="<?php echo $this->data->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="jcrmcontacts" />
</form>
