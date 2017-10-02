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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/baseApi.php');

class MenuApi extends BaseApi
{
	private $db;				// システムDBオブジェクト
	private $menuId;			// メニューID
	private $langId;
	private $now;					// 現在日時
	private $currentUserLogined;	// 現在のユーザはログイン中かどうか
	private $menuData;			// メニューデータ
	private $menuTree;			// メニュー階層データ
	private $activeMenuItemTitle;		// 選択中のメニュー項目のタイトル
	const CF_DEFAULT_MENU_ID = 'default_menu_id';		// メニューID取得用
	const MAX_MENU_TREE_LEVEL = 5;			// メニュー階層最大数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクトを取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		// メニューIDを取得
		$this->menuId = $this->gSystem->getSystemConfig(self::CF_DEFAULT_MENU_ID);
		
		// その他
		$this->langId = $this->gEnv->getCurrentLanguage();				// コンテンツの言語(コンテンツ取得用)
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentUserLogined = $this->gEnv->isCurrentUserLogined();	// 現在のユーザはログイン中かどうか
	}
	/**
	 * [WordPressテンプレート用API]メインメニューのIDを取得
	 *
	 * @return string		メニューID
	 */
	function getMenuId()
	{
		return $this->menuId;
	}
	/**
	 * [WordPressテンプレート用API]メインメニューが存在するか確認
	 *
	 * @return bool				true=存在する、false=存在しない
	 */
	function isExistsMenu()
	{
		if ($this->db->getChildMenuItems($this->menuId, 0/*親ID*/, $this->langId, $this->now, $rows)){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * [WordPressテンプレート用API]メニュー情報を取得
	 *
	 * @return array     				WP_Postオブジェクトの配列
	 */
	function getMenuItemList()
	{
		$this->menuData = array();			// Joomla用のメニューデータ
		$this->menuTree = array();			// Joomla用のメニュー階層データ
		$parentTree = array();			// 選択されている項目までの階層パス
		$menuHtml = $this->_createMenu($this->menuId, 0, 0, $tmp, $parentTree);

		$menuItems = array();
		for ($i = 0; $i < count($this->menuTree); $i++){
			$item = $this->menuTree[$i];
		
			$post_type = 'page';
			$post = new stdClass;
			$post->ID = $item->id;
			$post->post_author = '';
			$post->post_date = '';
			$post->post_date_gmt = '';
			$post->post_password = '';
			$post->post_name = $item->title;		// エンコーディングが必要?
			$post->post_type = $post_type;
			$post->post_status = 'publish';
			$post->to_ping = '';
			$post->pinged = '';
	/*		$post->comment_status = get_default_comment_status( $post_type );
			$post->ping_status = get_default_comment_status( $post_type, 'pingback' );
			$post->post_pingback = get_option( 'default_pingback_flag' );
			$post->post_category = get_option( 'default_category' );*/
	//		$post->page_template = 'default';
			$post->post_parent = $item->parentId;		// 親ID
			$post->menu_order = 0;
			// メニュー項目作成用
			$post->title = $item->title;
			$post->url = $item->flink;
			if ($item->browserNav){
				$post->target = '_blank';
			} else {
				$post->target = '';
			}
			// Magic3設定値追加
			$post->post_title = $item->title;
			$post->post_content = '';
			$post->guid = $item->flink;	// 詳細画面URL
			$post->active = $item->active;
			$post->filter = 'raw';
			
			$wpPostObj = new WP_Post($post);
			$menuItems[] = $wpPostObj;
		}
		return $menuItems;
	}
	/**
	 * [WordPressテンプレート用API]現在のページのメニュー項目のタイトルを取得
	 *
	 * @return string     				タイトル名
	 */
	function getActiveMenuItemTitle()
	{
/*		$title = '';
		for ($i = 0; $i < count($this->menuTree); $i++){
			$item = $this->menuTree[$i];
			if ($item->active) $title = $item->title;
		}
		return $title;*/
		return $this->activeMenuItemTitle;
	}
	/**
	 * メニューツリー作成
	 *
	 * @param string	$menuId		メニューID
	 * @param int		$parentId	親メニュー項目ID
	 * @param int		$level		階層数
	 * @param bool		$hasSelectedChild	現在選択状態の子項目があるかどうか
	 * @param array     $parentTree	現在の階層パス
	 * @return 			なし
	 */
	function _createMenu($menuId, $parentId, $level, &$hasSelectedChild, &$parentTree)
	{
		static $index = 0;		// インデックス番号
		$hasSelectedChild = false;

		// メニューの階層を制限
		if ($level >= self::MAX_MENU_TREE_LEVEL) return '';
		
		if ($this->db->getChildMenuItems($menuId, $parentId, $this->langId, $this->now, $rows)){
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
				$menuItem->parentId = $parentId;
				$menuItem->level = $level + 1;
				$menuItem->active = false;
				$menuItem->parent = false;
/*				// 階層作成用
				$menuItem->deeper = false;
				$menuItem->shallower = false;
				$menuItem->level_diff = 0;
				$menuTreeCount = count($this->menuTree);
				if ($menuTreeCount > 0){		// 前データがあるときは取得
					$menuLastItem = $this->menuTree[$menuTreeCount -1];
					$menuLastItem->deeper = ($menuItem->level > $menuLastItem->level);
					$menuLastItem->shallower = ($menuItem->level < $menuLastItem->level);
					$menuLastItem->level_diff = $menuLastItem->level - $menuItem->level;
				}*/
									
				// 非表示のときは処理を飛ばす
				if (!$row['md_visible']) continue;
				
				// ユーザ制限がある場合はログイン状態をチェック
				if ($row['md_user_limited'] && !$this->currentUserLogined) continue;
		
				// リンク先のコンテンツの表示状況に合わせる
				if ($row['md_content_type'] == M3_VIEW_TYPE_CONTENT){		// 汎用コンテンツの場合
					// ログインユーザに表示制限されている場合はメニューを追加しない
					if (!empty($row['cn_user_limited']) && !$this->currentUserLogined) continue;
				}
				
				// リンク先の作成
				$linkUrl = $row['md_link_url'];
				$linkUrl = $this->getUrl($linkUrl, true/*リンク用*/);
				if (empty($linkUrl)) $linkUrl = '#';
				
				// 選択状態の設定
				if ($this->_checkMenuItemUrl($linkUrl)){
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
				
				// 選択中であればメニュー項目のタイトルを取得
				if ($menuItem->active) $this->activeMenuItemTitle = $title;
				
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
						$menuText = $this->_createMenu($menuId, $row['md_id'], $level + 1, $hasSelectedChild, $parentTree);
						
						// 階層を戻す
						array_pop($parentTree);
						
						// 子項目が選択中のときは「active」に設定
						if ($hasSelectedChild) $classArray[] = 'active';

						// 先頭に「parent」クラスを追加
						array_unshift($classArray, 'parent');
						break;
					case 2:			// テキストのとき
						break;
					case 3:			// セパレータのとき
						// Joomla用メニューデータ作成
						$menuItem->type = 'separator';
						$menuItem->title = $title;
						$menuItem->flink = '';
						
						// ##### Joomla用メニュー階層更新 #####
						$this->menuTree[] = $menuItem;
						break;
				}
				
/*				$menuTreeCount = count($this->menuTree);
				if ($menuTreeCount > 0){		// 前データがあるときは取得
					$menuLastItem = $this->menuTree[$menuTreeCount -1];
					$menuLastItem->deeper = (1 > $menuLastItem->level);
					$menuLastItem->shallower = (1 < $menuLastItem->level);
					$menuLastItem->level_diff = $menuLastItem->level - 1;
				}*/
			}
		}
	}
	/**
	 * メニュー項目の選択条件をチェック
	 *
	 * @param string $url	チェック対象のURL
	 * @return bool			true=アクティブ、false=非アクティブ
	 */
	function _checkMenuItemUrl($url)
	{
		$currentUrl = $this->gEnv->getCurrentRequestUri();
		
		// 同じURLのとき
		if ($url == $currentUrl) return true;
		
		// URLを解析
		$queryArray = array();
		$parsedUrl = parse_url($url);
		if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $queryArray);		// クエリーの解析

		// ルートかどうかチェック(クエリー文字列なし)
		if ($this->_isRootUrl($url)){
			$parsedUrl = parse_url($currentUrl);
			if (empty($parsedUrl['query'])){		// クエリ文字列がないことが条件。「#」はあっても良い。
				// ページサブIDで比較
				if ($this->gEnv->getCurrentPageSubId() == $this->gEnv->getDefaultPageSubId()) return true;
			}
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
	function _isRootUrl($url)
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
