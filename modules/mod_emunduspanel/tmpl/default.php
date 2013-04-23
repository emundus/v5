<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emunduspanel/style/' );

if (!empty($tab)) {
?>
<fieldset>
	<?php
    if(isset($user->profile) && $user->profile>0) {
        $name = $user->profile;
        $query='SELECT label,id FROM #__emundus_setup_profiles WHERE id ='.$name;
        $db->setQuery($query);
        $label = $db->loadResult();
        echo '<a href="index.php?option=com_users&view=profile&layout=edit"><h2>'.JText::_('YOUR_PROFILE').' : '.$label. '</h2></a>';
    }
    
    ?>
    <table class="emundus_home_page" ><tr>
    <?php 
    $i=1; $j=1;
	$l = @$user->candidature_posted == 1 ? 2 : '999';
	//die(print_r($user));

    foreach ($tab as $t){ 
		if ($j>$l) 
			break;
        else 
			echo '<td align="center">'.$t.'</td>';
		$j++;
    } 
	
	// Apply again
	$query='SELECT count(id) as cpt FROM #__emundus_setup_campaigns 
			WHERE id NOT IN (
				select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user->id.'
			)';
	$db->setQuery($query);
	$cpt = $db->loadResult();

	if (@$user->applicant == 1 && @$user->candidature_posted == 1 && $cpt > 0) {
		$str = '<a href="index.php?option=com_emundus&view=renew_application"><img src="'.JURI::Base().'media/com_emundus/images/icones/renew.png" /></a>';
		$str .= '<br/><a class="text" href="'.JURI::Base().'index.php?option=com_emundus&view=renew_application">'.JText::_('RENEW_APPLICATION').'</a>';
		echo '<td align="center">'.$str.'</td>';
	}
    ?>
    </tr></table>
</fieldset>
<?php } ?>