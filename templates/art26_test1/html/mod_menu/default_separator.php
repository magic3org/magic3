<?php

defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
$attributes = array('class' => array(),
	'title' => $item->params->get('menu-anchor_title', ''));
$linktype = $item->params->get('menu_image', '')
	? ('<img src="' . $item->params->get('menu_image', '') . '" alt="' . $item->title . '" />'
		. ($item->params->get('menu_text', 1) ? '<span class="image-title">' . $item->title . '</span> ' : ''))
	: $item->title;

if ('default' == $menutype) {
	echo '<span class="separator">' . $linktype . '</span>';
} else if ('horizontal' == $menutype) {
	if (in_array($item->id, $path))
		$attributes['class'][] = 'active';
	$attributes['class'][] = 'separator';
	if (!$item->deeper)
		$attributes['class'][] = ' separator-without-submenu';
	if ($params->get('startLevel') == $item->level)
		$linktype = '<span class="l"></span><span class="r"></span><span class="t">' . $linktype . '</span>';
	echo artxTagBuilder('a', $attributes, $linktype);
}
