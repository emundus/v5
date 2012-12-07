<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once(JApplicationHelper::getPath('toolbar_html'));

switch($task) {
    case 'edit':
        $cid = JRequest::getVar('cid',array(0),'','array' );
        if (!is_array( $cid ))
            $cid = array(0);
        TOOLBAR_Jumi::_EDIT($cid[0]);
        break;

    case 'add'  :
    case 'editA':
        $id = JRequest::getVar('id',0,'','int');
        TOOLBAR_Jumi::_EDIT( $id );
        break;

    default:
        TOOLBAR_Jumi::_DEFAULT();
        break;
}