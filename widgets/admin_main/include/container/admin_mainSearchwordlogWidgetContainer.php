<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainSearchwordlogWidgetContainer.php 5802 2013-03-07 06:14:29Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_serverDb.php');

class admin_mainSearchwordlogWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serverDb;		// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $clientIp;			// クライアントのIPアドレス
	private $path;				// アクセスパス
	private $logOrder;			// 検索語の表示順
	private $logOrderArray;		// 検索語の表示順タイプ
	private $browserIconFile;	// ブラウザアイコンファイル名
	private $showMessage;		// メッセージ画面かどうか
	private $message;			// 表示メッセージ
	private $server;			// 指定サーバ
	private $startNo;			// 先頭の項目番号
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const MAX_PAGE_COUNT = 20;				// 最大ページ数
	const FLAG_ICON_DIR = '/images/system/flag/';		// 国旗アイコンディレクトリ
	const BROWSER_ICON_DIR = '/images/system/browser/';		// ブラウザアイコンディレクトリ
	const ICON_SIZE = 16;		// アイコンのサイズ
//	const DEFAULT_LOG_LEVEL = '0';		// デフォルトのログレベル
//	const DEFAULT_LOG_STATUS = '1';		// デフォルトのログステータス
	const DEFAULT_LOG_ORDER = '0';		// デフォルトの検索語表示順
	const DEFAULT_ACCESS_PATH = 'index';		// デフォルトのアクセスパス(PC用アクセスポイント)
	const ACCESS_PATH_ALL = '_all';				// アクセスパスすべて選択
	const ACCESS_PATH_OTHER = '_other';				// アクセスパスその他

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
		
		// 検索語の表示順タイプ
		$this->logOrderArray = array(	array(	'name' => '最新',	'value' => '0'),
										array(	'name' => '頻度高',	'value' => '1'));
										
		// ブラウザアイコンファイル名
		$this->browserIconFile = array(
			'OP' => 'opera.png',	// opera
			'IE' => 'ie.png',	// microsoft internet explorer
			'NS' => 'netscape.png',	// netscape
			'GA' => 'galeon.png',	// galeon
			'PX' => 'phoenix.png',	// phoenix
			'FF' => 'firefox.png',	// firefox
			'FB' => 'firebird.png',	// mozilla firebird
			'SM' => 'seamonkey.png',	// seamonkey
			'CA' => 'camino.png',	// camino
			'SF' => 'safari.png',	// safari
			'CH' => 'chrome.gif',	// chrome
			'KM' => 'k-meleon.png',	// k-meleon
			'MO' => 'mozilla.gif',	// mozilla
			'KO' => 'konqueror.png',	// konqueror
			'BB' => '',	// blackberry
			'IC' => 'icab.png',	// icab
			'LX' => '',	// lynx
			'LI' => '',	// links
			'MC' => '',	// ncsa mosaic
			'AM' => '',	// amaya
			'OW' => 'omniweb.png',	// omniweb
			'HJ' => '',	// hotjava
			'BX' => '',	// browsex
			'AV' => '',	// amigavoyager
			'AW' => '',	// amiga-aweb
			'IB' => '',	// ibrowse
			'AR' => '',	// arora
			'EP' => 'epiphany.png',	// epiphany
			'FL' => 'flock.png',	// flock
			'SL' => 'sleipnir.gif'		,// sleipnir
			'LU' => 'lunascape.gif',	// lunascape
			'SH' => 'shiira.gif',		// shiira
			'SW' => 'swift.png',	// swift
			'PS' => 'playstation.gif',	// playstation portable
			'PP' => 'playstation.gif',	// ワイプアウトピュア
			'NC' => 'netcaptor.gif',	// netcaptor
			'WT' => 'webtv.gif',	// webtv
			
			// クローラ
			'GB' => 'google.gif',	// Google
			'MS' => 'msn.gif',	// MSN
			'YA' => 'yahoo.gif',	// YahooSeeker
			'GO' => 'goo.gif',	// goo
			'HT' => 'hatena.gif',	// はてなアンテナ
			'NV' => 'naver.gif',	// Naver(韓国)
			
			// 携帯
			'DC' => 'docomo.gif',		// ドコモ
			'AU' => 'au.gif',		// au
			'SB' => 'softbank.gif',		// ソフトバンク
		);
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
		if ($task == 'searchwordlog_detail'){		// 詳細画面
			return 'searchwordlog_detail.tmpl.html';
		} else {			// 一覧画面
			return 'searchwordlog.tmpl.html';
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
		if ($task == 'searchwordlog_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
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
		$this->logOrder = $request->trimValueOf('logorder');// 検索語の表示順
		if ($this->logOrder == '') $this->logOrder = self::DEFAULT_LOG_ORDER;
		
		// 表示条件
//		$viewCount = $request->trimValueOf('viewcount');// 表示項目数
//		if ($viewCount == '') $viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$viewCount = $request->trimIntValueOf('viewcount', '0');
		if (empty($viewCount)) $viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号

		// 表示するログのタイプを設定
		$pathParam = $this->path;
		if ($pathParam == self::ACCESS_PATH_ALL){
			$pathParam = NULL;
		} else if ($pathParam == self::ACCESS_PATH_OTHER){		// その他のパス
			$pathParam = '';
		}
		switch ($this->logOrder){
			case 0:
			default:
				$this->tmpl->setAttribute('show_last_log', 'visibility', 'visible');// 最新から検索語を表示
				
				// 総数を取得
				$totalCount = $this->db->getSearchWordLogCount($pathParam);
				break;
			case 1:			// 頻度高
				$this->tmpl->setAttribute('show_sum_log', 'visibility', 'visible');// 検索頻度順に検索語を表示
				
				// 総数を取得
				$totalCount = $this->db->getSearchWordSumCount($pathParam);
				break;
		}

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$startNo = ($pageNo -1) * $viewCount +1;		// 先頭の行番号
		$endNo = $pageNo * $viewCount > $totalCount ? $totalCount : $pageNo * $viewCount;// 最後の行番号
		$this->startNo = $startNo;			// 先頭の項目番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i > self::MAX_PAGE_COUNT) break;			// 最大ページ数以上のときは終了
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		
		// アクセスパスメニュー、表示順選択メニュー作成
		$this->createPathMenu();
		$this->createLogOrderMenu();
			
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "view_count", $viewCount);	// 最大表示項目数
		$this->tmpl->addVar("search_range", "start_no", $startNo);
		$this->tmpl->addVar("search_range", "end_no", $endNo);
		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// アクセスログURL
		$accessLogUrl = '?task=accesslog_detail&openby=simple';
		if (!empty($this->server)) $accessLogUrl .= '&_server=' . $this->server;
		$this->tmpl->addVar("_widget", "access_log_url", $accessLogUrl);
		
		// 運用ログを取得
		switch ($this->logOrder){
			case 0:
			default:
				$this->db->getSearchWordLogList($viewCount, $pageNo, $pathParam, array($this, 'logListLoop'));
				if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist', 'visibility', 'hidden');		// ログがないときは非表示
				
				$this->tmpl->addVar("_widget", "detail_disabled", 'disabled');		// 詳細画面遷移なし
				break;
			case 1:			// 頻度高
				$this->db->getSearchWordSumList($viewCount, $pageNo, $pathParam, array($this, 'logListSumLoop'));
				if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist_sum', 'visibility', 'hidden');		// ログがないときは非表示
				break;
		}
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$word = $request->trimValueOf('word');		// 比較語
		$this->path = $request->trimValueOf('path');		// アクセスパス
		if (empty($this->path)) $this->path = self::DEFAULT_ACCESS_PATH;
		$this->logOrder = $request->trimValueOf('logorder');// 検索語の表示順
		if ($this->logOrder == '') $this->logOrder = self::DEFAULT_LOG_ORDER;
		$savedPageNo = $request->trimValueOf('page');				// ページ番号
		
		// 表示条件
//		$viewCount = $request->trimValueOf('viewcount');// 表示項目数
//		if ($viewCount == '') $viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$viewCount = $request->trimIntValueOf('viewcount', '0');
		if (empty($viewCount)) $viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf('page_', '1');				// ページ番号
		
		// 総数を取得
		$pathParam = $this->path;
		if ($pathParam == self::ACCESS_PATH_ALL){
			$pathParam = NULL;
		} else if ($pathParam == self::ACCESS_PATH_OTHER){		// その他のパス
			$pathParam = '';
		}
		$totalCount = $this->db->getSearchWordLogCountByWord($word, $pathParam);
				
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$startNo = ($pageNo -1) * $viewCount +1;		// 先頭の行番号
		$endNo = $pageNo * $viewCount > $totalCount ? $totalCount : $pageNo * $viewCount;// 最後の行番号
		$this->startNo = $startNo;			// 先頭の項目番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i > self::MAX_PAGE_COUNT) break;			// 最大ページ数以上のときは終了
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
			
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("_widget", "page_", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "view_count", $viewCount);	// 最大表示項目数
		$this->tmpl->addVar("search_range", "start_no", $startNo);
		$this->tmpl->addVar("search_range", "end_no", $endNo);
		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示

		// 前ウィンドウから引き継いだパラメータ
		$this->tmpl->addVar("_widget", "log_order", $this->logOrder);
		$this->tmpl->addVar("_widget", "path", $this->path);
		$this->tmpl->addVar("_widget", "compare_word", $word);
		$this->tmpl->addVar("_widget", "page", $savedPageNo);
		
		// アクセスログURL
		$accessLogUrl = '?task=accesslog_detail&openby=simple';
		if (!empty($this->server)) $accessLogUrl .= '&_server=' . $this->server;
		$this->tmpl->addVar("_widget", "access_log_url", $accessLogUrl);
		
		// 運用ログを取得
		$this->db->getSearchWordLogListByWord($word, $viewCount, $pageNo, $pathParam, array($this, 'logListLoop'));
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist', 'visibility', 'hidden');		// ログがないときは非表示
	}
	/**
	 * 検索語一覧取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function logListLoop($index, $fetchedRow, $param)
	{
		$agent = $fetchedRow['al_user_agent'];
		
		// 先頭の項目番号
		$no = $this->startNo + $index;
		
		// アクセスユーザの国を取得
		$countryCode = '';
		if (!empty($fetchedRow['al_accept_language'])) $countryCode = $this->gInstance->getAnalyzeManager()->getBrowserCountryCode($fetchedRow['al_accept_language']);
		
		$countryImg = '';
		if (!empty($countryCode)){
			$iconTitle = $countryCode;
			$iconUrl = $this->gEnv->getRootUrl() . self::FLAG_ICON_DIR . $countryCode . '.png';
			$countryImg = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}
		
		// ブラウザ、プラットフォームの情報を取得
/*		$browserCode = $this->gInstance->getAnalyzeManager()->getBrowserType($fetchedRow['al_user_agent'], $version);
		$browserImg = '';
		if (!empty($browserCode)){
			$iconFile = $this->browserIconFile[$browserCode];
			if (!empty($iconFile)){
				$iconTitle = $browserCode;
				$iconUrl = $this->gEnv->getRootUrl() . self::BROWSER_ICON_DIR . $iconFile;
				$browserImg = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
		}*/
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

		// アクセスログ番号
		$accessLog = '';
		if (!empty($fetchedRow['sw_access_log_serial'])) $accessLog = $this->convertToDispString($fetchedRow['sw_access_log_serial']);

		$row = array(
			'index' => $index,													// 行番号
			'no' => $no,			// シリアル番号
			'word' => $this->convertToDispString($fetchedRow['sw_word']),		// 語句
			'browser' => $browserImg,		// ブラウザ
			'country' => $countryImg,		// 国画像
			'access_log' => $accessLog,		// アクセスログ番号
			'user' => $this->convertToDispString($fetchedRow['lu_name']),										// ユーザ
			'dt' => $this->convertToDispDateTime($fetchedRow['al_dt']),	// 出力日時
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('loglist', $row);
		$this->tmpl->parseTemplate('loglist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['sw_serial'];
		return true;
	}
	/**
	 * 検索語一覧取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function logListSumLoop($index, $fetchedRow, $param)
	{
		// 先頭の項目番号
		$no = $this->startNo + $index;

		// 最新の検索語ログを取得
		$word = $fetchedRow['sw_basic_word'];
		$ret = $this->db->getSearchWordLogByCompareWord($word, $row);
		
		$row = array(
			'index' => $index,													// 行番号
			'no' => $no,			// シリアル番号
			'word' => $this->convertToDispString($row['sw_word']),		// 語句
			'compare_word' => $this->convertToDispString($word),		// 比較語
			'count' => $this->convertToDispString($fetchedRow['ct']),		// 検索回数
			'country' => $countryImg,		// 国画像
			'access_log' => $accessLog,		// アクセスログ番号
			'user' => $this->convertToDispString($row['lu_name']),										// ユーザ
			'dt' => $this->convertToDispDateTime($row['al_dt']),	// 出力日時
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('loglist_sum', $row);
		$this->tmpl->parseTemplate('loglist_sum', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $no;
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
	 * 表示順選択メニュー作成
	 *
	 * @return なし
	 */
	function createLogOrderMenu()
	{
		for ($i = 0; $i < count($this->logOrderArray); $i++){
			$value = $this->logOrderArray[$i]['value'];
			$name = $this->logOrderArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->logOrder) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 表示順
				'name'     => $name,			// 表示順名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('logorder_list', $row);
			$this->tmpl->parseTemplate('logorder_list', 'a');
		}
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
		$selected = '';
		if ($fetchedRow['pg_path'] == $this->path){
			$selected = 'selected';
		}
		$name = $this->convertToDispString($fetchedRow['pg_path']) . ' - ' . $this->convertToDispString($fetchedRow['pg_name']);			// ページ名
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_path']),			// アクセスパス
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
		return true;
	}
}
?>
