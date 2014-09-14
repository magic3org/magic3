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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainBaseWidgetContainer.php');

class admin_mainCreateconfigWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $basePath;		// 処理対象ディレクトリ
	private $files;			// 処理対象ファイル。対象ディレクトリからの相対パス。
	const DEFAULT_COPYFILE_FILENAME = 'copyfile.json';
	
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
		return 'createconfig.tmpl.html';
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
		$path = $request->trimValueOf('item_path');
		$fileType = $request->trimValueOf('type');
		
		if ($act == 'download'){		// 定義ファイルダウンロードのとき
			// ディレクトリ以下のファイルを取得
			$this->basePath = rtrim(ltrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
			$baseDir = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . $this->basePath;		// 処理対象ディレクトリ
			$this->files = $this->getFiles($baseDir);
			
			// 出力データ作成
			$configData = $this->getParsedTemplateData('copyfile.tmpl.json', array($this, 'makeJson'));

			// 一時ファイル作成
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD);		// zip処理用一時ファイル
			
			// ファイルに保存
			$ret = file_put_contents($tmpFile, $configData);
			if ($ret !== false){
				// ページ作成処理中断
				$this->gPage->abortPage();
				
				// ダウンロード処理
				$ret = $this->gPage->downloadFile($tmpFile, self::DEFAULT_COPYFILE_FILENAME, true/*実行後ファイル削除*/);
				
				// システム強制終了
				$this->gPage->exitSystem();
			} else {
				//$msg = 'ファイルのダウンロードに失敗しました(要因: ' . $zipFile->errorName(true) . ')';
				$msg = sprintf($this->_('Failed in downloading file. (detail: %s)'), 'ファイル保存失敗');		// ファイルのダウンロードに失敗しました(要因: %s)
				$this->setAppErrorMsg($msg);
				
				// テンポラリファイル削除
				unlink($tmpFile);
			}
		}
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'path', $this->convertToDispString($path));
	}
	/**
	 * データ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeJson($tmpl)
	{
		$fileCount = count($this->files);
		for ($i = 0; $i < $fileCount; $i++){
			$filePath = $this->gEnv->getSystemRootPath() . DIRECTORY_SEPARATOR . $this->basePath . DIRECTORY_SEPARATOR . $this->files[$i];
			if (file_exists($filePath)){
				$separator = ',';
				if ($i == $fileCount -1) $separator = '';
					
				$row = array(
					'no'    	=> $i + 1,
					'src_file'	=> $this->basePath . '/' . $this->files[$i],			// ファイルパス
					'dest' 		=> $this->basePath . '/',														// コピー先ディレクトリ
					'md5'		=> md5_file($filePath),
					'separator'	=> $separator
				);
				$tmpl->addVars('itemlist', $row);
				$tmpl->parseTemplate('itemlist', 'a');
			}
		}
	}
	/**
	 * ディレクトリ内のファイルを取得
	 *
	 * @param string $path		ディレクトリのパス
	 * @return array			相対ファイル名
	 */
	function getFiles($path)
	{
		static $basePath;
		
		if (!isset($basePath)) $basePath = $path . '/';
		$files = array();
		
		if ($dirHandle = @opendir($path)){
			while ($file = @readdir($dirHandle)) {
				if ($file == '..' || strStartsWith($file, '.') || strStartsWith($file, '_')) continue;	
				
				// ディレクトリのときはサブディレクトリもチェック
				$filePath = $path . '/' . $file;
				if (is_dir($filePath)){
					$files = array_merge($files, $this->getFiles($filePath));
				} else {
					$files[] = str_replace($basePath, '', $filePath);
				}
			}
			closedir($dirHandle);
		}
		return $files;
	}
}
?>
