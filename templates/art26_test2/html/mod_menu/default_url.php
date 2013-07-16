<?php

defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$attributes = array(
    'class' => array($item->params->get('menu-anchor_css', '')),
    'title' => $item->params->get('menu-anchor_title', ''),
    'href' => $item->flink);

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

$linktype = $item->menu_image
    ? ('<img class="art-menu-image" src="' . $item->menu_image . '" alt="' . $item->title . '" />'
        . ($item->params->get('menu_text', 1) ? $item->title : ''))
    : $item->title;

if ('horizontal' == $menutype || 'vertical' == $menutype) {
    if ($params->get('startLevel') == $item->level)
        $linktype = '<span class="l"></span><span class="r"></span><span class="t">' . $linktype . '</span>';
}
echo artxTagBuilder('a', $attributes, $linktype);
