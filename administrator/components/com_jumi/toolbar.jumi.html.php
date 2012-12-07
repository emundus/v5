<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class TOOLBAR_Jumi {

    function _EDIT($applid) {
        $cid = JRequest::getVar('cid',array(0));

        $text = ( $cid[0] ? JText::_( 'Edit' ) : JText::_( 'New' ) );

        JToolBarHelper::title(  JText::_( 'Jumi Application' ).': <small><small>[ ' . $text.' ]</small></small>' );
        //JToolBarHelper::Preview('index.php?option=com_application&tmpl=component&applid='.$applid);
        JToolBarHelper::save();
        if($text !== JText::_('New')) JToolBarHelper::apply();
        if ($cid[0]) JToolBarHelper::cancel('cancel','Close');
        else         JToolBarHelper::cancel();
        JToolBarHelper::help('screen.applications.edit');
    }

    function _DEFAULT() {
        JToolBarHelper::title(  JText::_( 'Jumi Applications Manager' ) );
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList();
        JToolBarHelper::editListX();
        JToolBarHelper::addNewX();
        JToolBarHelper::help( 'screen.applications' );
    }
}