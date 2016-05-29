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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainWidgetContainer extends admin_blog_mainBaseWidgetContainer
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
		if (empty($task)) $task = 'entry';

		// コンテナを起動
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case self::TASK_CONFIG:		// その他設定
			case self::TASK_IMAGE:		// ブログ記事画像
			case self::TASK_HISTORY:	// ブログ記事履歴
			case self::TASK_SCHEDULE:	// ブログ記事予約
			case self::TASK_ANALYTICS:	// アクセス解析
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'entry':		// ブログ記事管理
			case 'entry_detail':		// ブログ記事管理(詳細)
				$task = 'entry';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'comment':		// ブログ記事コメント管理
			case 'comment_detail':		// ブログ記事コメント管理(詳細)
				$task = 'comment';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'user':		// ユーザ管理
			case 'user_detail':		// ユーザ管理(詳細)
				$task = 'user';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'category':		// カテゴリ管理
			case 'category_detail':		// カテゴリ管理(詳細)
				$task = 'category';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'blogid':		// マルチブログ管理
			case 'blogid_detail':		// マルチブログ管理(詳細)
				$task = 'blogid';
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
