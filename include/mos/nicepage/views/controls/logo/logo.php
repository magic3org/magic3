<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

if (isset($controlProps) && isset($controlTemplate)) {
    $logoInfo = Nicepage_Theme_Nicepage::getLogoInfo(
        array(
            'src' => $controlProps['src'],
            'href' => $controlProps['href'],
            'default_width' => $controlProps['defaultWidth'],
        ),
        true
    );
    $controlTemplate = str_replace('[[url]]', $logoInfo['href'], $controlTemplate);
    $controlTemplate = str_replace('[[src]]', $logoInfo['src'], $controlTemplate);
    echo $controlTemplate;
}