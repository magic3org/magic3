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
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'functions.php';

$menuType = isset($attribs['type']) ? $attribs['type'] : '';
$isVmenu = (0 === strpos($params->get('moduleclass_sfx'), 'vmenu') ||
            false !== strpos($params->get('moduleclass_sfx'), ' vmenu'));
$modPath = dirname(__FILE__) . '/';
$tag_id = ($params->get('tag_id') != NULL) ? ' id="' . $params->get('tag_id') . '"' : '';

if ($isVmenu && file_exists($modPath . 'default_vmenu.php')) {
    require JModuleHelper::getLayoutPath('mod_menu', 'default_vmenu');
} elseif ('' !== $menuType && file_exists($modPath . 'default_' . $menuType . '.php')) {
    require JModuleHelper::getLayoutPath('mod_menu', 'default_' . $menuType);
} else {
    $menutype = 'default';
    echo '<ul class="menu' . $params->get('class_sfx') . '"' . $tag_id . '>';
    foreach ($list as $i => &$item) {

        $class = 'item-' . $item->id;
        $class .= $item->id == $active_id ? ' current' : '';
        $class .= ('alias' == $item->type
            && in_array($item->params->get('aliasoptions'), $path)
            || in_array($item->id, $path)) ? ' active' : '';
        $class .= $item->deeper ? ' deeper' : '';
        $class .= $item->parent ? ' parent' : '';

        echo '<li class="' . $class . '">';

        // Render the menu item.
        switch ($item->type) {
            case 'separator':
            case 'url':
            case 'component':
                require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
                break;
            default:
                require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
                break;
        }

        if ($item->deeper)
            echo '<ul>';
        elseif ($item->shallower)
            echo '</li>' . str_repeat('</ul></li>', $item->level_diff);
        else
            echo '</li>';
    }
    echo '</ul>';
}
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>