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
	
	$page=JRequest::getVar('limitstart','0','get');
 ?>

<form name="adminForm" method="POST" action="">

  <div><span class="jcrm_new_account">
    <a href="index.php?option=com_jcrm&view=addressbook"><img class="top_images" src="media/com_jcrm/images/gohome.png " title="<?php echo JText::_('HOME'); ?>"/></a>
    <a rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9},onClose:function(){delayCheck()}}" href="index.php?option=com_jcrm&view=organisation_form&tmpl=component#jcrm_edit_account" target="_self" class="modal"><img class="top_images" src="media/com_jcrm/images/add_group.png" title="<?php echo JText::_('NEW_ORGANISATION'); ?>"/></a>
	<a rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.9},onClose:function(){delayCheck()}}" href="index.php?option=com_jcrm&view=contact_form&tmpl=component#jcrm_edit_contact" target="_self" class="modal"><img class="top_images" src="media/com_jcrm/images/add_user.png" title="<?php echo JText::_('NEW_CONTACT'); ?>"/></a>
	<a href="index.php?option=com_jcrm&view=jcrmdeleted"><img class="top_images" src="media/com_jcrm/images/Corbeille1.JPG" title="<?php echo JText::_('CORBEILLE'); ?>"/></a>
	<a href="index.php?option=com_jcrm&view=check_contacts"><img class="top_images" src="media/com_jcrm/images/synch.jpg" width="34" height="35" title="<?php echo JText::_('SYNCHRONISE_REFERENCES'); ?>"/></a>
   </span></div><br>


<div class="jcrm_addressbook_accounts">
   <fieldset class="label_accountslist" id="label_accountslist">
     <legend><?php echo JText::_('ORGANISATIONS'); ?></legend>
	 <div>
<fieldset><legend>	<?php echo JText::_('FILTER')."&nbsp"; ?>
	 <input type="hidden" value="0" id="theValue" />
	<a href="javascript:;" onclick="addElement();"><img src="media/com_jcrm/images/search+.png" alt="<?php echo JText::_('ADD_SEARCH_ELEMENT'); ?>" title="<?php echo JText::_('ADD_SEARCH_ELEMENT'); ?>"/></a></legend>
    <table class="filter_account">	
     <tr>
     	<td>
        <select id="element" name="elements[]">
		<option value=""><?php echo JText::_('ALL'); ?></option>
		<option <?php if(!empty($this->search_elements)){if($this->search_elements[0]=="name"){ echo"selected";}} ?> value="name" ><?php echo JText::_('NAME'); ?></option>
		<option <?php if(!empty($this->search_elements)){if($this->search_elements[0]=="account_type"){ echo"selected";}} ?> value="account_type"><?php echo JText::_('TYPE'); ?></option>
		<option <?php if(!empty($this->search_elements)){if($this->search_elements[0]=="address_country"){ echo"selected";}} ?> value="address_country"><?php echo JText::_('COUNTRY'); ?></option>
		</select>
		<input type="text" id="search" name="search[]" size="35" placeholder="<?php echo JText::_('NAME'); ?>" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($this->search_acct)){echo $this->search_acct[0];} ?>"></td>
		<td><input type="image"  src="media/com_jcrm/images/search_button.png" class="search"  title="<?php echo JText::_('SEARCH'); ?>"/></td>
		<td><input type="image" src="media/com_jcrm/images/reset.jpg" class="reset" style="background:url(media/com_jcrm/images/reset.jpg)" title="<?php echo JText::_('RESET'); ?>" onclick="document.getElementById('search').value='';document.getElementById('element').value=''; this.form.submit();this.form.action='#jcrm_new_account'"/></td>
      </tr>
	  <tr><td id="search_acct_cont"></td></tr>
     </table>
	</fieldset>
	</div>
  
	<ol start="<?php echo $page+1; ?>" class="table_jcrm_account">
	<?php if(!empty($this->data)){  ?>
	
	<p><b><?php echo JText::_('Action');echo"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"; ?>
		 <?php echo JHTML::_('grid.sort', JText::_( 'NAME' ), 'name', $this->lists['order_Dir'], $this->lists['order']); ?></b>
		<?php
		    foreach($this->data as $dataItem){
			
	         $link=JRoute::_( "index.php?option=com_jcrm&view=addressbook&id_acct=".$dataItem->id."&limitstart=".$this->page."&Itemid=982#label_accountslist");
			 $link_edit=JRoute::_( "index.php?option=com_jcrm&view=organisation_form&tmpl=component&id_acct=".$dataItem->id ); ?>
	      
    <li <?php echo $dataItem->id==$this->id_acct?'id="jcrm_selected"':''; ?>>
		    <input name="id_account" type="hidden" value="<?php echo $dataItem->id; ?>"/>
		    <a href="#label_accountslist" onclick="delete_account('<?php echo $dataItem->id; ?>','<?php echo $this->page; ?>')"><img src="media/com_jcrm/images/delete_group.png" title="<?php echo JText::_('DELETE_ORGANISATION'); ?>"/></a>
			<a href="javascript:openModal('<?php echo $link_edit; ?>')"><img src="media/com_jcrm/images/edit.png" title="<?php echo JText::_('EDIT_ORGANISATION'); ?>" /></a>
			<input type="image" src="media/com_jcrm/images/down.png" title="<?php echo JText::_('CLICK_VIEW_CONTACTS_ACCOUNTS'); ?>" onclick= "this.form.action='<?php echo $link; ?>'">
			<?php echo "&nbsp&nbsp&nbsp".$dataItem->name; ?>
      </li>
		 
    <?php if($dataItem->id==$this->id_acct){  ?>
	    <ul id="list_cont_acct_toggle">
		 <?php if(!empty($this->data_contactslist)){ ?>   
	    <li> <p id="list_contact_toggle"><b><?php echo JText::_('CONTACTS'); ?></b></p></li>
	    <div id="list_toggle_1">
	    <ol type="1">
			 <?php foreach($this->data_contactslist as $dataItem_cont){
	            $link_detail=JRoute::_( "index.php?option=com_jcrm&view=address&id_cont=".$dataItem_cont->id );
				$link_edit=JRoute::_( "index.php?option=com_jcrm&view=contact_form&tmpl=component&id_cont=".$dataItem_cont->id ); ?> 
	     <li>
             <input type="hidden" name="id" value="<?php echo $dataItem_cont->id; ?>" />
			 <a href="#label_accountslist" onclick="delete_contactchild('<?php echo $dataItem_cont->id; ?>','<?php echo $this->page; ?>')"><img src="media/com_jcrm/images/delete_user.png" title="<?php echo JText::_('DELETE_CONTACT'); ?>" alt="<?php echo JText::_('DELETE'); ?>"/></a>
			<a href="javascript:openModal('<?php echo $link_edit; ?>')"><img src="media/com_jcrm/images/edit.png" title="<?php echo JText::_('EDIT_CONTACT'); ?>"/></a>
			<a href="javascript:void(0)" onClick="window.open('mailto:<?php echo $dataItem_cont->email; ?>')"><img src="media/com_jcrm/images/mailto.jpg" title="<?php echo JText::_('SEND_EMAIL_TO'); ?>" width="17" height="17" align="bottom" /></a>
	         <?php echo $dataItem_cont->first_name.'&nbsp'.$dataItem_cont->last_name; ?>
	     </li> <?php } ?> 
	   </ol> 
       </div>
		  <?php }else  if(empty($this->data_contactslist)){ ?>
	      <li> <?php echo "<p class='no_result_label'><b>".JText::_("NO_CONTACTS")."</b></p>"; ?></li>
		       <?php }  ?>
		 <?php if(!empty($this->data_child)){ ?>
	     <li><p id="list_account_toggle"><b><?php echo Jtext::_('ACCOUNTS'); ?></b></p></li>
		 
		    <div id="list_toggle_2">
        <ol type="1">
		   <?php foreach($this->data_child as $datachild){
	        $link=JRoute::_( "index.php?option=com_jcrm&view=addressbook&id_acct=".$datachild->id."&limitstart=".$this->page."&Itemid=982#");
			$link_edit=JRoute::_( "index.php?option=com_jcrm&view=organisation_form&tmpl=component&id_acct=".$datachild->id ); ?>
	   <li>
		 <input name="id_account" type="hidden" value="<?php echo $datachild->id; ?>"/>
		 <a href="#label_accountslist" onclick="delete_accountchild(<?php echo $datachild->id; ?>,<?php echo $this->id_acct; ?>,<?php echo $this->page; ?>)"><img src="media/com_jcrm/images/delete_group.png" alt="<?php echo JText::_('DELETE'); ?>" title="<?php echo JText::_('DELETE_ORGANISATION'); ?>"/></a>
	     <a href="javascript:openModal('<?php echo $link_edit; ?>')"><img src="media/com_jcrm/images/edit.png" title="<?php echo JText::_('EDIT_ORGANISATION'); ?>" /></a>
		 <img src="media/com_jcrm/images/down.png" title="<?php echo JText::_('CLICK_VIEW_CONTACTS_ACCOUNTS'); ?>" onclick="ajxGetCont(<?php echo $datachild->id; ?>)"/><?php echo "&nbsp&nbsp".$datachild->name; ?>
	   </li>
    <div id="get_contacts_<?php echo $datachild->id; ?>"> 
    </div><?php } ?>
		</ol>
		</div>
            <?php } if(empty($this->data_child)){  ?>
	   <li><?php echo "<p class='no_result_label'><b>".JText::_('NO_ACCOUNTS')."</b></p>"; ?></li>
	       <?php } ?>
      </ul><?php } }  } else {echo "<ul><p class='no_result_label'><b>".JText::_('NO_ACCOUNTS')."</b></p></ul>";} ?>
					
	    <br><div class="pagination"><?php echo $this->pagination->getPagesLinks(); echo $this->pagination->getPagesCounter(); ?></div>
    </ol>
     <input type="hidden" name="filter_order"  value="<?php echo $this->lists['order']; ?>"/>
	 <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" /> 
     <input type="hidden" name="filter_order_cont"  value="<?php echo $this->lists['order_cont']; ?>"/>
	 <input type="hidden" name="filter_order_Dir_cont" value="<?php echo $this->lists['order_Dir_cont']; ?>" /> 	 
  </fieldset>
 </div>
 
 <div>
<fieldset>
<legend><?php echo JText::_("EXPORT_CONTACTS"); ?></legend>
	<div class="export_fiche_accounts">
	<a href="index.php?option=com_jcrm&view=addressbook&type=xls&controller=check&task=export_contacts"><img title="<?php echo JText::_('EXPORT_ALL_CONTACTS_XLS'); ?>" src="media/com_jcrm/images/export_xls.jpg" width="40" height="40" class="export_contacts" name="export_contacts_xls" /></a>
	<a href="index.php?option=com_jcrm&view=addressbook&type=csv&controller=check&task=export_contacts"><img title="<?php echo JText::_('EXPORT_ALL_CONTACTS_CSV'); ?>" src="media/com_jcrm/images/export_csv.jpg" width="40" height="40" class="export_contacts" name="export_contacts_csv" /></a>
	<a href="index.php?option=com_jcrm&view=addressbook&type=txt&controller=check&task=export_contacts"><img title="<?php echo JText::_('EXPORT_ALL_CONTACTS_TXT'); ?>" src="media/com_jcrm/images/text.jpg" width="40" height="40" class="export_contacts" name="export_contacts_txt" /></a>
	<a href="index.php?option=com_jcrm&view=addressbook&type=pdf&controller=check&task=export_contacts"><img title="<?php echo JText::_('EXPORT_ALL_CONTACTS_PDF'); ?>" src="media/com_jcrm/images/pdf.jpg" width="40" height="40" class="export_contacts"  name="export_contacts_pdf" /></a>
	</div>
	</fieldset>
</div>	

  <?php if($this->id_acct==0){  ?>
  <div class="jcrm_addressbook_contactslist">
    <fieldset class="label_contacts_list" id="label_contacts_list">
	<legend><?php echo JText::_('CONTACTS'); ?></legend>
	
	    <table class="filter_contact">
	    <tr><td><?php echo"<b>". JText::_('FILTER')."</b>"; ?></td>
	    <td><input type="text" id="search_cont" name="search_cont" onfocus="style.backgroundColor='#FFFF99';" onblur="style.backgroundColor='white';" value="<?php if(!empty($this->search_cont)){echo $this->search_cont;} ?>"></td>
		<td><input type="image" class="search" src="media/com_jcrm/images/search_button.png" value="<?php echo JText::_('SEARCH'); ?>" title="<?php echo JText::_('SEARCH'); ?>" onclick="this.form.submit(); this.form.action='#label_contacts_list'"/></td>
		<td><input type="image" class="reset" src="media/com_jcrm/images/reset.jpg" value="<?php echo JText::_('RESET'); ?>" title="<?php echo JText::_('RESET'); ?>" onclick="reset_contact()"/></td>
		<td><div id="filter_alphabet_cont">
		<?php $alf=JRequest::getVar('alf',null,'get'); ?>
		<a <?php if(isset($alf)&&($alf=='a')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=a#label_contacts_list"><?php echo JText::_("A"); ?></a>
		<a <?php if(isset($alf)&&($alf=='b')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=b#label_contacts_list"><?php echo JText::_("B"); ?></a>
		<a <?php if(isset($alf)&&($alf=='c')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=c#label_contacts_list"><?php echo JText::_("C"); ?></a>
		<a <?php if(isset($alf)&&($alf=='d')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=d#label_contacts_list"><?php echo JText::_("D"); ?></a>
		<a <?php if(isset($alf)&&($alf=='e')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=e#label_contacts_list"><?php echo JText::_("E"); ?></a>
		<a <?php if(isset($alf)&&($alf=='f')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=f#label_contacts_list"><?php echo JText::_("F"); ?></a>
		<a <?php if(isset($alf)&&($alf=='g')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=g#label_contacts_list"><?php echo JText::_("G"); ?></a>
		<a <?php if(isset($alf)&&($alf=='h')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=h#label_contacts_list"><?php echo JText::_("H"); ?></a>
		<a <?php if(isset($alf)&&($alf=='i')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=i#label_contacts_list"><?php echo JText::_("I"); ?></a>
		<a <?php if(isset($alf)&&($alf=='j')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=j#label_contacts_list"><?php echo JText::_("J"); ?></a>
		<a <?php if(isset($alf)&&($alf=='k')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=k#label_contacts_list"><?php echo JText::_("K"); ?></a>
		<a <?php if(isset($alf)&&($alf=='l')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=l#label_contacts_list"><?php echo JText::_("L"); ?></a>
		<a <?php if(isset($alf)&&($alf=='m')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=m#label_contacts_list"><?php echo JText::_("M"); ?></a>
		<a <?php if(isset($alf)&&($alf=='n')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=n#label_contacts_list"><?php echo JText::_("N"); ?></a>
		<a <?php if(isset($alf)&&($alf=='o')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=o#label_contacts_list"><?php echo JText::_("O"); ?></a>
		<a <?php if(isset($alf)&&($alf=='p')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=p#label_contacts_list"><?php echo JText::_("P"); ?></a>
		<a <?php if(isset($alf)&&($alf=='q')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=q#label_contacts_list"><?php echo JText::_("Q"); ?></a>
		<a <?php if(isset($alf)&&($alf=='r')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=q#label_contacts_list"><?php echo JText::_("R"); ?></a>
		<a <?php if(isset($alf)&&($alf=='s')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=s#label_contacts_list"><?php echo JText::_("S"); ?></a>
		<a <?php if(isset($alf)&&($alf=='t')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=t#label_contacts_list"><?php echo JText::_("T"); ?></a>
		<a <?php if(isset($alf)&&($alf=='u')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=u#label_contacts_list"><?php echo JText::_("U"); ?></a>
		<a <?php if(isset($alf)&&($alf=='v')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=v#label_contacts_list"><?php echo JText::_("V"); ?></a>
		<a <?php if(isset($alf)&&($alf=='w')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=w#label_contacts_list"><?php echo JText::_("W"); ?></a>
		<a <?php if(isset($alf)&&($alf=='x')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=x#label_contacts_list"><?php echo JText::_("X"); ?></a>
		<a <?php if(isset($alf)&&($alf=='y')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=y#label_contacts_list"><?php echo JText::_("Y"); ?></a>
		<a <?php if(isset($alf)&&($alf=='z')) {echo "class='alphabet_cont_selected'";} else echo "class='alphabet_cont'"; ?> href="index.php?option=com_jcrm&view=addressbook&alf=z#label_contacts_list"><?php echo JText::_("Z"); ?></a><br><br>
		</div>
     </td></tr></table>
	<table id="userlist" >
        <thead>
			<tr>
				<td align="center" colspan="4">
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			<tr>
				<th width='66'><b><?php echo JText::_('ACTION'); ?></b></th>
				<th><b><?php echo"&nbsp&nbsp"; echo JHTML::_('grid.sort', JText::_( 'NAME' ), 'last_name', $this->lists['order_Dir_cont'], $this->lists['order_cont']); ?></b></th>
				<th><b><?php echo JText::_('EMAIL'); ?></b></th>
				<th><b><?php echo JText::_('TELEPHONE'); ?></b></th>
			</tr>
        </thead>
	    <tfoot class="pagination">
		     <tr>
			  <td colspan=4>
				<?php echo $this->pagination_contacts->getListFooter(); ?>
				</td>
			 
		      </tr>
	     </tfoot>
		<tbody class="table_contlist_body">
			<?php if(!(empty($this->data_contactslist))){
					$i=1;
			foreach($this->data_contactslist as $dataItem){
	            $link_detail=JRoute::_( "index.php?option=com_jcrm&view=address&id_cont=".$dataItem->id );
				$link_edit=JRoute::_( "index.php?option=com_jcrm&view=contact_form&tmpl=component&id_cont=".$dataItem->id."#jcrm_edit_contact" ); ?>
	    <tr class="<?php  echo $i%2==0?"row0":"row1"; ?>">
	    <td width="120">
			<?php  echo $i+$page; ?>
		    <input type="hidden" name="id" value="<?php echo $dataItem->id; ?>" />
		    
		    <a href="#label_contacts_list" onclick="delete_contact(<?php echo $dataItem->id; ?>,<?php echo $this->page; ?>)"><img src="media/com_jcrm/images/delete_user.png" title="<?php echo JText::_('DELETE_CONTACT'); ?>" alt="<?php echo JText::_('DELETE'); ?>"/></a>
			<a href="javascript:openModal('<?php echo $link_edit; ?>')"><img src="media/com_jcrm/images/edit.png"title="<?php echo JText::_('EDIT_CONTACT'); ?>"/></a>
			<a href="javascript:void(0)" onClick="window.open('mailto:<?php echo $dataItem->email; ?>')"><img src="media/com_jcrm/images/mailto.jpg" title="<?php echo JText::_('SEND_EMAIL_TO'); ?>" width="17" height="17" align="bottom" /></a>
			<a href="index.php?option=com_jcrm&view=addressbook&controller=check&task=export_vcf&id_cont=<?php echo $dataItem->id; ?>"><img src="media/com_jcrm/images/vcf.jpg" width="20" height="20" title="<?php echo JText::_('EXPORT_CONTACT_VCF'); ?>"/></a>
		</td>
	        <td><?php echo "<b>".strtoupper($dataItem->last_name)."</b><br>".$dataItem->first_name; ?></td>
		    <td><?php echo "<i>".$dataItem->email."</i>"; ?></td>
			<td><?php echo $dataItem->phone_work; ?></td>
		 </tr>
	       <?php if($i<=count($this->data_contactslist)){$i++;} 
		   } }else{echo "<tr><td><p class='no_result_label'><b>".JText::_('NO_CONTACTS')."</b></p></td></tr>";} ?> 
	   </tbody>
     </table>
	  
  </fieldset>
  </div>
  <?php } ?>
 </form>
 
 
 <script language='javascript'> 
function delete_account(id,page) {
	var ans = confirm("<?php echo JText::_('DELETE_ACCOUNT_CONFIRMATION'); ?>")
	
	if (ans){
		 window.location= "index.php?option=com_jcrm&view=addressbook&task=delete_account&id_acct="+id+"&limitstart="+page;
		return true;
	}
	else{
	     
		return false;
	}
}
function delete_accountchild(id,id_ac,page) {
	var ans = confirm("<?php echo JText::_('DELETE_ACCOUNT_CONFIRMATION'); ?>")
	
	if (ans){
		 window.location= "index.php?option=com_jcrm&view=addressbook&task=delete_account&id_acct="+id+"&id_ac="+id_ac+"&limitstart="+page;
		return true;
	}
	else{
	     
		return false;
	}
}
function delete_contact(id,page) {
	var ans = confirm("<?php echo JText::_('DELETE_CONTACT_CONFIRMATION'); ?>")
	
	if (ans){
		window.location="index.php?option=com_jcrm&view=addressbook&task=delete_contact&id_cont="+id+"&limitstart="+page;
		return true;
	}
	else{
		return false;	
	}
}
function delete_contactchild(id,page) {
	var ans = confirm("<?php echo JText::_('DELETE_CONTACT_CONFIRMATION'); ?>")
	
	if (ans){
		window.location = "index.php?option=com_jcrm&view=addressbook&task=delete_contactchild&id_cont="+id+"&limitstart="+page;
		return true;
	}
	else{
		return false;	
	}
}
function tableOrdering( order, dir, task )
{
	var form = document.adminForm;
	form.filter_order.value = order;
	form.filter_order_Dir.value = dir;
	
	form.filter_order_cont.value = order;
	form.filter_order_Dir_cont.value = dir;
	document.adminForm.submit( task );
}


 function delayCheck(){
  setTimeout("document.adminForm.submit()",1000);
 }
 
 
 function addElement() {
  var ni = document.getElementById('search_acct_cont');
  var numi = document.getElementById('theValue');
  var num = (document.getElementById('theValue').value -1)+ 2;
  numi.value = num;
  var newdiv = document.createElement('div');
  var divIdName = 'my'+num+'Div';
  newdiv.setAttribute('id',divIdName);
  newdiv.innerHTML = '<select name="elements[]" id="element"><option value=""> <?php echo JText::_("ALL"); ?> </option><option value="name"><?php echo JText::_("NAME"); ?></option><option value="account_type"><?php echo JText::_("TYPE"); ?></option><option value="address_country"><?php echo JText::_("COUNTRY"); ?></option></select><input type="text" name="search[]" size=35 /> <a href=\'#\' onclick=\'removeElement("'+divIdName+'")\'><img src="media/com_jcrm/images/search-.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>" title="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>';
  ni.appendChild(newdiv);
}

function removeElement(divNum) {
  var d = document.getElementById('search_acct_cont');
  var olddiv = document.getElementById(divNum);
  d.removeChild(olddiv);
}
   
function ajxGetCont(id_acct){
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
		if(xmlhr.readyState == 4 && xmlhr.status == 200){
          var contact=document.getElementById('get_contacts_'+id_acct);
           
          try //Internet Explorer
          {
            xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
            xmlDoc.async="false";
            xmlDoc.loadXML(xmlhr.responseText);
          }
        catch(e)
          {
          try //Firefox, Mozilla, Opera, etc.
            {
              parser=new DOMParser();
              xmlDoc=parser.parseFromString(xmlhr.responseText,"text/xml");
            }
          catch(e) {alert(e.message)}
          }
           contact.innerHTML=xmlhr.responseText;
      }
	  
		SqueezeBox.initialize({});
		$$('a.modal').each(function(el) {
		el.addEvent('click', function(e) {
		new Event(e).stop();
		SqueezeBox.fromElement(el);
		});
		});
   }
      xmlhr.open("GET","index.php?option=com_jcrm&view=addressbook&format=raw&task=getContacts&id_acct="+id_acct,true);
      xmlhr.send(null);
}

function reset_contact(){
var form = document.adminForm;
document.getElementById("search_cont").value="";
form.submit();
form.action="/index.php?option=com_jcrm&view=addressbook&Itemid=982#label_contacts_list"; 
}

function openModal(url){

			SqueezeBox.initialize({
            size: {x: 700, y: 400}
			});
			window.addEvent('domready', function() { 
			var dummylink = new Element('a', {
			href: url,
			rel: "{handler: 'iframe', size: {x:window.innerWidth-innerWidth*0.4,y:window.innerHeight-innerWidth*0.05},onClose:function(){delayCheck()}}"
			});
			SqueezeBox.fromElement(dummylink);
			});

}

</script>