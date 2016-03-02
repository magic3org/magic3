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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainServeradminBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $cmdPath;		// ジョブ格納ディレクトリ
	protected $cmdFile_create_site;				// サイト作成、コマンド実行ファイル
	protected $cmdFile_remove_site;				// サイト削除、コマンド実行ファイル
	protected $cmdFile_update_install_package;	// インストールパッケージ取得ジョブファイル名
	protected $cmdFile_update_ssl;				// SSL認証書の更新、コマンド実行ファイル
	protected $isShownJobStatus;	// ジョブの実行状況を表示
	
	const BREADCRUMB_TITLE	= 'サーバ管理';		// パンくずリストトップタイトル
	const JOB_OPTION_FILE_DIR	= 'file';		// ジョブ用ファイル格納ディレクトリ
	// 画面
	const TASK_SERVERINFO		= 'serverinfo';			// サーバ情報
	const TASK_SITELIST			= 'sitelist';			// サイト一覧
	const TASK_SITELIST_DETAIL	= 'sitelist_detail';	// サイト詳細
	const TASK_SERVERTOOL		= 'servertool';			// サーバ管理ツール
	const DEFAULT_TASK			= 'serverinfo';			// デフォルトの画面
	// ジョブタイプ
	const JOB_TYPE_CREATE_SITE				= 'CREATESITE';				// サイト作成ジョブ
	const JOB_TYPE_REMOVE_SITE				= 'REMOVESITE';				// サイト削除ジョブ
	const JOB_TYPE_UPDATE_INSTALL_PACKAGE	= 'UPDATEINSTALLPACKAGE';	// インストールパッケージ取得ジョブ
	const JOB_TYPE_UPDATE_SSL				= 'UPDATESSL';				// SSL認証書の更新ジョブ
	// ジョブコマンドファイル
	const CMD_FILENAME_CREATE_SITE				= 'CMD_00_CREATESITE';				// サイト作成ジョブファイル名
	const CMD_FILENAME_REMOVE_SITE				= 'CMD_00_REMOVESITE';				// サイト削除ジョブファイル名
	const CMD_FILENAME_UPDATE_INSTALL_PACKAGE	= 'CMD_00_UPDATEINSTALLPACKAGE';	// インストールパッケージ取得ジョブファイル名
	const CMD_FILENAME_UPDATE_SSL				= 'CMD_00_UPDATESSL';				// SSL認証書の更新ジョブファイル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ジョブ格納ディレクトリ
		$this->cmdPath = $this->gEnv->getCronjobsPath();
		if (!file_exists($this->cmdPath)) mkdir($this->cmdPath, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		
		// コマンドファイルパス
		$this->cmdFile_create_site 				= $this->cmdPath . M3_DS . self::CMD_FILENAME_CREATE_SITE;		// サイト作成、コマンド実行ファイル
		$this->cmdFile_remove_site				= $this->cmdPath . M3_DS . self::CMD_FILENAME_REMOVE_SITE;		// サイト削除、コマンド実行ファイル
		$this->cmdFile_update_install_package	= $this->cmdPath . M3_DS . self::CMD_FILENAME_UPDATE_INSTALL_PACKAGE;			// インストールパッケージ取得ジョブファイル名
		$this->cmdFile_update_ssl				= $this->cmdPath . M3_DS . self::CMD_FILENAME_UPDATE_SSL;		// SSL認証書の更新、コマンド実行ファイル
	}
	/**
	 * テンプレートに前処理
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _preAssign($request, &$param)
	{
		// ジョブの実行状況を表示
		$this->isShownJobStatus = $this->_showJobStatus();
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
	function _postAssign($request, &$param)
	{
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;
		
		// パンくずリストの作成
		$titles = array(self::BREADCRUMB_TITLE);
		switch ($task){
			case self::TASK_SERVERINFO:			// サーバ情報
				$titles[] = 'サーバ情報';
				break;
			case self::TASK_SITELIST:			// サイト一覧
			case self::TASK_SITELIST_DETAIL:	// サイト詳細
				$titles[] = 'サイト一覧';
				break;
			case self::TASK_SERVERTOOL:			// サーバ管理ツール
				$titles[] = 'サーバ管理ツール';
				break;
		}
		$this->gPage->setAdminBreadcrumbDef($titles);
		
		// メニューバーの作成
		$navbarDef = new stdClass;
		$navbarDef->title = '';
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= '';// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'サーバ情報',
									'task'		=> self::TASK_SERVERINFO,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_SERVERINFO),
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'サイト一覧',
									'task'		=> self::TASK_SITELIST,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_SITELIST || $task == self::TASK_SITELIST_DETAIL),
									'submenu'	=> array()
								),
								(Object)array(
									'name'		=> 'サーバ管理ツール',
									'task'		=> self::TASK_SERVERTOOL,
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> ($task == self::TASK_SERVERTOOL),
									'submenu'	=> array()
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
		
		// ジョブタイプの設定
		$this->tmpl->addVar('_widget', 'job_type_create_site',				self::JOB_TYPE_CREATE_SITE);				// サイト作成ジョブ
		$this->tmpl->addVar('_widget', 'job_type_remove_site',				self::JOB_TYPE_REMOVE_SITE);				// サイト削除ジョブ
		$this->tmpl->addVar('_widget', 'job_type_update_install_package',	self::JOB_TYPE_UPDATE_INSTALL_PACKAGE);		// インストールパッケージ取得ジョブ
		$this->tmpl->addVar('_widget', 'job_type_update_ssl',				self::JOB_TYPE_UPDATE_SSL);					// SSL認証書の更新ジョブ
	}
	/**
	 * ジョブの実行状況を表示
	 *
	 * @return bool			メッセージ表示ありかどうか
	 */
	function _showJobStatus()
	{
		$isShown = false;
		
		// ジョブの実行状況を表示
		if (file_exists($this->cmdFile_create_site)){
			$this->setUserErrorMsg('サイトの作成中です');
			$isShown = true;			// メッセージ表示あり
		}
		if (file_exists($this->cmdFile_remove_site)){
			$this->setUserErrorMsg('サイトの削除中です');
			$isShown = true;			// メッセージ表示あり
		}
		if (file_exists($this->cmdFile_update_install_package)){
			$this->setUserErrorMsg('インストーラの更新中です');
			$isShown = true;			// メッセージ表示あり
		}
		if (file_exists($this->cmdFile_update_ssl)){
			$this->setUserErrorMsg('SSL証明書の更新中です');
			$isShown = true;			// メッセージ表示あり
		}
		return $isShown;
	}
}
?>
