
var ListPluginManager = new Class({
	
	Extends: PluginManager,

	type: 'list',
	
	initialize: function (plugins, id) {
		this.parent(plugins, id);
	}
	
});

var ListForm = new Class({
	
	Implements: [Options],
	
	options: {},
	
	initialize: function (options) {
		var rows;
		this.setOptions(options);
		if (document.id('addAJoin')) {
			document.id('addAJoin').addEvent('click', function (e) {
				e.stop();
				this.addJoin();
			}.bind(this));
		}
		if (document.getElement('table.linkedLists')) {
			rows = document.getElement('table.linkedLists').getElement('tbody');
			new Sortables(rows, {'handle': '.handle'});
		}

		if (document.getElement('table.linkedForms')) {
			rows = document.getElement('table.linkedForms').getElement('tbody');
			new Sortables(rows, {'handle': '.handle'});
		}
		
		this.joinCounter = 0;
		this.watchOrderButtons();
		this.watchDbName();
		this.watchJoins();
	},
	
	watchOrderButtons: function () {
		document.getElements('.addOrder').removeEvents('click');
		document.getElements('.deleteOrder').removeEvents('click');
		document.getElements('.addOrder').addEvent('click', function (e) {
			e.stop();
			this.addOrderBy();
		}.bind(this));
		document.getElements('.deleteOrder').addEvent('click', function (e) {
			e.stop();
			this.deleteOrderBy(e);
		}.bind(this));
	},
	
	addOrderBy: function (e)
	{
		var t;
		if (e) {
			t = e.target.getParent('.orderby_container');
		} else {
			t = document.getElement('.orderby_container');
		}
		t.clone().inject(t, 'after');
		this.watchOrderButtons();
	},
	
	deleteOrderBy: function (e) {
		if (document.getElements('.orderby_container').length > 1) {
			e.target.getParent('.orderby_container').dispose();
			this.watchOrderButtons();
		}
	},
	
	watchDbName: function () {
		if (document.id('database_name')) {
			document.id('database_name').addEvent('blur', function (e) {
				if (document.id('database_name').get('value') === '') {
					document.id('tablename').disabled = false;
				} else {
					document.id('tablename').disabled = true;
				}
			});
		}
	},
	
	_buildOptions: function (data, sel) {
		var opts = [];
		if (data.length > 0) {
			if (typeof(data[0]) === 'object') {
				data.each(function (o) {
					if (o[0] === sel) {
						opts.push(new Element('option', {'value': o[0], 'selected': 'selected'}).set('text', o[1]));
					} else {
						opts.push(new Element('option', {'value': o[0]}).set('text', o[1]));
					}
				});
			} else {
				data.each(function (o) {
					if (o === sel) {
						opts.push(new Element('option', {'value': o, 'selected': 'selected'}).set('text', o));
					} else {
						opts.push(new Element('option', {'value': o}).set('text', o));
					}
				});
			}
		}
		return opts;	
	},
	
	addAJoin: function (e) {
		this.addJoin();
		e.stop();
	},
	
	watchFieldList: function (name) {
		document.getElement('div[id^=table-sliders-data]').addEvent('change:relay(select[name*=' + name + '])', function (e, target) {
			this.updateJoinStatement(target.getParent('table').id.replace('join', ''));
		}.bind(this));
	},
	
	_findActiveTables: function () {
		var t = document.getElements('.join_from').combine(document.getElements('.join_to'));
		t.each(function (sel) {
			var v = sel.get('value');
			if (this.options.activetableOpts.indexOf(v) === -1) {
				this.options.activetableOpts.push(v);
			}
		}.bind(this));
		this.options.activetableOpts.sort();
	},
	
	addJoin: function (groupId, joinId, joinType, joinToTable, thisKey, joinKey, joinFromTable, joinFromFields, joinToFields, repeat) {
		var repeaton, repeatoff, headings, row;
		joinType = joinType ? joinType : 'left';
		joinFromTable = joinFromTable ? joinFromTable : '';
		joinToTable = joinToTable ? joinToTable : '';
		thisKey = thisKey ? thisKey : '';
		joinKey = joinKey ? joinKey : '';
		groupId = groupId ? groupId : '';
		joinId = joinId ? joinId : '';
		repeat = repeat ? repeat : false;
		if (repeat) {
			repeaton = "checked=\"checked\"";
			repeatoff = "";
		} else {
			repeatoff = "checked=\"checked\"";
			repeaton = "";
		}
		this._findActiveTables();
		joinFromFields = joinFromFields ? joinFromFields : [['-', '']];
		joinToFields = joinToFields ? joinToFields : [['-', '']];
		
		var tbody = new Element('tbody');
		
		var ii = new Element('input', {
			'readonly': 'readonly',
			'size': '2',
			'class': 'disabled readonly input-mini',
			'name': 'jform[params][join_id][]',
			'value': joinId
		});
		
		var sContent = new Element('table', {'class': 'adminform', 'id': 'join' + this.joinCounter}).adopt([
			new Element('thead').adopt([
				new Element('tr', {
					events: {
						'click': function (e) {
							e.stop();
							var tbody = e.target.getParent('.adminform').getElement('tbody');
							Browser.ie ? tbody.toggle() : tbody.slide('toggle'); 
						}
					},
					'styles': {
						'cursor': 'pointer'
					}
				}).adopt(
					new Element('td', {'colspan': '2'}).adopt(new Element('div', {
						'id': 'join-desc-' + this.joinCounter,
						'styles': {
							'margin': '5px',
							'background-color': '#fefefe',
							'padding': '5px',
							'border': '1px dotted #666666'
						}
					}))
				)
				]),
				tbody.adopt([
			
					new Element('tr').adopt([
						new Element('td').set('text', 'id'),
						new Element('td').adopt(ii)
					]),
					new Element('tr').adopt([
						new Element('td').adopt([
							new Element('input', {'type': 'hidden', 'name': 'group_id[]', 'value': groupId})
						]).set('text', Joomla.JText._('COM_FABRIK_JOIN_TYPE')),
					
						new Element('td').adopt(
							new Element('select', {'name': 'jform[params][join_type][]', 'class': 'inputbox'}).adopt(this._buildOptions(this.options.joinOpts, joinType))
						)
					]),
			
					new Element('tr').adopt([
						new Element('td').set('text', Joomla.JText._('COM_FABRIK_FROM')),
						new Element('td').adopt(
							new Element('select', {'name': 'jform[params][join_from_table][]', 'class': 'inputbox join_from'}).adopt(this._buildOptions(this.options.activetableOpts, joinFromTable))
						)
					]),
			
					new Element('tr').adopt([
						new Element('td').set('text', Joomla.JText._('COM_FABRIK_TO')),
						new Element('td').adopt(
							new Element('select', {'name': 'jform[params][table_join][]', 'class': 'inputbox join_to'}).adopt(this._buildOptions(this.options.tableOpts, joinToTable))
						)
					]),
			
					new Element('tr').adopt([
						new Element('td').set('text', Joomla.JText._('COM_FABRIK_FROM_COLUMN')),
						new Element('td', {'id': 'joinThisTableId' + this.joinCounter }).adopt(
							new Element('select', {'name': 'jform[params][table_key][]', 'class': 'table_key inputbox'}).adopt(this._buildOptions(joinFromFields, thisKey))
						)
					]),
			
					new Element('tr').adopt([
						new Element('td').set('text', Joomla.JText._('COM_FABRIK_TO_COLUMN')),
						new Element('td', {'id': 'joinJoinTableId' + this.joinCounter }).adopt(
							new Element('select', {'name': 'jform[params][table_join_key][]', 'class': 'table_join_key inputbox'}).adopt(this._buildOptions(joinToFields, joinKey))
						)
					]),
			
					new Element('tr').set('html', "<td>" + Joomla.JText._('COM_FABRIK_REPEAT_GROUP_BUTTON_LABEL') + "</td><td>" +
					"<fieldset class=\"radio\">" +
					"<input type=\"radio\" id=\"joinrepeat" + this.joinCounter + "\" value=\"1\" name=\"jform[params][join_repeat][" + this.joinCounter + "][]\" " + repeaton + "/><label for=\"joinrepeat" + this.joinCounter + "\">" + Joomla.JText._('JYES') + "</label>" +
					"<input type=\"radio\" id=\"joinrepeatno" + this.joinCounter + "\" value=\"0\" name=\"jform[params][join_repeat][" + this.joinCounter + "][]\" " + repeatoff + "/><label for=\"joinrepeatno" + this.joinCounter + "\">" + Joomla.JText._('JNO') + "</label>" +
					"</fieldset></td>"),
			
					new Element('tr').adopt([
						new Element('td', {'colspan': '2'}).adopt([
							new Element('a', {
								'href': '#',
								'class': 'removeButton',
								'events': {
									'click': function (e) {
										this.deleteJoin(e);
										return false;
									}.bind(this)
								}
							}).set('text', Joomla.JText._('COM_FABRIK_DELETE'))
						])
					])
				])
			]);
		var d = new Element('div', {'id': 'join'}).adopt(sContent);
		d.inject(document.id('joindtd'));  
		if (thisKey !== '') {
			Browser.ie ? tbody.hide() : tbody.slide('hide'); 
		}
		this.updateJoinStatement(this.joinCounter);
		this.joinCounter ++;
	},
			
	deleteJoin: function (e) {
		e.stop();
		var t = document.id(e.target.up(4)); //was 3 but that was the tbody	
		var myfx = new Fx.Tween(t, {property: 'opacity', duration: 500});
		myfx.start(1, 0).chain(function () {
			t.dispose();
		});
	},
	
	watchJoins: function () {
		
		document.getElement('div[id^=table-sliders-data]').addEvent('change:relay(.join_from)', function (e, target) {
			var activeJoinCounter = target.getParent('table').id.replace('join', '');
			this.updateJoinStatement(activeJoinCounter);
			var table = target.get('value');
			var conn = document.getElement('input[name*=connection_id]').get('value');
	
			var url = 'index.php?option=com_fabrik&format=raw&task=list.ajax_loadTableDropDown&table=' + table + '&conn=' + conn;
			var myAjax = new Request.HTML({
				url: url,
				method: 'post', 
				update: document.id('joinThisTableId' + activeJoinCounter)
			}).send();
		}.bind(this));
		
		document.getElement('div[id^=table-sliders-data]').addEvent('change:relay(.join_to)', function (e, target) {
			var activeJoinCounter = target.getParent('table').id.replace('join', '');
			this.updateJoinStatement(activeJoinCounter);
			var table = target.get('value');
			var conn = document.getElement('input[name*=connection_id]').get('value');
			var url = 'index.php?name=jform[params][table_join_key][]&option=com_fabrik&format=raw&task=list.ajax_loadTableDropDown&table=' + table + '&conn=' + conn;
							
			var myAjax = new Request.HTML({
				url: url,
				method: 'post', 
				update: document.id('joinJoinTableId' + activeJoinCounter)
			}).send();
		}.bind(this));
		this.watchFieldList('join_type');
		this.watchFieldList('table_join_key');
		this.watchFieldList('table_key');
	},
	
	updateJoinStatement: function (activeJoinCounter) {
		var fields = $$('#join' + activeJoinCounter + ' .inputbox');
		var type = fields[0].get('value');
		var fromTable = fields[1].get('value');
		var toTable = fields[2].get('value');
		var fromKey = fields[3].get('value');
		var toKey = fields[4].get('value');
		var str = type + " JOIN " + toTable + " ON " + fromTable + "." + fromKey + " = " + toTable + "." + toKey;
		document.id('join-desc-' + activeJoinCounter).set('html', str);				
	}

});

////////////////////////////////////////////

var adminFilters = new Class({
	
	Implements: [Options],
	
	options: {},
	
	initialize: function (el, fields, options) {
		this.el = document.id(el);
		this.fields = fields;
		this.setOptions(options);
		this.filters = [];
		this.counter = 0;
		this.onDeleteClick = this.deleteFilterOption.bindWithEvent(this);
	},
	
	addHeadings: function () {
		var thead = new Element('thead').adopt(new Element('tr', {'id': 'filterTh', 'class': 'title'}).adopt(
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_JOIN')),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_FIELD')),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_CONDITION')),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_VALUE')),
			new Element('th').adopt(
				new Element('span', {'class': 'editlinktip'}).adopt(
					new Element('span', {}).set('text', Joomla.JText._('COM_FABRIK_APPLY_FILTER_TO'))
				)
			),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_DELETE'))			 
		));
		thead.inject(document.id('filterContainer'), 'before');
	},
	
	deleteFilterOption: function (e) {
		e.stop();
		var element = e.target;
		element.removeEvent("click", this.onDeleteClick);
		var tr = element.parentNode.parentNode;
		var table = tr.parentNode;
		table.removeChild(tr);
		this.counter --;
		if (this.counter === 0) {
			document.id('filterTh').dispose();
		}
	},
	
	_makeSel: function (c, name, pairs, sel) {
		var opts = [];
		opts.push(new Element('option', {'value': ''}).set('text', Joomla.JText._('COM_FABRIK_PLEASE_SELECT')));
		pairs.each(function (pair) {
			if (pair.value === sel) {
				opts.push(new Element('option', {'value': pair.value, 'selected': 'selected'}).set('text', pair.label));
			} else {
				opts.push(new Element('option', {'value': pair.value}).set('text', pair.label));
			}
		});
		return new Element('select', {'class': c + ' input-small', 'name': name}).adopt(opts);
	},
	
	addFilterOption: function (selJoin, selFilter, selCondition, selValue, selAccess, evaluate, grouped) {
		var and, or, joinDd, groupedNo, groupedYes, i, sels;
		if (this.counter <= 0) {
			this.addHeadings();
		}
		selJoin = selJoin ? selJoin : '';
		selFilter = selFilter ? selFilter : '';
		selCondition = selCondition ? selCondition : '';
		selValue = selValue ? selValue : '';
		selAccess = selAccess ? selAccess : '';
		grouped = grouped ? grouped: '';
		var conditionsDd = this.options.filterCondDd;					
		var tr = new Element('tr');
		if (this.counter > 0) {
			var opts = {'type': 'radio', 'name': 'jform[params][filter-grouped][' + this.counter + ']', 'value': '1'};
			opts.checked = (grouped === "1") ? "checked" : "";
			groupedYes = new Element('label').set('text', Joomla.JText._('JYES')).adopt(
				new Element('input', opts)
			);
			// Need to redeclare opts for ie8 otherwise it renders a field!
			opts = {
				'type': 'radio',
				'name': 'jform[params][filter-grouped][' + this.counter + ']',
				'value': '0'
			};
			opts.checked = (grouped !== '1') ? 'checked' : '';
			
			groupedNo = new Element('label').set('text', Joomla.JText._('JNO')).adopt(
				new Element('input', opts)
			);
			
		}
		if (this.counter === 0) {
			joinDd = new Element('span').set('text', 'WHERE').adopt(
				new Element('input', {
					'type': 'hidden',
					'id': 'paramsfilter-join',
					'class': 'inputbox',
					'name': 'jform[params][filter-join][]',
					'value': selJoin
				}));
		} else {
			if (selJoin === 'AND') {
				and =  new Element('option', {'value': 'AND', 'selected': 'selected'}).set('text', 'AND');
				or = new Element('option', {'value': 'OR'}).set('text', 'OR');
			} else {
				and = new Element('option', {'value': 'AND'}).set('text', 'AND');
				or = new Element('option', {'value': 'OR', 'selected': 'selected'}).set('text', 'OR');
			}
			joinDd = new Element('select', {
				'id': 'paramsfilter-join',
				'class': 'inputbox  input-small',
				'name': 'jform[params][filter-join][]'
			}).adopt(
		[and, or]);
		}
					
		var td = new Element('td');
		
		if (this.counter <= 0) {
			td.appendChild(new Element('input', {
				'type': 'hidden',
				'name': 'jform[params][filter-grouped][' + this.counter + ']',
				'value': '0'
			}));
		} else {
			
			td.appendChild(new Element('span').set('text', Joomla.JText._('COM_FABRIK_GROUPED')));
			td.appendChild(new Element('br'));
			td.appendChild(groupedNo);
			td.appendChild(groupedYes);
			td.appendChild(new Element('br'));
		}
		td.appendChild(joinDd);
		
		var td1 = new Element('td');
		td1.innerHTML = this.fields;
		var td2 = new Element('td');
		td2.innerHTML = conditionsDd;
		var td3 = new Element('td');
		var td4 = new Element('td');
		td4.innerHTML = this.options.filterAccess;
		var td5 = new Element('td');
		
		var textArea = new Element('textarea', {
			'name': 'jform[params][filter-value][]',
			'cols': 17,
			'rows': 4
		}).set('text', selValue);
		td3.appendChild(textArea);
		td3.appendChild(new Element('br'));
		
		var evalopts = [
			{'value': 0, 'label': Joomla.JText._('COM_FABRIK_TEXT')},
			{'value': 1, 'label': Joomla.JText._('COM_FABRIK_EVAL')},
			{'value': 2, 'label': Joomla.JText._('COM_FABRIK_QUERY')},
			{'value': 3, 'label': Joomla.JText._('COM_FABRIK_NO_QUOTES')}
		];
		td3.adopt(
			new Element('label').adopt([
				new Element('span').set('text', Joomla.JText._('COM_FABRIK_TYPE')),
				this._makeSel('inputbox elementtype', 'jform[params][filter-eval][]', evalopts, evaluate)
			])
		);

		var checked = (selJoin !== '' || selFilter !== '' || selCondition !== '' || selValue !== '') ? true : false;
		var delId = this.el.id + "-del-" + this.counter;
		var a = new Element('a', {href: '#', 'id': delId, 'class': 'removeButton'});
		td5.appendChild(a);
		tr.appendChild(td);
		tr.appendChild(td1);
		tr.appendChild(td2);
		tr.appendChild(td3);
		tr.appendChild(td4);
		tr.appendChild(td5);

		this.el.appendChild(tr);
		
		document.id(delId).addEvent('click', function (e) {
			this.deleteFilterOption(e);
		}.bind(this));
		
		document.id(this.el.id + "-del-" + this.counter).click = function (e) {
			this.deleteFilterOption(e);
		}.bind(this);
		
		/*set default values*/ 
		if (selJoin !== '') {
			sels = Array.from(td.getElementsByTagName('SELECT'));
			if (sels.length >= 1) {
				for (i = 0; i < sels[0].length; i++) {
					if (sels[0][i].value === selJoin) {
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}
		if (selFilter !== '') {
			sels = Array.from(td1.getElementsByTagName('SELECT'));
			if (sels.length >= 1) {
				for (i = 0; i < sels[0].length; i++) {
					if (sels[0][i].value === selFilter) {
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}				

		if (selCondition !== '') {
			sels = Array.from(td2.getElementsByTagName('SELECT'));
			if (sels.length >= 1) {
				for (i = 0; i < sels[0].length; i++) {
					if (sels[0][i].value === selCondition) {
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}	
		
		if (selAccess !== '') {
			sels = Array.from(td4.getElementsByTagName('SELECT'));
			if (sels.length >= 1) {
				for (i = 0; i < sels[0].length; i++) {
					if (sels[0][i].value === selAccess) {
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}					
		this.counter ++;
	}
	
});