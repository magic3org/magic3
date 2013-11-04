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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/menuDb.php');

class m_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	private $templateType;		// テンプレートのタイプ
	private $currentUserLogined;	// 現在のユーザはログイン中かどうか
	private $menuData = array();			// Joomla用のメニューデータ
	private $menuTree = array();			// Joomla用のメニュー階層データ
	const DEFAULT_CONFIG_ID = 0;
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new menuDb();
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
		$this->currentUserLogined = $this->gEnv->isCurrentUserLogined();	// 現在のユーザはログイン中かどうか
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){		// 定義データが取得できないとき
			// 出力抑止
			$this->cancelParse();
		} else {
			$menuId		= $targetObj->menuId;	// メニューID
			$name		= $targetObj->name;// 定義名
			$limitUser	= $targetObj->limitUser;// ユーザを制限するかどうか

			// ユーザ制限があるときはログイン時のみ表示
			if (!$limitUser || $this->currentUserLogined){
				// メニュー作成
				$this->menuData['path'] = array();
				$this->menuData['active_id'] = 0;
				$parentTree = array();			// 選択されている項目までの階層パス
				$menuHtml = $this->createMenu($menuId, 0, 0, $tmp, $parentTree);
				
				if (!empty($menuHtml)) $this->tmpl->addVar("_widget", "menu_html", $menuHtml);
				
				// Joomla用のメニュー階層データを設定
				$this->menuData['tree'] = $this->menuTree;
				$this->gEnv->setJoomlaMenuData($this->menuData);
			} else {
				// 出力抑止
				$this->cancelParse();
			}
		}
	}
	/**
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parantId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @param bool		$hasSelectedChild	現在選択状態の子項目があるかどうか
	 * @param array     $parentTree	現在の階層パス
	 * @return string				ツリーメニュータグ
	 */
	function createMenu($menuId, $parantId, $level, &$hasSelectedChild, &$parentTree)
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
				// Joomla用メニューデータ(デフォルト値)
				$menuItem = new stdClass;		// Joomla用メニューデータ
				$menuItem->type = 'alias';		// 内部リンク。外部リンク(url)
				$menuItem->id = $index + 1;
				$menuItem->level = $level + 1;
				$menuItem->active = false;
				$menuItem->parent = false;
				// 階層作成用
				$menuItem->deeper = false;
				$menuItem->shallower = false;
				$menuItem->level_diff = 0;
				$menuTreeCount = count($this->menuTree);
				if ($menuTreeCount > 0){		// 前データがあるときは取得
					$menuLastItem = $this->menuTree[$menuTreeCount -1];
					$menuLastItem->deeper = ($menuItem->level > $menuLastItem->level);
					$menuLastItem->shallower = ($menuItem->level < $menuLastItem->level);
					$menuLastItem->level_diff = $menuLastItem->level - $menuItem->level;
				}
									
				// 非表示のときは処理を飛ばす
				if (!$row['md_visible']) continue;
				
				// ユーザ制限がある場合はログイン状態をチェック
				if ($row['md_user_limited'] && !$this->currentUserLogined) continue;
		
				// リンク先のコンテンツの表示状況に合わせる
				if ($row['md_content_type'] == M3_VIEW_TYPE_CONTENT){		// 汎用コンテンツの場合
					// ログインユーザに表示制限されている場合はメニューを追加しない
					if (!empty($row['cn_user_limited']) && !$this->currentUserLogined) continue;
				}
						
				// Joomla1.0対応
				if ($this->templateType == 0) $linkClassArray[] = 'mainlevel';
				
				// リンク先の作成
				$linkUrl = $row['md_link_url'];
				$linkUrl = $this->getUrl($linkUrl, true/*リンク用*/);
				if (empty($linkUrl)) $linkUrl = '#';
				
				// 選択状態の設定
				if ($this->checkMenuItemUrl($linkUrl)){
					$attr = ' id="current"';		// メニュー項目を選択状態にする
					$classArray[] = 'active';
					$hasSelectedChild = true;
					
					// Joomla用メニュー階層データ
					$pathTree = $parentTree;			// パスを取得
					$pathTree[] = $index + 1;
					$this->menuData['path'] = $pathTree;
					$this->menuData['active_id'] = $index + 1;
					$menuItem->active = true;
				}
				
				// リンクタイプに合わせてタグを生成
				$linkOption = '';
				if (count($linkClassArray) > 0) $linkOption .= 'class="' . implode(' ', $linkClassArray) . '"';
				switch ($row['md_link_type']){
					case 0:			// 同ウィンドウで開くリンク
					default:
						$menuItem->browserNav = 0;		// ウィンドウオープン方法(0=同じウィンドウ、1=別タブ、2=別ウィンドウ)
						break;
					case 1:			// 別ウィンドウで開くリンク
						$linkOption .= ' target="_blank"';
						$menuItem->browserNav = 1;		// ウィンドウオープン方法(0=同じウィンドウ、1=別タブ、2=別ウィンドウ)
						break;
				}
				
				// メニュー項目を作成
				//$name = $row['md_name'];
				$name = $this->getCurrentLangString($row['md_name']);
				//if (empty($name)) continue;
				$title = $this->getCurrentLangString($row['md_title']);		// タイトル(HTML可)
				if (empty($title)) $title = $name;
				if (empty($title)) continue;
				
				// メニュータイトルの処理。タグが含まれていない場合は文字をエスケープする。
				$stripName = strip_tags($title);
				if (strlen($stripName) == strlen($title)) $title = $this->convertToDispString($title);		// 文字列長が同じとき
				
				$index++;		// インデックス番号更新
								
				switch ($row['md_type']){
					case 0:			// リンク項目のとき
						// Joomla用メニューデータ作成
						//$menuItem->title = $name;
						$menuItem->title = $title;
						$menuItem->flink = $linkUrl;
						
						// ##### Joomla用メニュー階層更新 #####
						$this->menuTree[] = $menuItem;
						
						// ##### タグ作成 #####
						if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
						//$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '><span>' . $this->convertToDispString($name) . '</span></a></li>' . M3_NL;
						$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" ' . $linkOption . '><span>' . $title . '</span></a></li>' . M3_NL;
						break;
					case 1:			// フォルダのとき
							// Joomla用メニューデータ作成
							//$menuItem->title = $name;
							$menuItem->title = $title;
							$menuItem->flink = $linkUrl;
							$menuItem->parent = true;
							// 階層作成用
							//$menuItem->deeper = true;
							//$menuItem->level_diff = 1;

							// ##### Joomla用メニュー階層更新 #####
							$this->menuTree[] = $menuItem;
						
							// 階層を更新
							//array_push($parentTree, $index + 1);
							array_push($parentTree, $index);
							
							// サブメニュー作成
							$menuText = $this->createMenu($menuId, $row['md_id'], $level + 1, $hasSelectedChild, $parentTree);
							
							// 階層を戻す
							array_pop($parentTree);
							
							// 子項目が選択中のときは「active」に設定
							if ($hasSelectedChild) $classArray[] = 'active';

							// 先頭に「parent」クラスを追加
							array_unshift($classArray, 'parent');
							
							// ##### タグ作成 #####
							if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
							//$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '"><span>' . $this->convertToDispString($name) . '</span></a>' . M3_NL;
							$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '"><span>' . $title . '</span></a>' . M3_NL;
							if (!empty($menuText)){
								$treeHtml .= '<ul>' . M3_NL;
								$treeHtml .= $menuText;
								$treeHtml .= '</ul>' . M3_NL;
							}
							$treeHtml .= '</li>' . M3_NL;
						break;
					case 2:			// テキストのとき
						//$treeHtml .= '<li><span>' . $this->convertToDispString($name) . '</span></li>' . M3_NL;
						$treeHtml .= '<li><span>' . $title . '</span></li>' . M3_NL;
						break;
					case 3:			// セパレータのとき
						// Joomla用メニューデータ作成
						$menuItem->type = 'separator';
						//$menuItem->title = $name;
						$menuItem->title = $title;
						$menuItem->flink = '';
						
						// ##### Joomla用メニュー階層更新 #####
						$this->menuTree[] = $menuItem;
						
						// ##### タグ作成 #####
						//$treeHtml .= '<li><span class="separator">' . $this->convertToDispString($name) . '</span></li>' . M3_NL;
						$treeHtml .= '<li><span class="separator">' . $title . '</span></li>' . M3_NL;
						break;
				}
				
				if ($this->templateType == 0){			// Joomla!v1.0のとき
					$itemRow = array(
						'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
						//'name' => $this->convertToDispString($name),			// タイトル
						'name' => $title,			// タイトル
						'attr' => $attr,			// liタグ追加属性
						'option' => $linkOption			// Aタグ追加属性
					);
					$this->tmpl->addVars('itemlist', $itemRow);
					$this->tmpl->parseTemplate('itemlist', 'a');
				}
				$menuTreeCount = count($this->menuTree);
				if ($menuTreeCount > 0){		// 前データがあるときは取得
					$menuLastItem = $this->menuTree[$menuTreeCount -1];
					$menuLastItem->deeper = (1 > $menuLastItem->level);
					$menuLastItem->shallower = (1 < $menuLastItem->level);
					$menuLastItem->level_diff = $menuLastItem->level - 1;
				}
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
