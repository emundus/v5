var FbListEmail = new Class({
	
	Extends : FbListPlugin,
	
	initialize: function (options) {
		this.parent(options);
	},

	buttonAction: function () {
		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&controller=list.email&task=popupwin&tmpl=component&iframe=1&id=' + this.listid + '&renderOrder=' + this.options.renderOrder;
		this.listform.getElements('input[name^=ids]').each(function (id) {
			if (id.get('value') !== false && id.checked !== false) {
				url += "&ids[]=" + id.get('value');
			}
		});
		var id = 'email-list-plugin';
		this.windowopts = {
			'id': id,
			title: 'Email',
			loadMethod: 'iframe',
			contentURL: url,
			width: 520,
			height: 420,
			evalScripts: true,
			y: 100,
			'minimizable': false,
			'collapsible': true,
			'onContentLoaded': function () {
				this.fitToContent();
			}
		};
		Fabrik.getWindow(this.windowopts);
	}

});
