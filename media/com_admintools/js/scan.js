/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

/** @var The AJAX proxy URL */
var admintools_scan_ajax_url_start = "";
var admintools_scan_ajax_url_step = "";

/** @var The callback function to call on error */
var admintools_scan_error_callback = scan_dummy_error_handler;

var admintools_scan_msg_ago = '';

var admintools_scan_timerid = -1;
var admintools_scan_responseago = 0;

/**
 * An extremely simple error handler, dumping error messages to screen
 * @param error The error message string
 */
function scan_dummy_error_handler(error)
{
	alert(error);
	window.location = 'index.php?option=com_admintools&view=scans';
}

/**
 * Performs an AJAX request and returns the parsed JSON output.
 * 
 * @param successCallback A function accepting a single object parameter, called on success
 * @param errorCallback A function accepting a single string parameter, called on failure
 */
function doScanAjax(url, successCallback, errorCallback)
{
	var structure =
	{
		method: 'get',
		onSuccess: function(msg, responseXML)
		{
			stop_scan_timer();
			
			// Initialize
			var junk = null;
			var message = "";

			// Get rid of junk before the data
			var valid_pos = msg.indexOf('###');
			if( valid_pos == -1 ) {
				// Valid data not found in the response
				msg = 'Invalid server response:\n' + msg;
				if(errorCallback == null)
				{
					if(admintools_scan_error_callback != null)
					{
						admintools_scan_error_callback(msg);
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

			try {
				var data = JSON.parse(message);
			} catch(err) {
				var msg = err.message + "\n\n" + message + "\n";
				if(errorCallback == null)
				{
					if(admintools_scan_error_callback != null)
					{
						admintools_scan_error_callback(msg);
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
			stop_scan_timer();
			
			var message = 'Server Error:\n'+req.status+' '+req.statusText;
			if(errorCallback == null)
			{
				if(admintools_scan_error_callback != null)
				{
					admintools_scan_error_callback(message);
				}
			}
			else
			{
				errorCallback(message);
			}
		}
	};

	var ajax_object = null;
	start_scan_timer();
	
	// Damn you, Internet Explorer!!!
	var randomJunk = new Date().getTime();
	url += '&randomJunk='+randomJunk;
	
	if(typeof(XHR) == 'undefined') {
		structure.url = url;
		ajax_object = new Request(structure);
		ajax_object.send();
	} else {
		ajax_object = new XHR(structure);
		ajax_object.send(url, null);
	}
}

function startScan()
{
	document.getElementById('admintools-scan-dim').style.display = 'block';
	doScanAjax(admintools_scan_ajax_url_start, function(data){
		processScanStep(data);
	})
}

function processScanStep(data)
{
	stop_scan_timer();
	
	if(data.status == false) {
		// handle failure
		admintools_scan_error_callback(data.error);
	} else {
		if(data.done) {
			window.location = 'index.php?option=com_admintools&view=scans';
		} else {
			start_scan_timer();
			doScanAjax(admintools_scan_ajax_url_step, function(data){
				processScanStep(data);
			})
		}
	}
}

function start_scan_timer()
{
	if(admintools_scan_timerid >= 0) {
		window.clearInterval(admintools_scan_timerid);
	}
	
	admintools_scan_responseago = 0;
	set_scan_timermsg();
	admintools_scan_timerid = window.setInterval('step_scan_timer()', 1000);
}

function step_scan_timer()
{
	admintools_scan_responseago++;
	set_scan_timermsg();
}

function stop_scan_timer()
{
	if(admintools_scan_timerid >= 0) {
		window.clearInterval(admintools_scan_timerid);
	}
}

function set_scan_timermsg()
{
	var myText = admintools_scan_msg_ago;
	document.id('admintools-lastupdate-text').innerHTML = myText.replace('%s',admintools_scan_responseago);
}