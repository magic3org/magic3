<?php
/**
 * index.php用コンテナクラス
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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class pretty_photoWidgetContainer extends BaseWidgetContainer
{
	private $paramObj;		// 定義取得用
	private $imageInfoArray = array();			// 画像情報
	private $imageSize;			// 画像サイズ
	private $groupId;			// グループ化用ID
	private $cssId;			// タグのID
	private $css;
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_IMAGE_SIZE = 60;		// デフォルトのサムネールサイズ
	const DEFAULT_THEME = 'light_rounded';		// デフォルトテーマ
	const DEFAULT_OPACITY = '0.80';		// デフォルトの透明度
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		return 'index.tmpl.html';
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
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		if ($cmd == M3_REQUEST_CMD_DO_WIDGET){	// ウィジェット単体実行
			//$imagePath = $this->gEnv->getSystemRootPath() . '/' . $request->trimValueOf('path');			// 画像への相対URL
			$imagePath = urldecode($request->trimValueOf('path'));			// 日本語ファイル名を処理
			if (!strStartsWith($imagePath, '/')) $imagePath = '/' . $imagePath;
			$imagePath = $this->gEnv->getSystemRootPath() . $imagePath;
			$size = $request->trimValueOf('size');
			if (empty($size)) $size = self::DEFAULT_IMAGE_SIZE;
			if (file_exists($imagePath)){
				$mime = $this->getMimeType($imagePath);
				$ext = $this->getExtension($imagePath);
				$type = $this->getImageType($mime, $ext);
				
				if ($type != ''){
					// ページ作成処理中断
					$this->gPage->abortPage();
	
					$image = $this->createThumb($type, $imagePath, $size);
					if ($image !== false){
						header("Content-type: image/{$type}", true);
						$this->outputThumb($type, $image);
					}
				
					// システム強制終了
					$this->gPage->exitSystem();
				}
			} else {
				$this->gOpeLog->writeUserData(__METHOD__, '画像ファイルが見つかりません。', 2001, 'path=[' . $imagePath . '], widgetid=' . $this->gEnv->getCurrentWidgetId());
			}
		} else {
			// 定義ID取得
			$configId = $this->gEnv->getCurrentWidgetConfigId();
			if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
			// パラメータオブジェクトを取得
			$targetObj = $this->getWidgetParamObjByConfigId($configId);
			if (!empty($targetObj)){		// 定義データが取得できたとき
				$name	= $targetObj->name;// 名前
				$this->imageSize	= $targetObj->size;			// 画像サイズ
				if (empty($this->imageSize)) $this->imageSize = self::DEFAULT_IMAGE_SIZE;
				$opacity = $targetObj->opacity;		// 透明度
				if (empty($opacity)) $opacity = self::DEFAULT_OPACITY;
				if (!empty($targetObj->imageInfo)){
					$this->imageInfoArray = $targetObj->imageInfo;			// 画像情報
				}
				$this->css		= $targetObj->css;		// CSS
				$this->cssId	= $targetObj->cssId;	// CSS用のID
				$theme	= $targetObj->theme;		// テーマ
				if (empty($theme)) $theme = self::DEFAULT_THEME;
				$showSocialButton = $targetObj->showSocialButton;		// ソーシャルボタンを表示するかどうか
				$showTitle = $targetObj->showTitle;		// タイトルを表示するかどうか
			}
		
			$this->groupId = $this->gEnv->getCurrentWidgetId() . '_' . $configId . '_' . 'group';				// グループ化用ID
			
			// サムネール表示を作成
			$this->createImageList();
			
			// 画面に値を埋め込む
			$this->tmpl->addVar("_widget", "opacity",	$opacity);		// 透明度
			$this->tmpl->addVar('_widget', 'group_id',	$this->groupId);	// グループID
			$this->tmpl->addVar("_widget", "css_id",	$this->cssId);		// CSS用ID
			$this->tmpl->addVar("_widget", "theme",	$theme);		// テーマ
			if (!empty($showTitle)) $this->tmpl->setAttribute('title_area', 'visibility', 'hidden');// タイトルを表示するかどうか
			if (empty($showSocialButton)) $this->tmpl->setAttribute('social_tool_area', 'visibility', 'visible');// ソーシャルボタンを非表示
		}
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->css;
	}
	/**
	 * 画像情報一覧を作成
	 *
	 * @return なし						
	 */
	function createImageList()
	{
		$size = $this->convertToDispString($this->imageSize);
		$imageCount = count($this->imageInfoArray);
		for ($i = 0; $i < $imageCount; $i++){
			$infoObj = $this->imageInfoArray[$i];
			$name = $infoObj->name;// タイトル名
			$desc = $infoObj->desc;		// 説明
			
			// 画像URL
			$url = '';
			$relativeUrl = '';
			if (!empty($infoObj->url)){
				$url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $infoObj->url);
				$relativeUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $infoObj->url);
			}

			// 生成パスを設定
			$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
			$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
			$urlparam .= 'act=generate&path=' . urlencode($relativeUrl) . '&size=' . $size;
			$thumbUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;
			
			$row = array(
				'thumb_url' => $this->convertToDispString($this->getUrl($thumbUrl)),				// サムネールURL
				'url' => $this->convertUrlToHtmlEntity($this->getUrl($url)),			// 実画像URL
				'width' => $size,		// 画像幅
				'height' => $size,		// 画像高さ
				'title' => $this->convertToDispString($name),			// 名前
				'desc' => $this->convertToDispString($desc),				// 説明
				'group_id' => $this->convertToDispString($this->groupId)			// グループ化用ID
			);
			$this->tmpl->addVars('image_list', $row);
			$this->tmpl->parseTemplate('image_list', 'a');
		}
	}
	/**
	 * MIMEのコンテンツタイプを取得
	 *
	 * @param string $path	ファイルパス
	 * @return string		コンテンツタイプ
	 */
	function getMimeType($path)
	{
		if (function_exists("mime_content_type")){
			$mime = mime_content_type($path);
		} else {
			$mime = $this->readMimeType($path);
		}
		return ($mime === false) ? '' : strtolower($mime);
	}
	/**
	 * ファイルを読み出して、MIMEのコンテンツタイプを取得
	 *
	 * @param string $path	ファイルパス
	 * @return string		コンテンツタイプ
	 */
	function readMimeType($path)
	{
		$fh = fopen($path, "r");
		if ($fh === false) return false;
		
		$start4 = fread($fh, 4);
		$start3 = substr($start4, 0, 3);
		fclose($fh);
		
		if ($start4 == "\x89PNG")		return "image/png";
		if ($start3 == "GIF")			return "image/gif";
		if ($start3 == "\xFF\xD8\xFF")	return "image/jpeg";
		if ($start4 == "hsi1")			return "image/jpeg";
		return '';
	}
	/**
	 * ファイルの拡張子を取得
	 *
	 * @param string $path	ファイルパス
	 * @return string		拡張子
	 */
	function getExtension($path)
	{
		if (function_exists('mb_ereg')){
			if (mb_ereg('^.*\.([^\.]*)$', $path, $part)){
				return strtolower($part[1]);
			}
		} else {
			//if (ereg('^.*\.([^\.]*)$', $path, $part)){
			if (preg_match('/^.*\.([^\.]*)$/', $path, $part)){		// PHP 5.3対応(2010/5/28)
				return strtolower($part[1]);
			}
		}
		return '';
	}
	/**
	 * 画像の種別を取得
	 *
	 * @param string $mime	MIMEコンテンツタイプ
	 * @param string $ext	拡張子
	 * @return string		画像の種別
	 */
	function getImageType($mime, $ext)
	{
		if ($mime != ''){
			if ($mime == "image/gif")	return "gif";
			if ($mime == "image/jpeg")	return "jpeg";
			if ($mime == "image/jpg")	return "jpeg";
			if ($mime == "image/pjpeg")	return "jpeg";
			if ($mime == "image/png")	return "png";
		}
		
		if ($ext == "gif")	return "gif";
		if ($ext == "jpg")	return "jpeg";
		if ($ext == "jpeg")	return "jpeg";
		if ($ext == "png")	return "png";
		return '';
	}
	/**
	 * サムネールを作成
	 *
	 * @param string $type	MIMEコンテンツタイプ
	 * @param string $path	拡張子
	 * @param int $size		サムネールの縦横サイズ
	 * @return object		画像オブジェクト
	 */
	function createThumb($type, $path, $size)
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
		$width = imagesx($img);
		$height = imagesy($img);
		
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
		
		// imagecreatetruecolor
		$thumb = imagecreatetruecolor($size, $size);
		
		$bgcolor = imagecolorallocate($thumb, 255, 255, 255);
		imagefill($thumb, 0, 0, $bgcolor);
		
		// imagecopyresized (imagecopyresampled)
		if (function_exists("imagecopyresampled")){
			if (!imagecopyresampled($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)){
				if (!imagecopyresized($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false;
			}
		} else {
			if (!imagecopyresized($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false;
		}
		return $thumb;
	}
	/**
	 * サムネールを出力
	 *
	 * @param string $type	MIMEコンテンツタイプ
	 * @param object $image	画像オブジェクト
	 * @param string $path	ファイル保存の場合のパス
	 * @return 				なし
	 */
	function outputThumb($type, &$image, $path = null)
	{
		if (is_null($path)){
			switch ($type){
				case "jpeg":
					imagejpeg($image);
					break;
				case "gif":
					imagegif($image);
					break;
				case "png":
					imagepng($image);
					break;
			}
		} else {
			switch ($type){
				case "jpeg":
					imagejpeg($image, $path);
					break;
				case "gif":
					imagegif($image, $path);
					break;
				case "png":
					imagepng($image, $path);
					break;
			}
		}
		// イメージを破棄
		imagedestroy($image);
	}
}
?>
