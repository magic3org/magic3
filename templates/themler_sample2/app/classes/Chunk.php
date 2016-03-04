<?php
class Chunk 
{
    public $UPLOAD_PATH;

    private $_lastChunk = null;
    private $_chunkFolder = '';
    private $_lockFile = '';
    private $_isLast = false;

    public function __construct() 
    {
        $this->UPLOAD_PATH = dirname(dirname(__FILE__)) . '/';
        $this->_chunkFolder = $this->UPLOAD_PATH . 'default';
    }

    /**
     * @param $info
     */
    public function save($info) 
    {
        $ret = $this->validate($info);
        if ('' !== $ret)
            trigger_error($ret, E_USER_ERROR);

        $this->_lastChunk = $info;
        $this->_chunkFolder = $this->UPLOAD_PATH . $info['id'];
        $this->_lockFile = $this->_chunkFolder . '/lock';

        if (!is_dir($this->_chunkFolder)) {
            Helper::createDir($this->_chunkFolder);
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
                    trigger_error('Chunk content is empty: ' . print_r($_FILES, true), E_USER_ERROR);
                }
                $content = Helper::readFile($_FILES['content']['tmp_name']);
                Helper::deleteFile($_FILES['content']['tmp_name']);
            }

            Helper::writeFile($this->_chunkFolder . '/' . (int) $info['current'], $content);

            flock($f, LOCK_UN);
            return true;
        } else {
            trigger_error('Couldn\'t lock the file: ' . $this->_lockFile, E_USER_NOTICE);
        }
    }

    /**
     * @return bool
     */
    public function last() 
    {
        return $this->_isLast;
    }

    /**
     * @return string
     */
    public function complete() 
    {
        $content = '';
        for ($i = 1, $count = (int) $this->_lastChunk['total']; $i <= $count; $i++) {
            if (!file_exists($this->_chunkFolder . "/$i"))
                trigger_error('Missing chunk #' . $i . ' : ' . implode(' / ', scandir($this->_chunkFolder)), E_USER_NOTICE);
            $data = Helper::readFile($this->_chunkFolder . "/$i");
            if (!empty($this->_lastChunk['encode']) || !empty($this->_lastChunk['zip'])) {
                $data = base64_decode($data);
            }
            $content .= $data;
        }
        Helper::removeDir($this->_chunkFolder);

        if (!empty($this->_lastChunk['zip'])) {
            $result = $this->unZip($content);
        } else if (!empty($this->_lastChunk['encode'])) {
            $result = array(
                'status' => 'done',
                'data' => rawurldecode($content)
            );
        } else {
            $result = array(
                'status' => 'done',
                'data' => $content
            );
        }

        return $result;
    }

    /**
     * @param $content
     * @return array
     */
    public function unZip($content)
    {
        $templateName = basename(dirname($this->UPLOAD_PATH));
        $tmp = JPATH_SITE . '/templates/' . $templateName . '/tmp';
        Helper::createDir($tmp);
        Helper::writeFile($tmp . '/content.zip', $content);
        Helper::createDir($tmp . '/unzip');

        $result = array('status' => 'done');
        if (version_compare(JVERSION, '3.0', '<')) {
            jimport('joomla.filesystem.archive');
            $ret = JArchive::extract($tmp . '/content.zip', $tmp . '/unzip');
            if ($ret === false) {
                $result = array(
                    'status' => 'error',
                    'message' => 'Invalid type.'
                );
            }
        } else {
            jimport('joomla.filesystem.path');
            try {
                JArchive::extract($tmp . '/content.zip', $tmp . '/unzip');
            } catch (Exception $e) {
                $result = array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
            }
        }
        if ($result['status'] === 'done' && file_exists($tmp . '/unzip/data')) {
            $result['data'] = file_get_contents($tmp . '/unzip/data');
        } else {
            $result['message'] = 'unzip error';
        }
        Helper::removeDir($tmp);
        return $result;
    }
    /**
     * @param $info
     * @return string
     */
    public function validate($info) 
    {
        $errors = array();
        if (!isset($info['id']) || !$info['id'])
            $errors[] = 'Invalid id';
        if (!isset($info['total']) || (int) $info['total'] < 1)
            $errors[] = 'Invalid chunks total';
        if (!isset($info['current']) || (int) $info['current'] < 1)
            $errors[] = 'Invalid current chunk number';
        if (empty($_FILES['content']) && empty($info['content']))
            $errors[] = 'Invalid chunk content';
        if (count($errors) < 1)
            return '';
        else
            return  implode(', ', $errors);
    }
}