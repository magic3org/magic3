<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('JPATH_BASE', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'administrator');
require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'helper.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'toolbar.php';

$app = JFactory::getApplication('administrator');

// checking user privileges
$user = JFactory::getUser();
$session = JFactory::getSession();
if (!(1 !== (integer)$user->guest && 'active' === $session->getState()))
    die;

define('EXPORT_APP', dirname(__DIR__) . '/classes');
require_once EXPORT_APP . '/Helper.php';
require_once EXPORT_APP . '/Storage.php';
require_once EXPORT_APP . '/Config.php';

Helper::tryAllocateMemory();
Config::buildPreview();

header('Content-Type: application/javascript');
header("Pragma: no-cache");

echo 'var config=' . Config::getConfigObject() . ';';
?>