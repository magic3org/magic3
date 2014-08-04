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
		$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=1';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, M3_SYSTEM_NAME . '/' . M3_SYSTEM_VERSION);		// ユーザエージェント
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		// 画面に出力しない
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
//curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10');
/*curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=tamae5tresstr4al2va1b4t4b6');*/
$content = curl_exec($ch);
curl_close($ch);
//echo "<pre>".$content."</pre>";
$homepage = $content;
//echo $url;
		//$homepage = file_get_contents($url);
		//<link[^>]+?text/css[^>]
	//	$pattern = '/(<link\b.+href=")(?!http)([^"]*)(".*>)/';
/*		$pattern = '/<link[^>]+?stylesheet[^>]*?>/si';*/
		/*$pattern = '/<link\b[^>]*?>/si';*/
		$pattern = '/<head\b[^>]*?>(.*?)<\/head\b[^>]*?>/si';
		if (preg_match($pattern, $homepage, $matches)) $headContent = $matches[0];

//echo $headContent;
		$pattern = '/<link[^<]*?href\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>/si';
	/*	$pattern = '/<!--\[if\b.*?\]>(.+?)<!\[endif\]-->/si';*/
/*	$pattern = '/<!--\[if\b.*?\]>[\b]*<link[^<]*?href\s*=\s*[\'"]+(.+?)[\'"]+[^>]*?>[\b]*<!\[endif\]-->/si';*/
		if ($ret = preg_match_all($pattern, $headContent, $matches, PREG_SET_ORDER)){
			foreach ($matches as $val) {
				echo "*{$val[1]}<br />";
			}
		}
echo '--------------------';
	}

}
?>
