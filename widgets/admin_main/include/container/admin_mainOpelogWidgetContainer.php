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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_serverDb.php');

class admin_mainOpelogWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serverDb;		// DB接続オブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $logLevelArray;				// 表示するログレベル
	private $logStatusArray;				// 表示するログステータス
	private $logLevel;					// 現在のログ表示レベル
	private $logStatus;					// 現在のログ表示ステータス
	private $clientIp;			// クライアントのIPアドレス
	private $browserIconFile;	// ブラウザアイコンファイル名
	private $showMessage;		// メッセージ画面かどうか
	private $message;			// 表示メッセージ
	private $server;			// 指定サーバ
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const MAX_PAGE_COUNT = 20;				// 最大ページ数
	const INFO_ICON_FILE = '/images/system/info16.png';			// 情報アイコン
	const NOTICE_ICON_FILE = '/images/system/notice16.png';		// 注意アイコン
	const ERROR_ICON_FILE = '/images/system/error16.png';		// エラーアイコン
	const ACTION_ICON_FILE = '/images/system/action16.png';		// 操作要求アイコン
	const GUIDE_ICON_FILE = '/images/system/guide16.png';		// ガイダンスアイコン
	const BROWSER_ICON_DIR = '/images/system/browser/';		// ブラウザアイコンディレクトリ
	const ICON_SIZE = 16;		// アイコンのサイズ
	const DEFAULT_LOG_LEVEL = '0';		// デフォルトのログレベル
	const DEFAULT_LOG_STATUS = '1';		// デフォルトのログステータス

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
		
		// ログレベルタイプ
		$this->logLevelArray = array(	array(	'name' => $this->_('All'),				'value' => '0'),	// すべて
										array(	'name' => $this->_('Check Required'),	'value' => '1'));		// 要確認項目のみ
		// ログステータスタイプ
		$this->logStatusArray = array(	array(	'name' => $this->_('All'),			'value' => '0'),	// すべて
										array(	'name' => $this->_('Unchecked'),	'value' => '1'),	// 未確認項目
										array(	'name' => $this->_('Checked'),		'value' => '2'));	// 確認済項目
										
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
				$this->message = $this->_('Could not connect to server.');		// サーバに接続できません
				return 'message.tmpl.html';
			}
		}
		
		$task = $request->trimValueOf('task');
		if ($task == 'opelog_detail'){		// 詳細画面
			return 'opelog_detail.tmpl.html';
		} else {			// 一覧画面
			return 'opelog.tmpl.html';
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
		
		$localeText = array();
		$task = $request->trimValueOf('task');
		if ($task == 'opelog_detail'){	// 詳細画面
			$this->createDetail($request);
			
			// テキストをローカライズ
			$localeText['msg_update'] = $this->_('Update item?');		// 項目を更新しますか?
			$localeText['msg_delete'] = $this->_('Delete item?');		// 項目を削除しますか?
			$localeText['label_log_detail'] = $this->_('Operation Log Detail');		// 運用ログ詳細
			$localeText['label_go_back'] = $this->_('Go back');		// 戻る
			$localeText['label_type'] = $this->_('Type');			// 種別
			$localeText['label_check'] = $this->_('Checked');		// 確認
			$localeText['label_message'] = $this->_('Message');			// メッセージ
			$localeText['label_message_detail'] = $this->_('Message Detail');// メッセージ詳細
			$localeText['label_message_code'] = $this->_('Message Code');// メッセージコード
			$localeText['label_access_log_no'] = $this->_('Access Log No');			// アクセスログ番号
			$localeText['label_date'] = $this->_('Date');			// 日時
			$localeText['label_update'] = $this->_('Update');	// 更新
		} else {			// 一覧画面
			$this->createList($request);
			
			// テキストをローカライズ
			$localeText['msg_select_item'] = $this->_('Select item to edit.');		// 編集する項目を選択してください
			$localeText['msg_select_del_item'] = $this->_('Select item to delete.');		// 削除する項目を選択してください
			$localeText['msg_delete_item'] = $this->_('Delete selected item?');// 選択項目を削除しますか?
			$localeText['label_log_list'] = $this->_('Operation Log List');					// 運用ログ一覧
			$localeText['label_log_level'] = $this->_('Level:');// 種別:
			$localeText['label_log_status'] = $this->_('Status:');// ステータス:
			$localeText['label_edit'] = $this->_('Edit');				// 編集
			$localeText['label_check'] = $this->_('Select');			// 選択
			$localeText['label_type'] = $this->_('Type');			// 種別
			$localeText['label_message'] = $this->_('Message');			// メッセージ
			$localeText['label_access_log'] = $this->_('Access Log');			// アクセスログ
			$localeText['label_check'] = $this->_('Checked');			// 確認
			$localeText['label_date'] = $this->_('Date');			// 日時
			$localeText['label_range'] = $this->_('Range:');		// 範囲：
		}
		$this->setLocaleText($localeText);
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$act = $request->trimValueOf('act');
		$this->clientIp = $this->gRequest->trimServerValueOf('REMOTE_ADDR');		// クライアントのIPアドレス
		$this->logLevel = $request->trimValueOf('loglevel');// 現在のログ表示レベル
		if ($this->logLevel == '') $this->logLevel = self::DEFAULT_LOG_LEVEL;		// 現在のログ表示レベル
		$this->logStatus = $request->trimValueOf('logstatus');// 現在のログ表示ステータス
		if ($this->logStatus == '') $this->logStatus = self::DEFAULT_LOG_STATUS;		// 現在のログ表示ステータス(0=すべて、1=未確認のみ、2=確認済みのみ)
		
		// 表示するログを制限
		$viewLevel = 0;				// 表示メッセージレベル(0すべて、1=注意以上、10=要確認)
		if ($this->logLevel == '1') $viewLevel = 10;

		// 表示条件
		$viewCount = $request->trimValueOf('viewcount');// 表示項目数
		if ($viewCount == '') $viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		if ($act == 'delete'){		// 項目を参照済みに設定
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
			/*
				$ret = $this->db->delUserBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}*/
			}
		}
		// 表示するログレベル、ログステータス選択メニュー作成
		$this->createLogLevelMenu();
		$this->createLogStatusMenu();
		
		// 総数を取得
		$totalCount = $this->db->getOpeLogCount($viewLevel, $this->logStatus);

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$startNo = ($pageNo -1) * $viewCount +1;		// 先頭の行番号
		$endNo = $pageNo * $viewCount > $totalCount ? $totalCount : $pageNo * $viewCount;// 最後の行番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i > self::MAX_PAGE_COUNT) break;			// 最大ページ数以上のときは終了
				if ($i == $pageNo){
					$link = ' ' . $i;
				} else {
					//$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
					$link = ' <a href="?task=opelog&act=selpage&page=' . $i . '">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", sprintf($this->_('%d Total'), $totalCount));
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "view_count", $viewCount);	// 最大表示項目数
		$this->tmpl->addVar("search_range", "start_no", $startNo);
		$this->tmpl->addVar("search_range", "end_no", $endNo);
		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// アクセスログURL
		$accessLogUrl = '?task=accesslog_detail&openby=simple';
		if (!empty($this->server)) $accessLogUrl .= '&_server=' . $this->server;
		$this->tmpl->addVar("_widget", "access_log_url", $accessLogUrl);
		
		// 運用ログ詳細URL
		$this->tmpl->addVar("_widget", "edit_url", '?task=opelog_detail');
		
		// 運用ログを取得
		$this->db->getOpeLogList($viewLevel, $this->logStatus, $viewCount, $pageNo, array($this, 'logListLoop'));
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist', 'visibility', 'hidden');		// ログがないときは非表示
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// 表示条件
		$viewCount = $request->trimValueOf('viewcount');// 表示項目数
		$page = $request->trimValueOf('page');				// ページ番号
		$logLevel = $request->trimValueOf('loglevel');// 現在のログ表示レベル
		$logStatus = $request->trimValueOf('logstatus');// 現在のログ表示ステータス
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$isMsgChecked = ($request->trimValueOf('item_msgchecked') == 'on') ? 1 : 0;			// 確認済みかどうか
			
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateOpeLogChecked($this->serialNo, $isMsgChecked);
				if ($ret){		// データ追加成功のとき
					//$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Data updated.'));		// データを更新しました
					$reloadData = true;		// データの再読み込み
				} else {
					//$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating data.'));		// データ更新に失敗しました
				}
			}
		} else {
			if (!empty($this->serialNo)) $reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			// 設定データを取得
			$ret = $this->db->getOpeLog($this->serialNo, $row);
			if ($ret){
				$iconUrl = '';
				switch ($row['ot_level']){
					case -1:		// ガイダンス
						$iconUrl = $this->gEnv->getRootUrl() . self::GUIDE_ICON_FILE;
						break;
					case 0:		// 情報
						$iconUrl = $this->gEnv->getRootUrl() . self::INFO_ICON_FILE;
						break;
					case 1:		// 操作要求アイコン
						$iconUrl = $this->gEnv->getRootUrl() . self::ACTION_ICON_FILE;
						break;
					case 2:		// 注意
						$iconUrl = $this->gEnv->getRootUrl() . self::NOTICE_ICON_FILE;
						break;
					case 10:	// 要確認
						$iconUrl = $this->gEnv->getRootUrl() . self::ERROR_ICON_FILE;
						break;
					default:
						break;
				}
				$iconTitle = $row['ot_name'];
				$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
				$msgTypeTag = $row['ot_name'];
				// 操作画面リンク
				if (!empty($row['ol_link'])){
					$link = $this->gEnv->getDefaultAdminUrl() . '?' . $row['ol_link'];
					$iconTag = '<a href="'. $this->getUrl($link) .'">' . $iconTag . '</a>';
					$msgTypeTag = '<a href="'. $this->getUrl($link) .'">' . $msgTypeTag . '</a>';
				}
				
				$isMsgChecked = $row['ol_checked'];		// 確認済みかどうか
				$logMsg = $row['ol_message'];		// メッセージ
				$logMsgDetail = $row['ol_message_ext'];// メッセージ詳細
				$logMsgCode = $row['ol_message_code'];// メッセージコード
				$accessLog = '';// アクセスログシリアル番号
				if (!empty($row['ol_access_log_serial'])) $accessLog = $this->convertToDispString($row['ol_access_log_serial']);
				$time = $this->convertToDispDateTime($row['ol_dt']);	// 出力日時
			}
		}
		// 取得データを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "type_image", $iconTag);
		$this->tmpl->addVar("_widget", "type", $msgTypeTag);
		$isChecked = '';
		if ($isMsgChecked) $isChecked = 'checked';
		$this->tmpl->addVar("_widget", "msg_checked", $isChecked);
		$this->tmpl->addVar("_widget", "message", $this->convertToDispString($logMsg));
		$this->tmpl->addVar("_widget", "detail", $this->convertToDispString($logMsgDetail));
		$this->tmpl->addVar("_widget", "code", $this->convertToDispString($logMsgCode));
		$this->tmpl->addVar("_widget", "access_log_serial", $accessLog);
		$this->tmpl->addVar("_widget", "time", $time);
		
		// アクセスログURL
		$accessLogUrl = '?task=accesslog_detail&openby=simple';
		if (!empty($this->server)) $accessLogUrl .= '&_server=' . $this->server;
		$this->tmpl->addVar("_widget", "access_log_url", $accessLogUrl);
		
		// 一覧の表示条件
		$this->tmpl->addVar("_widget", "page", $page);	// ページ番号
		$this->tmpl->addVar("_widget", "view_count", $viewCount);	// 最大表示項目数
		$this->tmpl->addVar("_widget", "log_level", $logLevel);	// ログ表示レベル
		$this->tmpl->addVar("_widget", "log_status", $logStatus);	// ログ表示ステータス
		
		$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		
		// サーバ指定の場合は更新ボタンを使用不可に設定
		if (!empty($this->server)){
			$this->tmpl->addVar("_widget", "msg_disabled", 'disabled');
			$this->tmpl->addVar("update_button", "update_disabled", 'disabled');
		}
	}
	/**
	 * 運用ログ一覧取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function logListLoop($index, $fetchedRow, $param)
	{
		$serial = $fetchedRow['ol_serial'];
				
		$msgChecked = '';
		if ($fetchedRow['ol_checked']){
			$msgChecked = 'checked';
		}
		// メッセージレベルの設定
		$iconUrl = '';
		switch ($fetchedRow['ot_level']){
			case -1:		// ガイダンス
				$iconUrl = $this->gEnv->getRootUrl() . self::GUIDE_ICON_FILE;
				break;
			case 0:		// 情報
				$iconUrl = $this->gEnv->getRootUrl() . self::INFO_ICON_FILE;
				break;
			case 1:		// 操作要求アイコン
				$iconUrl = $this->gEnv->getRootUrl() . self::ACTION_ICON_FILE;
				break;
			case 2:		// 注意
				$iconUrl = $this->gEnv->getRootUrl() . self::NOTICE_ICON_FILE;
				break;
			case 10:	// 要確認
				$iconUrl = $this->gEnv->getRootUrl() . self::ERROR_ICON_FILE;
				break;
			default:
				break;
		}
		$iconTitle = $fetchedRow['ot_name'];
		$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$accessLog = '';
		if (!empty($fetchedRow['ol_access_log_serial'])) $accessLog = $this->convertToDispString($fetchedRow['ol_access_log_serial']);
		
		// アクセス元のクライアントIP
		$ip = $fetchedRow['al_ip'];
		$ipStr = $this->convertToDispString($ip);
		if ($ip == $this->clientIp){			// クライアントのIPアドレスと同じときはグリーンで表示
			$ipStr = '<font color="green">' . $ipStr . '</font>';
		}
		
		// ブラウザ、プラットフォームの情報を取得
		$browserCode = $this->gInstance->getAnalyzeManager()->getBrowserType($fetchedRow['al_user_agent'], $version);
		$browserImg = '';
		if (!empty($browserCode)){
			$iconFile = $this->browserIconFile[$browserCode];
			if (!empty($iconFile)){
				$iconTitle = $browserCode;
				$iconUrl = $this->gEnv->getRootUrl() . self::BROWSER_ICON_DIR . $iconFile;
				$browserImg = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
		}
		
		// メッセージのリンク先
		$messageUrl = '?task=opelog_detail&serial=' . $serial;
		
		// 操作画面リンク
		if (!empty($fetchedRow['ol_link'])){
			$link = $this->gEnv->getDefaultAdminUrl() . '?' . $fetchedRow['ol_link'];
			$iconTag = '<a href="'. $this->getUrl($link) .'">' . $iconTag . '</a>';
		}
		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($serial),			// シリアル番号
			'type' => $iconTag,			// メッセージタイプを示すアイコン
			'message' => $this->convertToDispString($fetchedRow['ol_message']),		// メッセージ
			'url' => $this->convertUrlToHtmlEntity($messageUrl),			// メッセージのリンク先
			'ip' => $ipStr,		// クライアントIP
			'access_log' => $accessLog,		// アクセスログ番号
			'browser' => $browserImg,		// ブラウザ
			'output_dt' => $this->convertToDispDateTime($fetchedRow['ol_dt']),	// 出力日時
			'msg_checked' => $msgChecked,										// メッセージを確認したかどうか
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('loglist', $row);
		$this->tmpl->parseTemplate('loglist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * 表示ログレベル選択メニュー作成
	 *
	 * @return なし
	 */
	function createLogLevelMenu()
	{
		for ($i = 0; $i < count($this->logLevelArray); $i++){
			$value = $this->logLevelArray[$i]['value'];
			$name = $this->logLevelArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->logLevel) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ログレベル
				'name'     => $name,			// ログレベル名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('loglevel_list', $row);
			$this->tmpl->parseTemplate('loglevel_list', 'a');
		}
	}
	/**
	 * 表示ログステータス選択メニュー作成
	 *
	 * @return なし
	 */
	function createLogStatusMenu()
	{
		for ($i = 0; $i < count($this->logStatusArray); $i++){
			$value = $this->logStatusArray[$i]['value'];
			$name = $this->logStatusArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->logStatus) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ログレベル
				'name'     => $name,			// ログレベル名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('logstatus_list', $row);
			$this->tmpl->parseTemplate('logstatus_list', 'a');
		}
	}
}
?>
