<?php
/**
 * コンテンツAPIマネージャー
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
//require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

//class ContentApiManager extends Core
class ContentApi
{
	private $contentType;			// コンテンツタイプ
	private $langId;				// コンテンツの言語(コンテンツ取得用)
	private $limit;					// コンテンツ取得数(コンテンツ取得用)
	private $pageNo;				// ページ番号(1～)(コンテンツ取得用)
	private $contentArray;			// 取得コンテンツ
	
	const CF_DEFAULT_CONTENT_TYPE = 'default_content_type';		// デフォルトコンテンツタイプ取得用
	const DEFAULT_CONTENT_TYPE = 'blog';		// デフォルトコンテンツタイプのデフォルト値
	const DETECT_GOOGLEMAPS = 'Magic3 googlemaps v';		// Googleマップ検出用文字列
	// アドオンオブジェクト作成用
	const ADDON_OBJ_ID_CONTENT	= 'contentlib';
	const ADDON_OBJ_ID_BLOG		= 'bloglib';
	const ADDON_OBJ_ID_PRODUCT	= 'eclib';

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
		
//		// 親クラスを呼び出す
//		parent::__construct();
		
		// コンテンツタイプを取得
		$this->contentType = $gSystemManager->getSystemConfig(self::CF_DEFAULT_CONTENT_TYPE);// デフォルトコンテンツタイプ
		if (empty($this->contentType)) $this->contentType = self::DEFAULT_CONTENT_TYPE;
			
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
//				$this->db->getContentList($contentType, $this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, 0/*降順*/, array($this, 'contentLoop'));
				break;
			case M3_VIEW_TYPE_PRODUCT:	// 製品
				$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_PRODUCT);
//				$this->db->getProductList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
				break;
			case M3_VIEW_TYPE_BBS:	// BBS
				break;
			case M3_VIEW_TYPE_BLOG:	// ブログ
				$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_BLOG);
//				$this->db->getEntryList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
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
	 * @param int   $limit			取得する項目数最大値
	 * @param int	$pageNo			取得するページ番号(1～)
	 * @return						なし
	 */
	public function setCondition($params, $langId, $limit, $pageNo)
	{
		global $gEnvManager;
			
		if (empty($langId)) $langId = $gEnvManager->getCurrentLanguage();
		$this->langId = $langId;				// コンテンツの言語(コンテンツ取得用)
		$this->limit = $limit;					// コンテンツ取得数(コンテンツ取得用)
		$this->pageNo = $pageNo;				// ページ番号(コンテンツ取得用)
		$this->now = date("Y/m/d H:i:s");	// 現在日時
	}
	/**
	 * [WordPressテンプレート用API]コンテンツを取得
	 *
	 * @return array     				WP_Postオブジェクトの配列
	 */
	function getContent()
	{
//echo '###getContent()-start ';
		$entryId = 0;
		$addonObj = $this->_getAddonObj();
//		$idArray = $addonObj->getPublicContent($this->langId, $this->limit, $this->pageNo);
		$this->contentArray = array();			// 取得コンテンツ
		$addonObj->getPublicEntryItems($this->limit, $this->pageNo, $this->now, $entryId, $startDt, $endDt, $keywords, $this->langId, $order, array($this, '_itemListLoop'), null/*ブログID*/);
		
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
//echo 'inloop....';
		// レコード値取得
		$serial = $fetchedRow['be_serial'];
		$id		= $fetchedRow['be_id'];
		$title	= $fetchedRow['be_name'];
		$entryHtml = $fetchedRow['be_html'];
//echo $id.'*';
		// カテゴリーを取得
		$categoryArray = array();
//		$ret = self::$_mainDb->getEntryBySerial($serial, $row, $categoryRow);
		if ($ret){
			for ($i = 0; $i < count($categoryRow); $i++){
				if (function_exists('mb_strimwidth')){
					$categoryArray[] = mb_strimwidth($categoryRow[$i]['bc_name'], 0, self::CATEGORY_NAME_SIZE, '…');
				} else {
					$categoryArray[] = substr($categoryRow[$i]['bc_name'], 0, self::CATEGORY_NAME_SIZE) . '...';
				}
			}
		}
		$category = implode(',', $categoryArray);
		
		// 公開状態
		switch ($fetchedRow['be_status']){
			case 1:	$status = '<font color="orange">編集中</font>';	break;
			case 2:	$status = '<font color="green">公開</font>';	break;
			case 3:	$status = '非公開';	break;
		}
		// 参照数
//		$updateViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, $serial);		// 更新後からの参照数
//		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(blog_mainCommonDef::VIEW_CONTENT_TYPE, 0, $id);		// 新規作成からの参照数
//		$viewCountStr = $updateViewCount;
//		if ($totalViewCount > $updateViewCount) $viewCountStr .= '(' . $totalViewCount . ')';		// 新規作成からの参照数がない旧仕様に対応
		
		// ユーザからの参照状況
		$now = date("Y/m/d H:i:s");	// 現在日時
		$startDt = $fetchedRow['be_active_start_dt'];
		$endDt = $fetchedRow['be_active_end_dt'];
		
//		$isActive = false;		// 公開状態
//		if ($fetchedRow['be_status'] == 2) $isActive = $this->_isActive($startDt, $endDt, $now);// 表示可能
		
/*		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
			if ($isActive){		// コンテンツが公開状態のとき
				$iconUrl = $this->gEnv->getRootUrl() . self::SMALL_ACTIVE_ICON_FILE;			// 公開中アイコン
				$iconTitle = '公開中';
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::SMALL_INACTIVE_ICON_FILE;		// 非公開アイコン
				$iconTitle = '非公開';
			}
			$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::SMALL_ICON_SIZE . '" height="' . self::SMALL_ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		} else {
			if ($isActive){		// コンテンツが公開状態のとき
				$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
				$iconTitle = '公開中';
			} else {
				$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
				$iconTitle = '非公開';
			}
			$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}*/
		
		// アイキャッチ画像
//		$iconUrl = blog_mainCommonDef::getEyecatchImageUrl($fetchedRow['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE], self::$_configArray[blog_mainCommonDef::CF_THUMB_TYPE], 's'/*sサイズ画像*/) . '?' . date('YmdHis');
		if (empty($fetchedRow['be_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
//		$eyecatchImageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// 投稿日時
//		$outputDate = $fetchedRow['be_regist_dt'];
//		if ($this->_isSmallDeviceOptimize){			// 小画面デバイス最適化の場合
//			if (intval(date('Y', strtotime($outputDate))) == $this->currentYear){		// 年号が今日の年号のとき
//				$dispDate = $this->convertToDispDate($outputDate, 11/*年省略,0なし年月*/) . '<br />' . $this->convertToDispTime($outputDate, 1/*時分*/);
//			} else {
//				$dispDate = $this->convertToDispDate($outputDate, 3/*短縮年,0なし年月*/) . '<br />' . $this->convertToDispTime($outputDate, 1/*時分*/);
//			}
//		} else {
//			$dispDate = $this->convertToDispDateTime($outputDate, 0/*ロングフォーマット*/, 10/*時分*/);
//		}
		
		$row = array(
			'index' => $index,		// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $serial,			// シリアル番号
//			'id' => $this->convertToDispString($id),			// 記事ID
//			'name' => $this->convertToDispString($fetchedRow['be_name']),		// 名前
			'lang' => $lang,													// 対応言語
			'eyecatch_image' => $eyecatchImageTag,									// アイキャッチ画像
			'status_img' => $statusImg,												// 公開状態
			'status' => $status,													// 公開状況
			'category' => $category,											// 記事カテゴリー
			//'view_count' => $totalViewCount,									// 参照数
//			'view_count' => $this->convertToDispString($viewCountStr),			// 参照数
//			'reg_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 投稿者
//			'reg_date' => $this->convertToDispDateTime($fetchedRow['be_regist_dt'], 0/*ロングフォーマット*/, 10/*時分*/)		// 投稿日時
			'reg_date' => $dispDate
		);
		// コンテンツマクロ変換
		$entryHtml = $this->_convertM3ToHtml($entryHtml, true/*改行コーをbrタグに変換*/);
		
//		$this->tmpl->addVars('itemlist', $row);
//		$this->tmpl->parseTemplate('itemlist', 'a');
		$post = new stdClass;
		$post->ID = $id;
		$post->post_author = '';
		$post->post_date = $fetchedRow['be_regist_dt'];
		$post->post_date_gmt = '';
		$post->post_password = '';
		$post->post_name = $title;		// エンコーディングが必要?
		$post->post_type = $post_type;
//		$post->post_status = 'draft';	// デフォルトはpublish
		$post->to_ping = '';
		$post->pinged = '';
/*		$post->comment_status = get_default_comment_status( $post_type );
		$post->ping_status = get_default_comment_status( $post_type, 'pingback' );
		$post->post_pingback = get_option( 'default_pingback_flag' );
		$post->post_category = get_option( 'default_category' );*/
//		$post->page_template = 'default';
		$post->post_parent = 0;
		$post->menu_order = 0;
		// Magic3設定値追加
		$post->post_title = $title;
		$post->post_content = $entryHtml;
		// Magic3用パラメータ
		$post->thumb_src = $fetchedRow['be_thumb_src'];	// サムネールの元のファイル(リソースディレクトリからの相対パス)
		
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
}
?>
