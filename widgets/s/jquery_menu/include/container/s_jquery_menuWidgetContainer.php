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
 * @version    SVN: $Id: s_jquery_menuWidgetContainer.php 4946 2012-06-08 01:41:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_jquery_menuDb.php');

class s_jquery_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語
	private $isHierMenu;		// 階層化メニューを使用するかどうか
	private $menuType;		// メニュータイプ
	private $theme;			// メニューのテーマ
	private $targetObj;		// 設定値オブジェクト
	const DEFAULT_CONFIG_ID = 0;
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	const DEFAULT_MENU_TYPE = 'listview';			// デフォルトのメニュータイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new s_jquery_menuDb();
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
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$this->targetObj = $this->getWidgetParamObjByConfigId($configId);
		
		$this->menuType = $this->targetObj->menuType;		// メニュータイプ;
		if (empty($this->menuType)) $this->menuType = self::DEFAULT_MENU_TYPE;
		
		switch ($this->menuType){
			case 'listview':
			default:
				return 'index.tmpl.html';
			case 'navbar':
				return 'index_nav.tmpl.html';
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		if (empty($this->targetObj)){		// 定義データが取得できないとき
			// 出力抑止
			$this->cancelParse();
			return;
		}

		$menuId		= $this->targetObj->menuId;	// メニューID
		$name		= $this->targetObj->name;// 定義名
		$this->isHierMenu	= $this->targetObj->isHierMenu;		// 階層化メニューを使用するかどうか
		$title			= $this->targetObj->title;			// リストタイトル
		$this->theme	= $this->targetObj->theme;		// メニューのテーマ
		$insetList		= $this->targetObj->insetList;		// インセットリスト形式で表示するかどうか

		// メニュー作成
		$menuHtml = $this->createMenu($menuId, 0, 0, $tmp);
			
		if (!empty($menuHtml)) $this->tmpl->addVar("_widget", "menu_html", $menuHtml);
		
		// タイトル
		if (empty($title)){
			$this->tmpl->setAttribute('listtitle', 'visibility', 'hidden');
		} else {
			$this->tmpl->addVar("listtitle", "title",	$this->convertToDispString($title));
		}
		
		// リストスタイル
		$listOption = '';
		if (!empty($insetList)){		// インセットリスト形式で表示するかどうか
			$listOption .= ' data-inset="true"';
		}
		if (!empty($this->theme)){
			$listOption .= ' data-theme="' . $this->theme . '"';
		}
		$this->tmpl->addVar("_widget", "list_option",	$listOption);
	}
	/**
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parantId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @param bool		$hasSelectedChild	現在選択状態の子項目があるかどうか
	 * @return string				ツリーメニュータグ
	 */
	function createMenu($menuId, $parantId, $level, &$hasSelectedChild)
	{
		static $index = 0;		// インデックス番号
		$hasSelectedChild = false;

		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		$treeHtml = '';
		if ($this->db->getChildMenuItems($menuId, $parantId, $this->langId, $rows)){
			$itemCount = count($rows);
			for ($i = 0; $i < $itemCount; $i++){
				$row = $rows[$i];
				$classArray = array();
				$linkClassArray = array();
				$attr = '';
				
				// 非表示のときは処理を飛ばす
				if (!$row['md_visible']) continue;
				
				// リンク先のコンテンツの表示状況に合わせる
				if ($row['md_content_type'] == M3_VIEW_TYPE_CONTENT){		// 汎用コンテンツの場合
					// ログインユーザに表示制限されている場合はメニューを追加しない
					if (!empty($row['cn_user_limited']) && !$this->gEnv->isCurrentUserLogined()) continue;
				}
				
				// リンク先の作成
				$linkUrl = $row['md_link_url'];
				$linkUrl = $this->getUrl($linkUrl, true/*リンク用*/);
				if (empty($linkUrl)) $linkUrl = '#';
				
				// 選択状態の設定
				if ($this->checkMenuItemUrl($linkUrl)){
					$attr = ' id="current"';		// メニュー項目を選択状態にする
					$classArray[] = 'active';
					$hasSelectedChild = true;
				}
				
				// リンクタイプに合わせてタグを生成
				$linkOption = '';
				if (count($linkClassArray) > 0) $linkOption .= 'class="' . implode(' ', $linkClassArray) . '"';
				switch ($row['md_link_type']){
					case 0:			// 同ウィンドウで開くリンク
						break;
					case 1:			// 別ウィンドウで開くリンク
						$linkOption .= ' target="_blank"';
						break;
				}
				// 色を設定
				if ($this->menuType == 'navbar'){
					if (!empty($this->theme)) $linkOption .= ' data-theme="' . $this->theme . '"';
				}
				
				// メニュー項目を作成
				$name = $this->getCurrentLangString($row['md_name']);
				if (empty($name)) continue;
				
				switch ($row['md_type']){
					case 0:			// リンク項目のとき
						$linkOption .= ' data-ajax="false"';		// Ajaxでリンクしない
						if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
						$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '><span>' . $this->convertToDispString($name) . '</span></a></li>' . M3_NL;
						break;
					case 1:			// フォルダのとき
						if (!empty($this->isHierMenu)){	// 階層化メニューを使用する場合
							// サブメニュー作成
							$menuText = $this->createMenu($menuId, $row['md_id'], $level + 1, $hasSelectedChild);
							
							// 子項目が選択中のときは「active」に設定
							if ($hasSelectedChild) $classArray[] = 'active';

							// 先頭に「parent」クラスを追加
							array_unshift($classArray, 'parent');
							
							// 子項目を追加
							if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
							$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '"><span>' . $this->convertToDispString($name) . '</span></a>' . M3_NL;
							if (!empty($menuText)){
								$treeHtml .= '<ul>' . M3_NL;
								$treeHtml .= $menuText;
								$treeHtml .= '</ul>' . M3_NL;
							}
							$treeHtml .= '</li>' . M3_NL;
						}
						break;
					case 2:			// テキストのとき
						$treeHtml .= '<li><span>' . $this->convertToDispString($name) . '</span></li>' . M3_NL;
						break;
					case 3:			// セパレータのとき
						$treeHtml .= '<li data-role="list-divider"><span>' . $this->convertToDispString($name) . '</span></li>' . M3_NL;
						break;
				}
				$index++;		// インデックス番号更新
			}
		}
		return $treeHtml;
	}
	/**
	 * メニュー項目の選択条件をチェック
	 *
	 * @param string $url	チェック対象のURL
	 * @return bool			true=アクティブ、false=非アクティブ
	 */
	function checkMenuItemUrl($url)
	{
		$currentUrl = $this->gEnv->getCurrentRequestUri();
		
		// 同じURLのとき
		if ($url == $currentUrl) return true;
		
		// URLを解析
		$queryArray = array();
		$parsedUrl = parse_url($url);
		if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $queryArray);		// クエリーの解析
		
		// ルートかどうかチェック(クエリー文字列なし)
		if ($this->isRootUrl($url)){
			// ページサブIDで比較
			if ($this->gEnv->getCurrentPageSubId() == $this->gEnv->getDefaultPageSubId()) return true;
		}
		
		// パラメータがサブページIDだけの場合はページサブIDで比較
		if (count($queryArray) == 1 && isset($queryArray[M3_REQUEST_PARAM_PAGE_SUB_ID])){
			if ($this->gEnv->getCurrentPageSubId() == $queryArray[M3_REQUEST_PARAM_PAGE_SUB_ID]) return true;
		}
		return false;
	}
	/**
	 * URLがルートを指しているかどうか取得
	 *
	 * @param string $url	チェック対象のURL
	 * @return bool			true=ルート、false=ルート以外
	 */
	function isRootUrl($url)
	{
		$url = str_replace('https://', 'http://', $url);		// 一旦httpに統一
		$systemUrl = str_replace('https://', 'http://', $this->gEnv->getRootUrl());		// 一旦httpに統一
		$systemSslUrl = str_replace('https://', 'http://', $this->gEnv->getSslRootUrl());		// 一旦httpに統一

		$parsedUrl = parse_url($url);
		if (empty($parsedUrl['query'])){		// クエリ文字列がないことが条件。「#」はあっても良い。
			// パスを解析
			$relativePath = str_replace($systemUrl, '', $url);		// ルートURLからの相対パスを取得
			if (empty($relativePath)){			// Magic3のルートURLの場合
				return true;
			} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '/' . M3_FILENAME_INDEX)){		// ルートURL配下のとき
				return true;
			} else {		// ルートURL以外のURLのとき(SSL用のURL以下かどうかチェック)
				$relativePath = str_replace($systemSslUrl, '', $url);		// ルートURLからの相対パスを取得
				if (empty($relativePath)){			// Magic3のルートURLの場合
					return true;
				} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '/' . M3_FILENAME_INDEX)){		// ルートURL配下のとき
					return true;
				}
			}
		}
		return false;
	}
}
?>
