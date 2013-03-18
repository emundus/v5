<?php 
defined('_JEXEC') or die('Restricted access'); 

jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media'.DS.'com_emundus'.DS.'css'.DS );

$document   =& JFactory::getDocument();

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
$session =& JFactory::getSession();
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
<input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<input type="hidden" name="validation_list" value="" />

<?php  echo $this->filters; ?>

<div class="emundusraw">
<?php
if(!empty($this->users)) {
 if($current_user->profile!=16){
	echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_ZIP').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>'; 
	echo '<span class="editlinktip hasTip" title="'.JText::_('SEND_ELEMENTS').'"><input type="image" src="'.$this->baseurl.'/media/com_emundus/images/icones/XLSFile-selected_48.png" name="export_to_xls" onclick="document.pressed=this.name" /></span>'; 
}
?>
</div>
<?php 
	if($tmpl == 'component') {
		echo '<div><h3><img src="'.JURI::Base().'media/com_emundus/images/icones/folder_documents.png" alt="'.JText::_('COMPLETED_APPLICANTS_LIST').'"/>'.JText::_('COMPLETED_APPLICANTS_LIST').'</h3>';
		$document =& JFactory::getDocument();
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
        <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
        </th>
        <th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('NATIONALITY'), 'nationality', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICANT_FOR'), 'profile', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        <th><?php echo JHTML::_('grid.sort', JText::_('SCHOOL_YEAR'), 'c.schoolyear', $this->lists['order_Dir'], $this->lists['order']); ?> </th>
		<th><?php echo JHTML::_('grid.sort', JText::_('SEND_ON'), 'time_date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th><?php echo JHTML::_('grid.sort', JText::_('APPLICATION_FORM_VALIDATION'), 'validated', $this->lists['order_Dir'], $this->lists['order']); ?></th>
        
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
$i=0;
$j=0;
foreach ($this->users as $user) { ?>
	<tr class="row<?php echo $j++%2; ?>">
        <td> <?php 
		echo ++$i+$limitstart; $i++;
		echo "<div class='em_user_id'>#".$user->id."<div>";
        echo $this->actions[$user->id][$user->id];
		?> 
		</td>
		<td><?php 
			if(strtoupper($user->name) == strtoupper($user->firstname).' '.strtoupper($user->lastname)) 
				echo '<strong>'.strtoupper($user->lastname).'</strong><br />'.$user->firstname; 
			else 
				echo '<span class="hasTip" title="'.JText::_('USER_MODIFIED_ALERT').'"><font color="red">'.$user->name.'</font></span>'; 
			?>
		</td>
      <td><?php echo $user->jos_emundus_personal_detail__nationality; ?></td>
      <td>
	  <div class="emundusprofile<?php echo $user->profile; ?>"><?php echo $this->profiles[$user->profile]->label; ?></div>
	  <?php 
	   $query = 'SELECT esp.id, esp.label
					FROM #__emundus_users_profiles AS eup
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id=eup.profile_id
					WHERE eup.user_id = '.$user->id.'
					ORDER BY eup.id';
		$db->setQuery( $query );
		$profiles=$db->loadObjectList();
		$many_profiles = count($profiles)>1?true:false;
		echo '<ul>';
		foreach($profiles as $p){
			if ($p->id == $user->profile && $many_profiles)
				echo '<li class="bold">'.$p->label.' ('.JText::_('FIRST_CHOICE').')</li>';
			else
				echo '<li>'.$p->label.'</li>';
		}
		echo '</ul>';
	   ?></td>
      <td align="left" valign="middle"><?php echo $user->schoolyear; ?></td>
		<td><?php echo JHtml::_('date', $user->registerDate, JText::_('DATE_FORMAT_LC2')); ?></td>
		<td align="center">
        <?php
		if(!EmundusHelperAccess::isAdministrator($user->id) && !EmundusHelperAccess::isCoordinator($user->id)) {
			 echo '<span class="hasTip" title="'.JText::_('APPLICATION_FORM_VALIDATION_NOTE').'">'; ?>
			 <input type="image" name="<?php echo $user->validated>0?'unvalidate|'.$user->id:'validate|'.$user->id; ?>" src="<?php echo $this->baseurl; ?>/media/com_emundus/images/icones/<?php echo $user->validated>0?'tick.png':'publish_x.png' ?>"  onclick="document.pressed=this.name" >
        <?php echo '</span>'; 
		} else { ?>
			<img src="<?php JURI::Base(); ?>/media/com_emundus/images/<?php echo $user->validated>0?'tick.png':'publish_x.png' ?>" alt="<?php echo $user->validated>0?JText::_('VALIDATE_APPLICATION_FORM'):JText::_('UNVALIDATE_APPLICATION_FORM'); ?>"/>
		<?php 
        }
		?>
		</td>	
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
	echo $this->delayAct;
	JHTML::script( 'emundus.js', JURI::Base().'media/com_emundus/js/' );
?>
</script>