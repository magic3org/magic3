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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainTenantserverWidgetContainer.php 2734 2009-12-25 05:51:56Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_serverDb.php');

class admin_mainTenantserverWidgetContainer extends admin_mainBaseWidgetContainer
{
	//private $db;	// DB接続オブジェクト
	private $serverDb;		// DB接続オブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $isExistsServer;		// 一覧にサーバが存在するかどうか
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const CF_SERVER_ID = 'server_id';		// サーバID
	const CF_SERVER_URL = 'server_url';		// サーバURL
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		//$this->db = new admin_mainDb();
		$this->serverDb = new admin_serverDb();
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
		$task = $request->trimValueOf('task');
		if ($task == 'tenantserver_detail'){		// 詳細画面
			return 'serverlist_detail.tmpl.html';
		} else {			// 一覧画面
			return 'serverlist.tmpl.html';
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
		$task = $request->trimValueOf('task');
		if ($task == 'tenantserver_detail'){	// 詳細画面
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
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// メニュー項目の削除
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
				$ret = $this->serverDb->delServerBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimValueOf('page');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		
		// 総数を取得
		$totalCount = $this->serverDb->getAllSeverListCount();

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
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
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		
		// サーバリストを取得
		$this->serverDb->getAllServerList($maxListCount, $pageNo, array($this, 'serverListLoop'));
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// サーバ項目がないときは、一覧を表示しない
		if (!$this->isExistsServer) $this->tmpl->setAttribute('serverlist', 'visibility', 'hidden');
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$this->lang		= $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$name = $request->trimValueOf('item_name');				// サーバ名
		$serverId = $request->trimValueOf('item_server_id');		// サーバID
		$ip = $request->trimValueOf('item_ip');		// サーバIP
		$url = rtrim($request->trimValueOf('item_url'), '/');		// サーバURL
		$dbType = $request->trimValueOf('item_db_type');		// DB種別
		$dbHost = $request->trimValueOf('item_db_host');		// DBホスト名
		$dbPort = $request->trimValueOf('item_db_port');		// DBポート番号
		$dbName = $request->trimValueOf('item_db_name');		// DB名
		$dbAccount = $request->trimValueOf('item_db_account');	// DB接続アカウント
		$dbPassword = $request->trimValueOf('item_db_password');// DB接続パスワード
		$canAccess = ($request->trimValueOf('item_can_access') == 'on') ? 1 : 0;		// アクセスできるかどうか

		// DSN作成
		$dbDsn = '';
		if (!empty($dbType) && !empty($dbHost)){
			$dbDsn .= $dbType . ':';
			if (!empty($dbHost)) $dbDsn .= 'host=' . $dbHost;
			if (!empty($dbPort)) $dbDsn .= ';port=' . $dbPort;
			$dbDsn .= ';dbname=' . $dbName;
		}
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkSingleByte($serverId, 'サーバID');
			$this->checkIp($ip, 'IPアドレス');
			$this->checkUrl($url, 'URL');
			if ($dbHost != 'localhost') $this->checkIp($dbHost, 'ホストIP', true);
			$this->checkSingleByte($dbName, 'DB名', true);
			$this->checkSingleByte($dbAccount, 'DB接続アカウント', true);
			
			// 名前重複チェック
			if ($this->serverDb->isExistsServerName($name, $this->serialNo)) $this->setMsg(self::MSG_USER_ERR, 'すでに同名のサーバが存在しています');
			
			// サーバID重複チェック
			if ($this->serverDb->isExistsServerId($serverId, $this->serialNo)) $this->setMsg(self::MSG_USER_ERR, 'すでに同じサーバIDのサーバが存在しています');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->serverDb->updateServer($this->serialNo, $name, $serverId, $ip, $url, $dbDsn, $dbAccount, $dbPassword, $canAccess, $newSerial);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkSingleByte($serverId, 'サーバID');
			$this->checkIp($ip, 'IPアドレス');
			$this->checkUrl($url, 'URL');
			if ($dbHost != 'localhost') $this->checkIp($dbHost, 'ホストIP', true);
			$this->checkSingleByte($dbName, 'DB名', true);
			$this->checkSingleByte($dbAccount, 'DB接続アカウント', true);
			
			// 名前重複チェック
			if ($this->serverDb->isExistsServerName($name)) $this->setMsg(self::MSG_USER_ERR, 'すでに同名のサーバが存在しています');

			// サーバID重複チェック
			if ($this->serverDb->isExistsServerId($serverId)) $this->setMsg(self::MSG_USER_ERR, 'すでに同じサーバIDのサーバが存在しています');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->serverDb->updateServer(0/*新規*/, $name, $serverId, $ip, $url, $dbDsn, $dbAccount, $dbPassword, $canAccess, $newSerial);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			$ret = $this->serverDb->delServerBySerial(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
			}
		} else if ($act == 'testdb'){		// DB接続テスト
			// 接続可能かチェック
			if ($this->serverDb->testDbConnection($dbDsn, $dbAccount, $dbPassword)){
				$msg = ' DB接続 => <b><font color="green">OK</font></b>';
								
				// テスト用DBオブジェクト作成
				$db = new admin_mainDb();
				$db->openLocalDb($dbDsn, $dbAccount, $dbPassword);// 接続先を変更
				
				// サーバID取得
				$svrId = $db->getSystemConfig(self::CF_SERVER_ID);
				$msg .= '<br />サーバID取得 => ';
				if ($svrId == $serverId){
					$msg .= '<b><font color="green">OK</font></b>';
				} else {
					$msg .= '<b><font color="red">サーバIDが異なっています。ID：'. $svrId . '</font></b>';
				}
				
				// サイト名取得
				$siteName = $db->getSiteDef($this->lang, M3_TB_FIELD_SITE_TITLE);
				$msg .= '<br />サイト名取得 => ';
				if (empty($siteName)){
					$msg .= 'サイト名未設定';
				} else {
					$msg .= $siteName;
				}
				
				// サーバURL取得
				$svrUrl = $db->getSystemConfig(self::CF_SERVER_URL);
				$msg .= '<br />URL取得 => ';
				$msg .= $svrUrl;
				
				$db->closeLocalDb();// 接続先を戻す
			} else {
				$msg .= '<b><font color="red">接続エラー</font></b>';
			}
			$this->tmpl->addVar('_widget', 'db_test',	$msg);
		} else {
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			if (empty($this->serialNo)){		// 新規項目追加のとき
				$name = '';		// サーバ名
				$serverId = '';		// サーバID
				$ip = '';		// サーバIP
				$url = '';		// サーバURL
				$dbDsn = '';		// DB接続情報
				$dbAccount = '';	// DB接続アカウント
				$dbPassword = '';// DB接続パスワード
				$canAccess = 1;	// アクセス可能かどうか
			} else {
				// 設定データを取得
				$ret = $this->serverDb->getServerBySerial($this->serialNo, $row);
				if ($ret){
					$name = $row['ts_name'];		// サーバ名
					$serverId = $row['ts_server_id'];		// サーバID
					$ip = $row['ts_ip'];		// サーバIP
					$url = $row['ts_url'];		// サーバURL
					$dbDsn = $row['ts_db_connect_dsn'];		// DB接続情報
					$dbAccount = $row['ts_db_account'];	// DB接続アカウント
					$dbPassword = $row['ts_db_password'];// DB接続パスワード
					$canAccess = $row['ts_enable_access'];	// アクセス可能かどうか
				}
			}
		}
		// DB種別を取得
		$pos = strpos($dbDsn, ':');
		if ($pos === false){
			$pos = -1;
		} else {
			$dbType = trim(substr($dbDsn, 0, $pos));
		}
		// ホスト名、DB名を取得
		$dsnParams = explode(";", substr($dbDsn, $pos + 1));
		for ($i = 0; $i < count($dsnParams); $i++){
			list($key, $value) = explode("=", $dsnParams[$i]);
			$key = trim($key);
			$value = trim($value);
			if ($key == 'host'){
				$dbHost = $value;
			} else if ($key == 'port'){
				$dbPort = $value;
			} else if ($key == 'dbname'){
				$dbName = $value;
			}
		}
				
		// 取得データを設定
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));// サーバ名
		$this->tmpl->addVar("_widget", "SERVER_ID", $this->convertToDispString($serverId));// サーバID
		$this->tmpl->addVar("_widget", "IP", $this->convertToDispString($ip));// サーバIP
		$this->tmpl->addVar("_widget", "URL", $this->convertToDispString($url));// サーバURL
		if ($dbType == 'mysql'){
			$this->tmpl->addVar("_widget", "MYSQL_CHECKED", "checked");
		} else if ($dbType == 'pgsql'){
			$this->tmpl->addVar("_widget", "PGSQL_CHECKED", "checked");
		}
		$this->tmpl->addVar("_widget", "DB_HOST", $this->convertToDispString($dbHost));// DBホスト名
		$this->tmpl->addVar("_widget", "DB_PORT", $this->convertToDispString($dbPort));// DBポート番号
		$this->tmpl->addVar("_widget", "DB_NAME", $this->convertToDispString($dbName));// DB名
		$this->tmpl->addVar("_widget", "DB_DSN", $this->convertToDispString($dbDsn));// DB接続情報
		$this->tmpl->addVar("_widget", "DB_ACCOUNT", $dbAccount);// DB接続アカウント
		$this->tmpl->addVar("_widget", "DB_PASSWORD", $dbPassword);// DB接続パスワード
		$canAccessCheck = '';
		if ($canAccess) $canAccessCheck = 'checked';
		$this->tmpl->addVar("_widget", "can_access", $canAccessCheck);
		
		// 共通データを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
				
		if (empty($this->serialNo)){		// ユーザIDが空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 削除、更新ボタン表示
		}
	}
	/**
	 * ユーザリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function serverListLoop($index, $fetchedRow, $param)
	{
		$canAccess = '';
		if ($fetchedRow['ts_enable_access']){
			$canAccess = 'checked';
		}
		if (empty($fetchedRow['ts_url'])){
			$linkUrlStr = '';
		} else {
			$linkUrlStr = '<a href="#" onclick="showUrl(\'' . $fetchedRow['ts_url'] . '\');">' . $this->convertToDispString($fetchedRow['ts_url']) . '</a>';
		}
		// 運用ログ参照ボタンの制御
		$opelogDisabled = '';
		if (empty($fetchedRow['ts_db_connect_dsn'])) $opelogDisabled = 'disabled';
		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['ts_serial']),			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['ts_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['ts_name']),		// 名前
			'url' => $linkUrlStr,		// URL
			'ip' => $this->convertToDispString($fetchedRow['ts_ip']),		// IP
			'update_dt' => $this->convertToDispDateTime($fetchedRow['ts_create_dt']),	// 更新日時
			'can_access' => $canAccess,												// アクセス可能かどうか
			'selected' => $selected,												// 項目選択用ラジオボタン
			'opelog_disabled' => $opelogDisabled					// 運用ログ参照ボタン
		);
		$this->tmpl->addVars('serverlist', $row);
		$this->tmpl->parseTemplate('serverlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['ts_serial'];
		
		$this->isExistsServer = true;		// 一覧にサーバが存在するかどうか
		return true;
	}
}
?>
