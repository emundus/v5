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
(function(){var DOM=tinymce.DOM;tinymce.create('tinymce.themes.NoSkin',{init:function(ed,url){var t=this,s=ed.settings;t.editor=ed;function grabContent(){var n,or,r,se=ed.selection;n=ed.dom.add(ed.getBody(),'div',{id:'_mcePaste',style:'position:absolute;left:-1000px;top:-1000px'},'<br mce_bogus="1" />').firstChild;or=ed.selection.getRng();r=ed.getDoc().createRange();r.setStart(n,0);r.setEnd(n,0);se.setRng(r);window.setTimeout(function(){var n=ed.dom.get('_mcePaste');h=n.innerHTML;ed.dom.remove(n);se.setRng(or);h=h.replace(/<\/?\w+[^>]*>/gi,'');el=ed.dom.create('div',0,h);tinymce.each(ed.dom.select('span',el).reverse(),function(n){if(ed.dom.getAttribs(n).length<=1&&n.className==='')
return ed.dom.remove(n,1);});ed.execCommand('mceInsertContent',false,ed.serializer.serialize(el,{getInner:1}));},0);};ed.onInit.add(function(){ed.onBeforeExecCommand.add(function(ed,cmd,ui,val,o){o.terminate=true;return false;});ed.dom.loadCSS(url+"/skins/default/content.css");});ed.onKeyDown.add(function(ed,e){if((e.ctrlKey&&e.keyCode==86)||(e.shiftKey&&e.keyCode==45))
grabContent();});ed.onKeyDown.add(function(ed,e){if((e.ctrlKey&&e.keyCode==66)||(e.ctrlKey&&e.keyCode==73)||(e.ctrlKey&&e.keyCode==85))
return tinymce.dom.Event.cancel(e);});DOM.loadCSS((s.editor_css?ed.baseURI.toAbsolute(s.editor_css):'')||url+"/skins/default/ui.css");},renderUI:function(o){var t=this,n=o.targetNode,ic,tb,ed=t.editor,cf=ed.controlManager,sc;n=DOM.insertAfter(DOM.create('span',{id:ed.id+'_container','class':'mceEditor defaultNoSkin'}),n);n=sc=DOM.add(n,'table',{cellPadding:0,cellSpacing:0,'class':'mceLayout'});n=tb=DOM.add(n,'tbody');n=DOM.add(tb,'tr');n=ic=DOM.add(DOM.add(n,'td'),'div',{'class':'mceIframeContainer'});return{iframeContainer:ic,editorContainer:ed.id+'_container',sizeContainer:sc,deltaHeight:-20};},getInfo:function(){return{longname:'Simple theme',author:'Moxiecode Systems AB',authorurl:'http://tinymce.moxiecode.com',version:tinymce.majorVersion+"."+tinymce.minorVersion};}});tinymce.ThemeManager.add('none',tinymce.themes.NoSkin);})();