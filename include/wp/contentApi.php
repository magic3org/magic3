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
	private $langId;				// コンテンツの言語(コンテンツ取得用)
	private $limit;					// コンテンツ取得数(コンテンツ取得用)
	private $pageNo;				// ページ番号(1～)(コンテンツ取得用)
	private $contentArray;			// 取得コンテンツ
	private $contentId;				// 表示するコンテンツのID(複数の場合は配列)
	
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
		global $gPageManager;
		global $gSystemManager;
		global $gEnvManager;
		global $gRequestManager;
		global $gOpeLogManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// コンテンツタイプを取得
		// コンテンツタイプが設定されているページの場合は該当するコンテンツタイプのデータでWordPressの画面処理を行い、コンテンツタイプがない場合はMagic3のウィジェット処理で出力
//		$this->contentType = $gSystemManager->getSystemConfig(self::CF_DEFAULT_CONTENT_TYPE);// デフォルトコンテンツタイプ
//		if (empty($this->contentType)) $this->contentType = self::DEFAULT_CONTENT_TYPE;
		$this->contentType = '';
			
		// 現在のページにコンテンツタイプがある場合は取得
		$contentType = $gPageManager->getContentType();
		if (!empty($contentType)){
			// メインコンテンツタイプのみ対象とする
			$mainContentTypes = $gPageManager->getMainContentTypes();
			if (in_array($contentType, $mainContentTypes)) $this->contentType = $contentType;
		}
		
		// コンテンツのアクセス権のチェック(メインウィジェットがページに配置されているか)
		$widgetId = $gPageManager->getWidgetIdWithPageInfoByContentType($gEnvManager->getCurrentPageId(), $this->contentType);
		if (empty($widgetId)){
			$msgDetail = '対策：画面構成機能を使用して、該当するコンテンツタイプのページ属性のページにそのコンテンツを処理するメインウィジェットを配置します。';
			$gOpeLogManager->writeError(__METHOD__, 'デフォルトのコンテンツタイプに対応するメインウィジェットが配置されていません。(コンテンツタイプ=' . $this->contentType . ')', 2200, $msgDetail);
			
			// エラー処理
			$this->contentType = '';
		}
	}
	/**
	 * 対象のコンテンツタイプのアドオンオブジェクトを取得
	 *
	 * @return object 		アドオンオブジェクト
	 */
	function _getAddonObj()
	{
		global $gInstanceManager;
		
		switch ($this->contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
			$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_CONTENT);
			break;
		case M3_VIEW_TYPE_PRODUCT:	// 製品
			$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_PRODUCT);
			break;
		case M3_VIEW_TYPE_BBS:	// BBS
			break;
		case M3_VIEW_TYPE_BLOG:	// ブログ
			$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_BLOG);
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
	 * @param array $params			クエリー作成用パラメータ(連想配列)
	 * @param strin $langId			言語ID。空の場合は現在の言語。
	 * @param int   $limit			取得する項目数最大値(0の場合はデフォルト値)
	 * @param int	$pageNo			取得するページ番号(1～)
	 * @return						なし
	 */
	public function setCondition($params, $langId, $limit, $pageNo)
	{
		global $gEnvManager;

		// アドオンオブジェクト取得
		$addonObj = $this->_getAddonObj();
		
		// 一覧の表示項目数取得
		$viewCount = $addonObj->getPublicContentViewCount();
		
		// 初期値設定
		$this->langId = $gEnvManager->getCurrentLanguage();				// コンテンツの言語(コンテンツ取得用)
		$this->limit = $viewCount;					// コンテンツ取得数(コンテンツ取得用)
		$this->pageNo = 1;				// ページ番号(コンテンツ取得用)
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		
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
		$addonObj->getPublicContentList($this->limit, $this->pageNo, $entryId, $this->now, $startDt, $endDt, $keywords, $this->langId, $order, array($this, '_itemListLoop'), null/*ブログID*/);
		
		return $this->contentArray;
	}
	/**
	 * [WordPressテンプレート用API]総コンテンツ数を取得
	 *
	 * @return int     					コンテンツ数
	 */
/*	function getContentCount()
	{
		$addonObj = $this->_getAddonObj();
		$idArray = $addonObj->getPublicContentCount($this->langId, $this->limit, $this->pageNo);
		return $idArray;
	}*/
	/**
	 * [WordPressテンプレート用API]コンテンツ取得
	 *
	 * @param array     $ids				コンテンツID
	 * @return array						コンテンツデータ
	 */
/*	function selectContent($ids)
	{
		$addonObj = $this->_getAddonObj();
		$retValue = $addonObj->getList($langId, $limit, $pageNo, $rows);
		return $retValue;
	}*/
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
		// レコード値取得
		// IDを解析しエラーチェック。複数の場合は配列に格納する。
		switch ($this->contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
			$serial = $fetchedRow['cn_serial'];
			$id		= $fetchedRow['cn_id'];
			$title	= $fetchedRow['cn_name'];
			$authorId		= $fetchedRow['cn_create_user_id'];		// コンテンツ登録者
			$authorName		= $fetchedRow['lu_name'];				// コンテンツ登録者名
			$authorUrl		= '';			// コンテンツ登録者URL
			$date			= $fetchedRow['cn_create_dt'];
			$contentHtml	= $fetchedRow['cn_html'];
			$thumbSrc		= $fetchedRow['cn_thumb_src'];	// サムネールの元のファイル(リソースディレクトリからの相対パス)
			break;
		case M3_VIEW_TYPE_BLOG:	// ブログ
			$serial = $fetchedRow['be_serial'];
			$id		= $fetchedRow['be_id'];
			$title	= $fetchedRow['be_name'];
			$authorId		= $fetchedRow['be_regist_user_id'];		// コンテンツ登録者
			$authorName		= $fetchedRow['lu_name'];				// コンテンツ登録者名
			$authorUrl		= '';			// コンテンツ登録者URL
			$date			= $fetchedRow['be_regist_dt'];
			$contentHtml	= $fetchedRow['be_html'];
			$thumbSrc		= $fetchedRow['be_thumb_src'];	// サムネールの元のファイル(リソースディレクトリからの相対パス)
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
		
		$wpPostObj = new WP_Post($post);
		$this->contentArray[] = $wpPostObj;
		return true;
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
		global $gInstanceManager;
		global $gPageManager;
		global $gEnvManager;
		
		// ### コンテンツ内容のチェック ###
		// Googleマップが含まれている場合はコンテンツ情報として登録->Googleマップライブラリの読み込み指示
		$pos = strpos($src, self::DETECT_GOOGLEMAPS);
		if ($pos !== false) $gPageManager->setIsContentGooglemaps(true);
		
		// URLを求める
		$rootUrl = $gEnvManager->getRootUrlByCurrentPage();
//		$widgetUrl = str_replace($gEnvManager->getRootUrl(), $rootUrl, $gEnvManager->getCurrentWidgetRootUrl());
		
		// パスを変換
		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $src);// アプリケーションルートを変換
//		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_URL . M3_TAG_END, $widgetUrl, $dest);// ウィジェットルートを変換
		
		// コンテンツマクロ変換
		$dest = $gInstanceManager->getTextConvManager()->convContentMacro($dest, $convBr/*改行コードをbrタグに変換*/, $contentInfo, true/*変換後の値をHTMLエスケープ処理*/);
		
		// ウィジェット埋め込みタグを変換
		$gInstanceManager->getTextConvManager()->convWidgetTag($dest, $dest);
		
		// 残っているMagic3タグ削除
		$dest = $gInstanceManager->getTextConvManager()->deleteM3Tag($dest);
		return $dest;
	}
	/**
	 * デフォルトのサムネール画像の情報を取得
	 *
	 * @return array						画像パス,画像URL,画像幅,画像高さの配列
	 */
	function getDefaultThumbInfo()
	{
		global $gInstanceManager;
		static $thumbInfoArray;
		
		if (!isset($thumbInfoArray)){
			// アイキャッチ画像の情報を取得
			$formats = $gInstanceManager->getImageManager()->getSystemThumbFormat(10/*アイキャッチ画像*/);
			$ret = $gInstanceManager->getImageManager()->parseImageFormat($formats[0], $imageType, $imageAttr, $imageSize);
		
			$filename = $gInstanceManager->getImageManager()->getThumbFilename(0, $formats[0]);		// デフォルト画像ファイル名
			$thumbPath = $gInstanceManager->getImageManager()->getSystemThumbPath($this->contentType, 0/*PC用*/, $filename);
			if (file_exists($thumbPath)){
				$thumbUrl = $gInstanceManager->getImageManager()->getSystemThumbUrl($this->contentType, 0/*PC用*/, $filename);
				$thumbInfoArray = array($thumbPath, $thumbUrl, $imageSize, $imageSize);
			} else {
				$msgDetail = '画像パス:' . $thumbPath;
				$gOpeLogManager->writeError(__METHOD__, 'アイキャッチ用のデフォルト画像が見つかりません。(コンテンツタイプ=' . $this->contentType . ')', 2200, $msgDetail);
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
		global $gInstanceManager;
		global $gEnvManager;
		
		$linkInfoObj = $gInstanceManager->getObject(self::LINKINFO_OBJ_ID);
		$url = $linkInfoObj->getContentUrl($gEnvManager->getAccessDir()/*アクセスポイント*/, $this->contentType, $id, $this->langId);
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
	 * コンテンツIDを設定
	 *
	 * @param string $idStr			コンテンツID文字列
	 * @return bool					true=正常終了、false=異常終了
	 */
	function setContentId($idStr)
	{
		global $gOpeLogManager;
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
}
?>
