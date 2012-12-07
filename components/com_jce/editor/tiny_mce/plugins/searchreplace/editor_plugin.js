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
(function(){tinymce.create('tinymce.plugins.SearchReplacePlugin',{init:function(ed,url){function open(m){ed.windowManager.open({file:ed.getParam('site_url')+'index.php?option=com_jce&view=editor&layout=plugin&plugin=searchreplace',width:420+parseInt(ed.getLang('searchreplace.delta_width',0)),height:190+parseInt(ed.getLang('searchreplace.delta_height',0)),inline:1,auto_focus:0,popup_css:false},{mode:m,search_string:ed.selection.getContent({format:'text'}),plugin_url:url});};ed.addCommand('mceSearch',function(){open('search');});ed.addCommand('mceReplace',function(){open('replace');});ed.addButton('search',{title:'searchreplace.search_desc',cmd:'mceSearch'});ed.addButton('replace',{title:'searchreplace.replace_desc',cmd:'mceReplace'});ed.addShortcut('ctrl+f','searchreplace.search_desc','mceSearch');},getInfo:function(){return{longname:'Search/Replace',author:'Moxiecode Systems AB',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/searchreplace',version:tinymce.majorVersion+"."+tinymce.minorVersion};}});tinymce.PluginManager.add('searchreplace',tinymce.plugins.SearchReplacePlugin);})();