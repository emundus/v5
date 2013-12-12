<?php 
defined('_JEXEC') or die('Restricted access');

/** GET **/
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$v = JRequest::getVar('view', null, 'GET', 'none',0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$aid = JRequest::getVar('aid', null, 'GET', 'none',0);
$current_user = JFactory::getUser();
$applicant = JFactory::getUser($aid);


JHTML::script( 'emundus.js', JURI::Base().'media/com_emundus/js/' );
?>

<!-- Filters -->
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
    <input type="hidden" name="option" value="com_emundus"/>
    <input type="hidden" name="view" value="<?php echo $v; ?>"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/><?php 
	if(!empty($this->users)) { 
		echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('EVALUATION').'"/>'.JText::_('EVALUATION').' : '.$applicant->name.'</legend>';
		?>
		<div class="evaluation_users"><?php 
			if(isset($this->users) && !empty($this->users)){ ?>
                <table id="userlist" width="100%">
                <thead>
                    <tr><td align="center" colspan="18"><?php echo $this->pagination->getResultsCounter(); ?></td></tr>
                    <tr><?php
                        foreach ($this->header_values as $key=>$value){ 
                        	if($value['name'] != 'user' && $value['name'] != 'user_id' && $value['name'] != 'profile' && $value['name'] != 'email' && $value['name'] != 'time_date' && $value['name'] != 'ranking' && $value['name'] != 'comment' && $value['name'] != 'campaign_id' && $value['name'] != 'evaluation_id' && $value['name'] != 'final_grade' && $value['name'] != 'assoc_evaluators' && $value['name'] != 'email_applicant')
								echo '<th>'.JHTML::_('grid.sort', JText::_($value['label']), $value['name'], $this->lists['order_Dir'], $this->lists['order']).'</th>';
                        } ?>
                    </tr>
                </thead>
                <tbody><?php 
					$i=1; $j=0; // var_dump($this->users );
					foreach($this->users as $evalu){ ?>
                        <tr class="row<?php echo $j++%2; ?>" id="<?php echo 'em_line_'.$i.'_'.$evalu['user_id']; ?>"><?php
                            foreach ($evalu as $key=>$value){ ;
								if($key != 'user' && $key != 'user_id' && $key != 'profile' && $key != 'email' && $key != 'time_date' && $key != 'ranking' && $key != 'comment' && $key != 'campaign_id' && $key != 'evaluation_id' && $key != 'final_grade' && $key != 'email_applicant')  {
	                                if($key == 'comment'){
										echo '<td>'.$this->comment[$evalu['user_id']][$evalu['user']].'</td>';
	                                }elseif(empty($value) && $value !== '0' && $key != 'overall' && $key !='application_mark'){
	                                    echo '<td class="red">'.$value.'</td>';
									}else
										echo '<td >'.$value.'</td>';
								}
                            } //end foreach($evalu)
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
<?php   
	//end of !empty($this->users)
	} else echo '<h2>'.JText::_('NO_RESULT').'</h2>';
 ?>
</form>
<script>

<?php 	
	echo $this->addElement;
	echo $this->onSubmitForm; 
	echo $this->delayAct;
	// echo $this->getInput;
?>
</script>