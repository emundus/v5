<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class JumiController extends JController {
    function __construct($default = array()) {
        parent::__construct($default);
        $this->registerTask('add' , 'editApplication');
        $this->registerTask('edit', 'editApplication');
        $this->registerTask('save', 'saveApplication');
        $this->registerTask('apply', 'saveApplication');
        $this->registerTask('remove', 'removeApplication');
        $this->registerTask('publish', 'publishApplications');
        $this->registerTask('unpublish', 'publishApplications');
        //$this->registerTask('cancel', 'cancelApplication');
    }

    function showApplications() {
        $mainframe =& JFactory::getApplication();
        global $option;
        $option = JRequest::getCMD('option');
        $db               = &JFactory::getDBO();
        $filter_order     = $mainframe->getUserStateFromRequest("$option.filter_order",'filter_order','m.id');
        $filter_order_Dir = $mainframe->getUserStateFromRequest("$option.filter_order_Dir",'filter_order_Dir','');
        $filter_state     = $mainframe->getUserStateFromRequest("$option.filter_state",'filter_state','*');
        $search           = $mainframe->getUserStateFromRequest("$option.search",'search','');
        $search           = $db->getEscaped(trim(JString::strtolower($search)));

        $limit      = JRequest::getVar('global.list.limit',$mainframe->getCfg('list_limit'),'','int');
        $limitstart = $mainframe->getUserStateFromRequest($option.'limitstart','limitstart',0);

        $where = array();

        if($filter_state)
        {
            if($filter_state == 'P')
                $where[] = 'm.published = 1';
            elseif($filter_state == 'U')
                $where[] = 'm.published = 0';
        }
        if($search)
            $where[] = 'LOWER(m.title) LIKE "%'.$search.'%"';

        $where   = (count($where) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
        $orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;

        //$query = 'SELECT COUNT(m.id) FROM #__jumi as m left join #__groups as g on (m.access = g.id) '.$where;
        $query = 'SELECT COUNT(m.id) FROM #__jumi as m '.$where;
        $db->setQuery($query);
        $total = $db->loadResult();

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total,$limitstart,$limit);

        //$query = 'SELECT m.*, g.name as groupname FROM #__jumi as m left join #__groups as g on (m.access = g.id)  '.$where.' '.$orderby;
        $query = 'SELECT m.*, \'depracated\' as groupname FROM #__jumi as m '.$where.' '.$orderby;
        $db->setQuery($query,$pageNav->limitstart,$pageNav->limit);
        $rows = $db->loadObjectList();

        if($db->getErrorNum())
        {
            echo $db->stderr();
            return false;
        }

        // state filter
        $lists['state'] = JHTML::_('grid.state',$filter_state);

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        // search filter
        $lists['search'] = $search;

        JumiView::showApplications($rows,$pageNav,$option,$lists);
    }

    function editApplication() {
        $db     = &JFactory::getDBO();
        $cid    = JRequest::getVar('cid',array(0),'','array');
        $option = JRequest::getVar('option');
        $uid    = (int) @$cid[0];

        $query = 'SELECT * FROM #__jumi WHERE id = '.$uid;
        $db->setQuery($query);
        $row = $db->loadObject();

        JumiView::editApplication($row);
    }

    function saveApplication() {
        $db     = &JFactory::getDBO();
        $post   = JRequest::get('post');
        $cid    = JRequest::getVar('cid',array(0),'','array');
        $applid = (int) @$cid[0];

        $title         = $db->Quote($post['title']);
        $alias         = $db->Quote($post['alias']);
        $custom_script = $db->Quote(stripslashes($_POST['custom_script']));
        $path          = $db->Quote($post['path']);
        if($applid == 0) {
            $query = "insert into #__jumi (title, alias, custom_script, path) values($title,$alias,$custom_script,$path)";
            $db->setQuery($query);
            $db->query();
        } else {
            $query = "update #__jumi set title = $title, alias = $alias, custom_script = $custom_script, path = $path where id = $applid";
            $db->setQuery($query);
            $db->query();
        }

        switch($this->_task) {
            case 'apply':
                $msg = JText::_('Changes to Application saved');
                $link = 'index.php?option=com_jumi&task=edit&cid[]='. $applid .'';
                break;

            case 'save':
            default:
                $msg = JText::_('Application saved');
                $link = 'index.php?option=com_jumi';
                break;
        }

        $this->setRedirect($link,$msg);
    }

    function removeApplication() {
        $db      = &JFactory::getDBO();
        $cid    = JRequest::getVar( 'cid', array(), '', 'array' );
        $option = JRequest::getVar( 'option', 'com_jumi', '', 'string' );
        $msg = '';

        for($i=0, $n=count($cid); $i < $n; $i++) {
            $msg .= "Application with id: $cid[$i] successfully deleted";
            $query = "delete from #__jumi where id = $cid[$i]";
            $db->setQuery($query);
            $db->query();
        }

        $this->setRedirect('index.php?option='.$option,$msg);
    }

    function publishApplications() {
        $mainframe =& JFactory::getApplication();

        $db      = &JFactory::getDBO();
        $cid     = JRequest::getVar( 'cid', array(), '', 'array' );
        $publish = JRequest::getCmd('task') == 'publish' ? 1 : 0;
        $option  = JRequest::getVar( 'option', 'com_jumi', '', 'string' );
        $catid   = JRequest::getVar( 'catid', array(0), 'post', 'array' );

        if(count($cid) < 1) {
            $action = $publish ? 'publish' : 'unpublish';
            JError::raiseError(500,JText::_( 'Select an item to '.$action,true));
        }

        JArrayHelper::toInteger($cid);
        $cids = implode(',',$cid);

        $query = "UPDATE #__jumi SET published = ".intval($publish)." WHERE id in ($cids)";
        $db->setQuery($query);
        if (!$db->query())
            JError::raiseError(500, $db->getErrorMsg());

        $mainframe->redirect( 'index.php?option='. $option );
    }

    function accessApplication($access) {
        $mainframe =& JFactory::getApplication();

        $db     = &JFactory::getDBO();
        $option = JRequest::getVar( 'option', 'com_jumi', '', 'string' );
        $cid    = JRequest::getVar('cid',array(0),'','array');
        $applid = (int) @$cid[0];

        $query = "update #__jumi set access=$access where id=$applid";
        $db->setQuery($query);
        if (!$db->query())
            JError::raiseError(500, $db->getErrorMsg());

        $mainframe->redirect( 'index.php?option='. $option );
    }
}


class JumiView {

    function showApplications(&$rows,&$pageNav,$option,&$lists) {
        JHTML::_('behavior.tooltip');
        ?>
        <form action="index.php?option=com_jumi" method="post" name="adminForm">

        <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_( 'Filter' ); ?>:
                <input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
                <button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
            </td>
            <td nowrap="nowrap">
                <?php
                echo $lists['state'];
                ?>
            </td>
        </tr>
        </table>

        <div id="tablecell">
            <table class="adminlist">
            <thead>
                <tr>
                    <th width="1%">
                        <?php echo JText::_( 'NUM' ); ?>
                    </th>
                    <th width="2%">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
                    </th>
                    <th class="title">
                        <?php echo JHTML::_('grid.sort', 'Title', 'm.title', @$lists['order_Dir'], @$lists['order']); ?>
                    </th>
                    <th width="30%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Path', 'm.path', @$lists['order_Dir'], @$lists['order']); ?>
                    </th>
                    <th width="8%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Published', 'm.published', @$lists['order_Dir'], @$lists['order']); ?>
                    </th>
                    <!--th width="12%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Access', 'g.name', @$lists['order_Dir'], @$lists['order']); ?>
                    </th-->
                    <th width="1%" nowrap="nowrap">
                        <?php echo JHTML::_('grid.sort', 'ID', 'm.id', @$lists['order_Dir'], @$lists['order']); ?>
                    </th>
                </tr>
            </thead>
            <?php
            $k = 0;
            for ($i=0, $n=count( $rows ); $i < $n; $i++) {
                $row = &$rows[$i];

                $link        = JRoute::_('index.php?option=com_jumi&task=edit&cid[]='. $row->id);
                $checked     = JHTML::_('grid.checkedout',$row,$i);
                //$published   = JHTML::_('grid.published',$row,$i);
                $published   = JHtml::_('jgrid.published', $row->published, $i);
                $accesslevel = JHTML::_('grid.access',$row,$i);
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td>
                        <?php echo $pageNav->getRowOffset( $i ); ?>
                    </td>
                    <td>
                        <?php echo $checked; ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Application' ); ?>">
                            <?php echo $row->title; ?></a>
                    </td>
                    <td align="center">
                        <?php echo $row->path; ?>
                    </td>
                    <td align="center">
                        <?php echo $published; ?>
                    </td>
                    <!--td align="center">
                        <?php echo $accesslevel; ?>
                    </td-->
                    <td align="center">
                        <?php echo $row->id; ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
            <tfoot>
                <td colspan="8">
                    <?php echo $pageNav->getListFooter(); ?>
                </td>
            </tfoot>
            </table>
        </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="" />
        </form>
        <?php
    }

    function editApplication(&$row) {
        JRequest::setVar('hidemainmenu',1);

        jimport('joomla.filter.output');
        JFilterOutput::objectHTMLSafe($row,ENT_QUOTES);
        ?>
        <script language="javascript" type="text/javascript">
        function submitbutton(pressbutton) {
            var form = document.adminForm;
            if(pressbutton == 'cancel') {
                submitform(pressbutton);
                return;
            }

            // validation
            if(form.title.value == "")
                alert( "<?php echo JText::_('COM_JUMI_NEEDTITLE', true ); ?>" );
            else if(form.custom_script.value == "" && form.path.value == "")
                alert( "<?php echo JText::_('COM_JUMI_NEEDSCRIPT', true ); ?>" );
            else
                submitform(pressbutton);
        }
        </script>
        <form action="index.php" method="post" name="adminForm">

        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_JUMI_DETAILS'); ?></legend>

            <table class="admintable">
            <tr>
                <td width="200" class="key">
                    <label for="title">
                        <?php echo JText::_( 'COM_JUMI_TITLE' ); ?>:
                    </label>
                </td>
                <td>
                    <input class="inputbox" type="text" name="title" id="title" size="60" value="<?php echo @$row->title; ?>" />
                </td>
            </tr>
            <tr>
                <td width="200" class="key">
                    <label for="alias">
                        <?php echo JText::_( 'Alias' ); ?>:
                    </label>
                </td>
                <td>
                    <input class="inputbox" type="text" name="alias" id="alias" size="60" value="<?php echo @$row->alias; ?>" />
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="custom_script">
                        <?php echo JHTML::_('tooltip', JTEXT::_('COM_JUMI_CUSTOMSCRIPT')); ?> <?php echo JText::_( 'COM_JUMI_CUSTOM_SCRIPT' ); ?>:
                    </label>
                </td>
                <td>
                    <p><textarea name="custom_script" id="custom_script" cols="80" rows="10"><?php echo @$row->custom_script; ?></textarea></p>
                </td>
            </tr>
            <tr>
                <td class="key">

                    <label for="path">
                        <?php echo JHTML::_('tooltip', JTEXT::_('COM_JUMI_INCLFILE')); ?> <?php echo JText::_( 'COM_JUMI_PATHNAME' ); ?>:
                    </label>
                </td>
                <td>
                    <input class="inputbox" type="text" name="path" id="path" size="60" value="<?php echo @$row->path; ?>" />
                </td>
            </tr>
            </table>
        </fieldset>

        <div class="clr"></div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="option" value="com_jumi" />
        <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
        <input type="hidden" name="cid[]" value="<?php echo $row->id; ?>" />
        <input type="hidden" name="textfieldcheck" value="<?php echo @$n; ?>" />
        </form>
        <?php
    }
}

$controller = new JumiController(array('default_task' => 'showApplications'));

$task = JRequest::getVar('task');
switch (strtolower($task)) {
    case 'accesspublic' :
        JumiController::accessApplication(0);
        break;
    case 'accessregistered' :
        JumiController::accessApplication(1);
        break;
    case 'accessspecial' :
        JumiController::accessApplication(2);
        break;
    default:
        $controller->execute(JRequest::getVar('task'));
}

$controller->redirect();