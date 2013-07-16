<?php
/**
 * インストール情報クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: installInfo_default.php 3640 2010-09-27 09:55:04Z fishbone $
 * @link       http://www.magic3.org
 */
class InstallInfo
{
	private $createTableScripts;			// テーブル作成スクリプト
	private $insertTableScripts;			// データインストールスクリプト
	private $updateTableScripts;			// テーブル更新スクリプト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 実行SQLスクリプトファイルの定義
		$this->createTableScripts = array(	array(	'filename' 		=> 'create_base.sql',					// ファイル名
													'name'			=> 'システム基本テーブル作成',				// 表示名
													'description'	=> 'システムで最小限必要なテーブルの作成'),	// 説明
											array(	'filename' 		=> 'create_std.sql',					// ファイル名
													'name'			=> 'システム標準テーブル作成',				// 表示名
													'description'	=> 'システムを通常使用するのに必要なテーブルの作成'),	// 説明
											array(	'filename' 		=> 'create_ec.sql',					// ファイル名
													'name'			=> 'Eコマース用テーブル',				// 表示名
													'description'	=> 'Eコマース用テーブルの作成'));	// 説明

		$this->insertTableScripts = array(	array(	'filename' 		=> 'insert_base.sql',					// ファイル名
													'name'			=> 'システム基本データ登録',				// 表示名
													'description'	=> 'システムで最小限必要なデータの登録'),	// 説明
											array(	'filename' 		=> 'insert_std.sql',					// ファイル名
													'name'			=> 'システム標準データ登録',				// 表示名
													'description'	=> 'システムを通常使用するのに必要なデータの登録'));	// 説明
													
		$this->updateTableScripts = array(	array(	'filename' 		=> 'update_widgets.sql',					// ファイル名
													'name'			=> 'ウィジェット情報更新',				// 表示名
													'description'	=> 'ウィジェットの更新に合わせてウィジェット情報を更新'));	// 説明
	}
	/**
	 * テーブル作成スクリプトを取得
	 *
	 * @return array		スクリプト情報
	 */
	function getCreateTableScripts()
	{
		return $this->createTableScripts;
	}
	/**
	 * テーブル初期化スクリプトを取得
	 *
	 * @return array		スクリプト情報
	 */
	function getInsertTableScripts()
	{
		return $this->insertTableScripts;
	}
	/**
	 * テーブル更新スクリプトを取得
	 *
	 * @return array		スクリプト情報
	 */
	function getUpdateTableScripts()
	{
		return $this->updateTableScripts;
	}
}
?>
