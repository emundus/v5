<?php
/**
 * @package   	eMundus
 * @copyright 	Copyright © 2009-2012 Benjamin Rivalland. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * eMundus is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

jimport( 'joomla.application.component.view' );
jimport( 'joomla.application.component.helper' );

class EmundusViewConfig extends JView
{
    function display($tpl = null)
    {
    	$db =JFactory::getDBO();
        $model =$this->getModel();
        
/// Voir http://docs.joomla.org/Component_parameters
        
        $component 	= JComponentHelper::getComponent('com_emundus');        
        $config 	= JPATH_COMPONENT.DS.'config.xml';
       // die(JPATH_COMPONENT.DS.'config.xml');
        // get params definitions
		$params = new JParameter($component->params, $config);    
        $params->addElementPath(JPATH_COMPONENT.DS.'elements');
 
		//$this->assignRef('params', $params);
		
 //print_r($params);	       
        
		$this->assignRef('model',	$model);
        $this->assignRef('params',	$params);
        //JToolbarHelper::help('config.about');
        
        parent::display($tpl);
    }
}
?>