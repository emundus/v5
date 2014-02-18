﻿<?php
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
		$this->_db = JFactory::getDBO();
	}
}