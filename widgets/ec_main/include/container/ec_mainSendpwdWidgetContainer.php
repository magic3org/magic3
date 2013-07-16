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
 * @version    SVN: $Id: ec_mainSendpwdWidgetContainer.php 5572 2013-01-23 08:43:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainSendpwdWidgetContainer extends ec_mainBaseWidgetContainer
{
//	const USE_EMAIL		= 'use_email';		// EMAIL機能が使用可能かどうか
//	const AUTO_EMAIL_SENDER	= 'auto_email_sender';		// 自動送信メール用送信者アドレス
		
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
		$act = $request->trimValueOf('act');
		$useEmail = $this->_getConfig(photo_shopCommonDef::CF_USE_EMAIL);
		if ($act == 'sendpassword'){			// パスワード再送信のとき
			$account = $request->trimValueOf('photo_account');
			if ($useEmail == '1'){		// メール送信可能のとき
				$isSend = false;
				$errMessage = '';
				if ($this->_db->getLoginUserRecord($account, $row)){		// アカウントからログインIDを取得
					// パスワード作成
					$password = $this->makePassword();
			
					// パスワード変更
					$ret = $this->_db->updateLoginUserPassword($row['lu_id'], $password);
					if ($ret){
						$fromAddress = $this->_getConfig(photo_shopCommonDef::CF_AUTO_EMAIL_SENDER);	// 自動送信送信元
						if (empty($fromAddress)) $fromAddress = $this->gEnv->getSiteEmail();// 送信元が取得できないときは、システムのデフォルトメールアドレスを使用
						$toAddress = $account;			// eメール(ログインアカウント)
						
						//$url = $this->gEnv->createCurrentPageUrl() . sprintf(photo_shopCommonDef::EMAIL_LOGIN_URL, urlencode($account), urlencode($password));		// ログイン用URL
						//$url = $this->gPage->getDefaultPageUrlByWidget($this->gEnv->getCurrentWidgetId(), 
						//				sprintf(photo_shopCommonDef::EMAIL_LOGIN_URL, urlencode($toAddress), urlencode($password)));		// ログイン用URL
						$url = photo_shopCommonDef::createLoginUrl($toAddress, $password);		// ログイン用URL
						$mailParam = array();
						$mailParam['PASSWORD'] = $password;
						$mailParam['URL']		= $this->getUrl($url, true);		// ログイン用URL
						$mailParam['SIGNATURE']	= self::$_mainDb->getCommerceConfig(photo_shopCommonDef::CF_E_SHOP_SIGNATURE);	// ショップメール署名
						$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '',
																				photo_shopCommonDef::MAIL_FORM_SEND_PASSWORD, $mailParam);// 自動送信
						$isSend = true;		// 送信完了
					}
				} else {
					$errMessage = '登録されていないアカウントが入力されました。';
				}
				if ($isSend){
					$this->setGuidanceMsg('パスワード送信しました');
					$this->tmpl->addVar("_widget", "account_disabled", 'disabled');		// アカウント編集不可
					$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
				} else {
					$this->setAppErrorMsg('パスワード送信に失敗しました');
					$this->writeUserError(__METHOD__, 'パスワード送信に失敗しました。アカウント: ' . $account, 2200, $errMessage);
				}
			} else {
				$this->setAppErrorMsg('パスワード送信できません');
			}
			$this->tmpl->addVar("_widget", "account", $account);		// アカウント再設定
		}
		if ($useEmail == 0){		// メール送信機能が使用可能になっていないとき
			$this->tmpl->addVar("_widget", "account_disabled", 'disabled');		// アカウント編集不可
			$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
			$this->tmpl->addVar("_widget", "button_label", '送信機能停止中');		// ボタンのラベル
		} else {
			$this->tmpl->addVar("_widget", "button_label", 'パスワード送信');		// ボタンのラベル
		}
	}
}
?>
