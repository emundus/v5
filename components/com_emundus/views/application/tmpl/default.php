<?php
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
  <form action="" method="post" name="applicant_form" id="applicant_form" onsubmit="return OnSubmitForm();" >
    <div id="identity_card">
      <div class="ui two column grid">
        <div class="column">
          <div class="ui fluid form segment">
            <h3 class="ui header"><?php echo $this->student->name; ?> | <?php echo $this->student->id; ?></h3>
            <div class="content">
              <div class="ui two column divided grid">
                <div class="row">
                  <div class="column">
                    <input id="cb<?php echo $this->student->id; ?>" type="checkbox" checked="checked" value="<?php echo $this->student->id; ?>" name="uid[]" style="display: none;" />
                    <div id="photo">
                      <?php
                                if(!empty($this->userInformations["filename"])) {
                                    echo'<img id="image" class="rounded ui image" src="'.JURI::Base().EMUNDUS_PATH_REL.$this->student->id.'/'.$this->userInformations["filename"].'" width="50%">';
                                } else if(!empty($this->userInformations["gender"])){
                                    echo'<img id="image" class="rounded ui image" src="'.JURI::Base().'media/com_emundus/images/icones/'.strtolower($this->userInformations["gender"]).'_user.png" style="padding:10px 0 0 10px; width:120px;">';
                                }
                                ?>
                      <div class="mini ui icon buttons">
                        <div class="ui button" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>">
                          <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&task=pdf&user=<?php echo $this->student->id; ?>">
                            <i class="file icon"></i> 
                          </a>
                        </div>   
                        <div class="ui button" data-title="<?php echo JText::_('UPLOAD_FILE_FOR_STUDENT'); ?>">
                          <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
                            <i class="attachment icon"></i> 
                          </a>
                        </div>
                        <div class="ui button" data-title="<?php echo JText::_('ADD_COMMENT'); ?>">
                          <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=89&tableid=92&rowid=&jos_emundus_comments___applicant_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
                            <i class="comment icon"></i> 
                          </a>
                        </div>
                        <div class="ui button" data-title="<?php echo JText::_('SEND_EMAIL'); ?>">
                          <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&view=email&tmpl=component&sid=<?php echo $this->student->id; ?>&Itemid=<?php echo $itemid; ?>">
                            <i class="mail icon"></i> 
                          </a>
                        </div>
                        <div class="ui button" data-title="<?php echo JText::_('EXPORT_TO_ZIP'); ?>">
                          <button onclick="document.pressed=this.name;" name="export_zip"> 
                            <i class="archive icon"></i> 
                          </button>
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
                echo '<div class="campaign '.$campaign->campaign_candidature_id.'">';
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

                echo'<div class="campaign icon">';
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
                        echo '<img style="border:0;" src="'.JURI::Base().'media/com_emundus/images/icones/grade-'.$campaign->final_grade.'_16x16.png" /></a>';
                    }
                    echo'</div>';
                    if(EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) && count($this->userCampaigns) > 1) {
                        $delete_link = '<a class=​"ui" name="delete_campaign" data-title="'.JText::_('DELETE_CAMPAIGN').'" onClick="$(\'#confirm_type\').val(this.name); $(\'#campaign_id\').val('.$campaign->campaign_candidature_id.'); $(\'#campaign_table\').val(\'jos_emundus_campaign_candidature\'); $(\'.basic.modal.confirm.campaign\').modal(\'show\');"><i class="trash icon link"></i>​</a>​';
                    }
        ?>
              <a data-html="<?php echo htmlentities($info); ?>" href="#" title="" >
              <div class="title-campaign"><?php echo $campaign->label; ?></div>
              </a><?php echo @$delete_link; ?></div>
            <?php                           
                    
                    echo '<div class="ui divider"></div>';
                   // echo "</div>";
            }
        ?>
          </div>
        </div>
      </div>
    </div>
    <div class="ui fluid accordion">
      
      <div class="title" id="em_application_attachments"> <i class="dropdown icon"></i> <?php echo JText::_('ATTACHMENTS').' - '.$this->attachmentsProgress." % ".JText::_("SENT"); ?> </div>
      <div class="content">
        <div class="actions">
          <button class="ui left red icon button" data-title="<?php echo JText::_('DELETE_SELECTED_ATTACHMENTS'); ?>" onclick="document.pressed=this.name;" name="delete_attachments"> <i class="trash icon"></i> </button>
          <?php
			if (EmundusHelperAccess::asCoordinatorAccessLevel($this->current_user->id)) {
			?>
          <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y:window.getHeight()*0.8,onClose:function(){delayAct('<?php echo $this->student->id; ?>');}}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_fabrik&c=form&view=form&formid=67&tableid=70&rowid=&jos_emundus_uploads___user_id[value]=<?php echo $this->student->id; ?>&student_id=<?php echo $this->student->id; ?>&tmpl=component&iframe=1">
          <button class="ui right icon button" data-title="<?php echo JText::_('UPLOAD_FILE_FOR_STUDENT'); ?>" data-content="<?php echo JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK'); ?>"> <i class="attachment icon"></i> </button>
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
				$path = $attachment->lbl == "_archive"?EMUNDUS_PATH_REL."archives/".$attachment->filename:EMUNDUS_PATH_REL.$this->student->id.'/'.$attachment->filename;
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
				echo '<a href="'.JURI::Base().$path.'" target="_blank">';
				echo $img_dossier.' '. $img_locked.' '.$img_missing.' '.$attachment->value.' <em>'.$attachment->description.'</em>';
				echo '</a> ';
        echo '<i class="help circle icon link icon" data-html="'.htmlentities($info).'"></i>';
				echo '</div>';
				$i++;
			}
		} else echo JText::_('NO_ATTACHMENT');
		?>
      </div>
      <div class="active title" id="em_application_forms"> <i class="dropdown icon"></i> <?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?> </div>
      <div class="active content">
        <div class="actions"> <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&task=pdf&user=<?php echo $this->student->id; ?>">
          <button class="ui icon button" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>"> <i class="large file icon"></i> </button>
          </a>
          <button class="ui icon button" data-title="<?php echo JText::_('EXPORT_TO_ZIP'); ?>" onclick="document.pressed=this.name;" name="export_zip"> <i class="large archive icon"></i> </button>
        </div>
        <?php echo $this->forms; ?> </div>
      <div class="title" id="em_application_comments"> <i class="dropdown icon"></i> <?php echo JText::_('COMMENTS'); ?> </div>
      <div class="content">
        <div class="ui comments">

        <style type="text/css">
  .widget .panel-body { padding:5px; }
  .widget .list-group { margin-bottom: 0; }
  .widget .panel-title { display:inline }
  .widget .label-info { float: right; }
  .widget li.list-group-item {border-radius: 0;border: 0;border-top: 1px solid #ddd;}
  .widget li.list-group-item:hover { background-color: rgba(86,61,124,.1); }
  .widget .mic-info { color: #666666;font-size: 11px; }
  .widget .action { margin-top:5px; }
  .widget .comment-text { font-size: 12px; }
  .widget .btn-block { border-top-left-radius:0px;border-top-right-radius:0px; }
</style>

<div class="comments">
    <div class="row">
        <div class="panel panel-default widget">
            <div class="panel-heading">
                <span class="glyphicon glyphicon-comment"></span>
                <h3 class="panel-title"><?php echo JText::_('COMMENTS'); ?></h3>
                <span id="nb_comment" class="label label-info"><?php echo count($this->userComments); ?></span>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                <?php
        if(count($this->userComments) > 0) {
          $i=0;
          foreach($this->userComments as $comment){ ?>
            <li class="list-group-item comment <?php echo $comment->id; ?>" id="<?php echo $comment->id; ?>">
                <div class="row">
                    <div class="col-xs-10 col-md-11">
                        <div>
                        <?php
                        if($this->_user->id == $comment->user_id) { ?>
                            <a name="delete_comment" class="​&quot;ui&quot;" id="delete_comment" onclick="$('#confirm_type').val(this.name); $('#confirm_id').val(<?php echo $comment->id; ?>); $('.basic.modal.confirm').modal('show');" data-title="<?php echo JText::_('DELETE_COMMENT'); ?>"><i class="trash icon"></i>​</a>​ 
                        <?php } ?>
                           <a href="#"><?php echo $comment->reason; ?></a>
                            <div class="mic-info">
                                <a href="#"><?php echo $comment->name; ?></a> - <?php echo JHtml::_('date', $comment->date, JText::_('DATE_FORMAT_LC2')); ?>
                            </div>
                        </div>
                        <div class="comment-text">
                            <?php echo $comment->comment; ?>
                        </div>

                    </div>
                </div>
            </li>
         <?php
            $i++;
          }
        } else echo JText::_('NO_COMMENT');
        ?>  
                </ul>
            </div>

            <div class="form" id="form">
              <input class="form" placeholder="<?php echo JText::_('COMMENT_REASON');?>" id="comment-title" type="text" style="height:50px !important;width:100% !important;" value="" name="comment-title"/><br>
              <textarea class="form" placeholder="<?php echo JText::_('ENTER_COMMENT');?>"  style="height:200px !important;width:100% !important;"  id="comment-body"></textarea><br>
              <button type="button" onclick="send_comment()" class="btn btn-success"><?php echo JText::_('ADD_COMMENT');?></button>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

var textArea = '<hr>' +
                    '<label class="control-label"><?php echo JText::_('TITLE');?></label><br>' +
                    '<input class="form" id="comment-title" type="text" style="height:50px !important;width:100% !important;" value="" name="comment-title"/><br>' +
                    '<label for="comment" class="control-label"><?php echo JText::_('ENTER_COMMENT');?></label><br>' +
                    '<textarea class="form" style="height:200px !important;width:100% !important;"  id="comment-body"></textarea><br>' + 
                '<button type="button" onclick="send_comment()" class="btn btn-success"><?php echo JText::_('ADD_COMMENT');?></button>';

//$('#form').append(textArea);

function send_comment() {
  var comment = $('#comment-body').val();
  var title = $('#comment-title').val();
  var applicant_id = <?php echo $this->student->id; ?>

  if (comment.length == 0)
  {
      $('#comment-body').attr('style', 'height:250px !important;width:100% !important; border-color: red !important; background-color:pink !important;');
      return;
  }
  $('.modal-body').empty();
  $('.modal-body').append('<div>' +'<p>'+Joomla.JText._('COMMENT_SENT')+'</p>' +'<img src="media/com_emundus/images/icones/loader-line.gif" alt="loading"/>' +'</div>');
  url = 'index.php?option=com_emundus&controller=application&task=addcomment';

  $.ajax({
    type:'POST',
    url:url,
    dataType:'json',
    data:({id:1, applicant_id: applicant_id, title: title, comment:comment}),
    success: function(result)
    {
        
        if(result.status)
        {
            $('#nb_comment').text(parseInt($('#nb_comment').text())+1);
            $('#form').empty();
            $('#form').append('<p class="text-success"><strong>'+result.msg+'</strong></p>');
            var li = ' <li class="list-group-item" id="'+result.id+'">'+
                '<div class="row">'+
                    '<div class="col-xs-10 col-md-11">'+
                        '<div>'+
                            '<a href="#">'+title+'</a>'+
                            '<div class="mic-info">'+
                                '<a href="#"><?php echo $this->_user->name; ?></a> - <?php echo JHtml::_('date', date('Y-m-d H:i:s'), JText::_('DATE_FORMAT_LC2')); ?>'+
                            '</div>'+
                        '</div>'+
                        '<div class="comment-text">'+comment+'</div>'+
                    '</div>'+
                '</div>'+
            '</li>';
            $('.comments .list-group').append(li);
            $('#form').append(textArea);
        }
        else
        {
            $('#form').append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');
        }
    },
    error: function (jqXHR, textStatus, errorThrown)
    {
        console.log(jqXHR.responseText);
    }
  });
}
</script>

        </div>
      </div>
      <div class="title" id="em_application_evaluations"> <i class="dropdown icon"></i> <?php echo JText::_('EVALUATIONS'); ?> </div>
      <div class="content">
        <?php echo $this->actions[$this->student->id][$this->current_user->id][$this->campaign_id]; ?>
        <iframe classe="iframe evaluation" id="em_evaluations" src="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&view=evaluation&layout=evaluation&aid=<?php echo $this->student->id; ?>&tmpl=component&iframe=1&Itemid=<?php echo $itemid; ?>" width="100%" height="400px" frameborder="0" marfin="0" padding="0"></iframe>
      </div>
      <div class="title" id="em_application_emails"> <i class="dropdown icon"></i> <?php echo JText::_('EMAIL_HISTORY'); ?> </div>
      <div class="content">
        <div class="actions"> <a class="modal clean" target="_self" rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&view=email&tmpl=component&sid=<?php echo $this->student->id; ?>&Itemid=<?php echo $itemid; ?>">
          <button class="ui button teal submit labeled icon" data-title="<?php echo JText::_('SEND_EMAIL'); ?>"> <i class="icon mail"></i><?php echo JText::_('SEND_EMAIL'); ?> </button>
          </a> </div>
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
      <div class="title" id="em_application_connexion"> <i class="dropdown icon"></i> <?php echo JText::_('ACCOUNT'); ?> </div>
      <div class="content">
        <table>
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
              <td><?php
            if($this->current_user->authorise('core.manage', 'com_users'))
              echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="'.JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid='.$this->student->id.'&tmpl=component').'">'. $this->student->username .'</a>';
            else
              echo $this->student->username;
            ?></td>
              <td><?php echo JHtml::_('date', $this->student->registerDate, JText::_('DATE_FORMAT_LC2')); ?></td>
              <td><?php echo JHtml::_('date', $this->student->lastvisitDate, JText::_('DATE_FORMAT_LC2')); ?></td>
              <td><?php
            if (isset($this->logged[0]->logoutLink))
              echo '<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/green.png" alt="'.JText::_('ONLINE').'" title="'.JText::_('ONLINE').'" />';
            else
              echo '<img style="border:0;" src="'.JURI::Base().'/media/com_emundus/images/icones/red.png" alt="'.JText::_('OFFLINE').'" title="'.JText::_('OFFLINE').'" />';
            ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <input type="hidden" name="sid" value="<?php echo $this->student->id; ?>" />
    <input type="hidden" value="" name="task" />
    <input type="hidden" value="<?php echo $itemid; ?>" name="itemid" />
    <input type="hidden" value="<?php echo $view; ?>" name="view" />
    <input type="hidden" value="<?php echo $tmpl; ?>" name="tmpl" />
  </form>
</div>
</div>
<!-- Confirm delete comment -->
<div class="ui basic modal confirm">
<input class="input confirm type" id="confirm_type" type="hidden" value="" />
<input class="input confirm id" id="confirm_id" type="hidden" value="" />
<div class="header"> </div>
<div class="content">
  <div class="left"> <i class="comment icon"></i> </div>
  <div class="right"></div>
</div>
<div class="actions">
  <div class="two fluid ui buttons">
    <div class="ui negative labeled icon button"> <i class="remove icon"></i> <?php echo JText::_('JNO'); ?> </div>
    <div class="ui positive right labeled icon button"> <?php echo JText::_('JYES'); ?> <i class="checkmark icon"></i> </div>
  </div>
</div>

<!-- Confirm delete registration campaign -->
<div class="ui basic modal confirm campaign">
  <input class="input confirm type" id="campaign_type" type="hidden" value="" />
  <input class="input confirm id" id="campaign_id" type="hidden" value="" />
  <input class="input confirm id" id="campaign_table" type="hidden" value="" />
  <div class="header"> </div>
  <div class="content">
    <div class="left"> <i class="comment icon"></i> </div>
    <div class="right"></div>
  </div>
  <div class="actions">
    <div class="two fluid ui buttons">
      <div class="ui negative labeled icon button"> <i class="remove icon"></i> <?php echo JText::_('JNO'); ?> </div>
      <div class="ui positive right labeled icon button"> <?php echo JText::_('JYES'); ?> <i class="checkmark icon"></i> </div>
    </div>
  </div>
</div>

<!-- Confirm delete registration course -->
<div class="ui basic modal confirm course">
  <input class="input confirm type" id="course_type" type="hidden" value="" />
  <input class="input confirm id" id="course_id" type="hidden" value="" />
  <input class="input confirm id" id="course_table" type="hidden" value="" />
  <div class="header"> </div>
  <div class="content">
    <div class="left"> <i class="comment icon"></i> </div>
    <div class="right"></div>
  </div>
  <div class="actions">
    <div class="two fluid ui buttons">
      <div class="ui negative labeled icon button"> <i class="remove icon"></i> <?php echo JText::_('JNO'); ?> </div>
      <div class="ui positive right labeled icon button"> <?php echo JText::_('JYES'); ?> <i class="checkmark icon"></i> </div>
    </div>
  </div>
</div>
<script type="application/javascript">
// Confirm delete comment
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
                    $('#nb_comment').text(parseInt($('#nb_comment').text())-1);
                    $('.list-group-item.comment.'+id).fadeOut('slow');
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

// Confirm delete campaign
$('.basic.modal.confirm.campaign')
    .modal('setting', {
        closable  : true,
        onDeny    : function(){
            $(this).modal('hide');
            return false;
        },
        onApprove : function() {
            switch ($('#confirm_type').val()){
                case "delete_campaign" : 
                    var id = $('#campaign_id').val();
                    var table = $('#campaign_table').val();
                    deleteData(id, table);  
                    $('.campaign.'+id).fadeOut('slow');
                    break;

            }
            return true;
        },
        onShow : function() {
            $('.ui.basic.modal.confirm.campaign .content .right').empty();
            $('.ui.basic.modal.confirm.campaign .content .right').text('<?php echo JText::_('DELETE_CAMPAIGN_CONFIRM'); ?>');
            $('.ui.basic.modal.confirm.campaign .header').empty();
            $('.ui.basic.modal.confirm.campaign .header').text('<?php echo JText::_('DELETE_CAMPAIGN'); ?>');
            return true;
        }
    })
;

// Confirm delete course
$('.basic.modal.confirm.course')
    .modal('setting', {
        closable  : true,
        onDeny    : function(){
            $(this).modal('hide');
            return false;
        },
        onApprove : function() {
            switch ($('#confirm_type').val()){
                case "delete_course" : 
                    var id = $('#course_id').val();
                    var table = $('#course_table').val();
                    deleteData(id, table); 
                    $('.course.'+id).fadeOut('slow');
                    break;

            }
            return true;
        },
        onShow : function() {
            $('.ui.basic.modal.confirm.course .content .right').empty();
            $('.ui.basic.modal.confirm.course .content .right').text('<?php echo JText::_('DELETE_COURSE_CONFIRM'); ?>');
            $('.ui.basic.modal.confirm.course .header').empty();
            $('.ui.basic.modal.confirm.course .header').text('<?php echo JText::_('DELETE_COURSE'); ?>');
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
$('.attachment_name i')
    .popup({position : 'bottom right'})
;
$('.campaign a')
    .popup({position : 'bottom left'})
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
			}
		}
	};
	xhr.open("GET", "index.php?option=com_emundus&controller=application&format=raw&task=deletetraining&Itemid=<?php echo $itemid; ?>&id="+id+"&t="+table+"&sid=<?php echo $this->student->id; ?>", true);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.send("&id="+id+"&t="+table);
}

function delayAct(user_id){
	document.applicant_form.action = "index.php?option=com_emundus&view=<?php echo $view; ?>&Itemid=<?php echo $itemid; ?>&sid=<?php echo $this->student->id; ?> <?php if(!empty($tmpl)){ echo'&tmpl='.$tmpl; }?>";
	setTimeout("document.applicant_form.submit()",10) 
}

</script>