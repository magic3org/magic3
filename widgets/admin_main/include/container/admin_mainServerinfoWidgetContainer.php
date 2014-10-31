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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCommonPath() .	'/gitRepo.php');

class admin_mainServerinfoWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $cmdPath;		// ジョブ格納ディレクトリ
	const MAGIC3_SRC_VER_FILE = '/var/magic3/src_version';
	const WATCH_JOB_STATUS_FILE = 'STATUS';		// ジョブ状態確認用ファイル
	const CMD_FILENAME_CREATE_SITE = 'CMD_00_CREATESITE';			// サイト作成ジョブファイル名
	const CMD_FILENAME_REMOVE_SITE = 'CMD_00_REMOVESITE';			// サイト削除ジョブファイル名
	const CMD_FILENAME_UPDATE_INSTALL_PACKAGE = 'CMD_00_UPDATEINSTALLPACKAGE';			// インストールパッケージ取得ジョブファイル名
	
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
		return 'serverinfo.tmpl.html';
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
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
		$base = 1024;
		$path = '/';
		$cmdFile_update_install_package = $this->cmdPath . DIRECTORY_SEPARATOR . self::CMD_FILENAME_UPDATE_INSTALL_PACKAGE;		// インストールパッケージの更新、コマンド実行ファイル
		
		// ジョブの実行状況を表示
		$isShownJobStatus = $this->_showJobStatus();

		$act = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_ACT);
		if ($act == 'getnewsrc'){		// 最新インストールパッケージ取得のとき
			// コマンドファイルにパラメータを書き込む
			$cmdContent = '';
			$email = $this->gEnv->getSiteEmail();
			if (!empty($email)) $cmdContent .= 'mailto=' . $email . "\n";
			$ret = file_put_contents($cmdFile_update_install_package, $cmdContent, LOCK_EX/*排他的アクセス*/);
			if ($ret !== false){
				$this->tmpl->setAttribute('show_process_dialog', 'visibility', 'visible');		// 処理結果監視
			}
		} else if ($act == 'getinfo'){		// 最新情報取得
			if (file_exists($cmdFile_update_install_package)){
				$this->gInstance->getAjaxManager()->addData('code', '0');
			} else {			// インストールパッケージ更新完了のとき
				$this->gInstance->getAjaxManager()->addData('code', '1');
			}
			return;
		}

		//全体サイズ
		$totalBytes = @disk_total_space($path);
		if ($totalBytes === false){
			$this->SetMsg(self::MSG_APP_ERR, $this->_('Can not access the page.'));		// アクセスできません
			return;
		}
		$class = min((int)log($totalBytes , $base) , count($units) - 1);
		$totalStr = sprintf('%1.2f' , $totalBytes / pow($base, $class)) . $units[$class];

		//空き容量
		$freeBytes = disk_free_space($path);
		$class = min((int)log($freeBytes , $base) , count($units) - 1);
		$freeStr = sprintf('%1.2f' , $freeBytes / pow($base, $class)) . $units[$class];

		//使用容量
		$usedBytes = $totalBytes - $freeBytes;
		$class = min((int)log($usedBytes , $base) , count($units) - 1);
		$usedStr = sprintf('%1.2f' , $usedBytes / pow($base, $class)) . $units[$class];

		//使用率
		$usedRateStr = round($usedBytes / $totalBytes * 100, 2) . '%';
		
		// Magic3のソースバージョン
		$repo = new GitRepo('magic3org', 'magic3');
		$latestVersion = $repo->getLatestVersionStrByTag();
		if (file_exists(self::MAGIC3_SRC_VER_FILE)){
			$srcVer = file_get_contents(self::MAGIC3_SRC_VER_FILE);
			$srcVer = trim($srcVer);
			
			// 最新バージョンの場合はインストール不可
			$this->tmpl->addVar("_widget", "update_src_button_disabled", $this->convertToDisabledString((!empty($srcVer) && version_compare($srcVer, $latestVersion) == 0) || $isShownJobStatus));
			
			// 最新バージョン表示用
			$versionInfoStr = '<span class="available">(最新版 ' . $latestVersion . ')</span>';
		} else {
			$srcVer = '未取得';
		}
		
		// ジョブ監視状況
		$watchJobStatus = '<span class="stopped">停止</span>';
		if (file_exists($this->cmdPath . DIRECTORY_SEPARATOR . self::WATCH_JOB_STATUS_FILE)){
			// 10分以内にジョブが実行されている場合は稼動にする
			$time = filemtime($this->cmdPath . DIRECTORY_SEPARATOR . self::WATCH_JOB_STATUS_FILE);
			if (time() - $time < 60 * 10) $watchJobStatus = '<span class="running">稼動中</span>';
		}
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'total_size',	$this->convertToDispString($totalStr));
		$this->tmpl->addVar('_widget', 'free_size',		$this->convertToDispString($freeStr));
		$this->tmpl->addVar('_widget', 'used_size',		$this->convertToDispString($usedStr));
		$this->tmpl->addVar('_widget', 'used_rate',		$this->convertToDispString($usedRateStr));
		$this->tmpl->addVar('_widget', 'watch_job_status',		$watchJobStatus);
		$this->tmpl->addVar('_widget', 'src_version',	$this->convertToDispString($srcVer) . $versionInfoStr);
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
