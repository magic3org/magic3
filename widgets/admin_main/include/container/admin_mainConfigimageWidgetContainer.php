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
//require_once($gEnvManager->getCommonPath()				. '/uploadFile.php' );			// ファイルアップロード受信ライブラリ

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
		$updatedSiteLogo = $request->trimValueOf('sitelogo_updated');		// サイトロゴ更新フラグ
		$updatedUserAvatar = $request->trimValueOf('useravatar_updated');	// アバター更新フラグ

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			
			// サイトロゴのエラーチェック
			if (!empty($updatedSiteLogo)){
				$siteLogoFilenameArray = $this->gInstance->getImageManager()->getSiteLogoFilename();
				for ($i = 0; $i < count($siteLogoFilenameArray); $i++){
					$path = $tmpDir . DIRECTORY_SEPARATOR . $siteLogoFilenameArray[$i];
					if (!file_exists($path)){
						$this->setAppErrorMsg('サイトロゴが正常にアップロードされていません');
						break;
					}
				}
			}
			// アバターのエラーチェック
			if (!empty($updatedUserAvatar)){
				$userAvatarFilenameArray = $this->gInstance->getImageManager()->getDefaultAvatarFilename();
				for ($i = 0; $i < count($userAvatarFilenameArray); $i++){
					$path = $tmpDir . DIRECTORY_SEPARATOR . $userAvatarFilenameArray[$i];
					if (!file_exists($path)){
						$this->setAppErrorMsg('ユーザアバターが正常にアップロードされていません');
						break;
					}
				}
			}
			
			// 画像の更新
			if ($this->getMsgCount() == 0){		// エラーなしの場合は画像をコピー
				// サイトロゴ
				$ret = mvFileToDir($tmpDir, $siteLogoFilenameArray, $this->gInstance->getImageManager()->getSiteLogoPath());
				
				// アバター
				if ($ret) $ret = mvFileToDir($tmpDir, $userAvatarFilenameArray, $this->gInstance->getImageManager()->getAvatarPath());
				
				if (!$ret) $this->setAppErrorMsg('画像の更新に失敗しました');
			}
			
			if ($this->getMsgCount() == 0){		// エラーなしの場合
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				
				$replaceNew = true;		// データを再取得
				
				// 作業ディレクトリを削除
				rmDirectory($tmpDir);
			}
		} else if ($act == 'uploadimage'){		// 画像アップロード
			// 作業ディレクトリを作成
			$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $tmpDir);
		} else if ($act == 'getimage'){			// 画像取得
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
		
		// ##### 画像の表示 #####
		// アップロードされているファイルがある場合は、アップロード画像を表示
		$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
		
		// サイトロゴ
		$imageUrl = '';
		$updateStatus = '0';
		$siteLogoSizeArray = $this->gInstance->getImageManager()->getAllSiteLogoSizeId();
		$filenameArray = $this->gInstance->getImageManager()->getSiteLogoFilename();
		for ($i = 0; $i < count($filenameArray); $i++){
			$path = $tmpDir . DIRECTORY_SEPARATOR . $filenameArray[$i];
			if (!file_exists($path)) break;
		}
		if ($i == count($filenameArray)){		// 画像が存在する場合
			// 画像参照用URL
			$imageUrl = $this->gEnv->getDefaultAdminUrl();
			$imageUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIGIMAGE;
			$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
			$imageUrl .= '&type=' . self::IMAGE_TYPE_SITE_LOGO;		// サイトロゴ
			$updateStatus = '1';
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
		$this->tmpl->addVar("_widget", "sitelogo_updated", $updateStatus);
		$this->tmpl->addVar("_widget", "sitelogo_size", $imageSize . 'x' . $imageSize);
		
		// アバター
		$imageUrl = '';
		$updateStatus = '0';
		$avatarSizeArray = $this->gInstance->getImageManager()->getAllAvatarSizeId();
		$filenameArray = $this->gInstance->getImageManager()->getDefaultAvatarFilename();
		for ($i = 0; $i < count($filenameArray); $i++){
			$path = $tmpDir . DIRECTORY_SEPARATOR . $filenameArray[$i];
			if (!file_exists($path)) break;
		}
		if ($i == count($filenameArray)){		// 画像が存在する場合
			// 画像参照用URL
			$imageUrl = $this->gEnv->getDefaultAdminUrl();
			$imageUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIGIMAGE;
			$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
			$imageUrl .= '&type=' . self::IMAGE_TYPE_USER_AVATAR;		// アバター
			$updateStatus = '1';
		} else {
			if (!empty($avatarSizeArray)){
				$sizeId = $avatarSizeArray[count($avatarSizeArray) -1];		// 最大画像
				$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl(''/*デフォルトアバター*/, $sizeId) . '?' . date('YmdHis');		// サイトロゴファイル名
			}
		}
		// アバターサイズ取得
		if (!empty($avatarSizeArray)){
			$sizeId = $avatarSizeArray[count($avatarSizeArray) -1];		// 最大画像
			$this->gInstance->getImageManager()->getAvatarFormatInfo($sizeId, $imageType, $imageAttr, $imageSize);
		}
		
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());
		$this->tmpl->addVar("_widget", "useravatar_url", $this->convertUrlToHtmlEntity($this->getUrl($avatarUrl)));
		$this->tmpl->addVar("_widget", "useravatar_updated", $updateStatus);
		$this->tmpl->addVar("_widget", "useravatar_size", $imageSize . 'x' . $imageSize);
	}
	/**
	 * 最大画像を取得
	 *
	 * @param string $type		画像タイプ
	 * @return					なし
	 */
	function getImageByType($type)
	{
		// 画像パス作成
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
		$type = $request->trimValueOf('type');		// 画像タイプ
		
		if ($isSuccess){		// ファイルアップロード成功のとき
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
			
			$ret = $this->gInstance->getImageManager()->createImageByFormat($filePath, $formats, $destDir, $filenameBase, $destFilename);
			if ($ret){			// 画像作成成功の場合
				// 画像参照用URL
				$imageUrl = $this->gEnv->getDefaultAdminUrl();
				$imageUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIGIMAGE;
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getimage';
				$imageUrl .= '&type=' . $type . '&' . date('YmdHis');
				$resultObj['url'] = $imageUrl;
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
}
?>
