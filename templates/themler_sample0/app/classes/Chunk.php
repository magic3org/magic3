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
        $validationResult = $this->validate($info);
        if ('' !== $validationResult)
            trigger_error($validationResult, E_USER_ERROR);

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
                    return false;
                }
                $content = file_get_contents($_FILES['content']['tmp_name']);
                unlink($_FILES['content']['tmp_name']);
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
            if (!empty($this->_lastChunk['encode'])) {
                $data = base64_decode($data);
            }
            $content .= $data;
        }
        Helper::removeDir($this->_chunkFolder);
        return empty($this->_lastChunk['encode']) ? $content : rawurldecode($content);
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