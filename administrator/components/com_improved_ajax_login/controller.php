<?php
/*------------------------------------------------------------------------
# com_improved_ajax_login - Improved AJAX Login & Register
# ------------------------------------------------------------------------
# author    Balint Polgarfi
# copyright Copyright (C) 2012 Offlajn.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.offlajn.com
-------------------------------------------------------------------------*/
?>
<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class ImprovedAjaxLoginController extends JoomlaController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask( 'edit', 'display' );
    // $this->registerTask( 'add',  'display' );
	}

	function display($cachable = false, $urlparams = array())
	{
		switch($this->getTask())
		{
			case 'edit':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view'  , 'oauth');
				JRequest::setVar( 'edit', true );

				$model = $this->getModel('oauth');
			} break;
		}
		parent::display();
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$post	= JRequest::get('post');
		$id	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $id[0];

		$model = $this->getModel('oauth');

		if ($model->store($post)) {
			$msg = JText::_( 'Open Authentication Saved' );
		} else {
			$msg = JText::_( 'Error Saving Open Authentication' );
		}

		$link = 'index.php?option=com_improved_ajax_login&view=oauths';
		$this->setRedirect($link, $msg);
	}

	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$id = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($id);
		if (count( $id ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('oauth');
		if(!$model->publish($id, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_improved_ajax_login&view=oauths' );
	}


	function unpublish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$id = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($id);

		if (count( $id ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('oauth');
		if(!$model->publish($id, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_improved_ajax_login&view=oauths' );
	}

	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$this->setRedirect( 'index.php?option=com_improved_ajax_login&view=oauths' );
	}

}