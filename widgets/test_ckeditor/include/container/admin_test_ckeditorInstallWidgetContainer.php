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
require_once($gEnvManager->getContainerPath() .	'/baseInstallWidgetContainer.php');

class admin_test_ckeditorInstallWidgetContainer extends BaseInstallWidgetContainer
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
	 * SQLスクリプト実行前処理
	 *
	 * SQLスクリプトファイル実行前に呼ばれる。スクリプト実行前に必要な処理を行う。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップグレード)
	 * @return なし
	 */
	function _preScript($request, $install)
	{
	}
	/**
	 * SQLスクリプト実行後処理
	 *
	 * SQLスクリプトファイル実行後に呼ばれる。スクリプト実行後に必要な処理を行う。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップグレード)
	 * @return なし
	 */
	function _postScript($request, $install)
	{
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
