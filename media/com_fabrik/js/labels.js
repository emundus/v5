/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $H:true,unescape:true,head:true */

var Labels = new Class({
	
	Implements: [Events],
	
	initialize: function () {
		$$('.fabrikElementContainer').each(function (c) {
			var label = c.getElement('label');
			if (typeOf(label) !== 'null') {
				var input = c.getElement('input');
				if (typeOf(input) === 'null') {
					input = c.getElement('textarea');
				}
				if (typeOf(input) !== 'null') {
					input.value = label.innerHTML;
					
					input.addEvent('click', function (e) {
						this.toogleLabel(e, input, label.innerHTML);
					}.bind(this));
					
					input.addEvent('blur', function (e) {
						this.toogleLabel(e, input, label.innerHTML);
					}.bind(this));
					label.set('html', '');
					c.getElement('.fabrikLabel').dispose();
				}
			}
		}.bind(this));
	},
	
	toogleLabel: function (e, input, label) {
		new Event(e).stop();
		if (e.type === 'click') {
			if (input.get('value') === label) {
				input.value = '';
			}
		} else {
			if (input.get('value') === '') {
				input.value = label;
			}
		}
	}
       
});

head.ready(function () {
	new Labels();
});