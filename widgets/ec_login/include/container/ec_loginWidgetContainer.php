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
 * @version    SVN: $Id: ec_loginWidgetContainer.php 5425 2012-12-05 01:57:48Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_loginDb.php');

class ec_loginWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_CART_WIDGET = 'ec_main';		// カート内容表示用呼び出しウィジェットID
	const MAIL_OBJ_ID = 'ecmail';			// メール連携オブジェクト
	const EC_LIB_ID = "eclib";		// EC共通ライブラリオブジェクトID
	const DEFAULT_TITLE = '会員ログイン';
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const CF_AUTO_REGIST_MEMBER			= 'auto_regist_member';		// 自動会員登録
	const SHOP_WIDGET_TYPE = 'product';			// ショップ機能ウィジェットのウィジェットタイプ
	const WORD_KEY_ACCOUNT = 'word_account';		// 用語取得キー(アカウント)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new ec_loginDb();
		
		// メール連携オブジェクト取得
		$this->ecMailObj = $this->gInstance->getObject(self::MAIL_OBJ_ID);
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
		if ($act == 'eclogin_login'){			// 会員ログインのとき
			// アカウント、パスワード取得
			$account = $request->trimValueOf('account');
			$password = $request->trimValueOf('password');
		
			// ユーザ認証
			if ($this->gAccess->userLoginByAccount($account, $password)){
				// 初回ログインのときは、仮会員を正会員にする
				$userId = $this->gEnv->getCurrentUserId();
				if ($userId != 0){
					$ret = $this->gInstance->getObject(self::EC_LIB_ID)->makeTmpMemberToProperMember($userId);
					if ($ret){
						$ret = $this->_db->makeNormalLoginUser($userId);// 一般ログインユーザに設定
						
						// ######## 会員登録のメールをイントラネット側に送信 ########
						//$this->ecMailObj->sendMemberInfoToBackoffice(0/*新規登録*/, $userId);
					}
				}
				// 画面を全体を再表示する
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			} else {
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
				$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
				$this->tmpl->addVar("login_status", "message", 'ログインに失敗しました');
			}
		} else if ($act == 'eclogin_logout'){			// 会員ログアウトのとき
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
			
			// 自動会員登録機能を使用するかどうか
			$value = $this->db->getConfig(self::CF_AUTO_REGIST_MEMBER);
			if (!empty($value)) $this->tmpl->setAttribute('regmember_button', 'visibility', 'visible');		// 自動会員登録機能を使用する場合のみ会員登録ボタンを表示
			
			// 値埋め込み
			$this->tmpl->addVar("login_field", "word_account", $this->convertToDispString($this->gInstance->getMessageManager()->getWord(self::WORD_KEY_ACCOUNT)));		// 用語(アカウント)
		} else {		// ユーザがログイン中のとき
			// 会員情報を取得
			$ret = $this->db->getMember($this->gEnv->getCurrentUserId(), $memberRow);
			
			$this->tmpl->addVar("login_status", "user_name", 'ログイン: ' . $userName . ' 様');
			
			// 会員情報、ログアウトボタン表示
			$this->tmpl->setAttribute('login_status', 'visibility', 'visible');		// ログイン状況
			$this->tmpl->setAttribute('logout_button', 'visibility', 'visible');
			if ($ret) $this->tmpl->setAttribute('member_button', 'visibility', 'visible');		// 会員の場合のみ表示
		}
		$shopWidget = $this->gPage->getActiveMainWidgetIdByWidgetType(self::SHOP_WIDGET_TYPE);		// ショップ機能ウィジェット取得
		if (empty($shopWidget)) $shopWidget = self::DEFAULT_CART_WIDGET;
		
		// パスワード送信画面へのリンク
		$sendpwdUrl = $this->createCmdUrlToWidget($shopWidget, 'task=sendpwd');
		$this->tmpl->addVar("regmember_button", "sendpwd_url", $this->getUrl($sendpwdUrl, true));
		
		// 会員登録画面へのリンク
		$regMemberUrl = $this->createCmdUrlToWidget($shopWidget, 'task=regmember');
		$this->tmpl->addVar("regmember_button", "regmember_url", $this->getUrl($regMemberUrl, true));
		
		// パスワード変更画面へのリンク
		$changepwdUrl = $this->createCmdUrlToWidget($shopWidget, 'task=changepwd');
		$this->tmpl->addVar("member_button", "changepwd_url", $this->getUrl($changepwdUrl, true));
		
		// 会員情報画面へのリンク
		$memberUrl = $this->createCmdUrlToWidget($shopWidget, 'task=memberinfo');
		$this->tmpl->addVar("member_button", "member_url", $this->getUrl($memberUrl, true));
		
		// 購入履歴画面へのリンク
		$historyUrl = $this->createCmdUrlToWidget($shopWidget, 'task=purchasehistory');
		$this->tmpl->addVar("member_button", "purchasehistory_url", $this->getUrl($historyUrl, true));
		
		// 会員お知らせ画面へのリンク
		$memberNoticeUrl = $this->createCmdUrlToWidget($shopWidget, 'task=membernotice');
		$this->tmpl->addVar("member_button", "member_notice_url", $this->getUrl($memberNoticeUrl, true));
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
