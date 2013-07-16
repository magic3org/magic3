<?php
/**
 * patError error object used by the patFormsError manager as error messages 
 * container for precise error management.
 *
 *	$Id: patError.php 4142 2011-05-16 14:22:07Z fishbone $
 *
 * @access        public
 * @package       patError
 */

/**
 * patError error object used by the patFormsError manager as error messages 
 * container for precise error management.
 *
 * $Id: patError.php 4142 2011-05-16 14:22:07Z fishbone $
 *
 * @access        public
 * @package       patError
 * @version       0.3
 * @author        gERD Schaufelberger <gerd@php-tools.net>
 * @author        Sebastian Mordziol <argh@php-tools.net>
 * @author        Stephan Schmidt <schst@php-tools.net>
 * @license       LGPL
 * @link          http://www.php-tools.net
 */
class patError {
   /**
    * stores the error level for this error
    *
    * @access    private
    * @var        string
    */
    protected $level  =   null;
    
   /**
    * stores the code of the error
    *
    * @access    private
    * @var        string
    */
    protected $code  =   null;

   /**
    * stores the error message - this is the message that can also be shown the
    * user if need be.
    *
    * @access    private
    * @protected        string
    */
    protected $message  =   null;

   /**
    * additional info that is relevant for the developer of the script (e.g. if
    * a database connect fails, the dsn used) and that the end-user should not
    * see.
    *
    * @access    private
    * @protected        string
    */
    protected $info  =   '';
    
   /**
    * stores the filename of the file the error occurred in.
    *
    * @access    private
    * @protected        string
    */
    protected $file  =   '';
    
   /**
    * stores the line number the error occurred in.
    *
    * @access    private
    * @protected        integer
    */
    protected $line  =   0;

   /**
    * stores the name of the method the error occurred in
    *
    * @access    private
    * @protected        string
    */
    protected $function  =   '';

   /**
    * stores the name of the class (if any) the error occurred in.
    *
    * @access    private
    * @protected        string
    */
    protected $class  =   '';
    
   /**
    * stores the type of error, as it is listed in the error backtrace
    *
    * @access    private
    * @protected        string
    */
    protected $type  =   '';

   /**
    * stores the arguments the method that the error occurred in had received.
    *
    * @access    private
    * @protected        array
    */
    protected $args  =   array();
    
   /**
    * stores the complete debug backtrace (if your PHP version has the 
    * debug_backtrace function)
    *
    * @access    private
    * @protected        mixed
    */
    protected $backtrace  =   false;
    
   /**
    * constructor - used to set up the error with all needed error details.
    *
    * @access   public
    * @param    int       $level    The error level (use the PHP constants E_ALL, E_NOTICE etc.).
    * @param    string    $code    The error code from the application
    * @param    string    $msg    The error message
    * @param    string    $info    Optional: The additional error information.
    */
    public function __construct($level, $code, $msg, $info = null) {
        static $raise = array('raise', 
                              'raiseerror', 
                              'raisewarning', 
                              'raisenotice' 
                             );
                                        
        $this->level   = $level;
        $this->code    = $code;
        $this->message =$msg;
        
        if ($info != null) {
            $this->info = $info;
        }
    
        $this->backtrace = debug_backtrace();
        
        for ($i = count( $this->backtrace ) - 1; $i >= 0; --$i) {
            if (in_array($this->backtrace[$i]['function'], $raise)) {
                ++$i;
                if (isset($this->backtrace[$i]['file'])) {
                    $this->file = $this->backtrace[$i]['file'];
                }
                if (isset( $this->backtrace[$i]['line'])) {
                    $this->line = $this->backtrace[$i]['line'];
                }
                if (isset($this->backtrace[$i]['class'])) {
                    $this->class    =    $this->backtrace[$i]['class'];
                }
                if (isset($this->backtrace[$i]['function'])) {
					$this->function = $this->backtrace[$i]['function'];
                }
                if (isset($this->backtrace[$i]['type'])) {
                	$this->type = $this->backtrace[$i]['type'];
                }
                $this->args = false;
                if (isset($this->backtrace[$i]['args'])) {
					$this->args =$this->backtrace[$i]['args'];
                }
                break;
            }
        }
    }
    
   /**
    * returns the error level of the error - corresponds to the PHP 
    * error levels (E_ALL, E_NOTICE...)
    *
    * @access    public
    * @return    int $level    The error level
    * @see        $level
    */
    public function getLevel() {
        return $this->level;
    }


   /**
    * retrieves the error message
    *
    * @access    public
    * @return    string     $msg    The stored error message
    * @see        $message
    */
    public function getMessage() {
        return $this->message;
    }

   /**
    * retrieves the additional error information (information usually
    * only relevant for developers)
    *
    * @access    public
    * @return    mixed $info    The additional information
    * @see        $info
    */
    public function getInfo() {
        return $this->info;
    }
    
   /**
    * recieve error code
    *
    * @access    public
    * @return    string|integer        error code (may be a string or an integer)
    * @see        $code
    */
    public function getCode() {
        return $this->code;
    }

   /**
    * get the backtrace
    *
    * This is only possible, if debug_backtrace() is available.
    *
    * @access    public
    * @return    array backtrace
    * @see       $backtrace
    */
    public function getBacktrace() {
        return $this->backtrace;
    }

   /**
    * get the filename in which the error occured
    *
    * This is only possible, if debug_backtrace() is available.
    *
    * @access    public
    * @return    string filename
    * @see       $file
    */
    public function getFile() {
        return $this->file;
    }

   /**
    * get the line number in which the error occured
    *
    * This is only possible, if debug_backtrace() is available.
    *
    * @access    public
    * @return    integer line number
    * @see        $line
    */
    public function getLine() {
        return $this->line;
    }
}
?>