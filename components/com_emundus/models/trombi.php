<?php
/**
 * Trombi Model for eMundus World Component
 * 
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Jonas Lerebours
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelTrombi extends JModel
{

	function __construct()
	{
		parent::__construct();
	}

	function _buildQuery()
	{
		$schoolyear = JRequest::getVar('rowid', null, '', 'STRING');
		$profile = JRequest::getVar('pid', null, '', 'INT');
		$finalgrade = JRequest::getVar('fg', null, '', 'INT');
		$query = 'SELECT u.id, e.firstname, e.lastname, f.filename, e.schoolyear, d.nationality
					FROM #__users AS u 
					LEFT JOIN #__emundus_users AS e ON u.id = e.user_id
					LEFT JOIN #__emundus_personal_detail AS d ON d.user=u.id
					LEFT JOIN #__emundus_setup_profiles AS s ON s.id = e.profile
					LEFT JOIN #__emundus_uploads AS f ON f.user_id = u.id AND f.attachment_id = '.EMUNDUS_PHOTO_AID.'
					LEFT JOIN #__emundus_final_grade AS g ON g.student_id = u.id
					WHERE u.usertype != "Super Users" ';
		if(!empty($finalgrade) && is_numeric($finalgrade) && $finalgrade>0) $query .= ' AND g.Final_grade = '.mysql_real_escape_string($finalgrade) ;
		if(!empty($profile) && is_numeric($profile) && $profile>0) $query .= ' AND e.profile = '.mysql_real_escape_string($profile);
		if(!empty($schoolyear)) $query .= ' AND e.schoolyear = "'.mysql_real_escape_string($schoolyear).'"';
		return $query;
	} 

	function getUsers()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		return $this->_getList($query);
	} 

	function getSchoolyears()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT DISTINCT schoolyear FROM #__emundus_users ORDER BY schoolyear';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	function getProfiles()
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT id, label FROM #__emundus_setup_profiles ORDER BY label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}
}
?>