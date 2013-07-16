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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: default_login_boxWidgetContainer.php 5956 2013-04-21 10:43:02Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');

class default_login_boxWidgetContainer extends BaseWidgetContainer
{
	const TARGET_WIDGET = 'reg_user';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ログイン';			// デフォルトのウィジェットタイトル
	const CSS_FILE = '/style.css';		// CSSファイルのパス
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
		return 'index.tmpl.html';
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
		if ($act == 'loginbox_login'){			// ログインのとき
			// アカウント、パスワード取得
			$account = $request->trimValueOf('account');
			$password = $request->trimValueOf('password');
			$autoLogin = ($request->trimValueOf('autologin') == 'on') ? 1 : 0;		// 自動ログインを使用するかどうか
			
			// ユーザ認証
			if ($this->gAccess->userLoginByAccount($account, $password)){
				$userId = $this->gEnv->getCurrentUserId();
				
				// ### 自動ログインの処理 ###
				// 自動ログインしないに設定した場合は自動ログイン情報を削除
				$this->gAccess->userAutoLogin($userId, $autoLogin);
				
				// 画面を全体を再表示する
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			} else {		// ログイン失敗の場合
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
//				$this->tmpl->addVar("_widget", "account", $account);		// アカウント
//				$this->tmpl->addVar("_widget", "message", 'ログインに失敗しました');		// メッセージ
				$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
				$this->tmpl->addVar("login_status", "message", 'ログインに失敗しました');
			}
		} else if ($act == 'loginbox_logout'){			// ログアウトのとき
			$this->gAccess->userLogout();
			
			// 画面を全体を再表示する
			$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
			return;
		}
		
		// ログイン状態を取得
		$userName = $this->gEnv->getCurrentUserName();
		if (empty($userName)){		// ユーザがログインしていないとき
			// ログイン入力部、ログインボタン表示
			$this->tmpl->setAttribute('login_field', 'visibility', 'visible');
			$this->tmpl->setAttribute('login_button', 'visibility', 'visible');
			
			// 会員登録機能
			if ($this->canFindWidget(self::TARGET_WIDGET)){			// ウィジェット実行可能なとき
				$this->tmpl->setAttribute('regmember_button', 'visibility', 'visible');		// 会員登録ボタン、パスワード再送信ボタンを表示
				
				// パスワード送信画面へのリンク
				$sendpwdUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'task=sendpwd');
				$this->tmpl->addVar("regmember_button", "sendpwd_url", $this->getUrl($sendpwdUrl, true));
		
				// ユーザ登録画面へのリンク
				//$regUserUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'task=reguser');
				$regUserUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET);
				$this->tmpl->addVar("regmember_button", "reguser_url", $this->getUrl($regUserUrl, true));
			}
			
			// 自動ログイン機能
			$useAutoLogin = $this->gSystem->getSystemConfig(self::CF_AUTO_LOGIN);
			if ($useAutoLogin) $this->tmpl->setAttribute('auto_login', 'visibility', 'visible');
		} else {		// ユーザがログイン中のとき
			$this->tmpl->addVar("login_status", "user_name", 'ログイン: ' . $userName . ' 様');
			
			// 会員情報、ログアウトボタン表示
			$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
			$this->tmpl->setAttribute('logout_button', 'visibility', 'visible');
			
			// 会員登録機能
			if ($this->canFindWidget(self::TARGET_WIDGET)){			// ウィジェット実行可能なとき
				$this->tmpl->setAttribute('member_button', 'visibility', 'visible');		// 会員の場合のみ表示
				
				// パスワード変更画面へのリンク
				$changepwdUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'task=changepwd');
				$this->tmpl->addVar("member_button", "changepwd_url", $this->getUrl($changepwdUrl, true));
		
				// プロフィール画面へのリンク
				$profileUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'task=profile');
				$this->tmpl->addVar("member_button", "profile_url", $this->getUrl($profileUrl, true));
			}
		}
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
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
