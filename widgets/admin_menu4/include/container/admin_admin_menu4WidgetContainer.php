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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_menuDb.php');

class admin_admin_menu4WidgetContainer extends BaseAdminWidgetContainer
{
	protected $db;	// DB接続オブジェクト
//	const SEL_MENU_ID = 'admin_menu';		// メニュー変換対象メニューバーID
//	const TREE_MENU_TASK	= 'menudef';	// メニュー管理画面(多階層)
//	const SINGLE_MENU_TASK	= 'smenudef';	// メニュー管理画面(単階層)
	const CF_SITE_OPERATION_MODE = 'site_operation_mode';			// サイト運用モード
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_menuDB();
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
	 * テンプレートの後処理
	 *
	 * テンプレートのデータ埋め込み(_assign())の後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		$isHier = $request->trimValueOf('menu_type');
		
		if ($act == 'update'){		// 設定更新のとき
			// メニュー管理画面を変更
			$ret = $this->gSystem->changeSiteMenuHier($isHier);
			
			/*
			// メニュー情報を取得
			$ret = $this->getMenuInfo($dummy, $itemId, $row);
			if ($ret){
				// メニュー管理画面を変更
				if ($isHier){		// 多階層の場合
					$ret = $this->db->updateNavItemMenuType($itemId, self::TREE_MENU_TASK);
				} else {
					$ret = $this->db->updateNavItemMenuType($itemId, self::SINGLE_MENU_TASK);
				}
			}*/
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				$replaceNew = true;			// データ再取得
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
			}
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		} else if ($act == 'site_operation_mode_on'){			// サイト運用モード変更
			// システム制御マネージャーの値を更新
			$this->gSystem->updateSystemConfig(self::CF_SITE_OPERATION_MODE, 1);
			
			// 親ウィンドウを更新
			$this->gPage->updateParentWindow();
		} else if ($act == 'site_operation_mode_off'){			// サイト運用モード変更
			// システム制御マネージャーの値を更新
			$this->gSystem->updateSystemConfig(self::CF_SITE_OPERATION_MODE, 0);
			
			// 親ウィンドウを更新
			$this->gPage->updateParentWindow();
		} else {		// 初期表示の場合
			$replaceNew = true;			// データ再取得
		}
		
		if ($replaceNew){		// データ再取得のとき
			// メニュー情報を取得
			//$ret = $this->getMenuInfo($isHier, $itemId, $row);
			// メニューを階層化するかどうかを取得
			$isHier = $this->gSystem->isSiteMenuHier();
		}
		// 値を埋め込む
		if ($isHier){		// 階層化メニューのとき
			$this->tmpl->addVar("_widget", "menu_type_tree", 'checked');		// 多階層メニュー
		} else {
			$this->tmpl->addVar("_widget", "menu_type_single", 'checked');		// 単階層メニュー
		}
		
		$checked = '';
		if ($this->_db->getSystemConfig(self::CF_SITE_OPERATION_MODE) == '1') $checked = 'checked';		// サイト運用モードのとき
		$this->tmpl->addVar("_widget", "site_operation_mode_checked", $checked);
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
		$ret = $this->db->getNavItemsByTask(self::SEL_MENU_ID, self::TREE_MENU_TASK, $row);
		if ($ret){
			$isHier = true;
		} else {
			$ret = $this->db->getNavItemsByTask(self::SEL_MENU_ID, self::SINGLE_MENU_TASK, $row);
		}
		if ($ret) $itemId = $row['ni_id'];
		return $ret;
	}*/
}
?>
