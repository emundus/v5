<?php
/**
 * @package    Joomla
 * @subpackage emundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
 
class EmundusViewEmail extends JView{
	var $_user = null;
	var $_db = null;
	
	function __construct($config = array()){
		// require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
    function display($tpl = null){	
	
        $document = JFactory::getDocument();
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );

        $menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		
		if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id)) die("ACCESS_DENIED");
		
		//$aid = JRequest::getVar('sid', null, 'GET', 'none', 0);
		//$student = JFactory::getUser($aid);

		if(EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id)) {
			if($this->_user->profile!=16){
				$options = array('applicants');
				$email_applicant = EmundusHelperEmails::createEmailBlock($options);
				unset($options);
			}
		}
		else $email_applicant = '';
		$this->assignRef('email', $email_applicant);

		//var_dump($logged);
        parent::display($tpl);
    }
}
?>