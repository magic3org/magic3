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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/reg_userBaseWidgetContainer.php');

class reg_userChangepwdWidgetContainer extends reg_userBaseWidgetContainer
{
	const DEFAULT_TITLE = 'パスワード変更';		// 画面タイトル
	
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
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){			// Bootstrap型テンプレートのとき
			return 'changepwd_bootstrap.tmpl.html';
		} else {
			return 'changepwd.tmpl.html';
		}
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
				$this->tmpl->addVar("_widget", "message", 'パスワード変更が完了しました');
				$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
				$this->tmpl->addVar("_widget", "pwd1_disabled", 'disabled');		// 入力フィールド不可
				$this->tmpl->addVar("_widget", "pwd2_disabled", 'disabled');		// 入力フィールド不可
				
				$this->tmpl->addVar("_widget", "pwd_value", str_repeat('*', $length));		// ダミー値を設定
			} else {
				$this->tmpl->addVar("_widget", "message", 'パスワード変更に失敗しました');
			}
		} else {
			$this->tmpl->addVar("_widget", "message", '新規パスワードを入力してください');
		}
		$this->tmpl->addVar("_widget", "button_label", 'パスワード変更');		// ボタンのラベル
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
}
?>
