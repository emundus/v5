<?php 
defined('_JEXEC') or die('Restricted access');

/** GET **/
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$v = JRequest::getVar('view', null, 'GET', 'none',0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'array',0);

?>
<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<!-- Filters -->
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
    <input type="hidden" name="option" value="com_emundus"/>
    <input type="hidden" name="view" value="<?php echo $v; ?>"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/><?php 
	echo $this->filters;
	if(!empty($this->users)) { ?>
		<div class="emundusraw">
			<?php echo $this->export_icones; ?>
		</div><?php
		if($tmpl == 'component') {
			echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('EVALUATION').'"/>'.JText::_('EVALUATION').' : '.$this->current_schoolyear.'</h3>';
			$document = JFactory::getDocument();
			$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
		}else
			echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('EVALUATION').'"/>'.JText::_('EVALUATION').' : <div id="lschoolyears">';
				if(isset($schoolyears)){
					$nb = 1;
					foreach ($schoolyears as $schoolyear){
						if(in_array($schoolyear,$this->schoolyears)){
							if(count($schoolyears)==$nb){
								echo $schoolyear;
							}else{
								echo $schoolyear.', ';
							}
						}else{
							if(count($schoolyears)==$nb){
								echo JText::_('ALL');
							}else{
								echo JText::_('ALL').', ';
							}
						}
						$nb++;
					}
				}else{ 
					echo JText::_('ALL'); 
				} 
	echo'</div></legend>';
		?>
		<div class="evaluation_users"><?php 
			if(isset($this->users) && !empty($this->users)){ ?>
                <table id="userlist" width="100%">
                <thead>
                    <tr><td align="center" colspan="18"><?php echo $this->pagination->getResultsCounter(); ?></td></tr>
                    <tr><?php
                        foreach ($this->header_values as $key=>$value){
							if($value['name'] == 'user_id'){
								echo '<th align="center" style="font-size:9px;"><input type="checkbox" id="checkall" class="emundusraw" onClick="check_all(\'ud\',this)" />';
								echo JHTML::_('grid.sort', JText::_('#'), $value['name'], $this->lists['order_Dir'], $this->lists['order']);
								echo '</th>';
							}else
								echo '<th>'.JHTML::_('grid.sort', JText::_($value['label']), $value['name'], $this->lists['order_Dir'], $this->lists['order']).'</th>';
                        } ?>
                    </tr>
                </thead>
                <tbody><?php 
					$i=1; $j=0;
					foreach($this->users as $evalu){ ?>
                        <tr class="row<?php echo $j++%2; ?>" id="<?php echo 'em_line_'.$i.'_'.$evalu['user_id']; ?>"><?php
                            foreach ($evalu as $key=>$value){
								if($key != 'user' && $key != 'campaign_id' && $key != 'evaluation_id' && $key != 'final_grade' && $key != 'ranking') {
                                if($key=='user_id'){ ?>
                                    <td> 
										<?php 
                                        echo $i+$limitstart; $i++; 
                                        echo $this->actions[$value][$evalu['user']][$evalu['campaign_id']];
                                       // echo "#".$value;  
                                        ?>  
                                    </td><?php 	
                                }elseif($key == 'profile'){
                                    echo '<td><div class="emundusprofile'.$evalu['profile'].'">'.$this->profiles_label[$evalu['profile']]->label.'</div></td>';
                                }elseif($key == 'comment'){
									echo '<td>'.$this->comment[$evalu['user_id']][$evalu['user']][$evalu['campaign_id']].'</td>';
                                }elseif(empty($value) && $value !== '0' && $key != 'overall' && $key !='application_mark')
                                    echo '<td class="red">'.$value.'</td>';
								else
									echo '<td>'.$value.'</td>';
								}
                            } //end foreach($evalu)
                            if ( EmundusHelperAccess::isAdministrator($this->_user->id) ||  EmundusHelperAccess::isCoordinator($this->_user->id)  
								)
                           		echo '<td>'.$this->evaluator[$evalu['user_id']].'</td>'; 
						?> 
                        </tr>
					<?php } //end foreach($this->users)?>
                </tbody>
                <tfoot>
                	<tr><td colspan="<?php echo count($evalu)+1; ?>"><?php echo $this->pagination->getListFooter(); ?></td></tr>
                </tfoot>
                </table>
			<?php } //end of if(isset($this->users) && !empty($this->users))?>
		</div><?php 
		if($tmpl == 'component') echo '</div>';
		else echo '</fieldset>'; ?>
		<div class="emundusraw"><?php
			echo $this->affectEval; ?>
		</div>
        <div class="emundusraw"><?php
			echo $this->email; ?>
		</div><?php   
	//end of !empty($this->users)
	} else echo '<h2>'.JText::_('NO_RESULT').'</h2>';
 ?>
    
</form>
<script type="text/javascript">
function check_all() {
 var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
  document.getElementById('cb<?php echo $user['user_id']; ?>|<?php echo $user['campaign_id']; ?>').checked = checked;
<?php } ?>
}

function is_check() {
	var cpt = 0;
	<?php foreach ($this->users as $user) { ?>
  		if(document.getElementById('cb<?php echo $user['user_id']; ?>').checked == true) cpt++;
	<?php } ?>
	if(cpt > 0) return true;
	else return false;
}

<?php 
	echo $this->addElement;
	echo $this->onSubmitForm; 
	echo $this->delayAct;
	JHTML::script( 'emundus.js', JURI::Base().'media/com_emundus/js/' );?>
</script>