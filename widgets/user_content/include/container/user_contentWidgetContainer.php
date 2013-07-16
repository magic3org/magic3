<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: user_contentWidgetContainer.php 3011 2010-04-08 04:06:37Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/user_contentBaseWidgetContainer.php');

class user_contentWidgetContainer extends user_contentBaseWidgetContainer
{
	const DEFAULT_TASK = 'top';				// デフォルトの画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ウィジェットオブジェクト取得
		//self::$_paramObj = $this->getWidgetParamObj();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = 0;
		
		// パラメータオブジェクトを取得
		self::$_paramObj = $this->getWidgetParamObjByConfigId($configId);
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
		$roomId = $request->trimValueOf(M3_REQUEST_PARAM_ROOM_ID);
		if (empty($roomId)) $roomId = $request->trimValueOf(M3_REQUEST_PARAM_ROOM_ID_SHORT);		// 略式ルームID

		// ##### アクセス制御 #####
		self::$_canEditContent = false;		// コンテンツ編集権限
		
		// 設定値を取得
		if ($this->gEnv->isSystemManageUser()){			// システム運用可能ユーザのとき
			self::$_canEditContent = true;		// コンテンツ編集権限
		} else {
			if (!empty(self::$_paramObj)){
				if (!empty(self::$_paramObj->editBySameUserId) &&					// ルームIDと同じユーザIDのユーザに編集許可を与える
					!empty($roomId) && $roomId == $this->gEnv->getCurrentUserAccount()){		// ルームIDとユーザアカウントが同じ
					self::$_canEditContent = true;		// コンテンツ編集権限
				}
			}
		}

		// ##### コンテナを起動 #####
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case self::TASK_TOP:			// トップ画面
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;				
			case self::TASK_CONTENT:				// コンテンツ編集画面(別ウィンドウ)
			case self::TASK_CONTENT_DETAIL:			// コンテンツ編集画面詳細
				if (empty(self::$_canEditContent)){	// コンテンツが編集可能かどうか
					$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
					return true;				
				} else {
					$task = self::TASK_CONTENT;
					$goWidget = true;		// サブウィジェットを実行するかどうか
				}
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
			case self::TASK_TOP:			// トップ画面
			default:
				// メッセージを表示
				//$this->tmpl->addVar("_widget", "login_url", $this->gEnv->createCurrentPageUrl() . '&task=login');		// ログイン用URL
				break;
		}
	}
}
?>
