<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

JLoader::register('TagBalancer', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/balancer.php');
/**
 * Class DataBuilder
 */
abstract class DataBuilder
{
    /**
     * Get title data
     */
    abstract protected function title();

    /**
     * Get title link data
     */
    abstract protected function titleLink();

    /**
     * Get content data
     */
    abstract protected function content();

    /**
     * Get image data
     */
    abstract protected function image();

    /**
     * Create excerpt
     *
     * @param string $text       Article content
     * @param int    $max_length Max count
     * @param string $cut_off    Cut off text
     * @param bool   $keep_word  Keep word
     *
     * @return false|string|string[]|null
     */
    public function excerpt($text, $max_length = 140, $cut_off = '...', $keep_word = false)
    {
        if (strlen($text) > $max_length) {
            if ($keep_word) {
                $text = substr($text, 0, $max_length + 1);
                if ($last_space = strrpos($text, ' ')) {
                    $text = substr($text, 0, $last_space);
                    $text = rtrim($text);
                    $text .=  $cut_off;
                }
            } else {
                $text = substr($text, 0, $max_length);
                $text = rtrim($text);
                $text .=  $cut_off;
            }
        }
        $allowed_tags = array(
            '<a>',
            '<abbr>',
            '<blockquote>',
            '<b>',
            '<cite>',
            '<pre>',
            '<code>',
            '<em>',
            '<label>',
            '<i>',
            '<p>',
            '<strong>',
            '<ul>',
            '<ol>',
            '<li>',
            '<h1>',
            '<h2>',
            '<h3>',
            '<h4>',
            '<h5>',
            '<h6>',
            '<object>',
            '<param>',
            '<embed>'
        );
        $text = strip_tags($text, join('', $allowed_tags));
        $text = TagBalancer::process($text);
        return $text;
    }
}