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
	private $db;	// DB接続オブジェクト
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
		
		if ($act == 'update'){				// 送信確認

			
			$this->testScript();
		} else {

		}
	}
	function testScript()
	{
		// スクリプトファイルを読み込み
		$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/_test.sql';
//		$fileData = fread(fopen($scriptPath, 'r'), filesize($scriptPath));
		$fileData = file_get_contents($scriptPath);
				
		// ファイル内容を解析
//		$ret = $this->_db->_splitSql($lines, $fileData);
$ret = $this->_splitMultibyteSql($fileData, $lines);
		if ($ret){
			var_dump($lines);
		}
		
/*		$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $this->sampleId;
	
		// スクリプト実行
		if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
			$this->setMsg(self::MSG_GUIDANCE, 'スクリプト実行完了しました');
		} else {
			$this->setMsg(self::MSG_APP_ERR, "スクリプト実行に失敗しました");
		}
		if (!empty($errors)){
			foreach ($errors as $error) {
				$this->setMsg(self::MSG_APP_ERR, $error);
			}
		}*/
	}
}
?>
