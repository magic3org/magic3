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

if (!defined('_DESIGNER_FUNCTIONS'))
  require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../functions.php');

function pagination_list_render($list)
{
	$activePaginator = $GLOBALS['theme_settings']['active_paginator'];
    if ('specific' === $activePaginator) {
        return $list;
    } else {
        return renderTemplateFromIncludes('pagination_list_render_' . $activePaginator, array($list),
            'common' == $activePaginator ? 'prototypes' : 'includes');
	} 
}
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>