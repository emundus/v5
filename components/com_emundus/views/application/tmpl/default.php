<style type="text/css">
/* identity_card */
#identity_card {
	margin-bottom : 80px;
}
#identity_card .column {
	position: relative;
	float: left;
	margin-right: 5px;
}
#center {
	width:30%;
}
#center .content {
	background-color: #F2E5F8;
}
#left {
	width:30%;
}
#left .content {
	background-color: #E5F5F8;
}
#right {
	width:30%;
}
#right .content {
	background-color: #F4C9C9;
}
.column #title {
    margin:0;
    padding:5px;
    font: 12px Helvetica, Arial, sans-serif;
	color:#fff;
    background-color:#9DADC6;
    border:1px solid #8E98A4;
    border-bottom:0;
	text-align:center;
}

/* accordion */
#accordion  {
	clear: both;
	margin: 20px 0 0;
	width:90%;
	/*max-width: 400px;*/
}
#accordion H2 {
	background: #6B7B95;
	color:#fff;
    cursor: pointer;
    font: 12px Helvetica, Arial, sans-serif;
    margin: 0 0 4px 0;
    padding: 3px 5px 1px;
}
#accordion .content {
	background-color: #D3E9F8;
}
</style>
<?php  
defined('_JEXEC') or die('Restricted access'); 
$current_user = & JFactory::getUser();
if(!EmundusHelperAccess::asEvaluatorAccessLevel($current_user->id)) die("ACCESS_DENIED");
	 
jimport( 'joomla.utilities.date' );
JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
?>

<div id="identity_card">
	<div id="left" class="column">
		<div id="title">title1</div>
		<div class="content">eeee</div>
	</div>
	<div id="center" class="column">
		<div id="title">title2</div>
		<div class="content">ffff</div>
	</div>
	<div id="right" class="column">
		<div id="title">title3</div>
		<div class="content">gggg</div>
	</div>
</div>

<div id="accordion">
	<h2><?php echo JText::_('ATTACHEMENTS'); ?></h2>
	<div id="em_application_attachements" class="content">aaaa</div>
	
	<h2><?php echo JText::_('COMMENTS'); ?></h2>
	<div id="em_application_comments" class="content">bbb</div>
	
	<h2><?php echo JText::_('FORMS'); ?></h2>
	<div id="em_application_forms" class="content">cccc</div>
	
	<h2><?php echo JText::_('ACTIONS'); ?></h2>
	<div id="em_application_actions" class="content">dddd</div>
</div>

<script>
window.addEvent('domready', function(){
  new Fx.Accordion($('accordion'), '#accordion h2', '#accordion .content');
});
</script>