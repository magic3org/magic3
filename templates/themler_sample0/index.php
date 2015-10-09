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
/**
 * Template for Joomla! CMS, created with Designer.
 * See readme.txt for more details on how to use the template.
 */

$themeDir = dirname(__FILE__);

require_once $themeDir . DIRECTORY_SEPARATOR . 'functions.php';
// Create alias for $this object reference:
$document = $this;
$document->head = "<jdoc:include type=\"head\" />";
// Shortcut for template base url:
$templateUrl = $document->baseurl . '/templates/' . (isset($editorDir) ? $editorDir : $document->template);
$document->templateUrl = $templateUrl;
Designer::load("Designer_Page");

// Initialize $view:
$this->view = new DesignerPage($this);
echo $this->view->renderTemplate($themeDir);
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>