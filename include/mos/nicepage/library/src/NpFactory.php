<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

JLoader::register('NicepageHelpersNicepage', JPATH_ADMINISTRATOR . '/components/com_nicepage/helpers/nicepage.php');
JLoader::register('NpPage', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/NpPage.php');
JLoader::register('NpConfig', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/src/NpConfig.php');

/**
 * Class NpFactory
 */
abstract class NpFactory
{
    public static $page = null;

    public static $config = null;

    /**
     * Get page object
     *
     * @param null   $pageId  Page id
     * @param string $context Component context
     * @param null   $row     Component row
     * @param null   $params  Component params
     *
     * @return NpPage|null
     */
    public static function getPage($pageId = -1, $context = '', &$row = null, &$params = null)
    {
        if (!self::$page) {
            self::$page = NpPage::getInstance($pageId, $context, $row, $params);
        }

        return self::$page;
    }

    /**
     * Get component plugin
     *
     * @return NpConfig|null
     */
    public static function getConfig()
    {
        if (!self::$config) {
            self::$config = NpConfig::getInstance();
        }

        return self::$config;
    }
}