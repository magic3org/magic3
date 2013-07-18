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
 * @version    SVN: $Id: commentCommonDef.php 6144 2013-06-29 13:47:32Z fishbone $
 * @link       http://www.magic3.org
 */
 
class commentCommonDef
{
	static $_deviceType = 0;	// デバイスタイプ(PC)
	static $_viewContentType = 'comment';		// 添付ファイル管理用コンテンツタイプ
	
	// デフォルト値
	const DF_VIEW_COUNT		= 100;		// 表示コメント数
	const DF_VIEW_DIRECTION	= 0;		// 昇順
	const DF_MAX_LENGTH		= 300;		// コメント文字数
	const DF_MAX_IMAGE_SIZE	= 200;		// 画像最大サイズ
	
	// DBフィールド名
	const FD_VIEW_TYPE		= 'cf_view_type';			// コメントタイプ(0=フラット,1=ツリー)
	const FD_VIEW_DIRECTION = 'cf_view_direction';			// 表示方向(0=昇順、1=降順)
	const FD_MAX_COUNT		= 'cf_max_count';			// コメント最大数
	const FD_MAX_LENGTH		= 'cf_max_length';			// コメント文字数
	const FD_MAX_IMAGE_SIZE = 'cf_image_max_size';		// 画像の最大サイズ(縦横)
	const FD_VISIBLE		= 'cf_visible';			// 表示可否(個別設定可)
	const FD_VISIBLE_D		= 'cf_visible_d';			// 表示可否デフォルト値
	const FD_ACCEPT_POST	= 'cf_accept_post';			// コメントの受付(個別設定可)
	const FD_ACCEPT_POST_D	= 'cf_accept_post_d';			// コメントの受付デフォルト値
	const FD_START_DT		= 'cf_start_dt';			// 使用期間(開始)(個別設定可)
	const FD_END_DT			= 'cf_end_dt';			// 使用期間(終了)(個別設定可)
	const FD_USER_LIMITED	= 'cf_user_limited';			// 投稿ユーザを制限
	const FD_NEED_AUTHORIZE	= 'cf_need_authorize';		// 認証が必要かどうか
	const FD_PERMIT_HTML	= 'cf_permit_html';			// HTMLメッセージ
	const FD_PERMIT_IMAGE	= 'cf_permit_image';			// 画像あり
	const FD_AUTOLINK		= 'cf_autolink';			// 自動リンク
	const FD_USE_TITLE		= 'cf_use_title';			// タイトルあり
	const FD_USE_AUTHOR		= 'cf_use_author';			// 投稿者名あり
	const FD_USE_DATE		= 'cf_use_date';			// 投稿日時あり
	const FD_USE_EMAIL		= 'cf_use_email';			// eメールあり
	const FD_USE_URL		= 'cf_use_url';				// URLあり
	const FD_USE_AVATAR		= 'cf_use_avatar';			// アバターあり
	
	const DOWNLOAD_TYPE_IMAGE 		= '-image';				// ダウンロードするコンテンツのタイプ
	const REQUEST_PARAM_IMAGE_ID	= 'imageid';		// 画像識別ID
	const UPLOAD_IMAGE_DIR 			= '/etc/comment';		// アップロード画像格納ディレクトリ
	const OUTPUT_IMAGE_TYPE 		= IMAGETYPE_JPEG;		// 出力画像フォーマット
	const COMMENT_PERMA_HEAD		= 'comment-';		// コメントパーマリンク
	
	/**
	 * コメントへのリンク作成
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentsId		共通コンテンツID
	 * @param int $commentNo			コメント番号(0の場合はコンテンツへのリンク)
	 * @param string					URL(エラーの場合は空文字列)
	 */
	static function createCommentUrl($contentType, $contentsId, $commentNo = 0)
	{
		global $gEnvManager;
		$url = '';
		
		switch (self::$_deviceType){		// デバイスごとの処理
			case 0:		// PC
			default:
				$url = $gEnvManager->getDefaultPcUrl();
				break;
			case 1:		// 携帯
				$url = $gEnvManager->getDefaultMobileUrl();
				break;
			case 2:		// スマートフォン
				$url = $gEnvManager->getDefaultSmartphoneUrl();
				break;
		}
		$contentParam = self::createContentParam($contentType, $contentsId);
		if (empty($contentParam)) return '';
		$url .= '?' . $contentParam;

		if (!empty($commentNo)) $url .= '#' . commentCommonDef::COMMENT_PERMA_HEAD . $commentNo;		// コメントパーマリンク
		return $url;
	}
	/**
	 * コンテンツパラメータを作成
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentsId		共通コンテンツID
	 * @param string					パラメータ文字列
	 */
	static function createContentParam($contentType, $contentsId)
	{
		$param = '';
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$param = M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentsId;
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$param = M3_REQUEST_PARAM_PRODUCT_ID . '=' . $contentsId;
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				$param = M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $contentsId;
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$param = M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $contentsId;
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$param = $contentsId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$param = M3_REQUEST_PARAM_ROOM_ID . '=' . $contentsId;
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$param = M3_REQUEST_PARAM_EVENT_ID . '=' . $contentsId;
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$param = M3_REQUEST_PARAM_PHOTO_ID . '=' . $contentsId;
				break;
		}
		return $param;
	}
	/**
	 * アップロード画像格納ディレクトリ取得
	 *
	 * @return string		ディレクトリパス
	 */
	static function getUploadImageDir()
	{
		global $gEnvManager;
		$dir = $gEnvManager->getIncludePath() . self::UPLOAD_IMAGE_DIR;
		if (!file_exists($dir)) mkdir($dir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		return $dir;
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
