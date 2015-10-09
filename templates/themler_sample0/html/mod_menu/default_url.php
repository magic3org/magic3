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
jimport('joomla.filter.output');
$iconClassName = isset($iconClassName) ? $iconClassName : '';
$attributes = array(
    'class' => array($iconClassName, $item->anchor_css),
    'title' => $item->anchor_title,
    'href' => JFilterOutput::ampReplace(htmlspecialchars($item->flink)));

switch ($item->browserNav) {
    case 1:
        // _blank
        $attributes['target'] = '_blank';
        break;
    case 2:
        // window.open
        $attributes['onclick'] = 'window.open(this.href,\'targetWindow\','
            . '\'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes\');return false;';
        break;
}

$title = '<span>' . $item->title . '</span>';
$linktype = $item->menu_image
    ? ('<img class="bd-menu-image" src="' . $item->menu_image . '" alt="' . $item->title . '" />'
        . ($item->params->get('menu_text', 1) ? $title : ''))
    : $title;

if (('horizontal' == $menutype || 'vertical' == $menutype || 'top' == $menutype)
    && ('alias' == $item->type && in_array($item->params->get('aliasoptions'), $path) || in_array($item->id, $path)))
{
    $attributes['class'][] = 'active';
}

echo funcTagBuilder('a', $attributes, $linktype);

?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>