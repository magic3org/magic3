<?php
/**
 * 設定ファイル操作マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class ConfigManager extends _Core
{
	private $configFile = 'siteDef.php';		// 設定ファイル名
    private $defParam = array();				// 定義項目
	private $OPTION_DEF_COMMENT;		// オプション追加用のコメント
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期化
		$this->OPTION_DEF_COMMENT = '// #################### オプション定義項目 ####################' . M3_NL . '// ### インストール時に削除します' . M3_NL;		// オプション追加用のコメント
		$this->defParam['M3_SYSTEM_ROOT_URL'] = '';
		$this->defParam['M3_DB_CONNECT_DSN'] = '';
		$this->defParam['M3_DB_CONNECT_USER'] = '';
		$this->defParam['M3_DB_CONNECT_PASSWORD'] = '';
		
		// 設定値取得
		if (defined('M3_SYSTEM_ROOT_URL')) $this->defParam['M3_SYSTEM_ROOT_URL'] = M3_SYSTEM_ROOT_URL;				// ルートURL
		if (defined('M3_DB_CONNECT_DSN')) $this->defParam['M3_DB_CONNECT_DSN'] = M3_DB_CONNECT_DSN;					// 接続先ＤＢ
		if (defined('M3_DB_CONNECT_USER')) $this->defParam['M3_DB_CONNECT_USER'] = M3_DB_CONNECT_USER;				// 接続ユーザ
		if (defined('M3_DB_CONNECT_PASSWORD')) $this->defParam['M3_DB_CONNECT_PASSWORD'] = M3_DB_CONNECT_PASSWORD;	// パスワード
	}
	/**
	 * 設定ファイルのフルパス
	 *
	 * @return string			設定ファイルフルパス
	 */
	public function configFilePath()
	{
		return $this->gEnv->getIncludePath() . '/' . $this->configFile;
	}
	/**
	 * 設定ファイルが作成可能かどうか
	 *
	 * @return bool				true=可能、false=不可
	 */
	public function isConfigFileWritable()
	{
		if (file_exists($this->gEnv->getIncludePath() . '/' . $this->configFile) && is_writable($this->gEnv->getIncludePath() . '/' . $this->configFile)){
			return true;
		} else if (is_writable($this->gEnv->getIncludePath())){// ディレクトリが書き込み可能か
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 設定ファイルディレクトリが書き込み可能かどうか
	 *
	 * @return bool				true=可能、false=不可
	 */
	public function isConfigDirWritable()
	{
		if (is_writable($this->gEnv->getIncludePath())){// ディレクトリが書き込み可能か
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 定義値チェック
	 *
	 * 設定ファイルに最小限必要な設定が行われているかどうか確認
	 *
	 * @return bool		true=定義完了、false=定義未完
	 */
	public function isConfigured()
	{
		if ((defined('M3_SYSTEM_ROOT_URL') && strlen(trim(M3_SYSTEM_ROOT_URL)) > 0) &&				// ルートURL
				(defined('M3_DB_CONNECT_DSN') && strlen(trim(M3_DB_CONNECT_DSN)) > 0) &&			// 接続先ＤＢ
				(defined('M3_DB_CONNECT_USER') && strlen(trim(M3_DB_CONNECT_USER)) > 0) &&			// 接続ユーザ
				defined('M3_DB_CONNECT_PASSWORD')){													// パスワード(空の場合あり)			
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 設定ファイルの作成
	 *
	 * @param string $msg		エラーメッセージ
	 * @return bool				true=成功、false=失敗
	 */
	function updateConfigFile(&$msg)
	{
		// 設定ファイルのフルパス
		$configFilePath = $this->gEnv->getIncludePath() . '/' . $this->configFile;
		
		// 現在の設定ファイルを読み込む
		if (!($file = @fopen($configFilePath, "r"))){
			$errMsg = '設定ファイルのオープンに失敗しました ファイル=' . $configFilePath;
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
 			return false;
		}
		$content = @fread($file, filesize($configFilePath));
		@fclose($file);
		
		// 引数で渡ってきた値を再設定する。コメント行は処理しない。
		foreach($this->defParam as $key => $val){
			if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'].*[\"'][ \t]*\)/m", $content)){
				$content = preg_replace("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'].*[\"'][ \t]*\)/m",
					"define('" . $key . "', '" . addslashes($val) . "')", $content);
			} else {
				$errMsg = '設定ファイルにキーが見つかりません key=' . $key;
				$this->gLog->error(__METHOD__, $errMsg);
				$msg = $errMsg;
				return false;
			}
		}
		
		// テンポラリファイルに保存
		if (!($tmpFilename = tempnam($this->gEnv->getWorkDirPath(), "m3_"))){
			$errMsg = 'テンポラリファイルの作成に失敗しました ディレクトリ=' . $this->gEnv->getWorkDirPath();
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
 			return false;
		}
		if (!($tmpFHandle = fopen($tmpFilename, "w"))){
			$errMsg = 'テンポラリファイルのオープンに失敗しました ファイル=' . $tmpFilename;
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成した設定ファイルを削除
			unlink($tmpFilename);
 			return false;
		}
		fwrite($tmpFHandle, $content);
		fclose($tmpFHandle);
		
		// 現在の設定ファイルとテンポラリファイルを入れ替え
		$tmpConfigFilePath = $configFilePath . '_tmp';
		if (!renameFile($configFilePath, $tmpConfigFilePath)){
			$errMsg = '設定ファイルを退避できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成した設定ファイルを削除
			unlink($tmpFilename);
			return false;
		}
		if (!renameFile($tmpFilename, $configFilePath)){
			$errMsg = '設定ファイルを作成できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成した設定ファイルを削除
			unlink($tmpFilename);
			
			// 設定ファイルを戻す
			renameFile($tmpConfigFilePath, $configFilePath);
			return false;
		}
		
		// 古い設定ファイルを削除
		unlink($tmpConfigFilePath);
		
		// PHPのキャッシュファイルを再読み込みする
		if (function_exists('opcache_reset')) @opcache_reset();
	
		return true;
	}
	/**
	 * オプションパラメータの追加または更新
	 *
	 * @param array $params		キーと値の連想配列
	 * @param string $msg		エラーの場合のエラーメッセージ
	 * @return bool				true=成功、false=失敗
	 */
	function updateOptionParam($params, &$msg)
	{
		$ret = $this->_editFileContent(array($this, '_updateOptionParam'), $params, $msg);
		return $ret;
	}
	/**
	 * オプションパラメータの追加または更新のコールバックメソッド
	 *
	 * @param array $params		オプションパラメータ
	 * @param string $content	ファイル内容
	 * @param string $msg		エラーの場合のエラーメッセージ
	 * @return bool				true=成功、false=失敗
	 */
	function _updateOptionParam($params, &$content, &$msg)
	{
		// ##### 定義内容変換処理 #####
		// 一旦PHPの終了タグを削除
		$content = str_replace('?>', '', $content);
		
		// 引数で渡ってきた値を再設定する。コメント行は処理しない。
		foreach ($params as $key => $val){
			if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'].*[\"'][ \t]*\)/m", $content)){
				$content = preg_replace("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'].*[\"'][ \t]*\)/m",
					"define('" . $key . "', '" . addslashes($val) . "')", $content);
			} else {		// キーが見つからない場合はファイルの最後に追加
				// オプション追加のコメントがない場合はコメントを追加
				$pos = strpos($content, $this->OPTION_DEF_COMMENT);
				if ($pos === false){
					$content = rtrim($content);
					$content .= M3_NL . M3_NL . $this->OPTION_DEF_COMMENT;
				}
				
				// 定義文を追加
				$content = rtrim($content);
				$content .= M3_NL . "define('" . $key . "', '" . addslashes($val) . "');" . M3_NL;
			}
		}
		
		// PHPの終了タグを追加
		$content = rtrim($content);
		$content .= M3_NL . '?>' . M3_NL;
		
		return true;		// 正常終了
	}
	/**
	 * オプションパラメータを削除
	 *
	 * @param string $msg		エラーの場合のエラーメッセージ
	 * @return bool				true=成功、false=失敗
	 */
	function removeOptionParam(&$msg)
	{
		$ret = $this->_editFileContent(array($this, '_removeOptionParam'), array(), $msg);
		return $ret;
	}
	/**
	 * オプションパラメータの追加または更新のコールバックメソッド
	 *
	 * @param array $params		オプションパラメータ(未使用)
	 * @param string $content	ファイル内容
	 * @param string $msg		エラーの場合のエラーメッセージ
	 * @return bool				true=成功、false=失敗
	 */
	function _removeOptionParam($params, &$content, &$msg)
	{
		// ##### 定義内容変換処理 #####
		// 一旦PHPの終了タグを削除
		$content = str_replace('?>', '', $content);
		
		// オプション追加のコメント以降を削除
		$pos = strpos($content, $this->OPTION_DEF_COMMENT);
		if ($pos !== false){
			$content = substr($content, 0, $pos);
		}

		// PHPの終了タグを追加
		$content = rtrim($content);
		$content .= M3_NL . '?>' . M3_NL;
		
		return true;		// 正常終了
	}
	/**
	 * システムのルートURL
	 */
	public function setSystemRootUrl($name)
	{
		$this->defParam['M3_SYSTEM_ROOT_URL'] = $name;
	}
	/**
	 * システムのルートURL
	 */
	public function getSystemRootUrl()
	{
		return $this->defParam['M3_SYSTEM_ROOT_URL'];
	}
	/**
	 * 接続先ＤＢ情報
	 */
	public function setDbConnectDsn($name)
	{
		$this->defParam['M3_DB_CONNECT_DSN'] = $name;
	}
	/**
	 * 接続先ＤＢ情報
	 */
	public function getDbConnectDsn()
	{
		return $this->defParam['M3_DB_CONNECT_DSN'];
	}
	/**
	 * 接続先ＤＢ情報を取得
	 */
	public function getDbConnectDsnByList(&$dbType, &$hostname, &$dbname)
	{
		// DB種別を取得
		$dsn = self::getDbConnectDsn();
		$pos = strpos($dsn, ':');
		if ($pos === false){
			$dbtype = '';
			$pos = -1;
		} else {
			$dbtype = trim(substr($dsn, 0, $pos));
		}
		// ホスト名、DB名を取得
		$hostname = '';
		$dbname = '';
		$dsnParams = explode(";", substr($dsn, $pos+1));
		for ($i = 0; $i < count($dsnParams); $i++){
			list($key, $value) = explode("=", $dsnParams[$i]);
			$key = trim($key);
			$value = trim($value);
			if ($key == 'host'){
				$hostname = $value;
			} else if ($key == 'dbname'){
				$dbname = $value;
			}
		}
		return true;
	}
	/**
	 * 接続ユーザ
	 */
	public function setDbConnectUser($name)
	{
		$this->defParam['M3_DB_CONNECT_USER'] = $name;
	}
	/**
	 * 接続ユーザ
	 */
	public function getDbConnectUser()
	{
		return $this->defParam['M3_DB_CONNECT_USER'];
	}
	/**
	 * 接続パスワード
	 */
	public function setDbConnectPassword($name)
	{
		$this->defParam['M3_DB_CONNECT_PASSWORD'] = $name;
	}
	/**
	 * 接続パスワード
	 */
	public function getDbConnectPassword()
	{
		return $this->defParam['M3_DB_CONNECT_PASSWORD'];
	}
	/**
	 * 設定ファイル内容を編集
	 *
	 * @param function  $callback		コールバック関数
	 * @param array $params				キーと値の連想配列
	 * @param string $msg				エラーの場合のエラーメッセージ
	 * @return bool						true=成功、false=失敗
	 */
	function _editFileContent($callback, $params, &$msg)
	{
		// 設定ファイルのフルパス
		$configFilePath = $this->gEnv->getIncludePath() . '/' . $this->configFile;
		
		// 現在の設定ファイルを読み込む
		if (!($file = @fopen($configFilePath, "r"))){
			$errMsg = '設定ファイルのオープンに失敗しました ファイル=' . $configFilePath;
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
 			return false;
		}
		$content = @fread($file, filesize($configFilePath));
		@fclose($file);
		
		// ##### ファイルコンテンツ編集処理 #####
		$result = call_user_func_array($callback, array($params, &$content, &$msg));
		if (!$result) return false;
		
		// テンポラリファイルに保存
		if (!($tmpFilename = tempnam($this->gEnv->getWorkDirPath(), "m3_"))){
			$errMsg = 'テンポラリファイルの作成に失敗しました ディレクトリ=' . $this->gEnv->getWorkDirPath();
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
 			return false;
		}
		if (!($tmpFHandle = fopen($tmpFilename, "w"))){
			$errMsg = 'テンポラリファイルのオープンに失敗しました ファイル=' . $tmpFilename;
 			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成した設定ファイルを削除
			unlink($tmpFilename);
 			return false;
		}
		fwrite($tmpFHandle, $content);
		fclose($tmpFHandle);
		
		// 現在の設定ファイルとテンポラリファイルを入れ替え
		$tmpConfigFilePath = $configFilePath . '_tmp';
		if (!renameFile($configFilePath, $tmpConfigFilePath)){
			$errMsg = '設定ファイルを退避できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成した設定ファイルを削除
			unlink($tmpFilename);
			return false;
		}
		if (!renameFile($tmpFilename, $configFilePath)){
			$errMsg = '設定ファイルを作成できません';
			$this->gLog->error(__METHOD__, $errMsg);
			$msg = $errMsg;
			
			// 作成した設定ファイルを削除
			unlink($tmpFilename);
			
			// 設定ファイルを戻す
			renameFile($tmpConfigFilePath, $configFilePath);
			return false;
		}
		
		// 古い設定ファイルを削除
		unlink($tmpConfigFilePath);
		return true;
	}
}
?>
