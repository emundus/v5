<?php

/**
 * @package		SP Upgrade
 * @subpackage	Components
 * @copyright	SP CYEND - All rights reserved.
 * @author		SP CYEND
 * @link		http://www.cyend.com
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.model');
jimport('joomla.utilities.simplexml');

class SPUpgradeModelCom_Media extends SPUpgradeModelCom {

    public function images($name = null) {
        $general = $this->general;

        $message = ('<h2>' . JText::_($this->task->extension_name) . ' - ' . JText::_($this->task->extension_name . '_' . $this->task->name) . '</h2>');
        $general->writeLog($message);

        JFolder::move('images', 'images_bkp' . JFactory::getDate()->toFormat('_%Y_%m_%d_%H_%M_%S'), JPATH_SITE);

        $message = '50% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);

        JFolder::copy($general->source_path . '/images', JPATH_SITE . '/images');
        $message = '100% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);
    }

    public function template($name = null) {

        $general = $this->general;
        $name = JRequest::getVar('input_template', '');
        $source_path = $general->source_path . '/templates/' . $name;
        $destination_path = JPATH_SITE . '/tmp/' . $name;

        $message = ('<h2>' . JText::_($this->task->extension_name) . ' - ' . JText::_($this->task->extension_name . '_' . $this->task->name) . '</h2>');
        $general->writeLog($message);

        //Check if exists in source
        if (empty($name)) {
            $message = '<p>' . JText::plural('COM_SPUPGRADE_TEMPLATE_ERROR', $source_path) . '</p>';
            $general->writeLog($message);
            return false;
        }

        if (!JFolder::exists($source_path)) {
            $message = '<p>' . JText::plural('COM_SPUPGRADE_TEMPLATE_ERROR', $source_path) . '</p>';
            $general->writeLog($message);
            return false;
        }

        $message = '10% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);

        //delete if exists in destination
        if (JFolder::exists($destination_path))
            JFolder::delete($destination_path);

        $message = '20% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);

        //copy from source to destination
        if (!JFolder::copy($source_path, $destination_path)) {
            $message = '<p>' . JText::_('COM_SPUPGRADE_MSG_ERROR_TEMPLATE') . '</p>';
            $general->writeLog($message);
            return false;
        }

        $message = '30% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);

        //Copy error file
        JFile::copy(JPATH_SITE . '/templates/atomic/error.php', $destination_path . '/error.php');

        $message = '40% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);

        //Delete params file
        JFile::delete($destination_path . '/params.ini');

        //Rename templateDetails.xml
        if (!JFile::exists($destination_path . '/v15_templateDetails.xml'))
            JFile::move('templateDetails.xml', 'v15_templateDetails.xml', $destination_path);

        //Process individual files
        $files = JFolder::files($destination_path);
        foreach ($files as $fileName) {
            switch ($fileName) {

                case 'template_thumbnail.png':
                    $message = '50% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
                    $general->writeLog($message);

                    JFile::copy('template_thumbnail.png', 'template_preview.png', $destination_path);
                    break;

                case 'v15_templateDetails.xml':
                    $message = '60% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
                    $general->writeLog($message);

                    $xml_source = new JSimpleXML();
                    $xml_source->loadFile($destination_path . '/v15_templateDetails.xml');
                    $implementation = new DOMImplementation();
                    $dtd = $implementation->createDocumentType('install', '-//Joomla! 2.5//DTD template 1.0//EN', 'http://www.joomla.org/xml/dtd/2.5/template-install.dtd');
                    $xml_destination = $implementation->createDocument(null, null, $dtd);
                    $xml_destination->version = '1.0';
                    $xml_destination->encoding = 'utf-8';
                    $xml_destination->formatOutput = true;
                    $xml_destination->preserveWhiteSpace = false;

                    //set extension
                    $el_extension = $xml_destination->createElement('extension');
                    $el_extension->setAttribute('version', '2.5');
                    $el_extension->setAttribute('type', 'template');
                    $el_extension->setAttribute('client', 'site');

                    //get install
                    $el_install = $xml_source->document->_children;
                    $el_extension = $this->xml2dom($xml_destination, $xml_source, $el_install, $el_extension);

                    $xml_destination->appendChild($el_extension);
                    $xml_destination->save($destination_path . '/templateDetails.xml');

                default:
                    $message = '70% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
                    $general->writeLog($message);

                    //Modify php files
                    $jAp = JFactory::getApplication();
                    $fileName = $destination_path . '/' . $fileName;
                    $strLenght = strlen($fileName);
                    if (substr($fileName, $fileName - 4, 4) == '.php') {
                        $this->fix_php($fileName);
                    }
                    break;
            }
        }

        $message = '100% ' . JText::_('COM_SPUPGRADE_MSG_PROCESSED') . '<br/>';
        $general->writeLog($message);
        
        $message = JText::plural('COM_SPUPGRADE_MSG_TEMPLATE', $destination_path);
        $general->writeLog($message);
        
        return true;
    }

    private function xml2dom($xml_destination, $xml_source, $el_install, $el_extension) {
        foreach ($el_install as $value) {
            switch ($value->_name) {
                case 'images':
                    break;
                case 'css':
                    break;
                case 'files':
                    $name = JRequest::getVar('input_template', '');
                    $destination_path = JPATH_SITE . '/tmp/' . $name;
                    $folders = JFolder::folders($destination_path);
                    $files = JFolder::files($destination_path);
                    $el_files = $xml_destination->createElement('files');
                    foreach ($files as $file) {
                        $el_filename = $xml_destination->createElement('filename', $file);
                        $el_files->appendChild($el_filename);
                    }
                    $el_files->appendChild($el_filename);
                    foreach ($folders as $folder) {
                        $el_foldername = $xml_destination->createElement('folder', $folder);
                        $el_files->appendChild($el_foldername);
                    }
                    $el_extension->appendChild($el_files);
                    break;

                case 'params':
                    $config = $xml_destination->createElement('config');
                    $fields = $xml_destination->createElement('fields');
                    $fields->setAttribute('name', 'params');
                    $fieldset = $xml_destination->createElement('fieldset');
                    $fieldset->setAttribute('name', 'advanced');
                    $fieldset = $this->xml2dom($xml_destination, $xml_source, $value->_children, $fieldset);
                    $fields->appendChild($fieldset);
                    $config->appendChild($fields);
                    $el_extension->appendChild($config);
                    break;

                default:
                    if ($value->_name == 'param')
                        $value->_name = 'field';
                    $el_install_child = $xml_destination->createElement($value->_name, trim($value->_data));
                    if (!empty($value->_attributes)) {
                        foreach ($value->_attributes as $key => $attribute) {
                            $el_install_child->setAttribute($key, $attribute);
                        }
                    }
                    if (!empty($value->_children))
                        $el_install_child = $this->xml2dom($xml_destination, $xml_source, $value->_children, $el_install_child);
                    $el_extension->appendChild($el_install_child);
                    break;
            }
        }
        return $el_extension;
    }

    private function fix_php($fileName) {
        $general = $this->general;
        $file = $fileName;
        $backupfile = $fileName . '.bak';

        if (!copy($file, $backupfile)) {
            $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_FAILEDTOCOPY', $backupfile), 'error');
            $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_FAILEDTOCOPY', $backupfile) . '</font></b></p>';
            $general->writeLog($message);
            return false;
        }

        $backuphandle = @fopen($backupfile, "r+");
        $handle = @fopen($file, "w");
        if ($handle) {

            while (($buffer = fgets($backuphandle, 4096)) !== false) {

                $buffer = str_replace("defined( '_JEXEC' ) or die( 'Restricted access' );", "defined('_JEXEC') or die;JHtml::_('behavior.framework', true);", $buffer);
                $buffer = str_replace("\$mainframe->", "JFactory::getApplication()->", $buffer);

                IF (fwrite($handle, $buffer) === FALSE) {
                    $jAp->enqueueMessage(JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CANNOTWRITEFILE', $file), 'error');
                    $message = '<p><b><font color="red">' . JText::sprintf('COM_SPUPGRADE_MSG_ERROR_CANNOTWRITEFILE', $file) . '</font></b></p>';
                    $general->writeLog($message);
                    return false;
                }
            }
            if (!feof($backuphandle)) {
                $message = '<p><b><font color="red">' . JText::_('COM_SPUPGRADE_MSG_ERROR_UNEXPECTEDFGETS') . '</font></b></p>';
                $general->writeLog($message);
            }
            fflush($handle);
            fclose($handle);
            fclose($backuphandle);
            unlink($backupfile = $fileName . '.bak');
        }
    }

}
