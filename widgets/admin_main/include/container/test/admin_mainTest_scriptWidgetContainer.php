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
//	private $db;	// DB接続オブジェクト
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
//		$this->db = new admin_mainDb();
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
		$this->sampleId = $request->trimValueOf('sample_sql');
		if ($act == 'exec'){				// テスト実行
			// ファイルデータ取得
			$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $this->sampleId;
			$fileData = file_get_contents($scriptPath);
			
			// クエリー行取得
			$ret = $this->_db->_splitSql($fileData, $lines);
//			$ret = $this->_splitMultibyteSql($fileData, $lines2);
			if ($ret){
				$ret = $this->_db->_splitMultibyteSql($fileData, $lines2);

				$lineCount = count($lines);
				$lineCount2 = count($lines2);
				if ($lineCount == $lineCount2){
					for ($i = 0; $i < $lineCount; $i++) {
						if ($lines[$i] != $lines2[$i]){
							$this->setMsg(self::MSG_APP_ERR, "行データエラー file=" . $this->sampleId);
							$this->setMsg(self::MSG_APP_ERR, $lines[$i]);
							$this->setMsg(self::MSG_APP_ERR, $lines2[$i]);
							break;
						}
					}
					if ($i == $lineCount) $this->setMsg(self::MSG_GUIDANCE, 'データエラーなし file=' . $this->sampleId . ' 行数:' . $lineCount);
				} else {
					$this->setMsg(self::MSG_APP_ERR, "行数がマッチしません file=" . $this->sampleId);
				}

			} else {
				$this->setMsg(self::MSG_APP_ERR, "ファイルが読み込めません file=" . $this->sampleId);
			}
		}
		
		// サンプルSQLスクリプトディレクトリのチェック
		$searchPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR;
		$files = $this->getScript($searchPath);
		sort($files);		// ファイル名をソート

		// スクリプト選択メニュー作成
		for ($i = 0; $i < count($files); $i++){
			$file = $files[$i];
			$name = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file);		// 拡張子除く
			
			// デフォルトのファイル名を決定
			if (empty($this->sampleId)) $this->sampleId = $file;
			
			$selected = '';
			if ($file == $this->sampleId) $selected = 'selected';

			$row = array(
				'value'    => $this->convertToDispString($file),			// ファイル名
				'name'     => $this->convertToDispString($name),			// ファイル名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
	}
	/**
	 * ディレクトリ内のスクリプトファイルを取得
	 *
	 * @param string $path		ディレクトリのパス
	 * @return array			スクリプトファイル名
	 */
	function getScript($path)
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
					$files = array_merge($files, $this->getScript($filePath));
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
