<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

/**
 * Class TagBalancer
 */
class TagBalancer {

    public static $scriptStorage = array();
    public static $scriptIndex = 0;

    /**
     * @param array $match Matches
     *
     * @return string
     */
    public static function beginScriptReplacer($match) {
        self::$scriptIndex++;
        $key = '[[BDSCRIPT' . self::$scriptIndex . ']]';
        self::$scriptStorage[$key] = $match[0];
        return $key;
    }

    /**
     * @param array $match Matches
     *
     * @return mixed
     */
    public static function endScriptReplacer($match) {
        return self::$scriptStorage[$match[0]];
    }

    /**
     * Reset storage
     */
    public static  function resetBalanceStorage() {
        self::$scriptStorage = array();
        self::$scriptIndex = 0;
    }

    /**
     * @param string $text Artcile text
     *
     * @return string|string[]|null
     */
    public static function process($text) {
        self::resetBalanceStorage();
        $text = preg_replace_callback('/<script[^>]*>([\s\S]*?)<\/script>/', 'self::beginScriptReplacer', $text);

        $singleTags = array('area', 'base', 'basefont', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param', 'source');
        $nestedTags = array('blockquote', 'div', 'object', 'q', 'span');

        $stack = array();
        $size = 0;
        $queue = '';
        $output = '';

        while (preg_match("/<(\/?[\w:]*)\s*([^>]*)>/", $text, $match)) {
            $output .= $queue;

            $i = strpos($text, $match[0]);
            $l = strlen($match[0]);

            $queue = '';

            if (isset($match[1][0]) && '/' == $match[1][0]) {
                // processing of the end tag
                $tag = strtolower(substr($match[1], 1));

                if ($size <= 0) {
                    $tag = '';
                } else if ($stack[$size - 1] == $tag) {
                    $tag = '</' . $tag . '>';
                    array_pop($stack);
                    $size--;
                } else {
                    for ($j = $size-1; $j >= 0; $j--) {
                        if ($stack[$j] == $tag) {
                            for ($k = $size-1; $k >= $j; $k--) {
                                $queue .= '</' . array_pop($stack) . '>';
                                $size--;
                            }
                            break;
                        }
                    }
                    $tag = '';
                }
            } else {
                // processing of the begin tag
                $tag = strtolower($match[1]);

                if (substr($match[2], -1) == '/') {
                    if (!in_array($tag, $singleTags)) {
                        $match[2] = trim(substr($match[2], 0, -1)) . "></$tag";
                    }
                } elseif (in_array($tag, $singleTags)) {
                    $match[2] .= '/';
                } else {
                    if ($size > 0 && !in_array($tag, $nestedTags) && $stack[$size - 1] == $tag) {
                        $queue = '</' . array_pop($stack) . '>';
                        $size--;
                    }
                    $size = array_push($stack, $tag);
                }

                // attributes
                $attributes = $match[2];
                if (!empty($attributes) && $attributes[0] != '>') {
                    $attributes = ($tag ? ' ' : '') . $attributes;
                }

                $tag = '<' . $tag . $attributes . '>';

                if (!empty($queue)) {
                    $queue .= $tag;
                    $tag = '';
                }
            }
            $output .= substr($text, 0, $i) . $tag;
            $text = substr($text, $i + $l);
        }

        $output .= ($queue . $text);

        while ($t = array_pop($stack)) {
            $output .= '</' . $t . '>';
        }

        $output = preg_replace_callback('/\[\[BDSCRIPT[0-9]+\]\]/', 'self::endScriptReplacer', $output);
        return $output;
    }
}