var fbEmundusreferent = new Class({
	initialize: function(element, options) {
		this.options = options;
		this.plugin = 'emundusreferent';
		this.element = $(element);
		var btn = element+'_btn';
		var response = element+'_response';
		var error = element+'_error';
		var loader = element+'_loader';
		/*this.setOptions(element, this.options);*/
		if ($(btn) != null) {
			window.addEvent('domready',function(){
				$(btn).addEvent( 'click', function() { 
				$(btn).disabled = true;
				$(btn).value = this.options['sending'];
				$(loader).setStyle('display', '');
				email = document.getElementById(this.options['email']).value;
				id = this.options['id'];
				url = "index.php?option=com_fabrik&format=raw&controller=plugin&task=pluginAjax&plugin=emundusreferent&method=email&email="+email+"&id="+id;
				
				var SYload = new Ajax(url, {
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
						$(this.options['email']).setStyle('border', '1px solid #B0BB1E');
					} else {
						err.innerHTML = tab[1]; 
						$(btn).disabled = false;
						$(btn).value = this.options['sendmailagain']; 
						$(this.options['email']).setStyle('border', '4px solid #ff0000');
					}
					 $(loader).setStyle('display', 'none');
					}
				});
				SYload.request();
				});
			});
		}
	},
	
	select:function(){
		this.element.select();
	},
	
	focus:function(){
		this.element.focus();
	}
});
