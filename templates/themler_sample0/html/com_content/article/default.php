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
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php';

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

Designer::load("Designer_Content");
Designer::load("Designer_Shortcodes");

$component = new DesignerContent($this, $this->params);
$article = $component->article('article', $this->item, $this->item->params, array('print' => $this->print));
$currentTemplateName = getCurrentTemplateByType($article->isPage ? 'page' : 'post');
?>
<!--TEMPLATE <?php echo $currentTemplateName; ?> /-->
<?php
require 'default_template.php';
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>