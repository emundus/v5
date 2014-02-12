<?php
/**
 * Jcrm Model for Jcrm Component
 * 
 * @package    Jcrm
 * @subpackage com_jcrm
 * @license  GNU/GPL v2
 *
 * Created with Marco's Component Creator for Joomla! 1.5
 * http://www.mmleoni.net/joomla-component-builder
 *
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Jcrm Model
 *
 * @package    Joomla.Components
 * @subpackage 	Jcrm
 */
class JcrmControllerJcrmaccountslist extends JcrmController{

	/**
	 * Parameters in config.xml.
	 *
	 * @var	object
	 * @access	protected
	 */
	private $_params = null;

	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct(){
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		
		// Set reference to parameters
		$this->_params = &JComponentHelper::getParams( 'com_jcrm' );
		//$dummy = $this->_params->get('parm_text');
	}

	/**
	 * display the edit form
	 * @return void
	 */
	public function edit(){
		JRequest::setVar( 'view', 'jcrmaccounts' );
		JRequest::setVar( 'layout', 'default'  );
		JRequest::setVar( 'hidemainmenu', 1 );
		
		$view =& $this->getView('jcrmaccounts', 'html');
		// Related table model include [NB: include recordset list model]
		// see http://www.mmleoni.net/joomla-component-builder/create-joomla-extensions-manage-the-back-end-part-2
		// tips: insert file name, not class name
		/*
		$altModel =& $this->getModel('relateTableModelList');
		$view->setModel($altModel);
		*/


		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	public function save(){
		$model = $this->getModel('jcrmaccountslist'); 

		if ($model->store($post)) {
			$msg = JText::_( 'DATA_SAVED' );
		} else {
			$msg = JText::_( 'ERROR_SAVING_DATA' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_jcrm&controller=jcrmaccountslist';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	public function remove(){
		$model = $this->getModel('jcrmaccounts');
		if(!$model->delete()) {
			$msg = JText::_( 'ERROR_ONE_OR_MORE_DATA_COULD_NOT_BE_DELETED' );
		} else {
			$msg = JText::_( 'DATAS_DELETED' );
		}

		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist', $msg );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	public function cancel(){
		$msg = JText::_( 'OPERATION_CANCELLED' );
		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist', $msg );
	}
	
	
	public function publish(){
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'SELECT_AN_ITEM_TO_PUBLISH' ) );
		}
		$cids = implode( ',', $cid );
		$db	=& JFactory::getDBO();
		$query = 'UPDATE #__jcrm_accounts SET published = 1 WHERE `id` IN ( '. $cids.'  )';
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}
		$this->setMessage( JText::sprintf('Fields published', $n ) );
		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist' );
	}

	public function unpublish(){
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'SELECT_AN_ITEM_TO_UNPUBLISH' ) );
		}
		$cids = implode( ',', $cid );
		$db	=& JFactory::getDBO();
		$query = 'UPDATE #__jcrm_accounts SET published = 0 WHERE `id` IN ( '. $cids.'  )';
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}
		$this->setMessage( JText::sprintf( 'Fields unpublished', $n ) );
		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist' );
	}

	/*
	function cancel() {
		$model = $this->getModel( 'jcrmaccounts' );
		$model->checkin();
		$this->setRedirect( 'index.php?option=com_jcrm&view=jcrmaccountslist' );
	}
	*/

	public function orderup(){
		$model = $this->getModel( 'jcrmaccounts' );
		$model->move(-1);
		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist' );
	}

	public function orderdown(){
		$model = $this->getModel( 'jcrmaccounts' );
		$model->move(1);
		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist' );
	}

	public function saveorder(){
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel( 'jcrmaccounts' );
		$model->saveorder($cid, $order);

		$msg = JText::_( 'NEW_ORDERING_SAVED' );
		$this->setRedirect( 'index.php?option=com_jcrm&controller=jcrmaccountslist', $msg );
	}
	
	
}