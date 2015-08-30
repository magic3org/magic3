<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('JPATH_BASE', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';

$app = JFactory::getApplication('site');

require_once dirname(__DIR__) . '/classes/Config.php';

header('Content-Type: application/javascript');
header("Pragma: no-cache");

echo 'config.infoData.templates = ' . json_encode(Config::getThemeTemplates()) . ';';
?>