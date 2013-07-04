<?php
/**
 * Bluesky Form Template CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */
 ?>
<?php
header('Content-type: text/css');
$c = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
echo "

#{$view}_$c legend{
	background-color: #c0c0c0;
	-moz-user-select: none;
	border-bottom: 1px solid #B7B7B7;
	color: #325773;
	font-weight: bold;
	margin: 0;
	padding:0;
	text-shadow: 0 1px 0 #FFFFFF;
 	zoom: 1;
	width:100%;
	background: -moz-linear-gradient(center top , #DCECF4, #BECED2) repeat scroll 0 0 #E7E7E7;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#BECED2', endColorstr='#DCECF4'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#DCECF4),
		to(#BECED2) );
	background-image: -ms-linear-gradient(top, #DCECF4, #BECED2);
		position:absolute;
}

#{$view}_$c .groupintro{
	margin-top:40px;
	padding:0 20px;
	color:#666;
}

#{$view}_$c legend span{
	padding:5px;
	display:block;
}

#{$view}_$c{
	width:100%;
	background-color:#EAF5F9;
	border:1px solid #BDD0D5;
}

#main #{$view}_$c h1{
	paddiing-left:10px;
	margin:0;
}

#{$view}_$c fieldset{
	margin:5px 10px;
	position:relative;
	padding:0;
	border:1px solid #BDD0D5;
	background:#F6F9FA;
}

#{$view}_$c fieldset ul{
	list-style:none;
	margin:0;
}

#{$view}_$c fieldset > ul,
#details_$c fieldset > ul,
#{$view}_$c .fabrikSubGroupElements > ul{
	list-style:none;
	padding:40px 10px 20px 10px;
	margin:0;
}

#{$view}_$c .fabrikForm .fabrikGroup ul{
	list-style:none;
}

#details_$c .fabrikGalleryImage{
	border:1px solid #ccc;
	margin:5px;
	padding:5px;
}

/* START: align google map sub elements vertically */

.googlemap .fabrikSubElementContainer{
	-moz-box-orient:vertical;
	-webkit-box-orient:vertical;
	box-orient:vertical;
}

.googlemap .fabrikSubElementContainer > div{
	-mox-box-flex: 1;
	-webkit-box-flex: 1;
	box-flex: 1;
}

/* END: align google map sub elements vertically */
/* START : label spacing for chxbox, radios */

#{$view}_$c label span{
	padding:0 4px;
}

/* END : label spacing for chxbox, radios */

#{$view}_$c .linkedTables{
	margin:0.6em 0;
}

#{$view}_$c  .related_data_norecords{
	display:inline;
}

#{$view}_$c .fabrikForm .fabrikGroup ul .fabrikElementContainer,
#details_$c .fabrikElementContainer,
#{$view}_$c .fabrikElementContainer{
	padding:5px 10px;
	margin-top:10px;
	background:none !important;
	display:-webkit-box;
	display:-moz-box;
	display:box;
	width:50%;
}

#{$view}_$c .fabrikActions{
	padding:10px;
	clear:left;
	margin:5px 10px;
	border:1px solid #BDD0D5;
	background:#F6F9FA;
}
#{$view}_$c .fabrikActions input{
	margin-right:7px;
}

#{$view}_$c .fabrikValidating{
	color: #476767;
	background: #EFFFFF no-repeat right 7px !important;
}

#{$view}_$c .fabrikSuccess{
	color: #598F5B;
	background: #DFFFE0 no-repeat right 7px !important;
}

/*** slide out add option
section for dropdowns radio buttons etc**/

#{$view}_$c .addoption dl{
	display:inline;
	width:75%;
}
#{$view}_$c .addoption{
	clear:left;
	padding:8px;
	margin:3px 0;
	background-color:#efefef;
}

#{$view}_$c  a.toggle-addoption, a.toggle-selectoption{
	padding:0 0 0 10px;
}


/*** end slide out add option section **/

#{$view}_$c input,
#{$view}_$c select{
	border:1px solid #DDDDDD;
	border-radius:3px;
	padding:3px;
}

#{$view}_$c  .inputbox:focus{
	background-color:#ffffcc;
	border:1px solid #aaaaaa;
}

#{$view}_$c .addoption dd, .addoption dt{
	padding:2px;
	display:inline;
}

#{$view}_$c .fabrikSubGroup{
	clear:both;
}

#{$view}_$c .fabrikSubGroupElements{
	width:80%;
	float:left;
}

#{$view}_$c .geo{
	visibility:hidden;
}


#{$view}_$c .fabrikGroup .readonly,
#{$view}_$c .fabrikGroup .disabled{
	background-color:#DFDFDF !important;
	color:#8F8F8F;
}

/*** fileupload folder select css **/
#{$view}_$c ul.folderselect{
	border:1px dotted #eee;
	background-color:#efefef;
	color:#333;
}

#{$view}_$c .folderselect-container{
	border:1px dotted #666;width:350px;
}

#{$view}_$c .fabrikForm .breadcrumbs{
	background: transparent url(../images/folder_open.png) no-repeat center left;
	padding:2px 2px 2px 26px ;
}

#{$view}_$c .fabrikForm .fabrikGroup li.fileupload_folder{
	background: transparent url(../images/folder.png) no-repeat center left;
	padding:2px 2px 2px 26px ;
	margin:2px;
}

#{$view}_$c .fabrik_characters_left{
clear:left;
}

/** bump calendar above mocha window in mootools 1.2**/
#{$view}_$c div.calendar{
	z-index:115 !important;
}

/** special case for 'display' element with 'show label: no' option **/
#{$view}_$c .fabrikPluginElementDisplayLabel {
	width: 100% !important;
}

/** autocomplete container inject in doc body not in #forn_$c */
.auto-complete-container{
	overflow-y: hidden;
	border:1px solid #ddd;
	z-index:100;
}

.auto-complete-container ul{
list-style:none;
padding:0;
margin:0;
}

.auto-complete-container li.unselected{
	padding:2px 10px !important;
	background-color:#fff !important;
	margin:0 !important;
	border-top:1px solid #ddd;
	cursor:pointer;
}

.auto-complete-container li:hover,
.auto-complete-container li.selected{
	background-color:#DFFAFF !important;
	cursor:pointer;
}
#{$view}_$c .leftCol,
#details_$c .leftCol,
#{$view}_$c .fabrikSubLabel{
	width: 130px;
}
#details_$c .leftCol{
	color:#999;
}

#{$view}_$c .fabrikElement {
	margin-left: 10px;
	-webkit-box-flex:1;
	-moz-box-flex:1;
	box-flex:1;
}

#{$view}_$c .addbutton {
	background: transparent url(images/add.png) no-repeat left;
	padding: 2px 5px 0 20px;
	margin-left:7px;
}

#{$view}_$c .fabrikError,
#{$view}_$c .fabrikNotice,
#{$view}_$c .fabrikValidating,
#{$view}_$c .fabrikSuccess{
	font-weight: bold;
}

#{$view}_$c .fabrikMainError{
	height:2em;
	line-height:2em;
}

#{$view}_$c .fabrikMainError img{
	padding:0.35em 1em;
	float:left;
}

#{$view}_$c .fabrikNotice{
	color: #009FBF;
	background: #DFFDFF url(images/alert.png) no-repeat center left !important;
}

#{$view}_$c .fabrikError,
#{$view}_$c .fabrikGroup .fabrikError{
	color: #c00;
	background: #EFE7B8;
}

#{$view}_$c .fabrikErrorMessage{
	padding-right: 5px;
}



#{$view}_$c .fabrikLabel {
	min-height:1px; /*for elements with no label txt*/
}

#{$view}_$c .fabrikActions {
	padding-top: 15px;
	clear: left;
	padding-bottom: 15px;
}

#{$view}_$c .fabrikGroupRepeater {
	float: left;
	width: 19%;
	padding-top: 40px;
}

/** used by password element */
#{$view}_$c .fabrikSubLabel {
	margin-left: -10px;
	clear: left;
	margin-top: 10px;
	float: left;
}

#{$view}_$c .fabrikSubElement {
	display: block;
	margin-top: 10px;
}

#{$view}_$c .addGroup:link {
	text-decoration: none;
}

/*
some fun with fancy buttons not ready for prime time

#{$view}_$c .button{
background: -moz-linear-gradient(center top , #ccc 0%, #777) repeat scroll 0 0 transparent;
background-image: -ms-linear-gradient(top, #ccc, #777);
border: 1px solid #614337;
border-radius: 6px 6px 6px 6px;
box-shadow: 0 1px 2px rgba(0, 0, 0, 0.5), 0 0 2px rgba(255, 255, 255, 0.6) inset;
color: #FFFFFF;
margin: 10px;
padding: 5px 20px;

}

#{$view}_$c .button:hover{
background: -moz-linear-gradient(center top , #E88801 0%, #C93C00) repeat scroll 0 0 transparent; /* orange */
background: -moz-linear-gradient(center top , #8EC400 0%, #558A01) repeat scroll 0 0 transparent; /* green */
background-image: -ms-linear-gradient(top, #8EC400, #558A01);
text-shadow: 0 -1px 0 #000000, 0 1px 0 rgba(255, 255, 255, 0.2);
box-shadow: 0 1px 1px rgba(0, 0, 0, 0.5), 0 0 1px rgba(255, 255, 255, 0.6) inset;

}

#{$view}_$c .button[name=delete]:hover{
	background: -moz-linear-gradient(center top , #E88801 0%, #C93C00) repeat scroll 0 0 transparent;
	background-image: -ms-linear-gradient(top, #E88801, #C93C00);
}

#{$view}_$c .button[name=Reset]:hover{
	background: -moz-linear-gradient(center top , #E3EB01 0%, #B19F01) repeat scroll 0 0 transparent;
	background-image: -ms-linear-gradient(top, #E88801, #B19F01);
} */
";
?>