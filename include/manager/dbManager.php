<?php
/**
 * DB制御マネージャー
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/specificDb.php');

class DbManager extends Core
{
	private $db;						// DBオブジェクト
	private $specificDb;				// DBオブジェクト
	private $outputPgUndefinedTableMsg;	// PostgreSQLの「Undefined table」のエラーメッセージを出力するかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		$this->specificDb = new specificDb();
	}
	/**
	 * システム管理DB初期化スクリプトファイルを実行
	 *
	 * @param string  $filename	スクリプトファイル名、スクリプトディレクトリからの相対パス
	 * @param array	$errors		エラーメッセージ
	 * @return bool				true=正常終了、false=異常終了
	 */
	function execInitScriptFile($filename, &$errors)
	{
		global $gEnvManager;
		
		// DBの属性を取得
		$this->db->init();
		
		$scriptPath = '';
		if ($this->db->getDbType() == M3_DB_TYPE_MYSQL ||		// MySQLの場合
			$this->db->getDbType() == M3_DB_TYPE_PGSQL){	// PostgreSQLの場合
			$scriptPath = $gEnvManager->getSqlPath() . '/' . $filename;
		} else {
			$errors = array();
			array_push($errors, 'DBタイプが不正です');
			return false;
		}

		// スクリプトファイルが存在するときはスクリプトファイルを実行
		if (file_exists($scriptPath)){
			$this->db->displayErrMessage(false);		// エラーメッセージの画面出力を抑止
			$this->db->execSqlScriptWithConvert($scriptPath);
		
			$errors = $this->db->getErrMsg();
			if ($this->db->getDbType() == M3_DB_TYPE_PGSQL && !$this->outputPgUndefinedTableMsg){	// PostgreSQLの場合で「Undefined table」のエラーメッセージを出さないとき
				// 「Undefined table」のエラーメッセージを削除
				$modifiedErrors = array();
				for ($i = 0; $i < count($errors); $i++){
					if (!strpos($errors[$i], 'Undefined table:')) $modifiedErrors[] = $errors[$i];
				}
				$errors = $modifiedErrors;
			}
			if (count($errors)){
				return false;
			} else {
				return true;
			}
		} else {
			$errors = array();
			array_push($errors, 'スクリプトファイルが見つかりません(ファイル名=' . $filename . ')');
			return false;
		}
	}
	/**
	 * トランザクション開始
	 *
	 * @return なし
	 */
	function startTransaction()
	{
		$this->db->startTransaction();
	}
	/**
	 * トランザクション終了
	 *
	 * @return bool		true=コミットまで完了、false=エラー発生の場合ロールバックして値を返す
	 */
	function endTransaction()
	{
		return $this->db->endTransaction();
	}
	/**
	 * システム管理DB初期化スクリプトファイルを実行
	 *
	 * @param string   $scriptType	スクリプトタイプ
	 * @param array	$errors		エラーメッセージ
	 * @return bool				true=正常終了、false=異常終了
	 */
	function execInitScript($scriptType, &$errors)
	{
		global $gEnvManager;
		
		// DBのバージョン取得
		$this->db->init();		// DBの属性を取得
		$dbVersion = $this->db->getDbVersion();
		$ver = explode('.', $dbVersion);
		$majorVer = $ver[0];		// メジャーバージョン
		$minorVer = $ver[1];		// マイナーバージョン
		
		/*
		switch ($scriptType){
			case 0:			// システム基本
				$scriptName = '_create_base.sql';
				break;
			case 1:			// システム標準
				$scriptName = '_create_standard.sql';
				break;
			case 2:			// Eコマース
				$scriptName = '_create_ec.sql';
				break;
		}*/
		$scriptName = '_create_' . $scriptType . '.sql';
		$scriptPath = '';
		if ($this->db->getDbType() == M3_DB_TYPE_MYSQL ||		// MySQLの場合
			$this->db->getDbType() == M3_DB_TYPE_PGSQL){	// PostgreSQLの場合
			$scriptPath = $gEnvManager->getSqlPath() . '/' . M3_DB_TYPE_MYSQL . $scriptName;
		} else {
			$errors = array();
			array_push($errors, 'DBタイプが不正です');
			return false;
		}
		// スクリプトファイルが存在するときはスクリプトファイルを実行
		if (file_exists($scriptPath)){
			$this->db->displayErrMessage(false);		// エラーメッセージの画面出力を抑止
			$this->db->execSqlScriptWithConvert($scriptPath);
		
			$errors = $this->db->getErrMsg();
			if ($this->db->getDbType() == M3_DB_TYPE_PGSQL && !$this->outputPgUndefinedTableMsg){	// PostgreSQLの場合で「Undefined table」のエラーメッセージを出さないとき
				// 「Undefined table」のエラーメッセージを削除
				$modifiedErrors = array();
				for ($i = 0; $i < count($errors); $i++){
					if (!strpos($errors[$i], 'Undefined table:')) $modifiedErrors[] = $errors[$i];
				}
				$errors = $modifiedErrors;
			}
			if (count($errors)){
				return false;
			} else {
				return true;
			}
		} else {
			$errors = array();
			array_push($errors, 'スクリプトファイルが見つかりません');
			return false;
		}
	}
	/**
	 * SQLスクリプトファイルを実行
	 *
	 * @param string $scriptPath	スクリプトファイルパス
	 * @param array	$errors			エラーメッセージ
	 * @return bool					true=正常終了、false=異常終了
	 */
	function execScript($scriptPath, &$errors)
	{
		// スクリプトファイルが存在するときはスクリプトファイルを実行
		if (file_exists($scriptPath)){
			$this->db->execSqlScript($scriptPath);
		
			$errors = $this->db->getErrMsg();
			if (count($errors)){
				return false;
			} else {
				return true;
			}
		} else {
			$errors = array();
			array_push($errors, 'スクリプトファイルが見つかりません');
			return false;
		}
	}
	/**
	 * MySQL用のSQLスクリプトファイルを各DB用に変換しながら実行
	 *
	 * @param string $scriptPath	スクリプトファイルパス
	 * @param array	$errors			エラーメッセージ
	 * @return bool					true=正常終了、false=異常終了
	 */
	function execScriptWithConvert($scriptPath, &$errors)
	{
		// スクリプトファイルが存在するときはスクリプトファイルを実行
		if (file_exists($scriptPath)){
			$this->db->displayErrMessage(false);		// エラーメッセージの画面出力を抑止
			$this->db->execSqlScriptWithConvert($scriptPath);
		
			$errors = $this->db->getErrMsg();
			if ($this->db->getDbType() == M3_DB_TYPE_PGSQL && !$this->outputPgUndefinedTableMsg){	// PostgreSQLの場合で「Undefined table」のエラーメッセージを出さないとき
				// 「Undefined table」のエラーメッセージを削除
				$modifiedErrors = array();
				for ($i = 0; $i < count($errors); $i++){
					if (!strpos($errors[$i], 'Undefined table:')) $modifiedErrors[] = $errors[$i];
				}
				$errors = $modifiedErrors;
			}
			//$this->db->execSqlScript($scriptPath);
			//
			//$errors = $this->db->getErrMsg();
			if (count($errors)){
				return false;
			} else {
				return true;
			}
		} else {
			$errors = array();
			array_push($errors, 'スクリプトファイルが見つかりません');
			return false;
		}
	}
	/**
	 * PostgreSQLの「Undefined table」のエラーメッセージを出力するかどうか
	 *
	 * @param bool $status		出力状況
	 */
	function outputPgUndefinedTableMsg($status)
	{
		$this->outputPgUndefinedTableMsg = $status;
	}
	/**
	 * テーブルのデータ使用量を取得
	 *
	 * @param string $tableName		テーブル名
	 * @return int 					データ使用量バイトサイズ
	 */
	function getTableDataSize($tableName)
	{
		$size = 0;
		$ret = $this->specificDb->getTableDataSize($tableName, $row);
		if ($ret) $size = $row['size'];
		return $size;
	}
	/**
	 * データベースのデータ使用量を取得
	 *
	 * @return int 			データ使用量バイトサイズ
	 */
	function getDbDataSize()
	{
		$size = 0;
		$ret = $this->specificDb->getDbDataSize($row);
		if ($ret) $size = $row['size'];
		return $size;
	}
	/**
	 * データベースをバックアップ
	 *
	 * @param string $filename		バックアップファイル名
	 * @return bool					true=正常、false=異常
	 */
	function backupDb($filename)
	{
		$ret = $this->specificDb->backupDb($filename);
		return $ret;
	}
}
?>
