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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: help_templist.php 4834 2012-04-10 23:42:43Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_templist extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## テンプレート一覧 ##########
		$helpData = array(
			'templist' => array(	
				'title' =>	$this->_('Template List'),			// テンプレート一覧
				'body' =>	$this->_('The list is available templates in this system. Use bottom area of Template Install if you install template.')		// システムで利用可能なテンプレートの一覧です。テンプレートのインストールはこの画面の最下部の「テンプレートアップロード」から行います。
			),
			'templist_type' => array(	
				'title' =>	$this->_('Template Type'),			// テンプレートタイプ
				'body' =>	$this->_('Select template type for pc or mobile, smartphone.')		// PC用テンプレートか携帯用、スマートフォン用のテンプレートを選択します。
			),
			'templist_install_dir' => array(	
				'title' =>	$this->_('Template Install Directory'),			// テンプレートインストールディレクトリ
				'body' =>	$this->_('Templates installed in the directory by name of template ID.')		// テンプレートのインストールディレクトリです。この配下に「テンプレートID」のディレクトリ名で個々のテンプレートが格納されます。
			),
			'templist_id' => array(	
				'title' =>	$this->_('Template ID'),			// テンプレートID
				'body' =>	$this->_('Template is identified with Template ID. Template ID is same as directory name.')		// テンプレートのIDです。テンプレートのディレクトリ名と同一です。
			),
			'templist_name' => array(	
				'title' =>	$this->_('Template Name'),			// テンプレート名前
				'body' =>	$this->_('The name of template.')		// テンプレートの名前です。
			),
			'templist_format' => array(	
				'title' =>	$this->_('Template Format'),			// テンプレート形式
				'body' =>	$this->_('Available formats are below. System default is J15.<br /><strong>J10</strong> - Joomla! v1.0 format.<br /><strong>J15</strong> - Joomla! v1.5 format.<br /><strong>J25</strong> - Joomla! v1.7-v2.5 format.')		// テンプレートの形式です。<br />利用可能な形式は以下の通りです。Magic3では現在J15を標準としています。<br />●J10<br />Joomla! v1.0用テンプレート<br />●J15<br />Joomla! v1.5用テンプレート<br />●J25<br />Joomla! v1.7-v2.5用テンプレート
			),
			'templist_default' => array(	
				'title' =>	$this->_('Default Template'),			// デフォルト
				'body' =>	$this->_('The current selected template.')		// 現在システムで選択されているデフォルトのテンプレートを示します。
			),
			'templist_act' => array(	
				'title' =>	$this->_('Operation'),			// 操作
				'body' =>	$this->_('You can operate below.<br /><strong>Preview</strong> - Preview template with no widgets.<br /><strong>Update</strong> - Update line.<br /><strong>Delete</strong> - Delete template.<br /><strong>Download</strong> - Download template with zip format compressed. The compressed file can be upload by Template Upload area.')		// 各種操作を行います。<br />●プレビュー<br />テンプレートがプレビューできます。<br />●更新<br />この一覧で変更した値を保存します。<br />●削除<br />テンプレートをシステムから削除します。<br />●ダウンロード<br />テンプレートをZIP圧縮形式でダウンロードします。このファイルはそのまま「テンプレートアップロード」からシステムへインストールできる形式です。
			),
			'templist_upload' => array(	
				'title' =>	$this->_('Template Upload'),			// テンプレートアップロード
				'body' =>	$this->_('Upload template file compressed with zip format, install in the system. Error is occured if there is the same template ID.')		// ZIP形式のテンプレートファイルをアップロードし、システムにテンプレートをインストールします。同じIDのテンプレートがすでに存在する場合はエラーになります。
			),
			'templist_detail_check' => array(	
				'title' =>	$this->_('Show detail'),			// 詳細表示
				'body' =>	$this->_('Show detail list if checked.')		// チェックを入れると一覧が詳細表示できます。
			),
			'templist_reload_dir_btn' => array(	
				'title' =>	$this->_('Reload directory'),			// ディレクトリ再読み込み
				'body' =>	$this->_('Reload the directory and automatically install unlisted template.')		// テンプレートディレクトリを再読み込みして、一覧に表示されていないテンプレートを自動的にインストールします。
			)
		);
		return $helpData;
	}
}
?>
