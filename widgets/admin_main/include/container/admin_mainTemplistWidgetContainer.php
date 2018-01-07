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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainTempBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath()				. '/pcl/pclzip.lib.php' );
require_once($gEnvManager->getCurrentWidgetContainerPath()		. '/admin_mainDef.php');			// 定義クラス
require_once($gEnvManager->getCommonPath()		. '/archive.php');
// Joomlaテンプレート用
require_once($gEnvManager->getJoomlaRootPath() . '/JParameter.php');
require_once($gEnvManager->getJoomlaRootPath() . '/JRender.php');
				
class admin_mainTemplistWidgetContainer extends admin_mainTempBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $newTemplate = array();		// 新規追加テンプレート
	private $defalutTemplate;	// デフォルトのテンプレート
	private $templateTypeArray;		// テンプレートタイプ
	private $templateType;			// 現在のテンプレートタイプ
	private $isExistsTemplateList;		// テンプレートが存在するかどうか
//	const BREADCRUMB_TITLE			= 'テンプレート管理';		// 画面タイトル名(パンくずリスト)
	const TITLE_INFO_URL			= 'テンプレートの情報';		// テンプレート情報URLのタイトル
	const TEMPLATE_THUMBNAIL_FILENAME = 'template_thumbnail.png';		// テンプレートサムネール
	const TEMPLATE_THUMBNAIL_FILENAME_WP = 'screenshot';				// テンプレートサムネール(WordPressテンプレート)
	const previewImageSizeHeight = 27;
	const previewImageSizeWidth = 42;
	const imageSizeHeight = 135;
	const imageSizeWidth = 210;
	const JOOMLA_CONFIG_FILENAME = 'templateDetails.xml';		// Joomla!のテンプレート設定ファイル名
	const NOT_FOUND_TEMPLATE_ICON_FILE = '/images/system/notfound32.png';		// テンプレートが見つからないアイコン
	const DOWNLOAD_ZIP_ICON_FILE = '/images/system/download_zip32.png';		// Zipダウンロード用アイコン
//	const UPLOAD_ICON_FILE = '/images/system/upload32.png';		// ウィジェットアップロード用アイコン
	const RELOAD_ICON_FILE = '/images/system/reload32.png';		// 再読み込み用アイコン
//	const AREA_OPEN_ICON_FILE = '/images/system/area_open32.png';		// 拡張領域表示アイコン
	const THEMLER_APP_FILENAME = '/app/themler.version';		// Themlerテンプレートかどうかを判断するためのファイル
	const DEFAULT_IMAGE_DIR = '/images';		// デフォルトの画像格納ディレクトリ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		// テンプレートタイプメニュー項目
		$this->templateTypeArray = array(	array(	'name' => $this->_('For PC'),			'value' => '0'),		// PC用
											array(	'name' => $this->_('For Mobile'),		'value' => '1'),		// 携帯用
											array(	'name' => $this->_('For Smartphone'),	'value' => '2'));		// スマートフォン用
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
		return 'templist.tmpl.html';
	}
	/**
	 * ヘルプデータを設定
	 *
	 * ヘルプの設定を行う場合はヘルプIDを返す。
	 * ヘルプデータの読み込むディレクトリは「自ウィジェットディレクトリ/include/help」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ヘルプID。ヘルプデータはファイル名「help_[ヘルプID].php」で作成。ヘルプを使用しない場合は空文字列「''」を返す。
	 */
	function _setHelp($request, &$param)
	{	
		return 'templist';
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
/*	function _postAssign($request, &$param)
	{
		$this->gPage->setAdminBreadcrumbDef(array(self::BREADCRUMB_TITLE));
	}*/
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
		$selectedItemNo = $request->trimValueOf('no');		// 処理対象の項目番号
		$userId = $this->gEnv->getCurrentUserId();
		$templateId = $request->trimValueOf('template');		// テンプレートID
		$this->templateType = $request->trimValueOf('item_type');// 現在のテンプレートタイプ
		if ($this->templateType == '') $this->templateType = '0';		// デフォルトはPC用テンプレート
		
		if ($act == 'readnew'){		// テンプレート再読み込みのとき
			$addTemplateCount = 0;
			// テンプレート一覧取得
			if ($this->db->getAllTemplateIdList($rows)){
				// テンプレートディレクトリチェック
				switch ($this->templateType){
					case '0':		// PC用テンプレート
					default:
						$searchPath = $this->gEnv->getTemplatesPath();
						break;
					case '1':		// 携帯用テンプレート
						$searchPath = $this->gEnv->getTemplatesPath() . '/' . M3_DIR_NAME_MOBILE;
						break;
					case '2':		// スマートフォン用テンプレート
						$searchPath = $this->gEnv->getTemplatesPath() . '/' . M3_DIR_NAME_SMARTPHONE;
						break;
				}
				
				if (is_dir($searchPath)){
					$dir = dir($searchPath);
					while (($file = $dir->read()) !== false){
						$filePath = $searchPath . '/' . $file;
						// ディレクトリかどうかチェック
						if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath) &&
							strncmp($file, '_', 1) != 0	&&	// 「_」で始まる名前のディレクトリは読み込まない
							strlen($file) > 1 &&			// 携帯、スマートフォン用ディレクトリ「m」「s」は読み込まない
							!($this->templateType == 0 && $file == 'system')){				// PC用Joomla!デフォルトテンプレート「system」は読み込まない
							
							// テンプレートIDを作成
							switch ($this->templateType){
								case '0':		// PC用テンプレート
								default:
									$templateId = $file;
									break;
								case '1':		// 携帯用テンプレート
									$templateId = M3_DIR_NAME_MOBILE . '/' . $file;
									break;
								case '2':		// スマートフォン用テンプレート
									$templateId = M3_DIR_NAME_SMARTPHONE . '/' . $file;
									break;
							}
				
							// DBに登録されていない場合は登録
							for ($i = 0; $i < count($rows); $i++){
								if ($templateId == $rows[$i]['tm_id']) break;
							}
							if ($i == count($rows)){
								// テンプレートを新規登録
								$ret = $this->addNewTemplate(intval($this->templateType), $templateId);
								if ($ret){
									$this->newTemplate[] = $templateId;
									$addTemplateCount++;		// テンプレート追加
								}
							}
						}
					}
					$dir->close();
				}
			} else {
			}
			// 終了メッセージを表示
			if ($addTemplateCount > 0){
				//$msg = '新規テンプレートを追加しました(追加数=' . $addTemplateCount . ')';
				$msg = sprintf($this->_('New templates added. (templates count=%d)'), $addTemplateCount);			// 新規テンプレートを追加しました(追加数=%d)
			} else {
				//$msg = '新規テンプレートはありません';
				$msg = $this->_('No new templates added.');		// 新規テンプレートはありません
			}
			$this->setMsg(self::MSG_GUIDANCE, $msg);
		} else if ($act == 'deleteline'){		// テンプレート削除のとき
			// パラメータエラーチェック
			if (empty($templateId)) $this->setMsg(self::MSG_APP_ERR, $this->_('Template not selected.'));		// テンプレートが選択されていません
			
			if (!$this->isExistsMsg()){		// エラーなしのとき
				// テンプレートディレクトリ取得
				$templatePath = $this->gEnv->getTemplatesPath() . '/' . $templateId;
		
				// ディレクトリ削除
				if ((is_dir($templatePath) && rmDirectory($templatePath)) || !is_dir($templatePath)){// 削除成功か、ディレクトリが存在しないとき
					$ret = $this->db->deleteTemplate($templateId);
					if ($ret){		// データ更新成功のとき
						$this->setMsg(self::MSG_GUIDANCE, sprintf($this->_('Template deleted. (template ID: %s)'), $templateId));	// テンプレートを削除しました(テンプレートID：%s)
					} else {
						$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting template. (template ID: %s)'), $templateId));	// テンプレート削除に失敗しました(テンプレートID：%s)
					}
				} else {
					$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting template directory. (directory: %s)'), $templatePath));	// テンプレートのディレクトリが削除できませんでした(ディレクトリ：%s)
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				// 削除するコンテンツの情報を取得
				$delContentInfo = array();
				for ($i = 0; $i < count($delItems); $i++){
					// テンプレートディレクトリ取得
					$templateId = $delItems[$i];
					$templatePath = $this->gEnv->getTemplatesPath() . '/' . $templateId;
		
					// ディレクトリ削除
					if ((is_dir($templatePath) && rmDirectory($templatePath)) || !is_dir($templatePath)){// 削除成功か、ディレクトリが存在しないとき
						$ret = $this->db->deleteTemplate($templateId);
						if ($ret){		// データ更新成功のとき
							$this->setMsg(self::MSG_GUIDANCE, sprintf($this->_('Template deleted. (template ID: %s)'), $templateId));	// テンプレートを削除しました(テンプレートID：%s)
						} else {
							$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting template. (template ID: %s)'), $templateId));	// テンプレート削除に失敗しました(テンプレートID：%s)
						}
					} else {
						$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting template directory. (directory: %s)'), $templatePath));	// テンプレートのディレクトリが削除できませんでした(ディレクトリ：%s)
					}
				}
			}
		} else if ($act == 'upload'){		// ファイルアップロードの場合
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得

				// ファイル名の解析
				$pathParts = pathinfo($uploadFilename);
				$ext = $pathParts['extension'];		// 拡張子
				$templateName = basename($uploadFilename, '.' . $ext);		// 拡張子をはずす
				$ext = strtolower($ext);
				
				// ファイル拡張子のチェック
				if ($ext != 'zip'){
					//$msg = 'zip圧縮のファイルのみアップロード可能です';
					$msg = $this->_('Only zip format file is allowed to upload.');	// zip圧縮のファイルのみアップロード可能です
					$this->setAppErrorMsg($msg);
				}
				
				// テンポラリディレクトリの書き込み権限をチェック
				if (!is_writable($this->gEnv->getWorkDirPath())){
					//$msg = '一時ディレクトリに書き込み権限がありません。ディレクトリ：' . $this->gEnv->getWorkDirPath();
					$msg = sprintf($this->_('You are not allowed to write temporary directory. (directory: %s)'), $this->gEnv->getWorkDirPath());	// 一時ディレクトリに書き込み権限がありません。(ディレクトリ：%s)
					$this->setAppErrorMsg($msg);
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
		
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// 解凍先ディレクトリ取得
						$extDir = $this->gEnv->getTempDir();
						
						// ファイルを解凍
						$archiver = new Archive();
						$ret = $archiver->extract($tmpFile, $extDir, $ext);
						if ($ret){
							// 作成されたファイルを取得
							$fileList = getFileList($extDir);
							if (count($fileList) == 1 && is_dir($extDir . '/' . $fileList[0])){		// 単一ディレクトリのとき
								$srcTemplateDir = $extDir . '/' . $fileList[0];
							} else {
								// 設定ファイルを取得
								$srcTemplateDir = $extDir;
							}
							// テンプレートIDを求める
							$templateId = $this->getTemplateId($srcTemplateDir);
							
							// テンプレートIDが取得できないときは、送信されたファイルのファイル名を使用
							if (empty($templateId)) $templateId = $templateName;
							
							// テンプレートIDを修正
							switch ($this->templateType){
								case '0':		// PC用テンプレート
								default:
									break;
								case '1':		// 携帯用テンプレート
									$templateId = M3_DIR_NAME_MOBILE . '/' . $templateId;
									break;
								case '2':		// スマートフォン用テンプレート
									$templateId = M3_DIR_NAME_SMARTPHONE . '/' . $templateId;
									break;
							}
							
							// 同IDのテンプレートがないかチェック
							if ($this->db->getAllTemplateIdList($rows)){
								for ($i = 0; $i < count($rows); $i++){
									if ($templateId == $rows[$i]['tm_id']) break;
								}
								if ($i < count($rows)){
									//$msg = '同じIDのテンプレートがすでに存在します(テンプレートID：' . $templateId . ')';
									$msg = sprintf($this->_('The template already exists. (template ID: %s)'), $templateId);		// テンプレートがすでに存在します(テンプレートID：%s)
									$this->setAppErrorMsg($msg);
								}
							}
							
							if ($this->getMsgCount() == 0){		// エラーが発生していないとき
								// 既にディレクトリがないかチェック
								$destTemplateDir = $this->gEnv->getTemplatesPath() . '/' . $templateId;			// テンプレートの移動先
								if (file_exists($destTemplateDir)){
									//$msg = '同じIDのテンプレートディレクトリがすでに存在します(テンプレートID：' . $templateId . ')';
									$msg = sprintf($this->_('The template directory already exists. (directory: %s)'), $destTemplateDir);// テンプレートディレクトリがすでに存在します(ディレクトリ：%s)
									$this->setAppErrorMsg($msg);
								} else {
									// ディレクトリを移動
									$ret = @rename($srcTemplateDir, $destTemplateDir);
									if (!$ret) $ret = mvDirectory($srcTemplateDir, $destTemplateDir);		// 2009/7/17 異なるデバイス間でrenameできなかった問題に対応
									if ($ret){
										// テンプレートを新規登録
										$ret = $this->addNewTemplate(intval($this->templateType), $templateId);
							
										//$msg = 'ファイルのアップロードが完了しました(テンプレートID：' . $templateId . ')';
										$msg = sprintf($this->_('File uploaded. (template ID: %s)'), $templateId);		// ファイルのアップロードが完了しました(テンプレートID: %s)
										$this->setGuidanceMsg($msg);
										$this->newTemplate[] = $templateId;
									} else {
										//$msg = 'ディレクトリの移動に失敗しました(ディレクトリ：' . $destTemplateDir . ')';
										$msg = sprintf($this->_('Failed in moving directory. (directory: %s)'), $destTemplateDir);// ディレクトリの移動に失敗しました(ディレクトリ：%s)
										$this->setAppErrorMsg($msg);
									}
								}
							}
						}
						// 解凍用ディレクトリを削除
						if (file_exists($extDir)) rmDirectory($extDir);
					} else {
						//$msg = 'ファイルのアップロードに失敗しました';
						$msg = $this->_('Failed in uploading file.');		// ファイルのアップロードに失敗しました
						$this->setAppErrorMsg($msg);
					}
					// テンポラリファイル削除
					unlink($tmpFile);
				}
			} else {
				//$msg = 'アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります - ' . $this->gSystem->getMaxFileSizeForUpload() . 'バイト)';
				$msg = sprintf($this->_('Uploded file not found. (detail: The file may be over maximum size to be allowed to upload. Size %s bytes.'), $this->gSystem->getMaxFileSizeForUpload());	// アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。%sバイト)
				$this->setAppErrorMsg($msg);
			}
		} else if ($act == 'download'){		// ファイルダウンロードのとき
			switch ($this->templateType){
				case '0':		// PC用テンプレート
				default:
					$templatesDir = $this->gEnv->getTemplatesPath();		// テンプレートディレクトリ
					$templateDir = $templatesDir . '/' . $templateId;			// ダウンロードするテンプレートのディレクトリ
					$downloadFilename = $templateId . '.zip';		// ダウンロード時のファイル名
					break;
				case '1':		// 携帯用テンプレート
					$templatesDir = $this->gEnv->getTemplatesPath() . '/' . M3_DIR_NAME_MOBILE;		// テンプレートディレクトリ
					$templateDir = $this->gEnv->getTemplatesPath() . '/' . $templateId;			// ダウンロードするテンプレートのディレクトリ
					list($dir, $filename) = explode('/', $templateId);	// 先頭の「m/」を削除
					$downloadFilename = $filename . '.zip';		// ダウンロード時のファイル名
					break;
				case '2':		// スマートフォン用テンプレート
					$templatesDir = $this->gEnv->getTemplatesPath() . '/' . M3_DIR_NAME_SMARTPHONE;		// テンプレートディレクトリ
					$templateDir = $this->gEnv->getTemplatesPath() . '/' . $templateId;			// ダウンロードするテンプレートのディレクトリ
					list($dir, $filename) = explode('/', $templateId);	// 先頭の「s/」を削除
					$downloadFilename = $filename . '.zip';		// ダウンロード時のファイル名
					break;
			}
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD);		// zip処理用一時ファイル
			
			// zip圧縮
			$zipFile = new PclZip($tmpFile);
			$ret = $zipFile->create($templateDir, PCLZIP_OPT_REMOVE_PATH, $templatesDir);
			if ($ret){
				// ページ作成処理中断
				$this->gPage->abortPage();
				
				// ダウンロード処理
				$ret = $this->gPage->downloadFile($tmpFile, $downloadFilename, true/*実行後ファイル削除*/);
				
				// システム強制終了
				$this->gPage->exitSystem();
			} else {
				//$msg = 'ファイルのダウンロードに失敗しました(要因: ' . $zipFile->errorName(true) . ')';
				$msg = sprintf($this->_('Failed in downloading file. (detail: %s)'), $zipFile->errorName(true));		// ファイルのダウンロードに失敗しました(要因: %s)
				$this->setAppErrorMsg($msg);
				
				// テンポラリファイル削除
				unlink($tmpFile);
			}
		} else if ($act == 'changedefault'){		// デフォルトテンプレートの変更のとき
			// パラメータエラーチェック
			if (empty($templateId)) $this->setMsg(self::MSG_APP_ERR, $this->_('Template not selected.'));		// テンプレートが選択されていません
			
			if (!$this->isExistsMsg()){		// エラーなしのとき
				switch ($this->templateType){
					case '0':		// PC用テンプレート
					default:
						// デフォルトテンプレート変更
						$this->gSystem->changeDefaultTemplate($templateId);

						// セッションのテンプレートIDを更新
						$request->setSessionValue(M3_SESSION_CURRENT_TEMPLATE, $templateId);
						break;
					case '1':		// 携帯用テンプレート
						// デフォルトテンプレート変更
						$this->gSystem->changeDefaultMobileTemplate($templateId);
						break;
					case '2':		// スマートフォン用テンプレート
						// デフォルトテンプレート変更
						$this->gSystem->changeDefaultSmartphoneTemplate($templateId);
						break;
				}
				// キャッシュデータをクリア
				$this->gCache->clearAllCache();
			}
		} else if ($act == 'changetype'){		// テンプレートタイプの変更のとき
		} else if ($act == 'init'){		// テンプレート初期化のとき
			$ret = $this->initializeTemplate($templateId);
			if ($ret){
				$msg = sprintf($this->_('Template initialization completed. (template ID: %s)'), $templateId);		// テンプレート初期化完了しました。(テンプレートID: %s)
				$this->setMsg(self::MSG_GUIDANCE, $msg);
			} else {
				$msg = sprintf($this->_('Failed in template initializing. (template ID: %s)'), $templateId);		// テンプレート初期化に失敗しました。(テンプレートID: %s)
				$this->setMsg(self::MSG_APP_ERR, $msg);
			}
		}
			
		// テンプレートのタイプごとの処理
		switch ($this->templateType){
			case '0':		// PC用テンプレート
			default:
				$this->defalutTemplate = $this->gSystem->defaultTemplateId();// デフォルトのテンプレート
				$installDir = $this->gEnv->getTemplatesPath();// テンプレート格納ディレクトリ
				break;
			case '1':		// 携帯用テンプレート
				$this->defalutTemplate = $this->gSystem->defaultMobileTemplateId();// デフォルトのテンプレート
				$installDir = $this->gEnv->getTemplatesPath() . '/' . M3_DIR_NAME_MOBILE;// テンプレート格納ディレクトリ
				break;
			case '2':		// スマートフォン用テンプレート
				$this->defalutTemplate = $this->gSystem->defaultSmartphoneTemplateId();// デフォルトのテンプレート
				$installDir = $this->gEnv->getTemplatesPath() . '/' . M3_DIR_NAME_SMARTPHONE;// テンプレート格納ディレクトリ
				break;
		}
		// デフォルトのテンプレートが存在しない場合はエラーメッセージを表示
		if ($this->_db->getTemplate($this->defalutTemplate, $row)){
			$templateDir = $this->gEnv->getTemplatesPath() . '/' . $this->defalutTemplate;			// テンプレートのディレクトリ
			if (!file_exists($templateDir)){
				$msg = $this->_('Default template directory does not exist.');		// デフォルトテンプレートのディレクトリが存在しません。
				$this->setAppErrorMsg($msg);
			}
		} else {
			$msg = $this->_('Default template is not selected.');		// デフォルトテンプレートが選択されていません。
			$this->setAppErrorMsg($msg);
		}
		
		// テンプレート選択メニュー作成
		$this->createTemplateTypeMenu();
		
		// テンプレートリストを取得
		$this->db->getAllTemplateList(intval($this->templateType), array($this, 'tempListLoop'));
		if (!$this->isExistsTemplateList) $this->tmpl->setAttribute('templist', 'visibility', 'hidden');// テンプレートがないときは、一覧を表示しない
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "install_dir", $installDir);// インストールディレクトリ
		$this->tmpl->addVar("_widget", "admin_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理用URL
		$this->tmpl->addVar("_widget", "max_file_size", $this->gSystem->getMaxFileSizeForUpload(true/*数値のバイト数*/));			// アップロードファイルの最大サイズ
/*		// テンプレートアップロード
		$imageUrl = $this->getUrl($this->gEnv->getRootUrl() . self::UPLOAD_ICON_FILE);
		$imageTitle = 'テンプレートアップロード';
		$imageTag = '<img src="' . $imageUrl . '" width="32" height="32" border="0" alt="' . $imageTitle . '" title="' . $imageTitle . '" />';
		$this->tmpl->addVar("_widget", "upload_image", $imageTag);
		// 拡張表示アイコン
		$imageUrl = $this->getUrl($this->gEnv->getRootUrl() . self::AREA_OPEN_ICON_FILE);
		$imageTitle = '詳細表示';
		$imageTag = '<img src="' . $imageUrl . '" width="32" height="32" border="0" alt="' . $imageTitle . '" title="' . $imageTitle . '" />';
		$this->tmpl->addVar("_widget", "area_open_image", $imageTag);*/
		// 再読み込みアイコン
		$imageUrl = $this->getUrl($this->gEnv->getRootUrl() . self::RELOAD_ICON_FILE);
		$imageTitle = 'ディレクトリ再読み込み';
		$imageTag = '<img src="' . $imageUrl . '" width="32" height="32" border="0" alt="' . $imageTitle . '" title="' . $imageTitle . '" />';
		$this->tmpl->addVar("_widget", "reload_image", $imageTag);
		// その他
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_update_line'] = $this->_('Update line?');		// 行を更新しますか?
		$localeText['msg_delete_line'] = $this->_('Delete tmplate?');		// テンプレートを削除しますか?
		$localeText['msg_no_upload_file'] = $this->_('File not selected.');		// アップロードするファイルが選択されていません
		$localeText['msg_upload_file'] = $this->_('Upload file.');		// ファイルをアップロードします
		$localeText['label_template_list'] = $this->_('Template List');			// テンプレート一覧
//		$localeText['label_template_type'] = $this->_('Template Type:');			// テンプレートタイプ：
		$localeText['label_install_dir'] = $this->_('Install Directory:');			// インストールディレクトリ:
		$localeText['label_read_new'] = $this->_('Reload directory');			// ディレクトリ再読み込み
		$localeText['label_show_detail'] = $this->_('Show detail');			// 詳細表示
		$localeText['label_template_name'] = $this->_('Name');			// 名前
		$localeText['label_template_format'] = $this->_('Format');			// 形式
		$localeText['label_template_creator'] = $this->_('Genarator - Version');			// 生成ソフト - バージョン
		$localeText['label_template_default'] = $this->_('Default');			// デフォルト
		$localeText['label_template_date'] = $this->_('Update Date');			// 更新日時
		$localeText['label_template_operation'] = $this->_('Operation');			// 操作
		$localeText['label_template_upload'] = $this->_('Template Upload (zip compressed file)');			// テンプレートアップロード(zip圧縮ファイル)
		$localeText['msg_select_file'] = $this->_('Select file to upload.');			// アップロードするファイルを選択してください
		$localeText['label_upload'] = $this->_('Upload');			// アップロード
		$localeText['label_cancel'] = $this->_('Cancel');			// キャンセル
		$this->setLocaleText($localeText);
	}
	/**
	 * テンプレートリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function tempListLoop($index, $fetchedRow, $param)
	{
		$genarator = $fetchedRow['tm_generator'];			// テンプレート作成アプリケーション
		$version = $fetchedRow['tm_version'];				// テンプレートバージョン
		$infoUrl = $fetchedRow['tm_info_url'];				// テンプレート情報リンク

		// テンプレートが存在するかどうかチェック
		$isExistsTemplate = false;
		$templateId = $fetchedRow['tm_id'];// テンプレートID
		$templateDir = $this->gEnv->getTemplatesPath() . '/' . $templateId;			// テンプレートのディレクトリ
		if (file_exists($templateDir)) $isExistsTemplate = true;
		
/*		// デフォルトテンプレート
		$defaultCheck = '';
		if ($templateId == $this->defalutTemplate){
			$defaultCheck = 'checked ';
		}
		if (!$isExistsTemplate) $defaultCheck .= 'disabled';		// テンプレートが存在しないときは使用不可
		*/
		
		// 画面イメージ表示設定
		$name = $this->convertToDispString($fetchedRow['tm_name']);			// テンプレート名
		$templateIndexFile = $templateDir . '/index.php';						// テンプレートindex.phpファイル
		
		$imgUrl = '';
		if ($fetchedRow['tm_type'] == 100){		// WordPressテンプレートの場合
			// 画像を検索
			foreach (array('png', 'gif', 'jpg', 'jpeg') as $ext){
				$imgPath = $this->gEnv->getTemplatesPath() . '/' . $templateId . '/' . self::TEMPLATE_THUMBNAIL_FILENAME_WP . '.' . $ext;
				if (file_exists($imgPath)){
					$imgUrl = $this->gEnv->getTemplatesUrl() . '/' . $templateId . '/' . self::TEMPLATE_THUMBNAIL_FILENAME_WP . '.' . $ext;
					break;
				}
			}
		} else {
			$imgUrl = $this->gEnv->getTemplatesUrl() . '/' . $templateId . '/' . self::TEMPLATE_THUMBNAIL_FILENAME;
		}
		
		// 新規に追加されたテンプレートかチェック
		$idText = $this->convertToDispString($templateId);
		for ($i = 0; $i < count($this->newTemplate); $i++){
			if ($this->newTemplate[$i] == $templateId){
				$idText = '<b><font color="green">' . $this->convertToDispString($templateId) . '</font></b>';
				break;
			}
		}
		// デフォルトの場合はアイコンを付加
		if ($templateId == $this->defalutTemplate){
			$idText = '<span rel="m3help" data-container="body" title="デフォルトテンプレート"><i class="glyphicon glyphicon-ok-sign"></i></span> ' . $idText;
		}
		
		// テンプレートサムネール画像
		if ($isExistsTemplate){		// テンプレートが存在するとき
			$imageTag = '<img src="' . $this->getUrl($imgUrl) . '" width="' . self::previewImageSizeWidth . '" height="' . self::previewImageSizeHeight . '" border="0" />';
		} else {
			$iconTitle = $this->_('Template not found.');		// テンプレートが見つかりません
			$iconUrl = $this->gEnv->getRootUrl() . self::NOT_FOUND_TEMPLATE_ICON_FILE;
			$imageTag = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}
		
		// テンプレート情報リンク
		$infoButtonTag = '';
		if (!empty($infoUrl)) $infoButtonTag = $this->gDesign->createInfoLink($infoUrl, self::TITLE_INFO_URL);
		
		// テンプレートフォーマット
		switch ($fetchedRow['tm_type']){
			case 0:					// Joomla!v1.0型
				$formatType = 'J10';		// テンプレート形式
				break;
			case 1:					// Joomla!v1.5型
				$formatType = 'J15';		// テンプレート形式
				break;
			case 2:					// Joomla!v2.5型
				$formatType = 'J25';		// テンプレート形式
				break;
			case 10:					// Bootstrap v3.0型
				$formatType = 'B30';		// テンプレート形式
				break;
			case 100:					// WordPress型
				$formatType = 'W00';		// テンプレート形式
				break;
			default:
				$formatType = $this->_('Not Detected');			// 未定
				break;
		}
		$formatType .= ' /<br />';
		
		// テンプレートを作成したアプリケーション、バージョンを取得
		if (empty($genarator)){
			if (file_exists($templateIndexFile)){
				$content = file_get_contents($templateIndexFile);
				$version = $this->getArtVersion($content);
				if (empty($version)){
					$formatType .= $this->_('Not Detected');		// 未検出
				} else {
					$formatType .= 'artisteer - ' . $version;
				}
			}
		} else {
			$formatType .= $genarator . ' - ' . $version;
		}

		// 削除ボタン
		$eventAttr = 'onclick="deleteline(\'' . $templateId . '\');"';
		$deleteButtonTag = $this->gDesign->createTrashButton(''/*同画面*/, $this->_('Delete'), ''/*タグID*/, $eventAttr/*クリックイベント時処理*/);
		// プレビューボタン
		$eventAttr = 'onclick="previewInOtherWindow(\'' . $templateId . '\');"';
		$previewButtonTag = $this->gDesign->createPreviewButton(''/*同画面*/, $this->_('Preview'), ''/*タグID*/, $eventAttr/*クリックイベント時処理*/);
		// ダウンロードボタン
		$downloadDisabled = false;// ボタンの状態
		if (!$isExistsTemplate) $downloadDisabled = true;
/*		$downloadImg = $this->getUrl($this->gEnv->getRootUrl() . self::DOWNLOAD_ZIP_ICON_FILE);
		if (empty($downloadDisabled)){
			$downloadStr = 'ダウンロード';
		} else {
			$downloadStr = 'ダウンロード不可';
		}
		$downloadButtonTag = '<img src="' . $downloadImg . '" width="32" height="32" alt="' . $downloadStr . '" />';
		$downloadButtonTag = '<a class="btn btn-xs" href="javascript:void(0);" onclick="downloadTemplate(\'' . $templateId . '\');" rel="m3help" data-container="body" title="' . $downloadStr . '" ' . $downloadDisabled . '>' . $downloadButtonTag . '</a>';
		*/
		$eventAttr = 'onclick="downloadTemplate(\'' . $templateId . '\');"';
		$downloadButtonTag = $this->gDesign->createDownloadButton(''/*同画面*/, $this->_('.zip Download'), ''/*タグID*/, $eventAttr/*クリックイベント時処理*/, $downloadDisabled/*ボタン使用可否*/);
		// テンプレート編集ボタン
		$editDisabled = '';// ボタンの状態
		$editUrl = '?task=' . self::TASK_TEMPIMAGE . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $templateId;	// テンプレート編集画面
		$imageBasePath = $this->gEnv->getTemplatesPath() . '/' . $templateId . self::DEFAULT_IMAGE_DIR;				
		if (!is_dir($imageBasePath)){			// ディレクトリがない場合はボタンを使用不可にする
			$editDisabled = 'disabled';
			$editUrl = '';
		}
//		$editButtonTag = $this->gDesign->createEditButton($editUrl, $this->_('Edit Template'), ''/*タグID*/, $editDisabled/*ボタン状態*/);
		
		// テンプレートCSS生成ボタン
		$generateCssUrl = '?task=' . self::TASK_TEMPGENERATECSS . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $templateId;	// テンプレートCSS生成画面
		$generateDisabled = '';
		$canGenerateCss = $this->canGenerateCss($templateId);
		if (!$canGenerateCss) $generateDisabled = 'class="disabled"';
		
		// デフォルト選択ボタン
		$defaultDisabled = '';
		if (!$isExistsTemplate || $templateId == $this->defalutTemplate) $defaultDisabled = 'class="disabled"';
		
		$row = array(
			'index'			=> $index,													// 項目番号
			'serial'		=> $this->convertToDispString($fetchedRow['tm_serial']),			// シリアル番号
			'id_str'		=> $idText,
			'id'			=> $this->convertToDispString($templateId),			// ID
			'name'			=> $name,		// 名前
			'info_button'	=> $infoButtonTag,		// テンプレート情報URL
			'format_type'	=> $formatType,		// テンプレート形式
			'update_dt'		=> $this->convertToDispDateTime($fetchedRow['tm_create_dt']),	// 更新日時
//			'is_default'	=> $defaultCheck,										// デフォルトテンプレートかどうか
			'image_tag'		=> $imageTag,		// 画像
			'delete_button'		=> $deleteButtonTag,		// 削除ボタン
			'preview_button'	=> $previewButtonTag,		// プレビューボタン
			'download_button' 	=> $downloadButtonTag,		// ダウンロードボタン
//			'edit_button' 		=> $editButtonTag,			// テンプレート編集ボタン
			'edit_disabled'		=> $editDisabled,		// テンプレート編集ボタンの使用可否
			'edit_url'			=> $editUrl,				// テンプレート画像編集用URL
			'generate_css_url'	=> $generateCssUrl,			// テンプレートCSS生成用URL
			'generate_disabled'	=> $generateDisabled,		// CSS生成ボタンの使用可否
			'default_disabled'	=> $defaultDisabled		// デフォルト選択ボタン
		);
		$this->tmpl->addVars('templist', $row);
		$this->tmpl->parseTemplate('templist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $templateId;
		
		$this->isExistsTemplateList = true;		// テンプレートが存在する
		return true;
	}
	/**
	 * テンプレートを新規追加
	 *
	 * @param int $type			テンプレート種別(0=PC用、1=携帯用, 2=スマートフォン用)
	 * @param string $id		テンプレートID(ディレクトリ名)
	 * @param bool				true=成功、false=失敗
	 */
	function addNewTemplate($type, $id)
	{
		$ret = false;
		$templType = 0;			// テンプレートのタイプデフォルト値(Joomla!1.0)
		$cleanType = 0;			// HTMLの出力のクリーニングタイプ
		$genarator = '';		// テンプレート作成アプリケーション
		$version = '';			// テンプレートバージョン
		$infoUrl = '';			// テンプレート情報リンク
		$templateDir = $this->gEnv->getTemplatesPath() . '/' . $id;			// テンプレートディレクトリ
				
		// テンプレートの種別を判定
		switch ($type){
			case '0':		// PC用テンプレート
			case '2':		// スマートフォン用テンプレート
				$configFile = $this->gEnv->getTemplatesPath() . '/' . $id . '/' . self::JOOMLA_CONFIG_FILENAME;
				if (file_exists($configFile)){
					if (!function_exists('simplexml_load_file')){
						$msg = $this->_('SimpleXML module not installed.');		// SimpleXML拡張モジュールがインストールされていません
						$this->setAppErrorMsg($msg);
						return false;
					}
					$xml = simplexml_load_file($configFile);
					if ($xml !== false){
						if ($xml->attributes()->type == 'template'){
							$version = $xml->attributes()->version;
							$format = $xml->attributes()->format;
							if (strcasecmp($format, 'bootstrap') == 0){		// Bootstrapタイプのテンプレートの場合
								$templType = 10;		// Bootstrap v3.0型
							} else {					// デフォルトのテンプレート(Joomlaタイプ)
								if (version_compare($version, '1.6') >= 0){
									$templType = 2;		// Joomla!v2.5テンプレート
								} else if (version_compare($version, '1.5') >= 0){
									$templType = 1;		// Joomla!v1.5テンプレート

									// テンプレートをテスト
									$cleanType = $this->checkTemplate($id);
								}
							}
							
							// テンプレート情報リンク
							if (!empty($xml->infoUrl)) $infoUrl = $xml->infoUrl;
						}
					}
				} else {
					// 設定ファイルがない場合はWordPressテンプレートかどうかチェック
					$content = file_get_contents($templateDir . '/header.php');
					if (empty($content)) $content = file_get_contents($templateDir . '/index.php');		// Themlerテンプレート対応
					$ret = $this->isWordpressTemplate($content);
					if ($ret) $templType = 100;		// WordPress型
				}
				// ##### テンプレート作成アプリケーションを取得 #####
				// Artisteerテンプレートかどうか確認
				$content = file_get_contents($templateDir . '/index.php');
				$version = $this->getArtVersion($content);
				if (!empty($version)){
					$genarator = 'artisteer';		// テンプレート作成アプリケーション
				} else {
					// Themlerテンプレートかどうか確認
					$ret = $this->isTemlerTemplate($templateDir, $version);
					if ($ret){
						$genarator = 'themler';
						
						// Themlerテンプレートの修正処理を行う
						$this->fixThemlerTemplate($templateDir);
					}
				}
				break;
		}
			
		// テンプレートを登録
		$ret = $this->db->addNewTemplate($id, $id, $templType, intval($type), $cleanType, $genarator, $version, $infoUrl);
		if ($ret){
			// テンプレート登録後、テンプレートを解析してCSSファイルを取得
			$templateInfoObj = $this->gPage->parseTemplateCssFile($id);
		
			// テンプレートの情報を更新
			$updateParam = array();
			$updateParam['tm_editor_param'] = serialize($templateInfoObj);
			$ret = $this->_db->updateTemplate($id, $updateParam);
		}
		return $ret;
	}
	/**
	 * テンプレートを初期化
	 *
	 * @param string $id		テンプレートID
	 * @return bool				true=成功、false=失敗
	 */
	function initializeTemplate($id)
	{
		$templateDir = $this->gEnv->getTemplatesPath() . '/' . $id;		// テンプレートディレクトリ
		
		// テンプレートの情報を取得
		if ($this->_db->getTemplate($id, $row)){
			$genarator = $row['tm_generator'];			// テンプレート作成アプリケーション
			
			// Themlerテンプレートの修正処理を行う
			switch ($genarator){
			case 'themler':
				$this->fixThemlerTemplate($templateDir);
				break;
			}
			
			// テンプレートを解析し、使用しているCSSファイルを取得
			$templateInfoObj = $this->gPage->parseTemplateCssFile($id);
			
			// テンプレートの情報を更新
			$updateParam = array();
			$updateParam['tm_editor_param'] = serialize($templateInfoObj);
			$ret = $this->_db->updateTemplate($id, $updateParam);
			return $ret;
		} else {
			return false;
		}
	}
	/**
	 * テンプレートIDを取得
	 *
	 * @param string $dir		テンプレートのディレクトリ
	 * @return string			テンプレート(取得できないときは空を返す)
	 */
	function getTemplateId($dir)
	{
		// デフォルトはディレクトリ名
		$id = basename($dir);

		// 設定ファイルがあれば読み込む
		$configFile = $dir . '/' . self::JOOMLA_CONFIG_FILENAME;
		if (file_exists($configFile)){
			if (!function_exists('simplexml_load_file')){
				$msg = $this->_('SimpleXML module not installed.');		// SimpleXML拡張モジュールがインストールされていません
				$this->setAppErrorMsg($msg);
				return '';
			}
			$xml = simplexml_load_file($configFile);
			if ($xml !== false){
				$name = trim($xml->name);
				if (!empty($name)) $id = $name;
			}
		}
		return $id;
	}
	/**
	 * CSS生成可能か判断
	 *
	 * @param string $id		テンプレートID
	 * @return bool				true=CSS生成可能、false=CSS生成不可
	 */
	function canGenerateCss($id)
	{
		$canGenerate = false;		// 生成可否
		
		$configFile = $this->gEnv->getTemplatesPath() . '/' . $id . '/' . self::JOOMLA_CONFIG_FILENAME;
		if (file_exists($configFile)){
			if (!function_exists('simplexml_load_file')){
				$msg = $this->_('SimpleXML module not installed.');		// SimpleXML拡張モジュールがインストールされていません
				$this->setAppErrorMsg($msg);
				return false;
			}
			$xml = simplexml_load_file($configFile);
			if ($xml !== false){
				if ($xml->attributes()->type == 'template'){
					$version = $xml->attributes()->version;
					$format = $xml->attributes()->format;
					
					$less = $xml->develop->less;
//					$sass = $xml->develop->sass;
//					$scss = $xml->develop->scss;
					if (!empty($less)) $canGenerate = true;
				}
			}
		}
		return $canGenerate;
	}
	/**
	 * タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createTemplateTypeMenu()
	{
		for ($i = 0; $i < count($this->templateTypeArray); $i++){
			$value = $this->templateTypeArray[$i]['value'];
			$name = $this->templateTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->templateType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_type_list', $row);
			$this->tmpl->parseTemplate('item_type_list', 'a');
		}
	}
	/**
	 * テンプレートの出力チェック
	 *
	 * @param string $templateId	テンプレートID
	 * @return int					クリーン処理タイプ
	 */
	function checkTemplate($id)
	{
		$cleanType = 0;
		
		// Joomla!テンプレート共通の設定
		define('_JEXEC', 1);
			
		// Joomla!v1.5用の設定
		define('JPATH_BASE', dirname(__FILE__));
		define('DS', DIRECTORY_SEPARATOR);
		
		// バッファ作成
		ob_start();
		
		$render = new JRender();
		$render->setTemplate($id);
		$contents = $render->getComponentContents('test', 'dummy contents', 'test');
		
		// 不要なタグをチェック
		$pos = strpos($contents, 'PostHeaderIcons');
		if ($pos !== false) $cleanType = 1;
		
		// バッファを破棄
		ob_end_clean();
		return $cleanType;
	}
	/**
	 * テンプレートのindex.phpファイルからArtisteerバージョンを取得
	 *
	 * @param string $src		検索するデータ
	 * @return string			検出の場合はバージョン文字列、未検出の場合は空文字列。
	 */
	function getArtVersion($src)
	{
		$version = '';
		// <!-- Created by Artisteer v4.1.0.59688 -->
		$pattern = '/<!-- Created by Artisteer[^<]*?v([.\d]+)[^<]*?-->/i';
        $ret = preg_match($pattern, $src, $matches);
		if ($ret) $version = $matches[1];
		return $version;
	}
	/**
	 * WordPressテンプレートかどうかチェック
	 *
	 * @param string $src		検索するデータ
	 * @return bool				true=WordPressテンプレート、false=WordPressテンプレート以外
	 */
	function isWordpressTemplate($src)
	{
		$pos = strpos($src, 'wp_head()');
		if ($pos === false){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Themlerテンプレートかどうかを判断
	 *
	 * @param string $dir		テンプレートのディレクトリ
	 * @param string $version	Themlerテンプレートの場合、テンプレートのバージョンが返る
	 * @return bool				true=Themlerテンプレート、false=Themlerテンプレート以外
	 */
	function isTemlerTemplate($dir, &$version)
	{
		// Themlerテンプレートを判断するファイル
		$appFile = $dir . '/' . self::THEMLER_APP_FILENAME;
		$ret = file_exists($appFile);
		
		if ($ret){
			@require_once($dir . '/functions.php');		// エラーメッセージ抑止
			$verSrc = addThemeVersion('');
			list($tmp, $version) = explode('=', $verSrc);
		}
		return $ret;
	}
	/**
	 * Themlerテンプレートの修正
	 *
	 * @param string $templateDir		テンプレートのディレクトリ
	 * @return bool						true=成功、false=失敗
	 */
	function fixThemlerTemplate($templateDir)
	{
		$searchDir = $templateDir . '/html/com_content';
		$dir = dir($searchDir);
		while (($file = $dir->read()) !== false){
			$filePath = $searchDir . '/' . $file;
			
			// ディレクトリかどうかチェック
			if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath)){
				$targetFile = $filePath . '/default.php';
				$this->fixThemlerTemplateDefaultFile($targetFile);
			}
		}
		$dir->close();
	}
	/**
	 * Themlerテンプレートのcom_contentディレクトリ以下のdefault.phpファイルの修正を行う
	 *
	 * @param string $filePath	対象ファイルのパス
	 * @return bool				true=成功、false=失敗
	 */
	function fixThemlerTemplateDefaultFile($filePath)
	{
		// ファイルが存在しない場合は終了
		if (!file_exists($filePath)) return false;

		// 現在のファイルを読み込む
		if (!($file = @fopen($filePath, "r"))){
			$errMsg = 'ファイルのオープンに失敗しました ファイル=' . $filePath;
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
 			return false;
		}
		$content = @fread($file, filesize($filePath));
		@fclose($file);
		
		// プログラム変更処理
		$content = str_replace('require_once \'default_template.php\';', 'require \'default_template.php\';', $content);
		
		// ファイルに保存
		$backupFilePath = dirname($filePath) . '/_' . basename($filePath);
		$isOk = writeFile($backupFilePath, $content);
		if (!$isOk) return false;

		// 新旧ファイル入れ替え
		$tmpFilePath = $filePath . '_tmp';
		if (!renameFile($filePath, $tmpFilePath)){
			$errMsg = 'ファイルを移動できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成したファイルを削除
			unlink($backupFilePath);
			return false;
		}
		if (!renameFile($backupFilePath, $filePath)){
			$errMsg = 'ファイルを移動できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成したファイルを削除
			unlink($backupFilePath);
			
			// ファイルを戻す
			renameFile($tmpFilePath, $filePath);
			return false;
		}
		if (!renameFile($tmpFilePath, $backupFilePath)){
			$errMsg = 'ファイルを移動できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成したファイルを削除
			unlink($tmpFilePath);
			return false;
		}
		return true;
	}
}
?>
