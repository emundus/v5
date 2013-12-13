<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
//JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emundusflow/style/' );
$document = JFactory::getDocument();
$document->addScript( JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );
$document->addStyleSheet( JURI::base()."media/com_emundus/lib/semantic/packaged/css/semantic.min.css" );
$document->addScript( JURI::base()."media/com_emundus/lib/semantic/packaged/javascript/semantic.min.js" );
?>
<?php 
if ($sent>0 && $user->candidature_incomplete == 0) {
	// Apply again
	$query='SELECT count(id) as cpt FROM #__emundus_setup_campaigns 
			WHERE id NOT IN (
			select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user->id.'
			)';
	$db->setQuery($query);
	$cpt = $db->loadResult();
	$complete =  '<i class="large ok sign icon"></i>'.JText::_('APPLICATION_SENT')." : ".$user->campaign_name;
	if($cpt>0)
		$renew =  ' <a href="index.php?option=com_emundus&view=renew_application"><i class="large repeat icon"></i>'.JText::_('RENEW_APPLICATION').'</a>';
} else {
	$complete =  '<i class="large time icon"></i><a href="index.php?option=com_fabrik&c=form&view=form&formid=22&tableid=22&usekey=user&rowid=-1" title="'.JText::_('APPLICATION_NOT_SENT').'">'.JText::_('APPLICATION_NOT_SENT').'</a>'." : ".$user->campaign_name;
}
?>
<div class="ui small steps">
  <div class="ui <?php echo $forms<100?"disabled":"active"; ?> step">
    <?php echo  '<i class="large text file outline icon"></i> '.$forms.'% '.JText::_('FORM_FILLED'); ?>
  </div>
  <div class="ui <?php echo $attachments<100?"disabled":"active"; ?> step">
    <?php echo  '<i class="large attachment icon"></i> '.$attachments.'% '.JText::_('ATTACHMENT_SENT'); ?>
  </div>
  <div class="ui <?php echo $sent<=0?"disabled":"active"; ?> step">
    <?php echo $complete; ?>
  </div>
  </div>
</div>
 <?php echo $renew; ?> 