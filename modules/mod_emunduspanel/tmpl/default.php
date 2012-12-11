<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emunduspanel/style/' );

if (!empty($tab)) {
?>
<fieldset>
	<?php
    if(isset($user->profile) && $user->profile>0) {
        $name = $user->profile;
        $query='SELECT label,id FROM jos_emundus_setup_profiles WHERE id ='.$name;
        $db->setQuery($query);
        $label = $db->loadResult();
        echo '<a href="index.php?option=com_users&view=profile&layout=edit"><h2>'.JText::_('YOUR_PROFILE').' : '.$label. '</h2></a>';
    }
    
    ?>
    <table class="emundus_home_page" ><tr>
    <?php 
    $i=1;
	//die(print_r($is_text));
    foreach ($tab as $t){
        echo '<td align="center">'.$t.'</td>';
		if($is_text == 1){
			if(($col/2) == $i) echo '</tr><tr>';
			$i++;
		}
    }
    ?>
    </tr></table>
</fieldset>
<?php } ?>