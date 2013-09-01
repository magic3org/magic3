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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/event_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/event_categoryDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/event_commentDb.php');

class event_mainTopWidgetContainer extends event_mainBaseWidgetContainer
{
	private $categoryDb;	// DB接続オブジェクト
	private $commentDb;			// DB接続オブジェクト
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $currentPageUrl;			// 現在のページURL
	private $langId;		// 言語
	private $isOutputComment;		// コメントを出力するかどうか
	private $isExistsViewData;				// 表示データがあるかどうか
	private $viewExtEntry;			// 結果を表示するかどうか
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $pageTitle;				// 画面タイトル、パンくずリスト用タイトル
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $addLib = array();		// 追加スクリプト
	const CONTENT_TYPE = 'ev';
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const MESSAGE_NO_ENTRY_TITLE = 'イベント記事未登録';
	const MESSAGE_NO_ENTRY		= 'イベント記事は登録されていません';				// イベント記事が登録されていないメッセージ
//	const MESSAGE_NO_ENTRY_IN_FUTURE	= '今後のイベント記事は登録されていません';				// 今後のイベント記事が登録されていないメッセージ
	const MESSAGE_FIND_NO_ENTRY	= 'イベント記事が見つかりません';
	const MESSAGE_EXT_ENTRY		= '結果を見る';					// イベント記事に結果がある場合の表示
	const MESSAGE_EXT_ENTRY_PRE	= '…&nbsp;';							// イベント記事に結果がある場合の表示
	const COMMENT_TITLE		= ' についてのコメント';	// コメント用タイトル
	const ICON_SIZE = 32;		// アイコンのサイズ
	const EDIT_ICON_FILE = '/images/system/page_edit32.png';		// 編集アイコン
	const NEW_ICON_FILE = '/images/system/page_add32.png';		// 新規アイコン
	const DEFAULT_TITLE_SEARCH = '検索';		// 検索時のデフォルトタイトル
	const COOKIE_LIB = 'jquery.cookie';		// 名前保存用クッキーライブラリ
	const TASK_ADMIN_ENTRY_DETAIL = 'entry_detail';			// 記事編集画面詳細

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->categoryDb = new event_categoryDb();
		$this->commentDb = new event_commentDb();
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
		$act = $request->trimValueOf('act');
		if ($act == 'search'){
			return 'search.tmpl.html';
		} else {
			return 'main.tmpl.html';
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
		// 初期設定値
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->langId = $this->gEnv->getCurrentLanguage();
		$sendButtonLabel = 'コメントを投稿';		// 送信ボタンラベル
		$sendStatus = 0;		// 送信状況
		$regUserId		= $this->gEnv->getCurrentUserId();			// 記事投稿ユーザ
		$regDt			= date("Y/m/d H:i:s");						// 投稿日時
		$this->isOutputComment = false;// コメントを出力するかどうか
		$title = '';		// 表示タイトル
		$message = '';			// ユーザ向けメッセージ
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		$this->pageTitle = '';		// 画面タイトル、パンくずリスト用タイトル
		
		// イベント定義値取得
		$entryViewCount	= self::$_configArray[event_mainCommonDef::CF_ENTRY_VIEW_COUNT];// 記事表示数
		if (empty($entryViewCount)) $entryViewCount = event_mainCommonDef::DEFAULT_VIEW_COUNT;
		$entryViewOrder	= self::$_configArray[event_mainCommonDef::CF_ENTRY_VIEW_ORDER];// 記事表示順
		$receiveComment = self::$_configArray[event_mainCommonDef::CF_RECEIVE_COMMENT];		// コメントを受け付けるかどうか
		
		$act = $request->trimValueOf('act');
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
		if (empty($entryId)) $entryId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID_SHORT);		// 略式イベント記事ID
		$startDt = $request->trimValueOf('start');
		$endDt = $request->trimValueOf('end');
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$day = $request->trimValueOf('day');		// 日指定
		$keyword = $request->trimValueOf('keyword');// 検索キーワード
		$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID
		// コメントの入力
		$commentTitle = $request->trimValueOf('title');
		$name = $request->trimValueOf('name');
		$email = $request->trimValueOf('email');
		$url = $request->trimValueOf('url');
		$body = $request->trimValueOf('body');
		// ページ番号
		$pageNo = $request->trimIntValueOf('page', '1');				// ページ番号
		
		$showDefault = false;			// デフォルト状態での表示
		$this->viewExtEntry = false;			// 結果を表示するかどうか
		$showTopContent = false;		// トップコンテンツを表示するかどうか
		if ($act == 'search'){			// 検索
			// キーワード検索のとき
			if (empty($keyword)){
				$message = '検索キーワードが入力されていません';
			} else {
				// 検索キーワードを記録
				$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $keyword);
				
				// 総数を取得
				$totalCount = self::$_mainDb->searchEntryItemsCountByKeyword($now, $keyword, $this->langId);
				$this->calcPageLink($pageNo, $totalCount, $entryViewCount);		// ページ番号修正
				
				// リンク文字列作成、ページ番号調整
				//$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->currentPageUrl . '&act=search&keyword=' . urlencode($keyword));
				$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=search&keyword=' . urlencode($keyword));
				
				// 記事一覧を表示
				self::$_mainDb->searchEntryItemsByKeyword($entryViewCount, $pageNo, $now, $keyword, $this->langId, array($this, 'searchItemsLoop'));
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					$message = '検索キーワード：' . $keyword;
				} else {	// 検索結果なしの場合
					$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
					$message = self::MESSAGE_FIND_NO_ENTRY;
				}
			}
			$this->pageTitle = self::DEFAULT_TITLE_SEARCH;		// 画面タイトル、パンくずリスト用タイトル
		} else if ($act == 'view'){			// 記事を表示のとき
			// コメントを受け付けるときは、コメント入力欄を表示
			// ***** 記事を表示する前に呼び出す必要あり *****
			if (!empty($receiveComment)){
				$this->tmpl->setAttribute('entry_footer', 'visibility', 'visible');		// コメントへのリンク
			}
			if (!empty($category)){				// カテゴリー指定のとき
				// 総数を取得
				$totalCount = self::$_mainDb->getEntryItemsCountByCategory($now, $category, $this->langId);
				$this->calcPageLink($pageNo, $totalCount, $entryViewCount);		// ページ番号修正
				
				// リンク文字列作成、ページ番号調整
				//$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->currentPageUrl . '&act=view&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category);
				$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category);
				
				// 記事一覧を表示
				self::$_mainDb->getEntryItemsByCategory($entryViewCount, $pageNo, $now, $category, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));

				// タイトルの設定
				$ret = $this->categoryDb->getCategoryByCategoryId($category, $this->gEnv->getDefaultLanguage(), $row);
				if ($ret) $title = $row['bc_name'];
				
				// イベント記事データがないときはデータなしメッセージ追加
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				} else {
					$title = self::MESSAGE_NO_ENTRY_TITLE;
					$message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
				}
			} else if (!empty($year) && !empty($month)){
				if (empty($day)){		// 月指定のとき
					$startDt = $year . '/' . $month . '/1';
					$endDt = $this->getNextMonth($year . '/' . $month) . '/1';
					
					// 総数を取得
					$totalCount = self::$_mainDb->getEntryItemsCount($now, $startDt, $endDt, $this->langId);
					$this->calcPageLink($pageNo, $totalCount, $entryViewCount);		// ページ番号修正

					// リンク文字列作成、ページ番号調整
					//$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->currentPageUrl . '&act=view&year=' . $year . '&month=' . $month);
					$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&year=' . $year . '&month=' . $month);
				
					// 記事一覧作成
					self::$_mainDb->getEntryItems($entryViewCount, $pageNo, $now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));

					if ($this->isExistsViewData){
						// ページリンクを埋め込む
						if (!empty($pageLink)){
							$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
							$this->tmpl->addVar("page_link", "page_link", $pageLink);
						}
					}
					// 年月の表示
					$title = $year . '年 ' . $month . '月';
					
					// イベント記事データがないときはデータなしメッセージ追加
					if (!$this->isExistsViewData){
						$message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
					}
				} else {
					$startDt = $year . '/' . $month . '/' . $day;
					$endDt = $this->getNextDay($year . '/' . $month . '/' . $day);
					
					// 総数を取得
					$totalCount = self::$_mainDb->getEntryItemsCount($now, $startDt, $endDt, $this->langId);
					$this->calcPageLink($pageNo, $totalCount, $entryViewCount);		// ページ番号修正
					
					// リンク文字列作成、ページ番号調整
					//$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->currentPageUrl . '&act=view&year=' . $year . '&month=' . $month . '&day=' . $day);
					$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=view&year=' . $year . '&month=' . $month . '&day=' . $day);
					
					// 記事一覧作成
					self::$_mainDb->getEntryItems($entryViewCount, $pageNo, $now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));
					
					if ($this->isExistsViewData){
						// ページリンクを埋め込む
						if (!empty($pageLink)){
							$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
							$this->tmpl->addVar("page_link", "page_link", $pageLink);
						}
					}
					
					// 年月日の表示
					$title = $year . '年 ' . $month . '月 ' . $day . '日';
					
					// イベント記事データがないときはデータなしメッセージ追加
					if (!$this->isExistsViewData){
						$message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
					}
				}
			}
			$this->pageTitle = $title;		// カテゴリー名を画面タイトルにする
		} else if ($act == 'checkcomment'){		// コメント確認のとき
			// 入力チェック
			$maxCommentLength = self::$_configArray[event_mainCommonDef::CF_MAX_COMMENT_LENGTH];// コメント最大長
			if ($maxCommentLength == '') $maxCommentLength = event_mainCommonDef::DEFAULT_COMMENT_LENGTH;
			if (empty($maxCommentLength)){		// 空のときは長さのチェックなし
				$this->checkInput($body, 'コメント内容');
			} else {
				$this->checkLength($body, 'コメント内容', $maxCommentLength);
			}
			$this->checkMailAddress($email, 'Eメール', true);

			// エラーなしの場合は確認画面表示
			if ($this->getMsgCount() == 0){
				// タイトル作成
				$ret = self::$_mainDb->getEntryItem($entryId, $this->langId, $row);
				if ($ret) $title = $row['ee_name'] . self::COMMENT_TITLE;
				$this->pageTitle = $title;		// 画面タイトル
				
				// ハッシュキー作成
				$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
				$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
				
				$this->setGuidanceMsg('この内容でコメントを投稿しますか?');
				
				// 入力の変更不可
				$sendButtonLabel = 'コメントを投稿';		// 送信ボタンラベル
				$sendStatus = 1;// 送信状況を「確定」に変更
				$this->tmpl->addVar("add_comment", "title_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "name_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "email_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "url_disabled", 'readonly');
				$this->tmpl->addVar("add_comment", "body_disabled", 'readonly');
				$this->tmpl->setAttribute('cancel_button', 'visibility', 'visible');		// キャンセルボタン表示
				
				// ### コメント入力欄の表示 ###
				$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');// コメント入力欄表示
				$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
				$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
				$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');// 記事表示停止
			} else {
				$showDefault = true;			// デフォルト状態での表示
			}
			
			// 入力値を戻す
			$this->tmpl->addVar("add_comment", "title", $this->convertToDispString($commentTitle));
			$this->tmpl->addVar("add_comment", "name", $this->convertToDispString($name));
			$this->tmpl->addVar("add_comment", "email", $this->convertToDispString($email));
			$this->tmpl->addVar("add_comment", "url", $this->convertToDispString($url));
			$this->tmpl->addVar("add_comment", "body", $this->convertToDispString($body));
			$this->tmpl->addVar("_widget", "entry_id", $this->convertToDispString($entryId));		// 記事ID
		} else if ($act == 'sendcomment'){	// コメント受信のとき
			$postTicket = $request->trimValueOf('ticket');		// POST確認用
			$ret = false;
			if (!empty($entryId) && !empty($body) &&
				!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// コメントを保存
				$ret = $this->commentDb->addCommentItem($entryId, $this->langId, $commentTitle, $body, $url, $name, $email, $regUserId, $regDt, $newSerial);
			}
			if ($ret){
				$this->setGuidanceMsg('コメントを投稿しました');
			} else {
				$this->setUserErrorMsg('コメントの投稿に失敗しました');
			}
			$showDefault = true;			// デフォルト状態での表示
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		} else if ($act == 'sendcancel'){	// コメントキャンセルのとき
			$showDefault = true;			// デフォルト状態での表示
		} else {
			$showDefault = true;			// デフォルト状態での表示
		}
		// ##### デフォルトの表示では、最新のn件の記事を表示または、記事ID指定で1つの記事を表示
		if ($showDefault){
			// 記事ID指定のときは記事を取得
			$entryRow = array();
			if (!empty($entryId)){
				$ret = self::$_mainDb->getEntryItem($entryId, $this->langId, $row);
				if ($ret) $entryRow = $row;
			}
			
			// コメントを受け付けるときは、コメント入力欄を表示
			if (!empty($receiveComment)){
				if (empty($entryId)){		
					$this->tmpl->setAttribute('entry_footer', 'visibility', 'visible');		// コメントへのリンク
				} else {		// 記事ID指定の場合のみコメント入力可能
					$this->isOutputComment = true;// コメントを出力するかどうか
					
					$this->tmpl->setAttribute('show_comment', 'visibility', 'visible');		// 既存コメントを表示
					$this->tmpl->addVar("_widget", "entry_id", $entryId);		// 記事を指定
					
					// ### コメント入力欄の表示 ###
					//$ret = self::$_mainDb->getEntryItem($entryId, $this->langId, $row);
					//if ($ret && !empty($row['ee_receive_comment'])){		// コメントを受け付ける場合のみ表示
					if (!empty($entryRow) && !empty($entryRow['ee_receive_comment'])){		// コメントを受け付ける場合のみ表示
						$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');
						$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
						$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
						
						// 名前保存用のスクリプトライブラリ追加
						$this->tmpl->setAttribute('init_cookie', 'visibility', 'visible');
						$this->tmpl->setAttribute('update_cookie', 'visibility', 'visible');
						$this->addLib[] = self::COOKIE_LIB;
					}
				}
			}
			if (empty($entryId)){		// n件のイベント記事を表示するとき
				$showTopContent = true;		// トップコンテンツを表示

				// トップコンテンツを表示
				$topContent = self::$_configArray[event_mainCommonDef::CF_TOP_CONTENTS];// トップコンテンツ

				$this->tmpl->setAttribute('show_top_content', 'visibility', 'visible');
				$this->tmpl->addVar("show_top_content", "content", $topContent);

				// 総数を取得
				$totalCount = self::$_mainDb->getEntryItemsCount($now, $startDt, $endDt, $this->langId);
				$this->calcPageLink($pageNo, $totalCount, $entryViewCount);		// ページ番号修正
				
				// リンク文字列作成、ページ番号調整
				//$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->currentPageUrl);
				$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl);
			
				// 記事一覧作成
				self::$_mainDb->getEntryItems($entryViewCount, $pageNo, $now, 0/* 期間で指定 */, $startDt/*期間開始*/, $endDt/*期間終了*/,
								$this->langId, $entryViewOrder, array($this, 'itemsLoop'));
				
				if ($this->isExistsViewData){		// 表示するイベント記事があるとき
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				} else {			// 表示するイベント記事がないとき
					//$message = self::MESSAGE_NO_ENTRY_IN_FUTURE;				// 今後のイベント記事が登録されていないメッセージ
					$message = self::$_configArray[event_mainCommonDef::CF_MSG_NO_ENTRY_IN_FUTURE];// 予定イベントなし時メッセージ
					if (empty($message)) $message = event_mainCommonDef::DEFAULT_MSG_NO_FUTURE_EVENT;
				}
			} else {		// 記事単体表示のとき
				$this->viewExtEntry = true;			// 記事ID指定のときは結果(全文)を表示
				self::$_mainDb->getEntryItems($entryViewCount, $pageNo, $now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));
				
				// 記事がないときはコメントを隠す
				if (!$this->isExistsViewData){
					$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
					$this->tmpl->setAttribute('add_comment', 'visibility', 'hidden');
				}
				// ページのタイトル設定
				if (!empty($entryRow)) $this->pageTitle = $entryRow['ee_name'];		// 記事レコードがあるとき
			}
			
			// 年月日の表示
			// イベント記事データがないときはデータなしメッセージ追加
			if (!$showTopContent && !$this->isExistsViewData){
				$title = self::MESSAGE_NO_ENTRY_TITLE;
				$message = self::MESSAGE_NO_ENTRY;			// ユーザ向けメッセージ
			}
		}
		if (!$this->isExistsViewData) $this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');// イベント記事がないときは、一覧を表示しない
		
		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->pageTitle);
		
		// タイトルの設定
		if (!empty($title)){
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
			$this->tmpl->addVar("show_title", "title", $this->convertToDispString($title));
		}
		
		// メッセージを表示
		if (!empty($message)){
			$this->tmpl->setAttribute('message', 'visibility', 'visible');
			$this->tmpl->addVar("message", "message", $this->convertToDispString($message));
		}
		
		// 運用可能ユーザの場合は編集用ボタンを表示
		//if (!$showTopContent && $act != 'search' && self::$_canEditEntry){		// 検索画面以外で記事編集権限ありのとき
		if ($act != 'search' && self::$_canEditEntry){		// 検索画面以外で記事編集権限ありのとき
			// 画面編集用のリソースを読み込む
			$this->gPage->setEditMode();
			
			// 共通ボタン作成
			$buttonList = '';
			
			// 新規作成ボタン
			$iconUrl = $this->gEnv->getRootUrl() . self::NEW_ICON_FILE;		// 新規アイコン
			$iconTitle = '新規';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			$buttonList .= '<a href="javascript:void(0);" onclick="editEntry(0);">' . $editImg . '</a>';
			$buttonList = '<div class="m3edittool">' . $buttonList . '</div>';
			
			$this->tmpl->setAttribute('button_list', 'visibility', 'visible');
			$this->tmpl->addVar("button_list", "button_list", $buttonList);
			
			// 設定画面表示用のスクリプトを埋め込む
			$editUrl = $this->getConfigAdminUrl('openby=simple&task=' . self::TASK_ADMIN_ENTRY_DETAIL);
			$configUrl = $this->getConfigAdminUrl('openby=other');
			$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
			$this->tmpl->addVar("admin_script", "edit_url", $editUrl);
			$this->tmpl->addVar("admin_script", "config_url", $configUrl);
		}
		
		// 他画面へのリンク
		$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
		$topLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_CALENDAR, true));
		$topName = 'カレンダー';
		$this->tmpl->addVar("top_link_area", "calendar_url", $topLink);
		$this->tmpl->addVar("top_link_area", "calendar_name", $topName);
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
		if (!$this->isSystemManageUser){		// システム運用者以上の場合はカウントしない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::CONTENT_TYPE, $fetchedRow['ee_serial'], $this->currentDay, $this->currentHour);
		}

		$entryId = $fetchedRow['ee_id'];// 記事ID
		$title = $fetchedRow['ee_name'];// タイトル
		$date = $fetchedRow['ee_start_dt'];// 開催日時
		$place = $fetchedRow['ee_place'];// 開催場所
		$showComment = $fetchedRow['ee_show_comment'];				// コメントを表示するかどうか
		
		// コメントを取得
		$commentCount = $this->commentDb->getCommentCountByEntryId($entryId, $this->langId);	// コメント総数
		if ($this->isOutputComment){// コメントを出力のとき
			// コメントの内容を取得
			$ret = $this->commentDb->getCommentByEntryId($entryId, $this->langId, $row);
			if ($ret){
				$this->tmpl->clearTemplate('commentlist');
				for ($i = 0; $i < count($row); $i++){
					$userName = $this->convertToDispString($row[$i]['eo_user_name']);	// 投稿ユーザは入力値を使用
					$url = $this->convertToDispString($row[$i]['eo_url']);
					//$commentInfo = $this->convertToDispString($row[$i]['eo_regist_dt']) . '&nbsp;&nbsp;' . $userName;
					$commentInfo = '日時：&nbsp;' . $this->convertToDispDateTime($row[$i]['eo_regist_dt'], 0/*ロングフォーマット*/, 0/*時分秒*/);
					if (!empty($userName)) $commentInfo .= '&nbsp;&nbsp;&nbsp;&nbsp;名前：&nbsp;' . $userName;
					if (!empty($url)) $commentInfo .= '<br />' . $url;
					$comment = $this->convertToPreviewText($this->convertToDispString($row[$i]['eo_html']));		// 改行コードをbrタグに変換
					$commentRow = array(
						'comment_title'		=> $this->convertToDispString($row[$i]['eo_name']),			// コメントタイトル
						'comment'		=> $comment,			// コメント内容
						'user_name'		=> $userName,			// 投稿ユーザ名
						'comment_info'	=> $commentInfo						// コメント情報
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
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		$link = '<div><a href="' . $this->convertUrlToHtmlEntity($linkUrl . '#comment') . '" >コメント(' . $commentCount . ')</a></div>';
		
		// HTMLを出力(出力内容は特にエラーチェックしない)
		$entryText = $fetchedRow['ee_html'];
		$resultText = '';
		if ($this->viewExtEntry){			// 結果を表示するかどうか
			$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
			if (!empty($fetchedRow['ee_html_ext'])){		// 結果がある場合
				$resultText = $fetchedRow['ee_html_ext'];
				$resultText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $resultText);// アプリケーションルートを変換
				$resultText = '<div>' . $resultText . '</div>';
			}
		} else {
			// 結果がある場合はリンクを付加
			$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
			if (!empty($fetchedRow['ee_html_ext'])){
				$entryText .= self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . self::MESSAGE_EXT_ENTRY . '</a>';
			}
		}

		// イベント開催期間
		if ($fetchedRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 開催開始日時のみ表示のとき
			$dateStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
		} else {
			$dateStr = $this->convertToDispDateTime($fetchedRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/) . self::DATE_RANGE_DELIMITER;
			$dateStr .= $this->convertToDispDateTime($fetchedRow['ee_end_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
		}
		// 場所
		$placeStr = '';
		if (!empty($place)) $placeStr = '場所： ' . $this->convertToDispString($place);

		// ##### 記事のフッター部 #####
		// コメントを表示しないときはリンクを表示しない
		if (empty($showComment)) $link = '';
		$this->tmpl->clearTemplate('entry_footer');
		$row = array(
//			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'link' => $link		// コメントへのリンク
		);
		$this->tmpl->addVars('entry_footer', $row);
		$this->tmpl->parseTemplate('entry_footer', 'a');
		
		// コンテンツ編集権限がある場合はボタンを表示
		$buttonList = '';
		if (self::$_canEditEntry){		// 編集権限があるとき
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '編集';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			$buttonList .= '<a href="javascript:void(0);" onclick="editEntry(' . $fetchedRow['ee_serial'] . ');">' . $editImg . '</a>';
			$buttonList = '<div class="m3edittool">' . $buttonList . '</div>';
		}
		
		$row = array(
			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'title' => $title,
			'date' => $dateStr,			// 開催日時
			'place' => $placeStr,		// 開催場所
			'entry' => $entryText,	// イベント記事
			'result' => $resultText,	// イベント結果
			'button_list' => $buttonList	// 記事編集ボタン
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function searchItemsLoop($index, $fetchedRow)
	{
		$title = $fetchedRow['ee_name'];			// タイトル
		
		// 記事へのリンクを生成
		//$linkUrl = $this->currentPageUrl . '&entryid=' . $fetchedRow['ee_id'];
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $fetchedRow['ee_id'], true/*リンク用*/);
		$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $title . '</a>';
		
		$date = $fetchedRow['ee_start_dt'];// 開催日時
		$place = $fetchedRow['ee_place'];// 開催場所

		// HTMLを出力(出力内容は特にエラーチェックしない)
		// 結果がある場合はリンクを付加
		$entryText = $fetchedRow['ee_html'];
		$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
		if (!empty($fetchedRow['ee_html_ext'])){
			$entryText .= self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . self::MESSAGE_EXT_ENTRY . '</a>';
		}
		
		$row = array(
			'title' => $link,			// リンク付きタイトル
			'date' => $this->convertToDispDateTime($date, 0/*ロングフォーマット*/, 10/*時分*/),			// 開催日時
			'place' => $this->convertToDispString($place),		// 開催場所
			'entry' => $entryText	// 投稿記事
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
}
?>
