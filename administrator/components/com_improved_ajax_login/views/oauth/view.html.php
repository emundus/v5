<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login & Register
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class ImprovedAjaxLoginViewOAuth extends JoomlaView
{

	function display($tpl = null)
	{
		if($this->getLayout() == 'form') {
      if (version_compare(JVERSION,'3.0.0','ge')) $this->setLayout('form30');
			$this->_displayForm($tpl);
			return;
		}

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		$db		= JFactory::getDBO();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	=   $this->getModel();
		$oauth	= $this->get('data');

    $lists = array();
    $lists['published'] = '
    	<label style="float:left; min-width: 30px;" class="radiobtn" id="published1-lbl" for="published1">'.JText::_('Yes').'
        <input type="radio" '.($oauth->published==1? 'checked="checked"' : '').' value="1" id="published1" name="published" style="float:left; margin:3px" />
      </label>
    	<label style="float:left; min-width: 30px;" class="radiobtn" id="published0-lbl" for="published0">'.JText::_('No').'
        <input type="radio" '.($oauth->published==0? 'checked="checked"' : '').' value="0" id="published0" name="published" style="float:left; margin:3px" />
      </label>';

		$this->assignRef('lists',		$lists);
		$this->assignRef('oauth',		$oauth);

		parent::display($tpl);
	}

}