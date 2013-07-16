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
 * @version    SVN: $Id: reg_userSendpwdWidgetContainer.php 5197 2012-09-13 04:36:13Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/reg_userBaseWidgetContainer.php');

class reg_userSendpwdWidgetContainer extends reg_userBaseWidgetContainer
{
	const DEFAULT_TITLE = 'パスワード再送';		// 画面タイトル
	const SEND_PASSWORD_FORM = 'send_password';		// パスワード送信用フォーム
	
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
		if ($act == 'sendpassword'){			// パスワード再送信のとき
			$account = $request->trimValueOf('account');
			$this->checkMailAddress($account, 'Eメール');
			
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				$isSend = false;
				$errMessage = '';
				if ($this->_db->getLoginUserRecord($account, $row, true/*有効なユーザのみ*/)){		// アカウントからログインIDを取得
					// パスワード作成
					$password = $this->makePassword();
		
					// パスワード変更
					$ret = $this->_db->updateLoginUserPassword($row['lu_id'], $password);
					if ($ret){
						$fromAddress = $this->getFromAddress();	// 送信元アドレス
						$toAddress = $account;			// eメール(ログインアカウント)
						$url = $this->gEnv->createCurrentPageUrl() . sprintf(self::EMAIL_LOGIN_URL, urlencode($account), urlencode($password));		// ログイン用URL
						$mailParam = array();
						$mailParam['PASSWORD'] = $password;
						$mailParam['URL']		= $this->getUrl($url, true);		// ログイン用URL
						$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', self::SEND_PASSWORD_FORM, $mailParam);// 自動送信
						$isSend = true;		// 送信完了
					}
				} else {
					$errMessage = '登録されていないアカウント、または、アカウントが承認済み、ログイン可、有効期間内になっていません。';
				}
				if ($isSend){
					$this->setGuidanceMsg('パスワードを送信しました');
					$this->gOpeLog->writeUserInfo(__METHOD__, 'パスワードを送信しました。アカウント: ' . $account, 2100);
					
					$this->tmpl->addVar("_widget", "account_disabled", 'disabled');		// アカウント編集不可
					$this->tmpl->addVar("_widget", "button_disabled", 'disabled');		// ボタン使用不可
				} else {
					$this->setAppErrorMsg('パスワード送信に失敗しました');
					$this->writeUserError(__METHOD__, 'パスワード送信に失敗しました。アカウント: ' . $account, 2200, $errMessage);
				}
			}
			$this->tmpl->addVar("_widget", "account", $account);		// アカウント再設定
		}
		$this->tmpl->addVar("_widget", "button_label", 'パスワード送信');		// ボタンのラベル
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
