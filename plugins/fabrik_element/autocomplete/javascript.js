var fbAC = FbElement.extend({
	initialize: function(element, options) {
		this.parent(element, options);
		this.plugin = 'fabrikautocomplete';
		this.setOptions(element, options);
		//this.element.addEvent('change', this.showLoader());
		/* if(this.element.addEventListener ) {
			this.element.addEventListener("keydown",this.prout,false);
		} else if(this.element.attachEvent ) {
			this.element.attachEvent("onkeydown",this.prout); // damn IE hack	
		} */
		
		;
		this.element.onkeydown = function(e) {console.log(this.parent)
			this.getParent().getElement('.loader').setStyle('display', '');
			if (options['callbackid'] != "null" && options['infoid'] != "null") {
				var TABKEY = 9;
				if(navigator.appName === "Microsoft Internet Explorer") { 
					if( e.keyCode != TABKEY) {
						document.getElementById(options['callbackid']).value="";
						document.getElementById(options['infoid']).value="";
					}
				} else {
					if( e.which != TABKEY) {
						document.getElementById(options['callbackid']).value="";
						document.getElementById(options['infoid']).value="";
					}
				}
			}
		}
		
		this.element.onblur = function(e) {
			this.getParent().getElement('.loader').setStyle('display', 'none');
		}

		this.ac_options = {
				varname:"input",
				script: function(obj){
				//var svn = " "+ this.options.search_value_name;
				//if ( svn != " " ) {
				if (options['search_value_name'] != 'null') {
					search_value = document.getElementById(options['search_value_name']).value;
					url = "index.php?option=com_fabrik&format=raw&controller=plugin&task=pluginAjax&plugin=fabrikautocomplete&method=json_get&sql="+options['sql']+"&search_field="+options['search_field']+"&info_field="+options['info_field']+"&search_value_name="+options['search_value_name']+"&search_value="+search_value+"&input="+obj; 
				} else {
					url = "index.php?option=com_fabrik&format=raw&controller=plugin&task=pluginAjax&plugin=fabrikautocomplete&method=json_get&sql="+options['sql']+"&search_field="+options['search_field']+"&info_field="+options['info_field']+"&search_value_name=&search_value=&input="+obj;
				}
				//parent.showLoader();
				return url;
				},
				json:true,
				timeout:10000,
				shownoresults:true,
				maxresults:6,
				callback: function (obj) { 
					if (options['callbackid'] != 'null') 
						document.getElementById(options['callbackid']).value = obj.id;
					
					var reg=new RegExp("[,]+", "g");
					val = obj.value;
					var cname=val.split(reg);
					document.getElementById(options['id']).value = cname[0];
					
					var reg=new RegExp("[___]+", "g");
					vid = options['id'];
					var elt_name=vid.split(reg);
					
					if(elt_name[3] == "Last") {
						firstname_id = "jos_emundus_references___First_Name_"+elt_name[5];
						document.getElementById(firstname_id).value = cname[1];
					}

					if ( options['infoid'] != 'null' ) 
						document.getElementById(options['infoid']).value = obj.info;
					
					//element.getParent().getElement('.loader').setStyle('display', 'none');
					//this.hideLoader();
				}
		};
			//alert(this.options.search_value_name);
		var as_json = new bsn.AutoSuggest(this.options.id, this.ac_options);
	},
	

/*	keyHandler: function(e) {

		if (this.options['callbackid'] != "" && this.options['infoid'] != "") {
			var TABKEY = 9;
				if(e.keyCode != TABKEY) {
					document.getElementById(options['callbackid']).value="";
					document.getElementById(options['infoid']).value="";
				}
		} 
	},*/
	
	showLoader: function() {
		this.element.getParent().getElement('.loader').setStyle('display', '');
	},
	hideLoader: function() {
		this.element.getParent().getElement('.loader').setStyle('display', 'none');
	}
});