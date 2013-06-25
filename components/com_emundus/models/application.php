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
	var $_total = null;
	var $_pagination = null;
	protected $data;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		
	}
	
	function getUserInformations($id, $params){
		$select='';
		$informations = array('lastname'		=> 'eu.lastname',
							'firstname'		=> 'eu.firstname',
							'gender'		=> 'epd.gender',
							'email'			=> 'u.email',
							'nationality'	=> 'epd.nationality',
							'birthdate'		=> 'epd.birth_date as birthdate',
							'profile'		=> 'esp.label as profile',
							'photo'		=> 'eup.filename',
							'registerDate'		=> 'eu.registerDate'
							);
		$last = end($params);
		foreach($params as $param){
			foreach($informations as $nameI=>$valueI){
				if($param==$nameI){
					if($param==$last){
						$select.= $valueI;
					}else{
						$select.= $valueI.', ';
					}
				}
			}
		}
		
		$db = JFactory::getDBO();
		$query = 'SELECT '.$select.' 
		FROM #__users u 
		LEFT JOIN #__emundus_users eu ON u.id=eu.user_id 
		LEFT JOIN #__emundus_personal_detail epd ON epd.user=u.id
		LEFT JOIN #__emundus_setup_profiles esp ON esp.id=eu.profile
		LEFT JOIN #__emundus_uploads eup ON eup.user_id=u.id AND eup.filename like "%_photo%"
		WHERE u.id="'.$id.'"';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	function getUserCampaigns($id){
		$db = JFactory::getDBO();
		$query = 'SELECT esc.label, esc.year, ecc.date_submitted, efg.result_sent, efg.date_result_sent 
			FROM #__emundus_users eu
			LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
			LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
			LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
			WHERE eu.user_id="'.$id.'"';
			// echo str_replace ('#_', 'jos', $query);
			$db->setQuery( $query );
			return $db->loadObjectList();
	}
	
}
?>