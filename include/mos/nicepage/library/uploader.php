<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class Nicepage_File_Chunk_Uploader
 */
class Nicepage_File_Chunk_Uploader
{
    /**
     * @param string $uploadPath Upload path
     * @param bool   $isLast     Last chunk flag
     *
     * @return array
     */
    public function upload($uploadPath, $isLast)
    {
        $files = JFactory::getApplication()->input->files;
        $chunk = $files->get('chunk');
        if (!$chunk || !file_exists($chunk['tmp_name'])) {
            trigger_error('Empty chunk data', E_USER_ERROR);
        }

        $contentRange = $_SERVER['HTTP_CONTENT_RANGE'];
        if ('' === $contentRange && '' === $isLast) {
            trigger_error('Empty Content-Range header', E_USER_ERROR);
        }

        $rangeBegin = 0;

        if ($contentRange) {
            $contentRange = str_replace('bytes ', '', $contentRange);
            list($range, $total) = explode('/', $contentRange);
            list($rangeBegin, $rangeEnd) = explode('-', $range);
        }

        $tmpPath = dirname($uploadPath) . '/uptmp/' . basename($uploadPath);
        JFolder::create(dirname($tmpPath));

        $f = fopen($tmpPath, 'c');

        if (flock($f, LOCK_EX)) {
            fseek($f, (int) $rangeBegin);
            fwrite($f, file_get_contents($chunk['tmp_name']));

            flock($f, LOCK_UN);
            fclose($f);
        }

        $result = array(
            'status' => 'processed'
        );
        if ($isLast) {
            if (file_exists($uploadPath)) {
                $uploadPath = $this->_getNewUploadPath($uploadPath);
            }
            JFolder::create(dirname($uploadPath));
            JFile::move($tmpPath, $uploadPath);
            JFolder::delete(dirname($tmpPath));
            $result = array(
                'status' => 'done',
                'fileName' => basename($uploadPath),
                'path' => $uploadPath,
            );
        }
        return $result;
    }

    /**
     * Get new file path
     *
     * @param string $filePath File path
     *
     * @return mixed
     */
    private function _getNewUploadPath($filePath) {
        $baseName = basename($filePath);
        $fileParts = explode('.', $baseName);
        $fileName = $fileParts[0];
        $fileExt = $fileParts[1];
        $i = 1;
        do {
            $newBaseName = $fileName . '-' . $i . '.' . $fileExt;
            $newFilePath = str_replace($baseName, $newBaseName, $filePath);
            $i++;
        } while (file_exists($newFilePath) && $i < 20);
        return $newFilePath;
    }
}