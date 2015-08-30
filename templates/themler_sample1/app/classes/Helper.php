<?php

error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

require_once dirname(dirname(EXPORT_APP)) . '/library/Designer/LogFormatter.php';
/**
 * @param $type
 * @return string
 */
function friendlyErrorType($type)
{
    switch($type)
    {
        case E_ERROR: // 1
            return 'E_ERROR';
        case E_WARNING: // 2
            return 'E_WARNING';
        case E_PARSE: // 4
            return 'E_PARSE';
        case E_NOTICE: // 8
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32
            return 'E_CORE_WARNING';
        case E_CORE_ERROR: // 64
            return 'E_COMPILE_ERROR';
        case E_CORE_WARNING: // 128
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384
            return 'E_USER_DEPRECATED';
    }
    return "";
}

if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {
        if ($code !== NULL) {
            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
        return $code;
    }
}

function getSystemInfo() {
    $info = array();
    $info[] = 'OS: ' . php_uname();
    $info[] = 'PHP: ' . phpversion();
    return implode("\n", $info);
}
/**
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @param $errcontext
 * @return bool
 */
function errorHandlerExportTheme($errno, $errstr, $errfile, $errline, $errcontext)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
    ob_start();
    switch ($errno) {
        case E_USER_ERROR:
            ob_start();
            echo "USER ERROR: $errstr\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
            echo "Aborting...\n";
            break;
        case E_USER_WARNING:
            echo "USER WARNING: $errstr<br />\n";
            break;
        case E_USER_NOTICE:
            echo "USER NOTICE: $errstr<br />\n";
            break;
        default:
            $errorType = friendlyErrorType($errno);
            echo ("" !== $errorType ? "$errorType: " : "Unknown error type: ") . "$errstr $errfile $errline\n";
            break;
    }
    $error = ob_get_clean();

    $bt = debug_backtrace();
    $formatter = new LogFormatter();
    $callstack = '';
    foreach($bt as $key => $caller) {
        if (0 === $key) continue;
        $callstack .= "   -> ";
        if (isset($caller['class']))
            $callstack .= $caller['class'] . '::';
        $callstack .= $caller['function'] . '(' . $formatter->args($caller['args']) . ')';
        $callstack .= "\n";
    }
    $error = "\n\n" . getSystemInfo() . "\n" . '[ERROR HANDLER] ' . $error . "\n" . print_r($errcontext, true) . "  Callstack: \n" . $callstack . "\n\n";
    http_response_code(500);
    exit($error);

    /* Don't execute PHP internal error handler */
    return true;
}

set_error_handler('errorHandlerExportTheme', E_ALL);

// Will be called when php script ends
function shutdownHandler()
{
    if (isset($GLOBALS['shutdown_func_is_defined']) && 1 == $GLOBALS['shutdown_func_is_defined'])
        return;
    $errorText = '';
    $error = error_get_last();
    switch ($error['type'])
    {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            $errorText = "\n\n" . getSystemInfo() . "\n" . "[SHUTDOWN] type: " . $error['type'] . " | message: " . $error['message'] . " | file: " . $error['file'] . " | line: " . $error['line'];
    }
    if ('' !== $errorText) {
        http_response_code(500);
        exit($errorText);
    }
}

register_shutdown_function("shutdownHandler");

/**
 * @param $className
 */
function classAutoLoader($className)
{
    $filePath = dirname(__FILE__) . '/' . $className . '.php';
    if (file_exists($filePath))
        require_once $className . '.php';
}

spl_autoload_register('classAutoLoader');

/**
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @param $errcontext
 * @return bool
 */
function permissionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    throw new PermissionsException($errcontext['message'] . $errcontext['path']);
}

class Helper {
    /**
     * @param $path
     * @return array
     */
    public static function enumerateRecursive($path)
    {
        $files = array();
        if (!file_exists($path) || self::isEmptyDir($path))
            return $files;
        $flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, $flags));
        foreach ($iterator as $fileInfo) {
            $files[$iterator->getSubPathname()] = Helper::readFile($fileInfo->getPathName());
        }
        return $files;
    }

    /**
     * @param $path
     * @return array
     */
    public static function enumerate($path)
    {
        $files = array();
        if (!file_exists($path) || self::isEmptyDir($path))
            return $files;
        $flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        $iterator = new IteratorIterator(new FilesystemIterator($path, $flags));
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile())
                $files[$fileInfo->getFilename()] = Helper::readFile($fileInfo->getPathName());
        }
        return $files;
    }

    /**
     * @param $dir
     * @param bool $self
     */
    public static function removeDir($dir, $self = true)
    {
        if(!file_exists($dir) || !is_dir($dir))
            return;
        if(!is_readable($dir))
            return;

        $dh = opendir($dir);
        while (($contents = readdir($dh)) !== false) {
            if($contents != '.' && $contents != '..') {
                $path = $dir . '/'. $contents;
                if(is_dir($path))
                    Helper::removeDir($path, true);
                else
                    Helper::deleteFile($path);
            }
        }
        closedir($dh);

        if ($self) Helper::deleteFolder($dir);
    }

    /**
     * @param $fso
     * @param string $relative
     * @return mixed
     */
    public static function buildThemeStorage($fso, $relative = '')
    {
        static $storage = array();

        if(!array_key_exists('items', $fso) || !is_array($fso['items'])) {
            return;
        }
        foreach ($fso['items'] as $name => $file) {
            if(isset($file['content']) && isset($file['type'])) {
                switch ($file['type']) {
                    case 'text':
                        $storage[$relative . '/' . $name] = $file['content'];
                        break;
                    case 'data':
                        $storage[$relative . '/' . $name] = base64_decode($file['content']);
                        break;
                }
            } elseif(isset($file['items']) && isset($file['type'])) {
                Helper::buildThemeStorage($file, $relative . '/' . $name);
            }
        }
        return $storage;
    }

    /**
     * @param $path
     */
    public static function createDir($path)
    {
        if (!file_exists($path)) {
            $message = 'Unable to create the directory: ';
            set_error_handler('permissionErrorHandler');
            mkdir($path, 0755, true);
            if (!is_dir($path) && !file_exists($path)) {
                throw new PermissionsException($message . $path);
            }
            restore_error_handler();
        }
    }

    /**
     * @param $path
     * @param $content
     */
    public static function writeFile($path, $content, $flags = 0)
    {
        $message = 'Unable to create or write to the file: ';
        set_error_handler('permissionErrorHandler');
        if (file_exists($path))
            chmod($path, 0755);
        $return = file_put_contents($path, $content, $flags);
        if (false === $return) {
            throw new PermissionsException($message . $path);
        }
        restore_error_handler();
        chmod($path, 0755);
    }

    /**
     * @param $path
     */
    public static function readFile($path)
    {
        if (!file_exists($path))
            return;
        $message = 'Unable to read the file: ';
        set_error_handler('permissionErrorHandler');
        chmod($path, 0755);
        $content = file_get_contents($path);
        if (false === $content) {
            throw new PermissionsException($message . $path);
        }
        restore_error_handler();
        return $content;
    }

    /**
     * @param $oldname
     * @param $newname
     */
    public static function renameFile($path, $newname)
    {
        if (!file_exists($path))
            return;
        $message = 'Unable to rename the file: ';
        set_error_handler('permissionErrorHandler');
        chmod($path, 0755);
        if (!rename($path, $newname)) {
            throw new PermissionsException($message . $path);
        }
        restore_error_handler();
    }

    /**
     * @param $source
     * @param $dest
     */
    public static function copyFile($source, $path)
    {
        $message = 'Unable to copy the file: ';
        set_error_handler('permissionErrorHandler');
        if (file_exists($source))
            chmod($source, 0755);
        if (!copy($source, $path)) {
            throw new PermissionsException($message . $path);
        }
        restore_error_handler();
    }

    /**
     * @param $path
     */
    public static function deleteFolder($path)
    {
        if (!file_exists($path))
            return;
        $message = 'Unable to remove the folder: ';
        set_error_handler('permissionErrorHandler');
        chmod($path, 0755);
        @rmdir($path);
        if (is_dir($path) && file_exists($path)) {
            throw new PermissionsException($message . $path);
        }
        restore_error_handler();
    }

    /**
     * @param $file
     */
    public static function deleteFile($path)
    {
        if (!file_exists($path))
            return;
        $message = 'Unable to remove the file: ';
        set_error_handler('permissionErrorHandler');
        chmod($path, 0755);
        @unlink($path);
        if (file_exists($path)) {
            throw new PermissionsException($message . $path);
        }
        restore_error_handler();
    }

    /**
     * @param $source
     * @param $destination
     */
    public static function moveDir($source, $destination)
    {
        $dir = opendir($source);
        Helper::createDir($destination);
        while(false !== ( $file = readdir($dir)) ) {
            if (($file != '.') && ($file != '..')) {
                if ( is_dir($source . '/' . $file) ) {
                    self::moveDir($source . '/' . $file, $destination . '/' . $file);
                }
                else {
                    Helper::renameFile($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param $source
     * @param $destination
     */
    public static function copyDir($source, $destination) {
        $dir = opendir($source);
        Helper::createDir($destination);
        while(false !== ($file = readdir($dir)) ) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    self::copyDir($source . '/' . $file, $destination . '/' . $file);
                }
                else {
                    Helper::copyFile($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param $dir
     * @return bool|null
     */
    public static function isEmptyDir($dir)
    {
        if (!is_readable($dir)) return NULL;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * @param $value
     * @return int
     */
    public static function toBytes($value)
    {
        $str = strtolower(trim($value));
        if ($str) {
            switch ($str[strlen($str) - 1]) {
                case 'g':
                    $str *= 1024;
                case 'm':
                    $str *= 1024;
                case 'k':
                    $str *= 1024;
            }
        }
        return intval($str);
    }

    /**
     * @return int
     */
    public static function getMemoryLimit()
    {
        if (!function_exists('ini_get'))
            return -1;
        return Helper::toBytes(ini_get('memory_limit'));
    }

    /**
     * @return mixed
     */
    public static function tryAllocateMemory() {

        $tasks = array('run' => 1, 'error' => 0);

        $memory = Helper::getMemoryLimit();

        // can't retrieve memory limit option
        if (-1 == $memory)
            return $tasks['run'];

        // themler requires 64Mb of php memory: 64M - 67108864 bytes
        if ($memory >= 67108864)
            return $tasks['run'];

        if(!function_exists('ini_set'))
            return $tasks['error'];

        $ret = ini_set('memory_limit', '64M');
        if (!$ret)
            return $tasks['error'];

        return $tasks['run'];
    }

    /**
     * @param $files
     * @return array
     */
    public static function loadFragments($files)
    {
        $pieces = array();
        foreach($files as $path => $value) {
            $pathParts = explode('/', $path);
            $name  = array_pop($pathParts);
            $nameParts  = explode('.', $name);
            $key   = array_shift($nameParts);
            preg_match_all('/([\w\W]*)<!--\s*CONTAINER\s*-->([\w\W]*)<!--\s*\/CONTAINER\s*-->([\w\W]*)/', $value, $primary);
            if(empty($primary[1])) {
                $pieces[$key] = $value;
            } else {
                $pieces[$key . '_before'] = $primary[1][0];
                $pieces[$key . '_after'] = $primary[3][0];
                preg_match_all('/^([\w\W]*)<!--\s*CONTENT\s*-->([\w\W]*)$/', $primary[2][0], $secondary);
                if (empty($secondary[1])) {
                    $pieces[$key . '_prepend'] = $primary[2][0];
                    $pieces[$key . '_append'] = '';
                } else {
                    $pieces[$key . '_prepend'] = $secondary[1][0];
                    $pieces[$key . '_append'] = $secondary[2][0];
                }
            }
        }
        return $pieces;
    }
}

class UnregisterableCallback{

    // Store the Callback for Later
    private $callback;

    // Check if the argument is callable, if so store it
    public function __construct($callback)
    {
        if(is_callable($callback))
        {
            $this->callback = $callback;
            $GLOBALS['shutdown_func_is_defined'] = 1;
        }
        else
        {
            throw new InvalidArgumentException("Not a Callback");
        }
    }

    // Check if the argument has been unregistered, if not call it
    public function call()
    {
        if($this->callback == false)
            return false;

        $callback = $this->callback;
        $callback(); // weird PHP bug
    }

    // Unregister the callback
    public function unregister()
    {
        $this->callback = false;
        $GLOBALS['shutdown_func_is_defined'] = 0;
    }
}