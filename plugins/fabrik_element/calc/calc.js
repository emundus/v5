var FbCalc = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'calc';
		this.oldAjaxCalc = null;
		this.parent(element, options);
		if (this.element) {
			this.spinner = new Spinner(this.element.getParent());
		}
	},
	
	attachedToForm : function () {
		if (this.options.ajax) {
			var o2;
			// @TODO - might want to think about firing ajaxCalc here as well, if we've just been added to the form
			// as part of duplicating a group.  Don't want to do it in cloned(), as that would be before elements
			// we observe have finished setting themselves up.  So just need to work out if this is on page load
			// or on group clone.
			var form = this.form;
			this.options.observe.each(function (o) {
				if (this.form.formElements[o]) {
					this.form.formElements[o].addNewEventAux('change', function (e) {
						this.calc(e);
					}.bind(this));
				}
				else {
					// $$$ hugh - check to see if an observed element is actually part of a repeat group,
					// and if so, modify the placeholder name they used to match this instance of it
					// @TODO - add and test code for non-joined repeats!
					if (this.options.canRepeat) {
						if (this.options.isGroupJoin) {
							o2 = 'join___' + this.options.joinid + '___' + o + '_' + this.options.repeatCounter;
							if (this.form.formElements[o2]) {
								this.form.formElements[o2].addNewEventAux('change', function (e) {
									this.calc(e);
								}.bind(this));
							}
						}
						else {
							o2 = o + '_' + this.options.repeatCounter;
							if (this.form.formElements[o2]) {
								this.form.formElements[o2].addNewEventAux('change', function (e) {
									this.calc(e);
								}.bind(this));
							}							
						}
					}
					else {
						this.form.repeatGroupMarkers.each(function (v, k) {
							o2 = '';
							for (v2 = 0; v2 < v; v2++) {
								o2 = 'join___' + this.form.options.group_join_ids[k] + '___' + o + '_' + v2;
								if (this.form.formElements[o2]) {
									// $$$ hugh - think we can add this one as sticky ...
									this.form.formElements[o2].addNewEvent('change', function (e) {
										this.calc(e);
									}.bind(this));
								}
							}
						}.bind(this));
					}
				}
			}.bind(this));
		}
	},
	
	calc: function () {
		this.spinner.show();
		var formdata = this.form.getFormElementData();
		var testdata = $H(this.form.getFormData(false));

		testdata.each(function (v, k) {
			if (k.test(/^join\[\d+\]/) || k.test(/^fabrik_vars/)) {
				formdata[k] = v;
			}
		}.bind(this));
		
		$H(formdata).each(function (v, k) {
			if (k.test(/join___/)) {
				var bits = k.split('_');
				if (bits.getLast().toInt() === this.options.repeatCounter) {
					var elname = k.split('_').slice(6, -1).join('_');
					formdata[elname] = v;
				}
			}
		}.bind(this));
		
		// For placeholders lets set repeat joined groups to their full element name
		
		var data = {
				'option': 'com_fabrik',
				'format': 'raw',
				'task': 'plugin.pluginAjax',
				'plugin': 'calc',
				'method': 'ajax_calc',
				'element_id': this.options.id,
				'formid': this.form.id
			};
		data = Object.append(formdata, data);
		var myAjax = new Request({'url': '', method: 'post', 'data': data,
		onComplete: function (r) {
			this.spinner.hide();
			this.update(r);
			if (this.options.validations) {
				
				// If we have a validation on the element run it after AJAX calc is done
				this.form.doElementValidation(this.options.element);
			}
			// Fire an onChange event so that js actions can be attached and fired when the value updates
			this.element.fireEvent('change', new Event.Mock(this.element, 'change'));
			Fabrik.fireEvent('fabrik.calc.update', [this, r]);
		}.bind(this)}).send();
	},
	
	
	cloned: function (c) {
		this.parent(c);
		this.attachedToForm();
	},
	
	update: function (val) {
		if (this.getElement()) {
			this.element.innerHTML = val;
			this.options.value = val;
		}
	},
	
	getValue: function () {
		if (this.element) {
			return this.options.value;
		}
		return false;
	}
});