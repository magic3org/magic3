<?php

class LogFormatter
{
    function args($args)
    {
        $result = array();
        foreach ($args as $index => $value)
            $result[] = LogFormatter::_formatVar($value);
        return implode(', ', $result);
    }

    function _formatVar(&$value)
    {
        if (is_null($value))
            return LogFormatter::_formatNull($value);
        if (is_bool($value))
            return LogFormatter::_formatBool($value);
        if (is_int($value) || is_float($value))
            return $value;
        if (is_object($value))
            return LogFormatter::_formatObject($value);
        // is_callable should be placed before is_string and is_array:
        if (is_callable($value))
            return LogFormatter::_formatCallback($value);
        if (is_string($value))
            return LogFormatter::_formatString($value);
        if (is_array($value))
            return LogFormatter::_formatArray($value);
        return gettype($value) . ' { ... }';
    }

    function _formatNull()
    {
        return 'NULL';
    }

    function _formatString(&$value)
    {
        $text = $value;
        if (strlen($text) > 100)
            $text = substr($text, 0, 100) . '...';
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('\'', '\\\'', $text);
        return '\'' . $text . '\'';
    }

    function _formatArray(&$value)
    {
        return 'array(' . count($value) . ') { ... }';
    }

    function _formatObject(&$value)
    {
        return get_class($value) . '(...) { ... }';
    }

    function _formatBool(&$value)
    {
        return $value ? 'true' : 'false';
    }

    function _formatCallback(&$value)
    {
        if (is_string($value))
            return $value . '(...)';
        if (is_array($value))
            return (is_string($value[0]) ? ($value[0] . '::') : (get_class($value[0]) . '->')) . $value[1];
        return '#internal error';
    }
}