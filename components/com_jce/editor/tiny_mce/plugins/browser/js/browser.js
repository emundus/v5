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
var BrowserDialog={settings:{},preInit:function(){tinyMCEPopup.requireLangPack();},init:function(ed){var action="insert";$('button#insert').click(function(e){BrowserDialog.insert();e.preventDefault();});tinyMCEPopup.resizeToInnerSize();var win=tinyMCEPopup.getWindowArg("window");var src=tinyMCEPopup.getWindowArg("url");if(src){src=tinyMCEPopup.editor.convertURL(src);action="update";}
$('#insert').button('option','label',tinyMCEPopup.getLang('lang_'+action,'Insert',true));if(/(:\/\/|www|index.php(.*)\?option)/gi.test(src)){src='';}
$('<input type="hidden" id="src" value="'+src+'" />').appendTo(document.body);$.Plugin.init();WFFileBrowser.init('#src',{onFileClick:function(e,file){BrowserDialog.selectFile(file);},onFileInsert:function(e,file){BrowserDialog.selectFile(file);},expandable:false});},insert:function(){var win=tinyMCEPopup.getWindowArg("window");win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value=$('#src').val();tinyMCEPopup.close();},selectFile:function(file){var self=this;var name=file.title;var src=$.String.path(WFFileBrowser.get('getBaseDir'),file.id);src=src.charAt(0)=='/'?src.substring(1):src;$('#src').val(src);}};BrowserDialog.preInit();tinyMCEPopup.onInit.add(BrowserDialog.init,BrowserDialog);