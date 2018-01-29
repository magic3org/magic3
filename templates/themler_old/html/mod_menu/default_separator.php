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
// Note: it is important to remove spaces between elements.
$iconClassName = isset($iconClassName) ? $iconClassName : '';
$attributes = array('class' => array($iconClassName),
    'title' => $item->params->get('menu-anchor_title', ''));

$title = '<span>' . $item->title . '</span>';

$linktype = $item->menu_image
    ? ('<img class="bd-menu-image" src="' . $item->menu_image . '" alt="' . $item->title . '" />'
        . ($item->params->get('menu_text', 1) ? $title : ''))
    : $title;

if ('default' == $menutype) {
    echo '<span class="separator">' . $linktype . '</span>';
} else if ('horizontal' == $menutype || 'vertical' == $menutype || 'top' == $menutype) {
    if ('alias' == $item->type && in_array($item->params->get('aliasoptions'), $path) || in_array($item->id, $path))
        $attributes['class'][] = 'active';
    $attributes['class'][] = $item->deeper ? 'separator' : 'separator-without-submenu';
    echo funcTagBuilder('a', $attributes, $linktype);
}
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>