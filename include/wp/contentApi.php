<?php
/**
 * コンテンツAPI
 *
 * 主コンテンツ取得API。外部公開用にアクセス制限をチェックしアクティブなコンテンツのみ取得可能。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/baseApi.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/valueCheck.php');

class ContentApi extends BaseApi
{
	private $contentType;			// コンテンツタイプ
	private $pageType;				// ページタイプ(WordPressテンプレートのpage,single,search)
	private $accessPoint;			// アクセスポイント(空文字列=PC用,m=携帯用,s=スマートフォン用)
	private $langId;				// コンテンツの言語(コンテンツ取得用)
	private $limit;					// コンテンツ取得数(コンテンツ取得用)
	private $pageNo;				// ページ番号(1～)(コンテンツ取得用)
	private $order;					// コンテンツ並び順(0=昇順,1=降順)
	private $keywords;				// 検索条件(キーワード)
	private $startDt;				// 検索条件(期間開始日時)
	private $endDt;					// 検索条件(期間終了日時)
	private $contentArray;			// 取得コンテンツ
	private $serialArray;			// 取得したコンテンツのシリアル番号
	private $categoryArray;			// 取得コンテンツに関連したカテゴリー
	private $contentId;				// 表示するコンテンツのID(複数の場合は配列)
	private $prevNextBaseValue;		// 前後のコンテンツ取得用のベース値
	private $relativePosts;			// 現在のコンテンツに関連したWP_Postオブジェクト
	
	const CF_DEFAULT_CONTENT_TYPE = 'default_content_type';		// デフォルトコンテンツタイプ取得用
	const DEFAULT_CONTENT_TYPE = 'blog';		// デフォルトコンテンツタイプのデフォルト値
	const DETECT_GOOGLEMAPS = 'Magic3 googlemaps v';		// Googleマップ検出用文字列
	// アドオンオブジェクト作成用
	const ADDON_OBJ_ID_CONTENT	= 'contentlib';
	const ADDON_OBJ_ID_BLOG		= 'bloglib';
	const ADDON_OBJ_ID_PRODUCT	= 'eclib';
	const LINKINFO_OBJ_ID		= 'linkinfo';				// リンク情報取得用

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// コンテンツタイプを取得
		// コンテンツタイプが設定されているページの場合は該当するコンテンツタイプのデータでWordPressの画面処理を行い、コンテンツタイプがない場合はMagic3のウィジェット処理で出力
//		$this->contentType = $this->gSystem->getSystemConfig(self::CF_DEFAULT_CONTENT_TYPE);// デフォルトコンテンツタイプ
//		if (empty($this->contentType)) $this->contentType = self::DEFAULT_CONTENT_TYPE;
		$this->contentType = '';
			
		// 現在のページにコンテンツタイプがある場合は取得
		$contentType = $this->gPage->getContentType();
		if (!empty($contentType)){
			// メインコンテンツタイプのみ対象とする
			$mainContentTypes = $this->gPage->getMainContentTypes();
			if (in_array($contentType, $mainContentTypes)) $this->contentType = $contentType;
		}
		
		// コンテンツのアクセス権のチェック(メインウィジェットがページに配置されているか)
		$widgetId = $this->gPage->getWidgetIdWithPageInfoByContentType($this->gEnv->getCurrentPageId(), $this->contentType);
		if (empty($widgetId)){
			$msgDetail = '対策：画面構成機能を使用して、該当するコンテンツタイプのページ属性のページにそのコンテンツを処理するメインウィジェットを配置します。';
			$this->gOpeLog->writeError(__METHOD__, 'デフォルトのコンテンツタイプに対応するメインウィジェットが配置されていません。(コンテンツタイプ=' . $this->contentType . ')', 2200, $msgDetail);
			
			// エラー処理
			$this->contentType = '';
		}
		
		// メンバー変数初期化
		$this->relativePosts = array();			// 現在のコンテンツに関連したWP_Postオブジェクト
		$this->serialArray = array();			// 取得したコンテンツのシリアル番号
	}
	/**
	 * 対象のコンテンツタイプのアドオンオブジェクトを取得
	 *
	 * @return object 		アドオンオブジェクト
	 */
	function _getAddonObj()
	{
		switch ($this->contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
			$addonObj = $this->gInstance->getObject(self::ADDON_OBJ_ID_CONTENT);
			break;
		case M3_VIEW_TYPE_PRODUCT:	// 製品
			$addonObj = $this->gInstance->getObject(self::ADDON_OBJ_ID_PRODUCT);
			break;
		case M3_VIEW_TYPE_BBS:	// BBS
			break;
		case M3_VIEW_TYPE_BLOG:	// ブログ
			$addonObj = $this->gInstance->getObject(self::ADDON_OBJ_ID_BLOG);
			break;
		case M3_VIEW_TYPE_WIKI:	// Wiki
			break;
		case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
			break;
		case M3_VIEW_TYPE_EVENT:	// イベント
			break;
		case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
			break;
		}
		
		return $addonObj;
	}
	/**
	 * [WordPressテンプレート用API]SQLクエリー用のパラメータを設定
	 *
	 * @param array $params				クエリー作成用パラメータ(連想配列)
	 * @param strin $langId				言語ID。空の場合は現在の言語。
	 * @param int   $limit				取得する項目数最大値(0の場合はデフォルト値)
	 * @param int	$pageNo				取得するページ番号(1～)
	 * @param string,array $keywords	検索条件(キーワード)
	 * @param timestamp $startDt		検索条件(期間開始日時)
	 * @param timestamp $endDt			検索条件(期間終了日時)
	 * @return							なし
	 */
	public function setCondition($params, $langId, $limit, $pageNo, $keywords = '', $startDt = null, $endDt = null)
	{
		// アドオンオブジェクト取得
		$addonObj = $this->_getAddonObj();
		
		// 一覧の表示項目数取得
		$viewCount = $addonObj->getPublicContentViewCount();
		
		// 初期値設定
		$this->accessPoint = $this->gEnv->getAccessDir();		// アクセスポイント
		$this->langId = $this->gEnv->getCurrentLanguage();				// コンテンツの言語(コンテンツ取得用)
		$this->limit = $viewCount;					// コンテンツ取得数(コンテンツ取得用)
		$this->pageNo = 1;							// ページ番号(コンテンツ取得用)
		$this->order = 1;							// コンテンツ並び順。デフォルトは降順。
		$this->now = date("Y/m/d H:i:s");			// 現在日時
		$this->keywords = $keywords;		// 検索条件(キーワード)
		$this->startDt = $startDt;			// 検索条件(期間開始日時)
		$this->endDt = $endDt;				// 検索条件(期間終了日時)
		
		// パラメータ値で更新
		if (!empty($langId)) $this->langId = $langId;				// コンテンツの言語(コンテンツ取得用)
		if (!empty($limit))$this->limit = $limit;					// コンテンツ取得数(コンテンツ取得用)
		if (!empty($pageNo))$this->pageNo = $pageNo;				// ページ番号(コンテンツ取得用)
	}
	/**
	 * [WordPressテンプレート用API]コンテンツを取得
	 *
	 * @return array     				WP_Postオブジェクトの配列
	 */
	function getContentList()
	{
		$this->contentArray = array();			// 取得コンテンツ初期化
		
		// 取得条件作成。コンテンツIDが設定されている場合は指定したコンテンツのみ取得。
		if (empty($this->contentId)){
			$entryId = 0;		// コンテンツID以外の条件で取得
		} else {
			$entryId = $this->contentId;		// コンテンツIDを指定。コンテンツIDは複数あり。
		}
		// アドオンオブジェクト取得
		$addonObj = $this->_getAddonObj();
		
		// データ取得
		$addonObj->getPublicContentList($this->limit, $this->pageNo, $entryId, $this->now, $this->startDt, $this->endDt, $this->keywords, $this->langId, $this->order, array($this, '_itemListLoop'),  null/*カテゴリーID*/, null/*ブログID*/);
		
		return $this->contentArray;
	}
	/**
	 * [WordPressテンプレート用API]コンテンツ総数を取得
	 *
	 * 機能: getContentList()の検索条件でコンテンツの総数を取得。
	 *
	 * @return int     			コンテンツ総数
	 */
	function getContentCount()
	{
		// 取得条件にコンテンツIDが設定されている場合は総数0を返す
		if (!empty($this->contentId)) return 0;
		
		// アドオンオブジェクト取得
		$addonObj = $this->_getAddonObj();
		
		// コンテンツ総数取得
		$count = $addonObj->getPublicContentCount($this->now, $this->startDt, $this->endDt, $this->keywords, $this->langId, null/*カテゴリーID*/, null/*ブログID*/);
		return $count;
	}
	/**
	 * [WordPressテンプレート用API]1ページあたりのコンテンツ表示数を取得
	 *
	 * @return int     			コンテンツ数
	 */
	function getContentViewCount()
	{
		return $this->limit;
	}
	/**
	 * [WordPressテンプレート用API]現在取得中のコンテンツ基準で前のコンテンツを取得
	 *
	 * @return object     				WP_Postオブジェクト
	 */
	function getPrevContent()
	{
		// 戻り値初期化
		$wpPostObj = false;
		
		// アドオンオブジェクト取得
		$addonObj = $this->_getAddonObj();
		
		$row = $addonObj->getPublicPrevNextEntry(0/*前方データ*/, $this->prevNextBaseValue, $this->now, $startDt, $endDt, $keywords, $this->langId, $this->order, null/*カテゴリーID*/, null/*ブログID*/);
		if ($row) $wpPostObj = $this->_createWP_Post($row);

		// 取得オブジェクト保存
		if ($wpPostObj) $this->relativePosts[$wpPostObj->ID] = $wpPostObj;
		
		return $wpPostObj;
	}
	/**
	 * [WordPressテンプレート用API]現在取得中のコンテンツ基準で次のコンテンツを取得
	 *
	 * @return object     				WP_Postオブジェクト
	 */
	function getNextContent()
	{
		// 戻り値初期化
		$wpPostObj = false;
		
		// アドオンオブジェクト取得
		$addonObj = $this->_getAddonObj();
		
		$row = $addonObj->getPublicPrevNextEntry(1/*後方データ*/, $this->prevNextBaseValue, $this->now, $startDt, $endDt, $keywords, $this->langId, $this->order, null/*カテゴリーID*/, null/*ブログID*/);
		if ($row) $wpPostObj = $this->_createWP_Post($row);

		// 取得オブジェクト保存
		if ($wpPostObj) $this->relativePosts[$wpPostObj->ID] = $wpPostObj;
		
		return $wpPostObj;
	}
	/**
	 * [WordPressテンプレート用API]関連オブジェクト取得
	 *
	 * @param int $id					WP_PostオブジェクトのID
	 * @return object     				WP_Postオブジェクト
	 */
	function getRelativePost($id)
	{
		return $this->relativePosts[$id];
	}
	/**
	 * 取得コンテンツに関連付けされているカテゴリーを取得
	 *
	 * @param string $id					コンテンツID
	 * @return array						関連付けされているカテゴリー
	 */
	function getCategory($contentId)
	{
		// コンテンツIDが0のときは「未分類」カテゴリーを返す
		if ($contentId == 0){
			// カテゴリー情報をWP_Termオブジェクトに変換して格納
			$term = new stdClass;
			$term->term_id = -1;			// カテゴリーID。カテゴリーIDが0の場合カテゴリーラベルが非表示になるので0以外を設定。
			$term->name = __('Uncategorized');		// カテゴリー名
			$term->taxonomy = 'category';		// 種別はカテゴリーに設定

			// WP_Termオブジェクトに変換
			$wpTermObj = new WP_Term($term);
					
			return array($wpTermObj);
		}
		
		// 初回取得時にDBからデータを取得
		if (!isset($this->categoryArray)){			// 取得コンテンツに関連したカテゴリー
			// 取得カテゴリー初期化
			$this->categoryArray = array();
			
			// アドオンオブジェクト取得
			$addonObj = $this->_getAddonObj();
		
			// コンテンツのシリアル番号でカテゴリーを取得
			$rows = $addonObj->getContentCategoryBySerial($this->serialArray);
			if ($rows){
				$savedContentId = 0;		// コンテンツID退避用
				$categoryArray = array();
				$rowCount = count($rows);
				for ($i = 0; $i < $rowCount; $i++){
					$row = $rows[$i];

					// IDを解析しエラーチェック。複数の場合は配列に格納する。
					switch ($this->contentType){
					case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
						$id		= $row['cn_id'];			// コンテンツID
	//					$categoryId = ;// カテゴリーID
	//					$categoryTitle	= ;	// カテゴリー名
						break;
					case M3_VIEW_TYPE_BLOG:	// ブログ
						$id				= $row['be_id'];	// コンテンツID
						$categoryId		= $row['bc_id'];	// カテゴリーID
						$categoryTitle	= $row['bc_name'];	// カテゴリー名
						break;
					}
		
					// カテゴリー情報をWP_Termオブジェクトに変換して格納
					$term = new stdClass;
					$term->term_id = $categoryId;			// カテゴリーID
					$term->name = $categoryTitle;		// カテゴリー名
					$term->taxonomy = 'category';		// 種別はカテゴリーに設定
		
					// WP_Termオブジェクトに変換
					$wpTermObj = new WP_Term($term);
					
					if ($id != $savedContentId){
						// コンテンツが変更された場合は一旦保存
						if ($savedContentId > 0) $this->categoryArray[$savedContentId] = $categoryArray;
						
						// 現在のコンテンツIDを更新
						$categoryArray = array();
						$savedContentId = $id;
					}
					
					// WP_Termオブジェクトを追加
					$categoryArray[] = $wpTermObj;
					
					// 最後の項目の処理
					if ($i == $rowCount -1){
						if (count($categoryArray) > 0) $this->categoryArray[$savedContentId] = $categoryArray;
					}
				}
			}
		}
		return $this->categoryArray[$contentId];
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function _itemListLoop($index, $fetchedRow, $param)
	{
		$wpPostObj = $this->_createWP_Post($fetchedRow);
		$this->contentArray[] = $wpPostObj;
		
		// 前後のコンテンツ取得用のベース値を保存。単一コンテンツ表示の場合に使用。
		switch ($this->contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
			$this->prevNextBaseValue = $wpPostObj->ID;		// 前後のコンテンツ取得用のベース値(ID)
			break;
		case M3_VIEW_TYPE_BLOG:	// ブログ
			$this->prevNextBaseValue = $wpPostObj->post_date;		// 前後のコンテンツ取得用のベース値(登録日時)
			break;
		}
		return true;
	}
	/**
	 * テーブル行データからWP_Postオブジェクトを作成
	 *
	 * @param array $row			テーブル行データ
	 * @return object				WP_Postオブジェクト
	 */
	function _createWP_Post($row)
	{
		// IDを解析しエラーチェック。複数の場合は配列に格納する。
		switch ($this->contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
			$serial = $row['cn_serial'];
			$id		= $row['cn_id'];
			$title	= $row['cn_name'];
			$authorId		= $row['cn_create_user_id'];		// コンテンツ登録者
			$authorName		= $row['lu_name'];				// コンテンツ登録者名
			$authorUrl		= '';			// コンテンツ登録者URL
			$date			= $row['cn_create_dt'];
			$contentHtml	= $row['cn_html'];
			$thumbSrc		= $row['cn_thumb_src'];	// サムネールの元のファイル(リソースディレクトリからの相対パス)
			break;
		case M3_VIEW_TYPE_BLOG:	// ブログ
			$serial = $row['be_serial'];
			$id		= $row['be_id'];
			$title	= $row['be_name'];
			$authorId		= $row['be_regist_user_id'];		// コンテンツ登録者
			$authorName		= $row['lu_name'];				// コンテンツ登録者名
			$authorUrl		= '';			// コンテンツ登録者URL
			$date			= $row['be_regist_dt'];
			$contentHtml	= $row['be_html'];
			$thumbSrc		= $row['be_thumb_src'];	// サムネールの元のファイル(リソースディレクトリからの相対パス)
			break;
		}

		// コンテンツマクロ変換
		$contentHtml = $this->_convertM3ToHtml($contentHtml, true/*改行コーをbrタグに変換*/);
		
		// WP_Postオブジェクトに変換して格納
		$post = new stdClass;
		$post->ID = $id;
		$post->post_author = $authorId;		// 登録者のユーザID
		$post->post_date = $date;
		$post->post_date_gmt = '';
		$post->post_password = '';
		$post->post_name = '';		// スラッグ等で使用されるので設定しない
		$post->post_type = 'post';
		$post->post_status = 'publish';
		$post->to_ping = '';
		$post->pinged = '';
		$post->comment_status = 'closed';// コメント欄の表示設定
		$post->ping_status = 'closed';
	/*		$post->comment_status = get_default_comment_status( $post_type );
			$post->ping_status = get_default_comment_status( $post_type, 'pingback' );
			$post->post_pingback = get_option( 'default_pingback_flag' );
			$post->post_category = get_option( 'default_category' );*/
		$post->post_parent = 0;
		$post->menu_order = 0;
		// Magic3設定値追加
		$post->post_title = $title;
		$post->post_content = $contentHtml;
		$post->guid = $this->getContentUrl($id);	// 詳細画面URL
		$post->filter = 'raw';
		// Magic3独自パラメータ
		$post->thumb_src = $thumbSrc;
		$post->display_name = $authorName;			// コンテンツ登録者名
		$post->authorUrl = $authorUrl;				// コンテンツ登録者URL
		
		// WP_Postオブジェクトに変換
		$wpPostObj = new WP_Post($post);
		
		// シリアル番号を保存
		$this->serialArray[] = $serial;			// 取得したコンテンツのシリアル番号
		
		return $wpPostObj;
	}
	/**
	 * Magic3マクロを変換してHTMLを作成
	 *
	 * ・デフォルトでマクロ変換した文字列に改行が含まれるときは改行をbrに変換する
	 *
	 * @param string $src			変換するデータ
	 * @param bool $convBr			キーワード変換部分の改行コードをBRタグに変換するかどうか
	 * @param array $contentInfo	コンテンツ情報
	 * @return string					変換後データ
	 */
	function _convertM3ToHtml($src, $convBr = true, $contentInfo = array())
	{
		// ### コンテンツ内容のチェック ###
		// Googleマップが含まれている場合はコンテンツ情報として登録->Googleマップライブラリの読み込み指示
		$pos = strpos($src, self::DETECT_GOOGLEMAPS);
		if ($pos !== false) $this->gPage->setIsContentGooglemaps(true);
		
		// URLを求める
		$rootUrl = $this->gEnv->getRootUrlByCurrentPage();
//		$widgetUrl = str_replace($this->gEnv->getRootUrl(), $rootUrl, $this->gEnv->getCurrentWidgetRootUrl());
		
		// パスを変換
		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $src);// アプリケーションルートを変換
//		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_URL . M3_TAG_END, $widgetUrl, $dest);// ウィジェットルートを変換
		
		// コンテンツマクロ変換
		$dest = $this->gInstance->getTextConvManager()->convContentMacro($dest, $convBr/*改行コードをbrタグに変換*/, $contentInfo, true/*変換後の値をHTMLエスケープ処理*/);
		
		// ウィジェット埋め込みタグを変換
		$this->gInstance->getTextConvManager()->convWidgetTag($dest, $dest);
		
		// 残っているMagic3タグ削除
		$dest = $this->gInstance->getTextConvManager()->deleteM3Tag($dest);
		return $dest;
	}
	/**
	 * デフォルトのサムネール画像の情報を取得
	 *
	 * @return array						画像パス,画像URL,画像幅,画像高さの配列
	 */
	function getDefaultThumbInfo()
	{
		static $thumbInfoArray;
		
		if (!isset($thumbInfoArray)){
			// アイキャッチ画像の情報を取得
			$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(10/*アイキャッチ画像*/);
			$ret = $this->gInstance->getImageManager()->parseImageFormat($formats[0], $imageType, $imageAttr, $imageSize);
		
			$filename = $this->gInstance->getImageManager()->getThumbFilename(0, $formats[0]);		// デフォルト画像ファイル名
			$thumbPath = $this->gInstance->getImageManager()->getSystemThumbPath($this->contentType, 0/*PC用*/, $filename);
			if (file_exists($thumbPath)){
				$thumbUrl = $this->gInstance->getImageManager()->getSystemThumbUrl($this->contentType, 0/*PC用*/, $filename);
				$thumbInfoArray = array($thumbPath, $thumbUrl, $imageSize, $imageSize);
			} else {
				$msgDetail = '画像パス:' . $thumbPath;
				$this->gOpeLog->writeError(__METHOD__, 'アイキャッチ用のデフォルト画像が見つかりません。(コンテンツタイプ=' . $this->contentType . ')', 2200, $msgDetail);
			}
		}
		return $thumbInfoArray;
	}
	/**
	 * コンテンツ詳細画面のURLを取得
	 *
	 * @param string $id					コンテンツID
	 * @return string						URL
	 */
	function getContentUrl($id)
	{
		$linkInfoObj = $this->gInstance->getObject(self::LINKINFO_OBJ_ID);
		$url = $linkInfoObj->getContentUrl($this->accessPoint/*アクセスポイント*/, $this->contentType, $id, $this->langId);
		return $url;
	}
	/**
	 * コンテンツタイプを取得
	 *
	 * @return string						コンテンツタイプ
	 */
	function getContentType()
	{
		return $this->contentType;
	}
	/**
	 * ページタイプを設定
	 *
	 * @param string $type					ページタイプ(page,single,search)
	 * @return								なし
	 */
	function setPageType($type)
	{
		$this->pageType = $type;
	}
	/**
	 * ページタイプを取得
	 *
	 * @return string						ページタイプ(page,single,search)
	 */
	function getPageType()
	{
		return $this->pageType;
	}
	/**
	 * コンテンツIDを設定
	 *
	 * @param string $idStr			コンテンツID文字列
	 * @return bool					true=正常終了、false=異常終了
	 */
	function setContentId($idStr)
	{
		global $wp_query;
		
		// IDを解析しエラーチェック。複数の場合は配列に格納する。
		switch ($this->contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
		case M3_VIEW_TYPE_PRODUCT:	// 製品
		case M3_VIEW_TYPE_BBS:	// BBS
		case M3_VIEW_TYPE_BLOG:	// ブログ
		case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
		case M3_VIEW_TYPE_EVENT:	// イベント
		case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
			// 数値型、複数可
			// すべて数値であるかチェック
			$contentIdArray = explode(',', $idStr);
			if (ValueCheck::isNumeric($contentIdArray)){
				$this->contentId = $contentIdArray;
				
				// 記事が単体の場合は単体記事表示を指定
				if (count($this->contentId) == 1) $wp_query->is_single = true;
			} else {
				return false;
			}
			break;
		case M3_VIEW_TYPE_WIKI:	// Wiki
			// 文字列型、単一のみ
			$this->contentId = $id;
			break;
		}
		return true;
	}
	/**
	 * ページリンク用URL取得
	 *
	 * @param int $pageNo			ページ番号(1～)。
	 * @return string				URL
	 */
	function getPageLinkUrl($pageNo)
	{
		global $wp_query;
				
		$baseUrl = '';
		$urlParams = '';
		
		// デフォルトページの場合はページIDは付加しない
		$subId = $this->gEnv->getCurrentPageSubId();
		if ($subId != $this->gEnv->getDefaultPageSubId()) $urlParams = M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subId;
		
		// ベースURLを取得
		switch ($this->accessPoint){
		case '':			// PC用
		default:
			$baseUrl = $this->gEnv->getDefaultUrl();
			break;
		case 'm':			// 携帯用
			$baseUrl = $this->gEnv->getDefaultMobileUrl();
			break;
		case 's':			// スマートフォン用
			$baseUrl = $this->gEnv->getDefaultSmartphoneUrl();
			break;
		}
		
		$baseUrl .= '?';
		if (!empty($urlParams)){
			$baseUrl .= '?' . $urlParams . '&';
		}
		$baseUrl .= M3_REQUEST_PARAM_PAGE_NO . '=' . $pageNo;
		
		// 検索条件が設定されている場合は付加
		$searchParam = $wp_query->get('s');
		if (!empty($searchParam)) $baseUrl .= '&s=' . $searchParam;
		
		$url = $this->getUrl($baseUrl);
		return $url;
	}
}
?>
