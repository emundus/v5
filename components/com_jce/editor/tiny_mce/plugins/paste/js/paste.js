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
tinyMCEPopup.requireLangPack();var PasteDialog={settings:{},init:function(){var self=this,ed=tinyMCEPopup.editor,el=document.getElementById('container'),title=document.getElementById('title'),ifr,doc,css,cssHTML='';$.Plugin.init();$('#insert').click(function(e){self.insert();e.preventDefault;});$('#cancel').click(function(e){tinyMCEPopup.close();e.preventDefault;});var cmd=tinyMCEPopup.getWindowArg('cmd');title.innerHTML=ed.getLang('paste_dlg.title','Use CTRL+V on your keyboard to paste text into the window.');if(cmd=='mcePaste'){document.title=ed.getLang('paste.paste_desc');el.innerHTML='<iframe id="content" src="javascript:\'\';" frameBorder="0" style="border: 1px solid gray"></iframe>';ifr=document.getElementById('content');doc=ifr.contentWindow.document;css=tinymce.explode(ed.settings.content_css)||[];css.push(ed.baseURI.toAbsolute("themes/"+ed.settings.theme+"/skins/"+ed.settings.skin+"/content.css"));css.push(ed.baseURI.toAbsolute("plugins/paste/css/blank.css"));tinymce.each(css,function(u){cssHTML+='<link href="'+ed.documentBaseURI.toAbsolute(''+u)+'" rel="stylesheet" type="text/css" />';});doc.open();doc.write('<html><head><base href="'+ed.settings.base_url+'" />'+cssHTML+'</head><body class="mceContentBody" spellcheck="false"></body></html>');doc.close();doc.designMode='on';window.setTimeout(function(){ifr.contentWindow.focus();},10);}else{document.title=ed.getLang('paste.paste_text_desc');el.innerHTML='<textarea id="content" name="content" rows="15" cols="100" dir="ltr" wrap="soft" class="mceFocus"></textarea>';}
this.resize();},insert:function(){var h,wc,c=document.getElementById('content');tinyMCEPopup.restoreSelection();if(c.nodeName=='TEXTAREA'){h=c.value;lines=h.split(/\r?\n/);if(lines.length>1){h='';tinymce.each(lines,function(row){if(tinyMCEPopup.editor.getParam('force_p_newlines')){h+='<p>'+row+'</p>';}
else{h+=row+'<br />';}});}
wc=false;}else{h=c.contentWindow.document.body.innerHTML;wc=true;}
tinyMCEPopup.editor.execCommand('mceInsertClipboardContent',false,{content:h,wordContent:wc});tinyMCEPopup.close();},resize:function(){var vp=tinyMCEPopup.dom.getViewPort(window),el;el=document.getElementById('content');if(el){el.style.width=(vp.w-20)+'px';el.style.height=(vp.h-90)+'px';}}};tinyMCEPopup.onInit.add(PasteDialog.init,PasteDialog);