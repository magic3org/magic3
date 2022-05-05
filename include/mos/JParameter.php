<?php
/**
 * Joomla!パラメータクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2022 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($this->gEnv->getJoomlaRootPath() . '/class/error.php');
//require_once($this->gEnv->getJoomlaRootPath() . '/class/exception.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/object.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/registry.php');

function jimport($path)
{
	//return JLoader::import($path);
	return true;
}
class JParameter
{
	private $params = array();

	/**
	 * コンストラクタ
	 *
	 * @param string $data		初期化データ
	 * @param string $path		初期設定ファイル
	 */
	function __construct($data = '', $path = '')
	{
		$data = trim($data);
		if (!empty($data)){
			$lines = explode("\n", $data);
			$count = count($lines);
			for ($i = 0; $i < $count; $i++){
				list($key, $value) = explode("=", $lines[$i]);
				$this->params[$key] = $value;
			}
		}
	}
	/**
	 * キーを指定して値を設定
	 *
	 * @param string $key		取得キー
	 * @param string			設定値
	 * @return 					設定値
	 */
	public function set($key, $value)
	{
		$this->params[$key] = $value;
		return $value;
	}
	/**
	 * キーを指定して値を取得
	 *
	 * @param string $key			取得キー
	 * @param int,string $default	デフォルト値
	 * @return string				取得値
	 */
	public function get($key, $default = null)
	{
		if (isset($this->params[$key])){
			return $this->params[$key];
		} else {
			return isset($default) ? $default : '';
		}
	}
	/**
	 * デフォルト値を設定して値を取得
	 *
	 * @param string $key		取得キー
	 * @param string $default	デフォルト値
	 * @return string			取得値
	 */
	public function def($key, $default = '')
	{
		// 値が設定されていなければデフォルト値を設定
		$this->params[$key] = $default;
		
		$value = $this->get($key);
		return $value;
	}
}
abstract class JLoader
{
	public static function import($key, $base = null)
	{
		return true;
	}
}
class JUser
{
	/**
	 * 編集権限を返す
	 *
	 * @return bool				編集権限
	 */
	public function authorize()
	{
		return false;
	}
}
class JRoute
{
	public static function _($url, $xhtml = true, $ssl = null)
	{
		return $url;
	}
}
abstract class ContentHelperRoute
{
	public static function getArticleRoute($id, $catid = 0, $language = 0)
	{
		return $id;
	}
	public static function getCategoryRoute($catid, $language = 0)
	{
		return '';
	}
}
class JText
{
/*	private static $_strings = array(	'DATE_FORMAT_LC'	=> '%A, %d %B %Y',
										'DATE_FORMAT_LC1'	=> '%A, %d %B %Y',
										'DATE_FORMAT_LC2'	=> '%A, %d %B %Y %H:%M',
										'DATE_FORMAT_LC3'	=> '%d %B %Y',
										'DATE_FORMAT_LC4'	=> '%d.%m.%y',
										'DATE_FORMAT_JS1'	=> 'y-m-d');*/
	
	public static function _($string, $jsSafe = false)
	{
		global $gInstanceManager;
		
/*		$key = strtoupper($string);
		$key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;

		if (isset(self::$_strings[$key])) $string = self::$_strings[$key];*/

		$value = $gInstanceManager->getMessageManager()->getJoomlaText($string);
		if (!empty($value)) $string = $value;

		if ($jsSafe) $string = addslashes($string);
		return $string;
	}
	public static function sprintf($string)
	{
		global $gInstanceManager;
		
//		$lang =& JFactory::getLanguage();
		$args = func_get_args();
		if (count($args) > 0) {
//			$args[0] = $lang->_($args[0]);
			$value = $gInstanceManager->getMessageManager()->getJoomlaText($args[0]);
			if (!empty($value)) $args[0] = $value;
			return call_user_func_array('sprintf', $args);
		}
		return '';
	}
	public static function printf($string)
	{
		$lang =& JFactory::getLanguage();
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			return call_user_func_array('printf', $args);
		}
		return '';
	}
}
class JRequest
{
	private static $injectParams = array();		// Magic3からの設定用
	
	public static function getURI()
	{
		global $gEnvManager;
		
		return $gEnvManager->getCurrentRequestUri();
	}
	public static function getMethod()
	{
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		return $method;
	}
	public static function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
	{
		global $gRequestManager;
		
		$value = self::$injectParams[$name];
		if (isset($value)){
			return $value;
		} else {
			return $gRequestManager->trimValueOf($name);
		}
	}
	public static function injectSetVar($name, $value)
	{
		self::$injectParams[$name] = $value;
	}
	public static function getInt($name, $default = 0, $hash = 'default')
	{
		return intval(self::getVar($name, $default, $hash, 'int'));
	}
	public static function getFloat($name, $default = 0.0, $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'float');
	}
	public static function getBool($name, $default = false, $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'bool');
	}
	public static function getWord($name, $default = '', $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'word');
	}
	public static function getCmd($name, $default = '', $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'cmd');
	}
	public static function getString($name, $default = '', $hash = 'default', $mask = 0)
	{
		return self::getVar($name, $default, $hash, 'string', $mask);
	}
}
class JConfig {
	/**
	* -------------------------------------------------------------------------
	* Site configuration section
	* -------------------------------------------------------------------------
	*/
	/* Site Settings */
	var $offline = '0';
	var $offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';
	var $sitename = 'Joomla!';			// Name of Joomla site
	var $editor = 'tinymce';
	var $list_limit = '20';
	var $legacy = '0';

	/**
	* -------------------------------------------------------------------------
	* Database configuration section
	* -------------------------------------------------------------------------
	*/
	/* Database Settings */
	var $dbtype = 'mysql';					// Normally mysql
	var $host = 'localhost';				// This is normally set to localhost
	var $user = '';							// MySQL username
	var $password = '';						// MySQL password
	var $db = '';							// MySQL database name
	var $dbprefix = 'jos_';					// Do not change unless you need to!

	/* Server Settings */
	var $secret = 'FBVtggIk5lAzEU9H'; 		//Change this to something more secure
	var $gzip = '0';
	var $error_reporting = '-1';
	var $helpurl = 'http://help.joomla.org';
	var $xmlrpc_server = '1';
	var $ftp_host = '';
	var $ftp_port = '';
	var $ftp_user = '';
	var $ftp_pass = '';
	var $ftp_root = '';
	var $ftp_enable = '';
	var $tmp_path	= '/tmp';
	var $log_path	= '/var/logs';
	var $offset = '0';
	var $live_site = ''; 					// Optional, Full url to Joomla install.
	var $force_ssl = 0;		//Force areas of the site to be SSL ONLY.  0 = None, 1 = Administrator, 2 = Both Site and Administrator

	/* Session settings */
	var $lifetime = '15';					// Session time
	var $session_handler = 'database';

	/* Mail Settings */
	var $mailer = 'mail';
	var $mailfrom = '';
	var $fromname = '';
	var $sendmail = '/usr/sbin/sendmail';
	var $smtpauth = '0';
	var $smtpuser = '';
	var $smtppass = '';
	var $smtphost = 'localhost';

	/* Cache Settings */
	var $caching = '0';
	var $cachetime = '15';
	var $cache_handler = 'file';

	/* Debug Settings */
	var $debug      = '0';
	var $debug_db 	= '0';
	var $debug_lang = '0';

	/* Meta Settings */
	var $MetaDesc = 'Joomla! - the dynamic portal engine and content management system';
	var $MetaKeys = 'joomla, Joomla';
	var $MetaTitle = '1';
	var $MetaAuthor = '1';

	/* SEO Settings */
	var $sef = '0';
	var $sef_rewrite = '0';
	var $sef_suffix = '';

	/* Feed Settings */
	var $feed_limit   = 10;
	var $feed_email   = 'author';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gEnvManager;
		
		$this->sitename = $gEnvManager->getSiteName();// サイト名称
		$this->tmp_path = $gEnvManager->getWorkDirPath();		// 一時ディレクトリ
	}
}
class JString
{
	/**
	 * UTF-8 aware replacement for trim()
	 * Strip whitespace (or other characters) from the beginning and end of a string
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise trim will
	 * work normally on a UTF-8 string
	 *
	 * @param   string  $str       The string to be trimmed
	 * @param   string  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 *
	 * @see     http://www.php.net/trim
	 * @since   1.0
	 */
	public static function trim($str, $charlist = false)
	{
/*		if (empty($charlist) && $charlist !== false)
		{
			return $str;
		}

		require_once __DIR__ . '/phputf8/trim.php';

		if ($charlist === false)
		{
			return utf8_trim($str);
		}

		return utf8_trim($str, $charlist);*/
		return trim($str, $charlist);
	}
	/**
	 * UTF-8 aware alternative to substr
	 * Return part of a string given character offset (and optionally length)
	 *
	 * @param   string   $str     String being processed
	 * @param   integer  $offset  Number of UTF-8 characters offset (from left)
	 * @param   integer  $length  Optional length in UTF-8 characters from offset
	 *
	 * @return  mixed string or FALSE if failure
	 *
	 * @see     http://www.php.net/substr
	 * @since   1.0
	 */
	public static function substr($str, $offset, $length = false)
	{
/*		if ($length === false)
		{
			return utf8_substr($str, $offset);
		}

		return utf8_substr($str, $offset, $length);*/
		return substr($str, $offset, $length);
	}
	/**
	 * UTF-8 aware alternative to strlen.
	 *
	 * Returns the number of characters in the string (NOT THE NUMBER OF BYTES),
	 *
	 * @param   string  $str  UTF-8 string.
	 *
	 * @return  integer  Number of UTF-8 characters in string.
	 *
	 * @see http://www.php.net/strlen
	 * @since   1.0
	 */
	public static function strlen($str)
	{
		//return utf8_strlen($str);
		return strlen($str);
	}
	/**
	 * UTF-8 aware alternative to strrpos
	 * Finds position of last occurrence of a string
	 *
	 * @param   string   $str     String being examined.
	 * @param   string   $search  String being searched for.
	 * @param   integer  $offset  Offset from the left of the string.
	 *
	 * @return  mixed  Number of characters before the last match or false on failure
	 *
	 * @see     http://www.php.net/strrpos
	 * @since   1.0
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		//return utf8_strrpos($str, $search, $offset);
		return strrpos($str, $search, $offset);
	}
}
/**
 * Version information class for the Joomla CMS.
 * テンプレートからは、RELEASE値以外はまず使用されない
 *
 * @package  Joomla.Site
 * @since    1.0
 */
final class JVersion
{
	/** @var  string  Product name. */
	public $PRODUCT = 'Joomla!';

	/** @var  string  Release version. */
//	public $RELEASE = '1.7';
//	public $RELEASE = '1.5';
	public $RELEASE = '3.0';
	
	/** @var  string  Maintenance version. */
	public $DEV_LEVEL = '1';

	/** @var  string  Development STATUS. */
	public $DEV_STATUS = 'Stable';

	/** @var  string  Build number. */
	public $BUILD = '';

	/** @var  string  Code name. */
	public $CODENAME = 'Ember';

	/** @var  string  Release date. */
	public $RELDATE = '26-Sep-2011';

	/** @var  string  Release time. */
	public $RELTIME = '14:00';

	/** @var  string  Release timezone. */
	public $RELTZ = 'GMT';

	/** @var  string  Copyright Notice. */
	public $COPYRIGHT = 'Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.';

	/** @var  string  Link text. */
	public $URL = '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 *
	 * @return  bool    True if the version is compatible.
	 *
	 * @see     http://www.php.net/version_compare
	 * @since   1.0
	 */
	public function isCompatible($minimum)
	{
		return version_compare(JVERSION, $minimum, 'ge');
	}

	/**
	 * Method to get the help file version.
	 *
	 * @return  string  Version suffix for help files.
	 *
	 * @since   1.0
	 */
	public function getHelpVersion()
	{
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace('.', '', $this->RELEASE);
		}
		else {
			return '';
		}
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.5
	 */
	public function getShortVersion()
	{
		return $this->RELEASE.'.'.$this->DEV_LEVEL;
	}

	/**
	 * Gets a version string for the current Joomla with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.5
	 */
	public function getLongVersion()
	{
		return $this->PRODUCT.' '. $this->RELEASE.'.'.$this->DEV_LEVEL.' '
				. $this->DEV_STATUS.' [ '.$this->CODENAME.' ] '.$this->RELDATE.' '
				.$this->RELTIME.' '.$this->RELTZ;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param   string  $component    Name of the component.
	 * @param   bool    $mask         Mask as Mozilla/5.0 or not.
	 * @param   bool    $add_version  Add version afterwards to component.
	 *
	 * @return  string  User Agent.
	 *
	 * @since   1.0
	 */
	public function getUserAgent($component = null, $mask = false, $add_version = true)
	{
		if ($component === null) {
			$component = 'Framework';
		}

		if ($add_version) {
			$component .= '/'.$this->RELEASE;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		if ($mask) {
			return 'Mozilla/5.0 '. $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		}
		else {
			return $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		}
	}
}

// Define the Joomla version if not already defined.
if (!defined('JVERSION')) {
	$jversion = new JVersion;
	define('JVERSION', $jversion->getShortVersion());
}

?>
