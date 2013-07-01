<?php
/*
 *  Administrator Tools
 *  Copyright (C) 2010-2013  Nicholas K. Dionysopoulos / AkeebaBackup.com
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

// Make sure Admin Tools is installed, otherwise bail out
if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_admintools')) {
	return;
}

// PHP version check
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	$version = '5.0.0'; // all bets are off!
}
if(!version_compare($version, '5.3.0', '>=')) return;

// Joomla! version check
if(version_compare(JVERSION, '2.5', 'lt') && version_compare(JVERSION, '1.6.0', 'ge')) {
	// Joomla! 1.6.x and 1.7.x: sorry fellas, no go.
	return;
}

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if(function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
	if(function_exists('error_reporting')) {
		$oldLevel = error_reporting(0);
	}
	$serverTimezone = @date_default_timezone_get();
	if(empty($serverTimezone) || !is_string($serverTimezone)) $serverTimezone = 'UTC';
	if(function_exists('error_reporting')) {
		error_reporting($oldLevel);
	}
	@date_default_timezone_set( $serverTimezone);
}

// Include FOF's loader if required
if(!defined('FOF_INCLUDED')) {
	$libraries_dir = defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT.'/libraries';
	$mainFile = $libraries_dir.'/fof/include.php';
	$altFile = JPATH_ADMINISTRATOR.'/components/com_admintools/fof/include.php';
	if(file_exists($mainFile)) {
		@include_once $mainFile;
	} elseif(file_exists($altFile)) {
		@include_once $altFile;
	}
}

// If FOF is not present (e.g. not installed) bail out
if(!defined('FOF_INCLUDED') || !class_exists('FOFLess', true)) {
	return;
}

JLoader::import('joomla.filesystem.file');
$target_include = JPATH_ROOT.'/plugins/system/admintools/admintools/main.php';

if(JFile::exists($target_include)) {
	require_once $target_include;
} else {
	$target_include = $target_include = JPATH_ROOT.'plugins/system/admintools/admintools/main.php';
	if(JFile::exists($target_include)) {
		require_once $target_include;
	}
}