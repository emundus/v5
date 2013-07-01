<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Abstract view renderer class. The renderer is what turns XML view templates
 * into actual HTML code, renders the submenu links and potentially wraps the
 * HTML output in a div with a component-specific ID.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
abstract class FOFRenderAbstract
{
	/** @var int Priority of this renderer. Higher means more important */
	protected $priority = 50;

	/** @var int Is this renderer enabled? */
	protected $enabled = false;

	/**
	 * Returns the information about this renderer
	 *
	 * @return object
	 */
	public function getInformation()
	{
		return (object) array(
				'priority'	 => $this->priority,
				'enabled'	 => $this->enabled,
		);
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	abstract public function preRender($view, $task, $input, $config = array());

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	abstract public function postRender($view, $task, $input, $config = array());

	/**
	 * Renders a FOFForm and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form     The form to render
	 * @param   FOFModel  $model     The model providing our data
	 * @param   FOFInput  $input     The input object
	 * @param   string    $formType  The form type: edit, browse or read
	 * @param   boolean   $raw       If true, the raw form fields rendering (without the surrounding form tag) is returned.
	 *
	 * @return  string    The HTML rendering of the form
	 */
	public function renderForm(FOFForm &$form, FOFModel $model, FOFInput $input, $formType = null, $raw = false)
	{
		if (is_null($formType))
		{
			$formType = $form->getAttribute('type', 'edit');
		}
		else
		{
			$formType = strtolower($formType);
		}
		switch ($formType)
		{
			case 'browse':
				return $this->renderFormBrowse($form, $model, $input);
				break;

			case 'read':
				if ($raw)
				{
					return $this->renderFormRaw($form, $model, $input, 'read');
				}
				else
				{
					return $this->renderFormRead($form, $model, $input);
				}

				break;

			default:
				if ($raw)
				{
					return $this->renderFormRaw($form, $model, $input, 'edit');
				}
				else
				{
					return $this->renderFormEdit($form, $model, $input);
				}
				break;
		}
	}

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  The form to render
	 * @param   FOFModel  $model  The model providing our data
	 * @param   FOFInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormBrowse(FOFForm &$form, FOFModel $model, FOFInput $input);

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  The form to render
	 * @param   FOFModel  $model  The model providing our data
	 * @param   FOFInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormRead(FOFForm &$form, FOFModel $model, FOFInput $input);

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  The form to render
	 * @param   FOFModel  $model  The model providing our data
	 * @param   FOFInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormEdit(FOFForm &$form, FOFModel $model, FOFInput $input);

	/**
	 * Renders a raw FOFForm and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  	The form to render
	 * @param   FOFModel  $model  	The model providing our data
	 * @param   FOFInput  $input  	The input object
	 * @param   string	  $formType The form type e.g. 'edit' or 'read'
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormRaw(FOFForm &$form, FOFModel $model, FOFInput $input, $formType);
}
