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
		//$eventId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
		$eventEntryId = $request->trimValueOf('evententryid');			// 受付イベントID
		
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
//		$ret = self::$_mainDb->getEventEntryByEventId($this->_langId, $eventId, ''/*予約タイプ*/, $entryRow);
		$ret = self::$_mainDb->getEventEntryById($this->_langId, $eventEntryId, $entryRow);
		if ($ret){
			$eventName = $entryRow['ee_name'];
		}
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
		$isAllDay = $fetchedRow['ee_is_all_day'];			// 終日イベントかどうか
		
		// 公開状態
		switch ($fetchedRow['ee_status']){
			case 1:	$status = '<font color="orange">編集中</font>';	break;
			case 2:	$status = '<font color="green">公開</font>';	break;
			case 3:	$status = '非公開';	break;
		}
		// 総参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(event_mainCommonDef::VIEW_CONTENT_TYPE, $serial);
		
		// イベント開催期間
		if ($fetchedRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// // 期間終了がないとき
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['ee_start_dt']);
				$endDtStr = '';
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = '';
			}
		} else {
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$startDtStr = $this->convertToDispDate($fetchedRow['ee_start_dt']);
				$endDtStr = $this->convertToDispDate($fetchedRow['ee_end_dt']);
			} else {
				$startDtStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
				$endDtStr = $this->convertToDispDateTime($fetchedRow['ee_end_dt'], 1/*ショートフォーマット*/, 10/*時分*/);
			}
		}
		
		$isActive = false;		// 公開状態
		if ($fetchedRow['ee_status'] == 2) $isActive = true;// 表示可能
		
		if ($isActive){		// コンテンツが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// アイキャッチ画像
		$iconUrl = event_mainCommonDef::getEyecatchImageUrl($fetchedRow['ee_thumb_filename'], self::$_configArray[event_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[event_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
		if (empty($fetchedRow['ee_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
		$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';

		$row = array(
			'index' => $index,		// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['ee_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['ee_name']),		// 名前
			'lang' => $lang,													// 対応言語
			'eyecatch_image' => $eyecatchImageTag,									// アイキャッチ画像
			'status_img' => $statusImg,												// 公開状態
			'status' => $status,													// 公開状況
			'date_start' => $startDtStr,	// 開催日時
			'date_end' => $endDtStr,	// 開催日時
			'place' => $this->convertToDispString($fetchedRow['ee_place']),	// 開催場所
			'view_count' => $totalViewCount,									// 総参照数
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_date' => $this->convertToDispDateTime($fetchedRow['ee_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
