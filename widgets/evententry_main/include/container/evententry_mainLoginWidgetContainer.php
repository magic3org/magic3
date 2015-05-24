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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/evententry_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/evententry_mainDb.php');

class evententry_mainLoginWidgetContainer extends evententry_mainBaseWidgetContainer
{
	private $db;
	const WORD_KEY_ACCOUNT = 'word_account';		// 用語取得キー(アカウント)
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const TASK_MEMBER_REGIST = 'regist';			// 会員登録画面遷移用
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new evententry_mainDb();
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
			return 'login_bootstrap.tmpl.html';
		} else {
			return 'login.tmpl.html';
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
		$task = $request->trimValueOf('task');
		$act = $request->trimValueOf('act');
		$forward = $request->trimValueOf(M3_REQUEST_PARAM_FORWARD);		// 画面遷移用パラメータ
		$account = $request->trimValueOf('evententry_account');
		
		// 画面遷移用URLをチェック
		if (!empty($forward) && !$this->gEnv->isSystemUrlAccess($forward)) $forward = '';
		
		if ($act == 'evententry_login'){			// ログインのとき
			// アカウント、パスワード取得
			$password = $request->trimValueOf('password');
			
			// ユーザ認証
			if ($this->gAccess->userLoginByAccount($account, $password)){
				// 初回ログインのときは、仮会員を正会員にする
				$userId = $this->gEnv->getCurrentUserId();
				if ($userId != 0){
					$this->_db->makeNormalLoginUser($userId);// 一般ログインユーザに設定
				}
				if (empty($forward)){
					// 会員メニューへ
					$memberPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::DEFAULT_MEMBER_TASK;
					$this->gPage->redirect($memberPage);
				} else {
					$forwardPage = $this->gEnv->createCurrentPageUrl() . '&' . $forward;
					$this->gPage->redirect($forwardPage);
				}
				return;
			} else {
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
				$this->tmpl->addVar("_widget", "message", 'ログインに失敗しました');
			}
		} else {		// 初期画面
			// Eメールのリンクでのログインの場合
			if ($task == 'emaillogin'){
				// メールからのアクセスの場合は、ログイン後最初にパスワードを変更する
				//$account = $request->trimValueOf('account');
				$pwd = $request->trimValueOf('pwd');
				$forward = 'task=changepwd';		// パスワードを変更
			
				$this->tmpl->addVar("_widget", "account",	$this->convertToDispString($account));
				$this->tmpl->addVar("_widget", "password",	$this->convertToDispString($pwd));
				$this->tmpl->addVar("_widget", "savepwd",	$this->convertToDispString($pwd));
			}
		}
		// 画面修正
		if ($task == 'emaillogin'){			// Eメールからのログインの場合
			$this->tmpl->setAttribute('regmember_area', 'visibility', 'hidden');// 会員登録への遷移を削除
		} else {		// 通常の画面(会員登録画面へのリンクとログインエリアを表示)
			// コンテンツタイプが「会員」のページを取得
			$linkUrl  = $this->gPage->createContentPageUrl(
															M3_VIEW_TYPE_MEMBER, 
															M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_MEMBER_REGIST . '&' .
															M3_REQUEST_PARAM_FORWARD . '=' . urlencode($forward)
															);
			$linkUrl = $this->getUrl($linkUrl, true/*リンク用*/);

			// パラメータを画面に埋め込む
			$this->tmpl->addVar("regmember_area", "url_regmember", $this->convertUrlToHtmlEntity($linkUrl));		// 会員登録画面遷移用
			$this->tmpl->addVar("regmember_area", "word_account", $this->convertToDispString($this->gInstance->getMessageManager()->getWord(self::WORD_KEY_ACCOUNT)));		// 用語(アカウント)
		}
						
		// パラメータを画面に埋め込む
		$this->tmpl->addVar("_widget", "forward",		$this->convertToDispString($forward));		// 遷移先を維持
		$this->tmpl->addVar("_widget", "task",			$this->convertToDispString($task));
		$this->tmpl->addVar("_widget", "word_account",	$this->convertToDispString($this->gInstance->getMessageManager()->getWord(self::WORD_KEY_ACCOUNT)));		// 用語(アカウント)
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
}
?>
