<?php
/**
 * Users Model for eMundus World Component
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
 
class EmundusModelApplication extends JModel
{
	var $_user = null;
	var $_db = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		global $option;
		
		$mainframe = JFactory::getApplication();
		
		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getUser();		
	}
	
	function getApplicantInfos($aid, $param){
		$query = 'SELECT '.implode(",", $param).' 
				FROM #__users
				LEFT JOIN #__emundus_users ON #__emundus_users.user_id=#__users.id
				LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=#__users.id
				LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
				LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=#__users.id AND #__emundus_uploads.attachment_id=10)
				WHERE #__users.id='.$aid;
		$this->_db->setQuery( $query );
		$infos =  $this->_db->loadAssoc();
//echo str_replace("#_", "jos", $query);
//var_dump($infos);
		return $infos;
	}

	function getApplicantDetails($aid, $ids){
		$details = EmundusHelperList::getElementsDetailsByID($ids);

		foreach ($details as $detail) {
			$select[] = $detail->tab_name.'.'.$detail->element_name.' AS "'.$detail->element_id.'"';
		}

		$query = 'SELECT '.implode(",", $select).' 
				FROM #__users u 
				LEFT JOIN #__emundus_users ON #__emundus_users.user_id=u.id
				LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=u.id
				LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
				LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=u.id AND #__emundus_uploads.attachment_id=10)
				WHERE u.id='.$aid;
		$this->_db->setQuery( $query );
		$values =  $this->_db->loadAssoc();

		foreach ($details as $detail) {
			$detail->element_value = $values[$detail->element_id];
		}
//var_dump($details);
		return $details;
	}
	
	function getUserCampaigns($id){
		$db = JFactory::getDBO();
		$query = 'SELECT esc.label, esc.year, ecc.date_submitted, ecc.submitted, efg.result_sent, efg.date_result_sent 
			FROM #__emundus_users eu
			LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
			LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
			LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
			WHERE eu.user_id="'.$id.'"';
			// echo str_replace ('#_', 'jos', $query);
			$db->setQuery( $query );
			return $db->loadObjectList();
	}
	
	function getUserAttachments($id){
		$db = JFactory::getDBO();
		$query = 'SELECT upload.id AS aid, attachment.id, upload.filename, upload.description, attachment.value, upload.timedate, campaign.label as campaign_label, campaign.year  
            FROM #__emundus_uploads AS upload
            LEFT JOIN #__emundus_setup_attachments AS attachment ON  upload.attachment_id=attachment.id
			LEFT JOIN #__emundus_setup_campaigns AS campaign ON campaign.id=upload.campaign_id
            WHERE upload.user_id = '.$id;'
            ORDER BY attachment.ordering';
        $db->setQuery( $query );
        return $db->loadObjectList();
	}
	
	function getUsersComments($id){ 
		$db = JFactory::getDBO();
		$query = 'SELECT ec.id, ec.comment, ec.reason, ec.date, u.name
				FROM #__emundus_comments ec 
				LEFT JOIN #__users u ON u.id = ec.user_id 
				WHERE ec.applicant_id ='.$id.'
				ORDER BY ec.date DESC';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function deleteComment($id){ 
		$query = 'DELETE #__emundus_comments WHERE id = '.$id;
		$this->_db->setQuery($query);
		$this->_db->Query() or die($this->_db->getErrorMsg());
	}
	
}
?>