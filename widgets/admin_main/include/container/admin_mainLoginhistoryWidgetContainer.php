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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainUserBaseWidgetContainer.php');
//require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
//require_once($gEnvManager->getIncludePath() . '/common/userInfo.php');		// ユーザ情報クラス

class admin_mainLoginhistoryWidgetContainer extends admin_mainUserBaseWidgetContainer
{
	private $loginStatusArray;		// ログイン状況選択用
	private $loginStatus;	// ログイン状況
	private $browserIconFile;	// ブラウザアイコンファイル名
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const MAX_PAGE_COUNT = 20;				// 最大ページ数
	const BROWSER_ICON_DIR = '/images/system/browser/';		// ブラウザアイコンディレクトリ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ログイン状況選択用
		$this->loginStatusArray = array(	array(	'name' => $this->_('Login'),		'value' => '0'),	// ログイン
											array(	'name' => $this->_('Logout'),		'value' => '1'),	// ログアウト
											array(	'name' => $this->_('Error'),		'value' => '2'));	// エラー
											
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
		return 'loginhistory.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$localeText = array();
		$task = $request->trimValueOf('task');
		
		if ($task == 'loginhistory'){			// ログイン履歴
			$this->createList($request);
			
			// テキストをローカライズ
			$localeText['label_loginhistory'] = $this->_('Login History');	// ログイン履歴
			$localeText['label_date'] = $this->_('Date');		// 日時
			$localeText['label_name'] = $this->_('Name:');		// 名前：
			$localeText['label_account'] = $this->_('Login Acount:');		// ログインアカウント：
			$localeText['label_access_log'] = $this->_('Access Log');	// アクセスログ
			$localeText['label_range'] = $this->_('Range:');		// 範囲：
			$localeText['label_go_back'] = $this->_('Go back');		// 戻る
			$localeText['label_browser'] = $this->_('Browser');		// ブラウザ
		}
		$this->setLocaleText($localeText);
	}
	/**
	 * ユーザ単位のログイン履歴一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createList($request)
	{
		// ウィンドウ制御
		$this->setKeepForeTaskForBackUrl();	// 遷移前のタスクを戻りURLとして維持する
		
		// パラメータの取得
		$this->clientIp = $this->gRequest->trimServerValueOf('REMOTE_ADDR');		// クライアントのIPアドレス
		$act = $request->trimValueOf('act');
		$this->loginStatus = $request->trimValueOf('loginstatus');		// ログイン状況
//		$account = $request->trimValueOf('account');		// ログインアカウント
		$userId = $request->trimValueOf(M3_REQUEST_PARAM_USER_ID);		// 対象のユーザID
		$page = $request->trimValueOf('page');			// ページ
		
		// 表示条件
		$viewCount = $request->trimValueOf('viewcount');// 表示項目数
		if ($viewCount == '') $viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// ユーザ情報取得
//		$ret = $this->_mainDb->getUserByAccount($account, $row);
//		if ($ret) $userName = $row['lu_name'];
		$ret = $this->_mainDb->getUserById($userId, $row);
		if ($ret){
			$account	= $row['lu_account'];		// ユーザアカウント
			$userName	= $row['lu_name'];
		}
		
		// ログイン状況メニュー作成
		$this->createLoginStatusMenu();
		
		// メッセージコード取得
		switch ($this->loginStatus){
			case '0':		// ログイン
			default:
				$messageCode = array(2300, 2302);		// ログイン、自動ログイン
				break;
			case '1':		// ログアウト
				$messageCode = 2301;
				break;
			case '2':		// エラー
				$messageCode = 2310;
				break;
		}
		// 検索オプション作成
		$searchOption = 'account=' . $account;
		
		// 総数を取得
		$totalCount = $this->_mainDb->getOpeLogCountByMessageCode($messageCode, $searchOption);

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
					$link = '&nbsp;' . $i;
				} else {
					$linkUrl = '?task=loginstatus_history&account=' . $account . '&loginstatus=' . intval($this->loginStatus) . '&page=' . $i;
					$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $i . '</a>';
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
		
		// 一覧作成
		$this->_mainDb->getOpeLogListByMessageCode($messageCode, $searchOption, $viewCount, $pageNo, array($this, 'logListLoop'));
		
		// 値を画面に埋め込む
		$this->tmpl->addVar("_widget", "userid", $this->convertToDispString($userId));	// ユーザID
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($userName));	// ユーザ名
		$this->tmpl->addVar("_widget", "account", $this->convertToDispString($account));	// アカウント
	}
	/**
	 * ログイン状況選択メニュー作成
	 *
	 * @return なし
	 */
	function createLoginStatusMenu()
	{
		for ($i = 0; $i < count($this->loginStatusArray); $i++){
			$value = $this->loginStatusArray[$i]['value'];
			$name = $this->loginStatusArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->loginStatus) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('login_status_list', $row);
			$this->tmpl->parseTemplate('login_status_list', 'a');
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
		$accessLog = '';
		if (!empty($fetchedRow['ol_access_log_serial'])) $accessLog = $this->convertToDispString($fetchedRow['ol_access_log_serial']);
		
		// ログのサブ種別
		$logType = '';
		if ($this->loginStatus == 0){		// ログインの場合
			switch ($fetchedRow['ol_message_code']){
				case 2300:			// ログイン
					break;
				case 2302:			// 自動ログイン
					$logType = '自動';
					break;
			}
		}
				
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
		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($serial),			// シリアル番号
			'message' => $this->convertToDispString($fetchedRow['ol_message']),		// メッセージ
			'url' => $this->convertUrlToHtmlEntity($messageUrl),			// メッセージのリンク先
			'type' => $this->convertToDispString($logType),		// ログのサブ種別
			'ip' => $ipStr,		// クライアントIP
			'access_log' => $accessLog,		// アクセスログ番号
			'browser' => $browserImg,		// ブラウザ
			'output_dt' => $this->convertToDispDateTime($fetchedRow['ol_dt'])	// 出力日時
		);
		$this->tmpl->addVars('loglist', $row);
		$this->tmpl->parseTemplate('loglist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
