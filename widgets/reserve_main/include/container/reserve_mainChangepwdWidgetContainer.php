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
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: reserve_mainChangepwdWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainDb.php');

class reserve_mainChangepwdWidgetContainer extends BaseWidgetContainer
{
	private $mainDb;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	const USE_EMAIL		= 'use_email';		// EMAIL機能が使用可能かどうか
	const SEND_PASSWORD_FORM = 'send_password';		// パスワード送信用フォーム
	const AUTO_EMAIL_SENDER	= 'auto_email_sender';		// 自動送信メール用送信者アドレス
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->mainDb = new ec_mainDb();
		$this->sysDb = $gInstanceManager->getSytemDbObject();
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
		global $gEnvManager;
		
		$act = $request->trimValueOf('act');
		$password = $request->trimValueOf('password');
		$length = $request->trimValueOf('length');		// パスワード長
		if ($act == 'update'){			// パスワード更新のとき
			// パスワード変更
			$ret = $this->sysDb->updateLoginUserPassword($gEnvManager->getCurrentUserId(), $password, true/*MD5化されているパスワード*/);
			if ($ret){
				$this->tmpl->addVar("_widget", "message", 'パスワード変更が完了しました');
				$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
				$this->tmpl->addVar("_widget", "pwd1_disabled", 'disabled');		// 入力フィールド不可
				$this->tmpl->addVar("_widget", "pwd2_disabled", 'disabled');		// 入力フィールド不可
				
				$this->tmpl->addVar("_widget", "pwd_value", str_repeat('*', $length));		// ダミー値を設定
			} else {
				$this->tmpl->addVar("_widget", "message", 'パスワード変更に失敗しました');
			}
		}
		$this->tmpl->addVar("_widget", "button_label", 'パスワード変更');		// ボタンのラベル
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "script_url", $gEnvManager->getScriptsUrl());
	}
}
?>
