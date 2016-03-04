<?php

class PermissionsException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        $folders = array(JPATH_SITE . '/administrator/templates', JPATH_SITE . '/templates', JPATH_SITE . '/language', JFactory::getConfig()->get('tmp_path'));
        $content = file_get_contents(dirname(dirname(__FILE__)) . '/tmpl/warning.php');
        $content = preg_replace('/[\r\n]/', '', $content);
        $content = str_replace('{folders}', implode("<br>", $folders), $content);
        $content = str_replace('{root}', JPATH_SITE, $content);
        $message = $this->getMessage() . ' ' . $message . "\n" . $content;
        parent::__construct($message, $code, $previous);
    }
}