<?php

/**
 * @package		SP Upgrade
 * @subpackage	Components
 * @copyright	SP CYEND - All rights reserved.
 * @author		SP CYEND
 * @link		http://www.cyend.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html 
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of SPUpgrade component
 */
class com_spupgradeInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method

            /*
                //Decide if existing table jos_spupgrade
                $jAp=& JFactory::getApplication();
                $db =& JFactory::getDBO();
                $query = "
                  SELECT COUNT(*)
                    FROM ".$db->nameQuote('#__spupgrade')."
                  ";
                $db->setQuery($query);
                $count = $db->loadResult();
                if ($count == 0) { //proceed if new table
                    //Copy all users
                    $query = "select `id` from `#__users`";
                    $db->setQuery($query);
                    $result = $db->query();
                    if (is_null($column= $db->loadObjectList())) {$jAp->enqueueMessage(nl2br($db->getErrorMsg()),'error'); return;}

                    foreach($column as $i => $item) {
                        $query = "INSERT INTO `#__spupgrade` (`id`, `sendEmail`) VALUES ('.$item->id.', '1');";
                        $db->setQuery($query);
                        if (!$db->query()) {$jAp->enqueueMessage(nl2br($db->getErrorMsg()),'error'); return;}
                    }
                }
             * 
             */
		$parent->getParent()->setRedirectURL('index.php?option=com_spupgrade');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SPUPGRADE_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method

            /*
		//Decide if existing table jos_spupgrade
                $jAp=& JFactory::getApplication();
                $db =& JFactory::getDBO();
                $query = "
                  SELECT COUNT(*)
                    FROM ".$db->nameQuote('#__spupgrade')."
                  ";
                $db->setQuery($query);
                $count = $db->loadResult();
                if ($count == 0) { //proceed if new table
                    //Copy all users
                    $query = "select `id` from `#__users`";
                    $db->setQuery($query);
                    $result = $db->query();
                    if (is_null($column= $db->loadObjectList())) {$jAp->enqueueMessage(nl2br($db->getErrorMsg()),'error'); return;}

                    foreach($column as $i => $item) {
                        $query = "INSERT INTO `#__spupgrade` (`id`, `sendEmail`) VALUES ('.$item->id.', '1');";
                        $db->setQuery($query);
                        if (!$db->query()) {$jAp->enqueueMessage(nl2br($db->getErrorMsg()),'error'); return;}
                    }
                }
             * 
             */
                echo '<p>' . JText::_('COM_SPUPGRADE_UPDATE_TEXT') . '</p>';

	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		//echo '<p>' . JText::_('COM_SPUPGRADE_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
                //Update client_id in extensions table to enable online upgrade

                $jAp=& JFactory::getApplication();
                $db =& JFactory::getDBO();
                $query = "UPDATE `#__extensions` SET `client_id` = '0'  WHERE `name` ='com_spupgrade';";
                $db->setQuery($query);
                if (!$db->query()) {$jAp->enqueueMessage(nl2br($db->getErrorMsg()),'error'); return;}
                                
		//echo '<p>' . JText::_('COM_SPUPGRADE_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
       
	}
}
