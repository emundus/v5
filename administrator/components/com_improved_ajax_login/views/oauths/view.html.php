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

class ImprovedAjaxLoginViewOAuths extends JoomlaView
{

	function display($tpl = null)
	{
    if (version_compare(JVERSION,'3.0.0','ge')) $this->setLayout('default30');
		// Get data from the model
		$items		= $this->get('Data');
		$pagination = $this->get('Pagination');

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}

}