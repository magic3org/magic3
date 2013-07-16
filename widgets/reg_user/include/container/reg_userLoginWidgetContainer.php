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
 * @version    SVN: $Id: reg_userLoginWidgetContainer.php 5207 2012-09-14 05:10:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/reg_userBaseWidgetContainer.php');

class reg_userLoginWidgetContainer extends reg_userBaseWidgetContainer
{
	const DEFAULT_TITLE = 'ログイン';		// 画面タイトル
	const REGIST_USER_COMPLETED_FORM = 'regist_user_completed';		// メールフォーム
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	
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
		return 'login.tmpl.html';
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
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->authType	= $paramObj->authType;			// 承認タイプ
		}
		if (empty($this->authType)) return;		// 承認タイプが設定されていないときは終了
				
		$act = $request->trimValueOf('act');
		$task = $request->trimValueOf('task');
		if ($act == 'user_login'){			// 会員ログインのとき
			// アカウント、パスワード取得
			$account = $request->trimValueOf('user_account');
			$password = $request->trimValueOf('password');
			$postTicket = $request->trimValueOf('ticket');		// POST確認用
			
			$message = '';
			$isErr = true;	// エラー発生状況
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// ユーザ認証
				if ($this->authType == 'auto'){		// ユーザ自動承認のとき
					if ($this->gAccess->userLoginByAccount($account, $password, false/*アクセス権はチェックしない*/)){
						// ユーザ自動認証のときは未承認ユーザから一般ユーザへ変更
						$userId = $this->gEnv->getCurrentUserId();
						if (!empty($userId)){
							if (!$this->_db->isAuthenticatedUser($userId)){		// 未承認のときのみ承認作業を行う
								$ret = $this->_db->makeNormalLoginUser($userId);// 一般ログインユーザに設定
								if ($ret){
									$fromAddress = $this->getFromAddress();	// 送信元アドレス
									$toAddress = $fromAddress;
									$mailParam = array();
									$mailParam['ACCOUNT'] = $account;
									$ret = $this->gInstance->getMailManager()->sendFormMail(1, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', self::REGIST_USER_COMPLETED_FORM, $mailParam);// 自動送信
									$message = 'ログインしました。ユーザが自動承認されました。';
									$isErr = false;				// 正常終了
								}
							} else {	// 承認済みのとき
								$message = 'ログインしました';
								$isErr = false;				// 正常終了
							}
						}
					}
				} else if ($this->authType == 'admin'){		// 管理者による承認のとき
					if ($this->gAccess->userLoginByAccount($account, $password)){
						$message = 'ログインしました';
						$isErr = false;				// 正常終了
					}
				}
			}
			if ($isErr){		// エラー発生のとき
				// ログイン状態を削除
				$this->gAccess->userLogout();
			
				if (empty($message)) $message = 'ログインに失敗しました';
				$this->setUserErrorMsg($message);
			} else {
				$this->setGuidanceMsg($message);
				
				$this->tmpl->addVar("_widget", "user_account", $account);
				$this->tmpl->addVar("_widget", "user_password", self::DEFAULT_PASSWORD);
				
				$this->tmpl->addVar("_widget", "account_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "password_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "login_disabled", 'disabled');// 送信ボタン
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		} else {
			if ($task == 'emaillogin'){		// Eメールからのログイン画面の表示
				$account = $request->trimValueOf('account');
				$pwd = $request->trimValueOf('pwd');
			
				$this->tmpl->addVar("_widget", "user_account", $account);
				$this->tmpl->addVar("_widget", "user_password", $pwd);
			}
		}
		
		// ハッシュキー作成
		$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
		$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
		$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
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
