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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainTempBaseWidgetContainer.php');
require_once($gEnvManager->getLibPath()				. '/lessphp-0.5.0/lessc.inc.php' );

class admin_mainTempgeneratecssWidgetContainer extends admin_mainTempBaseWidgetContainer
{
	private $templateId;				// テンプレートID
	private $buildType;					// CSS生成タイプ
	const JOOMLA_CONFIG_FILENAME = 'templateDetails.xml';		// Joomla!のテンプレート設定ファイル名
	const DEFAULT_CSS_DIR = '/css';				// CSSファイルの格納ディレクトリ
	const DEFAULT_LESS_DIR = '/less';		// LESSソースファイルの格納ディレクトリ
	
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
		$task = $request->trimValueOf('task');
		if ($task == self::TASK_TEMPGENERATECSS_DETAIL){		// 詳細画面
			return 'tempgeneratecss_detail.tmpl.html';
		} else {			// 一覧画面
			return 'tempgeneratecss.tmpl.html';
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

		switch ($task){
			case self::TASK_TEMPGENERATECSS:	// テンプレートCSS生成
			default:
				$this->createList($request);
				break;
			case self::TASK_TEMPGENERATECSS_DETAIL:// テンプレートCSS生成(詳細)
				$this->createDetail($request);
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
		$enableBuild = true;		// CSS生成実行可否
		
		// CSS生成タイプを取得
		list($buildType, $srcFilePath, $destFilePath) = $this->getBuildType();
		
		// ソースファイル、出力ファイルの存在チェック
		if (!file_exists($srcFilePath)){
			$msg = 'ソースファイルが見つかりません。パス=' . $srcFilePath;
			$this->setAppErrorMsg($msg);
			
			$enableBuild = false;		// CSS生成実行可否
		}
		$filePathArray = explode('/', $srcFilePath);		// pathinfo,basenameは日本語処理できないので日本語対応
		$srcFile = end($filePathArray);		// ファイル名
		
		if (!is_writable($destFilePath)){
			$msg = 'CSS出力ファイルが存在しないか書き込みできません。パス=' . $destFilePath;
			$this->setAppErrorMsg($msg);
			
			$enableBuild = false;		// CSS生成実行可否
		}
		$filePathArray = explode('/', $destFilePath);		// pathinfo,basenameは日本語処理できないので日本語対応
		$destFile = end($filePathArray);		// ファイル名
		
		$act = $request->trimValueOf('act');
		if ($enableBuild && $act == 'generate'){		// CSS生成実行の場合
			$ret = false;
			
			if ($buildType = 'less'){
				$less = new lessc;
				try {
					// コンパイル実行
					$compiledData = $less->compileFile($srcFilePath);
				
					// ファイルに保存
					file_put_contents($destFilePath, $compiledData);
				
					$ret = true;		// コンパイル成功
				} catch (Exception $ex) {
					$msg = 'コンパイルエラー：' . $ex->getMessage();
					$this->setAppErrorMsg($msg);
				}
			}

			if ($ret) $this->setGuidanceMsg('CSSビルド完了しました');
		}
		
		// ソースファイル一覧を作成
		$this->createFileList(dirname($srcFilePath));
		
		// ファイル更新日時
		$updateDate = date('Y/m/d H:i:s', filemtime($destFilePath));
			
		// CSS生成不可の場合はボタンを使用不可にする
		if (!$enableBuild) $this->tmpl->addVar('_widget', 'build_disabled', 'disabled="disabled"');		// 「生成」ボタン使用不可
		
		$this->tmpl->addVar('_widget', 'template', $this->convertToDispString($this->templateId));		// テンプレート名
		$this->tmpl->addVar('_widget', 'build_type', $this->convertToDispString(strtoupper($buildType)));		// CSS生成タイプ
		$this->tmpl->addVar('_widget', 'source_file', $this->convertToDispString($srcFile));		// ソースファイル
		$this->tmpl->addVar('_widget', 'destination_file', $this->convertToDispString($destFile));		// 出力ファイル
		$this->tmpl->addVar('_widget', 'update_dt', $updateDate);		// 更新日時
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$canEdit = true;		// ファイル編集可否
					
		// CSS生成タイプを取得
		list($buildType, $srcFilePath, $destFilePath) = $this->getBuildType();
		
		// 編集ファイルのパスを取得
		$filename = $request->trimValueOf('filename');
		$filePath = dirname($srcFilePath) . '/' . $filename;
		if (!file_exists($filePath)){// ファイルの存在チェック
			$msg = 'ソースファイルが見つかりません。パス=' . $filePath;
			$this->setAppErrorMsg($msg);
			
			$canEdit = false;		// ファイル編集可否
		}
		
		$code = $request->valueOf('item_code');
		
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// コード更新のとき
			// ファイルに保存
			$ret = file_put_contents($filePath, $code);
			if ($ret){
				$this->setGuidanceMsg('ファイル更新しました');
			} else {
				$this->setAppErrorMsg('ファイル更新に失敗しました');
			}
		}
		
		// ファイルからソースコードを読み込む
		$code = file_get_contents($filePath);
		
		$this->tmpl->addVar('_widget', 'code', $this->convertToDispString($code));		// ファイル名
		$this->tmpl->addVar('_widget', 'filename', $this->convertToDispString($filename));		// ファイル名
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path		パス
	 * @return なし
	 */
	function createFileList($path)
	{
		// ファイル一覧取得
		$fileList = $this->getFileList($path);

		$index = 0;
		for ($i = 0; $i < count($fileList); $i++){
			$filePath = $fileList[$i];
			$relativeFilePath = substr($filePath, strlen($this->imageBasePath));

			$filePathArray = explode('/', $filePath);		// pathinfo,basenameは日本語処理できないので日本語対応
			$file = end($filePathArray);		// ファイル名
			$size = '';				// ファイルサイズ
			$fileLink = '';
			if (is_file($filePath)){			// ファイルのとき
				$fileLink = '<a href="#" onclick="editItemByFilename(\'' . addslashes($file) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
					
				$size = filesize($filePath);
			}
	
			// ファイル更新日時
			$updateDate = date('Y/m/d H:i:s', filemtime($filePath));
			
			$row = array(
				'serial'		=> $serial,
				'index'			=> $index,			// インデックス番号(チェックボックス識別)
				'name'			=> $this->convertToDispString($file),			// ファイル名
				'filename'    	=> $fileLink,			// ファイル名
				'size'     		=> $size,			// ファイルサイズ
				'update_dt'    	=> $updateDate			// 更新日時
			);
			$this->tmpl->addVars('file_list', $row);
			$this->tmpl->parseTemplate('file_list', 'a');
			
			// インデックス番号を更新
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
		
		// アルファベット順にソート
		usort($fileList, array($this, 'sortOrderByAlphabet'));
		
		return $fileList;
	}
	/**
	 * ファイルをアルファベットで昇順にソートする。ディレクトリは先頭。
	 *
	 * @param string  	$path1			比較するパス1
	 * @param string	$path2			比較するパス2
	 * @return int						同じとき0、$path1が$path2より大きいとき1,$path1が$path2より小さいとき-1を返す
	 */
	function sortOrderByAlphabet($path1, $path2)
	{
		// ディレクトリは常に先頭に表示
		if (is_dir($path1)){			// ディレクトリのとき
			if (!is_dir($path2)) return -1; // ファイルのとき
		} else {
			if (is_dir($path2)) return 1;	// ディレクトリのとき
		}
		
		return strcasecmp($path1, $path2);
	}
	/**
	 * CSS生成タイプを取得
	 *
	 * @return array			CSS生成タイプ(less,sass,scss)、ソースファイル名、出力ファイル名の配列
	 */
	function getBuildType()
	{
		$buildType = '';
		$srcFile = '';
		$destFile = '';
		
		$configFile = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . '/' . self::JOOMLA_CONFIG_FILENAME;
		if (file_exists($configFile)){
			if (!function_exists('simplexml_load_file')){
				$msg = $this->_('SimpleXML module not installed.');		// SimpleXML拡張モジュールがインストールされていません
				$this->setAppErrorMsg($msg);
				return array();
			}
			$xml = simplexml_load_file($configFile);
			if ($xml !== false){
				if ($xml->attributes()->type == 'template'){
					$version = $xml->attributes()->version;
					$format = $xml->attributes()->format;
					
					$less = $xml->develop->less;
					$sass = $xml->develop->sass;
					$scss = $xml->develop->scss;
					
					if (!empty($less)){
						$buildType = 'less';
						$srcFile = (string)$less->source;
						if (!empty($srcFile)) $srcFile = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . self::DEFAULT_LESS_DIR . '/' . $srcFile;
						$destFile = (string)$less->destination;
						if (!empty($destFile)) $destFile = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . self::DEFAULT_CSS_DIR . '/' . $destFile;
					} else if (!empty($sass)){
						$buildType = 'sass';
						$srcFile = (string)$sass->source;
						$destFile = (string)$sass->destination;
					} else if (!empty($scss)){
						$buildType = 'scss';
						$srcFile = (string)$scss->source;
						$destFile = (string)$scss->destination;
					}
				}
			}
		}
		return array($buildType, $srcFile, $destFile);
	}
}
?>
