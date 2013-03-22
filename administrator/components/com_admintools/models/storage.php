<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id: seoandlink.php 178 2011-02-16 08:43:23Z nikosdion $
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.model');

if(!class_exists('JoomlaCompatModel')) {
	if(interface_exists('JModel')) {
		abstract class JoomlaCompatModel extends JModelLegacy {}
	} else {
		class JoomlaCompatModel extends JModel {}
	}
}

class AdmintoolsModelStorage extends JoomlaCompatModel
{
	/** @var JRegistry */
	private $config = null;
	
	public function __construct($config = array()) {
		parent::__construct($config);
		
		// Check for FOF
		if(!defined('FOF_INCLUDED')) {
			require_once JPATH_ADMINISTRATOR.'/components/com_admintools/fof/include.php';
		}
	}
	
	public function getValue($key, $default = null)
	{
		if(is_null($this->config)) $this->load();
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			return $this->config->get($key, $default);
		} else {
			return $this->config->getValue($key, $default);
		}
	}
	
	public function setValue($key, $value, $save = false)
	{
		if(is_null($this->config)) {
			$this->load();
		}
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$x = $this->config->set($key, $value);
		} else {
			$x = $this->config->setValue($key, $value);
		}
		if($save) $this->save();
		return $x;
	}
	
	public function load()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select($db->quoteName('value'))
			->from($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key').' = '.$db->quote('cparams'));
		$db->setQuery($query);
		$res = $db->loadResult();
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$this->config = new JRegistry();
		} else {
			$this->config = new JRegistry('admintools');
		}
		if(!empty($res)) {
			$res = json_decode($res, true);
			$this->config->loadArray($res);
		}
	}
	
	public function save()
	{
		if(is_null($this->config)) {
			$this->load();
		}
		
		$db = JFactory::getDBO();
		$data = $this->config->toArray();
		$data = json_encode($data);
		
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key').' = '.$db->quote('cparams'));
		$db->setQuery($query);
		$db->execute();
		
		$object = (object)array(
			'key'		=> 'cparams',
			'value'		=> $data
		);
		$db->insertObject('#__admintools_storage', $object);
	}
}