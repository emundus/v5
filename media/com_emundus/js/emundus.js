
if (window.addEventListener) {
	window.addEventListener("load", prepare, false);
} else if (window.attachEvent) {
	window.attachEvent("onload", prepare)
} else if (document.getElementById) {
	window.onload = prepare;
}

function prepare() {
	formblock= document.getElementById('adminForm');
	forminputs = formblock.getElementsByTagName('input');
}

function check_all(name, value) {
	for (i = 0; i < forminputs.length; i++) {
		// regex here to check name attribute
		var regex = new RegExp(name, "i");
		if (regex.test(forminputs[i].getAttribute('name'))) {
			if (value.checked == true) forminputs[i].checked = true;
			else forminputs[i].checked = false;
		}
	}
}

function removeElement(divNum, opt) {
	if (opt == 1)
  		var d = document.getElementById('myDiv');
	else
		var d = document.getElementById('otherDiv');
  var olddiv = document.getElementById(divNum);
  d.removeChild(olddiv);
}

function tableOrdering( order, dir, task ) {
  var form = document.adminForm;
  form.filter_order.value = order;
  form.filter_order_Dir.value = dir;
  document.adminForm.submit( task );
}

function validation(uid, validate, cible){
	var getPlayerResult = $(cible);
	var getPlayer = new Request(
	{
      url:   'index.php?option=com_emundus&format=raw&task=ajax_validation',
      method: 'get',
      onRequest: function() {
            getPlayerResult.set('html', '<img src="media/com_emundus/images/icones/loading.gif">');
         },
      onSuccess: function(responseText) {
            getPlayerResult.set('html', responseText);
			$(cible).removeEvents('click');
         },
      onFailure: function() {
            getPlayerResult.set('text', 'ERROR');
			$(cible).removeEvents('click');
         }
	});
	$(cible).addEvent('click', function(getPlayerEvent) { 
    	getPlayerEvent.stop();
		getPlayer.send('&uid=' + uid + '&validate=' + validate + '&cible=' + cible);
	});
	
	
} 