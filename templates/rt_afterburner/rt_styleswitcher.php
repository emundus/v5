<?php
defined( '_JEXEC' ) or die( 'Restricted index access' );

$cookie_prefix = "afterburner-";
$cookie_time = time()+31536000;
$template_properties = array('tstyle');

foreach ($template_properties as $tprop) {
    $my_session = &JFactory::getSession();
	
	if (isset($_REQUEST[$tprop])) {
	    $$tprop = htmlentities(JRequest::getString($tprop, null, 'get'));
	
    	$my_session->set($cookie_prefix.$tprop, $$tprop);
    	setcookie ($cookie_prefix. $tprop, $$tprop, $cookie_time, '/', false);   
    	global $$tprop; 
    }
}

?>