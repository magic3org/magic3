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
 * @version    SVN: $Id: admin_ec_mainWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');

class admin_ec_mainWidgetContainer extends admin_ec_mainBaseWidgetContainer
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
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// コンテナを起動
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case 'other':		// その他設定
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'order':		// 受注管理
			case 'order_detail':		// 受注管理(詳細)
				$task = 'order';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'member':		// 会員管理
			case 'member_detail':		// 会員管理(詳細)
				$task = 'member';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'product':		// 商品管理
			case 'product_detail':		// 商品管理(詳細)
				$task = 'product';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'photoproduct':		// フォト商品管理
			case 'photoproduct_detail':		// フォト商品管理(詳細)
				$task = 'photoproduct';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'productcategory':				// 商品カテゴリー
			case 'productcategory_detail':		// 商品カテゴリー(詳細)
				$task = 'productcategory';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'delivmethod':		// 配送方法
			case 'delivmethod_detail':		// 配送方法(詳細)
				$task = 'delivmethod';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'paymethod':		// 支払方法
			case 'paymethod_detail':		// 支払方法(詳細)
				$task = 'paymethod';
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case 'calcorder':				// 注文計算
			case 'calcorder_detail':		// 注文計算(詳細)
				$task = 'calcorder';
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
		return 'admin_message.tmpl.html';
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
