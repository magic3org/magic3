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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

//class admin_test_ckeditorWidgetContainer extends BaseAdminWidgetContainer
class admin_test_ckeditorWidgetContainer extends BaseInstallWidgetContainer
{
	const CURRENT_VERSION = '1.1.0';		// 現在のバージョン
	
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
	}
	/**
	 * SQLスクリプト実行
	 *
	 * 実行するSQLスクリプトファイル名を実行順に配列で返す。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップデート)
	 * @param string $version				現在のバージョン
	 * @return array						実行するスクリプトの配列
	 */
	function _doScript($request, $install, $version)
	{
		$scripts = array();
		
		switch ($install){
			case 0:		// インストール
				$scripts[] = 'install.sql';
				break;
			case 1:		// アンインストール
				$scripts[] = 'uninstall.sql';
				break;
			case 2:		// アップデート
				// ウィジェットのバージョンを確認
				if (version_compare($version, self::CURRENT_VERSION) < 0) $scripts[] = 'update.sql';
				break;
			default:
				break;
		}
		return $scripts;
	}
}
?>
