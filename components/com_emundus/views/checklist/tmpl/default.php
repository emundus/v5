<?php 
JHTML::_('behavior.modal'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css/' );

defined('_JEXEC') or die('Restricted access'); 
$user = JFactory::getUser();
$chemin = EMUNDUS_PATH_REL;
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

//if applicant not yet selected
//if($this->isapplicant){ ?>
    <fieldset>
        <legend><?php echo $this->need<2?JText::_('CHECKLIST'):JText::_('RESULTS'); ?></legend>
        <div class = "<?php echo $this->need?'checklist'.$this->need:'checklist'.'0'; ?>" id="info_checklist">
            <h2><?php echo $this->title; ?></h2>
			<?php 
                if ($this->sent && count($this->result) == 0) echo '<h2>'.JText::_('APPLICATION_SENT').'</h2>';
				else echo $this->text;
                if(!$this->need) { ?>
                    <h2><a href="<?php echo $this->sent?'index.php?option=com_emundus&task=pdf':'index.php?option=com_fabrik&c=form&view=form&formid=22&tableid=22'; ?>" class="<?php echo $this->sent?'appsent':'sent'; ?>" target="<?php echo $this->sent?'_blank':''; ?>"><?php echo $this->sent?JText::_('PRINT_APPLICATION'):JText::_('SEND_APPLICATION'); ?></a></h2>
                <?php } ?>
        </div>
    </fieldset>
    <?php 
    if ($this->sent) { 
        if($this->is_other_campaign) {
            echo "<fieldset><legend>".JText::_('RENEW_APPLICATION')."</legend>";
            echo '<p><a href="index.php?option=com_emundus&view=renew_application"><img src="'.JURI::Base().'media/com_emundus/images/icones/renew.png" align="left" /> '.JText::_('RENEW_APPLICATION_DESC').'</a></p>'; 
            echo "</fieldset>";
        }
    } 
    ?>
<?php //} ?>
<p>
<div id="instructions">
	<h3><?php echo $this->instructions->title; ?></h3>
	<?php echo $this->instructions->text; ?>
</div>
</p>
<?php
if (count($this->attachments) > 0) {
?>
<form id="checklistForm" name="checklistForm" onSubmit="return OnSubmitForm();"  method="post" enctype="multipart/form-data">
    <div id="attachment_list">
        <h3><?php echo JText::_('ATTACHMENTS'); ?></h3>
        <h4><?php echo JText::_('UPLOAD_MAX_FILESIZE') . ' = ' . ini_get("upload_max_filesize") . ' '. JText::_('BYTES'); ?></h4>
        <div id="legend">
            <div class="need_missing"><?php echo JText::_('MISSING_DOC'); ?></div>
            <div class="need_ok"><?php echo JText::_('SENT_DOC'); ?></div>
            <div class="need_missing_fac"><?php echo JText::_('MISSING_DOC_FAC'); ?></div>
        </div>
        <?php
        $attachment_list_mand = '<div id="attachment_list_mand">';
        $attachment_list_opt = '<div id="attachment_list_opt">';
        foreach($this->attachments as $attachment) {
            if ($attachment->nb==0) {
                $class= $attachment->mandatory?'need_missing':'need_missing_fac';
            } else {
                $class= 'need_ok';
            }
            $div = '<fieldset id="a'.$attachment->id .'"><legend class="'.$class.'">'.$attachment->value .'<a href="javascript:toggleVisu(\''.$attachment->id .'\')">[+/-]</a></legend>
                <p class="description">'.$attachment->description .'</p>
                <table id="'.$attachment->id .'" border="0"><tbody>';
            if ($attachment->nb>0)
                    foreach($attachment->liste as $item) {
                    $div .= '<tr>
                        <td>';
                        if($item->can_be_viewed==1) {
                        $div .= '<a href="'.$chemin.$user->id .'/'.$item->filename .'" target="_blank"><img src="media/com_emundus/images/icones/viewmag_16x16.png" alt="show" style="vertical-align:middle"/>'.JText::_('VIEW').'</a>';
                        }
                        else { 
                        $div .= JText::_('CANT_VIEW') ;
                        } 
                        $div .= '&nbsp;-&nbsp;' ;
                        if($item->can_be_deleted==1) {
                        $div .= '<a href="?option=com_emundus&task=delete&aid='.$item->id .'&Itemid='.$itemid.'"><img src="media/com_emundus/images/icones/trashcan_full.png"  style="vertical-align:middle" alt="delete"/>'.JText::_('DELETE').'</a>';
                        } 
                        else { 
                        $div .= JText::_('CANT_DELETE'); 
                        } 
                        $div .= '</td>
                        <td>';
                        $div .= empty($item->description)?JText::_('NO_DESC'):$item->description;
                        $div .= '</td></tr>'; 	
                    } 
            if ($attachment->nb<$attachment->nbmax || $user->profile<=4) { 
                $div .= '<tr><td>'.JText::_('FILE').'<br /><input type="hidden" name="attachment[]" value="'.$attachment->id .'"/><input type="hidden" name="label[]" value="'.$attachment->lbl.'"/><input type="file" name="nom[]" size=15%/><br />'.JText::_('SHORT_DESC').'<br /><input type="text" name="description[]" size=35 /></td></tr><tr><td colspan="2"><h2>'. JText::_('PLEASE_ONLY').' '.$attachment->allowed_types.'</h2><em>'.JText::_('MAX_ALLOWED').' '.$attachment->nbmax .'</em></td></tr></tbody><tfoot><tr><td><input name="sendAttachment" type="submit" onclick="document.pressed=this.name" value="'.JText::_('SEND_ATTACHMENT').'"/></td></tr></tfoot>';
            } else { 
                $div .= '<tr><td colspan="2"><p class="description">'. JText::_('NO_MORE').' '.$attachment->value .'<br />'.JText::_('MAX_ALLOWED').' '.$attachment->nbmax .'</p></td></tr></tbody>';
            }
            $div .='</table></fieldset>'; 
            if ($attachment->mandatory)	
                $attachment_list_mand .= $div;
            else 
                $attachment_list_opt .= $div;
        }
            $attachment_list_mand .= '</div>';
            $attachment_list_opt .= '</div>';
            
            echo $attachment_list_mand;
            echo $attachment_list_opt;
        ?>
    </div>
</form>
<?php 
}
?>

<script>
function toggleVisu(baliseId)
  {
  if (document.getElementById && document.getElementById(baliseId) != null)
    {
	if (document.getElementById(baliseId).style.visibility=='visible')
		{
		document.getElementById(baliseId).style.visibility='hidden';
		document.getElementById(baliseId).style.display='none';
		}
	else
		{
		document.getElementById(baliseId).style.visibility='visible';
		document.getElementById(baliseId).style.display='block';
		}
    }
  }
<?php foreach($this->attachments as $attachment) { ?>
  document.getElementById('<?php echo $attachment->id; ?>').style.visibility='<?php echo ($attachment->mandatory && $attachment->nb==0)?'visible':'hidden'; ?>';
  document.getElementById('<?php echo $attachment->id; ?>').style.display='<?php echo ($attachment->mandatory && $attachment->nb==0)?'block':'none'; ?>';
<?php } ?>

function OnSubmitForm() {
	var btn = document.getElementsByName(document.pressed);
	btn[0].disabled="disabled";
	btn[0].value="<?php echo JText::_('SENDING_ATTACHMENT'); ?>";
	btn[1].disabled="disabled";
	btn[1].value="<?php echo JText::_('SENDING_ATTACHMENT'); ?>";
	switch(document.pressed) {
		case 'sendAttachment': 
			document.checklistForm.action ="index.php?option=com_emundus&task=upload&Itemid=<?php echo $itemid; ?>" 
		break;
		default: return false;
	}
	return true;
}
</script>