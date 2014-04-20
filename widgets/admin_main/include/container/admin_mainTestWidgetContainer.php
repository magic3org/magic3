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
require_once($gEnvManager->getLibPath() .	'/tcpdf/config/lang/jpn.php');
require_once($gEnvManager->getLibPath() .	'/tcpdf/tcpdf.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath() .	'/gitRepo.php');

class admin_mainTestWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
		
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
	 * ディスパッチ処理
	 *
     * HTTPリクエストの内容を見て処理をコンテナに振り分ける
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return bool 						このクラスの_setTemplate(), _assign()へ処理を継続するかどうかを返す。
	 *                                      true=処理を継続、false=処理を終了
	 */
	function _dispatch($request, &$param)
	{
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if ($task == 'test'){
			return true;
		} else {
			$this->gLaunch->goSubWidget($task);
			return false;
		}
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
		return 'test.tmpl.html';
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
		$repo = new GitRepo('magic3org', 'magic3');
		$infoSrc = file_get_contents('https://raw.githubusercontent.com/magic3org/magic3/master/include/sql/update_widgets.sql');
		
		$widgetId = 'blog_search_box';
		//$exp = '/INSERT INTO _widgets.*VALUES.*\(\'' . preg_quote($widgetId) . '\'.*\'([0-9\.]+[a-z]*)\'.*\);/s';			// バージョン番号の最後の「b」(ベータ版)等は許可
		//$exp = '/INSERT\sINTO\s_widgets.*?\(.*?\).*?VALUES.*?\(.*?\);/s';
		//$exp = '/INSERT\sINTO\s_widgets.*?\(.*?\)\sVALUES\s\(\'' . preg_quote($widgetId) . '\'/s';
		//$exp = '/INSERT.*?\(\'' . preg_quote($widgetId) . '\'/s';
		$exp = '/INSERT\sINTO\s_widgets[\s]*?\(([^\n]*?)\)[\s]*?VALUES[\s]*?\((\'' . preg_quote($widgetId) . '\'[^\n]*?)\);/s';
		if (preg_match($exp, $infoSrc, $matches)){
//echo $matches[0].'<br />';
echo $matches[1].'<br />';
echo $matches[2].'<br />';

		$keyArray = array_map('trim', explode(',', $matches[1]));
		$valueArray = explode(',', $matches[2]);
		$valueArray = array_map(create_function('$a', 'return trim($a, " \'");'), $valueArray);
var_dump($keyArray);
var_dump($valueArray);
		}
/*		$ret = $repo->createZipArchive('/widgets/blog_search_box', $this->gEnv->getIncludePath() . DIRECTORY_SEPARATOR . 'widgets_update' . DIRECTORY_SEPARATOR . 'blog_search_box.zip');
		if (!$ret){
			$resCode = $repo->getResponseCode();
			if ($resCode == 403){
				echo '接続回数の上限を超えました。しばらく待ってから再度接続してください。';
			} else {
				echo 'GitHubへの接続に失敗しました。';
			}
		}*/
	}
}
?>
