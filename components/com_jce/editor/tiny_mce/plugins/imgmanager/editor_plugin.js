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
(function(){tinymce.create('tinymce.plugins.ImageManager',{init:function(ed,url){function isMceItem(n){return/mceItem/.test(n.className);};ed.addCommand('mceImageManager',function(){var n=ed.selection.getNode();if(n.nodeName=='IMG'&&isMceItem(n)){return;}
ed.windowManager.open({file:ed.getParam('site_url')+'index.php?option=com_jce&view=editor&layout=plugin&plugin=imgmanager',width:780+ed.getLang('imgmanager.delta_width',0),height:640+ed.getLang('imgmanager.delta_height',0),inline:1,popup_css:false},{plugin_url:url});});ed.addButton('imgmanager',{title:'imgmanager.desc',cmd:'mceImageManager'});ed.onNodeChange.add(function(ed,cm,n){cm.setActive('imgmanager',n.nodeName=='IMG'&&!isMceItem(n));});ed.onInit.add(function(){if(ed&&ed.plugins.contextmenu){ed.plugins.contextmenu.onContextMenu.add(function(th,m,e){m.add({title:'imgmanager.desc',icon:'imgmanager',cmd:'mceImageManager'});});}});},getInfo:function(){return{longname:'Image Manager',author:'Ryan Demmer',authorurl:'http://www.joomlacontenteditor.net',infourl:'http://www.joomlacontenteditor.net/index2.php?option=com_content&amp;task=findkey&amp;pop=1&amp;lang=en&amp;keyref=imgmanager.about',version:'2.2.1.2'};}});tinymce.PluginManager.add('imgmanager',tinymce.plugins.ImageManager);})();