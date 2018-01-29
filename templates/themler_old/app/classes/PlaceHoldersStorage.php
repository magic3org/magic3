<?php

class PlaceHoldersStorage
{
    private $_storage;
    private $_index;
    private $_stack = array();
    private $_openBracket;
    private $_closeBracket;

    public $parent;

    /**
     * @param null $placeholders
     * @param string $openBracket
     * @param string $closeBracket
     */
    public function __construct($placeholders = null, $openBracket = '{', $closeBracket = '}')
    {
        $this->_openBracket = $openBracket;
        $this->_closeBracket = $closeBracket;

        if (!isset($placeholders)) {
            $this->parent = null;
            $this->_storage = array();
        } else if (is_callable($placeholders) || (is_object($placeholders)
                    && 'PlaceHoldersStorage' === get_class($placeholders)
                    && method_exists($placeholders,'get'))) {
            $this->parent = $placeholders;
            $this->_storage = array();
        } else {
            $this->parent = null;
            $this->_storage = $placeholders;
        }

        $this->_index = 1;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ( $this->{$method} instanceof Closure ) {
            return call_user_func_array($this->{$method},$args);
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function create($value)
    {
        $key = 'var' . $this->_index;
        $this->_index += 1;
        $this->set($key, $value);
        return $this->_openBracket . $key . $this->_closeBracket;
    }

    /**
     * @param $key
     * @param string $value
     */
    public function set($key, $value = '')
    {
        if (is_array($key)) {
            foreach ($key as $k => $v){
                $this->set($k, $v);
            }
            return;
        }
        if ($this->_validate($key)) {
            $this->_storage[$key] = $value;
        } else {
            trigger_error('The "' . $key . '" placeholder is not added to' +
                ' the collection because of unsupported name.', E_USER_ERROR);
        }
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->_storage)) {
            $value = $this->_storage[$key];
        } else {
           $value = (null === $this->parent) ? '' :
                (is_callable($this->parent) ? $this->parent($key) : $this->parent->get($key));
        }
        return $value;
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        if (isset($this->_storage[$key])) {
            unset($this->_storage[$key]);
        }
    }

    /**
     * @return null
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function replace($text)
    {
        return $this->replacer($text);
    }

    /**
     * @param $text
     * @return mixed
     */
    public function replacer($text)
    {
        $openBracket = preg_quote($this->_openBracket);
        $closeBracket = preg_quote($this->_closeBracket);
        return preg_replace_callback("/$openBracket([a-z0-9_]+)$closeBracket/i", array(&$this, '_processContent'), $text);
    }

    /**
     * @param $matches
     * @return mixed
     * @throws Exception
     */
    private function _processContent($matches)
    {
        $key = $matches[1];
        if (in_array($key, $this->_stack)) {
            throw new Exception('Circular reference in placeholder values.');
        }

        array_push($this->_stack, $key);
        $result = $this->replacer($this->get($key));
        array_pop($this->_stack);

        return '' !== $result ? $result : $matches[0];
    }

    /**
     * @param $name
     * @return mixed
     */
    private function _validate($name)
    {
        return preg_match('/^[\w\d_\-]+$/', $name);
    }
}