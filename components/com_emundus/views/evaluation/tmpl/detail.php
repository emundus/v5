<?php 
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
$student_id = JRequest::getVar('sid', null, 'GET', 'none',0);
$eval_id = JRequest::getVar('uid', null, 'GET', 'none',0);
$current_user =& JFactory::getUser($student_id);
//$index = JRequest::getVar('index', null, 'GET', 'none',0);

?>
<fieldset>
<legend><?php echo JText::sprintf('COMMENT_APPLICATION',$current_user->name); ?></legend>
<?php 
	$comment =& EmundusHelperList::getComment($student_id,$eval_id);
	if(!empty($comment)) echo str_replace('-','<br />- ',$comment); 
?>
</fieldset>
