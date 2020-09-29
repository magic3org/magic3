<?php

class CoreStatements {

    public static function containsModules()
    {
        $doc = JFactory::getDocument();
        foreach (func_get_args() as $position)
            if (0 != $doc->countModules($position))
                return true;
        return false;
    }

    public static function position($position, $style = null, $id = '', $variation = '')
    {
        if (!self::containsModules($position)) {
            return '';
        }

        $attributes = array(
            'type'      => 'modules',
            'name'      => $position,
            'style'     => 'upstyle',
            'variation' => $variation,
            'upstyle'   => (null != $style ?  $style : ''),
            'title'     => 'name-' . $id,
            'id'        => $id,
            'count'     => JFactory::getDocument()->countModules($position)
        );

        $config = JFactory::getConfig();
        $isProgressiveCaching = $config->get('caching') && $config->get('caching', 2) == 2 ? true : false;
        if ($isProgressiveCaching) {
            unset($attributes['title']);
        }

        $str = '';
        foreach ($attributes as $key => $value) {
            $str .= $key . '="' . $value . '" ';
        }
        return '<jdoc:include ' . $str . '/>';
    }

    public static function head()
    {
        return '<jdoc:include type="head" />';
    }

    public static function message()
    {
        return '<jdoc:include type="message" />';
    }

    public  static function component()
    {
        return '<jdoc:include type="component" />';
    }
}