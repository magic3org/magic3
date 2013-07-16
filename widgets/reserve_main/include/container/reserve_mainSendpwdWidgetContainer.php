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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: reserve_mainSendpwdWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class reserve_mainSendpwdWidgetContainer extends BaseWidgetContainer
{
	private $mainDb;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	const SEND_PASSWORD_FORM = 'send_password_simple';		// パスワード送信用フォーム(簡易形式)
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
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
		return 'sendpwd.tmpl.html';
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
		
		// メール送信のチェック
		$fromAddress = $gEnvManager->getSiteEmail();// システムのデフォルトメールアドレスを使用
		if (empty($fromAddress)){
			$canEmail = false;			// Eメール送信できるかどうか
		} else {
			$canEmail = true;			// Eメール送信できるかどうか
		}
		
		if ($act == 'sendpassword'){			// パスワード再送信のとき
			$account = $request->trimValueOf('reserve_account');
			
			// 入力エラーチェック
			$this->checkMailAddress($account, 'メールアドレス');
			
			// エラーなしの場合は、メール送信
			if ($this->getMsgCount() == 0){
				// ######## メール送信処理 ########
				$isSend = false;
				if ($this->sysDb->getLoginUserRecord($account, $row)){		// アカウントからログインIDを取得
					// パスワード作成
					$password = $this->makePassword();
		
					// パスワード変更
					$ret = $this->sysDb->updateLoginUserPassword($row['lu_id'], $password);
					if ($ret){
						$toAddress = $account;			// eメール(ログインアカウント)

						$mailParam = array();
						$mailParam['PASSWORD'] = $password;
						$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $gEnvManager->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', self::SEND_PASSWORD_FORM, $mailParam);// 自動送信
						$isSend = true;		// 送信完了
					}
				}
				if ($isSend){
					$this->setGuidanceMsg('パスワード送信しました');
					$this->tmpl->addVar("_widget", "account_disabled", 'disabled');		// アカウント編集不可
					$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
				} else {
					$this->setAppErrorMsg('パスワード送信に失敗しました');
				}
			}
			$this->tmpl->addVar("_widget", "account", $account);		// アカウント再設定
		} else {
			if (!$canEmail) $this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
		}
		if ($canEmail){
			$this->tmpl->addVar("_widget", "button_label", 'パスワード送信');		// ボタンのラベル
		} else {		// メール送信できないとき
			$this->tmpl->addVar("_widget", "button_label", 'パスワード送信停止中');		// ボタンのラベル
		}
		$this->tmpl->addVar("_widget", "login_url", $gEnvManager->createCurrentPageUrl() . '&task=login');		// ログイン用URL
	}
}
?>
