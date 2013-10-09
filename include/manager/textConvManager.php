<?php
/**
 * 各種テキスト変換マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: textConvManager.php 6089 2013-06-10 12:33:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/htmlEdit.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class TextConvManager extends Core
{
	private $db;						// DBオブジェクト
	private $rootUrl;					// ルートURL
	private $contentType;				// コンテンツタイプ
	private $contentDt;					// コンテンツ作成日時
	private $imageWidth;		// 画像幅
	private $imageHeight;		// 画像高さ
	private $convBr;			// 改行変換するかどうか
	private $contentInfo;		// コンテンツの情報
	private $htmlEscapedValue;	// 変換後の値をHTMLエスケープ処理するかどうか
	const DEFAULT_MOBILE_IMAGE_WIDTH = 240;		// 携帯用画像のデフォルトサイズ(幅)
	const DEFAULT_MOBILE_IMAGE_HEIGHT = 320;	// 携帯用画像のデフォルトサイズ(高さ)
	const DEFAULT_MOBILE_IMAGE_FILE_EXT = 'gif';		// 携帯用画像の形式
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
	}
	/**
	 * キー値テーブルの値を使用してテキストを変換
	 *
	 * @param string $src		変換するデータ
	 * @param string $dest      変換後データ
	 * @param bool $convBr      キーワード変換部分の改行コードをBRタグに変換するかどうか
	 * @return bool				true=成功、false=失敗
	 */
	function convByKeyValue($src, &$dest, $convBr=false)
	{
		// データをコピー
		$dest = $src;

		// キーワードを取得
		$keywords = array();
		$matches = array();
		$pattern = '/(' . preg_quote(M3_TAG_START) . '([A-Z0-9_]+)' . preg_quote(M3_TAG_END) . ')/u';
		preg_match_all($pattern, $src, $matches, PREG_SET_ORDER);
		for ($i = 0; $i < count($matches); $i++){
			$value = $matches[$i][2];
			if (!in_array($value, $keywords)) $keywords[] = $value;
		}
		// キーワードを変換
		for ($i = 0; $i < count($keywords); $i++){
			$key = $keywords[$i];
			$value = $this->db->getKeyValue($key, $tmp);
			if ($convBr){// 改行コード変換の場合
				//$value = HtmlEdit::convLineBreakToBr($value);
				$value = $this->convLineBreakToBr($value);
			}
			$dest = str_replace(M3_TAG_START . $key . M3_TAG_END, $value, $dest);
		}
		return true;
	}
	/**
	 * コンテンツマクロを変換
	 *
	 * 単純置換の場合、HTML文字のエスケープは呼び出し側で設定する。
	 * 個々のマクロのオプションで設定されている場合、HTML文字のエスケープを行う。マクロのデフォルトはHTML文字エスケープあり?
	 *
	 * @param string $src			変換するデータ
	 * @param bool $convBr      	キーワード変換部分の改行コードをBRタグに変換するかどうか(キーワード変換部分以外のテキストは変換しない)
	 * @param array $contentInfo	コンテンツ情報
	 * @param bool $htmlEscapedValue	変換後の値をHTMLエスケープ処理するかどうか
	 * @return string				変換後データ
	 */
	function convContentMacro($src, $convBr = false, $contentInfo = array(), $htmlEscapedValue = false)
	{
		$this->convBr = $convBr;			// 改行変換するかどうか
		$this->contentInfo	= $contentInfo;		// コンテンツの情報
		$this->htmlEscapedValue = $htmlEscapedValue;	// 変換後の値をHTMLエスケープ処理するかどうか
		
		// Magic3マクロを検索(「[#」と「#]」で区切られた文字列)
		$pattern = '/' . preg_quote(M3_TAG_START) . '([A-Z0-9_]+):?(.*?)' . preg_quote(M3_TAG_END) . '/u';
		$dest = preg_replace_callback($pattern, array($this, '_replace_content_macro_callback'), $src);
		return $dest;
	}
	/**
	 * コンテンツマクロ変換コールバック関数
	 * 変換される文字列はHTMLタグではないテキストで、変換後のテキストはHTMLタグ(改行)を含むか、HTMLエスケープしたテキスト
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_content_macro_callback($matchData)
	{
		global $gInstanceManager;
		global $gEnvManager;
		global $gPageManager;
		static $keyValues;

		$destTag	= $matchData[0];
		$typeTag	= $matchData[1];
		$format		= $matchData[2];
		if (strStartsWith($typeTag, M3_TAG_MACRO_CUSTOM_KEY)){		// キーワード置換キー
			if (!isset($keyValues)){
				$keyValues = array();
				$rows = $gInstanceManager->getSytemDbObject()->getAllKeyValueRecords();
				for ($i = 0; $i < count($rows); $i++){
					$line = $rows[$i];
					$keyValues[$line['kv_id']] = $line['kv_value'];
				}
			}
			$destTag = $keyValues[$typeTag];
		} else if (strStartsWith($typeTag, M3_TAG_MACRO_CONTENT_KEY)){		// コンテンツ置換キー
			switch ($typeTag){
				case 'CT_NOW':			// 現在日時
					if (empty($format)){
						$destTag = date(M3_VIEW_FORMAT_DATETIME);
					} else {
						$destTag = date($format);
					}
					break;
				case 'CT_CREATE_DT':			// コンテンツ作成日時
				case 'CT_UPDATE_DT':			// コンテンツ更新日時
				case 'CT_REGIST_DT':			// コンテンツ登録日時
					$value = $this->contentInfo[$typeTag];
					if (isset($value)){
						if (empty($value)){
							$destTag = $value;
						} else {
							if (empty($format)){
								$destTag = date(M3_VIEW_FORMAT_DATETIME, strtotime($value));
							} else {
								$destTag = date($format, strtotime($value));
							}
						}
					}
					break;
				case 'CT_DATE':					// コンテンツ登録日
					$value = $this->contentInfo[$typeTag];
					if (isset($value)){
						if (empty($value)){
							$destTag = $value;
						} else {
							if (empty($format)){
								$destTag = date(M3_VIEW_FORMAT_DATE, strtotime($value));
							} else {
								$destTag = date($format, strtotime($value));
							}
						}
					}
					break;
				case 'CT_TIME':					// コンテンツ登録時
					$value = $this->contentInfo[$typeTag];
					if (isset($value)){
						if (empty($value)){
							$destTag = $value;
						} else {
							if (empty($format)){
								$destTag = date(M3_VIEW_FORMAT_TIME, strtotime($value));
							} else {
								$destTag = date($format, strtotime($value));
							}
						}
					}
					break;
				case 'CT_ID':		// コンテンツID
				case 'CT_TITLE':		// コンテンツタイトル
				default:
					$value = $this->contentInfo[$typeTag];
					//if (!empty($value)) $destTag = $value;
					if (isset($value)) $destTag = $value;
					break;
			}
		} else if (strStartsWith($typeTag, M3_TAG_MACRO_COMMENT_KEY)){		// コメント置換キー
			switch ($typeTag){
				case 'CM_DATE':					// コメント登録日
					$value = $this->contentInfo[$typeTag];
					if (isset($value)){
						if (empty($value)){
							$destTag = $value;
						} else {
							if (empty($format)){
								$destTag = date(M3_VIEW_FORMAT_DATE, strtotime($value));
							} else {
								$destTag = date($format, strtotime($value));
							}
						}
					}
					break;
				case 'CM_TIME':					// コメント登録時
					$value = $this->contentInfo[$typeTag];
					if (isset($value)){
						if (empty($value)){
							$destTag = $value;
						} else {
							if (empty($format)){
								$destTag = date(M3_VIEW_FORMAT_TIME, strtotime($value));
							} else {
								$destTag = date($format, strtotime($value));
							}
						}
					}
					break;
				default:
					$value = $this->contentInfo[$typeTag];
					if (isset($value)) $destTag = $value;
					break;
			}
		} else if (strStartsWith($typeTag, M3_TAG_MACRO_SITE_KEY)){		// サイト定義置換キー
			switch ($typeTag){
				case 'SITE_NAME':			// サイト名
					$destTag = $gEnvManager->getSiteName();
					break;
				case 'SITE_URL':
					$destTag = $gEnvManager->getRootUrl() . '/';
					break;
				case 'SITE_DESCRIPTION':
					$destTag = $gPageManager->getHeadDescription();
					break;
				case 'SITE_IMAGE':			// サイトロゴ画像
					$destTag = $gInstanceManager->getImageManager()->getSiteLogoUrl();
					break;
			}
		}
		// HTMLのエスケープ処理
		if ($this->htmlEscapedValue) $destTag = convertToHtmlEntity($destTag);
		
		// 改行変換処理
		if ($this->convBr) $destTag = $this->convLineBreakToBr($destTag);			// 改行変換するかどうか
		return $destTag;
	}
	/**
	 * Magic3タグを削除
	 *
	 * @param string $src		変換するデータ
	 * @return string			変換後データ
	 */
	function deleteM3Tag($src)
	{
		$startTag = str_replace('[', '\[', M3_TAG_START);		// 「[」を正規表現用に「\[」に変換
		$endTag = str_replace(']', '\]', M3_TAG_END);		// 「[」を正規表現用に「\[」に変換
		$search = '/' . $startTag . 'M3_.*' . $endTag . '/';
		return preg_replace($search, '', $src);
	}
	/**
	 * 指定のタグを削除(タグで囲まれた領域は削除しない)
	 *
	 * @param string $src			変換するデータ
	 * @param string,array $tags	削除するタグ
	 * @return string				変換後データ
	 */
	function deleteTag($src, $tags)
	{
		$searchArray = array();
		$replaceArray = array();
		if (is_array($tags)){		// 配列のとき
			$tagArray = $tags;
		} else {		// 文字列のとき
			$tagArray = array($tags);
		}
		
		// 変換処理
		for ($i = 0;$i < count($tagArray); $i++){
			$searchArray[] = "'<" . $tagArray[$i] . "[^>]*?>'si";
			$searchArray[] = "'</" . $tagArray[$i] . "[^>]*?>'si";
			$replaceArray[] = '';
			$replaceArray[] = '';
		}
		$dest = preg_replace($searchArray, $replaceArray, $src);
		return $dest;
	}
	/**
	 * 絵文字画像をMagic3絵文字タグに変換
	 *
	 * @param string $src		変換するデータ
	 * @param string $dest      変換後データ
	 * @return bool				true=成功、false=失敗
	 */
	function convToEmojiTag($src, &$dest)
	{
		global $gEnvManager;
		
		$emojiUrl = $gEnvManager->getEmojiImagesUrl();
		$emojiUrl = str_replace('/', '\/', $emojiUrl);		// 「/」を正規表現用に「\/」に変換
		
		$str = '/<img[^<]*?src="' . $emojiUrl . '\/.*?\.gif\?code=(\d+?)"[^>]*?\/>/';
        $dest = preg_replace_callback($str, array($this, "_replace_to_emoji_tag_callback"), $src);
		return true;
	}
	/**
	 * Magic3絵文字タグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_to_emoji_tag_callback($matchData)
	{
		global $EMOJI;		// 絵文字マップ情報
		
		// 絵文字情報読み込み
		require_once(M3_SYSTEM_INCLUDE_PATH . '/data/emojiMap.php');// 絵文字マップ情報
						
		// 絵文字コードよりMagic3絵文字タグを作成
		$code = intval($matchData[1]);		// 絵文字コード
		if ($code < 0 || count($EMOJI) <= $code) return '';		// エラーチェック
		
        $code++;		// 絵文字コード(内部コードは1以上)
		$tag = M3_TAG_START . M3_TAG_MACRO_EMOJI_CODE . ':' . $code . M3_TAG_END;
		return $tag;
    }
	/**
	 * Magic3絵文字タグを絵文字画像に変換
	 *
	 * @param string $src		変換するデータ
	 * @param string $dest      変換後データ
	 * @return bool				true=成功、false=失敗
	 */
	function convFromEmojiTag($src, &$dest)
	{
		$startTag = str_replace('[', '\[', M3_TAG_START . M3_TAG_MACRO_EMOJI_CODE);		// 「[」を正規表現用に「\[」に変換
		$endTag = str_replace(']', '\]', M3_TAG_END);		// 「[」を正規表現用に「\[」に変換
		$str = '/' . $startTag . ':(\d+?)' . $endTag . '/';
        $dest = preg_replace_callback($str, array($this, "_replace_from_emoji_tag_callback"), $src);
		return true;
	}
	/**
	 * Magic3絵文字タグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_from_emoji_tag_callback($matchData)
	{
		global $gEnvManager;
		global $EMOJI;		// 絵文字マップ情報

		// 絵文字情報読み込み
		require_once(M3_SYSTEM_INCLUDE_PATH . '/data/emojiMap.php');// 絵文字マップ情報

		$emojiUrl = $gEnvManager->getEmojiImagesUrl();
				
		// 絵文字コードよりMagic3絵文字タグを作成
		// 配列のインデックスは、「絵文字コード-1」で指定
        $index = intval($matchData[1]) -1;		// 絵文字コード
		if ($index < 0 || count($EMOJI) <= $index) return '';// エラーチェック

		$emojiImageUrl = $emojiUrl . '/' . $EMOJI[$index]['filename'] . '.gif?code=' . $index;
		$tag = '<img src="' . $emojiImageUrl . '" />';
		return $tag;
    }
	/**
	 * Magic3絵文字タグを携帯端末に合わせて変換
	 *
	 * @param string $src		変換するデータ
	 * @param string $dest      変換後データ
	 * @return bool				true=成功、false=失敗
	 */
	function convEmoji($src, &$dest)
	{
		$startTag = str_replace('[', '\[', M3_TAG_START . M3_TAG_MACRO_EMOJI_CODE);		// 「[」を正規表現用に「\[」に変換
		$endTag = str_replace(']', '\]', M3_TAG_END);		// 「[」を正規表現用に「\[」に変換
		$str = '/' . $startTag . ':(\d+?)' . $endTag . '/';
        $dest = preg_replace_callback($str, array($this, "_replace_emoji_callback"), $src);
		return true;
	}
	/**
	 * Magic3絵文字タグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_emoji_callback($matchData)
	{
		global $gEnvManager;
		global $gInstanceManager;
		global $EMOJI;		// 絵文字マップ情報

		// 絵文字情報読み込み
		require_once(M3_SYSTEM_INCLUDE_PATH . '/data/emojiMap.php');// 絵文字マップ情報
		
		// 絵文字変換ライブラリ読み込み
		require_once($gEnvManager->getLibPath() . '/MobilePictogramConverter-1.2.0/MobilePictogramConverter.php');
		
		$agent = $gInstanceManager->getMobileAgent();
		$emojiUrl = $gEnvManager->getEmojiImagesUrl();
				
		// 絵文字コードよりMagic3絵文字タグを作成
		// 配列のインデックスは、「絵文字コード-1」で指定
        $index = intval($matchData[1]) -1;		// 絵文字コード
		if ($index < 0 || count($EMOJI) <= $index) return '';// エラーチェック
		
		$emojiData = pack('H*', $EMOJI[$index]['i']);		// imodeSJISデータ
		$mpc = MobilePictogramConverter::factory($emojiData, MPC_FROM_FOMA, MPC_FROM_CHARSET_SJIS);

		if ($agent->isDoCoMo()){	// ドコモ端末のとき
			$dest = $mpc->Convert(MPC_TO_FOMA, MPC_TO_OPTION_RAW);
		} else if ($agent->isEZweb()){	// au端末のとき
			$dest = $mpc->Convert(MPC_TO_EZWEB, MPC_TO_OPTION_RAW);
		} else if ($agent->isSoftBank()){	// ソフトバンク端末のとき
			$dest = $mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_RAW);
		} else {		// その他の端末のとき(PC用)
			$emojiImageUrl = $emojiUrl . '/' . $EMOJI[$index]['filename'] . '.gif?code=' . $index;
			$dest = '<img src="' . $emojiImageUrl . '" />';
		}
		return $dest;
    }
	/**
	 * PC用コンテンツを携帯用コンテンツに自動変換
	 *
	 * 以下の自動変換処理を行う
	 *  ・画像の自動縮小
	 *  ・tableタグ→divタグ
	 *
	 * @param string $src			変換するデータ
	 * @param string $rootUrl   	リソース用のルートURL
	 * @param string $contentType	コンテンツタイプ
	 * @param timestamp $contentDt	コンテンツの作成日時
	 * @return string				変換後データ
	 */
	function autoConvPcContentToMobile($src, $rootUrl, $contentType, $contentDt)
	{
		$this->rootUrl = $rootUrl;					// ルートURL
		$this->contentType = $contentType;
		$this->contentDt = $contentDt;
		$dest = $src;
		
		// ### タグの削除 ###
		// 表示しないタグを削除。データ量を減らす。
		$removeTags = array('script', 'style', 'noframes?');
		foreach ($removeTags as $value){
			$dest = preg_replace('/<'.$value.'\b[^>]*?>.*?<\/'.$value.'\b[^>]*?>/si', '', $dest);
		}
		
		// テーブルタグを除く
		$dest = $this->deleteTag($dest, 'table');
		$dest = $this->deleteTag($dest, 'tbody');
		$dest = $this->deleteTag($dest, 'thead');
		$dest = $this->deleteTag($dest, 'tfoot');
		$dest = $this->deleteTag($dest, 'tr');
		
		// ### 他のタグへの変換 ###
		// IFRAMEタグをAタグに変換
		$str = '/<iframe\b(.*?)>(.*?)<\/iframe\b[^>]*?>/si';
		$dest = preg_replace_callback($str, array($this, "_replace_iframe_callback"), $dest);
		
		// TH,TD,CAPTIONタグをDIVに変換
/*		$dest = preg_replace('/<th\b[^>]*?>/si', '<div>', $dest);
		$dest = preg_replace('/<\/th\b[^>]*?>/si', '</div>', $dest);
		$dest = preg_replace('/<td\b[^>]*?>/si', '<div>', $dest);
		$dest = preg_replace('/<\/td\b[^>]*?>/si', '</div>', $dest);*/
		$dest = preg_replace('/<(\/?)th\b[^>]*?>/si', '<\\1div>', $dest);
		$dest = preg_replace('/<(\/?)td\b[^>]*?>/si', '<\\1div>', $dest);
		$dest = preg_replace('/<(\/?)caption\b[^>]*?>/si', '<\\1div>', $dest);
		
		// UL,OL,LIタグをDIVに変換
		$dest = preg_replace('/<(\/?)(?:ul|ol|li)>/si', '<\\1div>', $dest);
		
		// DL,DT,DDタグをDIVに変換
		$dest = preg_replace('/<(\/?)(?:dl|dt|dd)>/si', '<\\1div>', $dest);
		
		// PタグをDIVに変換
		$dest = preg_replace('/<p\b[^>]*?>/si', '<div>', $dest);
		$dest = preg_replace('/<\/p\b[^>]*?>/si', '</div>', $dest);
		$dest = preg_replace('/<div>\s*<\/div>/si', '', $dest);		// 空のDIVタグ削除
		
		// BLOCKQUOTEタグの処理
		$dest = preg_replace('/<\/?blockquote>/si', '<hr />', $dest);
		
		// PREタグの処理
		$dest = preg_replace_callback('/<pre\b[^>]*?>(.*?)<\/pre\b[^>]*?>/si', create_function('$matches', 'return "<hr />".nl2br(trim($matches[1]))."<hr />";'), $dest);
		
		// 連続したタグをまとめる
		$search = array(
			"'(?:<hr\b[^>]*?>\s*){2,}'si",
			"'(?:<br\b[^>]*?>\s*){3,}'si"
		);
		$replace = array(
			"<hr />",
			"<br /><br />"
		);
		$dest = preg_replace($search, $replace, $dest);
		
		// 画像を携帯用に作成
		$str = '/<img[^<]*?src\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
		$dest = preg_replace_callback($str, array($this, "_replace_to_mobile_callback"), $dest);
		return $dest;
	}
	/**
	 * IMGタグ変換コールバック関数(携帯用)
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_to_mobile_callback($matchData)
	{
		global $gEnvManager;
		
		// 画像のパスを取得
		$relativePath = '';
		$imageFile = $matchData[1];
		$imageUrl = $matchData[1];
		if (strStartsWith($imageUrl, '/')){
			$relativePath = $gEnvManager->getRelativePathToSystemRootUrl($gEnvManager->getDocumentRootUrl() . $imageUrl);
		} else {
			if ($gEnvManager->isSystemUrlAccess($imageUrl)){		// システム内のファイルのとき
				$relativePath = $gEnvManager->getRelativePathToSystemRootUrl($imageUrl);
			}
		}
		if (empty($relativePath)){		// システム管理外の画像はそのまま出力
			$destTag = $matchData[0];
		} else {
			$resDir = '/' . M3_DIR_NAME_RESOURCE . '/';
			if (strStartsWith($relativePath, $resDir)){		// リソースディレクトリ以下のリソースのみ変換
				$imageFile = $gEnvManager->getSystemRootPath() . $relativePath;
				$destImageFilename = preg_replace('/.[^.]+$/', '', basename($relativePath)) . '.' . self::DEFAULT_MOBILE_IMAGE_FILE_EXT;	// 作成画像のファイル名
				$destImageRelativePath = dirname(substr($relativePath, strlen($resDir))) . '/' . $destImageFilename;
				$destImageFile = $gEnvManager->getResourcePath() . '/widgets/' . $this->contentType . '/' . M3_DIR_NAME_MOBILE . '/' . $destImageRelativePath;
				$destImageUrl = $gEnvManager->getResourceUrl() . '/widgets/' . $this->contentType . '/' . M3_DIR_NAME_MOBILE . '/' . $destImageRelativePath;

				// ファイルと日時をチェック
				$createImage = true;
				if (file_exists($destImageFile) && strtotime($this->contentDt) < filemtime($destImageFile)){
					$createImage = false;
				}
				
				// 画像の作成
				$isNoErr = true;
				if ($createImage){
					$imageSize = getimagesize($imageFile);
					$imageType = $this->_getImageType($imageSize['mime']);
					
					// ファイル拡張子のチェック
					if (empty($imageType)){
						$errMsg = 'ファイル形式が不明です';
						$isNoErr = false;		// エラー発生
					}
					
					if ($isNoErr){
						// 画像格納用のディレクトリ作成
						$destDir = dirname($destImageFile);
						if (!file_exists($destDir)) mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
						
						// 画像のサイズを求める
						$srcWidth = $imageSize[0];
						$srcHeight = $imageSize[1];
						$destWidth = $srcWidth;
						$destHeight = $srcHeight;
						if ($srcWidth > $srcHeight){
							if ($srcWidth > self::DEFAULT_MOBILE_IMAGE_WIDTH){
								$destWidth = self::DEFAULT_MOBILE_IMAGE_WIDTH;
								$destHeight = $srcHeight * (self::DEFAULT_MOBILE_IMAGE_WIDTH / $srcWidth);
							}
						} else {
							if ($srcHeight > self::DEFAULT_MOBILE_IMAGE_HEIGHT){
								$destWidth = $srcWidth * (self::DEFAULT_MOBILE_IMAGE_HEIGHT / $srcHeight);
								$destHeight = self::DEFAULT_MOBILE_IMAGE_HEIGHT;
							}
						}
						
						// 携帯用のため画像はすべてgifで作成
						$imageObj = $this->_createImage($imageType, $imageFile, $destWidth, $destHeight);
						$ret = $this->_outputImage(self::DEFAULT_MOBILE_IMAGE_FILE_EXT, $imageObj, $destImageFile);
						if (!$ret) $isNoErr = false;		// エラー発生
					}
				}
				if ($isNoErr){
					// 幅、高さを設定し直す
					$destTag = $matchData[0];
					$str = '/width\s*=\s*[\'"]+(.+?)[\'"]/si';
					$destTag = preg_replace($str, '', $destTag);
					$str = '/height\s*=\s*[\'"]+(.+?)[\'"]/si';
					$destTag = preg_replace($str, '', $destTag);
					$str = '/style\s*=\s*[\'"]+(.+?)[\'"]/si';		// 「style」属性を削除
					$destTag = preg_replace($str, '', $destTag);
					//$str = '/<img\s*/si';
					//$destTag = preg_replace($str, '<img height="' . $destHeight . '" ', $destTag);

					// 画像のURLを変換
					$destTag = str_replace($matchData[1], $destImageUrl, $destTag);
				} else {// エラー発生の場合は元のファイルのまま
					$destTag = $matchData[0];					
				}
			} else {
				$destTag = $matchData[0];
			}
		}
		return $destTag;
    }
	/**
	 * PC用コンテンツをスマートフォン用コンテンツに自動変換
	 *
	 * 以下の自動変換処理を行う
	 *  ・画像の自動縮小
	 *
	 * @param string $src			変換するデータ
	 * @param string $rootUrl   	リソース用のルートURL
	 * @param string $contentType	コンテンツタイプ
	 * @param timestamp $contentDt	コンテンツの作成日時
	 * @param int    $imageWidth	画像最大幅
	 * @param int    $imageHeight	画像最大高さ
	 * @return string				変換後データ
	 */
	function autoConvPcContentToSmartphone($src, $rootUrl, $contentType, $contentDt, $imageWidth, $imageHeight)
	{
		$this->rootUrl = $rootUrl;					// ルートURL
		$this->contentType = $contentType;
		$this->contentDt = $contentDt;
		$this->imageWidth = $imageWidth;		// 画像幅
		$this->imageHeight = $imageHeight;		// 画像高さ
		$dest = $src;
		
		// 画像をスマートフォン用に作成
		$str = '/<img[^<]*?src\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
		$dest = preg_replace_callback($str, array($this, "_replace_to_smartphone_callback"), $dest);
		return $dest;
	}
	/**
	 * IMGタグ変換コールバック関数(スマートフォン用)
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_to_smartphone_callback($matchData)
	{
		global $gEnvManager;
		
		// 画像のパスを取得
		$relativePath = '';
		$imageFile = $matchData[1];
		$imageUrl = $matchData[1];
		if (strStartsWith($imageUrl, '/')){
			$relativePath = $gEnvManager->getRelativePathToSystemRootUrl($gEnvManager->getDocumentRootUrl() . $imageUrl);
		} else {
			if ($gEnvManager->isSystemUrlAccess($imageUrl)){		// システム内のファイルのとき
				$relativePath = $gEnvManager->getRelativePathToSystemRootUrl($imageUrl);
			}
		}
		if (empty($relativePath)){		// システム管理外の画像はそのまま出力
			$destTag = $matchData[0];
		} else {
			$resDir = '/' . M3_DIR_NAME_RESOURCE . '/';
			if (strStartsWith($relativePath, $resDir)){		// リソースディレクトリ以下のリソースのみ変換
				$imageFile = $gEnvManager->getSystemRootPath() . $relativePath;		// 元画像のファイルパス
				$fileExt = getExtension($relativePath);
				$destImageFilename = preg_replace('/.[^.]+$/', '', basename($relativePath));
				if (!empty($fileExt)) $destImageFilename .= '.' . $fileExt;	// 作成画像のファイル名
				$destImageRelativePath = dirname(substr($relativePath, strlen($resDir))) . '/' . $destImageFilename;
				$destImageFile = $gEnvManager->getResourcePath() . '/widgets/' . $this->contentType . '/' . M3_DIR_NAME_SMARTPHONE . '/' . $destImageRelativePath;
				//$destImageUrl = $gEnvManager->getResourceUrl() . '/widgets/' . $this->contentType . '/' . M3_DIR_NAME_SMARTPHONE . '/' . $destImageRelativePath;
				$destImageUrl = $this->rootUrl . '/resource' . '/widgets/' . $this->contentType . '/' . M3_DIR_NAME_SMARTPHONE . '/' . $destImageRelativePath;

				// ファイルと日時をチェック
				$createImage = true;
				if (file_exists($destImageFile) && strtotime($this->contentDt) < filemtime($destImageFile)){
					$createImage = false;
				}
				
				// 画像の作成
				$isNoErr = true;
				if ($createImage){
					$imageSize = getimagesize($imageFile);
					$imageType = $this->_getImageType($imageSize['mime']);
					
					// ファイル拡張子のチェック
					if (empty($imageType)){
						$errMsg = 'ファイル形式が不明です';
						$isNoErr = false;		// エラー発生
					}
					
					if ($isNoErr){
						// 画像格納用のディレクトリ作成
						$destDir = dirname($destImageFile);
						if (!file_exists($destDir)) mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
						
						// 画像のサイズを求める
						$srcWidth = $imageSize[0];
						$srcHeight = $imageSize[1];
						$destWidth = $srcWidth;
						$destHeight = $srcHeight;
						if ($srcWidth > $srcHeight){
							if ($srcWidth > $this->imageWidth){
								$destWidth = $this->imageWidth;
								$destHeight = $srcHeight * ($this->imageWidth / $srcWidth);
							}
						} else {
							if ($srcHeight > $this->imageHeight){
								$destWidth = $srcWidth * ($this->imageHeight / $srcHeight);
								$destHeight = $this->imageHeight;
							}
						}

						// 画像フォーマットを維持して画像作成
						$imageObj = $this->_createImage($imageType, $imageFile, $destWidth, $destHeight);
						//$ret = $this->_outputImage(self::DEFAULT_MOBILE_IMAGE_FILE_EXT, $imageObj, $destImageFile);
						$ret = $this->_outputImage($imageType, $imageObj, $destImageFile);
						if (!$ret) $isNoErr = false;		// エラー発生
					}
				}
				if ($isNoErr){
					// 幅、高さを設定し直す
					$destTag = $matchData[0];
					$str = '/width\s*=\s*[\'"]+(.+?)[\'"]/si';
					$destTag = preg_replace($str, '', $destTag);
					$str = '/height\s*=\s*[\'"]+(.+?)[\'"]/si';
					$destTag = preg_replace($str, '', $destTag);
					$str = '/style\s*=\s*[\'"]+(.+?)[\'"]/si';		// 「style」属性を削除
					$destTag = preg_replace($str, '', $destTag);

					// 画像のURLを変換
					$destTag = str_replace($matchData[1], $destImageUrl, $destTag);
				} else {// エラー発生の場合は元のファイルのまま
					$destTag = $matchData[0];
				}
			} else {
				$destTag = $matchData[0];
			}
		}
		return $destTag;
    }
	/**
	 * 携帯用のHTMLをきれいにする
	 *
	 * 以下の自動変換処理を行う
	 *  ・HTML4仕様のタグをXHTML仕様に変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function cleanMobileTag($src)
	{
		$dest = $src;
		$dest = preg_replace('/<blink>(.*?)<\/blink>/si', '<span style="text-decoration:blink;">$1</span>', $dest);
		$dest = preg_replace('/<center>(.*?)<\/center>/si', '<div style="text-align:center;">$1</div>', $dest);
		$str = '/<font\b(.*?)>(.*?)<\/font\b[^>]*?>/si';
		$dest = preg_replace_callback($str, array($this, '_replace_font_callback'), $dest);
		
		// MARQUEEタグの変換
		$str = '/<marquee\b(.*?)>(.*?)<\/marquee\b[^>]*?>/si';
		$dest = preg_replace_callback($str, array($this, '_replace_markquee_callback'), $dest);
		return $dest;
	}
	/**
	 * IFRAMEタグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_iframe_callback($matchData)
	{
		// URL、タイトルを取得
		$url = $this->_getAttribute($matchData[1], 'src');
		$name = $this->_getAttribute($matchData[1], 'name');
		$destTag = '<a href="' . $url . '">' . $name . '</a>';
		return $destTag;
	}
	/**
	 * FONTタグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_font_callback($matchData)
	{
		// 属性をスタイル属性に直す
		$style = '';
		$str = '/color\s*=\s*[\'"]+(.+?)[\'"]/si';		// 文字色
		if (preg_match($str, $matchData[1], $matches)) $style .= 'color:' . $matches[1] . ';';
		$str = '/size\s*=\s*[\'"]+(.+?)[\'"]/si';		// 文字サイズ
		if (preg_match($str, $matchData[1], $matches)){
			if (strStartsWith($matches[1], '+')){
				$fontSize = 'larger';
			} else if (strStartsWith($matches[1], '-')){
				$fontSize = 'smaller';
			} else {
				switch ($matches[1]){
					case 1:
						$fontSize = 'xx-small';
						break;
					case 2:
						$fontSize = 'x-small';
						break;
					case 3:
						$fontSize = 'small';
						break;
					case 4:
						$fontSize = 'medium';
						break;
					case 5:
						$fontSize = 'large';
						break;
					case 6:
						$fontSize = 'x-large';
						break;
					case 7:
						$fontSize = 'xx-large';
						break;
					default:
						$fontSize = $matches[1];
						break;
				}
			}
			$style .= 'font-size:' . $fontSize . ';';
		}
		$destTag = '<span style="' . $style . '">' . $matchData[2] . '</span>';
		return $destTag;
	}
	/**
	 * MARQUEEタグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_markquee_callback($matchData)
	{
		// 属性をスタイル属性に直す
		$style = 'display:-wap-marquee;';
		$str = '/bgcolor\s*=\s*[\'"]+(.+?)[\'"]/si';		// 背景色
		if (preg_match($str, $matchData[1], $matches)) $style .= 'background-color:' . $matches[1] . ';';
		$str = '/behavior\s*=\s*[\'"]+(.+?)[\'"]/si';		// マーキースタイル
		if (preg_match($str, $matchData[1], $matches)) $style .= '-wap-marquee-style:' . $matches[1] . ';';
		$str = '/direction\s*=\s*[\'"]+(.+?)[\'"]/si';		// 表示方向
		if (preg_match($str, $matchData[1], $matches)) $style .= '-wap-marquee-dir:' . $matches[1] . ';';
		$str = '/loop\s*=\s*[\'"]+(.+?)[\'"]/si';		// 回数
		if (preg_match($str, $matchData[1], $matches)) $style .= '-wap-marquee-loop:' . $matches[1] . ';';
		
		$destTag = '<span style="' . $style . '">' . $matchData[2] . '</span>';
		return $destTag;
	}
	/**
	 * HTMLをテキストに変換
	 *
	 * @param string $src		変換するデータ
	 * @return string			変換後データ
	 */
	function htmlToText($src)
	{
		$search = array("'<script[^>]*?>.*?</script>'si",	// javascriptを削除
						"'<[\/\!]*?[^<>]*?>'si",  // htmlタグを削除
						"'([\r\n])[\s]+'",  // 空白文字を削除
						"'&(quot|#34);'i",  // htmlエンティティを置換
						"'&(amp|#38);'i",
						"'&(lt|#60);'i",
						"'&(gt|#62);'i",
						"'&(nbsp|#160);'i",
						"'&(iexcl|#161);'i",
						"'&(cent|#162);'i",
						"'&(pound|#163);'i",
						"'&(copy|#169);'i");
				//		"'&#(\d+);'e");  // phpとして評価      ##### /e modifier deprecated in PHP5.5 #####

		$replace = array("",
							"",
							"\\1",
							"\"",
							"&",
							"<",
							">",
							" ",
							chr(161),
							chr(162),
							chr(163),
							chr(169));
					//		"chr(\\1)");	// ##### /e modifier deprecated in PHP5.5 #####
					
		$destStr = preg_replace($search, $replace, $src);
		
		// ##### /e modifier deprecated in PHP5.5 #####
		$destStr = preg_replace_callback('/&#(\d+);/', create_function('$matches','return chr($matches[1]);'), $destStr);
		return $destStr;
	}
	/**
	 * 改行コードをbrタグに変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function convLineBreakToBr($src)
	{
		return preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $src);
	}
	/**
	 * 改行コードを削除
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function deleteLineBreak($src)
	{
		return preg_replace("/(\015\012)|(\015)|(\012)/", '', $src);
	}
	/**
	 * BBCodeをHTMLタグに変換
	 *
	 * @param string $src			変換するデータ
	 * @param bool $convBr      	改行コードをBRタグに変換するかどうか
	 * @return string				変換後データ
	 */
	function convBBCodeToHtml($src, $convBr = false)
	{
		// BBCode変換ライブラリ読み込み
		require_once($this->gEnv->getLibPath() . '/HTML_BBCodeParser2-0.1.0/BBCodeParser2.php');
		
		// 設定ファイル読み込み
		$config = parse_ini_file($this->gEnv->getIncludePath() . '/conf/BBCodeParser2.ini', true);
		$options = $config['HTML_BBCodeParser2'];

		$parser = new HTML_BBCodeParser2($options);
		$parser->setText($src);
		$parser->parse();
		$destHtml = $parser->getParsed();
		if ($convBr){// 改行コード変換の場合
			$destHtml = $this->convLineBreakToBr($destHtml);
		}
		return $destHtml;
	}
	/**
	 * HTMLタグから指定属性の値を取得
	 *
	 * @param string $src	HTMLタグ
	 * @param string $sttr	属性文字列
	 * @param bool $isPlainText	エンティティ文字を元のテキストの変換するかどうか
	 * @return string		属性の値
	 */
	function _getAttribute($src, $attr, $isPlainText = false)
	{
		$regex = '/'  . $attr . '\s*=\s*[\'"]+(.+?)[\'"]/si';
		if (preg_match($regex, $src, $matches)){
			if ($isPlainText){
				return convertFromHtmlEntity($matches[1]);
			} else {
				return $matches[1];
			}
		} else {
			return '';
		}
	}
	/**
	 * 画像の種別を取得
	 *
	 * @param string $mime	MIMEコンテンツタイプ
	 * @return string		画像の種別
	 */
	function _getImageType($mime)
	{
		if ($mime != ''){
			if ($mime == 'image/gif')	return 'gif';
			if ($mime == 'image/jpeg')	return 'jpeg';
			if ($mime == 'image/jpg')	return 'jpeg';
			if ($mime == 'image/pjpeg')	return 'jpeg';
			if ($mime == 'image/png')	return 'png';
		}
		return '';
	}		
	/**
	 * リサイズ画像を作成
	 *
	 * @param string $type	MIMEコンテンツタイプ
	 * @param string $path	拡張子
	 * @param int $width	幅
	 * @param int $height	高さ
	 * @return object		画像オブジェクト
	 */
	function _createImage($type, $path, $width, $height)
	{
		// 画像作成
		switch ($type){
			case "jpeg":
				$img = @imagecreatefromjpeg($path);
				break;
			case "gif":
				$img = @imagecreatefromgif($path);
				break;
			case "png":
				$img = @imagecreatefrompng($path);
				break;
			default:
				return false;
		}
		
		// size for thumbnail
		$srcWidth = imagesx($img);
		$srcHeight = imagesy($img);
/*
		if ($width > $height){
			$n_height = $height * ($size / $width);
			$n_width = $size;
		} else {
			$n_width = $width * ($size / $height);
			$n_height = $size;
		}
		
		$x = 0;
		$y = 0;
		if ($n_width < $size) $x = round(($size - $n_width) / 2);
		if ($n_height < $size) $y = round(($size - $n_height) / 2);
		*/
		// imagecreatetruecolor
		$thumb = imagecreatetruecolor($width, $height);
		
//		$bgcolor = imagecolorallocate($thumb, 255, 255, 255);
//		imagefill($thumb, 0, 0, $bgcolor);
		
		// imagecopyresized (imagecopyresampled)
		if (function_exists("imagecopyresampled")){
			if (!imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight)){
				if (!imagecopyresized($thumb, $img, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight)) return false;
			}
		} else {
			if (!imagecopyresized($thumb, $img, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight)) return false;
		}
		return $thumb;
	}
	/**
	 * 画像を出力
	 *
	 * @param string $type	MIMEコンテンツタイプ
	 * @param object $image	画像オブジェクト
	 * @param string $path	ファイル保存の場合のパス
	 * @return bool			true=成功、false=失敗
	 */
	function _outputImage($type, &$image, $path = null)
	{
		$ret = false;
		if (is_null($path)){
			switch ($type){
				case "jpeg":
					$ret = imagejpeg($image);
					break;
				case "gif":
					$ret = imagegif($image);
					break;
				case "png":
					$ret = imagepng($image);
					break;
			}
		} else {
			switch ($type){
				case "jpeg":
					$ret = imagejpeg($image, $path);
					break;
				case "gif":
					$ret = imagegif($image, $path);
					break;
				case "png":
					$ret = imagepng($image, $path);
					break;
			}
		}
		// イメージを破棄
		imagedestroy($image);
		
		return $ret;
	}
	/**
	 * ウィジェット埋め込みタグを変換
	 *
	 * @param string $src		変換するデータ
	 * @param string $dest      変換後データ
	 * @return bool				true=成功、false=失敗
	 */
	function convWidgetTag($src, &$dest)
	{
		$startTag = str_replace('[', '\[', M3_TAG_START . M3_TAG_MACRO_WIDGET);		// 「[」を正規表現用に「\[」に変換
		$endTag = str_replace(']', '\]', M3_TAG_END);		// 「]」を正規表現用に「\]」に変換
		$str = '/' . $startTag . ':(.*?)' . M3_WIDGET_ID_SEPARATOR . '(\d+?)' . $endTag . '/';
        $dest = preg_replace_callback($str, array($this, '_replace_widget_tag_callback'), $src);
		return true;
	}
	/**
	 * ウィジェット埋め込みタグ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_widget_tag_callback($matchData)
	{
		global $gPageManager;
		
		$widgetId = $matchData[1];			// ウィジェットID
		$configId = intval($matchData[2]);		// 定義ID

		// ウィジェットID、ウィジェット定義IDからウィジェット出力を取得
		$widgetOutput = $gPageManager->getWidgetOutput($widgetId, $configId);
		
		return $widgetOutput;
    }
	/**
	 * 検索用の入力をDB処理用に加工する
	 *
	 * ・絞り込み(AND)検索用のキーワード分割
	 *
	 * @param string $src		変換するデータ
	 * @return array $dest		変換後データ
	 */
	function parseSearchKeyword($src)
	{
		// 全角スペースを半角スペースに変換
		$destStr = '';
		$inQuote = false;
		for ($i = 0; $i < mb_strlen($src); $i++){
			$char = mb_substr($src , $i, 1);
	
			// 「"」で囲まれた範囲は変換しない
			if ($inQuote){
				if ($char == '"') $inQuote = false;
			} else {
				if ($char == '"'){
					$inQuote = true;
				} else if ($char == '　'){
					$char = ' ';
				}
			}
			$destStr .= $char;
		}

		// キーワード分割処理
		preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $destStr, $matches);		// 半角スペースまたは「+」で分割
		$keywords = array_map(array($this, '_trim_search_keyword'), $matches[0]);
		return $keywords;
	}
	/**
	 * 検索キーワード処理コールバック関数
	 *
	 * @param string $src		検索キーワード
	 * @return string			変換後データ
	 */
	function _trim_search_keyword($src){
		return trim($src, "\"'\n\r ");
	}
}
?>
