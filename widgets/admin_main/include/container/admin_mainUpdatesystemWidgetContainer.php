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
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainUpdatesystemWidgetContainer extends admin_mainBaseWidgetContainer
{
	const UPDATE_INFO_URL = 'https://raw.githubusercontent.com/magic3org/magic3/master/include/version_info/update_system.json';		// バージョンアップ可能なバージョン情報取得用
	
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
		return 'updatesystem.tmpl.html';
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
		if ($act == 'getinfo'){		// 最新情報取得
			/*$type = $request->trimValueOf('type');			// 実行コマンドタイプ
			if ($type == 'create_site'){
				$cmdFile = $this->cmdFile_create_site;		// サイト作成、コマンドファイル
			} else if ($type == 'remove_site'){
				$cmdFile = $this->cmdFile_remove_site;		// サイト削除、コマンドファイル
			}
			if (file_exists($cmdFile)){
				$this->gInstance->getAjaxManager()->addData('code', '0');
			} else {			// インストールパッケージ更新完了のとき
				$this->gInstance->getAjaxManager()->addData('code', '1');
			}*/
			debug('find....');
		} else {
			// アップデート可能なバージョンを取得
			$infoSrc = file_get_contents(self::UPDATE_INFO_URL);
			if ($infoSrc !== false){
				$versionInfo = json_decode($infoSrc, true);
			
				// バージョン番号を表示
				//$verStr = '<b><font color="green">' . $versionInfo['version_disp'] . '</font></b>';
				$versionStr = $versionInfo['version_disp'];
				$disabled = 'disabled';
				if (version_compare($versionInfo['version'], M3_SYSTEM_VERSION) > 0){	// バージョンアップ可能な場合
					$versionStr = '<span class="available">' . $versionStr . '</span>';
					$disabled = '';
				}
				$this->tmpl->addVar('_widget', 'ver_str', $versionStr);
				$this->tmpl->addVar('_widget', 'button_disabled', $disabled);
			}
		}
	}
}
?>
