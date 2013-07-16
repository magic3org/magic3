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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_ec_menuOtherWidgetContainer.php 5458 2012-12-12 08:29:09Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_ec_menuBaseWidgetContainer.php');

class admin_ec_menuOtherWidgetContainer extends admin_ec_menuBaseWidgetContainer
{
	const DEFAULT_CATEGORY_COUNT = 2;	// デフォルトの商品カテゴリー選択可能数
	
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
		return 'admin_other.tmpl.html';
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
		if ($act == 'update'){		// 設定更新のとき
			// 入力値を取得
			$useVerticalMenu = ($request->trimValueOf('item_vertical_menu') == 'on') ? 1 : 0;		// 縦型メニューデザインを使用するかどうか
			$linkProduct = ($request->trimValueOf('item_link_product') == 'on') ? 1 : 0;		// 商品詳細にカテゴリーを連動
			$categoryCount	= $request->valueOf('item_category_count');		// 商品カテゴリー選択可能数
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->useVerticalMenu	= $useVerticalMenu;		// 縦型メニューデザインを使用するかどうか
				$paramObj->linkProduct = $linkProduct;		// 商品詳細にカテゴリーを連動
				$paramObj->categoryCount	= $categoryCount;		// 商品カテゴリー選択可能数
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$useVerticalMenu = 1;	// 縦型メニューデザインを使用するかどうか
			$linkProduct = 1;		// 商品詳細にカテゴリーを連動
			$categoryCount = self::DEFAULT_CATEGORY_COUNT;
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$useVerticalMenu	= $paramObj->useVerticalMenu;
				$linkProduct		= $paramObj->linkProduct;		// 商品詳細にカテゴリーを連動
				$categoryCount		= $paramObj->categoryCount;		// 商品カテゴリー選択可能数
			}
		}
		
		// 画面にデータを埋め込む
		$checked = '';
		if ($useVerticalMenu) $checked = 'checked';
		$this->tmpl->addVar("_widget", "vertical_menu", $checked);		// 縦型メニューデザインを使用するかどうか
		$checked = '';
		if ($linkProduct) $checked = 'checked';
		$this->tmpl->addVar("_widget", "link_product", $checked);		// 商品詳細にカテゴリーを連動
		$this->tmpl->addVar("_widget", "category_count", $categoryCount);		// 商品カテゴリー選択可能数
	}
}
?>
