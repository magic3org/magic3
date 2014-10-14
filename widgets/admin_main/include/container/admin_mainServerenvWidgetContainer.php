<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainServerenvWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const CF_DISTRIBUTION_NAME = 'distribution_name';		// ディストリビューション名
	const CF_DISTRIBUTION_VERSION = 'distribution_version';		// ディストリビューションバージョン
	const CF_SERVER_ID = 'server_id';		// サーバID
	const CF_INSTALL_DT = 'install_dt';		// システムインストール日時
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'serverenv.tmpl.html';
	}
	/**
	 * ヘルプデータを設定
	 *
	 * ヘルプの設定を行う場合はヘルプIDを返す。
	 * ヘルプデータの読み込むディレクトリは「自ウィジェットディレクトリ/include/help」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ヘルプID。ヘルプデータはファイル名「help_[ヘルプID].php」で作成。ヘルプを使用しない場合は空文字列「''」を返す。
	 */
	function _setHelp($request, &$param)
	{	
		return 'serverenv';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		// バージョン
		$this->tmpl->addVar("_widget", "distribution_name", $this->db->getSystemConfig(self::CF_DISTRIBUTION_NAME));		// ディストリビューション名
		$value = $this->db->getSystemConfig(self::CF_DISTRIBUTION_VERSION);
		if (empty($value)) $value = M3_SYSTEM_VERSION;
		$this->tmpl->addVar("_widget", "distribution_version", $value);		// ディストリビューションバージョン
		$this->tmpl->addVar("_widget", "magic3_version", M3_SYSTEM_VERSION);
		$this->tmpl->addVar("_widget", "php_version", phpversion());
		if ($this->db->getDbType() == M3_DB_TYPE_MYSQL){		// MySQLの場合
			$dbType = 'MySQL';
		} else if ($this->db->getDbType() == M3_DB_TYPE_PGSQL){// PostgreSQLの場合
			$dbType = 'PostgreSQL';
		} else {
			$dbType = 'DB未設定';
		}
		$this->tmpl->addVar("_widget", "db_type", $dbType);			// 使用しているDB種
		$this->tmpl->addVar("_widget", "db_version", $this->db->getDbVersion());
		$this->tmpl->addVar("_widget", "os_version", php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m'));		// OSバージョン
		
		// DB接続
		$this->gConfig->getDbConnectDsnByList($dbType, $hostname, $dbname);
		$this->tmpl->addVar("_widget", "db_type", $dbType);			// DB種
		$this->tmpl->addVar("_widget", "db_host_name", $hostname);			// DBホスト名
		$this->tmpl->addVar("_widget", "db_name", $dbname);			// DB名
		$dbuser = $this->gConfig->getDbConnectUser();		// 接続ユーザ
		$this->tmpl->addVar("_widget", "db_user_name", $dbuser);			// 接続ユーザ名
				
		// mbstring
		if (extension_loaded('mbstring')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_mbstring", $data);
		// zlib
		if (extension_loaded('zlib')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_zlib", $data);
		// gd
		if (extension_loaded('gd')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_gd", $data);
		// dom
		if (extension_loaded('dom')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_dom", $data);
		// xml
		if (extension_loaded('xml')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_xml", $data);
		// gettext
		if (extension_loaded('gettext')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_gettext", $data);
		// curl
		if (extension_loaded('curl')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_curl", $data);
		// ファイルのアップロード許可
		if (ini_get('file_uploads')){
			$data = '<b><font color="green">On</font></b>';
		} else {
			$data = '<b><font color="red">Off</font></b>';
		}
		$this->tmpl->addVar("_widget", "file_uploads", $data);
		
		// メモリサイズ
		$this->tmpl->addVar("_widget", "upload_filesize_limit", $this->gSystem->getMaxFileSizeForUpload());
		$this->tmpl->addVar("_widget", "memory_limit", ini_get('memory_limit'));
		$this->tmpl->addVar("_widget", "post_max_size", ini_get('post_max_size'));
		$this->tmpl->addVar("_widget", "upload_max_filesize", ini_get('upload_max_filesize'));
		
		// サーバ環境
		$hostname = exec('hostname');
		$this->tmpl->addVar("_widget", "host_name", $hostname);
		$dnsResolv = '解決できません';
		if ($hostname != 'localhost.localdomain'){
			$hosts = gethostbynamel($hostname);
			if ($hosts !== false){
				if (count($hosts) > 0) $dnsResolv = $hosts[0];
			}
		}
		$this->tmpl->addVar("_widget", "dns_resolv", $dnsResolv);
		$this->tmpl->addVar("_widget", "server_id", $this->db->getSystemConfig(self::CF_SERVER_ID));
		$this->tmpl->addVar("_widget", "install_dt", $this->db->getSystemConfig(self::CF_INSTALL_DT));		// インストール日時
		
		// phpinfo出力へのURL
		$phpinfoUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_SHOW_PHPINFO;			// phpinfo画面
		$this->tmpl->addVar("_widget", "phpinfo_url", $phpinfoUrl);
	}
}
?>
