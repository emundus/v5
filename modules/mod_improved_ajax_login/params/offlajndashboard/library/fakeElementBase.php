<?php
/*-------------------------------------------------------------------------
# com_improved_ajax_login - Improved_AJAX_Login
# -------------------------------------------------------------------------
# @ author    Balint Polgarfi
# @ copyright Copyright (C) 2013 Offlajn.com  All Rights Reserved.
# @ license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @ website   http://www.offlajn.com
-------------------------------------------------------------------------*/
?><?php
defined('_JEXEC') or die('Restricted access');

if(!class_exists('JOfflajnFakeElementBase')) {
  jimport('joomla.html.parameter.element');
  
  class JOfflajnParams{
    function load($class){
      require_once(dirname(__FILE__).DS.'..'.DS.'..'.DS.$class.DS.$class.'.php');
    }
  }
  
  if(version_compare(JVERSION,'1.6.0','ge')) {
    
    class JOfflajnFakeElementBase extends JFormField {
    
      var $_moduleName = '';
      
      var $id = '';
    	
      public function __construct($parent = null){
    		$this->_parent = $parent;
        $this->getModule();
    	  $this->_fdir = (dirname(__FILE__));
    	  $this->_furl = "";
    	   if ( false !== strpos($this->_fdir, 'administrator') ) {
          preg_match_all('/administrator[a-zA-Z0-9\\\\_\\/]+/', $this->_fdir, $this->_furl);
        } else {
          preg_match_all('/modules[a-zA-Z0-9\\\\_\\/]+/', $this->_fdir, $this->_furl);
        }  
        $this->_furl = str_replace('\\', '/', $this->_furl[0][0]);
        $this->_furl = str_replace('library', '', $this->_furl);
        if(defined('WP_ADMIN')){
          $this->_furl = str_replace('administrator/', '', $this->_furl);
        }
      }

    	public function getInput(){
        $scripthack = '
          <script type="text/javascript">
          window.addEvent("domready", function(){
            document.formvalidator.isValid = function() {return true;};
          });
          </script>
        ';
        $this->id = $this->generateId($this->name);
        if(version_compare(JVERSION,'3.0','ge')){
          $node = $this->element;
        }else{
          $node = JFactory::getXMLParser('Simple');
          $node->loadString($this->element->asXML());
          $node = $node->document;
        }
        return $scripthack.$this->universalfetchElement($this->name, $this->value, $node);
    	}
      
      function getAttribute($attr){
        return $this->element[$attr];
      }

    	function getModule(){
        $d = explode(DS, dirname(__FILE__));
        $this->_moduleName = $d[count($d)-4];
      }
      
      function generateId($name){
        return str_replace(array('[x]', '[', ']','-x-', ' '), array('-x-','','','[x]', ''), $name);
      }
      
    	public function render(&$xmlElement, $value, $control_name = 'params')
    	{
    		$name	= $xmlElement->attributes('name');
    		$label	= $xmlElement->attributes('label');
    		$descr	= $xmlElement->attributes('description');
    		//make sure we have a valid label
    		$label = $label ? $label : $name;
    		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
    		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
    		$result[2] = $descr;
    		$result[3] = $label;
    		$result[4] = $value;
    		$result[5] = $name;
    
    		return $result;
    	}
      
    	public function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
    	{
    		$output = '<label id="'.$this->generateId($name).'-lbl" for="'.$this->generateId($name).'">';
    		$output .= JText::_($label).'</label>';
    
    		return $output;
    	}
    
    	public function fetchElement($name, $value, &$xmlElement, $control_name){
    	if(is_string($value))
      $value = stripslashes($value);
        $this->id = $this->generateId($control_name.'['.$name.']');
        if(is_object($value)) $value = (array) $value;
        return $this->universalfetchElement($control_name.'['.$name.']', $value, $xmlElement);
      }
      
      function renderForm(&$form){
        ob_start();
        $fieldSets = $form->getFieldsets('params');

      	foreach ($fieldSets as $name => $fieldSet) : ?>
      		<?php $hidden_fields = ''; ?>
      		<ul class="adminformlist">
      			<?php foreach ($form->getFieldset($name) as $field) : ?>
      			<?php if (!$field->hidden) : ?>
      			<li>
      				<?php echo $field->getLabel(); ?>
      				<?php echo $field->getInput(); ?>
      			</li>
      			<?php else : $hidden_fields.= $field->input; ?>
      			<?php endif; ?>
      			<?php endforeach; ?>
      		</ul>
      		<?php echo $hidden_fields; ?>
      	<?php endforeach;
        return ob_get_clean();
      }
      
      function loadFiles($name = '', $namespace = '') {
        $name = strtolower($name == '' ? $this->_name : $name); 
        if($namespace == '') $namespace = $name;  
        $filepath = str_replace('offlajndashboard'.DS.'library', '', $this->_fdir).$namespace.DS.$namespace.DS.$name;
        $document =& JFactory::getDocument();
        if(JFile::exists($filepath.".js")) DojoLoader::addScriptFile('/'.$this->_furl.'../'.$namespace.'/'.$namespace.'/'.$name.'.js');
        if(JFile::exists($filepath.".css")) $document->addStyleSheet(JURI::root(true).'/'.$this->_furl.'../'.$namespace.'/'.$namespace.'/'.$name.'.css');
      }                 
    }
  
  } else {
    class JOfflajnFakeElementBase extends JElement {
    
      var $_moduleName = '';
      
      var $id = '';
    	
      function __construct($parent = null){
        $this->_parent = $parent;
        $this->getModule();
      	$this->_fdir = (dirname(__FILE__));
    	  $this->_furl = "";
    	    if ( false !== strpos($this->_fdir, 'administrator') ) {
              preg_match_all('/administrator[a-zA-Z0-9\\\\_\\/]+/', $this->_fdir, $this->_furl);
            } else {
              preg_match_all('/modules[a-zA-Z0-9\\\\_\\/]+/', $this->_fdir, $this->_furl);
            }  
            $this->_furl = str_replace('\\', '/', $this->_furl[0][0]);
            $this->_furl = str_replace('library', '', $this->_furl);
    	}
      
      function getAttribute($attr){
        return $this->element->attributes($attr);
      }
      
      function fetchElement($name, $value, &$node, $control_name){
        $this->element = &$node;
        return $this->universalfetchElement($control_name.'['.$name.']', $value, $node);
      }
      
    	function render(&$xmlElement, $value, $control_name = 'params')
    	{
    		$name	= $xmlElement->attributes('name');
    		$label	= $xmlElement->attributes('label');
    		$descr	= $xmlElement->attributes('description');
    		//make sure we have a valid label
    		$label = $label ? $label : $name;
        $this->label = $label;
        $this->id = $this->generateId($control_name.'['.$name.']');
        
    		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
    		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
    		$result[2] = $descr;
    		$result[3] = $label;
    		$result[4] = $value;
    		$result[5] = $name; //TODO
    		return $result;
    	}
      
    	function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
    	{
    		$output = '<label id="'.$this->id.'-lbl" for="'.$this->id.'">';

    		$output .= JText::_($label).'</label>';
    
    		return $output;
    	}
      
      function getLabel(){
        return $this->label;
      }
	
      function loadFiles($name = '', $namespace = '') {
        $name = strtolower($name == '' ? $this->_name : $name); 
        if($namespace == '') $namespace = $name;       
        $filepath = str_replace('offlajndashboard'.DS.'library', '', $this->_fdir).$namespace.DS.$namespace.DS.$name;
        $document =& JFactory::getDocument();
        if(JFile::exists($filepath.".js")) DojoLoader::addScriptFile('/'.$this->_furl.'../'.$namespace.'/'.$namespace.'/'.$name.'.js');
        if(JFile::exists($filepath.".css")) $document->addStyleSheet(JURI::root(true).'/'.$this->_furl.'../'.$namespace.'/'.$namespace.'/'.$name.'.css');     
      }                 
          
    	function getModule(){
        $d = explode(DS, dirname(__FILE__));
        $this->_moduleName = $d[count($d)-4];
      }
      
      function generateId($name){
        return str_replace(array('[x]', '[', ']','-x-', ' '), array('-x-','','','[x]', ''), $name);
      }
    }
  }
}