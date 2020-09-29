<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'administrator');

require_once dirname(dirname(__FILE__)) . '/library/Core.php';

require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'helper.php';
$prefix = version_compare(JVERSION, '3.9', '>=') ? 'sub' : '';
require_once JPATH_BASE . DS . 'includes' . DS . $prefix . 'toolbar.php';

$app = JFactory::getApplication('administrator');

// checking user privileges
$user = JFactory::getUser();
if (!$user->authorise('core.edit', 'com_menus') || !$user->authorise('core.edit', 'com_modules') || !$user->authorise('core.edit', 'com_content')) {
    exit('error:2:You do not have sufficient permissions to import/install content.');
}

$pluginName = 'nicepage';

$pathDataLoader = JPATH_BASE . '/components/com_' . $pluginName . '/helpers/import.php';
if (!file_exists($pathDataLoader))
    exit('error:2:Loader not found');

require_once JPATH_BASE . '/components/com_' . $pluginName . '/helpers/' . $pluginName . '.php';
require_once JPATH_BASE . '/components/com_' . $pluginName . '/helpers/import.php';

$className = ucfirst($pluginName) . '_Data_Loader';
$loader = new $className();
$loader->load('content.json', true);
echo $loader->execute($_GET);
