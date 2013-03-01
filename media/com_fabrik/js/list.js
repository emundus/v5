/**
 * @author Robert
 */

/* jshint mootools: true */
/*
 * global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true,
 * $H:true,unescape:true,head:true,FbListActions:true,FbGroupedToggler:true,FbListKeys:true
 */

var FbListPlugin = new Class({
	Implements: [Events, Options],
	options: {
		requireChecked: true
	},

	initialize: function (options) {
		this.setOptions(options);
		this.result = true; // set this to false in window.fireEvents to stop
												// current action (eg stop ordering when
												// fabrik.list.order run)
		head.ready(function () {
			this.listform = this.getList().getForm();
			var l = this.listform.getElement('input[name=listid]');
			// in case its in a viz
			if (typeOf(l) === 'null') {
				return;
			}
			this.listid = l.value;
			this.watchButton();
		}.bind(this));
	},

	/**
	 * get the list object that the plugin is assigned to
	 */

	getList: function () {
		return Fabrik.blocks['list_' + this.options.ref];
	},
	
	/**
	 * get a html nodes row id - so you can pass in td or tr for example
	 * presumes each row has a fabrik_row class and its id is in a string 'list_listref_rowid'
	 */

	getRowId: function (node) {
		if (!node.hasClass('fabrik_row')) {
			node = node.getParent('.fabrik_row'); 
		}
		return node.id.split('_').getLast();
	},

	clearFilter: Function.from(),

	watchButton: function () {
		// Do relay for floating menus
		if (typeOf(this.options.name) === 'null') {
			return;
		}
		// Might need to be this.listform and not document
		document.addEvent('click:relay(.' + this.options.name + ')', function (e, element) {
			if (e.rightClick) {
				return;
			}
			e.stop();
			
			// Check that the button clicked belongs to this this.list
			if (element.get('data-list') !== this.list.options.listRef) {
				return;
			}
			e.preventDefault();
			var row, chx;
			// if the row button is clicked check its associated checkbox
			if (e.target.getParent('.fabrik_row')) {
				row = e.target.getParent('.fabrik_row');
				if (row.getElement('input[name^=ids]')) {
					chx = row.getElement('input[name^=ids]');
					this.listform.getElements('input[name^=ids]').set('checked', false);
					chx.set('checked', true);
				}
			}

			// check that at least one checkbox is checked
			var ok = false;
			this.listform.getElements('input[name^=ids]').each(function (c) {
				if (c.checked) {
					ok = true;
				}
			});
			if (!ok && this.options.requireChecked) {
				alert(Joomla.JText._('COM_FABRIK_PLEASE_SELECT_A_ROW'));
				return;
			}
			var n = this.options.name.split('-');
			this.listform.getElement('input[name=fabrik_listplugin_name]').value = n[0];
			this.listform.getElement('input[name=fabrik_listplugin_renderOrder]').value = n.getLast();
			this.buttonAction();
		}.bind(this));
	},

	buttonAction: function () {
		this.list.submit('list.doPlugin');
	}
});

var FbList = new Class({

	Implements: [Options, Events],

	options: {
		'admin': false,
		'filterMethod': 'onchange',
		'ajax': false,
		'ajax_links': false,
		'links': {'edit': '', 'detail': '', 'add': ''},
		'form': 'listform_' + this.id,
		'hightLight': '#ccffff',
		'primaryKey': '',
		'headings': [],
		'labels': {},
		'Itemid': 0,
		'formid': 0,
		'canEdit': true,
		'canView': true,
		'page': 'index.php',
		'actionMethod': 'floating',
		'formels': [], // elements that only appear in the form
		'data': [], // [{col:val, col:val},...] (depreciated)
		'rowtemplate': '',
		'floatPos': 'left',
		'csvChoose': false,
		'csvOpts': {},
		'popup_width': 300,
		'popup_height': 300,
		'popup_offset_x': null,
		'popup_offset_y': null,
		'groupByOpts': {},
		'listRef': '', // e.g. '1_com_fabrik_1'
		'fabrik_show_in_list': [],
		'singleOrdering' : false
	},

	initialize: function (id, options) {
		this.id = id;
		this.setOptions(options);
		this.getForm();
		this.result = true; //used with plugins to determine if list actions should be performed
		this.plugins = [];
		this.list = document.id('list_' + this.options.listRef);
		this.actionManager = new FbListActions(this, {
			'method': this.options.actionMethod,
			'floatPos': this.options.floatPos
		});
		this.groupToggle = new FbGroupedToggler(this.form, this.options.groupByOpts);
		new FbListKeys(this);
		if (this.list) {
			if (this.list.get('tag') === 'table') {
				this.tbody = this.list.getElement('tbody');
			}
			if (typeOf(this.tbody) === 'null') {
				this.tbody = this.list;
			}
			// $$$ rob mootools 1.2 has bug where we cant set('html') on table
			// means that there is an issue if table contains no data
			if (window.ie) {
				this.options.rowtemplate = this.list.getElement('.fabrik_row');
			}
		}
		this.watchAll(false);
		Fabrik.addEvent('fabrik.form.submitted', function () {
			this.updateRows();
		}.bind(this));
		
		/**
		 * once an ajax form has been submitted lets clear out any loose events and the form object itself
		 */
		Fabrik.addEvent('fabrik.form.ajax.submit.end', function (form) {
			form.formElements.each(function (el) {
				el.removeCustomEvents();
			});
			delete Fabrik.blocks['form_' + form.id];
		});
	},

	setRowTemplate: function () {
		// $$$ rob mootools 1.2 has bug where we cant setHTML on table
		// means that there is an issue if table contains no data
		if (typeOf(this.options.rowtemplate) === 'string') {
			var r = this.list.getElement('.fabrik_row');
			if (window.ie && typeOf(r) !== 'null') {
				this.options.rowtemplate = r;
			}
		}
	},

	watchAll: function (ajaxUpdate) {
		ajaxUpdate = ajaxUpdate ? ajaxUpdate : false;
		this.watchNav();
		if (!ajaxUpdate) {
			this.watchRows();
		}
		this.watchFilters();
		this.watchOrder();
		this.watchEmpty();
		this.watchButtons();
	},

	watchButtons: function () {
		this.exportWindowOpts = {
			id: 'exportcsv',
			title: 'Export CSV',
			loadMethod: 'html',
			minimizable: false,
			width: 360,
			height: 120,
			content: ''
		};
		if (this.options.view === 'csv') {
			//for csv links e.g. index.php?option=com_fabrik&view=csv&listid=10
			this.openCSVWindow();
		} else {
			if (this.form.getElements('.csvExportButton')) {
				this.form.getElements('.csvExportButton').each(function (b) {
					if (b.hasClass('custom') === false) {
						b.addEvent('click', function (e) {
							this.openCSVWindow();
							e.stop();
						}.bind(this));
					}
				}.bind(this));
			}
		}
	},
	
	openCSVWindow: function () {
		var thisc = this.makeCSVExportForm();
		this.exportWindowOpts.content = thisc;
		this.exportWindowOpts.onContentLoaded = function () {
			this.fitToContent();
		};
		Fabrik.getWindow(this.exportWindowOpts);
	},

	makeCSVExportForm: function () {
		if (this.options.csvChoose) {
			return this._csvExportForm();
		} else {
			return this._csvAutoStart();
		}
	},
	
	_csvAutoStart: function () {
		var c = new Element('div', {
			'id': 'csvmsg'
		}).set('html', Joomla.JText._('COM_FABRIK_LOADING') + ' <br /><span id="csvcount">0</span> / <span id="csvtotal"></span> ' + Joomla.JText._('COM_FABRIK_RECORDS') + '.<br/>' + Joomla.JText._('COM_FABRIK_SAVING_TO') + '<span id="csvfile"></span>');
		
		this.csvopts = this.options.csvOpts;
		this.csvfields = this.options.csvFields;
		
		this.triggerCSVExport(-1);
		return c;
	},
	
	_csvExportForm: function () {
		// cant build via dom as ie7 doesn't accept checked status
		var rad = "<input type='radio' value='1' name='incfilters' checked='checked' />" + Joomla.JText._('JYES');
		var rad2 = "<input type='radio' value='1' name='incraw' checked='checked' />" + Joomla.JText._('JYES');
		var rad3 = "<input type='radio' value='1' name='inccalcs' checked='checked' />" + Joomla.JText._('JYES');
		var rad4 = "<input type='radio' value='1' name='inctabledata' checked='checked' />" + Joomla.JText._('JYES');
		var rad5 = "<input type='radio' value='1' name='excel' checked='checked' />Excel CSV";
		var url = 'index.php?option=com_fabrik&view=list&listid=' + this.id + '&format=csv&Itemid=' + this.options.Itemid;

		var divopts = {
			'styles': {
				'width': '200px',
				'float': 'left'
			}
		};
		var c = new Element('form', {
			'action': url,
			'method': 'post'
		}).adopt([new Element('div', divopts).set('text', Joomla.JText._('COM_FABRIK_FILE_TYPE')), new Element('label').set('html', rad5), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'excel',
			'value': '0'
		}), new Element('span').set('text', 'CSV')]), new Element('br'), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_FILTERS')), new Element('label').set('html', rad), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'incfilters',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))]), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_DATA')), new Element('label').set('html', rad4), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'inctabledata',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))]), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_RAW_DATA')), new Element('label').set('html', rad2), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'incraw',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))]), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INLCUDE_CALCULATIONS')), new Element('label').set('html', rad3), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'inccalcs',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))])]);
		new Element('h4').set('text', Joomla.JText._('COM_FABRIK_SELECT_COLUMNS_TO_EXPORT')).inject(c);
		var g = '';
		var i = 0;
		$H(this.options.labels).each(function (label, k) {
			if (k.substr(0, 7) !== 'fabrik_' && k !== '____form_heading') {
				var newg = k.split('___')[0];
				if (newg !== g) {
					g = newg;
					new Element('h5').set('text', g).inject(c);
				}
				var rad = "<input type='radio' value='1' name='fields[" + k + "]' checked='checked' />" + Joomla.JText._('JYES');
				label = label.replace(/<\/?[^>]+(>|$)/g, "");
				var r = new Element('div', divopts).appendText(label);
				r.inject(c);
				new Element('label').set('html', rad).inject(c);
				new Element('label').adopt([new Element('input', {
					'type': 'radio',
					'name': 'fields[' + k + ']',
					'value': '0'
				}), new Element('span').appendText(Joomla.JText._('JNO'))]).inject(c);
				new Element('br').inject(c);
			}
			i++;
		}.bind(this));

		// elements not shown in table
		if (this.options.formels.length > 0) {
			new Element('h5').set('text', Joomla.JText._('COM_FABRIK_FORM_FIELDS')).inject(c);
			this.options.formels.each(function (el) {
				var rad = "<input type='radio' value='1' name='fields[" + el.name + "]' checked='checked' />" + Joomla.JText._('JYES');
				var r = new Element('div', divopts).appendText(el.label);
				r.inject(c);
				new Element('label').set('html', rad).inject(c);
				new Element('label').adopt([new Element('input', {
					'type': 'radio',
					'name': 'fields[' + el.name + ']',
					'value': '0'
				}), new Element('span').set('text', Joomla.JText._('JNO'))]).inject(c);
				new Element('br').inject(c);
			}.bind(this));
		}

		new Element('div', {
			'styles': {
				'text-align': 'right'
			}
		}).adopt(new Element('input', {
			'type': 'button',
			'name': 'submit',
			'value': Joomla.JText._('COM_FABRIK_EXPORT'),
			'class': 'button exportCSVButton',
			events: {
				'click': function (e) {
					e.stop();
					e.target.disabled = true;
					var csvMsg = document.id('csvmsg');
					if (typeOf(csvMsg) === 'null') {
						csvMsg = new Element('div', {
							'id': 'csvmsg'
						}).inject(e.target, 'before');
					}
					csvMsg.set('html', Joomla.JText._('COM_FABRIK_LOADING') + ' <br /><span id="csvcount">0</span> / <span id="csvtotal"></span> ' + Joomla.JText._('COM_FABRIK_RECORDS') + '.<br/>' + Joomla.JText._('COM_FABRIK_SAVING_TO') + '<span id="csvfile"></span>');
					this.triggerCSVExport(0);
				}.bind(this)

			}
		})).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'view',
			'value': 'table'
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'option',
			'value': 'com_fabrik'
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'listid',
			'value': this.id
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'format',
			'value': 'csv'
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'c',
			'value': 'table'
		}).inject(c);
		return c;
	},

	triggerCSVExport: function (start, opts, fields) {
		if (start !== 0) {
			if (start === -1) {
				// not triggered from front end selections
				start = 0;
				opts = this.csvopts;
				opts.fields = this.csvfields;
			} else {
				opts = this.csvopts;
				fields = this.csvfields;
			}
		} else {
			if (!opts) {
				opts = {};
				if (typeOf(document.id('exportcsv')) !== 'null') {
					$A(['incfilters', 'inctabledata', 'incraw', 'inccalcs', 'excel']).each(function (v) {
						var inputs = document.id('exportcsv').getElements('input[name=' + v + ']');
						if (inputs.length > 0) {
							opts[v] = inputs.filter(function (i) {
								return i.checked;
							})[0].value;
						}
					});
				}
			}
			// selected fields
			if (!fields) {
				fields = {};
				if (typeOf(document.id('exportcsv')) !== 'null') {
					document.id('exportcsv').getElements('input[name^=field]').each(function (i) {
						if (i.checked) {
							var k = i.name.replace('fields[', '').replace(']', '');
							fields[k] = i.get('value');
						}
					});
				}
			}
			opts.fields = fields;
			this.csvopts = opts;
			this.csvfields = fields;
		}

		this.getFilters().each(function (f) {
			opts[f.name] = f.get('value');
		}.bind(this));
		
		opts.start = start;
		opts.option = 'com_fabrik';
		opts.view = 'list';
		opts.format = 'csv';
		opts.Itemid = this.options.Itemid;
		opts.listid = this.id;
		opts.listref = this.id;
		this.options.csvOpts.custom_qs.split('&').each(function (qs) {
			var key = qs.split('=');
			opts[key[0]] = key[1];
		});
		
		// Append the custom_qs to the URL to enable querystring filtering of the list data
		var myAjax = new Request.JSON({
			url: '?' + this.options.csvOpts.custom_qs,
			method: 'post',
			data: opts,
			onError: function (text, error) {
				fconsole(text, error);
			},
			onComplete: function (res) {
				if (res.err) {
					alert(res.err);
					Fabrik.Windows.exportcsv.close();
				} else {
					if (typeOf(document.id('csvcount')) !== 'null') {
						document.id('csvcount').set('text', res.count);
					}
					if (typeOf(document.id('csvtotal')) !== 'null') {
						document.id('csvtotal').set('text', res.total);
					}
					if (typeOf(document.id('csvfile')) !== 'null') {
						document.id('csvfile').set('text', res.file);
					}
					if (res.count < res.total) {
						this.triggerCSVExport(res.count);
					} else {
						var finalurl = Fabrik.liveSite + 'index.php?option=com_fabrik&view=list&format=csv&listid=' + this.id + '&start=' + res.count + '&Itemid=' + this.options.Itemid;
						var msg = Joomla.JText._('COM_FABRIK_CSV_COMPLETE');
						msg += ' <a href="' + finalurl + '">' + Joomla.JText._('COM_FABRIK_CSV_DOWNLOAD_HERE') + '</a>';
						if (typeOf(document.id('csvmsg')) !== 'null') {
							document.id('csvmsg').set('html', msg);
						}
						document.getElements('input.exportCSVButton').removeProperty('disabled');
					}
				}
			}.bind(this)
		});
		myAjax.send();
	},

	addPlugins: function (a) {
		a.each(function (p) {
			p.list = this;
		}.bind(this));
		this.plugins = a;
	},

	watchEmpty: function (e) {
		var b = document.id(this.options.form).getElement('.doempty', this.options.form);
		if (b) {
			b.addEvent('click', function (e) {
				e.stop();
				if (confirm(Joomla.JText._('COM_FABRIK_CONFIRM_DROP'))) {
					this.submit('list.doempty');
				}
			}.bind(this));
		}
	},

	watchOrder: function () {
		var elementId = false;
		
		var hs = document.id(this.options.form).getElements('.fabrikorder, .fabrikorder-asc, .fabrikorder-desc');
		hs.removeEvents('click');
		hs.each(function (h) {
			h.addEvent('click', function (e) {
				var img = 'ordernone.png';
				var orderdir = '';
				var newOrderClass = '';
				// $$$ rob in pageadaycalendar.com h was null so reset to e.target
				h = document.id(e.target);
				var td = h.getParent('.fabrik_ordercell');
				if (h.tagName !== 'a') {
					h = td.getElement('a');
				}
				switch (h.className) {
				case 'fabrikorder-asc':
					newOrderClass = 'fabrikorder-desc';
					orderdir = 'desc';
					img = 'orderdesc.png';
					break;
				case 'fabrikorder-desc':
					newOrderClass = 'fabrikorder';
					orderdir = "-";
					img = 'ordernone.png';
					break;
				case 'fabrikorder':
					newOrderClass = 'fabrikorder-asc';
					orderdir = 'asc';
					img = 'orderasc.png';
					break;
				}
				td.className.split(' ').each(function (c) {
					if (c.contains('_order')) {
						elementId = c.replace('_order', '').replace(/^\s+/g, '').replace(/\s+$/g, '');
					}
				});
				if (!elementId) {
					fconsole('woops didnt find the element id, cant order');
					return;
				}
				h.className = newOrderClass;
				var i = h.getElement('img');
				
				// Swap images - if list doing ajax nav then we need to do this
				if (this.options.singleOrdering) {
					document.id(this.options.form).getElements('.fabrikorder, .fabrikorder-asc, .fabrikorder-desc').each(function (otherH) {
						var i = otherH.getElement('img');
						if (i) {
							i.src = i.src.replace('ordernone.png', '').replace('orderasc.png', '').replace('orderdesc.png', '');
							i.src += 'ordernone.png';
						}
					});
				}
				if (i) {
					i.src = i.src.replace('ordernone.png', '').replace('orderasc.png', '').replace('orderdesc.png', '');
					i.src += img;
				}
				
				this.fabrikNavOrder(elementId, orderdir);
				e.stop();
			}.bind(this));
		}.bind(this));

	},
	
	getFilters: function () {
		return document.id(this.options.form).getElements('.fabrik_filter');
	},

	watchFilters: function () {
		var e = '';
		var submit = document.id(this.options.form).getElement('.fabrik_filter_submit');
		this.getFilters().each(function (f) {
			e = f.get('tag') === 'select' ? 'change' : 'blur';
			if (this.options.filterMethod !== 'submitform') {
				f.removeEvent(e);
				f.store('initialvalue', f.get('value'));
				//document.getElement('.fabrik_filter_submit').highlight('#ffaa00');
				f.addEvent(e, function (e) {
					e.stop();
					if (e.target.retrieve('initialvalue') !== e.target.get('value')) {
						this.doFilter();
					}
				}.bind(this));
			} else {
				f.addEvent(e, function (e) {
					submit.highlight('#ffaa00');
				}.bind(this));
			}
		}.bind(this));
			
		if (this.options.filterMethod === 'submitform') {
			if (submit) {
				submit.removeEvents();
				submit.addEvent('click', function (e) {
					this.doFilter();
				}.bind(this));
			}
		}
		this.getFilters().addEvent('keydown', function (e) {
			if (e.code === 13) {
				e.stop();
				this.doFilter();
			}
		}.bind(this));
	},
	
	doFilter: function () {
		var res = Fabrik.fireEvent('list.filter', [this]).eventResults;
		if (typeOf(res) === 'null') {
			this.submit('list.filter');
		}
		if (res.length === 0 || !res.contains(false)) {
			this.submit('list.filter');
		}
	},

	// highlight active row, deselect others
	setActive: function (activeTr) {
		this.list.getElements('.fabrik_row').each(function (tr) {
			tr.removeClass('activeRow');
		});
		activeTr.addClass('activeRow');
	},

	getActiveRow: function (e) {
		var row = e.target.getParent('.fabrik_row');
		if (!row) {
			row = Fabrik.activeRow;
		}
		return row;
	},
	
	watchRows: function () {
		if (!this.list) {
			return;
		}
		
		if (this.options.ajax_links) {
			
			// $$$rob - HACKKKKK!!!!!! 
			// not sure why but on ajax first load of xhr content the form object does not ini
			// if we created the window, hidden from view, then this 'fixes' the issue. I'd really like to 
			// find out what the problem is here but for now this band aid is a help
			
			// Didnt work in admin list view if list add acl was anything other than public
			//var url = Fabrik.liveSite + "index.php?option=com_fabrik&view=form&formid=" + this.options.formid + '&rowid=0&tmpl=component&ajax=1';
			var url = 'index.php?option=com_fabrik&task=form.view&formid=' + this.options.formid + '&rowid=0&tmpl=component&ajax=1';
			var winOpts = {
				'id': 'add.' + this.id,
				'title': this.options.popup_edit_label,
				'loadMethod': 'xhr',
				'contentURL': url,
				'visible': false,
				'width': this.options.popup_width,
				'height': this.options.popup_height,
				'onContentLoaded': function () {}
			};
			if (typeOf(this.options.popup_offset_x) !== 'null') {
				winOpts.offset_x = this.options.popup_offset_x;
			}
			if (typeOf(this.options.popup_offset_y) !== 'null') {
				winOpts.offset_y = this.options.popup_offset_y;
			}
			var w = Fabrik.getWindow(winOpts);
		}
	},
	
	getForm: function () {
		if (!this.form) {
			this.form = document.id(this.options.form);
		}
		return this.form;
	},

	submit: function (task) {
		this.getForm();
		if (task === 'list.delete') {
			var ok = false;
			var delCount = 0;
			this.form.getElements('input[name^=ids]').each(function (c) {
				if (c.checked) {
					delCount ++;
					ok = true;
				}
			});
			if (!ok) {
				alert(Joomla.JText._('COM_FABRIK_SELECT_ROWS_FOR_DELETION'));
				Fabrik.loader.stop('listform_' + this.options.listRef);
				return false;
			}
			var delMsg = delCount === 1 ? Joomla.JText._('COM_FABRIK_CONFIRM_DELETE_1') : Joomla.JText._('COM_FABRIK_CONFIRM_DELETE').replace('%s', delCount); 
			if (!confirm(delMsg)) {
				Fabrik.loader.stop('listform_' + this.options.listRef);
				return false;
			}
		}
		// We may want to set this as an option - if long page loads feedback that list is doing something might be useful
		// Fabrik.loader.start('listform_' + this.options.listRef);
		if (task === 'list.filter') {
			Fabrik['filter_listform_' + this.options.listRef].onSubmit();
			this.form.task.value = task;
			if (this.form['limitstart' + this.id]) {
				this.form.getElement('#limitstart' + this.id).value = 0;
			}
		} else {
			if (task !== '') {
				this.form.task.value = task;
			}
		}
		if (this.options.ajax) {
			Fabrik.loader.start('listform_' + this.options.listRef);
			// For module & mambot
			// $$$ rob with modules only set view/option if ajax on
			this.form.getElement('input[name=option]').value = 'com_fabrik';
			this.form.getElement('input[name=view]').value = 'list';
			this.form.getElement('input[name=format]').value = 'raw';
			
			var data = this.form.toQueryString();
			if (task === 'list.filter' && this.advancedSearch !== false) {
				var advSearchForm = document.getElement('form.advancedSeach_' + this.options.listRef);
				if (typeOf(advSearchForm) !== 'null') {
					data += '&' + advSearchForm.toQueryString();
					data += '&replacefilters=1';
				}
			}
			// Pass the elements that are shown in the list - to ensure they are formatted
			for (var i = 0; i < this.options.fabrik_show_in_list.length; i ++) {
				data += '&fabrik_show_in_list[]=' + this.options.fabrik_show_in_list[i]; 
			}
			
			// Add in tmpl for custom nav in admin
			data += '&tmpl=' + this.options.tmpl;
			if (!this.request) {
				this.request = new Request({
					'url': this.form.get('action'),
					'data': data,
					onComplete: function (json) {
						json = JSON.decode(json);
						this._updateRows(json);
						Fabrik.loader.stop('listform_' + this.options.listRef);
						Fabrik['filter_listform_' + this.options.listRef].onUpdateData();
						Fabrik.fireEvent('fabrik.list.submit.ajax.complete', [this, json]);
						if (json.msg) {
							alert(json.msg);
						}
					}.bind(this)
				});
			} else {
				this.request.options.data = data;
			}
			this.request.send();
			Fabrik.fireEvent('fabrik.list.submit', [task, this.form.toQueryString().toObject()]);
		} else {
			this.form.submit();
		}
		//Fabrik['filter_listform_' + this.options.listRef].onUpdateData();
		return false;
	},

	fabrikNav: function (limitStart) {
		this.options.limitStart = limitStart;
		this.form.getElement('#limitstart' + this.id).value = limitStart;
		// cant do filter as that resets limitstart to 0
		Fabrik.fireEvent('fabrik.list.navigate', [this, limitStart]);
		if (!this.result) {
			this.result = true;
			return false;
		}
		this.submit('list.view');
		return false;
	},
	
	/**
	 * Get the primary keys for the visible rows
	 * 
	 * @since   3.0.7
	 * 
	 * @return  array 
	 */
	getRowIds: function () {
		var keys = [];
		$H(this.options.data).each(function (group) {
			group.each(function (row) {
				keys.push(row.data.__pk_val);
			});
		});
		return keys;
	},

	fabrikNavOrder: function (orderby, orderdir) {
		this.form.orderby.value = orderby;
		this.form.orderdir.value = orderdir;
		Fabrik.fireEvent('fabrik.list.order', [this, orderby, orderdir]);
		if (!this.result) {
			this.result = true;
			return false;
		}
		this.submit('list.order');
	},

	removeRows: function (rowids) {
		// @TODO: try to do this with FX.Elements
		for (i = 0; i < rowids.length; i++) {
			var row = document.id('list_' + this.id + '_row_' + rowids[i]);
			var highlight = new Fx.Morph(row, {
				duration: 1000
			});
			highlight.start({
				'backgroundColor': this.options.hightLight
			}).chain(function () {
				this.start({
					'opacity': 0
				});
			}).chain(function () {
				row.dispose();
				this.checkEmpty();
			}.bind(this));
		}
	},

	editRow: function () {
	},

	clearRows: function () {
		this.list.getElements('.fabrik_row').each(function (tr) {
			tr.dispose();
		});
	},

	updateRows: function () {
		var data = {
				'option': 'com_fabrik',
				'view': 'list',
				'task': 'list.view',
				'format': 'raw',
				'listid': this.id
			};
		//var url = Fabrik.liveSite + 'index.php?option=com_fabrik&view=list&format=raw&listid=' + this.id;
		var url = '';
		data['limit' + this.id] = this.options.limitLength;
		new Request.JSON({
			'url': url,
			'data': data,
			onSuccess: function (json) {
				this._updateRows(json);
				// Fabrik.fireEvent('fabrik.list.update', [this, json]);
			}.bind(this),
			onError: function (text, error) {
				console.log(text, error);
			},
			onFailure: function (xhr) {
				console.log(xhr);
			}
		}).send();
	},

	_updateRows: function (data) {
		if (data.id === this.id && data.model === 'list') {
			var header = document.id(this.options.form).getElements('.fabrik___heading').getLast();
			var headings = new Hash(data.headings);
			headings.each(function (data, key) {
				key = "." + key;
				try {
					if (typeOf(header.getElement(key)) !== 'null') {
						// $$$ rob 28/10/2011 just alter span to allow for maintaining filter toggle links
						header.getElement(key).getElement('span').set('html', data);
					}
				} catch (err) {
					fconsole(err);
				}
			});
			this.setRowTemplate();
			this.clearRows();
			var counter = 0;
			var rowcounter = 0;
			trs = [];
			this.options.data = this.options.isGrouped ? $H(data.data) : data.data;
			if (data.calculations) {
				this.updateCals(data.calculations);
			}
			if (typeOf(this.form.getElement('.fabrikNav')) !== 'null') {
				this.form.getElement('.fabrikNav').set('html', data.htmlnav);
			}
			// $$$ rob was $H(data.data) but that wasnt working ????
			// testing with $H back in again for grouped by data? Yeah works for
			// grouped data!!
			var gdata = this.options.isGrouped ? $H(data.data) : data.data;
			var gcounter = 0;
			gdata.each(function (groupData, groupKey) {
				var container, thisrowtemplate;
				var tbody = this.options.isGrouped ? this.list.getElements('.fabrik_groupdata')[gcounter] : this.tbody;
				
				// Set the group by heading
				if (this.options.isGrouped && tbody) {
					groupHeading = tbody.getPrevious();
					groupHeading.getElement('.groupTitle').set('html', groupData[0].groupHeading);
				}
				if (typeOf(tbody) !== 'null') {
					gcounter++;
					for (i = 0; i < groupData.length; i++) {
	
						if (typeOf(this.options.rowtemplate) === 'string') {
							container = (this.options.rowtemplate.trim().slice(0, 3) === '<tr') ? 'table' : 'div';
							thisrowtemplate = new Element(container);
							thisrowtemplate.set('html', this.options.rowtemplate);
						} else {
							container = this.options.rowtemplate.get('tag') === 'tr' ? 'table' : 'div';
							thisrowtemplate = new Element(container);
							// ie tmp fix for mt 1.2 setHTML on table issue
							thisrowtemplate.adopt(this.options.rowtemplate.clone());
						}
						var row = $H(groupData[i]);
						$H(row.data).each(function (val, key) {
							var rowk = '.' + key;
							var cell = thisrowtemplate.getElement(rowk);
							if (typeOf(cell) !== 'null' && cell.get('tag') !== 'a') {
								cell.set('html', val);
							}
							rowcounter ++;
						}.bind(this));
						// thisrowtemplate.getElement('.fabrik_row').id = 'list_' + this.id +
						// '_row_' + row.get('__pk_val');
						thisrowtemplate.getElement('.fabrik_row').id = row.id;
						if (typeOf(this.options.rowtemplate) === 'string') {
							var c = thisrowtemplate.getElement('.fabrik_row').clone();
							c.id = row.id;
							var newClass = row['class'].split(' ');
							for (j = 0; j < newClass.length; j ++) {
								c.addClass(newClass[j]);
							}
							c.inject(tbody);
						} else {
							var r = thisrowtemplate.getElement('.fabrik_row');
							r.inject(tbody);
							thisrowtemplate.empty();
						}
						counter++;
					}
				}
			}.bind(this));

			// Grouped data - show all tbodys, then hide empty tbodys (not going to work for none <table> tpls)
			var tbodys = this.list.getElements('tbody');
			tbodys.setStyle('display', '');
			tbodys.each(function (tbody) {
				if (!tbody.hasClass('fabrik_groupdata')) {
					var groupTbody = tbody.getNext();
					if (groupTbody.getElements('.fabrik_row').length === 0) {
						tbody.hide();
						groupTbody.hide();
					}
				}
			});
			
			var fabrikDataContainer = this.list.getParent('.fabrikDataContainer');
			var emptyDataMessage = this.list.getParent('.fabrikForm').getElement('.emptyDataMessage');
			if (rowcounter === 0) {
				/*
				 * if (typeOf(fabrikDataContainer) !== 'null') {
				 * fabrikDataContainer.setStyle('display', 'none'); }
				 */
				if (typeOf(emptyDataMessage) !== 'null') {
					emptyDataMessage.setStyle('display', '');
				}
			} else {
				if (typeOf(fabrikDataContainer) !== 'null') {
					fabrikDataContainer.setStyle('display', '');
				}
				if (typeOf(emptyDataMessage) !== 'null') {
					emptyDataMessage.setStyle('display', 'none');
				}
			}
			if (typeOf(this.form.getElement('.fabrikNav')) !== 'null') {
				this.form.getElement('.fabrikNav').set('html', data.htmlnav);
			}
			this.watchAll(true);
			Fabrik.fireEvent('fabrik.list.updaterows');
			Fabrik.fireEvent('fabrik.list.update', [this, data]);
		}
		this.stripe();
		Fabrik.loader.stop('listform_' + this.options.listRef);
	},

	addRow: function (obj) {
		var r = new Element('tr', {
			'class': 'oddRow1'
		});
		var x = {
			test: 'hi'
		};
		for (var i in obj) {
			if (this.options.headings.indexOf(i) !== -1) {
				var td = new Element('td', {}).appendText(obj[i]);
				r.appendChild(td);
			}
		}
		r.inject(this.tbody);
	},

	addRows: function (aData) {
		for (i = 0; i < aData.length; i++) {
			for (j = 0; j < aData[i].length; j++) {
				this.addRow(aData[i][j]);
			}
		}
		this.stripe();
	},

	stripe: function () {
		var trs = this.list.getElements('.fabrik_row');
		for (i = 0; i < trs.length; i++) {
			if (!trs[i].hasClass('fabrik___header')) { // ignore heading
				var row = 'oddRow' + (i % 2);
				trs[i].addClass(row);
			}
		}
	},

	checkEmpty: function () {
		var trs = this.list.getElements('tr');
		if (trs.length === 2) {
			this.addRow({
				'label': Joomla.JText._('COM_FABRIK_NO_RECORDS')
			});
		}
	},

	watchCheckAll: function (e) {
		var checkAll = this.form.getElement('input[name=checkAll]');
		if (typeOf(checkAll) !== 'null') {
			// IE wont fire an event on change until the checkbxo is blurred!
			checkAll.addEvent('click', function (e) {
				var p = this.list.getParent('.fabrikList') ? this.list.getParent('.fabrikList') : this.list;
				var chkBoxes = p.getElements('input[name^=ids]');
				c = !e.target.checked ? '' : 'checked';
				for (var i = 0; i < chkBoxes.length; i++) {
					chkBoxes[i].checked = c;
					this.toggleJoinKeysChx(chkBoxes[i]);
				}
				// event.stop(); dont event stop as this stops the checkbox being
				// selected
			}.bind(this));
		}
		this.form.getElements('input[name^=ids]').each(function (i) {
			i.addEvent('change', function (e) {
				this.toggleJoinKeysChx(i);
			}.bind(this));
		}.bind(this));
	},

	toggleJoinKeysChx: function (i) {
		i.getParent().getElements('input[class=fabrik_joinedkey]').each(function (c) {
			c.checked = i.checked;
		});
	},

	watchNav: function (e) {
		var limitBox = this.form.getElement('select[name*=limit]');
		if (limitBox) {
			limitBox.addEvent('change', function (e) {
				var res = Fabrik.fireEvent('fabrik.list.limit', [this]);
				if (this.result === false) {
					this.result = true;
					return false;
				}
				this.doFilter();
			}.bind(this));
		}
		var addRecord = this.form.getElement('.addRecord');
		if (typeOf(addRecord) !== 'null' && (this.options.ajax_links)) {
			addRecord.removeEvents();
			var loadMethod = (this.options.links.add === '' || addRecord.href.contains(Fabrik.liveSite)) ? 'xhr' : 'iframe';
			var url = addRecord.href;
			url += url.contains('?') ? '&' : '?';
			url += 'tmpl=component&ajax=1';
			addRecord.addEvent('click', function (e) {
				e.stop();
				// top.Fabrik.fireEvent('fabrik.list.add', this);//for packages?
				var winOpts = {
					'id': 'add.' + this.id,
					'title': this.options.popup_add_label,
					'loadMethod': loadMethod,
					'contentURL': url,
					'width': this.options.popup_width,
					'height': this.options.popup_height
				};
				if (typeOf(this.options.popup_offset_x) !== 'null') {
					winOpts.offset_x = this.options.popup_offset_x;
				}
				if (typeOf(this.options.popup_offset_y) !== 'null') {
					winOpts.offset_y = this.options.popup_offset_y;
				}
				Fabrik.getWindow(winOpts);
			}.bind(this));
		}
		if (document.id('fabrik__swaptable')) {
			document.id('fabrik__swaptable').addEvent('change', function (e) {
				window.location = 'index.php?option=com_fabrik&task=list.view&cid=' + e.target.get('value');
			}.bind(this));
		}
		// All nav links should submit the form, if we dont then filters are not taken into account when building the list cache id
		// Can result in 2nd pages of cached data being shown, but without filters applied
		if (typeOf(this.form.getElement('.pagination')) !== 'null') {
			var as = this.form.getElement('.pagination').getElements('.pagenav');
			if (as.length === 0) {
				as = this.form.getElement('.pagination').getElements('a');
			}
			as.each(function (a) {
				a.addEvent('click', function (e) {
					e.stop();
					if (a.get('tag') === 'a') {
						var o = a.href.toObject();
						this.fabrikNav(o['limitstart' + this.id]);
					}
				}.bind(this));
			}.bind(this));
		}
		
		if (this.options.admin) {
			Fabrik.addEvent('fabrik.block.added', function (block) {
				if (block.options.listRef === this.options.listRef) {
					var nav = block.form.getElement('.fabrikNav');
					if (typeOf(nav) !== 'null') {
						nav.getElements('a').addEvent('click', function (e) {
							e.stop();
							block.fabrikNav(e.target.get('href'));
						});
					}
				}
			}.bind(this));
		}
		this.watchCheckAll();
	},
	
	/**
	 * currently only called from element raw view when using inline edit plugin
	 * might need to use for ajax nav as well?
	 */

	updateCals: function (json) {
		var types = ['sums', 'avgs', 'count', 'medians'];
		this.form.getElements('.fabrik_calculations').each(function (c) {
			types.each(function (type) {
				$H(json[type]).each(function (val, key) {
					var target = c.getElement('.fabrik_row___' + key);
					if (typeOf(target) !== 'null') {
						target.set('html', val);
					}
				});
			});
		});
	}
});

/**
 * observe keyboard short cuts
 */

var FbListKeys = new Class({
	initialize: function (list) {
		window.addEvent('keyup', function (e) {
			if (e.alt) {
				switch (e.key) {
				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_ADD'):
					var a = list.form.getElement('.addRecord');
					if (list.options.ajax) {
						a.fireEvent('click');
					}
					if (a.getElement('a')) {
						list.options.ajax ? a.getElement('a').fireEvent('click') : document.location = a.getElement('a').get('href');
					} else {
						if (!list.options.ajax) {
							document.location = a.get('href');
						}
					}
					break;

				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_EDIT'):
					fconsole('edit');
					break;
				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_DELETE'):
					fconsole('delete');
					break;
				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_FILTER'):
					fconsole('filter');
					break;
				}
			}
		}.bind(this));
	}
});

/**
 * toggle grouped data by click on the grouped headings icon
 */

var FbGroupedToggler = new Class({
	Implements: Options,
	
	options: {
		collapseOthers: false,
		startCollapsed: false
	},
	
	initialize: function (container, options) {
		this.setOptions(options);
		this.container = container;
		this.toggleState = 'shown';
		if (this.options.startCollapsed && this.options.isGrouped) {
			this.collapse();
		}
		container.addEvent('click:relay(.fabrik_groupheading a.toggle)', function (e) {
			if (e.rightClick) {
				return;
			}
			e.stop();
			e.preventDefault(); //should work according to http://mootools.net/blog/2011/09/10/mootools-1-4-0/
			
			if (this.options.collapseOthers) {
				this.collapse();
			}
			var h = e.target.getParent('.fabrik_groupheading');
			var img = h.getElement('img');
			var state = img.retrieve('showgroup', true);
			var rows = h.getParent().getNext();
			state ? rows.hide() : rows.show();
			this.setIcon(img, state);
			state = state ? false : true;
			img.store('showgroup', state);
			return false;
		}.bind(this));
	},
	
	setIcon: function (img, state) {
		if (state) {
			img.src = img.src.replace('orderasc', 'orderneutral');
		} else {
			img.src = img.src.replace('orderneutral', 'orderasc');
		}
	},
	
	collapse: function () {
		this.container.getElements('.fabrik_groupdata').hide();
		var i = this.container.getElements('.fabrik_groupheading a img');
		if (i.length === 0) {
			i = this.container.getElements('.fabrik_groupheading img');
		}
		i.each(function (img) {
			img.store('showgroup', false);
			this.setIcon(img, true);
		}.bind(this));
	},
	
	expand: function () {
		this.container.getElements('.fabrik_groupdata').show();
		var i = this.container.getElements('.fabrik_groupheading a img');
		if (i.length === 0) {
			i = this.container.getElements('.fabrik_groupheading img');
		}
		i.each(function (img) {
			img.store('showgroup', true);
			this.setIcon(img, false);
		}.bind(this));
	},
	
	toggle: function () {
		this.toggleState === 'shown' ? this.collapse() : this.expand();
		this.toggleState = this.toggleState === 'shown' ? 'hidden' : 'shown';
	}
});

/**
 * set up and show/hide list actions for each row
 */
var FbListActions = new Class({

	Implements: [Options],
	options: {
		'method': 'floating',
		'floatPos': 'bottom'
	},

	initialize: function (list, options) {
		this.setOptions(options);
		this.list = list; // main list js object
		this.actions = [];
		this.setUpSubMenus();
		Fabrik.addEvent('fabrik.list.update', function (list, json) {
			this.observe();
		}.bind(this));
		this.observe();
	},

	observe: function () {
		if (this.options.method === 'floating') {
			this.setUpFloating();
		} else {
			this.setUpDefault();
		}
	},

	setUpSubMenus: function () {
		if (!this.list.form) {
			return;
		}
		this.actions = this.list.form.getElements('ul.fabrik_action');
		this.actions.each(function (ul) {
			// sub menus ie group by options
			if (ul.getElement('ul')) {
				var el = ul.getElement('ul');
				var c = new Element('div').adopt(el.clone());
				var trigger = el.getPrevious();
				if (trigger.getElement('.fabrikTip')) {
					trigger = trigger.getElement('.fabrikTip');
				}
				var tipOpts = Object.merge(Object.clone(Fabrik.tips.options), {
					showOn: 'click',
					hideOn: 'click',
					position: 'bottom',
					content: c
				});
				var tip = new FloatingTips(trigger, tipOpts);
				el.dispose();
			}
		});
	},

	setUpDefault: function () {
		this.actions = this.list.form.getElements('ul.fabrik_action');
		this.actions.each(function (ul) {
			if (ul.getParent().hasClass('fabrik_buttons')) {
				return;
			}
			ul.fade(0.6);
			var r = ul.getParent('.fabrik_row') ? ul.getParent('.fabrik_row') : ul.getParent('.fabrik___heading');
			if (r) {
				// $$$ hugh - for some strange reason, if we use 1 the object disappears
				// in Chrome and Safari!
				r.addEvents({
					'mouseenter': function (e) {
						ul.fade(0.99);
					},
					'mouseleave': function (e) {
						ul.fade(0.6);
					}
				});
			}
		});
	},

	setUpFloating: function () {
		this.list.form.getElements('ul.fabrik_action').each(function (ul) {
			if (ul.getParent('.fabrik_row')) {
				if (i = ul.getParent('.fabrik_row').getElement('input[type=checkbox]')) {
					var hideFn = function (e, elem, leaving) {
						if (!e.target.checked) {
							this.hide(e, elem);
						}
						if (leaving === 'tip') {
							// elem.checked = false; (cant do this otherwise delete
							// confirmation wont delete anything
						}
					};

					var c = function (el, o) {
						var r = ul.getParent();
						r.store('activeRow', ul.getParent('.fabrik_row'));
						return r;
					}.bind(this.list);

					var opts =  {
							position: this.options.floatPos,
							showOn: 'click',
							hideOn: 'click',
							content: c,
							hideFn: function (e) {
								return !e.target.checked;
							},
							showFn: function (e, trigger) {
								Fabrik.activeRow = ul.getParent().retrieve('activeRow');
								trigger.store('list', this.list);
								return e.target.checked;
							}.bind(this.list)
						};
					
					var tipOpts = Object.merge(Object.clone(Fabrik.tips.options), opts);
					var tip = new FloatingTips(i, tipOpts);
				}
			}
		}.bind(this));

		// watch the top/master chxbox
		var chxall = this.list.form.getElement('input[name=checkAll]');
		var c = function (el) {
			return el.getParent('.fabrik___heading').getElement('ul.fabrik_action');
		};

		var tipChxAllOpts = Object.merge(Object.clone(Fabrik.tips.options), {
			position: this.options.floatPos,
			html: true,
			showOn: 'click',
			hideOn: 'click',
			content: c,
			hideFn: function (e) {
				return !e.target.checked;
			},
			showFn: function (e, trigger) {
				trigger.retrieve('tip').click.store('list', this.list);
				return e.target.checked;
			}.bind(this.list)
		});
		var tip = new FloatingTips(chxall, tipChxAllOpts);

		// hide markup that contained the actions
		if (this.list.form.getElements('.fabrik_actions')) {
			this.list.form.getElements('.fabrik_actions').hide();
		}
		if (this.list.form.getElements('.fabrik_calculation')) {
			var calc = this.list.form.getElements('.fabrik_calculation').getLast();
			if (typeOf(calc) !== 'null') {
				calc.hide();
			}
		}
	}
});