<?php
/**
 * Wikiパラメータ管理クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */

class WikiParam
{
	private static $db = null;		// DBオブジェクト
	private static $_init;			// 初期化完了フラグ
	private static $cmd;			// コマンド
	private static $plugin;			// プラグイン
	private static $page;			// ページ値
	private static $msg;			// 編集データ値
	private static $original;		// 編集元データ値
	private static $refer;
	private static $digest;
	private static $arg;			// URLパラメータ
	private static $ignoreParams = array(M3_REQUEST_PARAM_PAGE_SUB_ID, M3_REQUEST_PARAM_OPERATION_COMMAND);		// 受け付けないパラメータ
	private static $subId = '';			// Magic3のページサブID指定
	private static $isInline;		// ブロック型,インライン型処理の区別
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * クラスを初期化する
	 *
	 * @return なし
	 */
	public static function _init()
	{
		global $gRequestManager;
		global $gEnvManager;
		
		// 初期化が完了しているときは終了
		if (self::$_init) return;
		
		// ##### パラメータ初期化 #####
		self::$cmd		= $gRequestManager->trimValueOf('wcmd');
		self::$plugin	= $gRequestManager->trimValueOf('plugin');
		self::$page		= strip_bracket($gRequestManager->trimValueOf('page'));
		self::$msg		= str_replace("\r", '', $gRequestManager->valueOf('msg'));
		self::$original	= str_replace("\r", '', $gRequestManager->valueOf('original'));
		//self::$digest	= $gRequestManager->trimValueOf('digest');
		self::$refer	= $gRequestManager->trimValueOf('refer');
		self::$arg		= $_SERVER['QUERY_STRING'];
		/*if (PKWK_QUERY_STRING_MAX && strlen(self::$arg) > PKWK_QUERY_STRING_MAX){	// 文字列長のチェック
			self::$arg = '';
		}*/
		self::$arg = self::_input_filter(self::$arg);

		// Magic3用パラメータを除く
		$newArg = '';
		$args = explode('&', self::$arg);// 「&」で分割
		for ($i = 0; $i < count($args); $i++){
			$line = $args[$i];
			$pos = strpos($line, '=');
			if ($pos){		// 「=」が存在するとき
				list($key, $value) = explode('=', $line);
				if (!in_array($key, self::$ignoreParams)) $newArg .= $line . '&';
				//if ($key == M3_REQUEST_PARAM_PAGE_SUB_ID) self::$subId = $line;			// ページサブIDを保存
			} else {
				$newArg .= $line . '&';
			}
		}
		self::$arg = trim($newArg, '&');
		
		// ページサブIDがデフォルトでないときは強制的に設定
		self::$subId = '';
		if ($gEnvManager->getCurrentPageSubId() != $gEnvManager->getDefaultPageSubId()) self::$subId = M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $gEnvManager->getCurrentPageSubId();
		
		// 初期化完了
		self::$_init = true;
	}
	/**
	 * 入力値クリーニング
	 *
	 * @param string,array $param	クリーニング対象値
	 * @return string,array			クリーニングした値
	 */
	public static function _input_filter($param)
	{
//		static $magic_quotes_gpc = NULL;
//		if ($magic_quotes_gpc === NULL)
//		    $magic_quotes_gpc = get_magic_quotes_gpc();

		if (is_array($param)) {
			return array_map('input_filter', $param);
		} else {
			$result = str_replace("\0", '', $param);
//			if ($magic_quotes_gpc) $result = stripslashes($result);
			return $result;
		}
	}
	/**
	 * オブジェクトを初期化
	 *
	 * @param object $db	DBオブジェクト
	 * @return				なし
	 */
	public static function init($db)
	{
		self::$db = $db;
		self::_init();
	}
	/**
	 * 呼び出しパラメータのチェック
	 *
	 * @return bool			true=正常、false=異常
	 */
	public static function checkParam()
	{
		global $gEnvManager;
		global $gOpeLogManager;
		
		// クラス初期化
		self::_init();
		
		// コマンドとプラグイン両方のパラメータが設定されている場合はエラー
		if (!empty(self::$cmd) && !empty(self::$plugin)){
			$errMessage = 'Wikiパラメータの不正。';
			$gOpeLogManager->writeUserAccess(__METHOD__, '不正なアクセスを検出しました。' . $errMessage, 2203, 'アクセスをブロックしました。URL: ' . $gEnvManager->getCurrentRequestUri());
			return false;
		}

		// 英数字以外はエラー
		if (!empty(self::$cmd) && !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', self::$cmd)){
			$errMessage = 'Wikiパラメータの不正。';
			$gOpeLogManager->writeUserAccess(__METHOD__, '不正なアクセスを検出しました。' . $errMessage, 2203, 'アクセスをブロックしました。URL: ' . $gEnvManager->getCurrentRequestUri());
			return false;
		}
		if (!empty(self::$plugin) && !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', self::$plugin)){
			$errMessage = 'Wikiパラメータの不正。';
			$gOpeLogManager->writeUserAccess(__METHOD__, '不正なアクセスを検出しました。' . $errMessage, 2203, 'アクセスをブロックしました。URL: ' . $gEnvManager->getCurrentRequestUri());
			return false;
		}
		return true;
	}
	/**
	 * コマンド値を取得
	 *
	 * @return string	コマンド値
	 */
	public static function getCmd()
	{
		return self::$cmd;
	}
	/**
	 * コマンド値を設定
	 *
	 * @param string $value		設定値
	 * @return なし
	 */
	public static function setCmd($value)
	{
		self::$cmd = $value;
	}
	/**
	 * プラグイン値を取得
	 *
	 * @return string	プラグイン値
	 */
	public static function getPlugin()
	{
		return self::$plugin;
	}
	/**
	 * ページ値を取得
	 *
	 * @return string	ページ値
	 */
	public static function getPage()
	{
		return self::$page;
	}
	/**
	 * ページ値を設定
	 *
	 * @param string $value		設定値
	 * @return なし
	 */
	public static function setPage($value)
	{
		self::$page = $value;
	}
	/**
	 * メッセージ値を取得
	 *
	 * @return string	メッセージ値
	 */
	public static function getMsg()
	{
		return self::$msg;
	}
	/**
	 * メッセージ値を設定
	 *
	 * @param string $value		設定値
	 * @return なし
	 */
	public static function setMsg($value)
	{
		self::$msg = $value;
	}
	/**
	 * レファラー値を取得
	 *
	 * @return string	レファラー値
	 */
	public static function getRefer()
	{
		return self::$refer;
	}
	/**
	 * レファラー値を設定
	 *
	 * @param string $value		設定値
	 * @return なし
	 */
	public static function setRefer($value)
	{
		self::$refer = $value;
	}
	/**
	 * 編集元データ値を取得
	 *
	 * @return string	編集元データ値
	 */
	public static function getOriginal()
	{
		return self::$original;
	}
	/**
	 * ダイジェスト値を取得
	 *
	 * @return string	ダイジェスト値
	 */
	public static function getDigest()
	{
		return self::$digest;
	}
	/**
	 * ダイジェスト値を設定
	 *
	 * @param string $value		設定値
	 * @return なし
	 */
	public static function setDigest($value)
	{
		self::$digest = $value;
	}
	/**
	 * 処理の区別(ブロック型,インライン型)を取得
	 *
	 * @return bool		true=インライン型、false=ブロック型
	 */
	public static function getIsInline()
	{
		return self::$isInline;
	}
	/**
	 * 処理の区別(ブロック型,インライン型)を設定
	 *
	 * @param bool $value		true=インライン型、false=ブロック型
	 * @return なし
	 */
	public static function setIsInline($value)
	{
		self::$isInline = $value;
	}
	/**
	 * 全パラメータを取得
	 *
	 * @return string	全パラメータ
	 */
	public static function getArg()
	{
		return self::$arg;
	}
	/**
	 * ブラケットをはずした全パラメータ文字列を取得
	 *
	 * @return string	全パラメータ文字列
	 */
	public static function getUnbraketArg()
	{
		$arg = rawurldecode(self::$arg);
		$arg = strip_bracket($arg);
		
		// 日本語パラメータにも対応
	//	$arg = mb_convert_encoding($arg, SOURCE_ENCODING, 'UTF-8,SJIS-win,ASCII');		// 出力するコードに変換
		$arg = mb_convert_encoding($arg, M3_ENCODING, 'UTF-8,SJIS-win,ASCII');		// 出力するコードに変換
		return $arg;
	}
	/**
	 * Magic3用サブページIDパラメータの取得
	 *
	 * @return string	パラメータ
	 */
	/*public static function getSubId()
	{
		return self::$subId;
	}*/
	/**
	 * Magic3用にクエリー文字列を変換
	 *
	 * @param string $srcStr	変換対象文字列
	 * @param bool $encode		URLエンコードを行うかどうか
	 * @return string			変換後文字列
	 */
	public static function convQuery($srcStr = '?', $encode = true)
	{
		global $gEnvManager;
				
		$destStr = $srcStr;
		
		// cmdをMagic3のWikiコマンド「wcmd」に変換
		$destStr = preg_replace_callback('/[a-z]*cmd=/', array(self, "_replace_query_callback"), $destStr);

		// Wikiページ名のみの場合はページサブIDを付加しない
		$pos = strpos($destStr, '=');
		if ($pos){		// 「=」が存在するとき
			// ページサブIDを追加
			if (!empty(self::$subId)){
				if (strEndsWith($destStr, '?')){		// 最後が「?」のとき
					$destStr .= self::$subId;
				} else {
					if ($encode){		// URLエンコードする場合
						$destStr .= '&amp;' . self::$subId;
					} else {
						$destStr .= '&' . self::$subId;
					}
				}
			}
		} else {		// 最後が「?」または「?Wikiページ名」のとき
			// 最後が「?」のときはページサブIDを追加
			if (strEndsWith($destStr, '?')) $destStr .= self::$subId;
		}
		if ($destStr == '?') $destStr = '';
		return $destStr;
	}
	/**
	 * クエリー文字列を変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    static function _replace_query_callback($matchData)
	{
		$destStr = $matchData[0];
		if ($destStr == 'cmd=') $destStr = M3_REQUEST_PARAM_OPERATION_WIKI_COMMAND . '=';
		return $destStr;
	}	
	/**
	 * コマンドキー値を取得
	 *
	 * @return string	コマンドキー値
	 */
	public static function getCmdKey()
	{
		return M3_REQUEST_PARAM_OPERATION_WIKI_COMMAND;
	}
	/**
	 * 実行中のスクリプトURL取得
	 *
	 * @return string	スクリプトURL
	 */
	public static function getScript()
	{
		return get_script_uri();
	}
	/**
	 * パラメータ値を取得
	 *
	 * @param string $key		取得キー
	 * @return なし
	 */
	public static function getVar($key)
	{
		global $gRequestManager;
		return $gRequestManager->trimValueOf($key);
	}
	/**
	 * POSTパラメータ値を取得
	 *
	 * @param string $key		取得キー
	 * @return なし
	 */
	public static function getPostVar($key)
	{
		global $gRequestManager;
		return $gRequestManager->trimValueOfPost($key);
	}
	/**
	 * パラメータ値を取得(trimなし)
	 *
	 * @param string $key		取得キー
	 * @return なし
	 */
	public static function getSrcVar($key)
	{
		global $gRequestManager;
		return $gRequestManager->valueOf($key);
	}
}
?>
