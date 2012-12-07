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
(function(){tinymce.create('tinymce.plugins.VisualChars',{init:function(ed,url){var t=this;t.editor=ed;var state=tinymce.util.Cookie.get('wf_visualchars_state');state=tinymce.is(state,'string')?parseFloat(state):ed.getParam('visualchars_default_state',0);ed.onInit.add(function(){ed.controlManager.setActive('visualchars',state);t._toggleVisualChars(state);});ed.addButton('visualchars',{title:'visualchars.desc',cmd:'mceVisualChars'});ed.addCommand('mceVisualChars',function(){state=!state;ed.controlManager.setActive('visualchars',state);t._toggleVisualChars(state);if(ed.getParam('use_cookies',1)){tinymce.util.Cookie.set('wf_visualchars_state',state?1:0);}},t);ed.onKeyUp.add(function(ed,e){if(state){if(e.keyCode==13){t._toggleVisualChars(state);}}});ed.onPreProcess.add(function(ed,o){if(o.get){t._toggleVisualChars(false,o.node);}});ed.onSetContent.add(function(ed,o){t._toggleVisualChars(state);});},getInfo:function(){return{longname:'Visual characters',author:'Moxiecode Systems AB / Ryan Demmer',authorurl:'http://tinymce.moxiecode.com',infourl:'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/visualchars',version:tinymce.majorVersion+"."+tinymce.minorVersion};},_toggleVisualChars:function(state,o){var t=this,ed=t.editor,nl,i,h,d=ed.getDoc(),b=o||ed.getBody(),nv,s=ed.selection,bo,div;if(state){nl=[];tinymce.walk(b,function(n){if(n.nodeType==3&&n.nodeValue&&/(\u00a0|&nbsp;)/.test(n.nodeValue))
nl.push(n);},'childNodes');for(i=0;i<nl.length;i++){nv=nl[i].nodeValue;nv=nv.replace(/(\u00a0|&nbsp;)/g,'<span class="mceItemHidden mceItemNbsp">$1</span>');div=ed.dom.create('div',null,nv);while(node=div.lastChild)
ed.dom.insertAfter(node,nl[i]);ed.dom.remove(nl[i]);}}else{nl=ed.dom.select('span.mceItemNbsp',b);for(i=nl.length-1;i>=0;i--){ed.dom.remove(nl[i],1);}}}});tinymce.PluginManager.add('visualchars',tinymce.plugins.VisualChars);})();