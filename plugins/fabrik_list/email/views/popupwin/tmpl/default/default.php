<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
FabrikHelperHTML::framework();
?>
<style>
#emailtable ul{
	list-style:none;
}

#emailtable li{
	background-image:none;
	margin-top:15px;
}
</style>
<div id="emailtable-content">
	<form method="post" enctype="multipart/form-data" action="<?php echo JURI::base();?>index.php" name="emailtable" id="emailtable">
		<p><?php echo JText::sprintf('PLG_LIST_EMAIL_N_RECORDS', $this->recordcount) ?></p>
		<ul>
		<li>
			<label>
				<?php echo JText::_('PLG_LIST_EMAIL_TO') ?><br />
				<?php echo $this->fieldList ?>
			</label>
		</li>
		<li>
			<label>
				<?php echo JText::_('PLG_LIST_EMAIL_SUBJECT') ?><br />
				<input class="inputbox fabrikinput" type="text" name="subject" id="subject" value="<?php echo $this->subject?>" size="50" />
			</label>
		</li>
		<li>
			<label>
				<?php echo JText::_('PLG_LIST_EMAIL_MESSAGE') ?><br />
				<?php $editor = JFactory::getEditor();
				echo $editor->display('message', $this->message, 'message', 75, 10, 75, 10);?>
			</label>
		</li>
		<li style="clear:both"></li>
		<?php if ($this->allowAttachment) {?>
		<li class="attachement">
			<label>
				<?php echo JText::_('PLG_LIST_EMAIL_ATTACHMENTS') ?><br />
				<input class="inputbox fabrikinput" name="attachement[]" type="file" id="attachement" />
			</label>
			<a href="#" class="addattachement">
				<img src="media/com_fabrik/images/add.png" alt="<?php echo JText::_('add');?>" />
			</a>
			<a href="#" class="delattachement">
				<img src="media/com_fabrik/images/del.png" alt="<?php echo JText::_('delete');?>" />
			</a>
		</li>
		<li>
		<?php }?>
			<input type="submit" id="submit" value="<?php echo JText::_('PLG_LIST_EMAIL_SEND') ?>" class="button" />
		</li>
	</ul>
		<input type="hidden" name="option" value="com_fabrik" />
		<input type="hidden" name="controller" value=list.email />
		<input type="hidden" name="task" value="doemail" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="renderOrder" value="<?php echo $this->renderOrder?>" />
		<input type="hidden" name="id" value="<?php echo $this->listid ?>" />
		<input type="hidden" name="recordids" value="<?php echo $this->recordids ?>" />
	</form>
</div>
<?php if ($this->allowAttachment) {?>
<script type="text/javascript"><!--

function watchAttachements() {
	document.getElements('.addattachement').removeEvents();
	document.getElements('.delattachement').removeEvents();

	document.getElements('.addattachement').addEvent('click', function(e) {
		e.stop();
		var li = e.target.findUp('li');
		li.clone().inject(li, 'after');
		watchAttachements();
	});

	document.getElements('.delattachement').addEvent('click', function(e) {
		e.stop();
		if(document.getElements('.addattachement').length > 1) {
			e.target.findUp('li').dispose();
		}
		watchAttachements();
	});
}

window.addEvent('load', function() {
	watchAttachements();
});
--></script>
<?php }?>
