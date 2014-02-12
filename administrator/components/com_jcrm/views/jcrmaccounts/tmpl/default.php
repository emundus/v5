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
		<label for="parent_id">
			<?php echo JText::_( 'PARENT_ID' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="parent_id" id="parent_id" size="32" maxlength="12" value="<?php echo htmlspecialchars($this->data->parent_id, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
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
		<input class="text_area" type="text" name="state" id="state" size="32" maxlength="4" value="<?php echo htmlspecialchars($this->data->state, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="country_id">
			<?php echo JText::_( 'COUNTRY_ID' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="country_id" id="country_id" size="32" maxlength="3" value="<?php echo htmlspecialchars($this->data->country_id, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="name">
			<?php echo JText::_( 'NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="name" id="name" size="32" maxlength="256" value="<?php echo htmlspecialchars($this->data->name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="logo_name">
			<?php echo JText::_( 'LOGO_NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="logo_name" id="logo_name" size="32" maxlength="24" value="<?php echo htmlspecialchars($this->data->logo_name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="phone_fax">
			<?php echo JText::_( 'PHONE_FAX' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="phone_fax" id="phone_fax" size="32" maxlength="32" value="<?php echo htmlspecialchars($this->data->phone_fax, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="phone_account">
			<?php echo JText::_( 'PHONE_ACCOUNT' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="phone_account" id="phone_account" size="32" maxlength="32" value="<?php echo htmlspecialchars($this->data->phone_account, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="account_type">
			<?php echo JText::_( 'ACCOUNT_TYPE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="account_type" id="account_type" size="32" maxlength="128" value="<?php echo htmlspecialchars($this->data->account_type, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="account_speciality">
			<?php echo JText::_( 'ACCOUNT_SPECIALITY' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="account_speciality" id="account_speciality" size="32" maxlength="256" value="<?php echo htmlspecialchars($this->data->account_speciality, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="cours_list">
			<?php echo JText::_( 'COURS_LIST' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="cours_list" id="cours_list" cols="80" rows="10"><?php echo htmlspecialchars($this->data->cours_list, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="degrees_list">
			<?php echo JText::_( 'DEGREES_LIST' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="degrees_list" id="degrees_list" cols="80" rows="10"><?php echo htmlspecialchars($this->data->degrees_list, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="research_areas_list">
			<?php echo JText::_( 'RESEARCH_AREAS_LIST' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="research_areas_list" id="research_areas_list" cols="80" rows="10"><?php echo htmlspecialchars($this->data->research_areas_list, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="annual_appropriations">
			<?php echo JText::_( 'ANNUAL_APPROPRIATIONS' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="annual_appropriations" id="annual_appropriations" size="32" maxlength="4" value="<?php echo htmlspecialchars($this->data->annual_appropriations, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="address_street">
			<?php echo JText::_( 'ADDRESS_STREET' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="address_street" id="address_street" size="32" maxlength="512" value="<?php echo htmlspecialchars($this->data->address_street, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="address_street_2">
			<?php echo JText::_( 'ADDRESS_STREET_2' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="address_street_2" id="address_street_2" size="32" maxlength="256" value="<?php echo htmlspecialchars($this->data->address_street_2, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="address_postalcode">
			<?php echo JText::_( 'ADDRESS_POSTALCODE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="address_postalcode" id="address_postalcode" size="32" maxlength="24" value="<?php echo htmlspecialchars($this->data->address_postalcode, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="address_city">
			<?php echo JText::_( 'ADDRESS_CITY' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="address_city" id="address_city" size="32" maxlength="56" value="<?php echo htmlspecialchars($this->data->address_city, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="address_state">
			<?php echo JText::_( 'ADDRESS_STATE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="address_state" id="address_state" size="32" maxlength="56" value="<?php echo htmlspecialchars($this->data->address_state, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="address_country">
			<?php echo JText::_( 'ADDRESS_COUNTRY' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="address_country" id="address_country" size="32" maxlength="128" value="<?php echo htmlspecialchars($this->data->address_country, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="website">
			<?php echo JText::_( 'WEBSITE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="website" id="website" size="32" maxlength="256" value="<?php echo htmlspecialchars($this->data->website, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="director_name">
			<?php echo JText::_( 'DIRECTOR_NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="director_name" id="director_name" size="32" maxlength="256" value="<?php echo htmlspecialchars($this->data->director_name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="director_email">
			<?php echo JText::_( 'DIRECTOR_EMAIL' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="director_email" id="director_email" size="32" maxlength="139" value="<?php echo htmlspecialchars($this->data->director_email, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="location">
			<?php echo JText::_( 'LOCATION' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="location" id="location" size="32" maxlength="136" value="<?php echo htmlspecialchars($this->data->location, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="economic_information">
			<?php echo JText::_( 'ECONOMIC_INFORMATION' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="economic_information" id="economic_information" size="32" maxlength="212" value="<?php echo htmlspecialchars($this->data->economic_information, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="number_student_places">
			<?php echo JText::_( 'NUMBER_STUDENT_PLACES' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="number_student_places" id="number_student_places" size="32" maxlength="3" value="<?php echo htmlspecialchars($this->data->number_student_places, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="number_students">
			<?php echo JText::_( 'NUMBER_STUDENTS' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="number_students" id="number_students" size="32" maxlength="6" value="<?php echo htmlspecialchars($this->data->number_students, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="code_account">
			<?php echo JText::_( 'CODE_ACCOUNT' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="code_account" id="code_account" size="32" maxlength="13" value="<?php echo htmlspecialchars($this->data->code_account, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="faculties_list">
			<?php echo JText::_( 'FACULTIES_LIST' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="faculties_list" id="faculties_list" size="32" maxlength="269" value="<?php echo htmlspecialchars($this->data->faculties_list, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="areas_of_excellence">
			<?php echo JText::_( 'AREAS_OF_EXCELLENCE' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="areas_of_excellence" id="areas_of_excellence" cols="80" rows="10"><?php echo htmlspecialchars($this->data->areas_of_excellence, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="campus_info">
			<?php echo JText::_( 'CAMPUS_INFO' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="campus_info" id="campus_info" cols="80" rows="10"><?php echo htmlspecialchars($this->data->campus_info, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="agreements_list">
			<?php echo JText::_( 'AGREEMENTS_LIST' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="agreements_list" id="agreements_list" cols="80" rows="10"><?php echo htmlspecialchars($this->data->agreements_list, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="practical_info">
			<?php echo JText::_( 'PRACTICAL_INFO' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="practical_info" id="practical_info" cols="80" rows="10"><?php echo htmlspecialchars($this->data->practical_info, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="comment">
			<?php echo JText::_( 'COMMENT' ); ?>:
		</label>
	</td>
	<td>
		<textarea class="text_area" name="comment" id="comment" cols="80" rows="10"><?php echo htmlspecialchars($this->data->comment, ENT_COMPAT, 'UTF-8');?></textarea>
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="partner_esa">
			<?php echo JText::_( 'PARTNER_ESA' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="partner_esa" id="partner_esa" size="32" maxlength="1" value="<?php echo htmlspecialchars($this->data->partner_esa, ENT_COMPAT, 'UTF-8');?>" />
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
<input type="hidden" name="controller" value="jcrmaccounts" />
</form>
