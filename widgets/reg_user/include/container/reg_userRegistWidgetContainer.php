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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/reg_userBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reg_userDb.php');

class reg_userRegistWidgetContainer extends reg_userBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const DEFAULT_TITLE = '会員登録';		// 画面タイトル
	const DEFAULT_CAN_REGIST = 1;			// ユーザ登録を使用するかどうか
	const REGIST_USER_AUTO_FORM = 'regist_user_auto';		// パスワード送信用フォーム(自動登録)
	const REGIST_USER_AUTH_FORM = 'regist_user_auth';		// パスワード送信用フォーム(管理者認証)
	const OPERATION_LOG_LINK = 'task=userlist';				// 運用ログリンク先
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new reg_userDb();
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
			return 'regist_bootstrap.tmpl.html';
		} else {
			return 'regist.tmpl.html';
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
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->authType	= $paramObj->authType;			// 承認タイプ
		}
		if (empty($this->authType)){
			$this->setAppErrorMsg('承認タイプが設定されていません');
			
			$this->tmpl->addVar("_widget", "send_button_label", '設定なし');// 送信ボタンラベル
			$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
			return;			// 承認タイプが設定されていないときは終了
		}

		// デフォルト値取得
		$canRegist = self::DEFAULT_CAN_REGIST;			// ユーザ登録を使用するかどうか
		$inputEnabled = true;			// 入力の許可状態
		
		// 入力値取得
		$act = $request->trimValueOf('act');
		$name = $request->trimValueOf('item_name');			// 名前
		$email = $request->trimValueOf('item_email');	// Email
		$email2 = $request->trimValueOf('item_email2');	// Email確認用
		$forward = $request->trimValueOf(M3_REQUEST_PARAM_FORWARD);		// 画面遷移用パラメータ
	
		// 画面遷移用URLをチェック
		if (!empty($forward) && !$this->gEnv->isSystemUrlAccess($forward)) $forward = '';
		
		if ($act == 'regist'){			// ユーザ登録
			$this->checkInput($name, '名前');
			$this->checkMailAddress($email, 'Eメール');
			$this->checkMailAddress($email2, 'Eメール(確認)');
			if ($this->getMsgCount() == 0){			// メールアドレスのチェック
				if ($email != $email2){
					$this->setAppErrorMsg('Eメールアドレスに誤りがあります');
				} else if ($this->_db->isExistsAccount($email)){// メールアドレスがログインIDとして既に登録されているかチェック
					$this->setAppErrorMsg('このEメールアドレスは既に登録されています');
				}
			}
		
			// エラーなしの場合はメール送信
			if ($this->getMsgCount() == 0){
				// パスワード作成
				$password = $this->makePassword();

				// ログインユーザを作成
				$ret = $this->db->addUser($name, $email, $password, $this->gEnv->getCurrentWidgetId(), $loginUserId);		// 新規ログインユーザIDを取得
				if ($ret){
					if ($this->authType == 'auto'){			// 自動認証
						// 運用ログを残す
						$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザが登録されました。ユーザはログインにより自動承認されます。アカウント: ' . $email . ', 名前: ' . $name, 2350,
												'account=' . $email . ', userid=' . $loginUserId, 'account=' . $email/*検索補助データ*/);
						
						// メールフォームタイプ
						$formType = self::REGIST_USER_AUTO_FORM;		// パスワード送信用フォーム(自動登録)
						$message = '登録完了しました。Eメールアドレス宛てにパスワードが送信されます。<br />ログインにより自動承認されます。';
					} else if ($this->authType == 'admin'){			// 管理者による認証
						// 運用ログを残す
						$this->gOpeLog->writeUserRequest(__METHOD__, 'ユーザが登録されました。ユーザを認証してください。アカウント: ' . $email . ', 名前: ' . $name, 2350,
												'account=' . $email . ', userid=' . $loginUserId, 'account=' . $email/*検索補助データ*/, self::OPERATION_LOG_LINK/*リンク先*/);
						
						// メールフォームタイプ
						$formType = self::REGIST_USER_AUTH_FORM;		// パスワード送信用フォーム(管理者認証)
						$message = '登録完了しました。Eメールアドレス宛てにパスワードが送信されます。<br />管理者からの承認後、ログイン可能になります。';
					}

					$fromAddress = $this->getFromAddress();	// 送信元アドレス
					$toAddress = $email;			// eメール(ログインアカウント)
					$ccAddress = $fromAddress;		// CCメール
					$url = $this->gEnv->createCurrentPageUrl() . sprintf(self::EMAIL_LOGIN_URL, urlencode($email), urlencode($password));		// ログイン用URL
					if (!empty($forward)) $url .= '&' . M3_REQUEST_PARAM_FORWARD . '=' . urlencode($forward);			// 遷移画面が設定されている場合は追加
					$mailParam = array();
					$mailParam['PASSWORD'] = $password;
					$mailParam['URL']		= $this->getUrl($url, true);		// ログイン用URL
					$ret = $this->gInstance->getMailManager()->sendFormMail(1, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', $formType, $mailParam, $ccAddress);// 自動送信
					$this->setGuidanceMsg($message);
											
					// 項目を入力不可に設定
					$inputEnabled = false;			// 入力の許可状態

					$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
				}
			}
			
			$this->tmpl->addVar("_widget", "send_button_label", '登録する');// 送信ボタンラベル
		} else {
			// メール送信不可の場合はボタンを使用不可にする
			if ($canRegist){
				$this->tmpl->addVar("_widget", "send_button_label", '登録する');// 送信ボタンラベル
			} else {
				$this->tmpl->addVar("_widget", "send_button_label", '登録停止中');// 送信ボタンラベル
				$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
			}
		}
		// ボタンの状態を設定
		if (!$inputEnabled){			// 入力の許可状態
			$this->tmpl->addVar('_widget', 'name_disabled', 'disabled');
			$this->tmpl->addVar('_widget', 'email_disabled', 'disabled');
			$this->tmpl->addVar('_widget', 'email2_disabled', 'disabled');
		}
		
		// 入力値を戻す
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "email", $this->convertToDispString($email));
		$this->tmpl->addVar("_widget", "email2", $this->convertToDispString($email2));
		$this->tmpl->addVar("_widget", "forward", $this->convertToDispString($forward));		// 遷移先を維持
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
