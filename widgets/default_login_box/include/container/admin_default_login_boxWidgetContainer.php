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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_default_login_boxWidgetContainer.php 5954 2013-04-21 03:55:07Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_default_login_boxWidgetContainer extends BaseAdminWidgetContainer
{
	const CF_AUTO_LOGIN = 'auto_login';		// 自動ログイン機能を使用するかどうか
		
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
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		if ($act == 'update'){		// 設定更新のとき
			// 入力値を取得
			$useAutoLogin = ($request->trimValueOf('item_use_auto_login') == 'on') ? 1 : 0;		// 自動ログイン
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$ret = $this->gSystem->updateSystemConfig(self::CF_AUTO_LOGIN, $useAutoLogin);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
		}
		$useAutoLogin = $this->gSystem->getSystemConfig(self::CF_AUTO_LOGIN);
		
		// 画面にデータを埋め込む
		$checked = '';
		if ($useAutoLogin) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_auto_login",	$checked);// 自動ログイン
	}
}
?>
