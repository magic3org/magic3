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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainFilebrowseWidgetContainer.php 5830 2013-03-15 13:44:37Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainBrowseBaseWidgetContainer.php');

class admin_mainFilebrowseWidgetContainer extends admin_mainBrowseBaseWidgetContainer
{
	private $canDeleteFile;		// ファイル削除可能かどうか
	private $serialArray = array();		// 表示されているファイルのインデックス番号
	const FILE_ICON_FILE = '/images/system/tree/file.png';			// ファイルアイコン
	const FOLDER_ICON_FILE = '/images/system/tree/folder.png';		// ディレクトリアイコン
	const PARENT_ICON_FILE = '/images/system/tree/parent.png';		// 親ディレクトリアイコン
	const ICON_SIZE = 16;		// アイコンのサイズ
	const CSS_FILE = '/swfupload2.5/css/default.css';		// CSSファイルのパス
	const MAX_FILE_SIZE = '10 MB';			// アップロード可能な最大ファイルサイズ
	
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
		return 'filebrowse.tmpl.html';
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
		$path = trim($request->valueOf('path'));		// 現在のパス
		if (empty($path)) $path = $this->gEnv->getResourcePath();// デフォルトはリソースディレクトリ

		$act = $request->trimValueOf('act');
		if ($act == 'uploadfile'){		// ファイルアップロードのとき
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// ファイルのアップロード処理
			if (isset($_FILES["Filedata"]) && is_uploaded_file($_FILES['Filedata']['tmp_name'])){		// アップロードファイルがある場合
				$uploadFilename = $_FILES['Filedata']['name'];		// アップロードされたファイルのファイル名取得
				$filePath = $path . DIRECTORY_SEPARATOR . $uploadFilename;

				// アップされたファイルをコピー
				$ret = move_uploaded_file($_FILES['Filedata']['tmp_name'], $filePath);
				if (!$ret){
					header("HTTP/1.1 595 File Upload Error");		// エラーコードはブラウザ画面に表示される
				}
			} else {			// アップロードファイルがないとき
				header("HTTP/1.1 596 File Upload Error");			// エラーコードはブラウザ画面に表示される
				if (isset($_FILES["Filedata"])) echo $_FILES["Filedata"]["error"];
			}
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else if ($act == 'delete'){			// ファイル削除のとき
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					// 削除可能かチェック
					$filename = $request->trimValueOf('item' . $i . '_name');
					$filePath = $path . DIRECTORY_SEPARATOR . $filename;
					if (is_writable($filePath) &&
						strStartsWith($filePath, $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . M3_DIR_NAME_RESOURCE . DIRECTORY_SEPARATOR)){
						$delItems[] = $filePath;		// 削除するファイルのパス
					} else {
						//$this->setMsg(self::MSG_USER_ERR, '削除できないファイルが含まれています。ファイル名=' . $this->convertToDispString($filename));
						$this->setMsg(self::MSG_USER_ERR, sprintf($this->_('Include files not allowed to delete. (filename: %s)'), $this->convertToDispString($filename)));		// 削除できないファイルが含まれています。(ファイル名： %s)
						break;
					}
				}
			}
			if ($this->getMsgCount() == 0 && count($delItems) > 0){
				$ret = true;
				for ($i = 0; $i < count($delItems); $i++){
					$delItem = $delItems[$i];
					if (is_dir($delItem)){		// ディレクトリのとき
						if (!rmDirectory($delItem)) $ret = false;
					} else {
						if (!unlink($delItem)) $ret = false;
					}
				}
				if ($ret){		// ファイル削除成功のとき
					//$this->setGuidanceMsg('ファイルを削除しました');
					$this->setGuidanceMsg($this->_('Files deleted.'));			// ファイルを削除しました
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
		if ($path == $this->gEnv->getSystemRootPath()){
			$pathLink = $this->gEnv->getSystemRootPath();
		} else {
			$pathLink = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString($this->gEnv->getSystemRootPath())) . '\');return false;">' . $this->convertToDispString($this->gEnv->getSystemRootPath()) . '</a>';
		}

		$relativePath = str_replace($this->gEnv->getSystemRootPath(), '', $path);
		$relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
		if (!empty($relativePath)){
			$absPath = $this->gEnv->getSystemRootPath();
			$pathArray = explode(DIRECTORY_SEPARATOR, $relativePath);
			for ($i = 0; $i < count($pathArray); $i++){
				if ($i == count($pathArray) -1){
					$pathLink .= '&nbsp;' . DIRECTORY_SEPARATOR . '&nbsp;' . $this->convertToDispString($pathArray[$i]);
				} else {
					$absPath .= DIRECTORY_SEPARATOR . $pathArray[$i];
					$pathLink .= '&nbsp;' . DIRECTORY_SEPARATOR . '&nbsp;';
					$pathLink .= '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString($absPath)) . '\');return false;">' . $this->convertToDispString($pathArray[$i]) . '</a>';
				}
			}
		}
		// ファイル削除可能かどうか(resourceディレクトリ以下のみ削除可能)
		$this->canDeleteFile = false;
		if ($relativePath == M3_DIR_NAME_RESOURCE || strStartsWith($relativePath, M3_DIR_NAME_RESOURCE . DIRECTORY_SEPARATOR)){
			$this->canDeleteFile = true;
		} else {
			// 削除ボタンを使用可能にする
			$this->tmpl->addVar("_widget", "del_disabled", 'disabled ');
		}
		
		// ファイル一覧を作成
		$this->createFileList($path);
		
		$this->tmpl->addVar('_widget', 'path', $path);// 現在のディレクトリ
		$this->tmpl->addVar('_widget', 'path_link', $pathLink);// 現在のディレクトリ
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "max_file_size", self::MAX_FILE_SIZE);			// アップロード可能な最大ファイルサイズ
		$this->tmpl->addVar('_widget', 'directory_name', $this->convertToDispString($dirName));// ディレクトリ作成用
		
		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;	// ウィジェットを単体実行
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . 'filebrowse';
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadfile';
		$uploadUrl .= '&' . M3_REQUEST_PARAM_ADMIN_KEY . '=' . $this->gEnv->getAdminKey();	// 管理者キー
		$uploadUrl .= '&path=' . $this->adaptWindowsPath($path);					// アップロードディレクトリ
		$this->tmpl->addVar("_widget", "upload_url", $this->getUrl($uploadUrl));
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_file_upload'] = $this->_('Files uploaded. Refresh file list?');		// アップロード終了しました。ファイル一覧を更新しますか?
		$localeText['msg_select_file'] = $this->_('Select files to delete.');		// 削除する項目を選択してください
		$localeText['msg_delete_file'] = $this->_('Delete selected files?');		// 選択項目を削除しますか?
		$localeText['msg_create_directory'] = $this->_('Create directory?');		// ディレクトリを作成しますか?
		$localeText['label_path'] = $this->_('Path:');		// パス
		$localeText['label_delete'] = $this->_('Delete');		// 削除
		$localeText['label_check'] = $this->_('Select');		// 選択
		$localeText['label_filename'] = $this->_('Filename');		// ファイル名
		$localeText['label_size'] = $this->_('Size');		// サイズ
		$localeText['label_permission'] = $this->_('Permission');		// パーミッション
		$localeText['label_date'] = $this->_('Update Date');		// 更新日時
		$localeText['label_owner'] = $this->_('Owner');		// 所有者
		$localeText['label_group'] = $this->_('Group');		// グループ
		$localeText['label_count'] = $this->_('Items');		// 件目
		$localeText['label_upload_file'] = $this->_('Upload File');		// ファイルアップロード
		$localeText['label_filesize'] = $this->_('Max Filesize');		// 1ファイル最大サイズ
		$localeText['label_select_file'] = $this->_('Select Files');		// ファイルを選択
		$localeText['label_cancel'] = $this->_('Cancel');// キャンセル
		$localeText['label_create_directory'] = $this->_('Create Directory');// ディレクトリ作成
		$localeText['label_directory_name'] = $this->_('Directory Name');// ディレクトリ名
		$localeText['label_create'] = $this->_('Create');// 作成
		$this->setLocaleText($localeText);
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path	ディレクトリパス
	 * @return なし
	 */
	function createFileList($path)
	{
		if (is_dir($path)){
			// 親ディレクトリを追加
			if ($path != $this->gEnv->getSystemRootPath()){
				$file = '..';
				$fileLink = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString(dirname($path))) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
				
				// アイコン作成
				$iconTitle = $file;
				$iconUrl = $this->gEnv->getRootUrl() . self::PARENT_ICON_FILE;
				$iconTag = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString(dirname($path))) . '\');return false;">';
				$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
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
			$dir = dir($path);
			while (($file = $dir->read()) !== false){
				$filePath = $path . DIRECTORY_SEPARATOR . $file;
				// カレントディレクトリかどうかチェック
				if ($file != '.' && $file != '..'){
					$size = '';
					$fileLink = '';
					$checkDisabled = '';		// チェックボックス使用制御
					if (is_dir($filePath)){			// ディレクトリのとき
						// アイコン作成
						$iconUrl = $this->gEnv->getRootUrl() . self::FOLDER_ICON_FILE;
						$iconTitle = $this->convertToDispString($file);
						$iconTag = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString($filePath)) . '\');return false;">';
						$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
						$iconTag .= '</a>';
					
						$fileLink = '<a href="#" onclick="selDir(\'' . $this->adaptWindowsPath($this->convertToDispString($filePath)) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
						// ディレクトリが削除できるかチェック。「.」「..」は除く。
						$files = scandir($filePath);
						if (count($files) > 2) $checkDisabled = 'disabled ';		// チェックボックス使用制御
					} else {
						// アイコン作成
						$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
						$iconTitle = $this->convertToDispString($file);
						$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
						
						$fileLink = $this->convertToDispString($file);
						$size = filesize($filePath);
						
						// ファイル削除用チェックボックス
						if (!$this->canDeleteFile || !is_writable($filePath)) $checkDisabled = 'disabled ';		// チェックボックス使用制御
					}
		
					// ファイルパーミッションの取得
					$perms = fileperms($filePath);
					$permStr = '';
					
					// 所有者
					$permStr .= (($perms & 0x0100) ? 'r' : '-');
					$permStr .= (($perms & 0x0080) ? 'w' : '-');
					$permStr .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

					// グループ
					$permStr .= (($perms & 0x0020) ? 'r' : '-');
					$permStr .= (($perms & 0x0010) ? 'w' : '-');
					$permStr .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

					// 全体
					$permStr .= (($perms & 0x0004) ? 'r' : '-');
					$permStr .= (($perms & 0x0002) ? 'w' : '-');
					$permStr .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
			
					// ファイル更新日時
					$updateDate = date('Y/m/d H:i:s', filemtime($filePath));
					
					// ファイル所有者
					$owner = '';
					if (function_exists('posix_getpwuid')){
						$ownerArray = posix_getpwuid(fileowner($filePath));
						$owner = $ownerArray['name'];
					}
					$group = '';
					if (function_exists('posix_getgrgid')){
						$groupArray = posix_getgrgid(filegroup($filePath));
						$group = $groupArray['name'];
					}
					
					$row = array(
						'index'		=> $index,			// インデックス番号
						'icon'		=> $iconTag,		// アイコン
						'name'		=> $this->convertToDispString($file),			// ファイル名
						'filename'    	=> $fileLink,			// ファイル名
						'size'     		=> $size,			// ファイルサイズ
						'permission'    => $permStr,			// ファイルパーミッション
						'date'    => $updateDate,			// 更新日時
						'owner'    => $this->convertToDispString($owner),			// ファイル所有者
						'group'    => $this->convertToDispString($group),			// ファイル所有グループ
						'check_disabled' => $checkDisabled,		// チェックボックス使用制御
					);
					$this->tmpl->addVars('file_list', $row);
					$this->tmpl->parseTemplate('file_list', 'a');
					
					// インデックス番号を保存
					$this->serialArray[] = $index;
					$index++;
				}
			}
			$dir->close();
		}
		//sort($fileList);		// ファイル名をソート
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
}
?>
