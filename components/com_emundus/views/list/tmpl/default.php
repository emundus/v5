<?php 
defined('_JEXEC') or die('Restricted access'); 
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$v = JRequest::getVar('view', null, 'GET', 'none',0);
?>
<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>

<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
    <input type="hidden" name="option" value="com_emundus"/>
    <input type="hidden" name="view" value="<?php echo $v; ?>"/>
    <input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
    <input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
<?php
    echo $this->filters;
	if(!empty($this->users)) { ?>
		<div class="emundusraw">
			<?php echo $this->export_icones; ?>
		</div><?php
		if ($itemid){
			$menu = JSite::getMenu();
			$menuname = $menu->getActive()->title;
		} else $menuname = '';
		
		if($tmpl == 'component') {
				echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.$menuname.'"/>'.$menuname.' : '.$this->current_schoolyear.'</h3>';
				$document = JFactory::getDocument();
				$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
		}else
				echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.$menuname.'"/>'.$menuname.' : '.$this->current_schoolyear.'</legend>'; ?>
        <div class="evaluation_users"><?php 
            if(isset($this->users)&&!empty($this->users)){ ?>
                <table id="userlist" width="100%">
                    <thead>
                        <tr>
                            <td align="center" colspan="18"><?php echo $this->pagination->getResultsCounter(); ?></td>
                        </tr>
                        <tr>
                            <td align="center" colspan="18"><?php echo $this->show_comments; ?></td>
                        </tr>
                        <tr>
                            <?php
                            foreach ($this->header_values as $key=>$value){
                                if($value['name'] == 'user_id'){
									echo '<th align="center" style="font-size:9px;"><input type="checkbox" id="checkall" class="emundusraw" onClick="check_all(\'ud\',this)" />';
                                    //echo JHTML::_('grid.sort', JText::_('#'), $value['name'], $this->lists['filter_order_Dir'], $this->lists['order']);
                                    echo '</th>';
                                }else
                                    echo '<th>'.JHTML::_('grid.sort', JText::_($value['label']), $value['name'], $this->lists['filter_order_Dir'], $this->lists['filter_order']).'</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody><?php 
                        $i=1; $j=0; 
                        foreach($this->users as $evalu){ ?>
                            <tr class="row<?php echo $j++%2; ?>"><?php
                                foreach ($evalu as $key=>$value){ 
                                    if($key=='user_id'){ ?>
                                        <td> <?php 
                                        echo $i+$limitstart; $i++; 
                                        echo $this->actions[$value][$value];
                                        //echo "#".$value;  
                                        ?> 
                                        </td><?php 	
                                    }elseif($key == 'profile'){ 
                                        echo '<td>';
                                        echo $this->profile[$evalu['user_id']];
                                        echo '</td>';
                                    }else
										echo '<td>'.$value.'</td>';
                                } 
								if(isset($this->app_comments))
									echo '<td>'.$this->app_comments[$evalu['user_id']].'</td>';?>
                            </tr><?php 
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo count($evalu)+1; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
                        </tr>
                    </tfoot>
                </table>
        <?php } // end if(isset($this->users)&&!empty($this->users)) ?>
        </div><?php 
		if($tmpl == 'component') echo '</div>';
		else echo '</fieldset>'; ?>		
 		<div class="emundusraw"><?php
            if ($this->incomplete || $this->complete || $this->batch)
                echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/kbackgammon_engine_22x22.png" alt="'.JText::_('BATCH').'"/>'.JText::_('BATCH').'</legend>';  
			echo $this->batch;
            echo $this->incomplete;
            echo $this->complete;
            if ($this->incomplete || $this->complete || $this->batch)
                echo '</fieldset>';
			echo $this->affectEval;
			echo $this->email_evaluator;
			echo $this->email_applicant;?>
		</div><?php   
	//end of !empty($this->users)
	} else echo '<h2>'.JText::_('NO_RESULT').'</h2>'; ?>
</form>

<script><?php 
	echo $this->addElement;
	echo $this->addElementOther;
	echo $this->onSubmitForm; 
	echo $this->delayAct;
	JHTML::script( 'emundus.js', JURI::Base().'media/com_emundus/js/' );?>
</script>