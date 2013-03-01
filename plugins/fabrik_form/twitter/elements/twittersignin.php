<?php
/**
 * Post content to twitter: JForm Element
 *
 * NOTE - as we can only have one addpath file specified for the params group, this file has to be located
 * in the main ./administrator/components/com_fabrik/models/fields folder.  So until we work out how to do the install
 * XML magic to relocate this file on install, we have simply made a copy of it in the admin location in SVN.
 * If you edit the copy in the plugin folder, please be sure to also modify the copy in the admin folder.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.twiter
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php');

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a twitter sign in button
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.twiter
 * @since		3.0
 */

class JFormFieldTwittersignin extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Twittersignin';

	/**
	 * Array Counter - not used?
	 *
	 * @var int
	 */
	var $_array_counter = null;

	/**
	 * Get the input
	 *
	 * @return string
	 */
	function getInput()
	{
		$iframeid = $this->id . '_iframe';
		$cid = JRequest::getVar('id', array(0), 'array');
		// $$$ hugh - when creating a new form, no 'cid' ... not sure what to do, so just set it to 0.  Should
		// prolly just return something like 'available after save' ?

		$c = isset($this->form->repeatCounter) ? (int) $this->form->repeatCounter : 0;

		$href = COM_FABRIK_LIVESITE . 'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=twitter&g=form&method=authenticateAdmin&tmpl=component&formid=' . $cid . '&repeatCounter=' . $c;

		$clearjs = '$(\'jform_params_twitter_oauth_token-'.$c.'\').value = \'\';';
		$clearjs .= '$(\'jform_params_twitter_oauth_token_secret-'.$c.'\').value = \'\';';
		$clearjs .= '$(\'jform_params_twitter_oauth_user-'.$c.'\').value = \'\';';
		$clearjs .= "return false;";

		$js = "window.open('$href', 'twitterwins', 'width=800,height=460,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;";
		$str =  '<a href="#" onclick="'.$js.'"><img src="'.COM_FABRIK_LIVESITE.'components/com_fabrik/libs/abraham-twitteroauth/images/lighter.png" alt="Sign in with Twitter"/></a>';
		$str .= " | <button class=\"button\" href=\"#\" onclick=\"$clearjs\">" .    JText::_('PLG_FORM_TWITTER_CLEAR_CREDENTIALS') . "</button><br/>";
		$str .= "<br /><input type=\"hidden\" readonly=\"readonly\" name=\"". $this->name . "\" id=\"" .$this->id . "\" value=\"" . $this->value . "\" />";
		return $str;
	}
}
