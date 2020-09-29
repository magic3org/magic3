<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class Nicepage_Image_Processor
 */
class Nicepage_Image_Processor
{
    /**
     * @var array
     */
    private $_postImages = array();

    /**
     * @param string $content Page content
     *
     * @return mixed
     */
    public function prepareImages($content)
    {
        if ('' === $content) {
            return $content;
        }
        $regexs = array('/src=["\']?([^\'"]+)["\']/', '/url\((["\']?([^\'"]*?)["\']?)\)/', '/image=["\']?([^\'"]*)["\']/');
        foreach ($regexs as $regex) {
            $content = preg_replace_callback($regex, array(&$this, '_proccessImages'), $content);
        }
        return $content;
    }

    /**
     * Get image items
     *
     * @return array
     */
    public function getImages()
    {
        return $this->_postImages;
    }

    /**
     * @param array $match Match for images
     *
     * @return mixed
     */
    private function _proccessImages($match)
    {
        $full = $match[0];
        $path = $match[1];

        if (preg_match('/^' . htmlentities('"') . '(.+)' . htmlentities('"') . '$/', $path, $newmatch)) {
            $path = $newmatch[1];
        }

        return $this->_proccessImage($path, $full);
    }

    /**
     * @param string $path Image path
     * @param string $full Full path
     *
     * @return mixed
     */
    private function _proccessImage($path, $full)
    {
        $root = dirname(dirname(JURI::current()));
        if (preg_match('/^http/', $path) && strpos($full, $root) == false) {
            return $full;
        }

        if ('' !== $path) {
            $firstSymbol = '';
            if ($path[0] == '/' || $path[0] == '\\') {
                $path = substr($path, 1);
                $firstSymbol = $path[0];
            }
            $filePath = JPATH_SITE . '/' . $path;
            if (file_exists($filePath) && !in_array($filePath, $this->_postImages)) {
                $this->_postImages[] = $filePath;
                return str_replace($firstSymbol . $path, $root . '/' . $path, $full);
            }

            if (strpos($path, $root) !== -1) {
                $file = str_replace($root, JPATH_SITE, $path);
                if (file_exists($file) && !in_array($file, $this->_postImages)) {
                    $this->_postImages[] = $file;
                }
                return $full;
            }
        }
        return $full;
    }
}