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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainTempimageWidgetContainer extends admin_mainBaseWidgetContainer
{
//	private $serialArray = array();		// 表示されているファイルのインデックス番号
	private $fileArray = array();		// ファイル名リスト
	private $imageBasePath;				// 画像格納ディレクトリ
	private $cacheDir;					// サムネール画像用キャッシュディレクトリ
	private $sortOrderByDateAsc;		// 日付でソート
	private $permitMimeType;			// アップロードを許可する画像タイプ
	private $templateId;				// テンプレートID
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 10;			// リンクページ数
	const FILE_ICON_FILE = '/images/system/tree/file_inactive32.png';			// ファイルアイコン
	const FOLDER_ICON_FILE = '/images/system/tree/folder32.png';		// ディレクトリアイコン
	const PARENT_ICON_FILE = '/images/system/tree/parent32.png';		// 親ディレクトリアイコン
	const ICON_SIZE = 32;		// ディレクトリアイコンのサイズ
	const LIST_ICON_SIZE = 128;		// サムネール画像のサイズ
	const DEFAULT_IMAGE_DIR = '/images';		// デフォルトの画像格納ディレクトリ
	const CACHE_DIR = '/.cache/images';			// 画像キャッシュディレクトリ
//	const DEFAULT_THUMBNAIL_SIZE = 128;		// サムネール画像サイズ
	const IMAGE_QUALITY = 100;			// 生成画像の品質(0～100)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 画像タイプ
		$this->permitMimeType = array(	image_type_to_mime_type(IMAGETYPE_GIF),
										image_type_to_mime_type(IMAGETYPE_JPEG),
										image_type_to_mime_type(IMAGETYPE_PNG),
										image_type_to_mime_type(IMAGETYPE_BMP));			// アップロードを許可する画像タイプ
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
		$task = $request->trimValueOf('task');
		if ($task == 'tempimage_detail'){		// 詳細画面
			return 'tempimage_detail.tmpl.html';
		} else {			// 一覧画面
			return 'tempimage.tmpl.html';
		}
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
		$task = $request->trimValueOf('task');
		$this->templateId = $request->trimValueOf(M3_REQUEST_PARAM_TEMPLATE_ID);		// テンプレートIDを取得

		// パラメータチェック
		if (empty($this->templateId)){
			$this->setAppErrorMsg('テンプレートIDが設定されていません');
			return;
		}
		
		// 画像キャッシュディレクトリ作成
		$this->cacheDir = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . self::CACHE_DIR;
		if (!is_dir($this->cacheDir)){
			if (!mkdir($this->cacheDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/)) $this->setAppErrorMsg('キャッシュディレクトリが作成できません');
		}
			
		switch ($task){
			case 'tempimage':
			default:
				$this->createList($request);
				break;
			case 'tempimage_detail':
				$this->createDetail($request);
				break;
			case 'tempimage_direct':
				if ($act == 'getimage'){		// 画像取得
					$width = $request->trimValueOf('width');
					$height = $request->trimValueOf('height');
					$photoId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID);			// 画像ID
//					photo_mainCommonDef::getImage($photoId, self::$_mainDb, $width, $height);
				}
				break;
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// 画像格納ディレクトリを取得
		$this->imageBasePath = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . self::DEFAULT_IMAGE_DIR;
		if (!is_dir($this->imageBasePath)){
			$this->setAppErrorMsg('画像ディレクトリが見つかりません。パス=' . $this->imageBasePath);
			return;
		}

		// パスを取得
		$path = trim($request->valueOf('path'));		// 現在のパス
		if (empty($path)){
			$path = $this->imageBasePath;
		} else {
			if (!strStartsWith($path, '/')) $path = '/' . $path;
			$path = $this->imageBasePath . $path;
		}
		// パスのエラーチェック
		if (isDescendantPath($this->imageBasePath, $path)){
			if (!is_dir($path)){
				return;
			}
		} else {
			return;
		}

		// ページ番号
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		$act = $request->trimValueOf('act');
		if ($act == 'uploadimage'){		// 画像アップロード
			$this->path = $path;

			// 作業ディレクトリを作成
			$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $tmpDir);
			
			// 作業ディレクトリ削除
			rmDirectory($tmpDir);
		} else if ($act == 'delete'){			// ファイル削除のとき
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delFiles = array();	// ファイル名
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					// 削除可能かチェック
					$filename = $request->trimValueOf('item' . $i . '_name');
					$filePath = $path . DIRECTORY_SEPARATOR . $filename;
					if (is_writable($filePath) && strStartsWith($filePath, $this->imageBasePath . DIRECTORY_SEPARATOR)){
						$delFiles[] = $filePath;		// 削除するファイルのパス
					} else {
						$this->setMsg(self::MSG_USER_ERR, sprintf($this->_('Include files not allowed to delete. (filename: %s)'), $this->convertToDispString($filename)));		// 削除できないファイルが含まれています。(ファイル名： %s)
						break;
					}
				}
			}
			if ($this->getMsgCount() == 0 && count($delFiles) > 0){

				// ファイル、ディレクトリ削除
				$imageFiles = array();
				for ($i = 0; $i < count($delFiles); $i++){
					$file = $delFiles[$i];
					if (is_dir($file)){
						// ファイル、ディレクトリがある場合は削除しない
						$files = getFileList($file);
						if (count($files) == 0){
							if (!rmDirectory($file)) $ret = false;
						} else {
							$ret = false;
						}
					} else {
						if (!@unlink($file)) $ret = false;
						$imageFiles[] = $file;
					}
				}
				// サムネール画像を削除
				$ret = $this->deleteCacheImages($imageFiles);

				if ($ret){		// ファイル削除成功のとき
					$this->setGuidanceMsg($this->_('Files deleted.'));			// ファイルを削除しました
				} else {
					$this->setAppErrorMsg($this->_('Failed in deleting files.'));			// ファイル削除に失敗しました
				}
			}
		} else if ($act == 'createdir'){			// ディレクトリ作成のとき
			$dirName = $request->trimValueOf('directory_name');
			$this->checkSingleByte($dirName, $this->_('Directory Name'));		// ディレクトリ名
			
			if ($this->getMsgCount() == 0){
				$dirPath = $path . DIRECTORY_SEPARATOR . $dirName;
				$ret = @mkdir($dirPath, M3_SYSTEM_DIR_PERMISSION);
				if ($ret){		// ファイル削除成功のとき
					$this->setGuidanceMsg($this->_('Directory created.'));			// ディレクトリを作成しました
					$dirName = '';
				} else {
					$this->setAppErrorMsg($this->_('Failed in creating directory.'));			// ディレクトリ作成に失敗しました
				}
			}
		}
		
		// カレントディレクトリのパスを作成
		$photoParentPath = dirname($this->imageBasePath);
		$pathLink = '';
		$relativePath = substr($path, strlen($photoParentPath));
		$relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
		if (!empty($relativePath)){
			$absPath = $photoParentPath;
			$pathArray = explode(DIRECTORY_SEPARATOR, $relativePath);
			for ($i = 0; $i < count($pathArray); $i++){
				if ($i == count($pathArray) -1){
					$pathLink .= '&nbsp;' . DIRECTORY_SEPARATOR . '&nbsp;' . $this->convertToDispString($pathArray[$i]);
				} else {
					$absPath .= DIRECTORY_SEPARATOR . $pathArray[$i];
					$relativeFilePath = substr($absPath, strlen($this->imageBasePath));
					$pathLink .= '&nbsp;' . DIRECTORY_SEPARATOR . '&nbsp;';
			//		$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativeFilePath;
					$pageUrl = '?task=' . self::TASK_TEMPIMAGE . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $relativeFilePath;
					$pathLink .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">' . $this->convertToDispString($pathArray[$i]) . '</a>';
				}
			}
		}
		
		// 総数を取得
		$fileList = $this->getFileList($path);
		$totalCount = count($fileList);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		$startNo = ($pageNo -1) * $maxListCount +1;		// 先頭の行番号
		$endNo = $pageNo * $maxListCount > $totalCount ? $totalCount : $pageNo * $maxListCount;// 最後の行番号
		
		// ページングリンク作成
		$relativePath = substr($path, strlen($this->imageBasePath));
	//	$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativePath;
		$pageUrl = '?task=' . self::TASK_TEMPIMAGE . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $relativePath;
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $pageUrl);

		$this->tmpl->addVar("_widget", "page_link", $pageLink);
//		$this->tmpl->addVar("_widget", "total_count", sprintf($this->_('%d Total'), $totalCount));// 全 x件
//		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
//		$this->tmpl->addVar("search_range", "start_no", $startNo);
//		$this->tmpl->addVar("search_range", "end_no", $endNo);
//		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// ファイル一覧を作成
		$this->createFileList($path, $fileList, $startNo, $endNo);
		
		$this->tmpl->addVar('_widget', 'template', $this->convertToDispString($this->templateId));		// テンプレート名
		$this->tmpl->addVar('_widget', 'path', substr($path, strlen($this->imageBasePath)));// 現在のディレクトリ
		$this->tmpl->addVar('_widget', 'path_link', $pathLink);// 現在のディレクトリ
//		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "file_list", implode($this->fileArray, ','));// ファイル名のリストを設定
		$this->tmpl->addVar('_widget', 'directory_name', $this->convertToDispString($dirName));// ディレクトリ作成用
		
		// アップロード実行用URL
//		$param = M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_TEMPIMAGE;
//		$param .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
//		$param .= '&path=' . $this->adaptWindowsPath(substr($path, strlen($this->imageBasePath)));					// アップロードディレクトリ
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_TEMPIMAGE;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
		$uploadUrl .= '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $this->adaptWindowsPath(substr($path, strlen($this->imageBasePath)));
		$this->tmpl->addVar("_widget", "upload_image_url", $this->getUrl($uploadUrl));
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());		// 画像アップロードエリア作成
	
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_file_upload'] = $this->_('Files uploaded. Refresh file list?');		// アップロード終了しました。ファイル一覧を更新しますか?
		$localeText['msg_select_item_to_edit'] = $this->_('Select item to edit.');		// 編集する項目を選択してください
		$localeText['msg_select_item_to_del'] = $this->_('Select item to delete.');		// 削除する項目を選択してください
		$localeText['msg_delete_item'] = $this->_('Delete selected item?');		// 選択項目を削除しますか?
		$localeText['msg_directory_not_allowed'] = $this->_('Directory not allowed to edit.');		// ディレクトリは編集できません
		$localeText['msg_create_directory'] = $this->_('Create directory?');		// ディレクトリを作成しますか?
		$localeText['label_template'] = $this->_('Template:');		// テンプレート
		$localeText['label_path'] = $this->_('Path:');		// パス
		$localeText['label_edit'] = $this->_('Edit');		// 編集
		$localeText['label_delete'] = $this->_('Delete');		// 削除
		$localeText['label_check'] = $this->_('Select');		// 選択
		$localeText['label_filename'] = $this->_('Filename');		// ファイル名
		$localeText['label_status'] = $this->_('Status');		// 状態
		$localeText['label_size'] = $this->_('Size');		// サイズ
		$localeText['label_date'] = $this->_('Update Date');		// 更新日時
		$localeText['label_upload'] = $this->_('Image Upload');		// 画像アップロード
		$localeText['label_filesize'] = $this->_('Max Filesize');		// 1ファイル最大サイズ
		$localeText['label_select_file'] = $this->_('Select Files');		// ファイルを選択
		$localeText['label_create_directory'] = $this->_('Create Directory');		// ディレクトリ作成
		$localeText['label_directory_name'] = $this->_('Directory Name');// ディレクトリ名
		$localeText['label_create'] = $this->_('Create');// 作成
		$localeText['label_cancel'] = $this->_('Cancel');// キャンセル
		$localeText['label_range'] = $this->_('Range:');		// 範囲：
		$this->setLocaleText($localeText);
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ウィンドウ表示状態
		$openby = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);
		
		$act = $request->trimValueOf('act');
		$filename = $request->trimValueOf('filename');

		// 画像格納ディレクトリを取得
		$this->imageBasePath = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . self::DEFAULT_IMAGE_DIR;
		if (!is_dir($this->imageBasePath)){
			$this->setAppErrorMsg('画像ディレクトリが見つかりません。パス=' . $this->imageBasePath);
			return;
		}

		// パスを取得
		$path = trim($request->valueOf('path'));		// 現在のパス
		if (empty($path)){
			$path = $this->imageBasePath;
		} else {
			if (!strStartsWith($path, '/')) $path = '/' . $path;
			$path = $this->imageBasePath . $path;
		}
		// パスのエラーチェック
		if (isDescendantPath($this->imageBasePath, $path)){
			if (!is_dir($path)){
				return;
			}
		} else {
			return;
		}
		$filePath = $path . '/' . $filename;
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック

			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){

//				$ret = self::$_mainDb->updatePhotoInfo(self::$_isLimitedUser, $this->serialNo/*更新*/, $this->_langId, ''/*ファイル名*/, ''/*格納ディレクトリ*/, ''/*画像コード*/, ''/*画像MIMEタイプ*/,
//								''/*画像縦横サイズ*/, ''/*元のファイル名*/, ''/*ファイルサイズ*/, $name, $camera, $location, $updatePhotoDate, $summary, $description, ''/*ライセンス*/, 0/*所有者*/, $keyword, $visible, $sortOrder, $categoryArray/*画像カテゴリー*/, ''/*サムネールファイル名*/, $newSerial);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Item updated.'));		// データを更新しました
					$reloadData = true;		// データの再読み込み
					
/*					// 運用ログを残す
					$ret = self::$_mainDb->getPhotoInfoBySerial($newSerial, $row, $categoryRows);
					if ($ret){
						$photoId = $row['ht_public_id'];
						$name = $row['ht_name'];
						$updateDt = $row['ht_create_dt'];		// コンテンツ作成日時
					}
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_PHOTO,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $photoId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
					$this->writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_UPDATE_CONTENT, $name), 2401, 'ID=' . $photoId, $eventParam);
					*/
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating item.'));		// データ更新に失敗しました
				}
			}
		} else if ($act == 'delete'){		// 削除のとき

			if ($ret){
//				$imagePath = $this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR . $row['ht_dir'] . DIRECTORY_SEPARATOR . $row['ht_public_id'];
				$delFiles = array($imagePath);	// ファイル名
				$delSystemFiles = array($row['ht_thumb_filename']);		// 削除するシステム用画像ファイル
				$photoId = $row['ht_public_id'];
				$name = $row['ht_name'];
						
				// 写真情報削除
				$ret = self::$_mainDb->delPhotoInfo($delItems);
				if ($ret){
					// ファイル、ディレクトリ削除
					$imageFiles = array();
					for ($i = 0; $i < count($delFiles); $i++){
						$file = $delFiles[$i];
						if (is_dir($file)){
							// ファイル、ディレクトリがある場合は削除しない
							$files = getFileList($file);
							if (count($files) == 0){
								if (!rmDirectory($file)) $ret = false;
							} else {
								$ret = false;
							}
						} else {
							if (!@unlink($file)) $ret = false;
							$imageFiles[] = $file;
						}
					}
					// 公開画像、サムネールを削除
					$ret = $this->deleteCacheImages($imageFiles);
				}
				if ($ret){		// ファイル削除成功のとき
					$this->setGuidanceMsg($this->_('Files deleted.'));			// ファイルを削除しました
				} else {
					$this->setAppErrorMsg($this->_('Failed in deleting files.'));			// ファイル削除に失敗しました
				}
			} else {
				$this->setAppErrorMsg($this->_('Failed in deleting files.'));			// ファイル削除に失敗しました
			}
		} else if ($act == 'uploadimage'){		// 画像アップロード
			// 作業ディレクトリを作成
			$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $tmpDir);
		} else if ($act == 'getimage'){			// 画像取得
			$this->getImageByType($type);
		} else {
			// 初期値を設定
			$reloadData = true;		// データの再読み込み
			
			// 作業ディレクトリを削除
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			rmDirectory($tmpDir);
		}

		// 画像情報取得
		if (file_exists($filePath)){
			$imageSize = @getimagesize($filePath);
			if ($imageSize){
				$imageWidth = $imageSize[0];
				$imageHeight = $imageSize[1];
				$imageType = $imageSize[2];
				$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
				
				$imageSizeStr = $imageWidth . 'x' . $imageHeight;
			}
			
			// ファイルサイズ
			$size = filesize($filePath);
			
			// 画像URL
			$imageUrl = $this->gEnv->getUrlToPath($filePath);
		}
							
		// アップロード実行用URL
/*		$uploadUrl = $this->gEnv->getDefaultAdminUrl();
		$uploadUrl .= '?' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIGIMAGE;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';*/
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?task=' . self::TASK_TEMPIMAGE_DETAIL;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
		$uploadUrl .= '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $this->adaptWindowsPath(substr($path, strlen($this->imageBasePath)));
		$this->tmpl->addVar("_widget", "upload_url_sitelogo", $this->getUrl($uploadUrl));
								
		// 取得データを設定
		$this->tmpl->addVar("_widget", "filename", $this->convertToDispString($filename));

		$this->tmpl->addVar("_widget", "image_tag", $imageTag);
		$this->tmpl->addVar("_widget", "image_url", $this->getUrl($imageUrl));

		$this->tmpl->addVar("_widget", "size", $this->convertToDispString($size));
		$this->tmpl->addVar("_widget", "image_size", $this->convertToDispString($imageSizeStr));
		

//		$this->tmpl->addVar('_widget', 'public_image_url', $this->convertToDispString($this->getUrl(photo_mainCommonDef::getPublicImageUrl($photoId))));	// 公開画像URL
		$this->tmpl->addVar('_widget', 'thumbnail_url', $thumbnailUrlHtml);	// サムネール画像URL

		
		$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新、削除ボタン表示

		// 「戻る」ボタンの表示
		if ($openby == 'simple' || $openby == 'tabs') $this->tmpl->setAttribute('cancel_button', 'visibility', 'hidden');		// 詳細画面のみの表示またはタブ表示のときは戻るボタンを隠す
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path		パス
	 * @param array $fileList	ファイル一覧
	 * @param int $startNo		開始番号
	 * @param int $endNo		終了番号
	 * @return なし
	 */
	function createFileList($path, $fileList, $startNo, $endNo)
	{
		// 親ディレクトリを追加
		if ($path != $this->imageBasePath){
			$file = '..';
			$relativeFilePath = substr(dirname($path), strlen($this->imageBasePath));
//			$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativeFilePath;
			$pageUrl = '?task=' . self::TASK_TEMPIMAGE . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $relativeFilePath;
			$fileLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">' . $this->convertToDispString($file) . '</a>';
					
			// アイコン作成
			$iconTitle = $file;
			$iconUrl = $this->gEnv->getRootUrl() . self::PARENT_ICON_FILE;
			$iconTag = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">';
			$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			$iconTag .= '</a>';
			
			$checkDisabled = 'disabled ';
			$row = array(
				'icon'		=> $iconTag,		// アイコン
				'name'		=> $this->convertToDispString($file),			// ファイル名
				'filename'    => $fileLink,			// ファイル名
				'size'     => $size,			// ファイルサイズ
				'check_disabled' => $checkDisabled,		// チェックボックス使用制御
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('file_list', $row);
			$this->tmpl->parseTemplate('file_list', 'a');
		}
			
		$index = 0;			// インデックス番号
		for ($i = $startNo -1; $i < $endNo; $i++){
			$filePath = $fileList[$i];
			$relativeFilePath = substr($filePath, strlen($this->imageBasePath));

			$filePathArray = explode('/', $filePath);		// pathinfo,basenameは日本語処理できないので日本語対応
			$file = end($filePathArray);		// ファイル名
			$size = '';				// ファイルサイズ
			$fileLink = '';
			$checkDisabled = '';		// チェックボックス使用制御
			$imageSizeStr = '';
			if (is_dir($filePath)){			// ディレクトリのとき
				// アイコン作成
				$iconUrl = $this->gEnv->getRootUrl() . self::FOLDER_ICON_FILE;
				$iconTitle = $this->convertToDispString($file);
//				$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativeFilePath;
				$pageUrl = '?task=' . self::TASK_TEMPIMAGE . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $relativeFilePath;
				$iconTag = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">';
				$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
				$iconTag .= '</a>';
			
				$fileLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">' . $this->convertToDispString($file) . '</a>';
				
				// ファイルまたはディレクトリがないときは削除可能
				$files = getFileList($filePath);
				if (count($files) > 0){
					$checkDisabled = 'disabled ';		// チェックボックス使用制御
				}
				$serial = -1;
			} else {		// ファイルのとき
				// 画像情報を取得
				$isImageFile = false;
				$imageSize = @getimagesize($filePath);
				if ($imageSize){
					$imageWidth = $imageSize[0];
					$imageHeight = $imageSize[1];
					$imageType = $imageSize[2];
					$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得

					// 処理可能な画像ファイルタイプかどうか
					if (in_array($imageMimeType, $this->permitMimeType)){
						$isImageFile = true;
						$imageSizeStr = $imageWidth . 'x' . $imageHeight;
					}
				}

				// ファイル削除用チェックボックス
				if (!is_writable($filePath)) $checkDisabled = 'disabled ';		// チェックボックス使用制御
				
				// アイコン作成
				$iconTitle = $this->convertToDispString($file);
				if ($isImageFile){				// 画像ファイルの場合
					// 画像サイズが指定サイズより大きい場合はサムネール画像を作成
					if ($imageWidth > self::LIST_ICON_SIZE || $imageHeight > self::LIST_ICON_SIZE){
						$thumbPath = $this->cacheDir . $relativeFilePath;		// サムネール画像パス
						
						// サムネール画像が存在しない場合は作成
						if (file_exists($thumbPath)){
							$imageSize = @getimagesize($thumbPath);
							if ($imageSize){
								$imageWidth = $imageSize[0];
								$imageHeight = $imageSize[1];
								$imageType = $imageSize[2];
								$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
							}
							$iconUrl = $this->gEnv->getUrlToPath($thumbPath);
						} else {
							// ディレクトリ作成
							$ret = true;
							$thumbDir = dirname($thumbPath);
							if (!is_dir($thumbDir)) $ret = mkdir($thumbDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
							
							if ($ret) $ret = $this->gInstance->getImageManager()->createImage($filePath, $thumbPath, self::LIST_ICON_SIZE, $imageType, $destSize);
							if ($ret){
								$imageWidth = $destSize['width'];
								$imageHeight = $destSize['height'];
								$iconUrl = $this->gEnv->getUrlToPath($thumbPath);
							} else {
								$this->setAppErrorMsg('サムネール画像が作成できません。画像ファイル=' . $filePath);
							
								$imageWidth = '';
								$imageHeight = '';
							}
						}
					} else {		// 画像サイズが範囲内の場合はそのまま表示
						$iconUrl = $this->gEnv->getUrlToPath($filePath);
					}
					
					$iconTag = '<a href="#" onclick="editItemByFilename(\'' . addslashes($file) . '\');return false;">';
					$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . $imageWidth . '" height="' . $imageHeight . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					$iconTag .= '</a>';
				} else {
					$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
					$iconTag = '<a href="#" onclick="editItemByFilename(\'' . addslashes($file) . '\');return false;">';
					$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					$iconTag .= '</a>';
				}
				
				$fileLink = '<a href="#" onclick="editItemByFilename(\'' . addslashes($file) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
				$size = filesize($filePath);
			}
	
			// ファイル更新日時
			$updateDate = date('Y/m/d H:i:s', filemtime($filePath));
			
			$row = array(
				'serial'		=> $serial,
				'index'			=> $index,			// インデックス番号(チェックボックス識別)
				'icon'			=> $iconTag,		// アイコン
				'name'			=> $this->convertToDispString($file),			// ファイル名
				'filename'    	=> $fileLink,			// ファイル名
				'image_size'	=> $imageSizeStr,		// 画像サイズ
				'size'     		=> $size,			// ファイルサイズ
				'date'    		=> $updateDate,			// 更新日時
				'check_disabled'	=> $checkDisabled,		// チェックボックス使用制御
			);
			$this->tmpl->addVars('file_list', $row);
			$this->tmpl->parseTemplate('file_list', 'a');
			
			// インデックス番号を保存
	//		$this->serialArray[] = $serial;
			$this->fileArray[] = $this->convertToDispString($file);			// ファイル名
			$index++;
		}
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path	ディレクトリパス
	 * @return array		ファイルパスのリスト
	 */
	function getFileList($path)
	{
		$fileList = array();
		
		// 引数エラーチェック
		if (!is_dir($path)) return $fileList;
		
		$dir = dir($path);
		while (($file = $dir->read()) !== false){
			$filePath = $path . DIRECTORY_SEPARATOR . $file;
			// カレントディレクトリかどうかチェック
			if ($file != '.' && $file != '..') $fileList[] = $filePath;
		}
		$dir->close();
		
		// 一覧を日付でソート
		$this->sortOrderByDateAsc = false;	// 日付で降順にソート
		usort($fileList, array($this, 'sortOrderByDate'));
		
		return $fileList;
	}
	/**
	 * ファイルパスを日付で昇順にソートする。ディレクトリは先頭。
	 *
	 * @param string  	$path1			比較するパス1
	 * @param string	$path2			比較するパス2
	 * @return int						同じとき0、$path1が$path2より大きいとき1,$path1が$path2より小さいとき-1を返す
	 */
	function sortOrderByDate($path1, $path2)
	{
		// ディレクトリは常に先頭に表示
		if (is_dir($path1)){			// ディレクトリのとき
			if (!is_dir($path2)) return -1; // ファイルのとき
		} else {
			if (is_dir($path2)) return 1;	// ディレクトリのとき
		}
		$fileTime1 = filemtime($path1);
		$fileTime2 = filemtime($path2);
		
		if ($fileTime1 == $fileTime2) return 0;
		if ($this->sortOrderByDateAsc){	// 日付で昇順にソート
			return ($fileTime1 < $fileTime2) ? -1 : 1;
		} else {
			return ($fileTime1 > $fileTime2) ? -1 : 1;
		}
	}
	/**
	 * windowパスを使用する場合は、Javascriptに認識させる
	 *
	 * @param string         $src			パス文字列
	 * @return string 						作成文字列
	 */
	function adaptWindowsPath($src)
	{
		// 「\」を有効にする
		if (DIRECTORY_SEPARATOR == '\\'){		// windowsパスのとき
			return addslashes($src);
		} else {
			return $src;
		}
	}
	/**
	 * 各種画像作成
	 *
	 * @param string $photoId				公開画像ID
	 * @param string $imagePath				画像パス
	 * @param string $originalFilename		オリジナル画像名
	 * @param string $thumbFilename			サムネールファイル名(画像情報格納用)
	 * @return bool 						true=成功、失敗
	 */
	function createImages($photoId, $imagePath, $originalFilename, &$thumbFilename)
	{
		$ret = true;
		$imageSize = @getimagesize($imagePath);
		if ($imageSize){
			$imageType = $imageSize[2];
		} else {
			return false;
		}
		// ##### システム用サムネール作成 #####
		$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_PHOTO, 0/*PC用*/, $photoId, $imagePath, $destFilename);
		if ($ret){
			$thumbFilename = implode(';', $destFilename);
		} else {
			$this->writeError(__METHOD__, 'システム用画像ファイル作成に失敗しました。', 1100, '元のファイル名=' . $originalFilename);// 運用ログに記録
		}
		
		// ##### サムネール画像作成 #####
		// サムネール画像サイズ取得
//		$thumbailSizeArray = trimExplode(',', self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_SIZE]);
//		if (empty($thumbailSizeArray)) $thumbailSizeArray = array(photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE);
		for ($i = 0; $i < count($thumbailSizeArray); $i++){
			$thumbnailSize = $thumbailSizeArray[$i];
//			$thumbnailPath = photo_mainCommonDef::getThumbnailPath($photoId, $thumbnailSize);
			//$ret = $this->createThumbImage($imagePath, $imageType, $thumbnailPath, self::DEFAULT_IMAGE_TYPE, $thumbnailSize);
//			if (self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_CROP]){		// 切り取りサムネールの場合
//				$ret = $this->gInstance->getImageManager()->createThumb($imagePath, $thumbnailPath, $thumbnailSize, self::DEFAULT_IMAGE_TYPE, true);
//			} else {
//				$ret = $this->gInstance->getImageManager()->createThumb($imagePath, $thumbnailPath, $thumbnailSize, self::DEFAULT_IMAGE_TYPE, false, self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_BG_COLOR]);
//			}
			if (!$ret){
				$this->writeError(__METHOD__, '画像ファイル作成に失敗しました。', 1100,
									'元のファイル名=' . $originalFilename . ', 画像ファイル=' . $thumbnailPath);// 運用ログに記録
			}
		}
		return $ret;
	}
	/**
	 * キャッシュ画像削除
	 *
	 * @param array $delFiles			削除対象のソース画像
	 * @return bool 					true=成功、失敗
	 */
	function deleteCacheImages($srcFiles)
	{
		$ret = true;
		for ($i = 0; $i < count($srcFiles); $i++){
			$filePath = $srcFiles[$i];
			
			// サムネール画像パスを取得
			$thumbPath = $this->cacheDir . '/' . str_replace($this->imageBasePath . '/', '', $filePath);
			if (file_exists($thumbPath)){
				// 画像削除
				if (!@unlink($thumbPath)) $ret = false;
			}
		}
		return $ret;
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
		if (!$isSuccess) return;		// ファイルアップロード失敗のときは終了

		$filename = $resultObj['file']['filename'];				// 元のファイル名
		$destFilePath = $this->path . DIRECTORY_SEPARATOR . $filename;
		
		if (file_exists($destFilePath)) $this->writeUserError(__METHOD__, 'アップロード画像がすでに存在しています。新しい画像で置き換えます。', 1100, 'ファイル名=' . $filename);

		// アップされたファイルをコピー
		$ret = copy($filePath, $destFilePath);
		if ($ret){
			$isErr = false;		// エラーが発生したかどうか

			// 画像情報を取得
			$imageSize = @getimagesize($destFilePath);
			if ($imageSize){
				$imageWidth = $imageSize[0];
				$imageHeight = $imageSize[1];
				$imageType = $imageSize[2];
				$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得

				// 受付可能なファイルタイプかどうか
				if (!in_array($imageMimeType, $this->permitMimeType)){
					$isErr = true;	
					$this->writeUserError(__METHOD__, 'アップロード画像のタイプが不正です。アカウント: ' . $account, 2200, 'ファイル名=' . $filename);
				}
			} else {
				$isErr = true;
				$this->writeUserError(__METHOD__, 'アップロード画像が不正です。アカウント: ' . $account, 2200, 'ファイル名=' . $filename);
			}

			// エラー処理
			if ($isErr){
				// エラーコード設定
				$resultObj = array('error' => '595 File Upload Error');
				
				// アップロードファイル削除
				unlink($destFilePath);
			}
		} else {
			// エラーコード設定
			$resultObj = array('error' => '596 File Upload Error');
		}
	}
}
?>