var FbEmundusreferent = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikEmundusreferent';
		this.parent(element, options);
		this.observer = document.id(element);
		this.options = options;
		this.btn = element+'_btn';
		this.response = element+'_response';
		this.error = element+'_error';
		this.loader = element+'_loader';
		this.setOptions(element, this.options);

		if (this.observer && $(this.btn) != null) {
			var v = this.observer.get('value');
			var email = document.getElementById(options['email']).value;
			
			var attachment_id = this.options.attachment_id;					
			this.myAjax = new Request({url: '', method: 'get',
					'data': {
						'option': 'com_fabrik',
						'format': 'raw',
						'task': 'plugin.pluginAjax',
						'plugin': 'emundusreferent',
						'method': 'onAjax_getOptions',
						'attachment_id': attachment_id,
						'email': email, 
						'v' : v,
						'formid': this.options.formid
					},
					onComplete: this.ajaxComplete.bindWithEvent(this)
			});
			this.observer.addEvent('click', function () {
				$(this.btn).disabled = true;
				$(this.btn).value = options['sending'];
				$(this.loader).setStyle('display', '');
							
				this.myAjax.send();
			}.bind(this));
			
			v = this.observer.get('value');
		} else {
			fconsole('observer not found ', element);
		}
	},
	
	update: function () {
		if (this.observer) {
			this.myAjax.options.data.v = this.observer.get('value');
			$filterData = eval(this.options.filterobj).getFilterData();
			Object.append(this.myAjax.options.data, $filterData);
			this.myAjax.send();
		}
	},
	
	ajaxComplete: function (json) {alert(json)
		json = JSON.decode(json);
		if (json.result == "1") { 
			$(this.observer).value = parseInt($(this.observer).value) + 1;
			$(this.response).innerHTML = json.message; 
			$(this.error).innerHTML = "";
			$(this.options['email']).setStyle('border', '1px solid #B0BB1E');
		} else {
			$(this.error).innerHTML = json.message; 
			$(this.btn).disabled = false;
			$(this.btn).value = this.options['sendmailagain'];
			$(this.options['email']).setStyle('border', '4px solid #ff0000');
		}
		this.endAjax();
	},
	
	endAjax: function ()
	{
		$(this.loader).setStyle('display', 'none');
	}
});