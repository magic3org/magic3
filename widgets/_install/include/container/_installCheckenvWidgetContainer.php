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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: _installCheckenvWidgetContainer.php 3791 2010-11-08 07:07:17Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/_installDb.php');

class _installCheckenvWidgetContainer extends _installBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new _installDB();
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
		return 'checkenv.tmpl.html';
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
		// 利用可能なDBをチェック
		// PostgreSQLのPDOが使用可能かどうか
		if (extension_loaded('pdo_pgsql')){
			$status = $this->_('available');
			$version = exec("psql --version", $output);	// PostgreSQLバージョン取得
			// 出力からバージョンを抜き出す
			$words = explode(' ', $output[0]);
			$version = $words[count($words)-1];		// 行末にはバージョン数値が存在する
			if (empty($version)) $version = $this->_('version unknown');// バージョン不明
			$status .= '(' . $version . ')';
			$data = '<b><font color="green">' . $status . '</font></b>';
		} else {
			$status = $this->_('not available(pdo_pgsql not installed)');		// 利用不可(pdo_pgsqlがインストールされていません)
			$data = '<b><font color="red">' . $status . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_postgresql_status", $data);

		// MySQLのPDOが使用可能かどうか
		if (extension_loaded('pdo_mysql')){
			$status = $this->_('available');
			$version = exec("mysql_config --version");	// MySQLバージョン取得
			if (empty($version)) $version = $this->_('version unknown');
			$status .= '(' . $version . ')';
			$data = '<b><font color="green">' . $status . '</font></b>';
		} else {
			$status = $this->_('not available(pdo_mysql not installed)');			// 利用不可(pdo_mysqlがインストールされていません)
			$data = '<b><font color="red">' . $status . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_mysql_status", $data);
		
		// PHPのバージョンをチェック
		$data = '<b><font color="green">' . $this->_('5.1.0 more') . '</font></b>';		// 5.1.0 以上
		$this->tmpl->addVar("_widget","config_php_version", $data);
		
		$phpVer = phpversion();
		if ($phpVer >= '5.1.0'){
			$data = '<b><font color="green">' . $phpVer . '</font></b>';
		} else {
			$data = '<b><font color="red">' . $phpVer . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_php_version", $data);
		
		// メモリ使用量のチェック
		$data = '<b><font color="green">' . M3_SYSTEM_MIN_MEMORY . $this->_('bytes(encouraged)') . '</font></b>';
		$this->tmpl->addVar("_widget","config_memory_limit", $data);
		
		$limit = ini_get('memory_limit');
		if (convBytes($limit) >= convBytes(M3_SYSTEM_MIN_MEMORY)){
			$data = '<b><font color="green">' . $limit . '</font></b>';
		} else {
			$data = '<b><font color="red">' . $limit . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_memory_limit", $data);
		
		// セーフモード
		$data = '<b><font color="green">' . $this->_('off(encouraged)') . '</font></b>';
		//$data = '<br />';
		$this->tmpl->addVar("_widget","config_safe_mode", $data);
		if (ini_get('safe_mode')){
			//$data = '<b><font color="red">on</font></b>';
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="green">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_safe_mode", $data);
	
		// Magic Quote
		$data = '<b><font color="green">off</font></b>';
		$this->tmpl->addVar("_widget","config_magic_quotes_gpc", $data);
		if (get_magic_quotes_gpc()){
			$data = '<b><font color="red">on</font></b>';
		} else {
			$data = '<b><font color="green">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_magic_quotes_gpc", $data);
		
		$data = '<b><font color="green">off</font></b>';
		$this->tmpl->addVar("_widget","config_magic_quotes_runtime", $data);
		if (get_magic_quotes_runtime()){
			$data = '<b><font color="red">on</font></b>';
		} else {
			$data = '<b><font color="green">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_magic_quotes_runtime", $data);
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_file_uploads", $data);
		if (ini_get('file_uploads')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_file_uploads", $data);
		
		// ######## PHP拡張オプションのインストール状況 ########
		// PDO
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_pdo", $data);
		if (extension_loaded('pdo')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off(' . $this->_('PDO not installed') . ')</font></b>';		// pdoがインストールされていません
		}
		$this->tmpl->addVar("_widget","current_pdo", $data);
		
		// mbstring
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_mbstring", $data);
		if (extension_loaded('mbstring')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_mbstring", $data);
		// mbstring.encoding_translation
		$data = '<b><font color="green">off</font></b>';
		$this->tmpl->addVar("_widget","config_mbstring_tran", $data);
		if (ini_get('mbstring.encoding_translation')){
			$data = '<b><font color="red">on</font></b>';
		} else {
			$data = '<b><font color="green">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_mbstring_tran", $data);

		// zlib
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_zlib", $data);
		if (extension_loaded('zlib')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_zlib", $data);
		// gd
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_gd", $data);
		if (extension_loaded('gd')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_gd", $data);
		// dom
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_dom", $data);
		if (extension_loaded('dom')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_dom", $data);
		// xml
		//$data = '<b><font color="green">offでも動作可</font></b>';
		$data = '';
		$this->tmpl->addVar("_widget","config_xml", $data);
		if (extension_loaded('xml')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_xml", $data);
		// gettext
/*		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_gettext", $data);
		if (extension_loaded('gettext')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_gettext", $data);*/
		
		// 設定ファイルのパス
		$data = $this->gConfig->configFilePath();
		$this->tmpl->addVar("_widget","current_config_path", $data);
		
		// 設定ファイルが書き込み可能かどうかチェック
		$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
		$this->tmpl->addVar("_widget","config_access", $data);
		
		if ($this->gConfig->isConfigDirWritable()){
			if ($this->gConfig->isConfigFileWritable()){
				$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
			} else {
				$data = '<b><font color="red">' . $this->_('not writable') . '</font></b>';
			}
		} else {
			$data = '<b><font color="red">' . $this->_('directory not writable') . '</font></b>';			// ディレクトリ書き込み不可
		}
		$this->tmpl->addVar("_widget","current_access", $data);
		
		// クッキーの使用
		$data = '<b><font color="green">on</font></b>';
		$this->tmpl->addVar("_widget","config_sess_use_cookies", $data);
		if (ini_get('session.use_cookies')){
			$data = '<b><font color="green">on</font></b>';
		} else {
			$data = '<b><font color="red">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_sess_use_cookies", $data);
		
		// ##### セッション関係 #####
		// セッションIDの自動付加
		$data = '<b><font color="green">off</font></b>';
		$this->tmpl->addVar("_widget","config_sess_use_trans_sid", $data);
		if (ini_get('session.use_trans_sid')){
			$data = '<b><font color="red">on</font></b>';
		} else {
			$data = '<b><font color="green">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_sess_use_trans_sid", $data);
		
		// セッション起動
		$data = '<b><font color="green">off</font></b>';
		$this->tmpl->addVar("_widget","config_sess_auto_start", $data);
		if (ini_get('session.auto_start')){
			$data = '<b><font color="red">on</font></b>';
		} else {
			$data = '<b><font color="green">off</font></b>';
		}
		$this->tmpl->addVar("_widget","current_sess_auto_start", $data);
		
		// セッション保存用パス
		$sp = ini_get('session.save_path');
		$this->tmpl->addVar("_widget","current_session_path", $sp);
						
		//$data = '<b><font color="green">どちらでも可</font></b>';
		$data = '<br />';
		$this->tmpl->addVar("_widget","config_session_access", $data);
		
		if (@is_writable($sp)){
			$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
		} else {
			$data = '<b><font color="green">' . $this->_('not writable') . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_session_access", $data);
		
		// セッションID
		$data = '<b><font color="green">' . $this->_('enable') . '</font></b>';
		$this->tmpl->addVar("_widget","config_session_id", $data);
		$sid = session_id();
		if (empty($sid)){
			$data = '<b><font color="red">' . $this->_('disable') . '</font></b>';
		} else {
			$data = '<b><font color="green">' . $sid . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_session_id", $data);
		
		// 一時ディレクトリパス
		//$path = M3_SYSTEM_WORK_DIR_PATH;
		$path = $this->gEnv->getWorkDirPath();
		$this->tmpl->addVar("_widget","current_tmp_dir", $path);
		
		$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
		$this->tmpl->addVar("_widget","config_tmp_dir_access", $data);
		
		if (is_writable($path)){
			$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
		} else {
			$data = '<b><font color="red">' . $this->_('not writable') . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_tmp_dir_access", $data);
		
		// 画像格納ディレクトリパス
		$path = $this->gEnv->getResourcePath();
		$this->tmpl->addVar("_widget","current_resource_dir", $path);
						
		$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
		$this->tmpl->addVar("_widget","config_resource_dir_access", $data);
		
		if (is_writable($path)){
			$data = '<b><font color="green">' . $this->_('writable') . '</font></b>';
		} else {
			$data = '<b><font color="red">' . $this->_('not writable') . '</font></b>';
		}
		$this->tmpl->addVar("_widget","current_resource_dir_access", $data);
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['title_check_env'] = $this->_('Check Install Environment');
		$localeText['label_check_item'] = $this->_('Check Item');
		$localeText['label_required_value'] = $this->_('Required Value');
		$localeText['label_current_value'] = $this->_('Current Value');
		$localeText['label_php_version'] = $this->_('PHP Version');
		$localeText['label_memory_limit'] = $this->_('Memory Limit(bytes)');			// 最大メモリ量(バイト)
		$localeText['label_permit_upload'] = $this->_('Permit file uploading');			// ファイルアップロード許可
		$localeText['label_session_path'] = $this->_('Session Path');	// セッション保存パス
		$localeText['label_session_path_permission'] = $this->_('Session Path Permission');// セッション保存パスのアクセス権
		$localeText['label_session_id'] = $this->_('Session ID');// セッションID
		$localeText['label_tmp_dir'] = $this->_('Temporary Directory(Install Use)');// 一時ディレクトリ(インストール時に使用する一時ディレクトリ)
		$localeText['label_tmp_dir_permission'] = $this->_('Temporary Directory Permission');// 一時ディレクトリのアクセス権
		$localeText['label_msg_dir_permission'] = $this->_('If directory is not writable, change permission to write or chenge directory to be writable.');// 書き込み不可の場合は書き込み可能なディレクトリあるいはアクセス権に設定する必要あり
		$localeText['label_magic3_config_file'] = $this->_('Magic3 Config File');		 // Magic3設定ファイルのパス
		$localeText['label_magic3_config_file_permission'] = $this->_('Magic3 Config File Permission');// Magic3設定ファイルのアクセス権
		$localeText['label_resource_dir'] = $this->_('Resource Directory(for Image files etc)');// リソース(画像等)格納ディレクトリ
		$localeText['label_resource_dir_permission'] = $this->_('Resource Directory Permission');// リソース(画像等)格納ディレクトリのアクセス権
		$this->setLocaleText($localeText);
	}
}
?>
