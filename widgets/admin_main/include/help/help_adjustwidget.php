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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()	. '/helpConv.php' );

class help_adjustwidget extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## ウィジェット表示調整 ##########
		$helpData = array(
			'adjustwidget_config' => array(	
				'title' =>	$this->_('Widget Common Config'),	// ウィジェット共通設定
				'body' =>	$this->_('Common config with all widgets.')		// すべてのウィジェットで共通する設定です。
			),
			'adjustwidget' => array(	
				'title' =>	$this->_('Adjust Widget Title and Contents'),	// ウィジェットタイトル、位置調整
				'body' =>	$this->_('Adjust widget title and widget contents.')		// ウィジェットのタイトルやウィジェットの表示内容の位置調整を行います。
			),
			'adjustwidget_view' => array(	
				'title' =>	$this->_('View Control'),	// ウィジェット表示制御
				'body' =>	$this->_('Control widget view.')		// ウィジェットの表示制御を行います。
			),
			'adjustwidget_title' => array(	
				'title' =>	$this->_('Title'),	// タイトル
				'body' =>	$this->_('Configure the title on the top of widget. If you set blank for the title, default title is displayed. You can control the title visible status by checking \'Visible\' checkbox. This configure takes first priority over other configures.')		// ウィジェットの上部に表示されるタイトル名を設定します。空に設定した場合はデフォルトのタイトル名が表示されます。「表示」チェックボックスでタイトルの表示、非表示の制御を行います。ここでの設定は他のすべての設定に優先します。
			),
			'adjustwidget_style' => array(	
				'title' =>	$this->_('Style'),	// スタイル
				'body' =>	$this->_('Adjust widget contents style.<br /><strong>Contents Margin</strong> - Adjust widget contents margin. If you set blank for margin field, the margin has no value.<br /><strong>Contents Position</strong> - Adjust widget contents position.<br /><strong>Remove list marker</strong> - If list has the marker, remove it.')		// ウィジェットの表示内容のスタイルを調整します。マージン - ウィジェットの表示内容のマージンを設定します。空に設定したフィールドは指定なしになります。テキスト表示位置 - ウィジェットの表示内容のテキストの位置を設定します。リストのマーカーを削除 - リストがマーカー付きの場合マーカーを削除します。
			),
			'adjustwidget_render' => array(	
				'title' =>	$this->_('Render'),	// 描画処理
				'body' =>	$this->_('If you check \'Render by Joomla! style\' checkbox, the widget is added border line and title. If you uncheck it, the widget is displayed with plane text.')		// 「Joomla!スタイルの描画処理」をオンにするとウィジェットの周囲の枠やタイトルが付加されます。チェックをはずした場合はウィジェットのプレーンな出力が表示されます。
			),
			'adjustwidget_top_content' => array(	
				'title' =>	$this->_('Additional Top Content'),	// 補助コンテンツ(上)
				'body' =>	$this->_('Additional content on the top of the widget.')		// ウィジェットの上部に表示する補助コンテンツです。
			),
			'adjustwidget_bottom_content' => array(	
				'title' =>	$this->_('Additional Bottom Content'),	// 補助コンテンツ(下)
				'body' =>	$this->_('Additional content on the bottom of the widget.')		// ウィジェットの下部に表示する補助コンテンツです。
			),
			'adjustwidget_readmore' => array(	
				'title' =>	$this->_('Readmore Button'),	// もっと読むボタン
				'body' =>	$this->_('If you check \'Visible\' checkbox, \'Read more Button\' is displayed. Input the button \'Title\' and the link \'Url\'.')		// 「表示」をオンにするともっと読むボタン表示されます。ボタンのタイトルとリンク先URLを入力します。
			),
			'adjustwidget_shared' => array(	
				'title' =>	$this->_('Global Attribute'),	// グローバル属性
				'body' =>	$this->_('Control global attribute of widget. If its attribute is on, the widget always shows on all pages.<br />When its attribute is on, the widget does not show on the exception page.')		// ウィジェットのグローバル属性を設定します。共通属性をオンにするとすべてのページでウィジェットが常時表示されます。グローバル属性がオンの場合、例外ページで選択されたページ上にはウィジェットは表示されません。
			),
			'adjustwidget_term' => array(	
				'title' =>	$this->_('View Term'),	// 表示期間
				'body' =>	$this->_('Control visible term of widget.')		// ウィジェットの表示期間を設定します。
			),
			'adjustwidget_option' => array(	
				'title' =>	$this->_('View Option'),	// 表示オプション
				'body' =>	$this->_('Control widget view styles.<br /><strong>View Type</strong> - <strong>Always</strong>: the widget always shows if user is or is not in login. <strong>When user in login</strong>: the widget shows if user is in login. <strong>When user not in login</strong>: the widget shows if user is not in login. ')		// ウィジェットの表示スタイルを制御します。<br />「表示タイプ」-「常時表示」(ユーザのログイン状態に関わらず常にウィジェットを表示)、「ログイン時のみ表示」(ユーザがログインしている場合のみウィジェットを表示)、「非ログイン時のみ表示」(ユーザがログインしていない場合のみウィジェットを表示)
			),
		);
		return $helpData;
	}
}
?>
