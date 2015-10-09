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

$themeDir = dirname(__FILE__);

require_once $themeDir . DIRECTORY_SEPARATOR . 'functions.php';

// Create alias for $this object reference:
$documentError = $this;
$document = JFactory::getDocument();

$document->head = "";
// Shortcut for template base url:
$document->templateUrl = $documentError->baseurl . '/templates/' .
    (isset($editorDir) ? $editorDir : $documentError->template);

$templatePath = $themeDir . '/templates/' . getCurrentTemplateByType('error404') . '.php';
if (file_exists($templatePath)) {
    include_once $templatePath;
} else {
    echo 'Template ' . $templatePath . ' not found';
}
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>