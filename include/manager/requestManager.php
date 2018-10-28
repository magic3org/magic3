<?php
/**
 * HTTPリクエスト、レスポンス、セッション管理マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class RequestManager extends Core
{
	private $db;						// DBオブジェクト
	private $tmpCookie;		// クッキー送信前のクッキー格納データ
	private $magicQuote;	// バックスラッシュでの文字エスケープ処理
	private $sessionNoUpdate;		// セッションの更新を停止するかどうか(参照は可能)
	private $sessionOpenEventCallbacks;		// セッション開始イベントコールバック関数
	private $sessionCloseEventCallbacks;		// セッション終了イベントコールバック関数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		// セッションハンドラ設定
		// セッションDBが使用可能な場合は、ハンドラを設定し、
		// 使用不可の場合は、デフォルトのセッション管理を使用する
		if (M3_SESSION_DB && $this->gEnv->canUseDbSession()){
			ini_set('session.save_handler', 'user');		// 追加(2008/7/7)
			session_set_save_handler(	array($this, '_sessionOpen'),
										array($this, '_sessionClose'),
										array($this, '_sessionRead'),
										array($this, '_sessionWrite'),
										array($this, '_sessionDestroy'),
										array($this, '_sessionGc'));
										
			// シャットダウン時の処理(2015/12/21 追加)
			if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
				session_register_shutdown();
			} else {
				register_shutdown_function('session_write_close');
			}
		}
		
		// その他パラメータ初期化
		$this->tmpCookie = array();		// クッキー送信前のクッキー格納データ
		$this->sessionOpenEventCallbacks = array();		// セッション開始イベントコールバック関数
		$this->sessionCloseEventCallbacks = array();	// セッション終了イベントコールバック関数
		
		// magic quoteが有効の場合は回避手段を取る
		if (get_magic_quotes_gpc() == 1) $this->magicQuote = true;
	}
	/**
	 * POST値が設定されているか判断
	 */
	public function isPost($name)
	{
		return isset($_POST[$name]);
	}
	/**
	 * GET値が設定されているか判断
	 */
	public function isGet($name)
	{
		return isset($_GET[$name]);
	}
	/**
	 * POST値取得
	 */
	public function valueOfPost($name)
	{
		return self::isPost($name) ? $_POST[$name] : '';
	}
	/**
	 * POST値取得(トリミング(前後の空白削除)あり)
	 */
	public function trimValueOfPost($name)
	{
		$value = self::isPost($name) ? $_POST[$name] : '';
		return $this->_trimValueOf($name, $value);
	}
	/**
	 * GET値取得
	 */
	public function valueOfGet($name)
	{
		return self::isGet($name) ? $_GET[$name] : '';
	}
	/**
	 * GET値取得(トリミング(前後の空白削除)あり)
	 */
	public function trimValueOfGet($name)
	{
		$value = self::isGet($name) ? $_GET[$name] : '';
		return $this->_trimValueOf($name, $value);
	}
	/**
	 * POST,GETから値を取得
	 *
	 * POSTまたはGETから値を取得
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @return string			取得値
	 */
	public function valueOf($name, $default = '')
	{
		//return isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : $default);
		//return self::gpc_stripslashes(isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : $default));
		return $this->gpc_stripslashes(isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : $default));
	}
	/**
	 * POST,GETから値を取得(トリミング(前後の空白削除)あり)
	 *
	 * POSTまたはGETから値を取得し、トリミング(前後の空白削除)する
	 * デフォルトでHTMLタグを取り除く
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @return string			取得値
	 */
	public function trimValueOf($name, $default = '')
	{
		$srcValue = isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : $default);
		return $this->_trimValueOf($name, $srcValue, $default);
	}
	/**
	 * POST,GETから値をint値を取得(トリミング(前後の空白削除)あり)
	 *
	 * POSTまたはGETから値を取得し、トリミング(前後の空白削除)する
	 * デフォルトでHTMLタグを取り除く
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないとき、値が空文字列のときのデフォルト値
	 * @return string			取得値
	 */
	//public function trimIntValueOf($name, $default = '')
	public function trimIntValueOf($name, $default)			// デフォルト値必須(2015/2/13)
	{
		$srcValue = isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : $default);
		return $this->_trimValueOf($name, $srcValue, $default, 1/* int型チェック */);
	}
	/**
	 * POST,GETから値をチェックボックスの値を取得(トリミング(前後の空白削除)あり)
	 *
	 * POSTまたはGETから値を取得し、トリミング(前後の空白削除)する
	 * デフォルトでHTMLタグを取り除く
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @return string			取得値(0または1)
	 */
	public function trimCheckedValueOf($name, $default = '')
	{
		$srcValue = isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : $default);
		$checked = $this->_trimValueOf($name, $srcValue, $default);
		return ($checked == 'on') ? '1' : '0';
	}
	/**
	 * トリミング(前後の空白削除)処理
	 *
	 * デフォルトで不正な文字(HTMLタグ等)を取り除く
	 *
	 * @param string $name		キー値
	 * @param string $value  	値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @param int $ckeckType  	値の型のチェックタイプ(0=型チェックなし、1=int型)
	 * @return string			取得値
	 */
	public function _trimValueOf($name, $value, $default='', $ckeckType=0)
	{
		// トリムをかける
		if (is_array($value)){
			$trimValue = array_map('trim', $value);
			$destValue = array();
			foreach (array_keys($trimValue) as $key){
				$stripValue = $this->gpc_stripslashes($trimValue[$key]);
				$stripValue = strip_tags($stripValue);

				// 危険性の高いその他変換。問題がある場合は文字列をクリアする
				$saveValue = $stripValue;
				$stripValue = $this->_convSafeText($stripValue);
				if ($stripValue != $saveValue) $stripValue = '';

				$isValid = false;
				if (strlen($stripValue) == strlen($trimValue[$key])){		// 文字列長が同じとき
					if ($ckeckType == 1){		// int型チェック
						if ($stripValue == ''){				// 空文字列の場合はエラーと見なさない(2015/2/19 追加)
							$isValid = true;
						} else if (is_numeric($stripValue) && !strstr($stripValue, '.')){
							$isValid = true;
						} else {	// エラーの場合は値を修正
							$stripValue = '';
						}
					} else {
						$isValid = true;
					}
				}
				
				// エラーがある場合はログを残す
				if (!$isValid){
					// ウィジェット内での処理の場合はウィジェットIDも出力
					$widgetId = $this->gEnv->getCurrentWidgetId();// ウィジェットID
					if (empty($widgetId)){
						$this->gOpeLog->writeUserData(__METHOD__, 'POSTまたはGET値の異常データを検出しました。', 2010, 'name=' . $name . ', value=[' . $value[$key] . ']');
					} else {		// ウィジェットIDが設定されているとき
						$this->gOpeLog->writeUserData(__METHOD__, 'POSTまたはGET値の異常データを検出しました。', 2011, 'name=' . $name . ', value=[' . $value[$key] . '], widgetid=' . $widgetId);
					}
				}
				// 空のときはデフォルト値の設定
				if ($stripValue == '') $stripValue = $default;
				$destValue[] = $stripValue;
			}
		} else {
			$trimValue = trim($value);
			
			// HTMLタグが含まれていた場合は、ログを残す
			$destValue = $this->gpc_stripslashes($trimValue);
			$destValue = strip_tags($destValue);
			
			// 危険性の高いその他変換。問題がある場合は文字列をクリアする
			$saveValue = $destValue;
			$destValue = $this->_convSafeText($destValue);
			if ($destValue != $saveValue) $destValue = '';

			$isValid = false;
			if (strlen($destValue) == strlen($trimValue)){		// 文字列長が同じとき
				if ($ckeckType == 1){		// int型チェック
					if ($destValue == ''){				// 空文字列の場合はエラーと見なさない(2015/2/19 追加)
						$isValid = true;
					} else if (is_numeric($destValue) && !strstr($destValue, '.')){
						$isValid = true;
					} else {	// エラーの場合は値を修正
						$destValue = '';
					}
				} else {
					$isValid = true;
				}
			}
				
			// エラーがある場合はログを残す
			if (!$isValid){
				// ウィジェット内での処理の場合はウィジェットIDも出力
				$widgetId = $this->gEnv->getCurrentWidgetId();// ウィジェットID
				if (empty($widgetId)){
					$this->gOpeLog->writeUserData(__METHOD__, 'POSTまたはGET値の異常データを検出しました。', 2012, 'name=' . $name . ', value=[' . $value . ']');
				} else {		// ウィジェットIDが設定されているとき
					$this->gOpeLog->writeUserData(__METHOD__, 'POSTまたはGET値の異常データを検出しました。', 2013, 'name=' . $name . ', value=[' . $value . '], widgetid=' . $widgetId);
				}
			}
			// 空のときはデフォルト値の設定
			if ($destValue == '') $destValue = $default;
		}
		return $destValue;
	}
	/**
	 * 安全なテキストに変換
	 *
	 * @param string $src		変換するデータ
	 * @return string			変換後データ
	 */
	function _convSafeText($src)
	{
		// マッチさせる文字列
		$search = array("':[\s]*?expression\('si",	// Javascriptが実行されないための対応「:expression(」「:url(」を削除(IE6,7用対応) 2009/1/15
						"':[\s]*?url\('si");

		// 変換文字列
		$replace = array("", "");

		return preg_replace($search, $replace, $src);
	}
	/**
	 * 取得メソッドがGETかどうか
	 *
	 * @return bool			true=GETメソッド、false=GET以外
	 */
	function isGetMethod()
	{
		$method = strtoupper($this->trimServerValueOf('REQUEST_METHOD'));	// アクセスメソッド
		if ($method == 'GET'){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 取得メソッドがPOSTかどうか
	 *
	 * @return bool			true=POSTメソッド、false=POST以外
	 */
	function isPostMethod()
	{
		$method = strtoupper($this->trimServerValueOf('REQUEST_METHOD'));	// アクセスメソッド
		if ($method == 'POST'){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * cmdパラメータ付きのアクセスかどうか
	 *
	 * @return bool			true=cmd付きアクセス、false=cmdなし
	 */
	function isCmdAccess()
	{
		$cmd = $this->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);	// 実行コマンド
		$isCmd = !empty($cmd);			// コマンド実行かどうか
		return $isCmd;
	}
	/**
	 * $_SERVERから値を取得(トリミング(前後の空白削除)あり)
	 *
	 * $_SERVERから値を取得し、トリミング(前後の空白削除)する
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @return string			取得値
	 */
	public function trimServerValueOf($name, $default = '')
	{
		$value = isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
		
		// トリムをかける
		if (is_array($value)){
			$value = array_map('trim', $value);
		} else {
			$value = trim($value);
		}
		return $value;
	}
	/**
	 * セッションから値を取得
	 *
	 * $_SESSIONから値を取得する。
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @return string			取得値
	 */
	public function getSessionValue($name, $default = '')
	{
		$value = isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
		return $value;
	}
	/**
	 * セッションに値を格納
	 *
	 * $_SESSIONに値を格納する。
	 *
	 * @param string $name		キー値
	 * @param string $value  	格納値
	 * @return bool				true=更新、false=更新なし
	 */
	public function setSessionValue($name, $value = '')
	{
		// セッション更新が停止されている場合は終了
		if ($this->sessionNoUpdate) return false;
		
		$_SESSION[$name] = $value;
		return true;
	}
	/**
	 * セッション格納値を開放
	 *
	 * $_SESSIONの指定値を開放する
	 *
	 * @param string $name		キー値
	 * @return bool				true=更新、false=更新なし
	 */
	public function unsetSessionValue($name)
	{
		// セッション更新が停止されている場合は終了
		if ($this->sessionNoUpdate) return false;
		
		unset($_SESSION[$name]);
		return true;
	}
	/**
	 * セッションからシリアライズされた値を取得
	 *
	 * @param string $name		キー値
	 * @return object			取得したオブジェクト、なしの場合はnull
	 */
	public function getSessionValueWithSerialize($name)
	{
		$value = isset($_SESSION[$name]) ? unserialize($_SESSION[$name]) : null;
		return $value;
	}
	/**
	 * セッションに値をシリアライズして格納
	 *
	 * @param string $name		キー値
	 * @param object $value  	格納するオブジェクト、nullをセットした場合はセッション値を削除
	 * @return bool				true=更新、false=更新なし
	 */
	public function setSessionValueWithSerialize($name, $value = null)
	{
		// セッション更新が停止されている場合は終了
		if ($this->sessionNoUpdate) return false;
		
		if ($value == null){
			unset($_SESSION[$name]);
		} else {
			$_SESSION[$name] = serialize($value);
		}
		return true;
	}
	/**
	 * セッションの値の更新を停止する
	 *
	 * @return 					なし
	 */
	public function stopSessionUpdate()
	{
		$this->sessionNoUpdate = true;		// セッションの更新を停止するかどうか(参照は可能)
	}
	/**
	 * クッキーから値を取得
	 *
	 * @param string $name		キー値
	 * @param string $default  	値が存在しないときのデフォルト値
	 * @return string			取得値
	 */
	public function getCookieValue($name, $default = '')
	{
		// 設定したクッキー値があれば取得
		$value = isset($this->tmpCookie[$name]) ? $this->tmpCookie[$name] : '';		// クッキー送信前のクッキー格納データ

		if ($value == '') $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
		return $value;
	}
	/**
	 * クッキーに値を格納
	 *
	 * @param string $name		キー値
	 * @param string $value  	格納値
	 * @param int $expireDay  	クッキーの生存期間(日)。0を設定した場合はブラウザ閉じるまで生存。
	 * @return 					なし
	 */
	public function setCookieValue($name, $value = '', $expireDay = 30)
	{
		if (floatval($expireDay) == 0){
			$cookExpire = 0;		// ブラウザ閉じるまで
		} else {
			$cookExpire = time() + 60 * 60 * 24 * $expireDay;
		}
		//setcookie($name, $value, $cookExpire);
		setcookie($name, $value, $cookExpire, '/');
		
		// 設定したクッキー値を保存
		$this->tmpCookie[$name] = $value;		// クッキー送信前のクッキー格納データ
	}
	/**
	 * クッキーの値を削除
	 *
	 * @param string $name		キー値
	 * @return 					なし
	 */
	public function removeCookieValue($name)
	{
		setcookie($name, '', time() - 3600, '/');
	}
	/**
	 * URLクエリー配列を取得
	 *
	 * @return array			URLクエリー文字列を解析した配列
	 */
	public function getQueryArray()
	{
		static $queryArray;
		
		if (!isset($queryArray)){
			$queryArray = array();
			$queryStr = self::trimServerValueOf('QUERY_STRING');
			if (!empty($queryStr)){
				parse_str($queryStr, $queryArray);
				// パラメータのエラーチェック
			}
		}
		return $queryArray;
	}
	/**
	 * クエリー文字列中のWikiページ名を取得
	 *
	 * @return string			Wikiページ名、空の時はWikiページ名なし
	 */
	public function getWikiPageFromQuery()
	{
		$wikiPage = '';
		$args = explode('&', $_SERVER['QUERY_STRING']);// 「&」で分割
		for ($i = 0; $i < count($args); $i++){
			$line = $args[$i];
			$pos = strpos($line, '=');
			if ($pos){		// 「=」が存在するとき
				//list($key, $value) = explode('=', $line);
			} else {
				$wikiPage = $line;		// 「=」なしのパラメータはwikiパラメータとする
				break;
			}
		}
		return $wikiPage;
	}
	/**
	 * 文字のエスケープ処理
	 *
	 * '(シングルクオート)、" (ダブルクオート)、\(バックスラッシュ) 、NULLにバックスラッシュでエスケープ処理を行う
	 *
	 * @param string $str		変換元文字列
	 * @return string			変換後文字列
	 */
/*	function gpc_addslashes($str){
		if ($this->magicQuote){
			return $str;
		} else {
			return addslashes($str);
		}
	}*/
	/**
	 * 文字のエスケープ処理をはずす
	 *
	 * '(シングルクオート)、" (ダブルクオート)、\(バックスラッシュ) 、NULLのバックスラッシュでのエスケープ処理を削除
	 *
	 * @param string $str		変換元文字列
	 * @return string			変換後文字列
	 */
	function gpc_stripslashes($str){
		if ($this->magicQuote){  
			return stripslashes($str);
		} else {  
			return $str;  
		}  
	}
	//******************************************************
	// patTemplateのテンプレートに値を埋め込む。値を省略した場合は、POST,GETデータから取得したデータを再設定する。
	// $name: patTemplateのテンプレート名
	// $valueName: HTMLタグに設定した名前。patTemplateの値埋め込み用キーワードも同じものを使用する。
	// $default: 値埋め込み用キーワードに埋め込む値。省略の場合はPOST,GETデータから$valueNameの値を取得する。
	function addValueToTemplate($name, $valueName, $default = null)
	{
		if ($default == null){
			$this->tmpl->addVar($name, $valueName, $this->valueOf($valueName));
		} else {
			$this->tmpl->addVar($name, $valueName, $default);
		}
	}
	/**
	 * セッションを開く
	 */
	function _sessionOpen($save_path, $session_name)
	{
		return true;
	}
	/**
	 * セッションを閉じる
	 */
	function _sessionClose()
	{
		return true;
	}
	/**
	 * セッションデータを読み込む
	 */
	function _sessionRead($id)
	{
		return $this->db->readSession($id);
	}
	/**
	 * セッションデータを書き込む
	 *
	 * @param string $id  			セッションID
	 * @param string $sessData  	セッションデータ
	 */
	function _sessionWrite($id, $sessData)
	{
		return $this->db->writeSession($id, $sessData);
	}
	/**
	 * セッションを破棄する
	 *
	 * @param string $id  			セッションID
	 */
	function _sessionDestroy($id)
	{
		return $this->db->destroySession($id);
	}
	/**
	 * 時間経過したセッションを破棄する
	 *
	 * @param int $maxlifetime  	セッションの生存時間(秒)
	 */
	function _sessionGc($maxlifetime)
	{
		return $this->db->gcSession($maxlifetime);
	}
	
	
	/**
	 * セッション開始イベント時のコールバック関数追加
	 *
	 * @param  callable  $callback		コールバック関数
	 * @return bool						true=正常終了,false=異常終了
	 */
	function addSessionOpenEventCallback($callback)
	{
		if (!is_callable($callback)) return false;
		
		// コールバックス関数追加
		$this->sessionOpenEventCallbacks[] = $callback;
		
		return true;
	}
	/**
	 * セッション終了イベント時のコールバック関数追加
	 *
	 * @param  callable  $callback		コールバック関数
	 * @return bool						true=正常終了,false=異常終了
	 */
	function addSessionCloseEventCallback($callback)
	{
		if (!is_callable($callback)) return false;
		
		// コールバックス関数追加
		$this->sessionCloseEventCallbacks[] = $callback;
		
		return true;
	}
	/**
	 * セッション開始イベント時のコールバック関数を実行
	 *
	 * @return bool						true=正常終了,false=異常終了
	 */
	function _doSessionOpenEventCallback()
	{
		$callbackCount = count($this->sessionOpenEventCallbacks);
		if ($callbackCount == 0) return true;
		
		$retStatus = true;
		for ($i = 0; $i < $callbackCount; $i++){
			$callback = $this->sessionOpenEventCallbacks[$i];
			if (is_callable($callback)){
				// コールバック関数を実行
				$result = call_user_func($callback);
				if (!$result) $retStatus = false;
			}
		}
		return $retStatus;
	}
	/**
	 * セッション終了イベント時のコールバック関数を実行
	 *
	 * @return bool						true=正常終了,false=異常終了
	 */
	function _doSessionCloseEventCallback()
	{
		$callbackCount = count($this->sessionCloseEventCallbacks);
		if ($callbackCount == 0) return true;
		
		$retStatus = true;
		for ($i = 0; $i < $callbackCount; $i++){
			$callback = $this->sessionCloseEventCallbacks[$i];
			if (is_callable($callback)){
				// コールバック関数を実行
				$result = call_user_func($callback);
				if (!$result) $retStatus = false;
			}
		}
		return $retStatus;
	}
}
?>
