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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: help_pagedef.php 3866 2010-11-29 09:02:05Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_pagedef extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## 画面作成 ##########
		$helpData = array(
			'pagedef_page_id' => array(	
				'title' =>	$this->_('Page Id'),			// ページID
				'body'	=>	$this->_('Page is identified by page id. Page id is added to url as parameter. Unpublish page is not published to users without site administration.')		// ページを定義するためのIDです。IDはURLに付加されます。非公開項目は管理権限のない一般ユーザには公開されないページです。
			),
			'pagedef_page_sub_id' => array(	
				'title' =>	$this->_('Page Sub Id'),			// ページサブID
				'body'	=>	$this->_('Page is identified by page id. Page id is added to url as parameter.')		// ページを定義するための補助IDです。IDはURLに付加されます。
			),
			'pagedef_preview_url' => array(	
				'title' =>	$this->_('URL'),			// URL
				'body'	=>	$this->_('URL in preview tab.')		// プレビューに表示しているページへのURLです。
			),
			'pagedef_detail_btn' => array(	
				'title' =>	$this->_('Detail Button'),			// 詳細ボタン
				'body'	=>	$this->_('Show page definition detail.')		// ページ定義の詳細を表示します。
			),
			'pagedef_maximize_btn' => array(	
				'title' =>	$this->_('Maximize Button'),			// 最大化ボタン
				'body'	=>	$this->_('Maximize layout tab. If go back to minimized tab, push down ESC key. If maximize, push down ESC key.')		// レイアウト画面を最大化します。画面を元に戻すにはESCキーを押します。ESCキーでも最大化できます。
			),
			'pagedef_preview_btn' => array(	
				'title' =>	$this->_('Preview in other window'),			// 別画面でプレビュー
				'body'	=>	$this->_('Preview page in other window.')		// 実際に表示される画面を別ウィンドウで表示します。
			),
			'pagedef_default_template' => array(	
				'title' =>	$this->_('Default Template'),			// デフォルトテンプレート
				'body'	=>	$this->_('Select default template for site.')		// デフォルトで設定されるデザインテンプレートを指定します。
			),
			'pagedef_change_template_btn' => array(	
				'title' =>	$this->_('Change Template'),			// テンプレート変更
				'body'	=>	$this->_('Change template.')		// デフォルトのテンプレートを変更します。
			),
			'pagedef_template_img' => array(	
				'title' =>	$this->_('Template Image'),			// テンプレート表示イメージ
				'body'	=>	$this->_('The template image if site on window.')		// テンプレートを設定した場合の画面の表示イメージです。
			),
			'pagedef_layout' => array(	
				'title' =>	$this->_('Page Layout'),			// 画面レイアウト
				'body'	=>	$this->_('<strong>Widget Layout</strong> - Create page with layouting widgets. Get widgets from widget list by mouse drag & drops. Layout widgets by dropping on any position. If you click on close box on the top right position, you can delete widget from layout page. The widget on layout page can move between any positions. If you click on miximize button or push down ESC key, you can maximize layout page. If pushing down ESC key, go back to former state.<br /><strong>Widget Context Menu</strong> - If clicking on the widget by mouse right button, Widget context menu shows. You can configure widgets and delete widgets by using the context menu. The widget with shared attribute always shows on every page. The shared widget shows with red color.')		// (ウィジェットの配置)<br />テンプレートにウィジェットを配置し、画面を作成します。ウィジェット一覧からマウスドラッグでウィジェットを取り出します。レイアウト画面の任意のポジション位置にドロップするとウィジェットが配置できます。レイアウト画面からウィジェットを削除するには、削除するウィジェットの右上のクローズボックスをクリックします。一度ドロップしたウィジェットは、ドラッグドロップでページ上の移動が可能です。最大化ボタンまたはESCキーでレイアウト画面が最大化できます。最大化状態から元に戻るにはESCキーを押します。<br />(ウィジェットコンテキストメニュー)<br />ウィジェットをマウス右クリックするとコンテキストメニューが表示されます。ウィジェットの設定を行ったり、ウィジェットの削除を行います。「ページ共通」属性を設定したウィジェットは、ページに関わらず常に表示されます。(赤色表示)
			),
			'pagedef_refresh' => array(	
				'title' =>	$this->_('Refresh Window'),			// 画面再表示
				'body'	=>	$this->_('Refresh layout page or preview.')		// レイアウトまたはプレビュー画面を再表示します。
			),
			'pagedef_position_block' => array(	
				'title' =>	$this->_('Position'),			// ポジション名
				'body'	=>	$this->_('The position is block name for layouting widgets in design template.')		// デザインテンプレート内でのウィジェットの配置ブロックを指定します。
			),
			'pagedef_position_index' => array(	
				'title' =>	$this->_('Order'),			// 表示順
				'body'	=>	$this->_('The order is the widget order in position block.')		// ポジションブロック内でのウィジェットの表示順を指定します。
			),
			'pagedef_widget_config_id' => array(	
				'title' =>	$this->_('Config ID'),			// 定義ID
				'body'	=>	$this->_('The Config ID is used for widget if needed.')		// 個別のウィジェットの設定IDです。設定が必要な場合のみ設定可能になります。
			),
			'pagedef_widget_visible' => array(	
				'title' =>	$this->_('Visible'),			// ウィジェット表示制御
				'body'	=>	$this->_('Control widget visible status.')		// ウィジェットの表示、非表示を制御します。
			),
			'pagedef_widget_common' => array(	
				'title' =>	$this->_('Shared'),			// ページ共通属性
				'body'	=>	$this->_('The Shared means widget shared status by pages. If widget has shared attribute, it always shows on evey page.')		// 同ページIDで、サブページIDに関わらずウィジェットを表示するかどうかを指定します。
			),
		);
		return $helpData;
	}
}
?>
