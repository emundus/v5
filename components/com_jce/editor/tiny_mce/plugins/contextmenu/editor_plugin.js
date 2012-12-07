/*  
 * JCE Editor                 2.2.1.2
 * @package                 JCE
 * @url                     http://www.joomlacontenteditor.net
 * @copyright               Copyright (C) 2006 - 2012 Ryan Demmer. All rights reserved
 * @license                 GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 * @date                    29 June 2012
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * NOTE : Javascript files have been compressed for speed and can be uncompressed using http://jsbeautifier.org/
 */
(function(){var Event=tinymce.dom.Event,each=tinymce.each,DOM=tinymce.DOM;tinymce.create('tinymce.plugins.ContextMenu',{init:function(ed){var t=this,showMenu,contextmenuNeverUseNative,realCtrlKey,hideMenu;t.editor=ed;contextmenuNeverUseNative=ed.settings.contextmenu_never_use_native;t.onContextMenu=new tinymce.util.Dispatcher(this);hideMenu=function(e){hide(ed,e);};showMenu=ed.onContextMenu.add(function(ed,e){if((realCtrlKey!==0?realCtrlKey:e.ctrlKey)&&!contextmenuNeverUseNative)
return;Event.cancel(e);if(e.target.nodeName=='IMG')
ed.selection.select(e.target);t._getMenu(ed).showMenu(e.clientX||e.pageX,e.clientY||e.pageY);Event.add(ed.getDoc(),'click',hideMenu);ed.nodeChanged();});ed.onRemove.add(function(){if(t._menu)
t._menu.removeAll();});function hide(ed,e){realCtrlKey=0;if(e&&e.button==2){realCtrlKey=e.ctrlKey;return;}
if(t._menu){t._menu.removeAll();t._menu.destroy();Event.remove(ed.getDoc(),'click',hideMenu);t._menu=null;}};ed.onMouseDown.add(hide);ed.onKeyDown.add(hide);ed.onKeyDown.add(function(ed,e){if(e.shiftKey&&!e.ctrlKey&&!e.altKey&&e.keyCode===121){Event.cancel(e);showMenu(ed,e);}});},getInfo:function(){return{longname:'Contextmenu',author:'Moxiecode Systems AB',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/contextmenu',version:tinymce.majorVersion+"."+tinymce.minorVersion};},_getMenu:function(ed){var t=this,m=t._menu,se=ed.selection,col=se.isCollapsed(),el=se.getNode()||ed.getBody(),am,p;if(m){m.removeAll();m.destroy();}
p=DOM.getPos(ed.getContentAreaContainer());m=ed.controlManager.createDropMenu('contextmenu',{offset_x:p.x+ed.getParam('contextmenu_offset_x',0),offset_y:p.y+ed.getParam('contextmenu_offset_y',0),constrain:1,keyboard_focus:true});t._menu=m;m.addSeparator();am=m.addMenu({title:'contextmenu.align'});am.add({title:'contextmenu.left',icon:'justifyleft',cmd:'JustifyLeft'});am.add({title:'contextmenu.center',icon:'justifycenter',cmd:'JustifyCenter'});am.add({title:'contextmenu.right',icon:'justifyright',cmd:'JustifyRight'});am.add({title:'contextmenu.full',icon:'justifyfull',cmd:'JustifyFull'});t.onContextMenu.dispatch(t,m,el,col);return m;}});tinymce.PluginManager.add('contextmenu',tinymce.plugins.ContextMenu);})();