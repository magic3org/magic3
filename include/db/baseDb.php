<?php
/**
 * DBベースクラス
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class BaseDb extends Core
{
	private static $_con = null;		// DBコネクション
	private $_localCon = null;			// 一時利用DBコネクション
	private static $_tranStatus;		// トランザクションの状態
	private static $_inTran;			// トランザクション中かどうか
	private static $_displayErrMessage = true;			// エラーメッセージの画面出力を行うかどうか
	protected $_connect_dsn;				// DB接続情報
	protected $_connect_user;				// DB接続ユーザ
	protected $_connect_password;			// DB接続パスワード
	private $_dsn;						// DB接続情報
	private $_fetchCallback = null;		// フェッチ時のコールバック関数
	private $_effectedRowCount;			// 直近のDELETE, INSERT, UPDATE文が作用した行数
	private $_statement;				// クエリー文字列
	private $_params;					// クエリーに埋め込むパラメータ
	private $_errorMsg = array();		// エラーメッセージ
	//private $_dbType;					// DBのタイプ
	private static $_dbType;					// DBのタイプ
	//private $_dbVersion;				// DBバージョン
	private static $_dbVersion;				// DBバージョン
	
	// エラーステータス
	const DB_NO_ERROR = 0;				// エラーなし
	const DB_ERROR = 1;					// エラーあり
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->_connect_dsn			= M3_DB_CONNECT_DSN;				// DB接続情報
		$this->_connect_user		= M3_DB_CONNECT_USER;				// DB接続ユーザ
		$this->_connect_password	= M3_DB_CONNECT_PASSWORD;			// DB接続パスワード
	}
	/**
	 * デストラクタ
	 */
	function __destruct()
	{
	}
	/**
	 * 初期化メソッド
	 *
	 * DBの属性パラメータが必要な場合に呼び出す
	 */
	function init()
	{
		self::_getConnection();
	}
	/**
	 * ローカル接続開始
	 *
	 * @param  string $dsn			DB接続情報
	 * @param  string $user			DB接続ユーザ
	 * @param  string $password		DB接続パスワード
	 * @return bool					true=成功、false=失敗
	 */
	function openLocalDb($dsn, $user, $password)
	{
		// ローカルDB用のコネクションを作成
		$this->_localCon = $this->_createConnection($dsn, $user, $password);	// 一時退避用DBコネクション
		if ($this->_localCon == null){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * ローカル接続終了
	 *
	 * @return 			なし
	 */
	function closeLocalDb()
	{
		$this->_localCon = null;
	}
	/**
	 * DB接続コネクション取得
	 *
	 * @return PDO 			接続コネクションオブジェクト
	 */
	function _getConnection()
	{
		// ローカルDBが接続されている場合はローカルDBを使用
		//if ($this->_localCon != null) return $this->_localCon;
		if (!is_null($this->_localCon)) return $this->_localCon;
		
		//if (self::$_con == null){
		if (is_null(self::$_con)){
			try {
				$isCharset = false;
				if (strStartsWith($this->_connect_dsn, 'mysql:') && version_compare(PHP_VERSION, '5.3.6') >= 0){
					$this->_connect_dsn .= ';charset=utf8';		// クライアントの文字セットを設定
					$isCharset = true;
				}
				self::$_con = new PDO($this->_connect_dsn, $this->_connect_user, $this->_connect_password);
				self::$_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				//$this->_dbType = self::$_con->getAttribute(PDO::ATTR_DRIVER_NAME);					// DBのタイプ
				self::$_dbType = self::$_con->getAttribute(PDO::ATTR_DRIVER_NAME);					// DBのタイプ

				//$this->_dbVersion = self::$_con->getAttribute(PDO::ATTR_SERVER_VERSION);		// DBバージョン
				self::$_dbVersion = self::$_con->getAttribute(PDO::ATTR_SERVER_VERSION);		// DBバージョン

				//if ($this->_dbType == 'mysql') {	// MySQLの場合
				if (self::$_dbType == 'mysql') {	// MySQLの場合
					self::$_con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
					
					// 文字化け防止SQL実行(MySQL4.1以上)
					//if (self::$_dbVersion >= '4.1') self::$_con->exec('SET NAMES utf8');
					if (!$isCharset) self::$_con->exec('SET NAMES utf8');// クライアントの文字セットを設定
				}
			} catch (PDOException $e) {
				// 共通エラー処理
				self::_onError($e, 'DSN=' . $this->_connect_dsn . ';' . 'USER=' . $this->_connect_user . ';' . 'PWD=' . $this->_connect_password);
			}
		}
		return self::$_con;
	}
	/**
	 * DB接続コネクション作成
	 *
	 * @param  string $dsn			DB接続情報
	 * @param  string $user			DB接続ユーザ
	 * @param  string $password		DB接続パスワード
	 * @return PDO 			接続コネクションオブジェクト
	 */
	function _createConnection($dsn, $user, $password)
	{
		try {
			$con = new PDO($dsn, $user, $password);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$this->dbType = $con->getAttribute(PDO::ATTR_DRIVER_NAME);					// DBのタイプ

			$this->dbVersion = $con->getAttribute(PDO::ATTR_SERVER_VERSION);		// DBバージョン

			if ($this->dbType == 'mysql') {	// MySQLの場合
				$con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
				
				// 文字化け防止SQL実行(MySQL4.1以上)
				$con->exec('SET NAMES utf8');
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e, 'DSN=' . $dsn . ';' . 'USER=' . $user . ';' . 'PWD=' . $password);
		}
		return $con;
	}
	/**
	 * DBの種別を取得
	 *
	 * @return string 			DBのタイプ
	 */
	function getDbType()
	{
		//return $this->_dbType;
		return self::$_dbType;
	}
	/**
	 * DBのバージョンを取得
	 *
	 * @return string 			DBのバージョン
	 */
	function getDbVersion()
	{
		//return $this->_dbVersion;
		return self::$_dbVersion;
	}
	/**
	 * 接続DB名を取得
	 *
	 * @return string 			DB名
	 */
	function getDbName()
	{
		$dbName = '';
		$dsnArray = explode(';', $this->_connect_dsn);
		for ($i = 0; $i < count($dsnArray); $i++){
			$pos = strpos($dsnArray[$i], '=');
			if ($pos === false) continue;
			
			list($key, $value) = explode('=', $dsnArray[$i]);
			$key = trim($key);
			$value = trim($value);
			if (strcasecmp($key, 'dbname') == 0){
				$dbName =$value;
				break;
			}
		}
		return $dbName;
	}
	/**
	 * Timestamp型データのNULLデータを取得
	 *
	 * @return string 			NULLデータ値
	 */
	function getTimestampNullValue()
	{
		//if ($this->_dbType == M3_DB_TYPE_MYSQL){		// MySQLの場合
		if (self::$_dbType == M3_DB_TYPE_MYSQL){		// MySQLの場合
			return '0000-00-00 00:00:00';
		//} else if ($this->_dbType == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
		} else if (self::$_dbType == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			return '0001-01-01 00:00:00';
		} else {
			return 'undefined';
		}
	}
	/**
	 * フェッチ時のコールバック関数を設定
	 */
	function _setFetchCallback($func)
	{
		$this->_fetchCallback = $func;
	}
	
	/**
	 * フェッチ処理
	 */
	function _fetch($stmt, $param = null)
	{
		$index = 0;// 何行目か
		
		// 取得したデータを処理
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if (is_callable($this->_fetchCallback)){
				// コールバック関数を実行
				// falseが返って来たときは終了する
				$result = call_user_func($this->_fetchCallback, $index, $row, $param);
				if (!$result) break;
			}
			$index++;
		}
	}
	/**
	 * SQLステートメントを設定
	 */
	function _setStatement($stmt, $params = null)
	{
		$this->_statement = $stmt;
		$this->_params = $params;
	}
	/**
	 * SQLステートメントを実行
	 *
	 * @param  string $statement		実行するSQLステートメント
	 */
	function _execStatement()
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();

			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($this->_statement);
				$stmt->execute($this->_params);
				$this->_effectedRowCount = $stmt->rowCount();	// 直近のDELETE, INSERT, UPDATE文が作用した行数

				// ステートメントを開放
				$stmt = null;
			
				// 正常終了
				$retValue = true;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}
		return $retValue;
	}
	/**
	 * SELECT後、一行ずつ取り出し、コールバック関数に渡す
	 *
	 * @param  object		コールバック関数に渡る値
	 * @return bool			true=正常終了、false=異常終了
	 */
	function _execLoop($param = null)
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($this->_statement);
				$stmt->execute($this->_params);
				$this->_effectedRowCount = $stmt->rowCount();	// 直近のDELETE, INSERT, UPDATE文が作用した行数
			
				// フェッチ処理
				self::_fetch($stmt, $param);

				// ステートメントを開放
				$stmt = null;
			
				// 正常終了
				$retValue = true;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * トランザクション開始
	 *
	 * @return bool			true=正常終了、false=異常終了
	 */
	function _beginTransaction()
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// トランザクションスタート
				$con->beginTransaction();

				// 正常終了
				$retValue = true;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
		}	
		return $retValue;
	}
	/**
	 * トランザクションコミット
	 *
	 * @return bool			true=正常終了、false=異常終了
	 */
	function _commit()
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// コミット
				$con->commit();

				// 正常終了
				$retValue = true;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
		}	
		return $retValue;
	}
	/**
	 * トランザクションロールバック
	 *
	 * @return bool			true=正常終了、false=異常終了
	 */
	function _rollBack()
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// ロールバック
				$con->rollBack();

				// 正常終了
				$retValue = true;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
		}	
		return $retValue;
	}
	/**
	 * 共通エラー処理
	 *
	 * @param exception $ex		例外オブジェクト
	 * @param string $message	追加メッセージ
	 */
	function _onError($ex, $message = '')
	{
		global $gLogManager;
		
		// エラーステータスセット
		self::$_tranStatus = self::DB_ERROR;
	
		// エラーメッセージ
		$errMsg = $ex->getMessage();
		if (M3_DB_ERROR_OUTPUT_STATEMENT) $errMsg .= ' [' . $this->_statement . ']';			// クエリー文字列を出力する場合
		
		if (self::$_displayErrMessage){		// エラーメッセージを出力する場合
			echo 'DB failed: ' . $errMsg;
			echo '    error code: ' . $ex->getCode();
		}
		array_push($this->_errorMsg, $errMsg);
		
		// ログにエラーメッセージを出力
		if (!empty($message)) $msg = ': ' . $message;

		// バックトレース出力
		// メモリ不足でdebug_print_backtrace()でフリーズするのでコメントアウトする(2008/11/28)
/*		ob_start();
		debug_print_backtrace();
		$backTraceMsg = ob_get_contents();
		ob_end_clean();
		$msg .= ' [BACKTRACE]' . $backTraceMsg;*/
		$msg .= ' [BACKTRACE]' . $ex->getTraceAsString();
		
		$gLogManager->error(__METHOD__, 'DBエラー発生: ' . $errMsg . $msg);
	}
	/**
	 * エラーメッセージの出力を行うかどうか
	 *
	 * @param bool $status	true=エラーメッセージの出力、false=エラーメッセージ出力しない
	 */
	function displayErrMessage($status)
	{	
		self::$_displayErrMessage = $status;
	}
	/**
	 * エラーメッセージの出力状態を取得
	 *
	 * @return bool 		true=エラーメッセージの出力、false=エラーメッセージ出力しない
	 */
	function getDisplayErrMessage()
	{	
		return self::$_displayErrMessage;
	}
	/**
	 * トランザクション開始
	 *
	 * @return なし
	 */
	function startTransaction()
	{
		self::$_tranStatus = self::DB_NO_ERROR;
		self::_beginTransaction();
		
		// トランザクションの状態を設定
		self::$_inTran = true;			// トランザクション中
	}
	/**
	 * トランザクション終了
	 *
	 * @return bool		true=コミットまで完了、false=エラー発生の場合ロールバックして値を返す
	 */
	function endTransaction()
	{
		if (self::$_tranStatus == self::DB_NO_ERROR){
			self::_commit();
		} else {
			self::_rollback();
		}
		
		// トランザクションの状態を解除
		self::$_inTran = false;
		
		// コミットが成功している場合はtrueを返す
		if (self::$_tranStatus == self::DB_NO_ERROR){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * トランザクション中止
	 */
	function cancelTransaction()
	{
		// エラーステータスセット
		self::$_tranStatus = self::DB_ERROR;
	}
	/**
	 * トランザクション中かどうかを取得
	 *
	 * @return bool			true=トランザクション中、false=トランザクション中でない
	 */
	function isInTransaction()
	{
		return self::$_inTran;
	}
	/**
	 * エラーメッセージ取得
	 *
	 * @return string			エラーメッセージ
	 */
	function getErrMsg()
	{
		return $this->_errorMsg;
	}
	/**
	 * テキストデータをクエリーの実行行単位に解析
	 *
	 * @param  string $sql		解析を行うテキストデータ
	 * @param  array  $ret		クエリー行の配列
	 * @return bool				true=正常終了、false=異常終了
	 */
/*	function _splitSql($sql, &$ret)
	{
		$sql               = trim($sql);
		$sql_len           = strlen($sql);
		$char              = '';
		$string_start      = '';
		$in_string         = false;
		$ret = array();		// 戻り値初期化

		for ($i = 0; $i < $sql_len; $i++){
			$char = $sql[$i];

			// 文字列のまとまりで切り取る
			if ($in_string){
				for (;;){
					$i = strpos($sql, $string_start, $i);
					if (!$i){		// // 見つからないときは終了
						$ret[] = $sql;
						//$ret[] = str_replace(array("\r", "\n"), '', $sql);			// 改行コード削除
						return true;
					} else if ($string_start == '`' || $sql[$i-1] != '\\'){
						$string_start      = '';
						$in_string         = false;
						break;
					} else {
						// バックスラッシュのエスケープ文字をチェック
						$j                     = 2;
						$escaped_backslash     = false;
						while ($i - $j > 0 && $sql[$i - $j] == '\\'){
							$escaped_backslash = !$escaped_backslash;
							$j++;
						}
						if ($escaped_backslash){
							$string_start  = '';
							$in_string     = false;
							break;
						} else {
							$i++;
						}
					}
				}
			} else if ($char == ';'){
				// if delimiter found, add the parsed part to the returned array
				$ret[]    = substr($sql, 0, $i);
				//$ret[] = str_replace(array("\r", "\n"), '', substr($sql, 0, $i));			// 改行コード削除
				$sql      = ltrim(substr($sql, min($i + 1, $sql_len)));
				$sql_len  = strlen($sql);
				if ($sql_len){
					$i      = -1;
				} else {
					// The submited statement(s) end(s) here
					return true;
				}
			} else if (($char == '"') || ($char == '\'') || ($char == '`')){
				$in_string    = true;
				$string_start = $char;
			} else if ($char == '#' || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')){		// 「#」「-- 」形式のコメント行の場合
				// starting position of the comment depends on the comment type
				$start_of_comment = (($sql[$i] == '#') ? $i : $i - 2);
				// if no "\n" exits in the remaining string, checks for "\r"
				// (Mac eol style)
				$end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
										? strpos(' ' . $sql, "\012", $i+2)
										: strpos(' ' . $sql, "\015", $i+2);
				//$end_of_comment   = (strpos(' ' . $sql, "\012", $i + 1));
				if (!$end_of_comment){
					$last = trim(substr($sql, 0, $i-1));
					//$last = trim(substr($sql, 0, $start_of_comment));
					if (!empty($last) && ($last[0] != '#' && $last[0] != '-')){		// コメント行でなければ追加
						$ret[] = $last;
						//$ret[] = str_replace(array("\r", "\n"), '', $last);			// 改行コード削除
					}
					return true;
				} else {
					$sql     = substr($sql, 0, $start_of_comment) . ltrim(substr($sql, $end_of_comment));
					$sql_len = strlen($sql);
					$i--;
					//$i = $end_of_comment - 1;
				}
			}
		}

		// add any rest to the returned array
		if (!empty($sql) && trim($sql) != ''){
			$ret[] = $sql;
			//$ret[] = str_replace(array("\r", "\n"), '', $sql);			// 改行コード削除
		}
		return true;
	}*/
	function _splitSql($sql, &$ret)
	{
		$sql               = trim($sql);
		$sql_len           = strlen($sql);
		$char              = '';
		$string_start      = '';
		$in_string         = false;
		$ret = array();		// 戻り値初期化

		for ($i = 0; $i < $sql_len; $i++){
			$char = $sql[$i];

			// 文字列のまとまりで切り取る
			if ($in_string){
				for (;;){
					$i = strpos($sql, $string_start, $i);
					if (!$i){		// // 見つからないときは終了
						//$ret[] = $sql;
						$ret[] = str_replace(array("\r", "\n"), '', $sql);			// 改行コード削除
						return true;
					} else if ($string_start == '`' || $sql[$i-1] != '\\'){
						$string_start      = '';
						$in_string         = false;
						break;
					} else {
						// バックスラッシュのエスケープ文字をチェック
						$j                     = 2;
						$escaped_backslash     = false;
						while ($i - $j > 0 && $sql[$i - $j] == '\\'){
							$escaped_backslash = !$escaped_backslash;
							$j++;
						}
						if ($escaped_backslash){
							$string_start  = '';
							$in_string     = false;
							break;
						} else {
							$i++;
						}
					}
				}
			} else if ($char == ';'){
				// if delimiter found, add the parsed part to the returned array
	//			$ret[]    = substr($sql, 0, $i);
				$ret[] = str_replace(array("\r", "\n"), '', substr($sql, 0, $i));			// 改行コード削除
				$sql      = ltrim(substr($sql, min($i + 1, $sql_len)));
				$sql_len  = strlen($sql);
				if ($sql_len){
					$i      = -1;
				} else {
					// The submited statement(s) end(s) here
					return true;
				}
			} else if (($char == '"') || ($char == '\'') || ($char == '`')){
				$in_string    = true;
				$string_start = $char;
			} else if ($char == '#' || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')){		// 「#」「-- 」形式のコメント行の場合
				// starting position of the comment depends on the comment type
				$start_of_comment = (($sql[$i] == '#') ? $i : $i - 2);

/*				$end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
										? strpos(' ' . $sql, "\012", $i+2)
										: strpos(' ' . $sql, "\015", $i+2);*/
			//	$end_of_comment   = (strpos(' ' . $sql, "\012", $i + 1));
				$end_of_comment   = strpos($sql, "\012", $i + 1);
				if (!$end_of_comment){
					//$last = trim(substr($sql, 0, $i-1));
					$last = trim(substr($sql, 0, $start_of_comment));
					if (!empty($last) && ($last[0] != '#' && $last[0] != '-')){		// コメント行でなければ追加
						//$ret[] = $last;
						$ret[] = str_replace(array("\r", "\n"), '', $last);			// 改行コード削除
					}
					return true;
				} else {

					$sql     = substr($sql, 0, $start_of_comment) . ltrim(substr($sql, $end_of_comment));
					$sql_len = strlen($sql);
				//	$i--;
					$i = $start_of_comment - 1;
				}
			}
		}

		// add any rest to the returned array
		if (!empty($sql) && trim($sql) != ''){
			//$ret[] = $sql;
			$ret[] = str_replace(array("\r", "\n"), '', $sql);			// 改行コード削除
		}
		return true;
	}
	/**
	 * テキストデータをクエリーの実行行単位に解析(マルチバイト対応)
	 *
	 * 機能:変換内容は、コメントの削除、行末改行コードと「;」の削除、行単位の分割。
	 *
	 * @param  string $sql		解析を行うテキストデータ
	 * @param  array  $ret		クエリー行の配列
	 * @return bool				true=正常終了、false=異常終了
	 */
	function _splitMultibyteSql($sql, &$ret)
	{
		$sql			= trim($sql);
		$sql			= preg_split("//u", $sql, -1, PREG_SPLIT_NO_EMPTY);			// マルチバイト文字に分割
		$sqlLen			= count($sql);
		$char			= '';
		$stringStart	= '';
		$inString		= false;
		$ret			= array();		// 戻り値初期化

		for ($i = 0; $i < $sqlLen; $i++){
			$char = $sql[$i];

			// クォート等、改行を跨るテキストの処理
			if ($inString){
				for (;;){
					$pos = $i;
					$i = array_search($stringStart, array_slice($sql, $i));
					if ($i !== false) $i += $pos;
					
					if (!$i){		// // 見つからないときは終了
						//$ret[] = implode('', $sql);
						$ret[] = str_replace(array("\r", "\n"), '', implode('', $sql));			// 改行コード削除
						return true;
					} else if ($stringStart == '`' || $sql[$i - 1] != '\\'){
						$stringStart      = '';
						$inString         = false;
						break;
					} else {
						// バックスラッシュのエスケープ文字をチェック
						$j                     = 2;
						$escaped_backslash     = false;
						while ($i - $j > 0 && $sql[$i - $j] == '\\'){
							$escaped_backslash = !$escaped_backslash;
							$j++;
						}
						if ($escaped_backslash){
							$stringStart  = '';
							$inString     = false;
							break;
						} else {
							$i++;
						}
					}
				}
			} else if ($char == ';'){
				// 行末を検出した場合はテキストを戻り配列に格納して、読み込み位置を更新
				//$ret[]	= implode('', array_slice($sql, 0, $i));
				$ret[]	= str_replace(array("\r", "\n"), '', implode('', array_slice($sql, 0, $i)));		// 改行コード削除
				$sql	= ltrim(implode('', array_slice($sql, min($i + 1, $sqlLen))));		// 先頭の改行コード削除
				$sql	= preg_split("//u", $sql, -1, PREG_SPLIT_NO_EMPTY);			// マルチバイト文字に分割
				$sqlLen	= count($sql);
				if ($sqlLen > 0){
					$i      = -1;		// 検索文字列の先頭から読み込む
				} else {
					return true;
				}
			} else if (($char == '"') || ($char == '\'') || ($char == '`')){
				$inString    = true;
				$stringStart = $char;
			} else if ($char == '#' || ($char == ' ' && $i > 1 && $sql[$i - 2] . $sql[$i - 1] == '--')){		// 「#」「-- 」形式のコメント行の場合
				// コメント開始位置を検出
				$commentStartPos = (($char == '#') ? $i : $i - 2);

				// コメント終了位置を検出
				$commentEndPos = array_search("\012", array_slice($sql, $i + 1));		// LF
				if ($commentEndPos === false){
					// コメントの前にテキストがある場合は取得
					$last	= trim(implode('', array_slice($sql, 0, $commentStartPos)));		// 改行コード削除
					$sql	= preg_split("//u", $last, -1, PREG_SPLIT_NO_EMPTY);			// マルチバイト文字に分割
					if (!empty($sql) && ($sql[0] != '#' && $sql[0] != '-')){		// コメント行でなければ追加
						//$ret[] = $last;
						$ret[] = str_replace(array("\r", "\n"), '', $last);			// 改行コード削除
					}
					return true;
				} else {
					// コメントを読み飛ばす
					$commentEndPos += $i + 1;
					$sql	= implode('', array_slice($sql, 0, $commentStartPos)) . ltrim(implode('', array_slice($sql, $commentEndPos)));		// 前行の改行コードを削除
					$sql	= preg_split("//u", $sql, -1, PREG_SPLIT_NO_EMPTY);			// マルチバイト文字に分割
					$sqlLen	= count($sql);
//					$i--;			// 再度同じ位置を読み込む
					$i = $commentStartPos -1;	// コメントの位置の先頭から再度読み込む
				}
			}
		}
		// ##### 行末に「;」がないコードなどコメント以外で問題のありそうなコードは、スクリプト処理でエラー表示させるために残す #####
		if (!empty($sql)){
			$sql = trim(implode('', array_slice($sql, 0)));
			if (!empty($sql)){
				//$ret[] = $sql;
				$ret[] = str_replace(array("\r", "\n"), '', $sql);		// 改行コード削除
			}
		}
		return true;
	}
	/**
	 * SQLを実行する
	 *
	 * @param  string $query			SQL文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @return bool						true=正常終了、false=異常終了
	 */
	function execStatement($query, $queryParams)
	{
		// 実行するSQLを設定
		self::_setStatement($query, $queryParams);
		
		return self::_execStatement();
	}
	/**
	 * SELECT文を実行後、1行ずつ取り出し、コールバック関数に渡す
	 *
	 * @param  string $query			SELECT文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @param  function  $callback		コールバック関数
	 * @param  array  $param			コールバック関数に渡る値
	 * @return 							なし
	 */
	function selectLoop($query, $queryParams, $callback, $param = null)
	{
		// 実行するSQLを設定
		self::_setStatement($query, $queryParams);
		
		// コールバック関数を設定
		self::_setFetchCallback($callback);
		
		// SQLを実行し、1行ずつ処理
		self::_execLoop($param);
	}
	/**
	 * SELECT文を実行後、1行取得する
	 *
	 * @param  string $query			SELECT文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @param  array  $row				取得した行
	 * @return bool						true=データあり, false=データなし
	 */
	function selectRecord($query, $queryParams, &$row)
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($query);
				$stmt->execute($queryParams);
			
				// フェッチ処理
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row) $retValue = true;// 正常終了

				// ステートメントを開放
				$stmt = null;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * SELECT文を実行し、すべての行を取得する
	 *
	 * @param  string $query			SELECT文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @param  array  $rows				取得した行
	 * @return bool						true=データあり, false=データなし
	 */
	function selectRecords($query, $queryParams, &$rows)
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($query);
				$stmt->execute($queryParams);
			
				// フェッチ処理を行い、データコピー
				$retRows = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$retRows[] = $row;
					$retValue = true;// データあり
				}
				$rows = $retRows;
			
				// ステートメントを開放
				$stmt = null;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * SELECT文を実行した結果を連想配列(key,value形式)で取得する
	 *
	 * @param  string $query			SELECT文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @param  string $keyField			keyとなるテーブルフィールド名
	 * @param  string $valueField		valueとなるテーブルフィールド名
	 * @param  array  $destArray		SELECT結果の連想配列データ
	 * @return bool						true=データあり, false=データなし
	 */
	function selectRecordsToArray($query, $queryParams, $keyField, $valueField, &$destArray)
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($query);
				$stmt->execute($queryParams);
			
				// フェッチ処理を行い、データコピー
				$retArray = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$retArray[$row[$keyField]] = $row[$valueField];
					$retValue = true;// データあり
				}
				$destArray = $retArray;
			
				// ステートメントを開放
				$stmt = null;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * SELECT文を実行し行数を取得する
	 *
	 * @param  string $query			SELECT文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @return int						行数
	 */
	function selectRecordCount($query, $queryParams)
	{
		// 戻り値初期化
		$retValue = 0;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($query);
				$stmt->execute($queryParams);
			
				// 行数を取得
				$retValue = $stmt->rowCount();
			
				// ステートメントを開放
				$stmt = null;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * レコードがあるか確認
	 *
	 * @param  string $query			SELECT文
	 * @param  array  $querryParams		クエリーに埋め込むパラメータ
	 * @return bool						true=レコードあり, false=レコードなし
	 */
	function isRecordExists($query, $queryParams)
	{
		// 戻り値初期化
		$retValue = false;
		
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($query);
				$stmt->execute($queryParams);
			
				// フェッチ処理
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row) $retValue = true;

				// ステートメントを開放
				$stmt = null;
			}
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e);
			
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * 直近のDELETE, INSERT, UPDATE文が作用した行数を返す
	 *
	 * @return int						行数
	 */
	function getEffectedRowCount()
	{
		return $this->_effectedRowCount;
	}
	/**
	 * テーブルがあるか確認
	 *
	 * @param  string $tableName		テーブル名
	 * @return bool						true=存在する, false=存在しない
	 */
	function isTableExists($tableName)
	{
		// 戻り値初期化
		$retValue = false;
		
		$query = 'SELECT * FROM ' . $tableName;
		$queryParams = array();
		try {
			// DBに接続
			$con = self::_getConnection();
			
			if ($con != null){
				// SQL実行
				$stmt = $con->prepare($query);
				$stmt->execute($queryParams);
			
				// テーブルが存在する
				$retValue = true;
				// フェッチ処理
				//$row = $stmt->fetch(PDO::FETCH_ASSOC);
				//if ($row) $retValue = true;

				// ステートメントを開放
				$stmt = null;
			}
		} catch (PDOException $e) {
			// ステートメントを開放
			$stmt = null;
		}	
		return $retValue;
	}
	/**
	 * SQLスクリプトを実行
	 *
	 * @param  string $scriptFullPath	実行するファイル
	 * @return 							なし
	 */
	function execSqlScript($scriptFullPath)
	{
		// スクリプトファイルを読み込み
		$fileData = fread(fopen($scriptFullPath, 'r'), filesize($scriptFullPath));
				
		// ファイル内容を解析
		if (M3_DB_MULTIBYTE_SCRIPT){			// マルチバイト対応のSQLスクリプト処理
			$ret = self::_splitMultibyteSql($fileData, $lines);
		} else {
			$ret = self::_splitSql($fileData, $lines);
		}
		
		if ($ret){
			$lineCount = count($lines);
			for ($i = 0; $i < $lineCount; $i++) {
				// SQLを実行
				self::_setStatement($lines[$i], array());
				self::_execStatement();
			}
		} else {
			array_push($this->_errorMsg, 'Split Sciprt File Error.');
		}
	}
	/**
	 * MySQL用のSQLスクリプトをコンバートして実行
	 *
	 * @param  string $scriptFullPath	実行するファイル
	 * @return 							実行結果(true=成功、false=失敗)
	 */
	function execSqlScriptWithConvert($scriptFullPath)
	{
		// DBタイプが不明のときは取得
		if (empty(self::$_dbType)) self::init();
		
		// スクリプトファイルを読み込み
		$fileData = fread(fopen($scriptFullPath, 'r'), filesize($scriptFullPath));

		// プレ変換処理
		if (self::$_dbType == M3_DB_TYPE_PGSQL && version_compare(self::getDbVersion(), '9.0.0') >= 0){// PostgreSQL 9.0以上の場合
			$fileData = $this->_preConvSql($fileData);
		}
		// バックスラッシュ文字を処理するかどうか
		$stripcSlash = false;
		if (version_compare(self::getDbVersion(), '9.1.0') >= 0){// PostgreSQL 9.1以上の場合
			$ret = $this->selectRecord('SELECT setting FROM pg_settings where name=\'standard_conforming_strings\'', $queryParams, $row);
			if ($ret && $row['setting'] == 'on') $stripcSlash = true;
		}
						
		// ファイル内容を解析
		if (M3_DB_MULTIBYTE_SCRIPT){			// マルチバイト対応のSQLスクリプト処理
			$ret = self::_splitMultibyteSql($fileData, $lines);
		} else {
			$ret = self::_splitSql($fileData, $lines);
		}
		
		if ($ret){
			$lineCount = count($lines);
			for ($i = 0; $i < $lineCount; $i++){
				// DBのタイプに合わせてスクリプトを変換
				if (self::$_dbType == M3_DB_TYPE_MYSQL){		// MySQLの場合
					// MySQL5.5以上のときは、「TYPE=innodb」を「ENGINE=innodb」に変換
					$line = $lines[$i];
					if (version_compare(self::getDbVersion(), '5.5.0') >= 0) $line = str_ireplace('TYPE=innodb', 'ENGINE=innodb', $line);

					// SQLを実行
					self::_setStatement($line, array());
					self::_execStatement();
				} else if (self::$_dbType == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
					if (self::_convertScriptFromMySqlToPgSql($lines[$i], $destSQL)){
						for ($j = 0; $j < count($destSQL); $j++){
							$statement = $destSQL[$j];
							if ($stripcSlash) $statement = stripcslashes($statement);		// バックスラッシュ文字を変換

							// SQLを実行
							//self::_setStatement($destSQL[$j], array());
							self::_setStatement($statement, array());
							self::_execStatement();
						}
					} else {
						array_push($this->_errorMsg, 'Split Sciprt File Error. [' . $lines[$i] . ']');
						return false;
					}
				}
			}
		} else {
			array_push($this->_errorMsg, 'Split Sciprt File Error.');
			return fales;
		}
		return true;
	}
	/**
	 * SQLスクリプトプレ変換処理
	 *
	 * @param string $src		変換するデータ
	 * @return string			変換後データ
	 */
	function _preConvSql($src)
	{
		// 「--」以降のSQLをPostgreSQL9.0用のSQLとする
		//$str = '/^(.+?;) *-- *(.+?;)(.*?)$/m';
		$str = '/^(.+?;) *-- *(.+?;) *--(.*?)$/m';
        $dest = preg_replace_callback($str, array($this, '_pre_conv_sql_callback'), $src);
		return $dest;
	}
	/**
	 * SQLスクリプトプレ変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _pre_conv_sql_callback($matchData)
	{
		return $matchData[2];
    }
	/**
	 * DB接続テスト
	 *
	 * @param string	$dsn		接続用DSN
	 * @param string	$user		接続ユーザ
	 * @param string	$password	接続パスワード	 
	 * @return bool 				true=正常、false=異常
	 */
	function testDbConnection($dsn, $user, $password)
	{
		$status = false;	// 終了ステータス
		try {
			$con = new PDO($dsn, $user, $password);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$dbType = $con->getAttribute(PDO::ATTR_DRIVER_NAME);					// DBのタイプ
			if ($dbType == 'mysql') {	// MySQLの場合
				$con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			$con = null;
			$status = true;	// 終了ステータス
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e, 'DSN=' . $dsn . ';' . 'USER=' . $user . ';' . 'PWD=' . $password);
		}
		return $status;
	}
	/**
	 * DBテーブル作成、破棄テスト
	 *
	 * @param string	$dsn		接続用DSN
	 * @param string	$user		接続ユーザ
	 * @param string	$password	接続パスワード	 
	 * @return bool 				true=正常、false=異常
	 */
	function testDbTable($dsn, $user, $password)
	{
		$status = false;	// 終了ステータス
		$con = null;
		try {
			$con = new PDO($dsn, $user, $password);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$dbType = $con->getAttribute(PDO::ATTR_DRIVER_NAME);					// DBのタイプ
			if ($dbType == 'mysql') {	// MySQLの場合
				$con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}			
			// トランザクションスタート
			$con->beginTransaction();
			
			// テーブル作成
			$stmt = $con->prepare('create table m3_test_table(param1 int, param2 varchar(10), param3 text)');			
			$stmt->execute(array());
			
			// テーブル破棄
			$stmt = $con->prepare('drop table m3_test_table');
			$stmt->execute(array());
			
			// コミット
			$con->commit();
			
			// ステートメントを開放
			$stmt = null;
			$con = null;
			$status = true;	// 終了ステータス
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e, 'DSN=' . $dsn . ';' . 'USER=' . $user . ';' . 'PWD=' . $password);
			
			if ($con != null){
				// ロールバック
				$con->rollBack();
			}
		}
		return $status;
	}
	/**
	 * DBエンコード環境取得
	 *
	 * @param string  $dsn			接続用DSN
	 * @param string  $user			接続ユーザ
	 * @param string  $password		接続パスワード	 
	 * @param  array  $rows			取得した行
	 * @return bool					true=データあり, false=データなし
	 */
	function testDBEncoding($dsn, $user, $password, &$rows)
	{
		$retValue = false;	// 終了ステータス(データなし)
		$con = null;
		
		try {
			$con = new PDO($dsn, $user, $password);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$dbType = $con->getAttribute(PDO::ATTR_DRIVER_NAME);					// DBのタイプ
			if ($dbType == 'mysql') {	// MySQLの場合
				$con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}

			// エンコーディング環境取得
			$stmt = $con->prepare("SHOW VARIABLES LIKE 'character\_set\_%'");
			$stmt->execute();
			
			$retRows = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$retRows[] = $row;
				$retValue = true;// データあり
			}
			$rows = $retRows;
			
			// ステートメントを開放
			$stmt = null;
			$con = null;
		} catch (PDOException $e) {
			// 共通エラー処理
			self::_onError($e, 'DSN=' . $dsn . ';' . 'USER=' . $user . ';' . 'PWD=' . $password);
		}
		return $retValue;
	}
	/**
	 * MySQL用のSQL文をPostgreSQLのSQL文に変換
	 *
	 * @param  string $src		MySQL用のSQL文
	 * @param  array $dest		PostgreSQL用のSQL文
	 * @return bool				true=正常終了、false=異常終了
	 */
	function _convertScriptFromMySqlToPgSql($src, &$dest)
	{
		$srcSql			= trim($src);
		$srcSqlLen		= strlen($srcSql);
		$dest			= array();	// 戻りデータ初期化
		$status			= 0;		// 処理ステータス
		$destStr		= '';		// 変換後SQL文
		$cmd0			= '';		// 実行コマンド
		$cmd1			= '';		// 実行コマンド
		$endLoop		= false;	// 終了
		$readPos		= 0;		// 読み込み位置
		$saveStr		= '';		// SQL保存用
		
		for ($i = 0; $i < $srcSqlLen; $i++){
			$char = $srcSql[$i];
			
			switch ($status){
				case 0:		// 括弧「(」の前
					// コマンドを判定
					$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), '(', $words);
					if ($pos == -1){		// 「(」がないとき
						$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), '', $words);// 最後までのテキストを1行で取得
						$endLoop = true;		// 処理終了
					} else {
						$readPos = $pos;		// 読み込み位置更新
						$status = 1;
					}
					
					$wordCount = count($words);
					if ($wordCount < 2){
						$dest[] = implode(' ', $words);
						return true;
					} else {
						// 処理可能なキーワード以外は終了
						$cmd0 = $words[0];		// 実行コマンド
						$cmd1 = $words[1];		// 実行コマンド
						$cmds = array('drop', 'truncate', 'delete', 'create', 'insert', 'alter');		// 処理対象コマンド
						for ($k = 0; $k < count($cmds); $k++){
							if (strncasecmp($cmd0, $cmds[$k], strlen($cmds[$k])) == 0) break;
						}
						if ($k == count($cmds)){		// 処理対象コマンドでないとき
							$dest[] = implode(' ', $words);
							return true;
						}
						$tableName = $words[2];		// テーブル名
						
						// insert into のときは次の「(」まで読み込む(フィールド名の取得)
						if (strncasecmp($cmd0, 'insert', strlen('insert')) == 0 && strncasecmp($cmd1, 'into', strlen('into')) == 0){		// insert intoのとき
							$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), '(', $words2);
							if ($pos == -1){
								$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), '', $words2);
								$endLoop = true;		// 処理終了
							} else {
								$readPos = $pos;		// 読み込み位置更新
								$status = 1;
							}
							$words = array_merge($words, $words2);		// コマンドとフィールド名を連結
						} else if (strncasecmp($cmd0, 'alter', strlen('alter')) == 0 && strncasecmp($cmd1, 'table', strlen('table')) == 0){		// ALTER TABLEのとき
							// ALTER TABLEのときは先頭からサブコマンドの次まで読み込む
							if (count($words) < 4){
								$dest[] = implode(' ', $words);
								return true;
							}
							// サブコマンドまでを読み飛ばす
							$pos = 0;
							for ($j = 0; $j < 3; $j++){
								$pos = strpos($srcSql, $words[$j], $pos);
								if ($pos == -1) break;
								$pos += strlen($words[$j]);
							}
							if ($pos != -1) $pos = strpos($srcSql, $words[$j], $pos);
							if ($pos == -1){
								$pos = self::_getLine($srcSql, 0, strlen($srcSql), '', $words2);
								$endLoop = true;		// 処理終了
							} else {
								$readPos = $pos;		// 読み込み位置更新
								$status = 1;
								$endLoop = false;		// 終了をキャンセル
							}
						}
						
						// ##### 必要のないクエリーを削除 #####
						// drop tableのときは、「if exists」を削除(PostgreSQL8.2よりも前のバージョンのとき)
						$destWords = array();
						if (strncasecmp($cmd0, 'drop', 4) == 0 && self::getDbVersion() < '8.2.0'){
							for ($j = 0; $j < $wordCount; $j++){
								if (strncasecmp($words[$j], 'if', 2) != 0 && strncasecmp($words[$j], 'exists', 6) != 0){
									$destWords[] = $words[$j];
								}
							}
						}
						if (count($destWords) == 0) $destWords = $words;
						
						// バッククォートをダブルクォートに変換
						for ($j = 0; $j < count($destWords); $j++){
							if (strlen($destWords[$j]) >= 2 && $destWords[$j][0] == '`' && $destWords[$j][strlen($destWords[$j])-1] == '`'){
								$destWords[$j] = '"' . trim($destWords[$j], "`") . '"';
							} else {
								$destWords[$j] = $destWords[$j];
							}
						}
						$destStr = implode(' ', $destWords);
						if ($endLoop) $dest[] = $destStr;		// 処理終了のとき
						
						// 読み込み位置修正
						if (strncasecmp($cmd0, 'insert', strlen('insert')) == 0 && strncasecmp($cmd1, 'into', strlen('into')) == 0){		// insert intoのとき
							// 「(」の位置から読み込む
							$saveStr = trim($destStr, '(');			// 前半部を保存、「(」を除く
							$readPos--;
							$destStr = '';
						} else if (strncasecmp($cmd0, 'alter', strlen('alter')) == 0 && strncasecmp($cmd1, 'table', strlen('table')) == 0){		// ALTER TABLEのとき
							$saveStr = implode(' ', array($cmd0, $cmd1, $destWords[2]));
							$destStr = '';
						}
					}
					break;
				case 1:// 括弧「(」の中
					if ((strncasecmp($cmd0, 'insert', strlen('insert')) == 0 && strncasecmp($cmd1, 'into', strlen('into')) == 0) ||		// insert intoのとき
						(strncasecmp($cmd0, 'alter', strlen('alter')) == 0 && strncasecmp($cmd1, 'table', strlen('table')) == 0)){		// ALTER TABLEのとき
						$separator1 = ',';
						$separator2 = '';
					} else {
						$separator1 = ',';
						$separator2 = ')';
					}
					$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), $separator1, $words);
					if ($pos == -1){
						$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), $separator2, $words);
						if ($pos == -1){
							$endLoop = true;		// 処理終了
						} else {
							$readPos = $pos;		// 読み込み位置更新
							$status = 2;
						}
					} else {
						$readPos = $pos;		// 読み込み位置更新
					}
					if (strncasecmp($cmd0, 'insert', strlen('insert')) == 0 && strncasecmp($cmd1, 'into', strlen('into')) == 0){		// insert intoのとき
						// 1行づつ作成
						if (count($words) > 0){
							$wordsCount = count($words);
							for ($j = 0; $j < $wordsCount; $j++){
								$trimValue = trim($words[$j], '\'"');
								if ($trimValue == M3_TIMESTAMP_INIT_VALUE_MYSQL){		// 日時の初期値の修正、PostgreSQLでは「0000-00-00 00:00:00」で初期化できない
									$words[$j] = str_replace(M3_TIMESTAMP_INIT_VALUE_MYSQL, M3_TIMESTAMP_INIT_VALUE_PGSQL, $words[$j]);
								} else if ($trimValue == M3_DATE_INIT_VALUE_MYSQL){		// 日時の初期値の修正、PostgreSQLでは「0000-00-00」で初期化できない
									$words[$j] = str_replace(M3_DATE_INIT_VALUE_MYSQL, M3_DATE_INIT_VALUE_PGSQL, $words[$j]);
								}
							}
							$dest[] = trim($saveStr . ' ' . implode(' ', $words), ' ,');		// 行末の「,」を削除
						}
					} else if (strncasecmp($cmd0, 'alter', strlen('alter')) == 0 && strncasecmp($cmd1, 'table', strlen('table')) == 0){		// ALTER TABLEのとき
						// 1行づつ作成
						if (count($words) > 0){
							$wordsCount = count($words);
							for ($j = 0; $j < $wordsCount; $j++){
								if ($j < $wordsCount -1 && 							// defaultに続く値を利用する
									strncasecmp($words[$j], 'default', strlen('default')) == 0){
									$defaultPos = $j;
									$trimValue = trim($words[$j+1], '\'"');
									if ($trimValue == M3_TIMESTAMP_INIT_VALUE_MYSQL){		// 日時の初期値の修正、PostgreSQLでは「0000-00-00 00:00:00」で初期化できない
										$words[$j+1] = str_replace(M3_TIMESTAMP_INIT_VALUE_MYSQL, M3_TIMESTAMP_INIT_VALUE_PGSQL, $words[$j+1]);
										break;
									} else if ($trimValue == M3_DATE_INIT_VALUE_MYSQL){		// 日時の初期値の修正、PostgreSQLでは「0000-00-00」で初期化できない
										$words[$j+1] = str_replace(M3_DATE_INIT_VALUE_MYSQL, M3_DATE_INIT_VALUE_PGSQL, $words[$j+1]);
										break;
									}
								} else if (strncasecmp($words[$j], 'text', strlen('text')) == 0){		// TEXTのとき
									// DEFAULTが設定されていないかチェック
									for ($k = 0; $k < $wordsCount; $k++){
										if (strncasecmp($words[$k], 'default', strlen('default')) == 0) break;// DEFAULTが見つかった
									}
									if ($k < $wordsCount) continue;		// DEFAULTが設定されているので処理を行わない

									// 「DEFAULT ''」を追加する
									array_splice($words, $j +1, 0, array('DEFAULT', '\'\''));
									break;
								}
							}
							// 「DEFAULT」「NOT NULL」を検出
							$defaultPos = -1;		// 「DEFAULT」の検出位置
							$notnullPos = -1;		// 「NOT NULL」の検出位置
							$wordsCount = count($words);
							for ($j = 0; $j < $wordsCount; $j++){
								if ($j < $wordsCount -1 && 							// 「DEFAULT」に続く値を利用する
									strncasecmp($words[$j], 'default', strlen('default')) == 0){
									$defaultPos = $j;
								} if ($j < $wordsCount -1 && 							// 「NOT」に続く値を利用する
									strncasecmp($words[$j], 'not', strlen('not')) == 0 &&
									strncasecmp($words[$j + 1], 'null', strlen('null')) == 0){
									$notnullPos = $j;
								}
							}
							
							// サブコマンド取得
							if (strncasecmp($words[0], 'modify', strlen('modify')) == 0){
								$words[0] = 'ALTER';
							} else if (strncasecmp($words[0], 'change', strlen('change')) == 0){
								$words[0] = 'RENAME';
							} else if (strncasecmp($words[0], 'drop', strlen('drop')) == 0){
								$words[0] = 'DROP';
							}
							if ($words[0] == 'ALTER'){			// 型の変換のとき
								// データ型を設定
								$dest[] = trim(implode(' ', array($saveStr, $words[0], $words[1], 'TYPE', $words[2])), ' ,');		// 行末の「,」を削除
								
								// 「DEFAULT」を設定
								if ($defaultPos != -1){
									$dest[] = trim(implode(' ', array($saveStr, $words[0], $words[1], 'SET', $words[$defaultPos], $words[$defaultPos + 1])), ' ,');		// 行末の「,」を削除
								}
								// 「NOT NULL」を設定
								if ($notnullPos != -1){
									$dest[] = trim(implode(' ', array($saveStr, $words[0], $words[1], 'SET', $words[$notnullPos], $words[$notnullPos + 1])), ' ,');		// 行末の「,」を削除
								}
							} else if ($words[0] == 'RENAME'){
								$dest[] = trim($saveStr . ' ' . $words[0] . ' ' . $words[1] . ' TO ' . $words[2], ' ,');		// 行末の「,」を削除
							} else if ($words[0] == 'DROP'){		// 「DROP INDEX」対応
								if (version_compare(self::getDbVersion(), '9.0.0') >= 0){// PostgreSQL 9.0以上の場合
									$dest[] = trim($saveStr . ' ' . $words[0] . ' CONSTRAINT ' . $words[2], ' ,');		// 行末の「,」を削除
								} else {
									$dest[] = trim($saveStr . ' ' . $words[0] . ' CONSTRAINT ' . $tableName . '_' . $words[2] . '_key', ' ,');		// 行末の「,」を削除
								}
							} else {
								$dest[] = trim($saveStr . ' ' . implode(' ', $words), ' ,');		// 行末の「,」を削除
							}
						}
					} else if (strncasecmp($cmd0, 'create', strlen('create')) == 0 && strncasecmp($cmd1, 'table', strlen('table')) == 0){		// create tableのとき
						$wordsCount = count($words);
						for ($j = 0; $j < $wordsCount; $j++){
							if ($j < $wordsCount -1 && 							// defaultに続く値を利用する
								strncasecmp($words[$j], 'default', strlen('default')) == 0){
								$trimValue = trim($words[$j+1], '\'"');
								if ($trimValue == M3_TIMESTAMP_INIT_VALUE_MYSQL){		// 日時の初期値の修正、PostgreSQLでは「0000-00-00 00:00:00」で初期化できない
									$words[$j+1] = str_replace(M3_TIMESTAMP_INIT_VALUE_MYSQL, M3_TIMESTAMP_INIT_VALUE_PGSQL, $words[$j+1]);
									$destStr .= ' ' . implode(' ', $words);
									break;
								} else if ($trimValue == M3_DATE_INIT_VALUE_MYSQL){		// 日時の初期値の修正、PostgreSQLでは「0000-00-00」で初期化できない
									$words[$j+1] = str_replace(M3_DATE_INIT_VALUE_MYSQL, M3_DATE_INIT_VALUE_PGSQL, $words[$j+1]);
									$destStr .= ' ' . implode(' ', $words);
									break;
								}
							} else if (strncasecmp($words[$j], 'auto_increment', strlen('auto_increment')) == 0){		// AUTO_INCREMENTのとき
								$destStr .= $words[0] . ' SERIAL';
								if ($words[$wordsCount -1] == ',') $destStr .= ',';
								break;
							} else if (strncasecmp($words[$j], 'text', strlen('text')) == 0){		// TEXTのとき
								// DEFAULTが設定されていないかチェック
								for ($k = 0; $k < $wordsCount; $k++){
									if (strncasecmp($words[$k], 'default', strlen('default')) == 0) break;// DEFAULTが見つかった
								}
								if ($k < $wordsCount) continue;		// DEFAULTが設定されているので処理を行わない

								$value = $words[$j];
								$words[$j] = str_replace($value, $value . ' DEFAULT \'\'', $words[$j]);// 「DEFAULT ''」を追加する
								$destStr .= ' ' . implode(' ', $words);
								break;
							}
						}
						if ($j == $wordsCount) $destStr .= ' ' . implode(' ', $words);
					} else {
						$destStr .= ' ' . implode(' ', $words);
					}
					break;
				case 2:// 括弧「)」の後
					$pos = self::_getLine($srcSql, $readPos, strlen($srcSql), '', $words);
					if (strncasecmp($cmd0, 'create', strlen('create')) != 0 || strncasecmp($cmd1, 'table', strlen('table')) != 0){		// create table以外のとき
						// 括弧後の文字列を追加
						if (count($words) > 0) $destStr .= ' ' . implode(' ', $words);
					}
					$status = 3;
					if (strlen($destStr) > 0) $dest[] = $destStr;
					break;
				case 3:		// 終了
					$endLoop = true;	// 終了
					break;
				default:
					echo 'status error.';
					break;
			}
			if ($endLoop) break;	// 終了
			// テーブル名の処理(0)
			// フィールド名の処理(1)
			// 行末の処理(2)
		}
		if (strncasecmp($cmd0, 'drop', strlen('drop')) == 0 ||				// DROPのとき
			strncasecmp($cmd0, 'truncate', strlen('truncate')) == 0 ||		// TRUNCATEのとき
			strncasecmp($cmd0, 'delete', strlen('delete')) == 0){		// DELETEのとき
			if ($status == 0) return true;
		} else if (strncasecmp($cmd0, 'create', strlen('create')) == 0){	// CREATEのとき
			if ($status == 3) return true;
		} else if (strncasecmp($cmd0, 'insert', strlen('insert')) == 0 ||		// INSERTのとき
			strncasecmp($cmd0, 'alter', strlen('alter')) == 0){					// ALTERのとき
			if ($status > 0) return true;
		}
//var_dump($dest);
		return false;
	}
	/**
	 * SQL文字列から1行取り出す
	 *
	 * @param string $src		SQL文
	 * @param int    $start		開始位置
	 * @param int    $end		終了位置
	 * @param string $searchStr	検索文字(1バイト)。空のときは最後までのデータを取得
	 * @param array $dest		語句で分割した行データ
	 * @return int   			次の読み込み位置
	 */
	function _getLine($src, $start, $end, $searchStr, &$dest)
	{
		$char		= '';
		$startStr	= '';
		$inQuote	= false;		// 語句内かどうか
		$startPos	= -1;			// 文字列先頭位置
		$nextPos	= -1;			// 次の読み込み位置
		$dest		= array();
		$bracketCount = 0;		// 括弧の数
						
		if ($start >= $end) return -1;

		for ($i = $start; $i < $end; $i++){
			$char = $src[$i];

			// 文字列のまとまりで切り取る
			if ($inQuote){
				while (true){
					$i = strpos($src, $startStr, $i);
					if (!$i){		// // 見つからないときは終了
						return -1;
					} else if ($startStr == '`' || $src[$i-1] != '\\'){
						$startStr      = '';
						$inQuote         = false;
						$dest[] = substr($src, $startPos, $i - $startPos + 1);
						$startPos = -1;
						break;
					} else {
						// バックスラッシュのエスケープ文字をチェック
						$j                     = 2;
						$escaped_backslash     = false;
						while ($i - $j > 0 && $src[$i - $j] == '\\'){
							$escaped_backslash = !$escaped_backslash;
							$j++;
						}
						if ($escaped_backslash){
							$startStr  = '';
							$inQuote     = false;
							$dest[] = substr($src, $startPos, $i - $startPos + 1);
							$startPos = -1;
							break;
						} else {
							$i++;
						}
					}
				}
			} else if (($char == '"') || ($char == '\'') || ($char == '`')){
				$inQuote  = true;
				$startStr = $char;
				if ($startPos == -1) $startPos = $i;
			} else if ($char == $searchStr && $bracketCount == 0){	// 終了文字のとき
				if ($startPos != -1) $dest[] = substr($src, $startPos, $i - $startPos);
				$dest[] = $searchStr;
				$nextPos = $i + 1;
				break;			// 処理終了
			} else if ($char == '('){		// 括弧の場合は、括弧の数をカウントする
				if ($startPos == -1) $startPos = $i;
				$bracketCount++;
			} else if ($char == ')'){
				if ($startPos == -1) $startPos = $i;
				$bracketCount--;
				if ($bracketCount < 0) $bracketCount = 0;
			} else if ($char == ' ' || $char == "\n" || $char == "\r" || $char == "\t"){		// スペース、改行、タブは読み飛ばす
				if ($startPos != -1){
					$dest[] = substr($src, $startPos, $i - $startPos);
					$startPos = -1;
				}
			} else {		// ダブルクォート等で区切られていない通常の文字
				if ($startPos == -1) $startPos = $i;
			}
		}
		if ($searchStr == ''){		// 読み込みデータの最後まで解析のとき
			if ($startPos != -1) $dest[] = substr($src, $startPos, $i - $startPos);
			$nextPos = strlen($src);
		}
		return $nextPos;
	}
}
?>
