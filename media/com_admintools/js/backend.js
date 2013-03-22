/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

/** @var The AJAX proxy URL */
var admintools_ajax_url = "";

/** @var The callback function to call on error */
var admintools_error_callback = dummy_error_handler;

/** @var The password sent to the restoration script */
var admintools_update_password = '';

var	admintools_update_stat_inbytes = 0;
var	admintools_update_stat_outbytes = 0;
var	admintools_update_stat_files = 0;
var admintools_update_factory = null;

/**
 * An extremely simple error handler, dumping error messages to screen
 * @param error The error message string
 */
function dummy_error_handler(error)
{
	alert("ERROR:\n"+error);
}

/**
 * Performs an AJAX request and returns the parsed JSON output.
 * The global akeeba_ajax_url is used as the AJAX proxy URL.
 * If there is no errorCallback, the global akeeba_error_callback is used.
 * @param data An object with the query data, e.g. a serialized form
 * @param successCallback A function accepting a single object parameter, called on success
 * @param errorCallback A function accepting a single string parameter, called on failure
 */
function doAjax(data, successCallback, errorCallback)
{
	var json = JSON.stringify(data);
	if( admintools_update_password.length > 0 )
	{
		json = AesCtr.encrypt( json, admintools_update_password, 128 );
	}
	var post_data = 'json='+encodeURIComponent(json);


	var structure =
	{
		onSuccess: function(msg, responseXML)
		{
			// Initialize
			var junk = null;
			var message = "";

			// Get rid of junk before the data
			var valid_pos = msg.indexOf('###');
			if( valid_pos == -1 ) {
				// Valid data not found in the response
				msg = 'Invalid AJAX data:\n' + msg;
				if(errorCallback == null)
				{
					if(admintools_error_callback != null)
					{
						admintools_error_callback(msg);
					}
				}
				else
				{
					errorCallback(msg);
				}
				return;
			} else if( valid_pos != 0 ) {
				// Data is prefixed with junk
				junk = msg.substr(0, valid_pos);
				message = msg.substr(valid_pos);
			}
			else
			{
				message = msg;
			}
			message = message.substr(3); // Remove triple hash in the beginning

			// Get of rid of junk after the data
			var valid_pos = message.lastIndexOf('###');
			message = message.substr(0, valid_pos); // Remove triple hash in the end
			// Decrypt if required
			if( admintools_update_password.length > 0 )
			{
				try {
					var data = JSON.parse(message);
				} catch(err) {
					message = AesCtr.decrypt(message, admintools_update_password, 128);
				}
			}

			try {
				var data = JSON.parse(message);
			} catch(err) {
				var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
				if(errorCallback == null)
				{
					if(admintools_error_callback != null)
					{
						admintools_error_callback(msg);
					}
				}
				else
				{
					errorCallback(msg);
				}
				return;
			}

			// Call the callback function
			successCallback(data);
		},
		onFailure: function(req) {
			var message = 'AJAX Loading Error: '+req.statusText;
			if(errorCallback == null)
			{
				if(admintools_error_callback != null)
				{
					admintools_error_callback(message);
				}
			}
			else
			{
				errorCallback(message);
			}
		}
	};

	var ajax_object = null;
	
	// Damn you, Internet Explorer!!!
	var randomJunk = new Date().getTime();
	var url = admintools_ajax_url+'?randomJunk='+randomJunk;
	
	if(typeof(XHR) == 'undefined') {
		structure.url = url;
		ajax_object = new Request(structure);
		ajax_object.send(post_data);
	} else {
		ajax_object = new XHR(structure);
		ajax_object.send(url, post_data);
	}
}

/**
 * Pings the update script (making sure its executable!!)
 * @return
 */
function pingUpdate()
{
	// Reset variables
	admintools_update_stat_files = 0;
	admintools_update_stat_inbytes = 0;
	admintools_update_stat_outbytes = 0;

	// Do AJAX post
	var post = {task : 'ping'};
	doAjax(post, function(data){
		startUpdate(data);
	});
}

/**
 * Starts the update
 * @return
 */
function startUpdate()
{
	// Reset variables
	admintools_update_stat_files = 0;
	admintools_update_stat_inbytes = 0;
	admintools_update_stat_outbytes = 0;

	var post = { task : 'startRestore' };
	doAjax(post, function(data){
		processUpdateStep(data);
	});
}

/**
 * Steps through the update
 * @param data
 * @return
 */
function processUpdateStep(data)
{
	if(data.status == false)
	{
		// handle failure
		admintools_error_callback(data.message);
	}
	else
	{
		if(data.done)
		{
			admintools_update_factory = data.factory;
			finalizeUpdate();
		}
		else
		{
			// Add data to variables
			admintools_update_stat_inbytes += data.bytesIn;
			admintools_update_stat_outbytes += data.bytesOut;
			admintools_update_stat_files += data.files;

			// Display data
			document.getElementById('extbytesin').innerHTML = admintools_update_stat_inbytes;
			document.getElementById('extbytesout').innerHTML = admintools_update_stat_outbytes;
			document.getElementById('extfiles').innerHTML = admintools_update_stat_files; 

			// Do AJAX post
			post = {
				task: 'stepRestore',
				factory: data.factory
			};
			doAjax(post, function(data){
				processUpdateStep(data);
			});
		}
	}
}

function finalizeUpdate()
{
	// Do AJAX post
	var post = { task : 'finalizeRestore', factory: admintools_update_factory };
	doAjax(post, function(data){
		updateFinished(data);
	});
}

function updateFinished()
{
	window.location = 'index.php?option=com_admintools&view=jupdate&task=finalize&file='+admintools_file;
}

function showWhatthis()
{
	if(document.getElementById('admintools-whatsthis-info').style.display == 'block') {
		document.getElementById('admintools-whatsthis-info').style.display = 'none';
	} else {
		document.getElementById('admintools-whatsthis-info').style.display = 'block';
	}
}

function hideWhatthis()
{
	document.getElementById('admintools-whatsthis-info').style.display = 'none';
}