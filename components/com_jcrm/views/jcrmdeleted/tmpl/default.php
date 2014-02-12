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
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet('jcrm.css', 'media/com_jcrm/css/');
 
?>

<div class="return_form>"> <a href="index.php?option=com_jcrm&view=addressbook"><img src="media/com_jcrm/images/back.png" title="<?php echo JText::_('RETURN'); ?>" alt="Return"></a> </div>
<div  class="deleted_accounts_list">
  <form method="post" id="adminForm" name="adminForm" action="" onSubmit="return confirmDeleteacct();">
    <table id="userlist" class="deleted_accounts">
      <caption>
        <b>
      <?php echo JText::_('DELETED_ACCOUNTS_LIST'); ?>
      </caption>
      <thead>
        <tr>
          <th width=50><input type="checkbox" name="toggle" value="" title="Check All" onclick="checkAll(<?php echo count($this->data); ?>);" ></th>
          <th><?php echo "<b>".JText::_('ID')."</b>"; ?></th>
          <th><?php echo "<b>".JText::_( 'NAME' )."</b>"; ?></th>
        </tr>
      </thead>
      <tbody>
        <?php
	$k = 0;
	for ($i=0, $n=count( $this->data ); $i < $n; $i++)	{
		$row = &$this->data[$i];
		$checked = JHTML::_('grid.id', $i, $row->id);
	?>
        <tr class="<?php if($checked){echo "checked";} ?>">
          <td><?php echo $checked; ?></td>
          <td><?php echo $row->id; ?></td>
          <td><?php echo $row->name; ?></td>
        </tr>
      </tbody>
      <?php  $k = 1 - $k;
	} ?>
    </table>
    <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
    <br>
    <input  type="submit" class="restore" value="<?php echo JText::_('RESTORE'); ?>" name="restore_acct" onclick="document.pressed=this.name">
    <input type="submit" class="delete" value="<?php echo JText::_('DELETE'); ?>" name="delete_acct" onclick="document.pressed=this.name">
  </form>
</div>
<div class="deleted_contacts_list">
  <form name="contactsForm" id="contactsForm" method="post" onsubmit="return confirmDeleteCont();"action="">
    <table id="userlist" class="deleted_contacts">
      <caption>
        <b>
      <?php echo JText::_('DELETED_CONTACTS_LIST'); ?>
      </caption>
      <thead>
        <tr>
          <th  width="50"><input type="checkbox" name="toggleContacts" value="" title="Check All" onclick="checkAllContacts(<?php echo count($this->data_cont); ?>, 'dc');" ></th>
          <th><?php echo "<b>".JText::_('ID')."</b>";?></th>
          <th><?php echo "<b>".JText::_( 'NAME' )."</b>"; ?></th>
          <th><?php echo "<b>".JText::_( 'EMAIL' )."</b>"; ?></th>
        </tr>
      </thead>
      <tbody>
        <?php 
		$k = 0;
		for ($i=0, $n=count( $this->data_cont ); $i < $n; $i++)	{
		$arr = &$this->data_cont[$i];
		$checked = JHTML::_('grid.id', $i, $arr->id);
		$link="index.php?option=com_jcrm&view=jcrmdeleted&task=restore_cont&id_cont={$arr->id}";
	    ?>
        <tr>
          <td><input type="checkbox" name="del[]" id="dc<?php echo $i; ?>" value="<?php echo $arr->id; ?>"></td>
          <td><?php echo $arr->id; ?></td>
          <td><?php echo $arr->first_name; ?> <?php echo $arr->last_name; ?></td>
          <td><?php echo $arr->email; ?></td>
        </tr>
        <?php  $k = 1 - $k;
			} ?>
      </tbody>
    </table>
    <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
    <br>
    <input  type="submit" value="<?php echo JText::_('RESTORE'); ?>" class="restore" name="restore_cont" onclick="document.pressed=this.name">
    <input type="submit" value="<?php echo JText::_('DELETE'); ?>" class="delete" name="delete_cont" onclick="document.pressed=this.name">
  </form>
</div>
<script>
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
    window.onload = func;
	} else {
    window.onload = function() {
      oldonload();
      func();
    }}}

function addClass(element,value) {
	if (!element.className) {
    element.className = value;
	} else {
    newClassName = element.className;
    newClassName+= " ";
    newClassName+= value;
    element.className = newClassName;
	}}

function stripeTables() {
	var tables = document.getElementsByTagName("table");
	for (var m=0; m<tables.length; m++) {
	if (tables[m].className == "deleted_accounts"||tables[m].className =="deleted_contacts") {
		var tbodies = tables[m].getElementsByTagName("tbody");
    for (var i=0; i<tbodies.length; i++) {
     var odd = true;
     var rows = tbodies[i].getElementsByTagName("tr");
     for (var j=0; j<rows.length; j++) {
      if (odd == false) {
       odd = true;
      } else {
       addClass(rows[j],"odd");
       odd = false;
      } } } }
}
}

function highlightRows() {
	if(!document.getElementsByTagName) return false;
	var tables = document.getElementsByTagName("table");
	for (var m=0; m<tables.length; m++) {
	if (tables[m].className == "deleted_accounts"||tables[m].className =="deleted_contacts") {
     var tbodies = tables[m].getElementsByTagName("tbody");
     for (var j=0; j<tbodies.length; j++) {
     var rows = tbodies[j].getElementsByTagName("tr");
     for (var i=0; i<rows.length; i++) {
        rows[i].oldClassName = rows[i].className
        rows[i].onmouseover = function() {
        if( this.className.indexOf("selected") == -1)
        addClass(this,"highlight");}
        rows[i].onmouseout = function() {
        if( this.className.indexOf("selected") == -1)
        this.className = this.oldClassName
        }  } }}
}}

function selectRowCheckbox(row) {
	var checkbox = row.getElementsByTagName("input")[0];
	if (checkbox.checked == true) {
	checkbox.checked = false;
		} else
	if (checkbox.checked == false) {
	checkbox.checked = true;
}
}

function lockRow() {
	var tables = document.getElementsByTagName("table");
	for (var m=0; m<tables.length; m++) {
	if (tables[m].className == "deleted_accounts"||tables[m].className =="deleted_contacts") {
     var tbodies = tables[m].getElementsByTagName("tbody");
     for (var j=0; j<tbodies.length; j++) {
      var rows = tbodies[j].getElementsByTagName("tr");
      for (var i=0; i<rows.length; i++) {
       rows[i].oldClassName = rows[i].className;
       rows[i].onclick = function() {
        if (this.className.indexOf("selected") != -1) {
         this.className = this.oldClassName;
        } else {
         addClass(this,"selected");
        }
        selectRowCheckbox(this);
       }  } }}}
}
	addLoadEvent(stripeTables);
	addLoadEvent(highlightRows);
	addLoadEvent(lockRow);

function lockRowUsingCheckbox() {
	var tables = document.getElementsByTagName("table");
	for (var m=0; m<tables.length; m++) {
	if (tables[m].className == "deleted_accounts"||tables[m].className =="deleted_contacts") {
		var tbodies = tables[m].getElementsByTagName("tbody");
    for (var j=0; j<tbodies.length; j++) {
     var checkboxes = tbodies[j].getElementsByTagName("input");
     for (var i=0; i<checkboxes.length; i++) {
      checkboxes[i].onclick = function(evt) {
       if (this.parentNode.parentNode.className.indexOf("selected") != -1){
        this.parentNode.parentNode.className = this.parentNode.parentNode.oldClassName;
       } else {
        addClass(this.parentNode.parentNode,"selected");
       }
       if (window.event && !window.event.cancelBubble) {
        window.event.cancelBubble = "true";
       } else {
        evt.stopPropagation();
       } }   }  } }}
}
	addLoadEvent(lockRowUsingCheckbox);

function isCheck (name) {
	var i = 0;
	while(document.getElementById(name + i)) {
	if(document.getElementById(name + i).checked)
		return true;
		i++;
		}
	alert("<?php echo JText::_('PLEASE_CHECK_BOX'); ?>" );
	return false;
}

function confirmDeleteacct(){
		var button_name=document.pressed.split("|");
		//alert(button_name[0]);
		var ans;
		switch(button_name[0]) {
			case 'delete_acct': 
			if (isCheck('cb')) {
			var ans = confirm("<?php echo JText::_('DELETE_CONFIRMATION'); ?>")
			if (ans){
			document.adminForm.action = "index.php?option=com_jcrm&view=jcrmdeleted&task=delete&cid[]=<?php echo @$row->id; ?>";	
				}
			}  else return false;
		break;
			case 'restore_acct': 
			if (isCheck('cb')) {
				var ans = confirm("<?php echo JText::_('RESTORE_CONFIRMATION'); ?>")
				if (ans){
			document.adminForm.action = "index.php?option=com_jcrm&view=jcrmdeleted&task=restore&cid[]=<?php echo @$row->id; ?>";
				}
			} else return false;
		break;
			default: return false;
		}
		return true;
	}

function confirmDeleteCont(){
	var button_name=document.pressed.split("|");
	var ans;
	switch(button_name[0]) {
		case 'delete_cont': 
		if (isCheck('dc')) {
		var ans = confirm("<?php echo JText::_('DELETE_CONFIRMATION'); ?>")
		if (ans){
		document.contactsForm.action = "index.php?option=com_jcrm&view=jcrmdeleted&task=delete&del[]=<?php echo @$arr->id; ?>";
			}
		} else return false;
	    break;
		case'restore_cont':
		if (isCheck('dc')) {
        var ans = confirm("<?php echo JText::_('RESTORE_CONFIRMATION'); ?>")
		if (ans){
			document.contactsForm.action = "index.php?option=com_jcrm&view=jcrmdeleted&task=restore&cid[]=<?php echo @$arr->id; ?>";
		}
		} else return false;
	break;
	default: return false;
	}
	return true;		
	}

function checkAllContacts( n, fldName ) {
	if (!fldName) {
     fldName = 'cb';
	}
	var f = document.contactsForm;
	var c = f.toggleContacts.checked;
	var n2 = 0; 
	for (i=0; i < n; i++) {
		cb = eval( 'f.' + fldName + '' + i );
		if (cb) {
			cb.checked = c;
			n2++;
		}
	}
	if (c) {
		document.contactsForm.boxchecked.value = n2;
	} else {
		document.contactsForm.boxchecked.value = 0;
	}
}
</script>