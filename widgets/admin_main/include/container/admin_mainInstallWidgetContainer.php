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
require_once($gEnvManager->getLibPath() .	'/gitRepo.php');

class admin_mainInstallWidgetContainer extends admin_mainBaseWidgetContainer
{
	const PROCESSING_ICON_FILE = '/images/system/processing.gif';		// 処理中
	
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
		return 'install.tmpl.html';
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
		// インストールディレクトリ
		$dirName = basename($this->gEnv->getSystemRootPath());
		
		// 入力値取得
		$backupDirName = $request->trimValueOf('item_backup_dir_name');	// バックアップディレクトリ名
		$versionTag = $request->trimValueOf('version_tag');	// バージョンタグ
		
		$act = $request->trimValueOf('act');
		if ($act == 'install'){		// システムインストールのとき
			if ($this->checkInput($backupDirName, 'バックアップディレクトリ名')){
				if ($dirName == $backupDirName) $this->setAppErrorMsg('バックアップディレクトリ名はディレクトリ名と異なる名前を入力してください');
			}
			// エラーなしの場合はインストール開始
			if ($this->getMsgCount() == 0){
				// タイムアウトを停止
				$this->gPage->setNoTimeout();
			
				// 作業ディレクトリ作成
				$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得

				// タグでZip圧縮ファイルを取得し、指定ディレクトリに解凍
				$repo = new GitRepo('magic3org', 'magic3');
				//$ret = $repo->downloadZipFileByTag($versionTag, $destPath);
				$ret = $repo->downloadZipFileByTag($versionTag, $tmpDir, $destPath);
				if ($ret){
					// バックアップ先削除
					$parentDir = dirname($this->gEnv->getSystemRootPath());
					$backupDir = $parentDir . '/' . $backupDirName;
					rmDirectory($backupDir);
			
					// ディレクトリを入れ替え
					mvDirectory($this->gEnv->getSystemRootPath(), $backupDir);
					mvDirectory($destPath, $this->gEnv->getSystemRootPath());
				}

				// 作業ディレクトリ削除
				rmDirectory($tmpDir);
				
				// 再起動
				$this->gPage->redirect();
			}
		} else {
			// バックアップディレクトリ
			$backupDirName = '_' . $dirName;
		}
		
		// 最新版のバージョン番号を取得
		$repo = new GitRepo('magic3org', 'magic3');
		$tagInfoArray = $repo->getTagInfo();
		if ($tagInfoArray !== false){
			$tagInfoCount = count($tagInfoArray);
			if ($tagInfoCount > 0){
				for ($i = 0; $i < $tagInfoCount; $i++){
					$tagInfo = $tagInfoArray[$i];
					$tagName = $tagInfo->name;
					$zipUrl = $tagInfo->zipball_url;
				
					// バージョン番号を取得
					$exp = '/[\c\.]*([0-9\.]+)/s';			// バージョン番号の最後の「b」(ベータ版)等は許可
					if (preg_match($exp, $tagName, $matches)){
						$version = $matches[1];
						if (!empty($version)) break;
					}
				}
			}
		}
		// インストール可能バージョン
		$versionStr = $version;
		if (version_compare($version, M3_SYSTEM_VERSION) > 0) $versionStr = '<span class="available">' . $versionStr . '</span>';
		
		// 現在のバージョンよりも古い場合はインストール不可
		$disabled = '';
		if (version_compare($version, M3_SYSTEM_VERSION) < 0) $disabled = 'disabled';
		$this->tmpl->addVar("_widget", "install_button_disabled", $disabled);
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "version_tag", $this->convertToDispString($tagName));		// 最新バージョンタグ
		$this->tmpl->addVar("_widget", "version", $this->convertToDispString($versionStr));		// 最新バージョン
		$this->tmpl->addVar("_widget", "dir_name", $this->convertToDispString($dirName));		// ディレクトリ名
		$this->tmpl->addVar("_widget", "backup_dir_name", $this->convertToDispString($backupDirName));		// デフォルトディレクトリ名
		$this->tmpl->addVar('_widget', 'process_image', $this->getUrl($this->gEnv->getRootUrl() . self::PROCESSING_ICON_FILE));	// 処理中アイコン
	}
}
?>
