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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class newsCommonDef
{
	static $_deviceType = 0;				// デバイスタイプ(PC)
	static $_viewContentType = 'news';		// コンテンツタイプ
	
	// デフォルト値
	const DF_VIEW_COUNT		= 100;		// 表示コメント数
	const DF_VIEW_DIRECTION	= 0;		// 昇順
	const DF_MAX_LENGTH		= 300;		// コメント文字数
	const DF_MAX_IMAGE_SIZE	= 200;		// 画像最大サイズ
	const DF_UPLOAD_MAX_BYTES	= 512000;		// アップロード画像最大バイトサイズ
	
	// DBフィールド名
	const FD_DEFAULT_MESSAGE	= 'default_message';		// デフォルトメッセージ
	const FD_DATE_FORMAT		= 'date_format';			// 日時フォーマット
	const FD_LAYOUT_LIST_ITEM	= 'layout_list_item';		// リスト項目レイアウト

	/**
	 * 新着情報定義値をDBから取得
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
				$key = $rows[$i]['nc_id'];
				$value = $rows[$i]['nc_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	
	
	
	

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

		if (!empty($commentNo)) $url .= '#' . newsCommonDef::COMMENT_PERMA_HEAD . $commentNo;		// コメントパーマリンク
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
