<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

/**
 * Class NpConfig
 */
class NpConfig
{
    private static $_instance;

    private $_config;
    private $_settigns;

    /**
     * NpConfig constructor.
     */
    public function __construct() {
        $this->_config = NicepageHelpersNicepage::getConfig();
        $this->_settigns = isset($this->_config['siteSettings']) ? json_decode($this->_config['siteSettings'], true) : array();
    }

    /**
     * Apply site settings to content\
     *
     * @param string $pageContent Page content
     *
     * @return mixed
     */
    public function applySiteSettings($pageContent) {
        $settings = $this->_settigns;
        if (isset($settings['captchaScript']) && $settings['captchaScript'] && strpos($pageContent, 'recaptchaResponse') !== false) {
            $pageContent = str_replace('</head>', $settings['captchaScript'] . '</head>', $pageContent);
        }
        if (isset($settings['metaTags']) && $settings['metaTags'] && strpos($pageContent, $settings['metaTags']) === false) {
            $pageContent = str_replace('</head>', $settings['metaTags'] . '</head>', $pageContent);
        }
        if (isset($settings['headHtml']) && $settings['headHtml'] && strpos($pageContent, $settings['headHtml']) === false) {
            $pageContent = str_replace('</head>', $settings['headHtml'] . '</head>', $pageContent);
        }
        if (isset($settings['analyticsCode']) && $settings['analyticsCode'] && strpos($pageContent, $settings['analyticsCode']) === false) {
            $pageContent = str_replace('</head>', $settings['analyticsCode'] . '</head>', $pageContent);
        }
        if (isset($settings['keywords']) && $settings['keywords'] && strpos($pageContent, $settings['keywords']) === false) {
            if (preg_match('/<meta\s+?name="keywords"\s+?content="([^"]+?)"\s+?\/>/', $pageContent, $keywordsMatches)) {
                $pageContent = str_replace($keywordsMatches[0], '<meta name="keywords" content="' . $settings['keywords'] . ', ' . $keywordsMatches[1] . '" />', $pageContent);
            } else {
                $pageContent = str_replace('<title>', '<meta name="keywords" content="' . $settings['keywords'] . '" />' . '<title>', $pageContent);
            }
        }
        if (isset($settings['description']) && $settings['description'] && strpos($pageContent, $settings['description']) === false) {
            if (preg_match('/<meta\s+?name="description"\s+?content="([^"]+?)"\s+?\/>/', $pageContent, $descMatches)) {
                $pageContent = str_replace($descMatches[0], '<meta name="description" content="' . $settings['description'] . ', ' . $descMatches[1] . '" />', $pageContent);
            } else {
                $pageContent = str_replace('<title>', '<meta name="description" content="' . $settings['description'] . '" />' . '<title>', $pageContent);
            }
        }
        return $pageContent;
    }

    /**
     * Get config instance
     * 
     * @return NpConfig
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}