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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath()				. '/pcl/pclzip.lib.php' );
require_once($gEnvManager->getCurrentWidgetContainerPath()		. '/admin_mainDef.php');			// 定義クラス
require_once($gEnvManager->getCommonPath()		. '/archive.php');
// Joomlaテンプレート用
require_once($gEnvManager->getJoomlaRootPath() . '/JParameter.php');
require_once($gEnvManager->getJoomlaRootPath() . '/JRender.php');
				
class admin_mainTemplistWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $newTemplate = array();		// 新規追加テンプレート
	private $defalutTemplate;	// デフォルトのテンプレート
	private $templateTypeArray;		// テンプレートタイプ
	private $templateType;			// 現在のテンプレートタイプ
	private $isExistsTemplateList;		// テンプレートが存在するかどうか
	const TEMPLATE_THUMBNAIL_FILENAME = 'template_thumbnail.png';		// テンプレートサムネール
	const previewImageSizeHeight = 27;
	const previewImageSizeWidth = 42;
	const imageSizeHeight = 135;
	const imageSizeWidth = 210;
	const JOOMLA_CONFIG_FILENAME = 'templateDetails.xml';		// Joomla!のテンプレート設定ファイル名
	const NOT_FOUND_TEMPLATE_ICON_FILE = '/images/system/notfound32.png';		// テンプレートが見つからないアイコン
		
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
	 * @param								なし
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
						//$this->setMsg(self::MSG_GUIDANCE, 'テンプレートを削除しました(テンプレートID：' . $templateId . ')');
						$this->setMsg(self::MSG_GUIDANCE, sprintf($this->_('Template deleted. (template ID: %s)'), $templateId));	// テンプレートを削除しました(テンプレートID：%s)
					} else {
						//$this->setMsg(self::MSG_APP_ERR, 'テンプレート削除に失敗しました(テンプレートID：' . $templateId . ')');
						$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting template. (template ID: %s)'), $templateId));	// テンプレート削除に失敗しました(テンプレートID：%s)
					}
				} else {
					//$this->setMsg(self::MSG_APP_ERR, 'テンプレートのディレクトリが削除できませんでした(テンプレートID：' . $templateId . ')');
					$this->setMsg(self::MSG_APP_ERR, sprintf($this->_('Failed in deleting template directory. (directory: %s)'), $templatePath));	// テンプレートのディレクトリが削除できませんでした(ディレクトリ：%s)
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
		
		// テンプレート選択メニュー作成
		$this->createTemplateTypeMenu();
		
		// テンプレートリストを取得
		$this->db->getAllTemplateList(intval($this->templateType), array($this, 'tempListLoop'));
		if (!$this->isExistsTemplateList) $this->tmpl->setAttribute('templist', 'visibility', 'hidden');// テンプレートがないときは、一覧を表示しない
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "install_dir", $installDir);// インストールディレクトリ
		$this->tmpl->addVar("_widget", "admin_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理用URL
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_update_line'] = $this->_('Update line?');		// 行を更新しますか?
		$localeText['msg_delete_line'] = $this->_('Delete tmplate?');		// テンプレートを削除しますか?
		$localeText['msg_no_upload_file'] = $this->_('File not selected.');		// アップロードするファイルが選択されていません
		$localeText['msg_upload_file'] = $this->_('Upload file.');		// ファイルをアップロードします
		$localeText['label_template_list'] = $this->_('Template List');			// テンプレート一覧
		$localeText['label_template_type'] = $this->_('Template Type:');			// テンプレートタイプ：
		$localeText['label_install_dir'] = $this->_('Install Directory:');			// インストールディレクトリ:
		$localeText['label_read_new'] = $this->_('Reload directory');			// ディレクトリ再読み込み
		$localeText['label_show_detail'] = $this->_('Show detail');			// 詳細表示
		$localeText['label_template_name'] = $this->_('Name');			// 名前
		$localeText['label_template_format'] = $this->_('Format');			// 形式
		$localeText['label_template_creator'] = $this->_('Artisteer Version');			// Artisteerバージョン
		$localeText['label_template_default'] = $this->_('Default');			// デフォルト
		$localeText['label_template_date'] = $this->_('Update Date');			// 更新日時
		$localeText['label_template_operation'] = $this->_('Operation');			// 操作
		$localeText['label_template_upload'] = $this->_('Template Upload (zip compressed file)');			// テンプレートアップロード(zip圧縮ファイル)
		$localeText['label_upload'] = $this->_('Upload');			// アップロード
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
		// テンプレートが存在するかどうかチェック
		$isExistsTemplate = false;
		$templateId = $fetchedRow['tm_id'];// テンプレートID
		$templateDir = $this->gEnv->getTemplatesPath() . '/' . $templateId;			// テンプレートのディレクトリ
		if (file_exists($templateDir)) $isExistsTemplate = true;
		
		// デフォルトテンプレート
		$defaultCheck = '';
		if ($fetchedRow['tm_id'] == $this->defalutTemplate){
			$defaultCheck = 'checked ';
		}
		if (!$isExistsTemplate) $defaultCheck .= 'disabled';		// テンプレートが存在しないときは使用不可
		
		// 画面イメージ表示設定
		$name = $this->convertToDispString($fetchedRow['tm_name']);			// テンプレート名
		$templateIndexFile = $templateDir . '/index.php';						// テンプレートindex.phpファイル
		$imgUrl = $this->gEnv->getTemplatesUrl() . '/' . $templateId . '/' . self::TEMPLATE_THUMBNAIL_FILENAME;
		
		// 新規に追加されたテンプレートかチェック
		$idText = $this->convertToDispString($templateId);
		for ($i = 0; $i < count($this->newTemplate); $i++){
			if ($this->newTemplate[$i] == $templateId){
				$idText = '<b><font color="green">' . $this->convertToDispString($templateId) . '</font></b>';
				break;
			}
		}
		// テンプレートサムネール画像
		if ($isExistsTemplate){		// テンプレートが存在するとき
			$imageTag = '<img src="' . $this->getUrl($imgUrl) . '" width="' . self::previewImageSizeWidth . '" height="' . self::previewImageSizeHeight . '" border="0" />';
		} else {
			$iconTitle = $this->_('Template not found.');		// テンプレートが見つかりません
			$iconUrl = $this->gEnv->getRootUrl() . self::NOT_FOUND_TEMPLATE_ICON_FILE;
			$imageTag = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		}
		
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
			default:
				$formatType = $this->_('Not Detected');			// 未定
				break;
		}
		$formatType .= ' /<br />';
		
		// テンプレートを作成したArtisteerのバージョンを取得
		if (file_exists($templateIndexFile)){
			$content = file_get_contents($templateIndexFile);
			$version = $this->getArtVersion($content);
			if (empty($version)){
				$formatType .= $this->_('Not Detected');		// 未検出
			} else {
				$formatType .= $version;
			}
		}
		
		// ボタンの状態
		$downloadButton = '';
		if (!$isExistsTemplate) $downloadButton = 'disabled';
		
		$row = array(
			'no' => $index + 1,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['tm_serial']),			// シリアル番号
			'id_str' => $idText,
			'id' => $this->convertToDispString($templateId),			// ID
			'name' => $name,		// 名前
			'format_type' => $formatType,		// テンプレート形式
			'update_dt' => $this->convertToDispDateTime($fetchedRow['tm_create_dt']),	// 更新日時
			'is_default' => $defaultCheck,										// デフォルトテンプレートかどうか
			'image_tag' => $imageTag,		// 画像
			'download_button' => $downloadButton,		// ダウンロードボタン
			'label_preview' => $this->_('Preview'),			// プレビュー
			'label_update' => $this->_('Update'),			// 更新
			'label_delete' => $this->_('Delete'),			// 削除
			'label_download' => $this->_('Download')			// ダウンロード
		);
		$this->tmpl->addVars('templist', $row);
		$this->tmpl->parseTemplate('templist', 'a');
		
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
							if (version_compare($version, '1.6') >= 0){
								$templType = 2;		// Joomla!v2.5テンプレート
							} else if (version_compare($version, '1.5') >= 0){
								$templType = 1;		// Joomla!v1.5テンプレート

								// テンプレートをテスト
								$cleanType = $this->checkTemplate($id);
							}
						}
					}
				}
				break;
		}
		
		// テンプレートを登録
		$ret = $this->db->addNewTemplate($id, $id, $templType, intval($type), $cleanType);
		return $ret;
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
}
?>
