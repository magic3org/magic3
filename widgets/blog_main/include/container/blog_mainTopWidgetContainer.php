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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/blog_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_categoryDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_commentDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class blog_mainTopWidgetContainer extends blog_mainBaseWidgetContainer
{
	private $categoryDb;	// DB接続オブジェクト
	private $commentDb;			// DB接続オブジェクト
	private $entryId;	// 記事ID
	private $blogId;			// ブログID
	private $startDt;
	private $endDt;
	private $pageNo;				// ページ番号
	private $now;	// 現在日時
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $currentPageUrl;			// 現在のページURL
	private $viewItemsData = array();			// Joomla!ビュー用データ
	private $buttonList;		// コンテンツ編集ボタン
	
	// 表示制御
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $isCmdAccess;			// cmd付きアクセスかどうか
	private $preview;				// プレビューモードかどうか
	private $outputHead;			// ヘッダ出力するかどうか
//	private $useWidgetTitle;		// ウィジェットタイトルを使用するかどうか
	private $isExistsViewData;		// 表示データがあるかどうか
	private $useMultiBlog;			// マルチブログを使用するかどうか
	private $isOutputComment;		// コメントを出力するかどうか
	private $receiveComment;		// コメントを受け付けるかどうか
	private $isAccessWithPageNo;	// ページ番号付きのアクセスかどうか(一覧の先頭かどうかを判断)
	
	// 表示項目
	private $entryViewCount;// 記事表示数
	private $entryViewOrder;// 記事表示順
	private $title;		// 表示タイトル
	private $widgetTitle;	// ウィジェットタイトル
	private $message;			// ユーザ向けメッセージ
	private $pageTitle;				// 画面タイトル、パンくずリスト用タイトル
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $categoryDesc;	// カテゴリーの説明
	private $addLib = array();		// 追加スクリプト
	private $headScript;	// HTMLヘッダに埋め込むJavascript
	private $viewMode;					// 表示モード
	private $showListType;				// 一覧表示タイプ
	private $viewParam = array();					// 表示用パラメータ
	private $editIconPos;			// 編集アイコンの位置
	private $avatarSize;		// アバター画像サイズ
	private $titleList;		// 一覧タイトル
	private $titleNoEntry;		// 記事なし時タイトル
	private $messageNoEntry;		// ブログ記事が登録されていないメッセージ
	private $messageFindNoEntry;	// ブログ記事が見つからないメッセージ
//	private $startTitleTagLevel;	// 最初のタイトルタグレベル
	private $itemTagLevel;			// 記事のタイトルタグレベル
	private $showEntryAuthor;	// 投稿者を表示するかどうか
	private $showEntryRegistDt;	// 投稿日時を表示するかどうか
	private $showEntryViewCount;	// 閲覧数を表示するかどうか
	private $showReadmore;			// 「もっと読む」ボタンを表示するかどうか
	private $readmoreLabel;			// 「もっと読む」ボタンラベル
	private $entryListDispType;		// 記事一覧表示タイプ
	private $showEntryListImage;		// 記事一覧に画像を表示するかどうか
	private $entryListImageType;	// 記事一覧用画像タイプ
	
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const EDIT_ICON_MIN_POS = 30;			// 編集アイコンの位置
	const EDIT_ICON_NEXT_POS = 35;			// 編集アイコンの位置
	const MESSAGE_EXT_ENTRY_PRE	= '…&nbsp;';							// 投稿記事に続きがある場合の表示
	const DEFAULT_TITLE_SEARCH = '検索';		// 検索時のデフォルトページタイトル
	const TITLE_RELATED_CONTENT_BLOCK = '関連記事';		// 関連コンテンツブロック
	const COMMENT_PERMA_HEAD	= 'comment-';		// コメントパーマリンク
	const COMMENT_TITLE		= ' についてのコメント';	// コメント用タイトル
	const NO_COMMENT_TITLE = 'タイトルなし';				// 未設定時のコメントタイトル
	const EDIT_ICON_FILE = '/images/system/page_edit32.png';		// 編集アイコン
	const NEW_ICON_FILE = '/images/system/page_add32.png';		// 新規アイコン
	const CONFIG_ICON_FILE = '/images/system/page_config32.png';		// 投稿管理画面アイコン
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const COOKIE_LIB = 'jquery.cookie';		// 名前保存用クッキーライブラリ
	const ENTRY_BODY_BLOCK_CLASS = 'blog_entry_body';		// 記事本文ブロックのCSSクラス
	const CATEGORY_BLOCK_CLASS = 'blog_category_list';		// カテゴリーブロックのCSSクラス
	const CATEGORY_BLOCK_LABEL = 'カテゴリー：';		// カテゴリーブロックのラベル
	const CATEGORY_BLOCK_SEPARATOR = ', ';			// カテゴリーブロック内の区切り
	const AVATAR_TITLE_TAIL = 'のアバター';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->categoryDb = new blog_categoryDb();
		$this->commentDb = new blog_commentDb();
		
		// 初期値設定
		$this->editIconPos = self::EDIT_ICON_MIN_POS;			// 編集アイコンの位置
		$this->outputHead = self::$_configArray[blog_mainCommonDef::CF_OUTPUT_HEAD];			// ヘッダ出力するかどうか
		$this->useMultiBlog = self::$_configArray[blog_mainCommonDef::CF_USE_MULTI_BLOG];// マルチブログを使用するかどうか
		$this->receiveComment = self::$_configArray[blog_mainCommonDef::CF_RECEIVE_COMMENT];		// コメントを受け付けるかどうか
//		$this->useWidgetTitle = self::$_configArray[blog_mainCommonDef::CF_USE_WIDGET_TITLE];		// ウィジェットタイトルを使用するかどうか
		
		$this->entryViewCount	= self::$_configArray[blog_mainCommonDef::CF_ENTRY_VIEW_COUNT];// 記事表示数
		if (empty($this->entryViewCount)) $this->entryViewCount = blog_mainCommonDef::DEFAULT_VIEW_COUNT;
		$this->entryViewOrder	= self::$_configArray[blog_mainCommonDef::CF_ENTRY_VIEW_ORDER];// 記事表示順	
		$this->title = self::$_configArray[blog_mainCommonDef::CF_TITLE_DEFAULT];		// デフォルトタイトル
		$this->titleList = self::$_configArray[blog_mainCommonDef::CF_TITLE_LIST];		// 一覧タイトル
		if (empty($this->titleList)) $this->titleList = blog_mainCommonDef::DEFAULT_TITLE_LIST;
		$this->titleSearchList = self::$_configArray[blog_mainCommonDef::CF_TITLE_SEARCH_LIST];		// 検索結果タイトル
		if (empty($this->titleSearchList)) $this->titleSearchList = blog_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;
		$this->titleNoEntry = self::$_configArray[blog_mainCommonDef::CF_TITLE_NO_ENTRY];		// 記事なし時タイトル
		if (empty($this->titleNoEntry)) $this->titleNoEntry = blog_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;
		$this->messageNoEntry = self::$_configArray[blog_mainCommonDef::CF_MESSAGE_NO_ENTRY];		// ブログ記事が登録されていない(404未検出)メッセージ
		if (empty($this->messageNoEntry)) $this->messageNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;
		$this->messageFindNoEntry = self::$_configArray[blog_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY];		// ブログ記事が見つからない(検索結果表示用)メッセージ
		if (empty($this->messageFindNoEntry)) $this->messageFindNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;
//		$this->startTitleTagLevel = self::$_configArray[blog_mainCommonDef::CF_TITLE_TAG_LEVEL];	// 最初のタイトルタグレベル
//		if (empty($this->startTitleTagLevel)) $this->startTitleTagLevel = blog_mainCommonDef::DEFAULT_TITLE_TAG_LEVEL;
		$this->showEntryAuthor		= self::$_configArray[blog_mainCommonDef::CF_SHOW_ENTRY_AUTHOR];		// 投稿者を表示するかどうか
		$this->showEntryRegistDt	= self::$_configArray[blog_mainCommonDef::CF_SHOW_ENTRY_REGIST_DT];		// 投稿日時を表示するかどうか
		$this->showEntryViewCount	= self::$_configArray[blog_mainCommonDef::CF_SHOW_ENTRY_VIEW_COUNT];	// 閲覧数を表示するかどうか
		$this->showReadmore = true;			// 「もっと読む」ボタンを表示するかどうか
		if (empty(self::$_configArray[blog_mainCommonDef::CF_READMORE_LABEL])) $this->showReadmore = false;			// 「もっと読む」ボタンのラベルが空の場合はボタンを非表示にする
		$this->readmoreLabel = self::$_configArray[blog_mainCommonDef::CF_READMORE_LABEL];// 「もっと読む」ボタンラベル
		$this->itemTagLevel = $this->getHTagLevel();			// 記事のタイトルタグレベル
		$this->entryListDispType	= self::$_configArray[blog_mainCommonDef::CF_ENTRY_LIST_DISP_TYPE];		// 記事一覧表示タイプ
		$this->showEntryListImage	= self::$_configArray[blog_mainCommonDef::CF_SHOW_ENTRY_LIST_IMAGE];		// 記事一覧に画像を表示するかどうか
		$this->entryListImageType	= self::$_configArray[blog_mainCommonDef::CF_ENTRY_LIST_IMAGE_TYPE];	// 記事一覧用画像タイプ
		if (empty($this->entryListImageType)) $this->entryListImageType = blog_mainCommonDef::DEFAULT_ENTRY_LIST_IMAGE_TYPE;
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
		// 入力値取得
		$act = $request->trimValueOf('act');
		$this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		if (empty($this->entryId)) $this->entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);		// 略式ブログ記事ID
		$this->blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);
		if (empty($this->blogId)) $this->blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID_SHORT);		// 略式ブログID
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		
		if (!empty($this->entryId)){		// 記事IDがある場合を優先
			$this->viewMode = 10;					// 表示モード(記事単体表示)
			if ($this->_renderType == M3_RENDER_BOOTSTRAP){
				return 'single_bootstrap.tmpl.html';		// 記事詳細
			} else {
				return 'single.tmpl.html';		// 記事詳細
			}
		} else if ($act == 'search' || !empty($keyword)){
			$this->viewMode = 2;					// 表示モード(検索一覧表示)
		} else {
			$year = $request->trimValueOf('year');		// 年指定
			$month = $request->trimValueOf('month');		// 月指定
			$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
			
			if (!empty($category) || !empty($year) || !empty($month)){
				$this->viewMode = 1;					// 表示モード(記事一覧表示)
			} else {
				$this->viewMode = 0;					// 表示モード(トップ一覧表示)
			}
		}
		return 'list.tmpl.html';		// トップ画面記事一覧
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
		// 初期設定値
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->isOutputComment = false;// コメントを出力するかどうか
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		$this->isCmdAccess = $request->isCmdAccess();			// cmd付きアクセスかどうか
		$this->pageTitle = '';		// 画面タイトル、パンくずリスト用タイトル
		$this->categoryDesc = '';	// カテゴリーの説明

		// 入力値取得
		$act = $request->trimValueOf('act');
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$this->pageNo = $request->trimIntValueOf('page', '1');				// ページ番号
		$this->startDt = $request->trimValueOf('start');
		$this->endDt = $request->trimValueOf('end');
		$this->isAccessWithPageNo = ($request->trimValueOf('page') == '') ? false : true;		// ページ番号付きのアクセスかどうか(一覧の先頭かどうかを判断)
		
		// 管理者でプレビューモードのときは表示制限しない
		$this->preview = false;
		//if ($this->isSystemManageUser && $cmd == M3_REQUEST_CMD_PREVIEW){		// システム運用者以上
		if ($this->isSystemManageUser && ($cmd == M3_REQUEST_CMD_PREVIEW || $cmd == M3_REQUEST_CMD_CSS)){		// システム運用者以上
			$this->preview = true;
		}
		
		// 共通ボタン作成
		if (self::$_canEditEntry){		// 記事編集権限ありのとき
			$this->buttonList = $this->createButtonTag(0);// 新規作成ボタン
		} else {
			$this->buttonList = '';
		}
		
		// ビューデータ作成
		switch ($this->viewMode){					// 表示モード
			case 0:			// トップ一覧表示
			default:
//				if (self::$_canEditEntry) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');		// 編集機能表示
				$this->selectListRender();			// 一覧タイプで出力
				
				$this->showTopList($request);
				break;
			case 1:			// 記事一覧表示
//				if (self::$_canEditEntry) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');		// 編集機能表示
				$this->selectListRender();			// 一覧タイプで出力
				
				$this->showList($request);
				break;
			case 2:			// 検索一覧表示
//				if (self::$_canEditEntry) $this->tmpl->setAttribute('show_script', 'visibility', 'visible');		// 編集機能表示
				$this->selectListRender();			// 一覧タイプで出力
				
				$this->showSearchList($request);
				break;
			case 10:			// 記事単体表示
				// ### ウィジェットタイトルはデフォルトで非表示 ###
//				$this->title = M3_TAG_START . M3_TAG_MACRO_NOTITLE . M3_TAG_END;			// 仕様変更(2018/2//25)

				$avatarFormat = $this->gInstance->getImageManager()->getDefaultAvatarFormat();		// 画像フォーマット取得
				$this->gInstance->getImageManager()->parseImageFormat($avatarFormat, $imageType, $imageAttr, $this->avatarSize);		// 画像情報取得
		
				$this->showSingle($request);		// 記事単体表示のとき
				break;
		}

		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->pageTitle);

		// タイトルの設定
/*		if ($this->useWidgetTitle){			// ウィジェットタイトルを使用するとき
			if (!empty($this->title)) $this->widgetTitle = $this->title;	// ウィジェットタイトル
		} else {
			if (!empty($this->title)){
				$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
				$this->tmpl->addVar("show_title", "title", '<h' . $this->startTitleTagLevel . '>' . $this->convertToDispString($this->title) . '</h' . $this->startTitleTagLevel . '>');
			}
		}*/
		// ウィジェットタイトルの使用に限定(2015/10/7)
		if (!empty($this->title)) $this->widgetTitle = $this->title;	// ウィジェットタイトル

		// メッセージを表示
		if (!empty($this->message)){
			if ($this->_renderType == M3_RENDER_JOOMLA_NEW){
				// Joomla!新型テンプレートの先頭にメッセージ追加
				$this->addPageHeadingContent($this->message);
			} else {
				$this->tmpl->setAttribute('message', 'visibility', 'visible');
				$this->tmpl->addVar("message", "message", $this->convertToDispString($this->message, true/*タグを残す*/));
			}
		}
		
		// ##### Joomla!新型テンプレートに記事データを設定 #####
		$this->setJoomlaViewData($this->viewItemsData, count($this->viewItemsData)/*先頭(leading部)のコンテンツ数*/, 0/*カラム部(intro部)のコンテンツ数*/, 0/*カラム部(intro部)のカラム数*/, $this->categoryDesc/*カテゴリーの説明*/, $this->readmoreLabel);
		
		// ##### 運用可能ユーザの場合は編集用ボタンを表示 #####
		$this->createEditButton();
	}
	/**
	 * 記事単体表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showSingle($request)
	{
		$sendButtonLabel = 'コメントを投稿';		// 送信ボタンラベル
		$sendStatus = 0;		// 送信状況
		$showPreviewEntry = false;		// プレビュー用の記事を表示するかどうか
		
		// プレビューモードの場合はプレビュー用の記事があるかどうか確認
		if ($this->preview){		// プレビューモードの場合
			// 記事IDを解析
			list($entryId, $userId) = explode(M3_CONTENT_PREVIEW_ID_SEPARATOR, $this->entryId);
			
			// ユーザIDのチェック
			if ($userId == $this->_userId){
				$this->entryId = intval($entryId);
				
				// プレビュー記事を取得
				$ret = self::$_mainDb->getEntryPreview($this->entryId, $this->_langId, $entryRow);
				if ($ret) $showPreviewEntry = true;			// プレビュー用の記事を表示
			}
		}
		
		// 単体記事を取得
		if (!$showPreviewEntry){
			// 記事ID指定のときは記事を取得
			if (!empty($this->entryId)){
				$ret = self::$_mainDb->getEntryItem($this->entryId, $this->_langId, $entryRow);
			}
		}
		
		if (!empty($entryRow)){		// 記事レコードがあるとき
			$this->title = $entryRow['be_name'];			// ウィジェット用画面タイトル(仕様追加 2018/2/25)
//			$this->pageTitle = $entryRow['be_name'];			// 画面タイトル
		}
		
		// 入力値取得
		$act = $request->trimValueOf('act');
		$commentTitle = $request->trimValueOf('title');
		$name = $request->trimValueOf('name');
		$email = $request->trimValueOf('email');
		$url = $request->trimValueOf('url');
		$body = $request->trimValueOf('body');
		
		// コメントを受け付けるときは、コメント入力欄を表示
		if ($this->receiveComment){
			if ($act == 'checkcomment'){		// コメント確認のとき
				// 入力チェック
				$maxCommentLength = self::$_configArray[blog_mainCommonDef::CF_MAX_COMMENT_LENGTH];// コメント最大長
				if ($maxCommentLength == '') $maxCommentLength = blog_mainCommonDef::DEFAULT_COMMENT_LENGTH;
				if (empty($maxCommentLength)){		// 空のときは長さのチェックなし
					$this->checkInput($body, 'コメント内容');
				} else {
					$this->checkLength($body, 'コメント内容', $maxCommentLength);
				}
				$this->checkMailAddress($email, 'Eメール', true);

				// エラーなしの場合は確認画面表示
				if ($this->getMsgCount() == 0){
					// コメントタイトル作成
					if (!empty($entryRow)){		// 記事レコードがあるとき
						$this->title = $entryRow['be_name'] . self::COMMENT_TITLE;
//						$this->pageTitle = $entryRow['be_name'] . self::COMMENT_TITLE;
					}
/*					$ret = self::$_mainDb->getEntryItem($this->entryId, $this->_langId, $row);
					if ($ret) $this->title = $row['be_name'] . self::COMMENT_TITLE;
					$this->pageTitle = $this->title;		// 画面タイトル*/
				
					// ハッシュキー作成
					$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
					$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
					$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
				
					$this->setGuidanceMsg('この内容でコメントを投稿しますか?');
				
					// 入力の変更不可
					$sendButtonLabel = 'コメントを投稿';		// 送信ボタンラベル
					$sendStatus = 1;// 送信状況を「確定」に変更
				
					if (!self::$_configArray[blog_mainCommonDef::CF_COMMENT_USER_LIMITED]){	// ユーザ制限なしのときは名前、メールアドレスを表示
						$this->tmpl->setAttribute('user_info', 'visibility', 'visible');
					}
					$this->tmpl->addVar("add_comment", "title_disabled", 'readonly');
					$this->tmpl->addVar("user_info", "name_disabled", 'readonly');
					$this->tmpl->addVar("user_info", "email_disabled", 'readonly');
					$this->tmpl->addVar("add_comment", "url_disabled", 'readonly');
					$this->tmpl->addVar("add_comment", "body_disabled", 'readonly');
					$this->tmpl->setAttribute('cancel_button', 'visibility', 'visible');		// キャンセルボタン表示
				
					// ### コメント入力欄の表示 ###
					$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');// コメント入力欄表示
					$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
					$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
					$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');// 記事表示停止
				}
			
				// 入力値を戻す
				$this->tmpl->addVar("add_comment", "title", $this->convertToDispString($commentTitle));
				$this->tmpl->addVar("user_info", "name", $this->convertToDispString($name));
				$this->tmpl->addVar("user_info", "email", $this->convertToDispString($email));
				$this->tmpl->addVar("add_comment", "url", $this->convertToDispString($url));
				$this->tmpl->addVar("add_comment", "body", $this->convertToDispString($body));
				$this->tmpl->addVar("_widget", "entry_id", $this->convertToDispString($this->entryId));		// 記事ID
			} else if ($act == 'sendcomment'){	// コメント受信のとき
				$postTicket = $request->trimValueOf('ticket');		// POST確認用
				$ret = false;
				if (!empty($this->entryId) && !empty($body) &&
					!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
					// コメントを保存
					$ret = $this->commentDb->addCommentItem($this->entryId, $this->_langId, $commentTitle, $body, $url, $name, $email, $this->_userId, $this->now, $newSerial);
				
					// 記事更新日を更新
					if ($ret) $ret = self::$_mainDb->updateEntryDt($this->entryId, $this->_langId);
				}
				if ($ret){
					$this->setGuidanceMsg('コメントを投稿しました');
				} else {
					$this->setUserErrorMsg('コメントの投稿に失敗しました');
				}
				$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			} else if ($act == 'sendcancel'){	// コメントキャンセルのとき
			}
			
			$this->isOutputComment = true;// コメントを出力するかどうか
			
			$this->tmpl->setAttribute('show_comment', 'visibility', 'visible');		// 既存コメントを表示
			$this->tmpl->addVar("_widget", "entry_id", $this->entryId);		// 記事を指定
			
			// ### コメント入力欄の表示 ###
			if (!empty($entryRow) && !empty($entryRow['be_receive_comment'])){		// コメントを受け付ける場合のみ表示
				if ((self::$_configArray[blog_mainCommonDef::CF_COMMENT_USER_LIMITED] && $this->gEnv->isCurrentUserLogined()) || 
					!self::$_configArray[blog_mainCommonDef::CF_COMMENT_USER_LIMITED]){		// ユーザ制限のときはログイン状態をチェック
					
					if (!self::$_configArray[blog_mainCommonDef::CF_COMMENT_USER_LIMITED]){	// ユーザ制限なしのときは名前、メールアドレスを表示
						$this->tmpl->setAttribute('user_info', 'visibility', 'visible');
					}
					$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');
					$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
					$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
				
					// 送信用スクリプト追加
					$this->tmpl->setAttribute('comment_script', 'visibility', 'visible');
					
					// 名前保存用のスクリプトライブラリ追加
					$this->tmpl->setAttribute('init_cookie', 'visibility', 'visible');
					$this->tmpl->setAttribute('update_cookie', 'visibility', 'visible');
					$this->addLib[] = self::COOKIE_LIB;
				}
			}
		}

		// ##### 記事の出力 #####
		// ***** 記事の出力は直接itemsLoop()を呼び出す方法に変更すべき *****
		// $this->itemsLoop($index, $fetchedRow)
		if ($showPreviewEntry){		// プレビュー記事を表示の場合
			$this->itemsLoop(0, $entryRow);
		} else {
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, $this->entryId, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/,
											''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null/*ブログ指定なし*/, null/*ユーザ指定なし*/, $this->preview);
			} else {
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, $this->entryId, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/,
											''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null/*ブログ指定なし*/, $this->_userId, $this->preview);
			}
		}
		
		// マルチブログのときはパンくずリストにブログ名を追加
		if ($this->useMultiBlog){
			if (!empty($entryRow)){		// 記事レコードがあるとき
				$blogUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $entryRow['bl_id']);
				$this->gPage->setHeadSubTitle($entryRow['bl_name'], $blogUrl);
			}
		}
		
		if ($this->isExistsViewData){
			// カノニカル属性を設定
			$url = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $this->entryId);
			$this->gPage->setCanonicalUrl($url);
		} else {
			// ブログ記事データがないときはデータなしメッセージ追加
			$this->title = $this->titleNoEntry;
			$this->message = $this->messageNoEntry;
			
			// ### 個別記事が見つからない場合はWebサーバ(Nginx)側で404ページを表示 ###
			// HTTPステータスコードの設定
			$this->gPage->setResponse(404/*存在しないページ*/);
		}
		
		// コンテンツタイトルを画面タイトルにする
		$this->pageTitle = $this->title;
			
		// ### 前画面、次画面への遷移ボタンを追加 ###
		if (empty($entryRow)) return;			// ブログ記事情報がない場合は終了
		$regDate = $entryRow['be_regist_dt'];	// 登録日付
				
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		$order = $request->trimValueOf('order');// 並び順(未使用)
		
		if (empty($category)){	// 年月、キーワード検索、条件なし取得の場合
			// キーワード分割
			// 検索キーワードは記録しない
			$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
			
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				$ret = self::$_mainDb->getPrevNextEntryByKeyword($regDate, $this->_langId, $this->entryViewOrder, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/, $parsedKeywords, $this->blogId, null/*ユーザ指定なし*/, $prevRow, $nextRow);
			} else {
				$ret = self::$_mainDb->getPrevNextEntryByKeyword($regDate, $this->_langId, $this->entryViewOrder, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/, $parsedKeywords, $this->blogId, $this->_userId, $prevRow, $nextRow);
			}
		} else {	// カテゴリーで取得の場合
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				$ret = self::$_mainDb->getPrevNextEntryByCategory($regDate, $this->_langId, $this->entryViewOrder, $category, $this->blogId, null/*ユーザ指定なし*/, $prevRow, $nextRow);
			} else {
				$ret = self::$_mainDb->getPrevNextEntryByCategory($regDate, $this->_langId, $this->entryViewOrder, $category, $this->blogId, $this->_userId, $prevRow, $nextRow);
			}
		}
		if ($ret){
			$prevUrl = '';
			$nextUrl = '';
			if (!empty($prevRow)){
				$url = $this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $prevRow['be_id'];
				if (!empty($keyword)) $url .= '&keyword=' . $keyword;
				if (!empty($category)) $url .= '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category;
				$prevUrl = $this->getUrl($url, true/*リンク用*/);
			}
			if (!empty($nextRow)){
				$url = $this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $nextRow['be_id'];
				if (!empty($keyword)) $url .= '&keyword=' . $keyword;
				if (!empty($category)) $url .= '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category;
				$nextUrl = $this->getUrl($url, true/*リンク用*/);
			}
			
			// 前画面、次画面遷移ボタン追加
			$showPrevNextEntryLinkPos	= self::$_configArray[blog_mainCommonDef::CF_SHOW_PREV_NEXT_ENTRY_LINK];	// 前後記事リンクを表示するかどうか
			$prevNextEntryLinkPos		= self::$_configArray[blog_mainCommonDef::CF_PREV_NEXT_ENTRY_LINK_POS];				// 前後記事リンク表示位置
			if ($showPrevNextEntryLinkPos) $this->setJoomlaPageNavData($prevNextEntryLinkPos, $prevUrl, $nextUrl);
		}
	}
	/**
	 * トップ一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showTopList($request)
	{
		$showTopContent = false;		// トップコンテンツを表示するかどうか
		$targetBlogId = null;		// 表示対象のブログID
		if ($this->useMultiBlog){		// マルチブログのとき
			// ブログIDが存在しないときはトップコンテンツを表示
			if (empty($this->blogId)){
				$showTopContent = true;
			} else {
				// 参照可能なブログかチェック
				$ret = self::$_mainDb->isActiveBlogInfo($this->blogId);
				if ($ret){			// ブログ情報が存在するとき
					// 参照権限をチェック
					if (!self::$_canEditEntry){		// 記事編集権限なしのとき
						$ret = self::$_mainDb->isReadableBlogInfo($this->blogId, $this->_userId);
						if (!$ret) $showTopContent = true;
					}
				} else {
					$showTopContent = true;
				}
				if (!$showTopContent){
					$targetBlogId = $this->blogId;
					
					// マルチブログ時のみの処理
					$ret = self::$_mainDb->getBlogInfoById($this->blogId, $row);
					if ($ret){
						// HTMLメタタグの設定
						$this->headTitle .= $row['bl_meta_title'];
						if (empty($this->headTitle)) $this->headTitle = $row['bl_name'];
						$this->headDesc = $row['bl_meta_description'];
						$this->headKeyword = $row['bl_meta_keywords'];
						
						// マルチブログタイトル
						$this->title = $row['bl_name'];
					}
				}
			}
		}

		if ($showTopContent){		// トップコンテンツを表示するとき
			// トップコンテンツを表示。トップコンテンツがない場合はブログ記事表示
			$topContent = self::$_configArray[blog_mainCommonDef::CF_MULTI_BLOG_TOP_CONTENT];// マルチブログ用トップコンテンツ
			if (empty($topContent)){		// トップコンテンツが設定されていない場合
				$showTopContent = false;
			} else {
				$this->tmpl->setAttribute('show_top_content', 'visibility', 'visible');
				$this->tmpl->addVar("show_top_content", "content", $topContent);
			}
		}
		if (!$showTopContent){				// トップコンテンツを表示しないとき
			// 総数を取得
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $this->startDt, $this->endDt, ''/*検索キーワード*/, $this->_langId, $targetBlogId, null, $this->preview);
			} else {
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $this->startDt, $this->endDt, ''/*検索キーワード*/, $this->_langId, $targetBlogId, $this->_userId, $this->preview);
			}
			$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
			
			// リンク文字列作成、ページ番号調整
			// マルチブログのときはブログIDを付加する
			$multiBlogParam = '';		// マルチブログ時の追加パラメータ
			if ($this->useMultiBlog) $multiBlogParam = '&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $targetBlogId;
			$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . $multiBlogParam);
			
			// ##### 作成されたページリンク情報を取得 #####
			$pageLinkInfo = $this->getPageLinkInfo();

			// 記事一覧作成
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/,
								''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $targetBlogId, null, $this->preview);
			} else {
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $this->startDt/*期間開始*/, $this->endDt/*期間終了*/,
								''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $targetBlogId, $this->_userId, $this->preview);
			}
			
			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
				// ##### ページ番号遷移ナビゲーションを作成 #####
				$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($this->pageNo -1) * $this->entryViewCount/*先頭に表示する項目のオフセット番号*/, $this->entryViewCount);
			} else {	// ブログ記事データがないときはデータなしメッセージ追加
				$this->title = $this->titleNoEntry;
				$this->message = $this->messageNoEntry;
				
				// ### 一覧表示画面の場合は404を返さない ###
				// HTTPステータスコードの設定
				//$this->gPage->setResponse(404/*存在しないページ*/);
			}
		}
	}
	/**
	 * 検索結果画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showSearchList($request)
	{
		// 入力値取得
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		
		// キーワード検索のとき
		if (empty($keyword)){
			$this->message = '検索キーワードが入力されていません';
		} else {
			// キーワード分割
			$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
			
			// 検索キーワードを記録
			for ($i = 0; $i < count($parsedKeywords); $i++){
				$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $parsedKeywords[$i]);
			}
			
			// 総数を取得
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, ''/*期間開始*/, ''/*期間終了*/, $parsedKeywords, $this->_langId, null/*ブログID*/, null, $this->preview);
			} else {
				$totalCount = self::$_mainDb->getEntryItemsCount($this->now, ''/*期間開始*/, ''/*期間終了*/, $parsedKeywords, $this->_langId, null/*ブログID*/, $this->_userId, $this->preview);
			}
			$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);

			// リンク文字列作成、ページ番号調整
			$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=search&keyword=' . urlencode($keyword));

			// ##### 作成されたページリンク情報を取得 #####
			$pageLinkInfo = $this->getPageLinkInfo();
			
			// 記事一覧を表示
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0, ''/*期間開始*/, ''/*期間終了*/,
													$parsedKeywords, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null/*ブログID*/, null, $this->preview);
			} else {
				self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0, ''/*期間開始*/, ''/*期間終了*/,
													$parsedKeywords, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null/*ブログID*/, $this->_userId, $this->preview);
			}
			
			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
				$this->message = '検索キーワード：' . $keyword;
				
				// ##### ページ番号遷移ナビゲーションを作成 #####
				$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($this->pageNo -1) * $this->entryViewCount/*先頭に表示する項目のオフセット番号*/, $this->entryViewCount);
			} else {	// 検索結果なしの場合
				$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
				$this->message = $this->messageFindNoEntry . '<br />検索キーワード：' . $keyword;
			}
		}
		$this->title = $this->titleSearchList;				// 検索一覧タイトル
		$this->pageTitle = self::DEFAULT_TITLE_SEARCH;		// 画面タイトル、パンくずリスト用タイトル
	}
	/**
	 * カテゴリー、アーカイブからの一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showList($request)
	{
		// 入力値取得
		$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$day = $request->trimValueOf('day');		// 日指定
		
		if (!empty($category)){				// カテゴリー指定のとき
			$this->showListType = 'category';				// 一覧表示タイプ(カテゴリー)
			$this->viewParam['category'] = $category;					// 表示用パラメータ
			
			// 総数を取得
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				$totalCount = self::$_mainDb->getEntryItemsCountByCategory($this->now, $category, $this->_langId);
			} else {
				$totalCount = self::$_mainDb->getEntryItemsCountByCategory($this->now, $category, $this->_langId, $this->_userId);
			}
			$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);

			// リンク文字列作成、ページ番号調整
			$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category);
			
			// ##### 作成されたページリンク情報を取得 #####
			$pageLinkInfo = $this->getPageLinkInfo();
			
			// 記事一覧を表示
			if ($this->isSystemManageUser){		// システム管理ユーザの場合
				self::$_mainDb->getEntryItemsByCategory($this->entryViewCount, $this->pageNo, $this->now, $category, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'));
			} else {
				self::$_mainDb->getEntryItemsByCategory($this->entryViewCount, $this->pageNo, $this->now, $category, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), $this->_userId);
			}

			// カテゴリータイトル、説明の設定
			$ret = $this->categoryDb->getCategoryByCategoryId($category, $this->gEnv->getDefaultLanguage(), $row);
			if ($ret){
				$this->title = str_replace('$1', $row['bc_name'], $this->titleList);
				
				// 一覧の先頭の場合は、カテゴリーの説明を付加
				if (!$this->isAccessWithPageNo) $this->categoryDesc = $row['bc_html'];// ページ番号付きのアクセスかどうか
			}
			
			// ブログ記事データがないときはデータなしメッセージ追加
			if ($this->isExistsViewData){
				// ページリンクを埋め込む
				if (!empty($pageLink)){
					$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
					$this->tmpl->addVar("page_link", "page_link", $pageLink);
				}
				
				// ##### ページ番号遷移ナビゲーションを作成 #####
				$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($this->pageNo -1) * $this->entryViewCount/*先頭に表示する項目のオフセット番号*/, $this->entryViewCount);
			} else {
				$this->title = $this->titleNoEntry;
				$this->message = $this->messageNoEntry;
				
				// ### 一覧表示画面の場合は404を返さない ###
				// HTTPステータスコードの設定
				//$this->gPage->setResponse(404/*存在しないページ*/);
			}
		} else if (!empty($year)){			// 年月日指定のとき
			if (!empty($month) && !empty($day)){		// 日指定のとき
				$this->showListType = 'day';				// 一覧表示タイプ(日)
				
				$startDt = $year . '/' . $month . '/' . $day;
				$endDt = $this->getNextDay($year . '/' . $month . '/' . $day);
				
				// 総数を取得
				if ($this->isSystemManageUser){		// システム管理ユーザの場合
					$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, null, null, $this->preview);
				} else {
					$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, null, $this->_userId, $this->preview);
				}
				$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
				
				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&year=' . $year . '&month=' . $month . '&day=' . $day);

				// ##### 作成されたページリンク情報を取得 #####
				$pageLinkInfo = $this->getPageLinkInfo();
			
				// 記事一覧作成
				if ($this->isSystemManageUser){		// システム管理ユーザの場合
					self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null, null, $this->preview);
				} else {
					self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null, $this->_userId, $this->preview);
				}
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					
					// ##### ページ番号遷移ナビゲーションを作成 #####
					$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($this->pageNo -1) * $this->entryViewCount/*先頭に表示する項目のオフセット番号*/, $this->entryViewCount);
				}
				
				// 年月日の表示
				$this->title = str_replace('$1', $year . '年 ' . $month . '月 ' . $day . '日', $this->titleList);
				
				// ブログ記事データがないときはデータなしメッセージ追加
				if (!$this->isExistsViewData){
					$this->message = $this->messageNoEntry;
					
					// ### 一覧表示画面の場合は404を返さない ###
					// HTTPステータスコードの設定
					//$this->gPage->setResponse(404/*存在しないページ*/);
				}
			} else if (!empty($month)){		// 月指定のとき
				$this->showListType = 'month';				// 一覧表示タイプ(月)
				
				$startDt = $year . '/' . $month . '/1';
				$endDt = $this->getNextMonth($year . '/' . $month) . '/1';
				
				// 総数を取得
				if ($this->isSystemManageUser){		// システム管理ユーザの場合
					$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, null, null, $this->preview);
				} else {
					$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, null, $this->_userId, $this->preview);
				}
				$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
				
				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&year=' . $year . '&month=' . $month);
				
				// ##### 作成されたページリンク情報を取得 #####
				$pageLinkInfo = $this->getPageLinkInfo();
			
				// 記事一覧作成
				if ($this->isSystemManageUser){		// システム管理ユーザの場合
					self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null, null, $this->preview);
				} else {
					self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null, $this->_userId, $this->preview);
				}
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					
					// ##### ページ番号遷移ナビゲーションを作成 #####
					$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($this->pageNo -1) * $this->entryViewCount/*先頭に表示する項目のオフセット番号*/, $this->entryViewCount);
				}
				// 年月の表示
				$this->title = str_replace('$1', $year . '年 ' . $month . '月', $this->titleList);
				
				// ブログ記事データがないときはデータなしメッセージ追加
				if (!$this->isExistsViewData){
					$this->message = $this->messageNoEntry;
					
					// ### 一覧表示画面の場合は404を返さない ###
					// HTTPステータスコードの設定
					//$this->gPage->setResponse(404/*存在しないページ*/);
				}
			} else {		// 年指定のとき
				$this->showListType = 'year';				// 一覧表示タイプ(年)
				
				$startDt = $year . '/1/1';
				$endDt = ($year + 1) . '/1/1';
				
				// 総数を取得
				if ($this->isSystemManageUser){		// システム管理ユーザの場合
					$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, null, null, $this->preview);
				} else {
					$totalCount = self::$_mainDb->getEntryItemsCount($this->now, $startDt, $endDt, ''/*検索キーワード*/, $this->_langId, null, $this->_userId, $this->preview);
				}
				$this->calcPageLink($this->pageNo, $totalCount, $this->entryViewCount);
				
				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($this->pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&year=' . $year);
				
				// ##### 作成されたページリンク情報を取得 #####
				$pageLinkInfo = $this->getPageLinkInfo();
			
				// 記事一覧作成
				if ($this->isSystemManageUser){		// システム管理ユーザの場合
					self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null, null, $this->preview);
				} else {
					self::$_mainDb->getEntryItems($this->entryViewCount, $this->pageNo, $this->now, 0/*期間で指定*/, $startDt/*期間開始*/, $endDt/*期間終了*/,
													''/*検索キーワード*/, $this->_langId, $this->entryViewOrder, array($this, 'itemsLoop'), null, $this->_userId, $this->preview);
				}
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					
					// ##### ページ番号遷移ナビゲーションを作成 #####
					$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($this->pageNo -1) * $this->entryViewCount/*先頭に表示する項目のオフセット番号*/, $this->entryViewCount);
				}
				// 年の表示
				$this->title = str_replace('$1', $year . '年', $this->titleList);
				
				// ブログ記事データがないときはデータなしメッセージ追加
				if (!$this->isExistsViewData){
					$this->message = $this->messageNoEntry;
					
					// ### 一覧表示画面の場合は404を返さない ###
					// HTTPステータスコードの設定
					//$this->gPage->setResponse(404/*存在しないページ*/);
				}
			}
		}
		$this->pageTitle = $this->title;		// カテゴリー名を画面タイトルにする
	}
	/**
	 * ヘッダ部メタタグの設定
	 *
	 * HTMLのheadタグ内に出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ。連想配列で「title」「description」「keywords」を設定。
	 */
	function _setHeadMeta($request, &$param)
	{
		$headData = array(	'title' => $this->headTitle,
							'description' => $this->headDesc,
							'keywords' => $this->headKeyword);
		return $headData;
	}
	/**
	 * JavascriptライブラリをHTMLヘッダ部に設定
	 *
	 * JavascriptライブラリをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string,array 				Javascriptライブラリ。出力しない場合は空文字列を設定。
	 */
	function _addScriptLibToHead($request, &$param)
	{
		return $this->addLib;
	}
	/**
	 * JavascriptをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addScriptToHead($request, &$param)
	{
		return $this->headScript;
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
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){
			return '';
		} else {
			return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
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
		return $this->widgetTitle;	// ウィジェットタイトル
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
		// 参照ビューカウントを更新
/*		if (!$this->isSystemManageUser &&		// システム運用者以上の場合はカウントしない
			!$this->isCmdAccess){				// cmd付きアクセスでない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, $fetchedRow['be_serial'], $this->currentDay, $this->currentHour, $fetchedRow['be_id']);
		}*/
		$this->gInstance->getAnalyzeManager()->logContentView(blog_mainCommonDef::VIEW_CONTENT_TYPE, $fetchedRow['be_serial'], $fetchedRow['be_id']);

		$serial = $fetchedRow['be_serial'];		// シリアル番号
		$entryId = $fetchedRow['be_id'];// 記事ID
		$title = $fetchedRow['be_name'];// タイトル
		$date = $fetchedRow['be_regist_dt'];// 登録日時
		$author	= $fetchedRow['lu_name'];	// 投稿者
		$summary	= $fetchedRow['be_description'];	// 概要
		$showComment = $fetchedRow['be_show_comment'];				// コメントを表示するかどうか
		$blogId = $fetchedRow['be_blog_id'];						// ブログID
		$accessPointUrl = $this->gEnv->getDefaultUrl();
		$startDt = $fetchedRow['be_active_start_dt'];		// コンテンツ置換キー(公開開始日時)
		$endDt = $fetchedRow['be_active_end_dt'];		// コンテンツ置換キー(公開終了日時)
		
		// コメントを取得
		$commentCount = $this->commentDb->getCommentCountByEntryId($entryId, $this->_langId);	// コメント総数
		if ($this->isOutputComment){// コメントを出力のとき
			// コメントの内容を取得
			$ret = $this->commentDb->getCommentByEntryId($entryId, $this->_langId, $row);
			if ($ret){
				$this->tmpl->clearTemplate('commentlist');
				for ($i = 0; $i < count($row); $i++){
					$permalink = '#' . self::COMMENT_PERMA_HEAD . $row[$i]['bo_no'];		// コメントパーマリンク
					$commentTitle = $row[$i]['bo_name'];			// コメントタイトル
					if (empty($commentTitle)) $commentTitle = self::NO_COMMENT_TITLE;
					$commentTitle = '<a href="' . $permalink . '">' . $this->convertToDispString($commentTitle) . '</a>';
					
					// コメント投稿ユーザ名
					if (self::$_configArray[blog_mainCommonDef::CF_COMMENT_USER_LIMITED]){	// ユーザ制限のときはログインユーザ名を取得
						$userName = $row[$i]['lu_name'];
					} else {
						$userName = $row[$i]['bo_user_name'];	// 入力値を使用
					}
					$url = $row[$i]['bo_url'];
					if (!empty($url)) $url = '<a href="' . $this->convertUrlToHtmlEntity($url) . '" target="_blank">URL</a>';
					$comment = '<div class="blog_comment_body">' . $this->convertToPreviewText($this->convertToDispString($row[$i]['bo_html'])) . '</div>';		// 改行コードをbrタグに変換
					
					// アバター
					$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl($row[$i]['lu_avatar']);
					$avatarTitle = $this->convertToDispString($userName) . self::AVATAR_TITLE_TAIL;
					$avatarTag = '<img src="' . $this->getUrl($avatarUrl) . '" width="' . $this->avatarSize . '" height="' . $this->avatarSize . 
									'" border="0" alt="' . $avatarTitle . '" title="' . $avatarTitle . '" />';
		
					// Magic3マクロ変換
					// あらかじめ「CM_」タグをすべて取得する?
					$contentParam = array();
					$contentParam[M3_TAG_MACRO_COMMENT_AUTHOR] = $this->convertToDispString($userName);			// コンテンツ置換キー(著者)
					$contentParam[M3_TAG_MACRO_COMMENT_DATE] = $this->timestampToDate($row[$i]['bo_regist_dt']);		// コンテンツ置換キー(登録日)
					$contentParam[M3_TAG_MACRO_COMMENT_TIME] = $this->timestampToTime($row[$i]['bo_regist_dt']);		// コンテンツ置換キー(登録時)
					$contentParam[M3_TAG_MACRO_AVATAR] = $avatarTag;
					$contentParam[M3_TAG_MACRO_TITLE] = $commentTitle;
					$contentParam[M3_TAG_MACRO_BODY] = $comment;
					$contentParam[M3_TAG_MACRO_URL] = $url;
					$commentText = $this->createComment($contentParam);

					$commentRow = array(
						'comment'		=> $commentText			// コメント内容
					);
					$this->tmpl->addVars('commentlist', $commentRow);
					$this->tmpl->parseTemplate('commentlist', 'a');
				}
			} else {	// コメントなしのとき
				$this->tmpl->clearTemplate('commentlist');
				$commentRow = array(
					'comment'		=> 'コメントはありません',			// コメント内容
					'comment_info'	=> ''						// コメント情報
				);
				$this->tmpl->addVars('commentlist', $commentRow);
				$this->tmpl->parseTemplate('commentlist', 'a');
			}
		}
		
		// 記事へのリンクを生成
		$permaLinkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);		// 記事単体
		switch ($this->showListType){	// 一覧表示タイプ
		case 'category':				// 一覧表示タイプ(カテゴリー)
			$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId . '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $this->viewParam['category'], true/*リンク用*/);		// カテゴリーID付加
			break;
		default:
			$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);
			break;
		}
		
		// ユーザ定義フィールド値取得
		// 埋め込む文字列はHTMLエスケープする
		if ($viewMode == 10){		// 記事単体表示の場合
			$fieldInfoArray = blog_mainCommonDef::parseUserMacro(self::$_configArray[blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE]);
		} else {
			$fieldInfoArray = blog_mainCommonDef::parseUserMacro(self::$_configArray[blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST]);
		}
		$userFields = array();
		if (!empty($fieldInfoArray)){
			$fieldValueArray = $this->unserializeArray($fetchedRow['be_option_fields']);
			$fieldKeys = array_keys($fieldInfoArray);
			for ($i = 0; $i < count($fieldKeys); $i++){
				$key = $fieldKeys[$i];
				$value = $fieldValueArray[$key];
				$userFields[$key] = isset($value) ? $this->convertToDispString($value) : '';
			}
		}
			
		// カレント言語がデフォルト言語と異なる場合はデフォルト言語の添付ファイルを取得
		$isDefaltContent = false;	// デフォルト言語のコンテンツを取得したかどうか
		if ($this->_isMultiLang && $this->_langId != $this->gEnv->getDefaultLanguage()){
			$ret = self::$_mainDb->getEntryItem($entryId, $this->_langId, $defaltContentRow);
			if ($ret) $isDefaltContent = true;
		}
			
		// コンテンツのサムネールを取得
		$thumbFilename = $fetchedRow['be_thumb_filename'];
		if ($isDefaltContent) $thumbFilename = $defaltContentRow['be_thumb_filename'];
		$thumbUrl = blog_mainCommonDef::getEyecatchImageUrl($thumbFilename, self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE]);

		// ブログへのリンクを作成
		$blogLink = '';
		if ($this->useMultiBlog && !empty($blogId)){
			$blogName = $fetchedRow['bl_name'];// ブログ名
			$blogUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId);
			$blogLink = '<a href="' . $this->convertUrlToHtmlEntity($blogUrl) . '" >' . $this->convertToDispString($blogName) . '</a>';
		}

		// カテゴリーリンクブロック
		$categoryTag = '';
		$defaultSerial = $fetchedRow['be_serial'];
		if ($isDefaltContent) $defaultSerial = $defaltContentRow['be_serial'];		// デフォルト言語のカテゴリーを取得
		$ret = self::$_mainDb->getEntryBySerial($defaultSerial, $row, $categoryRows);
		if ($ret){
			// テキストスタイルのとき
			$categoryItems = array();
			$categoryTag .= '<div class="' . self::CATEGORY_BLOCK_CLASS . '"><strong>' . self::CATEGORY_BLOCK_LABEL . '</strong>';		// カテゴリー項目のラベル
			for ($i = 0; $i < count($categoryRows); $i++){
				$categoryUrl = $this->currentPageUrl . '&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $categoryRows[$i]['bc_id'];	// カテゴリーリンク先
				$categoryItems[] = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($categoryUrl)) . '">' . $this->convertToDispString($categoryRows[$i]['bc_name']) . '</a>';
			}
			$categoryTag .= implode(self::CATEGORY_BLOCK_SEPARATOR, $categoryItems);	// リンク項目を連結
			$categoryTag .= '</div>';
		}
		// コメントへのリンク
		$commentLink = '';
		if ($this->viewMode != 10 && $this->receiveComment && $showComment){	// 記事単体表示でコメントを表示する場合
			$commentLink = '<strong><a href="' . $this->convertUrlToHtmlEntity($linkUrl . '#comment') . '" >コメント(' . $commentCount . ')</a></strong>';	// コメントへのリンク
		}
				
		// 関連コンテンツリンク
		$relatedContentTag = '';	// 関連コンテンツリンク
		if ($this->viewMode == 10){	// 記事単体表示のとき
			$relatedContent = $fetchedRow['be_related_content'];
			if ($isDefaltContent) $relatedContent = $defaltContentRow['be_related_content'];
			if (!empty($relatedContent)){
				$contentIdArray = array_map('trim', explode(',', $relatedContent));
				$contentIdArray = array_map('intval', $contentIdArray);		// 不正なIDを除く(2016/5/13)
				$ret = self::$_mainDb->getEntryItem($contentIdArray, $this->_langId, $rows);
				if ($ret){
					if ($this->_renderType == M3_RENDER_JOOMLA_NEW){		// Joomla!新型テンプレートのとき
						$relatedContentTag .= '<h' . ($this->itemTagLevel) . '>' . self::TITLE_RELATED_CONTENT_BLOCK . '</h' . ($this->itemTagLevel) . '>';
					} else {
						$relatedContentTag .= '<h' . ($this->itemTagLevel + 1) . '>' . self::TITLE_RELATED_CONTENT_BLOCK . '</h' . ($this->itemTagLevel + 1) . '>';
					}
					$relatedContentTag .= '<ul>';
					for ($i = 0; $i < count($rows); $i++){
						$relatedUrl = $accessPointUrl . '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $rows[$i]['be_id'];	// 関連コンテンツリンク先
						$relatedContentTag .= '<li><a href="' . $this->convertUrlToHtmlEntity($this->getUrl($relatedUrl)) . '">' . $this->convertToDispString($rows[$i]['be_name']);
						$relatedContentTag .= '</a></li>';
					}
					$relatedContentTag .= '</ul>';
				}
			}
		}

		// Magic3マクロ変換
		// あらかじめ「CT_」タグをすべて取得する?
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $fetchedRow['be_id'];			// コンテンツ置換キー(エントリーID)
		$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $linkUrl;// コンテンツ置換キー(エントリーURL)
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $fetchedRow['be_name'];			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $fetchedRow['be_description'];			// コンテンツ置換キー(簡易説明)
		$contentInfo[M3_TAG_MACRO_CONTENT_IMAGE] = $this->getUrl($thumbUrl);		// コンテンツ置換キー(画像)
		$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $fetchedRow['be_create_dt'];		// コンテンツ置換キー(更新日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_START_DT] = $startDt;		// コンテンツ置換キー(公開開始日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_END_DT] = $endDt;		// コンテンツ置換キー(公開終了日時)
		if ($this->useMultiBlog && !empty($blogId)){
			$contentInfo[M3_TAG_MACRO_CONTENT_BLOG_ID]		= $blogId;			// コンテンツ置換キー(ブログID)
			$contentInfo[M3_TAG_MACRO_CONTENT_BLOG_TITLE]	= $fetchedRow['bl_name'];			// コンテンツ置換キー(ブログタイトル)
		}
		// ### Joomla!新型テンプレートの場合はテンプレートに組み込みの表示機能を使用する ###
		if ($this->_renderType == M3_RENDER_JOOMLA_NEW){		// Joomla!新型テンプレートの場合
			$contentInfo[M3_TAG_MACRO_CONTENT_AUTHOR] = '';			// コンテンツ置換キー(著者)
			$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = '';		// コンテンツ置換キー(登録日時)
			$contentInfo[M3_TAG_MACRO_CONTENT_DATE] = '';		// コンテンツ置換キー(登録日)
			$contentInfo[M3_TAG_MACRO_CONTENT_TIME] = '';		// コンテンツ置換キー(登録時)
			if ($this->showEntryViewCount) $viewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, $serial);
			$contentInfo[M3_TAG_MACRO_CONTENT_VIEW_COUNT] = '';			// コンテンツ置換キー(閲覧数)
		} else {
			if ($this->showEntryAuthor){		// 投稿者
				$contentInfo[M3_TAG_MACRO_CONTENT_AUTHOR] = $author;			// コンテンツ置換キー(著者)
			} else {
				$contentInfo[M3_TAG_MACRO_CONTENT_AUTHOR] = '';			// コンテンツ置換キー(著者)
			}
			if ($this->showEntryRegistDt){		// 投稿日時
				$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = $date;		// コンテンツ置換キー(登録日時)
				$contentInfo[M3_TAG_MACRO_CONTENT_DATE] = $this->timestampToDate($date);		// コンテンツ置換キー(登録日)
				$contentInfo[M3_TAG_MACRO_CONTENT_TIME] = $this->timestampToTime($date);		// コンテンツ置換キー(登録時)
			} else {
				$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = '';		// コンテンツ置換キー(登録日時)
				$contentInfo[M3_TAG_MACRO_CONTENT_DATE] = '';		// コンテンツ置換キー(登録日)
				$contentInfo[M3_TAG_MACRO_CONTENT_TIME] = '';		// コンテンツ置換キー(登録時)
			}
			if ($this->showEntryViewCount){		// 閲覧数を表示するかどうか
				$viewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, $serial);
				$contentInfo[M3_TAG_MACRO_CONTENT_VIEW_COUNT] = $viewCount;			// コンテンツ置換キー(閲覧数)
			} else {
				$contentInfo[M3_TAG_MACRO_CONTENT_VIEW_COUNT] = '';			// コンテンツ置換キー(閲覧数)
			}
		}
		
		// メインのコンテンツデータを作成
		$isMoreContentExists = false;		// 「もっと読む」ボタンを作成するかどうか
		$entryHtml = $fetchedRow['be_html'];
		$imageTag = '';			// 記事サムネール画像(リンク付き)
		if ($this->viewMode == 10){	// 記事単体表示のとき
			if (!empty($fetchedRow['be_html_ext'])) $entryHtml = $fetchedRow['be_html_ext'];// 続きがある場合は続きを出力
			
			// ##### HTMLヘッダにタグ出力 #####
			$metaDesc = $fetchedRow['be_meta_description'];		// ページ要約(METAタグ)
			if (!empty($metaDesc)) $this->headDesc = $metaDesc;
			$metaKeyword = $fetchedRow['be_meta_keywords'];		// ページキーワード(METAタグ)
			if (!empty($metaKeyword)) $this->headKeyword = $metaKeyword;

			if ($this->outputHead){			// OGP等のヘッダ出力するかどうか
				$headText = self::$_configArray[blog_mainCommonDef::CF_HEAD_VIEW_DETAIL];
				$headText = $this->convertM3ToHead($headText, $contentInfo);
				$this->gPage->setHeadOthers($headText);
			}
		} else {
			switch ($this->entryListDispType){
			case 0:		// 記事一覧表示タイプがコンテンツ表示の場合
			default:
				// ##### 「もっと読む」ボタンの作成 #####
				if (!empty($fetchedRow['be_html_ext'])){
					// 旧テンプレート処理の場合は「もっと読む」ボタンを出力
					if ($this->_renderType != M3_RENDER_JOOMLA_NEW && $this->showReadmore) $entryHtml .= '<div>' . self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $this->convertToDispString($this->readmoreLabel) . '</a></div>';
				
					$isMoreContentExists = true;	// 「もっと読む」ボタンを表示する
				}
				break;
			case 1:		// 記事一覧表示タイプが概要表示の場合
				$entryHtml = $this->convertToDispString($summary);		// 概要
				
				// 画像タグ作成
				if ($this->showEntryListImage){			// サムネール画像を表示する場合
					$imageUrl = $this->getListImageUrl($entryId);
					$imageTitle = $this->convertToDispString($title);
					$imageTag = '<img src="' . $this->getUrl($imageUrl) . '" alt="' . $imageTitle . '" title="' . $imageTitle . '" />';
					$imageTag = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $imageTag . '</a>';
				}
				
				// 旧テンプレート処理の場合は「もっと読む」ボタンを出力
				if ($this->_renderType != M3_RENDER_JOOMLA_NEW && $this->showReadmore) $entryHtml .= '<div>' . self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $this->convertToDispString($this->readmoreLabel) . '</a></div>';
					
				$isMoreContentExists = true;		// 「もっと読む」ボタンを表示
				break;
			}
		}
		
		// 編集権限がある場合、記事が非公開の場合はクラス名を追加
		$statusClass = '';		// 記事状態クラス
		if (self::$_canEditEntry){
			$isActive = false;		// 公開状態
			if ($fetchedRow['be_status'] == 2) $isActive = $this->_isActive($startDt, $endDt, $this->now);// 表示可能
			if (!$isActive) $statusClass = ' m3content_private';		// 非公開記事の場合はクラス名を付加
		}
		if (!empty($entryHtml)) $entryHtml = '<div class="' . self::ENTRY_BODY_BLOCK_CLASS . $statusClass .'">' . $entryHtml . '</div>';// DIVで括る
		
		// コンテンツレイアウトに埋め込む
		/*$contentParam = array_merge($userFields, array(M3_TAG_MACRO_TITLE => $titleTag, M3_TAG_MACRO_BLOG_LINK => $blogLink, M3_TAG_MACRO_BODY => $entryHtml,
														//M3_TAG_MACRO_FILES => '', M3_TAG_MACRO_PAGES => '',
														'LINKS' => $relatedContentTag, M3_TAG_MACRO_CATEGORY => $categoryTag, M3_TAG_MACRO_COMMENT_LINK => $commentLink));*/
		$contentParam = array_merge($userFields, array(	M3_TAG_MACRO_TITLE => ''/*タイトル*/, M3_TAG_MACRO_BLOG_LINK => $blogLink, M3_TAG_MACRO_IMAGE => $imageTag, M3_TAG_MACRO_BODY => $entryHtml,
														/*M3_TAG_MACRO_FILES => '', M3_TAG_MACRO_PAGES => '',*/
														'LINKS' => $relatedContentTag, M3_TAG_MACRO_CATEGORY => $categoryTag, M3_TAG_MACRO_COMMENT_LINK => $commentLink));
		$entryHtml = $this->createDetailContent($this->viewMode, $contentParam);
		$entryHtml = $this->convertM3ToHtml($entryHtml, true/*改行コーをbrタグに変換*/, $contentInfo);		// コンテンツマクロ変換
		
		// タイトルを付加(コンテンツの先頭にタイトルを置く、旧タイトル設定方法)
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW){
			$titleTag = '<h' . $this->itemTagLevel . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $this->convertToDispString($title) . '</a></h' . $this->itemTagLevel . '>';
			$entryHtml = $titleTag . $entryHtml;
		}
		
		// ##### コンテンツ編集権限がある場合の追加機能 #####
		$buttonList = '';		// 記事編集ボタン
		if (self::$_canEditEntry) $buttonList = $this->createButtonTag($fetchedRow['be_serial']);// 編集権限があるとき
		
		$row = array(
			'entry' => $entryHtml,	// 投稿記事
			'button_list' => $buttonList	// 記事編集ボタン
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		
		//##### Joomla!新型描画処理でない場合は終了 #####
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW) return true;
		
		// ### Joomla!新型テンプレート用データ作成 ###
		$viewItem = new stdClass;
		$viewItem->id			= $entryId;	// コンテンツID
		$viewItem->title		= $title;	// コンテンツ名
		if ($this->entryListDispType == 0){			// 一覧の表示タイプがコンテンツ表示(カテゴリー、関連記事付き)の場合
			$viewItem->introtext	= $this->buttonList . $buttonList . $entryHtml;	// コンテンツ内容(Joomla!2.5以降テンプレート用)
		} else {		// タイトル・概要表示の場合
			// 「概要」または「本文1」を表示する
			if (empty($summary)){		// 「概要」が設定されていない場合
				// 「本文1」から画像を削除して概要を作成
				$summaryHtml = $this->convertM3ToHtml($fetchedRow['be_html'], true/*改行コーをbrタグに変換*/, $contentInfo);		// コンテンツマクロ変換
				$summaryHtml = $this->gInstance->getTextConvManager()->deleteTag($summaryHtml, array('img'));	// 画像タグは削除
				$viewItem->introtext	= $this->buttonList . $buttonList . $summaryHtml;
			} else {
				$viewItem->introtext	= $this->buttonList . $buttonList . $this->convertToDispString($summary);		// 概要
			}
		}
		$viewItem->text			= $viewItem->introtext;	// コンテンツ内容(Joomla!1.5テンプレート用)
		$viewItem->state		= 1;			// 表示モード(0=新着,1=表示済み)
		$viewItem->url			= $linkUrl;						// リンク先。viewItem->urlはMagic3の拡張値。

		// サムネール画像の設定
		if ($this->entryListDispType == 1){	// 記事一覧表示タイプが概要表示の場合
			if ($this->showEntryListImage){			// サムネール画像を表示する場合
				$imageUrl = $this->getListImageUrl($entryId);
				if (!empty($imageUrl)){
					$viewItem->thumbUrl	= $imageUrl;
					$viewItem->thumbAlt	= $title;
				}
			}
		}
		
		// 「もっと読む」のボタンを表示するかどうかは$viewItem->readmoreに値が設定されているかどうかで判断する
		if ($this->showReadmore && $isMoreContentExists){
			$viewItem->readmore	= 1;			// 「もっと読む」のボタンを表示する場合
			//$viewItem->readmorelavel	= 'some text';			// 個別に変更する場合
		}

		// 以下は表示する項目のみ値を設定する
		if ($this->showEntryAuthor) $viewItem->author		= $author;		// 投稿者
		if ($this->showEntryRegistDt) $viewItem->published	= $date;		// 投稿日時
		if ($this->showEntryViewCount) $viewItem->hits		= $viewCount;	// 閲覧数
		$this->viewItemsData[] = $viewItem;			// Joomla!ビュー用データ
		return true;
	}
	/**
	 * 詳細コンテンツを作成
	 *
	 * @param int $viewMode				表示モード
	 * @param array	$contentParam		コンテンツ作成用パラメータ
	 * @return string			作成コンテンツ
	 */
	function createDetailContent($viewMode, $contentParam)
	{
		static $initContentText;
		
		if (!isset($initContentText)){
			if ($viewMode == 10){		// 記事単体表示の場合
				$initContentText = self::$_configArray[blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE];			// コンテンツレイアウト(記事詳細)
				if (empty($initContentText)) $initContentText = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;
			} else {
				switch ($this->entryListDispType){
				case 0:		// 記事一覧表示タイプがコンテンツ表示の場合
				default:
					$initContentText = self::$_configArray[blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST];			// コンテンツレイアウト(記事一覧)
					if (empty($initContentText)) $initContentText = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_LIST;
					break;
				case 1:		// 記事一覧表示タイプが概要表示の場合
					$initContentText = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_LIST_SUMMARY;
					break;
				}
			}
		}
		
		// コンテンツを作成
		$contentText = $initContentText;
		$keys = array_keys($contentParam);
		for ($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			$value = str_replace('\\', '\\\\', $contentParam[$key]);	// ##### (注意)preg_replaceで変換値のバックスラッシュが解釈されるので、あらかじめバックスラッシュを2重化しておく必要がある
			
			$pattern = '/' . preg_quote(M3_TAG_START . $key) . ':?(.*?)' . preg_quote(M3_TAG_END) . '/u';
			$contentText = preg_replace($pattern, $value, $contentText);
		}
		return $contentText;
	}
	/**
	 * コメントを作成
	 *
	 * @param array	$contentParam		コンテンツ作成用パラメータ
	 * @return string			作成コンテンツ
	 */
	function createComment($contentParam)
	{
		static $initContentText;
		if (!isset($initContentText)) $initContentText = self::$_configArray[blog_mainCommonDef::CF_LAYOUT_COMMENT_LIST];			// コンテンツレイアウト(コメント一覧)
		
		// コンテンツを作成
		$contentText = $initContentText;
		$keys = array_keys($contentParam);
		for ($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			$value = str_replace('\\', '\\\\', $contentParam[$key]);	// ##### (注意)preg_replaceで変換値のバックスラッシュが解釈されるので、あらかじめバックスラッシュを2重化しておく必要がある
			
			$pattern = '/' . preg_quote(M3_TAG_START . $key) . ':?(.*?)' . preg_quote(M3_TAG_END) . '/u';
			$contentText = preg_replace($pattern, $value, $contentText);
		}
		return $contentText;
	}
	/**
	 * 編集用ボタンタグ作成
	 *
	 * @param int $serial		ブログ記事のシリアル番号(0のときは新規ボタン)
	 * @return string			タグ
	 */
	function createButtonTag($serial)
	{
		$buttonList = '';
		
		if (empty($serial)){
			// 投稿ユーザの場合は投稿管理画面(ブログ記事、コメントの投稿管理)への遷移用リンクを追加
			if (!$this->isSystemManageUser){
				$iconUrl = $this->gEnv->getRootUrl() . self::CONFIG_ICON_FILE;		// 投稿管理アイコン
				$iconTitle = '投稿管理';
				$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" data-container="body" />';
				$buttonLink = '<a href="javascript:void(0);" onclick="showConfig();">' . $editImg . '</a>';
				$buttonList .= '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;position:relative;">' . $buttonLink . '</div>';		// *** スタイルは直接設定する必要あり ***
			
				$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// アイコンの位置を更新
			}
			
			$iconUrl = $this->gEnv->getRootUrl() . self::NEW_ICON_FILE;		// 新規アイコン
			$iconTitle = '記事作成';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" data-container="body" />';
			$buttonLink = '<a href="javascript:void(0);" onclick="editEntry(0);">' . $editImg . '</a>';
			$buttonList .= '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;position:relative;">' . $buttonLink . '</div>';		// *** スタイルは直接設定する必要あり ***
			
			$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// アイコンの位置を更新
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '記事編集';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" data-container="body" />';
			$buttonLink = '<a href="javascript:void(0);" onclick="editEntry(' . $serial . ');">' . $editImg . '</a>';
			$buttonList .= '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;position:relative;">' . $buttonLink . '</div>';		// *** スタイルは直接設定する必要あり ***
			
			$this->editIconPos = self::EDIT_ICON_MIN_POS;			// アイコンの位置を初期位置に戻す
		}
		return $buttonList;
	}
	/**
	 * Javascriptデータ作成処理コールバック
	 *
	 * @param object  $tmpl			テンプレートオブジェクト
	 * @param array   $params		スクリプトのタイプ(0=管理者用、1=投稿者用)とマルチブログ用クエリー文字列の配列
	 * @param						なし
	 */
	function makeScript($tmpl, $params)
	{
		list($type, $multiBlogParam) = $params;
		
		switch ($type){
		case 0:		// 管理者用
			$editUrl = $this->getConfigAdminUrl('openby=simple&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam);
			$configUrl = $this->getConfigAdminUrl('openby=other');
			
			$tmpl->setAttribute('admin_script', 'visibility', 'visible');
			$tmpl->addVar("admin_script", "edit_url", $editUrl);
			$tmpl->addVar("admin_script", "config_url", $configUrl);
			break;
		case 1:		// 投稿者用
			// 投稿管理画面へのURL作成
			$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
			$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
			$urlparam .= 'openby=other' . $multiBlogParam;
			$configUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;

			// 編集用画面へのURL作成
			$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
			$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
			$urlparam .= 'openby=simple&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam;
			//$urlparam .= 'openby=other&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam;
			$editUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;

			// 設定画面表示用のスクリプトを埋め込む
			$tmpl->setAttribute('edit_script', 'visibility', 'visible');
			$tmpl->addVar("edit_script", "config_url", $this->getUrl($configUrl));
			$tmpl->addVar("edit_script", "edit_url", $this->getUrl($editUrl));
			break;
		}
	}
	/**
	 * メッセージを追加
	 *
	 * @param string $msgHtml				メッセージコンテンツ
	 * @return								なし
	 */
	function addPageHeadingContent($msgHtml)
	{
		// メッセージをカテゴリー説明の前に追加
		$this->categoryDesc = '<div>' . $msgHtml . '</div>' . $this->categoryDesc;
/*		// ### Joomla!新型テンプレート用データ作成 ###
		$viewItem = new stdClass;
		$viewItem->introtext	= $msgHtml;	// コンテンツ内容(Joomla!2.5以降テンプレート用)
		$viewItem->text			= $msgHtml;	// コンテンツ内容(Joomla!1.5テンプレート用)
		$viewItem->state		= 1;			// 表示モード(0=新着,1=表示済み)
		
		// 既存のビューデータの先頭に追加
		$this->viewItemsData = array_merge(array($viewItem), $this->viewItemsData);
		*/
	}
	/**
	 * 記事編集用のボタンを作成
	 *
	 * @return								なし
	 */
	function createEditButton()
	{
		if (self::$_canEditEntry){		// 記事編集権限ありのとき
			// ### 共通ボタンタグ埋め込み ###
			$this->tmpl->setAttribute('button_list', 'visibility', 'visible');
			$this->tmpl->addVar("button_list", "button_list", $this->buttonList);
			
			// ### 編集ボタン用のスクリプトを埋め込む ###
			$multiBlogParam = '';		// マルチブログ時の追加パラメータ
			if ($this->useMultiBlog){
				// ブログIDが空のときは取得
				if (empty($this->blogId)){
					$bId = '';
					$blogLibObj = $this->gInstance->getObject(blog_mainCommonDef::BLOG_OBJ_ID);
					if (isset($blogLibObj)) $bId = $blogLibObj->getBlogId();
					if (!empty($bId)) $multiBlogParam = '&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $bId;
				} else {
					$multiBlogParam = '&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $this->blogId;
				}
			}
			
			if ($this->isSystemManageUser){		// 管理者権限ありのとき
				$editUrl = $this->getConfigAdminUrl('openby=simple&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam);
				$configUrl = $this->getConfigAdminUrl('openby=other');
				
/*				$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
				$this->tmpl->addVar("admin_script", "edit_url", $editUrl);
				$this->tmpl->addVar("admin_script", "config_url", $configUrl);*/
				$this->headScript = $this->getParsedTemplateData('edit.tmpl.js', array($this, 'makeScript'), array(0/*管理者用スクリプト*/, $multiBlogParam));
			} else {			// 投稿ユーザのとき
				// 投稿管理画面へのURL作成
				$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
				$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
				$urlparam .= 'openby=other' . $multiBlogParam;
				$configUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;

				// 編集用画面へのURL作成
				$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
				$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
				$urlparam .= 'openby=simple&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam;
				//$urlparam .= 'openby=other&task=' . self::TASK_ENTRY_DETAIL . $multiBlogParam;
				$editUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;

				// 設定画面表示用のスクリプトを埋め込む
/*				$this->tmpl->setAttribute('edit_script', 'visibility', 'visible');
				$this->tmpl->addVar("edit_script", "config_url", $this->getUrl($configUrl));
				$this->tmpl->addVar("edit_script", "edit_url", $this->getUrl($editUrl));
				*/
				$this->headScript = $this->getParsedTemplateData('edit.tmpl.js', array($this, 'makeScript'), array(1/*投稿者用スクリプト*/, $multiBlogParam));
			}
		}
	}
	/**
	 * 一覧用画像URLの取得
	 *
	 * @param string $entryId				記事ID
	 * @return string						画像のURL
	 */
	function getListImageUrl($entryId)
	{
		$imageUrl = '';
		$filename = $this->gInstance->getImageManager()->getThumbFilename($entryId, $this->entryListImageType);
		$path = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_BLOG, 0/*PC用*/, $filename);
		if (!file_exists($path)){
			$filename = $this->gInstance->getImageManager()->getThumbFilename(0, $this->entryListImageType);		// デフォルト画像ファイル名
			$path = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_BLOG, 0/*PC用*/, $filename);
		}
		$imageUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_BLOG, 0/*PC用*/, $filename);
		
		return $imageUrl;
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function _isActive($startDt, $endDt, $now)
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
}
?>
