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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTest_scriptWidgetContainer extends admin_mainBaseWidgetContainer
{
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	
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
		return 'test/test_script.tmpl.html';
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
		$path = $request->trimValueOf('path');
		$sampleId = $request->trimValueOf('sample_sql');
		$installId = $request->trimValueOf('install_sql');
		if ($act == 'exec'){				// テスト実行
			$filePath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $sampleId;
			
			// ファイル内容チェック
			$this->checkFile($filePath);
		} else if ($act == 'install'){				// インストールテスト実行
			$filePath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $installId;
			
			// インストール実行
			if ($this->gInstance->getDbManager()->execScriptWithConvert($filePath, $errors)){// 正常終了の場合
				$this->setMsg(self::MSG_GUIDANCE, 'スクリプト実行完了しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, "スクリプト実行に失敗しました");
			}
			if (!empty($errors)){
				foreach ($errors as $error) {
					$this->setMsg(self::MSG_APP_ERR, $error);
				}
			}
		} else if ($act == 'execdir'){				// ディレクトリテスト実行
			// サンプルSQLスクリプトディレクトリのチェック
			$searchPath = $this->gEnv->getSqlPath() . '/' . $path;
			
			// トップディレクトリの場合はサブディレクトリ参照しない
			if (empty($path)){
				$files = $this->getScript($searchPath);
			} else {
				$files = $this->getScript($searchPath, true);
			}
			sort($files);		// ファイル名をソート

			set_time_limit(0);
			for ($i = 0; $i < count($files); $i++){
				if (empty($path)){
					$filePath = $this->gEnv->getSqlPath() . '/' . $files[$i];
				} else {
					$filePath = $this->gEnv->getSqlPath() . '/' . $path . '/' . $files[$i];
				}
				
				// ファイル内容チェック
				$this->checkFile($filePath);
			}
		}
		
		// サンプルSQLスクリプトディレクトリのチェック
		$searchPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR;
		$files = $this->getScript($searchPath, true);
		sort($files);		// ファイル名をソート

		// スクリプト選択メニュー作成
		for ($i = 0; $i < count($files); $i++){
			$file = $files[$i];
			$name = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file);		// 拡張子除く
			
			// デフォルトのファイル名を決定
			if (empty($sampleId)) $sampleId = $file;
			
			$selected = '';
			if ($file == $sampleId) $selected = 'selected';

			$row = array(
				'value'    => $this->convertToDispString($file),			// ファイル名
				'name'     => $this->convertToDispString($name),			// ファイル名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
		
		// スクリプト選択メニュー作成
		for ($i = 0; $i < count($files); $i++){
			$file = $files[$i];
			$name = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file);		// 拡張子除く
			
			// デフォルトのファイル名を決定
			if (empty($installId)) $installId = $file;
			
			$selected = '';
			if ($file == $installId) $selected = 'selected';

			$row = array(
				'value'    => $this->convertToDispString($file),			// ファイル名
				'name'     => $this->convertToDispString($name),			// ファイル名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list2', $row);
			$this->tmpl->parseTemplate('sample__sql_list2', 'a');
		}
	}
	/**
	 * ディレクトリ内のスクリプトファイルを取得
	 *
	 * @param string $path		ディレクトリのパス
	 * @param bool $subDir		サブディレクトリを参照するかどうか
	 * @return array			スクリプトファイル名
	 */
	function getScript($path, $subDir = false)
	{
		static $basePath;
		
		if (!isset($basePath)) $basePath = $path . '/';
		$files = array();
		
		if ($dirHandle = @opendir($path)){
			while ($file = @readdir($dirHandle)) {
				if ($file == '..' || strStartsWith($file, '.')) continue;	
				
				// ディレクトリのときはサブディレクトリもチェック
				$filePath = $path . '/' . $file;
				if (is_dir($filePath)){
					if ($subDir) $files = array_merge($files, $this->getScript($filePath, true));
				} else {
					$files[] = str_replace($basePath, '', $filePath);
				}
			}
			closedir($dirHandle);
		}
		return $files;
	}
	/**
	 * ファイルの内容をチェック
	 *
	 * @param string $path		ディレクトリのパス
	 * @return bool				true=問題なし,false=エラーあり
	 */
	function checkFile($path)
	{
		$basename = basename($path);
		
		// ファイルデータ取得
		$fileData = file_get_contents($path);
		
		// クエリー行取得
//$ret = $this->_db->_splitMultibyteSql($fileData, $lines2);
$ret = $this->_splitSql($fileData, $lines);
//		$ret = $this->_db->_splitSql($fileData, $lines);
		if ($ret){
			$ret = $this->_db->_splitMultibyteSql($fileData, $lines2);

			$lineCount = count($lines);
			$lineCount2 = count($lines2);
			if ($lineCount == $lineCount2){
				for ($i = 0; $i < $lineCount; $i++) {
					if ($lines[$i] != $lines2[$i]){
						$this->setMsg(self::MSG_APP_ERR, "行データエラー file=" . $basename . ', line='. ($i + 1));
						$this->setMsg(self::MSG_APP_ERR, $lines[$i] . '<br>length=' . strlen($lines[$i]));
						$this->setMsg(self::MSG_APP_ERR, $lines2[$i] . '<br>length=' . strlen($lines2[$i]));
						break;
					}
				}
				if ($i == $lineCount) $this->setMsg(self::MSG_GUIDANCE, 'データエラーなし file=' . $basename . ' 行数:' . $lineCount);
			} else {
				$this->setMsg(self::MSG_APP_ERR, "行数がマッチしません file=" . $basename);
			}

		} else {
			$this->setMsg(self::MSG_APP_ERR, "ファイルが読み込めません file=" . $basename);
		}
	}

}
?>
