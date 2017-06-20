<?php
/**
 * メニューAPI
 *
 * メニュー作成用のデータにアクセス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
class MenuApi
{
	private $db;				// システムDBオブジェクト
	private $menuId;			// メニューID
	private $langId;
	private $now;				// 現在日次
	const CF_DEFAULT_MENU_ID = 'default_menu_id';		// メニューID取得用
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		global $gSystemManager;
		global $gEnvManager;
		
		// システムDBオブジェクトを取得
		$this->db = $gInstanceManager->getSytemDbObject();
		
		// メニューIDを取得
		$this->menuId = $gSystemManager->getSystemConfig(self::CF_DEFAULT_MENU_ID);
		
		// その他
		$this->langId = $gEnvManager->getCurrentLanguage();				// コンテンツの言語(コンテンツ取得用)
		$this->now = date("Y/m/d H:i:s");	// 現在日時
	}
	/**
	 * [WordPressテンプレート用API]メニュー情報を取得
	 *
	 * @return array     				WP_Postオブジェクトの配列
	 */
	function getMenuItemList()
	{
		$parentTree = array();			// 選択されている項目までの階層パス
		$menuHtml = $this->_createMenu($this->menuId, 0, 0, $tmp, $parentTree);
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
	function _createMenu($menuId, $parantId, $level, &$hasSelectedChild, &$parentTree)
	{
		static $index = 0;		// インデックス番号
		$hasSelectedChild = false;

		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		$treeHtml = '';
		if ($this->db->getChildMenuItems($menuId, $parantId, $this->langId, $this->now, $rows)){
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
				if ($this->renderType == 'JOOMLA_OLD') $linkClassArray[] = 'mainlevel';
				
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
						if (!empty($this->isHierMenu)){	// 階層化メニューを使用する場合
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
							$menuText = $this->_createMenu($menuId, $row['md_id'], $level + 1, $hasSelectedChild, $parentTree);
							
							// 階層を戻す
							array_pop($parentTree);
							
							// 子項目が選択中のときは「active」に設定
							if ($hasSelectedChild) $classArray[] = 'active';

							// 先頭に「parent」クラスを追加
							array_unshift($classArray, 'parent');
							
							// ##### タグ作成 #####
							if ($this->renderType == 'BOOTSTRAP_NAV'){// Bootstrapナビゲーションメニューのとき
								//$classArray[] = 'dropdown';
								$dropDownCaret = '';
								if ($level == 0){
									$dropDownCaret = ' <b class="caret"></b>';
								} else {
									$classArray[] = 'dropdown-submenu';
								}
								
								if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
								$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" class="dropdown-toggle" data-toggle="dropdown"><span>' . $title . $dropDownCaret . '</span></a>' . M3_NL;
								if (!empty($menuText)){
									$treeHtml .= '<ul class="dropdown-menu">' . M3_NL;
									$treeHtml .= $menuText;
									$treeHtml .= '</ul>' . M3_NL;
								}
								$treeHtml .= '</li>' . M3_NL;
							} else {
								if (count($classArray) > 0) $attr .= ' class="' . implode(' ', $classArray) . '"';
								$treeHtml .= '<li' . $attr . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '"><span>' . $title . '</span></a>' . M3_NL;
								if (!empty($menuText)){
									$treeHtml .= '<ul>' . M3_NL;
									$treeHtml .= $menuText;
									$treeHtml .= '</ul>' . M3_NL;
								}
								$treeHtml .= '</li>' . M3_NL;
							}
						}
						break;
					case 2:			// テキストのとき
						$treeHtml .= '<li><span>' . $title . '</span></li>' . M3_NL;
						break;
					case 3:			// セパレータのとき
						// Joomla用メニューデータ作成
						$menuItem->type = 'separator';
						$menuItem->title = $title;
						$menuItem->flink = '';
						
						// ##### Joomla用メニュー階層更新 #####
						$this->menuTree[] = $menuItem;
						
						// ##### タグ作成 #####
						if ($this->renderType == 'BOOTSTRAP_NAV' || $this->renderType == 'BOOTSTRAP'){// Bootstrapメニューのとき
							$treeHtml .= '<li class="divider"></li>' . M3_NL;
						} else {
							$treeHtml .= '<li><span class="separator">' . $title . '</span></li>' . M3_NL;
						}
						break;
				}
				
				if ($this->renderType == 'JOOMLA_OLD'){			// Joomla!v1.0のとき
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
}
?>
