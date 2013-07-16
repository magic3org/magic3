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
 * @version    SVN: $Id: s_loginWidgetContainer.php 4821 2012-04-03 05:04:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');

class s_loginWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const TARGET_WIDGET = 'reg_user';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ログイン';			// デフォルトのウィジェットタイトル
	
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

			// ユーザ認証
			if ($this->gAccess->userLoginByAccount($account, $password)){
				// 画面を全体を再表示する
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			} else {		// ログイン失敗の場合
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
				$this->tmpl->addVar("_widget", "account", $account);		// アカウント
				$this->tmpl->addVar("login_status", "message", 'ログインに失敗しました');		// メッセージ
				$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// メッセージ表示
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
		} else {		// ユーザがログイン中のとき
			$this->tmpl->addVar("login_status", "user_name", 'ログイン: ' . $userName . ' 様');
			
			// 会員情報、ログアウトボタン表示
			$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
			$this->tmpl->setAttribute('logout_button', 'visibility', 'visible');
			
			// パスワード変更画面へのリンク
			if ($this->canFindWidget(self::TARGET_WIDGET)){			// ウィジェット実行可能なとき
				$changepwdUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'task=changepwd');
				$this->tmpl->setAttribute('user_items', 'visibility', 'visible');
				$this->tmpl->addVar("user_items", "changepwd_url", $this->getUrl($changepwdUrl, true));
			}
		}
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
