<?php
/**
 * Contacts Custom Form Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */
 ?>
<?php
/*
 This is an example of how to customize a form template, placing your elements in some specific layout,
 rather than using the simple 'list of elements' the Fabrik default templates use.

 In this example, to change it to suit your requirements there are two files you will want to edit:

 default_group.php
 template_css.php.

 You probably won't need to touch anything in this file, as it's all stuff which is
 either controlled by settings on the backend (like 'show title', etc), or you shouldn't touch because
 the form won't work without it (like $form->startTag), or can be more appropriately changed/styled using
 the template_css.php.

*/
?>
<?php if ($this->params->get('show_page_title', 1)) { ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php } ?>
<?php $form = $this->form;
//echo $form->startTag;
if ($this->params->get('show-title', 1)) {?>
<h1><?php echo $form->label;?></h1>
<?php }
echo $form->intro;
echo $form->startTag;
echo $this->plugintop;
$active = ($form->error != '') ? '' : ' fabrikHide';
echo "<div class=\"fabrikMainError fabrikError$active\">$form->error</div>";?>
	<?php
	if ($this->showEmail) {
		echo $this->emailLink;
	}
	if ($this->showPDF) {
		echo $this->pdfLink;
	}
	if ($this->showPrint) {
		echo $this->printLink;
	}

	echo $this->loadTemplate('group');

	echo $this->hiddenFields;
	echo $this->pluginbottom;
	?>
	<div class="fabrikActions"><?php echo $form->resetButton;?> <?php echo $form->submitButton;?>
	<?php echo $form->prevButton?> <?php echo $form->nextButton?> 
	 <?php echo $form->applyButton;?>
	<?php echo $form->copyButton  . " " . $form->gobackButton . ' ' .$this->message ?>
	</div>

<?php

echo $form->endTag;
echo $form->outro;

echo $this->pluginend;
echo FabrikHelperHTML::keepalive();?>