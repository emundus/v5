<?php
/**
 * Fabrik Google Map Viz Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.googlemap
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/visualization.php';
require_once JPATH_SITE . '/components/com_fabrik/helpers/googlemap.php';

/**
 * Fabrik Google Map Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.googlemap
 * @since       3.0
 */

class fabrikModelGooglemap extends FabrikFEModelVisualization
{

	/**
	 * Out put text
	 *
	 * @var array
	 */
	protected $txt = null;

	/**
	 * Arrays (width, height) keyed on image icon
	 *
	 * @var array
	 */
	protected $markerSizes = array();

	/**
	 * Number of Fabrik records parsed
	 *
	 * @var int
	 */
	protected $recordCount = 0;

	/**
	 * Get HTML text
	 *
	 * @return  strng
	 */

	public function getText()
	{
		return $this->txt;
	}

	/**
	 * Build js string to create the map js object
	 *
	 * @return string
	 */

	public function getJs()
	{
		$params = $this->getParams();
		if (!$this->getRequiredFiltersFound() && $params->get('fb_gm_always_show_map', '0') != '1')
		{
			return '';
		}
		$str = "head.ready(function() {";
		$viz = $this->getVisualization();

		$opts = new stdClass;
		$opts->lat = 0;
		$opts->lon = 0;
		$opts->icons = $this->getJSIcons();
		$opts->polyline = $this->getPolyline();
		$opts->id = $viz->id;
		$opts->zoomlevel = (int) $params->get('fb_gm_zoomlevel');
		$opts->scalecontrol = (bool) $params->get('fb_gm_scalecontrol');
		$opts->maptypecontrol = (bool) $params->get('fb_gm_maptypecontrol');
		$opts->overviewcontrol = (bool) $params->get('fb_gm_overviewcontrol');
		$opts->center = $params->get('fb_gm_center');
		if ($opts->center == 'querystring')
		{
			$opts->lat = JRequest::getVar('latitude', '') == '' ? $opts->lat : (float) JRequest::getVar('latitude');
			$opts->lon = JRequest::getVar('longitude', '') == '' ? $opts->lon : (float) JRequest::getVar('longitude');
			$opts->zoomlevel = JRequest::getVar('zoom', '') == '' ? $opts->zoomlevel : JRequest::getVar('zoom');
		}
		$opts->ajax_refresh = (bool) $params->get('fb_gm_ajax_refresh', false);
		$opts->ajax_refresh_center = (bool) $params->get('fb_gm_ajax_refresh_center', true);
		$opts->maptype = $params->get('fb_gm_maptype');
		$opts->clustering = (bool) $params->get('fb_gm_clustering', '0') == '1';
		$opts->cluster_splits = $params->get('fb_gm_cluster_splits');
		$opts->icon_increment = $params->get('fb_gm_cluster_icon_increment');
		$opts->refresh_rate = $params->get('fb_gm_ajax_refresh_rate');
		$opts->use_cookies = (bool) $params->get('fb_gm_use_cookies');
		$opts->container = $this->getContainerId();
		$opts->polylinewidth = (array) $params->get('fb_gm_polyline_width');
		$opts->polylinecolour = (array) $params->get('fb_gm_polyline_colour');
		$usePolygon = (array) $params->get('fb_gm_use_polygon');
		$opts->use_polygon = (bool) JArrayHelper::getValue($usePolygon, 0, true);
		$opts->polygonopacity = $params->get('fb_gm_polygon_fillOpacity', 0.35);
		$opts->polygonfillcolour = (array) $params->get('fb_gm_polygon_fillColor');
		$opts->overlay_urls = (array) $params->get('fb_gm_overlay_urls');
		$opts->overlay_labels = (array) $params->get('fb_gm_overlay_labels');
		$opts->use_overlays = (int) $params->get('fb_gm_use_overlays', '0');
		$opts->use_overlays_sidebar = $opts->use_overlays && (int) $params->get('fb_gm_use_overlays_sidebar', '0');
		$opts->use_groups = (bool) $params->get('fb_gm_group_sidebar', 0);
		$opts->groupTemplates = $this->getGroupTemplates();
		$opts->zoomStyle = (int) $params->get('fb_gm_zoom_control_style', 0);
		$opts->zoom = $params->get('fb_gm_zoom', 1);
		$opts->show_radius = $params->get('fb_gm_use_radius', '1') == '1' ? true : false;
		$opts->radius_defaults = (array) $params->get('fb_gm_radius_default');
		$opts->radius_fill_colors = (array) $params->get('fb_gm_radius_fill_color');
		$opts->styles = FabGoogleMapHelper::styleJs($params);
		$opts = json_encode($opts);
		$ref = $this->getJSRenderContext();
		$str .= "$ref = new FbGoogleMapViz('table_map', $opts)";
		$str .= "\n" . "Fabrik.addBlock('$ref', $ref);";
		$str .= "});\n";
		return $str;
	}

	/**
	 * Set an array of list id's whose data is used inside the visualaziation
	 *
	 * @return  void
	 */

	protected function setListIds()
	{
		if (!isset($this->listids))
		{
			$params = $this->getParams();
			$this->listids = (array) $params->get('googlemap_table');
		}
	}

	/**
	 * Build a polygon line to join up the markers
	 *
	 * @return  array  of lines each line being an array of points
	 */

	protected function getPolyline()
	{
		$params = $this->getParams();
		$lines = array();
		$polyelements = (array) $params->get('fb_gm_polyline_element');
		$listModels = $this->getlistModels();
		$c = 0;
		foreach ($listModels as $listModel)
		{
			$k = FabrikString::safeColName(FabrikString::rtrimword($polyelements[$c], '[]'));
			if ($k == '``')
			{
				$c++;
				continue;
			}
			try
			{
				$mapsElements = FabrikHelperList::getElements($listModel, array('plugin' => 'googlemap', 'published' => 1));
			}
			catch (Exception $e)
			{
				JError::raiseError(500, $e->getMessage());
			}

			$coordColumn = $mapsElements[0]->getFullName(false, false, false);
			$table = $listModel->getTable();

			$db = $listModel->getDb();
			$query = $db->getQuery(true);
			$query->select($coordColumn . ' AS coords')->from($table->db_table_name)->order($k);
			$query = $listModel->_buildQueryWhere(true, $query);
			$query = $listModel->_buildQueryJoin($query);
			$db->setQuery($query);
			$data = $db->loadObjectList();
			$points = array();
			if (is_null($data))
			{
				JError::raiseNotice(500, $db->getErrorMsg());
			}
			else
			{
				foreach ($data as $d)
				{
					$d = $this->getCordsFromData($d->coords);
					if ($d == array(0, 0))
					{
						// Don't show icons with no data
						continue;
					}
					$points[] = $d;
				}
			}
			$lines[] = $points;
			$c++;
		}
		return $lines;
	}

	/**
	 * Convert the Fabrik {lat,long:zoom} format into an array
	 *
	 * @param   string  $d  data
	 *
	 * @return  array
	 */

	private function getCordsFromData($d)
	{
		$v = trim($d);
		$v = FabrikString::ltrimword($v, "(");
		if (strstr($v, ","))
		{
			if (strstr($v, ":"))
			{
				$ar = explode(":", $v);
				array_pop($ar);
				$v = explode(",", $ar[0]);
			}
			else
			{
				$v = explode(",", $v);
			}
			$v[1] = FabrikString::rtrimword($v[1], ")");
		}
		else
		{

			$v = array(0, 0);
		}
		return $v;
	}

	/**
	 * Get the map icons
	 *
	 * @return  array
	 */

	public function getJSIcons()
	{
		$icons = array();
		$w = new FabrikWorker;
		$uri = JURI::getInstance();
		$params = $this->getParams();
		$templates = (array) $params->get('fb_gm_detailtemplate');
		$templates_nl2br = (array) $params->get('fb_gm_detailtemplate_nl2br');
		$listids = (array) $params->get('googlemap_table');

		// Images for file system
		$aIconImgs = (array) $params->get('fb_gm_iconimage');

		// Image from marker data
		$markerImages = (array) $params->get('fb_gm_iconimage2');

		// Specifed letter
		$letters = (array) $params->get('fb_gm_icon_letter');
		$aFirstIcons = (array) $params->get('fb_gm_first_iconimage');
		$aLastIcons = (array) $params->get('fb_gm_last_iconimage');
		$titleElements = (array) $params->get('fb_gm_title_element');
		$radiusElements = (array) $params->get('fb_gm_radius_element');
		$radiusDefaults = (array) $params->get('fb_gm_radius_default');
		$radiusUnits = (array) $params->get('fb_gm_radius_unit');
		$groupClass = (array) $params->get('fb_gm_group_class');

		$c = 0;
		$this->recordCount = 0;

		$maxMarkers = $params->get('fb_gm_markermax', 0);
		$recLimit = count($listids) == 1 ? $maxMarkers : 0;
		$limitMessageShown = false;
		$limitMessage = $params->get('fb_gm_markermax_message');
		$groupedIcons = array();
		$lc = 0;
		foreach ($listids as $listid)
		{
			$listModel = $this->getlistModel($listid);

			$template = JArrayHelper::getValue($templates, $c, '');
			/**
			* One day we should get smarter about how we decide which elements to render
			* but for now all we can do is set formatAll(), in case they use an element
			* which isn't set for list display, which then wouldn't get rendered unless we do this.
			*/
			if (FabrikString::usesElementPlaceholders($template)) {
				$listModel->formatAll(true);
			}
			$template_nl2br = JArrayHelper::getValue($templates_nl2br, $c, '1') == '1';

			$table = $listModel->getTable();

			try
			{
				$mapsElements = FabrikHelperList::getElements($listModel, array('plugin' => 'googlemap', 'published' => 1));
			}
			catch (Exception $e)
			{
				JError::raiseError(500, $e->getMessage());
			}

			$coordColumn = $mapsElements[0]->getFullName(false, true, false) . "_raw";

			// Are we using random start location for icons?
			$listModel->_randomRecords = ($params->get('fb_gm_random_marker') == 1 && $recLimit != 0) ? true : false;

			// Used in table model setLimits
			JRequest::setVar('limit' . $listid, $recLimit);
			$listModel->setLimits();

			$nav = $listModel->getPagination(0, 0, $recLimit);
			$data = $listModel->getData();
			$this->txt = array();
			$k = 0;

			foreach ($data as $groupKey => $group)
			{
				foreach ($group as $row)
				{
					$customimagefound = false;
					$iconImg = JArrayHelper::getValue($aIconImgs, $c, '');
					if ($k == 0)
					{
						$firstIcon = JArrayHelper::getValue($aFirstIcons, $c, $iconImg);
						if ($firstIcon !== '')
						{
							$iconImg = $firstIcon;
						}
					}
					$v = $this->getCordsFromData($row->$coordColumn);
					if ($v == array(0, 0))
					{
						// Don't show icons with no data
						continue;
					}
					$rowdata = JArrayHelper::fromObject($row);
					$rowdata['rowid'] = $rowdata['__pk_val'];
					$html = $w->parseMessageForPlaceHolder($template, $rowdata);

					$titleElement = JArrayHelper::getValue($titleElements, $c, '');
					$title = $titleElement == '' ? '' : strip_tags($row->$titleElement);
					/* $$$ hugh - if they provided a template, lets assume they will handle the link themselves.
					 * http://fabrikar.com/forums/showthread.php?p=41550#post41550
					 * $$$ hugh - at some point the fabrik_view / fabrik_edit links became optional
					 */
					if (empty($html) && (array_key_exists('fabrik_view', $rowdata) || array_key_exists('fabrik_edit', $rowdata)))
					{
						//Don't insert linebreak in empty bubble without links $html .= "<br />";

						// Use edit link by preference
						if (array_key_exists('fabrik_edit', $rowdata))
						{
							if ($rowdata['fabrik_edit']!="") $html .= "<br />";
							$html .= $rowdata['fabrik_edit'];
						}
						else
						{
							if ($rowdata['fabrik_view']!="") $html .= "<br />";
							$html .= $rowdata['fabrik_view'];
						}
					}
					if ($template_nl2br)
					{
						// $$$ hugh - not sure why we were doing this rather than nl2br?
						// If there was a reason, this is still broken, as it ends up inserting
						// two breaks.  So if we can't use nl2br ... I need fix this before using it again!
						/*
						$html = str_replace(array("\n\r"), "<br />", $html);
						$html = str_replace(array("\r\n"), "<br />", $html);
						$html = str_replace(array("\n", "\r"), "<br />", $html);
						*/
						$html = nl2br($html);
					}
					$html = str_replace("'", '"', $html);
					$this->txt[] = $html;
					if ($iconImg == '')
					{
						$iconImg = JArrayHelper::getValue($markerImages, $c, '');
						if ($iconImg != '')
						{
							$iconImg = JArrayHelper::getValue($rowdata, $iconImg, '');

							// Get the src
							preg_match('/src=["|\'](.*?)["|\']/', $iconImg, $matches);
							if (array_key_exists(1, $matches))
							{
								$iconImg = $matches[1];

								// Check file exists
								$path = str_replace(COM_FABRIK_LIVESITE, '', $iconImg);
								if (JFile::exists(JPATH_BASE . $path))
								{
									$customimagefound = true;
								}
							}
						}
						if ($iconImg != '')
						{
							list($width, $height) = $this->markerSize($iconImg);
						}
						else
						{
							// Standard google map icon size
							$width = 20;
							$height = 34;
						}
					}
					else
					{
						// Standard google map icon size
						list($width, $height) = $this->markerSize(JPATH_SITE . '/images/stories/' . $iconImg);
					}
					// Just for moosehunt!
					$radomize = ($_SERVER['HTTP_HOST'] == 'moosehunt.mobi') ? true : false;
					$groupKey = strip_tags($groupKey);

					$gClass = JArrayHelper::getValue($groupClass, 0, '');
					if (!empty($gClass))
					{
						$gClass .= '_raw';
						$gClass = (isset($row->$gClass)) ? $row->$gClass : '';
					}
					if (array_key_exists($v[0] . $v[1], $icons))
					{
						$existingIcon = $icons[$v[0] . $v[1]];
						if ($existingIcon['groupkey'] == $groupKey)
						{
							/* $$$ hugh - this inserts label between multiple record $html, but not at the top.
							 * If they want to insert label, they can do it themselves in the template.
							 * $icons[$v[0].$v[1]][2] = $icons[$v[0].$v[1]][2] . "<h6>$table->label</h6>" . $html;
							 * Don't insert linebreaks in empty bubble
							 */
							 if ($html!="") $html = "<br />" . $html;
							$icons[$v[0] . $v[1]][2] = $icons[$v[0] . $v[1]][2] .  $html;
							if ($customimagefound)
							{
								$icons[$v[0] . $v[1]][3] = $iconImg;
							}
						}
						else
						{
							$groupedIcons[] = array($v[0], $v[1], $html, $iconImg, $width, $height, 'groupkey' => $groupKey, 'listid' => $listid,
								'title' => $title, 'groupClass' => 'type' . $gClass);
						}
					}
					else
					{
						// Default icon - lets see if we need to use a letterd icon instead
						if (JArrayHelper::getValue($letters, $c, '') != '')
						{
							$iconImg = $uri->getScheme() . '://www.google.com/mapfiles/marker' . JString::strtoupper($letters[$c]) . '.png';
						}
						$icons[$v[0] . $v[1]] = array($v[0], $v[1], $html, $iconImg, $width, $height, 'groupkey' => $groupKey, 'listid' => $listid,
							'title' => $title, 'groupClass' => 'type' . $gClass);
					}

					if ($params->get('fb_gm_use_radius', '0') == '1')
					{
						$radiusElement = JArrayHelper::getValue($radiusElements, $c, '');
						$radiusUnits = JArrayHelper::getValue($radiusUnits, $c, 'k');
						$radiusMeters = $radiusUnits == 'k' ? 1000 : 1609.34;
						if (!empty($radiusElement))
						{
							$radius = (float) $row->$radiusElement;
							$radius *= $radiusMeters;
							$icons[$v[0].$v[1]]['radius'] = $radius;
						}
						else
						{
							$default = (float) JArrayHelper::getvalue($radiusDefaults, $c, 50);
							$default *= $radiusMeters;
							$icons[$v[0].$v[1]]['radius'] = $default;
						}
					}
					$icons[$v[0] . $v[1]]['c'] = $c;
					$this->recordCount++;
					$k++;
				}
			}
			// Replace last icon?
			$iconImg = JArrayHelper::getValue($aLastIcons, $c, '');
			if ($iconImg != '')
			{
				list($width, $height) = $this->markerSize(JPATH_SITE . '/media/com_fabrik/images/' . $iconImg);
				$icons[$v[0] . $v[1]][3] = $iconImg;
				$icons[$v[0] . $v[1]][4] = $width;
				$icons[$v[0] . $v[1]][5] = $height;
			}
			$c++;
		}
		// Replace coord keys with numeric keys
		$icons = array_values($icons);
		$icons = array_merge($icons, $groupedIcons);
		if ($maxMarkers != 0 && $maxMarkers < count($icons))
		{
			$icons = array_slice($icons, -$maxMarkers);
		}
		$limitMessageShown = !($k >= $recLimit);
		if (!$limitMessageShown && $recLimit !== 0 && $limitMessage != '')
		{
			$app->enqueueMessage($limitMessage);
		}
		FabrikHelperHTML::debug($icons, 'map');
		return $icons;
	}

	/**
	 * Get the width and height for an icon image
	 *
	 * @param   string  $iconImg  icon image path
	 *
	 * @return  array  (width, height)
	 */

	private function markerSize($iconImg)
	{
		if (!array_key_exists($iconImg, $this->markerSizes))
		{
			@$size = getimagesize($iconImg);
			$width = is_array($size) ? $size[0] : 25;
			$height = is_array($size) ? $size[1] : 25;

			// Ensure icons arent too big (25 is max)
			$scale = min(25 / $width, 25 / $height);
			/* If the image is larger than the max shrink it*/
			if ($scale < 1)
			{
				$width = floor($scale * $width);
				$height = floor($scale * $height);
			}
			$this->markerSizes[$iconImg] = array($width, $height);
		}
		return $this->markerSizes[$iconImg];
	}

	/**
	 * Ajax call to get the json encoded string of map markers
	 *
	 * @return  string
	 */

	public function onAjax_getMarkers()
	{
		echo json_encode($this->getJSIcons());
	}

	/**
	 * Get a static map
	 *
	 * @return  string  html image
	 */

	public function getStaticMap()
	{
		$params = $this->getParams();
		$icons = $this->getJSIcons();
		$iconstr = '';
		$lat = 0;
		$lon = 0;
		if (!empty($icons))
		{
			$first = $icons[0];
			$bounds = array('lat' => array($first[0], $first[0]), 'lon' => array($first[1], $first[1]));
			$c = 1;

			foreach ($icons as $i)
			{
				if ($c >= 50)
				{
					break;
				}
				$iconstr .= "&markers=" . trim($i[0]) . ',' . trim($i[1]);
				if ($i[0] < $bounds['lat'][0])
				{
					$bounds['lat'][0] = $i[0];
				}
				if ($i[0] > $bounds['lat'][1])
				{
					$bounds['lat'][1] = $i[0];
				}
				if ($i[1] < $bounds['lon'][0])
				{
					$bounds['lon'][0] = $i[1];
				}
				if ($i[1] > $bounds['lon'][1])
				{
					$bounds['lon'][1] = $i[1];
				}
				$c++;
			}
			if ($params->get('fb_gm_center') != 'middle')
			{
				$i = array_pop($icons);
				$lat = $i[0];
				$lon = $i[1];
			}
			else
			{
				$lat = ($bounds['lat'][1] + $bounds['lat'][0]) / 2;
				$lon = ($bounds['lon'][1] + $bounds['lon'][0]) / 2;
			}
		}
		$w = $params->get('fb_gm_mapwidth');
		$h = $params->get('fb_gm_mapheight');
		$z = $params->get('fb_gm_zoomlevel');

		if ($w > 640)
		{
			$w = 640;
		}
		// Max allowed static map size
		if ($w > 640)
		{
			$h = 640;
		}
		$uri = JURI::getInstance();
		$src = $uri->getScheme() . "://maps.google.com/staticmap?center=$lat,$lon&zoom={$z}&size={$w}x{$h}&maptype=mobile$iconstr";
		$str = '<img src="' . $src . '" alt="static map" />';
		return $str;
	}

	/**
	 * Get the map side bar which shows the list of overlays
	 * (not returning anything at the moment)
	 *
	 * @return  string
	 */

	public function getSidebar()
	{
		$params = $this->getParams();
		if ((int) $params->get('fb_gm_use_overlays', 0) && (int) $params->get('fb_gm_use_overlays_sidebar'))
		{

		}
	}

	/**
	 * Get wheter the map side bar should be shown
	 *
	 *  @return  bool
	 */

	public function getShowSideBar()
	{
		$params = $this->getParams();

		// KLM layers side bar?
		if ((int) $params->get('fb_gm_use_overlays', 0) === 1 && (int) $params->get('fb_gm_use_overlays_sidebar', 0) > 0)
		{
			return true;
		}
		if ((int) $params->get('fb_gm_group_sidebar', 0) === 1)
		{
			return true;
		}
		return false;
	}

	/**
	 * Get all the list models group templates
	 *
	 * @return  array
	 */

	public function getGroupTemplates()
	{
		$models = $this->getListModels();
		$groupbyTemplates = array();
		foreach ($models as $model)
		{
			$id = $model->getTable()->id;
			$tmpls = $model->groupTemplates;
			foreach ($tmpls as $k => $v)
			{
				$k = preg_replace('#[^0-9a-zA-Z_]#', '', $k);
				$groupbyTemplates[$id][$k] = $v;
			}
		}
		return $groupbyTemplates;
	}
}
