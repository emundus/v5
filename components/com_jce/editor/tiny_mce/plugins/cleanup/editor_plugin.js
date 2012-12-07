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
(function(){var each=tinymce.each,extend=tinymce.extend;tinymce.create('tinymce.plugins.CleanupPlugin',{init:function(ed,url){var self=this;this.editor=ed;if(ed.settings.verify_html===false){ed.settings.validate=false;}
ed.onPreInit.add(function(){if(ed.settings.validate){var invalidAttribValue=ed.getParam('invalid_attribute_values','');if(invalidAttribValue){function replaceAttributeValue(nodes,name,re){var i=nodes.length,node;while(i--){node=nodes[i];if(new RegExp(re).test(node.attr(name))){node.attr(name,"");if(name=='src'||name=='href'){node.attr('data-mce-'+name,"");}}}}
each(tinymce.explode(invalidAttribValue),function(item){var re,matches=/([a-z\*]+)\[([a-z]+)([\^\$]?=)["']([^"']+)["']\]/i.exec(item);if(matches&&matches.length==5){var tag=matches[1],attrib=matches[2],expr=matches[3],value=matches[4];switch(expr){default:case'=':re='('+value+')';break;case'!=':re='(^'+value+')';break;case'^=':re='^('+value+')';break;case'$=':re='('+value+')$';break;}
if(tag=='*'){ed.parser.addAttributeFilter(attrib,function(nodes,name){replaceAttributeValue(nodes,name,re);});}else{ed.parser.addNodeFilter(tag,function(nodes,name){replaceAttributeValue(nodes,attrib,re);});}}});}}
ed.parser.addAttributeFilter('onclick,ondblclick',function(nodes,name){var i=nodes.length,node;while(i--){node=nodes[i];node.attr('data-mce-'+name,node.attr(name));node.attr(name,null);}});ed.serializer.addAttributeFilter('data-mce-onclick,data-mce-ondblclick',function(nodes,name){var i=nodes.length,node,k;while(i--){node=nodes[i],k=name.replace('data-mce-','');node.attr(k,node.attr(name));node.attr(name,null);}});});if(ed.settings.validate===false&&ed.settings.verify_html===false){ed.addCommand('mceCleanup',function(){var s=ed.settings,se=ed.selection,bm;bm=se.getBookmark();var content=ed.getContent({cleanup:true});var schema=new tinymce.html.Schema({validate:true,verify_html:true,valid_styles:s.valid_styles,valid_children:s.valid_children,custom_elements:s.custom_elements,extended_valid_elements:s.extended_valid_elements});content=new tinymce.html.Serializer({validate:true},schema).serialize(new tinymce.html.DomParser({validate:true},schema).parse(content));ed.setContent(content,{cleanup:true});se.moveToBookmark(bm);});}
ed.onBeforeSetContent.add(function(ed,o){o.content=o.content.replace(/<pre xml:\s*(.*?)>(.*?)<\/pre>/g,'<pre class="geshi-$1">$2</pre>');if(ed.settings.validate){if(ed.getParam('invalid_attributes')){var s=ed.getParam('invalid_attributes','');o.content=o.content.replace(new RegExp('<([^>]+)('+s.replace(/,/g,'|')+')="([^"]+)"([^>]*)>','gi'),'<$1$4>');}}});ed.onPostProcess.add(function(ed,o){if(o.set){o.content=o.content.replace(/<pre xml:\s*(.*?)>(.*?)<\/pre>/g,'<pre class="geshi-$1">$2</pre>');}
if(o.get){o.content=o.content.replace(/<pre class="geshi-(.*?)">(.*?)<\/pre>/g,'<pre xml:$1>$2</pre>');o.content=o.content.replace(/<a([^>]*)class="jce(box|popup|lightbox|tooltip|_tooltip)"([^>]*)><\/a>/gi,'');o.content=o.content.replace(/<span class="jce(box|popup|lightbox|tooltip|_tooltip)">(.*?)<\/span>/gi,'$2');o.content=o.content.replace(/_mce_(src|href|style|coords|shape)="([^"]+)"\s*?/gi,'');if(ed.getParam('keep_nbsp',true)){o.content=o.content.replace(/\u00a0/g,'&nbsp;');}
if(ed.settings.validate===false){o.content=o.content.replace(/<body([^>]*)>([\s\S]*)<\/body>/,'$2');o.content=o.content.replace(/<(p|h1|h2|h3|h4|h5|h6|th|td|pre|div|address|caption)([^>]*)><\/\1>/gi,'<$1$2>&nbsp;</$1>');}}});ed.onGetContent.add(function(ed,o){if(o.save){if(ed.getParam('cleanup_pluginmode')){o.content=o.content.replace(/&#39;/gi,"'");o.content=o.content.replace(/&apos;/gi,"'");o.content=o.content.replace(/&amp;/gi,"&");o.content=o.content.replace(/&quot;/gi,'"');}}});ed.addButton('cleanup',{title:'advanced.cleanup_desc',cmd:'mceCleanup'});},getInfo:function(){return{longname:'Cleanup',author:'Ryan Demmer',authorurl:'http://www.joomlacontenteditor.net',infourl:'http://www.joomlacontenteditor.net',version:'2.2.1.2'};}});tinymce.PluginManager.add('cleanup',tinymce.plugins.CleanupPlugin);})();