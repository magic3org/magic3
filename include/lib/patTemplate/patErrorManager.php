<?php
/**
 * patErrorManager main error management class used by pat tools for the
 * application-internal error management. Creates patError objects for
 * any errors for precise error management.
 *
 * $Id: patErrorManager.php 4142 2011-05-16 14:22:07Z fishbone $
 *
 * @package	patError
 */

/**
 * error definition: illegal options.
 */
define('PATERRORMANAGER_ERROR_ILLEGAL_OPTIONS', 1);

/**
 * error definition: callback function does not exist.
 */
define('PATERRORMANAGER_ERROR_CALLBACK_NOT_CALLABLE', 2);

/**
 * error definition: illegal error handling mode.
 */
define('PATERRORMANAGER_ERROR_ILLEGAL_MODE', 3);

/**
 * patErrorManager main error management class used by pat tools for the
 * application-internal error management. Creates patError objects for
 * any errors for precise error management.
 *
 * @static
 * @package    patError
 * @version    0.3
 * @author    gERD Schaufelberger <gerd@php-tools.net>
 * @author    Stephan Schmidt <schst@php-tools.net>
 * @license    LGPL
 * @link    http://www.php-tools.net
 * @todo    implement ignoreError() to ignore errrors with a certain code
 * @todo    implement expectError() to ignore an error with a certain code only once.
 */
class patErrorManager
{
    /**
     * global definitions needed to keep track of things when calling the patErrorManager
     * static methods.
     */
	protected static $errorHandling = array(
                                            E_NOTICE    => array( 'mode' => 'echo' ),
                                            E_WARNING    => array( 'mode' => 'echo' ),
                                            E_ERROR        => array( 'mode' => 'die' )
                                        );

	/**
 	 * available error levels
 	 * Stored in a variable to keep them flexible
 	 */
	protected static $errorLevels = array(
                                            E_NOTICE    => 'Notice',
                                            E_WARNING   => 'Warning',
                                            E_ERROR     => 'Error'
                                        );
	/**
 	 * error class names
	 * Stored in a variable allows to change during runtime
	 */
	protected static $errorClass = 'patError';

	/**
	 * ignore errors
	 * Store error-codes that will be ignored forever
	 */
	protected static $errorIgnores = array();
	
	/**
	 * expects errors
	 * Store error-codes that will be ignored once
	 */
	protected static $errorExpects = array();
	
   /**
    * method for checking whether the return value of a pat application method is a pat
    * error object.
    *
    * @static
    * @access    public
    * @param    mixed    &$object
    * @return    boolean $result    True if argument is a patError-object, false otherwise.
    */
    public static function isError($object) {
        if (!is_object($object)) {
            return false;
        }

        if (strtolower(get_class($object)) != strtolower(self::$errorClass) && !is_subclass_of($object, self::$errorClass)) {
            return false;
        }
        return  true;
    }

   /**
    * wrapper for the {@link raise()} method where you do not have to specify the
    * error level - a {@link patError} object with error level E_ERROR will be returned.
    *
    * @static
    * @access    public
    * @param    string    $code    The application-internal error code for this error
    * @param    string    $msg    The error message, which may also be shown the user if need be.
    * @param    mixed    $info    Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
    * @return    object    $error    The configured patError object
    * @see        raise()
    * @see        patError
    */
    public static function raiseError($code, $msg, $info = null) {
        return patErrorManager::raise(E_ERROR, $code, $msg, $info);
    }

   /**
    * wrapper for the {@link raise()} method where you do not have to specify the
    * error level - a {@link patError} object with error level E_WARNING will be returned.
    *
    * @static
    * @access    public
    * @param    string    $code    The application-internal error code for this error
    * @param    string    $msg    The error message, which may also be shown the user if need be.
    * @param    mixed    $info    Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
    * @return    object    $error    The configured patError object
    * @see        raise()
    * @see        patError
    */
    public static function raiseWarning($code, $msg, $info = null) {
        return patErrorManager::raise(E_WARNING, $code, $msg, $info);
    }

   /**
    * wrapper for the {@link raise()} method where you do not have to specify the
    * error level - a {@link patError} object with error level E_NOTICE will be returned.
    *
    * @static
    * @access    public
    * @param    string    $code    The application-internal error code for this error
    * @param    string    $msg    The error message, which may also be shown the user if need be.
    * @param    mixed    $info    Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
    * @return    object    $error    The configured patError object
    * @see        raise()
    * @see        patError
    */
    public static function raiseNotice($code, $msg, $info = null) {
        return patErrorManager::raise(E_NOTICE, $code, $msg, $info);
    }

   /**
    * creates a new patError object given the specified information.
    *
    * @access    public
    * @param    int        $level    The error level - use any of PHP's own error levels for this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
    * @param    string    $code    The application-internal error code for this error
    * @param    string    $msg    The error message, which may also be shown the user if need be.
    * @param    mixed    $info    Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
    * @return    mixed    $error    The configured patError object or false if this error should be ignored
    * @see        patError
    * @todo        implement 'simple' mode that returns just false (BC for patConfiguration)
    * @todo        either remove HTML tags and entities from output or test for enviroment!!! <b></b> in shell is ugly!
    */
    public static function raise($level, $code, $msg, $info = null) {
        // ignore this error?
        if (in_array( $code, self::$errorIgnores)) {
            return false;
        }

        // need patError
        $class    =    self::$errorClass;
        if (!class_exists($class, false)) {
            include_once dirname(__FILE__) . '/'. $class .'.php';
        }

        // build error object
        $error = new $class($level, $code, $msg, $info);

        // this error was expected
        if (!empty( self::$errorExpects)) {
            $expected = array_pop(self::$errorExpects);
            array_push(self::$errorExpects, $expected);
            if (in_array( $code, $expected)) {
                return $error;
            }
        }

        // see what to do with this kind of error
        $handling = patErrorManager::getErrorHandling($level);

        $function = 'handleError' . ucfirst($handling['mode']);
        return patErrorManager::$function($error, $handling);
    }

   /**
    * register a new error level
    *
    * This allows you to add custom error levels to the built-in
    * - E_NOTICE
    * - E_WARNING
    * - E_NOTICE
    *
    * You may use this level in subsequent calls to raise().
    * Error handling will be set to 'ignore' for the new level, you
    * may change it by using setErrorHandling().
    *
    * You could be using PHP's predefined constants for error levels
    * or any other integer value.
    *
    * @access    public
    * @param    integer    error level
    * @param    string    human-readable name
    * @return    boolean    true on success; false if the level already has been registered
    * @see        raise(), setErrorHandling()
    * @link        http://www.php.net/manual/en/function.error-reporting.php
    */
    public static function registerErrorLevel($level, $name) {
        if (isset(self::$errorLevels[$level])) {
            return false;
        }
        self::$errorLevels[$level] = $name;
        patErrorManager::setErrorHandling($level, 'ignore');
        return true;
    }

   /**
    * sets the way the patErrorManager will handle teh different error levels. Use this
    * if you want to override the default settings.
    *
    * Error handling modes:
    * - ignore
    * - trigger
    * - verbose
    * - echo
    * - callback
    * - die
    *
    * You may also set the error handling for several modes at once using PHP's bit operations.
    * Examples:
    * - E_ALL = Set the handling for all levels
    * - E_ERROR | E_WARNING = Set the handling for errors and warnings
    * - E_ALL ^ E_ERROR = Set the handling for all levels except errors
    *
    * @static
    * @access    public
    * @param    int        $level        The error level for which to set the error handling
    * @param    string    $mode        The mode to use for the error handling.
    * @param    mixed    $options    Optional: Any options needed for the given mode.
    * @return    mixed    $result        True on success, or a patError object if failed.
    * @see        getErrorHandling()
    */
    public static function setErrorHandling($level, $mode, $options = null) {
        $levels = self::$errorLevels;

        $function = 'handleError' . ucfirst($mode);
        if (!is_callable( array('patErrorManager', $function))) {
            return patErrorManager::raiseError(E_ERROR,
                                                'patErrorManager:' . PATERRORMANAGER_ERROR_ILLEGAL_MODE,
                                                'Error Handling mode is not knwon',
                                                'Mode: ' .  $mode . ' is not implemented.'
                                                );
        }

        foreach ($levels as $eLevel => $eTitle) {
            if (($level & $eLevel ) != $eLevel) {
                continue;
            }

            // set callback options
            if ($mode == 'callback') {
                if (!is_array($options)) {
                    return patErrorManager::raiseError(E_ERROR,
                                                        'patErrorManager:' . PATERRORMANAGER_ERROR_ILLEGAL_OPTIONS,
                                                        'Options for callback not valid'
                                                        );
                }

                if (!is_callable($options)) {
                    $tmp = array('GLOBAL');
                    if (is_array($options)) {
                        $tmp[0] = $options[0];
                        $tmp[1] = $options[1];
                    } else {
                        $tmp[1] = $options;
                    }

                    return patErrorManager::raiseError(E_ERROR,
                                                        'patErrorManager:' . PATERRORMANAGER_ERROR_CALLBACK_NOT_CALLABLE,
                                                        'Function is not callable',
                                                        'Function:' . $tmp[1]  . ' scope ' . $tmp[0] . '.'
                                                        );
                }
            }


            // save settings
            self::$errorHandling[$eLevel] = array('mode' => $mode);
            if ($options != null) {
                self::$errorHandling[$eLevel]['options']= $options;
            }
        }
        return  true;
    }

   /**
    * retrieves the current error handling settings for the specified error level.
    *
    * @access    public
    * @param    int        $level        The error level to retrieve. This can be any of PHP's own error levels, e.g. E_ALL, E_NOTICE...
    * @return    array    $handling    All error handling details
    */
    public static function getErrorHandling($level) {
        return self::$errorHandling[$level];
    }

   /**
    * translate an error level
    *
    * returns the human-readable name for an error level,
    * e.g. E_ERROR will be translated to 'Error'.
    *
    * @access    public
    * @param    integer        error level
    * @return    string        human-readable representation
    */
    public static function translateErrorLevel($level) {
        if (isset(self::$errorLevels[$level])) {
            return self::$errorLevels[$level];
        }
        return 'Unknown error level';
    }

   /**
    * setErrorClass
    *
    * In order to autoload this class, the filename containing that class must be
    * named like the class itself; with an appending ".php". Although the file must be stored
    * in the same directory as patErrorManager.php (this file)
    *
    * @access public
    * @param string $name classname
    * @return boolean $result true on success
    */
    public static function setErrorClass($name) {
        // include old error-class
        if ($name !== self::$errorClass && !class_exists(self::$errorClass, false)) {
            include_once dirname( __FILE__ ) . '/' . self::$errorClass . '.php';
        }

        self::$errorClass = $name;
        return true;
    }

   /**
    * add error codes to be ingored
    *
    * @static
    * @access public
    * @param mixed $codes either an array of error code or a single code that will be ignored in future
    * @return boolean $result true on success
    */
    public static function addIgnore($codes) {
        if (!is_array($codes)) {
            $codes = array( $codes );
        }

        $codes = array_merge(self::$errorIgnores, $codes);
        self::$errorIgnores = array_unique($codes);

        return true;
    }

   /**
    * removeIgnore
    *
    *
    * @static
    * @access public
    * @return boolean $result true on success
    */
    public static function removeIgnore($codes) {
        if (!is_array($codes)) {
            $codes = array($codes);
        }

        foreach ($codes as $code) {
            $index = array_search($code, self::$errorIgnores);
            if ($index === false) {
                continue;
            }

            unset(self::$errorIgnores[$index]);
        }

        // reorder the codes
        self::$errorIgnores = array_values(self::$errorIgnores);

        return true;
    }

   /**
    * recieve all registerd error codes that will be ignored
    *
    * @static
    * @access public
    * @return array $codes list of error codes
    */
    public static function getIgnore() {
        return self::$errorIgnores;
    }

   /**
    * empty list of errors to be ignored
    *
    * @static
    * @access public
    * @return boolean $result true on success
    */
    public static function clearIgnore() {
        self::$errorIgnores = array();
        return true;
    }

   /**
    * add expected errors to stack
    *
    * @static
    * @access public
    * @param mixed $codes either an array of error code or a single code that will be ignored in future
    * @return boolean $result true on success
    */
    public static function pushExpect($codes) {
        if (!is_array($codes)) {
            $codes = array( $codes );
        }
        array_push(self::$errorExpects, $codes);
        return true;
    }

   /**
    * remove top of error-codes from stack
    *
    * @static
    * @access public
    * @return boolean $result true on success
    */
    public static function popExpect() {
        if (empty(self::$errorExpects)) {
            return false;
        }

        array_pop(self::$errorExpects);
        return true;
    }

   /**
    * recieve all registerd error codes that will be ignored
    *
    * @static
    * @access public
    * @return array $codes list of error codes
    */
    public static function getExpect() {
        return self::$errorExpects;
    }

   /**
    * empty list of errors to be ignored
    *
    * @static
    * @access public
    * @return boolean $result true on success
    */
    public static function clearExpect() {
        self::$errorExpects = array();
        return true;
    }

   /**
    * handleError: Ignore
    * Does nothing
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return object $error error-object
    * @see raise()
    */
    public static function handleErrorIgnore($error, $options) {
        return $error;
    }

   /**
    * handleError: Echo
    * display error message
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return object $error error-object
    * @see raise()
    */
    public static function handleErrorEcho($error, $options) {
        $level_human = patErrorManager::translateErrorLevel( $error->getLevel() );

        if (isset($_SERVER['HTTP_HOST'])) {
            // output as html
            echo "<br /><b>pat-$level_human</b>: " . $error->getMessage() . "<br />\n";
        } else {
            // output as simple text
            if (defined( 'STDERR')) {
                fwrite(STDERR, "pat-$level_human: " . $error->getMessage() . "\n");
            } else {
                echo "pat-$level_human: " . $error->getMessage() . "\n";
            }
        }
        return $error;
    }

   /**
    * handleError: Verbose
    * display verbose output for developing purpose
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return object $error error-object
    * @see raise()
    */
    public static function handleErrorVerbose($error, $options) {
        $level_human = patErrorManager::translateErrorLevel($error->getLevel());
        $info = $error->getInfo();

        if (isset($_SERVER['HTTP_HOST'])) {
            // output as html
            echo "<br /><b>pat-$level_human</b>: " . $error->getMessage() . "<br />\n";
            if ($info != null) {
                echo "&nbsp;&nbsp;&nbsp;" . $error->getInfo() . "<br />\n";
            }
        } else {
            // output as simple text
            echo "pat-$level_human: " . $error->getMessage() . "\n";
            if ($info != null) {
                echo "    " . $error->getInfo() . "\n";
            }
        }
        return $error;
    }

   /**
    * handleError: die
    * display error-message and die
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return object $error error-object
    * @see raise()
    */
    public static function handleErrorDie( $error, $options )
    {
        $level_human = patErrorManager::translateErrorLevel($error->getLevel());

        if (isset($_SERVER['HTTP_HOST'])) {
            // output as html
            die("<br /><b>pat-$level_human</b> " . $error->getMessage() . "<br />\n");
        } else {
            // output as simple text
            if (defined('STDERR')) {
                fwrite(STDERR, "pat-$level_human " . $error->getMessage() . "\n");
                die();
            } else {
                die("pat-$level_human " . $error->getMessage() . "\n");
            }
        }
        return $error;
    }

   /**
    * handleError: trigger
    * trigger php-error
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return object $error error-object
    * @see raise()
    */
    public static function handleErrorTrigger($error, $options) {
        switch ($error->getLevel()) {
            case E_NOTICE:
                $level = E_USER_NOTICE;
                break;
            case E_WARNING:
                $level = E_USER_WARNING;
                break;
            case E_NOTICE:
                $level = E_NOTICE;
                break;
            default:
                $level = E_USER_ERROR;
                break;
        }

        trigger_error($error->getMessage(), $level);
        return $error;
    }

   /**
    * handleError: callback
    * forward error to custom handler
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return object $error error-object
    * @see raise()
    */
    public static function handleErrorCallback($error, $options) {
        $opt = $options['options'];
        $result = call_user_func($opt, $error);
        return $result;
    }

   /**
    * handleError: throw an exception
    *
    * @access private
    * @param object $error patError-Object
    * @param array $options options for handler
    * @return null
    * @throws Exception
    * @see raise()
    */
    public static function handleErrorException($error, $options) {
        if (isset($options['options'])) {
            $exception = $options['options'];
        } else {
            $exception = 'Exception';
        }
        throw new $exception($error->getMessage());
    }
}
?>