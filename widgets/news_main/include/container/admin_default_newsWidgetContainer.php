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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('default_news') . '/admin_default_newsBaseWidgetContainer.php');

class admin_default_newsWidgetContainer extends admin_default_newsBaseWidgetContainer
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
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)){
			if ($this->getLaunchFromPagedef($request)){		// 画面編集からの起動の場合
				$task = self::TASK_CONFIG;
			} else {
				$task = self::TASK_NEWS;
			}
		}
		self::$_task = $task;			// 現在の画面
		
		// コンテナを起動
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case self::TASK_CONFIG:		// 基本設定
				$task = self::TASK_CONFIG;
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case self::TASK_NEWS:				// 新着情報一覧
			case self::TASK_NEWS_DETAIL:		// 新着情報詳細
				$task = self::TASK_NEWS;
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
		}
		if ($goWidget){		// サブウィジェットを実行するかどうか
			$this->gLaunch->goSubWidget($task, true);		// 管理者機能で呼び出し
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
	}
}
?>
