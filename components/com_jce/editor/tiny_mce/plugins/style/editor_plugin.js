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
(function(){tinymce.create('tinymce.plugins.StylePlugin',{init:function(ed,url){ed.addCommand('mceStyleProps',function(){var applyStyleToBlocks=false;var blocks=ed.selection.getSelectedBlocks();var styles=[];if(blocks.length===1){styles.push(ed.selection.getNode().style.cssText);}
else{tinymce.each(blocks,function(block){styles.push(ed.dom.getAttrib(block,'style'));});applyStyleToBlocks=true;}
ed.windowManager.open({file:ed.getParam('site_url')+'index.php?option=com_jce&view=editor&layout=plugin&plugin=style',width:620+parseInt(ed.getLang('style.delta_width',0)),height:360+parseInt(ed.getLang('style.delta_height',0)),inline:1,popup_css:false},{applyStyleToBlocks:applyStyleToBlocks,plugin_url:url,styles:styles});});ed.addCommand('mceSetElementStyle',function(ui,v){if(e=ed.selection.getNode()){ed.dom.setAttrib(e,'style',v);ed.execCommand('mceRepaint');}});ed.onNodeChange.add(function(ed,cm,n){cm.setDisabled('style',(n.nodeName==='BODY'||(n.nodeName==='BR'&&n.getAttribute('data-mce-bogus'))));});ed.addButton('style',{title:'style.desc',cmd:'mceStyleProps'});},getInfo:function(){return{longname:'Style',author:'Moxiecode Systems AB',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/style',version:tinymce.majorVersion+"."+tinymce.minorVersion};}});tinymce.PluginManager.add('style',tinymce.plugins.StylePlugin);})();