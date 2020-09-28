<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

if (isset($controlProps) && isset($controlTemplate)) {
    $controlTemplate = str_replace('[[action]]', JFactory::getDocument()->baseurl, $controlTemplate);

    JFactory::getLanguage()->load('mod_search', JPATH_SITE);
    $placeholder = htmlspecialchars(JText::_('MOD_SEARCH_SEARCHBOX_TEXT'), ENT_COMPAT, 'UTF-8');
    $controlTemplate = str_replace('[[placeholder]]', $placeholder, $controlTemplate);

    echo $controlTemplate;
}