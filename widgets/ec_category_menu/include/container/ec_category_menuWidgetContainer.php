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
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_category_menuWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/ec_category_menuInfo.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_category_menuDb.php');

class ec_category_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $levelCount;	// メニュー表示レベル
	private $imgFilename;		// 画像ファイル名
	private $color1;
	private $color2;
	private $color3;
	private $color4;
	private $imageMenu;		// 画像メニューを使用するかどうか
	private $title;			// メニュータイトル
	private $menuType = 0;		// デフォルトメニューのメニュータイプ(0=テーブル、1=リスト)
	const TARGET_WIDGET = 'ec_main';		// 呼び出しウィジェットID
	const THIS_WIDGET_ID = 'ec_category_menu';		// ウィジェットID
	const DEFAULT_IMG_FILENAME = 'menu1.png';		// デフォルトファイル名
	const DEFAULT_LEVEL_COUNT = 3;		// デフォルト表示階層
	const DEFAULT_MENU_TITLE = '商品カテゴリー';	// デフォルトメニュータイトル
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_category_menuDb();
	}
	/**
	 * ウィジェット単位のアクセス制御
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _checkAccess($request)
	{
		return true;
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
		global $gInstanceManager;
		
		// メニューの設定を取得
		$this->imgFilename = self::DEFAULT_IMG_FILENAME;		// メニュー画像
		$this->levelCount = self::DEFAULT_LEVEL_COUNT;		// デフォルト表示階層
		$this->title = self::DEFAULT_MENU_TITLE;			// メニュータイトル
		$serializedParam = $gInstanceManager->getSytemDbObject()->getWidgetParam(self::THIS_WIDGET_ID);
		if (!empty($serializedParam)){
			$menuInfo = unserialize($serializedParam);
			if (!empty($menuInfo->imageFilename)) $this->imgFilename = $menuInfo->imageFilename;
			if (!empty($menuInfo->levelCount)) $this->levelCount = $menuInfo->levelCount;
			if (!empty($menuInfo->fontColor1)) $this->color1 = $menuInfo->fontColor1;
			if (!empty($menuInfo->fontColor2)) $this->color2 = $menuInfo->fontColor2;
			if (!empty($menuInfo->fontColor3)) $this->color3 = $menuInfo->fontColor3;
			if (!empty($menuInfo->fontColor4)) $this->color4 = $menuInfo->fontColor4;
			if (!empty($menuInfo->useImageMenu)) $this->imageMenu = $menuInfo->useImageMenu;		// 画像メニューを使用するかどうか
			if (!empty($menuInfo->title)) $this->title = $menuInfo->title;			// メニュータイトル
		}
		if ($this->imageMenu){// 画像メニューを使用する場合
			return 'menu.tmpl.html';
		} else {
			return 'menu_v_table.tmpl.html';
		}
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
		global $gEnvManager;
		
		// 現在の言語
		$currentLang	= $gEnvManager->getCurrentLanguage();
		
		if ($this->imageMenu){// 画像メニューを使用する場合
			// メニュータイトルの設定
			if (!empty($this->title)){
				$titleString = '<tr><th>' . $this->title . '</th></tr>';
				$this->tmpl->addVar("_widget", "title", $titleString);
			}
			
			// メニューツリーを作成
			$categoryHtml =  $this->createCategoryTreeList($currentLang);
			$this->tmpl->addVar("_widget", "category_menu", $categoryHtml);
		
			// 埋め込みパラメータの設定
			$this->tmpl->addVar("_widget", "image_filename", $this->imgFilename);		// 画像ファイル名
			$this->tmpl->addVar("_widget", "color1", $this->color1);		// 文字色1
			$this->tmpl->addVar("_widget", "color2", $this->color2);		// 文字色2
			$this->tmpl->addVar("_widget", "color3", $this->color3);		// 文字色3
			$this->tmpl->addVar("_widget", "color4", $this->color4);		// 文字色4
		} else {
			// メニューテーブルのパラメータ
			$this->tmpl->addVar("_widget", "default_menu_param", $this->gDesign->getDefaultWidgetTableParam());
			
			// デフォルトメニューのときは1階層のみ作成
			// メニュータイトルの設定
			if (!empty($this->title)){
				$titleString = '';
				if ($this->menuType == 0){			// テーブルタイプのとき
					$titleString .= '<tr><th>' . $this->title . '</th></tr>';
				} else if ($this->_menuType == 1){		// リストタイプのとき
					$titleString .= '<lh>' . $this->title . '</lh>';
				} else {
				}
				$this->tmpl->addVar("_widget", "title", $titleString);
			}
			
			// メニューを作成
			$categoryHtml =  $this->createCategoryList($currentLang);
			$this->tmpl->addVar("_widget", "category_menu", $categoryHtml);
		}
	}
	/**
	 * カテゴリーツリーリスト作成
	 *
	 * @param string	$langId				言語ID
	 * @return string						カテゴリーリスト表示用HTML
	 */
	function createCategoryTreeList($langId)
	{
		$listHtml = '';
		$this->createCategoryTreeListLoop(0, $langId, 0, $listHtml);
		return $listHtml;
	}
	/**
	 * カテゴリーツリーリスト作成
	 *
	 * @param int       $parentId			親カテゴリーID
	 * @param string	$langId				言語ID
	 * @param int       $level				メニューの階層
	 * @param string						カテゴリーリスト表示用HTML
	 * @return なし	
	 */
	function createCategoryTreeListLoop($parentId, $langId, $level, &$listHtml)
	{
		$arraySize = $this->db->getChildCategoryWithRows($parentId, $langId, $rows);
		for ($i = 0; $i < $arraySize; $i++){
			$id = $rows[$i]['pc_id'];
			
			$url  = '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_FIND_WIDGET;
			$url .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . self::TARGET_WIDGET;
			$url .= '&' . M3_REQUEST_PARAM_FROM . '=' . self::THIS_WIDGET_ID;		// 送信元
//			$url .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=category';
//			$url .= '&id=' . $id;
			$url .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode('category=' . $id);
			
			// 最大階層のときは、サブメニューを生成しない
			if ($level + 1 >= $this->levelCount){
				$childCount = 0;
			} else {
				$childCount = $this->db->getChildCategoryWithRows($id, $langId, $rows2);
			}
			if ($childCount <= 0){			
				$listHtml .= '<li><a href="' . $this->getUrl($url, true) . '">' . $rows[$i]['pc_name'] . '</a></li>' . M3_NL;
			} else {
				$listHtml .= '<li><a href="' . $this->getUrl($url, true) . '">' . $rows[$i]['pc_name'] . '</a>' . M3_NL;
				$listHtml .= '<ul>' . M3_NL;
				$this->createCategoryTreeListLoop($id, $langId, $level+1, $listHtml);
				$listHtml .= '</ul>' . M3_NL;
				$listHtml .= '</li>' . M3_NL;
			}
		}
	}
	/**
	 * カテゴリーリスト作成(1階層のみ)
	 *
	 * @param string	$langId				言語ID
	 * @return string						カテゴリーリスト表示用HTML
	 */
	function createCategoryList($langId)
	{
		$listHtml = '';
		$arraySize = $this->db->getChildCategoryWithRows(0, $langId, $rows);
		for ($i = 0; $i < $arraySize; $i++){
			$id = $rows[$i]['pc_id'];
			
			$url  = '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_FIND_WIDGET;
			$url .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . self::TARGET_WIDGET;
			$url .= '&' . M3_REQUEST_PARAM_FROM . '=' . self::THIS_WIDGET_ID;		// 送信元
//			$url .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=category';
//			$url .= '&id=' . $id;
			$url .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode('category=' . $id);
			$listHtml .= '<tr><td><a href="' . $this->getUrl($url, true) . '" class="mainlevel">' . $rows[$i]['pc_name'] . '</a></td></tr>' . M3_NL;
		}
		return $listHtml;
	}
}
?>
