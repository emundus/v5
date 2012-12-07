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
(function(){tinymce.create('tinymce.plugins.FormatPlugin',{init:function(ed,url){var t=this;this.editor=ed;var blocks='p,div,address,pre,h1,h2,h3,h4,h5,h6,dl,dt,dd,code,samp';function isBlock(n){return new RegExp('^('+blocks.replace(',','|','g')+')$','i').test(n.nodeName);}
ed.onBeforeExecCommand.add(function(ed,cmd,ui,v,o){var se=ed.selection,n=se.getNode(),p;switch(cmd){case'FormatBlock':if(se.isCollapsed()){return;}
if(!v){ed.undoManager.add();p=ed.dom.getParent(n,blocks);if(p){ed.formatter.toggle(p.nodeName.toLowerCase());}
o.terminate=true;}
break;case'RemoveFormat':if(!v&&isBlock(n)){ed.undoManager.add();p=ed.dom.getParent(n,blocks);if(p){ed.formatter.toggle(p.nodeName.toLowerCase());}
o.terminate=true;}
break;}});ed.onExecCommand.add(function(ed,cmd,ui,v,o){var se=ed.selection,n=se.getNode();switch(cmd){case'FormatBlock':if(v=='dt'||v=='dd'){if(n&&n.nodeName!=='DL'){ed.formatter.apply('dl');}}
break;}});t.onClearBlocks=new tinymce.util.Dispatcher(t);tinymce.isChrome=tinymce.isWebkit&&/chrome/i.test(navigator.userAgent);ed.onPreInit.add(function(){function wrapList(node){var sibling=node.prev;if(node.parent&&node.parent.name=='dl'){return;}
if(sibling&&(sibling.name==='dl'||sibling.name==='dl')){sibling.append(node);return;}
sibling=node.next;if(sibling&&(sibling.name==='dl'||sibling.name==='dl')){sibling.insert(node,sibling.firstChild,true);return;}
node.wrap(ed.parser.filterNode(new tinymce.html.Node('dl',1)));}
ed.parser.addNodeFilter('dt,dd',function(nodes){for(var i=0,len=nodes.length;i<len;i++){wrapList(nodes[i]);}});ed.serializer.addNodeFilter('dt,dd',function(nodes){for(var i=0,len=nodes.length;i<len;i++){wrapList(nodes[i]);}});});},_select:function(el){var ed=this.editor,s=ed.selection,pos,br,r,fn;if(tinymce.isIE){s.select(el.firstChild);s.collapse(0);r=s.getRng();fn=s.getNode().firstChild;br=fn.nodeName=='BR'&&fn.getAttribute('mce_bogus');pos=br?-1:-2;r.move('character',pos);r.select();if(br){ed.dom.remove(fn);}}else{r=ed.getDoc().createRange();r.setStart(el,0);r.setEnd(el,0);s.setRng(r);}},_clearBlocks:function(ed,e){var ed=this.editor,dom=ed.dom,s,p,a=[],b,bm,n;var tag=ed.getParam('forced_root_block')||'br';n=ed.selection.getNode();p=dom.getParent(n,function(s){a.push(s);},ed.getBody());var el=dom.create(tag);var h=(tag=='br')?'':'<br data-mce-bogus="1" />';dom.setHTML(el,h);dom.insertAfter(el,a[a.length-1]);this._select(el);},getInfo:function(){return{longname:'Format',author:'Ryan Demmer',authorurl:'http://www.joomlacontenteditor.net',infourl:'http://www.joomlacontenteditor.net',version:'2.2.1.2'};}});tinymce.PluginManager.add('format',tinymce.plugins.FormatPlugin);})();