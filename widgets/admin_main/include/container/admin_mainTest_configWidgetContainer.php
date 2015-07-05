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

class admin_mainTest_configWidgetContainer extends admin_mainBaseWidgetContainer
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
		return 'test/test_config.tmpl.html';
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
		//$this->setUserErrorMsg('エラーメッセージテスト1231312312312313321312321313213131312');
		$this->setAppErrorMsg('エラーメッセージテスト1231312312312313321312321313213131312');
		

		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$act = $request->trimValueOf('act');
		
		if ($act == 'update'){				// 送信確認
			echo '<h4>Session value check</h4>';
			echo 'old='.$postTicket.'<br>';
			echo 'current='.$request->getSessionValue(M3_SESSION_POST_TICKET);
		} else {
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
		}
	}
}
?>
