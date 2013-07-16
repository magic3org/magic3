<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: specificDb.php 1909 2009-05-21 03:14:52Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/baseDb.php');

class specificDb extends BaseDb
{
	protected $_dbName;		// DB名

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->_dbName = $this->getDbName();
	}
	/**
	 * テーブルのデータ使用量を取得
	 *
	 * @param string  $tableName	テーブル名
	 * @param array $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getTableDataSize($tableName, &$row)
	{
		$ret = false;
		$dbType = $this->getDbType();
		switch ($dbType){
			case M3_DB_TYPE_MYSQL:		// MySQLの場合
				$queryStr  = 'SELECT DATA_LENGTH AS size FROM `information_schema`.`TABLES` ';
				$queryStr .=   'WHERE `TABLE_SCHEMA` = ? ';
				$queryStr .=     'AND TABLE_NAME = ?';
				$ret = $this->selectRecord($queryStr, array($this->_dbName, $tableName), $row);
				break;
			case M3_DB_TYPE_PGSQL:		// PostgreSQLの場合
				$queryStr  = 'SELECT pg_relation_size(\'' . addslashes($tableName) . '\') AS size';
				$ret = $this->selectRecord($queryStr, array(), $row);
				break;
		}
		return $ret;
	}
	/**
	 * データベースのデータ使用量を取得
	 *
	 * @param array $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getDbDataSize(&$row)
	{
		$ret = false;
		$dbType = $this->getDbType();
		switch ($dbType){
			case M3_DB_TYPE_MYSQL:		// MySQLの場合
				/*$queryStr  = 'SELECT DATA_LENGTH AS size FROM `information_schema`.`TABLES` ';
				$queryStr .=   'WHERE `TABLE_SCHEMA` = ? ';
				$queryStr .=     'AND TABLE_NAME = ?';
				$ret = $this->selectRecord($queryStr, array($this->_dbName, $tableName), $row);*/
				break;
			case M3_DB_TYPE_PGSQL:		// PostgreSQLの場合
				$queryStr  = 'SELECT pg_database_size(\'' . addslashes($this->_dbName) . '\') AS size';
				$ret = $this->selectRecord($queryStr, array(), $row);
				break;
		}
		return $ret;
	}
}
?>
