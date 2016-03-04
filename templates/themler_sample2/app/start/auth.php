<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('JPATH_BASE', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';

$app = JFactory::getApplication('site');

$uid = JRequest::getVar('uid', 0);
if (0 < $uid) {
    $session = JFactory::getSession();
    $user = new JUser($uid);
    $session->set('user', $user);
}
?>