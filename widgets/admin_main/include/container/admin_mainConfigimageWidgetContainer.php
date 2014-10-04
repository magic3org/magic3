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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath()			. '/qqFileUploader/fileuploader.php');

class admin_mainConfigimageWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const IMAGE_TYPE_SITE_LOGO = 'sitelogo';			// 画像タイプ(サイトロゴ)
	const IMAGE_TYPE_USER_AVATAR = 'useravatar';		// 画像タイプ(ユーザアバター)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'configimage.tmpl.html';
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
		$type = $request->trimValueOf('type');		// 画像タイプ

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			$isErr = false;

			if ($isErr){		// エラー発生のとき
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
			} else {
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				
				$replaceNew = true;		// データを再取得
				
				// 作業ディレクトリを削除
				$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
				rmDirectory($tmpDir);
			}
		} else if ($act == 'uploadimage'){		// 画像アップロード
			// 作業ディレクトリを作成
			$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
				
			$uploader = new qqFileUploader(array());
			$resultObj = $uploader->handleUpload($tmpDir);
			
			if ($resultObj['success']){
				$fileInfo = $resultObj['file'];
				
				// 各種画像を作成
				switch ($type){
				case self::IMAGE_TYPE_SITE_LOGO:		// サイトロゴ
					$formats = $this->gInstance->getImageManager()->getAllSiteLogoFormat();
					$filenameBase = $this->gInstance->getImageManager()->getSiteLogoFilenameBase();
					break;
				case self::IMAGE_TYPE_USER_AVATAR:		// アバター
					$formats = $this->gInstance->getImageManager()->getAllAvatarFormat();
					$filenameBase = $this->gInstance->getImageManager()->getDefaultAvatarFilenameBase();
					break;
				}
				
				$ret = $this->gInstance->getImageManager()->createImageByFormat($fileInfo['path'], $formats, $tmpDir, $filenameBase, $destFilename);
				if ($ret){			// 画像作成成功の場合
					// 画像参照用URL
					$imageUrl = $this->gEnv->getDefaultAdminUrl();
					$imageUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIGIMAGE;
					$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
					//$imageUrl .= '&' . M3_REQUEST_PARAM_FILE_ID . '=' . $fileInfo['fileid'];
					$imageUrl .= '&type=' . $type;
					$resultObj['url'] = $imageUrl;
				} else {// エラーの場合
					$resultObj = array('error' => 'Could not create resized images.');
				}
				// アップロードファイル削除
				unlink($fileInfo['path']);
			}
			// ##### 添付ファイルアップロード結果を返す #####
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// 添付ファイルの登録データを返す
			if (function_exists('json_encode')){
				$destStr = json_encode($resultObj);
			} else {
				$destStr = $this->gInstance->getAjaxManager()->createJsonString($resultObj);
			}
			//$destStr = htmlspecialchars($destStr, ENT_NOQUOTES);// 「&」が「&amp;」に変換されるので使用しない
			//header('Content-type: application/json; charset=utf-8');
			header('Content-Type: text/html; charset=UTF-8');		// JSONタイプを指定するとIE8で動作しないのでHTMLタイプを指定
			echo $destStr;
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else if ($act == 'getimage'){			// 画像取得
			//$fileId = $request->trimValueOf('fileid');	// ファイルID
		//	$this->getImage($fileId);
			$this->getImageByType($type);
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
		$uploadUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIGIMAGE;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
		$this->tmpl->addVar("_widget", "upload_url_sitelogo", $this->getUrl($uploadUrl . '&type=' . self::IMAGE_TYPE_SITE_LOGO));		// サイトロゴ用
		$this->tmpl->addVar("_widget", "upload_url_useravatar", $this->getUrl($uploadUrl . '&type=' . self::IMAGE_TYPE_USER_AVATAR));		// アバター用
		
		// サイトロゴ
		$siteLogoSizeArray = $this->gInstance->getImageManager()->getAllSiteLogoSizeId();
		if (!empty($siteLogoSizeArray)){
			$size = $siteLogoSizeArray[count($siteLogoSizeArray) -1];		// 最大画像
			$siteLogoFilename = $this->gInstance->getImageManager()->getSiteLogoFilename($size);
			$siteLogoUrl = $this->gInstance->getImageManager()->getSiteLogoUrl($size) . '?' . date('YmdHis');		// サイトロゴファイル名
		}
		$this->tmpl->addVar("_widget", "sitelogo_url", $this->convertUrlToHtmlEntity($this->getUrl($siteLogoUrl)));
		
		// アバター
		$avatarSizeArray = $this->gInstance->getImageManager()->getAllAvatarSizeId();
		if (!empty($avatarSizeArray)){
			$size = $avatarSizeArray[count($avatarSizeArray) -1];		// 最大画像
			$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl(''/*デフォルトアバター*/, $size) . '?' . date('YmdHis');		// サイトロゴファイル名
		}
		$this->tmpl->addVar("_widget", "useravatar_url", $this->convertUrlToHtmlEntity($this->getUrl($avatarUrl)));
	}
	/**
	 * 画像を取得
	 *
	 * @param string $type		画像タイプ
	 * @return					なし
	 */
	function getImageByType($type)
	{
		// 画像パス
//		$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
		//$imagePath = $this->gEnv->getTempDirBySession() . '/' . $fileId;		// セッション単位の作業ディレクトリを取得
		
		switch ($type){
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
		}
		
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
	/**
	 * 画像を取得
	 *
	 * @param string $fileId		画像ファイルID
	 * @return						なし
	 */
	function getImage($fileId)
	{
		// 画像パス
		$imagePath = $this->gEnv->getTempDirBySession() . '/' . $fileId;		// セッション単位の作業ディレクトリを取得
			
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
