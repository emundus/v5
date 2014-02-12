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
		<label for="name">
			<?php echo JText::_( 'NAME' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="name" id="name" size="32" maxlength="30" value="<?php echo htmlspecialchars($this->data->name, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="zone">
			<?php echo JText::_( 'ZONE' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="zone" id="zone" size="32" maxlength="11" value="<?php echo htmlspecialchars($this->data->zone, ENT_COMPAT, 'UTF-8');?>" />
	</td>
</tr>
<tr>
	<td width="100" align="right" class="key">
		<label for="continent">
			<?php echo JText::_( 'CONTINENT' ); ?>:
		</label>
	</td>
	<td>
		<input class="text_area" type="text" name="continent" id="continent" size="32" maxlength="28" value="<?php echo htmlspecialchars($this->data->continent, ENT_COMPAT, 'UTF-8');?>" />
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
<input type="hidden" name="controller" value="jcrmcountries" />
</form>
