<?php

defined('_JEXEC') or die;

class DesignerEditor
{
    /**
     * @param $template
     * @param $file
     * @return bool|string
     */
    public static function override($template, $file)
    {
        $editor = '/templates/' . $template . '/editor';
        $fileName = basename($file);
        $dirs = array();
        while (JPATH_ROOT != dirname($file)) {
            $file = dirname($file);
            array_push($dirs, '/' . basename($file));
        }

        if ($editor !== implode('', array_reverse(array_slice($dirs, -3)))) {
            $dirs = array_slice($dirs, 0, -2);
            $override = JPATH_ROOT . $editor . implode('', array_reverse($dirs)) . '/' . $fileName;
            if ('on' === JRequest::getCmd('is_preview')) {
                if (file_exists($override))
                    return $override;
            }
        }
        return false;
    }
}