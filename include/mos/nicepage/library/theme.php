<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class Nicepage_Theme_Nicepage
 */
class Nicepage_Theme_Nicepage
{
    /**
     * Get logo info from plugin
     *
     * @param array   $defaults      Default values
     * @param boolean $setThemeSizes Set themes sizes
     *
     * @return array
     */
    public static function getLogoInfo($defaults = array(), $setThemeSizes = false)
    {
        $app = JFactory::getApplication();
        $rootPath = str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT);

        $info = array();
        $info['default_width'] = isset($defaults['default_width']) ? $defaults['default_width'] : 0;
        $info['src'] = isset($defaults['src']) ? $defaults['src'] : '';
        $info['src_path'] = '';
        if ($info['src']) {
            $info['src'] = preg_match('#^(http:|https:|//)#', $info['src']) ? $info['src'] :
                JURI::root() . 'templates/' . $app->getTemplate() . $info['src'];
            $info['src_path'] = $rootPath . '/templates/' . $app->getTemplate() . $defaults['src'];
        }
        $info['href'] = isset($defaults['href']) ? $defaults['href'] : JUri::base(true);

        $themeParams = $app->getTemplate(true)->params;
        if ($themeParams->get('logoFile')) {
            $info['src'] = JURI::root() . $themeParams->get('logoFile');
            $info['src_path'] = $rootPath . '/' . $themeParams->get('logoFile');
        }
        if ($themeParams->get('logoLink')) {
            $info['href'] = $themeParams->get('logoLink');
        }

        $parts = explode(".", $info['src_path']);
        $extension = end($parts);
        $isSvgFile = strtolower($extension) == 'svg' ? true : false;

        if ($setThemeSizes) {
            $style = '';
            $themeLogoWidth = $themeParams->get('logoWidth');
            $themeLogoHeight = $themeParams->get('logoHeight');
            if ($themeLogoWidth) {
                $style .= "max-width: " . $themeLogoWidth . "px !important;\n";
            }
            if ($themeLogoHeight) {
                $style .= "max-width: " . $themeLogoHeight . "px !important;\n";
            }
            $style .= "width: " . ($isSvgFile ? ($themeLogoWidth ? $themeLogoWidth : $info['default_width']) . "px" : "auto") . " !important\n";
            if ($style) {
                $document = JFactory::getDocument();
                $document->addStyleDeclaration('.u-logo img {' . $style . '}');
            }
        }

        return $info;
    }

    /**
     * Get theme params by name
     *
     * @param string $name Name of option
     *
     * @return string
     */
    public static function getThemeParams($name) {
        $template = JFactory::getApplication()->getTemplate();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__template_styles')
            ->where('template = ' . $db->quote($template))
            ->where('client_id = 0');
        $db->setQuery($query);
        $templates = $db->loadObjectList('id');

        if (count($templates) < 1) {
            return '';
        }

        $site = JFactory::getApplication('site');
        $menu = $site->getMenu('site');
        $item = $menu->getActive();

        $id         = is_object($item) ? $item->template_style_id : 0;
        $template   = isset($templates[$id]) ? $templates[$id] : array_shift($templates);

        $registry = new JRegistry();
        $registry->loadString($template->params);
        return $registry->get($name, '');
    }

    /**
     * Build tag
     *
     * @param string $tag        Tag name
     * @param array  $attributes Array attrs
     * @param string $content    Content
     *
     * @return string
     */
    public static function funcTagBuilder($tag, $attributes = array(), $content = '') {
        $result = '<' . $tag;
        foreach ($attributes as $name => $value) {
            if (is_string($value)) {
                if (!empty($value)) {
                    $result .= ' ' . $name . '="' . $value . '"';
                }
            } else if (is_array($value)) {
                $values = array_filter($value);
                if (count($values)) {
                    $result .= ' ' . $name . '="' . implode(' ', $value) . '"';
                }
            }
        }
        $result .= '>' . $content . '</' . $tag . '>';
        return $result;
    }
}