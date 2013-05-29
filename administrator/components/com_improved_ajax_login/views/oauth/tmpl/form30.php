<?php
/*-------------------------------------------------------------------------
# com_improved_ajax_login - Improved_AJAX_Login & Register
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<?php
	// Set toolbar items for the page
	$edit		= JRequest::getVar('edit',true);
	JToolBarHelper::title( JText::_( 'Improved AJAX Login & Register: Edit Social Settings' ) );
	JToolBarHelper::save();
	if (!$edit)  {
		JToolBarHelper::cancel();
	} else {
		// for existing items the button is renamed `close`
		JToolBarHelper::cancel( 'cancel', 'Close' );
	}
?>

<script language="javascript" type="text/javascript">
	submitbutton = function(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
    if (form.published[1].checked)
		  // do field validation
      if (form.app_id.value == ""){
  			alert( "<?php echo JText::_( 'You must add the App/Client ID.', true ); ?>" );
  		} else if (form.app_secret.value == ""){
  			alert( "<?php echo JText::_( 'You must add the App/Client secret', true ); ?>" );
  		} else {
  			submitform( pressbutton );
  		}
    else submitform( pressbutton );
	}
  if (Joomla) Joomla.submitbutton = submitbutton;
</script>
<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">
<div class="col width-50" style="width:34%; float:left">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>
    <div class="tab-content form-horizontal">
      <div class="control-group">
        <div class="control-label"><label><?php echo JText::_( 'Name' ) ?>:</label></div>
        <div class="controls"><h4><?php echo $this->oauth->name;?></h4></div>
			</div>
      <div class="control-group">
					<div class="control-label"><label><?php echo JText::_( 'ID' ) ?>:</label></div>
					<div class="controls">
            <?php echo $this->oauth->id ?>
            <input type="hidden" name="cid" value="<?php echo $this->oauth->id?> "/>
          </div>
			</div>
      <div class="control-group">
        <div class="control-label"><label><?php echo JText::_( 'Enabled' ) ?>:</label></div>
        <div class="controls"><?php echo $this->lists['published']; ?><br /></div>
			</div>
      <div class="control-group">
        <div class="control-label"><label><?php echo JText::_( 'App/Client ID' ) ?>:</label></div>
        <div class="controls">
          <input type="text" name="app_id" id="app_id" maxlength="250" value="<?php echo $this->oauth->app_id;?>" size="40" />
        </div>
			</div>
      <div class="control-group">
        <div class="control-label"><label><?php echo JText::_( 'App/Client secret' ) ?>:</label></div>
        <div class="controls">
          <input type="text" name="app_secret" id="app_secret" maxlength="250" value="<?php echo $this->oauth->app_secret;?>" size="40" />
        </div>
			</div>

      <hr />

      <div class="control-group">
        <div class="control-label"><label><b>Get App/Client ID<br />and secret</b>:</label></div>
        <div class="controls">
          <a href="<?php echo $this->oauth->create_app; ?>" target="_blank">
            <?php echo $this->oauth->create_app; ?>
          </a>
          <br />Click above, log in and follow the tutorial
        </div>
			</div>
      <div class="control-group">
        <div class="control-label"><label><?php echo JText::_( 'App/Site Domain' ) ?>:</label></div>
        <div class="controls">
          <input type="text" size="40" style="cursor:text" readonly="readonly" onclick="this.select()" value="<?php echo JURI::root()?>" />
        </div>
			</div>
      <div class="control-group">
        <div class="control-label"><label><?php echo JText::_( 'Redirect URI' ) ?>:</label></div>
        <div class="controls">
          <textarea readonly="readonly" style="cursor:text" onclick="this.select()" style="width:240px"><?php
            echo JURI::root().'index.php?option=com_improved_ajax_login&task='.$this->oauth->alias;?>
          </textarea>
        </div>
			</div>
    </div>
	</fieldset>
</div>
<div id="tutor" class="col width-50" style="display:block; width:55%; float:right">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Tutorial' ); ?></legend>
    <table class="admintable" width="100%">
  		<tr>
  			<td align="center">
  				<h4>
            <a href="javascript:tutorialPrev()">&lt;&lt; Prev</a>
            &nbsp;&nbsp;&nbsp;Step <span id="step">0</span>&nbsp;&nbsp;&nbsp;
            <a href="javascript:tutorialNext()">Next &gt;&gt;</a>
          </h4>
  			</td>
  		</tr>
  		<tr>
  			<td align="center">
  				<img id="tutorial" style="width:100%; float:none"/>
  			</td>
  		</tr>
		</table>
	</fieldset>
</div>
<script type="text/javascript">
window.tutorialWidth = new Array();
window.tutorialPath = "<?php echo $tutorial_path = JURI::base().'components/com_improved_ajax_login/tutorials/'.$this->oauth->id; ?>";
window.tutorialMax = 0;
function tutorialNext() {
  var step = document.getElementById('step');
  var num = Number(step.innerHTML);
  var pic = document.getElementById('tutorial');
  if (num < tutorialMax) {
    pic.src = window.tutorialPath+'/'+(++num)+'.png';
    pic.style.maxWidth = window.tutorialWidth[num]+"px";
    step.innerHTML = num;
  } else {
    var img = new Image();
    img.onload = function(e) {
      if (window.tutorialMax == 0) document.getElementById('tutor').style.display="block";
      step.innerHTML = window.tutorialMax = num;
      pic.src = e.currentTarget.src;
      pic.style.maxWidth = (window.tutorialWidth[num]=e.currentTarget.width)+'px'
      delete img;
    };
    img.src = window.tutorialPath+'/'+(++num)+'.png';
  }
}
tutorialNext();
function tutorialPrev() {
  var step = document.getElementById('step');
  var num = Number(step.innerHTML);
  var pic = document.getElementById('tutorial');
  if (num > 1) {
    pic.src = window.tutorialPath+'/'+(--num)+'.png';
    pic.style.maxWidth = window.tutorialWidth[num]+"px";
    step.innerHTML = num;
  }
}
</script>
<div class="clr"></div>

	<input type="hidden" name="option" value="com_improved_ajax_login" />
	<input type="hidden" name="id[]" value="<?php echo $this->oauth->id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>