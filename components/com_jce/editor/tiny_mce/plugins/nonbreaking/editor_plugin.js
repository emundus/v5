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
(function(){tinymce.create("tinymce.plugins.Nonbreaking",{init:function(a,b){var c=this;c.editor=a;a.addCommand("mceNonBreaking",function(){a.execCommand("mceInsertContent",false,(a.plugins.visualchars&&a.plugins.visualchars.state)?'<span data-mce-bogus="1" class="mceItemHidden mceItemNbsp">&nbsp;</span>':"&nbsp;")});a.addButton("nonbreaking",{title:"nonbreaking.nonbreaking_desc",cmd:"mceNonBreaking"});if(a.getParam("nonbreaking_force_tab")){a.onKeyDown.add(function(d,f){if(f.keyCode==9){f.preventDefault();d.execCommand("mceNonBreaking");d.execCommand("mceNonBreaking");d.execCommand("mceNonBreaking")}})}},getInfo:function(){return{longname:"Nonbreaking space",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/nonbreaking",version:tinymce.majorVersion+"."+tinymce.minorVersion}}});tinymce.PluginManager.add("nonbreaking",tinymce.plugins.Nonbreaking)})();