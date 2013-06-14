<?php 
defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');

$document   = JFactory::getDocument();

$current_user = JFactory::getUser();
$current_p = JRequest::getVar('profile', null, 'POST', 'none',0);
$current_u = JRequest::getVar('user', null, 'POST', 'none',0);
$current_au = JRequest::getVar('user', null, 'POST', 'none',0);
$current_s = JRequest::getVar('s', null, 'POST', 'none',0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none',0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none',0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);
$validation_list = JRequest::getVar('validation_list', null, 'GET', 'none',0);
$v = JRequest::getVar('view', null, 'GET', 'none',0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$schoolyears = JRequest::getVar('schoolyears', null, 'POST', 'none',0);

// Starting a session.
$session = JFactory::getSession();
$session->clear( 'uid' );
$session->clear( 'profile' );
$session->clear( 'quick_search' );

// Gettig the orderid if there is one.
$s_elements = $session->get('s_elements');
$s_elements_values = $session->get('s_elements_values');

if (count($search)==0) {
	$search = $s_elements;
	$search_values = $s_elements_values;
}

if (count($search)==0) {
	$search = $s_elements;
	$search_values = $s_elements_values;
}

$db = JFactory::getDBO();
//$document->setTitle( JText::_( 'ADMINISTRATIVE_VALIDATION' ) );
?>

<link rel="stylesheet" type="text/css" href= "<?php echo JURI::Base().'/images/emundus/menu_style.css'; ?>" media="screen"/>

<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="menu/includes/ie6.css" media="screen"/>
<![endif]-->

<!-- <div class="componentheading"><?php echo JText::_( 'ADMINISTRATIVE_VALIDATION' ); ?></div> -->

<a href="<?php echo JURI::getInstance()->toString().'&tmpl=component&Itemid='.$itemid; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl.'/images/M_images/printButton.png" alt="'.JText::_('PRINT').'" title="'.JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST" />
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="check"/>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="validation_list" value="" />

<?php  echo $this->filters; ?>
<?php
if(!empty($this->users)) { ?>
	<div class="emundusraw">
		<?php echo $this->export_icones; ?>
	</div>
<?php 
	if($tmpl == 'component') {
		echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</h3>';
		$document = JFactory::getDocument();
		$document->addStyleSheet( JURI::base()."media/com_emundus/css/emundusraw.css" );
	}else{
		echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</legend>';
	}
?>
<table id="userlist" width="100%">
	<thead>
	<tr>
	    <td align="center" colspan="15">
	    	<?php echo $this->pagination->getResultsCounter(); ?>
	    </td>
    </tr>
	<tr align="left">
		<th width="120">
		<?php if($current_user->profile!=16){ ?>
        <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
		<?php } ?>
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'user_id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
        <th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'jos_emundus_personal_detail__last_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NATIONALITY'), 'jos_emundus_personal_detail__nationality', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php 
		echo JHTML::_('grid.sort', JText::_('CAMPAIGN'), 'label', $this->lists['order_Dir'], $this->lists['order']);
		echo ' | '; 
		echo JHTML::_('grid.sort', JText::_('ACADEMIC_YEAR'), 'schoolyear', $this->lists['order_Dir'], $this->lists['order']); 
		echo ' | '; 
		echo JHTML::_('grid.sort', JText::_('SUBMITTED_ON'), 'jos_emundus_campaign_candidature.date_submitted', $this->lists['order_Dir'], $this->lists['order']); 
		?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICATION_FORM_VALIDATION'), 'jos_emundus_declaration__validated', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        
	</tr>
    </thead>
	<tfoot>
		<tr>
        	<td colspan="10"><?php echo $this->statut; ?></td>
		</tr>
        <tr>
			<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
<?php 
$i=1;
$j=0;
foreach ($this->users as $user) { ?>
	<tr class="row<?php echo $j++%2; ?>">
        <td> <?php 
		echo $i+$limitstart; $i++;
		//echo "<div class='em_user_id'>#".$user['user_id']."<div>";
        echo $this->actions[$user['user_id']][$user['user_id']][@$user['campaign_id']];
		?> 
		</td>
		<td>
            <?php 
			if(strtoupper($user['name']) == strtoupper($user['jos_emundus_personal_detail__last_name'].' '.$user['jos_emundus_personal_detail__first_name'])) 
				echo '<strong>'.strtoupper($user['jos_emundus_personal_detail__last_name']).'</strong><br />'.$user['jos_emundus_personal_detail__first_name']; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user['name'].'</font></span>'; 
			?>
		</td>
      <td><?php echo $user['jos_emundus_personal_detail__nationality']; ?></td>
      <td><?php echo $this->campaigns[$user['user_id']][$user['user_id']][@$user->campaign_id]; ?></td>
	  <td><?php echo $this->validate[$user['user_id']]; ?></td>	
	</tr>
<?php } ?>

</table>
<?php 
	if($tmpl == 'component') {
		echo '</div>';
	}else{
		echo '</fieldset>';
	}
?>

  <div class="emundusraw">
	<?php 
	echo '<fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/kbackgammon_engine_22x22.png" alt="'.JText::_('BATCH').'"/>'.JText::_('BATCH').'</legend>';  
	echo $this->batch;
    echo '</fieldset>';
	echo $this->email_applicant; 
	?>
  </div>
</form>

<?php
} else { ?>
<h2><?php echo JText::_('NO_RESULT'); ?></h2>
<?php 
@$j++;
} 
?>

<script>
<?php 
	echo $this->addElement;
	echo $this->onSubmitForm; 
	echo $this->addElementOther;
	echo $this->delayAct;
	JHTML::script( 'emundus.js', JURI::Base().'media/com_emundus/js/' );
?>
</script>