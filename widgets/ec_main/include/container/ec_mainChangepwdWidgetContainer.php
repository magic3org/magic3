<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_mainChangepwdWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainChangepwdWidgetContainer extends ec_mainBaseWidgetContainer
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
		return 'changepwd.tmpl.html';
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
		$password = $request->trimValueOf('password');
		$length = $request->trimValueOf('length');		// パスワード長
		if ($act == 'update'){			// パスワード更新のとき
			// パスワード変更
			$ret = $this->_db->updateLoginUserPassword($this->gEnv->getCurrentUserId(), $password, true/*MD5化されているパスワード*/);
			if ($ret){
				//$this->tmpl->addVar("_widget", "message", 'パスワード変更が完了しました');
				$this->setGuidanceMsg('パスワード変更が完了しました');
				$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
				$this->tmpl->addVar("_widget", "pwd1_disabled", 'disabled');		// 入力フィールド不可
				$this->tmpl->addVar("_widget", "pwd2_disabled", 'disabled');		// 入力フィールド不可
				
				$this->tmpl->addVar("_widget", "pwd_value", str_repeat('*', $length));		// ダミー値を設定
				
				// 会員メニューへのリンクを表示
				$this->tmpl->setAttribute('show_complete', 'visibility', 'visible');
				$this->tmpl->addVar("show_complete", "menu_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=membermenu', true));
			} else {
				//$this->tmpl->addVar("_widget", "message", 'パスワード変更に失敗しました');
				$this->setUserErrorMsg('パスワード変更に失敗しました');
			}
		}
		$this->tmpl->addVar("_widget", "button_label", 'パスワード変更');		// ボタンのラベル
	}
}
?>
