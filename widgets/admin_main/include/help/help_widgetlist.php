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
 * @version    SVN: $Id: help_widgetlist.php 3871 2010-12-01 10:08:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_widgetlist extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## ウィジェット一覧 ##########
		$helpData = array(
			'widgetlist' => array(	
				'title' =>	$this->_('Widget List'),			// ウィジェット一覧
				'body' =>	$this->_('The list is available widgets in this system. Select widget type for pc or mobile, smartphone.')		// システムで利用可能なウィジェットの一覧です。PC用またはウィジェットか携帯用、スマートフォン用のウィジェットを選択します。
			),
/*			'widgetlist_type' => array(	
				'title' =>	$this->_('Widget Type'),			// ウィジェットタイプ
				'body' =>	$this->_('Select widget type for pc or mobile, smartphone.')		// PC用またはウィジェットか携帯用、スマートフォン用のウィジェットを選択します。
			),*/
			'widgetlist_install_dir' => array(	
				'title' =>	$this->_('Widget Install Directory'),			// ウィジェットインストールディレクトリ
				'body' =>	$this->_('Widgets installed in the directory by name of widget ID.')		// ウィジェットのインストールディレクトリです。この配下に「ウィジェットID」のディレクトリ名で個々のウィジェットが格納されます。
			),
			'widgetlist_id' => array(	
				'title' =>	$this->_('Widget ID'),			// ウィジェットID
				'body' =>	$this->_('Widget is identified with Widget ID. Widget ID is same as directory name.')		// ウィジェットのIDです。ウィジェットのディレクトリ名と同一です。
			),
			'widgetlist_name' => array(	
				'title' =>	$this->_('Widget Name'),			// ウィジェット名前
				'body' =>	$this->_('The name of widget.')		// ウィジェットの名前です。
			),
			'widgetlist_available' => array(	
				'title' =>	$this->_('Available'),			// 配置可能
				'body' =>	$this->_('The Available attribute allows widget listing on menu. The widget already on page keeps on.')		// ウィジェットを配置用の選択メニューに表示するかどうかを指定します。すでにページに配置されているウィジェットはそのまま維持されます。
			),
			'widgetlist_active' => array(	
				'title' =>	$this->_('Enable'),			// 実行可能
				'body' =>	$this->_('The Active attribute allows launching widget. You can stop the widget launched.')		// ウィジェットの実行を許可します。特定のウィジェットを緊急停止する場合に使用します。
			),
			'widgetlist_act' => array(	
				'title' =>	$this->_('Operation'),			// 操作
				'body' =>	$this->_('You can operate below.<br /><strong>Update</strong> - Update line.<br /><strong>Delete</strong> - Delete widget.<br /><strong>Config</strong> - Show config window of widget. The widget config window can be shown in layout page or top menu.<br /><strong>Download</strong> - Download widget with zip format compressed. The compressed file can be upload by Widget Upload area.')		// 各種操作を行います。<br />●更新<br />この一覧で変更した値を保存します。<br />●削除<br />ウィジェットをシステムから削除します。<br />●設定<br />ウィジェットの設定画面を表示します。ウィジェットの設定画面はページ作成画面やトップメニュー画面からも表示可能です。<br />●ダウンロード<br />ウィジェットをZIP圧縮形式でダウンロードします。このファイルはそのまま「ウィジェットアップロード」からシステムへインストールできる形式です。
			),
			'widgetlist_upload' => array(	
				'title' =>	$this->_('Widget Upload'),			// ウィジェットアップロード
				'body' =>	$this->_('Upload widget file compressed with zip format, install in the system. Error is occured if there is the same widget ID. <br />If \'Replace widget if exists.\' is checked, delete the widget and install new widget.')		// ZIP形式のウィジェットファイルをアップロードし、システムにウィジェットをインストールします。同じIDのウィジェットがすでに存在する場合はエラーになります。<br />「ウィジェットが存在する場合は置き換え」にチェックが入っている場合、既存のウィジェットを削除した後、ウィジェットをインストールします。
			),
/*			'widgetlist_detail_check' => array(	
				'title' =>	$this->_('Show detail'),			// 詳細表示
				'body' =>	$this->_('Show detail list if checked.')		// チェックを入れると一覧が詳細表示できます。
			),*/
			'widgetlist_reload_dir_btn' => array(	
				'title' =>	$this->_('Reload directory'),			// ディレクトリ再読み込み
				'body' =>	$this->_('Reload the directory and automatically install unlisted widget.')		// ウィジェットディレクトリを再読み込みして、一覧に表示されていないウィジェットを自動的にインストールします。
			)
		);
		return $helpData;
	}
}
?>
