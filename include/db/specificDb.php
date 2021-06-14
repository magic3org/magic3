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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @link       http://magic3.org
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
				$cmd = self::BACKUP_CMD . ' --opt -u ' . $this->_connect_user;
				if (!empty($this->_connect_password)) $cmd .= ' -p ' . $this->_connect_password;
				$cmd .= ' ' . $this->_dbName . ' --single-transaction | gzip > ' . $filename;
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
	 * データベーステーブルをバックアップ
	 *
	 * @param string $tableName		テーブル名
	 * @param string $filename		バックアップファイル名
	 * @return bool					true=正常、false=異常
	 */
	function backupTable($tableName, $filename)
	{
		$ret = false;
		$dbType = $this->getDbType();
		switch ($dbType){
			case M3_DB_TYPE_MYSQL:		// MySQLの場合
				//$cmd = self::BACKUP_CMD . ' --opt -u ' . $this->_connect_user . ' -p ' . $this->_connect_password . ' ' . $this->_dbName . ' ' . $tableName . ' --single-transaction | gzip > ' . $filename;
				$cmd = self::BACKUP_CMD . ' --opt -u ' . $this->_connect_user;
				if (!empty($this->_connect_password)) $cmd .= ' -p ' . $this->_connect_password;
				$cmd .= ' ' . $this->_dbName . ' ' . $tableName . ' --single-transaction | gzip > ' . $filename;
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
	 * データベースをリストア
	 *
	 * @param string $filename		バックアップファイル名
	 * @return bool					true=正常、false=異常
	 */
	function restoreDb($filename)
	{
		$ret = false;
		$dbType = $this->getDbType();
		switch ($dbType){
			case M3_DB_TYPE_MYSQL:		// MySQLの場合
				// 一時ファイルに解凍
				$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_BACKUP_FILENAME_HEAD);
				$sfp = gzopen($filename, "rb");
				$fp = fopen($tmpFile, "w");
				while ($string = gzread($sfp, 4096)) {
					fwrite($fp, $string, strlen($string));
				}
				gzclose($sfp);
				fclose($fp);

				// リストアコマンド実行
				$cmd = "mysql -u $this->_connect_user -p $this->_connect_password -e 'source $tmpFile' $this->_dbName";
				$ret = $this->_procExec($cmd);
				if ($ret == 0){
					$ret = true;
				} else {
					$ret = false;
				}
				
				// 一時ファイル削除
				unlink($tmpFile);
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

		// コマンドを実行
		$process = proc_open($command, $descriptorspec, $pipes, null, null);
		if (!is_resource($process)) return $retVal;		// 起動に失敗した場合
		
		// コマンドの終了を待機
		$status = proc_get_status($process);
		while ($status["running"]) {
			sleep(1);
			$status = proc_get_status($process);
		}
		$retVal = $status['exitcode'];	// 常に0が返る?
		
		$output = stream_get_contents($pipes[1]);
		$errorOutput = stream_get_contents($pipes[2]);
			
		// 終了処理
		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($process);	// リソースを閉じる
		
		return $retVal;
	}
	/**
	 * テーブルのレコード数と最大シリアル番号を取得
	 *
	 * @param string $tableName				テーブル名
	 * @param string $serialNoFieldName		シリアル番号のフィールド名
	 * @param int    $maxSerialNo			最大シリアル番号
	 * @return int							レコード数
	 */
	function getTableRecordCount($tableName, $serialNoFieldName, &$maxSerialNo)
	{
		$count = 0;
		$queryStr  = 'SELECT COUNT(*) AS count, MAX(%s) AS maxserial FROM %s';
		$ret = $this->selectRecord(sprintf($queryStr, $serialNoFieldName, $tableName), null, $row);
		if ($ret){
			$count = $row['count'];
			$maxSerialNo = $row['maxserial'];
		}
		return $count;
	}
	/**
	 * 古いテーブルレコードを削除
	 *
	 * @param string $tableName				テーブル名
	 * @param string $serialNoFieldName		シリアル番号のフィールド名
	 * @param int    $maxSerialNo			削除するレコードの最大シリアル番号
	 * @return bool							true=正常終了、false=異常終了
	 */
	function deleteTableRecord($tableName, $serialNoFieldName, $maxSerialNo)
	{
		$queryStr  = 'DELETE FROM %s WHERE %s <= ?';
		$ret = $this->execStatement(sprintf($queryStr, $tableName, $serialNoFieldName), array($maxSerialNo));
		return $ret;
	}
}
?>
