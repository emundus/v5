<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.link
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render two fields to capture a link (url/label)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.link
 * @since       3.0
 */

class plgFabrik_ElementLink extends plgFabrik_Element
{

	public $hasSubElements = true;

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$listModel = $this->getlistModel();
		$params = $this->getParams();
		$target = $params->get('link_target', '');
		$smart_link = $params->get('link_smart_link', false);
		if ($listModel->getOutPutFormat() != 'rss' && ($smart_link || $target == 'mediabox'))
		{
			FabrikHelperHTML::slimbox();
		}
		$data = FabrikWorker::JSONtoData($data, true);

		if (!empty($data))
		{
			if (array_key_exists('label', $data))
			{
				$data = (array) $this->_renderListData($data, $thisRow);
			}
			else
			{
				for ($i = 0; $i < count($data); $i++)
				{
					$data[$i] = JArrayHelper::fromObject($data[$i]);
					$data[$i] = $this->_renderListData($data[$i], $thisRow);
				}
			}
		}
		$data = json_encode($data);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Redinder Individual parts of the cell data.
	 * Called from renderListData();
	 *
	 * @param   string  $data     cell data
	 * @param   object  $thisRow  the data in the lists current row
	 *
	 * @return  string  formatted value
	 */

	protected function _renderListData($data, $thisRow)
	{
		if (is_string($data))
		{
			$data = FabrikWorker::JSONtoData($data, true);
		}
		$listModel = $this->getlistModel();
		$params = $this->getParams();
		if (is_array($data))
		{
			if (count($data) == 1)
			{
				$data['label'] = JArrayHelper::getValue($data, 'link');
			}
			$_lnk = trim($data['link']);
			$_lbl = trim($data['label']);
			if (JString::strtolower($_lnk) == 'http://' || JString::strtolower($_lnk) == 'https://')
			{
				// Treat some default values as empty
				$_lnk = '';
			}
			// If used as a icon - the dom parser needs to use &amp; and not & in url querystrings
			if (!strstr($_lnk, '&amp;'))
			{
				$_lnk = str_replace('&', '&amp;', $_lnk);
			}
			$target = $params->get('link_target', '');
			if ($listModel->getOutPutFormat() != 'rss')
			{
				$link = '';
				if (empty($_lbl))
				{
					// If label is empty, set as a copy of the link
					$_lbl = $_lnk;
				}
				if ((!empty($_lbl)) && (!empty($_lnk)))
				{
					$smart_link = $params->get('link_smart_link', false);
					if ($smart_link || $target == 'mediabox')
					{
						$smarts = $this->_getSmartLinkType($_lnk);
						$link = '<a href="' . $_lnk . '" rel="lightbox[' . $smarts['type'] . ' ' . $smarts['width'] . ' ' . $smarts['height'] . ']">'
							. $_lbl . '</a>';
					}
					else
					{
						$link = '<a href="' . $_lnk . '" target="' . $target . '">' . $_lbl . '</a>';
					}
				}
			}
			else
			{
				$link = $_lnk;
			}
			$w = new FabrikWorker;
			$aRow = JArrayHelper::fromObject($thisRow);
			$link = $listModel->parseMessageForRowHolder($link, $aRow);
			return $link;
		}
		return $data;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$bits = $this->inputProperties($repeatCounter);
		$value = $this->getValue($data, $repeatCounter);
		if ($value == "")
		{
			$value = array('label' => '', 'link' => '');
		}
		else
		{
			if (!is_array($value))
			{
				$value = FabrikWorker::JSONtoData($value, true);
				if (array_key_exists(0, $value))
				{
					$value['label'] = $value[0];
				}
			}
		}

		if (count($value) == 0)
		{
			$value = array('label' => '', 'link' => '');
		}

		if (FabrikWorker::getMenuOrRequestVar('rowid') == 0)
		{
			$value['link'] = $params->get('link_default_url');
		}
		if (!$this->isEditable())
		{
			$_lbl = trim($value['label']);
			$_lnk = trim($value['link']);
			$w = new FabrikWorker;
			$_lnk = is_array($data) ? $w->parseMessageForPlaceHolder($_lnk, $data) : $w->parseMessageForPlaceHolder($_lnk);
			if (empty($_lnk) || JString::strtolower($_lnk) == 'http://' || JString::strtolower($_lnk) == 'https://')
			{
				// Don't return empty links
				return '';
			}
			$target = $params->get('link_target', '');
			$smart_link = $params->get('link_smart_link', false);
			if (empty($_lbl))
			{
				// If label is empty, set as a copy of the link
				$_lbl = $_lnk;
			}
			if ($smart_link || $target == 'mediabox')
			{
				$smarts = $this->_getSmartLinkType($_lnk);
				return '<a href="' . $_lnk . '" rel="lightbox[' . $smarts['type'] . ' ' . $smarts['width'] . ' ' . $smarts['height'] . ']">' . $_lbl
					. '</a>';
			}
			return '<a href="' . $_lnk . '" target="' . $target . '">' . $_lbl . '</a>';
		}

		$labelname = FabrikString::rtrimword($name, "[]") . '[label]';
		$linkname = FabrikString::rtrimword($name, "[]") . '[link]';

		$html = array();
		$bits['name'] = $labelname;
		$bits['placeholder'] = JText::_('PLG_ELEMENT_LINK_LABEL');
		$bits['value'] = $value['label'];
		$bits['class'] .= ' fabrikSubElement';
		unset($bits['id']);

		$html[] = '<div class="fabrikSubElementContainer" id="' . $id . '">';
		$html[] = $this->buildInput('input', $bits);
		$bits['placeholder'] = JText::_('PLG_ELEMENT_LINK_URL');
		$bits['name'] = $linkname;
		$bits['value'] = JArrayHelper::getValue($value, 'link');
		$html[] = $this->buildInput('input', $bits);
		$html[] = '</div>';
		return implode("\n", $html);
	}

	/**
	 * Turn form value into email formatted value
	 *
	 * @param   mixed  $value          Element value
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  Group repeat counter
	 *
	 * @return  string  email formatted value
	 */

	protected function _getEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		if (is_string($value))
		{
			$value = FabrikWorker::JSONtoData($value, true);
			$value['label'] = JArrayHelper::getValue($value, 0);
			$value['link'] = JArrayHelper::getValue($value, 1);
		}
		if (is_array($value))
		{
			$w = new FabrikWorker;
			$link = $w->parseMessageForPlaceHolder($value['link']);
			$value = '<a href="' . $link . '" >' . $value['label'] . '</a>';
		}
		return $value;
	}

	/**
	 * Manupulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   this elements posted form data
	 * @param   array  $data  posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		/* $$$ hugh - added 'normalization' of links, to add http:// if no :// in the link.
		* not sure if we really want to do it here, or only when rendering?
		* $$$ hugh - quit normalizing links.
		*/
		$return = '';
		$params = $this->getParams();
		if (is_array($val))
		{
			if ($params->get('use_bitly'))
			{
				require_once JPATH_SITE . '/components/com_fabrik/libs/bitly/bitly.php';
				$login = $params->get('bitly_login');
				$key = $params->get('bitly_apikey');
				$bitly = new bitly($login, $key);
			}
			foreach ($val as $key => &$v)
			{
				if (is_array($v))
				{
					if ($params->get('use_bitly'))
					{
						/* bitly will return an error if you try and shorten a shortened link,
						* and the class file we are using doesn't check for this
						*/
						if (!strstr($v['link'], 'bit.ly/') && $v['link'] !== '')
						{
							$v['link'] = $bitly->shorten($v['link']);
						}
					}
					/*$return .= implode(GROUPSPLITTER2, $v);
					$return .= GROUPSPLITTER;*/

				}
				else
				{
					if ($key == 'link')
					{
						$v = FabrikString::encodeurl($v);
					}
					// Not in repeat group
					if ($key == 'link' && $params->get('use_bitly'))
					{
						if (!strstr($v, 'bit.ly/') && $v !== '')
						{
							$v = $bitly->shorten($v);
						}
					}
				}
			}
		}
		else
		{
			if (json_decode($val))
			{
				return $val;
			}
			$return = $val;
		}
		$return = json_encode($val);
		return $return;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$listModel = $this->getlistModel();
		$params = $this->getParams();
		$target = $params->get('link_target', '');
		$smart_link = $params->get('link_smart_link', false);
		if ($listModel->getOutPutFormat() != 'rss' && ($smart_link || $target == 'mediabox'))
		{
			FabrikHelperHTML::slimbox();
		}
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbLink('$id', $opts)";
	}

	/**
	 * Called by form model to build an array of values to encrypt
	 *
	 * @param   array  &$values  previously encrypted values
	 * @param   array  $data     form data
	 * @param   int    $c        repeat group counter
	 *
	 * @return  void
	 */

	public function getValuesToEncrypt(&$values, $data, $c)
	{
		$data = (array) json_decode($this->getValue($data, $c, true));
		$name = $this->getFullName(false, true, false);
		$group = $this->getGroup();
		if ($group->canRepeat())
		{
			// $$$ rob - I've not actually tested this bit!
			if (!array_key_exists($name, $values))
			{
				$values[$name]['data']['label'] = array();
				$values[$name]['data']['link'] = array();
			}
			$values[$name]['data']['label'][$c] =  JArrayHelper::getValue($data, 'label');
			$values[$name]['data']['link'][$c] = JArrayHelper::getValue($data, 'link');
		}
		else
		{
			$values[$name]['data']['label'] = JArrayHelper::getValue($data, 'label');
			$values[$name]['data']['link'] = JArrayHelper::getValue($data, 'link');
		}
	}

	/**
	 * This really does get just the default value (as defined in the element's settings)
	 *
	 * @param   array  $data  form data
	 *
	 * @return mixed
	 */

	public function getDefaultValue($data = array())
	{
		if (!isset($this->_default))
		{
			$w = new FabrikWorker;
			$params = $this->getParams();
			$link = $params->get('link_default_url');
			/* $$$ hugh - no idea what this was here for, but it was causing some BIZARRE bugs!
			*$formdata = $this->getForm()->getData();
			* $$$ rob only parse for place holder if we can use the element
			* otherwise for encrypted values store raw, and they are parsed when the
			* form in processsed in form::addEncrytedVarsToArray();
			*/
			if ($this->canUse())
			{
				$link = $w->parseMessageForPlaceHolder($link, $data);
			}
			$element = $this->getElement();
			$default = $w->parseMessageForPlaceHolder($element->default, $data);
			if ($element->eval == "1")
			{
				$default = @eval(stripslashes($default));
			}
			$this->_default = array('label' => $default, 'link' => $link);
		}
		return $this->_default;
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  when repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           options
	 *
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		if (!isset($this->defaults))
		{
			$this->defaults = array();
		}
		if (!array_key_exists($repeatCounter, $this->defaults))
		{
			$groupModel = $this->getGroup();
			$group = $groupModel->getGroup();
			$joinid = $group->join_id;
			$formModel = $this->getFormModel();
			$element = $this->getElement();

			$default = $this->getDefaultOnACL($data, $opts);
			$name = $this->getFullName(false, true, false);

			if ($groupModel->isJoin())
			{
				if ($groupModel->canRepeat())
				{
					if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid])
						&& array_key_exists($name, $data['join'][$joinid]) && array_key_exists($repeatCounter, $data['join'][$joinid][$name]))
					{
						$default = $data['join'][$joinid][$name][$repeatCounter];
					}
				}
				else
				{
					if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid])
						&& array_key_exists($name, $data['join'][$joinid]))
					{
						$default = $data['join'][$joinid][$name];
					}
				}
			}
			else
			{
				if ($groupModel->canRepeat())
				{
					// Repeat group NO join
					if (array_key_exists($name, $data))
					{
						if (is_array($data[$name]))
						{
							// Occurs on form submission for fields at least
							$a = $data[$name];
						}
						else
						{
							// Occurs when getting from the db
							$a = json_decode($data[$name]);
						}
						$default = JArrayHelper::getValue($a, $repeatCounter, $default);
					}

				}
				else
				{
					if (array_key_exists($name, $data))
					{
						$default = JArrayHelper::getValue($data, $name);
					}
				}
			}
			if ($default === '')
			{
				// Query string for joined data
				$default = JArrayHelper::getValue($data, $name);
			}
			$element->default = $default;

			// Stops this getting called from form validation code as it messes up repeated/join group validations
			if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1)
			{
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}
			if (is_array($element->default))
			{
				$element->default = json_encode($element->default);
			}
			$this->defaults[$repeatCounter] = $element->default;

		}
		return $this->defaults[$repeatCounter];
	}

	/**
	 * Get an array containing info about the media link
	 *
	 * @param   string  $link  to examine
	 *
	 * @return  array width, height, type of link
	 */

	protected function _getSmartLinkType($link)
	{
		/* $$$ hugh - not really sure how much of this is necessary, like setting different widths
		 * and heights for different social video sites. I copied the numbers from the examples page
		 * for mediabox: http://iaian7.com/webcode/mediaboxAdvanced
		 */
		$ret = array('width' => '800', 'height' => '600', 'type' => 'mediabox');
		if (preg_match('#^http://([\w\.]+)/#', $link, $matches))
		{
			$site = $matches[1];
			/*
			 * @TODO should probably make this a little more intelligent, like optional www,
			 * and check for site specific spoor in the URL (like '/videoplay' for google,
			 * '/photos' for flicker, etc).
			 */
			switch ($site)
			{
				case 'www.flickr.com':
					$ret['width'] = '400';
					$ret['height'] = '300';
					$ret['type'] = 'social';
					break;
				case 'video.google.com':
					$ret['width'] = '640';
					$ret['height'] = '400';
					$ret['type'] = 'social';
					break;
				case 'www.metacafe.com':
					$ret['width'] = '400';
					$ret['height'] = '350';
					$ret['type'] = 'social';
					break;
				case 'vids.myspace.com':
					$ret['width'] = '430';
					$ret['height'] = '346';
					$ret['type'] = 'social';
					break;
				case 'myspacetv.com':
					$ret['width'] = '430';
					$ret['height'] = '346';
					$ret['type'] = 'social';
					break;
				case 'www.revver.com':
					$ret['width'] = '480';
					$ret['height'] = '392';
					$ret['type'] = 'social';
					break;
				case 'www.seesmic.com':
					$ret['width'] = '425';
					$ret['height'] = '353';
					$ret['type'] = 'social';
					break;
				case 'www.youtube.com':
					$ret['width'] = '480';
					$ret['height'] = '380';
					$ret['type'] = 'social';
					break;
				case 'www.veoh.com':
					$ret['width'] = '540';
					$ret['height'] = '438';
					$ret['type'] = 'social';
					break;
				case 'www.viddler.com':
					$ret['width'] = '437';
					$ret['height'] = '370';
					$ret['type'] = 'social';
					break;
				case 'vimeo.com':
					$ret['width'] = '400';
					$ret['height'] = '302';
					$ret['type'] = 'social';
					break;
				case '12seconds.tv':
					$ret['width'] = '431';
					$ret['height'] = '359';
					$ret['type'] = 'social';
					break;
			}
			if ($ret['type'] == 'mediabox')
			{
				$ext = JString::strtolower(JFile::getExt($link));
				switch ($ext)
				{
					case 'swf':
					case 'flv':
					case 'mp4':
						$ret['width'] = '640';
						$ret['height'] = '360';
						$ret['type'] = 'flash';
						break;
					case 'mp3':
						$ret['width'] = '400';
						$ret['height'] = '20';
						$ret['type'] = 'audio';
						break;
				}
			}
		}
		return $ret;
	}

	/**
	 * Does the element conside the data to be empty
	 * Used in isempty validation rule
	 *
	 * @param   array  $data           data to test against
	 * @param   int    $repeatCounter  repeat group #
	 *
	 * @return  bool
	 */

	public function dataConsideredEmpty($data, $repeatCounter)
	{
		$data = strip_tags($data);
		if (trim($data) == '' || $data == '<a target="_self" href=""></a>')
		{
			return true;
		}
		return false;
	}

	/**
	 * get the class to manage the form element
	 * if a plugin class requires to load another elements class (eg user for dbjoin then it should
	 * call FabrikModelElement::formJavascriptClass('plugins/fabrik_element/databasejoin/databasejoin.js', true);
	 * to ensure that the file is loaded only once
	 *
	 * @param   array   &$srcs   scripts previously loaded (load order is important as we are loading via head.js
	 * and in ie these load async. So if you this class extends another you need to insert its location in $srcs above the
	 * current file
	 * @param   string  $script  script to load once class has loaded
	 *
	 * @return void
	 */

	public function formJavascriptClass(&$srcs, $script = '')
	{
		// Whilst link isnt really an element list we can use its js AddNewEvent method
		$elementList = 'media/com_fabrik/js/elementlist.js';
		if (!in_array($elementList, $srcs))
		{
			$srcs[] = $elementList;
		}
		parent::formJavascriptClass($srcs, $script);
	}

}
