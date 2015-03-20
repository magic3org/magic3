<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    パンくずリスト
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2012-2015 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_breadcrumbWidgetContainer extends BaseAdminWidgetContainer
{
	private $tmpDir;		// 作業ディレクトリ
	const IMAGE_TYPE_SEPARATOR = 'separator';	// 画像タイプ(区切り画像)
	const ACT_UPLOAD_IMAGE	= 'uploadimage';				// 画像アップロード
	const ACT_GET_IMAGE		= 'getimage';					// 画像取得
	const DEFAULT_ARROW_IMAGE_FILE = '/images/arrow.png';	// デフォルト区切り画像
	const IMAGE_FILENAME_BASE = 'arrow';					// 画像ファイル名ベース
	const TMP_IMAGE_FILENAME = 'tmpimgage';					// 一時画像ファイル名
	
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
		return 'admin.tmpl.html';
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
		
		// 入力値を取得
		$visibleOnRoot = $request->trimCheckedValueOf('item_visible_on_root');		// トップページでリスト表示するかどうか
		$useHiddenMenu = $request->trimCheckedValueOf('item_use_hidden_menu');		// 非表示のメニューウィジェットの定義を使用する
		$updatedSeparator = $request->trimValueOf('separator_updated');				// 区切り画像更新フラグ
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			
			// 区切り画像のエラーチェック
			if (!empty($updatedSeparator)){
				$tmpImagePath = $tmpDir . '/' . self::TMP_IMAGE_FILENAME;
				if (!file_exists($tmpImagePath)) $this->setAppErrorMsg('区切り画像が正常にアップロードされていません');
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 画像情報取得
				$imageMimeType = '';
				$imageSize = @getimagesize($tmpImagePath);
		
				// 拡張子
				$ext = '';
				switch ($imageSize[2]){
					case IMAGETYPE_JPEG:
						$ext = 'jpg';
						break;
					case IMAGETYPE_GIF:
						$ext = 'gif';
						break;
					case IMAGETYPE_PNG:
						$ext = 'png';
						break;
					case IMAGETYPE_BMP:
						$ext = 'bmp';
						break;
				}
		
				// 画像ファイル名作成
				$newImgPath = '/widgets/' . $this->gEnv->getCurrentWidgetId() . '/images/' . self::IMAGE_FILENAME_BASE . '.' . $ext;

				// 画像を移動
				$ret = mvFile($tmpImagePath, $this->gEnv->getResourcePath() . $newImgPath);
			
				$paramObj = new stdClass;
				$paramObj->visibleOnRoot	= $visibleOnRoot;
				$paramObj->useHiddenMenu	= $useHiddenMenu;
				$paramObj->separatorImgPath	= $newImgPath;		// 区切り画像パス
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;		// データの再ロード
					
					// 作業ディレクトリを削除
					rmDirectory($this->tmpDir);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == self::ACT_UPLOAD_IMAGE){		// 画像アップロード
			// 作業ディレクトリを作成
			$this->tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $this->tmpDir);
		} else if ($act == self::ACT_GET_IMAGE){			// 画像取得
			// Ajaxでの画像取得
			$this->getImageByType(self::IMAGE_TYPE_SEPARATOR);
/*		} else if ($act == 'upload'){		// 画像アップロードのとき
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				// テンポラリディレクトリの書き込み権限をチェック
				if (!is_writable($this->gEnv->getWorkDirPath())){
					$msg = '一時ディレクトリに書き込み権限がありません。ディレクトリ：' . $this->gEnv->getWorkDirPath();
					$this->setAppErrorMsg($msg);
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
		
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// ファイルの内容のチェック
						$imageSize = @getimagesize($tmpFile);// 画像情報を取得
						if ($imageSize){
							$imageWidth = $imageSize[0];
							$imageHeight = $imageSize[1];
							$imageType = $imageSize[2];
							$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得

							// 受付可能なファイルタイプかどうか
							if (!in_array($imageMimeType, $this->permitMimeType)){
								$msg = 'アップロード画像のタイプが不正です。';
								$this->setAppErrorMsg($msg);
							}
						} else {
							$msg = 'アップロード画像が不正です。';
							$this->setAppErrorMsg($msg);
						}
				
						if ($this->getMsgCount() == 0){		// エラーが発生していないとき
							// 画像をコピー
							$newImgPath = $this->gEnv->getResourcePath() . DIRECTORY_SEPARATOR . 'widgets/' . $this->gEnv->getCurrentWidgetId() . self::DEFAULT_ARROW_IMAGE_FILE;
							$newImgDir = dirname($newImgPath);
							if (!file_exists($newImgDir)) mkdir($newImgDir, M3_SYSTEM_DIR_PERMISSION, true);		// ディレクトリ作成
							$ret = copy($tmpFile, $newImgPath);
							if ($ret){
								$separatorImgPath = str_replace($this->gEnv->getSystemRootPath(), '', $newImgPath);		// 区切り画像パス
								
								$msg = '画像を変更しました';
								$this->setGuidanceMsg($msg);
							}
						}
					} else {
						$msg = 'ファイルのアップロードに失敗しました';
						$this->setAppErrorMsg($msg);
					}
					// テンポラリファイル削除
					unlink($tmpFile);
				}
			} else {
				$msg = sprintf($this->_('Uploded file not found. (detail: The file may be over maximum size to be allowed to upload. Size %s bytes.'), $this->gSystem->getMaxFileSizeForUpload());	// アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。%dバイト)
				$this->setAppErrorMsg($msg);
			}*/
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再ロード
			
			// 作業ディレクトリを削除
			rmDirectory($this->tmpDir);
		}
		// データ再取得
		if ($reloadData){
			// デフォルト値設定
			$visibleOnRoot = 1;				// トップページでリスト表示するかどうか
			$useHiddenMenu = 0;		// 非表示のメニューウィジェットの定義を使用する
			$separatorImgPath = '';		// 区切り画像パス
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$visibleOnRoot	= $paramObj->visibleOnRoot;
				$useHiddenMenu	= $paramObj->useHiddenMenu;
				$separatorImgPath = $paramObj->separatorImgPath;		// 区切り画像パス
			}
		}

		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->_widgetId;	// ウィジェットID
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_UPLOAD_IMAGE;
		$this->tmpl->addVar("_widget", "upload_url_separator", $this->getUrl($uploadUrl . '&type=' . self::IMAGE_TYPE_SEPARATOR));
		
		// 区切り画像
		if (empty($separatorImgPath)){
			// 一時画像ファイルがある場合は取得
			$tmpImagePath = $this->tmpDir . '/' . self::TMP_IMAGE_FILENAME;
			if (file_exists($tmpImagePath)){
				$separatorImgUrl = $this->getTmpImageUrl(self::IMAGE_TYPE_SEPARATOR);
			} else {
				$separatorImgUrl = $this->gEnv->getCurrentWidgetRootUrl() . self::DEFAULT_ARROW_IMAGE_FILE . '?' . date('YmdHis');		// デフォルトの画像
			}
		} else {
			$separatorImgUrl = $this->gEnv->getResourceUrl() . $separatorImgPath . '?' . date('YmdHis');		// ユーザ指定の画像
		}
		$this->tmpl->addVar("_widget", "separator_url", $this->convertUrlToHtmlEntity($this->getUrl($separatorImgUrl)));
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "visible_on_root", $this->convertToCheckedString($visibleOnRoot));
		$this->tmpl->addVar("_widget", "use_hidden_menu", $this->convertToCheckedString($useHiddenMenu));
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());		// 画像アップロードエリア
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		case self::IMAGE_TYPE_SEPARATOR:			// 画像タイプ(区切り画像)
			$imagePath = $this->gEnv->getTempDirBySession() . '/' . self::TMP_IMAGE_FILENAME;
			break;
		}
			
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
			case self::IMAGE_TYPE_SEPARATOR:			// 画像タイプ(区切り画像)
				break;
			}

			// 一時区切り画像ファイル
			$newImgPath = $this->tmpDir . '/' . self::TMP_IMAGE_FILENAME;

			// 画像をコピー
			$ret = copy($filePath, $newImgPath);
			if ($ret){			// 画像作成成功の場合
				$resultObj['url'] = $this->getTmpImageUrl($type);
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
	/**
	 * 一時区切り画像ファイルのURLを取得
	 *
	 * @param string $type	画像タイプ
	 * @return string		画像URL
	 */
	function getTmpImageUrl($type)
	{
		$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->_widgetId;	// ウィジェットID
		$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
		$imageUrl .= '&type=' . $type . '&' . date('YmdHis');
		return $imageUrl;
	}
}
?>
