<?php
/**
 * Learning Agreement Model for eMundus Component
 * 
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 * @author     Decision Publique - Benjamin Rivalland
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
class EmundusModelLearningAgreement extends JModel
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
	
	function getTeachingUnity()
	{
		$query = 'SELECT estu.id, estu.code, estu.label, estu.university_id, c.title as university, estu.schoolyear, estu.semester, estu.ects, estu.notes 
			FROM #__emundus_setup_teaching_unity AS estu
			LEFT JOIN #__categories AS c ON c.id=estu.university_id 
			WHERE estu.published=1
			ORDER BY estu.university_id, estu.label'; 
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}
	
	function getLearningAgreementSatus()
	{
		$query = 'SELECT id, user_id, teacher_id, status FROM #__emundus_learning_agreement_status';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList('user_id');
	}
	
	function getPersonneInCharge()
	{
		$current_user = JFactory::getUser();
		$student_id = JRequest::getVar('student_id', null, 'GET', 'none',0);
		$query = 'SELECT count(id) FROM #__emundus_confirmed_applicants WHERE user_id='.$student_id.' AND 	evaluator_id='.$current_user->id;
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}
}
?>