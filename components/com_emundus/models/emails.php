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
 
class EmundusModelEmails extends JModel
{
	var $_db = null;
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		$this->_db =& JFactory::getDBO();
	}

	/**
	* Replace specifics tags in a string
	* @return string The body message with tag replacement
	*/
	function getEmail($lbl)
	{
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE lbl="'.mysql_real_escape_string($lbl).'"';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}
	
	function setBody($user, $str, $passwd='')
	{
		$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/','/\n/', '/\[USERNAME\]/', '/\[PASSWORD\]/', '/\[ACTIVATION_URL\]/', '/\[SITE_URL\]/');
		$replacements = array ($user->id, $user->name, $user->email, '<br />', $user->username, $passwd, JURI::base()."index.php?option=com_extendeduser&task=activate&activation=".$user->get('activation'), JURI::base());
		
		$strval = html_entity_decode(preg_replace($patterns, $replacements, $str), ENT_QUOTES);
		
		return $strval;
	}
}
?>