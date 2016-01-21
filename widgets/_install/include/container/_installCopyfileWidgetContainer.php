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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/_installDb.php');

class _installCopyfileWidgetContainer extends _installBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $templateIdArray = array();			// コピーしたテンプレートID
	private $widgetIdArray = array();			// コピーしたウィジェットID
	
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
		return 'copyfile.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$type = $request->trimValueOf('install_type');
		$from = $request->trimValueOf('from');
		$dbStatus = $request->trimValueOf('dbstatus');		// DBの状態
		$isResourceDir = ($request->trimValueOf('is_resource_dir') == 'on') ? 1 : 0;		// リソースディレクトリをコピーするかどうか
		$isTemplate = ($request->trimValueOf('is_template') == 'on') ? 1 : 0;				// テンプレートをコピーするかどうか
		$isWidget = ($request->trimValueOf('is_widget') == 'on') ? 1 : 0;				// ウィジェットをコピーするかどうか
		if (empty($dbStatus)){
			if ($from == 'updatedb'){
				$dbStatus = 'update';
			} else {
				$dbStatus = 'init';
			}
		}
		// 旧システムのルートディレクトリ
		$rootDir = $request->trimValueOf('root_dir');
		$rootDir = rtrim($rootDir, '/');
		// ファイル名のみの場合は現在のシステムと同じディレクトリに存在しているとする
		$realRootDir = $rootDir;
		if (!empty($realRootDir)){
			if (dirname($realRootDir) == '.'){		// ファイル名のみの場合
				// フルパス表記に変更
				$realRootDir = dirname($this->gEnv->getSystemRootPath()) . '/' . $realRootDir;
			}
		}
		
		if ($act == 'copyfile'){		// 旧システムのリソースファイルをコピーするとき
			// 入力チェック
			$ret = $this->checkDir($realRootDir);
			if ($ret){
				// ディレクトリの存在チェック
				if ($isResourceDir){
					if (!is_dir($realRootDir . '/' . M3_DIR_NAME_RESOURCE)) $this->setMsg(self::MSG_APP_ERR, $this->_('Resource directory not found.'));		// リソースディレクトリが見つかりません
				}
				if ($isTemplate){
					if (!is_dir($realRootDir . '/' . M3_DIR_NAME_TEMPLATES)) $this->setMsg(self::MSG_APP_ERR, $this->_('Template directory not found.'));// テンプレートディレクトリが見つかりません
				}
				if ($isWidget){
					if (!is_dir($realRootDir . '/' . M3_DIR_NAME_WIDGETS)) $this->setMsg(self::MSG_APP_ERR, $this->_('Widget directory not found.'));		// ウィジェットディレクトリが見つかりません
				}
			}
			
			// エラーなしの場合は、データのコピー処理
			if ($this->getMsgCount() == 0){	// 入力チェックOKの場合
				$isCompleted = true;		// 正常に終了したかどうか
				
				// リソースディレクトリのコピー
				if ($isResourceDir){
					$resourceDir = $this->gEnv->getSystemRootPath() . '/' . M3_DIR_NAME_RESOURCE;
					//$tmpResourceDir = $this->gEnv->getSystemRootPath() . '/_' . M3_DIR_NAME_RESOURCE;
					$tmpResourceDir = $this->gEnv->getTempDir() . '/' . M3_DIR_NAME_RESOURCE;
					$oldResourceDir = $realRootDir . '/' . M3_DIR_NAME_RESOURCE;

					// 現在のディレクトリを退避
					$ret = mvDirectory($resourceDir, $tmpResourceDir);
					
					// 旧システムのデータをコピー
					if ($ret) $ret = cpDirectory($oldResourceDir, $resourceDir);
					
					if ($ret){
						// 退避ディレクトリ削除
						rmDirectory($tmpResourceDir);
						
						// 運用ログを残す
						$msg = $this->_('Resource directory in old system copied. Directory: %s');		// 旧システムのリソースディレクトリをコピーしました。ディレクトリ: %s
						$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, $oldResourceDir), 1000);
					} else {
						// 退避ディレクトリを戻す
						mvDirectory($tmpResourceDir, $resourceDir);
						
						$isCompleted = false;
					}
				}
				// テンプレートディレクトリのコピー
				if ($isTemplate){
					$searchDir = array('', '/' . M3_DIR_NAME_MOBILE);	// コピー対象の相対パス
					$excludeDir = array(M3_DIR_NAME_MOBILE);			// コピー対象外のディレクトリ名

					for ($i = 0; $i < count($searchDir); $i++){
						// ディレクトリのコピー
						$destDir = $this->gEnv->getSystemRootPath() . '/' . M3_DIR_NAME_TEMPLATES . $searchDir[$i];
						$srcDir = $realRootDir . '/' . M3_DIR_NAME_TEMPLATES . $searchDir[$i];
						$this->copyTemplate($srcDir, $destDir, $excludeDir);
					}
					
					// テンプレートを取り込んだときはメッセージを残す
					if (count($this->templateIdArray) > 0){
						// 運用ログを残す
						$msg = $this->_('Template in old system copied. Template: %s');		// 旧システムのテンプレートをコピーしました。テンプレート: %s
						$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, implode($this->templateIdArray, ',')), 1000);
					}
				}
				// ウィジェットディレクトリのコピー
				if ($isWidget){
					$searchDir = array('', '/' . M3_DIR_NAME_MOBILE);	// コピー対象の相対パス
					$excludeDir = array(M3_DIR_NAME_MOBILE);			// コピー対象外のディレクトリ名

					for ($i = 0; $i < count($searchDir); $i++){
						// ディレクトリのコピー
						$destDir = $this->gEnv->getSystemRootPath() . '/' . M3_DIR_NAME_WIDGETS . $searchDir[$i];
						$srcDir = $realRootDir . '/' . M3_DIR_NAME_WIDGETS . $searchDir[$i];
						$this->copyWidget($srcDir, $destDir, $excludeDir);
					}
					
					// ウィジェットを取り込んだときはメッセージを残す
					if (count($this->widgetIdArray) > 0){
						// 運用ログを残す
						$msg = $this->_('Widget in old system copied. Widget: %s');		// 旧システムのウィジェットをコピーしました。ウィジェット: %s
						$this->gOpeLog->writeInfo(__METHOD__, sprintf($msg, implode($this->widgetIdArray, ',')), 1000);
					}
				}
				if ($isCompleted){
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Files copied.'));		// ファイルをコピーしました
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in copying files.'));		// ファイルコピーに失敗しました
				}
			}
		} else if ($act == 'checkdir'){		// 旧システムのルートディレクトリの位置をチェック
			// 入力チェック
			$this->checkDir($realRootDir);
			
			// エラーなしの場合は正常メッセージを表示
			if ($this->getMsgCount() == 0){// 入力チェックOKの場合
				$msg = '<b><font color="green">' . $this->_('Check directory') . '</font></b>';		// ディレクトリチェック
				$msg .= ' => ';
				$msg .= '<b><font color="green">' . $this->_('Existing') . '</font></b>';			// 正常
				$this->tmpl->addVar("_widget", "check_msg", $msg);
			}
		} else if ($act == 'goback'){		// 「戻り」で画面遷移した場合
		} else {
			// リダイレクトで初回遷移時のみメッセージを表示
			$referer	= $request->trimServerValueOf('HTTP_REFERER');
			if (!empty($referer)){
				if ($dbStatus == 'update'){
					$this->setSuccessMsg($this->_('Updating database completed.'));// ＤＢバージョンアップが完了しました
				}
			}
			
			// パラメータ初期化
			$isResourceDir = 1;			// リソースディレクトリをコピー対象とする
			$isTemplate = 1;			// テンプレートをコピー対象とする
			$isWidget = 1;				// ウィジェットをコピー対象とする
			
			// 旧システムディレクトリデフォルト値
			$rootDir = '';
			$backupDirName = '_' . basename($this->gEnv->getSystemRootPath());
			$backupDir = dirname($this->gEnv->getSystemRootPath()) . '/' . $backupDirName;
			if (@file_exists($backupDir)) $rootDir = $backupDirName;		// バックアップディレクトリが存在している場合は値を設定。2016/1/21 PHPの設定open_basedirディレクトリアクセス制限の場合のエラーメッセージを抑止
		}
		
		// 画面のヘッダ、タイトルを設定
		if ($dbStatus == 'update'){
			$this->tmpl->addVar("_widget", "title", $this->_('Database Updated'));		// ＤＢバージョンアップ完了
		}
		$this->tmpl->addVar("_widget", "db_status", $dbStatus);
		$this->tmpl->addVar("_widget", "root_dir", $rootDir);
		$checked = '';
		if ($isResourceDir) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_resource_dir_checked", $checked);
		$checked = '';
		if ($isTemplate) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_template_checked", $checked);
		$checked = '';
		if ($isWidget) $checked = 'checked';
		$this->tmpl->addVar("_widget", "is_widget_checked", $checked);
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_copy_files'] = $this->_('Copy files?');// ファイルをコピーしますか?
		$localeText['msg_copy_from_old_system'] = $this->_('If you copy files form old system to this system, use this operation field below.<br />If you don\'t, go next.');	// 旧システムのファイルをこのシステムへコピーする場合は以下の処理を実行してください<br />何も行わない場合は「次へ」進みます。
		$localeText['label_copy_from_old_system'] = $this->_('Copy files in old system');		// 旧システムからファイルをコピー
		$localeText['label_old_system_dir'] = $this->_('Old System Directory');// 旧システムディレクトリ
		$localeText['label_check_dir'] = $this->_('Check directory');// ディレクトリチェック
		$localeText['label_target_copy'] = $this->_('Copy Target');// コピー対象
		$localeText['label_replace_resource'] = $this->_('Replace resource directory');// リソースディレクトリ(/resource) 置き換え
		$localeText['label_copy_template'] = $this->_('Copy template (difference only)');// テンプレート 差分のみ取得
		$localeText['label_copy_widget'] = $this->_('Copy widget (difference only)');// ウィジェット 差分のみ取得
		$localeText['label_copy_files'] = $this->_('Copy files');// ファイルをコピー
		$this->setLocaleText($localeText);
	}
	/**
	 * ディレクトリの入力チェック
	 *
	 * @param string $dir	ディレクトリ
	 * @return bool			true=正常、false=異常
	 */
	function checkDir($dir)
	{
		if (empty($dir)){
			$this->setMsg(self::MSG_APP_ERR, $this->_('The root directory in old system not selected.'));	// 旧システムのルートディレクトリが設定されていません
		} else {
			if ($dir == $this->gEnv->getSystemRootPath()){// このシステムのディレクトリではないか
				$this->setMsg(self::MSG_APP_ERR, $this->_('This system selected.'));		// このシステム自体を指定しています
			} else if (!is_file($dir . '/include/global.php') || !is_file($dir . '/include/siteDef.php')){// Magic3かどうか
				$this->setMsg(self::MSG_APP_ERR, $this->_('Magic3 system not selected.'));		// Magic3システムを指定していません
			}
		}
		
		if ($this->getMsgCount() == 0){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * テンプレートのコピー
	 *
	 * @param string $srcDir		旧テンプレートディレクトリ
	 * @param string $destDir		テンプレートディレクトリ
	 * @param array $excludeDir		コピー対象外のディレクトリ名
	 * @return bool						true=正常、false=異常
	 */
	function copyTemplate($srcDir, $destDir, $excludeDir)
	{
		$dir = dir($srcDir);
		while (($file = $dir->read()) !== false){
			// 携帯等のディレクトリは除く
			if (strStartsWith($file, '.') || $file == '..' || in_array($file, $excludeDir)) continue;
			
			$srcPath = $srcDir . '/' . $file;
			if (is_dir($srcPath)){			// ディレクトリのとき
				$destPath = $destDir . '/' . $file;

				// 存在していないときはコピー
				if (!file_exists($destPath)){
					$ret = cpDirectory($srcPath, $destPath);
					if ($ret){
						$subDir = str_replace($this->gEnv->getSystemRootPath() . '/' . M3_DIR_NAME_TEMPLATES . '/', '', $destPath);
						$this->templateIdArray[] = $subDir;
					}
				}
			}
		}
		$dir->close();
		return true;
	}
	/**
	 * ウィジェットディレクトリのコピー
	 *
	 * @param string $srcDir	旧ウィジェットディレクトリ
	 * @param string $destDir		ウィジェットディレクトリ
	 * @param array $excludeDir		コピー対象外のディレクトリ名
	 * @return bool						true=正常、false=異常
	 */
	function copyWidget($srcDir, $destDir, $excludeDir)
	{
		$dir = dir($srcDir);
		while (($file = $dir->read()) !== false){
			// 携帯等のディレクトリは除く
			if (strStartsWith($file, '.') || $file == '..' || in_array($file, $excludeDir)) continue;
			
			$srcPath = $srcDir . '/' . $file;
			if (is_dir($srcPath)){			// ディレクトリのとき
				$destPath = $destDir . '/' . $file;

				// 存在していないときはコピー
				if (!file_exists($destPath)){
					$ret = cpDirectory($srcPath, $destPath);
					if ($ret){
						$subDir = str_replace($this->gEnv->getSystemRootPath() . '/' . M3_DIR_NAME_WIDGETS . '/', '', $destPath);
						$this->widgetIdArray[] = $subDir;
					}
				}
			}
		}
		$dir->close();
		return true;
	}
}
?>
