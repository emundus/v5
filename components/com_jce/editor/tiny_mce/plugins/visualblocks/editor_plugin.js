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
(function(){var cookie=tinymce.util.Cookie;tinymce.create('tinymce.plugins.VisualBlocks',{init:function(ed,url){var cssId;if(!window.NodeList){return;}
var state=cookie.get('wf_visualblocks_state');if(state&&tinymce.is(state,'string')){if(state=='null'){state=0;}
state=parseFloat(state);}
state=ed.getParam('visualblocks_default_state',state);ed.addCommand('mceVisualBlocks',function(){var dom=ed.dom,linkElm;if(!cssId){cssId=dom.uniqueId();linkElm=dom.create('link',{id:cssId,rel:'stylesheet',href:url+'/css/visualblocks.css'});ed.getDoc().getElementsByTagName('head')[0].appendChild(linkElm);}else{linkElm=dom.get(cssId);linkElm.disabled=!linkElm.disabled;}
ed.controlManager.setActive('visualblocks',!linkElm.disabled);if(linkElm.disabled){cookie.set('wf_visualblocks_state',0);}else{cookie.set('wf_visualblocks_state',1);}});ed.onSetContent.add(function(){var dom=ed.dom,linkElm;if(cssId){linkElm=dom.get(cssId);ed.controlManager.setActive('visualblocks',!linkElm.disabled);}});ed.addButton('visualblocks',{title:'visualblocks.desc',cmd:'mceVisualBlocks'});ed.onInit.add(function(){if(state){ed.execCommand('mceVisualBlocks',false,null,{skip_focus:true});}});},getInfo:function(){return{longname:'Visual blocks',author:'Moxiecode Systems AB',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/visualblocks',version:tinymce.majorVersion+"."+tinymce.minorVersion};}});tinymce.PluginManager.add('visualblocks',tinymce.plugins.VisualBlocks);})();