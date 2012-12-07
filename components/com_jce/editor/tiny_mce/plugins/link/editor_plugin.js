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
(function(){var each=tinymce.each,extend=tinymce.extend,JSON=tinymce.util.JSON;var Node=tinymce.html.Node;tinymce.create('tinymce.plugins.LinkPlugin',{init:function(ed,url){this.editor=ed;this.url=url;var self=this;function isLink(n){if(n&&n.nodeName!='A'){n=ed.dom.getParent(n,'A');}
return n&&n.nodeName=='A'&&!isAnchor(n);}
function isAnchor(n){return ed.dom.is(n,'a.mceItemAnchor')||ed.dom.is(n,'img.mceItemAnchor');}
ed.addCommand('mceLink',function(){var se=ed.selection,n=se.getNode();if(n.nodeName=='A'&&!n.name){se.select(n);}
ed.windowManager.open({file:ed.getParam('site_url')+'index.php?option=com_jce&view=editor&layout=plugin&plugin=link',width:500+ed.getLang('link.delta_width',0),height:515+ed.getLang('link.delta_height',0),inline:1,popup_css:false},{plugin_url:url});});ed.addButton('link',{title:'link.desc',cmd:'mceLink'});ed.addShortcut('ctrl+k','link.desc','mceLink');ed.onInit.add(function(){if(ed&&ed.plugins.contextmenu){ed.plugins.contextmenu.onContextMenu.add(function(th,m,e){m.addSeparator();m.add({title:'link.desc',icon:'link',cmd:'mceLink',ui:true});if((e.nodeName=='A'&&!ed.dom.getAttrib(e,'name'))){m.add({title:'advanced.unlink_desc',icon:'unlink',cmd:'UnLink'});}});}});ed.onNodeChange.add(function(ed,cm,n,co){cm.setActive('link',isLink(n));cm.setDisabled('link',isAnchor(n));});},getInfo:function(){return{longname:'Link',author:'Moxiecode Systems AB / Ryan Demmer',authorurl:'http://tinymce.moxiecode.com / http://www.joomlacontenteditor.net',infourl:'http://www.joomlacontenteditor.net',version:'2.2.1.2'};}});tinymce.PluginManager.add('link',tinymce.plugins.LinkPlugin);})();