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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_test_uploadfileWidgetContainer extends BaseAdminWidgetContainer
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
		return 'admin.tmpl.html';
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
		$html = $request->valueOf('editor');
		$html2 = $request->valueOf('editor2');
		if ($act == 'send'){
			echo 'editor='.$html.'<br />';
			echo 'editor2='.$html2.'<br />';
		}
		// パスの設定
//		$this->tmpl->addVar("_widget", "widget_url", $gEnvManager->getCurrentWidgetRootUrl());	// ウィジェットのルートディレクトリ
//		$this->tmpl->addVar("_widget", "root_url", $gEnvManager->getRootUrl());
//		$this->tmpl->addVar("_widget", "widget_sc_url", $gEnvManager->getCurrentWidgetScriptsUrl());

$this->gPage->addCkeditorCssFile($this->gEnv->getScriptsUrl() . '/' . 'bootstrap-3.3.6/css/bootstrap.min.css');
echo 'CKEditor2 added: ' . $this->gEnv->getScriptsUrl() . '/' . 'bootstrap-3.3.6/css/bootstrap.min.css';
	}
}
?>
