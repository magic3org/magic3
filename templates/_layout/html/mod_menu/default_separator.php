<?php

defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$attributes = array('class' => array(),
    'title' => $item->params->get('menu-anchor_title', ''));

$linktype = $item->menu_image
    ? ('<img class="art-menu-image" src="' . $item->menu_image . '" alt="' . $item->title . '" />'
        . ($item->params->get('menu_text', 1) ? $item->title : ''))
    : $item->title;

if ('default' == $menutype) {
    echo '<span class="separator">' . $linktype . '</span>';
} else if ('horizontal' == $menutype || 'vertical' == $menutype) {
    if (in_array($item->id, $path))
        $attributes['class'][] = 'active';
    $attributes['class'][] = $item->deeper ? 'separator' : 'separator-without-submenu';
    if ($params->get('startLevel') == $item->level)
        $linktype = '<span class="l"></span><span class="r"></span><span class="t">' . $linktype . '</span>';
    echo artxTagBuilder('a', $attributes, $linktype);
}
