(function() {
	tinymce.create('tinymce.plugins.HighslideExpanderPlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceHsExpander', function() {
				var se = ed.selection;

				ed.windowManager.open({
					file : ed.getParam('site_url') + 'index.php?option=com_jce&view=editor&layout=plugin&plugin=hsexpander',
					width : 560 + ed.getLang('hsexpander.delta_width', 0),
					height : 780 + ed.getLang('hsexpander.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('hsexpander', {
				title : 'Popup Highslide',
				cmd : 'mceHsExpander',
				image : url + '/img/hsexpander.png'			
			});

			ed.onNodeChange.add(function(ed, cm, n, co) {
				//cm.setDisabled('hsexpander', co && n.nodeName != 'A');
				cm.setActive('hsexpander', (n.nodeName == 'A' || n.nodeName == 'IMG') && !n.name);
			});
            ed.onInit.add( function() {
                if (ed && ed.plugins.contextmenu) {
                    ed.plugins.contextmenu.onContextMenu.add( function(th, m, e) {
                        m.addSeparator();
                        m.add({title : 'hsexpander.desc', icon : 'hsexpander', cmd : 'mceHsExpander', ui : true});
                        if ((e.nodeName == 'A' && !ed.dom.getAttrib(e, 'name'))) {
                            m.add({title : 'advanced.unlink_desc', icon : 'unlink', cmd : 'UnLink'});
                        }
                    });
                }
            });
		},

		getInfo : function() {
			return {
				longname : 'Highslide Expander',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advlink',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('hsexpander', tinymce.plugins.HighslideExpanderPlugin);
})();