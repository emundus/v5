<?php
/**
 * Jcrm View for com_jcrm Component
 * 
 * @package    Joomla
 * @subpackage Jcrm
 * @license  GNU/GPL v2
 * Décision Publique http://www.decisionpublique.fr
 *
 */
 
defined('_JEXEC') or die('Restricted access'); 
JHTML::_('behavior.mootools');	
JHTML::stylesheet('jcrm.css', 'media/com_jcrm/css/');
JHTML::_('behavior.modal');

$document = JFactory::getDocument();
$ls = JRequest::getVar('limitstart',0);
$page=JRequest::getVar('limitstart',0,'get'); ?>


 <div class="liste_contacts">
 
<form name="adminForm" method="post" action="">
<input type="hidden" name="limitstart" value="<?php echo $page; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="search" value="<?php if(!empty($this->lists['search'])){ echo $this->lists['search'];} ?>" />
<input type="hidden" name="search_option" value="<?php if(!empty($this->lists['search_option'])){ echo $this->lists['search_option'];} ?>" />
<br><input type="button" name="check" value="<?php echo JText::_('CHECK_CONTACTS'); ?>" onclick="checkContacts()">
<input type="button" name="ignore_conatcts" value="<?php echo JText::_('IGNORE_CONTACTS'); ?>" onclick="ignoreContacts()">
<br><br>
<fieldset>
	<legend><img alt="" src="media/com_emundus/images/icones/folder_documents.png"><?php echo JText::_("SYNCHRONISE_REFERENCES_LIST"); ?></legend>
	<fieldset>
		<legend><img src="media/com_emundus/images/icones/viewmag_22x22.png"><?php echo JText::_("FILTER"); ?></legend>
		<table class="table_search">
			<tr>
				<td>
					<select id="search_option" name="search_option">
						<option value=""><?php echo JText::_("ALL"); ?></option>
						<option value="first_name" <?php if($this->lists["search_option"]=="first_name"){echo "selected";} ?> ><?php echo JText::_("FIRST_NAME"); ?></option>
						<option value="last_name" <?php if($this->lists["search_option"]=="last_name"){echo "selected";} ?>><?php echo JText::_("LAST_NAME"); ?></option>
						<option value="email" <?php if($this->lists["search_option"]=="email"){echo "selected";} ?>><?php echo JText::_("EMAIL"); ?></option>
						<option value="organisation" <?php if($this->lists["search_option"]=="organisation"){echo "selected";} ?>><?php echo JText::_("ORGANISATION"); ?></option>
					</select>
				</td>
				<td><input type="text" size="35" id="search" name="search" placeholder="<?php echo JText::_('NAME'); ?>" value="<?php if(!empty($this->lists['search'])){ echo $this->lists['search'];} ?>" /></td>
				<td><input type="image" src="media/com_jcrm/images/search_button.png" class="search" onclick="this.form.submit();" title="<?php echo JText::_('SEARCH'); ?>"></td> 
				<td><input type="image" src="media/com_jcrm/images/reset.jpg" class="reset" onclick="document.getElementById('search').value='';document.getElementById('search_option').value='';this.form.submit()" title="<?php echo JText::_('RESET'); ?>" /></td>
			</tr>
		</table>
	</fieldset>
	<br>
	<table class='jcrm_check_contacts' id="userlist">
	<thead>
		<tr>
	    	<td align="center" colspan="3">
	    		<?php echo $this->pagination->getResultsCounter(); ?>
	    	</td>
    	</tr>
		<tr>
			<th width=80><input type="checkbox" name="toggle" title="<?php echo JText::_('CHECK_ALL'); ?>" onclick="checkAll(<?php echo count($this->data_contacts); ?>)"><?php echo JText::_("ALL"); ?></th>
			<th><?php echo "<b>".JHTML::_('grid.sort',JText::_('NAME'),'last_name',$this->lists['order_Dir'], $this->lists['order'])."</b>"; ?></th>
			<th><?php echo "<b>".JHTML::_('grid.sort',JText::_('ORGANISATION'),'organisation',$this->lists['order_Dir'], $this->lists['order'])."</b>" ; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php  if(empty($this->data_contacts[0])){ ?>
		<tr>
			<td colspan="3" style="color:red;"><p style="font-size:20px"><?php echo JText::_("NO_RESULT"); ?></p>
			</td>
		</tr><?php } else{ ?>
		<?php
			$k = 0;
			for ($i=0, $n=count( $this->data_contacts); $i < $n; $i++)	{
				$data_item = & $this->data_contacts[$i];
				$checked = JHTML::_('grid.id', $i, $data_item['id']);
			?>
		<tr class="<?php  echo $i%2==0?"row0":"row1"; ?>">
			<td> <?php echo $i+$ls+1; echo $checked; ?>
				&nbsp;<img src="media/com_jcrm/images/synch.jpg" title='<?php echo JText::_("CHECK_CONTACT"); ?>' width=20 height=20 onclick='checkContact("<?php echo $i; ?>")'>
				&nbsp;<img src="media/com_jcrm/images/ignore.png" title="<?php echo JText::_('IGNORE_CONTACT'); ?>" onclick='ignoreCont("<?php echo $data_item['id']; ?>")'>
			</td>
			<td style="font-size:13px">
				<label id="cont_last_name_<?php echo $data_item['id']; ?>"> 
					<?php  echo "<b>".strtoupper($data_item['last_name'])."</b>"; ?>
				</label>
				<label id="cont_first_name_<?php echo $data_item['id']; ?>"> 
					<?php  echo "<br>";echo $data_item['first_name']; ?>
				</label>
				<label id="cont_email_<?php echo $data_item['id']; ?>"> 
					<?php echo "<br>";echo "<i>".$data_item['email']."</i>"; ?>
				</label>
				<div id="id_ref_cont_<?php echo $i ; ?>"></div>
			</td>
			<td style="font-size:13px">
				<label id="name_orega_<?php echo $data_item['id']; ?>"> 
					<h3><?php echo $data_item['organisation']; ?></h3>
				</label>
				<input type="hidden" name="id_account" id="id_orga_<?php echo $data_item['id']; ?>" value="<?php echo $data_item['id_account']; ?>">
				<div id="id_ref_col_<?php echo $i ; ?>"></div>
			</td>
		</tr>
		<?php   $k = 1 - $k;
			}  } ?>
	</tbody>
	<tfoot class="pagination"><tr><td colspan='3'><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
	</table>
</fieldset>
	 <input type="hidden" name="boxchecked" id="boxchecked" value="0" /><br>
</form>
 </div> 
 <script type="text/javascript">
 
 function tableOrdering( order, dir, task ) {
  var form = document.adminForm;
  form.filter_order.value = order;
  form.filter_order_Dir.value = dir;
  document.adminForm.submit( task );
}
 
 function createxmlhr(i){
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
			var orga=document.getElementById('id_ref_col_'+i);		
		    orga.innerHTML="<p><img src='media/com_jcrm/images/waiting.gif' width='20' height='20'/></p>";		
		    if(xmlhr.readyState == 4 && xmlhr.status == 200 ){
		    orga.innerHTML=xmlhr.responseText;		
        }
		
		SqueezeBox.initialize({});
 
$$('a.modal').each(function(el) {
el.addEvent('click', function(e) {
	new Event(e).stop();
	SqueezeBox.fromElement(el);
	});
});
		}
		var id=document.getElementById('cb'+i).value
		var name_org=document.getElementById("name_orega_"+id).innerHTML;
	    
	    var $url="index.php?option=com_jcrm&view=check_contacts&format=raw&controller=check&task=check&i="+i+"&cid[]="+id+"&name_org="+name_org;
        xmlhr.open("GET", $url,true);
	    xmlhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
        xmlhr.send(null);	
 }
 
 function createxmlhrOrg(i,id_org){
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
			var orga=document.getElementById('id_ref_col_'+i);		
		    orga.innerHTML="<p><img src='media/com_jcrm/images/waiting.gif' width='20' height='20'/></p>";		
		    if(xmlhr.readyState == 4 && xmlhr.status == 200 ){
		    orga.innerHTML=xmlhr.responseText;		
        }
		
		SqueezeBox.initialize({});
 
$$('a.modal').each(function(el) {
el.addEvent('click', function(e) {
	new Event(e).stop();
	SqueezeBox.fromElement(el);
	});
});
		}
		var id=document.getElementById('cb'+i).value
		var name_org=document.getElementById("name_orega_"+id).innerHTML;
	    
	    var $url="index.php?option=com_jcrm&view=check_contacts&format=raw&controller=check&task=check&cid[]="+id+"&name_org="+name_org+"&id_acct="+id_org;
        xmlhr.open("GET", $url,true);
	    xmlhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
        xmlhr.send(null);
 
 
 }
 
 function createxmlhttp(i){
 var xmlhttp;
		 if (window.XMLHttpRequest)
		{
			xmlhttp=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function(){
			var contact=document.getElementById('id_ref_cont_'+i);
			contact.innerHTML="<p><img src='media/com_jcrm/images/waiting.gif' width='20' height='20'/></p>";
		    if(xmlhttp.readyState == 4 && xmlhttp.status == 200 ){
			contact.innerHTML=xmlhttp.responseText;
        }
		
		SqueezeBox.initialize({});
		$$('a.modal').each(function(el) {
		el.addEvent('click', function(e) {
		new Event(e).stop();
		SqueezeBox.fromElement(el);
		});
		});
		
	}
		var id=document.getElementById('cb'+i).value;
		var first_name=document.getElementById("cont_first_name_"+id).innerHTML;
	    var last_name=document.getElementById("cont_last_name_"+id).innerHTML;
	    var email=document.getElementById("cont_email_"+id).innerHTML;
	    var org_name=document.getElementById("name_orega_"+id).innerHTML;
	    
	    var $url="index.php?option=com_jcrm&view=check_contacts&format=raw&controller=check&task=check_cont&i="+i+"&cid[]="+id+"&fn="+first_name+"&ln="+last_name+"&email="+email+"&on="+org_name;
        xmlhttp.open("GET", $url,true);
	    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
        xmlhttp.send(null);
	
 }
 function createxmlhttpCont(i,id_cont){
 var xmlhttp;
		 if (window.XMLHttpRequest)
		{
			xmlhttp=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function(){
			var contact=document.getElementById('id_ref_cont_'+i);
			contact.innerHTML="<p><img src='media/com_jcrm/images/waiting.gif' width='20' height='20'/></p>";
		    if(xmlhttp.readyState == 4 && xmlhttp.status == 200 ){
			contact.innerHTML=xmlhttp.responseText+'posdflkjhsfdbg';
        }
		
		SqueezeBox.initialize({});
		$$('a.modal').each(function(el) {
		el.addEvent('click', function(e) {
		new Event(e).stop();
		SqueezeBox.fromElement(el);
		});
		});
		
	}
		var id=document.getElementById('cb'+i).value;
		var first_name=document.getElementById("cont_first_name_"+id).innerHTML;
	    var last_name=document.getElementById("cont_last_name_"+id).innerHTML;
	    var email=document.getElementById("cont_email_"+id).innerHTML;
	    var org_name=document.getElementById("name_orega_"+id).innerHTML;
	    
	    var $url="index.php?option=com_jcrm&view=check_contacts&format=raw&controller=check&task=check_cont&cid[]="+id+"&id_cont="+id_cont+"&fn="+first_name+"&ln="+last_name+"&email="+email+"&on="+org_name;
        xmlhttp.open("GET", $url,true);
	    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
        xmlhttp.send(null);
	
 }
 
 function checkContacts(){
 
		var cb = document.getElementsByName('cid[]');
		
		if(isCheck('cb')){
		 var i=0;
		for (  i; i<cb.length;i++){ 
		if(cb[i].checked){
		createxmlhr(i);
		createxmlhttp(i);
		UsingCheckbox(i);
	 }  
	 }
	 }
	 
 }
 function delayCheck(i_index){
		
		setTimeout("checkContacts()",1000);
		//setTimeout("checkContact("+i_index+")",1000);
}
function delayAct(){
	setTimeout("document.adminForm.submit()",1000);
}
 
 function ignoreCont(id){

		var ans=confirm("<?php echo JText::_('CONFIRM_IGNORE_CONTACT'); ?>");
		if(ans){
		window.location="index.php?option=com_jcrm&view=check_contacts&controller=check&task=ignore_cont&id_cont="+id;
		}return false;
}
function checkContact(i){
		var id_cont='cb'+i;
        document.getElementById(id_cont).checked=true;
        createxmlhr(i);
		createxmlhttp(i);
		UsingCheckbox(i);
}

function UsingCheckbox(index) {

	var tables = document.getElementsByTagName("table");
	if (tables[1].className == "jcrm_check_contacts") {
	var tbodies = tables[1].getElementsByTagName("tbody");
    var checkbox = tbodies[0].getElementById("cb"+index);
	   if(checkbox.checked){ 
        checkbox.parentNode.parentNode.className="selected";
       }else{checkbox.parentNode.parentNode.className = checkbox.parentNode.parentNode.oldClassName;}
       if (window.event && !window.event.cancelBubble) {
        window.event.cancelBubble = "true";
       } }
}


function checkOrg(id_ref,id_org){
var cb = document.getElementsByName('cid[]');
		
		if(isCheck('cb')){
		 var i=0;
		for (  i; i<cb.length;i++){
		 var id_cont='cb'+i;
		if(document.getElementById(id_cont).value==id_ref){
        document.getElementById(id_cont).checked=true;
		
        createxmlhrOrg(i,id_org);
		setTimeout("createxmlhttp("+i+")",1000);
				}
			}
		}
}
function checkCont(id_ref,id_cont){
var cb = document.getElementsByName('cid[]');
		
		if(isCheck('cb')){
		 var i=0;
		for (  i; i<cb.length;i++){
		 var id_contact='cb'+i;
		if(document.getElementById(id_contact).value==id_ref){
        document.getElementById(id_contact).checked=true;
		createxmlhttpCont(i,id_cont);
				}
			}
		}
}
 
  function ignoreContacts(){
  
		var id_conts=new Array();
		var cb = document.getElementsByName('cid[]');
			if(isCheck('cb')){
			var i=0;
			for (  i; i<cb.length;i++){ 
			if(cb[i].checked){
			var id_cont=document.getElementById('cb'+i).value;
			id_conts.push(id_cont);
					}
				}
		var ans=confirm("<?php echo JText::_('CONFIRM_IGNORE_CONTACT'); ?>");
		if(ans){
		window.location="index.php?option=com_jcrm&view=check_contacts&controller=check&task=ignore_cont&id_cont="+id_conts;
			}return false;
		}
 
 } 
 
 function isCheck (name) {
	var i = 0;
	while(document.getElementById(name + i)) {
	if(document.getElementById(name + i).checked){
		return true;
		}i++;
    }
	alert("<?php echo JText::_('PLEASE_CHECK_BOX'); ?>" );
	return false;
}
 
 function update_cont(i,id_ref){ 
 
		var choise=document.getElementById("jcrm_contacts_found_"+id_ref).value;
		if(choise==""){
		alert("<?php echo JText::_('WRONG_SELECT'); ?>");
		}
		else if (choise=="new_contact"){
		new_cont(i,id_ref);
		}
		else{
			//var ans=confirm("<?php echo JText::_('CONFIRM_VALIDE_CONTACT'); ?>");
			//if(ans){
			//document.location="index.php?option=com_jcrm&view=check_contacts&controller=check&task=insertContId&id_ref="+id_ref+"&id_cont="+choise;
			 checkCont(id_ref,choise);
			//}
		}
 }

 function edit_cont(id_ref){
 
		var choise=document.getElementById("jcrm_contacts_found_"+id_ref).value;
		if(choise==""||choise=="new_contact"){
			alert("<?php echo JText::_('WRONG_SELECT'); ?>");
		}else{
		window.open('index.php?option=com_jcrm&view=contact_form&tmpl=component&id_cont='+choise,'contact_form','modal=yes');
		}
 } 
 
  function new_cont(i,id_ref){
 
	var first_name=document.getElementById("cont_first_name_"+id_ref).innerHTML;
	var last_name=document.getElementById("cont_last_name_"+id_ref).innerHTML;
	var email=document.getElementById("cont_email_"+id_ref).innerHTML;
	var org_name=document.getElementById("name_orega_"+id_ref).innerHTML;
	var id_account=document.getElementById("id_orga_"+id_ref).value;
	SqueezeBox.initialize({
    size: {x: 900, y: 800}
    });
	var url="index.php?option=com_jcrm&view=contact_form&tmpl=component&fn="+first_name+"&ln="+last_name+"&email="+email+"&on="+org_name+"&id_acct="+id_account+"&id_ref="+id_ref;
	
	window.addEvent('domready', function() { 
	var dummylink = new Element('a', {
	 href: url,
	 rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window. innerHeight-innerWidth*0.05},onClose:function(){delayCheck('"+i+"')}}"
	});
	SqueezeBox.fromElement(dummylink);
	 
	});
	
 } 

 function update_org(i,id_ref){
	var choise=document.getElementById('jcrm_accounts_found_'+id_ref).value;
	if(choise==""){
		alert("<?php echo JText::_('WRONG_SELECT'); ?>");
	}else if(choise=="new_account"){
		new_org(i,id_ref);
	}else{
		//var ans=confirm("<?php echo JText::_('CONFIRM_VALIDE_ACCOUNT'); ?>");
		//if(ans){
			checkOrg(id_ref,choise)
			//edit_acct(id_ref);			
		//} 
	}
}

 function edit_acct(i,id_ref){
 
		var choise=document.getElementById('jcrm_accounts_found_'+id_ref).value;
		if(choise==""||choise=="new_account"){
			alert("<?php echo JText::_('WRONG_SELECT'); ?>");
		} else{
			var options = {size: {x:500, y:500}};
			SqueezeBox.initialize(options);
			SqueezeBox.open('index.php?option=com_jcrm&view=organisation_form&tmpl=component&&id_acct='+choise, {handler: 'iframe', size: {x:window.innerWidth*0.8,y:window. innerHeight*0.95},onClose:function(){delayCheck('"+i+"')}});
	/*
			SqueezeBox.initialize({
            size: {x: 700, y: 400}
			});
			var url='index.php?option=com_jcrm&view=organisation_form&tmpl=component&id_acct='+choise;
			window.addEvent('domready', function() { 
			var dummylink = new Element('a', {
			href: url,
			rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window. innerHeight-innerWidth*0.05},onClose:function(){delayCheck('"+i+"')}}"
			});
			SqueezeBox.fromElement(dummylink);
			});
		*/	
			
		}
 }

 function add_org(i, name_org, id_contact, i_referee){ 	
	var options = {size: {x:500, y:500}};
	SqueezeBox.initialize(options);
	SqueezeBox.open('index.php?option=com_jcrm&view=organisation_form&tmpl=component&name='+name_org+'&id_ref='+id_contact+'&i_referee='+i_referee, {handler: 'iframe', size: {x:window.innerWidth*0.8,y:window. innerHeight*0.95},onClose:function(){delayCheck('"+i+"')}});

	/*
			SqueezeBox.initialize({
            size: {x: 700, y: 400}
			});
			var url='index.php?option=com_jcrm&view=organisation_form&tmpl=component&name='+name_org+'&id_ref='+id_contact+'&i_referee='+i_referee;
			window.addEvent('domready', function() { 
			var dummylink = new Element('a', {
			href: url,
			rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window. innerHeight-innerWidth*0.05},onClose:function(){delayCheck('"+i+"')}}"
			});
			SqueezeBox.fromElement(dummylink);
			}); */
 }
 
 function add_cont(i,url){
 	var options = {size: {x:500, y:500}};
	SqueezeBox.initialize(options);
	SqueezeBox.open(url, {handler: 'iframe', size: {x:window.innerWidth*0.8,y:window. innerHeight*0.95},onClose:function(){delayCheck('"+i+"')}});
/*	
			SqueezeBox.initialize({
            size: {x: 700, y: 400}
			});
			window.addEvent('domready', function() { 
			var dummylink = new Element('a', {
			href: url,
			rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window. innerHeight-innerWidth*0.05},onClose:function(){delayCheck('"+i+"')}}"
			});
			SqueezeBox.fromElement(dummylink);
			});
 */
 }
   function new_org(i,id_ref){
   
		var name_org=document.getElementById("name_orega_"+id_ref).innerHTML;
		var options = {size: {x:500, y:500}};
		SqueezeBox.initialize(options);
		SqueezeBox.open('index.php?option=com_jcrm&view=organisation_form&tmpl=component&name='+name_org+'&id_ref='+id_ref, {handler: 'iframe', size: {x:window.innerWidth*0.8,y:window. innerHeight*0.95},onClose:function(){delayCheck('"+i+"')}});
/*
			SqueezeBox.initialize({
            size: {x: 700, y: 400}
			});
			var url='index.php?option=com_jcrm&view=organisation_form&tmpl=component&name='+name_org+'&id_ref='+id_ref;
			window.addEvent('domready', function() { 
			var dummylink = new Element('a', {
			href: url,
			rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window. innerHeight-innerWidth*0.05},onClose:function(){delayCheck('"+i+"')}}"
			});
			SqueezeBox.fromElement(dummylink);
	 
			});
*/
}

 function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
    window.onload = func;
	} else {
    window.onload = function() {
      oldonload();
      func();
    }}}

function lockRowUsingCheckbox() {
	var tables = document.getElementsByTagName("table");
	for (var m=0; m<tables.length; m++) {
	if (tables[m].className == "jcrm_check_contacts") {
		var tbodies = tables[m].getElementsByTagName("tbody");
    for (var j=0; j<tbodies.length; j++) {
     var checkboxes = tbodies[j].getElementsByTagName("input");
     for (var i=0; i<checkboxes.length; i++) {
      checkboxes[i].onclick = function(evt) {
       if (this.parentNode.parentNode.className.indexOf("selected") != -1){
        this.parentNode.parentNode.className = this.parentNode.parentNode.oldClassName;
       }
	   else if(this.checked){ 
        this.parentNode.parentNode.className="selected";
       }
       if (window.event && !window.event.cancelBubble) {
        window.event.cancelBubble = "true";
       } else {
        evt.stopPropagation();
       } }   }  } }}
}
addLoadEvent(lockRowUsingCheckbox); 

function checkAll( n, fldName ) {
	if (!fldName) {
     fldName = 'cb';
	}
	var f = document.adminForm;
	var c = f.toggle.checked;
	var n2 = 0; 
	for (i=0; i < n; i++) {
		cb = eval( 'f.' + fldName + '' + i );
		if (cb) {
			cb.checked = c;
			UsingCheckbox(i);
			n2++;
		}
	}
	if (c) {
		document.adminForm.boxchecked.value = n2;
	} else {
		document.adminForm.boxchecked.value = 0;
	}
}
 </script>