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
			//new Element('img', {'id': this.loader, 'src': Fabrik.liveSite + 'media/com_fabrik/images/ajax-loader.gif', 'alt': 'loading...', 'styles': {'opacity': '0'}}).inject(this.observer, 'before');

			var v = this.observer.get('value');
			var email = document.getElementById(options['email']).value;
			var attachment_id = this.options.attachment_id;
			//var url = "index.php?option=com_fabrik&format=raw&controller=plugin&task=pluginAjax&plugin=emundusreferent&method=email&email="+email+"&id="+attachment_id;
					
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
			//window.addEvent('domready',function () {
			//document.getElementById(this.btn).onclick=function(){alert("button2 clicked");};
			//$(this.btn).addEventListener( 'click', function() { 
				$(this.btn).disabled = true;
				$(this.btn).value = options['sending'];
				$(this.loader).setStyle('display', '');
							
				this.myAjax.send();
					
				/*var SYload = new Ajax(url, {
				  method: 'get',
				  onSuccess: function(resp) {
					 var reg=new RegExp("[|]+", "g");
					 var tab=resp.split(reg);
					 var res = $(response); 
					 var err = $(error); 
					if (tab[0] == 1) {
						$(element).value = parseInt($(element).value) + 1;
						res.innerHTML = tab[1]; 
						err.innerHTML = "";
						$(options['email']).setStyle('border', '1px solid #B0BB1E');
					} else {
						err.innerHTML = tab[1]; 
						$(btn).disabled = false;
						$(btn).value = options['sendmailagain']; 
						$(options['email']).setStyle('border', '4px solid #ff0000');
					}
					 $(loader).setStyle('display', 'none');
					}
				});
				SYload.request();
				});*/
			//});
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
	
	ajaxComplete: function (json) {alert(json);
		json = JSON.decode(json);
		alert(count(json));
		/*this.periodcount ++;
		if (this.periodcount > 5) {
			this.endAjax();
			return;
		}
		if (typeOf(document.id(this.options.filterid)) === 'null') {
			fconsole('filterid not found: ', this.options.filterid);
			this.endAjax();
			return;				
		}*/

		//document.id(this.options.filterid).empty();
		/*json.each(function (item) {
			new Element('option', {'value': item.value}).appendText(item.text).inject(document.id(this.options.filterid));
			document.id(this.options.filterid).value = this.options.def;
		}.bind(this));*/
		if (json.result == "1") { 
			$(this.observer).value = parseInt($(this.observer).value) + 1;
			$(this.response).innerHTML = json.message; 
			$(this.error).innerHTML = "";
			$(this.options['email']).setStyle('border', '1px solid #B0BB1E');
		} else {
			$(this.error).innerHTML = json.message; 
			$(this.btn).disabled = false;
			$(this.btn).value = $(this.options['sendmailagain']); 
			$(this.options['email']).setStyle('border', '4px solid #ff0000');
		}
		this.endAjax();
	},
	
	endAjax: function ()
	{
		$(this.loader).setStyle('display', 'none');
		//document.id(this.loader).setStyle('opacity', '0');
		//clearInterval(this.periodical);
	}
});