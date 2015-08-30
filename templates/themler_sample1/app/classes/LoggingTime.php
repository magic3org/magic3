<?php
class LoggingTime
{
    private static $instance;
    private $_log = array();

    private function __construct(){}
    private function __clone()    {}

    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function start($key = '')
    {
        $this->_log[] = array(
            'key' => $key,
            'type' => 'start',
            'time' => microtime(true) * 1000
        );
    }

    public function end($key = '')
    {
        $this->_log[] = array(
            'key' => $key,
            'type' => 'end',
            'time' => microtime(true) * 1000
        );
    }

    public function getLog()
    {
        return $this->_log;
    }
}
?>