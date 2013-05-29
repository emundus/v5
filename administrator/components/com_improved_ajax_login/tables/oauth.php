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
// no direct access
defined('_JEXEC') or die('Restricted access');

class TableOAuth extends JTable
{

	var $id = null;
	var $name = null;
	var $published = null;
	var $app_id = null;
	var $app_secret = null;
	var $create_app = null;

	function __construct(& $db) {
		parent::__construct('#__offlajn_oauths', 'id', $db);
	}

	function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}

	function check()
	{
		return true;
	}
}

class oauthTableOAuth extends TableOAuth {}