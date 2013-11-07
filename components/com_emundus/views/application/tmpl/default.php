﻿<?php
defined('_JEXEC') or die('Restricted access'); 

$itemid 	= JRequest::getVar('Itemid', null, 'GET', 'none',0);
$view 		= JRequest::getVar('view', null, 'GET', 'none',0);
$task 		= JRequest::getVar('task', null, 'GET', 'none',0);
$tmpl 		= JRequest::getVar('tmpl', null, 'GET', 'none',0);
 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.modal');

function age($naiss) {
	@list($annee, $mois, $jour) = split('[-.]', $naiss);
	$today['mois'] = date('n');
	$today['jour'] = date('j');
	$today['annee'] = date('Y');
	$annees = $today['annee'] - $annee;
	if ($today['mois'] <= $mois) {
		if ($mois == $today['mois']) {
		if ($jour > $today['jour'])
			$annees--;
	}
	else
		$annees--;
	}
	return $annees;
}

?>
<div class="ui piled segment">
<form action="" name="applicant_form" method="POST" onsubmit="return OnSubmitForm();" >
<div id="identity_card">
<div class="ui two column grid">
    <div class="column">
        <div class="ui fluid form segment">
            <h3 class="ui header"><?php echo JText::_('APPLICANT'); ?></h3>
            <div class="content">
                <div class="ui two column divided grid">
                    <div class="row">
                        <div class="column">
                            <input id="cb<?php echo $this->student->id; ?>" type="checkbox" checked="" value="<?php echo $this->student->id; ?>" name="uid[]" style="display: none;">
                            <div id="photo">
                                <?php
                                if(!empty($this->userInformations["filename"])) {
                                    echo'<img id="image" class="rounded ui image" src="'.JURI::Base().EMUNDUS_PATH_REL.$this->student->id.'/'.$this->userInformations["filename"].'" width="50%">';
                                } else if(!empty($this->userInformations["gender"])){
                                    echo'<img id="image" class="rounded ui image" src="'.JURI::Base().'media/com_emundus/images/icones/'.strtolower($this->userInformations["gender"]).'_user.png" style="padding:10px 0 0 10px; width:120px;">';
                                }
                                ?>
                                <div class="ui secondary inverted segment"><i class="sign icon"></i><?php echo $this->student->id; ?>
                                <div class="ui buttons">
                                <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&task=pdf&user=<?php echo $this->student->id; ?>">
                                    <button class="mini ui icon button" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>">
                                        <i class="file icon"></i>
                                    </button>
                                </a>
                                <button class="mini ui icon button" data-title="<?php echo JText::_('EXPORT_TO_ZIP'); ?>" onClick="document.pressed=this.name;" name="export_zip">
                                    <i class="archive icon"></i>
                                </button>
                                <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
                                    <button class="mini ui icon button" data-title="<?php echo JText::_('UPLOAD_FILE_FOR_STUDENT'); ?>" data-content="<?php echo JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK'); ?>">
                                        <i class="attachment basic icon"></i>
                                    </button>
                                </a>
                                <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=89&tableid=92&rowid=&jos_emundus_comments___applicant_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
                                    <button class="mini ui icon button" data-title="<?php echo JText::_('ADD_COMMENT'); ?>">
                                        <i class="comment basic icon"></i>
                                    </button>
                                </a>
                                <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&view=email&tmpl=component&sid=<?php echo $this->student->id; ?>&Itemid=<?php echo $itemid; ?>">
                                    <button class="mini ui icon button" data-title="<?php echo JText::_('SEND_EMAIL'); ?>">
                                        <i class="mail basic icon "></i>
                                    </button>
                                </a>
                                </div>
                            </div>
                    </div>
                </div>
                        <div class="column">
                            <div id="informations">
                                <div class="ui list">
                                    <?php
                                    foreach ($this->profile as $key => $value) {
                                        echo '<i class="item right"></i>'.JText::_(strtoupper($key)).' : <b>'.$value.'</b>';
                                    }
                                    foreach ($this->userDetails as $details) {
                                        //$params = json_decode($details->params); print_r($params);
                                        if ($details->element_plugin == "date") {
                                            $params = json_decode($details->params);
                                            $value = strftime($params->date_form_format, strtotime($details->element_value));
                                            if ($details->element_name == "birth_date") {
                                                $value .= ' ('.age($this->userInformations['birthdate']).' '.JText::_('YEARS_OLD').')';
                                            }
                                        } else
                                            $value = $details->element_value;
                                        echo '<i class="item right"></i>'.$details->element_label.' : <b>'.$value.'</b>';
                                    }
                                    echo '<i class="item right"></i><a href="mailto:'.$this->student->email.'">'.$this->student->email.'</a>';
                                    echo '<i class="item right"></i>'.JText::_('PROFILE').' : <b>'.$this->userInformations['profile'].'</b>';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
</div>


            </div>
        </div>
    </div>

    <div class="column">
        <div class="ui fluid form segment">
            <h3 class="ui header"><?php echo JText::_('CAMPAIGN'); ?></h3>
            <div class="content">
                <?php
            foreach($this->userCampaigns as $campaign){
                $info = '<div class="ui list">
                          <div class="item">
                            <div class="header">'. $campaign->training.'</div>
                          <div class="item">
                            <div class="header">'. JText::_('ACADEMIC_YEAR').'</div>'.$campaign->year.'
                          </div>';
                if($campaign->submitted==1){
                     $info .= '<div class="item">
                            <div class="header">'. JText::_('SUBMITTED').'</div>'.JText::_('JYES').'
                          </div>
                          <div class="item">
                            <div class="header">'. JText::_('DATE_SUBMITTED').'</div>'.JHtml::_('date', $campaign->date_submitted, JText::_('DATE_FORMAT_LC2')).'
                          </div>';
                } else {
                     $info .= '<div class="item">
                            <div class="header">'. JText::_('SUBMITTED').'</div>'.JText::_('JNO').'
                          </div>';
                }
                if(!empty($campaign->result_sent) && $campaign->result_sent==1){
                    $info .= '<div class="item">
                            <div class="header">'. JText::_('RESULT_SENT').'</div>'.JText::_('SENT').'
                          </div>
                          <div class="item">
                            <div class="header">'. JText::_('DATE_RESULT_SENT').'</div>'.JHtml::_('date', $campaign->date_result_sent, JText::_('DATE_FORMAT_LC2')).'
                          </div>';
                } else {
                    $info .= '<div class="item">
                            <div class="header">'. JText::_('RESULT_SENT').'</div>'.JText::_('NOT_SENT').'
                          </div>';
                }
                $info .= '</div>';

                echo'<div class="icon">';
                if($campaign->submitted==0){
                ?>
                <a data-title="<?php echo JText::_('SUBMITTED'); ?>" data-content="<?php echo JText::_('JNO'); ?>" href="#" title="" >
                    <?php
                    echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/publish_x.png" style="margin-right:20px;" />';
                    echo '</a>';
                    }else{
                    if($campaign->result_sent==0){
                    ?>
                    <a data-title="<?php echo JText::_('SUBMITTED'); ?>" data-content="<?php echo JText::_('JYES'); ?>" href="#" title="" >
                        <?php
                        echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/tick.png" />
							</a>';
                        ?>
                        <a data-title="<?php echo JText::_('RESULT_SENT'); ?>" data-content="<?php echo JText::_('JNO'); ?>" href="#" title="" >
                            <?php
                            echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/email_not_send.png" />
							</a>';
                            }else if($campaign->result_sent==1){
                            ?>
                            <a data-title="<?php echo JText::_('SUBMITTED'); ?>" data-content="<?php echo JText::_('JYES'); ?>" href="#" title="" >
                                <?php
                                echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/tick.png" />
							</a>';
                                ?>
                                <a data-title="<?php echo JText::_('RESULT_SENT'); ?>" data-content="<?php echo JText::_('JYES'); ?>" href="#" title="" >
                                    <?php
                                    echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/email_send.png" />
							</a>';
                                    }
                                    }
                                    $tab = array (2,3,4);
                                    if(!empty($campaign->final_grade) && in_array($campaign->final_grade,$tab)){
                                    $contenu = '';
                                    if($campaign->final_grade==4){
                                        $contenu.=JText::_("ACCEPTED");
                                    }else if($campaign->final_grade==3){
                                        $contenu.=JText::_("WAITING_LIST");
                                    }else if($campaign->final_grade==2){
                                        $contenu.=JText::_("REJECTED");
                                    }
                                    ?>
                                    <a data-title="<?php echo JText::_('FINAL_GRADE'); ?>" data-content="<?php echo $contenu; ?>" href="#" title="" >
                                        <?php
                                        echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/grade-'.$campaign->final_grade.'_16x16.png" />
						</a>';
                                        }
                                        echo'</div>';
                                        ?>
                                        <div class="campaign">
                                            <a data-html="<?php echo htmlentities($info); ?>" href="#" title="" >
                                            <?php
                                            echo'<div class="title-campaign">'.$campaign->label.'</div>
				                            </a></div>
				                            <div class="ui divider"></div>';
                                            }
                                            ?>
            </div>
        </div>
    </div>
</div>

</div>

<div class="ui fluid accordion">
    <div class="title" id="em_application_connexion">
        <i class="dropdown icon"></i>
	<?php echo JText::_('ACCOUNT'); ?>
    </div>
    <div class="content">
	<table class="adminlist">
			<thead>
				<tr>
					<th><strong><?php echo JText::_('USERNAME'); ?></strong></th>
					<th><strong><?php echo JText::_('ACCOUNT_CREATED_ON');?></strong></th>
					<th><strong><?php echo JText::_('LAST_VISIT');?></strong></th>
					<th><strong><?php echo JText::_('STATUS');?></strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>

					<td class="center">
						<?php
						if($this->current_user->authorise('core.manage', 'com_users'))
							echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="'.JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid='.$this->student->id.'&tmpl=component').'">'. $this->student->username .'</a>';
						else
							echo $this->student->username;
						?>
					</td>
					<td class="center">
						<?php echo JHtml::_('date', $this->student->registerDate, JText::_('DATE_FORMAT_LC2')); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('date', $this->student->lastvisitDate, JText::_('DATE_FORMAT_LC2')); ?>
					</td>
					<td class="center">
						<?php
						if (isset($this->logged[0]->logoutLink))
							echo '<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/green.png" alt="'.JText::_('ONLINE').'" title="'.JText::_('ONLINE').'" />';
						else
							echo '<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/red.png" alt="'.JText::_('OFFLINE').'" title="'.JText::_('OFFLINE').'" />';
						?>
					</td>
				</tr>
			</tbody>
		</table>
    </div>
    <div class="title" id="em_application_attachments">
        <i class="dropdown icon"></i>
	<?php echo JText::_('ATTACHMENTS').' - '.$this->attachmentsProgress." % ".JText::_("SENT"); ?>
    </div>
    <div class="content">
			<div class="actions">
                <button class="ui left red icon button" data-title="<?php echo JText::_('DELETE_SELECTED_ATTACHMENTS'); ?>" onClick="document.pressed=this.name;" name="delete_attachments">
                    <i class="trash icon"></i>
                </button>

			<?php
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id)) {
			?>
                <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8,onClose:function(){delayAct('<?php echo $this->student->id; ?>');}}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
                    <button class="ui right icon button" data-title="<?php echo JText::_('UPLOAD_FILE_FOR_STUDENT'); ?>" data-content="<?php echo JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK'); ?>">
                    <i class="large attachment basic icon"></i>
                    </button>
                </a>
            <?php
			 }
			?>
		</div>
		
		<?php
		if(count($this->userAttachments) > 0) { 
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
				echo '<div id="checkall-attachment"><input type="checkbox" name="attachments" id="checkall1" onClick="check_all(this.id)"/><label for="checkall1"><strong>'.JText::_('SELECT_ALL').'</strong></label></div>';
			$i=0;
			foreach($this->userAttachments as $attachment){
				$path = $attachment->id == 27?EMUNDUS_PATH_REL."archives/".$attachment->filename:EMUNDUS_PATH_REL.$this->student->id.'/'.$attachment->filename;
				$img_missing = (!file_exists($path))?'<img style="border:0;" src="media/com_emundus/images/icones/agt_update_critical.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
				$img_dossier = (is_dir($path))?'<img style="border:0;" src="media/com_emundus/images/icones/dossier.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
				$img_locked = (strpos($attachment->filename, "_locked") > 0)?'<img src="media/com_emundus/images/icones/encrypted.png" />':"";

                $info = '<div class="ui list">
                          <div class="item">
                            <div class="header">'. $img_locked . JText::_('ATTACHMENT_FILENAME').'</div>'.$attachment->filename.'
                          <div class="item">
                            <div class="header">'. JText::_('ATTACHMENT_DESCRIPTION').'</div>'.$attachment->description.'
                          </div>
                          <div class="item">
                            <div class="header">'. JText::_('ATTACHMENT_DATE').'</div>'.JHtml::_('date', $attachment->timedate, JText::_('DATE_FORMAT_LC2')).'
                          </div>
                          <div class="item">
                            <div class="header">'. JText::_('CAMPAIGN').'</div>'.$attachment->campaign_label.'
                          </div>
                           <div class="item">
                            <div class="header">'. JText::_('ACADEMIC_YEAR').'</div>'.$attachment->year.'
                          </div>
                        </div>';
				
				echo '<div class="attachment_name">';
				if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id))
					echo '<input type="checkbox" name="attachments[]" id="aid'.$attachment->aid.'" value="'.$attachment->aid.'" />';
				echo '<a href="'.JURI::Base().$path.'" target="_blank" data-html="'.htmlentities($info).'">';
				echo '<label for="aid_'.$i.'">'.$img_dossier.' '. $img_locked.' '.$img_missing.' '.$attachment->value.' <em>'.$attachment->description.'</em></label>';
				echo '</a> ';
				echo '</div>';
				$i++;
			}
		} else echo JText::_('NO_ATTACHMENT');
		?>
	</div>

    <div class="active title" id="em_application_forms">
        <i class="dropdown icon"></i>
        <?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?>
    </div>
	<div class="active content">
		<div class="actions">
            <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&task=pdf&user=<?php echo $this->student->id; ?>">
                <button class="ui icon button" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>">
                    <i class="large file icon"></i>
                </button>
            </a>
            <button class="ui icon button" data-title="<?php echo JText::_('EXPORT_TO_ZIP'); ?>" onClick="document.pressed=this.name;" name="export_zip">
                <i class="large archive icon"></i>
            </button>

		</div>
		<?php echo $this->forms; ?>
	</div>

    <div class="title" id="em_application_comments">
        <i class="dropdown icon"></i>
        <?php echo JText::_('COMMENTS'); ?>
    </div>
	<div class="content">
		<div class="actions">
            <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=89&tableid=92&rowid=&jos_emundus_comments___applicant_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
                <button class="ui button teal submit labeled icon" data-title="<?php echo JText::_('ADD_COMMENT'); ?>">
                    <i class="icon edit"></i><?php echo JText::_('ADD_COMMENT'); ?>
                </button>
            </a>
		</div>

        <div class="ui comments">
		<?php
		if(count($this->userComments) > 0) { 
			$i=0;
			foreach($this->userComments as $comment){ ?>
                <div class="comment <?php echo $comment->id; ?>" id="comment-box_<?php echo $comment->id; ?>'">
                    <div class="content"  id="comment_content_<?php echo $comment->id; ?>'">
                        <a class=​"ui" name="delete_comment" data-title="<?php echo JText::_('DELETE_COMMENT'); ?>" onClick="$('#confirm_type').val(this.name); $('#confirm_id').val(<?php echo $comment->id; ?>); $('.basic.modal.confirm').modal('show');"><i class="trash icon"></i>​</a>​

                        <a class="author"><?php echo $comment->name; ?></a>
                        <div class="metadata">
                            <div class="date"><?php echo JHtml::_('date', $comment->date, JText::_('DATE_FORMAT_LC2')); ?></div>
                        </div>
                        <div class="text"><h3><?php echo $comment->reason; ?></h3><?php echo $comment->comment; ?></div>
                    </div>
                    <div class="ui clearing divider"></div>
                </div>
        <?php
				$i++;
			}
		} else echo JText::_('NO_COMMENT');
		?>
       </div>
	</div>
    <div class="title" id="em_application_evaluations">
        <i class="dropdown icon"></i>
        <?php echo JText::_('EVALUATIONS'); ?>
    </div>
    <div class="content">
        <iframe classe="iframe evaluation" id="em_evaluations" src="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&view=evaluation&layout=evaluation&aid=1572&tmpl=component&iframe=1&Itemid=143" width="100%" height="400px" frameborder="0" marfin="0" padding="0"></iframe>
    </div>
    <div class="title" id="em_application_emails">
        <i class="dropdown icon"></i>
        <?php echo JText::_('EMAIL_HISTORY'); ?>
    </div>
	<div class="content">
        <div class="actions">
            <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&view=email&tmpl=component&sid=<?php echo $this->student->id; ?>&Itemid=<?php echo $itemid; ?>">
                    <button class="ui button teal submit labeled icon" data-title="<?php echo JText::_('SEND_EMAIL'); ?>">
                        <i class="icon mail"></i><?php echo JText::_('SEND_EMAIL'); ?>
                    </button>
            </a>
        </div>

	<?php
		if(!empty($this->email['from'])){
			echo'
			<div id="email_sent">
				<div class="email_title">'.JText::_('EMAIL_SENT').'</div>
			';
			foreach($this->email['from'] as $email){
					echo'
					<div class="email">
						<div class="email_subject">
							'.$email->subject.'
						</div>
						<div class="email_details">
							<div class="email_to">
								<div class="email_legend">'.JText::_('TO').' : </div>
								<div class="email_to_text">'.strtoupper($email->lastname).' '.strtolower($email->firstname).' ('.$email->email.') </div>
							</div>
							<div class="email_date">
								'.JHtml::_('date', $email->date_time, JText::_('DATE_FORMAT_LC2')).'
							</div>
						</div>
						<div class="email_message">
							<div class="email_legend">'.JText::_('MESSAGE').' : </div>
							'.$email->message.'
						</div>
					</div>
					';
			}
			echo'
			</div>
			';
		}
		if(!empty($this->email['to'])){
			echo'
			<div id="email_received">
				<div class="email_title">'.JText::_('EMAIL_RECEIVED').'</div>
			';
			foreach($this->email['to'] as $email){
					echo'
					<div class="email">
						<div class="email_subject">
							'.$email->subject.'
						</div>
						<div class="email_details">
							<div class="email_from">
								'.strtoupper($email->lastname).' '.strtolower($email->firstname).' ('.$email->email.') 
							</div>
							<div class="email_date">
								'.JHtml::_('date', $email->date_time, JText::_('DATE_FORMAT_LC2')).'
							</div>
						</div>
						<div class="email_message">
							<div class="email_legend">'.JText::_('MESSAGE').' : </div>
							'.$email->message.'
						</div>
					</div>
					';
			}
			echo'
			</div>
			';
		}
	?>
	</div>
</div>
<input type="hidden" name="sid" value="<?php echo $this->student->id; ?>" />
<input type="hidden" value="" name="task">
<input type="hidden" value="<?php echo $itemid; ?>" name="itemid">
<input type="hidden" value="<?php echo $view; ?>" name="view">
<input type="hidden" value="<?php echo $tmpl; ?>" name="tmpl">
</form>
</div>

<div class="ui basic modal confirm">
    <input class="input confirm type" id="confirm_type" type="hidden" value="">
    <input class="input confirm id" id="confirm_id" type="hidden" value="">
    <div class="header"> </div>
    <div class="content">
        <div class="left">
            <i class="comment icon"></i>
        </div>
        <div class="right"></div>
    </div>
    <div class="actions">
        <div class="two fluid ui buttons">
            <div class="ui negative labeled icon button">
                <i class="remove icon"></i>
                <?php echo JText::_('JNO'); ?>
            </div>
            <div class="ui positive right labeled icon button">
                <?php echo JText::_('JYES'); ?>
                <i class="checkmark icon"></i>
            </div>
        </div>
    </div>
</div>
<script type="application/javascript">

$('.basic.modal.confirm')
    .modal('setting', {
        closable  : true,
        onDeny    : function(){
            $(this).modal('hide');
            return false;
        },
        onApprove : function() {
            switch ($('#confirm_type').val()){
                case "delete_comment" :
                    var id = $('#confirm_id').val();
                    deleteComment(id);
                    $('.comment.'+id).fadeOut('slow');
                    break;

            }
            return true;
        },
        onShow : function() {
            $('.ui.basic.modal.confirm .content .right').empty();
            $('.ui.basic.modal.confirm .content .right').text('<?php echo JText::_('DELETE_COMMENT_CONFIRM'); ?>');
            $('.ui.basic.modal.confirm .header').empty();
            $('.ui.basic.modal.confirm .header').text('<?php echo JText::_('DELETE_COMMENT'); ?>');
            return true;
        }
    })
;

$('.ui.fluid.accordion')
    .accordion()
;
$('.ui.fluid.accordion')
    .accordion('open', document.location.hash.substring(1))
;

$('.ui.icon')
    .popup({position : 'bottom center'})
;
$('.ui.button')
    .popup({position : 'bottom center'})
;
$('a')
    .popup({position : 'bottom right'})
;
$('.campaign a')
    .popup({position : 'bottom center'})
;
$('.ui.label')
    .popup({position : 'bottom right'})
;

$('#em_evaluations').contents().find('body').css({"min-height": "100", "overflow" : "hidden"});
setInterval( "$('em_evaluations').height($('em_evaluations').contents().find('body').height() + 100)", 1 );

function check_all(id) {
	var checked = document.getElementById(id).checked;
	var name = document.getElementById(id).name;
	if(name=="attachments"){
		var checkbox = document.getElementsByName('attachments[]');
		for (i=0;i< checkbox.length;i++){
			checkbox[i].checked=checked;
		}
	}
	if(name=="comments"){
		var checkbox = document.getElementsByName('cid[]');
		for (i=0;i< checkbox.length;i++){
			checkbox[i].checked=checked;
		}
	}
}
function OnSubmitForm() { 
	if(typeof document.pressed !== "undefined") { 
		document.applicant_form.task.value = "";
		var button_name=document.pressed.split('|');
		switch(button_name[0]) {
			case "export_zip": 
				document.applicant_form.task.value = "export_zip";
				document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=export_zip";
			break;
			case "export_to_xls": 
				document.applicant_form.task.value = "transfert_view";
				document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=transfert_view&v=<?php echo $view; ?>";
			break;
			case "applicant_email": 
				document.applicant_form.task.value = "applicantEmail";
				document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=applicantEmail";
			break;
			case "default_email": 
				if (confirm("<?php echo JText::_("CONFIRM_DEFAULT_EMAIL"); ?>")) {
					document.applicant_form.task.value = "defaultEmail";
					document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&task=defaultEmail";
				} else 
					return false;
			break;
			case "delete_attachments": 
				document.applicant_form.task.value = "delete_attachments";
				if (confirm("<?php echo JText::_("CONFIRM_DELETE_SELETED_ATTACHMENTS"); ?>")) {
	        		document.applicant_form.action ="index.php?option=com_emundus&view=<?php echo $view; ?>&controller=<?php echo $view; ?>&task=delete_attachments&Itemid=<?php echo $itemid; ?>&sid=<?php echo $this->student->id; ?>";
			 	} else 
			 		return false;
			break;
			default: return false;
		}
		return true;
	}
}

function getXMLHttpRequest() {
	var xhr = null;
	 
	if (window.XMLHttpRequest || window.ActiveXObject) {
		if (window.ActiveXObject) {
			try {
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
		} else {
			xhr = new XMLHttpRequest();
		}
	} else {
		alert("Votre navigateur ne supporte pas l\'objet XMLHTTPRequest...");
		return null;
	}
	 
	return xhr;
}
		
function deleteComment(comment_id){
	var xhr = getXMLHttpRequest();
	xhr.onreadystatechange = function()
	{
		if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
		{
			if(xhr.responseText!="SQL Error"){
                return true;
				/*var comment = (($('comment_'+comment_id).parentNode).parentNode).id;
				var comment_content = ($('comment_'+comment_id).parentNode).id;
				var comment_icon = $('comment_'+comment_id);
				var i;
				for (i=0;i<comment_icon.childNodes.length;i++)
				{
					comment_icon.childNodes[i].src = "<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/trash.png";
					comment_icon.childNodes[i].onclick = null;
				}
				$(comment).style.background="#B0B4B3";
				$(comment_content).style.background="#B0B4B3";
				$(comment).style.color="#FFFFFF";
				$(comment_content).style.color="#FFFFFF";
				$(comment).style.textDecoration="line-through";
				$(comment_content).style.textDecoration="line-through";*/
			}else{
				alert(xhr.responseText);
                return false;
			}
            return true;
		}
	};
	xhr.open("GET", "index.php?option=com_emundus&controller=application&format=raw&task=deletecomment&Itemid=<?php echo $itemid; ?>&comment_id="+comment_id, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send("&comment_id="+comment_id);
    return true;
}

function deleteData(id, table){ 
	var xhr = getXMLHttpRequest();
	xhr.onreadystatechange = function()
	{
		if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
		{
			if(xhr.responseText!="SQL Error"){
				var training = ($('training_'+id).parentNode).id;
				var training_content = ($('training_'+id).parentNode).id;
				var training_icon = $('training_'+id);
				var i;
				for (i=0;i<training_icon.childNodes.length;i++)
				{
					training_icon.childNodes[i].src = "<?php echo JURI::Base(); ?>/media/com_emundus/images/icones/trash.png";
					training_icon.childNodes[i].onclick = null;
				}
				$(training).style.background="#B0B4B3";
				$(training_content).style.background="#B0B4B3";
				$(training).style.color="#FFFFFF";
				$(training_content).style.color="#FFFFFF";
				$(training).style.textDecoration="line-through";
				$(training_content).style.textDecoration="line-through";
			}else{
				alert(xhr.responseText);
			}
		}
	};
	xhr.open("GET", "index.php?option=com_emundus&controller=application&format=raw&task=deletetraining&Itemid=<?php echo $itemid; ?>&id="+id+"&t="+table, true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send("&id="+id+"&t="+table);
}

function delayAct(user_id){
	document.applicant_form.action = "index.php?option=com_emundus&view=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&sid=<?php echo $this->student->id; ?> <?php if(!empty($tmpl)){ echo'&tmpl='.$tmpl; }?>";
	setTimeout("document.applicant_form.submit()",10) 
}

</script>