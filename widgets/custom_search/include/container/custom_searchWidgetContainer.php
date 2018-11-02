<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    カスタム検索
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010-2018 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/custom_searchDb.php');

class custom_searchWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $resultCount;				// 結果表示件数
	private $wikiLibObj;		// Wikiコンテンツオブジェクト
	private $resultLength;		// 検索結果コンテンツの文字列最大長
	private $templateType;		// 現在のテンプレートタイプ
	private $showImage;		// 画像を表示するかどうか
	private $imageType;				// 画像タイプ
	private $imageWidth;			// 画像幅
	private $imageHeight;			// 画像高さ
	private $topContent;			// トップコンテンツ
	private $hTagLevel;			// コンテンツのタグレベル
	private $viewItemsData = array();			// Joomla!ビュー用データ
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'カスタム検索';			// デフォルトのウィジェットタイトル
	const FIELD_HEAD = 'item';			// フィールド名の先頭文字列
	const DEFAULT_SEARCH_COUNT	= 20;				// デフォルトの検索結果表示数
	const DEFAULT_IMAGE_FILENAME_HEAD = 'default';		// デフォルトの画像ファイル名ヘッダ
	const DEFAULT_IMAGE_TYPE = '80c.jpg';		// デフォルトの画像タイプ
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const MESSAGE_NO_KEYWORD	= '検索キーワードが入力されていません';
	const MESSAGE_FIND_NO_CONTENT	= '該当するコンテンツが見つかりません';
	const SEARCH_LIST_CONTENT_ID = 'SEARCH_LIST';	// 検索一覧に表示するコンテンツのID
	const DEFAULT_SEARCH_ACT = 'custom_search';		// 検索実行処理
	const CF_USE_PASSWORD			= 'use_password';		// 汎用コンテンツに対するパスワードアクセス制御
	const WIKI_OBJ_ID = 'wikilib';			// Wikiコンテンツオブジェクト
	const DEFAULT_RESULT_LENGTH = 200;			// 検索結果コンテンツの文字列最大長
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new custom_searchDb();
		
		// Wikiコンテンツオブジェクト取得
		$this->wikiLibObj = $this->gInstance->getObject(self::WIKI_OBJ_ID);
		
		// 一覧タイプで出力
		$this->selectListRender();
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
		// テンプレートタイプに合わせて出力を変更
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){
			return 'index_bootstrap.tmpl.html';
		} else {
			return 'index.tmpl.html';
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
		// 初期値設定
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->showImage		= 0;				// 画像を表示するかどうか
		$this->imageType		= self::DEFAULT_IMAGE_TYPE;				// 画像タイプ
		$this->imageWidth		= 0;				// 画像幅
		$this->imageHeight		= 0;				// 画像高さ
		$this->topContent		= '';				// トップコンテンツ
		$this->hTagLevel = $this->getHTagLevel();			// コンテンツのタグレベル
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){		// 定義データが取得できないとき
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$isAll = false;
		if ($this->gEnv->isCurrentUserLogined()) $isAll = true;
		
		$name = $targetObj->name;// 定義名
		$this->resultCount	= intval($targetObj->resultCount);			// 表示項目数
		if ($this->resultCount <= 0) $this->resultCount = self::DEFAULT_SEARCH_COUNT;
		$this->resultLength = intval($targetObj->resultLength);
		if ($this->resultLength <= 0) $this->resultLength = self::DEFAULT_RESULT_LENGTH;	// 検索結果コンテンツの文字列最大長
		if (isset($targetObj->showImage))	$this->showImage		= $targetObj->showImage;				// 画像を表示するかどうか
		if (isset($targetObj->imageType))	$this->imageType		= $targetObj->imageType;				// 画像タイプ
		if (isset($targetObj->imageWidth))	$this->imageWidth		= $targetObj->imageWidth;				// 画像幅
		if (isset($targetObj->imageHeight))	$this->imageHeight		= $targetObj->imageHeight;				// 画像高さ
		$this->searchTextId = $targetObj->searchTextId;		// 検索用テキストフィールドのタグID
		$this->searchButtonId = $targetObj->searchButtonId;		// 検索用ボタンのタグID
		$this->searchResetId = $targetObj->searchResetId;		// 検索エリアリセットボタンのタグID
		$isTargetContent = $targetObj->isTargetContent;		// 汎用コンテンツを検索対象とするかどうか
		$isTargetBlog = $targetObj->isTargetBlog;			// ブログ記事を検索対象とするかどうか
		$isTargetProduct = $targetObj->isTargetProduct;			// 商品情報を検索対象とするかどうか
		$isTargetEvent = $targetObj->isTargetEvent;			// イベント情報を検索対象とするかどうか
		$isTargetBbs = $targetObj->isTargetBbs;			// BBSを検索対象とするかどうか
		$isTargetPhoto = $targetObj->isTargetPhoto;			// フォトギャラリーを検索対象とするかどうか
		$isTargetWiki = $targetObj->isTargetWiki;			// Wikiを検索対象とするかどうか
		$searchTemplate = $targetObj->searchTemplate;		// 検索用テンプレート
		$searchFormId = $this->gEnv->getCurrentWidgetId() . '_' . $configId . '_form';		// フォームのID
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 入力値を取得
		$keyword = $request->trimValueOf('keyword');		// 検索キーワード
		$act = $request->trimValueOf('act');
		// ##### joomla!検索インターフェイス #####
		$task = $request->trimValueOf('task');
		$option = $request->trimValueOf('option');
		
		if ($act == self::DEFAULT_SEARCH_ACT ||			// 検索実行
			//($task == 'search' && $option == 'com_search')){		// joomla!検索インターフェイスからの検索
			$task == 'search'){		// joomla!検索インターフェイスからの検索
			
			// ##### joomla!検索インターフェイスからの実行の場合 #####
/*			if ($task == 'search' && $option == 'com_search'){
				if (empty($keyword)) $keyword = $request->trimValueOf('searchword');		// 検索キーワード
			}*/
			
			// 検索キーワードが空以外の場合は、キーワードログを残す
			$parsedKeywords = array();
			if (!empty($keyword)){
				// キーワード分割
				$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
				
				// 検索キーワードを記録
				for ($i = 0; $i < count($parsedKeywords); $i++){
					$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $parsedKeywords[$i]);
				}
			}
			
			// 入力条件が検索キーワードのみの場合、キーワードが入力されていなければエラーメッセージを表示
			if ($fieldCount == 0 && empty($parsedKeywords)){
				$message = self::MESSAGE_NO_KEYWORD;
			} else {
				// ##### 検索実行の場合 #####
				// 設定値取得
				$configArray = $this->loadContentConfig();
				$contentUsePassword = $configArray[self::CF_USE_PASSWORD];			// パスワードによるコンテンツ閲覧制限
		
				// 総数を取得
				$totalCount = $this->db->searchContentsByKeyword(0/*項目数取得*/, 0/*ダミー*/, $parsedKeywords, $this->_langId, $isAll, $isTargetContent, $isTargetBlog,
								$isTargetProduct, $isTargetEvent, $isTargetBbs, $isTargetPhoto, $isTargetWiki, $contentUsePassword);
				$this->calcPageLink($pageNo, $totalCount, $this->resultCount);		// ページ番号修正
				
				// リンク文字列作成、ページ番号調整
				$linkStyle = 0;			// HTMLの出力タイプ
				if ($this->templateType == M3_TEMPLATE_BOOTSTRAP_30) $linkStyle = 2;		// Bootstrap型テンプレートの場合
	//			$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&act=' . self::DEFAULT_SEARCH_ACT . '&keyword=' . urlencode($keyword),
				$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $this->currentPageUrl . '&task=search&keyword=' . urlencode($keyword),
													''/*追加パラメータなし*/, $linkStyle);

				// ##### 作成されたページリンク情報を取得 #####
				$pageLinkInfo = $this->getPageLinkInfo();

				// 検出項目を表示
				$this->db->searchContentsByKeyword($this->resultCount, $pageNo, $parsedKeywords, $this->_langId, $isAll, $isTargetContent, $isTargetBlog, 
								$isTargetProduct, $isTargetEvent, $isTargetBbs, $isTargetPhoto, $isTargetWiki, $contentUsePassword, array($this, 'searchItemsLoop'));
				
				if ($this->isExistsViewData){
					// ページリンクを埋め込む
					if (!empty($pageLink) && $this->_renderType != M3_RENDER_JOOMLA_NEW){					// Joomla!新型描画処理でない場合
						$this->tmpl->setAttribute('page_link_top', 'visibility', 'visible');		// リンク表示
						$this->tmpl->setAttribute('page_link', 'visibility', 'visible');		// リンク表示
						$this->tmpl->addVar("page_link_top", "page_link", $pageLink);
						$this->tmpl->addVar("page_link", "page_link", $pageLink);
					}
					
					// ##### ページ番号遷移ナビゲーションを作成 #####
					$this->setJoomlaPaginationData($pageLinkInfo, $totalCount, ($pageNo -1) * $this->resultCount/*先頭に表示する項目のオフセット番号*/, $this->resultCount);
				} else {	// 検索結果なしの場合
					$this->tmpl->setAttribute('result_list', 'visibility', 'hidden');
					$message = self::MESSAGE_FIND_NO_CONTENT;
				}
			}
		}
		// メッセージを表示
		if (!empty($message)){
//			if ($this->_renderType == M3_RENDER_JOOMLA_NEW){
//				// Joomla!新型テンプレートの先頭にメッセージ追加
//				$this->addTopMessage($message);
//			} else {
				$this->tmpl->setAttribute('message', 'visibility', 'visible');
				$this->tmpl->addVar("message", "message", $this->convertToDispString($message));
//			}
		}
		
		// 表示データ埋め込み
		$this->tmpl->addVar("_widget", "page_sub",	$this->gEnv->getCurrentPageSubId());		// ページサブID
		$this->tmpl->addVar("_widget", "html",	$searchTemplate);
		$this->tmpl->addVar("_widget", "search_text_id",	$this->searchTextId);		// 検索用テキストフィールドのタグID
		$this->tmpl->addVar("_widget", "search_button_id",	$this->searchButtonId);		// 検索用ボタンのタグID
		$this->tmpl->addVar("_widget", "search_reset_id",	$this->searchResetId);		// 検索エリアリセットボタンのタグID
		$this->tmpl->addVar("_widget", "search_form_id",	$searchFormId);		// 検索フォームのタグID
		$this->tmpl->addVar("_widget", "keyword",	$keyword);		// 検索キーワード
		$this->tmpl->addVar("_widget", "search_act",	self::DEFAULT_SEARCH_ACT);		// 検索実行処理
		
		// ##### Joomla!新型テンプレートに記事データを設定 #####
		$this->topContent = '';
		$this->setJoomlaViewData($this->viewItemsData, count($this->viewItemsData)/*先頭(leading部)のコンテンツ数*/, 0/*カラム部(intro部)のコンテンツ数*/, 0/*カラム部(intro部)のカラム数*/, ''/*$this->topContent*//*トップコンテンツ*/, ''/*「もっと読む」ボタンラベル*/, true/*ウィジェットデフォルト描画出力を使用*/);
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
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function searchItemsLoop($index, $fetchedRow)
	{
		// コンテンツへのリンクを生成
		$title = '';
		$linkUrl = '';
		$summary = '';		// コンテンツ概要
		$contentType = $fetchedRow['type'];
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツの場合
				$contentId = $fetchedRow['id'];
				
				// コンテンツへのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId, true/*リンク用*/);

				// コンテンツを取得
				$ret = $this->db->getContentByContentId(''/*汎用コンテンツ*/, $contentId, $this->_langId, $row);
				if ($ret){
					// テキストに変換。HTMLタグ削除。
					$content = $this->gInstance->getTextConvManager()->htmlToText($row['cn_html']);
		
					// 登録したキーワードを変換
					// *** キーワード部分の検索はどうする？ ***
					$this->gInstance->getTextConvManager()->convByKeyValue($content, $content);
		
					// 検索結果用のテキスト作成
					$summary = $this->_createSummaryText($content);
				}
				
				// 固定コンテンツウィジェット等でコンテンツをページに直接埋め込んでいる場合はリンク先を修正
				$ret = $this->_db->getSubPageIdByContent(M3_VIEW_TYPE_CONTENT, $contentId, $this->gEnv->getCurrentPageId(), $rows);
				if ($ret){
					$pageSubId = $rows[0]['pd_sub_id'];
					$pageTitle = $rows[0]['pn_meta_title'];
	
					// ページタイトルが設定されている場合は取得
					if (!empty($pageTitle)) $title = $pageTitle;
					
					// ページへのURLを作成
					$linkUrl = $this->getUrl($this->gEnv->createPageUrl() . '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $pageSubId, true/*リンク用*/);
				}
				break;
			case M3_VIEW_TYPE_BLOG:			// ブログコンテンツの場合
				$entryId = $fetchedRow['id'];
				
				// ブログ記事へのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId, true/*リンク用*/);

				// ブログ記事を取得
				$ret = $this->db->getEntryByEntryId($entryId, $this->_langId, $row);
				if ($ret){
					$content = trim($row['be_html']);
					if (empty($content)) $content = trim($row['be_html_ext']);		// 本文1がない場合は本文2を表示
					
					// テキストに変換。HTMLタグ削除。
					$content = $this->gInstance->getTextConvManager()->htmlToText($content);

					// 検索結果用のテキスト作成
					$summary = $this->_createSummaryText($content);
				}
				
				// ブログ記事画像
				$imageUrl = $this->getImageUrl(M3_VIEW_TYPE_BLOG, $entryId, $this->imageType);
				break;
			case M3_VIEW_TYPE_PRODUCT:			// 商品情報の場合
				$productId = $fetchedRow['id'];
				
				// 商品情報へのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $productId, true/*リンク用*/);

				// 商品情報を取得
				$ret = $this->db->getProductByProductId($productId, $this->_langId, $row);
				if ($ret){
					$summary = $row['pt_description'];
					if (empty($summary)){
						// テキストに変換。HTMLタグ削除。
						$content = $this->gInstance->getTextConvManager()->htmlToText($row['pt_html']);
	
						// 検索結果用のテキスト作成
						$summary = $this->_createSummaryText($content);
					}
				}
				break;
			case M3_VIEW_TYPE_EVENT:			// イベント情報の場合
				$eventId = $fetchedRow['id'];
				
				// イベント情報へのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_EVENT_ID . '=' . $eventId, true/*リンク用*/);

				// イベント情報を取得
				$ret = $this->db->getEvent($eventId, $this->_langId, $row);
				if ($ret){
					$summary = $row['ee_summary'];
					if (empty($summary)){
						// テキストに変換。HTMLタグ削除。
						$content = $this->gInstance->getTextConvManager()->htmlToText($row['ee_html']);
	
						// 検索結果用のテキスト作成
						$summary = $this->_createSummaryText($content);
					}
				}
				
				// イベント記事画像
				$imageUrl = $this->getImageUrl(M3_VIEW_TYPE_EVENT, $eventId, $this->imageType);
				break;
			case M3_VIEW_TYPE_BBS:			// BBSスレッド情報の場合
				$threadId = $fetchedRow['id'];
				
				// フォト情報へのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId, true/*リンク用*/);

				// フォト情報を取得
				$ret = $this->db->getBbsThread($threadId, $row);
				if ($ret){
					// テキストに変換。HTMLタグ削除。
					$content = $this->gInstance->getTextConvManager()->htmlToText($row['te_message']);

					// 検索結果用のテキスト作成
					$summary = $this->_createSummaryText($content);
				}
				break;
			case M3_VIEW_TYPE_PHOTO:			// フォト情報の場合
				$photoId = $fetchedRow['id'];
				
				// フォト情報へのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId, true/*リンク用*/);

				// フォト情報を取得
				$ret = $this->db->getPhoto($photoId, $this->_langId, $row);
				if ($ret){
					$summary = $row['ht_summary'];
					if (empty($summary)){
						// テキストに変換。HTMLタグ削除。
						$content = $this->gInstance->getTextConvManager()->htmlToText($row['ht_description']);
	
						// 検索結果用のテキスト作成
						$summary = $this->_createSummaryText($content);
					}
				}
				break;
			case M3_VIEW_TYPE_WIKI:			// Wikiコンテンツの場合
				$wikiId = $fetchedRow['id'];
				
				// Wikiコンテンツへのリンクを作成
				$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . $wikiId, true/*リンク用*/);

				// Wikiコンテンツを取得
				$ret = $this->db->getWiki($wikiId, $row);
				if ($ret){
					$content = $this->wikiLibObj->convertToText($row['wc_data'], $wikiId);
				
					// 検索結果用のテキスト作成
					$summary = $this->_createSummaryText($content);
				}
				break;
			default:
				break;
		}
		if (empty($title)) $title = $fetchedRow['name'];
		$escapedTitle = $this->convertToDispString($title);
		$titleLink = $this->convertToDispString($title);
		$escapedLinkUrl = $this->convertUrlToHtmlEntity($linkUrl);
		if (!empty($linkUrl)) $titleLink = '<a href="' . $escapedLinkUrl . '" >' . $titleLink . '</a>';

		// 画像がない場合はデフォルト画像を取得
		if (empty($imageUrl)) $imageUrl = $this->getDefaultImageUrl($this->imageType);
		
		// 画像
		$imageTag = '';
		if ($this->showImage && !empty($imageUrl)){	// サムネール画像を表示する場合
			$style = '';
			if ($this->imageWidth > 0) $style .= 'width:' . $this->imageWidth . 'px;';
			if ($this->imageHeight > 0) $style .= 'height:' . $this->imageHeight . 'px;';
			if (!empty($style)) $style = 'style="' . $style . '" ';
			$imageTag = '<img src="' . $this->getUrl($imageUrl) . '" alt="' . $escapedTitle . '" title="' . $escapedTitle . '" ' . $style . '/>';
			$imageTag = '<div style="float:left;"><a href="' . $escapedLinkUrl . '">' . $imageTag . '</a></div>';
			
			// コンテンツ概要
			$summary = '<div class="clearfix">' . $summary . '</div>';
		}
		
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW){
			$row = array(
				'title'		=> $titleLink,		// タイトル
				'image'		=> $imageTag,		// 画像
				'body'		=> $summary			// コンテンツ概要
			);
			$this->tmpl->addVars('result_list', $row);
			$this->tmpl->parseTemplate('result_list', 'a');
		}
		$this->isExistsViewData = true;				// 表示データがあるかどうか

		//##### Joomla!新型描画処理でない場合は終了 #####
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW) return true;
		
		// ### Joomla!新型テンプレート用データ作成 ###
		$titleTag = '<h' . $this->hTagLevel . '>' . $titleLink . '</h' . $this->hTagLevel . '>';
		$summaryHtml = $titleTag . $summary;		// タイトルを付加
		
		$viewItem = new stdClass;
//		$viewItem->id			= $entryId;	// コンテンツID
//		$viewItem->title		= $title;	// コンテンツ名。コンテンツのタイトルを変更。	// *** ThemlerテンプレートではHタグの上が過度に空いてしまう問題あり(2015/10/21) ***
		$viewItem->introtext	= $summaryHtml;	// コンテンツ内容(Joomla!2.5以降テンプレート用)
		$viewItem->text			= $viewItem->introtext;	// コンテンツ内容(Joomla!1.5テンプレート用)
		$viewItem->state		= 1;			// 表示モード(0=新着,1=表示済み)
		$viewItem->url			= $linkUrl;						// リンク先。viewItem->urlはMagic3の拡張値。
		if ($this->showImage && !empty($imageUrl)){	// サムネール画像を表示する場合
			$viewItem->thumbUrl	= $imageUrl;
			$viewItem->thumbAlt	= $title;
		}
		
//		// 以下は表示する項目のみ値を設定する
//		if ($this->showEntryAuthor) $viewItem->author		= $author;		// 投稿者
//		if ($this->showEntryRegistDt) $viewItem->published	= $date;		// 投稿日時
//		if ($this->showEntryViewCount) $viewItem->hits		= $viewCount;	// 閲覧数
		$this->viewItemsData[] = $viewItem;			// Joomla!ビュー用データ
		return true;
	}
	/**
	 * 検索結果表示用の要約テキスト作成
	 *
	 * @param string $src			変換するテキスト
	 * @return string				変換済みテキスト
	 */
	function _createSummaryText($src)
	{
		// 検索結果用にテキストを詰める。改行、タブ、スペース削除。
		$content = str_replace(array("\r", "\n", "\t", " "), '', $src);

		// 文字列長を修正
		if (function_exists('mb_strimwidth')){
			$content = mb_strimwidth($content, 0, $this->resultLength, '…');
		} else {
			$content = substr($content, 0, $this->resultLength) . '...';
		}
		return $content;
	}
	/**
	 * URLに検索条件のパラメータを付加
	 *
	 * @param string $url	URL
	 * @return string		作成されたURL
	 */
	function addSearchParam($url)
	{
		$destUrl = $url;
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$itemName = self::FIELD_HEAD . ($i + 1);
			$itemValue = $this->gRequest->trimValueOf($itemName);
			if (is_array($itemValue)){
				$paramCount = count($itemValue);
				for ($j = 0; $j < $paramCount; $j++){
					$destUrl .= '&' . $itemName . '[]=' . urlencode($itemValue[$j]);
				}
			} else {
				if (!empty($itemValue)) $destUrl .= '&' . $itemName . '=' . urlencode($itemValue);
			}
		}
		return $destUrl;
	}
	/**
	 * 汎用コンテンツ定義値をDBから取得
	 *
	 * @return array		取得データ
	 */
	function loadContentConfig()
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $this->db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['ng_id'];
				$value = $rows[$i]['ng_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * 画像のURLを取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param string $format		画像フォーマット
	 * @return string				URL
	 */
	function getImageUrl($contentType, $contentId, $format)
	{
		$url = '';
		switch ($contentType){
		case M3_VIEW_TYPE_BLOG:
		case M3_VIEW_TYPE_EVENT:
			$filename = $this->gInstance->getImageManager()->getThumbFilename($contentId, $format);
			$path = $this->gInstance->getImageManager()->getSystemThumbPath($contentType, 0/*PC用*/, $filename);
			if (!file_exists($path)){
				$filename = $this->gInstance->getImageManager()->getThumbFilename(0, $format);		// デフォルト画像ファイル名
				$path = $this->gInstance->getImageManager()->getSystemThumbPath($contentType, 0/*PC用*/, $filename);
			}
			$url = $this->gInstance->getImageManager()->getSystemThumbUrl($contentType, 0/*PC用*/, $filename);
			break;
		}
		return $url;
	}
	/**
	 * デフォルトの画像のURLを取得
	 *
	 * @param string $format		画像フォーマット
	 * @return string				URL
	 */
	function getDefaultImageUrl($format)
	{
		$filename = $this->gInstance->getImageManager()->getThumbFilename(self::DEFAULT_IMAGE_FILENAME_HEAD, $format);
		$url = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_SEARCH, 0/*PC用*/, $filename);
		return $url;
	}
	/**
	 * トップメッセージを追加
	 *
	 * @param string $msgHtml				メッセージコンテンツ
	 * @return								なし
	 */
	function addTopMessage($msgHtml)
	{
		// トップコンテンツの先頭にメッセージを追加
		$this->topContent = '<div>' . $msgHtml . '</div>' . $this->topContent;
	}
}
?>
