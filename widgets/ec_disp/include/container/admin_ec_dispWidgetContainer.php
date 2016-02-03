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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_ec_dispWidgetContainer extends baseAdminWidgetContainer
{
	const DEFAULT_PRODUCT_COUNT = 10;				// デファオルとの表示項目数
	const DEFAULT_TARGET_WIDGET = 'ec_main';		// 呼び出しウィジェットID
	const DEFAULT_STOCK_VIEW_FORMAT = '0:なし;3:残り僅か($1);:あり($1)';
	const DEFAULT_CATEGORY_LIST_TITLE = '「$1」の商品一覧';		// カテゴリー表示時タイトル
	const DEFAULT_CATEGORY_TITLE_SEPARATOR = '-';		// デフォルトのカテゴリータイトル作成用セパレータ
	
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
		
		$categoryListTitle	= $request->trimValueOf('item_category_list_title');	// カテゴリー表示時タイトル
		$categoryTitleSeparator	= $request->valueOf('item_category_title_separator');		// カテゴリータイトル作成用セパレータ(空白許可)
		$productCount	= $request->trimValueOf('item_product_list_count');	// 商品表示数
		$targetWidget	= $request->trimValueOf('item_cart_widget');			// カート表示ウィジェット
		$showStock		= ($request->trimValueOf('item_show_stock') == 'on') ? 1 : 0;		// 在庫表示するかどうか
		$stockViewFormat	= $request->trimValueOf('item_stock_view_format');			// 在庫表示フォーマット
		$contentNoStock	= $request->valueOf('item_content_no_stock');			// 在庫なし時コンテンツ
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($productCount, '表示項目数');
			$this->checkSingleByte($targetWidget, 'カート表示ウィジェット');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				if (empty($categoryListTitle)) $categoryListTitle = self::DEFAULT_CATEGORY_LIST_TITLE;	// カテゴリー表示時タイトル
				if (empty($categoryTitleSeparator)) $categoryTitleSeparator = self::DEFAULT_CATEGORY_TITLE_SEPARATOR;// カテゴリータイトル作成用セパレータ
				if (empty($stockViewFormat)) $stockViewFormat	= self::DEFAULT_STOCK_VIEW_FORMAT;			// 在庫表示フォーマット
				
				$paramObj = new stdClass;
				$paramObj->categoryListTitle = $categoryListTitle;	// カテゴリー表示時タイトル
				$paramObj->categoryTitleSeparator = $categoryTitleSeparator;	// カテゴリータイトル作成用セパレータ
				$paramObj->productCount	= $productCount;		// 商品表示数
				$paramObj->targetWidget = $targetWidget;		// カート表示ウィジェット
				$paramObj->showStock	= $showStock;				// 在庫表示するかどうか
				$paramObj->stockViewFormat	= $stockViewFormat;	// 在庫表示フォーマット
				$paramObj->contentNoStock	= $contentNoStock;	// 在庫なし時コンテンツ
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
			$categoryListTitle = self::DEFAULT_CATEGORY_LIST_TITLE;	// カテゴリー表示時タイトル
			$categoryTitleSeparator = self::DEFAULT_CATEGORY_TITLE_SEPARATOR;	// カテゴリータイトル作成用セパレータ
			$productCount = self::DEFAULT_PRODUCT_COUNT;						// 商品表示数
			$targetWidget = self::DEFAULT_TARGET_WIDGET;		// カート表示ウィジェット
			$showStock		= 0;		// 在庫表示するかどうか
			$stockViewFormat	= self::DEFAULT_STOCK_VIEW_FORMAT;			// 在庫表示フォーマット
			$contentNoStock = '';	// 在庫なし時コンテンツ
			
			// 保存値取得
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$categoryListTitle = $paramObj->categoryListTitle;	// カテゴリー表示時タイトル
				$categoryTitleSeparator = $paramObj->categoryTitleSeparator;	// カテゴリータイトル作成用セパレータ
				$productCount = $paramObj->productCount;		// 商品表示数
				$targetWidget = $paramObj->targetWidget;		// カート表示ウィジェット
				$showStock		= $paramObj->showStock;				// 在庫表示するかどうか
				$stockViewFormat	= $paramObj->stockViewFormat;	// 在庫表示フォーマット
				$contentNoStock		= $paramObj->contentNoStock;	// 在庫なし時コンテンツ
			}
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "category_list_title", $this->convertToDispString($categoryListTitle));		// カテゴリー表示時タイトル
		$this->tmpl->addVar("_widget", "category_title_separator", $this->convertToDispString($categoryTitleSeparator));	// カテゴリータイトル作成用セパレータ
		$this->tmpl->addVar("_widget", "product_list_count", $this->convertToDispString($productCount));		// 商品表示数
		$this->tmpl->addVar("_widget", "cart_widget", $this->convertToDispString($targetWidget));		// カート表示ウィジェット
		$this->tmpl->addVar("_widget", "stock_view_format", $stockViewFormat);		// 在庫表示フォーマット
		$this->tmpl->addVar("_widget", "content_no_stock", $contentNoStock);		// 在庫なし時コンテンツ
		$checkedStr = '';
		if ($showStock) $checkedStr = 'checked';
		$this->tmpl->addVar("_widget", "show_stock_checked", $checkedStr);		// 在庫表示するかどうか
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
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
	}
}
?>
