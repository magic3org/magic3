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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_blogReadWidgetContainer.php 3836 2010-11-17 06:05:07Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_blogBaseWidgetContainer.php');

class m_blogReadWidgetContainer extends m_blogBaseWidgetContainer
{
	private $messageCount;			// メッセージ数
	private $isExistsMessage;	// メッセージが存在するかどうか
	private $isExistsNextPage;	// 次のページがあるかどうか
	private $pageTitle;				// 画面タイトル、パンくずリスト用タイトル
	const DEFAULT_SEARCH_COUNT	= 5;				// デフォルトの検索記事数
	
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
		return 'thread_read.tmpl.html';
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
		// パラメータ初期化
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->langId = $this->gEnv->getCurrentLanguage();
		$this->pageTitle = '';		// 画面タイトル、パンくずリスト用タイトル
		
		// 定義値取得
		$entryViewCount	= $this->_configArray[self::CF_ENTRY_VIEW_COUNT];// 記事表示数
		if (empty($entryViewCount)) $entryViewCount = self::DEFAULT_VIEW_COUNT;
		$entryViewOrder	= $this->_configArray[self::CF_ENTRY_VIEW_ORDER];// 記事表示順
		$titleColor = $this->_configArray[self::CF_TITLE_COLOR];// タイトル背景色
			
		// 入力値取得
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		$entryId = $request->trimValueOf('entryid');
		$startDt = $request->trimValueOf('start');
		$endDt = $request->trimValueOf('end');
		$year = $request->trimValueOf('year');		// 年指定
		$month = $request->trimValueOf('month');		// 月指定
		$day = $request->trimValueOf('day');		// 日指定
		//$keyword = $request->trimValueOf('keyword');// 検索キーワード
		$keyword = $request->mobileTrimValueOf('keyword');// 検索キーワード
		$category = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);		// カテゴリID

		$showDefault = false;			// デフォルト状態での表示
		if ($act == 'search'){			// 検索
			// キーワード検索のとき
			if (empty($keyword)){
				$message = '検索キーワードが入力されていません';
			} else {
				// 検索項目数
				$searchCount = self::DEFAULT_SEARCH_COUNT;		// 検索記事数
				
				// 検索キーワードを記録
				$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $keyword);
				
				/*
				// 検索キーワードログを残す
				// スペース区切りの場合はワードを分割
								
				// 全角英数を半角に、半角カナ全角ひらがなを全角カナに変換
				$basicWord = $keyword;
				if (function_exists('mb_convert_kana')) $basicWord = mb_convert_kana($basicWord, 'aKCV');
				$basicWord = strtolower($basicWord);		// 大文字を小文字に変換
				
				// 検索キーワードログ書き込み
				$cid = $this->gAccess->getClientId();// クッキー値のクライアントID
				//$this->db->writeKeywordLog($cid, $this->gEnv->getCurrentWidgetId(), $keyword, $basicWord);
				*/
				
				// 総数を取得
				$totalCount = $this->_db->searchEntryItemsCountByKeyword($now, $keyword, $this->langId);

				// リンク文字列作成、ページ番号調整
				$convKeyword = $request->convMobileText($keyword);		// 検索キーワードを携帯用のコードへ変換
				$pageLink = $this->createPageLink($pageNo, $totalCount, $searchCount, $this->_currentPageUrl . '&act=search&keyword=' . urlencode($convKeyword));
				
				// 記事一覧を表示
				$this->_db->searchEntryItemsByKeyword($searchCount, $pageNo, $now, $keyword, $this->langId, array($this, 'searchItemsLoop'));
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					$message = self::MESSAGE_SEARCH_KEYWORD . $keyword;
				} else {	// 検索結果なしの場合
					$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
					$message = self::MESSAGE_FIND_NO_ENTRY;
				}
			}
			$this->setGuidanceMsg($message);			// ユーザ向けメッセージ
			$this->pageTitle = self::DEFAULT_TITLE_SEARCH;		// 画面タイトル、パンくずリスト用タイトル
		} else if ($act == 'view'){			// 記事を表示のとき
			// コメントを受け付けるときは、コメント入力欄を表示
			// ***** 記事を表示する前に呼び出す必要あり *****
			/*if (!empty($receiveComment)){
				$this->tmpl->setAttribute('entry_footer', 'visibility', 'visible');		// コメントへのリンク
			}*/
			if (!empty($category)){				// カテゴリー指定のとき
				// 総数を取得
				$totalCount = $this->_db->getEntryItemsCountByCategory($now, $category, $this->langId);

				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->_currentPageUrl . '&act=view&' . M3_REQUEST_PARAM_CATEGORY_ID . '=' . $category);
				
				// 記事一覧を表示
				$this->_db->getEntryItemsByCategory($entryViewCount, $pageNo, $now, $category, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));

				// タイトルの設定
				$ret = $this->_db->getCategoryByCategoryId($category, $this->gEnv->getDefaultLanguage(), $row);
				if ($ret) $title = $row['bc_name'];
				
				// ブログ記事データがないときはデータなしメッセージ追加
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				} else {
					$title = self::MESSAGE_NO_ENTRY_TITLE;
					$this->setGuidanceMsg(self::MESSAGE_NO_ENTRY);			// ユーザ向けメッセージ
				}
			} else if (!empty($year) && !empty($month)){
				if (empty($day)){		// 月指定のとき
					$startDt = $year . '/' . $month . '/1';
					$endDt = $this->getNextMonth($year . '/' . $month) . '/1';
					
					// 総数を取得
					$totalCount = $this->_db->getEntryItemsCount($now, $startDt, $endDt, $this->langId);

					// リンク文字列作成、ページ番号調整
					$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->_currentPageUrl . '&act=view&year=' . $year . '&month=' . $month);
				
					// 記事一覧作成
					$this->_db->getEntryItems($entryViewCount, $pageNo, $now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));

					if ($this->isExistsViewData){
						// ページリンクを埋め込む
						if (!empty($pageLink)){
							$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
							$this->tmpl->addVar("page_link", "page_link", $pageLink);
						}
					}
					// 年月の表示
					$title = $year . '年 ' . $month . '月';
					
					// ブログ記事データがないときはデータなしメッセージ追加
					if (!$this->isExistsViewData) $this->setGuidanceMsg(self::MESSAGE_NO_ENTRY);			// ユーザ向けメッセージ
				} else {
					$startDt = $year . '/' . $month . '/' . $day;
					$endDt = $this->getNextDay($year . '/' . $month . '/' . $day);
					
					// 総数を取得
					$totalCount = $this->_db->getEntryItemsCount($now, $startDt, $endDt, $this->langId);

					// リンク文字列作成、ページ番号調整
					$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->_currentPageUrl . '&act=view&year=' . $year . '&month=' . $month . '&day=' . $day);
					
					// 記事一覧作成
					$this->_db->getEntryItems($entryViewCount, $pageNo, $now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));
					
					if ($this->isExistsViewData){
						// ページリンクを埋め込む
						if (!empty($pageLink)){
							$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
							$this->tmpl->addVar("page_link", "page_link", $pageLink);
						}
					}
					
					// 年月日の表示
					$title = $year . '年 ' . $month . '月 ' . $day . '日';
					
					// ブログ記事データがないときはデータなしメッセージ追加
					if (!$this->isExistsViewData) $this->setGuidanceMsg(self::MESSAGE_NO_ENTRY);			// ユーザ向けメッセージ
				}
			}
			$this->pageTitle = $title;		// カテゴリー名を画面タイトルにする
		} else {
			$showDefault = true;			// デフォルト状態での表示
		}
		// ##### デフォルトの表示では、最新のn件の記事を表示または、記事ID指定で1つの記事を表示
		if ($showDefault){
			// コメントを受け付けるときは、コメント入力欄を表示
			if (!empty($receiveComment)){
				if (empty($entryId)){		
					$this->tmpl->setAttribute('entry_footer', 'visibility', 'visible');		// コメントへのリンク
				} else {		// 記事ID指定の場合のみコメント入力可能
					$this->isOutputComment = true;// コメントを出力するかどうか
					
					$this->tmpl->setAttribute('show_comment', 'visibility', 'visible');		// 既存コメントを表示
					$this->tmpl->addVar("_widget", "entry_id", $entryId);		// 記事を指定
					
					// ### コメント入力欄の表示 ###
					$this->tmpl->setAttribute('add_comment', 'visibility', 'visible');
					$this->tmpl->addVar("add_comment", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
					$this->tmpl->addVar("add_comment", "send_status", $sendStatus);// 送信状況
				}
			}
			if (empty($entryId)){
				// 総数を取得
				$totalCount = $this->_db->getEntryItemsCount($now, $startDt, $endDt, $this->langId);

				// リンク文字列作成、ページ番号調整
				$pageLink = $this->createPageLink($pageNo, $totalCount, $entryViewCount, $this->_currentPageUrl);
				
				// 記事一覧作成
				$this->_db->getEntryItems($entryViewCount, $pageNo, $now, 0/* 期間で指定 */, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink)){
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
				}
			} else {
				$this->viewExtEntry = true;			// 記事ID指定のときは続き(全文)を表示
				$this->_db->getEntryItems($entryViewCount, $pageNo, $now, $entryId, $startDt/*期間開始*/, $endDt/*期間終了*/, $this->langId, $entryViewOrder, array($this, 'itemsLoop'));
				
				// 記事がないときはコメントを隠す
				if (!$this->isExistsViewData){
					$this->tmpl->setAttribute('entrylist', 'visibility', 'hidden');
					//$this->tmpl->setAttribute('add_comment', 'visibility', 'hidden');
				}
			}
			
			// 年月日の表示
			// ブログ記事データがないときはデータなしメッセージ追加
			if (!$this->isExistsViewData){
				$title = self::MESSAGE_NO_ENTRY_TITLE;
				$this->pageTitle = $title;		// HTMLヘッダタイトル
				$this->setGuidanceMsg(self::MESSAGE_NO_ENTRY);			// ユーザ向けメッセージ
			}
		}
		
		// タイトルの設定
		if (!empty($title)){
			// タイトル作成
			$titleStr = '<div align="center" style="text-align:center;';
			if (!empty($titleColor)) $titleStr .= 'background-color:' . $titleColor . ';';// タイトル背景色
			$titleStr .= '">' . $this->convertToDispString($title) . '</div>';

			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
			$this->tmpl->addVar("show_title", "title", $titleStr);
		}
		
		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->pageTitle);
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
		if (!$this->gEnv->isSystemManageUser()){		// システム運用者以上の場合はカウントしない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::CONTENT_TYPE, $fetchedRow['be_serial'], $this->currentDay, $this->currentHour);
		}

		$entryId = $fetchedRow['be_id'];// 記事ID
		$title = $fetchedRow['be_name'];// タイトル
		$date = $fetchedRow['be_regist_dt'];// 日付
		
		// ページタイトルの設定
		if (empty($this->pageTitle)) $this->pageTitle = $title;		// 画面タイトル、パンくずリスト用タイトル
		
		// コメントを取得
		/*$commentCount = $this->commentDb->getCommentCountByEntryId($entryId, $this->langId);	// コメント総数
		if ($this->isOutputComment){// コメントを出力のとき
			// コメントの内容を取得
			$ret = $this->commentDb->getCommentByEntryId($entryId, $this->langId, $row);
			if ($ret){
				$this->tmpl->clearTemplate('commentlist');
				for ($i = 0; $i < count($row); $i++){
					$userName = $this->convertToDispString($row[$i]['bo_user_name']);	// 投稿ユーザは入力値を使用
					$url = $this->convertToDispString($row[$i]['bo_url']);
					$commentInfo = $this->convertToDispString($row[$i]['bo_regist_dt']) . '&nbsp;&nbsp;' . $userName;
					if (!empty($url)) $commentInfo .= '<br />' . $url;
					$comment = $this->convertToPreviewText($this->convertToDispString($row[$i]['bo_html']));		// 改行コードをbrタグに変換
					$commentRow = array(
						'comment_title'		=> $this->convertToDispString($row[$i]['bo_name']),			// コメントタイトル
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
		}*/
		
		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultMobileUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);
		$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >コメント(' . $commentCount . ')</a>';
		
		// HTMLを出力(出力内容は特にエラーチェックしない)
		$entryText = $fetchedRow['be_html'];
		if ($this->viewExtEntry){			// 続きを表示するかどうか
			if (!empty($fetchedRow['be_html_ext'])) $entryText = $fetchedRow['be_html_ext'];// 続きがある場合は続きを出力
			$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
		} else {
			// 続きがある場合はリンクを付加
			$entryText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $entryText);// アプリケーションルートを変換
			if (!empty($fetchedRow['be_html_ext'])){
				$entryText .= self::MESSAGE_EXT_ENTRY_PRE . '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . self::MESSAGE_EXT_ENTRY . '</a>';
			}
		}
		// 携帯用コンテンツに変換
		$entryText = $this->gInstance->getTextConvManager()->autoConvPcContentToMobile($entryText, $this->currentRootUrl/*現在のページのルートURL*/, 
																				M3_VIEW_TYPE_BLOG/*ブログコンテンツ*/, $fetchedRow['be_create_dt']/*コンテンツ作成日時*/);
																				
		// 記事のフッター部
		$this->tmpl->clearTemplate('entry_footer');
		$row = array(
			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'link' => $link		// コメントへのリンク
		);
		$this->tmpl->addVars('entry_footer', $row);
		$this->tmpl->parseTemplate('entry_footer', 'a');

		$row = array(
			'permalink' => $this->convertUrlToHtmlEntity($linkUrl),	// パーマリンク
			'title' => $title,
			'date' => $date,			// 日付
			'entry' => $entryText	// 投稿記事
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
		$entryId = $fetchedRow['be_id'];// 記事ID
		$title = $fetchedRow['be_name'];// タイトル
		$date = $fetchedRow['be_regist_dt'];// 日付
		
		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultMobileUrl() . '?'. M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);
		$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $title . '</a>';

		// テキストに変換。HTMLタグ削除。
		$entryText = $this->gInstance->getTextConvManager()->htmlToText($fetchedRow['be_html']);

		// 検索結果用にテキストを詰める。改行、タブ、スペース削除。
		$entryText = str_replace(array("\r", "\n", "\t", " "), '', $entryText);

		// 文字列長を修正
		if (function_exists('mb_strimwidth')){
			$entryText = mb_strimwidth($entryText, 0, self::SEARCH_BODY_SIZE, '…');
		} else {
			$entryText = substr($entryText, 0, self::SEARCH_BODY_SIZE) . '...';
		}

		$row = array(
			'title' => $link,			// リンク付きタイトル
			'date' => $date,			// 日付
			'entry' => $entryText	// 投稿記事
		);
		$this->tmpl->addVars('entrylist', $row);
		$this->tmpl->parseTemplate('entrylist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * ページリンク作成
	 *
	 * @param int $pageNo			ページ番号(1～)。ページ番号が範囲外にある場合は自動的に調整
	 * @param int $totalCount		総項目数
	 * @param int $viewItemCount	1ページあたりの項目数
	 * @param string $baseUrl		リンク用のベースURL
	 * @return string				リンクHTML
	 */
	function createPageLink(&$pageNo, $totalCount, $viewItemCount, $baseUrl)
	{
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewItemCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;

		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			// ページ数1から「LINK_PAGE_COUNT」までのリンクを作成
			$maxPageCount = $pageCount < self::LINK_PAGE_COUNT ? $pageCount : self::LINK_PAGE_COUNT;
			for ($i = 1; $i <= $maxPageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$linkUrl = $this->getUrl($baseUrl . '&page=' . $i, true/*リンク用*/);
					$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $i . '</a>';
				}
				$pageLink .= $link;
			}
			// 残りは「...」表示
			if ($pageCount > self::LINK_PAGE_COUNT) $pageLink .= '&nbsp;...';
		}
		if ($pageNo > 1){		// 前ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&page=' . ($pageNo -1), true/*リンク用*/);
			$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" accesskey="1">前へ[1]</a>';
			$pageLink = $link . $pageLink;
		}
		if ($pageNo < $pageCount){		// 次ページがあるとき
			$linkUrl = $this->getUrl($baseUrl . '&page=' . ($pageNo +1), true/*リンク用*/);
			$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" accesskey="2">次へ[2]</a>';
			$pageLink .= $link;
		}
		return $pageLink;
	}
}
?>
