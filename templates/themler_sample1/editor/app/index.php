<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('EXPORT_APP', __DIR__ . '/classes');

require_once EXPORT_APP . '/Helper.php';
require_once EXPORT_APP . '/LoggingTime.php';
require_once EXPORT_APP . '/ExportController.php';

$data = array_merge($_GET, $_POST);

$timeLogging = LoggingTime::getInstance();
$timeLogging->start('[PHP] Joomla start of work');

$timeLogging->start('[PHP] Initializing Joomla');
if (isset($data['frontend']) && $data['frontend'] == true) {
    define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))));
    require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
    require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';
    $app = JFactory::getApplication('site');
} else {
    define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'administrator');
    require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
    require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';
    require_once JPATH_BASE . DS . 'includes' . DS . 'helper.php';
    require_once JPATH_BASE . DS . 'includes' . DS . 'toolbar.php';
    $app = JFactory::getApplication('administrator');
}
$timeLogging->end('[PHP] Initializing Joomla');

$controller = new ExportController();
$controller->execute($data);