<?php
/**
 * 運用ログマネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/**
 * メッセージコード
 * 1000～		DB関係のメッセージ
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class OpeLogManager extends Core
{
	private $db;						// DBオブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
	}
	/**
	 * デバッグ用出力
	 *
	 * デバッグフラグ「M3_SYSTEM_DEBUG」(global.php)がtrueのとき、_debugテーブルにメッセージを出力する
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @return なし
	 */
	public function writeDebug($method, $msg, $code = 0, $msgExt = '')
	{
		if (M3_SYSTEM_DEBUG) $this->db->debugOut($method, $msg, $code, $msgExt);
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
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeInfo($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('info', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
	/**
	 * 操作要求出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * システム管理者に操作要求するメッセージを出力する
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeRequest($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('request', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
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
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeWarn($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('warn', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
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
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeError($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('error', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
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
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeFatal($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('fatal', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
	/**
	 * ユーザ操作出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * ユーザの通常の操作で記録すべきもの
	 * 例) ログイン実行等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeUserInfo($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('user_info', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
	/**
	 * ユーザ操作要求出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * ユーザの通常の操作で、システム管理者に操作要求するメッセージを出力する
	 * 例) ユーザ登録の手動認証
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeUserRequest($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('user_request', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
	/**
	 * ユーザ操作エラー出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * ユーザの通常の操作エラーで記録すべきもの
	 * 例) ログイン失敗等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeUserError($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('user_err', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
	/**
	 * ユーザの不正なアクセスを出力
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * ユーザの不正なサイトアクセス
	 * 例) 不正なURLへのアクセス
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeUserAccess($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('user_access', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
	/**
	 * ユーザからの不正なデータを検出
	 *
	 * 以下の状況でメッセージ出力するためのインターフェイス
	 * ユーザからの受信データで不正なデータを検出
	 * 例) 不正登録データの受信
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @param bool   $showTop	メッセージをトップ表示するかどうか
	 * @return なし
	 */
	public function writeUserData($method, $msg, $code = 0, $msgExt = '', $searchOption = '', $link = '', $showTop = false)
	{
		$this->db->writeErrorLog('user_data', $method, $msg, $code, $msgExt, $searchOption, $link, $showTop);
	}
}
?>
