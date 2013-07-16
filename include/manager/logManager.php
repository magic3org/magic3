<?php
/**
 * ログ管理マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: logManager.php 1979 2009-06-14 15:08:38Z fishbone $
 * @link       http://www.magic3.org
 */
define('LOG4PHP_CONFIGURATION', M3_SYSTEM_CONF_PATH . '/log4php.properties');		// 設定ファイルの位置
//require_once(M3_SYSTEM_LIB_PATH . '/log4php/LoggerManager.php');					// log4Php取り込み

/**
 * ログ出力管理クラス
 *
 * Magic3 Frameworkで出力するすべてのログを管理する
 * 設定ファイルのパスは上記「LOG4PHP_CONFIGURATION」で定義する
 * 設定ファイルのデフォルトの位置：プロジェクトルート/include/conf/log4php.properties
 * ログファイルはデフォルトで、「/tmp/magic3_YYYYMMDD.log」に出力される
 * 
 * エラーレベルの順位
 * DEBUG ＜ INFO ＜ WARN ＜ ERROR ＜ FATAL
 * エラーレベル指定で、ログの出力を制御する
 */
class LogManager
{
//	public $logger;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// ログオブジェクト作成
		//$this->logger = LoggerManager::getLogger('Main');
	}
	/**
	 * ログ出力オブジェクト取得
	 *
	 * @return PDO 			接続コネクションオブジェクト
	 */
	function _getLogger()
	{
		static $logger;
		
		if (!is_object($logger)){
			// ログオブジェクト作成
			require_once(M3_SYSTEM_LIB_PATH . '/log4php/LoggerManager.php');					// log4Php取り込み
			$logger = LoggerManager::getLogger('Main');
		}
		return $logger;
	}
	/**
	 * デバッグ文出力
	 *
	 * デバッグ時に任意のメッセージを出力するためのインターフェイス
	 * 実運用時は出力しない
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 */
	public function debug($method, $msg)
	{
		//$this->logger->debug($msg . ' (' . $method . ')');
		$this->_getLogger()->debug($msg . ' (' . $method . ')');
	}
	/**
	 * 運用状況確認用出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * アプリケーション運用時に、正常な状態で取得したいメッセージを出力する
	 * 例) ログインの状況等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 */
	public function info($method, $msg)
	{
		//$this->logger->info($msg . ' (' . $method . ')');
		$this->_getLogger()->info($msg . ' (' . $method . ')');
	}
	/**
	 * ワーニング出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * 正常動作可能でエラーではないが、ユーザに注意をうながすためのメッセージ
	 * 例) 引数の指定方法が正確でない等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 */
	public function warn($method, $msg)
	{
		//$this->logger->warn($msg . ' (' . $method . ')');
		$this->_getLogger()->warn($msg . ' (' . $method . ')');
	}
	/**
	 * 通常エラー出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * 割合起こりやすいエラーで、アプリケーションの続行は可能なもの
	 * 例) ファイル読み込みエラー、接続タイムアウト等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 */
	public function error($method, $msg)
	{
		//$this->logger->error($msg . ' (' . $method . ')');
		$this->_getLogger()->error($msg . ' (' . $method . ')');
	}
	/**
	 * 致命的エラー出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * アプリケーションの処理が続行不可能なエラーやシステム的エラー
	 * 例) DB例外発生等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 */
	public function fatal($method, $msg)
	{
		//$this->logger->fatal($msg . ' (' . $method . ')');
		$this->_getLogger()->fatal($msg . ' (' . $method . ')');
	}
}
?>
