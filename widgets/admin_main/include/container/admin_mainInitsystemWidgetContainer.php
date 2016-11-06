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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainMainteBaseWidgetContainer.php');

class admin_mainInitsystemWidgetContainer extends admin_mainMainteBaseWidgetContainer
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
		return 'initsystem.tmpl.html';
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

		if ($act == 'initsys'){		// システム初期化のとき
			if (M3_PERMIT_REINSTALL){		// 再インストール可能なとき
				// テーブルの初期化フラグをリセット
				$this->gSystem->enableInitSystem();
			
				// インストーラを回復
				$this->gInstance->getFileManager()->recoverInstaller();
			
				$this->setMsg(self::MSG_GUIDANCE, 'インストーラを起動します<br />一旦ログアウトしてください');
			
				// 現在の設定しているテンプレートを解除
				$request->unsetSessionValue(M3_SESSION_CURRENT_TEMPLATE);
			} else {
				$this->setUserErrorMsg('インストール時の設定により再インストール処理は実行できません');
			}
		}
	}
}
?>
