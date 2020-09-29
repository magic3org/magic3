<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class Nicepage_Data_Chunk
 */
class Nicepage_Data_Chunk
{
    public $UPLOAD_PATH;
    private $_lastChunk = null;
    private $_chunkFolder = '';
    private $_lockFile = '';
    private $_isLast = false;

    /**
     * Nicepage_Data_Chunk constructor.
     */
    public function __construct()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $this->UPLOAD_PATH = JFactory::getConfig()->get('tmp_path') . '/';
        $this->_chunkFolder = $this->UPLOAD_PATH . 'default';
    }

    /**
     * Get chunk info
     *
     * @param array $data Chunk data
     *
     * @return array
     */
    private function _getChunkInfo($data)
    {
        return array(
            'id'      => $data->get('id', '', 'RAW'),
            'content' => $data->get('content', '', 'RAW'),
            'current' => $data->get('current', '', 'RAW'),
            'total'   => $data->get('total', '', 'RAW'),
            'blob'    => $data->get('blob', '', 'RAW') == 'true' ? true : false,
        );
    }

    /**
     * Save chunk
     *
     * @param array $data Chunk info
     *
     * @return array|bool
     */
    public function save($data)
    {
        $info = $this->_getChunkInfo($data);
        $ret = $this->validate($info);
        if ('' !== $ret) {
            return array(
                'status' => 'error',
                'data' => $ret
            );
        }

        $this->_lastChunk = $info;
        $this->_chunkFolder = $this->UPLOAD_PATH . $info['id'];
        $this->_lockFile = $this->_chunkFolder . '/lock';

        if (!is_dir($this->_chunkFolder)) {
            JFolder::create($this->_chunkFolder);
        }

        $f = fopen($this->_lockFile, 'c');

        if (flock($f, LOCK_EX)) {
            $chunks = array_diff(scandir($this->_chunkFolder), array('.', '..', 'lock'));

            if ((int) $this->_lastChunk['total'] === count($chunks) + 1) {
                $this->_isLast = true;
            }

            $content = $info['content'];

            if (!empty($this->_lastChunk['blob'])) {
                if (empty($_FILES['content']['tmp_name'])) {
                    return array(
                        'status' => 'error',
                        'data' => 'Chunk content is empty: ' . print_r($_FILES, true)
                    );
                }
                $content = JFile::read($_FILES['content']['tmp_name']);
                JFile::delete($_FILES['content']['tmp_name']);
            }

            JFile::write($this->_chunkFolder . '/' . (int) $info['current'], $content);

            flock($f, LOCK_UN);
            return true;
        } else {
            return array(
                'status' => 'error',
                'data' => 'Couldn\'t lock the file: ' . $this->_lockFile
            );
        }
    }

    /**
     * Checking chunk for last
     *
     * @return bool
     */
    public function last()
    {
        return $this->_isLast;
    }

    /**
     * Complete content
     *
     * @return array
     */
    public function complete()
    {
        $content = '';
        for ($i = 1, $count = (int) $this->_lastChunk['total']; $i <= $count; $i++) {
            if (!file_exists($this->_chunkFolder . "/$i")) {
                return array(
                    'status' => 'error',
                    'data' => 'Missing chunk #' . $i . ' : ' . implode(' / ', scandir($this->_chunkFolder))
                );
            }
            $data = JFile::read($this->_chunkFolder . "/$i");
            $content .= $data;
        }
        JFolder::delete($this->_chunkFolder);

        return array(
            'status' => 'done',
            'data' => $content
        );
    }

    /**
     * Validate chunk info
     *
     * @param array $info Chunk info
     *
     * @return string
     */
    public function validate($info)
    {
        $errors = array();
        if (!isset($info['id']) || !$info['id']) {
            $errors[] = 'Invalid id';
        }
        if (!isset($info['total']) || (int) $info['total'] < 1) {
            $errors[] = 'Invalid chunks total';
        }
        if (!isset($info['current']) || (int) $info['current'] < 1) {
            $errors[] = 'Invalid current chunk number';
        }
        if (empty($_FILES['content']) && empty($info['content'])) {
            $errors[] = 'Invalid chunk content';
        }
        if (count($errors) < 1) {
            return '';
        } else {
            return implode(', ', $errors);
        }
    }

    /**
     * Remove chunk by id
     *
     * @param string $id Chunk id
     *
     * @return bool
     */
    public static function clearChunksById($id) {
        $chunkUploadPath = JFactory::getConfig()->get('tmp_path') . '/' . $id;
        if ($id && is_dir($chunkUploadPath)) {
            jimport('joomla.filesystem.folder');
            JFolder::delete($chunkUploadPath);
            return true;
        } else {
            return false;
        }
    }
}