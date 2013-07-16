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
 * @version    SVN: $Id: s_slide_menuWidgetContainer.php 4947 2012-06-08 02:15:04Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_slide_menuDb.php');

class s_slide_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	private $cssId;			// タグのID
	private $css;
	private $menuType;		// メニュータイプ
	private $menuSpeed;		// メニュー表示速度
	private $defaultNo;			// デフォルトの項目番号
	const DEFAULT_CONFIG_ID = 0;
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	const MENU_CLASS = 'slidemenu';		// メニュー用クラス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new s_slide_menuDb();
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
		return 'index.tmpl.html';
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){		// 定義データが取得できないとき
			// 出力抑止
			$this->cancelParse();
		} else {		// 定義データが取得できたとき
			$menuId		= $targetObj->menuId;	// メニューID
			$name		= $targetObj->name;// 定義名
			$this->menuType = $targetObj->menuType;			// メニュータイプ
			$this->menuSpeed = $targetObj->menuSpeed;			// メニュー表示速度
			$this->defaultNo = $targetObj->defaultNo;			// デフォルトの項目番号
			$this->css		= $targetObj->css;		// メニューCSS
			$this->cssId		= $targetObj->cssId;	// メニューCSSのID

			// メニュー作成
			$menuHtml = $this->createMenu($menuId, 0);
			
			$this->tmpl->addVar("_widget", "menu_html", $menuHtml);
			$this->tmpl->addVar("_widget", "css_id",	$this->cssId);	// CSS用ID
			$menuOptionClass = '';
			if ($this->menuType == 'slide') $menuOptionClass .= ' noaccordion';		// メニューの開閉なし
			$this->tmpl->addVar("_widget", "menu_class",	self::MENU_CLASS);		// メニュークラス
			$this->tmpl->addVar("_widget", "menu_option",	$menuOptionClass);		// メニュークラス
			$this->tmpl->addVar("_widget", "menu_speed",	$this->menuSpeed);		// メニュー表示速度
		}
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->css;
	}
	/**
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parantId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @return string		ツリーメニュータグ
	 */
	function createMenu($menuId, $parantId, $level = 0)
	{
		static $index = 1;		// 項目順
		
		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		$treeHtml = '';
		if ($this->db->getChildMenuItems($menuId, $parantId, $rows)){
			$itemCount = count($rows);
			for ($i = 0; $i < $itemCount; $i++){
				$row = $rows[$i];
				
				// 非表示のときは処理を飛ばす
				if (!$row['md_visible']) continue;

				// リンク先の作成
				$linkUrl = $row['md_link_url'];
				$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $linkUrl);
				if (empty($linkUrl)) $linkUrl = '#';
				$linkUrl = $this->convertUrlToHtmlEntity($linkUrl);
				
				// リンクタイプに合わせてタグを生成
				$option = '';
				switch ($row['md_link_type']){
					case 0:			// 同ウィンドウで開くリンク
						break;
					case 1:			// 別ウィンドウで開くリンク
						$option = 'target="_blank"';
						break;
				}
				
				// メニュー項目を作成
				$name = $this->getCurrentLangString($row['md_name']);
				if (empty($name)) continue;
				
				// メニュー項目のIDを生成
				$menuItemId = $this->cssId . '_' . $index;
				$index++;
				
				// ##### ツリーメニュー作成 #####
				if ($row['md_type'] == 0){	// リンク項目のとき
					// spanタグを付けてフォルダと区別
					$treeHtml .= '<li id="' . $menuItemId . '"><span><a href="' . $linkUrl . '" ' . $option . '>' . $this->convertToDispString($name) . '</a></span></li>' . M3_NL;
				} else if ($row['md_type'] == 1){			// フォルダのとき
					// サブメニュー作成
					// リンク先も設定する(2010/2/12)
					if ($linkUrl == '#'){		// リンク先がないとき
						$treeHtml .= '<li id="' . $menuItemId . '"><a href="#">' . $this->convertToDispString($name) . '</a>' . M3_NL;
					} else {		// リンク先が設定されている場合は開閉の動きをしない
						$treeHtml .= '<li id="' . $menuItemId . '"><span><a href="' . $linkUrl . '">' . $this->convertToDispString($name) . '</a></span>' . M3_NL;
					}
					
					// 1階層目のときは、メニューの開閉を制御
					$optionClass = '';
					if ($this->menuType != 'open'){
						if ($level == 0 && $i + 1 == intval($this->defaultNo)) $optionClass = ' class="expand"';
						
						if (empty($optionClass)) $optionClass = ' style="display:none;"';
					}
					$treeHtml .= '<ul' . $optionClass . '>' . M3_NL;
					
					$treeHtml .= $this->createMenu($menuId, $row['md_id'], $level + 1);
					$treeHtml .= '</ul>' . M3_NL;
					$treeHtml .= '</li>' . M3_NL;
				} else if ($row['md_type'] == 2){			// テキストのとき
					//$treeHtml .= '<li id="' . $menuItemId . '"><span>' . $this->convertToDispString($name) . '</span></li>' . M3_NL;
				} else if ($row['md_type'] == 3){			// セパレータのとき
					//$treeHtml .= '<li id="' . $menuItemId . '"><span>' . '-----' . '</span></li>' . M3_NL;
				}
			}
		}
		return $treeHtml;
	}
}
?>
