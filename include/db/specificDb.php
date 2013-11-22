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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/baseDb.php');

class specificDb extends BaseDb
{
	protected $_dbName;		// DB名
	const BACKUP_CMD = 'mysqldump';			// DBバックアップコマンド

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
	/**
	 * データベースをバックアップ
	 *
	 * @param string $filename		バックアップファイル名
	 * @return bool					true=正常、false=異常
	 */
	function backupDb($filename)
	{
		$ret = false;
		$dbType = $this->getDbType();
		switch ($dbType){
			case M3_DB_TYPE_MYSQL:		// MySQLの場合
				$cmd = self::BACKUP_CMD . ' --opt -u' . $this->_connect_user . ' -p' . $this->_connect_password . ' ' . $this->_dbName . ' --single-transaction | zip > ' . $filename;
				$ret = $this->_procExec($cmd);
				if ($ret == 0){
					$ret = true;
				} else {
					$ret = false;
				}
				break;
			case M3_DB_TYPE_PGSQL:		// PostgreSQLの場合

				break;
		}
		return $ret;
	}
	/**
	 * シェルコマンドを実行
	 *
	 * @param string $command		コマンド
	 * @param array $output			標準出力
	 * @param array $errorOutput	標準エラー出力
	 * @return int					プロセス終了コード
	 */
	protected function _procExec($command, &$output = null, &$errorOutput = null)
	{
		$retVal = -1;		// 終了コード
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w")   // stderr
		);

		$process = proc_open($command, $descriptorspec, $pipes, null, null);
		
		if (is_resource($process)){
			fclose($pipes[0]);

			$output = stream_get_contents($pipes[1]);
			$errorOutput = stream_get_contents($pipes[2]);

			fclose($pipes[1]);
			fclose($pipes[2]);
			
			$retVal = proc_close($process);
		}
		return $retVal;
	}
}
?>
