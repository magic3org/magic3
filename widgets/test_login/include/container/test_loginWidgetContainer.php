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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class test_loginWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $blogItemExists;	// マルチブログリストがあるかどうか
	const TARGET_WIDGET = 'reg_user';		// 呼び出しウィジェットID
	const TARGET_WIDGET_BLOG = 'blog_main';
	const DEFAULT_TITLE = 'ログイン';			// デフォルトのウィジェットタイトル
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const CF_AUTO_LOGIN = 'auto_login';		// 自動ログイン機能を使用するかどうか
	const BLOG_OBJ_ID = 'bloglib';		// ブログオブジェクトID
	const CF_USE_MULTI_BLOG			= 'use_multi_blog';		// マルチブログ機能を使用するかどうか
	const TASK_MEMBER_REGIST = 'regist';			// 会員登録画面遷移用
	const TASK_MEMBER_SEND_PASSWORD	= 'sendpwd';		// パスワード送信
	const TASK_MEMBER_PROFILE			= 'profile';		// プロフィール画面(要ログイン)
	const TASK_MEMBER_CHANGE_PASSWORD	= 'changepwd';		// パスワード変更(要ログイン)
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// フォームチェック機能を使用
//		$this->setFormCheck();
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
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
//		if ($act == 'loginbox_login' && $this->checkFormId()){			// ログインのとき
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
//				$this->gAccess->userAutoLogin($userId, $autoLogin);
				
				// 画面を全体を再表示する
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			} else {		// ログイン失敗の場合
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
//				$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
//				$this->tmpl->addVar("login_status", "message", 'ログインに失敗しました');
				$this->setUserErrorMsg('ログインに失敗しました');
			}
//		} else if ($act == 'loginbox_logout' && $this->checkFormId()){			// ログアウトのとき
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
			

			
//			// 自動ログイン機能
//			$useAutoLogin = $this->gSystem->getSystemConfig(self::CF_AUTO_LOGIN);
//			if ($useAutoLogin) $this->tmpl->setAttribute('auto_login', 'visibility', 'visible');
		} else {		// ユーザがログイン中のとき
			$this->tmpl->addVar("login_status", "user_name", 'ログイン: ' . $this->convertToDispString($userName) . ' 様');
			
			// 会員情報、ログアウトボタン表示
			$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
			$this->tmpl->setAttribute('logout_button', 'visibility', 'visible');
		}
	}
}
?>
