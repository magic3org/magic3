<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('JPATH_BASE',dirname(dirname(dirname(__FILE__))) . DS . 'administrator');

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

$path = dirname(dirname(__FILE__)) . '/manifests/themler-' . $_GET['ver'] . '.manifest';
if (!file_exists($path))
    die;

$isSSL = false;
if (isset($_SERVER['HTTPS'])) {
    if ('on' == strtolower($_SERVER['HTTPS']))
        $isSSL = true;
    if ('1' == $_SERVER['HTTPS'])
        $isSSL = true;
} elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
    $isSSL = true;
}

header('Content-Type: text/cache-manifest');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$content = file_get_contents($path);
echo $isSSL ? str_replace('http://', 'https://', $content) : str_replace('https://', 'http://', $content);