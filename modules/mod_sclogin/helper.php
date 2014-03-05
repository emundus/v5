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

define('DISPLAY_BLOCK', ' class="show"');

class modSCLoginHelper
{
    var $isJFBConnectInstalled = false;

    var $providers = array();
    var $params;

    function __construct($params)
    {
        $this->params = $params;

        if (class_exists('JFBCFactory'))
        {
            $this->isJFBConnectInstalled = true;
            $this->providers = JFBCFactory::getAllProviders();
        }
    }

    function getPoweredByLink()
    {
        $showPoweredBy = $this->params->get('showPoweredByLink');
        if ($showPoweredBy == 0)
            return;

        if ($this->isJFBConnectInstalled)
        {
            $jfbcAffiliateID = JFBCFactory::config()->getSetting('affiliate_id');
            $showJFBCPoweredBy = (($showPoweredBy == '2' && JFBCFactory::config()->getSetting('show_powered_by_link')) || ($showPoweredBy == '1'));

            if ($showJFBCPoweredBy)
            {
                jimport('sourcecoast.utilities');
                $title = 'Facebook for Joomla';
                $poweredByLabel = 'JFBConnect';
                $link = SCLibraryUtilities::getAffiliateLink($jfbcAffiliateID);
            }
        }

        if (isset($link))
        {
            return '<div class="powered-by">' . JText::_('MOD_SCLOGIN_POWERED_BY') . ' <a target="_blank" href="' . $link . '" title="' . $title . '">' . $poweredByLabel . '</a></div>';
        }
        return "";
    }

    function getType()
    {
        $user = JFactory::getUser();
        return (!$user->get('guest')) ? 'logout' : 'login';
    }

    function getLoginRedirect($loginType)
    {
        if (JRequest::getString('return'))
            return JRequest::getString('return');

        $itemId = $this->params->get($loginType);
        $url = $this->getMenuIdUrl($itemId);

        // If no URL determined from the Itemid set, use the current page
        if (!$url)
        {
            $uri = JURI::getInstance();
            $url = $uri->toString(array('path', 'query'));
        }

        // Finally, if we're getting the logout URL, make sure we're not going back to a registered page
        if ($loginType == 'jlogout')
        {
            if ($itemId == "")
                $itemId = JFactory::getApplication()->input->getInt('Itemid', '');
            if ($itemId != "")
            {
                $db = JFactory::getDBO();
                $query = "SELECT * FROM #__menu WHERE id=" . $db->quote($itemId);
                $db->setQuery($query);
                $menuItem = $db->loadObject();
                if ($menuItem && $menuItem->access != "1")
                {
                    $default = JFactory::getApplication()->getMenu()->getDefault();
                    $url = JRoute::_($default->link . '&Itemid=' . $default->id, false);
                }
            }
        }

        return base64_encode($url);
    }

    private function getMenuIdUrl($itemId)
    {
        $url = "";
        $menu = JFactory::getApplication()->getMenu();
        if ($itemId)
        {
            $item = $menu->getItem($itemId);

            if ($item)
            {
                if ($item->type == 'url')
                    $url = $item->link;
                else
                {
                    if ($item->type == 'alias')
                        $itemId = $item->params->get('aliasoptions');

                    $router = JFactory::getApplication()->getRouter();
                    if ($item->link)
                    {
                        if ($router->getMode() == JROUTER_MODE_SEF)
                            $url = 'index.php?Itemid=' . $itemId;
                        else
                            $url = $item->link . '&Itemid=' . $itemId;
                        $url = JRoute::_($url, false);
                    }
                }
            }
        }
        return $url;
    }

    function getSocialAvatarImage($avatarURL, $profileURL, $profileURLTarget)
    {
        $html = '';
        if ($avatarURL)
        {
            $picHeightParam = $this->params->get("profileHeight");
            $picWidthParam = $this->params->get("profileWidth");
            $picHeight = $picHeightParam != "" ? 'height="' . $picHeightParam . 'px"' : "";
            $picWidth = $picWidthParam != "" ? 'width="' . $picWidthParam . 'px"' : "";

            $html = '<img src="' . $avatarURL . '" ' . $picWidth . " " . $picHeight . ' />';

            $isLinked = ($this->params->get("linkProfile") == 1);
            if ($isLinked && $profileURL != '')
                $html = '<a target="' . $profileURLTarget . '" href="' . $profileURL . '">' . $html . '</a>';
        }
        return $html;
    }

    function getProviderAvatar($provider, $user)
    {
        $html = "";
        $providerId = JFBCFactory::usermap()->getProviderUserId($user->get('id'), $provider->systemName);

        if ($providerId)
        {
            $params = new JRegistry();
            $params->set('width', $this->params->get("profileWidth"));
            $params->set('height', $this->params->get("profileHeight"));
            $params->set('secure', JURI::getInstance()->getScheme() == 'https');

            $avatarURL = $provider->profile->getAvatarUrl($providerId, false, $params);
            $profileURL = $provider->profile->getProfileUrl($providerId);
            $html = $this->getSocialAvatarImage($avatarURL, $profileURL, "_blank");
        }
        return $html;
    }

    function getJoomlaAvatar($registerType, $profileLink, $user)
    {
        $html = '';
        if ($registerType == 'jomsocial' && file_exists(JPATH_BASE . '/components/com_community/libraries/core.php'))
        {
            $jsUser = & CFactory::getUser($user->id);
            $avatarURL = $jsUser->getAvatar();
            $html = $this->getSocialAvatarImage($avatarURL, $profileLink, "_self");
        }
        else if ($registerType == 'easysocial' && file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
        {
            $avatarURL = Foundry::user($user->id)->getAvatar();
            $html = $this->getSocialAvatarImage($avatarURL, $profileLink, "_self");
        }
        else if ($registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
        {
            include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
            $cbUser = CBuser::getInstance( $user->id );
            $avatar = $cbUser->getField( 'avatar', null, 'csv', 'none', 'list' );
            $html = $this->getSocialAvatarImage($avatar, $profileLink, "_self");
        }
        else if ($registerType == 'kunena' && JFolder::exists(JPATH_SITE . '/components/com_kunena'))
        {
            $db = JFactory::getDbo();
            $query = "SELECT avatar FROM #__kunena_users WHERE userid = " . $user->id;
            $db->setQuery($query);
            $avatarURL = $db->loadResult();
            if ($avatarURL)
                $avatarURL = JRoute::_('media/kunena/avatars/' . $avatarURL, false);
            $html = $this->getSocialAvatarImage($avatarURL, $profileLink, "_self");
        }
        return $html;
    }

    function getSocialAvatar($registerType, $profileLink, $user)
    {
        $html = "";
        if ($this->params->get('enableProfilePic') == 'social')
        {
            foreach ($this->providers as $provider)
            {
                $html = $this->getProviderAvatar($provider, $user);
                if ($html != "")
                    break;
            }
        }
        else // 'joomla')
        {
            $html = $this->getJoomlaAvatar($registerType, $profileLink, $user);
        }

        if ($html != "")
            $html = '<div id="scprofile-pic">' . $html . '</div>';

        return $html;
    }

    function getLoginButtons($addClearfix, $loginButtonType, $orientation, $alignment, $loginButtonSize, $fbLoginButtonLinkImage, $liLoginButtonLinkImage, $goLoginButtonLinkImage, $twLoginButtonLinkImage)
    {
        $loginButtons = '';

        $params['buttonType'] = $loginButtonType;
        $params['alignment'] = $alignment;
        $params['orientation'] = $orientation;
        $params['facebookLinkImage'] = $fbLoginButtonLinkImage;
        $params['googleLinkImage'] = $goLoginButtonLinkImage;
        $params['linkedinLinkImage'] = $liLoginButtonLinkImage;
        $params['twitterLinkImage'] = $twLoginButtonLinkImage;
        $params['buttonSize'] = $loginButtonSize;

        foreach ($this->providers as $provider)
        {
            $loginButtons .= $provider->loginButton($params);
            if ($addClearfix && $loginButtons != '')
                $loginButtons .= '<div style="clear:both"></div>';
        }

        return $loginButtons;
    }

    function getReconnectButtons($addClearfix, $loginButtonType, $orientation, $alignment, $loginButtonSize, $fbLoginButtonLinkImage, $liLoginButtonLinkImage, $goLoginButtonLinkImage, $twLoginButtonLinkImage)
    {
        $buttonHtml = '';

        $params['buttonType'] = $loginButtonType;
        $params['alignment'] = $alignment;
        $params['orientation'] = $orientation;
        $params['facebookLinkImage'] = $fbLoginButtonLinkImage;
        $params['googleLinkImage'] = $goLoginButtonLinkImage;
        $params['linkedinLinkImage'] = $liLoginButtonLinkImage;
        $params['twitterLinkImage'] = $twLoginButtonLinkImage;
        $params['buttonSize'] = $loginButtonSize;
        $params['buttonText'] = JText::_('MOD_SCLOGIN_CONNECT_BUTTON');

        foreach ($this->providers as $provider)
        {
            $buttonHtml .= $provider->connectButton($params);
            if ($addClearfix && $buttonHtml != '')
                $buttonHtml .= '<div style="clear:both"></div>';
        }

        if ($buttonHtml)
            $buttonHtml = '<div class="sc-connect-user">' . JText::_('MOD_SCLOGIN_CONNECT_USER') . '</div>' . $buttonHtml;

        return $buttonHtml;
    }

    function getForgotUser($registerType, $showForgotUsername, $forgotLink, $forgotUsernameLink, $buttonImageColor)
    {
        $forgotUsername = '';

        if ($showForgotUsername && $registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
        {
            $forgotUsername = '<a href="' . $forgotLink . '" class="forgot btn width-auto hasTooltip" data-placement="right" data-original-title="' . JText::_('MOD_SCLOGIN_FORGOT_LOGIN') . '"><i class="icon-question-sign' . $buttonImageColor . '" title="' . JText::_('MOD_SCLOGIN_FORGOT_LOGIN') . '"></i></a>';
        }
        else if ($showForgotUsername)
        {
            $forgotUsername = '<a href="' . $forgotUsernameLink . '" class="forgot btn width-auto hasTooltip" data-placement="right" data-original-title="' . JText::_('MOD_SCLOGIN_FORGOT_USERNAME') . '"><i class="icon-question-sign' . $buttonImageColor . '" title="' . JText::_('MOD_SCLOGIN_FORGOT_USERNAME') . '"></i></a>';
        }

        return $forgotUsername;
    }

    function getForgotPassword($registerType, $showForgotPassword, $forgotLink, $forgotPasswordLink, $buttonImageColor)
    {
        $forgotPassword = '';

        if ($showForgotPassword && $registerType == "communitybuilder" && file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
        {
            $forgotPassword = '<a href="' . $forgotLink . '" class="forgot btn width-auto hasTooltip" data-placement="right" data-original-title="' . JText::_('MOD_SCLOGIN_FORGOT_LOGIN') . '"><i class="icon-question-sign' . $buttonImageColor . '" title="' . JText::_('MOD_SCLOGIN_FORGOT_LOGIN') . '"></i></a>';
        }
        else if ($showForgotPassword)
        {
            $forgotPassword = '<a href="' . $forgotPasswordLink . '" class="forgot btn width-auto hasTooltip" data-placement="right" data-original-title="' . JText::_('MOD_SCLOGIN_FORGOT_PASSWORD') . '"><i class="icon-question-sign' . $buttonImageColor . '" title="' . JText::_('MOD_SCLOGIN_FORGOT_PASSWORD') . '"></i></a>';
        }

        return $forgotPassword;
    }

    function getUserMenu($userMenu, $menuStyle)
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $menu_items = $menu->getItems('menutype', $userMenu);

        if (!empty($menu_items))
        {
            $db = JFactory::getDbo();
            $query = 'SELECT title FROM #__menu_types WHERE menutype=' . $db->quote($userMenu);
            $db->setQuery($query);
            $parentTitle = $db->loadResult();

            if ($menuStyle) //Show in List view
            {
                $menuNav = '<div class="scuser-menu list-view">';
                //$menuNav .= '<ul class="menu nav"><li class="dropdown"><span>'.$parentTitle.'</span>';
                $menuNav .= '<ul class="menu nav"><li><span>' . $parentTitle . '</span>';
                //$menuNav .= '<ul class="dropdown-menu">';
                $menuNav .= '<ul class="flat-list">';
                foreach ($menu_items as $menuItem)
                    $menuNav .= $this->getUserMenuItem($menuItem);
                $menuNav .= '</ul>';
                $menuNav .= '</li></ul>';
                $menuNav .= '</div>';
            }
            else //Show in Bootstrap dropdown list
            {
                if ($this->isJFBConnectInstalled)
                    $ddName = JFBCFactory::config()->getSetting('jquery_load') ? 'sc-dropdown' : 'dropdown';
                else
                    $ddName = $this->params->get('loadJQuery') ? 'sc-dropdown' : 'dropdown';

                $menuNav = '<div class="scuser-menu dropdown-view">';
                $menuNav .= '<div class="btn-group">';
                $menuNav .= '<a class="btn dropdown-toggle" data-toggle="' . $ddName . '" href="#">' . $parentTitle . '<span class="caret"></span></a>';
                $menuNav .= '<ul class="dropdown-menu">';
                foreach ($menu_items as $menuItem)
                    $menuNav .= $this->getUserMenuItem($menuItem);
                $menuNav .= '</ul>';
                $menuNav .= '</div>';
                $menuNav .= '</div>';
            }

        }
        else
            $menuNav = '';
        return $menuNav;
    }

    private function getUserMenuItem($item)
    {
        $url = $this->getMenuIdUrl($item->id);

        if ($item->type == 'url')
        {
            if ($item->link == 'sclogout')
                $url = JRoute::_('index.php?option=com_users&task=user.logout&return=' . $this->getLoginRedirect('jlogout') . '&' . JSession::getFormToken() . '=1');
            if ($item->link == 'scconnect')
            {
                $providers = JFBCFactory::getAllProviders();
                $sepAdded = false;
                $html = '';

                $params['buttonType'] = 'icon_button';
                $params['alignment'] = 'left';
                $params['orientation'] = 'side';
                $params['buttonText'] = JText::_('MOD_SCLOGIN_CONNECT_BUTTON');

                foreach ($providers as $p)
                {
                    if (!JFBCFactory::usermap()->getProviderUserId(JFactory::getUser()->id, $p->name))
                    {
                        if (!$sepAdded)
                        {
                            $html .= '<li class="connect">' . $item->title . '<br/>';
                            $sepAdded = true;
                        }
                        $html .= $p->connectButton($params);

                    }
                }

                if ($sepAdded)
                    $html .= "</li>";
                return $html;
            }
        }
        $target = $item->browserNav == 1 ? ' target="_blank" ' : '';
        return '<li><a href="' . $url . '"' . $target . '>' . $item->title . '</a></li>';
    }
}