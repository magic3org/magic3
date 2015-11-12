<?php
/**
 * index.php用共通定義クラス
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
 
class blog_mainCommonDef
{
	static $_deviceType = 0;	// デバイスタイプ
	
	// ##### 定義値 #####
	const VIEW_CONTENT_TYPE = 'bg';				// 記事参照数取得用
	const USER_ID_SEPARATOR = ',';				// ユーザID区切り用セパレータ
	const ATTACH_FILE_DIR = '/etc/blog';		// 添付ファイル格納ディレクトリ
	const DOWNLOAD_CONTENT_TYPE = '-file';		// ダウンロードするコンテンツのタイプ
	
	// ##### DB定義値 #####
	const CF_ENTRY_VIEW_COUNT		= 'entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'entry_view_order';			// 記事表示方向
	const CF_ENTRY_DEFAULT_IMAGE	= 'entry_default_image';		// 記事デフォルト画像
	const CF_CATEGORY_COUNT			= 'category_count';				// カテゴリ数
	const CF_RECEIVE_COMMENT		= 'receive_comment';		// コメントを受け付けるかどうか
	const CF_RECEIVE_TRACKBACK		= 'receive_trackback';		// トラックバックを受け付けるかどうか
	const CF_MAX_COMMENT_LENGTH		= 'comment_max_length';		// コメント最大文字数
	const CF_COMMENT_USER_LIMITED	= 'comment_user_limited';		// コメントのユーザ制限
	const CF_USE_MULTI_BLOG			= 'use_multi_blog';		// マルチブログ機能を使用するかどうか
	const CF_MULTI_BLOG_TOP_CONTENT	= 'multi_blog_top_content';		// マルチブログ時のトップコンテンツ
	const CF_READMORE_LABEL			= 'readmore_label';				//「もっと読む」ボタンラベル
	const CF_ENTRY_LIST_DISP_TYPE	= 'entry_list_disp_type';		// 一覧の表示タイプ
	const CF_SHOW_ENTRY_LIST_IMAGE	= 'show_entry_list_image';		// 記事一覧に画像を表示するかどうか
	const CF_ENTRY_LIST_IMAGE_TYPE	= 'entry_list_image_type';		// 一覧の画像タイプ
	const CF_OUTPUT_HEAD			= 'output_head';				// ヘッダ出力するかどうか
	const CF_HEAD_VIEW_DETAIL		= 'head_view_detail';			// ヘッダ出力(詳細表示)
	const CF_LAYOUT_ENTRY_SINGLE	= 'layout_entry_single';		// コンテンツレイアウト(記事詳細)
	const CF_LAYOUT_ENTRY_LIST		= 'layout_entry_list';			// コンテンツレイアウト(記事一覧)
	const CF_LAYOUT_COMMENT_LIST	= 'layout_comment_list';			// コンテンツレイアウト(コメント一覧)
	const CF_USE_WIDGET_TITLE		= 'use_widget_title';		// ウィジェットタイトルを使用するかどうか(廃止予定)
	const CF_TITLE_DEFAULT			= 'title_default';		// デフォルトタイトル
	const CF_TITLE_LIST				= 'title_list';		// 一覧タイトル
	const CF_TITLE_SEARCH_LIST		= 'title_search_list';		// 検索結果タイトル
	const CF_TITLE_NO_ENTRY			= 'title_no_entry';		// 記事なし時タイトル
	const CF_MESSAGE_NO_ENTRY		= 'msg_no_entry';		// ブログ記事が登録されていないメッセージ
	const CF_MESSAGE_FIND_NO_ENTRY	= 'msg_find_no_entry';		// ブログ記事が見つからないメッセージ
//	const CF_TITLE_TAG_LEVEL		= 'title_tag_level';		// タイトルのタグレベル
	const CF_THUMB_TYPE				= 'thumb_type';				// サムネールタイプ
	const CF_SHOW_PREV_NEXT_ENTRY_LINK	= 'show_prev_next_entry_link';	// 前後記事リンクを表示するかどうか
	const CF_PREV_NEXT_ENTRY_LINK_POS	= 'prev_next_entry_link_pos';	// 前後記事リンク表示位置
	const CF_SHOW_ENTRY_AUTHOR		= 'show_entry_author';				// 投稿者を表示するかどうか
	const CF_SHOW_ENTRY_REGIST_DT	= 'show_entry_regist_dt';			// 投稿日時を表示するかどうか
	const CF_SHOW_ENTRY_VIEW_COUNT	= 'show_entry_view_count';				// 閲覧数を表示するかどうか
	
	// ##### デフォルト値 #####
	const DEFAULT_VIEW_COUNT	= 10;				// デフォルトの表示記事数
	const DEFAULT_COMMENT_LENGTH	= 300;				// デフォルトのコメント最大文字数
	const DEFAULT_CATEGORY_COUNT	= 2;				// デフォルトのカテゴリー数
	const DEFAULT_LAYOUT_ENTRY_SINGLE = '<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]';	// デフォルトのコンテンツレイアウト(記事詳細)
	const DEFAULT_LAYOUT_ENTRY_LIST = '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]';	// デフォルトのコンテンツレイアウト(記事一覧)
	const DEFAULT_LAYOUT_COMMENT_LIST = '[#AVATAR#]<dl><dt>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>';	// デフォルトのコンテンツレイアウト(コメント一覧)
	const DEFAULT_HEAD_VIEW_DETAIL = '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />';	// デフォルトのヘッダ出力(詳細表示)
//	const DEFAULT_TITLE_DEFAULT 		= 'ブログ新規';		// ブログタイトルのデフォルト値
	const DEFAULT_TITLE_LIST 			= '「$1」の記事';		// 一覧タイトルのデフォルト値
	const DEFAULT_TITLE_SEARCH_LIST 	= 'ブログ検索';		// 検索結果タイトルのデフォルト値
	const DEFAULT_TITLE_NO_ENTRY		= 'ブログ記事未登録';
	const DEFAULT_MESSAGE_NO_ENTRY		= 'ブログ記事は登録されていません';				// ブログ記事が登録されていないメッセージ
	const DEFAULT_MESSAGE_FIND_NO_ENTRY	= 'ブログ記事が見つかりません';					// ブログ記事が見つからないメッセージ
//	const DEFAULT_TITLE_TAG_LEVEL		= 2;		// デフォルトのタイトルタグレベル
	const DEFAULT_ENTRY_LIST_IMAGE_TYPE	= '80c.jpg';				// 画像タイプデフォルト
	
	/**
	 * ブログ定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// ブログ定義値を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['bg_id'];
				$value = $rows[$i]['bg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * 添付ファイル格納ディレクトリ取得
	 *
	 * @return string		ディレクトリパス
	 */
	static function getAttachFileDir()
	{
		global $gEnvManager;
		$dir = $gEnvManager->getIncludePath() . self::ATTACH_FILE_DIR;
		if (!file_exists($dir)) mkdir($dir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		return $dir;
	}
	/**
	 * レイアウトからユーザ定義フィールドを取得
	 *
	 * @param string,array $src		変換するデータ
	 * @return array				フィールドID
	 */
	static function parseUserMacro($src)
	{
		global $gInstanceManager;
		static $fields;
		
		if (!isset($fields)) $fields = $gInstanceManager->getTextConvManager()->parseUserMacro($src);
		return $fields;
	}
	/**
	 * アイキャッチ用画像のURLを取得
	 *
	 * @param string $filenames				作成済みファイル名(「;」区切り)
	 * @param string $defaultFilenames		作成済みデフォルトファイル名(「;」区切り)
	 * @param string $thumbTypeDef			サムネール画像タイプ定義(タイプ指定の場合)
	 * @param string $thumbType				サムネール画像タイプ(s,m,l)(タイプ指定の場合)
	 * @return string						画像URL
	 */
	static function getEyecatchImageUrl($filenames, $defaultFilenames, $thumbTypeDef = '', $thumbType = '')
	{
		global $gInstanceManager;
		static $thumbTypeArray;
		
		$thumbUrl = '';
		if (empty($filenames)) $filenames = $defaultFilenames;		// 記事デフォルト画像
		if (!empty($filenames)){
			$thumbFilename = $gInstanceManager->getImageManager()->getSystemThumbFilenameByType($filenames, $thumbTypeDef, $thumbType);
			if (!empty($thumbFilename)) $thumbUrl = $gInstanceManager->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_BLOG, self::$_deviceType, $thumbFilename);
		}
		return $thumbUrl;
	}
	
	/**
	 * アイキャッチ用画像を公開ディレクトリにコピー
	 *
	 * @param string    $entryId		記事ID
	 * @return bool						true=成功、false=失敗
	 */
	static function copyEyecatchImageToPublicDir($entryId)
	{
		global $gInstanceManager;
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $gInstanceManager->getImageManager()->getSystemThumbFilename($entryId, 1/*クロップ画像のみ*/);
		
		// 画像を公開ディレクトリにコピー
		$privateThumbDir = $gInstanceManager->getImageManager()->getSystemPrivateThumbPath(M3_VIEW_TYPE_BLOG, self::$_deviceType);
		$publicThumbDir = $gInstanceManager->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_BLOG, self::$_deviceType);
		$ret = cpFileToDir($privateThumbDir, $filenames, $publicThumbDir);
		return $ret;
	}
	/**
	 * 公開ディレクトリのアイキャッチ用画像を削除
	 *
	 * @param string    $entryId		記事ID
	 * @return bool						true=成功、false=失敗
	 */
	static function removeEyecatchImageInPublicDir($entryId)
	{
		global $gInstanceManager;
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $gInstanceManager->getImageManager()->getSystemThumbFilename($entryId, 1/*クロップ画像のみ*/);
		
		// 公開ディレクトリ内の画像を削除
		$publicThumbDir = $gInstanceManager->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_BLOG, self::$_deviceType);
		for ($i = 0; $i < count($filenames); $i++){
			$publicThumbPath = $publicThumbDir . DIRECTORY_SEPARATOR . $filenames[$i];
			if (file_exists($publicThumbPath)) @unlink($publicThumbPath);
		}
		return true;
	}
	/**
	 * 公開,非公開ディレクトリのアイキャッチ用画像を削除
	 *
	 * @param string    $entryId		記事ID
	 * @return bool						true=成功、false=失敗
	 */
	static function removeEyecatchImage($entryId)
	{
		global $gInstanceManager;
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $gInstanceManager->getImageManager()->getSystemThumbFilename($entryId, 1/*クロップ画像のみ*/);

		// 公開ディレクトリ、非公開ディレクトリの画像を削除
		$publicThumbDir = $gInstanceManager->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_BLOG, self::$_deviceType);
		$privateThumbDir = $gInstanceManager->getImageManager()->getSystemPrivateThumbPath(M3_VIEW_TYPE_BLOG, self::$_deviceType);
		for ($i = 0; $i < count($filenames); $i++){
			$publicThumbPath = $publicThumbDir . DIRECTORY_SEPARATOR . $filenames[$i];
			$privateThumbPath = $privateThumbDir . DIRECTORY_SEPARATOR . $filenames[$i];
			if (file_exists($publicThumbPath)) @unlink($publicThumbPath);
			if (file_exists($privateThumbPath)) @unlink($privateThumbPath);
		}
		return true;
	}
}
?>
