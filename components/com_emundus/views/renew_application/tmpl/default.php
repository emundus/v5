<?php 
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'components/com_emundus/style/' );
$current_user = JFactory::getUser();

if($this->statut){
	JError::raiseNotice('YEAR', JText::_( 'ALREADY_APPLY' ));
	echo '<center><h2>'.JText::_( 'RENEW' ).'</h2>';
	echo '<a href="index.php?option=com_emundus&controller=renew_application&task=edit_user&view=renew_application&uid='.$current_user->id.'&up='.$current_user->profile.'">
			<img src="images/emundus/icones/renew.png"/>
		</a></center>';
}
?>