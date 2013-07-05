<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldCampaign extends JFormField
{
    protected $type = 'campaign';

    protected function getInput() {
        $course = JRequest::getVar('course', '', '', 'str');
        $course = !empty($course)?$course:"%";

        $db =& JFactory::getDBO();
        $query = "SELECT id, CONCAT(label,' (',year,')') AS label 
                    FROM #__emundus_setup_campaigns 
                    WHERE published=1 
                    AND NOW() >= start_date 
                    AND end_date >= NOW()
                    AND training like ".$db->Quote($course)." 
                    ORDER BY label";
        $db->setQuery($query);
        $campaigns = $db->loadAssocList();

        $list = '<select id="jform_emundus_profile_'.$this->element['name'].'" class="required" name="jform[emundus_profile]['.$this->element['name'].']">';
        $list .= '<option value="">'.JText::_('PLEASE_SELECT').'</option>';
        foreach ($campaigns as $campaign) {
           $list .= '<option value="'.$campaign['id'].'">'.$campaign['label'].'</option>';
        }
        $list .= '</select>';
/*
        $please_select = array(null => JText::_('PLEASE_SELECT'));
        $campaigns = $please_select + $campaigns;
        $list = JHTML::_('select.genericlist', $campaigns, $this->element['name'], 'onChange="changeFunc()"', 'id', 'label');
*/
        $script = "<script>function changeFunc(){alert('good')}</script>";
       
        $div = '<div id="em_campaign_info"><div>';

        return $script.$list.$div;
    }
}
?>