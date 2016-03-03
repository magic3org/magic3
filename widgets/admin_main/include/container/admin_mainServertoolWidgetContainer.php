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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainServeradminBaseWidgetContainer.php');

class admin_mainServertoolWidgetContainer extends admin_mainServeradminBaseWidgetContainer
{
	private $toolArray;			// サーバ管理ツール
	private $toolUrl;			// ツールディレクトリへのURL
	const CF_SERVER_TOOL_USER = 'server_tool_user';			// 管理ツールアカウント
	const CF_SERVER_TOOL_PASSWORD = 'server_tool_password';		// 管理ツールパスワード
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// サーバ管理ツール
		$this->toolArray = array(	array(	'name' => 'phpMyAdmin',		'value' => 'phpmyadmin'),
									array(	'name' => 'PostfixAdmin',	'value' => 'postfixadmin')
							);
							
		$this->toolUrl = $this->gEnv->getAdminUrl(true/*adminディレクトリ削除*/) . '/' . M3_DIR_NAME_SERVER_TOOLS . '/';
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
		return 'servertool.tmpl.html';
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
		// 入力値を取得
		$act = $request->trimValueOf('act');
		
		// サーバ管理ツールメニュー作成
		$this->createToolMenu();
		
		// BASIC認証解除用のURL作成
		$user = $this->gSystem->getSystemConfig(self::CF_SERVER_TOOL_USER);// 管理ツールアカウント
		$pwd = $this->gSystem->getSystemConfig(self::CF_SERVER_TOOL_PASSWORD);// 管理ツールパスワード
		
		if (empty($user)){
			$toolUrl = $this->toolUrl;
		} else {
			list($preUrl, $postUrl) = explode('//', $this->toolUrl);
			$toolUrl = $preUrl . '//' . $user . ':' . $pwd . '@' . $postUrl;
		}
		$this->tmpl->addVar("_widget", "tool_url", $toolUrl);
	}
	/**
	 * サーバ管理ツールメニュー作成
	 *
	 * @return なし
	 */
	function createToolMenu()
	{
		$toolExists = false;		// ツールが存在するかどうか
		
		for ($i = 0; $i < count($this->toolArray); $i++){
			$value = $this->toolArray[$i]['value'];		// ディレクトリ名
			$name = $this->toolArray[$i]['name'];
			$url =  $this->toolUrl . $value . '/';
			
			// ツールが存在するかチェック
			$ret = @file_get_contents($url);
			if ($ret === false) continue;

			$row = array(
				'name'		=> $this->convertToDispString($name),			// ツール名
				'url'		=> $url				// ツールへのURL
			);
			$this->tmpl->addVars('tool_list', $row);
			$this->tmpl->parseTemplate('tool_list', 'a');
			$toolExists = true;		// ツールが存在するかどうか
		}
		if (!$toolExists){
			$this->setMsg(self::MSG_GUIDANCE, '使用可能なツールがありません');
			
			// 一覧非表示
			$this->tmpl->setAttribute('tool_list', 'visibility', 'hidden');
		}
	}
}
?>
