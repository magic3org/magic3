<?php
/**
 * コンテナクラス
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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_serverDb.php');

class admin_mainAccesslogWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serverDb;		// DB接続オブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $clientIp;			// クライアントのIPアドレス
	private $path;				// アクセスパス
	private $showMessage;		// メッセージ画面かどうか
	private $message;			// 表示メッセージ
	private $server;			// 指定サーバ
	private $startDt;			// 検索範囲開始日付
	private $endDt;				// 検索範囲終了日付
	private $browserTypeInfo;	// ブラウザ情報(再利用)
	private $osInfo;			// プラットフォーム情報(再利用)
	private $developMode;			// 開発モードかどうか
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 10;			// リンクページ数
	const INFO_ICON_FILE = '/images/system/info16.png';			// 情報アイコン
	const NOTICE_ICON_FILE = '/images/system/notice16.png';		// 注意アイコン
	const ERROR_ICON_FILE = '/images/system/error16.png';		// エラーアイコン
	const FLAG_ICON_DIR = '/images/system/flag/';		// 国旗アイコンディレクトリ
	const BROWSER_ICON_DIR = '/images/system/browser/';		// ブラウザアイコンディレクトリ
	const OS_ICON_DIR = '/images/system/os/';		// OSアイコンディレクトリ
	const ICON_SIZE = 16;		// アイコンのサイズ
	const AVATAR_ICON_SIZE = 32;		// ユーザアバターアイコンサイズ
	const DEFAULT_LOG_LEVEL = '0';		// デフォルトのログレベル
	const DEFAULT_LOG_STATUS = '1';		// デフォルトのログステータス
	const DEFAULT_ACCESS_PATH = 'index';		// デフォルトのアクセスパス(PC用アクセスポイント)
	const ACCESS_PATH_ALL = '_all';				// アクセスパスすべて選択
	const ACCESS_PATH_OTHER = '_other';				// アクセスパスその他
	const CF_PERMIT_DETAIL_CONFIG	= 'permit_detail_config';				// 開発モードかどうか
	
	// カレンダー用スクリプト
	const CALENDAR_SCRIPT_FILE = '/jscalendar-1.0/calendar.js';		// カレンダースクリプトファイル
	const CALENDAR_LANG_FILE = '/jscalendar-1.0/lang/calendar-ja.js';	// カレンダー言語ファイル
	const CALENDAR_SETUP_FILE = '/jscalendar-1.0/calendar-setup.js';	// カレンダーセットアップファイル
	const CALENDAR_CSS_FILE = '/jscalendar-1.0/calendar-win2k-1.css';		// カレンダー用CSSファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		$this->serverDb = new admin_serverDb();
		
		$this->developMode = $this->gSystem->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);	// 開発モード
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{
		// サーバ指定されている場合は接続先DBを変更
		$this->server = $request->trimValueOf(M3_REQUEST_PARAM_SERVER);
		if (!empty($this->server)){
			// 設定データを取得
			$ret = $this->serverDb->getServerById($this->server, $row);
			if ($ret){
				$dbDsn = $row['ts_db_connect_dsn'];		// DB接続情報
				$dbAccount = $row['ts_db_account'];	// DB接続アカウント
				$dbPassword = $row['ts_db_password'];// DB接続パスワード

				// テスト用DBオブジェクト作成
				$ret = $this->db->openLocalDb($dbDsn, $dbAccount, $dbPassword);// 接続先を変更
			}
			if (!$ret){		// サーバに接続できない場合
				$this->showMessage = true;		// メッセージ画面かどうか
				$this->message = 'サーバに接続できません';
				return 'message.tmpl.html';
			}
		}
		
		$task = $request->trimValueOf('task');
		if ($task == 'accesslog_detail'){		// 詳細画面
			return 'accesslog_detail.tmpl.html';
		} else {			// 一覧画面
			return 'accesslog.tmpl.html';
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		if ($this->showMessage){		// メッセージ画面かどうか
			$this->setMsg(self::MSG_APP_ERR, $this->message);
			return;
		}
		$task = $request->trimValueOf('task');
		if ($task == 'accesslog_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addScriptFileToHead($request, &$param)
	{
		$scriptArray = array($this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_SCRIPT_FILE),		// カレンダースクリプトファイル
							$this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_LANG_FILE),	// カレンダー言語ファイル
							$this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_SETUP_FILE));	// カレンダーセットアップファイル
		return $scriptArray;

	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_CSS_FILE);
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$this->clientIp = $this->gRequest->trimServerValueOf('REMOTE_ADDR');		// クライアントのIPアドレス
		$act = $request->trimValueOf('act');
		$this->path = $request->trimValueOf('path');		// アクセスパス
		if (empty($this->path)) $this->path = self::DEFAULT_ACCESS_PATH;

		// 期間指定を取得
		$this->startDt = $request->trimValueOf('start_date');		// 検索範囲開始日付
		if (!empty($this->startDt)) $this->startDt = $this->convertToProperDate($this->startDt);
		$this->endDt = $request->trimValueOf('end_date');			// 検索範囲終了日付
		if (!empty($this->endDt)) $this->endDt = $this->convertToProperDate($this->endDt);
		$endNextDt = $this->endDt;
		if (!empty($endNextDt)) $endNextDt = $this->getNextDay($endNextDt);			// 翌日を取得
		
		// アクセス元IPを取得
		$ip = $request->trimValueOf('ip');
		
		// 表示条件
		$maxListCount = $request->trimIntValueOf('viewcount', '0');
		if (empty($maxListCount)) $maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 入力データのエラーチェック
		if (!empty($this->startDt) && !empty($this->endDt) && $this->startDt > $this->endDt){
			$this->setUserErrorMsg('期間の指定範囲にエラーがあります。');
		}
		if (!empty($ip)){
			$value = filter_var($ip, FILTER_VALIDATE_IP);
			if ($value === false) $this->setUserErrorMsg('IPの指定にエラーがあります。');
		}
		
		// 総数を取得
		$pathParam = $this->path;
		if ($pathParam == self::ACCESS_PATH_ALL){
			$pathParam = NULL;
		} else if ($pathParam == self::ACCESS_PATH_OTHER){		// その他のパス
			$pathParam = '';
		}
		$totalCount = $this->db->getAccessLogCount($pathParam, $this->startDt, $endNextDt, $ip);
		
		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		
		// ページングリンク作成
		$detailUrl = '?task=accesslog&path=' . $this->path;
		if (!empty($this->startDt)) $detailUrl .= '&start_date=' . $this->startDt;	// 検索範囲開始日付
		if (!empty($this->endDt)) $detailUrl .= '&end_date=' . $this->endDt;		// 検索範囲終了日付
		if (!empty($ip)) $detailUrl .= '&ip=' . $ip;		// アクセス元IP
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $detailUrl);
		
		// アクセスパスメニュー作成
		$this->createPathMenu();

		// 値を画面に埋め込む
		$this->tmpl->addVar("_widget", "edit_url", '?task=accesslog_detail');// アクセスログ詳細URL
		$this->tmpl->addVar("_widget", "start_date", $this->startDt);	// 開始日付
		$this->tmpl->addVar("_widget", "end_date", $this->endDt);	// 終了日付
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "view_count", $maxListCount);	// 最大表示項目数
		
		// アクセスログを取得
		$this->db->getAccessLogList($maxListCount, $pageNo, $pathParam, array($this, 'logListLoop'), $this->startDt, $endNextDt, $ip);
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist', 'visibility', 'hidden');		// ログがないときは非表示
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createDetail($request)
	{
		// 表示条件
		$maxListCount = $request->trimValueOf('viewcount');// 表示項目数
		$page = $request->trimValueOf('page');				// ページ番号
		$path = $request->trimValueOf('path');				// アクセスパス
		$startDt = $request->trimValueOf('start_date');		// 検索範囲開始日付
		$endDt = $request->trimValueOf('end_date');			// 検索範囲終了日付
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$requestParamStr = '';			// 受信リクエスト
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'gethostname'){		// ホスト名取得のとき
			// 設定データを取得
			$ret = $this->db->getAccessLog($this->serialNo, $row);
			if ($ret){
				$ip = $row['al_ip'];
				
				// ホスト名取得
				$hostname = gethostbyaddr($ip);
				if ($hostname === false || $hostname == $ip){			// ホスト名が取得できないとき
					$hostname = '<span style="color:red">[取得不可]</span>';
				} else {		// ホスト名が取得できた場合は正引きでIPをチェック
					$ret = $this->checkHostname($hostname, $ip);
					if ($ret){
						$hostname = $this->convertToDispString($hostname);
					} else {
						$hostname = '<span style="color:red">[正引き失敗]</span>';
					}
				}
			}
			
			$reloadData = true;		// データの再読み込み
		} else {
			if (!empty($this->serialNo)) $reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			// 設定データを取得
			$ret = $this->db->getAccessLog($this->serialNo, $row);
			if ($ret){
				$userName = $row['lu_name'];
				$userId = $row['lu_id'];
				$uri = $row['al_uri'];
				$referer = $row['al_referer'];
				$ip = $row['al_ip'];
				$requestParam = $row['al_request'];
				$agent = $row['al_user_agent'];
				$language = $row['al_accept_language'];
				$method = $row['al_method'];
				$cookie = $row['al_cookie_value'];
				$time = $this->convertToDispDateTime($row['al_dt']);	// 出力日時
				
				// 表示用の文字列に変換
				if (!empty($requestParam)){
					if (strncmp($requestParam, '[', 1) == 0){			// 先頭に「[」がある「;」区切りバージョンのとき(v1.7.18以前)
						$requestParamStr = $this->convertToDispString($requestParam);
					} else {		// タブ区切りバージョンのとき(v1.7.19以降)
						$requestParamArray = explode(M3_TB, $requestParam);
						for ($i = 0; $i < count($requestParamArray); $i++){
							$line = trim($requestParamArray[$i]);
							if (empty($line)) continue;
						
							$pos = strpos($line, '=');
							if ($pos === false){
							} else {
								$key = substr($line, 0, $pos);
								$value = substr($line, $pos + 1);
								$requestParamStr .= $this->convertToDispString($key) . '=<b>[</b>' . $this->convertToDispString($value) . '<b>]</b><br />';
							}
						}
					}
				}

				// ブラウザ、プラットフォームの情報を取得
				$browserTypeInfo = $this->gInstance->getAnalyzeManager()->getBrowserType($agent);
				$browserImg = '';
				if (!empty($browserTypeInfo)){
					$iconFile = $browserTypeInfo['icon'];
					if (!empty($iconFile)){
						$iconTitle = $browserTypeInfo['name'];
						$iconUrl = $this->gEnv->getRootUrl() . self::BROWSER_ICON_DIR . $iconFile;
						$browserImg = '<img src="' . $this->getUrl($iconUrl) . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					}
				}
		
				// アクセスユーザの国を取得
				$countryCode = '';
				if (!empty($language)) $countryCode = $this->gInstance->getAnalyzeManager()->getBrowserCountryCode($language);
		
				$countryImg = '';
				if (!empty($countryCode)){
					$iconTitle = $countryCode;
					$iconUrl = $this->gEnv->getRootUrl() . self::FLAG_ICON_DIR . $countryCode . '.png';
					$countryImg = '<img src="' . $this->getUrl($iconUrl) . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
				}
		
				$osImg = '';			// OS
				$osInfo = $this->gInstance->getAnalyzeManager()->getPlatformType($agent);
/*				if (!empty($osCode)){
					$iconFile = $this->osIconFile[$osCode];	// OSアイコンファイル名
					if (!empty($iconFile)){
						$iconTitle = $osCode;
						$iconUrl = $this->gEnv->getRootUrl() . self::OS_ICON_DIR . $iconFile;
						$osImg = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					}
				}*/
				if (!empty($osInfo)){
					$iconFile = $osInfo['icon'];	// OSアイコンファイル名
					if (!empty($iconFile)){
						$iconTitle = $osInfo['name'] . ' ' . $osInfo['version_name'];
						$iconUrl = $this->gEnv->getRootUrl() . self::OS_ICON_DIR . $iconFile;
						$osImg = '<img src="' . $this->getUrl($iconUrl) . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					}
				}
			}
		}
		if (empty($userId)){		// ユーザが取得できないとき
			$user = '[取得不可]';
		} else {
			$user = $this->convertToDispString($userName . '(' . $userId . ')');
		}
		
		// ホスト名設定
		if (!empty($hostname)){
			$this->tmpl->setAttribute('show_hostname', 'visibility', 'visible');
			$this->tmpl->addVar("show_hostname", "hostname", $hostname);
			
			// 「取得」ボタンを非表示にする
			$this->tmpl->setAttribute('show_hostname_button', 'visibility', 'hidden');
		}
		
		// 取得データを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "user", $user);
		$this->tmpl->addVar("_widget", "uri", $this->convertToDispString($uri));
		$this->tmpl->addVar("_widget", "uri_title", $this->convertToDispString(urldecode($uri)));		// URIデコードタイトル
		$this->tmpl->addVar("_widget", "referer", $this->convertToDispString($referer));
		$this->tmpl->addVar("_widget", "referer_title", $this->convertToDispString(urldecode($referer)));	// リファラーデコードタイトル
		$this->tmpl->addVar("_widget", "ip", $this->convertToDispString($ip));
		$this->tmpl->addVar("_widget", "request", $requestParamStr);
		$this->tmpl->addVar("_widget", "agent", $this->convertToDispString($agent));
		$this->tmpl->addVar("_widget", "browser", $browserImg);
		$this->tmpl->addVar("_widget", "os", $osImg);
		$this->tmpl->addVar("_widget", "language", $this->convertToDispString($language));
		$this->tmpl->addVar("_widget", "country", $countryImg);
		$this->tmpl->addVar("_widget", "method", $this->convertToDispString($method));
		$this->tmpl->addVar("_widget", "cookie", $this->convertToDispString($cookie));
		$this->tmpl->addVar("_widget", "time", $time);

		// 一覧の表示条件
		$this->tmpl->addVar("_widget", "page", $page);	// ページ番号
		$this->tmpl->addVar("_widget", "view_count", $maxListCount);	// 最大表示項目数
		$this->tmpl->addVar("_widget", "path", $path);	// アクセスパス
		$this->tmpl->addVar("_widget", "start_date", $startDt);	// 開始日付
		$this->tmpl->addVar("_widget", "end_date", $endDt);	// 終了日付
		
		// 閉じるボタンの表示制御
		if ($openBy == 'simple') $this->tmpl->setAttribute('cancel_button', 'visibility', 'hidden');		// 詳細画面のみの表示のときは戻るボタンを隠す
	}
	/**
	 * アクセスログ一覧取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function logListLoop($index, $fetchedRow, $param)
	{
		static $savedAgent;		// 前ブラウザ情報退避用
		
		$serial = $fetchedRow['al_serial'];
		$agent = $fetchedRow['al_user_agent'];
		
		$ip = $fetchedRow['al_ip'];
		$ipStr = $this->convertToDispString($ip);
		if ($ip == $this->clientIp){			// クライアントのIPアドレスと同じときはグリーンで表示
			$ipStr = '<span style="color:green;">' . $ipStr . '</span>';
		}
		$ipLinkUrl = '?task=accesslog&ip=' . $ip;
		$ipLink = '<a href="' . $this->convertUrlToHtmlEntity($ipLinkUrl) . '">' . $ipStr . '</a>';

		// ブラウザ、プラットフォームの情報を取得
		if ($agent != $savedAgent) $this->browserTypeInfo = $this->gInstance->getAnalyzeManager()->getBrowserType($agent);
		$browserImg = '';
		if (!empty($this->browserTypeInfo)){
			$iconFile = $this->browserTypeInfo['icon'];
			if (!empty($iconFile)){
				$iconTitle = $this->browserTypeInfo['name'];
				$iconUrl = $this->gEnv->getRootUrl() . self::BROWSER_ICON_DIR . $iconFile;
				$browserImg = '<img src="' . $this->getUrl($iconUrl) . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
		}
		
		// アクセスユーザの国を取得
		$countryCode = '';
		if (!empty($fetchedRow['al_accept_language'])) $countryCode = $this->gInstance->getAnalyzeManager()->getBrowserCountryCode($fetchedRow['al_accept_language']);
		
		$countryImg = '';
		if (!empty($countryCode)){
			$iconTitle = $countryCode;
			$iconUrl = $this->gEnv->getRootUrl() . self::FLAG_ICON_DIR . $countryCode . '.png';
			$countryImg = '<img src="' . $this->getUrl($iconUrl) . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}
		
		$osImg = '';			// OS
		if ($agent != $savedAgent) $this->osInfo = $this->gInstance->getAnalyzeManager()->getPlatformType($agent);
/*		if (!empty($osCode)){
			$iconFile = $this->osIconFile[$osCode];	// OSアイコンファイル名
			if (!empty($iconFile)){
				$iconTitle = $osCode;
				$iconUrl = $this->gEnv->getRootUrl() . self::OS_ICON_DIR . $iconFile;
				$osImg = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
		}*/
		if (!empty($this->osInfo)){
			$iconFile = $this->osInfo['icon'];	// OSアイコンファイル名
			if (!empty($iconFile)){
				$iconTitle = $this->osInfo['name'] . ' ' . $this->osInfo['version_name'];
				$iconUrl = $this->gEnv->getRootUrl() . self::OS_ICON_DIR . $iconFile;
				$osImg = '<img src="' . $this->getUrl($iconUrl) . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
		}

		// 詳細画面へのURL
		$detailUrl = '?task=accesslog_detail&serial=' . $serial;
		
		// ユーザアイコン取得
		$userIconHtml = '';
		if (!empty($fetchedRow['lu_name'])) $userIconHtml = $this->gDesign->createUserAvatar($fetchedRow['lu_name'], $fetchedRow['lu_avatar'], self::AVATAR_ICON_SIZE);
		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($serial),			// シリアル番号
			'uri' => $this->convertToDispString($fetchedRow['al_uri']),		// URI
			'url_title' => $this->convertToDispString(urldecode($fetchedRow['al_uri'])),		// URLタイトル
			'detail_url' => $this->convertUrlToHtmlEntity($detailUrl),		// 詳細画面URL
			'browser' => $browserImg,		// ブラウザ
			'os' => $osImg,			// OS
			'country' => $countryImg,		// 国画像
			'ip' => $ipLink,		// クライアントIP
			'user' => $userIconHtml,										// ユーザ
			'dt' => $this->convertToDispDateTime($fetchedRow['al_dt']),	// 出力日時
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('loglist', $row);
		$this->tmpl->parseTemplate('loglist', 'a');
		
		// ブラウザ情報退避
		$savedAgent = $agent;		// 前ブラウザ情報退避用
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * アクセスパスメニュー作成
	 *
	 * @return								なし
	 */
	function createPathMenu()
	{
		$selected = '';
		if ($this->path == self::ACCESS_PATH_ALL){// アクセスパスすべて選択
			$selected = 'selected';
		}
		$row = array(
			'value'    => self::ACCESS_PATH_ALL,			// アクセスパス
			'name'     => 'すべて表示',			// 表示文字列
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
		
		$this->db->getPageIdList(array($this, 'pageIdLoop'), 0/*ページID*/);
		
		$selected = '';
		if ($this->path == self::ACCESS_PATH_OTHER){// アクセスパスその他
			$selected = 'selected';
		}
		$row = array(
			'value'    => self::ACCESS_PATH_OTHER,			// アクセスパス
			'name'     => 'その他',			// 表示文字列
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		// 開発モードのときはすべて表示、開発モードでないときはフロント画面用アクセスポイントのみ取得
		if (!$this->developMode && !$fetchedRow['pg_frontend']) return true;
		
		$selected = '';
		if ($fetchedRow['pg_path'] == $this->path){
			$selected = 'selected';
		}

		$name = $this->convertToDispString($fetchedRow['pg_name']);			// ページ名
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_path']),			// アクセスパス
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
		return true;
	}
	/**
	 * ホスト名のクライアントが指定のIPアドレスであるかチェック
	 *
	 * @param string $hostname		ホスト名
	 * @param string $ip			IPアドレス
	 * @return bool					true=適合する、false=適合しない
	 */
	function checkHostname($hostname, $ip)
	{
		$ipArray = false;
		
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){	// IPがIPv6の場合
			$rows = @dns_get_record($hostname, DNS_AAAA);
			if ($rows){
				$ipArray = array_map(function ($v){ return $v['ipv6']; }, $rows);
			}
		} else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){	// IPがIPv4の場合
			$ipArray = gethostbynamel($hostname);
		}
		
		if ($ipArray && in_array($ip, $ipArray)){
			return true;
		} else {
			return false;
		}
	}
}
?>
