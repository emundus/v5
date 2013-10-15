<?php
/**
 * Profile Model for eMundus Component
 * 
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelProfile extends JModel
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
		$this->_db = JFactory::getDBO();
	}

	/**
	* Gets the greeting
	* @return string The greeting to be displayed to the user
	*/
	function getProfile($p)
	{
		$query = 'SELECT * FROM #__emundus_setup_profiles WHERE id='.mysql_real_escape_string($p);
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	function getProfileByApplicant($aid)
	{
		$query = 'SELECT eu.firstname, eu.lastname, eu.profile, eu.university_id, 
							esp.label AS profile_label, esp.menutype, esp.published
						FROM #__emundus_users AS eu 
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile 
						WHERE eu.user_id = '.$aid;
		$this->_db->setQuery( $query );
		return $this->_db->loadAssoc();
	}
	
	function getAttachments($p)
	{
		$query = 'SELECT attachment.id, attachment.value, profile.id AS selected, profile.displayed, profile.mandatory, profile.bank_needed 
					FROM #__emundus_setup_attachments AS attachment
					LEFT JOIN #__emundus_setup_attachment_profiles AS profile ON profile.attachment_id = attachment.id AND profile.profile_id='.mysql_real_escape_string($p).' 
					ORDER BY attachment.ordering';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getForms($p)
	{
		$query = 'SELECT fbtable.id, fbtable.label, menu.id>0 AS selected, menu.ordering AS `order` FROM #__fabrik_lists AS fbtable 
					LEFT JOIN #__menu AS menu ON fbtable.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					AND menu.menutype=(SELECT profile.menutype FROM #__emundus_setup_profiles AS profile WHERE profile.id = '.mysql_real_escape_string($p).')
					WHERE fbtable.created_by_alias = "form" ORDER BY selected DESC, menu.ordering ASC, fbtable.label ASC';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function isProfileUserSet($uid) {
		$query = 'SELECT count(user_id) as cpt, profile FROM #__emundus_users WHERE user_id = '.$uid. ' GROUP BY user_id';
		$this->_db->setQuery( $query );
		$res = $this->_db->loadAssocList();

		return $res[0];
	}

	function updateProfile($uid, $campaign) {
		$query = 'UPDATE #__emundus_users SET profile='.$campaign->profile_id.', schoolyear="'.$campaign->year.'" WHERE user_id='.$uid;
		$this->_db->setQuery( $query ); 
		return $this->_db->query();
	}

	function getCurrentCampaignByApplicant($uid) {
		$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id = '.$uid. ' ORDER BY date_time DESC';
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult();

		return $res;
	}

	function getCurrentIncompleteCampaignByApplicant($uid) {
		$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE submitted=0 AND applicant_id = '.$uid. ' ORDER BY date_time DESC';
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult();

		return $res;
	}

	function getCurrentCompleteCampaignByApplicant($uid) {
		$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE submitted=1 AND applicant_id = '.$uid. ' ORDER BY date_time DESC';
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult();

		return $res;
	}

	function getCurrentCampaignInfoByApplicant($uid) {
		$query = 'SELECT esc.*, ecc.date_time, ecc.submitted, ecc.date_submitted 
					FROM #__emundus_campaign_candidature AS ecc 
					LEFT JOIN #__emundus_setup_campaigns AS esc ON ecc.campaign_id = esc.id
					WHERE ecc.applicant_id = '.$uid. ' ORDER BY ecc.date_time DESC';
		$this->_db->setQuery( $query );
		$res = $this->_db->loadAssoc();

		return $res;
	}

	function getCampaignById($id) {
		$query = 'SELECT * FROM  #__emundus_setup_campaigns AS esc WHERE id='.$id;
		$this->_db->setQuery( $query );
		$res = $this->_db->loadAssoc();

		return $res;
	}

	function isApplicationDeclared($aid) {
		$query = 'SELECT COUNT(*) FROM #__emundus_declaration WHERE user = '.$aid;
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult();
		return $res>0?true:false;
	}
}
?>