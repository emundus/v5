<fieldset>
<legend><img src="<?php JURI::Base(); ?>media/com_emundus/images/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?></legend>
<table width="100%" id="filters">
	<tr>  
		<th width="14%" align="left"><?php echo '<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'</span>'; ?></th>
		<th width="9%" align="left"><?php echo JText::_('FINAL_GRADE'); ?></th>
		<th width="9%" align="left"><?php echo JText::_('PROFILE_FILTER'); ?></th>
		<th width="9%" align="left"><?php echo JText::_('GROUP_EVAL'); ?></th>
	</tr>
	<tr>
		<td align="left">
			<input type="text" name="s" value="<?php echo $current_l; ?>" onKeyPress="return submitenter(this,event)" />
		</td>
		<td align="left">  
			<?php 
			$db = JFactory::getDBO();
			$query = 'SELECT params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
			$db->setQuery( $query );
			$result = EmundusHelperFilters::insertValuesInQueryResult($db->loadAssocList(), array("sub_values", "sub_labels"));
			$sub_values = explode('|', $result[0]['sub_values']);
			foreach($sub_values as $sv)
				$p_grade[]="/".$sv."/";
			$grade = explode('|', $result[0]['sub_labels']);
			?>
			<select name="final_grade" onChange="javascript:submit()">
				<option value="0"> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
				<?php  
				$groupe ="";
			
				for($i=0; $i<count($grade); $i++) { 
					$val = substr($p_grade[$i],1,1);
					echo '<option value="'.$val.'"';
						if($val == $current_fg) echo ' selected';
								echo '>'.$grade[$i].'</option>'; 
				} 
				unset($val);
				unset($i);
				?>
			</select>
		</td>
		<td align="left">  
			<select name="rowid" onChange="javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php 
				foreach($this->profiles as $profile) { echo '<option value="'.$profile->id.'"';
					if($current_p==$profile->id) echo ' selected';
						echo '>'.$profile->label;'</option>'; 
				} 
				?>
			</select>
		</td>
		<td>
			<select name="groups_eval" onChange="clear_campaigns_filter(this); javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php 
				foreach($this->allGroupEval as $group) { 
				echo '<option value="'.$group->id.'"';
					if($current_groupEval==$group->id) echo ' selected';
						echo '>'.$group->label.'</option>'; 
				}
				?>
			</select> 
		</td>
	</tr>
	<tr>
		<th width="5%" align="left"><?php echo JText::_('SCHOOLYEARS'); ?></th>
		<th width="5%" align="left"><?php echo JText::_('CAMPAIGNS'); ?></th>
		<th width="6%" align="left"><ul><?php 
			echo '<span class="editlinktip hasTip" title="'.JText::_('SPAM_SUSPECT').'::'.JText::_('SPAM_SUSPECT_DETAILS').'">';
			echo JText::_('SPAM_SUSPECT'); 
			echo '</span>';
			?>
			<span style="margin-left:10px;"><?php echo JText::_('NEWSLETTER'); ?></span>
		</th>
		<th width="57%" align="left"></th>
		<th width="57%" align="left">&nbsp;</th>
		<th width="57%" align="left">&nbsp;</th>
	</tr>
	<tr>
		<td>
			<select name="schoolyears" onChange="clear_groupsEval_filter(this); javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php 
				foreach($this->schoolyears as $s) { 
					echo '<option value="'.$s.'"';
					if($schoolyears==$s) echo ' selected';
						echo '>'.$s.'</option>'; 
				}
				?>
			</select> 
		</td>
		<td>
			<select name="campaigns" onChange="clear_groupsEval_filter(this);javascript:submit()">
				<option value="0"> <?php echo JText::_('ALL'); ?> </option>
				<?php var_dump($this->current_campaigns);
				foreach($this->current_campaigns as $campaign) { 
				  echo '<option value="'.$campaign->id.'"';
					if($current_campaigns==$campaign->id) echo ' selected';
						echo '>'.$campaign->label.'</option>'; 
				}
				?>
			</select> 
		</td>
		<td>
			<input style="margin-left:40px" name="spam_suspect" type="checkbox" value="1" <?php echo $spam_suspect==1?'checked=checked':''; ?> />
			<input style="margin-left:70px" name="newsletter" type="checkbox" value="1" <?php echo $newsletter==1?'checked=checked':''; ?> />
		</td>
		<td>
			<input type="submit" name="search" onclick="document.pressed=this.name" value="<?php echo JText::_('SEARCH_BTN'); ?>"/>
			<input style="margin-left:10px" type="submit" name="clear" onclick="document.pressed=this.name" value="<?php echo JText::_('CLEAR_BTN'); ?>"/>
		</td>
	</tr>
</table>
<div id="info_filters"></div>
</fieldset>

<script>
/*function makeArray(items)
{
	try {
		//this converts object into an array in non-ie browsers
		return Array.prototype.slice.call(items);
	}catch (ex) {
		var i = 0,
		len = items.length,
		result = Array(len);
		while(i < len) {
			result[i] = items[i];
			i++;
		}
		return result; 
	}	
}

function clear_campaigns_filter(current_select){
	var selects_object = document.getElementById('filters').getElementsByTagName('select');
	var selects = makeArray(selects_object);
	
	for(var i=0;i<selects.length;i++){
		var select = selects[i];
		var name_s = select.name;
		if(name_s=='schoolyears' || name_s=='campaigns'){
			select.value = 0;
			//window.document.getElementById('info_filters').innerHTML = "<?php echo JText::_('INFO_FILTERS'); ?>";
		}
	}
	return;
}

function clear_groupsEval_filter(current_select){
	var selects_object = document.getElementById('filters').getElementsByTagName('select');
	var selects = makeArray(selects_object);
	
	for(var i=0;i<selects.length;i++){
		var select = selects[i];
		var name_s = select.name;
		if(name_s=='groups_eval'){
			select.value = 0;
			//window.document.getElementById('info_filters').innerHTML = "<?php echo JText::_('INFO_FILTERS'); ?>";
		}
	}
	return;
}*/
</script>