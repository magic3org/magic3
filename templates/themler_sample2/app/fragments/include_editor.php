<!-- CONTAINER -->
<?php
defined('_JEXEC') or die;
$templateName = JFactory::getApplication('site')->getTemplate();
$templateDir = JPATH_THEMES . '/' . $templateName;
require_once $templateDir . '/app/' . 'Editor.php';

if ($way = DesignerEditor::override($templateName, __FILE__)) {
	$editorDir = $templateName . '/editor';
    require($way);
    return;
} else {
?>
<!-- CONTENT -->
<?php } ?>
<!-- /CONTAINER -->