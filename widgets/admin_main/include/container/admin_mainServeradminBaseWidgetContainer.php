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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainServeradminBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $cmdPath;		// ジョブ格納ディレクトリ
	
	const BREADCRUMB_TITLE	= 'サーバ管理';		// パンくずリストトップタイトル
	const JOB_OPTION_FILE_DIR	= 'file';		// ジョブ用ファイル格納ディレクトリ
	// 画面
	const TASK_SERVERINFO		= 'serverinfo';			// サーバ情報
	const TASK_SITELIST			= 'sitelist';			// サイト一覧
	const TASK_SITELIST_DETAIL	= 'sitelist_detail';	// サイト詳細
	const DEFAULT_TASK			= 'serverinfo';			// デフォルトの画面
	// ジョブコマンド
	const CMD_FILENAME_CREATE_SITE				= 'CMD_00_CREATESITE';			// サイト作成ジョブファイル名
	const CMD_FILENAME_REMOVE_SITE				= 'CMD_00_REMOVESITE';			// サイト削除ジョブファイル名
	const CMD_FILENAME_UPDATE_INSTALL_PACKAGE	= 'CMD_00_UPDATEINSTALLPACKAGE';			// インストールパッケージ取得ジョブファイル名
	const CMD_FILENAME_UPDATE_SSL				= 'CMD_00_UPDATESSL';			// SSL認証書の更新、コマンド実行ファイル
	
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
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
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
		$cmdFile_create_site = $this->cmdPath . DIRECTORY_SEPARATOR . self::CMD_FILENAME_CREATE_SITE;		// サイト作成、コマンド実行ファイル
		$cmdFile_remove_site = $this->cmdPath . DIRECTORY_SEPARATOR . self::CMD_FILENAME_REMOVE_SITE;		// サイト削除、コマンド実行ファイル
		$cmdFile_update_insatll_package = $this->cmdPath . DIRECTORY_SEPARATOR . self::CMD_FILENAME_UPDATE_INSTALL_PACKAGE;			// インストールパッケージ取得ジョブファイル名
		if (file_exists($cmdFile_create_site)){
			$this->setUserErrorMsg('サイトの作成中です');
			$isShown = true;			// メッセージ表示あり
		}
		if (file_exists($cmdFile_remove_site)){
			$this->setUserErrorMsg('サイトの削除中です');
			$isShown = true;			// メッセージ表示あり
		}
		if (file_exists($cmdFile_update_insatll_package)){
			$this->setUserErrorMsg('インストーラの更新中です');
			$isShown = true;			// メッセージ表示あり
		}
		return $isShown;
	}
}
?>
