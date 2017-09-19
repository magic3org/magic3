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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainWidgetContainer extends ec_mainBaseWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * ディスパッチ処理(メインコンテナのみ実行)
	 *
     * HTTPリクエストの内容を見て処理をコンテナに振り分ける
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return bool 						このクラスの_setTemplate(), _assign()へ処理を継続するかどうかを返す。
	 *                                      true=処理を継続、false=処理を終了
	 */
	function _dispatch($request, &$param)
	{
		// 実行処理を決定
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TOP_TASK;
		$forward = $request->trimValueOf(M3_REQUEST_PARAM_FORWARD);		// 画面遷移用パラメータ
		
		// ##### アクセス制御 #####
		$canNonMemberOrder = $this->_getConfig(ec_mainCommonDef::CF_E_PERMIT_NON_MEMBER_ORDER);

		// ログインが必要な処理の場合は、ログイン状況をチェックする
		switch ($task){
			case 'membermenu':		// 会員向けメニュー
			case 'membernotice':		// 会員向けお知らせ
			case 'memberinfo':			// 会員情報変更
			case 'changepwd':		// パスワード変更
			case 'purchasehistory':	// 購入履歴
				// ログイン状態を取得
				if (!$this->gEnv->isCurrentUserLogined()){		// ログインされていない場合
					// トップページへ遷移
					$this->gPage->redirect($this->gEnv->createCurrentPageUrl());
					return true;
				}
		}

		// 注文プロセスもログインが必要
		if (in_array($task, self::$_orderProcessAllTasks)){
			if (empty($canNonMemberOrder)){			// 会員のみ注文が可能なとき
				// ログイン状態を取得
				if (!$this->gEnv->isCurrentUserLogined()){		// ログインされていない場合
					// トップページへ遷移
					$this->gPage->redirect($this->gEnv->createCurrentPageUrl());
					return true;
				}
			}
			// 注文受付停止中はトップ画面へ遷移(システム管理者以外)
			if (!$this->gEnv->isSystemAdmin() && !$this->_getConfig(ec_mainCommonDef::CF_E_ACCEPT_ORDER)){
				$this->gPage->redirect($this->gEnv->createCurrentPageUrl());
				return true;
			}
		}
		
		// ##### 遷移先を決定 #####
		if ($task == 'login'){
			if ($this->gEnv->isCurrentUserLogined()){
				if (empty($forward)){
					// 既にログイン中のときは、会員向けメニューへ
					$orderPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::DEFAULT_MEMBER_TASK;
					$this->gPage->redirect($orderPage);
				} else {
					$forwardPage = $this->gEnv->createCurrentPageUrl() . '&' . $forward;
					$this->gPage->redirect($forwardPage);
				}
				return true;
			}
		} else if ($task == 'order'){		// 注文処理
			if ($this->gEnv->isCurrentUserLogined()){		// ユーザがログインしている場合(会員の場合)
				// 注文の画面遷移(配送先入力→配送方法選択→支払い方法入力→確認)
				//$orderPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::DEFAULT_ORDER_START_TASK;
				$orderPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::$_orderProcessTasks[0];
				$this->gPage->redirect($orderPage);
				return true;
			} else {		// ログイン画面を表示した後、注文画面へ遷移
				//$loginPage = $this->gEnv->createCurrentPageUrl() . '&task=login&' . M3_REQUEST_PARAM_FORWARD . '=' . urlencode('task=' . self::DEFAULT_ORDER_START_TASK);
				$loginPage = $this->gEnv->createCurrentPageUrl() . '&task=login&' . M3_REQUEST_PARAM_FORWARD . '=' . urlencode('task=' . self::$_orderProcessTasks[0]);
				$this->gPage->redirect($loginPage);
				return true;
			}
		} else if ($task == 'logout'){// ログアウト処理
			$this->gAccess->userLogout();// ログイン状態を削除
			
			// トップページへ遷移
			$this->gPage->redirect($this->gEnv->createCurrentPageUrl());
			return true;
		} else if (in_array($task, self::$_orderProcessTasks) && $task != self::$_orderProcessTasks[0]){			// 注文処理の最初のタスク以外の場合
			// システム運用権限ありで、プレビュー指定の場合は遷移しない
			if (!($this->gEnv->isSystemManageUser() && $cmd == M3_REQUEST_CMD_PREVIEW)){
				// 注文書が初期化されていなければカート画面へ遷移
				$ret = $this->_getOrderSheet($row);
				if (!$ret){
					$orderPage = $this->gEnv->createCurrentPageUrl() . '&task=cart';
					$this->gPage->redirect($orderPage);
					return true;
				}
			}
		}
		
		// ##### 起動タスクを変更 #####
		switch ($task){
			case 'emaillogin':
				$task = 'login';
				break;
		}
		// 会員規約を承認していない場合は、規約ページを表示
		if ($task == 'regmember' && $this->getWidgetSession(ec_mainCommonDef::SK_AGREE_MEMBER, '0') == '0'){
			$task = 'agreemember';		// 会員規約
		}
		
		// ##### タスク初期処理 #####
		if ($task == self::$_orderProcessTasks[0]){			// 注文処理の最初のタスクの場合
			// 注文書を初期化。戻るボタンで来た時は注文書を初期化しない。
			$act = $request->trimValueOf('act');
			if ($act != 'goback') $this->_initOrderSheet();
		}
		
		// ##### コンテナを起動 #####
		switch ($task){
			case 'cart':			// カート機能
			case 'login':			// 会員ログイン
			case 'regmember':			// 会員登録
			case 'regcustomer':			// 購入者情報入力
			case 'delivery':		// 配送先入力
			case 'delivmethod':		// 配送方法選択
			case 'payment':			// 支払い
			case 'confirm':			// 確認
			case 'complete':		// 手続き完了
			case 'sendpwd':			// パスワード送信
			case 'membermenu':		// 会員向けメニュー
			case 'membernotice':		// 会員向けお知らせ
			case 'memberinfo':			// 会員情報変更
			case 'agreemember':		// 会員規約表示
			case 'changepwd':			// パスワード変更
			case 'purchasehistory':			// 購入履歴
				self::$_task = $task;		// 現在のタスク
				$this->gLaunch->goSubWidget($task);
				return false;
			default:
				//$this->SetMsg(self::MSG_APP_ERR, "画面が見つかりません");
				return true;
		}
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
		return 'message.tmpl.html';
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
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		// 要求画面によってテンプレートを変更
		switch ($task){
			case 'error':			// エラー画面
				$title = 'エラー';
				$message = 'エラーが発生しました。もう一度最初からやり直してください。';
				break;
			default:
				$title = 'アクセスエラー';
				$message = 'アクセスできません';
				break;
		}
		$this->tmpl->addVar("_widget", "title", $title);
		$this->tmpl->addVar("_widget", "message", $message);
	}
}
?>
