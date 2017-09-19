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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainProductDb.php');

class admin_ec_mainImageWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト

	const TITLE_MOVE_RIGHT = '右の画像に変更';
	const MOVE_RIGHT_ICON_FILE = '/images/system/move_right64.png';			// 画像変更表示用アイコン
	const CREATE_EYECATCH_TAG_ID = 'createeyecatch';			// アイキャッチ画像作成ボタンタグID
	const ACT_DELETE_EYECATCH = 'deleteeyecatch';				// アイキャッチ画像を削除
	const ACT_CREATE_IMAGE	= 'createimage';	// 画像作成
	const ACT_GET_IMAGE		= 'getimage';		// 画像取得
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_mainProductDb();
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
		return 'admin_image.tmpl.html';
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
		$langId	= $this->gEnv->getDefaultLanguage();		// デフォルト言語を取得
		$act = $request->trimValueOf('act');
		$productId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);
		$act = $request->trimValueOf('act');
		$eyecatchSrc = $request->trimValueOf('eyecatch_src');		// 更新画像ソース
		
		$reloadData = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 項目更新の場合
			// 作業ディレクトリを取得
			$tmpDir = $this->gEnv->getTempDirBySession();
			
			// 画像ファイル名、フォーマット取得
			list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($productId, 1/*クロップ画像のみ*/);
	
			// サムネール画像の存在をチェック
			for ($i = 0; $i < count($filenames); $i++){
				$path = $tmpDir . DIRECTORY_SEPARATOR . $filenames[$i];
				if (!file_exists($path)){
					$this->setAppErrorMsg('画像が作成されていません');
					
					// 作業ディレクトリを削除
					rmDirectory($tmpDir);
					break;
				}
			}
			// サムネール作成用の元の画像の存在をチェック
			$eyecatchSrcPath = $this->gEnv->getAbsolutePath($this->gEnv->getDocumentRootUrl() . $eyecatchSrc);
			if (!file_exists($eyecatchSrcPath)) $this->setAppErrorMsg('サムネール作成元の画像が見つかりません');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// アイキャッチ画像を非公開ディレクトリに保存
				$privateThumbDir = $this->gInstance->getImageManager()->getSystemPrivateThumbPath(M3_VIEW_TYPE_PRODUCT, photo_shopCommonDef::$_deviceType);
				$ret = mvFileToDir($tmpDir, $filenames, $privateThumbDir);
				
				// 画像を公開ディレクトリにコピー
				$publicThumbDir = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_PRODUCT, photo_shopCommonDef::$_deviceType);
				if ($ret) $ret = cpFileToDir($privateThumbDir, $filenames, $publicThumbDir);

				// サムネール作成元画像のパスをresourceディレクトリからの相対パスに変換
				$eyecatchSrcPath = str_replace($this->gEnv->getResourcePath(), '', $eyecatchSrcPath);
				
				// 製品情報のサムネールファイル名を更新
				$thumbFilename = implode(';', $filenames);
				if ($ret) $ret = self::$_mainDb->updateThumbFilename($productId, $langId, $thumbFilename, $eyecatchSrcPath);
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$reloadData = true;		// データを再取得
				
					// 作業ディレクトリを削除
					rmDirectory($tmpDir);
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == self::ACT_DELETE_EYECATCH){		// アイキャッチ画像を削除
			// 作業ディレクトリを取得
			$tmpDir = $this->gEnv->getTempDirBySession();
			
			// 画像ファイル名、フォーマット取得
			list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($productId, 1/*クロップ画像のみ*/);
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 公開ディレクトリ、非公開ディレクトリの画像を削除
				$publicThumbDir = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_PRODUCT, photo_shopCommonDef::$_deviceType);
				$privateThumbDir = $this->gInstance->getImageManager()->getSystemPrivateThumbPath(M3_VIEW_TYPE_PRODUCT, photo_shopCommonDef::$_deviceType);
				for ($i = 0; $i < count($filenames); $i++){
					$publicThumbPath = $publicThumbDir . DIRECTORY_SEPARATOR . $filenames[$i];
					$privateThumbPath = $privateThumbDir . DIRECTORY_SEPARATOR . $filenames[$i];
					if (file_exists($publicThumbPath)) @unlink($publicThumbPath);
					if (file_exists($privateThumbPath)) @unlink($privateThumbPath);
				}
			
				// 作業ディレクトリを削除
				rmDirectory($tmpDir);
					
				// 製品情報のサムネールファイル名を更新
				$ret = self::$_mainDb->updateThumbFilename($productId, $langId, '', ''/*サムネール作成元画像のパス*/);
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'アイキャッチ画像を削除しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'アイキャッチ画像削除に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == self::ACT_CREATE_IMAGE){		// 画像作成
			$this->createCropImage($request);
			return;
		} else if ($act == self::ACT_GET_IMAGE){			// 画像取得
			// Ajaxでの画像取得
			$this->getImageByType($request);
			return;
		} else {
			$reloadData = true;		// データを再取得
			
			// 作業ディレクトリを削除
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			rmDirectory($tmpDir);
		}

		$ret = $this->db->getProductByProductId($productId, $langId, $row, $row2, $row3, $row4, $row5);
		if ($ret){
//			$html		= $row['be_html'];				// HTML
//			$html2		= $row['be_html_ext'];			// HTML続き
		
			// ### 現在設定されているアイキャッチ画像 ###
			// 最大サイズのアイキャッチ画像を取得。公開ディレクトリになければデフォルト画像を表示。
			$eyecatchUrl = photo_shopCommonDef::getEyecatchImageUrl($row['pt_thumb_filename'], self::$_configArray[photo_shopCommonDef::CF_E_PRODUCT_DEFAULT_IMAGE]);
			
			// ### 置き換え用アイキャッチ画像 ###
			// 画像ファイル名、フォーマット取得
			list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($productId, 10/*アイキャッチ画像*/);
	
			$imagePath = '';
			$filename = $filenames[0];
			if (!empty($filename)) $imagePath = $this->gEnv->getTempDirBySession() . '/' . $filename;	// 一時ディレクトリ
			if (is_readable($imagePath)){	// 置き換え用アイキャッチ画像がある場合
				// 置き換え用アイキャッチ画像URL
				$imageUrl = $this->getEyecatchUrl($productId);
				
				$titleStr = self::TITLE_MOVE_RIGHT;
				$iconUrl = $this->gEnv->getRootUrl() . self::MOVE_RIGHT_ICON_FILE;		// 右の画像に変更アイコン

				$eyecatchImagTag = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $titleStr . '" title="' . $titleStr . '" rel="m3help" />';
				$eyecatchImagTag .= '<img src="' . $this->getUrl($imageUrl) . '" />';
			}
			
			// 記事内でアイキャッチ画像に使用した画像を取得
			$originalEyecatchUrl = '';
			$privateThumbDir = $this->gInstance->getImageManager()->getSystemPrivateThumbPath(M3_VIEW_TYPE_PRODUCT, photo_shopCommonDef::$_deviceType);
			$imagePath = $privateThumbDir . '/' . $filename;
			if (!empty($row['pt_thumb_filename']) && !file_exists($imagePath)){// 画像が作成されていて、非公開ディレクトリに画像がない場合
				// アイキャッチを作成したソース画像を取得
				$originalEyecatchPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
				if (empty($originalEyecatchPath) && !empty($html2)) $originalEyecatchPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
				if (!empty($originalEyecatchPath)) $originalEyecatchUrl = $this->gEnv->getUrlToPath($originalEyecatchPath);		// URLに変換
			}
			
			// アイキャッチ画像削除用ボタンを表示
			if (!empty($row['pt_thumb_filename'])) $this->tmpl->setAttribute('delete_eyecatch_button', 'visibility', 'visible');
		}
		// Ajax用URL
//		if ($this->gEnv->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			$ajaxUrl = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . '&widget=' . $this->gEnv->getCurrentWidgetId();
//		} else {
//			$ajaxUrl = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&widget=' . $this->gEnv->getCurrentWidgetId() . '&openby=other&blogid=' . $this->_blogId;
//		}
		$this->tmpl->addVar("_widget", "ajax_url", $ajaxUrl);
		
		// アイキャッチ画像の情報を取得
		$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(10/*アイキャッチ画像*/);
		$ret = $this->gInstance->getImageManager()->parseImageFormat($formats[0], $imageType, $imageAttr, $imageSize);

		// アイキャッチ画像変更ボタン
		$createEyecatchButton = $this->gDesign->createEditButton(''/*同画面*/, '画像を作成', self::CREATE_EYECATCH_TAG_ID);
		$this->tmpl->addVar("_widget", "create_eyecatch_button", $createEyecatchButton);
		$this->tmpl->addVar("_widget", "tagid_create_eyecatch", self::CREATE_EYECATCH_TAG_ID);		// 画像作成タグ
		
		// 画像変更表示用アイコン
		$titleStr = self::TITLE_MOVE_RIGHT;
		$iconUrl = $this->gEnv->getRootUrl() . self::MOVE_RIGHT_ICON_FILE;		// 右の画像に変更アイコン
		$moveIconTag = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $titleStr . '" title="' . $titleStr . '" rel="m3help" />';
				
		$this->tmpl->addVar("_widget", "eyecatch_url", $this->convertUrlToHtmlEntity($this->getUrl($eyecatchUrl . '?' . date('YmdHis'))));
		$this->tmpl->addVar("_widget", "eyecatch_new_image", $eyecatchImagTag);			// 置き換え用アイキャッチ画像
		$this->tmpl->addVar("_widget", "move_icon_tag", $moveIconTag);			// 画像変更表示用アイコン
		$this->tmpl->addVar("_widget", "original_eyecatch_url", $this->convertUrlToHtmlEntity($this->getUrl($originalEyecatchUrl)));		// アイキャッチ画像の元の画像
		$this->tmpl->addVar("_widget", "eyecatch_size", $imageSize . 'x' . $imageSize);
		$this->tmpl->addVar("_widget", "product_id", $this->convertToDispString($productId));
	}
	/**
	 * クロップ画像を作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createCropImage($request)
	{
		$productId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);
		$type = $request->trimValueOf('type');		// 画像タイプ
		$src = $request->trimValueOf('src');	// 画像URL
		$x = $request->trimValueOf('x');
		$y = $request->trimValueOf('y');
		$w = $request->trimValueOf('w');
		$h = $request->trimValueOf('h');
		
		// 引数エラーチェック
		$productId = intval($productId);
		if (empty($productId)) return false;
		
		// 作業ディレクトリを作成
		$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
		
		// ソース画像パスを取得
		$srcPath = $this->gEnv->getAbsolutePath($this->gEnv->getDocumentRootUrl() . $src);
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($productId, 1/*クロップ画像のみ*/);

		// クロップ画像を作成
		for ($i = 0; $i < count($formats); $i++){
			$format = $formats[$i];

			// フォーマット情報を取得
			$ret = $this->gInstance->getImageManager()->parseImageFormat($format, $imageType, $imageAttr, $imageSize, $imageWidthHeight);
			if ($ret){
				// 画像作成
				$destPath = $tmpDir . DIRECTORY_SEPARATOR . $filenames[$i];
				$ret = $this->gInstance->getImageManager()->createCropImage($srcPath, $x, $y, $w, $h, $destPath, $imageWidthHeight['width'], $imageWidthHeight['height']);
				if (!$ret) return false;
			} else {
				break;
			}
		}
		// 画像参照用URL
		$imageUrl = $this->getEyecatchUrl($productId);
		$this->gInstance->getAjaxManager()->addData('url', $this->getUrl($imageUrl));
	}
	/**
	 * 最大画像を取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return					なし
	 */
	function getImageByType($request)
	{
		$productId = $request->trimValueOf(M3_REQUEST_PARAM_PRODUCT_ID);
		$type = $request->trimValueOf('type');		// 画像タイプ
		
		// 作業ディレクトリを取得
		$tmpDir = $this->gEnv->getTempDirBySession();
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemThumbFilename($productId, 10/*アイキャッチ画像画像*/);
		
		$imagePath = '';
		$filename = $filenames[0];
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
	 * 置き換え用アイキャッチ画像のURLを取得
	 *
	 * @param string $productId	記事ID
	 * @return string			画像のURL
	 */
	function getEyecatchUrl($productId)
	{
//		if ($this->gEnv->isAdminDirAccess()){		// 管理画面へのアクセスのとき
			$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
			$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
//		} else {
//			$imageUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;	// ウィジェット直接実行
//			$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
//			$imageUrl .= '&openby=other&blogid=' . $this->_blogId;
//		}
		$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_IMAGE;
		$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
		$imageUrl .= '&' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $productId;
//		$imageUrl .= '&type=' . $type;
		$imageUrl .= '&' . date('YmdHis');
		return $imageUrl;
	}
}
?>
