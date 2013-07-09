<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.noframes');

$app	= JFactory::getApplication();
$template = $app->getTemplate();
$lang->load('tpl_'.$template, JPATH_THEMES.DS.$template);
//$this->form->reset( true );
$this->form->loadFile( dirname(__FILE__) . DS . "registration.xml"); 

$jform = $app->getUserState('com_users.registration.data');

$course = JRequest::getVar('course', null, 'GET', null, 0);

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
$campaign = new EmundusModelCampaign;
$campaigns = $campaign->getCampaignsByCourse($course);
$campaign_id = $campaigns['id'];

?>
<style> #jform_name {border:solid 0px #FFF;} </style>
<br />
<h4><?php echo JText::_("EMUNDUS_REGISTRATION_INSTRUCTIONS"); ?></h4>
<br />

<div class="registration<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register&course='.$course); ?>" method="post" class="form-validate">
<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($fieldset->name); ?>
	<?php if (count($fields)):?>
		<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.
		?>
			<legend><?php echo JText::_($fieldset->label);?></legend>
		<?php endif;?>
			<dl>
		<?php foreach($fields as $field):?>
			<?php if ($field->hidden):?>
				<?php echo $field->input;?>
			<?php else:?>
				<dt>
					<?php echo $field->label; ?>
					<?php if (!$field->required && $field->type!='Spacer'): ?>
						<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
					<?php endif; ?>
				</dt>
				<dd><?php echo ($field->type!='Spacer') ? $field->input : "&#160;";  $this->form->setValue($field->name, $field->group, "rr"); ?></dd>
			<?php endif;?>
		<?php endforeach;?>
			</dl>
		</fieldset>
	<?php endif;?>
<?php endforeach;?>
		<div>
			<button type="submit" class="validate"><?php echo JText::_('JREGISTER');?></button>
			<?php echo JText::_('COM_USERS_OR');?>
			<a href="<?php echo JRoute::_('index.php');?>" title="<?php echo JText::_('JCANCEL');?>"><?php echo JText::_('JCANCEL');?></a>
			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="course" value="<?php echo $course; ?>" />
			<input type="hidden" name="task" value="registration.register" />
			<?php echo JHtml::_('form.token');?>
		</div>
	</form>
</div>
<h1><?php echo JText::_("CONTACT_US_FOR_TECHNICAL_ISSUES"); ?></h1>
<?php
$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
//Detection du browser
if(preg_match('/Safari/', $HTTP_USER_AGENT) && !preg_match('/Konqueror/', $HTTP_USER_AGENT))
	$browser='Safari';
elseif (preg_match('/msie/', $HTTP_USER_AGENT) && !preg_match('/opera/', $HTTP_USER_AGENT))
	$browser='IE';
elseif (preg_match('/opera/', $HTTP_USER_AGENT))
	$browser='Opera';
elseif (preg_match('/Mozilla/', $HTTP_USER_AGENT))
	$browser='FireFox';
else {
	$browser=$HTTP_USER_AGENT;
}

?>

<script>
function check_field(){
	campaign_id = "<?php echo $campaign_id ?>";
	campaign = $('jform_emundus_profile_campaign');
	if(campaign_id != "") { 
		for (var i=0; i<campaign.options.length; ++i) {
			if(campaign.options[i].value == campaign_id)
				campaign.options[i].selected=true;
		}
	} else { campaign.options[0].selected=true; }

	var form_values = new Array();
	<?php 
	foreach($jform as $key => $value) { 
	 	if(is_array($value)) {
	 		foreach($value as $k => $v)
	 			echo 'form_values["jform_'.$key.'_'.$k.'"] = "'.$v.'"; '; 
		} else {
	 	echo 'form_values["jform_'.$key.'"] = "'.$value.'"; ';
		} 
	} 
	?>

	firstname = document.getElementById("jform_emundus_profile_firstname");
	lastname = document.getElementById("jform_emundus_profile_lastname");

    <?php $i=0; foreach($fields as $field){ ?>
		field = document.getElementsByName("<?php echo $field->name; ?>");
		if (field[0] != undefined) { 
			field[0].value = form_values[field[0].id]
			if (field[0].value == "" && "<?php echo $browser; ?>" != "IE")
				field[0].setStyles({backgroundColor: '#F7F2B2'});
			field[0].onblur = function() {
				if ("<?php echo $browser; ?>" != "IE")
					this.setStyles({backgroundColor: '#fff'}); 
				$("jform_name").value = firstname.value + ' ' + lastname.value;
			}
			if ("<?php echo $browser; ?>" != "IE") {
				field[0].onchange = function(){this.setStyles({backgroundColor: '#fff'});}
				field[0].onkeyup = function(){this.setStyles({backgroundColor: '#fff'});}
			}
		}
	<?php } ?>
}
check_field();

</script>