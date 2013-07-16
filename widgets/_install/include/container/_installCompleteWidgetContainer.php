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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: _installCompleteWidgetContainer.php 5060 2012-07-23 08:42:48Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');

class _installCompleteWidgetContainer extends _installBaseWidgetContainer
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
//		$this->db = new _installDB();
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
		return 'complete.tmpl.html';
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
		$dbStatus = $request->trimValueOf('dbstatus');		// DBの状態
		$type = $request->trimValueOf('install_type');
		$dbStatus = $request->trimValueOf('dbstatus');		// DBの状態
		$act = $request->trimValueOf('act');
		if ($act == 'delinstaller'){		// インストーラを削除の場合
			// ファイルを退避する
			$this->gInstance->getFileManager()->backupInstaller();
			
			// 管理者画面へ遷移
			$this->gPage->redirectToDirectory();
			//$this->gPage->redirect($this->gEnv->getDefaultAdminUrl());			// Firefox5でadmin/install.phpにリダイレクトされるバグ(URLがキャッシュされる?)回避　2011/7/1
		}
		
		// アクセス用URLを表示
		$this->tmpl->addVar('_widget', 'url',			$this->gConfig->getSystemRootUrl());
		$this->tmpl->addVar('_widget', 'url_admin',	$this->gConfig->getSystemRootUrl() . '/' . M3_SYSTEM_ADMIN_DIR_NAME);
		$this->tmpl->addVar("_widget", "db_status", $dbStatus);
		$this->tmpl->addVar("_widget", "install_type", $type);			// インストールタイプ
		if ($dbStatus == 'update'){			// DB更新の場合
			$this->tmpl->addVar("_widget", "fore_task", "copyfile");			// 旧システムのファイルをコピー
			
			// ユーザ、パスワード
			$this->tmpl->setAttribute('show_login_info', 'visibility', 'visible');
			$this->tmpl->addVar('show_login_info', 'message',	$this->_('You can login by existing user.'));		// 既存のユーザでログインできます
		} else {
			$this->tmpl->addVar("_widget", "fore_task", "initother");		// その他のDB初期化
			
			// ユーザ、パスワード
			$this->tmpl->setAttribute('show_login_user', 'visibility', 'visible');
			$this->tmpl->addVar('show_login_user', 'user',		'admin');		// 初期管理ユーザ
			$this->tmpl->addVar('show_login_user', 'password',	'admin');		// 初期管理ユーザのパスワード
		}
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['title_install_complete'] = $this->_('Install Completed');		// インストール完了
		$localeText['msg_install_complete'] = $this->_('Succeeded in installing Magic3 system.');// Magic3のインストール完了しました
		$localeText['label_main_url'] = $this->_('Main URL is');		// メインのURLは
		$localeText['label_admin_url'] = $this->_('Administration URL is');		// 管理機能のURLは
		$localeText['label_user'] = $this->_('User:');// ユーザ：
		$localeText['label_password'] = $this->_('Password:');// パスワード：
		$localeText['label_close'] = $this->_('Delete Installer and Close');// インストーラを削除して終了
		$this->setLocaleText($localeText);
	}
}
?>
