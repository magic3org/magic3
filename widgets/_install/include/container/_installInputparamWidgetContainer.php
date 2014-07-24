<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: _installInputparamWidgetContainer.php 3791 2010-11-08 07:07:17Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/_installDb.php');

class _installInputparamWidgetContainer extends _installBaseWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'inputparam.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		// ########### 画面項目の表示制御 ############
		// PostgreSQLのPDOが使用可能かどうか
		$canUsePgsql = false;
		$canUseMysql = false;
		// PostgreSQLのPDOが使用可能かどうか
		if (extension_loaded('pdo_pgsql')){
			$this->tmpl->setAttribute("db_pgsql", "visibility", "visible");
			$canUsePgsql = true;
		} else {
			$this->tmpl->setAttribute("db_pgsql", "visibility", "hidden");
		}
		// MySQLのPDOが使用可能かどうか
		if (extension_loaded('pdo_mysql')){
			$this->tmpl->setAttribute("db_mysql", "visibility", "visible");
			$canUseMysql = true;
		} else {
			$this->tmpl->setAttribute("db_mysql", "visibility", "hidden");
		}
		if (!$canUsePgsql && !$canUseMysql) $this->setMsg(self::MSG_USER_ERR, $this->_('Can\'t use database.'));			// DBが使用できません
		
		$dbtype = '';
		$isConfigured = false;		// 設定ファイルが作成されたかどうか
		$isTested = false;			// 接続テスト完了かどうか
		$act = $request->trimValueOf('act');
		if (empty($act)){		// 初期状態
			// 使用可能なDBが１つのときはデフォルトとする
			if ($canUsePgsql && !$canUseMysql){
				$dbtype = M3_DB_TYPE_PGSQL;
			} else if (!$canUsePgsql && $canUseMysql){
				$dbtype = M3_DB_TYPE_MYSQL;
			}
			
			// 設定ファイルがある場合は、現在値を取得
			if ($this->gConfig->isConfigured()){
				$rooturl = $this->gConfig->getSystemRootUrl();
				$dsn = $this->gConfig->getDbConnectDsn();
				// DB種別を取得
				$pos = strpos($dsn, ':');
				if ($pos === false){
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
				$dbuser = $this->gConfig->getDbConnectUser();
				$password = $this->gConfig->getDbConnectPassword();
			} else {// 設定値がないときはデフォルト値を設定
				$sytemRootUrl = $this->gEnv->calcSystemRootUrl();
				if (empty($sytemRootUrl)) $sytemRootUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . M3_SYSTEM_ROOT_DIR_NAME;
				$rooturl = $sytemRootUrl;
				$hostname = 'localhost';
				$dbname = '';
				$dbuser = '';
				$password = '';
			}
		} else if ($act == 'save' || $act == 'testdb'){
			// ### 設定ファイルを更新した場合は次の画面へ遷移 ###
			// 入力項目のエラーチェック
			$rooturl = $request->trimValueOf('rooturl');
			$this->checkUrl($rooturl, 'ルートURL');
			$hostname = $request->trimValueOf('hostname');
			//$this->checkSingleByte($hostname, "ホスト名");// IPアドレスを入力したとき「.」が除かれる
			$dbuser = $request->trimValueOf('dbuser');
			$this->checkSingleByte($dbuser, $this->_('User'));		// DBユーザ
			$password = $request->trimValueOf('password');
			$dbname = $request->trimValueOf('dbname');
			$this->checkSingleByte($dbname, $this->_('Database Name'));			// DB名
			
			// DB種別
			$dbtype = $request->trimValueOf('dbtype');
			if ($dbtype != M3_DB_TYPE_PGSQL && $dbtype != M3_DB_TYPE_MYSQL){		// DBが選択されていないとき
				$this->setMsg(self::MSG_USER_ERR, $this->_('Database Type not selected.'));		// DB種別が設定されていません
			}

			if ($this->getMsgCount() == 0){// 入力チェックOKの場合
				if ($act == 'save'){		// 設定ファイルに保存の場合
					// 設定ファイルの作成
					$dsn = $dbtype . ':' . 'host=' . $hostname . ';dbname=' . $dbname;
					$updateParam = array();
					$this->gConfig->setDbConnectDsn($dsn);		// 接続先ＤＢ
					$this->gConfig->setDbConnectUser($dbuser);// 接続ユーザ
					$this->gConfig->setDbConnectPassword($password);	// パスワード
					$this->gConfig->setSystemRootUrl($rooturl);		// システムのルートURL
					$ret = $this->gConfig->updateConfigFile($msg);
			
					// 完了メッセージを出力
					if ($ret){
						$this->setMsg(self::MSG_GUIDANCE, $this->_('Configration updated.'));		// 設定値を更新しました
						$isConfigured = true;
					} else {
						$this->setMsg(self::MSG_APP_ERR, $msg);
						$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating configration.'));		// 設定値の更新に失敗しました
					}
				} else if ($act == 'testdb'){		// DB接続テスト
					$dsn = $dbtype . ':' . 'host=' . $hostname . ';dbname=' . $dbname;
					$db = new _installDB();
					if ($db->testDbConnection($dsn, $dbuser, $password)){
						$msg = '<b><font color="green">' . $this->_('Succeeded in connecting database.') . '</font></b>';			// 接続正常
						$msg .= ' => ';
						if ($db->testDbTable($dsn, $dbuser, $password)){
							$msg .= '<b><font color="green">' . $this->_('Succeeded in creating table.') . '</font></b>';	// テーブル作成正常
						} else {
							$msg .= '<b><font color="red">' . $this->_('Failed in creating table.') . '</font></b>';			// テーブル作成エラー
						}
						$isTested = true;			// 接続テスト完了かどうか
					} else {
						$msg .= '<b><font color="red">' . $this->_('Failed in connecting database.') . '</font></b>';		// 接続エラー
					}
					$this->tmpl->addVar('_widget', 'db_test',	$msg);
				}
			}
		}
		// 入力データを再設定
		if ($dbtype == M3_DB_TYPE_PGSQL){
			$this->tmpl->addVar("db_pgsql", "checked", "checked");
		} else if ($dbtype == M3_DB_TYPE_MYSQL){
			$this->tmpl->addVar("db_mysql", "checked", "checked");
		}
		$this->tmpl->addVar("_widget", "root_url",	$rooturl);
		$this->tmpl->addVar("_widget", "dbuser",		$dbuser);
		$this->tmpl->addVar("_widget", "hostname",	$hostname);
		$this->tmpl->addVar("_widget", "password",	$password);
		$this->tmpl->addVar("_widget", "dbname",		$dbname);
		
		// 設定ファイルの内容をみて、ボタンを制御
		if (!$this->gConfig->isConfigured() && !$isConfigured) $this->tmpl->addVar('_widget', 'button_disabled', 'disabled');
		
		// 次の操作のボタンのカラーを設定
		$buttonTestConnection = $buttonGoBack = $buttonUpdateConfig = $buttonGoNext = 'btn-primary';
		if ($this->gConfig->isConfigured()){		// 設定が保存されているとき
			$buttonGoNext = 'btn-success';			// 「次へ」ボタンをアクティブ化
		} else if ($isConfigured){
			$buttonGoNext = 'btn-success';			// 「次へ」ボタンをアクティブ化
		} else if ($isTested){
			$buttonUpdateConfig = 'btn-success';		// 「設定値を更新」ボタンをアクティブ化
		} else {
			$buttonTestConnection = 'btn-success';	// 「接続テスト」ボタンをアクティブ化
		}
		$this->tmpl->addVar("_widget", "button_test_connection",	$buttonTestConnection);
		$this->tmpl->addVar("_widget", "button_go_back",	$buttonGoBack);
		$this->tmpl->addVar("_widget", "button_update_config",	$buttonUpdateConfig);
		$this->tmpl->addVar("_widget", "button_go_next",	$buttonGoNext);
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['label_update_config'] = $this->_('Update Config');		// 設定値を更新
		$localeText['label_test_connection'] = $this->_('Test Connection');// 接続テスト
		$localeText['msg_change_config'] = $this->_('Configration changed.\\nUpdate configration?');// 設定値が変更されています\n設定値を保存しますか?
		$localeText['msg_update_config'] = $this->_('Update configration?');//設定値を更新しますか?
		$localeText['title_input_site_info'] = $this->_('Input Site Information');	// サイト情報入力
		
		$localeText['label_site_info'] = $this->_('Site Information');	// サイト情報
		$localeText['label_root_url'] = $this->_('Root URL');	// ルートURL
		$localeText['label_db_info'] = $this->_('Database Information');	// DB接続情報
		$localeText['label_db_type'] = $this->_('Database Type');	// DB種別
		$localeText['label_db_hostname'] = $this->_('Hostname');	// ホスト名
		$localeText['label_db_name'] = $this->_('Database Name');	// DB名
		$localeText['label_db_user'] = $this->_('User');	// DBユーザ
		$localeText['label_db_password'] = $this->_('Password');	// パスワード
		$this->setLocaleText($localeText);
	}
}
?>
