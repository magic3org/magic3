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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainDbbackupWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const BACKUP_DIR = '/etc/db/backup';		// バックアップファイル格納ディレクトリ
	const BACKUP_FILENAME_HEAD = 'backup_';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
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
		$task = $request->trimValueOf('task');
		return 'dbbackup.tmpl.html';
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
		$task = $request->trimValueOf('task');
		return $this->createList($request);
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// パラメータの取得
		$act = $request->trimValueOf('act');
		$filename = $request->trimValueOf('filename');
		
		$backupDir = $this->gEnv->getIncludePath() . self::BACKUP_DIR;				// バックアップファイル格納ディレクトリ
		if (!file_exists($backupDir)) @mkdir($backupDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的に作成*/);

		if ($act == 'new'){
			$backupFile = $backupDir . DIRECTORY_SEPARATOR . self::BACKUP_FILENAME_HEAD . date('Ymd-His') . '.sql.gz';
			$this->gInstance->getDbManager()->backupDb($backupFile);
		} if ($act == 'delete'){		// メニュー項目の削除
			$listedItem = explode(',', $request->trimValueOf('filelist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue) $delItems[] = $listedItem[$i];		// チェック項目
			}
			if ($this->getMsgCount() == 0 && count($delItems) > 0){
				for ($i = 0; $i < count($delItems); $i++){
					$filePath = $backupDir . DIRECTORY_SEPARATOR . $delItems[$i];
					unlink($filePath);
				}
				if ($i == count($delItems)){
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'download'){		// ファイルダウンロードのとき
			$downloadFilename = $backupDir . DIRECTORY_SEPARATOR . $filename;
			
			if (file_exists($downloadFilename)){
				// ページ作成処理中断
				$this->gPage->abortPage();
			
				// ダウンロード処理
				$ret = $this->gPage->downloadFile($downloadFilename, $filename);
			
				// システム強制終了
				$this->gPage->exitSystem();
			} else {
				$this->setAppErrorMsg('ファイルが見つかりません(ファイル名=' . $filename . ')');
			}
		}
		// ファイル一覧作成
		$files = $this->getFileList($backupDir);
		for ($i = 0; $i < count($files); $i++){
			$fileName = $files[$i];
			$filePath = $backupDir . DIRECTORY_SEPARATOR . $files[$i];
			$size = filesize($filePath);
			
			$row = array(
				'index'			=> $i,
				'filename'    	=> $this->convertToDispString($fileName),			// ファイル名
				'size'     		=> $size			// ファイルサイズ
			);
			$this->tmpl->addVars('file_list', $row);
			$this->tmpl->parseTemplate('file_list', 'a');
		}
		if (count($files) <= 0) $this->tmpl->setAttribute('file_list', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		$this->tmpl->addVar("_widget", "filelist", implode($files, ','));// ファイル名を設定
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path	ディレクトリパス
	 * @return array		ファイルパスのリスト
	 */
	function getFileList($path)
	{
		$fileList = array();
		
		// 引数エラーチェック
		if (!is_dir($path)) return $fileList;
		
		$dir = dir($path);
		while (($file = $dir->read()) !== false){
			$filePath = $path . DIRECTORY_SEPARATOR . $file;
			// カレントディレクトリかどうかチェック
			if ($file == '.' || $file == '..' || !strStartsWith($file, self::BACKUP_FILENAME_HEAD)) continue;
			$fileList[] = $file;
		}
		$dir->close();
		
		// ファイル名でソート
		rsort($fileList);
		return $fileList;
	}
}
?>
