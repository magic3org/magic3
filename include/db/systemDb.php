<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: systemDb.php 6161 2013-07-02 12:38:50Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/baseDb.php');

class SystemDb extends BaseDb
{
	const MAX_ACCESS_LOG_FIELD_LENGTH = 300;				// ログテーブルのフィールドの最大長
	const MAX_ACCESS_LOG_REQUEST_LENGTH = 500;				// リクエストの最大長
	const ADMIN_KEY_LIFETIME = 1440;							// 管理者一時キーの生存期間(分)
	const INDEX_ADD_VALUE = 5;								// 表示順の増加分
	
	/**
	 * システム定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @return string		値
	 */
	function getSystemConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT sc_value FROM _system_config ';
		$queryStr .=  'WHERE sc_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['sc_value'];
		return $retValue;
	}
	/**
	 * システム定義値をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllSystemConfig(&$rows)
	{
		$queryStr  = 'SELECT sc_id,sc_value FROM _system_config ';
		$queryStr .=   'ORDER BY sc_id';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * システム定義値を更新
	 *
	 * @param string $key	キーとなる項目値
	 * @param string $value	設定値
	 * @return bool			true=更新成功、false=更新失敗
	 */
	function updateSystemConfig($key, $value)
	{
		$queryStr = 'SELECT sc_value FROM _system_config ';
		$queryStr .=  'WHERE sc_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret){
			$queryStr  = 'UPDATE _system_config ';
			$queryStr .=   'SET sc_value = ? ';
			$queryStr .=   'WHERE sc_id = ?';
			$ret = $this->execStatement($queryStr, array($value, $key));			
		} else {
			$queryStr = 'INSERT INTO _system_config (';
			$queryStr .=  'sc_id, ';
			$queryStr .=  'sc_value ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($key, $value));	
		}
		return $ret;
	}
	/**
	 * 言語定義値をすべて取得
	 *
	 * @param string $lang			言語ID
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllLangString($lang, &$rows)
	{
		$queryStr  = 'SELECT ls_id,ls_value FROM _language_string ';
		$queryStr .=  'WHERE ls_language_id  = ? ';
		$queryStr .=   'ORDER BY ls_id';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * 言語定義値を更新
	 *
	 * @param string $lang	言語ID
	 * @param string $key	キーとなる項目値
	 * @param string $value	設定値
	 * @return bool			true=更新成功、false=更新失敗
	 */
	function updateLangString($lang, $key, $value)
	{
		$queryStr  = 'SELECT ls_value FROM _language_string ';
		$queryStr .=   'WHERE ls_id  = ? ';
		$queryStr .=     'AND ls_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($key, $lang), $row);
		if ($ret){
			$queryStr  = 'UPDATE _language_string ';
			$queryStr .=   'SET ls_value = ? ';
			$queryStr .=   'WHERE ls_id = ?';
			$queryStr .=     'AND ls_language_id = ?';
			$ret = $this->execStatement($queryStr, array($value, $key, $lang));			
		} else {
			$queryStr = 'INSERT INTO _language_string (';
			$queryStr .=  'ls_id, ';
			$queryStr .=  'ls_language_id, ';
			$queryStr .=  'ls_value ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($key, $lang, $value));	
		}
		return $ret;
	}
	/**
	 * サイト定義を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @param string $lang   言語ID
	 */
	function getSiteDefValue($key, $lang = '')
	{
		global $gEnvManager;
		
		if (empty($lang)){	// 空の場合はデフォルト言語ID取得
			$language = $gEnvManager->getDefaultLanguage();
		} else {
			$language = $lang;
		}
		$retValue = '';
		$queryStr  = 'SELECT sd_value FROM _site_def ';
		$queryStr .=   'WHERE sd_deleted = false ';
		$queryStr .=     'AND sd_id = ? ';
		$queryStr .=     'AND sd_language_id = ?';
		$ret = $this->selectRecord($queryStr, array($key, $language), $row);
		if ($ret) $retValue = $row['sd_value'];
		return $retValue;
	}
	/**
	 * サイト定義をすべて取得
	 *
	 * @param string $lang   言語ID
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllSiteDefValue($lang, &$rows)
	{
		$queryStr  = 'SELECT sd_id,sd_value FROM _site_def ';
		$queryStr .=   'WHERE sd_deleted = false ';
		$queryStr .=     'AND sd_language_id = ? ';
		$queryStr .=   'ORDER BY sd_id';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * デザイン定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @return string		値
	 */
	function getDesignConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT dn_value FROM _design ';
		$queryStr .=  'WHERE dn_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['dn_value'];
		return $retValue;
	}
	/**
	 * デザイン定義値を更新
	 *
	 * @param string $key	キーとなる項目値
	 * @param string $value	設定値
	 * @return				なし
	 */
	function updateDesignConfig($key, $value)
	{
		$queryStr = "UPDATE _design SET dn_value = ? WHERE dn_id = ?";
		$params = array($value, $key);
		$this->execStatement($queryStr, $params);
	}
	/**
	 * アクセスログの記録
	 *
	 * @param int    $user			ログインユーザID(0=不明のとき)
	 * @param string $cookieVal    	アクセス管理用クッキー値
	 * @param string $session		セッションID
	 * @param string $ip			クライアントIPアドレス
	 * @param string $method		アクセスメソッド
	 * @param string $uri			アクセスURI
	 * @param string $referer		リファラー
	 * @param string $request		リクエストパラメータ
	 * @param string $agent			クライアントアプリケーション
	 * @param string $language		クライアント認識可能言語
	 * @param string $path			アクセスパス
	 * @param bool   $isCookie		クッキーがあるかどうか
	 * @param bool   $isCrawler		クローラかどうか
	 * @return int					アクセスログシリアルNo(ログ保存失敗のときは0)
	 */
	function accessLog($user, $cookieVal, $session, $ip, $method, $uri, $referer, $request, $agent, $language, $path, $isCookie, $isCrawler)
	{
		global $gSystemManager;
		
		// 格納長を制限
		if (function_exists('mb_substr')){
			$uri = mb_substr($uri, 0, self::MAX_ACCESS_LOG_FIELD_LENGTH);	
			$referer = mb_substr($referer, 0, self::MAX_ACCESS_LOG_FIELD_LENGTH);
			$agent = mb_substr($agent, 0, self::MAX_ACCESS_LOG_FIELD_LENGTH);
			$request = mb_substr($request, 0, self::MAX_ACCESS_LOG_REQUEST_LENGTH);
		} else {
			$uri = substr($uri, 0, self::MAX_ACCESS_LOG_FIELD_LENGTH);	
			$referer = substr($referer, 0, self::MAX_ACCESS_LOG_FIELD_LENGTH);
			$agent = substr($agent, 0, self::MAX_ACCESS_LOG_FIELD_LENGTH);
			$request = substr($request, 0, self::MAX_ACCESS_LOG_REQUEST_LENGTH);
		}
		// トランザクション開始
		$this->startTransaction();
		
		// バージョンによる処理の違いを吸収
		if (defined('M3_STATE_IN_INSTALL')){		// システムがインストールモードで起動のとき
			// DBから直接バージョンを取得
			$currentVer = $this->getSystemConfig(M3_TB_FIELD_DB_VERSION);
		} else {
			$currentVer = $gSystemManager->getSystemConfig(M3_TB_FIELD_DB_VERSION);
		}
		if ($currentVer >= 2012031501){
			// バージョン2012031501以降で「al_cookie(クッキーがあるかどうか)」「al_crawler(クローラかどうか)」を追加(2012/3/15)
			$queryStr = 'INSERT INTO _access_log (';
			$queryStr .=  'al_user_id, ';
			$queryStr .=  'al_cookie_value, ';
			$queryStr .=  'al_session, ';
			$queryStr .=  'al_ip, ';
			$queryStr .=  'al_method, ';
			$queryStr .=  'al_uri, ';
			$queryStr .=  'al_referer, ';
			$queryStr .=  'al_request, ';
			$queryStr .=  'al_user_agent, ';
			$queryStr .=  'al_accept_language, ';
			$queryStr .=  'al_path, ';
			$queryStr .=  'al_cookie, ';
			$queryStr .=  'al_crawler, ';
			$queryStr .=  'al_dt ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now()';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($user, $cookieVal, $session, $ip, $method, $uri, $referer, $request, $agent, $language, $path, intval($isCookie), intval($isCrawler)));
		} else if ($currentVer >= 2010100901){
			// バージョン2010100901以降で「al_session(セッションID)」を追加(2010/10/11)
			$queryStr = 'INSERT INTO _access_log (';
			$queryStr .=  'al_user_id, ';
			$queryStr .=  'al_cookie_value, ';
			$queryStr .=  'al_session, ';
			$queryStr .=  'al_ip, ';
			$queryStr .=  'al_method, ';
			$queryStr .=  'al_uri, ';
			$queryStr .=  'al_referer, ';
			$queryStr .=  'al_request, ';
			$queryStr .=  'al_user_agent, ';
			$queryStr .=  'al_accept_language, ';
			$queryStr .=  'al_path, ';
			$queryStr .=  'al_dt ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now()';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($user, $cookieVal, $session, $ip, $method, $uri, $referer, $request, $agent, $language, $path));
		} else if ($currentVer >= 2009071601){
			// バージョン2009071601以降で「al_path(アクセスポイントパス)」を追加(2009/7/19)
			$queryStr = 'INSERT INTO _access_log (';
			$queryStr .=  'al_user_id, ';
			$queryStr .=  'al_cookie_value, ';
			$queryStr .=  'al_ip, ';
			$queryStr .=  'al_method, ';
			$queryStr .=  'al_uri, ';
			$queryStr .=  'al_referer, ';
			$queryStr .=  'al_request, ';
			$queryStr .=  'al_user_agent, ';
			$queryStr .=  'al_accept_language, ';
			$queryStr .=  'al_path, ';
			$queryStr .=  'al_dt ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now()';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($user, $cookieVal, $ip, $method, $uri, $referer, $request, $agent, $language, $path));
		} else {
			$queryStr = 'INSERT INTO _access_log (';
			$queryStr .=  'al_user_id, ';
			$queryStr .=  'al_cookie_value, ';
			$queryStr .=  'al_ip, ';
			$queryStr .=  'al_method, ';
			$queryStr .=  'al_uri, ';
			$queryStr .=  'al_referer, ';
			$queryStr .=  'al_request, ';
			$queryStr .=  'al_user_agent, ';
			$queryStr .=  'al_accept_language, ';
			$queryStr .=  'al_dt ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, now()';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($user, $cookieVal, $ip, $method, $uri, $referer, $request, $agent, $language));
		}
		
		$serial = $this->getMaxSerialOfAccessLog();
		
		// トランザクション終了
		$ret = $this->endTransaction();
		if ($ret){
			return $serial;
		} else {
			return 0;
		}
	}
	/**
	 * アクセスログのユーザIDを更新
	 *
	 * @param int    $serialNo		ログシリアルNo
	 * @param int    $userid		ユーザID
	 * @return bool					true=更新成功、false=更新失敗
	 */
	function updateAccessLogUser($serialNo, $userid)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$sql = "UPDATE _access_log SET al_user_id = ? WHERE al_serial = ?";
		$params = array($userid, $serialNo);
		$this->execStatement($sql, $params);
			
		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * アクセスログの最大シリアルNoを取得
	 *
	 * @return int		最大シリアルNo
	 */
	function getMaxSerialOfAccessLog()
	{
		$retValue = 0;
		$queryStr = 'select max(al_serial) as m from _access_log';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $retValue = $row['m'];
		return $retValue;
	}
	/**
	 * 自動ログイン情報登録
	 *
	 * @param int    $loginUserId	ログインユーザID
	 * @param string $loginKey		ログインキー
	 * @param string $clientId		クライアントID
	 * @param string $accessPath	アクセスポイントパス
	 * @param timestamp	$expireDt	期限日時
	 * @return bool				true=成功、false=失敗
	 */
	function updateAutoLogin($loginUserId, $loginKey, $clientId, $accessPath, $expireDt)
	{
		global $gEnvManager;
				
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 最新の登録データを取得
		$index = 0;		// インデックス番号
		$queryStr  = 'SELECT * FROM _auto_login ';
		$queryStr .=   'WHERE ag_user_id = ? ';
		$queryStr .=     'AND ag_client_id = ? ';
		$queryStr .=  'ORDER BY ag_index DESC ';
		$ret = $this->selectRecord($queryStr, array($loginUserId, $clientId), $row);
		if ($ret){
			if (!$row['ag_deleted']){		// レコードが削除されていないとき
				// データを削除
				$queryStr  = 'UPDATE _auto_login ';
				$queryStr .=   'SET ';
				$queryStr .=     'ag_deleted = true, ';
				$queryStr .=     'ag_update_user_id = ?, ';
				$queryStr .=     'ag_update_dt = now() ';
				$queryStr .=   'WHERE ag_id = ? ';
				$this->execStatement($queryStr, array($userId, $row['ag_id']));
			}
			$index = $row['ag_index'] + 1;
		}
		
		// データを追加
		$queryStr  = 'INSERT INTO _auto_login ';
		$queryStr .=   '(ag_id, ag_user_id, ag_client_id, ag_index, ag_path, ag_expire_dt, ag_access_log_serial, ag_create_user_id, ag_create_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, now())';
		$this->execStatement($queryStr, array($loginKey, $loginUserId, $clientId, $index, $accessPath, $expireDt, $logSerial, $userId));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 自動ログイン情報削除
	 *
	 * @param string $loginKey		ログインキー
	 * @return bool				true=成功、false=失敗
	 */
	function delAutoLogin($loginKey)
	{
		global $gEnvManager;
				
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// データを削除
		$queryStr  = 'UPDATE _auto_login ';
		$queryStr .=   'SET ';
		$queryStr .=     'ag_deleted = true, ';
		$queryStr .=     'ag_update_user_id = ?, ';
		$queryStr .=     'ag_update_dt = now() ';
		$queryStr .=   'WHERE ag_id = ? ';
		$this->execStatement($queryStr, array($userId, $loginKey));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 自動ログインキー取得
	 *
	 * @param int    $userId		ユーザID
	 * @param string $clientId		クライアントID
	 * @return string				自動ログインキー。取得できないときは空文字列。
	 */
	function getAutoLoginKey($userId, $clientId)
	{
		$queryStr  = 'SELECT * FROM _auto_login ';
		$queryStr .=   'WHERE ag_deleted = false ';
		$queryStr .=     'AND ag_user_id = ? ';
		$queryStr .=     'AND ag_client_id = ? ';
		$ret = $this->selectRecord($queryStr, array($userId, $clientId), $row);
		if ($ret){
			return $row[ag_id];
		} else {
			return '';
		}
	}
	/**
	 * ログインキーで自動ログイン情報取得
	 *
	 * @param string $loginKey		ログインキー
	 * @param string $clientId		クライアントID(照合用)
	 * @param array $row			取得データ
	 * @return bool				true=成功、false=失敗
	 */
	function getAutoLogin($loginKey, $clientId, &$row)
	{
		$queryStr  = 'SELECT * FROM _auto_login ';
		$queryStr .=   'WHERE ag_deleted = false ';
		$queryStr .=     'AND ag_id = ? ';
		$queryStr .=     'AND ag_client_id = ? ';
		$ret = $this->selectRecord($queryStr, array($loginKey, $clientId), $row);
		return $ret;
	}
	/**
	 * 言語名称を取得
	 *
	 * @param string $langId			言語ID
	 * @param string $dispLangId    	表示言語ID
	 * @return string					言語名称
	 */
	function getLanguageNameByDispLanguageId($langId, $dispLangId)
	{
		$retValue = '';
		$queryStr = 'SELECT * from _language ';
		$queryStr .=  'WHERE ln_id = ?';
		$ret = $this->selectRecord($queryStr, array($langId), $row);
		if ($ret){
			if ($dispLangId == 'ja'){	// 日本語のとき
				$retValue = $row['ln_name'];
			} else {
				$retValue = $row['ln_name_en'];
			}
		}
		return $retValue;
	}
	/**
	 * ページIDのリストを取得
	 *
	 * @param int $type				リストの種別(0=ページメインID,1=ページサブID)
	 * @param array $row			取得データ
	 * @return bool					true=成功、false=失敗
	 */
	function getPageIdRecords($type, &$rows)
	{
		$queryStr = 'SELECT * FROM _page_id ';
		$queryStr .=  'WHERE pg_type = ? ';
		$queryStr .=  'ORDER BY pg_priority';
		return $this->selectRecords($queryStr, array($type), $rows);
	}
	/**
	 * デフォルトのページサブIDを取得
	 *
	 * @param string $pageId		ページID
	 * @param int    $deviceType	端末タイプ(0=PC、1=携帯、2=スマートフォン)。値を取得する場合は初期値0を設定する。
	 * @return string				デフォルトのテンプレートID
	 */
	function getDefaultPageSubId($pageId, &$deviceType = null)
	{
		$retValue = '';
		$queryStr = 'SELECT * FROM _page_id ';
		$queryStr .=  'WHERE pg_id = ? AND pg_type = 0';
		$ret = $this->selectRecord($queryStr, array($pageId), $row);
		if ($ret){
			$retValue = $row['pg_default_sub_id'];
			if (isset($deviceType)) $deviceType = $row['pg_device_type'];
		}
		return $retValue;
	}
	/**
	 * デフォルトのページサブIDの更新
	 *
	 * @param string $pageId	ページID
	 * @param string $pageSubId	ページサブID
	 * @return bool				true=成功、false=失敗
	 */
	function updateDefaultPageSubId($pageId, $pageSubId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'UPDATE _page_id ';
		$queryStr .=   'SET ';
		$queryStr .=     'pg_default_sub_id = ? ';
		$queryStr .=   'WHERE pg_id = ? ';
		$queryStr .=     'AND pg_type = 0 ';		// ページID
		$queryStr .=     'AND pg_editable = true ';		// 編集可能項目のみ更新
		$this->execStatement($queryStr, array($pageSubId, $pageId));

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 指定のページがアクセス可能なページかチェック
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param bool   $pageVisible	ページ表示かどうか
	 * @param bool   $pageSubVisible	ページサブ表示かどうか
	 * @param bool   $isAdminMenu		管理メニューを使用するアクセスポイントかどうか
	 * @return bool					true=可能、false=不可
	 */
	function canAccessPage($pageId, $pageSubId, &$pageVisible, &$pageSubVisible, &$isAdminMenu)
	{
		// MySQLでは大文字小文字が区別されないのでBINARY演算子を追加(2011/12/22)
		$binaryCompStr = '';
		if ($this->getDbType() == M3_DB_TYPE_MYSQL) $binaryCompStr = 'BINARY ';		// MySQLの場合
		
		$queryStr  = 'SELECT t1.pg_visible AS page_visible, t2.pg_visible AS sub_visible, t1.pg_admin_menu AS point_admin_menu FROM _page_id AS t1 CROSS JOIN _page_id AS t2 ';
		$queryStr .=   'WHERE t1.pg_type = 0 AND t1.pg_id = ' . $binaryCompStr . '? AND t1.pg_active = true ';
		$queryStr .=     'AND t2.pg_type = 1 AND t2.pg_id = ' . $binaryCompStr . '? AND t2.pg_active = true ';
		$ret = $this->selectRecord($queryStr, array($pageId, $pageSubId), $row);
		if ($ret){
			$pageVisible = $row['page_visible'];
			$pageSubVisible = $row['sub_visible'];
			$isAdminMenu	= $row['point_admin_menu'];
		}
		return $ret;
	}
	/**
	 * サーバ接続アクセス可能な送信元IPアドレスかどうか
	 *
	 * @param string $ip	IPアドレス
	 * @return				true=存在する、false=存在しない
	 */
	function isExistsTenantServerIp($ip)
	{
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_deleted = false ';// 削除されていない
		$queryStr .=     'AND ts_enable_access = true ';	// アクセス可能
		$queryStr .=     'AND ts_ip = ?';
		return $this->isRecordExists($queryStr, array($ip));
	}
	/**
	 * ウィジェットリスト(メニューから選択可能なもの)取得
	 *
	 * @param int   $type			端末タイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param array $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAvailableWidgetList($type, &$rows)
	{
		$queryStr  = 'SELECT * FROM _widgets ';
		$queryStr .=   'WHERE wd_deleted = false ';// 削除されていない
		$queryStr .=     'AND wd_available = true ';		// メニューから選択可能なもの
		
		$params = array();
		switch ($type){
			case 0:		// PC用
			case 2:		// スマートフォン用
			default:
				$queryStr .=    'AND wd_mobile = false ';		// 携帯用以外
				$queryStr .=    'AND wd_device_type = ? '; $params[] = $type;
				break;
			case 1:		// 携帯用
				$queryStr .=    'AND wd_mobile = true ';		// 携帯用
				break;
		}
		$queryStr .=   'ORDER BY wd_id';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * ウィジェットタイプからウィジェットリストを取得
	 *
	 * @param string $widgetType	ウィジェットタイプ
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getWidgetListByType($widgetType, &$rows)
	{
		$queryStr  = 'SELECT * FROM _widgets ';
		$queryStr .=   'WHERE wd_deleted = false ';	// 削除されていない
		$queryStr .=     'AND wd_type = ? ';		// ウィジェットタイプ
		$queryStr .=   'ORDER BY wd_id';
		$retValue = $this->selectRecords($queryStr, array($widgetType), $rows);
		return $retValue;
	}
	/**
	 * 指定のウィジェットがアクセス可能なウィジェットかチェック
	 *
	 * アクセス可能なウィジェットは以下のすべての条件を満たすもの
	 * ・ウィジェット単体起動が許可されている
	 * ・ウィジェットが公開ページ上にあるか、または、ページ共通属性が設定されている
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param int    $setId			定義セットID
	 * @return bool					true=可能、false=不可
	 */
	function canAccessWidget($widgetId, $setId = 0)
	{
		$queryStr  = 'SELECT * ';
		$queryStr .= 'FROM (_page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false) ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'WHERE pd_widget_id = ? ';
		$queryStr .=   'AND pd_set_id = ? ';
		$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
//		$queryStr .=   'AND wd_enable_operation = true ';	// 単体実行可能
		$queryStr .=   'AND wd_active = true ';				// 一般ユーザが実行可能かどうか
		//$queryStr .=   'AND pg_active = true ';			// 公開中のページ
		$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// ページ共通ウィジェットか公開中のページ上のウィジェット
		$params = array($widgetId, $setId);
		$ret = $this->isRecordExists($queryStr, $params);
		return $ret;
	}
	/**
	 * 指定のウィジェットがページ上にあるかチェック
	 *
	 * @param string $pageId		ページID
	 * @param string $widgetId		ウィジェットID
	 * @param bool $activePageOnly	公開中のページのウィジェットに制限するかどうか。falseの場合はすべてのページのウィジェットを対象とする。
	 * @param int    $setId			定義セットID
	 * @return bool					true=ページ上にあり、false=ページ上になし
	 */
	function isWidgetOnPage($pageId, $widgetId, $activePageOnly=true, $setId = 0)
	{
		$queryStr  = 'SELECT * ';
		$queryStr .= 'FROM (_page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false) ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'WHERE pd_id = ? ';
		$queryStr .=   'AND pd_widget_id = ? ';
		$queryStr .=   'AND pd_set_id = ? ';
		$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
		if ($activePageOnly){
			$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// ページ共通ウィジェットか公開中のページ上のウィジェット
		}
		$params = array($pageId, $widgetId, $setId);
		$ret = $this->isRecordExists($queryStr, $params);
		return $ret;
	}
	/**
	 * テンプレート情報の取得
	 *
	 * @param string  $id			テンプレートID
	 * @return						true=正常、false=異常
	 */
	function getTemplate($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _templates ';
		$queryStr .=   'WHERE tm_id = ? ';
		$queryStr .=   'AND tm_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 1ページ上のすべての画面情報を取得
	 *
	 * @param string $pageId		ページID
	 * @param string $subpage    	ページサブID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getPageDefOnPage($pageId, $pageSubId, &$rows, $setId = 0)
	{
		$queryStr  = 'select * from _page_def left join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=   'where pd_visible = true ';		// ウィジェットを表示
		$queryStr .=     'and pd_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=     'and pd_set_id = ? ';
		$queryStr .=   'order by pd_position_id, pd_index';
		$retValue = $this->selectRecords($queryStr, array($pageId, $pageSubId, $setId), $rows);
		return $retValue;
	}
	/**
	 * 画面情報を取得
	 *
	 * @param string $pageId		ページID
	 * @param string $subpage    	ページサブID
	 * @param string $position		表示位置ID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @param bool   $visibleOnly	表示ウィジェットのみ取得
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getPageDef($pageId, $pageSubId, $position, &$rows, $setId = 0, $visibleOnly = false)
	{
		global $gEnvManager;
		
		$params = array();
		$initDt = $gEnvManager->getInitValueOfTimestamp();		// 日時初期化値
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_id = ? '; $params[] = $pageId;
		$queryStr .=     'AND (pd_sub_id = ? OR pd_sub_id = \'\') '; $params[] = $pageSubId;	// 空の場合は共通項目
		$queryStr .=     'AND pd_position_id = ? '; $params[] = $position;
		$queryStr .=     'AND pd_set_id = ? '; $params[] = $setId;
		// 表示ウィジェットに限定する場合は日付範囲も指定
		if ($visibleOnly){
			$queryStr .=     'AND pd_visible = true ';		// ウィジェットを表示
			
			// 公開期間を指定
			$queryStr .=    'AND (pd_active_start_dt = ? OR (pd_active_start_dt != ? AND pd_active_start_dt <= ?)) ';
			$queryStr .=    'AND (pd_active_end_dt = ? OR (pd_active_end_dt != ? AND pd_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}
		$queryStr .=   'ORDER BY pd_position_id, pd_index';
		$retValue = $this->selectRecords($queryStr, $params, $rows);
		return $retValue;
	}
	/**
	 * シリアル番号から画面情報を取得
	 *
	 * @param int $serial			定義シリアル番号
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getPageDefBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM _page_def ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$retValue = $this->selectRecord($queryStr, array($serial), $row);
		return $retValue;
	}
	/**
	 * 指定定義IDのウィジェットの参照数を取得
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param string $configId    	定義ID
	 * @param int    $setId			定義セットID
	 * @return int					参照数
	 */
	function getPageDefCount($widgetId, $configId, $setId = 0)
	{
		$queryStr  = 'select * from _page_def left join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=   'where pd_widget_id = ? ';		// ウィジェットID
		$queryStr .=     'and pd_config_id = ? ';
		$queryStr .=     'and pd_set_id = ? ';
		return $this->selectRecordCount($queryStr, array($widgetId, $configId, $setId));
	}
	/**
	 * 指定のメニューIDのウィジェットからの参照数を取得
	 *
	 * @param string $menuId    	メニューID
	 * @param int    $setId			定義セットID
	 * @return int					参照数
	 */
	function getMenuIdRefCount($menuId, $setId = 0)
	{
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_menu_id = ? ';		// メニューID
		$queryStr .=     'AND pd_set_id = ? ';
		return $this->selectRecordCount($queryStr, array($menuId, $setId));
	}
	/**
	 * 指定のページのウィジェット数を取得
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId    	ページサブID
	 * @param bool   $isLimited		共通属性ウィジェットを除くかどうか
	 * @param int    $setId			定義セットID
	 * @return int					参照数
	 */
	function getWidgetCountOnPage($pageId, $pageSubId, $isLimited = false, $setId = 0)
	{
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_id = ? ';
		if ($isLimited){
			$queryStr .=     'AND pd_sub_id = ? ';
		} else {
			$queryStr .=     'AND (pd_sub_id = ? OR pd_sub_id = \'\') ';	// 空の場合は共通項目
		}
		$queryStr .=     'AND pd_set_id = ? ';
		return $this->selectRecordCount($queryStr, array($pageId, $pageSubId, $setId));
	}
	/**
	 * ウィジェットIDとウィジェット定義IDで画面情報を取得
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param string $configId    	定義ID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getPageDefByWidgetConfigId($widgetId, $configId, &$rows, $setId = 0)
	{
		$queryStr  = 'select * from _page_def left join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=   'where pd_widget_id = ? ';		// ウィジェットID
		$queryStr .=     'and pd_config_id = ? ';
		$queryStr .=     'and pd_set_id = ? ';
		$ret = $this->selectRecords($queryStr, array($widgetId, $configId, $setId), $rows);
		return $ret;
	}
	/**
	 * ページID、ウィジェットIDで画面情報を取得
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId    	ページサブID
	 * @param string $widgetId			ウィジェットID
	 * @param array  $row			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getPageDefOnPageByWidgetId($pageId, $pageSubId, $widgetId, &$row, $setId = 0)
	{
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_id = ? ';
		$queryStr .=     'AND (pd_sub_id = ? OR pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=     'AND pd_widget_id = ? ';		// ウィジェットID
		$queryStr .=     'AND pd_set_id = ? ';
		$queryStr .=   'ORDER BY pd_position_id, pd_index';
		$retValue = $this->selectRecord($queryStr, array($pageId, $pageSubId, $widgetId, $setId), $row);
		return $retValue;
	}
	/**
	 * ウィジェットが指定のページID上にあるかどうか判断
	 *
	 * @param string $pageId			ページID
	 * @param string $subpage    		ページサブID
	 * @param string $widgetId			ウィジェットID
	 * @param int    $setId				定義セットID
	 * @return bool						true=ページ上にあり、false=ページ上になし
	 */
	function isExistsWidgetOnPage($pageId, $pageSubId, $widgetId, $setId = 0)
	{
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_id = ? ';
		$queryStr .=     'AND (pd_sub_id = ? OR pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=     'AND pd_widget_id = ? ';
		$queryStr .=     'AND pd_set_id = ? ';
		$params = array($pageId, $pageSubId, $widgetId, $setId);
		$ret = $this->isRecordExists($queryStr, $params);
		return $ret;
	}
	/**
	 * 指定したウィジェットの表示できるサブページIDを取得
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param string $pageId		ページID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getSubPageId($widgetId, $pageId, &$rows, $setId = 0)
	{	
		$queryStr  = 'SELECT DISTINCT pd_sub_id, pd_config_id, ';
		$queryStr .=   'CASE pd_sub_id ';
		$queryStr .=     'WHEN \'\' THEN -1 ';
		$queryStr .=     'ELSE pg_priority ';
		$queryStr .=   'END AS idx ';
		$queryStr .= 'FROM (_page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false) ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'WHERE pd_id = ? ';
		$queryStr .=   'AND pd_widget_id = ? ';
		$queryStr .=   'AND pd_set_id = ? ';
		$queryStr .=   'AND pg_active = true ';			// 公開中のページ
		$queryStr .= 'ORDER BY idx';
		$retValue = $this->selectRecords($queryStr, array($pageId, $widgetId, $setId), $rows);
		return $retValue;
	}
	/**
	 * 指定したコンテンツが表示可能なサブページIDを取得
	 *
	 * @param string $contentType	ウィジェットID
	 * @param string $pageId		ページID
	 * @return string				サブページID、該当なしのときは空
	 */
	function getSubPageIdWithContent($contentType, $pageId)
	{
		$retValue = '';
		$queryStr = 'SELECT * FROM _page_info ';
		$queryStr .=  'WHERE pn_deleted = false ';// 削除されていない
		$queryStr .=  'AND pn_id = ? ';
		$queryStr .=  'AND pn_content_type = ? ';
		$queryStr .=  'AND pn_language_id = ? ';		// 言語ID(2010/2/23追加)
		$queryStr .=  'ORDER BY pn_sub_id';
		$ret = $this->selectRecord($queryStr, array($pageId, $contentType, ''/*言語なし*/), $row);
		if ($ret) $retValue = $row['pn_sub_id'];
		return $retValue;
	}
	/**
	 * URLパラメータからサブページIDを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $urlParam		URLパラメータ
	 * @param array  $row			取得レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getSubPageIdByUrlContentParam($pageId, $urlParam, &$row, $setId = 0)
	{
		$queryStr  = 'SELECT DISTINCT pd_sub_id, ';
		$queryStr .=   'CASE pd_sub_id ';
		$queryStr .=     'WHEN \'\' THEN -1 ';
		$queryStr .=     'ELSE pg_priority ';
		$queryStr .=   'END AS idx ';
		$queryStr .= 'FROM _option_content_param LEFT JOIN _page_def ON oc_widget_id = pd_widget_id ';
		$queryStr .=   'LEFT JOIN _widgets ON oc_widget_id = wd_id AND wd_deleted = false ';		// ウィジェット情報
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'WHERE oc_page_id = ? ';
		$queryStr .=   'AND oc_id = ? ';
		$queryStr .=   'AND pd_set_id = ? ';
		$queryStr .=   'AND pg_active = true ';			// 公開中のページ
		$queryStr .= 'ORDER BY idx';
		$ret = $this->selectRecord($queryStr, array($pageId, $urlParam, $setId), $row);
		return $ret;
	}
	/**
	 * 指定のルーム用のウィジェット定義ID(所属グループID)を取得
	 *
	 * @param string	$roomId				ルームID
	 * @return int							定義ID
	 */
	function getWidgetConfigIdForRoom($roomId)
	{
		$retValue = 0;
		$queryStr = 'SELECT * FROM user_content_room ';
		$queryStr .=  'WHERE ur_deleted = false ';
		$queryStr .=  'AND ur_id = ? ';
		$ret = $this->selectRecord($queryStr, array($roomId), $row);
		if ($ret) $retValue = $row['ur_group_id'];
		return $retValue;
	}
	/**
	 * メニュー項目のURLからサブページIDを取得
	 *
	 * @param string $url			URL
	 * @param string $pageId		ページID
	 * @param string $contentType	コンテンツ種別
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getSubPageIdByMenuItemUrl($url, $pageId, $contentType, &$rows, $setId = 0)
	{
		$queryStr  = 'SELECT DISTINCT pd_sub_id, ';
		$queryStr .=   'CASE pn_content_type ';
		$queryStr .=     'WHEN \'' . $contentType . '\' THEN -1 ';
		$queryStr .=     'ELSE pg_priority ';
		$queryStr .=   'END AS idx ';
		$queryStr .= 'FROM (_page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false) ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .=   'LEFT JOIN _page_info ON pd_id = pn_id AND pd_sub_id = pn_sub_id AND pn_deleted = false AND pn_language_id = \'\' ';
		$queryStr .=   'LEFT JOIN _menu_def ON pd_menu_id = md_menu_id ';// メニューID
		$queryStr .= 'WHERE pd_id = ? ';
		$queryStr .=   'AND pd_sub_id != ? ';			// ローカルウィジェット
		$queryStr .=   'AND pd_set_id = ? ';
		$queryStr .=   'AND wd_type = ? ';		// メニュータイプ
		$queryStr .=   'AND md_link_url = ? ';
		$queryStr .=   'AND pg_active = true ';			// 公開中のページ
		//$queryStr .= 'ORDER BY pg_priority';
		$queryStr .= 'ORDER BY idx';
		$retValue = $this->selectRecords($queryStr, array($pageId, '', $setId, 'menu', $url), $rows);
		return $retValue;
	}
	/**
	 * ページ情報を取得(言語非依存データ)
	 *
	 * @return array				取得レコード, 取得できないときは空配列が返る
	 */
	function getPageInfoRecords()
	{
		$queryStr = 'SELECT * FROM _page_info ';
		$queryStr .=  'WHERE pn_deleted = false ';// 削除されていない
		$queryStr .=    'AND pn_language_id = ? ';		// 言語ID(2010/2/23追加)
		$queryStr .=  'ORDER BY pn_id, pn_sub_id';
		$retValue = $this->selectRecords($queryStr, array(''/*言語なし*/), $rows);
		if ($retValue){
			return $rows;
		} else {
			return array();
		}
	}
	/**
	 * 言語からページ情報を取得
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $langId		言語ID
	 * @param array  $row			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getPageInfo($pageId, $pageSubId, $langId, &$row)
	{
		// 言語指定で取得
		$queryStr = 'SELECT * FROM _page_info ';
		$queryStr .=  'WHERE pn_deleted = false ';// 削除されていない
		$queryStr .=    'AND pn_id = ? ';		// ページID
		$queryStr .=    'AND pn_sub_id = ? ';		// ページサブID
		$queryStr .=    'AND pn_language_id = ? ';		// 言語ID(2010/2/23追加)
		$ret = $this->selectRecord($queryStr, array($pageId, $pageSubId, $langId), $row);
		return $ret;
	}
	/**
	 * ウィジェットタイプからウィジェットIDを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId    	ページサブID
	 * @param string $widgetType    ウィジェットタイプ
	 * @param int    $setId			定義セットID
	 * @param array  $row			取得レコード
	 * @return string 				ウィジェットID(見つからないときは空文字列を返す)
	 */
	function getWidgetIdByType($pageId, $pageSubId, $widgetType, $setId = 0, &$row = array())
	{
		$retValue = '';
		$queryStr  = 'select * from _page_def left join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=   'where pd_visible = true ';		// ウィジェットを表示
		$queryStr .=     'and pd_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=     'and pd_set_id = ? ';
		$queryStr .=     'and wd_type = ? ';
		$queryStr .=   'order by pd_serial';
		$ret = $this->selectRecord($queryStr, array($pageId, $pageSubId, $setId, $widgetType), $row);
		if ($ret) $retValue = $row['wd_id'];
		return $retValue;
	}
	/**
	 * コンテンツタイプからウィジェットIDを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId    	ページサブID
	 * @param string $contentType    コンテンツタイプ
	 * @param int    $setId			定義セットID
	 * @param array  $row			取得レコード
	 * @return string 				ウィジェットID(見つからないときは空文字列を返す)
	 */
	function getWidgetIdByContentType($pageId, $pageSubId, $contentType, $setId = 0, &$row = array())
	{
		$retValue = '';
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_visible = true ';		// ウィジェットを表示
		$queryStr .=     'AND pd_id = ? ';
		$queryStr .=     'AND (pd_sub_id = ? OR pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=     'AND pd_set_id = ? ';
		$queryStr .=     'AND wd_content_type = ? ';
		$queryStr .=   'ORDER BY pd_serial';
		$ret = $this->selectRecord($queryStr, array($pageId, $pageSubId, $setId, $contentType), $row);
		if ($ret) $retValue = $row['wd_id'];
		return $retValue;
	}
	/**
	 * ウィジェットタイプからメインウィジェットのウィジェットIDを取得
	 *
	 * @param string $widgetType    ウィジェットタイプ
	 * @param int    $setId			定義セットID
	 * @return string 				ウィジェットID(見つからないときは空文字列を返す)
	 */
	function getActiveMainWidgetIdByType($widgetType, $setId = 0)
	{
		$retValue = '';
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .=   'WHERE pd_visible = true ';		// ウィジェットを表示
		$queryStr .=     'AND pd_set_id = ? ';
		$queryStr .=     'AND wd_type = ? ';
		$queryStr .= 'ORDER BY pg_priority';
		$ret = $this->selectRecord($queryStr, array($setId, $widgetType), $row);
		if ($ret) $retValue = $row['wd_id'];
		return $retValue;
	}
	/**
	 * 互換性のあるウィジェットのIDを取得
	 *
	 * @param string $widgetId	ウィジェットID
	 * @return string 			互換ウィジェットID(見つからないときは空文字列を返す)
	 */
	function getCompatibleWidgetId($widgetId)
	{
		$retValue = '';
		$queryStr = 'SELECT * FROM _widgets ';
		$queryStr .=  'WHERE wd_deleted = false ';// 削除されていない
		$queryStr .=  'AND wd_compatible_id = ? ';
		$queryStr .=  'ORDER BY wd_id DESC';
		$ret = $this->selectRecord($queryStr, array($widgetId), $row);
		if ($ret) $retValue = $row['wd_id'];
		return $retValue;
	}
	/**
	 * スクリプトライブラリが必要なウィジェットIDを取得
	 *
	 * @param string $pageid		ページID
	 * @param string $subpageid    	ページサブID
	 * @param array  $rows			レコード
	 * @param int    $setId				定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getWidgetsIdWithLib($pageid, $subpageid, &$rows, $setId = 0)
	{
		$queryStr = 'select distinct wd_id from _page_def join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=  'where pd_visible = true ';
		$queryStr .=    'and pd_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=    'and pd_set_id = ? ';
		$queryStr .=    'and wd_add_script_lib != \'\' ';
		$queryStr .=  'order by wd_id';
		$ret = $this->selectRecords($queryStr, array($pageid, $subpageid, $setId), $rows);
		if ($ret){
			$idArray = array();
			for ($i = 0; $i < count($rows); $i++){
				$idArray[] = '\'' . $rows[$i]['wd_id'] . '\'';
			}
			if (count($idArray) > 0){
				$idStr = implode(',', $idArray);
				$queryStr = 'SELECT * from _widgets ';
				$queryStr .=  'WHERE wd_deleted = false ';// 削除されていない
				$queryStr .=  'AND wd_id in (' . $idStr . ') ';
				$ret = $this->selectRecords($queryStr, array(), $rows);
			} else {
				$ret = false;
			}
		}
		return $ret;
	}
	/**
	 * スクリプトファイルが必要なウィジェットIDを取得
	 *
	 * @param string $pageid		ページID
	 * @param string $subpageid    	ページサブID
	 * @param array  $rows			レコード
	 * @param int    $setId				定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getWidgetsIdWithScript($pageid, $subpageid, &$rows, $setId = 0)
	{
		$queryStr = 'select distinct wd_id from _page_def join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=  'where pd_visible = true ';
		$queryStr .=    'and pd_id = ? ';
		//$queryStr .=    'and pd_sub_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=    'and pd_set_id = ? ';
		$queryStr .=    'and wd_read_scripts = true ';
		$queryStr .=  'order by wd_id';
		
		$retValue = $this->selectRecords($queryStr, array($pageid, $subpageid, $setId), $rows);
		return $retValue;
	}
	/**
	 * CSSファイルが必要なウィジェットIDを取得
	 *
	 * @param string $pageid		ページID
	 * @param string $subpageid    	ページサブID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getWidgetsIdWithCss($pageid, $subpageid, &$rows, $setId = 0)
	{
		$queryStr = 'select distinct wd_id from _page_def join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=  'where pd_visible = true ';
		$queryStr .=    'and pd_id = ? ';
		//$queryStr .=    'and pd_sub_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=    'and pd_set_id = ? ';
		$queryStr .=    'and wd_read_css = true ';
		$queryStr .=  'order by wd_id';
		
		$retValue = $this->selectRecords($queryStr, array($pageid, $subpageid, $setId), $rows);
		return $retValue;
	}
	/**
	 * 共通Ajaxライブラリを使用しているウィジェットがあるかどうか
	 *
	 * @param string $pageid		ページID
	 * @param string $subpageid    	ページサブID
	 * @param array  $rows			レコード
	 * @param int    $setId			定義セットID
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function isExistsWidgetWithAjax($pageid, $subpageid, $setId = 0)
	{
		$queryStr = 'select distinct wd_id from _page_def join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=  'where pd_visible = true ';
		$queryStr .=    'and pd_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=    'and pd_set_id = ? ';
		$queryStr .=    'and wd_use_ajax = true ';		// Ajaxを使用
		$queryStr .=  'order by wd_id';
		return $this->isRecordExists($queryStr, array($pageid, $subpageid, $setId));
	}
	/**
	 * ウィジェット情報取得
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param array $row			取得した行
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getWidgetInfo($widgetId, &$row)
	{
		$queryStr = 'SELECT * from _widgets ';
		$queryStr .=  'WHERE wd_deleted = false ';// 削除されていない
		$queryStr .=  'AND wd_id = ?';
		return $this->selectRecord($queryStr, array($widgetId), $row);
	}
	/**
	 * ウィジェットの定義IDを更新する
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param int    $serial		シリアル番号
	 * @param int    $configId		定義ID
	 * @param int    $title			定義名
	 * @param string 	$menuId		メニューID(メニューの場合)
	 * @return bool				true=成功、false=失敗
	 */
	function updateWidgetConfigId($widgetId, $serial, $configId, $title = '', $menuId = '')
	{
		global $gEnvManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		// 現在の値取得
		$queryStr  = 'SELECT * FROM _page_def ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if (!$ret){		// 登録レコードがないとき
			$this->endTransaction();
			return false;
		}
		if ($row['pd_widget_id'] != $widgetId){		// ウィジェットIDが異なるとき
			$this->endTransaction();
			return false;
		}
		// 既存項目を更新
		$queryStr  = 'UPDATE _page_def ';
		$queryStr .=   'SET ';
		$queryStr .=     'pd_config_id = ?, ';
		$queryStr .=     'pd_config_name = ?, ';
		$queryStr .=     'pd_menu_id = ?, ';
		$queryStr .=     'pd_update_user_id = ?, ';
		$queryStr .=     'pd_update_dt = ? ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$this->execStatement($queryStr, array($configId, $title, $menuId, $userId, $now, $serial));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットを移動する
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $position		ポジション
	 * @param int $serial			シリアル番号
	 * @param int    $posIndex		ポジション内の位置
	 * @param int    $setId			定義セットID
	 * @return bool					true=成功、false=失敗
	 */
	function moveWidget($pageId, $pageSubId, $position, $serial, $posIndex, $setId = 0)
	{
		global $gEnvManager;

		// 指定ポジションのウィジェットをすべて取得
		$ret = $this->getPageDef($pageId, $pageSubId, $position, $rows, $setId);

		// 移動するウィジェットを取得
		$queryStr  = 'SELECT * FROM _page_def ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if (!$ret) return false;
		
		$inBlock = false;	// 同じポジション内の移動かどうか
		if ($row['pd_position_id'] == $position) $inBlock = true;
		
		// 指定インデックスのエラーチェック
		$widgetCount = count($rows);
		if ($inBlock){
			if ($posIndex < 0 || $widgetCount -1 < $posIndex) return false;
		} else {
			if ($posIndex < 0 || $widgetCount < $posIndex) return false;
		}
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		// ウィジェットを移動し、表示順を再設定
		// 新規追加ウィジェットの表示順を決定
		if ($inBlock){			// ブロック内の移動のとき
			// シリアル番号を並べなおす
			$widgetArray = array();
			for ($i = 0; $i < $widgetCount; $i++){
				if ($rows[$i]['pd_serial'] != $serial) $widgetArray[] = $rows[$i]['pd_serial'];
			}
			array_splice($widgetArray, $posIndex, 0, $serial);

			for ($i = 0; $i < $widgetCount; $i++){
				$serialNo = $widgetArray[$i];
				$index = $rows[$i]['pd_index'];
			
				// 既存項目を更新
				$queryStr  = 'UPDATE _page_def ';
				$queryStr .=   'SET ';
				$queryStr .=     'pd_index = ?, ';
				$queryStr .=     'pd_update_user_id = ?, ';
				$queryStr .=     'pd_update_dt = ? ';
				$queryStr .=   'WHERE pd_serial = ? ';
				$this->execStatement($queryStr, array($index, $userId, $now, $serialNo));
			}
		} else {
			if ($widgetCount == 0){		// ポジションにウィジェットがないとき
				$newIndex = self::INDEX_ADD_VALUE;
			} else {
				if ($posIndex == 0){		// すべてのウィジェットよりも先頭のとき
					$newIndex = $rows[$posIndex]['pd_index'] - self::INDEX_ADD_VALUE;
					if ($newIndex < 1){			// 表示順振りなおし
						for ($i = 0; $i < $widgetCount; $i++){
							$serialNo = $rows[$i]['pd_serial'];
							$index = $i * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 3;			// 5とばしで設定
						
							// 既存項目を更新
							$queryStr  = 'UPDATE _page_def ';
							$queryStr .=   'SET ';
							$queryStr .=     'pd_index = ?, ';
							$queryStr .=     'pd_update_user_id = ?, ';
							$queryStr .=     'pd_update_dt = ? ';
							$queryStr .=   'WHERE pd_serial = ? ';
							$this->execStatement($queryStr, array($index, $userId, $now, $serialNo));
						}
						$newIndex = self::INDEX_ADD_VALUE * 2;
					}
				} else if ($posIndex == $widgetCount){// すべてのウィジェットの最後のとき
					$newIndex = $rows[$posIndex -1]['pd_index'] + self::INDEX_ADD_VALUE;
				} else {
					$foreIndex = $rows[$posIndex -1]['pd_index'];
					$nextIndex = $rows[$posIndex]['pd_index'];
				
					// 間隔がつまっているときは、表示順を振りなおす
					if ($nextIndex - $foreIndex > 1){
						$newIndex = $foreIndex + intval(($nextIndex - $foreIndex) / 2);
					} else {
						for ($i = 0; $i < $widgetCount; $i++){
							$serialNo = $rows[$i]['pd_serial'];
						
							if ($i < $posIndex){
								$index = $i * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 2;			// 5とばしで設定
							} else {
								$index = $i * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 3;			// 5とばしで設定
							}
						
							// 既存項目を更新
							$queryStr  = 'UPDATE _page_def ';
							$queryStr .=   'SET ';
							$queryStr .=     'pd_index = ?, ';
							$queryStr .=     'pd_update_user_id = ?, ';
							$queryStr .=     'pd_update_dt = ? ';
							$queryStr .=   'WHERE pd_serial = ? ';
							$this->execStatement($queryStr, array($index, $userId, $now, $serialNo));
						}
						$newIndex = $posIndex * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 2;
					}
				}
			}
			// 移動した項目を更新
			$queryStr  = 'UPDATE _page_def ';
			$queryStr .=   'SET ';
			$queryStr .=     'pd_position_id = ?, ';
			$queryStr .=     'pd_index = ?, ';
			$queryStr .=     'pd_update_user_id = ?, ';
			$queryStr .=     'pd_update_dt = ? ';
			$queryStr .=   'WHERE pd_serial = ? ';
			$this->execStatement($queryStr, array($position, $newIndex, $userId, $now, $serial));
		}
										
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットを指定ポジションに追加する
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $position		ポジション
	 * @param string $widgetId		追加ウィジェットID
	 * @param int $posIndex			ポジション内の位置
	 * @param int    $setId			定義セットID
	 * @return bool				true=成功、false=失敗
	 */
	function addWidget($pageId, $pageSubId, $position, $widgetId, $posIndex, $setId = 0)
	{
		global $gEnvManager;

		// 引数エラーチェック
		if (empty($widgetId)) return false;
		
		// 指定ポジションのウィジェットをすべて取得
		$ret = $this->getPageDef($pageId, $pageSubId, $position, $rows, $setId);
		if (!$ret) $rows = array();
	
		// 指定インデックスのエラーチェック
		$widgetCount = count($rows);
		if ($posIndex < 0 || $widgetCount < $posIndex) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		// 新規追加ウィジェットの表示順を決定
		if ($widgetCount == 0){		// ポジションにウィジェットがないとき
			$newIndex = self::INDEX_ADD_VALUE;
		} else {
			if ($posIndex == 0){		// すべてのウィジェットよりも先頭のとき
				$newIndex = $rows[$posIndex]['pd_index'] - self::INDEX_ADD_VALUE;
				if ($newIndex < 1){			// 表示順振りなおし
					for ($i = 0; $i < $widgetCount; $i++){
						$serialNo = $rows[$i]['pd_serial'];
						$index = $i * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 3;			// 5とばしで設定
						
						// 既存項目を更新
						$queryStr  = 'UPDATE _page_def ';
						$queryStr .=   'SET ';
						$queryStr .=     'pd_index = ?, ';
						$queryStr .=     'pd_update_user_id = ?, ';
						$queryStr .=     'pd_update_dt = ? ';
						$queryStr .=   'WHERE pd_serial = ? ';
						$this->execStatement($queryStr, array($index, $userId, $now, $serialNo));
					}
					$newIndex = self::INDEX_ADD_VALUE * 2;
				}
			} else if ($posIndex == $widgetCount){// すべてのウィジェットの最後のとき
				$newIndex = $rows[$posIndex -1]['pd_index'] + self::INDEX_ADD_VALUE;
			} else {
				$foreIndex = $rows[$posIndex -1]['pd_index'];
				$nextIndex = $rows[$posIndex]['pd_index'];
				
				// 間隔がつまっているときは、表示順を振りなおす
				if ($nextIndex - $foreIndex > 1){
					$newIndex = $foreIndex + intval(($nextIndex - $foreIndex) / 2);
				} else {
					for ($i = 0; $i < $widgetCount; $i++){
						$serialNo = $rows[$i]['pd_serial'];
						
						if ($i < $posIndex){
							$index = $i * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 2;			// 5とばしで設定
						} else {
							$index = $i * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 3;			// 5とばしで設定
						}
						
						// 既存項目を更新
						$queryStr  = 'UPDATE _page_def ';
						$queryStr .=   'SET ';
						$queryStr .=     'pd_index = ?, ';
						$queryStr .=     'pd_update_user_id = ?, ';
						$queryStr .=     'pd_update_dt = ? ';
						$queryStr .=   'WHERE pd_serial = ? ';
						$this->execStatement($queryStr, array($index, $userId, $now, $serialNo));
					}
					$newIndex = $posIndex * self::INDEX_ADD_VALUE + self::INDEX_ADD_VALUE * 2;
				}
			}
		}
		// 新規データを追加
		$configId = 0;
		$suffix = '';
		$style = '';
		$visible = 1;
		$editable = 1;
		$queryStr  = 'INSERT INTO _page_def (';
		$queryStr .=   'pd_id, ';
		$queryStr .=   'pd_sub_id, ';
		$queryStr .=   'pd_set_id, ';
		$queryStr .=   'pd_position_id, ';
		$queryStr .=   'pd_index, ';
		$queryStr .=   'pd_widget_id, ';
		$queryStr .=   'pd_config_id, ';
		$queryStr .=   'pd_suffix, ';
		$queryStr .=   'pd_style, ';
		$queryStr .=   'pd_visible, ';
		$queryStr .=   'pd_editable, ';
		$queryStr .=   'pd_update_user_id, ';
		$queryStr .=   'pd_update_dt) ';
		$queryStr .= 'VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?)';
		$this->execStatement($queryStr, array($pageId, $pageSubId, $setId, $position, $newIndex, 
							$widgetId, $configId, $suffix, $style, $visible, $editable, $userId, $now));
								
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットを削除する
	 *
	 * @param int $serial		シリアル番号
	 * @return bool				true=成功、false=失敗
	 */
	function deleteWidget($serial)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'DELETE FROM _page_def WHERE pd_serial = ?';
		$this->execStatement($queryStr, array($serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットの共通属性を変更
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param int $serial		シリアル番号
	 * @param int    $shared	共通属性
	 * @return bool				true=成功、false=失敗
	 */
	function toggleSharedWidget($pageId, $pageSubId, $serial, $shared)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 現在の値取得
		$queryStr  = 'SELECT * FROM _page_def ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			if (empty($shared)){		// 単独ウィジェットのとき
				$newPageSubId = $pageSubId;
			} else {
				$newPageSubId = '';
			}
			
			// 既存項目を更新
			$queryStr  = 'UPDATE _page_def ';
			$queryStr .=   'SET ';
			$queryStr .=     'pd_sub_id = ?, ';
			$queryStr .=     'pd_update_user_id = ?, ';
			$queryStr .=     'pd_update_dt = ? ';
			$queryStr .=   'WHERE pd_serial = ? ';
			$this->execStatement($queryStr, array($newPageSubId, $userId, $now, $serial));
		} else {
			$this->endTransaction();
			return false;
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * インナーウィジェットリスト取得
	 *
	 * @param  string $widgetId	ウィジェットID
	 * @param  string $type			インナーウィジェットのタイプ
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllIWidgetListByType($widgetId, $type, $callback)
	{
		$queryStr = 'SELECT * FROM _iwidgets ';
		$queryStr .=  'WHERE iw_deleted = false ';// 削除されていない
		$queryStr .=  'AND (iw_widget_id = ? OR iw_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=  'AND iw_type = ? ';
		$queryStr .=  'ORDER BY iw_id';
		$this->selectLoop($queryStr, array($widgetId, $type), $callback);
	}
	/**
	 * インナーウィジェット情報取得
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param string $id			インナーウィジェットID
	 * @param array $row			取得した行
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getIWidgetInfo($widgetId, $id, &$row)
	{
		$queryStr = 'SELECT * from _iwidgets ';
		$queryStr .=  'WHERE iw_deleted = false ';// 削除されていない
		$queryStr .=  'AND iw_widget_id = ? ';
		$queryStr .=  'AND iw_id = ?';
		return $this->selectRecord($queryStr, array($widgetId, $id), $row);
	}
	/**
	 * セッションデータ取得
	 *
	 * @param  string $id	セッションID
	 * @return string		セッションデータ
	 */
	function readSession($id)
	{
		$sql = 'SELECT ss_data FROM _session WHERE ss_id = ?';
		$params = array($id); 
		$this->selectRecord($sql, $params, $row);
		return $row['ss_data'];
    }
	/**
	 * セッションデータ書き込み
	 *
	 * @param  string $id		セッションID
	 * @param  string $sessData	セッションデータ
	 * @return bool				true=成功、false=失敗
	 */
	function writeSession($id, $sessData)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// レコードが存在するときは、更新
        $sql="SELECT ss_id FROM _session WHERE ss_id = ?";
        $params = array($id);
        $ret = $this->isRecordExists($sql, $params);
		if ($ret){
			$sql = "UPDATE _session SET ss_data = ?, ss_update_dt = now() WHERE ss_id = ?";
			$params = array($sessData, $id);
			$this->execStatement($sql, $params);
		} else {
			$sql = "INSERT INTO _session (ss_id, ss_data, ss_update_dt) VALUES (?, ?, now())";
			$params = array($id, $sessData);
			$this->execStatement($sql, $params);
		}
		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * セッションレコード削除
	 *
	 * @param  string $id	セッションID
	 * @return bool			削除の結果
	 */
    function destroySession($id)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$sql = "DELETE FROM _session WHERE ss_id = ?";
		$params = array($id);
		$this->execStatement($sql, $params);
		
		// トランザクション終了
		return $this->endTransaction();
    }
	/**
	 * 一定期間経過しているセッションレコード削除
	 *
	 * @param  int $maxlifetime	削除までの期間
	 * @return bool				削除の結果
	 */
	function gcSession($maxlifetime)
	{
		global $gLogManager;
		$gLogManager->debug(__METHOD__, 'セッションガーベージコレクション開始');// debugログ出力

		// トランザクション開始
		$this->startTransaction();

		if ($this->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$sql = "DELETE FROM _session WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(ss_update_dt) > ?";
			$params = array($maxlifetime);
		} else if ($this->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			//$sql = "DELETE FROM _session WHERE 'now'::timestamp - ss_update_dt > '? seconds'";
			$sql = "DELETE FROM _session WHERE 'now'::timestamp - ss_update_dt > '" . $maxlifetime . " seconds'";
			$params = array();
		}
		$this->execStatement($sql, $params);
		
		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * ログインユーザ情報取得
	 *
	 * @param string 	$account	ユーザアカウント
	 * @param array  	$row		取得レコード
	 * @param string	$availableUserOnly		有効なユーザのみ取得(ログイン可,承認済み,有効期間内)
	 * @return bool				true=正常取得、false=取得できず
	 */
	function getLoginUserRecord($account, &$row, $availableUserOnly = false)
	{
		global $gEnvManager;
		
		$params = array();
			
		// MySQLでは大文字小文字が区別されないのでBINARY演算子を追加(2011/12/22)
		$binaryCompStr = '';
		if ($this->getDbType() == M3_DB_TYPE_MYSQL) $binaryCompStr = 'BINARY ';		// MySQLの場合
		
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_account = ' . $binaryCompStr . ' ? '; $params[] = $account;
		$queryStr .=     'AND lu_deleted = false ';
		if ($availableUserOnly){
			$now = date("Y/m/d H:i:s");	// 現在日時
			$initDt = $gEnvManager->getInitValueOfTimestamp();
		
			$queryStr .=     'AND lu_user_type >= 0 ';		// 承認済み
			$queryStr .=     'AND lu_enable_login = true ';
			
			// 公開期間
			$queryStr .=    'AND (lu_active_start_dt = ? OR (lu_active_start_dt != ? AND lu_active_start_dt <= ?)) ';
			$queryStr .=    'AND (lu_active_end_dt = ? OR (lu_active_end_dt != ? AND lu_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}
		$ret = $this->selectRecord($queryStr, $params, $row);
		return $ret;
	}
	/**
	 * ユーザアカウントが存在するかチェック
	 *
	 * @param string $account	アカウント
	 * @return					true=存在する、false=存在しない
	 */
	function isExistsAccount($account)
	{
		$queryStr = 'SELECT * from _login_user ';
		$queryStr .=  'WHERE lu_account = ? ';
		$queryStr .=    'AND lu_deleted = false';
		return $this->isRecordExists($queryStr, array($account));
	}
	/**
	 * ログインユーザ情報取得をIDで取得
	 *
	 * @param int	$id						ユーザID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getLoginUserRecordById($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _login_user LEFT JOIN _login_user_info ON lu_id = li_id AND li_deleted = false ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=     'AND lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * ログインユーザ情報をIDで削除
	 *
	 * @param int	$id						ユーザID
	 * @return bool							取得 = true, 取得なし= false
	 */
	function delLoginUser($id)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のIDのレコードが削除状態でないかチェック
		$queryStr  = 'select * from _login_user ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=    'and lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			$this->endTransaction();
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['lu_serial']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ログインユーザのアクセス可能機能を削除
	 *
	 * @param int	$id						ユーザID
	 * @param string	$accessType			削除する機能
	 * @param bool		$deleteUser			アクセス可能機能がすべて削除になったとき、ユーザを削除するかどうか
	 * @return bool							取得 = true, 取得なし= false
	 */
	function releaseLoginUser($id, $accessType, $deleteUser = false)
	{
		global $gEnvManager;
		
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のIDのレコードが削除状態でないかチェック
		$queryStr  = 'select * from _login_user ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=    'and lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			if ($startTran) $this->endTransaction();
			return false;
		}
		
		// 機能のアサイン状況を取得
		$replaceStr = $accessType . ',';
		$assign = $row['lu_assign'];
		$pos = strpos($assign, $replaceStr);
		if ($pos === false){// 見つからないとき
			if ($startTran) $this->endTransaction();
			return false;
		} else {
			$assign = str_replace($replaceStr, '', $assign);
		}

		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$ret = $this->execStatement($queryStr, array($userId, $now, $row['lu_serial']));
		
		if (!$deleteUser || !empty($assign)){		// ユーザ削除しないとき
			// 新規レコード追加
			$queryStr  = 'INSERT INTO _login_user (';
			$queryStr .=   'lu_id, ';
			$queryStr .=   'lu_history_index, ';
			$queryStr .=   'lu_name, ';
			$queryStr .=   'lu_account, ';
			$queryStr .=   'lu_password, ';
			$queryStr .=   'lu_user_type, ';
			$queryStr .=   'lu_assign, ';
			$queryStr .=   'lu_widget_id, ';
			$queryStr .=   'lu_enable_login, ';
			$queryStr .=   'lu_active_start_dt, ';
			$queryStr .=   'lu_active_end_dt, ';
			$queryStr .=   'lu_create_user_id, ';
			$queryStr .=   'lu_create_dt ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?) ';
			$ret = $this->execStatement($queryStr, array($row['lu_id'], $row['lu_history_index'] + 1, $row['lu_name'], $row['lu_account'], $row['lu_password'], 
														$row['lu_user_type'], $assign, $row['lu_widget_id'], $row['lu_enable_login'], $row['lu_active_start_dt'], $row['lu_active_end_dt'], $userId, $now));
		}
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 管理者権限があるかどうかをIDで取得
	 *
	 * @param int	$id						ユーザID
	 * @return bool							管理権限あり=true, 管理権限なし=false
	 */
	function isSystemAdmin($userId)
	{
		$ret = $this->getLoginUserRecordById($userId, $row);
		if ($ret && $row['lu_user_type'] >= UserInfo::USER_TYPE_SYS_ADMIN){		// 管理者権限ありのとき
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 承認済みユーザかどうかを判断
	 *
	 * @param int	$id						ユーザID
	 * @return bool							承認済みユーザ=true, 未承認ユーザ=false
	 */
	function isAuthenticatedUser($userId)
	{
		$ret = $this->getLoginUserRecordById($userId, $row);
		if ($ret && $row['lu_user_type'] >= UserInfo::USER_TYPE_TMP){		// 仮ユーザ以上
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 新規ユーザの追加
	 *
	 * @param string  $name			名前
	 * @param string  $account		アカウント
	 * @param string  $password		パスワード
	 * @param int     $userType		ユーザ種別
	 * @param bool    $canLogin		ログインできるかどうか
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param int     $newSerial	新規シリアル番号
	 * @param string  $adminWidget	管理可能ウィジェット(システム運用者)
	 * @param string  $userTypeOption	ユーザタイプオプション
	 * @param array   $groupArray	ユーザグループ
	 * @param array   $otherParams	その他のフィールド値
	 * @return						true=成功、false=失敗
	 */
	function addNewLoginUser($name, $account, $password, $userType, $canLogin, $startDt, $endDt, &$newSerial,
								$adminWidget = '', $userTypeOption = '', $groupArray = null, $otherParams = null)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		$widgetId = $gEnvManager->getCurrentWidgetId();		// 現在のウィジェット
		
		// トランザクション開始
		$this->startTransaction();
		
		// 新規IDを作成
		$newId = 1;
		$queryStr = 'SELECT MAX(lu_id) AS ms FROM _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newId = $row['ms'] + 1;
		
		// 新規レコードを追加
		$params = array();
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, '; $params[] = $newId;
		$queryStr .=   'lu_name, '; $params[] = $name;
		$queryStr .=   'lu_account, '; $params[] = $account;
		$queryStr .=   'lu_password, '; $params[] = $password;
		$queryStr .=   'lu_user_type, '; $params[] = $userType;
		$queryStr .=   'lu_enable_login, '; $params[] = intval($canLogin);
		if (!empty($startDt)){
			$queryStr .=   'lu_active_start_dt, '; $params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=   'lu_active_end_dt, '; $params[] = $endDt;
		}
		$queryStr .=   'lu_widget_id, '; $params[] = $widgetId;
		$queryStr .=   'lu_regist_dt, '; $params[] = $now;
		$queryStr .=   'lu_admin_widget, '; $params[] = $adminWidget;
		$queryStr .=   'lu_user_type_option, '; $params[] = $userTypeOption;
		$queryStr .=   'lu_create_user_id, '; $params[] = $userId;
		$queryStr .=   'lu_create_dt'; $params[] = $now;
		
		// その他のフィールド値を設定
		$otherValueStr = '';
		if (!empty($otherParams)){
			$keys = array_keys($otherParams);// キーを取得
			for ($i = 0; $i < count($keys); $i++){
				$fieldName = $keys[$i];
				$fieldValue = $otherParams[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$queryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
		
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		if (!empty($startDt)) $queryStr .=   '?, ';
		if (!empty($endDt)) $queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?' . $otherValueStr . ') ';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(lu_serial) AS ns FROM _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザの更新
	 *
	 * @param int $serial			シリアル番号
	 * @param string  $name			ユーザ名
	 * @param string  $account		アカウント
	 * @param string  $password		パスワード(空のときは更新しない)
	 * @param int     $userType		ユーザ種別
	 * @param string $canLogin		ログイン可能かどうか
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @param int     $newSerial	新規シリアル番号
	 * @param string  $adminWidget	管理可能ウィジェット(システム運用者)
	 * @param string  $userTypeOption	ユーザタイプオプション
	 * @param array   $groupArray	ユーザグループ
	 * @param array   $otherParams	その他のフィールド値
	 * @return						true=成功、false=失敗
	 */
	function updateLoginUser($serial, $name, $account, $password, $userType, $canLogin, $startDt, $endDt, &$newSerial,
									$adminWidget = null, $userTypeOption = null, $groupArray = null, $otherParams = null)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['lu_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['lu_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// パスワードが設定されているときは更新
		$pwd = $row['lu_password'];
		if (!empty($password)) $pwd = $password;
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));

		$saveFields = array();	// 値を引き継ぐフィールド名
		$saveFields[] = 'lu_id';
		$saveFields[] = 'lu_assign';
		$saveFields[] = 'lu_user_status';
		$saveFields[] = 'lu_avatar';
		$saveFields[] = 'lu_tmp_password';
		$saveFields[] = 'lu_tmp_pwd_dt';
		$saveFields[] = 'lu_widget_id';
		$saveFields[] = 'lu_regist_dt';
		$saveFields[] = 'lu_email';
		$saveFields[] = 'lu_skype_account';
		
		// 新規レコード追加
		$params = array();
		$queryStr  = 'INSERT INTO _login_user (';
			
		$queryStr .=   'lu_history_index, '; $params[] = $historyIndex;
		$queryStr .=   'lu_name, '; $params[] = $name;
		$queryStr .=   'lu_account, '; $params[] = $account;
		$queryStr .=   'lu_password, '; $params[] = $pwd;
		$queryStr .=   'lu_user_type, '; $params[] = $userType;
		$queryStr .=   'lu_enable_login, '; $params[] = $canLogin;
		if (!empty($startDt)){
			$queryStr .=   'lu_active_start_dt, '; $params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=   'lu_active_end_dt, '; $params[] = $endDt;
		}
		if (is_null($adminWidget)){
			$queryStr .=   'lu_admin_widget, '; $params[] = $row['lu_admin_widget'];
		} else {
			$queryStr .=   'lu_admin_widget, '; $params[] = $adminWidget;
		}
		if (is_null($userTypeOption)){
			$queryStr .=   'lu_user_type_option, '; $params[] = $row['lu_user_type_option'];
		} else {
			$queryStr .=   'lu_user_type_option, '; $params[] = $userTypeOption;
		}
		$queryStr .=   'lu_create_user_id, '; $params[] = $userId;
		$queryStr .=   'lu_create_dt'; $params[] = $now;
		
		// その他のフィールド値を設定
		$otherValueStr = '';
		$otherKeys = array();
		if (!empty($otherParams)){
			$otherKeys = array_keys($otherParams);// キーを取得
			for ($i = 0; $i < count($otherKeys); $i++){
				$fieldName = $otherKeys[$i];
				$fieldValue = $otherParams[$fieldName];
				if (!isset($fieldValue)) continue;
				$params[] = $fieldValue;
				$queryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
		
		// 値を引き継ぐフィールドをセット
		for ($i = 0; $i < count($saveFields); $i++){
			$fieldName = $saveFields[$i];
			if (!in_array($fieldName, $otherKeys)){
				$params[] = $row[$fieldName];
				$queryStr .= ', ' . $fieldName;
				$otherValueStr .= ', ?';
			}
		}
		
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		if (!empty($startDt)) $queryStr .=   '?, ';
		if (!empty($endDt)) $queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?' . $otherValueStr . ') ';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(lu_serial) AS ns FROM _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// ユーザグループの更新
		if (is_null($groupArray)){
			// ユーザグループを取得
			$queryStr  = 'SELECT * FROM _user_with_group ';
			$queryStr .=   'WHERE uw_user_serial = ? ';
			$queryStr .=  'ORDER BY uw_index ';
			$this->selectRecords($queryStr, array($serial), $groupRows);

			for ($i = 0; $i < count($groupRows); $i++){
				$ret = $this->_updateUserGroup($newSerial, $i, $groupRows[$i]['uw_group_id']);
				if (!$ret){
					$this->endTransaction();
					return false;
				}
			}
		} else {
			for ($i = 0; $i < count($groupArray); $i++){
				$ret = $this->_updateUserGroup($newSerial, $i, $groupArray[$i]);
				if (!$ret){
					$this->endTransaction();
					return false;
				}
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * フィールド指定でユーザの情報を更新
	 *
	 * @param int	$id				ユーザID
	 * @param array $fieldArray		更新フィールドと値の配列
	 * @param int   $newSerial		新規シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateLoginUserByField($id, $fieldArray, &$newSerial)
	{
		global $gEnvManager;
		
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のIDのレコードが削除状態でないかチェック
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=     'AND lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			if ($startTran) $this->endTransaction();
			return false;
		}
		$serial = $row['lu_serial'];
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['lu_serial']));

		$updateFields = array();	// 変更可能フィールド名
		$updateFields[] = 'lu_account';
		$updateFields[] = 'lu_password';
		$updateFields[] = 'lu_name';
		$updateFields[] = 'lu_user_type';
		$updateFields[] = 'lu_user_type_option';
		$updateFields[] = 'lu_assign';
		$updateFields[] = 'lu_admin_widget';
		$updateFields[] = 'lu_user_status';
		$updateFields[] = 'lu_avatar';
		$updateFields[] = 'lu_enable_login';
		$updateFields[] = 'lu_active_start_dt';
		$updateFields[] = 'lu_active_end_dt';
		$updateFields[] = 'lu_tmp_password';
		$updateFields[] = 'lu_tmp_pwd_dt';
		$updateFields[] = 'lu_email';
		$updateFields[] = 'lu_skype_account';
		
		// 新規レコード追加
		$params = array();
		$valueStr = '';
		$queryStr  = 'INSERT INTO _login_user (';

		// フィールドをセット
		for ($i = 0; $i < count($updateFields); $i++){
			$fieldName = $updateFields[$i];
			$fieldValue = $fieldArray[$fieldName];
			if (isset($fieldValue)){
				$params[] = $fieldValue;
			} else {
				$params[] = $row[$fieldName];
			}
			$queryStr .= $fieldName . ', ';
			$valueStr .= '?, ';
		}
		
		$queryStr .=   'lu_id, '; $params[] = $row['lu_id'];
		$queryStr .=   'lu_history_index, '; $params[] = $row['lu_history_index'] + 1;
		$queryStr .=   'lu_widget_id, '; $params[] = $row['lu_widget_id'];			// レコードを登録したウィジェットID
		$queryStr .=   'lu_regist_dt, '; $params[] = $row['lu_regist_dt'];			// 登録日時
		$queryStr .=   'lu_create_user_id, '; $params[] = $userId;
		$queryStr .=   'lu_create_dt '; $params[] = $now;
		$queryStr .= ') VALUES (';
		$queryStr .= $valueStr;
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(lu_serial) AS ns FROM _login_user ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// ユーザグループを更新
		$queryStr  = 'SELECT * FROM _user_with_group ';
		$queryStr .=   'WHERE uw_user_serial = ? ';
		$queryStr .=  'ORDER BY uw_index ';
		$this->selectRecords($queryStr, array($serial), $groupRows);

		for ($i = 0; $i < count($groupRows); $i++){
			$ret = $this->_updateUserGroup($newSerial, $i, $groupRows[$i]['uw_group_id']);
			if (!$ret){
				if ($startTran) $ret = $this->endTransaction();
				return false;
			}
		}
			
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザグループの更新
	 *
	 * @param int        $serial		記事シリアル番号
	 * @param int        $index			インデックス番号
	 * @param int        $groupId		ユーザグループID
	 * @return bool		 true = 成功、false = 失敗
	 */
	function _updateUserGroup($serial, $index, $groupId)
	{
		// 新規レコード追加
		$queryStr = 'INSERT INTO _user_with_group ';
		$queryStr .=  '(';
		$queryStr .=  'uw_user_serial, ';
		$queryStr .=  'uw_index, ';
		$queryStr .=  'uw_group_id) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($serial, $index, $groupId));
		return $ret;
	}
	/**
	 * ログインユーザのパスワードを更新
	 *
	 * @param int	$id				ユーザID
	 * @param string  $password		パスワード
	 * @param bool  $isMd5			MD5化されたパスワードかどうか
	 * @return						true=成功、false=失敗
	 */
	function updateLoginUserPassword($id, $password, $isMd5=false)
	{
		global $gEnvManager;
		
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のIDのレコードが削除状態でないかチェック
		$queryStr  = 'select * from _login_user ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=    'and lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			if ($startTran) $this->endTransaction();
			return false;
		}
		$serial = $row['lu_serial'];
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['lu_serial']));

		// 新規レコード追加
		// パスワードはMD5変換して格納
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_active_start_dt, ';
		$queryStr .=   'lu_active_end_dt, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		if ($isMd5){		// MD5化されているパスワードのときはそのまま格納
			$queryStr .=   '?, ';
		} else {
			$queryStr .=   'md5(?), ';
		}
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$this->execStatement($queryStr, array($row['lu_id'], $row['lu_history_index'] + 1, $row['lu_name'], $row['lu_account'], $password, $row['lu_user_type'], $row['lu_assign'], $row['lu_widget_id'], $row['lu_enable_login'], $row['lu_active_start_dt'], $row['lu_active_end_dt'], $userId, $now));
		
		// ユーザグループを更新
		$queryStr  = 'SELECT * FROM _user_with_group ';
		$queryStr .=   'WHERE uw_user_serial = ? ';
		$queryStr .=  'ORDER BY uw_index ';
		$this->selectRecords($queryStr, array($serial), $groupRows);

		for ($i = 0; $i < count($groupRows); $i++){
			$ret = $this->_updateUserGroup($newSerial, $i, $groupRows[$i]['uw_group_id']);
			if (!$ret){
				if ($startTran) $ret = $this->endTransaction();
				return false;
			}
		}
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ログインアカウントを変更
	 *
	 * @param int		$id			ユーザID
	 * @param string 	$account	アカウント
	 * @return bool					成功 = true, 失敗= false
	 */
	function updateLoginUserAccount($id, $account)
	{
		global $gEnvManager;
		
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'select * from _login_user ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=    'and lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			if ($startTran) $this->endTransaction();
			return false;
		}
		$serial = $row['lu_serial'];
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['lu_serial']));

		// 新規レコード追加
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_active_start_dt, ';
		$queryStr .=   'lu_active_end_dt, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($row['lu_id'], $row['lu_history_index'] + 1, $row['lu_name'], $account, $row['lu_password'], $row['lu_user_type'], $row['lu_assign'], $row['lu_widget_id'], $row['lu_enable_login'], $row['lu_active_start_dt'], $row['lu_active_end_dt'], $userId, $now));
		
		// ユーザグループを更新
		$queryStr  = 'SELECT * FROM _user_with_group ';
		$queryStr .=   'WHERE uw_user_serial = ? ';
		$queryStr .=  'ORDER BY uw_index ';
		$this->selectRecords($queryStr, array($serial), $groupRows);

		for ($i = 0; $i < count($groupRows); $i++){
			$ret = $this->_updateUserGroup($newSerial, $i, $groupRows[$i]['uw_group_id']);
			if (!$ret){
				if ($startTran) $ret = $this->endTransaction();
				return false;
			}
		}
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザのタイプを一般ユーザに変更
	 *
	 * @param int $userId			ユーザID
	 * @return						true=成功、false=失敗
	 */
	function makeNormalLoginUser($userId)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$updateUserId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
			
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_id = ? AND lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($userId), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['lu_user_type'] >= UserInfo::USER_TYPE_NORMAL){		// 既に一般ユーザ以上のときは終了
				$this->endTransaction();
				return true;
			}
			$historyIndex = $row['lu_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		$userType = UserInfo::USER_TYPE_NORMAL;		// 一般ユーザ
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial = ?';
		$this->execStatement($queryStr, array($updateUserId, $now, $row['lu_serial']));

		// 新規レコードを追加
		$queryStr  = 'INSERT INTO _login_user (';
		$queryStr .=   'lu_id, ';
		$queryStr .=   'lu_history_index, ';
		$queryStr .=   'lu_name, ';
		$queryStr .=   'lu_account, ';
		$queryStr .=   'lu_password, ';
		$queryStr .=   'lu_user_type, ';
		$queryStr .=   'lu_assign, ';
		$queryStr .=   'lu_enable_login, ';
		$queryStr .=   'lu_widget_id, ';
		$queryStr .=   'lu_active_start_dt, ';
		$queryStr .=   'lu_active_end_dt, ';
		$queryStr .=   'lu_create_user_id, ';
		$queryStr .=   'lu_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($userId, $historyIndex, $row['lu_name'], $row['lu_account'], $row['lu_password'],
								$userType, $row['lu_assign'], $row['lu_enable_login'], $row['lu_widget_id'], $row['lu_active_start_dt'], $row['lu_active_end_dt'], $updateUserId, $now));
								
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ログインログを更新
	 *
	 * @param int	$id				ユーザID
	 * @param int  $logSerial		アクセスログシリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateLoginLog($id, $logSerial)
	{
		// トランザクション開始
		$this->startTransaction();

		$queryStr  = 'SELECT * FROM _login_log ';
		$queryStr .=   'WHERE ll_user_id = ? ';	
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			$queryStr  = 'UPDATE _login_log ';
			$queryStr .=   'SET ll_login_count = ?, ';
			$queryStr .=     'll_access_log_serial = ?, ';
			$queryStr .=     'll_pre_login_dt = ?, ';
			$queryStr .=     'll_last_login_dt = now() ';
			$queryStr .=   'WHERE ll_user_id = ?';
			$this->execStatement($queryStr, array($row['ll_login_count'] +1, $logSerial, $row['ll_last_login_dt'], $id));			
		} else {
			$queryStr = 'INSERT INTO _login_log (';
			$queryStr .=  'll_user_id, ';
			$queryStr .=  'll_login_count, ';
			$queryStr .=  'll_access_log_serial, ';
			$queryStr .=  'll_last_login_dt ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, now()';
			$queryStr .=  ')';
			$this->execStatement($queryStr, array($id, 1, $logSerial));	
		}
				
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * エラーのログインログを追加
	 *
	 * @param string $account		入力アカウント
	 * @param string $ip			IPアドレス
	 * @param int  $logSerial		アクセスログシリアル番号
	 * @return						true=成功、false=失敗
	 */
/*	function addErrorLoginLog($account, $ip, $logSerial)
	{
		// トランザクション開始
		$this->startTransaction();

		$queryStr = 'INSERT INTO _login_err_log (';
		$queryStr .=  'le_account, ';
		$queryStr .=  'le_ip, ';
		$queryStr .=  'le_access_log_serial ';
		$queryStr .=  ') VALUES (';
		$queryStr .=  '?, ?, ?';
		$queryStr .=  ')';
		$this->execStatement($queryStr, array($account, $ip, $logSerial));
				
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}*/
	/**
	 * 管理者一時キーを登録
	 *
	 * @param string $key			登録キー
	 * @param string $ip			IPアドレス
	 * @return						true=成功、false=失敗
	 */
	function addAdminKey($key, $ip)
	{
		// トランザクション開始
		$this->startTransaction();

		$queryStr = 'INSERT INTO _admin_key (';
		$queryStr .=  'ak_id, ';
		$queryStr .=  'ak_ip, ';
		$queryStr .=  'ak_create_dt ';
		$queryStr .=  ') VALUES (';
		$queryStr .=  '?, ?, now()';
		$queryStr .=  ')';
		$this->execStatement($queryStr, array($key, $ip));
				
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 有効な管理者一時キーかどうか
	 *
	 * @param string $key	アカウント
	 * @param string $ip	ユーザのアクセス元IP
	 * @return				true=有効、false=無効
	 */
	function isValidAdminKey($key, $ip)
	{
		$queryStr = 'SELECT * FROM _admin_key ';
		$queryStr .=  'WHERE ak_id = ? ';
		$queryStr .=    'AND ak_ip = ?';
		$ret = $this->selectRecord($queryStr, array($key, $ip), $row);
		if ($ret){	// 有効期間もチェック
			$lifeTime = self::ADMIN_KEY_LIFETIME;
			if (strtotime("-$lifeTime minutes") <= strtotime($row['ak_create_dt'])){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * ウィジェットパラメータ更新
	 *
	 * @param string  $widgetId			ウィジェットID
	 * @param string  $serializedParam	シリアライズしたデータ
	 * @param int     $userId			更新者ユーザID
	 * @param string  $now				現在日時
	 * @param int     $configId			定義ID(-1のとき新規追加、-1以外のときは更新)
	 * @return bool						true = 成功、false = 失敗
	 */
	function updateWidgetParam($widgetId, $serializedParam, $userId, $now = null, &$configId = 0)
	{
		// 更新日時が設定されていないときは設定
		if (empty($now)) $now = date("Y/m/d H:i:s");	// 現在日時
		$historyIndex = 0;		// 履歴番号
		
		// トランザクション開始
		$this->startTransaction();
		
		if ($configId >= 0){		// 更新のとき
			// 前レコードの削除状態チェック
			$queryStr = 'SELECT * FROM _widget_param ';
			$queryStr .=  'WHERE wp_id = ? AND wp_config_id = ? ';
			$queryStr .=  'ORDER BY wp_history_index DESC ';
			$ret = $this->selectRecord($queryStr, array($widgetId, $configId), $row);
			if ($ret){
				$historyIndex = $row['wp_history_index'] + 1;
	
				// レコードが削除されていない場合は削除
				if (!$row['wp_deleted']){
					// 古いレコードを削除
					$queryStr  = 'UPDATE _widget_param ';
					$queryStr .=   'SET wp_deleted = true, ';	// 削除
					$queryStr .=     'wp_update_user_id = ?, ';
					$queryStr .=     'wp_update_dt = ? ';
					$queryStr .=   'WHERE wp_serial = ?';
					$ret = $this->execStatement($queryStr, array($userId, $now, $row['wp_serial']));
					if (!$ret){
						$this->endTransaction();
						return false;
					}
				}
			}
		} else {		// 新規追加のときは定義IDを求める
			// 定義IDは1以上を使用する
			$newId = 1;
			$queryStr = 'SELECT max(wp_config_id) as ms FROM _widget_param WHERE wp_id = ?';
			$ret = $this->selectRecord($queryStr, array($widgetId), $row);
			if ($ret) $newId = $row['ms'] + 1;
			$configId = $newId;			// 定義IDを更新
		}
		
		if (!empty($serializedParam)){		// 設定データが空のときはデータを追加しない
			// データを追加
			$queryStr = 'INSERT INTO _widget_param ';
			$queryStr .=  '(wp_id, wp_config_id, wp_history_index, wp_param, wp_create_user_id, wp_create_dt) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($widgetId, $configId, $historyIndex, $serializedParam, $userId, $now));
		}
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットパラメータ更新(キャッシュデータ更新用)
	 *
	 * @param string  $widgetId			ウィジェットID
	 * @param int     $configId			定義ID
	 * @param string  $cacheData		キャッシュデータ
	 * @param string  $metaTitle		METAタグ、タイトル
	 * @param string  $metaDesc			METAタグ、ページ要約
	 * @param string  $metaKeyword		METAタグ、検索用キーワード
	 * @return bool						true = 成功、false = 失敗
	 */
	function updateWidgetCache($widgetId, $configId, $cacheData, $metaTitle = '', $metaDesc = '', $metaKeyword = '')
	{
		global $gEnvManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		$historyIndex = 0;		// 履歴番号
		
		// トランザクション開始
		$this->startTransaction();
		
		// データが存在する場合は更新。存在しない場合は新規追加
		$queryStr  = 'SELECT * FROM _widget_param ';
		$queryStr .=   'WHERE wp_id = ? AND wp_config_id = ? AND wp_deleted = false';
		$ret = $this->selectRecord($queryStr, array($widgetId, $configId), $row);
		if ($ret){
			$queryStr  = 'UPDATE _widget_param ';
			$queryStr .=   'SET wp_cache_html = ?, ';
			$queryStr .=     'wp_meta_title = ?, ';
			$queryStr .=     'wp_meta_description = ?, ';
			$queryStr .=     'wp_meta_keywords = ?, ';
			$queryStr .=     'wp_cache_user_id = ?, ';
			$queryStr .=     'wp_cache_update_dt = ? ';
			$queryStr .=   'WHERE wp_serial = ?';
			$ret = $this->execStatement($queryStr, array($cacheData, $metaTitle, $metaDesc, $metaKeyword, $userId, $now, $row['wp_serial']));			
		} else {
			$queryStr  = 'INSERT INTO _widget_param (';
			$queryStr .=   'wp_id, ';
			$queryStr .=   'wp_config_id, ';
			$queryStr .=   'wp_cache_html, ';
			$queryStr .=   'wp_meta_title, ';
			$queryStr .=   'wp_meta_description, ';
			$queryStr .=   'wp_meta_keywords, ';
			$queryStr .=   'wp_cache_user_id, ';
			$queryStr .=   'wp_cache_update_dt ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?, ?, ?, ?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array($widgetId, $configId, $cacheData, $metaTitle, $metaDesc, $metaKeyword, $userId, $now));	
		}
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットパラメータ取得
	 *
	 * @param  string $widgetId			ウィジェットID
	 * @param int     $configId			定義ID
	 * @return string		シリアライズされたパラメータデータ
	 */
	function getWidgetParam($widgetId, $configId = 0)
	{
		$sql = 'SELECT wp_param FROM _widget_param WHERE wp_id = ? AND wp_config_id = ? AND wp_deleted = false';
		$params = array($widgetId, $configId); 
		$this->selectRecord($sql, $params, $row);
		return $row['wp_param'];
    }
	/**
	 * ウィジェットパラメータ取得(キャッシュデータ取得用)
	 *
	 * @param  string $widgetId			ウィジェットID
	 * @param int     $configId			定義ID
	 * @param  array  $row				取得レコード
	 * @return bool						true=正常取得、false=取得できず
	 */
	function getWidgetCache($widgetId, $configId, &$row)
	{
		$sql = 'SELECT wp_cache_html,wp_meta_title,wp_meta_description,wp_meta_keywords,wp_cache_user_id,wp_cache_update_dt FROM _widget_param WHERE wp_id = ? AND wp_config_id = ? AND wp_deleted = false';
		$params = array($widgetId, $configId); 
		$ret = $this->selectRecord($sql, $params, $row);
		return $ret;
    }
	/**
	 * ウィジェットキャッシュデータを削除
	 *
	 * @param  string $widgetId		ウィジェットID(空のときはすべてのウィジェットが対象)
	 * @param int     $configId		定義ID
	 * @return bool					true=成功、false=失敗
	 */
	function deleteWidgetCache($widgetId = '', $configId = 0)
	{
		global $gEnvManager;
		
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($widgetId)){
			$queryStr  = 'UPDATE _widget_param ';
			$queryStr .=   'SET wp_cache_html = ?, ';
			$queryStr .=     'wp_meta_title = ?, ';
			$queryStr .=     'wp_meta_description = ?, ';
			$queryStr .=     'wp_meta_keywords = ?, ';
			$queryStr .=     'wp_cache_user_id = ?, ';
			$queryStr .=     'wp_cache_update_dt = ? ';
			$ret = $this->execStatement($queryStr, array('', '', '', '', 0, $gEnvManager->getInitValueOfTimestamp()));
		} else {		// ウィジェットID指定のとき
			if ($configId == 0){
				$queryStr  = 'UPDATE _widget_param ';
				$queryStr .=   'SET wp_cache_html = ?, ';
				$queryStr .=     'wp_meta_title = ?, ';
				$queryStr .=     'wp_meta_description = ?, ';
				$queryStr .=     'wp_meta_keywords = ?, ';
				$queryStr .=     'wp_cache_user_id = ?, ';
				$queryStr .=     'wp_cache_update_dt = ? ';
				$queryStr .=   'WHERE wp_id = ? ';
				$ret = $this->execStatement($queryStr, array('', '', '', '', 0, $gEnvManager->getInitValueOfTimestamp(), $widgetId));
			} else {	// 定義IDを制限
				$queryStr  = 'UPDATE _widget_param ';
				$queryStr .=   'SET wp_cache_html = ?, ';
				$queryStr .=     'wp_meta_title = ?, ';
				$queryStr .=     'wp_meta_description = ?, ';
				$queryStr .=     'wp_meta_keywords = ?, ';
				$queryStr .=     'wp_cache_user_id = ?, ';
				$queryStr .=     'wp_cache_update_dt = ? ';
				$queryStr .=   'WHERE wp_id = ? ';
				$queryStr .=     'AND wp_config_id = ? ';
				$ret = $this->execStatement($queryStr, array('', '', '', '', 0, $gEnvManager->getInitValueOfTimestamp(), $widgetId, $configId));
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットパラメータをすべて取得
	 *
	 * @param  string $widgetId		ウィジェットID
	 * @param array  $rows			レコード
	 * @return						true=1行以上取得、false=取得せず
	 */
	function getAllWidgetParam($widgetId, &$rows)
	{
		$sql = 'SELECT wp_config_id,wp_param FROM _widget_param WHERE wp_id = ? AND wp_deleted = false ORDER BY wp_config_id';
		$params = array($widgetId); 
		$retValue = $this->selectRecords($sql, $params, $rows);
		return $retValue;
    }
	/**
	 * すべての追加クラス情報を取得
	 *
	 * @param array  $rows			レコード
	 * @return			true=1行以上取得、false=取得せず
	 */
	function getAllAddons(&$rows)
	{
		$queryStr = 'SELECT * FROM _addons WHERE ao_autoload = true ORDER BY ao_index';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * メールフォームを取得
	 *
	 * @param  string $formId	フォームID
	 * @param  string $langId	言語ID
	 * @param  array  $row		取得レコード
	 * @return bool				true=正常取得、false=取得できず
	 */
	function getMailForm($formId, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM _mail_form ';
		$queryStr .=   'WHERE mf_id = ? ';
		$queryStr .=     'AND mf_language_id = ? ';
		$queryStr .=     'AND mf_deleted = false';
		return $this->selectRecord($queryStr, array($formId, $langId), $row);
    }
	/**
	 * メールフォーマットを更新
	 *
	 * @param string $id			フォームID
	 * @param string $langId		言語ID
	 * @param string $content		メール本文
	 * @param string $subject		メール件名
	 * @return bool 				true=正常、false=異常
	 */
	function updateMailForm($id, $langId, $content, $subject = null)
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
		$queryStr  = 'SELECT * FROM _mail_form ';
		$queryStr .=   'WHERE mf_id = ? ';
		$queryStr .=     'AND mf_language_id = ? ';
		$queryStr .=   'ORDER BY mf_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		if ($ret){
			$historyIndex = $row['mf_history_index'] + 1;
			if (is_null($subject)) $subject = $row['mf_subject'];
			
			if (!$row['mf_deleted']){		// レコード存在していれば削除
				$queryStr  = 'UPDATE _mail_form ';
				$queryStr .=   'SET mf_deleted = true, ';	// 削除
				$queryStr .=     'mf_update_user_id = ?, ';
				$queryStr .=     'mf_update_dt = ? ';
				$queryStr .=   'WHERE mf_serial = ?';
				$ret = $this->execStatement($queryStr, array($userId, $now, $row['mf_serial']));
				if (!$ret){
					$this->endTransaction();
					return false;
				}
			}
		}
		
		// データを追加
		$params = array();
		$queryStr  = 'INSERT INTO _mail_form ';
		$queryStr .=   '(';
		$queryStr .=  'mf_id, '; $params[] = $id;
		$queryStr .=  'mf_language_id, '; $params[] = $langId;
		$queryStr .=  'mf_history_index, '; $params[] = $historyIndex;
		if (!is_null($subject)){
			$queryStr .=  'mf_subject, ';
			$params[] = $subject;
		}
		$queryStr .=  'mf_content, '; $params[] = $content;
		$queryStr .=  'mf_create_user_id, '; $params[] = $userId;
		$queryStr .=  'mf_create_dt'; $params[] = $now;
		$queryStr .=  ') VALUES (' . rtrim(str_repeat('?,', count($params)), ',') . ')';
		$this->execStatement($queryStr, $params);

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メール送信ログを追加
	 *
	 * @param int $type				メール送信タイプ(0=未設定、1=自動送信、2=手動送信)
	 * @param string $widgetId		送信を行ったウィジェットID
	 * @param string $toAddress		送信先メールアドレス
	 * @param string $fromAddress	送信元メールアドレス
	 * @param string $subject		件名
	 * @param string $body			メール本文
	 * @return bool					true=正常、false=異常
	 */
	function addMailLog($type, $widgetId, $toAddress, $fromAddress, $subject, $body)
	{	
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'INSERT INTO _mail_send_log (';
		$queryStr .=  'ms_type, ';
		$queryStr .=  'ms_widget_id, ';
		$queryStr .=  'ms_to, ';
		$queryStr .=  'ms_from, ';
		$queryStr .=  'ms_subject, ';
		$queryStr .=  'ms_body, ';
		$queryStr .=  'ms_dt ';
		$queryStr .=  ') VALUES (';
		$queryStr .=  '?, ?, ?, ?, ?, ?, now()';
		$queryStr .=  ')';
		$ret = $this->execStatement($queryStr, array($type, $widgetId, $toAddress, $fromAddress, $subject, $body));
		
		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * コンテンツのビューカウントを更新
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param int     	$serial				シリアル番号(0のときはコンテンツIDを使用)
	 * @param string    $contentId			コンテンツID
	 * @param string	$day				日にち
	 * @param int		$hour				時間(0～23)
	 * @return bool							true=成功, false=失敗
	 */
	function updateViewCount($typeId, $serial, $contentId = '', $day = null, $hour = null)
	{
		// 現在日時を取得
		if (is_null($day)) $day = date("Y/m/d");		// 日
		if (is_null($hour)) $hour = (int)date("H");		// 時間
		
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($serial)){		// コンテンツIDで指定のとき
			// 既にレコードが作成されている場合はレコードを更新
			$queryStr  = 'SELECT * FROM _view_count ';
			$queryStr .=   'WHERE vc_type_id = ? ';			// データタイプはデフォルトコンテンツ
			$queryStr .=     'AND vc_content_id = ? ';
			$queryStr .=     'AND vc_date = ? ';
			$queryStr .=     'AND vc_hour = ? ';
			$ret = $this->selectRecord($queryStr, array($typeId, $contentId, $day, $hour), $row);
			if ($ret){
				$count = $row['vc_count'];
				$count++;		// カウント数を更新
				$queryStr = 'UPDATE _view_count ';
				$queryStr .=  'SET vc_count = ? ';
				$queryStr .=  'WHERE vc_serial = ? ';
				$this->execStatement($queryStr, array($count, $row['vc_serial']));
			} else {
				$queryStr = 'INSERT INTO _view_count ';
				$queryStr .=  '(vc_type_id, vc_count, vc_content_id, vc_date, vc_hour) ';
				$queryStr .=  'VALUES ';
				$queryStr .=  '(?, 1, ?, ?, ?)';
				$this->execStatement($queryStr, array($typeId, $contentId, $day, $hour));
			}
		} else {
			// 既にレコードが作成されている場合はレコードを更新
			$queryStr  = 'SELECT * FROM _view_count ';
			$queryStr .=   'WHERE vc_type_id = ? ';			// データタイプはデフォルトコンテンツ
			$queryStr .=     'AND vc_content_serial = ? ';
			$queryStr .=     'AND vc_date = ? ';
			$queryStr .=     'AND vc_hour = ? ';
			$ret = $this->selectRecord($queryStr, array($typeId, $serial, $day, $hour), $row);
			if ($ret){
				$count = $row['vc_count'];
				$count++;		// カウント数を更新
				$queryStr = 'UPDATE _view_count ';
				$queryStr .=  'SET vc_count = ? ';
				$queryStr .=  'WHERE vc_serial = ? ';
				$this->execStatement($queryStr, array($count, $row['vc_serial']));
			} else {
				$queryStr = 'INSERT INTO _view_count ';
				$queryStr .=  '(vc_type_id, vc_count, vc_content_serial, vc_date, vc_hour) ';
				$queryStr .=  'VALUES ';
				$queryStr .=  '(?, 1, ?, ?, ?)';
				$this->execStatement($queryStr, array($typeId, $serial, $day, $hour));
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツのビューカウント総数を取得
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param int $serial					シリアル番号(0のときはコンテンツIDを使用)
	 * @param string    $contentId			コンテンツID
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @return int							総数
	 */
	function getTotalViewCount($typeId, $serial, $contentId = '', $startDt = null, $endDt = null)
	{
		$count = 0;
		$params = array();
		$queryStr  = 'SELECT SUM(vc_count) AS total FROM _view_count ';
		$queryStr .=   'WHERE vc_type_id = ? ';	$params[] = $typeId;		// データタイプはデフォルトコンテンツ
		/*if (is_array($serial)){	// 配列のとき
			$queryStr .=	'AND vc_content_serial in (' . implode(",", $serial) . ') ';
		} else {
			$queryStr .=	'AND vc_content_serial = ? '; $params[] = $serial;
		}*/
		if (empty($serial)){	// コンテンツIDで指定のとき
			$queryStr .=	'AND vc_content_id = ? '; $params[] = $contentId;
		} else {
			$queryStr .=	'AND vc_content_serial = ? '; $params[] = $serial;
		}
		// 日付範囲
		if (!empty($startDt)){
			$queryStr .=    'AND ? <= vc_date ';
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			$queryStr .=    'AND vc_date < ? ';
			$params[] = $endDt;
		}
		$ret = $this->selectRecord($queryStr, $params, $row);
		if ($ret) $count = intval($row['total']);
		return $count;
	}
	/**
	 * エラーログ書き込み
	 *
	 * @param  string $type		メッセージタイプ(info=情報,warn=警告,error=通常エラー,fatal=致命的エラー)
	 * @param  string $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param  string $message	メッセージ
	 * @param  int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param string $searchOption   	検索用補助データ
	 * @param string $link		リンク先
	 * @return bool				true=成功、false=失敗
	 */
	function writeErrorLog($type, $method, $message, $code = 0, $msgExt = '', $searchOption = '', $link = '')
	{
		global $gEnvManager;
		global $gAccessManager;
		global $gSystemManager;
		
		// アクセスログ初期化
		$gAccessManager->initAccessLog();
		
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// トランザクション開始
		$this->startTransaction();
		
		// バージョンによる処理の違いを吸収
		if (defined('M3_STATE_IN_INSTALL')){		// システムがインストールモードで起動のとき
			// DBから直接バージョンを取得
			$currentVer = $this->getSystemConfig(M3_TB_FIELD_DB_VERSION);
		} else {
			$currentVer = $gSystemManager->getSystemConfig(M3_TB_FIELD_DB_VERSION);
		}
		if ($currentVer >= 2012090701){
			// バージョン2012090701以降で「ol_link(リンク先)」を追加(2012/9/12)
			$sql = "INSERT INTO _operation_log (ol_type, ol_method, ol_message, ol_message_ext, ol_message_code, ol_access_log_serial, ol_search_option, ol_link, ol_widget_id, ol_dt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, now())";
			$params = array($type, $method, $message, $msgExt, intval($code), intval($logSerial), $searchOption, $link, $gEnvManager->getCurrentWidgetId());
		} else if ($currentVer >= 2012022401){
			// バージョン2012022401以降で「ol_widget_id(実行ウィジェットID)」を追加(2012/3/1)
			$sql = "INSERT INTO _operation_log (ol_type, ol_method, ol_message, ol_message_ext, ol_message_code, ol_access_log_serial, ol_search_option, ol_widget_id, ol_dt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, now())";
			$params = array($type, $method, $message, $msgExt, intval($code), intval($logSerial), $searchOption, $gEnvManager->getCurrentWidgetId());
		} else if ($currentVer >= 2011051401){
			// バージョン2011051401以降で「ol_search_option(検索用補助データ)」を追加(2011/5/14)
			$sql = "INSERT INTO _operation_log (ol_type, ol_method, ol_message, ol_message_ext, ol_message_code, ol_access_log_serial, ol_search_option, ol_dt) VALUES (?, ?, ?, ?, ?, ?, ?, now())";
			$params = array($type, $method, $message, $msgExt, intval($code), intval($logSerial), $searchOption);
		} else if ($currentVer >= 2008112601){
			// バージョン2008112601以降で「ol_message_ext(メッセージ詳細)」を追加(2008/11/26)
			$sql = "INSERT INTO _operation_log (ol_type, ol_method, ol_message, ol_message_ext, ol_message_code, ol_access_log_serial, ol_dt) VALUES (?, ?, ?, ?, ?, ?, now())";
			$params = array($type, $method, $message, $msgExt, intval($code), intval($logSerial));
		} else if ($currentVer >= 2008111901){
			// バージョン2008111901以降で「ol_message_code(メッセージコード)」を追加(2008/11/24)
			$sql = "INSERT INTO _operation_log (ol_type, ol_method, ol_message, ol_message_code, ol_access_log_serial, ol_dt) VALUES (?, ?, ?, ?, ?, now())";
			$params = array($type, $method, $message, intval($code), intval($logSerial));
		} else {
			$sql = "INSERT INTO _operation_log (ol_type, ol_method, ol_message, ol_access_log_serial, ol_dt) VALUES (?, ?, ?, ?, now())";
			$params = array($type, $method, $message, intval($logSerial));
		}
		$this->execStatement($sql, $params);

		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * デバッグ出力書き込み
	 *
	 * @param  string $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param  string $message	メッセージ
	 * @param  int    $code		メッセージコード
	 * @return bool				true=成功、false=失敗
	 */
	function debugOut($method, $message, $code = 0)
	{
		global $gEnvManager;
		
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		$time = sprintf('%01.06f', microtime(true) - M3_MTIME);
		$memoryUsage = 0;
		if (function_exists('memory_get_usage')) $memoryUsage = memory_get_usage();
		
		// トランザクション開始
		$this->startTransaction();
		
		$sql = "INSERT INTO _debug (db_method, db_message, db_access_log_serial, db_memory_usage, db_time, db_dt) VALUES (?, ?, ?, ?, ?, now())";
		$params = array($method, $message, $logSerial, $memoryUsage, $time);
		$this->execStatement($sql, $params);

		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * ウィジェット実行ログ書き込み
	 *
	 * ウィジェットの実行状況(公開アクセス)をログに残す
	 *
	 * @param string $widgetId		実行ウィジェット
	 * @param int $type				実行タイプ(0=ページからの実行、1=単体実行)
	 * @param string $cmd			実行コマンド
	 * @param string $message		メッセージ
	 * @return bool					true=ログ出力完了、false=失敗
	 */
	function writeWidgetLog($widgetId, $type, $cmd = '', $message = '')
	{
		global $gEnvManager;
		
		// ログを残さないときは終了
		if (!$gEnvManager->getWidgetLog()) return false;
		
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// トランザクション開始
		$this->startTransaction();
		
		$sql = "INSERT INTO _widget_log (wl_widget_id, wl_type, wl_cmd, wl_message, wl_access_log_serial, wl_dt) VALUES (?, ?, ?, ?, ?, now())";
		$params = array($widgetId, $type, $cmd, $message, $logSerial);
		$this->execStatement($sql, $params);

		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * 汎用連想配列型パラメータを取得
	 *
	 * @param string	$key			キー
	 * @param string	$name			名前
	 * @return string					値、未設定の場合は空文字列
	 */
	function getKeyValue($key, &$name)
	{
		$retValue = '';
		$queryStr  = 'SELECT * FROM _key_value ';
		$queryStr .=   'WHERE kv_deleted = false ';	// 削除されていない
		$queryStr .=   'AND kv_id = ? ';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret){
			$retValue = $row['kv_value'];
			$name = $row['kv_name'];
		}
		return $retValue;
	}
	/**
	 * 汎用連想配列型パラメータをすべて取得
	 *
	 * @return array		レコード
	 */
	function getAllKeyValueRecords()
	{
		$queryStr  = 'SELECT * FROM _key_value ';
		$queryStr .=   'WHERE kv_deleted = false ';// 削除されていない
		$queryStr .=   'ORDER BY kv_id';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		if ($retValue){
			return $rows;
		} else {
			return array();
		}
	}
	/**
	 * 汎用連想配列型パラメータのキーをシリアル番号で取得
	 *
	 * @param int		$serial			シリアル番号
	 * @return string					キー文字列、未設定の場合は空文字列
	 */
	function getKeyBySerial($serial)
	{
		$retValue = '';
		$queryStr  = 'SELECT * FROM _key_value ';
		$queryStr .=   'WHERE kv_deleted = false ';	// 削除されていない
		$queryStr .=   'AND kv_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			$retValue = $row['kv_id'];
		}
		return $retValue;
	}
	/**
	 * 汎用連想配列型パラメータを更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @param string $name		名前
	 * @param string $group		グループID
	 * @return					true = 正常、false=異常
	 */
	function updateKeyValue($key, $value, $name='', $group = '')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 前レコードの削除状態チェック
		$historyIndex = 0;
//		$name = '';
		$desc = '';
		$queryStr = 'SELECT * FROM _key_value ';
		$queryStr .=  'WHERE kv_id = ? ';
		$queryStr .=  'ORDER BY kv_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret){
			$historyIndex = $row['kv_history_index'] + 1;
//			$name = $row['kv_name'];
			$desc = $row['kv_description'];
			
			// レコードが削除されていない場合は削除
			if (!$row['kv_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE _key_value ';
				$queryStr .=   'SET kv_deleted = true, ';	// 削除
				$queryStr .=     'kv_update_user_id = ?, ';
				$queryStr .=     'kv_update_dt = ? ';
				$queryStr .=   'WHERE kv_serial = ?';
				$ret = $this->execStatement($queryStr, array($userId, $now, $row['kv_serial']));
				if (!$ret){
					$this->endTransaction();
					return false;
				}
			}
		}
		
		// データを追加
		$queryStr = 'INSERT INTO _key_value ';
		$queryStr .=  '(kv_id, kv_history_index, kv_value, kv_name, kv_description, kv_group_id, kv_create_user_id, kv_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($key, $historyIndex, $value, $name, $desc, $group, $userId, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	
	/**
	 * ログインユーザ情報を更新
	 *
	 * @param int     $id			ログインユーザID
	 * @param string  $no			ユーザNo(任意使用)
	 * @param string  $familyName	名前(姓)
	 * @param string  $firstName	名前(名)
	 * @param string  $familyNameKana	名前カナ(姓)
	 * @param string  $firstNameKana	名前カナ(名)
	 * @param int     $gender		性別(0=男、1=女)
	 * @param date    $birthday		生年月日
	 * @param string  $email		Eメール
	 * @param string  $mobile		携帯電話
	 * @param string  $zipcode		郵便番号
	 * @param int     $stateId		都道府県
	 * @param string  $address1		市区町村
	 * @param string  $address2		ビル・マンション名等
	 * @param string  $phone		電話番号
	 * @param string  $fax			FAX
	 * @param string  $countryId	国ID
	 * @param int     $newSerial	新規レコードのシリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateLoginUserInfo($id, $no, $familyName, $firstName, $familyNameKana, $firstNameKana, $gender, $birthday, $email, $mobile,
													$zipcode, $stateId, $address1, $address2, $phone, $fax, $countryId, &$newSerial)
	{
		global $gEnvManager;
			
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のレコードの履歴インデックス取得
		$queryStr  = 'SELECT * FROM _login_user_info ';
		$queryStr .=   'WHERE li_id = ? ';
		$queryStr .=  'ORDER BY li_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			if (!$row['li_deleted']){		// 削除されていなければ削除
				// レコードを削除
				$queryStr  = 'UPDATE _login_user_info ';
				$queryStr .=   'SET li_deleted = true, ';
				$queryStr .=   'li_update_user_id = ?, ';
				$queryStr .=   'li_update_dt = ? ';			
				$queryStr .=   'WHERE li_serial = ? ';
				$this->execStatement($queryStr, array($userId, $now, $row['li_serial']));
			}
			$historyIndex = $row['li_history_index'] + 1;
		} else {
			$historyIndex = 0;
		}
	
		// 新規レコードを追加
		$queryStr  = 'INSERT INTO _login_user_info (';
		$queryStr .=   'li_id, ';
		$queryStr .=   'li_history_index, ';
		$queryStr .=   'li_no, ';
		$queryStr .=   'li_family_name, ';
		$queryStr .=   'li_first_name, ';
		$queryStr .=   'li_family_name_kana, ';
		$queryStr .=   'li_first_name_kana, ';
		$queryStr .=   'li_gender, ';
		$queryStr .=   'li_birthday, ';
		$queryStr .=   'li_email, ';
		$queryStr .=   'li_mobile, ';
		$queryStr .=   'li_zipcode, ';
		$queryStr .=   'li_state_id, ';
		$queryStr .=   'li_address1, ';
		$queryStr .=   'li_address2, ';
		$queryStr .=   'li_phone, ';
		$queryStr .=   'li_fax, ';
		$queryStr .=   'li_country_id, ';
		$queryStr .=   'li_create_user_id, ';
		$queryStr .=   'li_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$ret = $this->execStatement($queryStr, array($id, $historyIndex, $no, $familyName, $firstName, $familyNameKana, $firstNameKana, $gender, $birthday, $email, $mobile,
													$zipcode, $stateId, $address1, $address2, $phone, $fax, $countryId, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'select max(li_serial) as ns from _login_user_info ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * フィールド指定でユーザ情報を更新
	 *
	 * @param int	$id				ユーザID
	 * @param array $fieldArray		更新フィールドと値の配列
	 * @param int   $newSerial		新規シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function updateLoginUserInfoByField($id, $fieldArray, &$newSerial)
	{
		global $gEnvManager;
		
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のIDの最新のレコードを取得
		$keepValue = false;		// フィールド値を維持するかどうか
		$historyIndex = 0;
		$queryStr  = 'SELECT * FROM _login_user_info ';
		$queryStr .=   'WHERE li_id = ? ';
		$queryStr .=   'ORDER BY li_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			if (!$row['li_deleted']){			// 削除されていないとき
				// 古いレコードを削除
				$queryStr  = 'UPDATE _login_user_info ';
				$queryStr .=   'SET li_deleted = true, ';	// 削除
				$queryStr .=     'li_update_user_id = ?, ';
				$queryStr .=     'li_update_dt = ? ';
				$queryStr .=   'WHERE li_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $row['li_serial']));
				
				$keepValue = true;		// フィールド値を維持するかどうか
			}
			$historyIndex = $row['li_history_index'] + 1;
		}

		$updateFields = array();	// 変更可能フィールド名
		$updateFields[] = 'li_no';		// 任意利用No
		$updateFields[] = 'li_family_name';		// ユーザ名(姓)漢字
		$updateFields[] = 'li_first_name';		// ユーザ名(名)漢字
		$updateFields[] = 'li_family_name_kana';	// ユーザ名(姓)カナ
		$updateFields[] = 'li_first_name_kana';		// ユーザ名(名)カナ
		$updateFields[] = 'li_gender';	// 性別(0=未設定、1=男、2=女)
		$updateFields[] = 'li_birthday';	// 誕生日(西暦)
		$updateFields[] = 'li_email';		// Eメールアドレス
		$updateFields[] = 'li_mobile';		// 携帯電話
		$updateFields[] = 'li_zipcode';		// 郵便番号(7桁)
		$updateFields[] = 'li_state_id';	// 都道府県、州(geo_zoneテーブル)
		$updateFields[] = 'li_address1';	// 市区町村
		$updateFields[] = 'li_address2';	// ビル・マンション名等
		$updateFields[] = 'li_phone';		// 電話番号
		$updateFields[] = 'li_fax';			// FAX
		$updateFields[] = 'li_country_id';	// 国ID
		$updateFields[] = 'li_profile';		// 自己紹介
	
		// 新規レコード追加
		$params = array();
		$valueStr = '';
		$queryStr  = 'INSERT INTO _login_user_info (';

		// フィールドをセット
		for ($i = 0; $i < count($updateFields); $i++){
			$fieldName = $updateFields[$i];
			$fieldValue = $fieldArray[$fieldName];
			if (isset($fieldValue)){
				$params[] = $fieldValue;
				$queryStr .= $fieldName . ', ';
				$valueStr .= '?, ';
			} else {
				if ($keepValue){
					$params[] = $row[$fieldName];
					$queryStr .= $fieldName . ', ';
					$valueStr .= '?, ';
				}
			}
		}
		
		$queryStr .=   'li_id, '; $params[] = $id;
		$queryStr .=   'li_history_index, '; $params[] = $historyIndex;
		$queryStr .=   'li_create_user_id, '; $params[] = $userId;
		$queryStr .=   'li_create_dt '; $params[] = $now;
		$queryStr .= ') VALUES (';
		$queryStr .= $valueStr;
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?) ';
		$this->execStatement($queryStr, $params);

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(li_serial) AS ns FROM _login_user_info ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ログインユーザ情報をIDで削除
	 *
	 * @param int	$id						ユーザID
	 * @return bool							取得 = true, 取得なし= false
	 */
	function delLoginUserInfo($id)
	{
		global $gEnvManager;
		
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// 指定のIDのレコードが削除状態でないかチェック
		$queryStr  = 'select * from _login_user_info ';
		$queryStr .=   'WHERE li_id = ? ';
		$queryStr .=    'and li_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			if ($startTran) $this->endTransaction();
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _login_user_info ';
		$queryStr .=   'SET li_deleted = true, ';	// 削除
		$queryStr .=     'li_update_user_id = ?, ';
		$queryStr .=     'li_update_dt = ? ';
		$queryStr .=   'WHERE li_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['li_serial']));
		
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画面キャッシュデータを取得
	 *
	 * @param string $widgetId	ウィジェットID
	 * @param string $url    	キーとなるURL
	 * @param  array  $row		取得レコード
	 * @return bool				true=正常取得、false=取得できず
	 */
	function getCacheData($widgetId, $url, &$row)
	{
		$queryStr = 'SELECT * FROM _cache ';
		$queryStr .=  'WHERE ca_widget_id = ? ';
		$queryStr .=    'AND ca_url = ?';
		return $this->selectRecord($queryStr, array($widgetId, $url), $row);
	}
	/**
	 * ウィジェットIDで画面キャッシュデータを取得
	 *
	 * @param string $widgetId	ウィジェットID
	 * @param  array  $rows		取得レコード
	 * @return bool				true=正常取得、false=取得できず
	 */
	function getCacheDataByWidgetId($widgetId, &$rows)
	{
		$queryStr = 'SELECT ca_serial,ca_url,ca_page_id,ca_page_sub_id,ca_update_user_id,ca_update_dt FROM _cache ';
		$queryStr .=  'WHERE ca_widget_id = ? ';
		return $this->selectRecords($queryStr, array($widgetId), $rows);
	}
	/**
	 * 画面キャッシュデータを更新
	 *
	 * @param string $widgetId	ウィジェットID
	 * @param string $url    	キーとなるURL
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $html		キャッシュデータ
	 * @param string $metaTitle		METAタグ、タイトル
	 * @param string $metaDesc		METAタグ、ページ要約
	 * @param string $metaKeyword	METAタグ、検索用キーワード
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function updateCacheData($widgetId, $url, $pageId, $pageSubId, $html, $metaTitle='', $metaDesc='', $metaKeyword='')
	{
		global $gEnvManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 古いデータを削除
		$queryStr = 'DELETE FROM _cache ';
		$queryStr .=  'WHERE ca_widget_id = ? ';
		$queryStr .=    'AND ca_url = ?';
		$this->execStatement($queryStr, array($widgetId, $url));
		
		// 新規データを追加
		$queryStr = 'INSERT INTO _cache (';
		$queryStr .=  'ca_widget_id, ';
		$queryStr .=  'ca_url, ';
		$queryStr .=  'ca_page_id, ';
		$queryStr .=  'ca_page_sub_id, ';
		$queryStr .=  'ca_html, ';
		$queryStr .=  'ca_meta_title, ';
		$queryStr .=  'ca_meta_description, ';
		$queryStr .=  'ca_meta_keywords, ';
		$queryStr .=  'ca_update_user_id, ';
		$queryStr .=  'ca_update_dt ';
		$queryStr .=  ') VALUES (';
		$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
		$queryStr .=  ')';
		$ret = $this->execStatement($queryStr, array($widgetId, $url, $pageId, $pageSubId, $html, $metaTitle, $metaDesc, $metaKeyword, $userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画面キャッシュデータを削除
	 *
	 * @param string $pageId		ページID(空のときはすべて削除)
	 * @param string $pageSubId		ページサブID
	 * @return bool					true=成功、false=失敗
	 */
	function deletePageCacheData($pageId, $pageSubId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($pageId)){		// キャッシュデータをすべて削除のとき
			// 古いデータを削除
			$queryStr = 'DELETE FROM _cache ';
			$this->execStatement($queryStr, array());
		} else if (empty($pageSubId)){
			// 古いデータを削除
			$queryStr = 'DELETE FROM _cache ';
			$queryStr .=  'WHERE ca_page_id = ? ';
			$this->execStatement($queryStr, array($pageId));
		} else {
			// 古いデータを削除
			$queryStr = 'DELETE FROM _cache ';
			$queryStr .=  'WHERE ca_page_id = ? ';
			$queryStr .=    'AND ca_page_sub_id = ?';
			$this->execStatement($queryStr, array($pageId, $pageSubId));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画面キャッシュデータをシリアル番号で削除
	 *
	 * @param array $serialArray	シリアルNo
	 * @return bool					true=成功、false=失敗
	 */
	function deletePageCacheDataBySerial($serialArray)
	{
		if (!is_array($serialArray) || count($serialArray) <= 0) return true;
				
		// トランザクション開始
		$this->startTransaction();
		
		// データを削除
		$queryStr = 'DELETE FROM _cache ';
		$queryStr .=  'WHERE ca_serial in (' . implode($serialArray, ',') . ') ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * テナントサーバ情報をサーバIDで取得
	 *
	 * @param string	$id					サーバID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getServerById($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _tenant_server ';
		$queryStr .=   'WHERE ts_deleted = false ';// 削除されていない
		$queryStr .=     'AND ts_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 検索キーワードログ書き込み
	 *
	 * @param int $deviceType 		端末タイプ
	 * @param string $cid			クッキー値のクライアントID
	 * @param string $widgetId		実行ウィジェット
	 * @param string $keyword		検索キーワード
	 * @param string $basicWord		比較用基本ワード
	 * @param string $path			アクセスパス
	 * @return bool					true=ログ出力完了、false=失敗
	 */
	function writeSearchWordLog($deviceType, $cid, $widgetId, $keyword, $basicWord, $path)
	{
		global $gEnvManager;
				
		$now = date("Y/m/d H:i:s");	// 現在日時
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// トランザクション開始
		$this->startTransaction();
		
		// データを追加
		$queryStr  = 'INSERT INTO _search_word ';
		$queryStr .=   '(sw_device_type, sw_client_id, sw_widget_id, sw_word, sw_basic_word, sw_path, sw_access_log_serial, sw_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array(intval($deviceType), $cid, $widgetId, $keyword, $basicWord, $path, $logSerial, $now));

		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * ダウンロードログ書き込み
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @return bool					true=ログ出力完了、false=失敗
	 */
	function writeDownloadLog($contentType, $contentId)
	{
		global $gEnvManager;
				
		$now = date("Y/m/d H:i:s");	// 現在日時
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		$userId = $gEnvManager->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// データを追加
		$queryStr  = 'INSERT INTO _download_log ';
		$queryStr .=   '(dl_user_id, dl_content_type, dl_content_id, dl_access_log_serial, dl_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($userId, $contentType, $contentId, $logSerial, $now));

		// トランザクション終了
		return $this->endTransaction();
	}
	/**
	 * 添付ファイル情報を新規追加(仮登録)
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $fileId		ファイル識別ID(コンテンツタイプでユニーク)
	 * @param string $filePath		ファイルパス
	 * @param string $originalFilename	元のファイル名
	 * @param string $originalUrl		取得元のURL
	 * @return					true = 正常、false=異常
	 */
	function addAttachFileInfo($contentType, $fileId, $filePath, $originalFilename, $originalUrl = '')
	{
		global $gEnvManager;
		global $gAccessManager;

		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// クライアントIDを取得
		$clientId = $gAccessManager->getClientId();
		if (empty($clientId)) return false;
		
		// ファイルが登録されていないか確認
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_file_id = ? ';			// ファイルIDは全体でユニーク?
		$ret = $this->isRecordExists($queryStr, array($fileId));
		if ($ret) return false;
		
		// ファイルの情報取得
		if (!file_exists($filePath)) return false;
		$filename = strtr($originalFilename, ' ', '_');		// ファイル名の半角スペースはアンダーバーに変換
		$fileSize = filesize($filePath);
		$fileType = '';			// ファイルタイプ
		$uploadDt = date('Y/m/d H:i:s', filemtime($filePath));

		// トランザクション開始
		$this->startTransaction();
		
		// 仮登録用のコンテンツシリアル番号を取得
		$newSerial = 1;
		$queryStr  = 'SELECT MAX(af_content_serial) AS ns FROM _attach_file ';
		$queryStr .=   'WHERE af_content_type = ? ';
		$queryStr .=     'AND af_client_id = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $clientId), $row);
		if ($ret) $newSerial = $row['ns'] + 1;

		// 新規データを追加
		$queryStr  = 'INSERT INTO _attach_file (';
		$queryStr .=   'af_content_type, ';
		$queryStr .=   'af_content_serial, ';
		$queryStr .=   'af_client_id, ';
		$queryStr .=   'af_file_id, ';
		$queryStr .=   'af_filename, ';
		$queryStr .=   'af_file_type, ';
		$queryStr .=   'af_original_filename, ';
		$queryStr .=   'af_original_url, ';
		$queryStr .=   'af_file_size, ';
		$queryStr .=   'af_file_dt, ';
		$queryStr .=   'af_upload_log_serial ';
		$queryStr .= ') VALUES (';
		$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
		$queryStr .= ')';
		$ret = $this->execStatement($queryStr, array($contentType, $newSerial, $clientId, $fileId, $filename, $fileType, $originalFilename, $originalUrl, $fileSize, $uploadDt, $logSerial));

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 添付ファイル情報を更新
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID。1回目の更新時のみ必要。
	 * @param int $oldContentSerial	旧コンテンツシリアル番号
	 * @param int $contentSerial	コンテンツシリアル番号
	 * @param array $fileInfo		ファイル情報
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					true=成功、false=失敗
	 */
	public function updateAttachFileInfo($contentType, $contentId, $oldContentSerial, $contentSerial, $fileInfo, $dir)
	{
		global $gEnvManager;
		global $gAccessManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// クライアントIDを取得
		$clientId = $gAccessManager->getClientId();
		if (empty($clientId)) return false;
				
		// 変更対象の旧データ取得
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_content_type = ? AND af_file_deleted = false ';		// コンテンツタイプ
		$queryStr .=     'AND ((af_client_id = ? AND af_content_id = \'\') ';		// 仮登録ファイル
		$queryStr .=     'OR (af_client_id = \'\' AND af_content_serial = ?)) ';		// 本登録ファイル
		$retValue = $this->selectRecords($queryStr, array($contentType, $clientId, $oldContentSerial), $oldRows);
		
		// ファイル情報エラーチェック
		$oldRowCount = count($oldRows);
		for ($i = 0; $i < count($fileInfo); $i++){
			$info = $fileInfo[$i];
			$fileId = $info->fileId;
			
			for ($j = 0; $j < $oldRowCount; $j++){
				if ($fileId == $oldRows[$j]['af_file_id']) break;
			}
			if ($j == $oldRowCount) return false;		// 該当データが見つからないときは終了
		}

		// 削除ファイル取得
		$delFileId = array();
		for ($i = 0; $i < $oldRowCount; $i++){
			$oldFileId = $oldRows[$i]['af_file_id'];
			$oldFileSerial = $oldRows[$i]['af_serial'];

			for ($j = 0; $j < count($fileInfo); $j++){
				$info = $fileInfo[$j];
				$fileId = $info->fileId;
				if ($oldFileId == $fileId) break;
			}
			if ($j == count($fileInfo)){
				$delFileId[] = $oldFileId;
			}
		}
		// ファイル情報数エラーチェック
		if (count($fileInfo) != $oldRowCount - count($delFileId)) return false;
		
		// ファイル削除
		for ($i = 0; $i < count($delFileId); $i++){
			$filePath = $dir . DIRECTORY_SEPARATOR . $delFileId[$i];
			if (file_exists($filePath)) unlink($filePath);
		}
		
		// トランザクション開始
		$this->startTransaction();
		
		// ファイル情報のファイル削除フラグ更新
/*		if (count($delFileSerial) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ? ';
			$queryStr .=   'WHERE af_serial in (' . implode(',', $delFileSerial) . ') ';
			$this->execStatement($queryStr, array($now));
		}*/
		if (count($delFileId) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ?, ';
			$queryStr .=     'af_delete_log_serial = ? ';
			$queryStr .=   'WHERE af_content_type = ? AND af_file_deleted = false ';	// コンテンツタイプ
			$queryStr .=     'AND af_file_id in (' . implode(',', array_map(create_function('$a','return "\'" . $a . "\'";'), $delFileId)) . ') ';
			$this->execStatement($queryStr, array($now, $logSerial, $contentType));
		}
		
		for ($i = 0; $i < count($fileInfo); $i++){
			$info = $fileInfo[$i];
			$fileId = $info->fileId;
			
			for ($j = 0; $j < $oldRowCount; $j++){
				$oldRow = $oldRows[$j];
				if ($fileId == $oldRow['af_file_id']) break;
			}
			if ($j < $oldRowCount){
				if (empty($oldRow['af_client_id'])){		// 本登録済みのとき
					$contId = $oldRow['af_content_id'];		// コンテンツID
				} else {	// 仮登録から本登録のとき
					// 仮登録データ更新
					$queryStr  = 'UPDATE _attach_file ';
					$queryStr .=   'SET af_content_id = ? ';
					$queryStr .=   'WHERE af_serial = ? ';
					$this->execStatement($queryStr, array($contentId, $oldRow['af_serial']));
					
					$contId = $contentId;		// コンテンツID
				}
				// 新規データを追加
				$filename = $info->filename;
				$fileTitle = $info->title;
				$desc = '';
				
				$queryStr  = 'INSERT INTO _attach_file (';
				$queryStr .=   'af_content_type, ';
				$queryStr .=   'af_content_id, ';
				$queryStr .=   'af_content_serial, ';
				$queryStr .=   'af_index, ';
				$queryStr .=   'af_file_id, ';
				$queryStr .=   'af_filename, ';
				$queryStr .=   'af_title, ';
				$queryStr .=   'af_desc, ';
				$queryStr .=   'af_file_type, ';
				$queryStr .=   'af_original_filename, ';
				$queryStr .=   'af_original_url, ';
				$queryStr .=   'af_file_size, ';
				$queryStr .=   'af_file_dt, ';
				$queryStr .=   'af_upload_log_serial ';
				$queryStr .= ') VALUES (';
				$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
				$queryStr .= ')';
				$ret = $this->execStatement($queryStr, array($contentType, $contId, $contentSerial, $i,
											$fileId, $filename, $fileTitle, $desc, $oldRow['af_file_type'], $oldRow['af_original_filename'], $oldRow['af_original_url'],
											$oldRow['af_file_size'], $oldRow['af_file_dt'], $oldRow['af_upload_log_serial']));
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 添付ファイル情報を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int $contentSerial	コンテンツシリアル番号
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function getAttachFileInfo($contentType, $contentSerial, &$rows)
	{
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_content_type = ? ';
		$queryStr .=     'AND af_client_id = \'\' ';
		$queryStr .=     'AND af_content_serial = ? ';
		$queryStr .=   'ORDER BY af_index';
		$retValue = $this->selectRecords($queryStr, array($contentType, $contentSerial), $rows);
		return $retValue;
	}
	/**
	 * ファイルIDから添付ファイル情報を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $fileId		ファイルID
	 * @param array  $row			レコード
	 * @param bool $assignedOnly	本登録済みファイルのみかどうか
	 * @return bool					取得あり = true, 取得なし= false
	 */
	public function getAttachFileInfoByFileId($contentType, $fileId, &$row, $assignedOnly = true)
	{
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_content_type = ? ';
		if ($assignedOnly) $queryStr .=     'AND af_client_id = \'\' ';				// 本登録済みファイルのみの場合
		$queryStr .=     'AND af_file_id = ? ';
//		$queryStr .=   'ORDER BY af_content_serial DESC';
		$ret = $this->selectRecord($queryStr, array($contentType, $fileId), $row);
		return $ret;
	}
	/**
	 * クライアントIDで仮登録の添付ファイル情報を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $clientId		クライアントID
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function getAttachFileInfoByClientId($contentType, $clientId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_content_type = ? AND af_file_deleted = false ';		// コンテンツタイプ
		$queryStr .=     'AND (af_client_id = ? AND af_content_id = \'\') ';		// 仮登録ファイル
		$queryStr .=   'ORDER BY af_content_serial';
		$retValue = $this->selectRecords($queryStr, array($contentType, $clientId), $rows);
		return $retValue;
	}
	/**
	 * 添付ファイル情報を削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int $contentSerial	コンテンツシリアル番号
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function delAttachFileInfo($contentType, $contentSerial, $dir)
	{
		global $gEnvManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_file_deleted = false ';
		$queryStr .=     'AND af_content_type = ? ';
		$queryStr .=     'AND af_client_id = \'\' ';
		$queryStr .=     'AND af_content_serial = ?';
		$retValue = $this->selectRecords($queryStr, array($contentType, $contentSerial), $rows);
		
		// ファイル削除
		//$delFileSerial = array();
		$delFileId = array();
		for ($i = 0; $i < count($rows); $i++){
			$filePath = $dir . DIRECTORY_SEPARATOR . $rows[$i]['af_file_id'];
			if (file_exists($filePath)) unlink($filePath);
			
			//$delFileSerial[] = $rows[$i]['af_serial'];
			$fileId = $rows[$i]['af_file_id'];
			if (!in_array($fileId, $delFileId)) $delFileId[] = $rows[$i]['af_file_id'];
		}
		
		// トランザクション開始
		$this->startTransaction();
		
		// ファイル情報のファイル削除フラグ更新
/*		if (count($delFileSerial) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ? ';
			$queryStr .=   'WHERE af_serial in (' . implode(',', $delFileSerial) . ') ';
			$this->execStatement($queryStr, array($now));
		}*/
		if (count($delFileId) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ?, ';
			$queryStr .=     'af_delete_log_serial = ? ';
			$queryStr .=   'WHERE af_content_type = ? AND af_file_deleted = false ';	// コンテンツタイプ
			$queryStr .=     'AND af_file_id in (' . implode(',', array_map(create_function('$a','return "\'" . $a . "\'";'), $delFileId)) . ') ';
			$this->execStatement($queryStr, array($now, $logSerial, $contentType));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 添付ファイル情報をコンテンツIDで削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function delAttachFileInfoByContentId($contentType, $contentId, $dir)
	{
		global $gEnvManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_file_deleted = false ';
		$queryStr .=     'AND af_content_type = ? ';
		$queryStr .=     'AND af_client_id = \'\' ';
		$queryStr .=     'AND af_content_id = ?';
		$retValue = $this->selectRecords($queryStr, array($contentType, $contentId), $rows);
		
		// ファイル削除
		//$delFileSerial = array();
		$delFileId = array();
		for ($i = 0; $i < count($rows); $i++){
			$filePath = $dir . DIRECTORY_SEPARATOR . $rows[$i]['af_file_id'];
			if (file_exists($filePath)) unlink($filePath);
			
			//$delFileSerial[] = $rows[$i]['af_serial'];
			$fileId = $rows[$i]['af_file_id'];
			if (!in_array($fileId, $delFileId)) $delFileId[] = $rows[$i]['af_file_id'];
		}
		
		// トランザクション開始
		$this->startTransaction();
		
		// ファイル情報のファイル削除フラグ更新
/*		if (count($delFileSerial) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ? ';
			$queryStr .=   'WHERE af_serial in (' . implode(',', $delFileSerial) . ') ';
			$this->execStatement($queryStr, array($now));
		}*/
		if (count($delFileId) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ?, ';
			$queryStr .=     'af_delete_log_serial = ? ';
			$queryStr .=   'WHERE af_content_type = ? AND af_file_deleted = false ';	// コンテンツタイプ
			$queryStr .=     'AND af_file_id in (' . implode(',', array_map(create_function('$a','return "\'" . $a . "\'";'), $delFileId)) . ') ';
			$this->execStatement($queryStr, array($now, $logSerial, $contentType));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 仮登録の添付ファイル情報を削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function cleanAttachFileInfo($contentType, $dir)
	{
		global $gAccessManager;
		global $gEnvManager;

		$now = date("Y/m/d H:i:s");	// 現在日時
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// クライアントIDを取得
		$clientId = $gAccessManager->getClientId();
		if (empty($clientId)) return false;
		
		// 仮登録データ取得
		$queryStr  = 'SELECT * FROM _attach_file ';
		$queryStr .=   'WHERE af_content_type = ? ';
		$queryStr .=     'AND af_client_id = ? ';
		$queryStr .=     'AND af_content_id = \'\' ';		// 仮登録ファイル
		$queryStr .=     'AND af_file_deleted = false ';
		$ret = $this->selectRecords($queryStr, array($contentType, $clientId), $rows);
		
		// ファイル削除
		//$delFileSerial = array();
		$delFileId = array();
		for ($i = 0; $i < count($rows); $i++){
			$filePath = $dir . DIRECTORY_SEPARATOR . $rows[$i]['af_file_id'];
			if (file_exists($filePath)) unlink($filePath);
			
			//$delFileSerial[] = $rows[$i]['af_serial'];
			$fileId = $rows[$i]['af_file_id'];
			if (!in_array($fileId, $delFileId)) $delFileId[] = $rows[$i]['af_file_id'];
		}
		// トランザクション開始
		$this->startTransaction();
		
		// ファイル情報のファイル削除フラグ更新
/*		if (count($delFileSerial) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ? ';
			$queryStr .=   'WHERE af_serial in (' . implode(',', $delFileSerial) . ') ';
			$this->execStatement($queryStr, array($now));
		}*/
		if (count($delFileId) > 0){
			$queryStr  = 'UPDATE _attach_file ';
			$queryStr .=   'SET af_file_deleted = true, ';
			$queryStr .=     'af_file_deleted_dt = ?, ';
			$queryStr .=     'af_delete_log_serial = ? ';
			$queryStr .=   'WHERE af_content_type = ? AND af_file_deleted = false ';	// コンテンツタイプ
			$queryStr .=     'AND af_file_id in (' . implode(',', array_map(create_function('$a','return "\'" . $a . "\'";'), $delFileId)) . ') ';
			$this->execStatement($queryStr, array($now, $logSerial, $contentType));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
