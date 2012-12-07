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
(function(){var each=tinymce.each,cookie=tinymce.util.Cookie,DOM=tinymce.DOM;tinymce.create('tinymce.plugins.KitchenSink',{init:function(ed,url){var state=false;function toggle(row){var n=DOM.getNext(row,'table.mceToolbar');while(n){if(DOM.isHidden(n)){DOM.show(n);state=true;}else{DOM.hide(n);state=false;}
n=DOM.getNext(n,'table.mceToolbar');}
ed.controlManager.setActive('kitchensink',state);}
ed.addCommand('mceKitchenSink',function(){var row=DOM.getParents(ed.id+'_kitchensink','table.mceToolbar');if(row){toggle(row[0]);}});ed.addButton('kitchensink',{title:'kitchensink.desc',cmd:'mceKitchenSink'});ed.onPostRender.add(function(ed,cm){if(DOM.get('mce_fullscreen')){state=true;return;}
ed.execCommand('mceKitchenSink');DOM.setStyle(ed.id+'_ifr','height',ed.getContentAreaContainer().offsetHeight);});ed.onInit.add(function(ed){ed.controlManager.setActive('kitchensink',state);});},getInfo:function(){return{longname:'Kitchen Sink',author:'Ryan Demmer',authorurl:'http://www.joomlacontenteditor.net/',infourl:'http://www.joomlacontenteditor.net/',version:'2.2.1.2'};}});tinymce.PluginManager.add('kitchensink',tinymce.plugins.KitchenSink);})();