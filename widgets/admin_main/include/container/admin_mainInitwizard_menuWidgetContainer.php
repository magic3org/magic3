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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainInitwizardBaseWidgetContainer.php');

class admin_mainInitwizard_menuWidgetContainer extends admin_mainInitwizardBaseWidgetContainer
{
//	const SEL_MENU_ID = 'admin_menu';		// メニュー変換対象メニューバーID
//	const TREE_MENU_TASK	= 'menudef';	// メニュー管理画面(多階層)
//	const SINGLE_MENU_TASK	= 'smenudef';	// メニュー管理画面(単階層)
	
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
		return 'initwizard_menu.tmpl.html';
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
		// デフォルト値取得
		$this->langId		= $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$isHier = $request->trimCheckedValueOf('menu_hier');		// 階層化ありかどうか
	
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
/*			// メニュー項目IDを取得
			$ret = $this->getMenuInfo($isCurrentHier, $itemId, $row);

			// メニュー管理画面を更新
			if ($isHier){		// 階層化あり
				$ret = $this->_mainDb->updateNavItemMenuType($itemId, self::TREE_MENU_TASK);
			} else {
				$ret = $this->_mainDb->updateNavItemMenuType($itemId, self::SINGLE_MENU_TASK);
			}*/
			// メニュー管理画面を変更
			$ret = $this->gSystem->changeSiteMenuHier($isHier);
			
			if ($ret){
				// 次の画面へ遷移
				$this->_redirectNextTask();
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');			// データ更新に失敗しました
			}
		} else {
			$reloadData = true;
		}
		
		if ($reloadData){		// データ再取得のとき
			// メニュー情報を取得
			//$ret = $this->getMenuInfo($isHier, $itemId, $row);
			// メニューを階層化するかどうかを取得
			$isHier = $this->gSystem->isSiteMenuHier();
		}
		$this->tmpl->addVar("_widget", "menu_hier_checked",			$this->convertToCheckedString($isHier));		// 階層化ありかどうか
	}
	/**
	 * メニュー管理画面の情報を取得
	 *
	 * @param bool  $isHier		階層化メニューかどうか
	 * @param int   $itemId		メニュー項目ID
	 * @param array  $row		取得レコード
	 * @return bool				取得できたかどうか
	 */
/*	function getMenuInfo(&$isHier, &$itemId, &$row)
	{
		$isHier = false;	// 多階層メニューかどうか
		$ret = $this->_mainDb->getNavItemsByTask(self::SEL_MENU_ID, self::TREE_MENU_TASK, $row);
		if ($ret){
			$isHier = true;
		} else {
			$ret = $this->_mainDb->getNavItemsByTask(self::SEL_MENU_ID, self::SINGLE_MENU_TASK, $row);
		}
		if ($ret) $itemId = $row['ni_id'];
		return $ret;
	}*/
}
?>
