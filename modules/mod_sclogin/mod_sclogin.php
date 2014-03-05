<?php

/**
 * @package        JFBConnect
 * @copyright (C) 2011-2013 by Source Coast - All rights reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/sc_helper.php');
$helper = new modSCLoginHelper($params);

$user = JFactory::getUser();

$jLoginUrl = $helper->getLoginRedirect('jlogin');
$jLogoutUrl = $helper->getLoginRedirect('jlogout');

$registerType = $params->get('register_type');
$forgotLink = '';
if ($registerType == "jomsocial" && file_exists(JPATH_BASE . '/components/com_community/libraries/core.php'))
{
    $jspath = JPATH_BASE . '/components/com_community';
    include_once($jspath . '/libraries/core.php');
    $registerLink = CRoute::_('index.php?option=com_community&view=register');
    $profileLink = CRoute::_('index.php?option=com_community');
}
else if ($registerType == 'easysocial' && file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
{
    $registerLink = JRoute::_('index.php?option=com_easysocial&view=registration');
    $forgotUsernameLink = JRoute::_('index.php?option=com_easysocial&view=profile&layout=forgetusername');
    $forgotPasswordLink = JRoute::_('index.php?option=com_easysocial&view=profile&layout=forgetpassword');
    $profileLink = JRoute::_('index.php?option=com_easysocial&view=profile');
}
else if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
{
    $registerLink = JRoute::_("index.php?option=com_comprofiler&task=registers", false);
    $forgotLink = JRoute::_("index.php?option=com_comprofiler&task=lostPassword");
    $profileLink = JRoute::_("index.php?option=com_comprofiler", false);
}
else if ($registerType == "virtuemart" && file_exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php'))
{
    require_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/version.php');
    if (class_exists('vmVersion') && property_exists('vmVersion', 'RELEASE'))
    {
        if (version_compare('1.99', vmVersion::$RELEASE)) // -1 if ver1, 1 if 2.0+
        $registerLink = JRoute::_("index.php?option=com_virtuemart&view=user", false);
        else
        {
            if (file_exists(JPATH_SITE . '/components/com_virtuemart/virtuemart_parser.php'))
            {
                require_once(JPATH_SITE . '/components/com_virtuemart/virtuemart_parser.php');
                global $sess;
                $registerLink = $sess->url(SECUREURL . 'index.php?option=com_virtuemart&amp;page=shop.registration');
            }
        }
    }
    $profileLink = '';
}
else if ($registerType == 'kunena' && JFolder::exists(JPATH_SITE . '/components/com_kunena'))
{
    $profileLink = JRoute::_('index.php?option=com_kunena&view=user', false);
    $registerLink = JRoute::_('index.php?option=com_users&view=registration', false);
}
else
{
    $profileLink = '';
    $registerLink = JRoute::_('index.php?option=com_users&view=registration', false);
}
// common for J!, JomSocial, and Virtuemart

if (!isset($forgotUsernameLink))
    $forgotUsernameLink = JRoute::_('index.php?option=com_users&view=remind', false);
if (!isset($forgotPasswordLink))
    $forgotPasswordLink = JRoute::_('index.php?option=com_users&view=reset', false);

$showRegisterLink = $params->get('showRegisterLink');
$showRegisterLinkInModal = $showRegisterLink == 2 || $showRegisterLink == 3;
$showRegisterLinkInLogin = $showRegisterLink == 1 || $showRegisterLink == 3;

// Load our CSS and Javascript files
$document = JFactory::getDocument();

$paths = array();
$paths[] = JPATH_ROOT . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/mod_sclogin/themes/';
$paths[] = JPATH_ROOT . '/media/sourcecoast/themes/sclogin/';
$theme = $params->get('theme', 'default.css');
$file = JPath::find($paths, $theme);
$file = str_replace(JPATH_SITE, '', $file);
$document->addStyleSheet(JURI::base(true) . $file);

// Add placeholder Javascript for old browsers that don't support the placeholder field
if ($user->guest)
{
    jimport('joomla.environment.browser');
    $browser = JBrowser::getInstance();
    $browserType = $browser->getBrowser();
    $browserVersion = $browser->getMajor();
    if (($browserType == 'msie') && ($browserVersion <= 9))
    {
        // Using addCustomTag to ensure this is the last section added to the head, which ensures that jfbcJQuery has been defined
        $document->addCustomTag('<script src="' . JURI::base(true) . '/media/sourcecoast/js/jquery.placeholder.js" type="text/javascript"> </script>');
        $document->addCustomTag("<script>jfbcJQuery(document).ready(function() { jfbcJQuery('input').placeholder(); });</script>");
    }
}

// Two factor authentication check
$jVersion = new JVersion();
$tfaLoaded = false;
if (version_compare($jVersion->getShortVersion(), '3.2.0', '>=') && ($user->guest))
{
    $db = JFactory::getDbo();
    // Check if TFA is enabled. If not, just return false
    $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__extensions')
            ->where('enabled=' . $db->q(1))
            ->where('folder=' . $db->q('twofactorauth'));
    $db->setQuery($query);
    $tfaCount = $db->loadResult();

    if ($tfaCount > 0)
    {
        $tfaLoaded = true;
    }
}

$needsBootstrap = $params->get('displayType') == 'modal' || ($params->get('showUserMenu') && $params->get('userMenuStyle') == 0);
if (!$helper->isJFBConnectInstalled && $params->get('loadJQuery') && ($needsBootstrap || $tfaLoaded))
{
    $document->addScript(JURI::base(true) . '/media/sourcecoast/js/jq-bootstrap-1.8.3.js');
    $document->addScriptDeclaration('if (typeof jfbcJQuery == "undefined") jfbcJQuery = jQuery;');
}

if ($tfaLoaded)
{
    $document->addScript(Juri::base(true) . '/media/sourcecoast/js/mod_sclogin.js');
    $document->addScriptDeclaration('sclogin.token = "' . JSession::getFormToken() . '";' .
        //"jfbcJQuery(window).on('load',  function() {
        // Can't use jQuery here because we don't know if jfbcJQuery has been loaded or not.
        "window.onload = function() {
            sclogin.init();
        };
        sclogin.base = '" . JURI::base() . "';\n"
    );
}

//if ($params->get('loadBootstrap'))
$document->addStyleSheet(JURI::base(true) . '/media/sourcecoast/css/sc_bootstrap.css');

// Setup our parameters
$layout = $params->get('socialButtonsLayout', 'vertical'); //horizontal or vertical
$orientation = $params->get('socialButtonsOrientation'); //bottom or side
$alignment = $params->get('socialButtonsAlignment');
$loginButtonType = $params->get('loginButtonType');

if ($layout == 'horizontal')
{
    $joomlaSpan = 'pull-left';
    $socialSpan = 'pull-' . $alignment;
}
else if ($orientation == 'side' && $loginButtonType == "icon_button")
{
    $joomlaSpan = 'span10';
    $socialSpan = 'span2';

}
else if ($orientation == 'side' && $loginButtonType == "icon_text_button")
{
    $joomlaSpan = 'span8';
    $socialSpan = 'span4';

}
else //orientation == 'bottom'
{
    $joomlaSpan = 'span12';
    $socialSpan = 'span12';
}

$addClearfix = ($layout == 'vertical' && $orientation == "side") ||
        ($layout == "horizontal" && $orientation == "side" && $params->get('displayType') == 'modal');
$loginButtons = $helper->getLoginButtons($addClearfix, $loginButtonType, $orientation, $alignment, $params->get("loginButtonSize"), $params->get('facebookLoginButtonLinkImage'), $params->get('linkedInLoginButtonLinkImage'), $params->get('googleLoginButtonLinkImage'), $params->get('twitterLoginButtonLinkImage'));

require(JModuleHelper::getLayoutPath('mod_sclogin', $helper->getType()));
?>
