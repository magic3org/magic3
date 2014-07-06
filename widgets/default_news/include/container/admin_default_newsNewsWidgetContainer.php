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
require_once($gEnvManager->getWidgetContainerPath('default_news') . '/admin_default_newsBaseWidgetContainer.php');

class admin_default_newsNewsWidgetContainer extends admin_default_newsBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $langId;		// デフォルトの言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $contentType;		// 選択中のコンテンツタイプ
	private $status;			// メッセージ状態(0=非公開、1=公開)
	private $statusTypeArray;	// コメント状態メニュー作成用
	
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const MESSAGE_SIZE = 40;			// メッセージの最大文字列長
	const ICON_SIZE = 16;		// アイコンのサイズ
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const ACTIVE_ICON_FILE = '/images/system/active.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive.png';		// 非公開アイコン
	const UNKNOWN_CONTENT_TYPE = 'コンテンツタイプ不明';
	const UNKNOWN_CONTENT = 'タイトル不明';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期設定
		$this->langId = $this->gEnv->getDefaultLanguage();
		$this->statusTypeArray = array(	array(	'name' => '非公開',	'value' => '0'),
										array(	'name' => '公開',	'value' => '1'));
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
		if ($task == 'news_detail'){		// 詳細画面
			return 'admin_news_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_news.tmpl.html';
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
		if ($task == 'news_detail'){	// 詳細画面
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
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// 初期化
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// 入力値取得
		$act = $request->trimValueOf('act');
//		$this->contentType = $request->trimValueOf('content_type');		// 選択中のコンテンツタイプ	
//		if (empty($this->contentType)) $this->contentType = $request->trimValueOf('item_content_type');		// 選択中のコンテンツタイプ

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
				$ret = self::$_mainDb->delNewsItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			if (!empty($search_startDt) && !empty($search_endDt) && $search_startDt > $search_endDt){
				$this->setUserErrorMsg('期間の指定範囲にエラーがあります。');
			}
			$pageNo = 1;		// ページ番号初期化
		} else if ($act == 'selcontenttype'){		// コンテンツタイプ変更のとき
		} else {
//			$this->contentType = $this->getDefaultContentType();			// コンテンツタイプ
		}
		
		// ###### 一覧の取得条件を作成 ######
		if (!empty($search_endDt)) $endDt = $this->getNextDay($search_endDt);
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
		
		// 総数を取得
		//$totalCount = self::$_mainDb->getCommentItemCount($this->contentType, $this->langId, $search_startDt, $endDt, $parsedKeywords);
		$totalCount = self::$_mainDb->getNewsListCount($this->contentType, $parsedKeywords);

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
		
		// コメントリストを取得
//		self::$_mainDb->searchCommentItems($this->_contentType, $this->langId, $maxListCount, $pageNo, $search_startDt, $endDt, $parsedKeywords, array($this, 'itemListLoop'));
		self::$_mainDb->getNewsList($this->_contentType, $maxListCount, $pageNo, $parsedKeywords, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// コメントがないときは、一覧を表示しない

		// ボタン作成
		$searchImg = $this->getUrl($this->gEnv->getRootUrl() . self::SEARCH_ICON_FILE);
		$searchStr = '検索';
		$this->tmpl->addVar("_widget", "search_img", $searchImg);
		$this->tmpl->addVar("_widget", "search_str", $searchStr);
		
		// 検索結果
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		
		// 検索条件
		$this->tmpl->addVar("_widget", "search_start", $search_startDt);	// 開始日付
		$this->tmpl->addVar("_widget", "search_end", $search_endDt);	// 終了日付
		$this->tmpl->addVar("_widget", "search_keyword", $keyword);	// 検索キーワード

		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "list_count", $maxListCount);	// 一覧表示項目数
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
		
		$contentTitle = $request->trimValueOf('item_content_title');			// コンテンツタイトル
		$date = $request->trimValueOf('item_date');		// 投稿日
		$time = $request->trimValueOf('item_time');		// 投稿時間
		$message = $request->valueOf('item_message');		// メッセージ
		$url = $request->valueOf('item_url');
		$this->status = $request->trimValueOf('item_status');		// メッセージ状態(0=非公開、1=公開)
		$mark = 0;
		$contentTitleDisabled = '';
		
		$reloadData = false;		// データの再ロード
		if ($act == 'add'){		// メッセージを追加
			// 入力チェック
			$this->checkInput($message, 'メッセージ');
			$this->checkDate($date, '登録日付');
			$this->checkTime($time, '登録時間');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 入力データの修正
				$regDt = $this->convertToProperDate($date) . ' ' . $this->convertToProperTime($time);		// 登録日時
				
				//$ret = self::$_mainDb->updateNewsItem(0/*新規*/, $message, $url, $newSerial);
				$ret = self::$_mainDb->updateNewsItem(0/*新規*/, $contentTitle, $message, $url, $mark, $this->status, $regDt, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($message, 'メッセージ');
			$this->checkDate($date, '登録日付');
			$this->checkTime($time, '登録時間');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 入力データの修正
				$regDt = $this->convertToProperDate($date) . ' ' . $this->convertToProperTime($time);		// 登録日時
				$url = $this->gEnv->getMacroPath($url);// パスをマクロ形式に変換
				
				//$ret = self::$_mainDb->updateNewsItem($this->serialNo, $message, $url, $newSerial);
				$ret = self::$_mainDb->updateNewsItem($this->serialNo, $contentTitle, $message, $url, $mark, $this->status, $regDt, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->delNewsItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {	// 初期画面表示のとき
			$reloadData = true;		// データの再ロード
		}
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getNewsItem($this->serialNo, $row);
			if ($ret){
				$date = $this->timestampToDate($row['nw_regist_dt']);		// 登録日
				$time = $this->timestampToTime($row['nw_regist_dt']);		// 登録時間
				$this->status = intval($row['nw_visible']);			// 状態(0=非公開、1=公開)

				$message = $row['nw_message'];				// メッセージ
				$url = $row['nw_url'];				// URL
				$url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);		// URLを修正

				// コンテンツタイトル取得
				$contentType = $row['nw_content_type'];	// コンテンツタイプ
				$contentId = $row['nw_content_id'];	// コンテンツID
				if (!empty($contentType) && !empty($contentId)){
					list($contentTypeName, $contentTitle) = $this->getContentTitle($contentType, $contentId);
					
					// コンテンツタイトルを編集不可にする
					$contentTitleDisabled = 'disabled';
				} else {
					$contentTypeName = '';
					$contentTitle = $row['nw_name'];	// コンテンツタイトル
				}
			} else {
				$this->serialNo = 0;
				$date = date("Y/m/d");		// 登録日
				$time = date("H:i:s");		// 登録時間
				$this->status = 0;			// 状態(0=非公開、1=公開)
				$message = self::$_configArray[newsCommonDef::FD_DEFAULT_MESSAGE];				// メッセージ
				
				$contentType = '';	// コンテンツタイプ
				$contentId = '';	// コンテンツID
				$contentTitle = '';			// コンテンツタイトル
			}
		}
		// 状態メニュー作成
		$this->createStatusMenu();
		
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号

		// 入力フィールドの設定
		if (empty($this->serialNo)){		// 未登録データのとき
			// データ追加ボタン表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
		} else {
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}

		// 表示項目を埋め込む
		$this->tmpl->addVar("_widget", "content_type", $this->convertToDispString($contentTypeName));		// コンテンツタイプ
		$this->tmpl->addVar("_widget", "content_id", $this->convertToDispString($contentId));		// コンテンツID
		$this->tmpl->addVar("_widget", "content_title", $this->convertToDispString($contentTitle));		// コンテンツタイトル
		$this->tmpl->addVar("_widget", "content_title_disabled", $contentTitleDisabled);		// コンテンツタイトルフィールド
		$this->tmpl->addVar("_widget", "message", $this->convertToDispString($message));		// メッセージ
		$this->tmpl->addVar("_widget", "url", $this->convertToDispString($url));		// URL
		$this->tmpl->addVar("_widget", "date", $date);	// 投稿日
		$this->tmpl->addVar("_widget", "time", $time);	// 投稿時間
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
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
		// シリアル番号
		$serial = $fetchedRow['nw_serial'];
		
		$contentType = $fetchedRow['nw_content_type'];	// コンテンツタイプ
		$contentId = $fetchedRow['nw_content_id'];	// コンテンツID
		
		// 公開状態
		if ($fetchedRow['nw_visible']){		// コンテンツが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// コンテンツタイトル取得
		$contentType = $fetchedRow['nw_content_type'];	// コンテンツタイプ
		$contentId = $fetchedRow['nw_content_id'];	// コンテンツID
		if (!empty($contentType) && !empty($contentId)){
			list($contentTypeName, $contentTitle) = $this->getContentTitle($contentType, $contentId);
		} else {
			$contentTitle = $fetchedRow['nw_name'];	// コンテンツタイトル
		}
				
		// メッセージ
		$message = $fetchedRow['nw_message'];
		$keyTag = M3_TAG_START . M3_TAG_MACRO_TITLE . M3_TAG_END;
		$message = str_replace($keyTag, $contentTitle, $message);// タイトルを変換
				
		if (function_exists('mb_strimwidth')){
			$message = mb_strimwidth($message, 0, self::MESSAGE_SIZE, '…');
		} else {
			$message = substr($message, 0, self::MESSAGE_SIZE) . '...';
		}
		
		$row = array(
			'index' => $index,		// 項目番号
			'serial' => $serial,			// シリアル番号
			'id'	=> $this->convertToDispString($fetchedRow['nw_id']),		// ID
			'message' => $this->convertToDispString($message),		// メッセージ
			'status_img' => $statusImg,													// 公開状況
			'date' => $this->convertToDispDateTime($fetchedRow['nw_regist_dt'])	// 投稿日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * コンテンツタイトル取得
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @param array						コンテンツタイプ、タイトルの配列
	 */
	function getContentTitle($contentType, $contentId)
	{
		$contentTypeName = self::UNKNOWN_CONTENT_TYPE;
		$contentName = self::UNKNOWN_CONTENT;
		
		// コンテンツタイプ名取得
		$mainContentType = $this->gPage->getMainContentType();
		for ($i = 0; $i < count($mainContentType); $i++){
			$contentTypeRow = $mainContentType[$i];
			if ($contentTypeRow['value'] == $contentType){
				$contentTypeName = $contentTypeRow['name'];
				break;
			}
		}
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$ret = self::$_mainDb->getContentById(''/*PC用コンテンツ*/, $this->_langId, $contentId, $row);
				if ($ret) $contentName = $row['cn_name'];
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$ret = self::$_mainDb->getProductById($contentId, $this->_langId, $row);
				if ($ret) $contentName = $row['pt_name'];
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				// 未使用
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$ret = self::$_mainDb->getEntryById($contentId, $this->_langId, $row);
				if ($ret) $contentName = $row['be_name'];
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$contentName = $contentId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$ret = self::$_mainDb->getRoomById($contentId, $this->_langId, $row);
				if ($ret) $contentName = $row['ur_name'];
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$ret = self::$_mainDb->getEventById($contentId, $this->_langId, $row);
				if ($ret) $contentName = $row['ee_name'];
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$ret = self::$_mainDb->getPhotoById($contentId, $this->_langId, $row);
				if ($ret) $contentName = $row['ht_name'];
				break;
		}
		return array($contentTypeName, $contentName);
	}
	/**
	 * コメント状態選択タイプメニュー作成
	 *
	 * @return なし
	 */
	function createStatusMenu()
	{
		for ($i = 0; $i < count($this->statusTypeArray); $i++){
			$value = $this->statusTypeArray[$i]['value'];
			$name = $this->statusTypeArray[$i]['name'];
			$selected = '';
			if ($this->status == $value) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('status_list', $row);
			$this->tmpl->parseTemplate('status_list', 'a');
		}
	}
}
?>
