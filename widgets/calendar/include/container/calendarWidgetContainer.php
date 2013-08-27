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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('calendar') . '/default_calendarCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('calendar') . '/calendarDb.php');

class calendarWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $showEvent;		// イベント記事を表示するかどうか
	private $events;		// 表示イベント
	private $langId;		// 言語
	private $css;		// デザインCSS
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'カレンダー';		// デフォルトのウィジェットタイトル名
	const MAX_ITEM_COUNT = 100;				// カレンダーに表示する項目の最大数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new calendarDb();
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
		return 'main.tmpl.html';
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
		// 初期値設定
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		$act = $request->trimValueOf('act');
		if ($act == 'getdata'){
			$this->getData($request);
		} else {		// カレンダー表示
			$this->showCalendar($request);
		}
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->css;
	}
	/**
	 * カレンダー表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showCalendar($request)
	{
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		$viewOption = $targetObj->viewOption;	// FullCalendar表示オプション
		if (isset($targetObj->showEvent)) $this->showEvent = $targetObj->showEvent;		// イベント記事を表示するかどうか
		if (isset($targetObj->css)) $this->css = $targetObj->css;			// デザインCSS
		
		// 取得コンテンツタイプ
		$typeArray = array();
		if ($this->showEvent) $typeArray[] = 'event';
		$type = implode(',', $typeArray);
		
		list($year, $month, $day) = explode('/', date('Y/m/d'));	// 現在日時
		$month = intval($month) -1;
		$day = intval($day) - 1;
		
		// データを埋め込む
		$this->tmpl->addVar("_widget", "type", $type);		// 取得コンテンツタイプ
		$this->tmpl->addVar("_widget", "year", $year);
		$this->tmpl->addVar("_widget", "month", $month);
		$this->tmpl->addVar("_widget", "day", $day);
		$this->tmpl->addVar("_widget", "option", $this->convertToDispString($viewOption));
	}
	/**
	 * カレンダー情報データ取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function getData($request)
	{
		// ##### データアクセス権チェック #####
		
		// 画面出力キャンセル
		$this->cancelParse();
		
		$eventType = $request->trimValueOf('type');			// 取得コンテンツタイプ
		$typeArray = array();
		if (!empty($eventType)) $typeArray = explode(',', $eventType);
		$startDt = $request->trimValueOf('start');
		$endDt = $request->trimValueOf('end');
			
		// 表示データを取得
		$this->events = array();
		if (in_array('event', $typeArray)){			// イベント記事
			// 項目取得
			$this->db->getEventItems(self::MAX_ITEM_COUNT, 1, $startDt, $endDt, $this->langId, array($this, 'itemsLoop'));
		}
		// Ajax戻りデータ
		$this->gInstance->getAjaxManager()->addData('events', $this->events);
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$entryId = $fetchedRow['ee_id'];// 記事ID
		$title = $fetchedRow['ee_name'];// タイトル
		$startDate = $fetchedRow['ee_start_dt'];// 開催日時(開始)
		$endDate = $fetchedRow['ee_end_dt'];// 開催日時(終了)

		// イベント記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		
		$event = array('title'	=> $title,
						'start'	=> $startDate,		// 開始
						'end'	=> $endDate,		// 終了
						'url'	=> $linkUrl);		// リンク先
		$this->events[] = $event;
		return true;
	}
}
?>
