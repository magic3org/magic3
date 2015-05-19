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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('evententry_main') . '/admin_evententry_mainBaseWidgetContainer.php');

class admin_evententry_mainRequestWidgetContainer extends admin_evententry_mainBaseWidgetContainer
{
	const DEFAULT_LIST_COUNT	= 20;			// 一覧の項目数
	const LINK_PAGE_COUNT		= 5;			// ページング用リンク数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 参加アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 参加キャンセルアイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		if ($task == self::TASK_REQUEST_DETAIL){		// 詳細画面
			return 'admin_request_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_request.tmpl.html';
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
		if ($task == self::TASK_EVENT_DETAIL){	// 詳細画面
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
		$eventEntryId = $request->trimValueOf('evententryid');			// 受付イベントID
		
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$keyword = $request->trimValueOf('search_keyword');			// 検索キーワード
		
		if ($act == 'delete'){		// 項目削除の場合
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
				$ret = $this->db->delCategoryBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 総数を取得
		$totalCount = self::$_mainDb->getEventEntryRequestListCount($this->_langId, $eventEntryId, ''/*検索キーワードなし*/);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, self::DEFAULT_LIST_COUNT);
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// 記事項目リストを取得
		self::$_mainDb->getEventEntryRequestList($this->_langId, $eventEntryId, self::DEFAULT_LIST_COUNT, $pageNo, ''/*検索キーワードなし*/, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 記事がないときは、一覧を表示しない
		
		// 受付イベント取得
		$ret = self::$_mainDb->getEventEntryById($this->_langId, $eventEntryId, $entryRow);
		if ($ret) $eventName = $entryRow['ee_name'];

		// 検索条件
		$this->tmpl->addVar("_widget", "page_link", $pageLink);			// ページリンク
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		
		// その他の値
		$this->tmpl->addVar("_widget", "event_name", $this->convertToDispString($eventName));	// イベント名
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$serial = $fetchedRow['ee_serial'];// シリアル番号
		
		// 受付状態
		switch ($fetchedRow['er_status']){
		case 0:			// 未設定
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非アクティブアイコン
			$iconTitle = '未設定';
			break;
		case 1:			// 参加
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// アクティブアイコン
			$iconTitle = '参加';
			break;
		case 2:			// キャンセル
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非アクティブアイコン
			$iconTitle = 'キャンセル';
			break;
		}

		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// ユーザ詳細画面(管理画面用メニューバーを非表示にする)
		$userDetailUrl	= '?task=userlist_detail&' . M3_REQUEST_PARAM_USER_ID . '=' . $fetchedRow['lu_id'] . '&menu=off';		// ユーザ詳細画面URL
		
		// 登録日時
		$dateTag = $this->convertToDispDateTime($fetchedRow['er_create_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
		
		$row = array(
			'index'		=> $index,		// 項目番号
			'serial'	=> $serial,			// シリアル番号
			'no'		=> $this->convertToDispString($fetchedRow['er_index']),		// 受付番号
			'name'		=> $this->convertToDispString($fetchedRow['lu_name']),		// 名前
			'name_url'	=> $this->convertUrlToHtmlEntity($userDetailUrl),
			'status'	=> $statusImg,												// 状態
			'date'		=> $dateTag						// 登録日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
