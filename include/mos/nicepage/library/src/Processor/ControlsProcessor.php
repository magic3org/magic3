<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

class ControlsProcessor
{
    public static $controlName = '';
    /**
     * Process all custom controls on the header
     *
     * @param string $content content
     *
     * @return mixed
     */
    public static function process($content) {
        $controls = ['menu', 'logo', 'headline', 'search', 'login'];
        foreach ($controls as $value) {
            self::$controlName = $value;
            $content =  preg_replace_callback(
                '/<\!--np_' . $value . '--><!--np_json-->([\s\S]+?)<\!--\/np_json-->([\s\S]*?)<\!--\/np_' . $value . '-->/',
                'self::processControl',
                $content
            );
        }
        return $content;
    }

    /**
     * Process control
     *
     * @param array $matches Matches
     *
     * @return false|string
     */
    public static function processControl($matches) {
        $controlProps = json_decode(trim($matches[1]), true);
        $controlTemplate = $matches[2];
        ob_start();
        include JPATH_ADMINISTRATOR . '/components/com_nicepage/views/controls/'. self::$controlName . '/' . self::$controlName . '.php';
        return ob_get_clean();
    }
}