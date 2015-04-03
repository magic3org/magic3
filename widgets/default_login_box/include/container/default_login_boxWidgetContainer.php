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
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/default_login_boxDb.php');

class default_login_boxWidgetContainer extends BaseWidgetContainer
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
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new default_login_boxDb();
		
		// フォームチェック機能を使用
		$this->setFormCheck();
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
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){
			return 'index_bootstrap.tmpl.html';
		} else {
			return 'index.tmpl.html';
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
		$act = $request->trimValueOf('act');
		if ($act == 'loginbox_login' && $this->checkFormId()){			// ログインのとき
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
		} else if ($act == 'loginbox_logout' && $this->checkFormId()){			// ログアウトのとき
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
			$this->tmpl->addVar("login_status", "user_name", 'ログイン: ' . $this->convertToDispString($userName) . ' 様');
			
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
			
			// マルチブログを使用している場合はブログリストを表示
			if ($this->canFindWidget(self::TARGET_WIDGET_BLOG)){			// ウィジェット実行可能なとき
				$blogLibObj = $this->gInstance->getObject(self::BLOG_OBJ_ID);
				if (isset($blogLibObj)){
					$value = $blogLibObj->getConfig(self::CF_USE_MULTI_BLOG);
					if ($value){
						// ブログリストを作成
						$this->db->getAvailableBlogId(array($this, 'blogListLoop'));
						if ($this->blogItemExists) $this->tmpl->setAttribute('blog_info', 'visibility', 'visible');
					}
				}
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
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){
			return '';
		} else {
			return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
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
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function blogListLoop($index, $fetchedRow, $param)
	{
		// リンク先の作成
		$name = $fetchedRow['bl_name'];
		$linkUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $fetchedRow['bl_id'];
		$row = array(
			'url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
			'name' => $this->convertToDispString($name)			// タイトル
		);
		$this->tmpl->addVars('blog_list', $row);
		$this->tmpl->parseTemplate('blog_list', 'a');
		
		$this->blogItemExists = true;
		return true;
	}
}
?>
