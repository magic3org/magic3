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
require_once($gEnvManager->getContainerPath() . '/baseAdminTemplateContainer.php');

class admin_bootstrap4_customTemplateContainer extends BaseAdminTemplateContainer
{
	private $templatePath;		// テンプレートのパス
	private $isCssCdn;			// CSSがCDNかどうか
	private $cssData;			// CSSフォイルのパス(「/」で開始)またはCDNタグ
	private $cssFile;			// 選択中のCSSファイル
	const CSS_DIR = '/upload/css';		// CSSファイルディレクトリ
	const CSS_FILE_EXT = 'css';		// cssファイル拡張子
	const DEFAULT_CSS_BASE_FILENAME = 'bootstrap';			// CSSファイル名のデフォルト
	const FILENAME_MAX_NO = 99;			// ファイル名付加番号最大値
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期値設定
		$this->templatePath = $this->gEnv->getTemplatesPath() . '/' . $this->_templateId;		// テンプレートのパス
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
	 * テンプレートの後処理
	 *
	 * テンプレートのデータ埋め込み(_assign())の後に実行される。
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
		// 初期値取得
		$this->isCssCdn = false;			// CSSがCDNかどうか
		$this->cssData = '';
		
		// 入力値を取得
		$act = $request->trimValueOf('act');
		$cssType = $request->trimValueOf('item_css_type');
		if ($cssType == 'cdn') $this->isCssCdn = true;		// CSSがCDNかどうか
		$this->cssFile = $request->trimValueOf('item_file');
		$cssCdn = $request->valueOf('item_cdn');		// CDN文字列(タグ形式)
		if ($this->isCssCdn){			// CSSにCDNを選択のとき
			$this->cssData = $cssCdn;
		} else {
			$this->cssData = $this->cssFile;
		}

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			// 入力チェック
			if ($this->isCssCdn){			// CSSにCDNを選択のとき
				$this->checkInput($cssCdn, 'CDN');
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 保存用のCSSデータ作成
				if ($this->isCssCdn){			// CSSにCDNを選択のとき
					$saveCssData = $this->cssData;
				} else {
					if (empty($this->cssData)){
						$saveCssData = '';			// デフォルトのCSS
					} else {
						$saveCssData = self::CSS_DIR . '/' . $this->cssData;		// 相対パスのCSSファイル
					}
				}
				$templateCustomObj = array();
				$templateCustomObj['head_css_data'] = $saveCssData;			// ヘッダ部CSSデータ

				// テンプレート情報のカスタマイズパラメータを更新。
				$updateParam = array();
				$updateParam['tm_custom_params'] = serialize($templateCustomObj);
				$ret = $this->_db->updateTemplate($this->_templateId, $updateParam);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'upload_css'){
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得

				// ファイル名の解析
				$pathParts = pathinfo($uploadFilename);
				$ext = $pathParts['extension'];		// 拡張子
				$ext = strtolower($ext);
				
				// ファイル拡張子のチェック
				if ($ext != self::CSS_FILE_EXT){
					$msg = '拡張子がcssのファイルのみアップロード可能です';
					$this->setAppErrorMsg($msg);
				}
				
				// テンプレートディレクトリの書き込み権限をチェック
				if (!is_writable($this->templatePath)){
					$msg = 'テンプレートディレクトリに書き込み権限がありません。ディレクトリ：' . $this->templatePath;
					$this->setAppErrorMsg($msg);
				}

				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// CSSファイル格納ディレクトリを作成
					$cssDir = $this->templatePath . self::CSS_DIR;
					mkdir($cssDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
					
					// ファイル名を作成
					$filename = $pathParts['filename'];		// 拡張子以外
					if (empty($filename)){
						$baseFilename = self::DEFAULT_CSS_BASE_FILENAME;
						$followFilename = '.' . $ext;
					} else {
						$baseFilename = explode('.', $filename)[0];
						$followFilename = ltrim($pathParts['basename'], $baseFilename);
					}
					$newCssFilename = $this->createDefaultName($cssDir, $baseFilename, $followFilename);
					$newCssPath = $cssDir . '/' . $newCssFilename;

					// アップロードされたCSSファイルをテンプレート内のuploadディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $newCssPath);
					if ($ret){
						$msg = 'ファイルのアップロードが完了しました(ファイル名：' . $newCssFilename . ')';
						$this->setGuidanceMsg($msg);
					} else {
						$msg = 'ファイルのアップロードに失敗しました';
						$this->setAppErrorMsg($msg);
					}
				}
			} else {
				$msg = 'アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。' . $this->gSystem->getMaxFileSizeForUpload() . 'バイト)';
				$this->setAppErrorMsg($msg);
			}
		} else {		// 初期表示の場合
			$replaceNew = true;			// データ再取得
		}
		
		if ($replaceNew){		// データ再取得のとき
			$this->isCssCdn = false;			// CSSがCDNかどうか
			$this->cssData = '';
			$this->cssFile = '';		// 選択中のCSSファイル
			$cssCdn = '';				// CDNデータ
			
			// テンプレートカスタマイズ情報取得
			$ret = $this->_db->getTemplate($this->_templateId, $row);
			if ($ret){
				$optionParams = $row['tm_custom_params'];
				if (empty($optionParams)){
					$templateCustomObj = array();
				} else {
					$templateCustomObj = unserialize($optionParams);		// 連想配列に変換
				}
				$cssData = $templateCustomObj['head_css_data'];
				if (!empty($cssData)){
					$this->cssData = $cssData;
					if (strStartsWith($this->cssData, '/')){		// CSSファイルの場合
						$this->cssFile = basename($this->cssData);
					} else {
						$this->isCssCdn = true;		// CDNで設定
						$cssCdn = $cssData;
					}
				}
			}
		}
		
		if ($this->isCssCdn){			// CSSがCDNかどうか
			$this->tmpl->addVar('_widget', 'css_cdn_checked', 'checked');		// CDN
		} else {
			$this->tmpl->addVar('_widget', 'css_file_checked', 'checked');		// CSSファイル
		}
		
		// CSSファイル選択メニュー作成
		$ret = $this->createCssFileMenu();
		if (!$ret) $this->tmpl->setAttribute('css_file_list', 'visibility', 'hidden');

		// アップロードボタン
		$eventAttr = 'data-toggle="modal" data-target="#uploadModal"';		// ファイル選択ダイアログ起動
		$UploadButtonTag = $this->gDesign->createUploadButton(''/*同画面*/, 'アップロード', ''/*タグID*/, $eventAttr/*追加属性*/);
		$this->tmpl->addVar('_widget', 'upload_button', $UploadButtonTag);
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'cdn', $this->convertToDispString($cssCdn));		// CDN
		$this->tmpl->addVar("_widget", "max_file_size", $this->gSystem->getMaxFileSizeForUpload(true/*数値のバイト数*/));			// アップロードファイルの最大サイズ
	}
	/**
	 * CSSファイル選択メニュー作成
	 *
	 * @return bool			true=ファイルあり、false=ファイルなし
	 */
	function createCssFileMenu()
	{
		$cssDir = $this->templatePath . self::CSS_DIR;
		if (!is_dir ($cssDir)) return false;
		
		// CSSファイルディレクトリ読み込み
		$isExistsFile = false;		// CSSファイルが存在するかどうか
		$dir = dir($cssDir);
		while (($file = $dir->read()) !== false){
			$filePath = $cssDir . '/' . $file;
			$pathParts = pathinfo($file);
			$ext = $pathParts['extension'];		// 拡張子
			$ext = strtolower($ext);
				
			// ファイルかどうかチェック
			if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath) &&
				$ext == self::CSS_FILE_EXT){		// 拡張子をチェック
				$isExistsFile = true;
				$value = $file;
				$name = $file;
			
				// 選択状態を設定
				$selected = '';
				//if (strStartsWith($this->cssData, '/') && $filePath == $this->templatePath . $this->cssData) $selected = 'selected';
				if ($file == $this->cssFile) $selected = 'selected';
			
				$row = array(
					'value'    => $this->convertToDispString($value),
					'name'     => $this->convertToDispString($name),
					'selected' => $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('css_file_list', $row);
				$this->tmpl->parseTemplate('css_file_list', 'a');
			}
		}
		$dir->close();
		return $isExistsFile;
	}
	/**
	 * デフォルトファイル名を作成
	 *
	 * @param string $dir					作成するファイルの格納ディレクトリ
	 * @param string $baseFilename			ファイル名のヘッダ文字列
	 * @param string $followFilename		ファイル名のヘッダ文字列以外
	 * @return string						ファイル名。作成失敗の場合は空文字列が返る。
	 */
	function createDefaultName($dir, $baseFilename, $followFilename)
	{
		$name = $baseFilename . $followFilename;
		$path = $dir . '/' . $name;
		if (!file_exists($path)) return $name;
		
		// NOを付加したファイル名を作成
		for ($i = 2; $i <= self::FILENAME_MAX_NO; $i++){
			$name = $baseFilename . $i . $followFilename;
			$path = $dir . '/' . $name;
			if (!file_exists($path)) return $name;
		}
		return '';
	}
}
?>
