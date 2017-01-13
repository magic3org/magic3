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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_photo_mainBaseWidgetContainer.php');

class admin_photo_mainImagebrowseWidgetContainer extends admin_photo_mainBaseWidgetContainer
{
	private $serialArray = array();		// 表示されているファイルのインデックス番号
	private $photoBasePath;				// アクセス可能な画像格納ディレクトリ
	private $sortOrderByDateAsc;		// 日付でソート
	private $masterMimeType;			// マスター画像のMIMEタイプ
	private $permitMimeType;			// アップロードを許可する画像タイプ
	private $fileListAdded;				// 一覧にデータが追加されたかどうか
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const FILE_ICON_FILE = '/images/system/tree/file_inactive32.png';			// ファイルアイコン
	const FOLDER_ICON_FILE = '/images/system/tree/folder32.png';		// ディレクトリアイコン
	const PARENT_ICON_FILE = '/images/system/tree/parent32.png';		// 親ディレクトリアイコン
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	const ICON_SIZE = 32;		// アイコンのサイズ
	const LIST_ICON_SIZE = 64;		// リスト用アイコンのサイズ
	const CSS_FILE = '/swfupload2.5/css/default.css';		// CSSファイルのパス
//	const PHOTO_DIR = '/etc/photo';		// マスター画像格納ディレクトリ
	const PHOTO_HOME_DIR = '/etc/photo/home';		// マスター画像格納ディレクトリ（ユーザ別)
	const DEFAULT_THUMBNAIL_SIZE = 128;		// サムネール画像サイズ
	const DEFAULT_IMAGE_EXT = 'jpg';			// 画像ファイルのデフォルト拡張子
	const DEFAULT_IMAGE_TYPE = IMAGETYPE_JPEG;
	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
	const IMAGE_QUALITY = 100;			// 生成画像の品質(0～100)
	const LOG_MSG_ADD_CONTENT		= 'フォトコンテンツを追加しました。タイトル: %s';
	const LOG_MSG_UPDATE_CONTENT	= 'フォトコンテンツを更新しました。タイトル: %s';
	const LOG_MSG_DEL_CONTENT		= 'フォトコンテンツを削除しました。タイトル: %s';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 画像タイプ
		$this->masterMimeType = image_type_to_mime_type(IMAGETYPE_JPEG);			// マスター画像のMIMEタイプ
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
		switch ($task){
			case self::TASK_IMAGEBROWSE:
			default:
				$filename = 'admin_imagebrowse.tmpl.html';
				break;
			case self::TASK_IMAGEBROWSE_DETAIL:
				$filename = 'admin_imagebrowse_detail.tmpl.html';
				break;
			case self::TASK_IMAGEBROWSE_DIRECT:
				$filename = '';
				break;
		}
		return $filename;
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
		
		switch ($task){
			case self::TASK_IMAGEBROWSE:
			default:
				$this->createList($request);
				break;
			case self::TASK_IMAGEBROWSE_DETAIL:
				$this->createDetail($request);
				break;
			case self::TASK_IMAGEBROWSE_DIRECT:
				if ($act == 'getimage'){		// 画像取得
					$width = $request->trimValueOf('width');
					$height = $request->trimValueOf('height');
					$photoId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID);			// 画像ID
					photo_mainCommonDef::getImage($photoId, self::$_mainDb, $width, $height);
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
		$account = $this->gEnv->getCurrentUserAccount();
		
		// パラメータエラーチェック
		if (empty($account)){
			$this->SetMsg(self::MSG_APP_ERR, $this->_('Error parameters.'));// パラメータにエラーがあります。
			return;
		}
		
		// 移動可能なディレクトリ範囲を取得
		if (self::$_isLimitedUser){		// 使用限定ユーザの場合
			$this->photoBasePath = $this->gEnv->getIncludePath() . self::PHOTO_HOME_DIR . DIRECTORY_SEPARATOR . $account;				// 画像格納ディレクトリ
			
			// ホームディレクトリがなければ作成
			if (!is_dir($this->photoBasePath)) @mkdir($this->photoBasePath, M3_SYSTEM_DIR_PERMISSION);
		} else {
			$this->photoBasePath = $this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR;				// 画像格納ディレクトリ
		}

		// パスを取得
		$path = trim($request->valueOf('path'));		// 現在のパス
		if (empty($path)){
			$path = $this->photoBasePath;
		} else {
			if (!strStartsWith($path, '/')) $path = '/' . $path;
			$path = $this->photoBasePath . $path;
		}
		// パスのエラーチェック
		if (isDescendantPath($this->photoBasePath, $path)){
			if (!is_dir($path)){
				$this->setUserErrorMsg($this->_('Can not access the page.'));			// アクセスできません
				return;
			}
		} else {
			$this->setUserErrorMsg($this->_('Can not access the page.'));			// アクセスできません
			return;
		}
		
		// ページ番号
		$viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
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
			$delItems = array();	// シリアル番号
			$delPhotos = array();	// 写真ID
			$delFiles = array();	// ファイル名
			$delSystemFiles = array();		// 削除するシステム用画像ファイル
			$delPhotoInfo = array();		// 削除するフォトコンテンツ情報
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					// 削除可能かチェック
					$filename = $request->trimValueOf('item' . $i . '_name');
					$filePath = $path . DIRECTORY_SEPARATOR . $filename;
					if (is_writable($filePath) && strStartsWith($filePath, $this->photoBasePath . DIRECTORY_SEPARATOR)){
						$serial = $listedItem[$i];
						if ($serial > 0){
							// 写真情報のアクセス権をチェック
							$ret = self::$_mainDb->getPhotoInfoBySerial($serial, $row);
							if ($ret){
								if (!self::$_isLimitedUser || (self::$_isLimitedUser && $this->_userId == $row['ht_owner_id'])){
									$delItems[] = $serial;
									$delPhotos[] = $filename;		// 写真ID
									$delSystemFiles[] = $row['ht_thumb_filename'];
									
									$newInfoObj = new stdClass;
									$newInfoObj->photoId = $row['ht_public_id'];// フォトID
									$newInfoObj->name = $row['ht_name'];		// フォト名
									$delPhotoInfo[] = $newInfoObj;
								}
							}
						}
						$delFiles[] = $filePath;		// 削除するファイルのパス
					} else {
						//$this->setMsg(self::MSG_USER_ERR, '削除できないファイルが含まれています。ファイル名=' . $this->convertToDispString($filename));
						$this->setMsg(self::MSG_USER_ERR, sprintf($this->_('Include files not allowed to delete. (filename: %s)'), $this->convertToDispString($filename)));		// 削除できないファイルが含まれています。(ファイル名： %s)
						break;
					}
				}
			}
			if ($this->getMsgCount() == 0 && count($delFiles) > 0){
				$ret = self::$_mainDb->delPhotoInfo($delItems);
				if ($ret){
					// ファイル、ディレクトリ削除
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
						}
					}
					// 公開画像、サムネールを削除
					$ret = $this->deleteImages($delPhotos, $delSystemFiles);
				}
				if ($ret){		// ファイル削除成功のとき
					//$this->setGuidanceMsg('ファイルを削除しました');
					$this->setGuidanceMsg($this->_('Files deleted.'));			// ファイルを削除しました
					
					// 運用ログを残す
					for ($i = 0; $i < count($delPhotoInfo); $i++){
						$infoObj = $delPhotoInfo[$i];
						$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_PHOTO,
												M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $infoObj->photoId,
												M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
						$this->writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_DEL_CONTENT, $infoObj->name), 2402, 'ID=' . $infoObj->photoId, $eventParam);
					}
				} else {
					//$this->setAppErrorMsg('ファイル削除に失敗しました');
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
		$photoParentPath = dirname($this->photoBasePath);
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
					$relativeFilePath = substr($absPath, strlen($this->photoBasePath));
					$pathLink .= '&nbsp;' . DIRECTORY_SEPARATOR . '&nbsp;';
					$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativeFilePath;
					$pathLink .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">' . $this->convertToDispString($pathArray[$i]) . '</a>';
				}
			}
		}
	
/*		$this->canDeleteFile = false;
		if ($relativePath == M3_DIR_NAME_RESOURCE || strStartsWith($relativePath, M3_DIR_NAME_RESOURCE . DIRECTORY_SEPARATOR)){
			$this->canDeleteFile = true;
		} else {
			// 削除ボタンを使用不可にする
			$this->tmpl->addVar("_widget", "del_disabled", 'disabled ');
		}*/
		
		// 総数を取得
		$fileList = $this->getFileList($path);
		$totalCount = count($fileList);

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $viewCount + 1;		// 先頭番号
		$startNo = ($pageNo -1) * $viewCount +1;		// 先頭の行番号
		$endNo = $pageNo * $viewCount > $totalCount ? $totalCount : $pageNo * $viewCount;// 最後の行番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					//$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
					$relativePath = substr($path, strlen($this->photoBasePath));
					$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativePath;
					if ($i > 1) $pageUrl .= '&page=' . $i;
					$link = '&nbsp;<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", sprintf($this->_('%d Total'), $totalCount));// 全 x件
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
//		$this->tmpl->addVar("search_range", "start_no", $startNo);
//		$this->tmpl->addVar("search_range", "end_no", $endNo);
//		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// ファイル一覧を作成
		$this->createFileList($path, $fileList, $startNo, $endNo);
		if (!$this->fileListAdded) $this->tmpl->setAttribute('file_list', 'visibility', 'hidden');				// 一覧にデータが追加されたかどうか
		
		$this->tmpl->addVar('_widget', 'path', substr($path, strlen($this->photoBasePath)));// 現在のディレクトリ
		$this->tmpl->addVar('_widget', 'path_link', $pathLink);// 現在のディレクトリ
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar('_widget', 'directory_name', $this->convertToDispString($dirName));// ディレクトリ作成用
		
		// アップロード実行用URL
/*		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_IMAGEBROWSE;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
//		$uploadUrl .= '&' . M3_REQUEST_PARAM_ADMIN_KEY . '=' . $this->gEnv->getAdminKey();	// 管理者キー
		//$uploadUrl .= '&path=' . $this->adaptWindowsPath($path);
		$uploadUrl .= '&path=' . $this->adaptWindowsPath(substr($path, strlen($this->photoBasePath)));					// アップロードディレクトリ
		*/
		$param = M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_IMAGEBROWSE;
		$param .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadimage';
		$param .= '&path=' . $this->adaptWindowsPath(substr($path, strlen($this->photoBasePath)));					// アップロードディレクトリ
		$uploadUrl = $this->getConfigAdminUrl($param);
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
		$localeText['label_path'] = $this->_('Path:');		// パス
		$localeText['label_edit'] = $this->_('Edit');		// 編集
		$localeText['label_delete'] = $this->_('Delete');		// 削除
		$localeText['label_check'] = $this->_('Select');		// 選択
		$localeText['label_filename'] = $this->_('Filename');		// ファイル名
		$localeText['label_status'] = $this->_('Status');		// 状態
		$localeText['label_image_code'] = $this->_('Image Code');		// 画像コード
		$localeText['label_size'] = $this->_('Size');		// サイズ
		$localeText['label_view_count'] = $this->_('View Count');		// 閲覧数
		$localeText['label_rate'] = $this->_('Rating');		// 評価
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
		
		$categoryCount = self::$_configArray[photo_mainCommonDef::CF_IMAGE_CATEGORY_COUNT];			// カテゴリ数
		if (empty($categoryCount)) $categoryCount = photo_mainCommonDef::DEFAULT_CATEGORY_COUNT;
		
		$act = $request->trimValueOf('act');
		$this->serialNo = intval($request->trimValueOf('serial'));		// 選択項目のシリアル番号
		$name = $request->trimValueOf('item_name');
		$sortOrder = $request->trimValueOf('item_sort_order');			// 表示順
		$location = $request->trimValueOf('item_location');		// 撮影場所
		$camera = $request->trimValueOf('item_camera');		// カメラ
		$summary = $request->trimValueOf('item_summary');			// 画像概要
		if (self::$_configArray[photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION]){		// HTMLタイプの説明のとき
			$description = $request->valueOf('item_description');			// 画像説明
		} else {
			$description = $request->trimValueOf('item_description');			// 画像説明
		}
		$keyword = $request->trimValueOf('item_keyword');	// 検索キーワード
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// チェックボックス
		$photoDate = $request->trimValueOf('item_date');		// 撮影日
		if (!empty($photoDate)) $photoDate = $this->convertToProperDate($photoDate);
		
		// カテゴリーを取得
		$categoryArray = array();
		for ($i = 0; $i < $categoryCount; $i++){
			$itemName = 'item_category' . $i;
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue)){		// 0以外の値を取得
				$categoryArray[] = $itemValue;
			}
		}
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			$this->checkNumeric($sortOrder, '表示順');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->updatePhotoInfo(self::$_isLimitedUser, $this->serialNo/*更新*/, $this->_langId, ''/*ファイル名*/, ''/*格納ディレクトリ*/, ''/*画像コード*/, ''/*画像MIMEタイプ*/,
								''/*画像縦横サイズ*/, ''/*元のファイル名*/, ''/*ファイルサイズ*/, $name, $camera, $location, $photoDate, $summary, $description, ''/*ライセンス*/, 0/*所有者*/, $keyword, $visible, $sortOrder, $categoryArray/*画像カテゴリー*/, ''/*サムネールファイル名*/, $newSerial);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Item updated.'));		// データを更新しました
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 運用ログを残す
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
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating item.'));		// データ更新に失敗しました
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			$ret = self::$_mainDb->getPhotoInfoBySerial($this->serialNo, $row);
			if ($ret){
				$delItems = array($this->serialNo);	// シリアル番号
				$delPhotos = array($row['ht_public_id']);	// 公開画像ID
				$imagePath = $this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR . $row['ht_dir'] . DIRECTORY_SEPARATOR . $row['ht_public_id'];
				$delFiles = array($imagePath);	// ファイル名
				$delSystemFiles = array($row['ht_thumb_filename']);		// 削除するシステム用画像ファイル
				$photoId = $row['ht_public_id'];
				$name = $row['ht_name'];
						
				// 写真情報削除
				$ret = self::$_mainDb->delPhotoInfo($delItems);
				if ($ret){
					// ファイル、ディレクトリ削除
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
						}
					}
					// 公開画像、サムネールを削除
					$ret = $this->deleteImages($delPhotos, $delSystemFiles);
				}
				if ($ret){		// ファイル削除成功のとき
					//$this->setGuidanceMsg('ファイルを削除しました');
					$this->setGuidanceMsg($this->_('Files deleted.'));			// ファイルを削除しました
					
					// 運用ログを残す
					$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_PHOTO,
											M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $photoId,
											M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
					$this->writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_DEL_CONTENT, $name), 2402, 'ID=' . $photoId, $eventParam);
				} else {
					//$this->setAppErrorMsg('ファイル削除に失敗しました');
					$this->setAppErrorMsg($this->_('Failed in deleting files.'));			// ファイル削除に失敗しました
				}
			} else {
				$this->setAppErrorMsg($this->_('Failed in deleting files.'));			// ファイル削除に失敗しました
			}
		} else if ($act == 'recreate_image'){		// 画像再作成のとき
			$ret = self::$_mainDb->getPhotoInfoBySerial($this->serialNo, $row);
			if ($ret){
				$photoId		= $row['ht_public_id'];
				$originalName	= $row['ht_original_filename'];
				$imagePath		= $this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR . $row['ht_dir'] . DIRECTORY_SEPARATOR . $row['ht_public_id'];
				$ret = $this->createImages($photoId, $imagePath, $originalName, $thumbFilename);
				if ($ret){		// ファイル削除成功のとき
					$this->setGuidanceMsg($this->_('Images recreated.'));			// 画像再作成しました
				}
			}
			$reloadData = true;		// データの再読み込み
		} else {
			// 初期値を設定
			$reloadData = true;		// データの再読み込み
		}
		$isPhtoInfo = false;		// 画像情報があるかどうか
		if ($reloadData){		// データの再読み込み
			$ret = self::$_mainDb->getPhotoInfoBySerial($this->serialNo, $row, $categoryRows);
			if ($ret){
				$name = $row['ht_name'];
				$photoId = $row['ht_public_id'];
				$photoCode = $row['ht_code'];
				$originalName = $row['ht_original_filename'];
				$size = $row['ht_image_size'];
				$author = $row['lu_name'];
				$sortOrder = $row['ht_sort_order'];		// ソート順
				$location = $row['ht_location'];		// 撮影場所
				$camera = $row['ht_camera'];		// カメラ
				$summary = $row['ht_summary'];			// 画像概要
				$description = $row['ht_description'];			// 画像説明
				$keyword = $row['ht_keyword'];	// 検索キーワード
				$visible = $row['ht_visible'];		// 公開
				$photoDate = $this->convertToDispDate($row['ht_date']);	// 撮影日
				$photoTime = $this->convertToDispTime($row['ht_time'], 1/*時分*/);	// 撮影時間
				
				// 画像サイズ
				list($width, $height) = explode('x', $row['ht_image_size']);
				$originalWidth = $width;
				$originalHeight = $height;
				
				// 画像フォーマット
				$mimeType = $row['ht_mime_type'];
				$format = '未検出';
				if ($mimeType == image_type_to_mime_type(IMAGETYPE_GIF)){
					$format = 'GIF';
				} else if ($mimeType == image_type_to_mime_type(IMAGETYPE_JPEG)){
					$format = 'JPEG';
				} else if ($mimeType == image_type_to_mime_type(IMAGETYPE_PNG)){
					$format = 'PNG';
				} else if ($mimeType == image_type_to_mime_type(IMAGETYPE_BMP)){
					$format = 'BMP';
				}
				
				// 画像URL取得
				photo_mainCommonDef::adjustImageSize($width, $height, photo_mainCommonDef::DEFAULT_PUBLIC_IMAGE_SIZE);
				$originalImageUrl = $this->_baseUrl . '&task=imagebrowse_direct&act=getimage&' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;		// 元の写真
				$imageUrl = $originalImageUrl . '&width=' . $width . '&height=' . $height;// 画像
				$imageTag = '<img src="' . $this->getUrl($imageUrl) . '" width="' . $width . '" height="' . $height . '" border="0" alt="' . $photoId . '" title="' . $photoId . '" />';
				
				// 記事カテゴリー取得
				$categoryArray = $this->getCategory($categoryRows, $categoryCount);
				
				$isPhtoInfo = true;		// 画像情報があるかどうか
			} else {
				$this->setAppErrorMsg($this->_('Can not find photo information.'));			// 画像情報が見つかりません
			}
		}
		// 入力領域の表示制御
		if (!self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DATE]){
			$this->tmpl->setAttribute('show_photo_date', 'visibility', 'hidden');			// 画像情報(撮影日)を使用
			$this->tmpl->setAttribute('show_calender', 'visibility', 'hidden');				// カレンダーを作成しない
		}
		if (!self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_LOCATION]) $this->tmpl->setAttribute('show_photo_location', 'visibility', 'hidden');	// 画像情報(撮影場所)を使用
		if (!self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_CAMERA]) $this->tmpl->setAttribute('show_photo_camera', 'visibility', 'hidden');		// 画像情報(カメラ)を使用
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION]){
			if (self::$_configArray[photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION]) $this->tmpl->setAttribute('show_html_description', 'visibility', 'visible');		// HTMLエディターを表示
		} else {
			$this->tmpl->setAttribute('show_photo_description', 'visibility', 'hidden');	// 画像情報(説明)を使用
		}
		if (!self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_KEYWORD]) $this->tmpl->setAttribute('show_photo_keyword', 'visibility', 'hidden');	// 画像情報(検索キーワード)を使用
		if (!self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_CATEGORY]) $this->tmpl->setAttribute('show_photo_category', 'visibility', 'hidden');	// 画像情報(カテゴリー)を使用
		
		// カテゴリーメニューを作成
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_CATEGORY]){	// 画像情報(カテゴリー)を使用のとき
			$ret = self::$_mainDb->getAllCategory($this->_langId, $allCategoryRows);
			if ($ret) $this->createCategoryMenu($allCategoryRows, $categoryArray, $categoryCount);
		}
		
		// サムネールのURLの作成
		$thumbnailUrlHtml = '';
		$thumbailSizeArray = trimExplode(',', self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_SIZE]);
		if (empty($thumbailSizeArray)) $thumbailSizeArray = array(photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE);
		for ($i = 0; $i < count($thumbailSizeArray); $i++){
			$thumbnailSize = $thumbailSizeArray[$i];
			$thumbnailPath = photo_mainCommonDef::getThumbnailPath($photoId, $thumbnailSize);
			if (file_exists($thumbnailPath)){		// サムネールが存在するとき
				$thumbnailUrlHtml .= '<b><font color="green">';
			} else {
				$thumbnailUrlHtml .= '<b><font color="red">';
			}
			$thumbnailUrlHtml .= $this->convertToDispString($this->getUrl(photo_mainCommonDef::getThumbnailUrl($photoId, $thumbnailSize)));
			$thumbnailUrlHtml .= '</font></b><br />';
		}
								
		// 取得データを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "photo_id", $this->convertToDispString($photoId));
		$this->tmpl->addVar("_widget", "code", $this->convertToDispString($photoCode));
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "sort_order", $this->convertToDispString($sortOrder));
		$this->tmpl->addVar("_widget", "original_name", $this->convertToDispString($originalName));
		$this->tmpl->addVar("_widget", "image_tag", $imageTag);
		$this->tmpl->addVar("_widget", "image_url", $this->getUrl($originalImageUrl));
		$this->tmpl->addVar("_widget", "format", $this->convertToDispString($format));
		$this->tmpl->addVar("_widget", "size", $this->convertToDispString($size));
		$this->tmpl->addVar("_widget", "author", $this->convertToDispString($author));
		$this->tmpl->addVar("_widget", "summary", $this->convertToDispString($summary));			// 画像概要
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_LOCATION]){	// 画像情報(撮影場所)を使用のとき
			$this->tmpl->addVar("show_photo_location", "location", $this->convertToDispString($location));	// 撮影場所
		}
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_CAMERA]){		// 画像情報(カメラ)を使用のとき
			$this->tmpl->addVar("show_photo_camera", "camera", $this->convertToDispString($camera));		// カメラ
		}
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION]){		// 画像情報(説明)を使用のとき
			$this->tmpl->addVar("show_photo_description", "description", $this->convertToDispString($description));
		}
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_KEYWORD]){	// 画像情報(検索キーワード)を使用のとき
			$this->tmpl->addVar("show_photo_keyword", "keyword", $this->convertToDispString($keyword));	// 検索キーワード
		}
		if (self::$_configArray[photo_mainCommonDef::CF_USE_PHOTO_DATE]){		// 画像情報(撮影日)を使用のとき
			$this->tmpl->addVar("show_photo_date", "date", $photoDate);	// 撮影日
			$this->tmpl->addVar('show_photo_date', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
		}
		$this->tmpl->addVar('_widget', 'public_image_url', $this->convertToDispString($this->getUrl(photo_mainCommonDef::getPublicImageUrl($photoId))));	// 公開画像URL
		$this->tmpl->addVar('_widget', 'thumbnail_url', $thumbnailUrlHtml);	// サムネール画像URL
		$checkedStr = '';
		if ($visible) $checkedStr = 'checked';
		$this->tmpl->addVar("_widget", "visible", $checkedStr);		// 公開
		
		if (!empty($this->serialNo) && $isPhtoInfo){		// 新規以外で画像情報がある場合
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新、削除ボタン表示
		}
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
		if ($path != $this->photoBasePath){
			$file = '..';
			$relativeFilePath = substr(dirname($path), strlen($this->photoBasePath));
			//$fileLink = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString(dirname($path))) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
			//$fileLink = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString($relativeFilePath)) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
			$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativeFilePath;
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
			
			$this->fileListAdded = true;				// 一覧にデータが追加されたかどうか
		}
			
		$index = 0;			// インデックス番号
		//for ($i = 0; $i < count($fileList); $i++){
		for ($i = $startNo -1; $i < $endNo; $i++){
			$filePath = $fileList[$i];
			$relativeFilePath = substr($filePath, strlen($this->photoBasePath));

			//$pathParts = pathinfo($filePath);
			////$file = basename($filePath);
			//$file = $pathParts['basename'];
//			$file = end(explode('/', $filePath));			// pathinfo,basenameは日本語処理できないので日本語対応
			$filePathArray = explode('/', $filePath);		// pathinfo,basenameは日本語処理できないので日本語対応
			$file = end($filePathArray);
			$size = '';
			$fileLink = '';
			$filenameOption = '';			// ファイル名オプション
			$code = '';			// 画像コード
			$checkDisabled = '';		// チェックボックス使用制御
			$statusImg = '';			// 状態
			$rating = '';			// 評価
			if (is_dir($filePath)){			// ディレクトリのとき
				// アイコン作成
				$iconUrl = $this->gEnv->getRootUrl() . self::FOLDER_ICON_FILE;
				$iconTitle = $this->convertToDispString($file);
				$pageUrl = $this->_baseUrl . '&task=imagebrowse&path=' . $relativeFilePath;
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
				
				$totalViewCount = '';		// 総参照数
			} else {		// 画像ファイルのとき
				// 画像情報取得
				$ret = self::$_mainDb->getPhotoInfo($file, $this->_langId, $row, $categoryRows);
				if ($ret){
					$serial = $row['ht_serial'];
					$code = $row['ht_code'];			// 画像コード
					$rating = $row['ht_rate_average'];			// 評価
					// 所属カテゴリー
					$categoryStr = '';
					$categoryCount = count($categoryRows);
					for ($j = 0; $j < $categoryCount; $j++){
						$categoryStr .= $categoryRows[$j]['hc_name'];
						if ($j < $categoryCount -1) $categoryStr .= ', ';
					}
					$filenameOption = '<br />元のファイル名： ' . $this->convertToDispString($row['ht_original_filename']);		// ファイル名オプション
					$filenameOption .= '<br />タイトル名： ' . $this->convertToDispString($row['ht_name']);		// タイトル名
					$filenameOption .= '<br />カテゴリー： ' . $this->convertToDispString($categoryStr);		// 所属カテゴリー
					
					// 使用限定ユーザの場合は、所有者でなければ削除できない
					if (self::$_isLimitedUser && $this->_userId != $row['ht_owner_id']){
						$checkDisabled = 'disabled ';		// チェックボックス使用制御
						$serial = -1;
					}
				} else {
					if (self::$_isLimitedUser){		// 使用限定ユーザの場合は削除不可
						$checkDisabled = 'disabled ';		// チェックボックス使用制御
						$serial = -1;
					}
				}
				// ファイル削除用チェックボックス
				//if (!$this->canDeleteFile || !is_writable($filePath)) $checkDisabled = 'disabled ';		// チェックボックス使用制御
				
				$thumbnailPath = photo_mainCommonDef::getThumbnailPath($file);
				
				// アイコン作成
				$iconTitle = $this->convertToDispString($file);
				if (file_exists($thumbnailPath)){	// サムネールが存在する場合
					$iconUrl = photo_mainCommonDef::getThumbnailUrl($file);
					$iconTag = '<a href="#" onclick="editItemBySerial(' . $serial . ');return false;">';
					$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::LIST_ICON_SIZE . '" height="' . self::LIST_ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					$iconTag .= '</a>';
				} else {
					$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
					$iconTag = '<a href="#" onclick="editItemBySerial(' . $serial . ');return false;">';
					$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					$iconTag .= '</a>';
				}
				
				//$fileLink = $this->convertToDispString($file);
				$fileLink = '<a href="#" onclick="editItemBySerial(' . $serial . ');return false;">' . $this->convertToDispString($file) . '</a>';
				$size = filesize($filePath);
				
				// 総参照数
				//$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(photo_mainCommonDef::REF_TYPE_CONTENT, $row['ht_id']);
				$totalViewCount = $row['ht_view_count'];
				
				// 公開状態
				$isActive = false;		// 公開状態
				if ($row['ht_visible']) $isActive = true;// 表示可能
		
				if ($isActive){		// コンテンツが公開状態のとき
					$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
					$iconTitle = $this->_('Published');
				} else {
					$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
					$iconTitle = $this->_('Unpublished');
				}
				$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			}
	
			// ファイル更新日時
			$updateDate = date('Y/m/d H:i:s', filemtime($filePath));
			
			$row = array(
				'serial'	=> $serial,
				'index'		=> $index,			// インデックス番号(チェックボックス識別)
				'icon'		=> $iconTag,		// アイコン
				'name'		=> $this->convertToDispString($file),			// ファイル名
				'filename'    	=> $fileLink,			// ファイル名
				'filename_option'    	=> $filenameOption,			// ファイル名オプション
				'code'		=> $this->convertToDispString($code),			// 画像コード
				'size'     		=> $size,			// ファイルサイズ
				'date'    => $updateDate,			// 更新日時
				'view_count' => $totalViewCount,									// 総参照数
				'rate' => $this->convertToDispString($rating),									// 評価
				'status' => $statusImg,												// 公開状況
				'check_disabled' => $checkDisabled,		// チェックボックス使用制御
			);
			$this->tmpl->addVars('file_list', $row);
			$this->tmpl->parseTemplate('file_list', 'a');
			
			// インデックス番号を保存
			$this->serialArray[] = $serial;
			$index++;
			$this->fileListAdded = true;				// 一覧にデータが追加されたかどうか
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
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getScriptsUrl() . self::CSS_FILE);
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
	 * サムネールを作成
	 *
	 * @param string $path		ファイルパス
	 * @param int $type			画像タイプ
	 * @param string $destPath	出力ファイル保存のパス
	 * @param int $destType		出力画像タイプ
	 * @param int $size			サムネールの縦横サイズ
	 * @return bool				true=成功、false=失敗
	 */
	function createThumbImage($path, $type, $destPath, $destType, $size)
	{
		// 画像作成
		switch ($type){
			case IMAGETYPE_GIF:
				$image = @imagecreatefromgif($path);
				break;
			case IMAGETYPE_JPEG:
				$image = @imagecreatefromjpeg($path);
				break;
			case IMAGETYPE_PNG:
				$image = @imagecreatefrompng($path);
				break;
			default:
				return false;
		}

		// 画像サイズ取得
		$width = imagesx($image);
		$height = imagesy($image);

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
		
		// サムネールの背景色を取得
		$bgColor = self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_BG_COLOR];	// サムネール背景色
		$bgColorR = intval(substr($bgColor, 1, 2), 16);
		$bgColorG = intval(substr($bgColor, 3, 2), 16);
		$bgColorB = intval(substr($bgColor, 5, 2), 16);

		// TrueColorイメージを作成
		$thumb = imagecreatetruecolor($size, $size);
		//$bgcolor = imagecolorallocate($thumb, 255, 255, 255);		// 背景色設定
		$bgcolor = imagecolorallocate($thumb, $bgColorR, $bgColorG, $bgColorB);		// 背景色設定
		imagefill($thumb, 0, 0, $bgcolor);
		
		// 画像リサイズ
		// imagecopyresampledの方がimagecopyresizedよりも画質が良いのでこちらを使用
		if (function_exists("imagecopyresampled")){
			if (!imagecopyresampled($thumb, $image, $x, $y, 0, 0, $n_width, $n_height, $width, $height)){
				if (!imagecopyresized($thumb, $image, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false;
			}
		} else {
			if (!imagecopyresized($thumb, $image, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false;
		}

		// 画像出力
		switch ($destType){
			case IMAGETYPE_GIF:
				$ret = @imagegif($thumb, $destPath, self::IMAGE_QUALITY);
				break;
			case IMAGETYPE_JPEG:
				$ret = @imagejpeg($thumb, $destPath, self::IMAGE_QUALITY);
				break;
			case IMAGETYPE_PNG:
				$ret = @imagepng($thumb, $destPath, self::IMAGE_QUALITY);
				break;
		}
		// イメージを破棄
		$ret = imagedestroy($image);
		$ret = imagedestroy($thumb);
		return $ret;
	}
	/**
	 * ウォータマーク入り公開画像を作成
	 *
	 * @param string $path		元画像ファイルパス
	 * @param int $type			元画像の画像タイプ
	 * @param string $wPath		ウォータマーク画像ファイルパス
	 * @param int $wType		ウォータマーク画像の画像タイプ
	 * @param string $destPath	出力画像のパス
	 * @param int $destType		出力画像の画像タイプ
	 * @param int $size			出力画像の縦または横の最大サイズ
	 * @return bool				true=成功、false=失敗
	 */
	function createPublicImage($path, $type, $wPath, $wType, $destPath, $destType, $size)
	{
		// 画像作成
		switch ($type){
			case IMAGETYPE_GIF:
				$image = @imagecreatefromgif($path);
				break;
			case IMAGETYPE_JPEG:
				$image = @imagecreatefromjpeg($path);
				break;
			case IMAGETYPE_PNG:
				$image = @imagecreatefrompng($path);
				break;
			default:
				return false;
		}
		
		// 画像サイズ取得
		$width = imagesx($image);
		$height = imagesy($image);
		
		// 出力サイズ取得
		$destWidth = $size;
		$destHeight = $size;
		$imageRatio = $width / $height;
		if ($destWidth / $destHeight > $imageRatio){
			$destWidth = $destHeight * $imageRatio;
		} else {
			$destHeight = $destWidth / $imageRatio;
		}
		// 出力画像作成
		// imagecopyresampledの方がimagecopyresizedよりも画質が良いのでこちらを使用
		$destImage = imagecreatetruecolor($destWidth, $destHeight);
		imagecopyresampled($destImage, $image, 0, 0, 0, 0, $destWidth, $destHeight, $width, $height);
		
		if (!empty(self::$_configArray[photo_mainCommonDef::CF_IMAGE_PROTECT_COPYRIGHT])){	// 著作権保護ありの場合
			// 画像作成
			switch ($wType){
				case IMAGETYPE_GIF:
					$wImage = @imagecreatefromgif($wPath);
					break;
				case IMAGETYPE_JPEG:
					$wImage = @imagecreatefromjpeg($wPath);
					break;
				case IMAGETYPE_PNG:
					$wImage = @imagecreatefrompng($wPath);
					break;
				default:
					return false;
			}
		
			// 画像サイズ取得
			$wWidth = imagesx($wImage);
			$wHeight = imagesy($wImage);
		
			$startwidth = ($destWidth - $wWidth) / 2; 
			$startheight = ($destHeight - $wHeight) / 2;
			imagecopymerge($destImage, $wImage, $startwidth, $startheight, 0, 0, $wWidth, $wHeight, 10);
		}
		
		// 画像出力
		switch ($destType){
			case IMAGETYPE_GIF:
				$ret = @imagegif($destImage, $destPath, self::IMAGE_QUALITY);
				break;
			case IMAGETYPE_JPEG:
				$ret = @imagejpeg($destImage, $destPath, self::IMAGE_QUALITY);
				break;
			case IMAGETYPE_PNG:
				$ret = @imagepng($destImage, $destPath, self::IMAGE_QUALITY);
				break;
		}
		// イメージを破棄
		$ret = imagedestroy($destImage);
		$ret = imagedestroy($image);
		if (isset($wImage)) $ret = imagedestroy($wImage);
		return $ret;
	}
	/**
	 * 指定数分の画像カテゴリー取得
	 *
	 * @param array  	$srcRows			全取得行
	 * @param int		$categoryCount		取得行数
	 * @return array						取得した行
	 */
	function getCategory($srcRows, $categoryCount)
	{
		$destArray = array();
		$itemCount = 0;
		for ($i = 0; $i < count($srcRows); $i++){
			if (!empty($srcRows[$i]['hw_category_id'])){
				$destArray[] = $srcRows[$i]['hw_category_id'];
				$itemCount++;
				if ($itemCount >= $categoryCount) break;
			}
		}
		return $destArray;
	}
	/**
	 * 画像カテゴリーメニューを作成
	 *
	 * @param array $categoryRows	カテゴリー情報
	 * @param array $categoryArray	選択中のカテゴリー
	 * @param int  	$size			メニューの表示数
	 * @return なし						
	 */
	function createCategoryMenu($categoryRows, $categoryArray, $size)
	{
		for ($j = 0; $j < $size; $j++){
			// selectメニューの作成
			$this->tmpl->clearTemplate('category_list');
			for ($i = 0; $i < count($categoryRows); $i++){
				$categoryId = $categoryRows[$i]['hc_id'];
				$selected = '';
				if ($j < count($categoryArray) && $categoryArray[$j] == $categoryId){
					$selected = 'selected';
				}
				$menurow = array(
					'value'		=> $categoryId,			// カテゴリーID
					'name'		=> $categoryRows[$i]['hc_name'],			// カテゴリー名
					'selected'	=> $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('category_list', $menurow);
				$this->tmpl->parseTemplate('category_list', 'a');
			}
			$itemRow = array(		
					'index'		=> $j			// 項目番号											
			);
			$this->tmpl->addVars('category', $itemRow);
			$this->tmpl->parseTemplate('category', 'a');
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
		$thumbailSizeArray = trimExplode(',', self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_SIZE]);
		if (empty($thumbailSizeArray)) $thumbailSizeArray = array(photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE);
		for ($i = 0; $i < count($thumbailSizeArray); $i++){
			$thumbnailSize = $thumbailSizeArray[$i];
			$thumbnailPath = photo_mainCommonDef::getThumbnailPath($photoId, $thumbnailSize);
			//$ret = $this->createThumbImage($imagePath, $imageType, $thumbnailPath, self::DEFAULT_IMAGE_TYPE, $thumbnailSize);
			if (self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_CROP]){		// 切り取りサムネールの場合
				$ret = $this->gInstance->getImageManager()->createThumb($imagePath, $thumbnailPath, $thumbnailSize, self::DEFAULT_IMAGE_TYPE, true);
			} else {
				$ret = $this->gInstance->getImageManager()->createThumb($imagePath, $thumbnailPath, $thumbnailSize, self::DEFAULT_IMAGE_TYPE, false, self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_BG_COLOR]);
			}
			if (!$ret){
				$this->writeError(__METHOD__, '画像ファイル作成に失敗しました。', 1100,
									'元のファイル名=' . $originalFilename . ', 画像ファイル=' . $thumbnailPath);// 運用ログに記録
			}
		}

		// ##### ウォータマーク画像作成 #####
		if ($ret){
			$watermarkPath = $this->gEnv->getCurrentWidgetRootPath() . '/images/default_mark.png';
			$watermarkSize = @getimagesize($watermarkPath);
			if ($watermarkSize) $watermarkType = $watermarkSize[2];	// ファイルタイプを取得
			$imageMaxSize = intval(self::$_configArray[photo_mainCommonDef::CF_DEFAULT_IMAGE_SIZE]);
			if ($imageMaxSize <= 0) $imageMaxSize = photo_mainCommonDef::DEFAULT_IMAGE_SIZE;
			//$ret = $this->createPublicImage($imagePath, $imageType, $watermarkPath, $watermarkType,
			//					photo_mainCommonDef::getPublicImagePath($photoId), self::DEFAULT_IMAGE_TYPE, photo_mainCommonDef::DEFAULT_PUBLIC_IMAGE_SIZE);
			$ret = $this->createPublicImage($imagePath, $imageType, $watermarkPath, $watermarkType,
								photo_mainCommonDef::getPublicImagePath($photoId), self::DEFAULT_IMAGE_TYPE, $imageMaxSize);
			if (!$ret){
				$this->writeError(__METHOD__, '画像ファイル作成に失敗しました。', 1100,
									'元のファイル名=' . $originalFilename . ', 画像ファイル=' . $watermarkPath);// 運用ログに記録
			}
		}
		return $ret;
	}
	/**
	 * 各種画像削除
	 *
	 * @param array $photoIdArray			公開画像ID
	 * @param array $systemFiles			システム用画像ファイル
	 * @return bool 						true=成功、失敗
	 */
	function deleteImages($photoIdArray, $systemFiles)
	{
		$ret = true;
		for ($i = 0; $i < count($photoIdArray); $i++){
			// サムネール削除
			//$thumbnailPath = photo_mainCommonDef::getThumbnailPath($photoIdArray[$i]);
			//if (!@unlink($thumbnailPath)) $ret = false;
			$thumbailSizeArray = trimExplode(',', self::$_configArray[photo_mainCommonDef::CF_THUMBNAIL_SIZE]);
			if (empty($thumbailSizeArray)) $thumbailSizeArray = array(photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE);
			for ($j = 0; $j < count($thumbailSizeArray); $j++){
				$thumbnailSize = $thumbailSizeArray[$j];
				$thumbnailPath = photo_mainCommonDef::getThumbnailPath($photoIdArray[$i], $thumbnailSize);
				if (!@unlink($thumbnailPath)) $ret = false;
			}
			// 公開画像削除
			$publicImagePath = photo_mainCommonDef::getPublicImagePath($photoIdArray[$i]);
			if (!@unlink($publicImagePath)) $ret = false;
			
			// 作成したシステム用サムネール削除
			$oldFiles = explode(';', $systemFiles[$i]);
			$this->gInstance->getImageManager()->delSystemDefaultThumb(M3_VIEW_TYPE_PHOTO, 0/*PC用*/, $oldFiles);
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
		
		$account = $this->gEnv->getCurrentUserAccount();
		
		// ファイル名を作成
		$code = $account . '-' . self::$_mainDb->getNewPhotoNo($this->_userId);		// 画像コード
		$ret = self::$_mainDb->isExistsPhotoCode($code);		// 画像コードの重複確認
		if ($ret){		// 画像コードが重複するとき
			$errMessage = '画像コードが重複しています。';
			$errDetail = '画像コード=' . $code;
		} else {
			// ファイル名は画像ファイルのハッシュを取得
			//$filename = md5($code . time());
			//$filename = md5_file($_FILES['Filedata']['tmp_name']);
			$filename = md5_file($filePath);			// オリジナル画像の実体のハッシュ値を取得
			//$originalFilename = $_FILES['Filedata']['name'];		// 元のファイル名
			$originalFilename = $resultObj['file']['filename'];	// 元のファイル名
			$destFilePath = $this->path . DIRECTORY_SEPARATOR . $filename;
			
			// 画像のアップロード状況確認
			$ret = self::$_mainDb->isExistsPhoto($filename);
			if ($ret){
				$errMessage = '画像ファイルが重複しています。';
				$errDetail = '画像ID=' . $filename . ', 元のファイル名=' . $originalFilename;
			}
		}

		if (!$ret){		// 画像の重複がない場合
			if (file_exists($destFilePath)) $this->writeError(__METHOD__, 'アップロード画像がすでに存在しています。新しい画像で置き換えます。', 1100, '元のファイル名=' . $originalFilename);

			// アップされたファイルをコピー
			//$ret = move_uploaded_file($resultObj['file']['path'], $destFilePath);
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
						$this->writeUserError(__METHOD__, 'アップロード画像のタイプが不正です。アカウント: ' . $account, 2200, '元のファイル名=' . $originalFilename);
					}
				} else {
					$isErr = true;
					$this->writeUserError(__METHOD__, 'アップロード画像が不正です。アカウント: ' . $account, 2200, '元のファイル名=' . $originalFilename);
				}

				if (!$isErr){
					// 各種画像作成
					$ret = $this->createImages($filename, $destFilePath, $originalFilename, $thumbFilename);
					if (!$ret) $isErr = true;
					
					if (!$isErr){
						//$relativePath = strtr(str_replace($this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR, '', $path), '\\', '/');		// 画像格納ディレクトリ
						$relativePath = strtr(substr($path, strlen($this->gEnv->getIncludePath() . photo_mainCommonDef::PHOTO_DIR)), '\\', '/');		// 画像格納ディレクトリ
						$imageSize = $imageWidth . 'x' . $imageHeight;		// 画像の縦横サイズ
						$filesize = filesize($destFilePath);;			// ファイルサイズ(バイト数)
						$name = removeExtension($originalFilename);		// 画像名
						$name = strtr($name, '_', ' ');
						$camera = '';	// カメラ
						$location = '';	// 場所
						$date = $this->gEnv->getInitValueOfDate();		// 撮影日
						$summary = '';		// 画像概要
						$description = '';			// その他情報
						$license = '';		// ライセンス
						$keyword = '';		// 検索情報
						$visible = true;	// 表示
						$ret = self::$_mainDb->updatePhotoInfo(self::$_isLimitedUser, 0/*新規追加*/, $this->_langId, $filename, $relativePath, $code, $imageMimeType,
										$imageSize, $originalFilename, $filesize, $name, $camera, $location, $date, $summary, $description, $license, $this->_userId, 
										$keyword, $visible, 0/*ソート順*/, array()/*画像カテゴリー*/, $thumbFilename, $newSerial);
						if ($ret){
							// 運用ログを追加
							$ret = self::$_mainDb->getPhotoInfoBySerial($newSerial, $row, $categoryRows);
							if ($ret){
								$photoId = $row['ht_public_id'];
								$name = $row['ht_name'];
								$updateDt = $row['ht_create_dt'];		// コンテンツ作成日時
							}
							$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_PHOTO,
													M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $photoId,
													M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
							$this->writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_ADD_CONTENT, $name), 2400, 'ID=' . $photoId, $eventParam);
						} else {
							$isErr = true;
							$this->writeError(__METHOD__, 'アップロード画像のデータ登録に失敗しました。', 1100, '元のファイル名=' . $originalFilename);
						}
					}
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
		} else {		// 画像が重複している場合
			// エラーコード設定
			$resultObj = array('error' => $errMessage);
			
			$this->writeError(__METHOD__, $errMessage, 1100, $errDetail);
		}
	}
}
?>
