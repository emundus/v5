<?php
/**
 * Mint Form Template: CSS
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
	border-bottom: 1px solid #8CC77B;
	color: #567E4E;
	font-weight: bold;
	margin: 0;
	padding:0;
	text-shadow: 0 1px 0 #FFFFFF;
	zoom: 1;
	width:100%;
	background: -moz-linear-gradient(center top , #E3F9E1, #A9EEA4) repeat scroll 0 0 #E7E7E7;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#A9EEA4', endColorstr='#E3F9E1'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#E3F9E1),
		to(#A9EEA4) );
	background-image: -ms-linear-gradient(top, #E3F9E1, #A9EEA4);
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
	background-color:#FBFCFA;
	border:1px solid #D2F2B7;
}

#main #{$view}_$c h1{
	paddiing-left:10px;
	margin:0;

}

#{$view}_$c fieldset{
	margin:5px 10px;
	position:relative;
	padding:0;
	border:1px solid #C3E7AF;
	background-color:#F3FCEE;
}

#{$view}_$c fieldset ul{
	list-style:none;
	padding:40px 10px 20px 10px;
	margin:0;
}

#{$view}_$c ul.fabrikRepeatData{
	padding:0;
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

#{$view}_$c .related_data_norecords{
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
		border:1px solid #C3E7AF;
	background-color:#F3FCEE;
}
#{$view}_$c .fabrikActions input{
	margin-right:7px;
}

#{$view}_$c .fabrikValidating{
	color: #476767;
	background: #EFFFFF;
}

#{$view}_$c .fabrikSuccess{
	color: #598F5B;
	background: #DFFFE0;
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

";
