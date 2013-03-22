<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

$this->loadHelper('select');

$lang = JFactory::getLanguage();
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal form-horizontal-wide"> 
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="seoandlink" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />

	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPTGROUP_MIGRATION') ?></legend>
		
		<div class="control-group">
			<label for="linkmigration" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_LINKMIGRATION'); ?></label>
			<div class="controls">
				<?php echo AdmintoolsHelperSelect::booleanlist('linkmigration', array(), $this->salconfig['linkmigration']) ?>
			</div>
		</div>
		<div class="control-group">
			<label for="migratelist" class="control-label" title="<?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_LINKMIGRATIONLIST_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_LINKMIGRATIONLIST'); ?></label>
			<div class="controls">
				<textarea rows="5" cols="55" name="migratelist" id="migratelist"><?php echo $this->salconfig['migratelist'] ?></textarea>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPTGROUP_COMBINE') ?></legend>
		
		<input type="hidden" name="combinecache" value="" />
		
		<div class="control-group">
			<label for="jscombine" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_JSCOMBINE'); ?></label>
			<div class="controls">
				<?php echo AdmintoolsHelperSelect::booleanlist('jscombine', array(), $this->salconfig['jscombine']) ?>
			</div>
		</div>
		<div class="control-group">
			<label for="jsdelivery" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_JSDELIVERY'); ?></label>
			<div class="controls">
				<?php echo AdmintoolsHelperSelect::deliverymethod('jsdelivery', array(), $this->salconfig['jsdelivery']) ?>
			</div>
		</div>
		<div class="control-group">
			<label for="jsskip" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_JSSKIP'); ?></label>
			<div class="controls">
				<textarea rows="5" cols="55" name="jsskip" id="jsskip"><?php echo $this->salconfig['jsskip'] ?></textarea>
			</div>
		</div>
		
		<div style="clear:both"></div>
		<hr/>
		
		<div class="control-group">
			<label for="csscombine" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_CSSCOMBINE'); ?></label>
			<div class="controls">
				<?php echo AdmintoolsHelperSelect::booleanlist('csscombine', array(), $this->salconfig['csscombine']) ?>
			</div>
		</div>
		<div class="control-group">
			<label for="cssdelivery" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_CSSDELIVERY'); ?></label>
			<div class="controls">
				<?php echo AdmintoolsHelperSelect::deliverymethod('cssdelivery', array(), $this->salconfig['cssdelivery']) ?>
			</div>
		</div>
		<div class="control-group">
			<label for="cssskip" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_CSSSKIP'); ?></label>
			<div class="controls">
				<textarea rows="5" cols="55" name="cssskip" id="jsskip"><?php echo $this->salconfig['cssskip'] ?></textarea>
			</div>
		</div>
	</fieldset>
	
	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPTGROUP_TOOLS') ?></legend>
		
		<div class="control-group">
			<label for="httpsizer" class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_HTTPSIZER'); ?></label>
			<div class="controls">
				<?php echo AdmintoolsHelperSelect::booleanlist('httpsizer', array(), $this->salconfig['httpsizer']) ?>
			</div>
		</div>
	</fieldset>
</form>