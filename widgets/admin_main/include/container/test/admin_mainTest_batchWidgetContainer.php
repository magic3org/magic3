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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTest_batchWidgetContainer extends admin_mainBaseWidgetContainer
{
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
		return 'test/test_batch.tmpl.html';
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
	/*
		$output=null;
		$retval=null;
		
		//$cmd = 'php hoge.php > /dev/null &';
		$cmd = 'whoami > /dev/null &';
		exec($cmd, $output, $retval);
		
		echo "Returned with status $retval and output:\n";
		print_r($output);
		*/
		$this->post_async("http://localhost/projectname/testpage.php", "Keywordname=testValue");
	}
	function post_async($url,$params)
    {

        $post_string = $params;

        $parts=parse_url($url);
/*
        //$fp = fsockopen($parts['Host'],isset($parts['port'])?$parts['port']:80,
		$fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 30);

        //$out = "GET ".$parts['path']."?$post_string"." HTTP/1.1\r\n";
        //$out.= "Host: ".$parts['Host']."\r\n";
		$out.= "Host: localhost\r\n";
 //       $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
 //       $out.= "Content-Length: ".strlen($post_string)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);*/
		
		$fp = fsockopen('127.0.0.1', 80, $errno, $errstr, 30);	// 送信元は「127.0.0.1」
		//$fp = fsockopen('localhost', 80, $errno, $errstr, 30);		// 送信元がIPv6のループバックIP「::1」になる
		if (!$fp) {
		    echo "$errstr ($errno)<br />\n";
		} else {
		$out = "GET /magic3/connector.php?task=dailyjob HTTP/1.1\r\n";
		    //$out = "GET / HTTP/1.1\r\n";
		    //$out .= "Host: 127.0.0.1\r\n";
			$out .= "Host: localhost\r\n";
		    $out .= "Connection: Close\r\n\r\n";
		    fwrite($fp, $out);
		    //while (!feof($fp)) {
		    //    echo fgets($fp, 128);
		    //}
		    fclose($fp);
		}
    }
}
?>
