<?php
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php'); 

?>
<div class="emundusraw">
<form action="index.php?option=com_emundus&task=adduser" method="POST" name="adduser"/>
<fieldset><legend><?php echo JText::_('ADD_USER'); ?></legend>
<table>
	<tr><th><?php echo JText::_('FIRSTNAME_FORM'); ?></th><td><input type="text" size="30" name="firstname" value=""/></td></tr>
	<tr><th><?php echo JText::_('LASTNAME_FORM'); ?></th><td><input type="text" size="30" name="lastname" value=""/></td></tr>
	<tr><th><?php echo JText::_('LOGIN_FORM'); ?></th><td><input type="text" size="30" name="login" value=""/></td></tr>
	<tr><th><?php echo JText::_('EMAIL_FORM'); ?></th><td><input style="padding-left:20px;" type="text" size="30" name="email" value="" onChange="validateEmail(email);"/>
	</td></tr>
	
	<tr><th><?php echo JText::_('PROFILE_FORM'); ?></th><td><select name="profile" onchange="hidden_tr('show_univ','show_group', this);" >
			<?php foreach($this->profiles as $profile) { 
					echo '<option id="'.$profile->acl_aro_groups.'" value="'.$profile->id;
					echo @$this->users[0]->profile==$profile->id?'" selected':'"';
					echo '>'.$profile->label;'</option>'; 
				} ?>
				</select><?php echo'<input type="hidden" id="acl_aro_groups" name="acl_aro_groups" value="" />'; ?></td></tr>
    
	 <tr id="show_univ" style="visibility:hidden;"><th><?php echo JText::_('UNIVERSITY_FROM'); ?></th><td><select name="university_id">
			<?php echo '<option value="0">'.JText::_('PLEASE_SELECT').'</option>';
			foreach($this->universities as $university) { 
				echo '<option value="'.$university->id;
				echo @$this->users[0]->university_id==$university->id?'" selected':'"';
				echo '>'.$university->title;'</option>'; 
			} ?></select></td></tr>
     <tr id="show_group" style="visibility:hidden;">
       <th ><?php echo JText::_('GROUPS'); ?></th>
       <td>
			<?php foreach($this->groups as $groups) { 
					echo '<label><input type="checkbox" name="cb_groups[]" value="'.$groups->id.'"/>'.$groups->label.'</label><br />';
				} 
			?>
        </td>
    </tr>
    <tr id="show_campaign" style="visibility:visible;">
      <th ><?php echo JText::_('CAMPAIGN'); ?></th>
      <td><?php 
        echo '<select name="cb_campaigns[]" size="5" multiple="multiple" id="cb_campaigns">';

        foreach($this->campaigns_published as $campaign) { 
          if($edit==1){
            $applied = false;
            foreach($this->campaigns_candidature as $cc){ 
              if($campaign->id == $cc->campaign_id){
                 $applied = true;
              }
            }
            if($applied){
              echo '<option value="'.$campaign->id.'" selected />'.$campaign->label.' ('.$campaign->year.') - '.$campaign->training.' | '.JText::_('START_DATE').' : '.$campaign->start_date.'</option>';
            } else{
               echo '<option value="'.$campaign->id.'"/>'.$campaign->label.' ('.$campaign->year.') - '.$campaign->training.' | '.JText::_('START_DATE').' : '.$campaign->start_date.'</option>';
            }
          } else{
             echo '<option value="'.$campaign->id.'"/>'.$campaign->label.' ('.$campaign->year.') - '.$campaign->training.' | '.JText::_('START_DATE').' : '.$campaign->start_date.'</option>';
          }
        }
        echo '</select>';; 
      ?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="hidden" name="Itemid" id="Itemid" value="<?php echo JRequest::getVar('Itemid', '', 'GET', 'STRING'); ?>"/>
			<input type="hidden" name="tmpl" id="tmpl" value="<?php echo JRequest::getVar('tmpl', '', 'GET', 'STRING'); ?>"/>
			<input type="submit" value="<?php echo JText::_('SAVE'); ?>"/>
		</td>
	</tr>
</table>
</fieldset>
</form>
<script>$(document).ready(function() {$("#cb_campaigns").chosen({width: "650px"}); })</script>';
<script type="text/javascript">
function hidden_tr(div1,div2, profile)
{
	if (profile[profile.selectedIndex].id!=2)
	{
		document.getElementById(div1).style.visibility = "visible";
		document.getElementById(div2).style.visibility = "visible";
	}
	else
	{
		document.getElementById(div1).style.visibility = "hidden";
		document.getElementById(div2).style.visibility = "hidden";
	}
	document.getElementById('acl_aro_groups').value = profile[profile.selectedIndex].id;
	// alert(document.getElementById('acl_aro_groups').value);
}
function validateEmail(email) { 
	var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
	
	if(reg.test(email.value)){
		//document.getElementById('email_valid').innerHTML="* <?php echo JText::_('EMAIL_VALID'); ?>";
		email.style.background="url(<?php echo $this->baseurl ?>/media/com_emundus/images/icones/button_ok.png) no-repeat left";
	}else{
		//document.getElementById('email_valid').innerHTML="* <?php echo JText::_('EMAIL_NOT_VALID'); ?>";
		email.style.background="url(<?php echo $this->baseurl ?>/media/com_emundus/images/icones/button_cancel.png) no-repeat left";
	}
}
</script>