<?php

defined('_JEXEC') or die;

if (!defined('_M3BOOTSTRAP_FUNCTIONS')) {
    define('_M3BOOTSTRAP_FUNCTIONS', 1);

	function m3TagBuilder($tag, $attributes = array(), $content = '') {
        $result = '<' . $tag;
        foreach ($attributes as $name => $value) {
            if (is_string($value)) {
                if (!empty($value))
                    $result .= ' ' . $name . '="' . $value . '"';
            } else if (is_array($value)) {
                $values = array_filter($value);
                if (count($values))
                    $result .= ' ' . $name . '="' . implode(' ', $value) . '"';
            }
        }
        $result .= '>' . $content . '</' . $tag . '>';
        return $result;
    }
}
