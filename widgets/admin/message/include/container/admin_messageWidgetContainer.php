<?php
/**
 * index.php用コンテナクラス
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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_messageDb.php');

class admin_messageWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const DEFAULT_LOG_LEVEL = '0';		// デフォルトのログレベル
	const DEFAULT_LOG_STATUS = '1';		// デフォルトのログステータス
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const DEFAULT_VIEW_COUNT = 10;			// 一度に表示可能なリスト項目数
//	const MAX_PAGE_COUNT = 20;				// 最大ページ数
	const INFO_ICON_FILE = '/images/system/info16.png';			// 情報アイコン
	const NOTICE_ICON_FILE = '/images/system/notice16.png';		// 注意アイコン
	const ERROR_ICON_FILE = '/images/system/error16.png';		// エラーアイコン
	const ACTION_ICON_FILE = '/images/system/action16.png';		// 操作要求アイコン
	const ICON_SIZE = 16;		// アイコンのサイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_messageDb();
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
		return 'index.tmpl.html';
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
		$localeText = array();
		$this->createList($request);
		
		// テキストをローカライズ
		$localeText['label_type'] = $this->_('Type');			// 種別
		$localeText['label_message'] = $this->_('Message');			// メッセージ
		$localeText['label_date'] = $this->_('Date');			// 日時
		
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
//		$act = $request->trimValueOf('act');
//		$this->clientIp = $this->gRequest->trimServerValueOf('REMOTE_ADDR');		// クライアントのIPアドレス
//		$this->logLevel = $request->trimValueOf('loglevel');// 現在のログ表示レベル
//		if ($this->logLevel == '') $this->logLevel = self::DEFAULT_LOG_LEVEL;		// 現在のログ表示レベル
//		$this->logStatus = $request->trimValueOf('logstatus');// 現在のログ表示ステータス
//		if ($this->logStatus == '') $this->logStatus = self::DEFAULT_LOG_STATUS;		// 現在のログ表示ステータス(0=すべて、1=未確認のみ、2=確認済みのみ)
		$this->logLevel = self::DEFAULT_LOG_LEVEL;		// 現在のログ表示レベル
		$this->logStatus = self::DEFAULT_LOG_STATUS;		// 現在のログ表示ステータス(0=すべて、1=未確認のみ、2=確認済みのみ)

		// 表示するログを制限
		$viewLevel = 0;				// 表示メッセージレベル(0すべて、1=注意以上、10=要確認)
		if ($this->logLevel == '1') $viewLevel = 10;

		// 表示条件
		$paramObj = $this->getWidgetParamObj();
		if (empty($paramObj)){		// 既存データなしのとき
			// デフォルト値設定
			$listCount = self::DEFAULT_LIST_COUNT;		// 取得数
			$viewCount = self::DEFAULT_VIEW_COUNT;		// 表示数
		} else {
			$listCount = $paramObj->listCount;		// 取得数
			if (!isset($listCount)) $listCount = self::DEFAULT_LIST_COUNT;		// 取得数
			$viewCount = $paramObj->viewCount;		// 表示数
			if (!isset($viewCount)) $viewCount = self::DEFAULT_VIEW_COUNT;		// 表示数
		}
		$pageNo = 1;
		
		// 表示するログレベル、ログステータス選択メニュー作成
		//$this->createLogLevelMenu();
		//$this->createLogStatusMenu();
		
		// 総数を取得
		$totalCount = $this->db->getOpeLogCount($viewLevel, $this->logStatus);

		
		// アクセスログURL
		$accessLogUrl = '?task=accesslog_detail&openby=simple';
		$this->tmpl->addVar("_widget", "access_log_url", $accessLogUrl);
		
		// 運用ログを取得
//		$this->db->getOpeLogList($viewLevel, $this->logStatus, $listCount, $pageNo, array($this, 'logListLoop'));
		//$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
//		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist', 'visibility', 'hidden');		// ログがないときは非表示
		
		$this->tmpl->addVar('_widget', 'view_count', $viewCount);			// 一度に表示可能なリスト項目数
	}

}
?>
