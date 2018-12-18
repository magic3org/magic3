<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTest_uploadWidgetContainer extends admin_mainBaseWidgetContainer
{
	const TASK_TESTUPLOAD = "test_upload";
	
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
		return 'test/test_upload.tmpl.html';
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
		$act = $request->trimValueOf('act');

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'uploadimage'){		// 画像アップロード
			// 作業ディレクトリを作成
			$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $tmpDir);
		} else if ($act == 'getimage'){			// 画像取得
			$this->getImage();
		} else {
			$replaceNew = true;		// データを再取得
			
			// 作業ディレクトリを削除
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			rmDirectory($tmpDir);
		}
		
		if ($replaceNew){
		}
		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl();
		$uploadUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_TESTUPLOAD;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
		$this->tmpl->addVar("_widget", "upload_url", $this->getUrl($uploadUrl));		// アップロード用URL
		
		// ##### 画像の表示 #####
		// アップロードされているファイルがある場合は、アップロード画像を表示
		$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
		
		// サイトロゴ
		$imageUrl = '';
		$siteLogoSizeArray = $this->gInstance->getImageManager()->getAllSiteLogoSizeId();
		$filenameArray = $this->gInstance->getImageManager()->getSiteLogoFilename();
		for ($i = 0; $i < count($filenameArray); $i++){
			$path = $tmpDir . DIRECTORY_SEPARATOR . $filenameArray[$i];
			if (!file_exists($path)) break;
		}
		if ($i == count($filenameArray)){		// 画像が存在する場合
			// 画像参照用URL
			$imageUrl = $this->gEnv->getDefaultAdminUrl();
			$imageUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_TESTUPLOAD;
			$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
		} else {
			// 既存の画像を表示
			if (!empty($siteLogoSizeArray)){
				$sizeId = $siteLogoSizeArray[count($siteLogoSizeArray) -1];		// 最大画像
				$imageUrl = $this->gInstance->getImageManager()->getSiteLogoUrl($sizeId) . '?' . date('YmdHis');		// サイトロゴファイル名
			}
		}
		// サイトロゴサイズ取得
		if (!empty($siteLogoSizeArray)){
			$sizeId = $siteLogoSizeArray[count($siteLogoSizeArray) -1];		// 最大画像
			$this->gInstance->getImageManager()->getSiteLogoFormatInfo($sizeId, $imageType, $imageAttr, $imageSize);
		}
		$this->tmpl->addVar("_widget", "sitelogo_url", $this->convertUrlToHtmlEntity($this->getUrl($imageUrl)));
		$this->tmpl->addVar("_widget", "sitelogo_size", $imageSize . 'x' . $imageSize);
		
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());

	}
	/**
	 * アップロードファイルから各種画像を作成
	 *
	 * @param bool           $isSuccess		アップロード成功かどうか
	 * @param object         $resultObj		アップロード処理結果オブジェクト
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $filePath		アップロードされたファイル
	 * @param string         $destDir		アップロード先ディレクトリ
	 * @return								なし
	 */
	function uploadFile($isSuccess, &$resultObj, $request, $filePath, $destDir)
	{
		if ($isSuccess){		// ファイルアップロード成功のとき
			$ret = $this->gInstance->getImageManager()->createImageByFormat($filePath, $formats, $destDir, $filenameBase, $destFilename);
			if ($ret){			// 画像作成成功の場合
				// 画像参照用URL
				$imageUrl = $this->gEnv->getDefaultAdminUrl();
				$imageUrl .= '?' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
				$imageUrl .= '&' . date('YmdHis');
				$resultObj['url'] = $imageUrl;
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
	/**
	 * 最大画像を取得
	 *
	 * @return					なし
	 */
	function getImage()
	{
debug("get image");
		// 画像パス作成
/*		switch ($type){
		case self::IMAGE_TYPE_SITE_LOGO:		// サイトロゴ
			$siteLogoSizeArray = $this->gInstance->getImageManager()->getAllSiteLogoSizeId();
			if (!empty($siteLogoSizeArray)){
				$size = $siteLogoSizeArray[count($siteLogoSizeArray) -1];		// 最大画像
				$filename = $this->gInstance->getImageManager()->getSiteLogoFilename($size);
			}
			break;
		case self::IMAGE_TYPE_USER_AVATAR:		// アバター
			$avatarSizeArray = $this->gInstance->getImageManager()->getAllAvatarSizeId();
			if (!empty($avatarSizeArray)){
				$size = $avatarSizeArray[count($avatarSizeArray) -1];		// 最大画像
				$filename = $this->gInstance->getImageManager()->getDefaultAvatarFilename($size);
			}
			break;
		}*/
		$imagePath = '';
		if (!empty($filename)) $imagePath = $this->gEnv->getTempDirBySession() . '/' . $filename;
			
		// ページ作成処理中断
		$this->gPage->abortPage();

		if (is_readable($imagePath)){
			// 画像情報を取得
			$imageMimeType = '';
			$imageSize = @getimagesize($imagePath);
			if ($imageSize) $imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
			
			// 画像MIMEタイプ設定
			if (!empty($imageMimeType)) header('Content-type: ' . $imageMimeType);
			
			// キャッシュの設定
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0');
			header('Pragma: no-cache');
		
			// 画像ファイル読み込み
			readfile($imagePath);
		} else {
			$this->gPage->showError(404);
		}
	
		// システム強制終了
		$this->gPage->exitSystem();
	}
}
?>
