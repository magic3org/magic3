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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainConfigWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $tmpDir;		// 作業ディレクトリ
	private $entryListDispType;		// 記事一覧表示タイプ
	private $entryListImageType;	// 記事一覧用画像タイプ
	const IMAGE_TYPE_ENTRY_IMAGE = 'entryimage';			// 画像タイプ(記事デフォルト画像)
	const ACT_UPLOAD_IMAGE	= 'uploadimage';			// 画像アップロード
	const ACT_GET_IMAGE		= 'getimage';		// 画像取得
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 作業ディレクトリを取得
		$this->tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリパスを取得
		
		// 記事一覧表示タイプ
		$this->entryListDispTypeArray = array(	array(	'name' => 'コンテンツ',	'value' => '0'),
												array(	'name' => '概要',	'value' => '1'));
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
				
		$act = $request->trimValueOf('act');
		$entryViewCount	= $request->trimValueOf('entry_view_count');		// 記事表示数
		$entryViewOrder	= $request->trimValueOf('entry_view_order');		// 記事表示順
		$categoryCount	= $request->trimValueOf('category_count');		// カテゴリ数
		$receiveComment = $request->trimCheckedValueOf('receive_comment');		// コメントを受け付けるかどうか
		$useMultiBlog	= $request->trimCheckedValueOf('use_multi_blog');		// マルチブログ機能を使用するかどうか
		$topContent		= $request->valueOf('top_content');	// トップコンテンツ
		$readmoreLabel	= $request->trimValueOf('item_readmore_label');			//「もっと読む」ボタンラベル
		$this->entryListDispType	= $request->trimValueOf('item_entry_list_disp_type');		// 記事一覧表示タイプ
		$showEntryListImage			= $request->trimCheckedValueOf('item_show_entry_list_image');		// 記事一覧に画像を表示するかどうか
		$this->entryListImageType	= $request->trimValueOf('item_entry_list_image_type');		// 一覧用画像タイプ
		$maxCommentLength	= $request->valueOf('max_comment_length');	// コメント最大文字数
		$layoutEntrySingle	= $request->valueOf('item_layout_entry_single');					// コンテンツレイアウト(記事詳細)
		$layoutEntryList	= $request->valueOf('item_layout_entry_list');					// コンテンツレイアウト(記事一覧)
		$layoutCommentList	= $request->valueOf('item_layout_comment_list');					// コンテンツレイアウト(コメント一覧)
		$outputHead			= $request->trimCheckedValueOf('item_output_head');		// ヘッダ出力するかどうか
		$headViewDetail		= $request->valueOf('item_head_view_detail');					// ヘッダ出力(詳細表示)
		$commentUserLimited = $request->trimCheckedValueOf('comment_user_limited');		// コメントのユーザ制限
		if (strlen($topContent) <= 10){ // FCKEditorのバグの対応(空の場合でもBRタグが送信される)
			$topContent = '';
		}
//		$useWidgetTitle 	= $request->trimCheckedValueOf('item_use_widget_title');		// ウィジェットタイトルを使用するかどうか
		$titleDefault		= $request->trimValueOf('item_title_default');		// デフォルトタイトル
		$titleList			= $request->trimValueOf('item_title_list');		// 一覧タイトル
		$titleSearchList	= $request->trimValueOf('item_title_search_list');		// 検索結果タイトル
		$titleNoEntry		= $request->trimValueOf('item_title_no_entry');		// 記事なし時タイトル
		$messageNoEntry		= $request->trimValueOf('item_message_no_entry');		// 記事が登録されていないメッセージ
		$messageFindNoEntry = $request->trimValueOf('item_message_find_no_entry');		// 記事が見つからないメッセージ
//		$titleTagLevel		= $request->trimIntValueOf('item_title_tag_level', blog_mainCommonDef::DEFAULT_TITLE_TAG_LEVEL);		// タイトルタグレベル
		$imageType			= $request->trimValueOf('type');		// 画像タイプ
		$updatedEntryImage	= $request->trimValueOf('updated_entryimage');		// 記事デフォルト画像更新フラグ
		$showPrevNextEntryLinkPos	= $request->trimCheckedValueOf('item_show_prev_next_entry_link');	// 前後記事リンクを表示するかどうか
		$prevNextEntryLinkPos		= $request->trimValueOf('item_prev_next_entry_link_pos');				// 前後記事リンク表示位置
		$showEntryAuthor	= $request->trimCheckedValueOf('item_show_entry_author');	// 投稿者を表示するかどうか
		$showEntryRegistDt	= $request->trimCheckedValueOf('item_show_entry_regist_dt');	// 投稿日時を表示するかどうか
		$showEntryViewCount	= $request->trimCheckedValueOf('item_show_entry_view_count');				// 閲覧数を表示するかどうか
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			$this->checkNumeric($maxCommentLength, 'コメント最大文字数');
			
			// 記事デフォルト画像のエラーチェック
			if (!empty($updatedEntryImage)){
				list($entryImageFilenameArray, $tmpArray) = $this->gInstance->getImageManager()->getSystemThumbFilename('0'/*デフォルト画像*/);
				for ($i = 0; $i < count($entryImageFilenameArray); $i++){
					$path = $this->tmpDir . DIRECTORY_SEPARATOR . $entryImageFilenameArray[$i];
					if (!file_exists($path)){
						$this->setAppErrorMsg('記事デフォルト画像が正常にアップロードされていません');
						break;
					}
				}
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 空の場合はデフォルト値を設定
				if (empty($titleList)) $titleList = blog_mainCommonDef::DEFAULT_TITLE_LIST;				// 一覧タイトル
				if (empty($titleSearchList)) $titleSearchList = blog_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;// 検索結果タイトル
				if (empty($titleNoEntry)) $titleNoEntry = blog_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;	// 記事なし時タイトル
				if (empty($messageNoEntry)) $messageNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;// 記事が登録されていないメッセージ
				if (empty($messageFindNoEntry)) $messageFindNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;	// 記事が見つからないメッセージ
			
				$ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_VIEW_COUNT, $entryViewCount);				// 記事表示数
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_VIEW_ORDER, $entryViewOrder);	// 記事表示順
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_CATEGORY_COUNT, $categoryCount);		// カテゴリ数
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_RECEIVE_COMMENT, $receiveComment);		// コメントを受け付けるかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MAX_COMMENT_LENGTH, $maxCommentLength);// コメント最大文字数
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_COMMENT_USER_LIMITED, $commentUserLimited);// コメントのユーザ制限
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_USE_MULTI_BLOG, $useMultiBlog);		// マルチブログ機能を使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MULTI_BLOG_TOP_CONTENT, $topContent);	// マルチブログ時のトップコンテンツ
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_READMORE_LABEL, $readmoreLabel);			//「もっと読む」ボタンラベル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_LIST_DISP_TYPE, $this->entryListDispType);// 記事一覧表示タイプ
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_SHOW_ENTRY_LIST_IMAGE, $showEntryListImage);// 記事一覧に画像を表示するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_LIST_IMAGE_TYPE, $this->entryListImageType);		// 一覧用画像タイプ
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE, $layoutEntrySingle);		// コンテンツレイアウト(記事詳細)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST, $layoutEntryList);		// コンテンツレイアウト(記事一覧)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_LAYOUT_COMMENT_LIST, $layoutCommentList);		// コンテンツレイアウト(コメント一覧)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_OUTPUT_HEAD, $outputHead);		// ヘッダ出力するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_HEAD_VIEW_DETAIL, $headViewDetail);	// ヘッダ出力(詳細表示)
//				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_USE_WIDGET_TITLE, $useWidgetTitle);		// ウィジェットタイトルを使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_DEFAULT, $titleDefault);		// デフォルトタイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_LIST, $titleList);			// 一覧タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_SEARCH_LIST, $titleSearchList);		// 検索結果タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_NO_ENTRY, $titleNoEntry);		// 記事なし時タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MESSAGE_NO_ENTRY, $messageNoEntry);		// 記事が登録されていないメッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY, $messageFindNoEntry);		// 記事が見つからないメッセージ
//				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_TAG_LEVEL, $titleTagLevel);		// タイトルタグレベル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_SHOW_PREV_NEXT_ENTRY_LINK, $showPrevNextEntryLinkPos);	// 前後記事リンクを表示するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_PREV_NEXT_ENTRY_LINK_POS, $prevNextEntryLinkPos);				// 前後記事リンク表示位置
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_SHOW_ENTRY_AUTHOR, $showEntryAuthor);	// 投稿者を表示するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_SHOW_ENTRY_REGIST_DT, $showEntryRegistDt);	// 投稿日時を表示するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_SHOW_ENTRY_VIEW_COUNT, $showEntryViewCount);	// 閲覧数を表示するかどうか
		
				// 画像の移動
				if ($ret && !empty($updatedEntryImage)){		// 画像更新の場合
					$ret = mvFileToDir($this->tmpDir, $entryImageFilenameArray, $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType,
								''/*ディレクトリ取得*/));
					if ($ret){
						$ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE, implode(';', $entryImageFilenameArray));
					}
				}
																									
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$reloadData = true;		// データを再取得
				
					// 作業ディレクトリを削除
					rmDirectory($this->tmpDir);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == self::ACT_UPLOAD_IMAGE){		// 画像アップロード
			// 作業ディレクトリを作成
			$this->tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $this->tmpDir);
		} else if ($act == self::ACT_GET_IMAGE){			// 画像取得
			// Ajaxでの画像取得
			$this->getImageByType($imageType);
		} else {		// 初期表示の場合
			$reloadData = true;		// データを再取得
			
			// 作業ディレクトリを削除
			rmDirectory($this->tmpDir);
		}
		if ($reloadData){		// データを再取得
			$entryViewCount	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
			if (empty($entryViewCount)) $entryViewCount = blog_mainCommonDef::DEFAULT_VIEW_COUNT;
			$entryViewOrder	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
			$categoryCount	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_CATEGORY_COUNT);// カテゴリ数
			if (empty($categoryCount)) $categoryCount = blog_mainCommonDef::DEFAULT_CATEGORY_COUNT;
			$receiveComment	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_RECEIVE_COMMENT);
			$maxCommentLength = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MAX_COMMENT_LENGTH);	// コメント最大文字数
			if ($maxCommentLength == '') $maxCommentLength = blog_mainCommonDef::DEFAULT_COMMENT_LENGTH;
			$commentUserLimited = self::$_mainDb->getConfig(blog_mainCommonDef::CF_COMMENT_USER_LIMITED);	// コメントのユーザ制限
			if (!isset($commentUserLimited)) $commentUserLimited = '0';
			$useMultiBlog = self::$_mainDb->getConfig(blog_mainCommonDef::CF_USE_MULTI_BLOG);// マルチブログ機能を使用するかどうか
			if (!isset($useMultiBlog)) $useMultiBlog = '0';
			$topContent = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MULTI_BLOG_TOP_CONTENT);// マルチブログ時のトップコンテンツ
			$readmoreLabel = self::$_mainDb->getConfig(blog_mainCommonDef::CF_READMORE_LABEL);			//「もっと読む」ボタンラベル
			$this->entryListDispType	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_LIST_DISP_TYPE);// 記事一覧表示タイプ
			$showEntryListImage			= self::$_mainDb->getConfig(blog_mainCommonDef::CF_SHOW_ENTRY_LIST_IMAGE);// 記事一覧に画像を表示するかどうか
			$this->entryListImageType	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_LIST_IMAGE_TYPE);		// 一覧用画像タイプ
			if (empty($this->entryListImageType)) $this->entryListImageType = blog_mainCommonDef::DEFAULT_ENTRY_LIST_IMAGE_TYPE;				// 画像タイプデフォルト
			$layoutEntrySingle = self::$_mainDb->getConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE);		// コンテンツレイアウト(記事詳細)
			if (empty($layoutEntrySingle)) $layoutEntrySingle = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;
			$layoutEntryList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST);		// コンテンツレイアウト(記事一覧)
			if (empty($layoutEntryList)) $layoutEntryList = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_LIST;
			$layoutCommentList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_LAYOUT_COMMENT_LIST);		// コンテンツレイアウト(コメント一覧)
			if (empty($layoutCommentList)) $layoutCommentList = blog_mainCommonDef::DEFAULT_LAYOUT_COMMENT_LIST;
			$outputHead = self::$_mainDb->getConfig(blog_mainCommonDef::CF_OUTPUT_HEAD);		// ヘッダ出力するかどうか
			$headViewDetail = self::$_mainDb->getConfig(blog_mainCommonDef::CF_HEAD_VIEW_DETAIL);		// ヘッダ出力(詳細表示)
//			$useWidgetTitle = self::$_mainDb->getConfig(blog_mainCommonDef::CF_USE_WIDGET_TITLE);		// ウィジェットタイトルを使用するかどうか
			$titleDefault = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_DEFAULT);		// デフォルトタイトル
			$titleList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_LIST);		// 一覧タイトル
			if (empty($titleList)) $titleList = blog_mainCommonDef::DEFAULT_TITLE_LIST;
			$titleSearchList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_SEARCH_LIST);		// 検索結果タイトル
			if (empty($titleSearchList)) $titleSearchList = blog_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;
			$titleNoEntry = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_NO_ENTRY);		// 記事なし時タイトル
			if (empty($titleNoEntry)) $titleNoEntry = blog_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;
			$messageNoEntry = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MESSAGE_NO_ENTRY);		// 記事が登録されていないメッセージ
			if (empty($messageNoEntry)) $messageNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;
			$messageFindNoEntry = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY);		// 記事が見つからないメッセージ
			if (empty($messageFindNoEntry)) $messageFindNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;
//			$titleTagLevel = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_TAG_LEVEL);		// タイトルタグレベル
//			if (empty($titleTagLevel)) $titleTagLevel = blog_mainCommonDef::DEFAULT_TITLE_TAG_LEVEL;
			$showPrevNextEntryLinkPos	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_SHOW_PREV_NEXT_ENTRY_LINK);	// 前後記事リンクを表示するかどうか
			$prevNextEntryLinkPos		= self::$_mainDb->getConfig(blog_mainCommonDef::CF_PREV_NEXT_ENTRY_LINK_POS);				// 前後記事リンク表示位置
			$showEntryAuthor	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_SHOW_ENTRY_AUTHOR);	// 投稿者を表示するかどうか
			$showEntryRegistDt	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_SHOW_ENTRY_REGIST_DT);	// 投稿日時を表示するかどうか
			$showEntryViewCount	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_SHOW_ENTRY_VIEW_COUNT);				// 閲覧数を表示するかどうか
		}
		
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		
		// 記事一覧表示タイプ選択メニュー作成
		$this->createEntryListDispTypeMenu();
	
		// 一覧画像タイプ選択メニュー作成
		$this->createEntryListImageTypeMenu();
		
		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_UPLOAD_IMAGE;
		$this->tmpl->addVar("_widget", "upload_url_entryimage", $this->getUrl($uploadUrl . '&type=' . self::IMAGE_TYPE_ENTRY_IMAGE));		// 記事デフォルト画像
		
		// ##### 画像の表示 #####
		// アップロードされているファイルがある場合は、アップロード画像を表示
		// 記事デフォルト画像
		$imageUrl = '';
		$updateStatus = '0';			// 画像更新状態
		$entryImageFilename = $this->getDefaultEntryImageFilename();		// 記事デフォルト画像名取得
		if (!empty($entryImageFilename)){
			$imageUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $entryImageFilename) . '?' . date('YmdHis');
		}
		$this->tmpl->addVar("_widget", "entryimage_url", $this->convertUrlToHtmlEntity($this->getUrl($imageUrl)));			// 記事デフォルト画像
		$this->tmpl->addVar("_widget", "updated_entryimage", $updateStatus);
		
		$this->tmpl->addVar("_widget", "category_count", $this->convertToDispString($categoryCount));// カテゴリ数
		$this->tmpl->addVar("_widget", "receive_comment", $this->convertToCheckedString($receiveComment));// コメントを受け付けるかどうか
		$this->tmpl->addVar("_widget", "max_comment_length", $this->convertToDispString($maxCommentLength));// コメント最大文字数
		$this->tmpl->addVar("_widget", "comment_user_limited", $this->convertToCheckedString($commentUserLimited));// コメントのユーザ制限
		$this->tmpl->addVar("_widget", "use_multi_blog", $this->convertToCheckedString($useMultiBlog));// マルチブログ機能を使用するかどうか
		$this->tmpl->addVar("_widget", "top_content", $topContent);		// マルチブログ時のトップコンテンツ
		$this->tmpl->addVar("_widget", "readmore_label", $this->convertToDispString($readmoreLabel));			//「もっと読む」ボタンラベル
		$this->tmpl->addVar("_widget", "show_entry_list_image",	$this->convertToCheckedString($showEntryListImage));// 記事一覧に画像を表示するかどうか
		$this->tmpl->addVar("_widget", "layout_entry_single", $layoutEntrySingle);		// コンテンツレイアウト(記事詳細)
		$this->tmpl->addVar("_widget", "layout_entry_list", $layoutEntryList);		// コンテンツレイアウト(記事一覧)
		$this->tmpl->addVar("_widget", "layout_comment_list", $layoutCommentList);		// コンテンツレイアウト(コメント一覧)
		$this->tmpl->addVar("_widget", "output_head_checked", $this->convertToCheckedString($outputHead));		// ヘッダ出力するかどうか
		$this->tmpl->addVar("_widget", "head_view_detail", $headViewDetail);		// ヘッダ出力(詳細表示)
//		$this->tmpl->addVar("_widget", "use_widget_title",	$this->convertToCheckedString($useWidgetTitle));// ウィジェットタイトルを使用するかどうか
		$this->tmpl->addVar("_widget", "title_default", $titleDefault);		// デフォルトタイトル
		$this->tmpl->addVar("_widget", "title_list", $titleList);		// 一覧タイトル
		$this->tmpl->addVar("_widget", "title_search_list", $titleSearchList);		// 検索結果タイトル
		$this->tmpl->addVar("_widget", "title_no_entry", $titleNoEntry);		// 記事なし時タイトル
		$this->tmpl->addVar("_widget", "message_no_entry", $messageNoEntry);		// 記事が登録されていないメッセージ
		$this->tmpl->addVar("_widget", "message_find_no_entry", $messageFindNoEntry);		// 記事が見つからないメッセージ
//		$this->tmpl->addVar("_widget", "title_tag_level", $titleTagLevel);		// タイトルタグレベル
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());		// 画像アップロードエリア
		$this->tmpl->addVar("_widget", "show_prev_next_entry_link",	$this->convertToCheckedString($showPrevNextEntryLinkPos));	// 前後記事リンクを表示するかどうか	
		$this->tmpl->addVar("_widget", "prev_next_entry_link_pos_top", $this->convertToSelectedString($prevNextEntryLinkPos, 0));// 前後記事リンク表示位置(0=上、1=下)
		$this->tmpl->addVar("_widget", "prev_next_entry_link_pos_bottom", $this->convertToSelectedString($prevNextEntryLinkPos, 1));// 前後記事リンク表示位置(0=上、1=下)
		$this->tmpl->addVar("_widget", "show_entry_author",	$this->convertToCheckedString($showEntryAuthor));	// 投稿者を表示するかどうか
		$this->tmpl->addVar("_widget", "show_entry_regist_dt",	$this->convertToCheckedString($showEntryRegistDt));	// 投稿日時を表示するかどうか
		$this->tmpl->addVar("_widget", "show_entry_view_count",	$this->convertToCheckedString($showEntryViewCount));	// 閲覧数を表示するかどうか
	}
	/**
	 * 最大画像を取得
	 *
	 * @param string $type		画像タイプ
	 * @return					なし
	 */
	function getImageByType($type)
	{
		// 画像パス作成
		switch ($type){
		case self::IMAGE_TYPE_ENTRY_IMAGE:			// 記事デフォルト画像
			$filename = $this->getDefaultEntryImageFilename();		// 記事デフォルト画像名取得
			break;
		}
		$imagePath = '';
		if (!empty($filename)) $imagePath = $this->gEnv->getTempDirBySession() . '/' . $filename;
			
		// ページ作成処理中断
		$this->gPage->abortPage();

		if (is_readable($imagePath)){
			// 画像情報を取得
			$imageMimeType = '';
			$imageSize = @getimagesize($imagePath);
			if ($imageSize) $imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
			
			// 画像MIMEタイプ設定
			if (!empty($imageMimeType)) header('Content-type: ' . $imageMimeType);
			
			// キャッシュの設定
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0');
			header('Pragma: no-cache');
		
			// 画像ファイル読み込み
			readfile($imagePath);
		} else {
			$this->gPage->showError(404);
		}
	
		// システム強制終了
		$this->gPage->exitSystem();
	}
	/**
	 * アップロードファイルから各種画像を作成
	 *
	 * @param bool           $isSuccess		アップロード成功かどうか
	 * @param object         $resultObj		アップロード処理結果オブジェクト
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $filePath		アップロードされたファイル
	 * @param string         $destDir		アップロード先ディレクトリ
	 * @return								なし
	 */
	function uploadFile($isSuccess, &$resultObj, $request, $filePath, $destDir)
	{
		$type = $request->trimValueOf('type');		// 画像タイプ
		
		if ($isSuccess){		// ファイルアップロード成功のとき
			// 各種画像を作成
			switch ($type){
			case self::IMAGE_TYPE_ENTRY_IMAGE:			// 記事デフォルト画像
				$formats = $this->gInstance->getImageManager()->getSystemThumbFormat();
				$filenameBase = '0';
				break;
			}

			$ret = $this->gInstance->getImageManager()->createImageByFormat($filePath, $formats, $destDir, $filenameBase, $destFilename);
			if ($ret){			// 画像作成成功の場合
				// 画像参照用URL
				$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
				$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
				$imageUrl .= '&type=' . $type . '&' . date('YmdHis');
				$resultObj['url'] = $imageUrl;
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
	/**
	 * 記事デフォルト画像名を取得
	 *
	 * @return string		ファイル名
	 */
	function getDefaultEntryImageFilename()
	{
		$filename = '';
		$value = self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE);
		if (!empty($value)){
			$filenameArray = explode(';', $value);
			$filename = $filenameArray[count($filenameArray) -1];
		}
		return $filename;
	}
	/**
	 * 画像タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createEntryListImageTypeMenu()
	{
		$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(1/*クロップ画像のみ*/);
		
		for ($i = 0; $i < count($formats); $i++){
			$id = $formats[$i];
			$name = $id;
			
			$selected = '';
			if ($id == $this->entryListImageType) $selected = 'selected';

			$row = array(
				'value'			=> $this->convertToDispString($id),				// 値
				'name'			=> $this->convertToDispString($name),			// 名前
				'selected'		=> $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('entry_list_image_type_list', $row);
			$this->tmpl->parseTemplate('entry_list_image_type_list', 'a');
		}
	}
	/**
	 * 記事一覧表示タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createEntryListDispTypeMenu()
	{
		for ($i = 0; $i < count($this->entryListDispTypeArray); $i++){
			$value = $this->entryListDispTypeArray[$i]['value'];
			$name = $this->entryListDispTypeArray[$i]['name'];
			$selected = '';
			if ($this->entryListDispType == $value) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('entry_list_disp_type_list', $row);
			$this->tmpl->parseTemplate('entry_list_disp_type_list', 'a');
		}
	}
}
?>
