<?php
/**
 * ヘルプリソースファイル
 * index.php
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
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_menudef extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## メニュー定義 ##########
		$helpData = array(
			'menudef_list' => array(	
				'title' =>	$this->_('Menu Definition'),			// メニュー定義
				'body' =>	$this->_('Menu item list in selected menu definition.')		// 選択したメニュー定義のメニュー項目一覧です。
			),
			'menudef_detail' => array(	
				'title' =>	$this->_('Menu Item Detail'),			// メニュー項目詳細
				'body' =>	$this->_('Edit menu item.')		// メニュー項目の設定を編集します。
			),
			'menudef_check' => array(	
				'title' =>	$this->_('Checkbox to Select'),			// 選択用チェックボックス
				'body' =>	$this->_('Select items to edit or delete by using checkboxes.')		// 編集や削除を行う項目を選択します。
			),
			'menudef_name' => array(	
				'title' =>	$this->_('Name'),			// 名前
				'body' =>	$this->_('Definition name for menu item.')		// メニュー項目の名前です。
			),
			'menudef_title' => array(	
				'title' =>	$this->_('Title'),			// タイトル
				'body' =>	$this->_('Configure the different value from \'Name\' value. You can use HTML tags.')		// メニュー項目に「名前」と異なる表示を行う場合に設定します。HTMLタグが使用可能です。
			),
			'menudef_lang' => array(	
				'title' =>	$this->_('Language'),			// 言語
				'body' =>	$this->_('Language for menu item.')		// メニュー項目の対応言語です。
			),
			'menudef_item_type' => array(	
				'title' =>	$this->_('Menu Item Type'),			// メニュー項目タイプ
				'body' =>	$this->_('You can select menu item type below.<br /><strong>Folder</strong> - Item has sub menu items.<br /><strong>Separator</strong> - Separete between menu items.')		// メニュー項目のタイプです。「フォルダ」-サブメニューを持つ項目です。<br />「セパレータ」-区切り項目です。
			),
			'menudef_link_url' => array(	
				'title' =>	$this->_('URL'),			// リンク先URL
				'body' =>	$this->_('Url to link.')		// メニュー項目をクリックしたときに表示されるURLです。
			),
			'menudef_link_type' => array(	
				'title' =>	$this->_('Action'),			// 動作
				'body' =>	$this->_('Select type to open page in the same window or in other window if clicked.')		// メニュー項目をクリックしたときにリンク先が同じウィンドウで表示するか、別ウィンドウで表示するかを指定します。
			),
			'menudef_visible' => array(	
				'title' =>	$this->_('Visible'),			// 表示制御
				'body' =>	$this->_('Control menu item visible status. If menu item is hidden when visible status unchecked.')		// メニュー項目をユーザに公開するかどうかを制御します。非公開に設定の場合はユーザから参照することはできません。
			),
			'menudef_product_head_content' => array(	
				'title' =>	$this->_('Product Head Content'),			// 商品ヘッダコンテンツ
				'body' =>	$this->_('Input contents in \'Product Head\' widget when the category is selected.')		// 商品カテゴリー選択時に「商品ヘッダ」ウィジェットに表示するコンテンツを設定します。
			),
			'menudef_act' => array(	
				'title' =>	$this->_('Operation'),			// 操作
				'body' =>	$this->_('You can operate the actions below.<br /><strong>Edit Contents</strong> - If the link target is contents, edit the contents.')		// 各種操作を行います。<br />●コンテンツを編集<br />メニュー項目のリンク先がコンテンツ表示の場合、表示されるコンテンツの編集を行います。
			),
			'menudef_sel_link' => array(	
				'title' =>	$this->_('Select Link Type'),			// リンク先を選択
				'body' =>	$this->_('Select the link type of menu item. If \'contents\' is selected, you can select the contents to view.')		// リンク先を選択します。「コンテンツ」を選択した場合は、表示するコンテンツを指定します。[任意設定]を選択した場合は、任意のURLにリンク先を設定できます。
			),
			'menudef_menu_layout' => array(	
				'title' =>	$this->_('Menu Layout'),			// メニューレイアウト
				'body' =>	$this->_('Change the order of items by dragging.')		// 項目をドラッグして並び順を変えることができます。
			),
			'menudef_desc' => array(	
				'title' =>	$this->_('Description'),			// 説明
				'body' =>	$this->_('Description about menu item.')		// メニュー項目についての説明です。
			),
			'menudef_new_btn' => array(	
				'title' =>	$this->_('New Button'),			// 新規ボタン
				'body' =>	$this->_('Add menu item.')		// 新規メニュー項目を追加します。
			),
			'menudef_edit_btn' => array(	
				'title' =>	$this->_('Edit Button'),			// 編集ボタン
				'body' =>	$this->_('Edit menu item.<br />Select the menu item by using the left checkbox.')		// 選択されているメニュー項目を編集します。<br />メニュー項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。
			),
			'menudef_del_btn' => array(	
				'title' =>	$this->_('Delete Button'),			// 削除ボタン
				'body' =>	$this->_('Delete menu item.<br />Select the menu item by using the left checkbox.')		// 選択されているメニュー項目を削除します。<br />メニュー項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。
			),
			'menudef_ret_btn' => array(	
				'title' =>	$this->_('Go back Button'),			// 戻るボタン
				'body' =>	$this->_('Go back to menu item list.')		// メニュー項目一覧へ戻ります。
			),
		);
		return $helpData;
	}
}
?>
