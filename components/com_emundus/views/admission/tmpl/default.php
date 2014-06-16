<?php
jimport('joomla.utilities.date');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
JHTML::_('behavior.mootools');
JHTML::stylesheet('emundus.css', JURI::Base() . 'components/com_emundus/style/');
defined('_JEXEC') or die('Restricted access');
$document = & JFactory::getDocument();
$current_user = JFactory::getUser();
$current_p = JRequest::getVar('groups', null, 'POST', 'none', 0);
$current_pid = JRequest::getVar('profile', null, 'POST', 'none', 0);
$current_apid = JRequest::getVar('apid', null, 'POST', 'none', 0);
$current_engaged = JRequest::getVar('engaged', null, 'POST', 'none', 0);
$current_u = JRequest::getVar('user', null, 'POST', 'none', 0);
$current_ap = JRequest::getVar('profil', null, 'POST', 'none', 0);
$current_au = JRequest::getVar('user', null, 'POST', 'none', 0);
$current_s = JRequest::getVar('s', null, 'POST', 'none', 0);
//$schoolyears = JRequest::getVar('schoolyears', $this->schoolyear, 'POST', 'none', 0);
$current_y = JRequest::getVar('schoolyear', $this->schoolyear, 'POST', 'none', 0);
$limitstart = JRequest::getVar('limitstart', null, 'GET', 'none', 0);
$search = JRequest::getVar('elements', null, 'POST', 'array', 0);
$search_values = JRequest::getVar('elements_values', null, 'POST', 'array', 0);
$ls = JRequest::getVar('limitstart', null, 'GET', 'none', 0);
$filter_order = JRequest::getVar('filter_order', null, 'GET', 'none', 0);
$filter_order_Dir = JRequest::getVar('filter_order_Dir', null, 'GET', 'none', 0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none', 0);
$v = JRequest::getVar('view', null, 'GET', 'none', 0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none', 0);
$selected = JRequest::getVar('checkselected', array(), 'post', 'array');
$checkselected = (isset($selected[0]) && ($selected[0] == 1)) ? "checked" : "";

// Starting a session.
$session = & JFactory::getSession();
// Gettig the orderid if there is one.
$s_elements = $session->get('s_elements');
$s_elements_values = $session->get('s_elements_values');

if (count($search) == 0 && isset($s_elements)) {
    $search = $s_elements;
    $search_values = $s_elements_values;
}
?>

<a href="<?php echo JURI::getInstance()->toString() . '&tmpl=component'; ?>" target="_blank" class="emundusraw"><img src="<?php echo $this->baseurl . '/images/M_images/printButton.png" alt="' . JText::_('PRINT') . '" title="' . JText::_('PRINT'); ?>" width="16" height="16" align="right" /></a>

<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="admission"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<fieldset>
    <legend>
        <img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag_22x22.png" alt="<?php JText::_('FILTERS'); ?>"/> <?php echo JText::_('FILTERS'); ?>
    </legend>

    <table width="100%">
        <tr>
            <th align="left"><?php echo '<span class="editlinktip hasTip" title="' . JText::_('NOTE') . '::' . JText::_('NAME_EMAIL_USERNAME') . '">' . JText::_('QUICK_FILTER') . '</span>'; ?></th>
            <th align="left"><?php echo JText::_('PROFILE'); ?></th>
            <th align="left"><?php echo JText::_('SELECTED'); ?></th>
            <th align="left"><?php echo JText::_('ENGAGED'); ?></th>
            <th align="left"><?php echo JText::_('SCHOOLYEAR_SELECT'); ?></th>
        </tr>
        <tr>
            <td align="left"><input type="text" name="s" size="30" value="<?php echo $current_s; ?>"/></td>
            <td>
                <select name="profile" onChange="javascript:submit()">
                    <option value=""> <?php echo JText::_('ALL_PROFILES'); ?> </option>
                    <?php
                    foreach ($this->applicantsProfiles as $applicantsProfiles) {
                        echo '<option value="' . $applicantsProfiles->id . '"';
                        if ($current_pid == $applicantsProfiles->id)
                            echo ' selected';
                        echo '>' . $applicantsProfiles->label . '</option>';
                    }
                    ?>  
                </select> 
            </td>
            <td>
                <input onClick="javascript:submit()" type="checkbox" id="checkselected" name="checkselected" value="1" <?php echo $checkselected; ?>>
            </td>
            <td>
                <select name="engaged" onChange="javascript:submit()">
                    <option value=""> <?php echo JText::_('ALL'); ?> </option>
                    <option value="1" <?php echo $current_engaged == '1' ? 'selected' : ''; ?>> <?php echo JText::_('YES'); ?> </option>
                    <option value="2" <?php echo $current_engaged == '2' ? 'selected' : ''; ?>> <?php echo JText::_('NO'); ?> </option>
                </select>
            </td>
            <td>
                <select name="schoolyear" onChange="javascript:submit()">
                    <option value=""> <?php echo JText::_('ALL'); ?> </option>
                    <?php
                    foreach ($this->schoolYears as $schoolYears) {
                        echo '<option value="' . $schoolYears->schoolyear . '"';
                        if ($current_y == $schoolYears->schoolyear)
                            echo ' selected';
                        echo '>' . $schoolYears->schoolyear . '</option>';
                    }
                    ?>  
                </select>
            </td>
            </td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <th align="left">
<?php echo '<span class="editlinktip hasTip" title="' . JText::_('NOTE') . '::' . JText::_('FILTER_HELP') . '">' . JText::_('ELEMENT_FILTER') . '</span>'; ?>
                <input type="hidden" value="0" id="theValue" />
                <a href="javascript:;" onclick="addElement();"><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag+_16x16.png" alt="<?php JText::_('ADD_SEARCH_ELEMENT'); ?>"/></a>
            </th>
        </tr>
        <tr>
            <td>
                <div id="myDiv">
                    <?php
                    if (count($search) > 0 && isset($search) && is_array($search)) {

                        $i = 0;
                        foreach ($search as $sf) {
                            echo '<div id="filter' . $i . '">';
                            ?>
                            <select name="elements[]" id="elements">
                                <option value=""> <?php echo JText::_('PLEASE_SELECT'); ?> </option>
                                <?php
                                $groupe = "";
                                foreach ($this->elements as $elements) {
                                    $groupe_tmp = $elements->group_label;
                                    $length = 50;
                                    $dot_grp = strlen($groupe_tmp) >= $length ? '...' : '';
                                    $dot_elm = strlen($elements->element_label) >= $length ? '...' : '';
                                    if ($groupe != $groupe_tmp) {
                                        echo '<option class="emundus_search_grp" disabled="disabled" value="">' . substr(strtoupper($groupe_tmp), 0, $length) . $dot_grp . '</option>';
                                        $groupe = $groupe_tmp;
                                    }
                                    echo '<option class="emundus_search_elm" value="' . $elements->table_name . '.' . $elements->element_name . '"';
                                    //$key = array_search($elements->table_name.'.'.$elements->element_name, $search);
                                    if ($elements->table_name . '.' . $elements->element_name == $search[$i])
                                        echo ' selected';
                                    echo '>' . substr($elements->element_label, 0, $length) . $dot_elm . '</option>';
                                }
                                ?>
                            </select>

                            <input name="elements_values[]" width="30" value="<?php echo $search_values[$i]; ?>" />
                            <a href="#" onclick="removeElement('<?php echo 'filter' . $i; ?>')"><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag-_16x16.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>
                            <?php
                            $i++;
                            echo '</div>';
                        }
                    }
                    ?>  
                </div>
                <input type="submit" name="search_button" onclick="document.pressed=this.name" value="<?php echo JText::_('SEARCH_BTN'); ?>"/>
                <input type="submit" name="clear_button" onclick="document.pressed=this.name" value="<?php echo JText::_('CLEAR_BTN'); ?>"/>
            </td>
        </tr>
    </table>
</fieldset>

<div class="emundusraw">
    <?php
    if (!empty($this->users)) {
        /* echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_SELECTED_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/XLSFile-selected_48.png" name="export_to_xls" onclick="document.pressed=this.name"></span>'; 
          echo '<span class="editlinktip hasTip" title="'.JText::_('EXPORT_ALL_TO_XLS').'"><input type="image" src="'.$this->baseurl.'/images/emundus/icones/XLSFile_48.png" name="export_all_to_xls" onclick="document.pressed=this.name" /></span>'; */
        echo '<span class="editlinktip hasTip" title="' . JText::_('EXPORT_SELECTED_TO_ZIP') . '"><input type="image" src="' . $this->baseurl . '/images/emundus/icones/ZipFile-selected_48.png" name="export_zip" onclick="document.pressed=this.name" /></span>';
        echo '<span class="editlinktip hasTip" title="' . JText::_('SEND_ELEMENTS') . '"><input type="image" src="' . $this->baseurl . '/images/emundus/icones/XLSFile-selected_48.png" name="export_to_xls" onclick="document.pressed=this.name" /></span>';
        ?>
    </div>

    <?php
    if ($tmpl == 'component') {
        echo '<div><h3><img src="' . JURI::Base() . 'images/emundus/icones/folder_documents.png" alt="' . JText::_('SELECTED_APPLICANTS_LIST') . '"/>' . JText::_('SELECTED_APPLICANTS_LIST') . '</h3>';
        $document = & JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . "components/com_emundus/style/emundusraw.css");
    } else {
        echo '<fieldset><legend><img src="' . JURI::Base() . 'images/emundus/icones/folder_documents.png" alt="' . JText::_('SELECTED_APPLICANTS_LIST') . '"/>' . JText::_('SELECTED_APPLICANTS_LIST') . '</legend>';
    }
    ?>

    <table id="userlist" width="100%">
        <thead>
            <tr>
                <td align="center" colspan="7">
    <?php echo $this->pagination->getResultsCounter(); ?>
                </td>
            </tr>
            <tr>
                <th>
                    <input type="checkbox" id="checkall" class="emundusraw" onClick="javascript:check_all()"/>
    <?php echo JHTML::_('grid.sort', JText::_('#'), 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
                </th>
                <th><?php echo JText::_('PHOTO'); ?></th>
                <th><?php echo JHTML::_('grid.sort', JText::_('NAME'), 'lastname', $this->lists['order_Dir'], $this->lists['order']); ?></th>
                <th><?php echo JText::_('INFO1'); ?></th>
                <th><?php echo JText::_('INFO2'); ?></th>
                <th><?php echo JHTML::_('grid.sort', JText::_('ENGAGED'), 'engaged', $this->lists['order_Dir'], $this->lists['order']); ?></th>
                <th><?php echo JHTML::_('grid.sort', JText::_('PROFILE'), 'profile', $this->lists['order_Dir'], $this->lists['order']); ?></th>
                <th><?php echo JHTML::_('grid.sort', JText::_('RESULT_FOR'), 'LabelProfile', $this->lists['order_Dir'], $this->lists['order']); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="10">
    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>

            <?php
            $i = 0;
            $j = 0;
            foreach ($this->users as $user) {
                ?>
                <tr class="row<?php echo $j++ % 2; ?>">
                    <td>
                            <?php echo++$i + $limitstart; ?>
                        <div class="emundusraw">
                            <?php if ($user->id != 62)  ?> <input id="cb<?php echo $user->id; ?>" type="checkbox" name="ud[]" value="<?php echo $user->id; ?>"/><br />
                            <?php
                            echo '<span class="editlinktip hasTip" title="' . JText::_('MAIL_TO') . '::' . $user->email . '">';
                            if ($user->gender == 'male')
                                echo '<a href="mailto:' . $user->email . '"><img src="' . $this->baseurl . '/images/emundus/icones/user_male.png" width="22" height="22" align="bottom" /></a>';
                            elseif ($user->gender == 'female')
                                echo '<a href="mailto:' . $user->email . '"><img src="' . $this->baseurl . '/images/emundus/icones/user_female.png" width="22" height="22" align="bottom" /></a>';
                            else
                                echo '<a href="mailto:' . $user->email . '">' . $user->gender . '</a>';
                            echo '</span>';
                            echo '<span class="editlinktip hasTip" title="' . JText::_('APPLICATION_FORM') . '::' . JText::_('POPUP_APPLICATION_FORM_DETAILS') . '">';
                            echo '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-innerWidth*0.2,y:window.
innerHeight-40}}" href="' . $this->baseurl . '/index.php?option=com_emundus&view=application_form&sid=' . $user->id . '&tmpl=component" target="_self" class="modal"><img src="' . $this->baseurl . '/images/emundus/icones/viewmag_16x16.png" alt="' . JText::_('DETAILS') . '" title="' . JText::_('DETAILS') . '" width="16" height="16" align="bottom" /></a>';
                            echo '</span>';
                            echo '<span class="editlinktip hasTip" title="' . JText::_('UPLOAD_FILE_FOR_STUDENT') . '::' . JText::_('YOU_CAN_ATTACH_A_DOCUMENT_FOR_THE_STUDENT_THRU_THAT_LINK') . '">';
                            echo '<a rel="{handler:\'iframe\',size:{x:450,y:window.
innerHeight}}" href="' . $this->baseurl . '/index.php?option=com_emundus&view=checklist&layout=attachments&sid=' . $user->id . '&tmpl=component" target="_self" class="modal"><img src="' . $this->baseurl . '/images/emundus/icones/attach_16x16.png" alt="' . JText::_('UPLOAD') . '" title="' . JText::_('UPLOAD') . '" width="16" height="16" align="bottom" /></a> ';
                            echo '</span></div>';
                            echo '#' . $user->id . '</div>';
                            ?>
                    </td>
                    <td align="center" valign="middle">
                        <?php
                        echo '<span class="editlinktip hasTip" title="' . JText::_('OPEN_PHOTO_IN_NEW_WINDOW') . '::">';
                        echo '<a href="' . $this->baseurl . '/' . EMUNDUS_PATH_REL . $user->id . '/' . $user->avatar . '" target="_blank" class="modal"><img src="' . $this->baseurl . '/' . EMUNDUS_PATH_REL . $user->id . '/tn_' . $user->avatar . '" width="60" /></a>';
                        echo '</span>';
                        ?>        
                    </td>
                    <td><?php
                if (strtoupper($user->name) == strtoupper($user->firstname) . ' ' . strtoupper($user->lastname))
                    echo '<strong>' . strtoupper($user->lastname) . '</strong> ' . $user->firstname;
                else
                    echo '<span class="hasTip" title="' . JText::_('USER_MODIFIED_ALERT') . '"><font color="red">' . $user->name . '</font></span>';
                        ?>
                    </td>
                    <td>
                        <?php
                        echo '<textarea id="info1-' . $user->id . '" class="fabrikinput inputbox" rows="3" cols="20" name="info1">' . $user->info1 . '</textarea>';
                        echo '<button type="button" id="btn-info1-' . $user->id . '"><img width="20" heigth="20" src="images/apply_f2.png"></button>';
                        echo '<div id="i1-' . $user->id . '"></div>';
                        $url = 'index.php?option=com_emundus&controller=admission&format=raw&task=set_info&f=info1&sid=' . $user->id;
                        ?>
                        <script>
                            window.addEvent( 'domready', function() {
                                $('<?php echo 'btn-info1-' . $user->id; ?>').addEvent( 'click', function() {
                                    $('i1-<?php echo $user->id; ?>').empty().addClass('ajax-loading');
                                    var a = new Ajax( '<?php echo $url; ?>'+'&info='+document.getElementById("<?php echo 'info1-' . $user->id; ?>").value.replace(/\n/g,'|-|'), { method: 'get', update: $('i1-<?php echo $user->id; ?>')}).request();
                                }); 
                            });
                        </script>
                    </td>	
                    <td>
                        <?php
                        echo '<textarea id="info2-' . $user->id . '" class="fabrikinput inputbox" rows="3" cols="20" name="info1">' . $user->info2 . '</textarea>';
                        echo '<button type="button" id="btn-info2-' . $user->id . '"><img width="20" heigth="20" src="images/apply_f2.png"></button>';
                        echo '<div id="i2-' . $user->id . '"></div>';
                        $url = 'index.php?option=com_emundus&controller=admission&format=raw&task=set_info&f=info2&sid=' . $user->id;
                        ?>
                        <script>window.addEvent( 'domready', function() {$('<?php echo 'btn-info2-' . $user->id; ?>').addEvent( 'click', function() {
                            $('i2-<?php echo $user->id; ?>').empty().addClass('ajax-loading');
                            var a = new Ajax( '<?php echo $url; ?>'+'&info='+document.getElementById("<?php echo 'info2-' . $user->id; ?>").value.replace(/\n/g,'|-|'), { method: 'get', update: $('i2-<?php echo $user->id; ?>')}).request();
                }); });</script>
                    </td>
                    <td>
                        <?php
                        $state = $user->profile == 7 ? 'disabled="disabled"' : '';
                        echo '<input name="checkbox" type="checkbox" ' . $state . ' id="engaged-' . $user->id . '" ';
                        if ($user->engaged == 1) {
                            echo 'checked="checked"';
                            $set = 0;
                        } else
                            $set = 1;
                        echo ' />';
                        echo '<div id="e-' . $user->id . '"></div>';
                        $url = 'index.php?option=com_emundus&controller=admission&format=raw&task=set_engaged&sid=' . $user->id . '&set=' . $set;
                        ?>
                        <script>window.addEvent( 'domready', function() {$('<?php echo 'engaged-' . $user->id; ?>').addEvent( 'click', function() {
                    $('e-<?php echo $user->id; ?>').empty().addClass('ajax-loading');
                    var a = new Ajax( '<?php echo $url; ?>', {
                        method: 'get',
                        update: $('e-<?php echo $user->id; ?>')
                    }).request();
        }); });</script>
                    </td>
                    <td><?php echo '<div class="emundusprofile' . $user->profile . '">' . $this->profiles_id[$user->profile]->label . '</div>'; ?>
                    </td>
                    <td><div class="emundusprofile<?php echo $user->result_for; ?>"><?php echo $user->LabelProfile; ?></div></td>
                </tr>
        <?php
        $j++;
    }
    ?>
    </table>
    <?php
    if ($tmpl == 'component') {
        echo '</div>';
    } else {
        echo '</fieldset>';
    }
    ?>

    </form>
    <?php
}
?>


<script>
function check_all() {
var checked = document.getElementById('checkall').checked;
<?php foreach ($this->users as $user) { ?>
    document.getElementById('cb<?php echo $user->id; ?>').checked = checked;
<?php } ?>
}

<?php
$allowed = array("Super Administrator", "Administrator", "Editor");
if (!in_array($current_user->usertype, $allowed)) {
    ?>
    function hidden_all() {
    document.getElementById('checkall').style.visibility='hidden';
    <?php foreach ($this->users as $user) { ?>
        document.getElementById('cb<?php echo $user->id; ?>').style.visibility='hidden';
    <?php } ?>
    }
    hidden_all();
    <?php
}
?>

function addElement() {
var ni = document.getElementById('myDiv');
var numi = document.getElementById('theValue');
var num = (document.getElementById('theValue').value -1)+ 2;
numi.value = num;
var newdiv = document.createElement('div');
var divIdName = 'my'+num+'Div';
newdiv.setAttribute('id',divIdName);
newdiv.innerHTML = '<select name="elements[]" id="elements"><option value=""> <?php echo JText::_("PLEASE_SELECT"); ?> </option><?php $groupe = "";
$i = 0;
foreach ($this->elements as $elements) {
    $groupe_tmp = $elements->group_label;
    $length = 50;
    $dot_grp = strlen($groupe_tmp) >= $length ? '...' : '';
    $dot_elm = strlen($elements->element_label) >= $length ? '...' : '';
    if ($groupe != $groupe_tmp) {
        echo "<option class=\"emundus_search_grp\" disabled=\"disabled\" value=\"\">" . substr(strtoupper($groupe_tmp), 0, $length) . $dot_grp . "</option>";
        $groupe = $groupe_tmp;
    } echo "<option class=\"emundus_search_elm\" value=\"" . $elements->table_name . '.' . $elements->element_name . "\">" . substr(htmlentities($elements->element_label, ENT_QUOTES), 0, $length) . $dot_elm . "</option>";
    $i++;
} ?></select><input name="elements_values[]" width="30" /> <a href=\'#\' onclick=\'removeElement("'+divIdName+'")\'><img src="<?php JURI::Base(); ?>images/emundus/icones/viewmag-_16x16.png" alt="<?php JText::_('REMOVE_SEARCH_ELEMENT'); ?>"/></a>';
ni.appendChild(newdiv);
}

function removeElement(divNum) {
var d = document.getElementById('myDiv');
var olddiv = document.getElementById(divNum);
d.removeChild(olddiv);
}

function tableOrdering( order, dir, task ) {
var form = document.adminForm;
form.filter_order.value = order;
form.filter_order_Dir.value = dir;
document.adminForm.submit( task );
}

function cptCheck() {
var cpt = 0;
<?php foreach ($this->users as $user) { ?>
    if(document.getElementById('cb<?php echo $user->id; ?>').checked)
    cpt++;
<?php } ?>
return cpt;
}

function OnSubmitForm() {
switch(document.pressed) {
case 'search_button': 
document.adminForm.submit();
break;
case 'clear_button': 
document.adminForm.action ="index.php?option=com_emundus&controller=admission&task=clear";
break;
/*case 'export_to_xls': 
                        document.adminForm.action ="index.php?option=com_emundus&controller=admission&task=export_to_xls";
                break;
                case 'export_all_to_xls': 
                        document.adminForm.action ="index.php?option=com_emundus&controller=admission&task=export_all_to_xls";
                break;*/
case 'export_zip': 
document.adminForm.action ="index.php?option=com_emundus&controller=check&task=export_zip";
break;
case 'export_to_xls': 
document.adminForm.action ="index.php?option=com_emundus&task=transfert_view&v=<?php echo $v; ?>&Itemid=<?php echo $itemid; ?>";
break;
default: return false;
}
return true;
}
</SCRIPT>