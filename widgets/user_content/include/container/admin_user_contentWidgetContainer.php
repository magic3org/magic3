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
 * @version    SVN: $Id: admin_user_contentWidgetContainer.php 3015 2010-04-08 12:54:00Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentWidgetContainer extends admin_user_contentBaseWidgetContainer
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
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// コンテナを起動
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case 'content':		// コンテンツ管理
			case 'content_detail':		// コンテンツ管理(詳細)
				$task = 'content';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'room':		// ルーム管理
			case 'room_detail':		// ルーム管理(詳細)
				$task = 'room';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'tab':		// タブ定義
			case 'tab_detail':		// タブ定義(詳細)
				$task = 'tab';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'item':		// コンテンツ項目
			case 'item_detail':		// コンテンツ項目(詳細)
				$task = 'item';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'category':		// カテゴリ定義
			case 'category_detail':		// カテゴリ定義(詳細)
				$task = 'category';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'other':		// その他設定
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			default:
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
