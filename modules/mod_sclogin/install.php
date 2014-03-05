<?php
/**
 * @package        JFBConnect
 * @copyright (C) 2009-2013 by Source Coast - All rights reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class mod_scloginInstallerScript
{
    var $extReqs = array(
        array('name' => 'JFBConnect', 'version' => '5.1.0', 'element' => 'com_jfbconnect')
    );

    public function preflight($type, $parent)
    {
        foreach ($this->extReqs as $req)
        {
            $currentVersion = $this->getInstalledVersion($req['element']);
            if ($currentVersion && version_compare($currentVersion, $req['version'], '<'))
            {
                $installStr = 'SCLogin requires JFBConnect v5.1.0 or higher for Facebook, Google+, Twitter and LinkedIn functionality. Please upgrade JFBConnect to enable the Facebook, Google+, Twitter and LinkedIn social login features.';
                JFactory::getApplication()->enqueueMessage($installStr, 'error');
            }
        }
    }

    private function getInstalledVersion($element)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('manifest_cache')->from('#__extensions')->where($db->qn('element') . '=' . $db->q($element));
        $db->setQuery($query);
        $manifest = $db->loadResult();
        if ($manifest)
        {
            $manifest = json_decode($manifest);
            return $manifest->version;
        }
        else
            return "";
    }
}