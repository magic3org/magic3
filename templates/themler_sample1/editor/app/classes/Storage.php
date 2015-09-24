<?php
abstract class Storage
{
    protected $_file;
    protected $_data = array();

    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->_file = $file;
        if (file_exists($this->_file)) {
            $content = Helper::readFile($this->_file);
            $ret = json_decode($content, true);
            if (null !== $ret)
                $this->_data = $ret;
        }
    }

    /**
     * @return mixed
     */
    public function toJson()
    {
        return json_encode($this->_data);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * @return $this
     */
    public function get()
    {
        return $this;
    }

    public function save()
    {
        $content = json_encode($this->_data);
        Helper::writeFile($this->_file, $content);
    }

    abstract protected function refresh($data = array());
}
    
class Cache extends Storage
{
    /**
     * @param $file
     */
    public function __construct($file)
    {
        parent::__construct($file);
    }

    /**
     * @param array $data
     * @param bool $new
     */
    public function refresh($data = array(), $new = false)
    {
        if ($new) $this->_data = array();

        foreach ($data as $controlName => $storage) {
            if (!is_array($storage))
                continue;
            foreach ($storage as $file => $content) {
                if ('[DELETED]' === $content) {
                    if (isset($this->_data[$controlName]) && isset($this->_data[$controlName][$file])){
                        unset($this->_data[$controlName][$file]);
                        if (count($this->_data[$controlName]) < 1)
                            unset($this->_data[$controlName]);
                        $t = 'test';
                    }
                } else {
                    $this->_data[$controlName][$file] = $content;
                }
            }
        }
    }
}
    
class Hash extends Storage
{
    /**
     * @param $file
     */
    public function __construct($file)
    {
        parent::__construct($file);
    }

    /**
     * @param array $data
     * @param bool $new
     */
    public function refresh($data = array(), $new = false)
    {
        if ($new) $this->_data = array();

        foreach ($data as $file => $hash) {
            if ('[DELETED]' === $hash) {
                if (isset($this->_data[$file]))
                    unset($this->_data[$file]);
            } else {
                $this->_data[$file] = $hash;
            }
        }
    }
}
    
class PreviewDiffs extends Storage
{
    /**
     * @param $file
     */
    public function __construct($file)
    {
        parent::__construct($file);
    }

    /**
     * @param array $data
     */
    public function refresh($data = array())
    {
        if (!is_array($data)) return;

        $this->_data = $data;
    }
}

class FileDiffs extends Storage
{
    /**
     * @param $file
     */
    public function __construct($file)
    {
        parent::__construct($file);
    }

    /**
     * @param array $data
     */
    public function refresh($data = array())
    {
        if (!is_array($data)) return;

        foreach ($data as $file => $content) {
            $this->_data[$file] = $content;
        }
    }

    public function clean()
    {
        $this->_data = array();
    }

    public function destroy()
    {
        Helper::deleteFile($this->_file);
    }
}

class  Project extends Storage
{
    /**
     * @param $file
     */
    public function __construct($file)
    {
        parent::__construct($file);
    }

    /**
     * @param array $data
     * @param bool $new
     */
    public function refresh($data = array(), $new = false)
    {
        if ($new) $this->_data = array();

        foreach ($data as $key => $value) {
            $this->_data[$key] = $value;
        }
    }

    public function getValue($key) {
        $value = '';
        if (isset($this->_data[$key]))
            $value = $this->_data[$key];
        return $value;
    }
}
?>