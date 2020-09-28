<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

if (isset($controlProps)) {
    $doc = JFactory::getDocument();
    if (strpos($controlProps['position'], 'hmenu-') !== false && !$doc->countModules($controlProps['position'])) {
        $controlProps['position'] = 'hmenu';
    }
    echo $doc->getBuffer('modules', $controlProps['position'], $controlProps['attr']);
}