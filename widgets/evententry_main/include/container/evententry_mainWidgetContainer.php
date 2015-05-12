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

class evententry_mainWidgetContainer extends evententry_mainBaseWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ウィジェットオブジェクト取得
		self::$_paramObj = $this->getWidgetParamObj();
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
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;

		// ##### アクセス制御 #####
		// ログインが必要な処理の場合は、ログイン状況をチェックする
		switch ($task){
			case self::TASK_REGIST:		// 参加登録画面
				// ログイン状態を取得
				if (!$this->gEnv->isCurrentUserLogined()){		// ログインされていない場合
					$loginPage = $this->gEnv->createCurrentPageUrl() . '&task=' . self::TASK_LOGIN . '&' . M3_REQUEST_PARAM_FORWARD . '=' . urlencode($this->gEnv->getCurrentRequestUri());
					$this->gPage->redirect($loginPage);
					return true;
//					// トップページへ遷移
//					$this->gPage->redirect($this->gEnv->createCurrentPageUrl());
//					return true;
				}
		}
		
		// ##### コンテナを起動 #####
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case self::TASK_REGIST:		// 参加登録画面
			case self::TASK_LOGIN:		// ログイン画面
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
		}
		if ($goWidget){		// サブウィジェットを実行するかどうか
			$this->gLaunch->goSubWidget($task);		// 一般機能で呼び出し
			return false;
		} else {
			$this->SetMsg(self::MSG_APP_ERR, "画面が見つかりません");
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
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
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
			case self::TASK_REGIST:			// 参加登録画面
			default:
				break;
		}
	}
}
?>
