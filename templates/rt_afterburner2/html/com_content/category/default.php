<?php
/**
 * @version   $Id: default.php 7496 2013-02-13 15:55:28Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2013 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('_JEXEC') or die;
include(JPATH_LIBRARIES.'/gantry/gantry.php');
$gantry->init();
include JPATH_SITE.'/templates/'.$gantry->getCurrentTemplate().'/html/base_override.php';