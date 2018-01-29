<?php

Designer::load('Designer_Data_CategoryMapper');
Designer::load('Designer_Data_ContentMapper');
Designer::load('Designer_Data_ExtensionMapper');
Designer::load('Designer_Data_MenuItemMapper');
Designer::load('Designer_Data_MenuMapper');
Designer::load('Designer_Data_ModuleMapper');

class Designer_Data_Mappers
{
    public static function errorCallback($callback, $get = false)
    {
        static $errorCallback;
        if (!$get)
            $errorCallback = $callback;
        return $errorCallback;
    }

    public static function get($name)
    {
        $className = 'Designer_Data_' . ucfirst($name) . 'Mapper';
        $mapper = new $className();
        return $mapper;
    }

    public static function error($error, $code)
    {
        $null = null;
        $callback = Designer_Data_Mappers::errorCallback($null, true);
        if (isset($callback))
            call_user_func($callback, $error, $code);
        return $error;
    }
}