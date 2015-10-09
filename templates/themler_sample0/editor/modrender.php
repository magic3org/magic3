<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
	require_once $templateDir . '/app/' . 'Editor.php';
	$ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
	$editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>
<?php

jimport( 'joomla.application.module.helper' );

$modulename = JRequest::getVar('modulename', '');
$module = JModuleHelper::getModule($modulename);

$attribs['style'] = 'drstyle';
$attribs['drstyle'] = JRequest::getVar('modulestyle', '');
$attribs['id'] = JRequest::getVar('moduleid', '');

echo JModuleHelper::renderModule( $module, $attribs );
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>