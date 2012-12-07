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
(function(){tinyMCEPopup.requireLangPack();var PageBreakDialog={init:function(){var self=this,ed=tinyMCEPopup.editor,s=ed.selection,n=s.getNode(),action='insert';tinyMCEPopup.resizeToInnerSize();$('button#insert').click(function(e){self.insert();e.preventDefault();});$.Plugin.init();if(n.nodeName=='IMG'&&ed.dom.hasClass(n,'mceItemPageBreak')){action='update';$('#title').val(ed.dom.getAttrib(n,'title',''));$('#alt').val(ed.dom.getAttrib(n,'alt',''));}
$('#insert').button('option','label',tinyMCEPopup.getLang(action,'Insert',true));},insert:function(){var d=document,ed=tinyMCEPopup.editor,s=ed.selection,n=s.getNode();var v={title:$('#title').val(),alt:$('#alt').val()};if(n&&n.nodeName=='IMG'&&ed.dom.hasClass(n,'mceItemPageBreak')){ed.dom.setAttribs(n,v);}else{tinyMCEPopup.execCommand('mcePageBreak',false,v);}
tinyMCEPopup.close();}};tinyMCEPopup.onInit.add(PageBreakDialog.init,PageBreakDialog);})();