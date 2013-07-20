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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_commentCommentWidgetContainer.php 6115 2013-06-16 12:39:34Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('comment') . '/admin_commentBaseWidgetContainer.php');

class admin_commentCommentWidgetContainer extends admin_commentBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $langId;		// デフォルトの言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $contentType;		// 選択中のコンテンツタイプ
	private $contentTypeArray;		// コンテンツ選択メニュー用
	private $permitHtml;		// HTMLあり
	private $status;			// コメント状態(0=未設定、1=非公開、2=公開)
	private $statusTypeArray;	// コメント状態メニュー作成用
	
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const COMMENT_SIZE = 40;			// コメント内容の最大文字列長
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const PREVIEW_ICON_FILE = '/images/system/preview.png';		// プレビュー用アイコン
	const UNTITLED_CONTENT = 'タイトル未設定';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期設定
		$this->contentTypeArray = array_merge(array(array('name' => '[すべて]', 'value' => '')), $this->gPage->getMainContentType());// コンテンツタイプ取得
		$this->langId = $this->gEnv->getDefaultLanguage();
		$this->statusTypeArray = array(	array(	'name' => '未承認',	'value' => '0'),
										array(	'name' => '非公開',	'value' => '1'),
										array(	'name' => '公開',	'value' => '2'));
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
		if ($task == 'comment_detail'){		// 詳細画面
			return 'admin_comment_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_comment.tmpl.html';
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
		if ($task == 'comment_detail'){	// 詳細画面
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
				$ret = self::$_mainDb->delCommentItem($delItems);
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
		
		// コンテンツ選択メニュー作成
		$this->createContentTypeMenu();
		
		// ###### 一覧の取得条件を作成 ######
		if (!empty($search_endDt)) $endDt = $this->getNextDay($search_endDt);
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
		
		// 総数を取得
		$totalCount = self::$_mainDb->getCommentItemCount($this->_contentType, $this->langId, $search_startDt, $endDt, $parsedKeywords);

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
		self::$_mainDb->searchCommentItems($this->_contentType, $this->langId, $maxListCount, $pageNo, $search_startDt, $endDt, $parsedKeywords, array($this, 'itemListLoop'));
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
//		$this->tmpl->addVar('_widget', 'config_admin_url', $this->getUrl($this->getConfigAdminUrl()));// コメント管理画面URL
		$this->tmpl->addVar('_widget', 'config_admin_url', $this->getUrl($this->_baseUrl));
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
//		$name = $request->trimValueOf('item_name');
//		$html = $request->valueOf('item_html');
//		$url = $request->valueOf('item_url');
//		$email = $request->valueOf('item_email');
//		$reg_user = $request->valueOf('item_reg_user');
		$this->status = $request->trimValueOf('item_status');		// エントリー状態(0=未設定、1=編集中、2=公開、3=非公開)
//		$this->contentType = $request->trimValueOf('content_type');		// 選択中のコンテンツタイプ

		// コメント定義取得
		$ret = self::$_mainDb->getConfig($this->_contentType, ''/*全体の定義*/, $row);
		if ($ret){
			$this->permitHtml		= $row[commentCommonDef::FD_PERMIT_HTML];		// HTMLあり
		} else {
			$this->permitHtml = 0;		// HTMLあり
		}
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 項目更新の場合
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$fieldData = array('cm_status' => $this->status);
				$ret = self::$_mainDb->updateCommentItem($this->serialNo, $fieldData);
				
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					$reloadData = true;		// データの再ロード
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
				$ret = self::$_mainDb->delCommentItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'deleteid'){		// ID項目削除の場合
		} else {	// 初期画面表示のとき
			$reloadData = true;		// データの再ロード
		}
		
		// 設定データを再取得
		if ($reloadData){		// データの再ロード
			$ret = self::$_mainDb->getCommentItem($this->serialNo, $row);
			if ($ret){
				$contentsId = $row['cm_contents_id'];				// 共通コンテンツID
				$title = $row['cm_title'];				// コメントタイトル
				$this->status = $row['cm_status'];			// コメント状態(0=未設定、1=非公開、2=公開)

				if ($this->permitHtml){			// HTMLコメントの場合
					$commentTag = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->currentPageRootUrl, $row['cm_message']);// アプリケーションルートを変換
				} else {
					$commentTag = $this->convertToPreviewText($this->convertToDispString($row['cm_message']));// 改行コードをbrタグに変換
				}
				$email = $row['cm_email'];				// Eメール
				if (!empty($row['lu_email'])) $email = $row['lu_email'];
				$url = $row['cm_url'];				// URL
				$author = $row['cm_author'];				// 投稿者
				if (!empty($row['author'])) $author = $row['author'];
				$reg_dt = $row['cm_create_dt'];				// 投稿日時
				$update_user = $row['update_user_name'];		// 更新者
				$update_dt = $row['cm_update_dt'];		// 更新日時
				
				// コンテンツタイトル取得
				$contentTitle = $this->getContentTitle($this->_contentType, $contentsId);
			}
		}
		// コメント状態メニュー作成
		$this->createStatusMenu();
		
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号

		// 入力フィールドの設定、共通項目のデータ設定
/*		if ($this->entryId == 0){		// 記事IDが0のときは、新規追加モードにする
			$this->tmpl->addVar('_widget', 'id', '新規');
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
		} else {
			$this->tmpl->addVar('_widget', 'id', $this->entryId);
			*/
			if ($this->serialNo == 0){		// 未登録データのとき
				// データ追加ボタン表示
				$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
			} else {
				// データ更新、削除ボタン表示
				$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');
				$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
			}
//		}
		// 表示項目を埋め込む
		$this->tmpl->addVar("_widget", "content_title", $this->convertToDispString($contentTitle));		// コンテンツタイトル
		$this->tmpl->addVar("_widget", "title", $this->convertToDispString($title));		// コメントタイトル
		$this->tmpl->addVar("_widget", "comment", $commentTag);		// コメント内容
		$this->tmpl->addVar("_widget", "email", $this->convertToDispString($email));		// Eメール
		$this->tmpl->addVar("_widget", "url", $this->convertToDispString($url));		// URL
		$this->tmpl->addVar("_widget", "author", $this->convertToDispString($author));		// 投稿者
		$this->tmpl->addVar("_widget", "date", $this->convertToDispDateTime($reg_dt));	// 投稿日時
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($update_user));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($update_dt));	// 更新日時
		//$this->tmpl->addVar('_widget', 'config_admin_url', $this->getUrl($this->getConfigAdminUrl()));// コメント管理画面URL
		$this->tmpl->addVar('_widget', 'config_admin_url', $this->getUrl($this->_baseUrl));
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
		$serial = $fetchedRow['cm_serial'];
		
		$contentsId = $fetchedRow['cm_contents_id'];	// 共通コンテンツID
		$contentType = $fetchedRow['cm_content_type'];	// コンテンツタイプ
		
		// 公開状態
		switch ($fetchedRow['cm_status']){
			case 0:	$status = '<font color="red">未承認</font>';
				break;
			case 1:	$status = '<font color="orange">非公開</font>';
				break;
			case 2:	$status = '<font color="green">公開</font>';
				break;
		}
		// コンテンツタイトル取得
		$title = $this->getContentTitle($contentType, $contentsId);
		
		$userName = $fetchedRow['lu_name'];
		if (empty($userName)) $userName = $fetchedRow['cm_author'];
			
		// コメント内容
		$comment = strip_tags($fetchedRow['cm_message']);		// タグを削除
		if (function_exists('mb_strimwidth')){
			$comment = mb_strimwidth($comment, 0, self::COMMENT_SIZE, '…');
		} else {
			$comment = substr($comment, 0, self::COMMENT_SIZE) . '...';
		}
		// プレビュー用URL
//		$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
		$previewUrl = commentCommonDef::createCommentUrl($contentType, $contentsId, $fetchedRow['cm_no']);
		$previewImg = $this->getUrl($this->gEnv->getRootUrl() . self::PREVIEW_ICON_FILE);
		$previewStr = 'プレビュー';
		
		$row = array(
			'index' => $index,		// 項目番号
			'serial' => $serial,			// シリアル番号
			'content_title' => $this->convertToDispString($title),		// コンテンツタイトル
			'no'	=> $this->convertToDispString($fetchedRow['cm_no']),		// コメント番号
			'name' => $this->convertToDispString($fetchedRow['cm_title']),		// コメントタイトル
			'content' => $this->convertToDispString($comment),		// コメント内容
			'status' => $status,													// 公開状況
			'author' => $this->convertToDispString($userName),	// 投稿者
			'date' => $this->convertToDispDateTime($fetchedRow['cm_create_dt']),	// 投稿日時
			'preview_url' => $previewUrl,											// プレビュー用のURL
			'preview_img' => $previewImg,											// プレビュー用の画像
			'preview_str' => $previewStr									// プレビュー文字列
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * 選択用メニューを作成
	 *
	 * @return なし
	 */
	function createContentTypeMenu()
	{
		for ($i = 0; $i < count($this->contentTypeArray); $i++){
			$name = $this->contentTypeArray[$i]['name'];
			$value = $this->contentTypeArray[$i]['value'];

			$row = array(
				'name' => $name,		// 名前
				'value' => $value,		// 値
				'selected' => $this->convertToSelectedString($value, $this->_contentType)	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('content_type_list', $row);
			$this->tmpl->parseTemplate('content_type_list', 'a');
		}
	}
	/**
	 * コンテンツタイトル取得
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentsId		共通コンテンツID
	 * @param string					タイトル
	 */
	function getContentTitle($contentType, $contentsId)
	{
		$contentName = self::UNTITLED_CONTENT;
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$ret = self::$_mainDb->getContentById(''/*PC用コンテンツ*/, $this->_langId, $contentsId, $row);
				if ($ret) $contentName = $row['cn_name'];
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$ret = self::$_mainDb->getProductById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['pt_name'];
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				// 未使用
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$ret = self::$_mainDb->getEntryById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['be_name'];
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$contentName = $contentsId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$ret = self::$_mainDb->getRoomById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['ur_name'];
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$ret = self::$_mainDb->getEventById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['ee_name'];
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$ret = self::$_mainDb->getPhotoById($contentsId, $this->_langId, $row);
				if ($ret) $contentName = $row['ht_name'];
				break;
		}
		return $contentName;
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
