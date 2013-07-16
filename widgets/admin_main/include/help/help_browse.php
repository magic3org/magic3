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
 * @version    SVN: $Id: help_browse.php 5830 2013-03-15 13:44:37Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_browse extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## リソースブラウズ ##########
		$helpData = array(
			'resbrowse' => array(	
				'title' =>	$this->_('Resource Browse'),	// リソースブラウズ
				'body' =>	$this->_('Operate files with thumbnail.')		// サムネール画像を見ながら画像ファイルの処理が行えます。
			),
		);
		// ########## ファイルブラウズ ##########
		$helpData = array_merge($helpData, array(
			'filebrowse' => array(	
				'title' =>	$this->_('File Browse'),	// ファイルアップロード
				'body' =>	$this->_('Upload files at once.')		// 複数のファイルを一度にアップロードできます。
			),
			'filebrowse_path' => array(	
				'title' =>	$this->_('Path'),	// パス
				'body' =>	$this->_('Show current directory.')		// 現在のディレクトリパスを示します。
			),
			'filebrowse_filename' => array(	
				'title' =>	$this->_('Filename'),	// ファイル名
				'body' =>	$this->_('Show file or directory name.')		// ファイル名、ディレクトリ名を示します。
			),
			'filebrowse_size' => array(	
				'title' =>	$this->_('Size'),	// サイズ
				'body' =>	$this->_('Show byte counts of file.')		// ファイルのサイズを示します。
			),
			'filebrowse_permission' => array(	
				'title' =>	$this->_('Permission'),	// パーミッション
				'body' =>	$this->_('Show permission of file or directory.')		// ファイル、ディレクトリのパーミッションを示します。
			),
			'filebrowse_owner' => array(	
				'title' =>	$this->_('Owner'),	// 所有者
				'body' =>	$this->_('Show owner of file or directory.')		// ファイル、ディレクトリの所有者を示します。
			),
			'filebrowse_group' => array(	
				'title' =>	$this->_('Group'),	// グループ
				'body' =>	$this->_('Show group owner of file or directory.')		// ファイル、ディレクトリのグループ所有者を示します。
			),
			'filebrowse_date' => array(	
				'title' =>	$this->_('Update Date'),	// 更新日時
				'body' =>	$this->_('Show update date of file or directory.')		// ファイル、ディレクトリの更新日時を示します。
			),
			'filebrowse_upload_file' => array(	
				'title' =>	$this->_('Upload File'),	// ファイルアップロード
				'body' =>	$this->_('Uplod files in this area. Show upload status in uploading files.')		// ファイルのアップロードはここから行います。ファイルアップロード時はアップロード状況が表示されます。
			),
			'filebrowse_create_directory' => array(	
				'title' =>	$this->_('Create Directory'),	// ディレクトリ作成
				'body' =>	$this->_('Create directory in this area.')		// ディレクトリの作成はここから行います。
			),
			'filebrowse_del_btn' => array(	
				'title' =>	$this->_('Delete Button'),	// 削除ボタン
				'body' =>	$this->_('Delete files.<br />Select the file by using the left checkbox.<br />You can delete files and directory in \'resource\' directory.')		// 選択されているファイル項目を削除します。<br />メニュー項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。<br />「resource」ディレクトリ以下のファイルのみ削除可能です。
			),
		));
		return $helpData;
	}
}
?>
