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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class blog_mainCommonDef
{
	static $_deviceType = 0;	// デバイスタイプ
	
	// DB定義値
	const CF_RECEIVE_COMMENT		= 'receive_comment';		// コメントを受け付けるかどうか
	const CF_RECEIVE_TRACKBACK		= 'receive_trackback';		// トラックバックを受け付けるかどうか
	const CF_ENTRY_VIEW_COUNT		= 'entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'entry_view_order';			// 記事表示方向
	const CF_ENTRY_DEFAULT_IMAGE	= 'entry_default_image';		// 記事デフォルト画像
	const CF_MAX_COMMENT_LENGTH		= 'comment_max_length';		// コメント最大文字数
	const CF_COMMENT_USER_LIMITED	= 'comment_user_limited';		// コメントのユーザ制限
	const CF_USE_MULTI_BLOG			= 'use_multi_blog';		// マルチブログ機能を使用するかどうか
	const CF_MULTI_BLOG_TOP_CONTENT	= 'multi_blog_top_content';		// マルチブログ時のトップコンテンツ
	const CF_CATEGORY_COUNT			= 'category_count';		// カテゴリ数
	const CF_OUTPUT_HEAD			= 'output_head';		// ヘッダ出力するかどうか
	const CF_HEAD_VIEW_DETAIL		= 'head_view_detail';		// ヘッダ出力(詳細表示)
	const CF_LAYOUT_ENTRY_SINGLE	= 'layout_entry_single';			// コンテンツレイアウト(記事詳細)
	const CF_LAYOUT_ENTRY_LIST		= 'layout_entry_list';			// コンテンツレイアウト(記事一覧)
	const CF_LAYOUT_COMMENT_LIST	= 'layout_comment_list';			// コンテンツレイアウト(コメント一覧)
	const CF_TITLE_SEARCH_LIST		= 'title_search_list';		// 検索結果タイトル
	const CF_TITLE_NO_ENTRY			= 'title_no_entry';		// 記事なし時タイトル
	const CF_MESSAGE_NO_ENTRY			= 'message_no_entry';		// ブログ記事が登録されていないメッセージ
	const CF_MESSAGE_FIND_NO_ENTRY		= 'message_find_no_entry';		// ブログ記事が見つからないメッセージ
	
	const USER_ID_SEPARATOR = ',';			// ユーザID区切り用セパレータ
	const ATTACH_FILE_DIR = '/etc/blog';				// 添付ファイル格納ディレクトリ
	const DOWNLOAD_CONTENT_TYPE = '-file';				// ダウンロードするコンテンツのタイプ
	const DEFAULT_LAYOUT_ENTRY_SINGLE = '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#LINKS#]';	// デフォルトのコンテンツレイアウト(記事詳細)
	const DEFAULT_LAYOUT_ENTRY_LIST = '[#TITLE#]<small>[#CT_AUTHOR#] [#CT_DATE#] [#CT_TIME#] [#BLOG_LINK#]</small>[#BODY#][#CATEGORY#][#COMMENT_LINK#]';	// デフォルトのコンテンツレイアウト(記事一覧)
	const DEFAULT_LAYOUT_COMMENT_LIST = '[#AVATAR#]<dl><db>[#TITLE#] <small>[#CM_AUTHOR#] [#CM_DATE#] [#CM_TIME#] [#URL#]</small></dt><dd>[#BODY#]</dd></dl>';	// デフォルトのコンテンツレイアウト(コメント一覧)
	const DEFAULT_HEAD_VIEW_DETAIL = '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />';	// デフォルトのヘッダ出力(詳細表示)
	const DEFAULT_TITLE_SEARCH_LIST 	= 'ブログ検索';		// 検索結果タイトルのデフォルト値
	const DEFAULT_TITLE_NO_ENTRY		= 'ブログ記事未登録';
	const DEFAULT_MESSAGE_NO_ENTRY		= 'ブログ記事は登録されていません';				// ブログ記事が登録されていないメッセージ
	const DEFAULT_MESSAGE_FIND_NO_ENTRY	= 'ブログ記事が見つかりません';					// ブログ記事が見つからないメッセージ
	
	/**
	 * フォトギャラリー定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
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
		$fields = array();
		$pattern = '/' . preg_quote(M3_TAG_START . M3_TAG_MACRO_USER_KEY) . '([A-Z0-9_]+):?(.*?)' . preg_quote(M3_TAG_END) . '/u';
		
		if (is_array($src)){
			for ($j = 0; $j < count($src); $j++){
				preg_match_all($pattern, $src[$j], $matches, PREG_SET_ORDER);
				for ($i = 0; $i < count($matches); $i++){
					$key = M3_TAG_MACRO_USER_KEY . $matches[$i][1];
					$value = $matches[$i][2];
					if (!array_key_exists($key, $fields)) $fields[$key] = $value;
				}
			}
		} else {
			preg_match_all($pattern, $src, $matches, PREG_SET_ORDER);
			for ($i = 0; $i < count($matches); $i++){
				$key = M3_TAG_MACRO_USER_KEY . $matches[$i][1];
				$value = $matches[$i][2];
				if (!array_key_exists($key, $fields)) $fields[$key] = $value;
			}
		}
		return $fields;
	}
	/**
	 * サムネール画像を作成
	 *
	 * @param string $srcHtml		画像を検索するHTML
	 * @param int    $entryId		ブログ記事ID
	 * @param timestamp $updateDt	記事の更新日付
	 * @return string				画像URL
	 */
	static function createThumbnail($srcHtml, $entryId, $updateDt)
	{
		global $gEnvManager;
		global $gInstanceManager;
		
		// サムネール
		$thumbUrl	= $gInstanceManager->getImageManager()->getDefaultThumbUrl(M3_VIEW_TYPE_BLOG, $entryId);
		$thumbPath	= $gInstanceManager->getImageManager()->getDefaultThumbPath(M3_VIEW_TYPE_BLOG, $entryId);

		// ファイルと日時をチェック
		$createImage = true;
		if (file_exists($thumbPath) && strtotime($updateDt) < filemtime($thumbPath)){
			$createImage = false;
		}
		
		// サムネールを作成
		if ($createImage){
			// ブログ記事から最初の画像を取得
			// 読み飛ばしが指定されている場合は飛ばす
			$regex = '/<img[^<]*?src\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
			if (preg_match($regex, $srcHtml, $matches)){		// 画像が取得できたとき
				// 相対パスを取得
				$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $matches[1]);
				if (strStartsWith($imageUrl, '/')){
					$relativePath = $gEnvManager->getRelativePathToSystemRootUrl($gEnvManager->getDocumentRootUrl() . $imageUrl);
				} else {
					if ($gEnvManager->isSystemUrlAccess($imageUrl)){		// システム内のファイルのとき
						$relativePath = $gEnvManager->getRelativePathToSystemRootUrl($imageUrl);
					}
				}
				
				if (strStartsWith($relativePath, '/' . M3_DIR_NAME_RESOURCE . '/')){		// リソースディレクトリ以下のリソースのみ変換
					$imagePath = $gEnvManager->getSystemRootPath() . $relativePath;		// 元画像のファイルパス
					
					// 画像格納用のディレクトリ作成
					$destDir = dirname($thumbPath);
					if (!file_exists($destDir)) mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);

					// サムネール作成
					$ret = $gInstanceManager->getImageManager()->createDefaultThumb(M3_VIEW_TYPE_BLOG, $entryId, $imagePath);
					if (!$ret) $thumbUrl = '';
				} else {
					$thumbUrl = '';
				}
			} else {		// 画像が取得できないとき
				// サムネール画像を削除
				if (file_exists($thumbPath)) @unlink($thumbPath);
				$thumbUrl = '';
			}
		}
		return $thumbUrl;
	}
	/**
	 * サムネール画像を削除
	 *
	 * @param int    $entryId		ブログ記事ID
	 * @return bool					true=成功、false=失敗
	 */
	static function removeThumbnail($entryId)
	{
		global $gInstanceManager;
		
		$thumbPath	= $gInstanceManager->getImageManager()->getDefaultThumbPath(M3_VIEW_TYPE_BLOG, $entryId);
		if (file_exists($thumbPath)) @unlink($thumbPath);
		return true;
	}
}
?>
