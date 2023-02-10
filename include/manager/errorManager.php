<?php
/**
 * エラー処理マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2023 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: errorManager.php 198 2008-01-07 01:42:19Z fishbone $
 * @link       http://www.magic3.org
 */
class ErrorManager
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * デバッグ用出力
	 *
	 * デバッグフラグ「M3_SYSTEM_DEBUG」(global.php)がtrueのとき、_debugテーブルにメッセージを出力する
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 */
	public function writeDebug($method, $msg)
	{
		if (M3_SYSTEM_DEBUG) $this->systemDb->debugOut($method, $msg);
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
	public function writeInfo($method, $msg)
	{
		$this->systemDb->writeErrorLog('info', $method, $msg);
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
	public function writeWarn($method, $msg)
	{
		$this->systemDb->writeErrorLog('warn', $method, $msg);
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
	public function writeError($method, $msg)
	{
		$this->systemDb->writeErrorLog('error', $method, $msg);
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
	public function writeFatal($method, $msg)
	{
		$this->systemDb->writeErrorLog('fatal', $method, $msg);
	}
}
?>
