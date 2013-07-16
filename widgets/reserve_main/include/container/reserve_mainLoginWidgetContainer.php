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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: reserve_mainLoginWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');

class reserve_mainLoginWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	
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
		global $gEnvManager;
		global $gAccessManager;
		global $gPageManager;
		global $gInstanceManager;
		
		$act = $request->trimValueOf('act');
		if ($act == 'reserve_login'){			// 会員ログインのとき
			// アカウント、パスワード取得
			$account = $request->trimValueOf('reserve_account');
			$password = $request->trimValueOf('password');
		
			// ユーザ認証
			if ($gAccessManager->userLoginByAccount($account, $password)){		// ログイン成功
				// 画面を全体を再表示する
				$gPageManager->redirect($gEnvManager->createCurrentPageUrl());
				return;
			} else {
				// ログイン状態を削除
				$gAccessManager->userLogout();
				
				$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
				$this->tmpl->addVar("login_status", "message", 'ログインに失敗しました');
			}
		} else if ($act == 'reserve_logout'){			// 会員ログアウトのとき
			$gAccessManager->userLogout();
			
			// 画面を全体を再表示する
			$gPageManager->redirect($gEnvManager->createCurrentPageUrl());
			return;
		}
		// ログイン状態を取得
		$userName = $gEnvManager->getCurrentUserName();
		if (empty($userName)){		// ユーザがログインしていないとき
			// ログイン入力部、ログインボタン表示
			$this->tmpl->setAttribute('login_field', 'visibility', 'visible');
			$this->tmpl->setAttribute('login_button', 'visibility', 'visible');
			$this->tmpl->setAttribute('regmember_button', 'visibility', 'visible');		// 会員登録ボタン
		} else {		// ユーザがログイン中のとき
			$this->tmpl->addVar("login_status", "user_name", 'ログイン ' . $userName . ' 様');
			
			// 会員情報、ログアウトボタン表示
			$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
			$this->tmpl->setAttribute('logout_button', 'visibility', 'visible');
			$this->tmpl->setAttribute('member_button', 'visibility', 'visible');
		}
		
		// タイトル部のパラメータ
		$this->tmpl->addVar("_widget", "default_menu_param", $this->gDesign->getDefaultWidgetTableParam());
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "script_url", $gEnvManager->getScriptsUrl());
		
		$this->tmpl->addVar("regmember_button", "sendpwd_url", $gEnvManager->createCurrentPageUrl() . '&task=sendpwd');		// パスワー送信用URL
	}
}
?>
