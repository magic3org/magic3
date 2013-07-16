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
 * @version    SVN: $Id: s_bbs_2chWidgetContainer.php 4851 2012-04-15 00:43:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/s_bbs_2chBaseWidgetContainer.php');

class s_bbs_2chWidgetContainer extends s_bbs_2chBaseWidgetContainer
{
	const DEFAULT_TASK = 'top';				// デフォルトの画面
	
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
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)){
			// スレッドIDを取得
			$threadId = $request->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID);
			if (empty($threadId)) $request->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID_SHORT);
			
			// 検索キーワードを取得
			$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);
			
			// スレッドIDが設定されているときはスレッド表示タスクを設定
			if (empty($threadId)){
				if (empty($keyword)){
					$task = self::DEFAULT_TASK;
				} else {		// 検索キーワードが設定されている場合
					$task = self::TASK_SUBJECT;		// 件名一覧
				}
			} else {
				$task = self::TASK_READ_THREAD;		// スレッド表示
			}
		} else if ($task == self::TASK_NEW_THREAD){		// スレッド新規作成
			$task = self::TASK_THREAD;
		}

		// ##### コンテナを起動 #####
		switch ($task){
			case self::TASK_TOP:			// トップ画面
			case self::TASK_SUBJECT:		// スレッド件名
			case self::TASK_THREAD:		// スレッド処理
			case self::TASK_READ_THREAD:		// スレッド表示
				$this->gLaunch->goSubWidget($task);
				return false;
			default:
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
			case self::TASK_TOP:			// トップ画面
			default:
				// メッセージを表示
				//$this->tmpl->addVar("_widget", "login_url", $this->gEnv->createCurrentPageUrl() . '&task=login');		// ログイン用URL
				break;
		}
	}
}
?>
