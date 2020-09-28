<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

$linkClassName = isset($linkClassName) ? $linkClassName : '';
$linkInlineStyles = isset($linkInlineStyles) ? $linkInlineStyles : '';
$linkActiveClass = isset($itemIsCurrent) && $itemIsCurrent ? 'active' : '';
$attributes = array(
    'class' => array($linkClassName, $item->params->get('menu-anchor_css', ''), $linkActiveClass),
    'title' => $item->params->get('menu-anchor_title', ''),
    'style' => $linkInlineStyles);
switch ($item->browserNav) {
default:
case 0:
    $attributes['href'] = $item->flink;
    break;
case 1:
    // _blank
    $attributes['href'] = $item->flink;
    $attributes['target'] = '_blank';
    break;
case 2:
    // window.open
    $attributes['href'] = $item->flink;
    $attributes['onclick'] = 'window.open(this.href,\'targetWindow\','
        . '\'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes\');return false;';
    break;
}

$title = '<span>' . $item->title . '</span>';

$linktype = $item->menu_image
    ? ('<img src="' . $item->menu_image . '" alt="' . $item->title . '" />'
        . ($item->params->get('menu_text', 1) ? $title : ''))
    : $title;

echo Nicepage_Theme_Nicepage::funcTagBuilder('a', $attributes, $linktype);