(function() {
	tinymce.create('tinymce.plugins.HighslideHtmlExpanderPlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceHsHtmlExpander', function() {
				var se = ed.selection;

				ed.windowManager.open({
					file : ed.getParam('site_url') + 'index.php?option=com_jce&view=editor&layout=plugin&plugin=hshtmlexpander',
					width : 584 + ed.getLang('hshtmlexpander.delta_width', 0),
					height : 740 + ed.getLang('hshtmlexpander.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('hshtmlexpander', {
				title : 'Popup Highslide HTML',
				cmd : 'mceHsHtmlExpander',
				image : url + '/img/hshtmlexpander.png'			});

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('hshtmlexpander', co && n.nodeName != 'A');
				cm.setActive('hshtmlexpander', n.nodeName == 'A' && !n.name);
			});
            ed.onInit.add( function() {
                if (ed && ed.plugins.contextmenu) {
                    ed.plugins.contextmenu.onContextMenu.add( function(th, m, e) {
                        m.addSeparator();
                        m.add({title : 'hshtmlexpander.desc', icon : 'hshtmlexpander', cmd : 'mceHsHtmlExpander', ui : true});
                        if ((e.nodeName == 'A' && !ed.dom.getAttrib(e, 'name'))) {
                            m.add({title : 'advanced.unlink_desc', icon : 'unlink', cmd : 'UnLink'});
                        }
                    });
                }
            });
		},

		getInfo : function() {
			return {
				longname : 'Highslide HTML Expander',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advlink',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('hshtmlexpander', tinymce.plugins.HighslideHtmlExpanderPlugin);
})();