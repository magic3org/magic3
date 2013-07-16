<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_admin_opelogWidgetContainer.php 5788 2013-03-04 13:52:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_admin_opelogWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const DEFAULT_VIEW_COUNT = 10;			// 一度に表示可能なリスト項目数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		return 'admin.tmpl.html';
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
		
		// 入力値を取得
		$listCount = $request->trimValueOf('item_list_count');		// 取得数
		$viewCount = $request->trimValueOf('item_view_count');		// 表示数
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			// 入力チェック
			$this->checkNumeric($listCount, '取得数');
			$this->checkNumeric($viewCount, '表示数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->listCount = $listCount;		// 取得数
				$paramObj->viewCount = $viewCount;		// 表示数
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			$replaceNew = true;			// データ再取得
		}
		
		if ($replaceNew){		// データ再取得のとき
			$paramObj = $this->getWidgetParamObj();
			if (empty($paramObj)){		// 既存データなしのとき
				// デフォルト値設定
				$listCount = self::DEFAULT_LIST_COUNT;		// 取得数
				$viewCount = self::DEFAULT_VIEW_COUNT;		// 表示数
			} else {
				$listCount = $paramObj->listCount;		// 取得数
				if (!isset($listCount)) $listCount = self::DEFAULT_LIST_COUNT;		// 取得数
				$viewCount = $paramObj->viewCount;		// 表示数
				if (!isset($viewCount)) $viewCount = self::DEFAULT_VIEW_COUNT;		// 表示数
			}
		}
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "list_count", $listCount);		// 取得数
		$this->tmpl->addVar("_widget", "view_count", $viewCount);		// 表示数
	}
}
?>
