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

class admin_default_newsConfigWidgetContainer extends admin_default_newsBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;		// デフォルトの言語
	private $contentType;		// 選択中のコンテンツタイプ
	private $contentTypeArray;		// コンテンツ選択メニュー用
	private $configAdminUrl;		// ウィジェット設定画面URL
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const ICON_SIZE = 16;		// アイコンのサイズ
	const CONTENT_TYPE = '';			// コンテンツタイプ
	const ACTIVE_ICON_FILE = '/images/system/active.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive.png';		// 非公開アイコン
	const PREVIEW_ICON_FILE = '/images/system/preview.png';		// プレビュー用アイコン
	const MSG_NO_CONFIG = '設定が保存されていません';		// 未定義メッセージ

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期設定
		$this->contentTypeArray = $this->gPage->getMainContentType();// コンテンツタイプ取得
		$this->langId = $this->gEnv->getDefaultLanguage();
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
		return 'admin_config.tmpl.html';
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
		return $this->createConfig($request);
	}
	/**
	 * 基本設定画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createConfig($request)
	{
		// 入力値取得
		$act = $request->trimValueOf('act');
//		$this->contentType = $request->trimValueOf('content_type');		// 選択中のコンテンツタイプ
//		if (empty($this->contentType)) $this->contentType = $request->trimValueOf('item_content_type');		// 選択中のコンテンツタイプ
		$viewType = $request->trimValueOf('item_view_type');				// コメントタイプ
		$viewCount = $request->trimValueOf('item_view_count');			// 表示項目数
		$viewDirection = $request->trimValueOf('item_view_direction');				// 表示順
		$commentVisible = ($request->trimValueOf('item_comment_visible') == 'on') ? 1 : 0;		// コメントを表示する
		$commentVisibleDefault = ($request->trimValueOf('item_comment_visible_d') == 'on') ? 1 : 0;		// コメントを表示する(個別デフォルト)
		$commentAccept = ($request->trimValueOf('item_comment_accept') == 'on') ? 1 : 0;		// コメントを受け付ける
		$commentAcceptDefault = ($request->trimValueOf('item_comment_accept_d') == 'on') ? 1 : 0;		// コメントを受け付ける(個別デフォルト)
		$userLimited = ($request->trimValueOf('item_user_limited') == 'on') ? 1 : 0;		// ユーザ制限あり
		$permitHtml = ($request->trimValueOf('item_permit_html') == 'on') ? 1 : 0;		// HTMLあり
		$permitImage = ($request->trimValueOf('item_permit_image') == 'on') ? 1 : 0;		// 画像あり
		$autolink = ($request->trimValueOf('item_autolink') == 'on') ? 1 : 0;		// 自動リンク作成
		$maxLength = $request->trimValueOf('item_max_length');			// 文字数
		$maxImageSize = $request->trimValueOf('item_max_image_size');			// 画像最大サイズ
		$uploadMaxBytes = $request->trimValueOf('item_upload_max_bytes');			// アップロード画像最大バイトサイズ
		$useTitle = ($request->trimValueOf('item_use_title') == 'on') ? 1 : 0;		// タイトルあり
		$useAuthor = ($request->trimValueOf('item_use_author') == 'on') ? 1 : 0;		// 投稿者名あり
		$useDate = ($request->trimValueOf('item_use_date') == 'on') ? 1 : 0;		// 投稿日時あり
		$useEmail = ($request->trimValueOf('item_use_email') == 'on') ? 1 : 0;		// Eメールあり
		$useUrl = ($request->trimValueOf('item_use_url') == 'on') ? 1 : 0;		// URLあり
		$useAvatar = ($request->trimValueOf('item_use_avatar') == 'on') ? 1 : 0;		// アバターあり
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkInput($this->_contentType, 'コンテンツタイプ');
			$this->checkNumeric($viewCount, '表示項目数');
			$this->checkNumeric($maxLength, '文字数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$fieldValues = array();
			    $fieldValues[newsCommonDef::FD_VIEW_TYPE]		= $viewType;		// コメントタイプ(0=フラット,1=ツリー)
			    $fieldValues[newsCommonDef::FD_VIEW_DIRECTION]	= $viewDirection;		// 表示方向(0=昇順、1=降順)
			    $fieldValues[newsCommonDef::FD_MAX_COUNT]		= $viewCount;		// コメント最大数
			    $fieldValues[newsCommonDef::FD_MAX_LENGTH]		= $maxLength;		// コメント文字数
				$fieldValues[newsCommonDef::FD_MAX_IMAGE_SIZE]	= $maxImageSize;			// 画像最大サイズ
				$fieldValues[newsCommonDef::FD_UPLOAD_MAX_BYTES]	= $uploadMaxBytes * 1024;			// アップロード画像最大バイトサイズ
			    $fieldValues[newsCommonDef::FD_VISIBLE]			= intval($commentVisible);		// 表示可否(個別設定可)
			    $fieldValues[newsCommonDef::FD_VISIBLE_D]		= intval($commentVisibleDefault);		// 表示可否デフォルト値
			    $fieldValues[newsCommonDef::FD_ACCEPT_POST]		= intval($commentAccept);		// コメントの受付(個別設定可)
			    $fieldValues[newsCommonDef::FD_ACCEPT_POST_D]	= intval($commentAcceptDefault);		// コメントの受付デフォルト値
			    //$fieldValues[newsCommonDef::FD_START_DT]			= ;		// 使用期間(開始)(個別設定可)
			    //$fieldValues[newsCommonDef::FD_END_DT]			= ;		// 使用期間(終了)(個別設定可)
			    $fieldValues[newsCommonDef::FD_USER_LIMITED]		= intval($userLimited);		// 投稿ユーザを制限
			    $fieldValues[newsCommonDef::FD_PERMIT_HTML]		= intval($permitHtml);		// HTMLメッセージ
			    $fieldValues[newsCommonDef::FD_PERMIT_IMAGE]		= intval($permitImage);		// 画像あり
				$fieldValues[newsCommonDef::FD_AUTOLINK]			= intval($autolink);		// 自動リンク作成
			    $fieldValues[newsCommonDef::FD_USE_TITLE]		= intval($useTitle);		// タイトルあり
			    $fieldValues[newsCommonDef::FD_USE_AUTHOR]		= intval($useAuthor);		// 投稿者名あり
				$fieldValues[newsCommonDef::FD_USE_DATE]			= intval($useDate);		// 投稿日時あり
			    $fieldValues[newsCommonDef::FD_USE_EMAIL]		= intval($useEmail);		// Eメールあり
			    $fieldValues[newsCommonDef::FD_USE_URL]			= intval($useUrl);		// URLあり
			    $fieldValues[newsCommonDef::FD_USE_AVATAR]		= intval($useAvatar);		// アバターあり
	
				$ret = self::$_mainDb->updateConfig($this->_contentType, ''/*全体の定義*/, $fieldValues);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'selcontenttype'){		// コンテンツタイプ変更のとき
			$reloadData = true;		// データの再読み込み
		} else {		// 初期表示の場合
//			if (empty($this->contentType)) $this->contentType = $this->getDefaultContentType();			// コンテンツタイプ
			
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			// コメント定義取得
			$ret = self::$_mainDb->getConfig($this->_contentType, ''/*全体の定義*/, $row);
			if ($ret){
				$viewType				= $row[newsCommonDef::FD_VIEW_TYPE];				// コメントタイプ(フラット)
				$viewCount				= $row[newsCommonDef::FD_MAX_COUNT];			// 表示項目数
				$viewDirection			= $row[newsCommonDef::FD_VIEW_DIRECTION];				// 表示順
				$commentVisible			= $row[newsCommonDef::FD_VISIBLE];		// コメントを表示する
				$commentVisibleDefault	= $row[newsCommonDef::FD_VISIBLE_D];		// コメントを表示する(個別デフォルト)
				$commentAccept			= $row[newsCommonDef::FD_ACCEPT_POST];		// コメントを受け付ける
				$commentAcceptDefault	= $row[newsCommonDef::FD_ACCEPT_POST_D];		// コメントを受け付ける(個別デフォルト)
				$userLimited			= $row[newsCommonDef::FD_USER_LIMITED];		// ユーザ制限あり
				$permitHtml				= $row[newsCommonDef::FD_PERMIT_HTML];		// HTMLあり
				$permitImage			= $row[newsCommonDef::FD_PERMIT_IMAGE];		// 画像あり
				$autolink				= $row[newsCommonDef::FD_AUTOLINK];		// 自動リンク作成
				$maxLength				= $row[newsCommonDef::FD_MAX_LENGTH];			// 文字数
				$maxImageSize			= $row[newsCommonDef::FD_MAX_IMAGE_SIZE];			// 画像最大サイズ
				$uploadMaxBytes			= $row[newsCommonDef::FD_UPLOAD_MAX_BYTES];			// アップロード画像最大バイトサイズ
				$useTitle				= $row[newsCommonDef::FD_USE_TITLE];		// タイトルあり
				$useAuthor				= $row[newsCommonDef::FD_USE_AUTHOR];		// 投稿者名あり
				$useDate				= $row[newsCommonDef::FD_USE_DATE];		// 投稿日時あり
				$useEmail				= $row[newsCommonDef::FD_USE_EMAIL];		// Eメールあり
				$useUrl					= $row[newsCommonDef::FD_USE_URL];		// URLあり
				$useAvatar				= $row[newsCommonDef::FD_USE_AVATAR];		// アバターあり
				
				// 値修正
				if ($maxImageSize <= 0) $maxImageSize = newsCommonDef::DF_MAX_IMAGE_SIZE;			// 画像最大サイズ
				if ($uploadMaxBytes <= 0) $uploadMaxBytes = newsCommonDef::DF_UPLOAD_MAX_BYTES;		// アップロード画像最大バイトサイズ
				$uploadMaxBytes /= 1024;
			} else {
				$viewType = 0;				// コメントタイプ(フラット)
				$viewCount = newsCommonDef::DF_VIEW_COUNT;			// 表示項目数
				$viewDirection = newsCommonDef::DF_VIEW_DIRECTION;				// 表示順
				$commentVisible = 1;		// コメントを表示する
				$commentVisibleDefault = 1;		// コメントを表示する(個別デフォルト)
				$commentAccept = 1;		// コメントを受け付ける
				$commentAcceptDefault = 1;		// コメントを受け付ける(個別デフォルト)
				$userLimited = 0;		// ユーザ制限あり
				$permitHtml = 0;		// HTMLあり
				$permitImage = 0;		// 画像あり
				$autolink = 1;		// 自動リンク作成
				$maxLength = newsCommonDef::DF_MAX_LENGTH;			// 文字数
				$maxImageSize = newsCommonDef::DF_MAX_IMAGE_SIZE;			// 画像最大サイズ
				$uploadMaxBytes	= newsCommonDef::DF_UPLOAD_MAX_BYTES;			// アップロード画像最大バイトサイズ
				$uploadMaxBytes /= 1024;
				$useTitle = 0;		// タイトルあり
				$useAuthor = 1;		// 投稿者名あり
				$useDate = 1;		// 投稿日時あり
				$useEmail = 0;		// Eメールあり
				$useUrl = 0;		// URLあり
				$useAvatar = 1;		// アバターあり
				
				$message = $userType = '<font color="red">' . self::MSG_NO_CONFIG . '</font>';		// 未定義メッセージ
			}
		}
		
		// コンテンツ選択メニュー作成
		$this->createContentTypeMenu();
		
		$this->tmpl->addVar('_widget', 'view_type_flat_selected', $this->convertToSelectedString($viewType, 0));// コメントタイプ
		$this->tmpl->addVar('_widget', 'view_type_tree_selected', $this->convertToSelectedString($viewType, 1));// コメントタイプ
		$this->tmpl->addVar('_widget', 'view_order_inc_selected', $this->convertToSelectedString($viewDirection, 0));	// 表示順
		$this->tmpl->addVar('_widget', 'view_order_dec_selected', $this->convertToSelectedString($viewDirection, 1));	// 表示順
		$this->tmpl->addVar('_widget', 'comment_visible_checked', $this->convertToCheckedString($commentVisible));		// コメントを表示する
		$this->tmpl->addVar('_widget', 'comment_visible_d_checked', $this->convertToCheckedString($commentVisibleDefault));		// コメントを表示する(個別デフォルト)
		$this->tmpl->addVar('_widget', 'comment_accept_checked', $this->convertToCheckedString($commentAccept));		// コメントを受け付ける
		$this->tmpl->addVar('_widget', 'comment_accept_d_checked', $this->convertToCheckedString($commentAcceptDefault));		// コメントを受け付ける(個別デフォルト)
		$this->tmpl->addVar('_widget', 'view_count', $this->convertToDispString($viewCount));							// 表示コメント数
		$this->tmpl->addVar('_widget', 'max_length', $this->convertToDispString($maxLength));			// 入力文字数
		$this->tmpl->addVar('_widget', 'max_image_size', $this->convertToDispString($maxImageSize));			// 画像最大サイズ
		$this->tmpl->addVar('_widget', 'upload_max_bytes', $this->convertToDispString($uploadMaxBytes));			// アップロード画像最大バイトサイズ
		$this->tmpl->addVar('_widget', 'user_limited_checked', $this->convertToCheckedString($userLimited));	// ユーザ制限あり
		$this->tmpl->addVar('_widget', 'permit_html_checked', $this->convertToCheckedString($permitHtml));		// HTMLあり
		$this->tmpl->addVar('_widget', 'permit_image_checked', $this->convertToCheckedString($permitImage));		// 画像あり
		$this->tmpl->addVar('_widget', 'autolink_checked', $this->convertToCheckedString($autolink));		// 自動リンク作成
		$this->tmpl->addVar('_widget', 'use_title_checked', $this->convertToCheckedString($useTitle));		// タイトルあり
		$this->tmpl->addVar('_widget', 'use_author_checked', $this->convertToCheckedString($useAuthor));		// 投稿者名あり
		$this->tmpl->addVar('_widget', 'use_date_checked', $this->convertToCheckedString($useDate));		// 投稿日時あり
		$this->tmpl->addVar('_widget', 'use_email_checked', $this->convertToCheckedString($useEmail));		// Eメールあり
		$this->tmpl->addVar('_widget', 'use_url_checked', $this->convertToCheckedString($useUrl));		// URLあり
		$this->tmpl->addVar('_widget', 'use_avatar_checked', $this->convertToCheckedString($useAvatar));		// アバターあり
		
		$this->tmpl->addVar('_widget', 'message', $message);		// メッセージ
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
		// 入力値取得
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
//		$this->contentType = $request->trimValueOf('content_type');		// 選択中のコンテンツタイプ
		$contentsId = $request->trimValueOf('contentsid');		// コンテンツID
		
		$commentVisible = ($request->trimValueOf('item_comment_visible') == 'on') ? 1 : 0;		// コメントを表示する
		$commentAccept = ($request->trimValueOf('item_comment_accept') == 'on') ? 1 : 0;		// コメントを受け付ける
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 設定更新のとき
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$fieldValues = array();
			    $fieldValues['cf_visible']			= intval($commentVisible);		// 表示可否(個別設定可)
			    $fieldValues['cf_accept_post']		= intval($commentAccept);		// コメントの受付(個別設定可)
	
				$ret = self::$_mainDb->updateConfig($this->_contentType, $contentsId, $fieldValues);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			// コメント定義取得
			$ret = self::$_mainDb->getConfig($this->_contentType, $contentsId, $row);
			if ($ret){
				$commentVisible			= $row[newsCommonDef::FD_VISIBLE];		// コメントを表示する
				$commentAccept			= $row[newsCommonDef::FD_ACCEPT_POST];		// コメントを受け付ける
			} else {		// コンテンツ個別設定がない場合はデフォルトを取得
				$ret = self::$_mainDb->getConfig($this->_contentType, ''/*全体の定義*/, $row);
				if ($ret){
					$commentVisible	= $row[newsCommonDef::FD_VISIBLE_D];		// コメントを表示する(個別デフォルト)
					$commentAccept	= $row[newsCommonDef::FD_ACCEPT_POST_D];		// コメントを受け付ける(個別デフォルト)
				}
			}

			// コンテンツ情報取得
			$ret = self::$_mainDb->getContentById(''/*PC用汎用コンテンツ*/, $this->langId, $contentsId, $row);
			if ($ret){
				$contentName = $row['cn_name'];
			}
		}
		
		$contentTypeName = '';
		for ($i = 0; $i < count($this->contentTypeArray); $i++){
			if ($this->contentTypeArray[$i]['value'] == $this->_contentType) $contentTypeName = $this->contentTypeArray[$i]['name'];
		}
		
		// パラメータ引継ぎ
		$this->tmpl->addVar("_widget", "page_no", $this->convertToDispString($pageNo));		// ページ番号
		$this->tmpl->addVar("_widget", "content_type", $this->convertToDispString($this->_contentType));		// コンテンツタイプ
		
		// その他
		$this->tmpl->addVar('_widget', 'comment_visible_checked', $this->convertToCheckedString($commentVisible));		// コメントを表示する
		$this->tmpl->addVar('_widget', 'comment_accept_checked', $this->convertToCheckedString($commentAccept));		// コメントを受け付ける
		$this->tmpl->addVar("_widget", "id", $this->convertToDispString($contentsId));
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($contentName));
		$this->tmpl->addVar("_widget", "content_type_name", $this->convertToDispString($contentTypeName));		// コンテンツタイプ名
		$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		$this->tmpl->addVar('_widget', 'config_admin_url', $this->getUrl($this->_baseUrl));
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// 変数初期化
		$maxListCount = self::DEFAULT_LIST_COUNT;
				
		// 入力値取得
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
//		$this->contentType = $request->trimValueOf('content_type');		// 選択中のコンテンツタイプ

		$contentTypeName = '';
		for ($i = 0; $i < count($this->contentTypeArray); $i++){
			if ($this->contentTypeArray[$i]['value'] == $this->_contentType) $contentTypeName = $this->contentTypeArray[$i]['name'];
		}
		
		// コンテンツ総数を取得
		switch ($this->_contentType)
		{
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$totalCount = self::$_mainDb->getContentCount(self::CONTENT_TYPE, $this->langId);
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$totalCount = self::$_mainDb->getProductCount($this->langId);
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$totalCount = self::$_mainDb->getEntryCount($this->langId);
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$totalCount = self::$_mainDb->getWikiCount($this->langId);
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$totalCount = self::$_mainDb->getRoomCount($this->langId);
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$totalCount = self::$_mainDb->getEventCount($this->langId);
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$totalCount = self::$_mainDb->getPhotoCount($this->langId);
				break;
		}

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
		
		// コンテンツリストを取得
		switch ($this->_contentType)
		{
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				self::$_mainDb->getContent(self::CONTENT_TYPE, $this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				self::$_mainDb->getProduct($this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				self::$_mainDb->getEntry($this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				self::$_mainDb->getWiki($this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				self::$_mainDb->getRoom($this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				self::$_mainDb->getEvent($this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				self::$_mainDb->getPhoto($this->langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
				break;
		}
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧項目がないときは、一覧を表示しない
		
		// パラメータ引継ぎ
		$this->tmpl->addVar("_widget", "page_no", $this->convertToDispString($pageNo));		// ページ番号
		$this->tmpl->addVar("_widget", "content_type", $this->convertToDispString($this->_contentType));		// コンテンツタイプ
		
		// その他
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "content_type_name", $this->convertToDispString($contentTypeName));		// コンテンツタイプ名
		$this->tmpl->addVar('_widget', 'config_admin_url', $this->getUrl($this->_baseUrl));
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
//			$selected = '';

//			if ($this->contentType == $value) $selected = 'selected';
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
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$serial = $this->convertToDispString($fetchedRow['cn_serial']);
//		$contentsId = $fetchedRow['cn_id'];		// コンテンツID
		$contentsId = $fetchedRow['contents_id'];		// 共通コンテンツID
		$contentTitle = $fetchedRow['content_title'];	// コンテンツタイトル
		$updateUser = $fetchedRow['lu_name'];		// 更新者名
		$updateDt = $fetchedRow['update_dt'];	// 更新日時
		
		$visible = false;		// 表示状態
		$limited = false;		// ユーザ制限
		switch ($this->_contentType)
		{
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				if ($fetchedRow['cn_visible']) $visible = true;
				if ($fetchedRow['cn_user_limited']) $limited = true;
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				if ($fetchedRow['pt_visible']) $visible = true;
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				if ($fetchedRow['be_status'] == 2) $visible = true;
				if ($fetchedRow['be_user_limited']) $limited = true;
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$visible = true;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				if ($fetchedRow['ur_visible']) $visible = true;
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				if ($fetchedRow['ee_status'] == 2) $visible = true;
				if ($fetchedRow['ee_user_limited']) $limited = true;
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				if ($fetchedRow['ht_visible']) $visible = true;
				if ($fetchedRow['ht_user_limited']) $limited = true;
				break;
		}
		
		// 公開状況の設定
		$now = date("Y/m/d H:i:s");	// 現在日時
		$startDt = $fetchedRow['cn_active_start_dt'];
		$endDt = $fetchedRow['cn_active_end_dt'];
		
		$isActive = false;		// 公開状態
		if ($fetchedRow['cn_visible']) $isActive = $this->isActive($startDt, $endDt, $now);// 表示可能
		
		if ($isActive){		// コンテンツが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// プレビュー
		$previewUrl = newsCommonDef::createCommentUrl($this->_contentType, $contentsId);
//		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;// プレビュー用URL
		$previewImg = $this->getUrl($this->gEnv->getRootUrl() . self::PREVIEW_ICON_FILE);
		$previewStr = 'プレビュー';
		
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($contentsId),			// ID
			'name' => $this->convertToDispString($contentTitle),		// コンテンツタイトル
			'lang' => $lang,													// 対応言語
			'status' => $statusImg,												// 公開状況
			'update_user' => $this->convertToDispString($updateUser),	// 更新者
			'update_dt' => $this->convertToDispDateTime($updateDt),	// 更新日時
			'visible' => $this->convertToCheckedString($visible),							// 公開状況
			'limited' => $this->convertToCheckedString($limited),							// ユーザ制限

			'preview_url' => $previewUrl,											// プレビュー用のURL
			'preview_img' => $previewImg,											// プレビュー用の画像
			'preview_str' => $previewStr									// プレビュー文字列
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $contentsId;
		return true;
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function isActive($startDt, $endDt, $now)
	{
		$isActive = false;		// 公開状態

		if ($startDt == $this->gEnv->getInitValueOfTimestamp() && $endDt == $this->gEnv->getInitValueOfTimestamp()){
			$isActive = true;		// 公開状態
		} else if ($startDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		} else if ($endDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) >= strtotime($startDt)) $isActive = true;		// 公開状態
		} else {
			if (strtotime($startDt) <= strtotime($now) && strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		}
		return $isActive;
	}
	/**
	 * デフォルトのコンテンツタイプを取得
	 *
	 * @return string						コンテンツタイプ
	 */
	function getDefaultContentType()
	{
		$contentType = '';
		$ret = $this->_db->getPageDefBySerial($this->_defSerial, $row);
		if ($ret){
			$pageId = $row['pd_id'];
			$pageSubId = $row['pd_sub_id'];
			$pageInfo = $this->gPage->getPageInfo($pageId, $pageSubId);
			$contentType = $pageInfo['pn_content_type'];
		}
		if (empty($contentType)) $contentType = M3_VIEW_TYPE_CONTENT;				// 汎用コンテンツ
		return $contentType;
	}
}
?>
