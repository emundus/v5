<?php 
/**
 * Jcrm entry point file for jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Décision Publique
 *
 */
?>

<div class="jcrm_edit_account" id="jcrm_edit_account">
<?php 
$dataItem=empty($this->data_acct)?$this->data_references:$this->data_acct; 


$user_data=$this->user; 
$acctname=$this->accountname;
$countryjcrm=$this->countryjcrm; 



if(!empty($dataItem)){echo"<b>".JText::_('EDIT_ORGANISATION')."</b>";}else{echo"<b>".JText::_('ADD_NEW_ORGANISATION')."</b>";}
?>

<form name="organisationForm" onSubmit="return OnSubmitForm();" method="post" action="">
<fieldset name="personal_details" id="personal_details">
<legend><?php echo JText::_('GENERAL_INFORMATION'); ?></legend>
    <table>
      <tr>
	    <th>
	        <p><label><?php echo JText::_('NAME_ORGANISATION'); ?><label>
		</th>
	    <th><input type="text" name="name" id="name" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->name)){echo $dataItem->name;}if(isset($this->name)&&!empty($this->name)){echo $this->name;} ?>"></P>
		</th>
	 </tr>
	  <tr>
	    <th>
	        <p><label><?php echo JText::_('PARENT_ORGANISATION'); ?><label>
		</th>
	    <th><input type="text" name="parent_organisation" id="parent_organisation"  size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="document.getElementById('account_name_loader').style.display = 'none';style.backgroundColor='white';" value="<?php if(!empty($dataItem->parent_id)&&!empty($acctname->name)){echo $acctname->name;} ?>">
		<img id="account_name_loader" class="loader" style="padding-left: 10px; display: none;" alt="Loading" src="/media/com_fabrik/images/ajax-loader.gif"></P>
		</th>
	 </tr>
	  <tr>
	    <th>
	        <p><label><?php echo JText::_('NAME_LOGO'); ?><label>
		</th>
	    <th><input type="text" name="logo_name" id="logo_name" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->logo_name)){echo $dataItem->logo_name;} ?>"></P>
		</th>
	 </tr>
	 <tr>
	    <th>
	        <p><label><?php echo JText::_('TYPE'); ?><label>
		</th>
	    <th><select name="account_type" id="account_type" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';">
		    <option value=''><?php echo JText::_('PLEASE_SELECT'); ?></option>
			<option <?php if(!empty($dataItem->account_type)&&$dataItem->account_type==JText::_('UNIVERSITE')){echo"selected";}?> value="<?php echo JText::_('UNIVERSITE'); ?>"><?php echo JText::_('UNIVERSITE'); ?></option>
			<option <?php if(!empty($dataItem->account_type)&&$dataItem->account_type==JText::_('FACULTE')){echo"selected";}?> value="<?php echo JText::_('FACULTE'); ?>"><?php echo JText::_('FACULTE'); ?></option>
			<option <?php if(!empty($dataItem->account_type)&&$dataItem->account_type==JText::_('DEPARTEMENT')){echo"selected";} ?> value="<?php echo JText::_('DEPARTEMENT'); ?>"><?php echo JText::_('DEPARTEMENT'); ?></option>
			<option <?php if(!empty($dataItem->account_type)&&$dataItem->account_type==JText::_('SERVICE')){echo"selected";}?> value="<?php echo JText::_('SERVICE'); ?>"><?php echo JText::_('SERVICE'); ?></option>
			<option <?php if(!empty($dataItem->account_type)&&$dataItem->account_type==JText::_('INSTITUTION')){echo"selected";}?> value="<?php echo JText::_('INSTITUTION'); ?>"><?php echo JText::_('INSTITUTION'); ?></option>			
			<option <?php if(!empty($dataItem->account_type)&&($dataItem->account_type==JText::_('PUBLIQUE')||$dataItem->account_type==JText::_('PUBLIQUE'))){echo"selected";}?> value="<?php echo JText::_('PUBLIQUE'); ?>"><?php echo JText::_('PUBLIQUE'); ?></option>
			<option <?php if(!empty($dataItem->account_type)&&($dataItem->account_type==JText::_('PRIVEE')||$dataItem->account_type==JText::_('PRIVEE'))){echo"selected";}?> value="<?php echo JText::_('PRIVEE'); ?>"><?php echo JText::_('PRIVEE'); ?></option>
		     
			 
		    </select>
			</P>
		</th>
	 </tr>
	 </table>
	 </fieldset>
	 <fieldset name="speciality_details" id="speciality_details">
	 <legend><?php echo JText::_('SPECIALITY_DETAILS'); ?></legend>
	 <table>
	 <tr>
	<th>
	<p><label><?php echo JText::_('SPECIALITY'); ?></label>
	</th>
	<th>
	<input type="text" name="account_speciality" id="account_speciality"  size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->account_speciality)){echo $dataItem->account_speciality;} ?>"></p>
	</th>
	</tr>
	 <tr>
	<th>
	<p><label><?php echo JText::_('COURS'); ?></label>
	</th>
	<th>
	<textarea name="cours_list"id="cours_list" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->cours_list)){echo $dataItem->cours_list;} ?></textarea></p>
	</th>
	</tr>
	 <tr>
	<th>
	<p><label><?php echo JText::_('DEGREES'); ?></label>
	</th>
	<th>
	<textarea name="degrees_list"id="degrees_list" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->degrees_list)){echo $dataItem->degrees_list;}  ?></textarea></p>
	</th>
	</tr>
	 <tr>
	<th>
	<p><label><?php echo JText::_('RESEARCH_AREAS'); ?></label>
	</th>
	<th>
	<textarea name="research_areas_list" id="research_areas_list" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->research_areas_list)){echo $dataItem->research_areas_list;} ?></textarea></p>
	</th>
	</tr>
	</table>
	</fieldset>
	<fieldset name="permanent_address" id="permanent_address">
	<legend><?php echo JText::_('PERMANENT_ADDRESS'); ?></legend>
	<table>
	<tr>
	    <th>
	       <p><label><?php echo JText::_('COUNTRY'); ?></label>
	    </th>
	    <th>
	       <input name="address_country" id="address_country" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="document.getElementById('account_name_loader_2').style.display = 'none'; style.backgroundColor='white';" value="<?php if(!empty($dataItem->address_country)){echo $dataItem->address_country;} ?>">
		   <img id="account_name_loader_2" class="loader" style="padding-left: 10px; display: none;" alt="Loading" src="/media/com_fabrik/images/ajax-loader.gif">
		   </p>
	    </th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('STATE'); ?></label>
	</th>
	<th>
	<input type="text" name="address_state" id="address_state" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->address_state)){echo $dataItem->address_state;} ?>"></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('CITY'); ?></label>
	</th>
	<th>
	<input type="text" name="address_city" id="address_city" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->address_city)){echo $dataItem->address_city;} ?>"></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('ADDRESS_STREET'); ?></label>
	</th>
	<th>
	<textarea  name="address_street" id="address_street" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" cols=35 ><?php if(!empty($dataItem->address_street)){echo $dataItem->address_street;} ?></textarea></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('ADDRESS_STREET_ADDITION'); ?></label>
	</th>
	<th>
	<textarea  name="address_street_2" id="address_street_2" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" cols=35><?php if(!empty($dataItem->address_street_2)){echo $dataItem->address_street_2;} ?></textarea></p>
	</th>
	</tr>
	<tr>
		<th>
	       <p><label><?php echo JText::_('TELEPHONE'); ?></label>
         </th>
		 <th>
		   <input type="text" name="phone_account" id="phone_account" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" size="40" value="<?php if(!empty($dataItem->phone_account)){echo $dataItem->phone_account;} ?>"></p>
		   </th>
		   </tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('FAX'); ?></label>
	</th>
	<th>
	<input type="text" name="phone_fax" id="phone_fax"  size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->phone_fax)){echo $dataItem->phone_fax;} ?>"></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('POSTCODE'); ?></label>
	</th>
	<th>
	<input type="text" name="address_postalcode" id="address_postalcode" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->address_postalcode)){echo $dataItem->address_postalcode;} ?>"></p>
	</th>
	</tr>
	 <tr>
	 <td>
<p><label><?php echo JText::_('WEBSITE'); ?></label>
     </td>
	 <td> <input type="input" name="website" id="website" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->website)){echo $dataItem->website;} ?>"/></p>
	 </td>
	 </tr>
	  <tr>
	 <td>
<p><label><?php echo JText::_('LOCATION'); ?></label>
     </td>
	 <td><input type="input" name="location" id="location" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->location)){echo $dataItem->location;} ?>"/></p>
	 </td>
	 </tr>
	 </table>
	 </fieldset>
	 <fieldset name="contact_detail" id="contact_detail">
	 <legend><?php echo JText::_('CONTACT_DETAILS'); ?></legend>
	 <table>
	 <tr>
	 <td>
<p><label><?php echo JText::_('NAME_DIRECTOR'); ?></label>
     </td>
	 <td><input type="input" name="director_name"id="director_name"  size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->director_name)){echo $dataItem->director_name;} ?>"/></p>
	 </td>
	 </tr>
	 <tr>
	 <td>
<p><label><?php echo JText::_('EMAIL_DIRECTOR'); ?> </label>
     </td>
	 <td>
	<input type="input" name="director_email" id="director_email" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->director_email)){echo $dataItem->director_email;} ?>"/></p>
	 </td>
	 </tr>
	 
	 </table>
	 </fieldset>
	 <fieldset name="situation_details" id="situation_details">
	 <legend><?php echo JText::_('SITUATION_DETAILS'); ?></legend>
	 <table>
	 <tr>
	<th>
	<p><label><?php echo JText::_('ECONOMIC_INFORMATION'); ?></label>
	</th>
	<th>
	<textarea  name="economic_information" id="economic_information" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->economic_information)){echo $dataItem->economic_information;} ?></textarea></p>
	</th>
	</tr>
	 <tr>
	 <td>
<p><label><?php echo JText::_('ANNUAL_APPROPRIATIONS'); ?></label>
     </td>
	 <td><input type="input" name="annual_appropriations" id="annual_appropriations" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->annual_appropriations)){echo $dataItem->annual_appropriations;} ?>"/></p>
	 </td>
	 </tr>
	  <tr>
	 <td>
<p><label><?php echo JText::_('NUMBER_STUDENT_PLACES'); ?></label>
     </td>
	 <td><input type="input" name="number_student_places" id="number_student_places" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->number_student_places)){echo $dataItem->number_student_places;} ?>"/></p>
	 </td>
	 </tr>
	  <tr>
	 <td>
<p><label><?php echo JText::_('NUMBER_STUDENTS'); ?></label>
     </td>
	 <td><input type="input" name="number_students" id="number_students" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->number_students)){echo $dataItem->number_students;} ?>"/></p>
	 </td>
	 </tr>
	  <tr>
	 <td>
<p><label><?php echo JText::_('CODE_ORGANISATION'); ?></label>
     </td>
	 <td><input type="input" name="code_account" id="code_account" size="40" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->code_account)){echo $dataItem->code_account;} ?>"/></p>
	 </td>
	 </tr>
	  <tr>
	<th>
	<p><label><?php echo JText::_('FACULTIES'); ?></label>
	</th>
	<th>
	<textarea  name="faculties_list" id="faculties_list" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->faculties_list)){echo $dataItem->faculties_list;} ?></textarea></p>
	</th>
	</tr>
	  <tr>
	<th>
	<p><label><?php echo JText::_('AREAS_EXCELLENCE'); ?></label>
	</th>
	<th>
	<textarea  name="areas_of_excellence" id="areas_of_excellence" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35 ><?php if(!empty($dataItem->areas_of_excellence )){echo $dataItem->areas_of_excellence;} ?></textarea></p>
	</th>
	</tr>
	 <tr>
	<th>
	<p><label><?php echo JText::_('CAMPUS_INFORMATIONS'); ?></label>
	</th>
	<th>
	<textarea  name="campus_info" id="campus_info" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->campus_info)){echo $dataItem->campus_info ;}  ?></textarea></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('AGREEMENTS_LIST'); ?></label>
	</th>
	<th>
	<textarea  name="agreements_list" id="agreements_list" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->agreements_list)){echo $dataItem->agreements_list ;} ?></textarea></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('PRACTICAL_INFORMATION'); ?></label>
	</th>
	<th>
	<textarea  name="practical_info" id="practical_info" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->practical_info)){echo $dataItem->practical_info ;} ?></textarea></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('COMMENT'); ?></label>
	</th>
	<th>
	<textarea  name="comment" id="comment" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"cols=35><?php if(!empty($dataItem->comment)){echo $dataItem->comment;} ?></textarea></p>
	</th>
	</tr>
	<tr>
	<th>
	<p><label><?php echo JText::_('PARTNER_ESA'); ?></label>
	</th>
	<th>
	<input  type="checkbox" name="partner_esa" id="partner_esa" value="1" 
	<?php if(!empty($dataItem->partner_esa)&&$dataItem->partner_esa=='1'){echo"checked";} ?> ></p>
	</th>
	</tr>
	</table>
	</fieldset>
	<input type="hidden" name="id" value="<?php echo $this->id_acct; ?>" />
	<p><input type="hidden" id="date_modified" name="date_modified" value="<?php echo date("Y-m-d H:i:s"); ?>"></p>
	<p><input type="hidden" name="parent_id" id="parent_id"  size="40" value="<?php if(!empty($dataItem->parent_id )){echo $dataItem->parent_id;} ?>"></P>
	<p><input type="hidden" name="country_id" id="country_id"  size="40" value="<?php if(!empty($dataItem->country_id )){echo $dataItem->country_id;} ?>"></P>
	<p><input type="hidden" id="created_by" name="created_by" value="<?php  if(!empty($dataItem->created_by)){echo $dataItem->created_by;} else { echo $user_data->id;} ?>"></p>
    <p><input type="hidden" id="modified_user_id" name="modified_user_id" value="<?php  echo $user_data->id; ?>"></p>
	
	
	<p><input type="submit" name="save" onClick="document.pressed=this.name" value="<?php if(empty($dataItem)){echo JText::_("SAVE");}else{echo JText::_("UPDATE");} ?>">
	<input type="button"  name="cancel" onClick="redirect()" value="<?php echo JText::_("CANCEL"); ?>">
	</p>
</form>		
</div>

<script type='text/javascript'>
function OnSubmitForm() {
	var button_name=document.pressed.split("|");
	switch(button_name[0]) {
		case 'save': 
		var name=document.getElementById('name').value;
		if(name==""){
		alert("<?php echo JText::_('NAME_EMPTY'); ?>");
		return false;}
		else{
			<?php if(!empty($dataItem->id)){ ?>
			document.organisationForm.action ="index.php?option=com_jcrm&view=addressbook&task=save_account&id_acct=<?php echo $dataItem->id; ?>";
			<?php }else{ ?>
			document.organisationForm.action ="index.php?option=com_jcrm&view=addressbook&task=save_account&id_ref=<?php if(isset($this->id_ref)&&!empty($this->id_ref)){echo $this->id_ref;} ?>";
			<?php }  ?>
			redirect();
		break;}
		default: return false;
	}
	return true;
} 

function redirect(){  
window.parent.document.getElementById( 'sbox-window' ).close();
}

var myInput = document.getElementById("parent_organisation");
			if(myInput.addEventListener ) {
				myInput.addEventListener("keydown",this.keyHandlerName,false);
			} else if(myInput.attachEvent ) {
				myInput.attachEvent("onkeydown",this.keyHandlerName); /* damn IE hack */
			}
			function keyHandlerName(e) {
				var TABKEY = 9;
				if(e.keyCode != TABKEY) {
				document.getElementById("parent_id").value="";
				}
			}
			
			var options = {
				varname:"input",
				script: function(obj){
					search_value = document.getElementById("parent_organisation").value;
					url ="index.php?option=com_jcrm&view=organisation_form&task=listaccounts&format=raw&search_field=name&info_field=info&search_value="+search_value+"&input="+obj;
					document.getElementById("account_name_loader").style.display="";
					return url;
					},
				json:true,
				timeout:10000,
				shownoresults:true,
				maxresults:6,
				callback: function (obj) { 
					document.getElementById("parent_id").value = obj.id;
					
					var nameAcct=obj.value.split('->');
					document.getElementById("parent_organisation").value = nameAcct[0];
					
					var reg=new RegExp("[,]+", "g");
					val = obj.info;
					var cname=val.split(reg);
					if (typeof cname[0] != "undefined")
						document.getElementById("address_postalcode").value = cname[0];
					if (typeof cname[1] != "undefined")
						document.getElementById("address_city").value = cname[1];
					if (typeof cname[2] != "undefined")
						document.getElementById("address_state").value = cname[2];
					
					document.getElementById("account_name_loader").style.display = "none";
				}
			};
			var as_json = new bsn.AutoSuggest("parent_organisation", options);
	
var myInputcountry = document.getElementById("address_country");
			if(myInputcountry.addEventListener ) {
				myInputcountry.addEventListener("keydown",this.keyHandler,false);
			} else if(myInputcountry.attachEvent ) {
				myInputcountry.attachEvent("onkeydown",this.keyHandler); /* damn IE hack */
			}
			function keyHandler(e) {
				var TABKEY = 9;
				if(e.keyCode != TABKEY) {
					document.getElementById("country_id").value="";
					
				}
			}
			var options = {
				varname:"input",
				script: function(obj){
					
					url ="index.php?option=com_jcrm&view=organisation_form&format=raw&task=listcountries&search_field=name_en&input="+obj;
					document.getElementById("account_name_loader_2").style.display="";
					return url;
					},
				json:true,
				timeout:10000,
				shownoresults:true,
				maxresults:6,
				callback: function (obj) { 
					document.getElementById("country_id").value = obj.id;
					document.getElementById("account_name_loader_2").style.display = "none";
				}
			};
			var as_json = new bsn.AutoSuggest("address_country", options);
</script>

