<?php
/**
 * @version		$Id: javascript.php 14401 2013-03-19 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2008 - 2013 Decision Publique. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * eMundus is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperJavascript{
	
	function onSubmitForm(){
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$view = JRequest::getVar('view', null, 'GET', 'none',0);
		
		$script = '
function OnSubmitForm() { 
	if(typeof document.pressed !== "undefined") { 
		var button_name=document.pressed.split("|"); 
		switch(button_name[0]) {
		   case \'affect\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=setAssessor";
			break;
			case \'unaffect\': 
				if (confirm("'.JText::_("CONFIRM_UNAFFECT_ASSESSORS").'")) {
					document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=unsetAssessor";
				} else 
					return false;
			break;
			case \'export_zip\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=export_zip";
			break;
			case \'export_to_xls\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&Itemid='.$itemid.'&task=transfert_view&v='.$view.'";
			break;
			case \'custom_email\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=customEmail";
			break;
			case \'applicant_email\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=applicantEmail";
			break;
			case \'default_email\': 
				if (confirm("'.JText::_("CONFIRM_DEFAULT_EMAIL").'")) {
					document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=defaultEmail";
				} else 
					return false;
			break;
			case \'search_button\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&Itemid='.$itemid.'";
			break;
			case \'clear_button\':
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=clear";
			break;
			case \'delete\':
			if(confirm("'.JText::_("CONFIRM_DELETE").'"))
				document.adminForm.action = "index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=delete&sid="+button_name[1];
			break;
			case \'push_true\': 
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=push_true";
			break;
			case \'push_false\':
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=push_false";
			break;
			case \'validate\': 
				document.getElementById("cb"+button_name[1]).checked = true;
				document.getElementById("validation_list").value = 1;
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=administrative_check";
			break;
			case \'unvalidate\': 
				document.getElementById("cb"+button_name[1]).checked = true;
				document.getElementById("validation_list").value = 0;
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=administrative_check";
			break;
			case \'set_status\':
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=administrative_check";
			break;
			case \'delete_eval\': 
			if(confirm("'.JText::_("CONFIRM_DELETE_EVAL").'"))
				document.adminForm.action ="index.php?option=com_emundus&view='.$view.'&controller='.$view.'&Itemid='.$itemid.'&task=delete_eval&sid="+button_name[1];
			else return false;
		break;
			default: return false;
		}
		return true;
	}
} ';
		
		return $script;
	}
	
	/*
	** @todo : 
	*/
	
	function addElement(){
		$script = 'function addElement() {
			var ni = document.getElementById("myDiv");
		  	var numi = document.getElementById("theValue");
		  	var num = (document.getElementById("theValue").value -1)+ 2;
		  	numi.value = num;
		  	var newdiv = document.createElement("div");
		  	var divIdName = "my"+num+"Div";
		  	newdiv.setAttribute("id",divIdName);
			newdiv.innerHTML = "<select name=\"elements[]\" id=\"elements\" onChange=\"javascript:submit();\"><option value=\"\">'.JText::_("PLEASE_SELECT").'</option>';
		$groupe =""; $i=0; 
		$length = 50; 
		$all_elements =& EmundusHelperFilters::getElements();
		foreach($all_elements as $elements) { 
			$groupe_tmp = $elements->group_label; 
			$dot_grp = strlen($groupe_tmp)>=$length?'...':''; 
			$dot_elm = strlen($elements->element_label)>=$length?'...':''; 
			if ($groupe != $groupe_tmp) { 
				$script .= '<option class=\"emundus_search_grp\" disabled=\"disabled\" value=\"\">'.substr(strtoupper($groupe_tmp), 0, $length).$dot_grp.'</option>'; 
				$groupe = $groupe_tmp; 
			} 
			$script .= '<option class=\"emundus_search_elm\" value=\"'.$elements->table_name.'.'.$elements->element_name.'\">'.substr(htmlentities($elements->element_label, ENT_QUOTES), 0, $length).$dot_elm.'</option>'; 
			$i++; 
		} 
		$script .= '</select><a href=\"#removeElement\" onclick=\"removeElement(\'"+divIdName+"\', 1)\"><img src=\"'.JURI::Base().'media/com_emundus/images/icones/viewmag-_16x16.png\" alt=\"'.JText::_('REMOVE_SEARCH_ELEMENT').'\" id=\"add_filt\"/></a>"; ni.appendChild(newdiv); } ';
		//die($script);
		return $script;
	}
	
	function addElementFinalGrade($tables){
		$script = 'function addElementOther() {
			var ni = document.getElementById("otherDiv");
		  	var numi = document.getElementById("theValue");
		  	var num = (document.getElementById("theValue").value -1)+ 2;
		  	numi.value = num;
		  	var newdiv = document.createElement("div");
		  	var divIdName = "other"+num+"Div";
		  	newdiv.setAttribute("id",divIdName);
			newdiv.innerHTML = "<select name=\"elements_other[]\" id=\"elements_other\" onChange=\"javascript:submit();\"><option value=\"\">'.JText::_("PLEASE_SELECT").'</option>';
		$groupe =""; $i=0; 
		$length = 50;
		$elements =& EmundusHelperFilters::getElementsOther($tables);
		if(!empty($elements))
			foreach($elements as $element) { 
				$groupe_tmp = $element->group_label;  
				$dot_grp = strlen($groupe_tmp)>=$length?'...':''; 
				$dot_elm = strlen($element->element_label)>=$length?'...':''; 
				if ($groupe != $groupe_tmp) { 
					$script .= '<option class=\"emundus_search_grp\" disabled=\"disabled\" value=\"\">'.substr(strtoupper($groupe_tmp), 0, $length).$dot_grp.'</option>'; 
					$groupe = $groupe_tmp; 
				}
				$script .= '<option class=\"emundus_search_elm_other\" value=\"'.$element->table_name.'.'.$element->element_name.'\">'.substr(htmlentities($element->element_label, ENT_QUOTES), 0, $length).$dot_elm.'</option>'; 
				$i++; 
			}
		$script .= '</select><a href=\"#removeElement\" onclick=\"removeElement(\'"+divIdName+"\', 2)\"><img src=\"'.JURI::Base().'media/com_emundus/images/icones/viewmag-_16x16.png\" alt=\"'.JText::_('REMOVE_SEARCH_ELEMENT').'\" id=\"add_filt\"/></a>"; ni.appendChild(newdiv); } ';
		return $script;
	}
	
	function delayAct(){
		$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
		$script = 'function delayAct(user_id){
			document.adminForm.action = "index.php?option=com_emundus&view='.JRequest::getCmd( 'view' ).'&Itemid='.$itemid.'#cb"+user_id;
			setTimeout("document.adminForm.submit()",500) }';
		return $script;
	}
}
?>