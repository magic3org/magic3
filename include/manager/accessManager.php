<?php
/**
 * アクセス制御マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: accessManager.php 6084 2013-06-06 12:58:33Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class AccessManager extends Core
{
	private $db;						// DBオブジェクト
	private $_clientId;					// クライアントID(クッキー用)
	private $_oldSessionId;				// 古いセッションID
	private $_accessLogSerialNo;		// アクセスログのシリアルNo
	private $_useAutoLogin;				// 自動ログイン機能を使用するかどうか
   	const LOG_REQUEST_PARAM_MAX_LENGTH	= 1024;				// ログに保存するリクエストパラメータの最大長
	const PASSWORD_LENGTH = 8;		// パスワード長
	const SEND_PASSWORD_FORM = 'send_tmp_password';		// 仮パスワード送信用フォーム
	const TMP_PASSWORD_AVAILABLE_HOURS = 24;						// 仮パスワード有効時間
	const CF_AUTO_LOGIN = 'auto_login';			// 自動ログイン機能を使用するかどうか
	const AUTO_LOGIN_EXPIRE_DAYS = 30;			// 自動ログインの有効期間
	
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
	 * アクセスログ初期化処理(直接呼出し用)
	 *
	 * @return なし
	 */
	function initAccessLog()
	{
		if (empty($this->_accessLogSerialNo)) $this->accessLog();
	}
	/**
	 * ログイン処理
	 *
	 * ログイン処理を行う。可能な場合はユーザ情報を読み込む
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return bool   						true=アクセス可能、false=アクセス不可
	 */
	function userLogin($request)
	{
		// アカウント、パスワード取得
		$account = $request->trimValueOf('account');
		$password = $request->trimValueOf('password');
		return $this->userLoginByAccount($account, $password);
	}
	/**
	 * ログイン処理
	 *
	 * ログイン処理を行う。可能な場合はユーザ情報を読み込む
	 *
	 * @param string $account		アカウント
	 * @param string $password		パスワード
	 * @param bool $checkUser		ユーザのアクセス権をチェックするかどうか
	 * @return bool   				true=アクセス可能、false=アクセス不可
	 */
	function userLoginByAccount($account, $password, $checkUser=true)
	{
		// 初期化
		$retValue = false;
		
		// アカウントが空のときはアクセス不可
		if (empty($account)) return false;
		
		// ユーザ情報取得
		if ($this->db->getLoginUserRecord($account, $row)){
			// ******** ユーザのログインチェック             ********
			// ******** ログインのチェックを行うのはここだけ ********
			$checkLogin = false;		// ログインが通ったかどうか
			$pass  = $row['lu_password'];
			
			if ($pass == $password){
				$checkLogin = true;			// パスワード一致
			} else {
				// 仮パスワードが設定されている場合は仮パスワードをチェック
				$tmpPass = $row['lu_tmp_password'];				// 仮パスワード
				$tmpPassDt = $row['lu_tmp_pwd_dt'];			// 仮パスワード変更日時
				if (!empty($tmpPass) && $tmpPass == $password && $tmpPassDt != $this->gEnv->getInitValueOfTimestamp() && 
					strtotime('-' . self::TMP_PASSWORD_AVAILABLE_HOURS . ' hours') <= strtotime($tmpPassDt)){	// 仮パスワード有効のとき
					
					// 仮パスワード初期化
					$fieldArray = array();
					$fieldArray['lu_tmp_password'] = '';				// 仮パスワード
					$fieldArray['lu_tmp_pwd_dt'] = $this->gEnv->getInitValueOfTimestamp();			// 仮パスワード変更日時
					$ret = $this->db->updateLoginUserByField($row['lu_id'], $fieldArray, $newSerial);
					
					$checkLogin = true;			// パスワード一致
				}
			}
			
			if ($checkLogin){			// パスワード一致のとき
				// ユーザ情報を読み込み
				$retValue = $this->_loadUserInfo($row, $checkUser);
			}
		}
		// ログインのログを残す
		if ($retValue){
			// ユーザログインログを残す
			$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザがログインしました。アカウント: ' . $account, 2300,
											'account=' . $account . ', passward=' . $password . ', userid=' . $this->gEnv->getCurrentUserId(), 'account=' . $account/*検索補助データ*/);
		} else {
			// ユーザエラーログを残す
			$this->gOpeLog->writeUserError(__METHOD__, 'ユーザがログインに失敗しました。アカウント: ' . $account, 2310,
											'account=' . $account . ', passward=' . $password, 'account=' . $account/*検索補助データ*/);
			
			// 入力アカウントを保存
			//$this->db->addErrorLoginLog($account, $accessIp, $this->_accessLogSerialNo);
		}
		return $retValue;
	}
	/**
	 * ユーザ情報を読み込み
	 *
	 * @param array $row		ユーザのログインユーザ情報レコード
	 * @param bool $checkUser	ユーザのアクセス権のチェックを行うかどうか
	 * @return bool   			true=成功、false=失敗
	 */
	function _loadUserInfo($row, $checkUser = true)
	{
		global $gInstanceManager;
		global $gRequestManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$accessIp = $gRequestManager->trimServerValueOf('REMOTE_ADDR');		// アクセスIP
				
		// ユーザチェックが必要な場合は承認済みユーザのみ許可する
		if (($checkUser && $row['lu_user_type'] >= 0 && $row['lu_enable_login'] &&
			$this->_isActive($row['lu_active_start_dt'], $row['lu_active_end_dt'], $now)) || // 承認済みユーザ、ログイン可能
			!$checkUser){			// ユーザのアクセス権をチェックしない場合
			
			// ログインした場合はセッションIDを変更する
			$this->setOldSessionId(session_id());		// 古いセッションIDを保存
			session_regenerate_id(true);
	
			// ユーザ情報オブジェクトを作成
			$userInfo = new UserInfo();
			$userInfo->userId	= $row['lu_id'];		// ユーザID
			$userInfo->account	= $row['lu_account'];	// ユーザアカウント
			$userInfo->name		= $row['lu_name'];		// ユーザ名
			$userInfo->email	= $row['lu_email'];		// Eメール
			$userInfo->userType	= $row['lu_user_type'];	// ユーザタイプ
			$userInfo->userTypeOption = $row['lu_user_type_option'];			// ユーザタイプオプション
			$userInfo->_recordSerial = $row['lu_serial'];	// レコードシリアル番号
		
			// アクセス可能ウィジェット(システム運用者の場合)
			$userInfo->adminWidget = array();
			if ($userInfo->userType == UserInfo::USER_TYPE_MANAGER){	// システム運用可能ユーザのとき
				$adminWidget = trim($row['lu_admin_widget']);
				if (!empty($adminWidget)) $userInfo->adminWidget = explode(',', $adminWidget);
			}
			
			// システム運用可能ユーザの場合は、管理者キーを発行(2011/9/16 修正)
			if ($userInfo->userType >= UserInfo::USER_TYPE_MANAGER){	// システム運用可能ユーザのとき
				$adminKey = $this->createAdminKey();
				if ($this->db->addAdminKey($adminKey, $accessIp)) $userInfo->_adminKey = $adminKey;
			} else {
				$userInfo->_adminKey = '';
			}
		
			// インスタンスマネージャーとセッションに保存
			$gInstanceManager->setUserInfo($userInfo);
			$gRequestManager->setSessionValueWithSerialize(M3_SESSION_USER_INFO, $userInfo);
		
			// ログインのログを残す
			$this->db->updateLoginLog($userInfo->userId, $this->_accessLogSerialNo);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 自動ログイン開始処理
	 *
	 * @return bool   				true=ログイン成功、false=ログイン不可
	 */
	function startAutoLogin()
	{
		global $gSystemManager;
		global $gRequestManager;
		
		// 自動ログイン機能を使用するかどうか
		$value = $gSystemManager->getSystemConfig(self::CF_AUTO_LOGIN);
		$this->_useAutoLogin = isset($value) ? $value : '0';
		if (!$this->_useAutoLogin) return false;
		
		// ログイン中でない場合のみ自動ログイン処理を行う
		$userId = $this->gEnv->getCurrentUserId();
		if ($userId > 0) return false;

		// 自動ログイン処理。自動ログイン情報がある場合は自動ログインクッキーも存在する
		$loginKey = $gRequestManager->getCookieValue(M3_COOKIE_AUTO_LOGIN);		// 自動ログインキー
		$clientId = $this->getClientId();		// クライアントID
		if (empty($loginKey) || empty($clientId)) return false;			// 空の場合は問題なし
		$ret = $this->db->getAutoLogin($loginKey, $clientId, $row);
		if (!$ret){		// 自動ログイン情報がない場合は終了
			// 自動ログイン情報を削除
			$this->_removeAutoLogin($loginKey);
			
			// ユーザエラーログを残す
			$errMsg = '不正な自動ログインを検出しました。ログインキー: ' . $loginKey;
			$msgDetail = 'クライアントID=' . $clientId;
			$this->gOpeLog->writeUserError(__METHOD__, $errMsg, 2310, $msgDetail);
			return false;
		}
		// 自動ログインの有効期間をチェック
		$expireDt = $row['ag_expire_dt'];
		$now = date("Y/m/d H:i:s");	// 現在日時
		if ($expireDt == $this->gEnv->getInitValueOfTimestamp() || strtotime($expireDt) < strtotime($now)){		// 期限切れのとき
			// 自動ログイン情報を削除
			$this->_removeAutoLogin($loginKey);
			return false;
		}

		// ユーザ情報を取得
		$ret = $this->db->getLoginUserRecordById($row['ag_user_id'], $userInfoRow);
		if (!$ret){
			// 自動ログイン情報を削除
			$this->_removeAutoLogin($loginKey);
			
			$errMsg = '自動ログインエラー(要因=ユーザ情報取得失敗)';
			$msgDetail = 'ユーザID=' . $row['ag_user_id'];
			$this->gOpeLog->writeError(__METHOD__, $errMsg, 1100, $msgDetail);
			return false;
		}
		
		// ユーザレベルをチェック。システム運用可能ユーザは自動ログイン不可。
		// アクセスパスをチェック?
		if ($userInfoRow['lu_user_type'] >= UserInfo::USER_TYPE_MANAGER) return false;	// システム運用可能ユーザのとき
		
		// ユーザ情報を読み込む
		$ret = $this->_loadUserInfo($userInfoRow);
		$userId = $userInfoRow['lu_id'];		// ユーザID再取得
		$account = $userInfoRow['lu_account'];
		if ($ret){// ログイン成功
			// ユーザログインログを残す
			$errMsg = 'ユーザが自動ログインしました。アカウント: ' . $account;
			$msgDetail = 'ログインキー=' . $loginKey . ', クライアントID=' . $clientId;
			$this->gOpeLog->writeUserInfo(__METHOD__, $errMsg, 2302/*自動ログイン*/, $msgDetail, 'account=' . $account/*検索補助データ*/);
		} else {
			// 自動ログイン情報を削除
			$this->_removeAutoLogin($loginKey);
			
			// ユーザエラーログを残す
			$errMsg = 'ユーザが自動ログインに失敗しました。アカウント: ' . $account;
			$msgDetail = 'ログインキー=' . $loginKey . ', クライアントID=' . $clientId;
			$this->gOpeLog->writeUserError(__METHOD__, $errMsg, 2310, $msgDetail, 'account=' . $account/*検索補助データ*/);
			return false;
		}
		// 新規のログインキーを作成
		$newLoginKey = md5($account . $clientId . time());
		$ret = $this->_createAutoLogin($newLoginKey, $userId, $clientId);
		if (!$ret){
			$errMsg = '自動ログインエラー(要因=自動ログイン情報作成失敗)';
			$msgDetail = 'ログインキー=' . $newLoginKey . ',ユーザID=' . $userId . ',クライアントID=' . $clientId;
			$this->gOpeLog->writeError(__METHOD__, $errMsg, 1100, $msgDetail);
		}
		return true;
	}
	/**
	 * 自動ログイン終了処理
	 *
	 * @return bool   				true=成功、false=失敗
	 */
	function endAutoLogin()
	{
		if (!$this->_useAutoLogin) return;
	}
	/**
	 * 自動ログイン処理
	 *
	 * @param int $userId			ユーザID
	 * @param bool $isAutoLogin		true=自動ログイン開始、false=自動ログイン終了
	 * @return bool   				true=自動ログイン完了、false=自動ログイン失敗
	 */
	function userAutoLogin($userId, $isAutoLogin)
	{
		// 自動ログイン行わない場合は終了
		if (!$this->_useAutoLogin) return false;
		
		$clientId = $this->getClientId();		// クライアントID

		if ($isAutoLogin){
			// 引数エラーチェック
			if ($userId <= 0) return false;
			if (empty($clientId)) return false;
		
			// ユーザ情報取得
			$userInfo = $this->gEnv->getCurrentUserInfo();

			// ユーザレベルをチェック。システム運用可能ユーザは自動ログイン不可。
			if ($userInfo->userType >= UserInfo::USER_TYPE_MANAGER) return false;	// システム運用可能ユーザのとき
			
			// 新規のログインキーを作成
			$newLoginKey = md5($userInfo->account . $clientId . time());
			$ret = $this->_createAutoLogin($newLoginKey, $userId, $clientId);
			if (!$ret){
				$errMsg = '自動ログインエラー(要因=自動ログイン情報作成失敗)';
				$msgDetail = 'ログインキー=' . $newLoginKey . ',ユーザID=' . $userId . ',クライアントID=' . $clientId;
				$this->gOpeLog->writeError(__METHOD__, $errMsg, 1100, $msgDetail);
			}
		} else {
			// クッキー削除は必ず行うため引数エラーチェックはしない
			// 自動ログイン情報を削除
			$loginKey = $this->db->getAutoLoginKey($userId, $clientId);
			$ret = $this->_removeAutoLogin($loginKey);
		}
		return $ret;
	}
	/**
	 * 自動ログイン情報作成
	 *
	 * @param string $loginKey	ログインキー
	 * @param int    $userId	ユーザID
	 * @param string $clientId	クライアントID
	 * @return bool   			true=成功、false=失敗
	 */
	function _createAutoLogin($loginKey, $userId, $clientId)
	{
		global $gRequestManager;
		
		// クッキーを作成
		//$gRequestManager->setCookieValue(M3_COOKIE_AUTO_LOGIN, '', -1);		// 一旦削除
		$gRequestManager->setCookieValue(M3_COOKIE_AUTO_LOGIN, $loginKey, self::AUTO_LOGIN_EXPIRE_DAYS);
		
		// 自動ログイン情報を更新
		$accessPath = $this->gEnv->getAccessPath();
		$expireDt = date("Y/m/d H:i:s", strtotime(self::AUTO_LOGIN_EXPIRE_DAYS . ' day'));
		$ret = $this->db->updateAutoLogin($userId, $loginKey, $clientId, $accessPath, $expireDt);
		return $ret;
	}
	/**
	 * 自動ログイン情報削除
	 *
	 * @param string $loginKey	ログインキー
	 * @return bool   			true=成功、false=失敗
	 */
	function _removeAutoLogin($loginKey)
	{
		global $gRequestManager;
		
		// クッキーを削除
		$gRequestManager->setCookieValue(M3_COOKIE_AUTO_LOGIN, '', -1);
		
		// 自動ログイン情報削除
		$ret = $this->db->delAutoLogin($loginKey);
		return $ret;
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function _isActive($startDt, $endDt, $now)
	{
		$isActive = false;		// 公開状態

		if ($startDt == $this->gEnv->getInitValueOfTimestamp() && $endDt == $this->gEnv->getInitValueOfTimestamp()){
			$isActive = true;		// 公開状態
		} else if ($startDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		} else if ($endDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) >= strtotime($startDt)) $isActive = true;		// 公開状態
		} else {
			if (strtotime($startDt) <= strtotime($now) && strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		}
		return $isActive;
	}
	/**
	 * ユーザログアウト処理
	 *
	 * @param bool $delSession		セッションを削除するかどうか
	 */
	function userLogout($delSession = false)
	{
		// ユーザ情報取得
		$userInfo = $this->gEnv->getCurrentUserInfo();
		
		// ##### 自動ログイン情報を削除 #####
		$this->userAutoLogin($userInfo->userId, false/*自動ログイン情報削除*/);
		
		// ログアウトログを残す
		if (!is_null($userInfo)){
			$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザがログアウトしました。アカウント: ' . $userInfo->account, 2301,
											'account=' . $userInfo->account . ', userid=' . $userInfo->userId, 'account=' . $userInfo->account/*検索補助データ*/);
		}
		
		// ログアウトしたとき、管理者のセッション値は削除。一般ユーザの場合はログアウトしても残しておくセッション値があるので(テンプレート等)ユーザ情報のみ削除。
		// セッション終了
		if ($delSession){
//			session_start();
			session_destroy();
		}
			
		// ユーザ情報を削除
		$this->gInstance->setUserInfo(null);
	}
	/**
	 * アクセスログの記録
	 *
	 * アクセスログを記録する
	 *
	 * @return なし
	 */
	function accessLog()
	{
		global $gRequestManager;
		global $gEnvManager;
		global $gAccessManager;
		global $gInstanceManager;
		
		// ユーザ情報が存在しているときは、ユーザIDを登録する
		$userId = 0;
		$userInfo = $gEnvManager->getCurrentUserInfo();
		if (!is_null($userInfo)){		// ユーザ情報がある場合
			$userId = $userInfo->userId;
		}
		$cookieVal	= isset($this->_clientId) ? $this->_clientId : '';			// アクセス管理用クッキー
		$session	= session_id();				// セッションID
		//$ip			= $gRequestManager->trimServerValueOf('REMOTE_ADDR');		// クライアントIP
		$ip			= $this->_getClientIp($gRequestManager);		// クライアントIP
		$method		= $gRequestManager->trimServerValueOf('REQUEST_METHOD');	// アクセスメソッド
		$uri		= $gRequestManager->trimServerValueOf('REQUEST_URI');
		$referer	= $gRequestManager->trimServerValueOf('HTTP_REFERER');
		$agent		= $gRequestManager->trimServerValueOf('HTTP_USER_AGENT');		// クライアントアプリケーション
		$language	= $gRequestManager->trimServerValueOf('HTTP_ACCEPT_LANGUAGE');	// クライアント認識可能言語
		
		$request = '';
		foreach ($_REQUEST as $strKey => $strValue ) {
			$request .= sprintf("%s=%s" . M3_TB, $strKey, $strValue);		// タブ区切り
		}
		$request = rtrim($request, M3_TB);		// 最後のタブを消去
		$request = substr($request, 0, self::LOG_REQUEST_PARAM_MAX_LENGTH);	// 格納長を制限
		
		// アクセスパスを取得
		$path = $gEnvManager->getAccessPath();
		
		// アクセスの種別を取得
		$isCookie = !empty($_COOKIE);			// クッキーがあるかどうか
		$isCrawler = false;				// クローラかどうか
		
		// アクセスログのシリアルNoを保存
		$this->_accessLogSerialNo = $this->db->accessLog($userId, $cookieVal, $session, $ip, $method, $uri, $referer, $request, $agent, $language, $path, $isCookie, $isCrawler);
		
		// 即時アクセス解析
		if (M3_SYSTEM_REALTIME_ANALYTICS) $gInstanceManager->getAnalyzeManager()->realtimeAnalytics($this->_accessLogSerialNo, $cookieVal);
	}
	/**
	 * アクセス元のIPを取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return string						IPアドレス
	 */
	function _getClientIp($request)
	{
		$remoteIp		= $request->trimServerValueOf('REMOTE_ADDR');
		$forwardedStr	= $request->trimServerValueOf('HTTP_X_FORWARDED_FOR');
		if (empty($forwardedStr)) return $remoteIp;
		
		$candidateIpArray = array_reverse(explode(',', str_replace(' ', '', $forwardedStr) . ',' . $remoteIp));
		foreach ($candidateIpArray as $ip){
			if (!empty($ip) && !preg_match("/^(172\.(1[6-9]|2[0-9]|30|31)|192\.168|10|127)\./", $ip)) return $ip;
		}
		return $remoteIp;
	}
	/**
	 * アクセスログユーザの記録
	 *
	 * アクセスログユーザを記録する
	 *
	 * @return なし
	 */
	function accessLogUser()
	{
		global $gEnvManager;
				
		$userId = 0;
		$userInfo = $gEnvManager->getCurrentUserInfo();
		if (!is_null($userInfo)){		// ユーザ情報がある場合
			$userId = $userInfo->userId;
		}
		// ユーザ情報が存在しているときは、ユーザIDを登録する
		if ($userId != 0) $this->db->updateAccessLogUser($this->_accessLogSerialNo, $userId);
	}
	/**
	 * クッキーに保存するクライアントIDを生成
	 */
	function createClientId()
	{
		global $gRequestManager;
			
		// アクセスログの最大シリアルNoを取得
		$max = $this->db->getMaxSerialOfAccessLog();
		$this->_clientId = md5(time() . $gRequestManager->trimServerValueOf('REMOTE_ADDR') . ($max + 1));
		return $this->_clientId;				// クライアントID(クッキー用)
	}
	/**
	 * クッキーから取得したクライアントIDを設定
	 *
	 * @param string $clientId		クライアントID
	 */
	function setClientId($clientId)
	{
		$this->_clientId = $clientId;				// クライアントID(クッキー用)
	}
	/**
	 * クッキーから取得したクライアントIDを取得
	 *
	 * @return string		クライアントID
	 */
	function getClientId()
	{
		return $this->_clientId;				// クライアントID(クッキー用)
	}
	/**
	 * 管理者用一時キーを作成
	 */
	function createAdminKey()
	{
		global $gRequestManager;
			
		return md5($gRequestManager->trimServerValueOf('REMOTE_ADDR') . time());
	}
	/**
	 * 変更前のセッションIDを設定
	 *
	 * @param string $sessionId		セッションID
	 */
	function setOldSessionId($sessionId)
	{
		$this->_oldSessionId = $sessionId;
	}
	
	/**
	 * アクセスログのシリアルNoを取得
	 *
	 * @return int		シリアルNo
	 */
	function getAccessLogSerialNo()
	{
		return $this->_accessLogSerialNo;
	}
	/**
	 * システム管理者の認証が完了しているかどうか判断(フレームワーク以外のアクセス制御用)
	 *
	 * @return bool		true=完了、false=未完了
	 */
	function loginedByAdmin()
	{
		global $gRequestManager;
		global $gSystemManager;
		
		// セッション変数を取得可能にする
		session_cache_limiter('none');		// IE対策(暫定対応20070703)
		session_start();
		
		// セッションを再生成する(セキュリティ対策)
		if ($gSystemManager->regenerateSessionId()){
			$this->setOldSessionId(session_id());		// 古いセッションIDを保存
			session_regenerate_id(true);
		}

		// セッションからユーザ情報を取得
		// セッションが残っていない場合がある(IEのみ)→アクセスできない(原因不明)
		$userInfo = $gRequestManager->getSessionValueWithSerialize(M3_SESSION_USER_INFO);
		if (is_null($userInfo)) return false;		// ログインしていない場合

		if ($userInfo->isSystemAdmin()){	// システム管理者の場合
			return true;
		} else {
			return false;
		}
	}
	/**
	 * ユーザの認証が完了しているかどうか判断(フレームワーク以外のアクセス制御用)
	 *
	 * @return bool		true=完了、false=未完了
	 */
	function loginedByUser()
	{
		global $gRequestManager;
		global $gSystemManager;
		global $gInstanceManager;
		
		// セッション変数を取得可能にする
		session_cache_limiter('none');		// IE対策(暫定対応20070703)
		session_start();
		
		// セッションを再生成する(セキュリティ対策)
		if ($gSystemManager->regenerateSessionId()){
			$this->setOldSessionId(session_id());		// 古いセッションIDを保存
			session_regenerate_id(true);
		}

		// セッションからユーザ情報を取得
		// セッションが残っていない場合がある(IEのみ)→アクセスできない(原因不明)
		$userInfo = $gRequestManager->getSessionValueWithSerialize(M3_SESSION_USER_INFO);
		if (is_null($userInfo)) return false;		// ログインしていない場合
		
		// ユーザ情報を保存
		$gInstanceManager->setUserInfo($userInfo);
		
		return true;
		/*if ($userInfo->userType >= UserInfo::USER_TYPE_MANAGER){	// システム運用可能ユーザのとき
			return true;
		} else {
			return false;
		}*/
	}
	/**
	 * 管理者認証用一時キーが存在するかどうか
	 *
	 * @return bool			true=完了、false=未完了
	 */
	function isValidAdminKey()
	{
		global $gRequestManager;
		static $isValidAdminKey;
		
		if (!isset($isValidAdminKey)){
			// 管理者一時キーを取得
			//$adminKey = $request->trimValueOf(M3_REQUEST_PARAM_ADMIN_KEY);
			$adminKey = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_ADMIN_KEY);
			if (empty($adminKey)){
				$isValidAdminKey = false;
			} else {
				//$ret = $this->db->isValidAdminKey($adminKey, $request->trimServerValueOf('REMOTE_ADDR'));
				$isValidAdminKey = $this->db->isValidAdminKey($adminKey, $gRequestManager->trimServerValueOf('REMOTE_ADDR'));
			}
		}
		return $isValidAdminKey;
	}
	/**
	 * URL用のパラメータとして使用するセッションIDを取得
	 *
	 * @return string			セッションIDパラメータ文字列
	 */	
	function getSessionIdUrlParam()
	{
		return session_name() . '=' . session_id();
	}
	/**
	 * 仮パスワードを発行して送信
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return bool   						true=送信完了、false=送信失敗
	 */	
	function sendPassword($request)
	{
		global $gEnvManager;
		
		$account = $request->trimValueOf('account');
		$inputEmail = $request->trimValueOf('email');		// 送信先Eメール
		
		$isSend = false;
		$errMessage = '';
		$errMessageDetail = '';
		if ($this->db->getLoginUserRecord($account, $row, true/*有効なユーザのみ*/)){		// アカウントからログインIDを取得
			$siteEmail = $gEnvManager->getSiteEmail();
			$email = $row['lu_email'];
			
			// 送信先Eメールアドレスをチェック
			if (!empty($inputEmail) && $inputEmail == $email){
				// 送信先が設定されているかチェック
				if (!empty($email) && !empty($siteEmail)){
					$now = date("Y/m/d H:i:s");	// 現在日時
				
					// パスワード作成
					$password = makePassword(self::PASSWORD_LENGTH);
				
					// 仮パスワードと発行日時を更新
					$fieldArray = array();
					$fieldArray['lu_tmp_password'] = md5($password);				// 仮パスワード
					$fieldArray['lu_tmp_pwd_dt'] = $now;			// 仮パスワード変更日時
					$ret = $this->db->updateLoginUserByField($row['lu_id'], $fieldArray, $newSerial);
					if ($ret){
						$fromAddress	= $siteEmail;		// 送信元アドレス
						$toAddress		= $email;			// 送信先アドレス
						$mailParam = array();
						$mailParam['PASSWORD'] = $password;
						$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', self::SEND_PASSWORD_FORM, $mailParam);// 自動送信
						$isSend = true;		// 送信完了
					}
				} else {
					$errMessageDetail = 'サイトEメールまたはアカウントの送信先Eメールが設定されていません。';
				}
			} else {
				$errMessage = '仮パスワード送信への不正なアクセスを検出しました。アカウント: ' . $account . ', Eメール: ' . $inputEmail;
			}
		} else {
			$errMessageDetail = '登録されていないアカウント、または、アカウントが承認済み、ログイン可、有効期間内になっていません。';
		}
		if ($isSend){
			$this->gOpeLog->writeUserInfo(__METHOD__, '仮パスワードを送信しました。アカウント: ' . $account, 2100);
		} else {
			if (empty($errMessage)) $errMessage = '仮パスワード送信に失敗しました。アカウント: ' . $account;
			$this->gOpeLog->writeUserError(__METHOD__, $errMessage, 2200, $errMessageDetail);
		}
	}
}
?>
