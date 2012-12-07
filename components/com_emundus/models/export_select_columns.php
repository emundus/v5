<?php
/**
 * Profile Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelExport_select_columns extends JModel {
	var $_db = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct(){
		parent::__construct();
		$this->_db =& JFactory::getDBO();
	}
	
	function getElements(){
		$query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.id, element.label AS element_label, element.plugin AS element_plugin, groupe.id as group_id, groupe.label AS group_label,  
			INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label, tab.created_by_alias
				FROM #__fabrik_elements element 
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id 
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id 
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id 
				INNER JOIN #__menu AS menu ON tab.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
				WHERE tab.published = 1 
				AND (tab.created_by_alias = "form" OR tab.created_by_alias = "comment")
					AND element.published=1 
					AND element.hidden=0 
					AND element.label!=" " 
					AND element.label!=""  
				ORDER BY menu.ordering, formgroup.ordering, groupe.id, element.ordering'; 
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
}