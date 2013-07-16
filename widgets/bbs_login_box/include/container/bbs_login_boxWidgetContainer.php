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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: bbs_login_boxWidgetContainer.php 2623 2009-12-05 12:40:38Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/bbs_login_boxDb.php');

class bbs_login_boxWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	const TARGET_WIDGET = 'bbs_main';		// 呼び出しウィジェットID
	const THIS_WIDGET_ID = 'bbs_login_box';		// ウィジェットID
	const DEFAULT_TITLE = 'BBS会員ログイン';			// デフォルトのウィジェットタイトル
			
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new bbs_login_boxDb();
		$this->sysDb = $this->gInstance->getSytemDbObject();
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
		if ($act == 'bbsloginbox_login'){			// 会員ログインのとき
			// アカウント、パスワード取得
			$account = $request->trimValueOf('bbs_account');
			$password = $request->trimValueOf('password');
		
			// ユーザ認証
			if ($this->gAccess->userLoginByAccount($account, $password)){
				// 初回ログインのときは、仮会員を正会員にする
				$userId = $this->gEnv->getCurrentUserId();
				if ($userId != 0){
					$ret = $this->db->makeTmpMemberToProperMember($userId);
					if ($ret) $this->sysDb->makeNormalLoginUser($userId);// 一般ログインユーザに設定
				}
				// 画面を全体を再表示する
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			} else {		// ログイン失敗の場合
				// ログイン状態を削除
				$this->gAccess->userLogout();
				
				//$this->tmpl->addVar("_widget", "account", $account);		// アカウント
				$this->tmpl->addVar("_widget", "message", 'ログインに失敗しました');		// メッセージ
			}
		} else if ($act == 'bbsloginbox_logout'){			// 会員ログアウトのとき
			$this->gAccess->userLogout();
			
			// 画面を全体を再表示する
			$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
			return;
		} else if ($act == 'bbsloginbox_regmember'){			// 会員登録のとき
			// BBSメインに会員登録を表示させる
			$url = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, self::THIS_WIDGET_ID, 'task=regist');
			$this->gPage->redirect($url);
			return;
		}
		
		// ログイン状態を取得
		$userName = $this->gEnv->getCurrentUserName();
		if (empty($userName)){
			$this->tmpl->addVar("_widget", "user_name", '');
		} else {
			$this->tmpl->addVar("_widget", "user_name", 'ログインユーザ: ' . $userName . ' 様');
		}
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "script_url", $this->gEnv->getScriptsUrl());
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
