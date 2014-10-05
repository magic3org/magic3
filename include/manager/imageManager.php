<?php
/**
 * 画像処理マネージャー
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class ImageManager extends Core
{
	private $defaultThumbExt;			// デフォルトのサムネールのファイル拡張子
	private $defaultThumbSize;			// デフォルトのサムネールの画像サイズ
	private $defaultThumbType;			// デフォルトのサムネールの画像タイプ
	private $siteLogoFomatArray;		// サイトロゴ画像フォーマット情報
	private $avatarFormatArray;			// アバターの画像フォーマット情報
	const CONTENT_DIR = '/etc/';
	const THUMBNAIL_DIR = '/thumb';
	const SITE_LOGO_DIR = '/etc/site/thumb';		// サイトロゴ格納ディレクトリ
	const AVATAR_DIR = '/etc/avatar/';		// アバター格納ディレクトリ
	const DEFAULT_THUMB_DIR = '/etc/default/thumb/';		// デフォルトサムネールディレクトリ
	const DEFAULT_SITE_LOGO_BASE = 'logo';		// デフォルトのサイトロゴファイル名ベース
	const DEFAULT_AVATAR_BASE = 'default';		// デフォルトのアバターファイル名ベース
	const NOT_AVAILABLE_HEAD = 'notavailable_';		// 設定画像がない場合の表示画像のファイル名ヘッダ部
	// DB定義値
	const CF_SITE_LOGO_FILENAME	= 'site_logo_filename';		// サイトロゴファイル
	const CF_SITE_LOGO_FORMAT	= 'site_logo_format';		// サイトロゴフォーマット
	const CF_THUMB_FORMAT		= 'thumb_format';		// サムネールフォーマット
	const CF_AVATAR_FORMAT		= 'avatar_format';	// アバターフォーマット
	const CF_OGP_THUMB_FORMAT	= 'ogp_thumb_format';		// OGPサムネールフォーマット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gSystemManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// デフォルト値取得
		
		// 画像のフォーマットを取得
		$formatArray = explode(';', $gSystemManager->getSystemConfig(self::CF_THUMB_FORMAT));
		if (count($formatArray) > 0){
			$format = trim($formatArray[0]);
			$ret = preg_match('/(\d+)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $format, $matches);
			if ($ret){
				$this->defaultThumbSize = $matches[1];
				$this->defaultThumbType = $this->defaultThumbSize . strtolower($matches[2]);
				$this->defaultThumbExt = strtolower($matches[3]);
			}
		}
	}
	/**
	 * システム共通のデフォルトのサムネールを作成
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int    $deviceType	デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param string $contentId		コンテンツID
	 * @param string $path			元の画像ファイル
	 * @param array  $destFilename	作成した画像のファイル名
	 * @param string $destDir		作成した画像のディレクトリ
	 * @param string $destDirUrl	作成した画像のディレクトリURL
	 * @return bool					true=作成,false=作成失敗
	 */
	function createSystemDefaultThumb($contentType, $deviceType, $contentId, $path, &$destFilename, &$destDir = NULL, &$destDirUrl = NULL)
	{
		global $gSystemManager;

		// パラメータエラーチェック
		if (strlen($contentId) == 0) return false;
		
		$destFilename = array();		// 画像ファイル名
		
		// 画像格納用のディレクトリ作成
		$thumbDir = $this->getSystemThumbPath($contentType, $deviceType);
		if (!file_exists($thumbDir)) mkdir($thumbDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		if (!is_null($destDir)) $destDir = $thumbDir;
		
		// 画像ディレクトリのURL
		if (!is_null($destDirUrl)) $destDirUrl = $this->getSystemThumbUrl($contentType, $deviceType);

		// 画像のフォーマットを取得
		$formatArray = explode(';', $gSystemManager->getSystemConfig(self::CF_THUMB_FORMAT));
		
		for ($i = 0; $i < count($formatArray); $i++){
			$format = trim($formatArray[$i]);
			$ret = preg_match('/(\d+)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $format, $matches);
			if ($ret){
				$thumbSize = $matches[1];
				$thumbAttr = strtolower($matches[2]);
				$ext = strtolower($matches[3]);

				$imageType = IMAGETYPE_JPEG;
				switch ($ext){
					case 'jpg':
					case 'jpeg':
						$imageType = IMAGETYPE_JPEG;
						break;
					case 'gif':
						$imageType = IMAGETYPE_GIF;
						break;
					case 'png':
						$imageType = IMAGETYPE_PNG;
						break;
					case 'bmp':
						$imageType = IMAGETYPE_BMP;
						break;
				}
				$thumbFilename = $contentId . '_' . $format;
				$thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $thumbFilename;

				// サムネールの作成
				if ($thumbAttr == 'c'){		// 切り取りサムネールの場合
					//$ret = $this->gInstance->getImageManager()->createThumb($path, $thumbPath, $thumbSize, $imageType, true);
					$ret = $this->createThumb($path, $thumbPath, $thumbSize, $imageType, true);
				} else {
					//$ret = $this->gInstance->getImageManager()->createThumb($path, $thumbPath, $thumbSize, $imageType, false);
					$ret = $this->createThumb($path, $thumbPath, $thumbSize, $imageType, false);
				}
				if ($ret){
					$destFilename[] = $thumbFilename;
				} else {
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * システム共通のデフォルトのサムネールを削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int    $deviceType	デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param array  $filename		ファイル名
	 * @return bool					true=成功,false=失敗
	 */
	function delSystemDefaultThumb($contentType, $deviceType, $filename)
	{
		global $gSystemManager;
		
		// 画像格納用のディレクトリ取得
		$thumbDir = $this->getSystemThumbPath($contentType, $deviceType);
		
		$ret = true;
		for ($i = 0; $i < count($filename); $i++){
			$thumbnailPath = $thumbDir . DIRECTORY_SEPARATOR . $filename[$i];
			if (!@unlink($thumbnailPath)) $ret = false;
		}
		return $ret;
	}
	/**
	 * システム共通のサムネール画像のURLを取得
	 *
	 * @param string     $contentType	コンテンツタイプ
	 * @param int        $deviceType	デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param string     $filename		ファイル名(空のときは格納ディレクトリを返す)
	 * @param string     $notAvailableFileType	ファイルが見つからない場合の代替ファイルのタイプ(ogp=OGP用サムネール)
	 * @return string					画像URL
	 */
	function getSystemThumbUrl($contentType, $deviceType, $filename = '', $notAvailableFileType = '')
	{
		global $gEnvManager;
				
		switch ($deviceType){
			case 0:		// PC
			default:
				$deviceDir = '';
				break;
			case 1:		// 携帯
				$deviceDir = '/m';
				break;
			case 2:		// スマートフォン
				$deviceDir = '/s';
				break;
		}
		
		$destUrl = $gEnvManager->getResourceUrl() . self::CONTENT_DIR . $contentType . $deviceDir . self::THUMBNAIL_DIR;		// 画像格納用のディレクトリ
		
		if (empty($notAvailableFileType)){
			if (empty($filename)){
				return $destUrl;
			} else {
				return $destUrl . '/' . $filename;
			}
		} else {		// 画像が見つからない場合の画像タイプが指定されているとき
			// ファイルとして扱う
			$destDir = $gEnvManager->getResourcePath() . self::CONTENT_DIR . $contentType . $deviceDir . self::THUMBNAIL_DIR;		// 画像格納用のディレクトリ
			$path = $destDir . DIRECTORY_SEPARATOR . $filename;
			if (!is_dir($path) && is_readable($path)){
				return $destUrl . '/' . $filename;
			} else {
				return $this->getNotAvailableImageUrl($notAvailableFileType);
			}
		}
	}
	/**
	 * システム共通のサムネール画像のパスを取得
	 *
	 * @param string     $contentType	コンテンツタイプ
	 * @param int        $deviceType	デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	 * @param string     $filename		ファイル名(空のときは格納ディレクトリを返す)
	 * @param string     $notAvailableFileType	ファイルが見つからない場合の代替ファイルのタイプ(ogp=OGP用サムネール)
	 * @return string					画像パス
	 */
	function getSystemThumbPath($contentType, $deviceType, $filename = '', $notAvailableFileType = '')
	{
		global $gEnvManager;
				
		switch ($deviceType){
			case 0:		// PC
			default:
				$deviceDir = '';
				break;
			case 1:		// 携帯
				$deviceDir = '/m';
				break;
			case 2:		// スマートフォン
				$deviceDir = '/s';
				break;
		}
		
		$destDir = $gEnvManager->getResourcePath() . self::CONTENT_DIR . $contentType . $deviceDir . self::THUMBNAIL_DIR;		// 画像格納用のディレクトリ
		
		if (empty($notAvailableFileType)){
			if (empty($filename)){
				return $destDir;
			} else {
				return $destDir . DIRECTORY_SEPARATOR . $filename;
			}
		} else {		// 画像が見つからない場合の画像タイプが指定されているとき
			// ファイルとして扱う
			$path = $destDir . DIRECTORY_SEPARATOR . $filename;
			if (!is_dir($path) && is_readable($path)){
				return $destDir . DIRECTORY_SEPARATOR . $filename;
			} else {
				return $this->getNotAvailableImagePath($notAvailableFileType);
			}
		}
	}
	/**
	 * デフォルトのサムネールを作成
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param string $path			元の画像ファイル
	 * @return bool				true=作成,false=作成失敗
	 */
	function createDefaultThumb($contentType, $contentId, $path)
	{
		global $gEnvManager;
		
		// サムネール画像のパス
		$destPath = $gEnvManager->getResourcePath() . self::CONTENT_DIR . $contentType . self::THUMBNAIL_DIR . DIRECTORY_SEPARATOR . $contentId . '_' . $this->defaultThumbType . '.' . $this->defaultThumbExt;

		// 画像格納用のディレクトリ作成
		$destDir = dirname($destPath);
		if (!file_exists($destDir)) mkdir($destDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
					
		$ret = $this->createThumb($path, $destPath, $this->defaultThumbSize, IMAGETYPE_PNG);
		return $ret;
	}
	/**
	 * サムネール画像のURLを取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @return string				画像URL
	 */
	function getDefaultThumbUrl($contentType, $contentId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourceUrl() . self::CONTENT_DIR . $contentType . self::THUMBNAIL_DIR . '/' . $contentId . '_' . $this->defaultThumbType . '.' . $this->defaultThumbExt;
	}
	/**
	 * サムネール画像のファイルパスを取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @return string				画像パス
	 */
	function getDefaultThumbPath($contentType, $contentId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourcePath() . self::CONTENT_DIR . $contentType . self::THUMBNAIL_DIR . DIRECTORY_SEPARATOR . $contentId . '_' . $this->defaultThumbType . '.' . $this->defaultThumbExt;
	}
	/**
	 * サムネールを作成
	 *
	 * @param string $path		元の画像ファイル
	 * @param string $destPath	サムネールファイルパス
	 * @param int,array $size	サムネールの縦横サイズ(縦横異なる場合は連想配列(width,height))
	 * @param int $type			サムネールの画像タイプ
	 * @param bool $useCrop		画像切り取りありかどうか
	 * @param string $bgColor	背景色。設定しない場合は空文字列
	 * @param int $imageQuality	画像品質
	 * @return bool				true=作成,false=作成失敗
	 */
	function createThumb($path, $destPath, $size, $type, $useCrop = true, $bgColor = '#FFFFFF', $imageQuality = 100)
	{
		$imageSize = @getimagesize($path);
		if ($imageSize){
			$imageType = $imageSize[2];
		} else {
			return false;
		}
		
		// 画像作成
		switch ($imageType){
			case IMAGETYPE_GIF:
				$imageObj = @imagecreatefromgif($path);
				break;
			case IMAGETYPE_JPEG:
				$imageObj = @imagecreatefromjpeg($path);
				break;
			case IMAGETYPE_PNG:
				$imageObj = @imagecreatefrompng($path);
				break;
			default:
				return false;
		}

		// 画像サイズ取得
		$x = 0;
		$y = 0;
		$newX = 0;
		$newY = 0;
		$width = imagesx($imageObj);
		$height = imagesy($imageObj);
		if (is_array($size)){
			$destWidth = $size['width'];		// サムネールの幅
			$destHeight = $size['height'];		// サムネールの高さ
		} else {
			$destWidth = $size;		// サムネールの幅
			$destHeight = $size;		// サムネールの高さ
		}
		if ($useCrop){		// 画像切り取りありのとき
			if (is_array($size)){
				if ($height / $destHeight < $width / $destWidth){
					$newHeight	= $destHeight;
					$newWidth	= round($destHeight * $width / $height);
					$x = ceil(($newWidth - $destWidth) / 2);
					$y = 0;
				} else {
					$newWidth	= $destWidth;
					$newHeight	= round($destWidth * $height / $width);
					$x = 0;
					$y = ceil(($newHeight - $destHeight) / 2);
				}
			} else {
				if ($width > $height){
					$newHeight	= $size;
					$newWidth	= round($size * $width / $height);

					$x = ceil(($width - $height) / 2);
					$y = 0;
				} else {
					$newWidth	= $size;
					$newHeight	= round($size * $height / $width);

					$x = 0;
					$y = ceil(($height - $width) / 2);
				}
			}
		} else {
			if ($width > $height){
				$newHeight = $height * ($size / $width);
				$newWidth = $size;
			} else {
				$newWidth = $width * ($size / $height);
				$newHeight = $size;
			}
		
			$newX = 0;
			$newY = 0;
			if ($newWidth < $size) $newX = round(($size - $newWidth) / 2);
			if ($newHeight < $size) $newY = round(($size - $newHeight) / 2);
		}

		// TrueColorイメージを作成
		//$thumbObj = imagecreatetruecolor($size, $size);
		$thumbObj = imagecreatetruecolor($destWidth, $destHeight);
		
		// 背景色の設定		
		if (!$useCrop){			// 切り取りなしのとき
			// サムネールの背景色を取得
			$bgColorR = intval(substr($bgColor, 1, 2), 16);
			$bgColorG = intval(substr($bgColor, 3, 2), 16);
			$bgColorB = intval(substr($bgColor, 5, 2), 16);

			// TrueColorイメージを作成
		//	$thumbObj = imagecreatetruecolor($size, $size);
			$bgcolor = imagecolorallocate($thumbObj, $bgColorR, $bgColorG, $bgColorB);		// 背景色設定
			imagefill($thumbObj, 0, 0, $bgcolor);
		}
		
		// 画像リサイズ
		// imagecopyresampledの方がimagecopyresizedよりも画質が良いのでこちらを使用
		if (function_exists("imagecopyresampled")){
			if (!imagecopyresampled($thumbObj, $imageObj, $newX, $newY, $x, $y, $newWidth, $newHeight, $width, $height)){
				if (!imagecopyresized($thumbObj, $imageObj, $newX, $newY, $x, $y, $newWidth, $newHeight, $width, $height)) return false;
			}
		} else {
			if (!imagecopyresized($thumbObj, $imageObj, $newX, $newY, $x, $y, $newWidth, $newHeight, $width, $height)) return false;
		}
		
		// 画像出力
		switch ($type){
			case IMAGETYPE_GIF:
				$ret = @imagegif($thumbObj, $destPath, $imageQuality);
				break;
			case IMAGETYPE_JPEG:
				$ret = @imagejpeg($thumbObj, $destPath, $imageQuality);
				break;
			case IMAGETYPE_PNG:
				//$ret = @imagepng($thumbObj, $destPath, $imageQuality);		// PNGでは$imageQualityを使用すると画像が0サイズで作成される
				$ret = @imagepng($thumbObj, $destPath);
				break;
		}
		// イメージを破棄
		$ret = imagedestroy($imageObj);
		$ret = imagedestroy($thumbObj);
		return $ret;
	}
	
	/**
	 * リサイズ画像を作成
	 *
	 * @param string $path		元の画像ファイル
	 * @param string $destPath	出力画像ファイルパス
	 * @param int,array $size	画像縦横最大サイズ(intの場合は縦横が収まる最大サイズ、arrayの場合は縦横の最大サイズ(width,height))
	 * @param int $type			出力画像タイプ
	 * @param array destSize	作成画像の縦横
	 * @param int $imageQuality	画像品質
	 * @return bool				true=作成,false=作成失敗
	 */
	function createImage($path, $destPath, $size, $type, &$destSize, $imageQuality = 100)
	{
		$imageSize = @getimagesize($path);
		if ($imageSize){
			$imageType = $imageSize[2];
			$srcWidth = $imageSize[0];
			$srcHeight = $imageSize[1];
		} else {
			return false;
		}

		// 画像オブジェクト作成
		switch ($imageType){
			case IMAGETYPE_GIF:
				$imageObj = @imagecreatefromgif($path);
				break;
			case IMAGETYPE_JPEG:
				$imageObj = @imagecreatefromjpeg($path);
				break;
			case IMAGETYPE_PNG:
				$imageObj = @imagecreatefrompng($path);
				break;
			default:		// 処理不可の画像タイプの場合は終了
				return false;
		}
		
		// 画像のサイズを求める
		if (is_array($size)){		// 配列の場合は縦横サイズ指定
			$destWidth = $size['width'];
			$destHeight = $size['height'];
		} else {
			$destWidth = $srcWidth;
			$destHeight = $srcHeight;
			if ($srcWidth > $srcHeight){
				if ($srcWidth > $size){
					$destWidth = $size;
					$destHeight = round($srcHeight * ($size / $srcWidth));
				}
			} else {
				if ($srcHeight > $size){
					$destWidth = round($srcWidth * ($size / $srcHeight));
					$destHeight = $size;
				}
			}
		}
		
		// 出力用の画像オブジェクト作成
		$destImageObj = imagecreatetruecolor($destWidth, $destHeight);
		
		// 画像作成
		if (function_exists("imagecopyresampled")){
			if (!imagecopyresampled($destImageObj, $imageObj, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight)){
				if (!imagecopyresized($destImageObj, $imageObj, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight)) return false;
			}
		} else {
			if (!imagecopyresized($destImageObj, $imageObj, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight)) return false;
		}
		
		// 画像ファイル出力
		switch ($type){
			case IMAGETYPE_GIF:
				$ret = @imagegif($destImageObj, $destPath, $imageQuality);
				break;
			case IMAGETYPE_JPEG:
				$ret = @imagejpeg($destImageObj, $destPath, $imageQuality);
				break;
			case IMAGETYPE_PNG:
				//$ret = @imagepng($destImageObj, $destPath, $imageQuality);		// PNGでは$imageQualityを使用すると画像が0サイズで作成される
				$ret = @imagepng($destImageObj, $destPath);
				break;
		}
		// イメージを破棄
		$ret = imagedestroy($imageObj);
		$ret = imagedestroy($destImageObj);
		
		// 画像サイズの再設定
		$destSize = array('width' => $destWidth, 'height' => $destHeight);
		return $ret;
	}
	/**
	 * コンテンツから先頭の画像のパスを取得
	 *
	 * @param string $html		検索コンテンツ
	 * @return string			画像パス(取得できない場合は空文字列)
	 */
	function getFirstImagePath($html)
	{
		global $gEnvManager;
		
		$exp = '/<img[^<]*?src\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
		$ret = preg_match($exp, $html, $matches);
		if ($ret){
			$path = $gEnvManager->getMacroPath($matches[1]);		// 画像URL
			$path = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getSystemRootPath(), $path);// マクロ変換
			if (is_readable($path)) return $path;
		}
		return '';
	}
	/**
	 * サイトロゴ画像のURLを取得
	 *
	 * @param string $type			画像タイプ「sm」「md」「lg」。空の場合はロゴ格納ディレクトリURLを返す。
	 * @return string				画像URL。画像が存在しないときは空。
	 */
	function getSiteLogoUrl($type = 'lg')
	{
		global $gEnvManager;
		global $gSystemManager;
		
		// サイトロゴ画像情報を読み込む
		$this->_loadSiteLogoInfo();
		
		//$logoFilename = '';
		//$value = $this->siteLogoFomatArray[$type];
		//if (isset($value)) $logoFilename = self::DEFAULT_SITE_LOGO_BASE . '_' . $value;
		$logoFilename = $this->getSiteLogoFilename($type);
		
		if (empty($logoFilename)){
			$url = $gEnvManager->getResourceUrl() . self::SITE_LOGO_DIR;
		} else {
			$path = $gEnvManager->getResourcePath() . self::SITE_LOGO_DIR . '/' . $logoFilename;
			$url = '';
			
			// 画像の存在をチェック
			if (is_readable($path)) $url = $gEnvManager->getResourceUrl() . self::SITE_LOGO_DIR . '/' . $logoFilename;
		}
		return $url;
	}
	/**
	 * サイトロゴ画像のパスを取得
	 *
	 * @param string $type			画像タイプ「sm」「md」「lg」。空文字列の場合はロゴ格納ディレクトリを返す。
	 * @return string				画像パス
	 */
	function getSiteLogoPath($type = 'lg')
	{
		global $gEnvManager;
		
		// サイトロゴ画像情報を読み込む
		$this->_loadSiteLogoInfo();
		
//		$logoFilename = '';
//		$value = $this->siteLogoFomatArray[$type];
//		if (isset($value)) $logoFilename = self::DEFAULT_SITE_LOGO_BASE . '_' . $value;
		$logoFilename = $this->getSiteLogoFilename($type);
		
		if (empty($logoFilename)){
			$path = $gEnvManager->getResourcePath() . self::SITE_LOGO_DIR;
		} else {
			$path = $gEnvManager->getResourcePath() . self::SITE_LOGO_DIR . '/' . $logoFilename;
		}
		return $path;
	}
	/**
	 * サイトロゴの画像サイズIDを取得
	 *
	 * @return array				画像サイズID
	 */
	function getAllSiteLogoSizeId()
	{
		// サイトロゴ画像情報を読み込む
		$this->_loadSiteLogoInfo();
		
		return array_keys($this->siteLogoFomatArray);
	}
	/**
	 * サイトロゴの画像フォーマットを取得
	 *
	 * @return array				画像フォーマット
	 */
	function getAllSiteLogoFormat()
	{
		// サイトロゴ画像情報を読み込む
		$this->_loadSiteLogoInfo();
		
		return array_values($this->siteLogoFomatArray);
	}
	/**
	 * サイトロゴ画像ファイル名を取得
	 *
	 * @param string $type			画像タイプ「sm」「md」「lg」。空の場合はすべてのファイル名を取得。
	 * @return string,array			画像ファイル名
	 */
	function getSiteLogoFilename($type = '')
	{
		// サイトロゴ画像情報を読み込む
		$this->_loadSiteLogoInfo();
		
		if (empty($type)){
			$filenameArray = array();
			$formats = array_values($this->siteLogoFomatArray);
			for ($i = 0; $i < count($formats); $i++){
				$filenameArray[] = self::DEFAULT_SITE_LOGO_BASE . '_' . $formats[$i];
			}
			return $filenameArray;
		} else {
			$logoFilename = '';
			$value = $this->siteLogoFomatArray[$type];
			if (isset($value)) $logoFilename = self::DEFAULT_SITE_LOGO_BASE . '_' . $value;
		
			return $logoFilename;
		}
	}
	/**
	 * サイトロゴ画像情報を読み込む
	 *
	 * @return 					なし
	 */
	function _loadSiteLogoInfo()
	{
		global $gSystemManager;
		
		if (!isset($this->siteLogoFomatArray)){
			// フォーマットを読み込む
			$this->siteLogoFomatArray = array();
			$lines = explode(';', $gSystemManager->getSystemConfig(self::CF_SITE_LOGO_FORMAT));		// サイトロゴフォーマット
			for ($i = 0; $i < count($lines); $i++){
				$line = trim($lines[$i]);
				if (!empty($line)){
					$ret = preg_match('/(.*?)\s*=\s*(\d+)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $line, $matches);
					if ($ret){
						$imageType = $matches[1];
						$imageFormat = $matches[2] . strtolower($matches[3]) . '.' . strtolower($matches[4]);
						if (!empty($imageType)) $this->siteLogoFomatArray[$imageType] = $imageFormat;
					}
				}
			}
		}
	}
	/**
	 * サイトロゴのファイル名ベースを取得
	 *
	 * @return string				ファイル名ベース
	 */
	function getSiteLogoFilenameBase()
	{
		return self::DEFAULT_SITE_LOGO_BASE;
	}
	/**
	 * アバター画像のURLを取得
	 *
	 * @param string $filename		画像ファイル名。空文字列の場合はデフォルトアバター画像。
	 * @param string $type			画像タイプ「sm」「md」「lg」
	 * @return string				画像URL。存在しない場合はデフォルト画像URLを取得
	 */
	function getAvatarUrl($filename = '', $type = 'lg')
	{
		global $gEnvManager;
		
		if (empty($filename)){
//			$filename = self::DEFAULT_AVATAR_BASE . '_' . $this->getAvatarFormat($type);
			$filename = $this->getDefaultAvatarFilename($type);
		} else {
			$path = $gEnvManager->getResourcePath() . self::AVATAR_DIR . $filename;
			//if (!is_readable($path)) $filename = self::DEFAULT_AVATAR_BASE . '_' . $this->getAvatarFormat($type);
			if (!is_readable($path)) $filename = $this->getDefaultAvatarFilename($type);
		}
		$url = $gEnvManager->getResourceUrl() . self::AVATAR_DIR . $filename;
		return $url;
	}
	/**
	 * アバター画像のパスを取得
	 *
	 * @param string $filename		画像ファイル名。空文字列の場合はデフォルトアバター画像。
	 * @param string $type			画像タイプ「sm」「md」「lg」。画像ファイル名が空文字列で、画像タイプが空文字列の場合はロゴ格納ディレクトリを返す。
	 * @return string				画像パス
	 */
	function getAvatarPath($filename = '', $type = 'lg')
	{
		global $gEnvManager;
		
/*		if (empty($filename)) $filename = self::DEFAULT_AVATAR_BASE . '_' . $this->getAvatarFormat($type);
		$path = $gEnvManager->getResourcePath() . self::AVATAR_DIR . $filename;
		return $path;*/
		
		if (empty($filename)){
			//$value = $this->avatarFormatArray[$type];
			//if (isset($value)) $filename = self::DEFAULT_AVATAR_BASE . '_' . $value;
			$filename = $this->getDefaultAvatarFilename($type);
		}
		
		if (empty($filename)){
			$path = $gEnvManager->getResourcePath() . self::AVATAR_DIR;
		} else {
			$path = $gEnvManager->getResourcePath() . self::AVATAR_DIR . '/' . $filename;
		}
		return $path;
	}
	/**
	 * アバターデフォルトフォーマットを取得
	 *
	 * @return string				フォーマット
	 */
	function getDefaultAvatarFormat()
	{
		$format = $this->getAvatarFormat('md');
		return $format;
	}
	/**
	 * アバター画像フォーマットを取得
	 *
	 * @param string $type				画像タイプ「sm」「md」「lg」
	 * @param string					画像フォーマット。取得できないときは空文字列。
	 */
	function getAvatarFormat($type)
	{
		// アバター画像情報を読み込む
		$this->_loadAvatarInfo();
		
		$format = '';
		$value = $this->avatarFormatArray[$type];
		if (isset($value)) $format = $value;
		return $format;
	}
	/**
	 * アバターの画像情報を読み込む
	 *
	 * @return 					なし
	 */
	function _loadAvatarInfo()
	{
		global $gSystemManager;
		
		if (!isset($this->avatarFormatArray)){
			// フォーマットを読み込む
			$this->avatarFormatArray = array();
			$lines = explode(';', $gSystemManager->getSystemConfig(self::CF_AVATAR_FORMAT));		// アバターフォーマット
			for ($i = 0; $i < count($lines); $i++){
				$line = trim($lines[$i]);
				if (!empty($line)){
					$ret = preg_match('/(.*?)\s*=\s*(\d+)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $line, $matches);
					if ($ret){
						$imageType = $matches[1];
						$imageFormat = $matches[2] . strtolower($matches[3]) . '.' . strtolower($matches[4]);
						if (!empty($imageType)) $this->avatarFormatArray[$imageType] = $imageFormat;
					}
				}
			}
		}
	}
	/**
	 * アバターの画像サイズIDを取得
	 *
	 * @return array				画像サイズID
	 */
	function getAllAvatarSizeId()
	{
		// アバター画像情報を読み込む
		$this->_loadAvatarInfo();
		
		return array_keys($this->avatarFormatArray);
	}
	/**
	 * アバターのフォーマットを取得
	 *
	 * @return array				画像サイズID
	 */
	function getAllAvatarFormat()
	{
		// アバター画像情報を読み込む
		$this->_loadAvatarInfo();
		
		return array_values($this->avatarFormatArray);
	}
	/**
	 * デフォルトアバターのファイル名ベースを取得
	 *
	 * @return string				ファイル名ベース
	 */
	function getDefaultAvatarFilenameBase()
	{
		return self::DEFAULT_AVATAR_BASE;
	}
	/**
	 * デフォルトアバターのファイル名を取得
	 *
	 * @param string $type			画像タイプ「sm」「md」「lg」。空の場合はすべてのファイル名を取得。
	 * @return string,array			画像ファイル名
	 */
	function getDefaultAvatarFilename($type = '')
	{
		// アバター画像情報を読み込む
		$this->_loadAvatarInfo();
		
		if (empty($type)){
			$filenameArray = array();
			$formats = array_values($this->avatarFormatArray);
			for ($i = 0; $i < count($formats); $i++){
				$filenameArray[] = self::DEFAULT_AVATAR_BASE . '_' . $formats[$i];
			}
			return $filenameArray;
		} else {
			$filename = '';
			$value = $this->avatarFormatArray[$type];
			if (isset($value)) $filename = self::DEFAULT_AVATAR_BASE . '_' . $value;
			return $filename;
		}
	}
	
	/**
	 * OGP用サムネールデフォルトフォーマットを取得
	 *
	 * @return string				フォーマット
	 */
	function getDefaultOGPThumbFormat()
	{
		global $gSystemManager;
		
		$format = $gSystemManager->getSystemConfig(self::CF_OGP_THUMB_FORMAT);		// OGPサムネールフォーマット
		return $format;
	}
	/**
	 * 指定フォーマットの画像を作成
	 *
	 * @param string $path					元の画像ファイル
	 * @param string,array $formats			作成する画像フォーマット(複数の場合は「;」区切り)またはフォーマットの配列
	 * @param string $destDir				作成する画像のディレクトリ
	 * @param string $destFilenameBase		作成する画像ファイル名基本部。ファイル名は「基本部_フォーマット」で作成。
	 * @param array  $destFilename			作成した画像のファイル名
	 * @return bool							true=作成,false=作成失敗
	 */
	function createImageByFormat($path, $formats, $destDir, $destFilenameBase, &$destFilename)
	{
		// 引数エラーチェック
		if (empty($path) || empty($formats) || empty($destDir) || empty($destFilenameBase)) return false;
		
		$destFilename = array();		// 画像ファイル名
		
		// 画像のフォーマットを取得
		if (is_array($formats)){
			$formatArray = $formats;
		} else {
			$formatArray = explode(';', $formats);
		}
		
		for ($i = 0; $i < count($formatArray); $i++){
			$format = $formatArray[$i];
			$ret = $this->parseImageFormat($format, $imageType, $imageAttr, $imageSize);
			if ($ret){
				$newFilename = $destFilenameBase . '_' . $format;
				$newPath = $destDir . DIRECTORY_SEPARATOR . $newFilename;

				// サムネールの作成
				if ($imageAttr == 'c'){		// 切り取りサムネールの場合
					$ret = $this->createThumb($path, $newPath, $imageSize, $imageType, true);
				} else {
					$ret = $this->createThumb($path, $newPath, $imageSize, $imageType, false);
				}
				if ($ret){
					$destFilename[] = $newFilename;
				} else {
					return false;
				}
			} else {
				break;
			}
		}
		return $ret;
	}
	/**
	 * フォーマットから画像サイズ、画像タイプを取得
	 *
	 * @param string $format		画像フォーマット
	 * @param string $imageType		画像タイプ
	 * @param string $imageAttr		画像属性(c=切り取りあり)
	 * @param string,array $imageSize		画像サイズ(縦横異なる場合は連想配列(width,height))
	 * @return bool					true=取得成功、false=取得失敗
	 */
	function parseImageFormat($format, &$imageType, &$imageAttr, &$imageSize)
	{
		$format = trim($format);
		//$ret = preg_match('/(\d+)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $format, $matches);
		$ret = preg_match('/(\d+)([xX]?)(\d*)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $format, $matches);	// 「MMxNNc.jpg」タイプのフォーマットを解析
		if ($ret){
			$imageSize = $matches[1];
			$height = intval($matches[3]);
			if ($height > 0){
				$imageSize = array('width' => $imageSize, 'height' => $height);
			}
			//$imageAttr = strtolower($matches[2]);
			//$ext = strtolower($matches[3]);
			$imageAttr = strtolower($matches[4]);
			$ext = strtolower($matches[5]);
			
			$imageType = IMAGETYPE_JPEG;
			switch ($ext){
				case 'jpg':
				case 'jpeg':
					$imageType = IMAGETYPE_JPEG;
					break;
				case 'gif':
					$imageType = IMAGETYPE_GIF;
					break;
				case 'png':
					$imageType = IMAGETYPE_PNG;
					break;
				case 'bmp':
					$imageType = IMAGETYPE_BMP;
					break;
			}
		}
		return $ret;
	}
	/**
	 * 画像未設定時に表示する画像のURLを取得
	 *
	 * @param string $type		画像のタイプ(ogp=OGP用サムネール)
	 * @return string			画像URL
	 */
	function getNotAvailableImageUrl($type)
	{
		global $gEnvManager;
			
		switch ($type){
			case 'ogp':
				$url = $gEnvManager->getResourceUrl() . self::DEFAULT_THUMB_DIR . self::NOT_AVAILABLE_HEAD . $this->getDefaultOGPThumbFormat();
				break;
		}
		return $url;
	}
	/**
	 * 画像未設定時に表示する画像のパスを取得
	 *
	 * @param string $type		画像のタイプ(ogp=OGP用サムネール)
	 * @return string			画像URL
	 */
	function getNotAvailableImagePath($type)
	{
		global $gEnvManager;
			
		switch ($type){
			case 'ogp':
				$path = $gEnvManager->getResourcePath() . self::DEFAULT_THUMB_DIR . self::NOT_AVAILABLE_HEAD . $this->getDefaultOGPThumbFormat();
				break;
		}
		return $path;
	}
	/**
	 * 画像の情報を取得
	 *
	 * @param string $path		画像ファイル
	 * @param int $width		画像幅
	 * @param int $height		画像高さ
	 * @return bool				true=取得,false=取得失敗
	 */
	function getImageInfo($path, &$width, &$height)
	{
		$imageSize = @getimagesize($path);
		if ($imageSize){
			$imageType = $imageSize[2];
			$width = $imageSize[0];
			$height = $imageSize[1];
			return true;
		} else {
			return false;
		}
	}
}
?>
