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
if (null === $params->get('display_text') || $params->get('display_text', 1)) {
    $label = $text;
    if ('' == str_replace(' ', '', $label))
        $label = JText::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES');
    if ('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES' == $label)
        $label = '';
} else
    $label = '';

echo '<a href="' . $link . '" class="bd-rss-tag-icon syndicate-module' . $moduleclass_sfx . '">'
  . ($label ? '<span>' . $label . '</span>' : '') . '</a>';

?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>