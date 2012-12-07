<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<fieldset style="background-color:#FC0">
<h2><?php echo $this->message->title ; ?></h2>

<div class="message">
	<?php echo  $this->message->text ; ?>
</div>
</fieldset>