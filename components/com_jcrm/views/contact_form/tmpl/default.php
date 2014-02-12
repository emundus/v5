<?php 
/**
 * Jcrm entry point file for jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 *
 * Déision Publique
 *
 */

 ?>
 
<div class="jcrm_edit_contact" id="jcrm_edit_contact">
<?php JHTML::stylesheet('autosuggest_inquisitor.css', 'components/com_fabrik/plugins/element/fabrikautocomplete/css/');
      JHTML::stylesheet('light2.css', 'templates/rt_afterburner_j15/css/');
      JHTML::script('bsn.AutoSuggest_2.1.3_comp.js', 'components/com_fabrik/plugins/element/fabrikautocomplete/js/', false); ?>
<?php $id_acct=$this->id_acct;$user_data=$this->user; ?>
<?php $dataItem=$this->data_cont; ?>
<form name="contactForm" method="post" onSubmit="return OnSubmitForm();" action="">
<?php if(!empty($dataItem)){echo "<b>".JText::_('EDIT_CONTACT')."</b>";}else{echo "<b>".JText::_('ADD_NEW_CONTACT')."</b>";} ?>
<fieldset name="personal_details" id="personal_details">
<legend><?php echo JText::_("PERSONAL_DETAILS"); ?></legend>
    <table>
	  <tr>
	  <th><p><?php echo JText::_("CIVILITY"); ?></th>
	  <th><input type="radio" id="salutation" name="salutation" value="Mr" <?php if(!empty($dataItem->salutation)&&$dataItem->salutation=='Mr'){echo JText::_('CHECKED');} ?>/><?php echo JText::_("MR"); ?>
	      <input type="radio" id="salutation" name="salutation" value="Miss" <?php if(!empty($dataItem->salutation)&&$dataItem->salutation=='Miss'){echo JText::_('CHECKED');} ?> /><?php echo JText::_("MISS"); ?>
		  <input type="radio" id="salutation" name="salutation" value="Mrs"<?php if(!empty($dataItem->salutation)&&$dataItem->salutation=='Mrs'){echo JText::_('CHECKED');} ?> /><?php echo JText::_("Mrs"); ?></th>
	  </tr>
      <tr>
	    <th>
		<input type="hidden" name="id" value="<?php echo $this->id_cont; ?>" />
	        <p><label class="information_contact"><?php echo JText::_("FIRST_NAME"); ?></label></th>
	    <th><input type="text" id="first_name" name="first_name" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"value="<?php if(!empty($dataItem->first_name)){echo $dataItem->first_name;} elseif(isset($this->fn)&&!empty($this->fn)){echo $this->fn;} ?>"></P>
		</th>
	 </tr>
	 <tr>
	   <th>
	    <p><label><?php echo JText::_("LAST_NAME"); ?></label>
	   </th>
	   <th>
	      <input type="text" id="last_name" name="last_name" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->last_name)){echo $dataItem->last_name;}elseif(isset($this->ln)&&!empty($this->ln)){echo $this->ln;} ?>"></p>
		</th>
		</tr>
		<tr>
	   <th>
	    <p><label><?php echo JText::_("TITRE"); ?></label>
	   </th>
	   <th>
	      <input type="text" id="title" name="title" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->title)){echo $dataItem->title;} ?>"></p>
		</th>
		</tr>
	<tr>
	    <th>
	        <p><label for="account_name"><?php echo JText::_("ACCOUNT_NAME"); ?></label>
	    </th>
	    <th>
	        <input type="text" id="account_name" name="account_name" size='50' onfocus="style.backgroundColor='#FFFF99';" onblur="document.getElementById('account_name_loader').style.display = 'none'; style.backgroundColor='white';"  value="<?php if(!empty($dataItem->account_name)){echo $dataItem->account_name;}elseif(isset($this->on)&&!empty($this->on)){echo $this->on;} ?>" >
	        <img id="account_name_loader" class="loader" style="padding-left: 10px; display: none;" alt="Loading" src="/media/com_fabrik/images/ajax-loader.gif">
			<a href="javascript:openModal('index.php?option=com_jcrm&view=organisation_form&tmpl=component')"><image src="media/com_jcrm/images/add_group.png" Title="<?php echo JText::_('ACCOUNT_NOT_EXIST'); ?>"></a></p>
	    </th>
	</tr>
	<tr>
	     <th>
	         <p><label><?php echo JText::_("TYPE"); ?></label>
	     </th>
	     <th>
	          <input type="radio" id="contact_type" name="contact_type" value="Universitaire" <?php if(!empty($dataItem->contact_type)&&$dataItem->contact_type=="Universitaire"){echo "checked";} ?>>
			  <?php echo JText::_("UNIVERSITAIRE"); ?>
			  <input type="radio" id="contact_type" name="contact_type" value="Non universitaire " <?php if(!empty($dataItem->contact_type)&&$dataItem->contact_type!="Universitaire"){echo "checked";} ?>>
			  <?php echo JText::_("NON_UNIVERSITAIRE"); ?>
			  </p>
	     </th>
	</tr>
	</table>
	</fieldset>
	<fieldset name="permanent_address" id="permanent_address">
	<legend><?php echo JText::_("PERMANENT_ADDRESS"); ?></legend>
	<table>
	<tr>
		<th>
	       <p><label><?php echo JText::_("EMAIL"); ?></label>
         </th>
		 <th>
		   <input type="text" id="email" name="email" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"  value="<?php if(!empty($dataItem->email)){echo $dataItem->email;}elseif(isset($this->email)&&!empty($this->email)){echo $this->email;} ?>"></p>
		  </th>
	</tr>
	<tr>
		<th>
	       <p><label><?php echo JText::_("MAILING"); ?></label>
         </th>
		 <th>
		   <input type="checkbox" id="mailing" name="mailing" value="1" <?php if(!empty($dataItem->mailing)&&$dataItem->mailing==1){echo 'checked';} ?>></p>
		  </th>
	</tr>
	<tr>
		  <th>
	        <p><label><?php echo JText::_("TELEPHONE"); ?></label>
	      </th>
		  <th>
	           <input type="text" id="phone_work" name="phone_work" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"  value="<?php if(!empty($dataItem->phone_work)){echo $dataItem->phone_work;} ?>"></p>
	     </th>
	</tr>
	<tr>
	     <th>
	        <p><label><?php echo JText::_("FAX"); ?></label>
	     </th>
	     <th>
	        <input type="text" id="phone_fax" name="phone_fax" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';"  value="<?php if(!empty($dataItem->phone_fax)){echo $dataItem->phone_fax;} ?>"></p>
	    </th>
	</tr>
	<tr>
	    <th>
	        <p><label><?php echo JText::_("DEPARTMENT"); ?></label>
	    </th>
	    <th>
	        <input type="text" id="department" name="department" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->department)){echo $dataItem->department;} ?>"></p>
	    </th>
	</tr>
	    <tr>
	    <th>
	       <p><label><?php echo JText::_("WEBSITE"); ?></label>
	    </th>
	    <th>
	        <input type="text" id="website" name="website" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->website)){echo $dataItem->website;} ?>"></p>
	    </th>
	</tr>
	 <tr>
	    <th>
	       <p><label><?php echo JText::_("COUNTRY"); ?></label>
	    </th>
	    <th>
	       <select name="country_code" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" >
		   <option value=""><?php echo JText::_("PLEASE_SELECT"); ?></option>
		   <?php foreach ($this->country as $countryItem){ ?>
		   <option  value="<?php echo $countryItem->iso2; ?>" <?php if(!empty($dataItem->country_code)&&$countryItem->iso2==$dataItem->country_code){echo "selected";} ?>>
		   <?php echo $countryItem->name_en; ?></option>
		   <?php } ?>
		   </select>
		   </p>
	    </th>
	</tr>
	<tr>
	    <th>
	       <p><label><?php echo JText::_("ADDRESS_STREET"); ?></label>
	    </th>
	    <th>
	        <textarea  id="primary_address_street" name="primary_address_street" cols="45" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" ><?php if(!empty($dataItem->primary_address_street)){echo $dataItem->primary_address_street;} ?></textarea></p>
	    </th>
	</tr>
	<tr>
	    <th>
	        <p><label><?php echo JText::_("STATE"); ?></label>
	    </th>
	    <th>
	         <input type="text" id="primary_address_state" name="primary_address_state" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->primary_address_state)){echo $dataItem->primary_address_state;} ?>"></p>
	    </th>
	</tr>
	<tr>
	    <th>
	       <p><label><?php echo JText::_("CITY"); ?></label>
	    </th>
	    <th>
	          <input type="text" id="primary_address_city" name="primary_address_city" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->primary_address_city)){echo $dataItem->primary_address_city;} ?>"></p>
	    </th>
	</tr>
	<tr>
	     <th>
	         <p><label><?php echo JText::_("POSTCODE"); ?></label>
	     </th>
	     <th>
	          <input type="text" id="primary_address_postalcode"  name="primary_address_postalcode" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->primary_address_postalcode)){echo $dataItem->primary_address_postalcode;} ?>"></p>
	     </th>
	</tr>
	</table>
	</fieldset>
<!--	<fieldset name="others" id="others">
	<legend><?php echo JText::_("OTHERS"); ?></legend>
	<table>
	<tr>
	     <th>
	         <p><label><?php echo JText::_("COMMENT"); ?></label>
	     </th>
	     <th>
	          <textarea id="comment"  name="comment" cols="45" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" ><?php if(!empty($dataItem->comment)){echo $dataItem->comment;} ?></textarea></p>
	     </th>
	</tr>
	
	<tr>
	     <th>
	         <p><label><?php echo JText::_("NAME_ESA_REFERENT"); ?></label>
	     </th>
	     <th>
	          <input type="text" id="referent_esa_name"  name="referent_esa_name" size="50" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($dataItem->referent_esa_name)){echo $dataItem->referent_esa_name;} ?>"></p>
	     </th>
	</tr>
	<tr>
		<th>
	       <p><label><?php echo JText::_("POSTGRADUTATE_REFERTENCE"); ?></label>
         </th>
		 <th>
		   <input type="checkbox" id="referent_postgradutate" name="referent_postgradutate" value="1" <?php if(!empty($dataItem->referent_postgradutate)&&$dataItem->referent_postgradutate==1){echo 'checked';} ?>></p>
		  </th>
	</tr>
	<tr>
		<th>
	       <p><label><?php echo JText::_("ACTIVE"); ?></label>
         </th>
		 <th>
		   <input type="checkbox" id="active" name="active" value="1" <?php if(!empty($dataItem->active)&&$dataItem->active=='1'){echo 'checked';} ?>></p>
		  </th>
	</tr>
</table>
</fieldset> -->
 <p><input type="hidden" id="account_id" name="account_id" value="<?php if(!empty($dataItem->account_id)){echo $dataItem->account_id;}else if(isset($this->id_account)&&!empty($this->id_account)){echo $this->id_account;} ?>"></p>
 <p><input type="hidden" id="date_modified" name="date_modified" value="<?php echo date("Y-m-d H:i:s"); ?>"></p>
 <p><input type="hidden" id="created_by" name="created_by" value="<?php  if(!empty($dataItem->created_by)){echo $dataItem->created_by;} else { echo $user_data->id;} ?>"></p>
 <p><input type="hidden" id="modified_user_id" name="modified_user_id" value="<?php echo $user_data->id; ?>"></p>
 
 
	<p><input type="submit" name="save" onClick="document.pressed=this.name" value="<?php if(empty($dataItem)){echo JText::_("SAVE");}else{echo JText::_("UPDATE");} ?>">
	
	   <input type="button" name="cancel"  onClick="redirect()" value="<?php echo JText::_('CANCEL'); ?>">
	  </p>
</form>	
	<script type="text/javascript">
	function OnSubmitForm() {
	var button_name=document.pressed.split("|");
	var id_account=document.getElementById("account_id").value
	var last_name=document.getElementById("last_name").value
	
	switch(button_name[0]) {
		case 'save': 
		
		if (id_account==""||id_account==null){
		alert("<?php echo JText::_('ACCOUNT_NOT_EXIST'); ?>" ); return false;
		} 
		else if(last_name==""){alert("<?php echo JText::_('LASTNAME_NOT_EMPTY'); ?>");return false;} 
		
		else{  
		document.contactForm.action ="index.php?option=com_jcrm&view=addressbook&task=save_contact&id_ref=<?php if(isset($this->id_ref)&&!empty($this->id_ref)){echo $this->id_ref;} ?>";
		}
		redirect();
		break;
		default: return false;
	}
	return true;
}


function redirect(){  
window.parent.document.getElementById( 'sbox-window' ).close();

}
function blur(){
document.getElementById("account_name_loader").style.display = "none";
}

function openModal(url){
			
	SqueezeBox.initialize({
            size: {x: 700, y: 400}
			});
			window.addEvent('domready', function() { 
			/* var orgName=getElementById("name").value;
			alert(orgName); */
			var dummylink = new Element('a', {
			href: url,
			rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window.innerHeight-innerWidth*0.05},onClose:function(){getName()}}"
			});
			
			SqueezeBox.fromElement(dummylink);
			});

}

function getName(){
		// location.reload();
		setTimeout("createxmlhrName()",1000);
		setTimeout("createxmlhrId()",1000);
}

function createxmlhrName(){
 var xmlhr;
		 if (window.XMLHttpRequest)
		{
			xmlhr=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
			xmlhr=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhr.onreadystatechange = function(){
			var orgname=document.getElementById('account_name');
			var orgid=document.getElementById('account_id');
			document.getElementById("account_name_loader").style.display = "";		
		    if(xmlhr.readyState == 4 && xmlhr.status == 200 ){
		    orgname.value=xmlhr.responseText;
			document.getElementById("account_name_loader").style.display = "none";
        }
		SqueezeBox.initialize({});
		$$('a.modal').each(function(el) {
		el.addEvent('click', function(e) {
		new Event(e).stop();
		SqueezeBox.fromElement(el);
			});
		});
		}
	    var $url="index.php?option=com_jcrm&view=contact_form&format=raw&task=maxOrg";
        xmlhr.open("GET", $url,true);
	    xmlhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
        xmlhr.send(null);	
 }
 function createxmlhrId(){
		var xmlhr;
		 if (window.XMLHttpRequest)
		{
			xmlhr=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
			xmlhr=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhr.onreadystatechange = function(){
			
			var orgid=document.getElementById('account_id');
			//document.getElementById("account_name_loader").style.display = "";		
		    if(xmlhr.readyState == 4 && xmlhr.status == 200 ){
		    orgid.value=xmlhr.responseText;
			document.getElementById("account_name_loader").style.display = "none";
        }
		SqueezeBox.initialize({});
		$$('a.modal').each(function(el) {
		el.addEvent('click', function(e) {
		new Event(e).stop();
		SqueezeBox.fromElement(el);
			});
		});
		}
	    var $url="index.php?option=com_jcrm&view=contact_form&format=raw&task=maxOrgid";
        xmlhr.open("GET", $url,true);
	    xmlhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
        xmlhr.send(null);	
 
 }
	
var myInput = document.getElementById("account_name");
			if(myInput.addEventListener ) {
				myInput.addEventListener("keydown",this.keyHandler,false);
			} else if(myInput.attachEvent ) {
				myInput.attachEvent("onkeydown",this.keyHandler); /* damn IE hack */
			}
			function keyHandler(e) {
				var TABKEY = 9;
				if(e.keyCode != TABKEY) {
					document.getElementById("account_id").value="";
					//document.getElementById("primary_address_city").value="";
				}
			}
			var options = {
				varname:"input",
				script: function(obj){
					search_value = document.getElementById("account_name").value;
					url ="index.php?option=com_jcrm&view=contact_form&task=listaccounts&format=raw&search_field=name&info_field=info&search_value="+search_value+"&input="+obj;
					var loader=document.getElementById("account_name_loader");
					loader.style.display="";
					return url;
					},
				json:true,
				timeout:10000,
				shownoresults:true,
				maxresults:6,
				callback: function (obj) { 
					document.getElementById("account_id").value = obj.id;
					
					var nameAcct=obj.value.split('->');
					document.getElementById("account_name").value = nameAcct[0];
					
					var reg=new RegExp("[,]+", "g");
					val = obj.info;
					var cname=val.split(reg);

					if (typeof cname[0] != "undefined")
						document.getElementById("primary_address_postalcode").value = cname[0];
						
					if (typeof cname[1] != "undefined")
						document.getElementById("primary_address_city").value = cname[1];
						
					if (typeof cname[2] != "undefined")
						document.getElementById("primary_address_state").value = cname[2];
					
					document.getElementById("account_name_loader").style.display = "none";
				}
				 
			};
			 
			var as_json = new bsn.AutoSuggest("account_name", options);
			
		</script>

</div>