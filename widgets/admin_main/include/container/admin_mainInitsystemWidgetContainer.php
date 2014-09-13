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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath() .	'/gitRepo.php');

class admin_mainInitsystemWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $showDetail;		// 詳細表示モードかどうか
	private $sampleId;		// サンプルデータID
	private $sampleTitle;	// サンプルデータタイトル
	private $sampleDesc;	// サンプルデータ説明
	private $archivePath;	// サンプルデータインストール用アーカイブの相対パス
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	const DOWNLOAD_FILE_PREFIX = 'DOWNLOAD:';		// ダウンロードファイルプレフィックス
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		$this->showDetail = $this->db->canDetailConfig();		// 詳細表示かどうか
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
		return 'initsystem.tmpl.html';
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
		// 送信値を取得
		$develop = $request->trimValueOf('develop');
		if (!empty($develop)) $this->showDetail = '1';
		
		$act = $request->trimValueOf('act');
		$connectOfficial = $request->trimCheckedValueOf('item_connect_official');
		$this->sampleId = $request->trimValueOf('sample_sql');
		$archivePath = $request->trimValueOf('archivepath');
		
		if ($act == 'initsys'){		// システム初期化のとき
			// テーブルの初期化フラグをリセット
			$this->gSystem->enableInitSystem();
			
			// インストーラを回復
			$this->gInstance->getFileManager()->recoverInstaller();
			
			$this->setMsg(self::MSG_GUIDANCE, 'システム初期化完了しました<br />一旦ログアウトしてください');
			
			// 現在の設定しているテンプレートを解除
			$request->unsetSessionValue(M3_SESSION_CURRENT_TEMPLATE);
		} else if ($act == 'initother'){		// 追加テーブル再作成のとき
			// DB初期化実行
			$ret = $this->gInstance->getDbManager()->execInitScript('base', $errors);// 標準テーブル
			if ($ret) $ret = $this->gInstance->getDbManager()->execInitScript('ec', $errors);// ECテーブル
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, 'テーブル再作成完了しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, "テーブル再作成に失敗しました");
			}
			if (!empty($errors)){
				foreach ($errors as $error) {
					$this->setMsg(self::MSG_APP_ERR, $error);
				}
			}
			// 現在の設定しているテンプレートを解除
			$request->unsetSessionValue(M3_SESSION_CURRENT_TEMPLATE);
		} else if ($act == 'installsampledata'){		// サンプルデータインストールのとき
			if (strStartsWith($this->sampleId, self::DOWNLOAD_FILE_PREFIX)){		// 公式サイトからサンプルデータを取得の場合
			 	// サンプルデータインストール用アーカイブを取得しインストール
				$this->installSampleArchive($archivePath);
			} else {
				$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $this->sampleId;
			
				// スクリプト実行
				if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
					$this->setMsg(self::MSG_GUIDANCE, 'スクリプト実行完了しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, "スクリプト実行に失敗しました");
				}
				if (!empty($errors)){
					foreach ($errors as $error) {
						$this->setMsg(self::MSG_APP_ERR, $error);
					}
				}
			}
			// 現在の設定しているテンプレートを解除
			$request->unsetSessionValue(M3_SESSION_CURRENT_TEMPLATE);
		} else if ($act == 'selectfile'){		// スクリプトファイルを選択
			//$this->sampleId = $request->trimValueOf('sample_sql');
		} else if ($act == 'develop'){		// 開発用モード
			$this->showDetail = '1';
		}
		
		// DBのタイプ
		$dbType = $this->db->getDbType();
		
		// サンプルSQLスクリプトディレクトリのチェック
		$searchPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR;
		$files = $this->getScript($searchPath);
		sort($files);		// ファイル名をソート

		// スクリプト選択メニュー作成
		for ($i = 0; $i < count($files); $i++){
			$file = $files[$i];
			$name = preg_replace("/(.+)(\.[^.]+$)/", "$1", $file);		// 拡張子除く
			
			// デフォルトのファイル名を決定
			if (empty($this->sampleId)) $this->sampleId = $file;
			
			$selected = '';
			if ($file == $this->sampleId) $selected = 'selected';

			$row = array(
				'value'    => $this->convertToDispString($file),			// ファイル名
				'name'     => $this->convertToDispString($name),			// ファイル名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
		
		// 公式サイト接続の場合は公式サイトからサンプルパッケージリストを取得
		if ($connectOfficial){
			$row = array(
				'value'    => '',			// ファイル名
				'name'     => '-- 公式サイト --',			// ファイル名
				'selected' => ''	
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
			
			// 公式サイトのサンプルデータリストを取得
			$this->getSampleListFromOfficialSite();
		}
		
		// 実行スクリプトファイルのヘッダを取得
		if (!empty($this->sampleId) && !strStartsWith($this->sampleId, self::DOWNLOAD_FILE_PREFIX)){
			$filePath = $searchPath . '/' . $this->sampleId;
			
			// ファイルの読み込み
			$fileDescArray = array();
			$fp = fopen($filePath, 'r');
			while (!feof($fp)){
			    $line = fgets($fp, 1024);
				$line = trim($line);
				
				// 空行が来たら終了
				if (empty($line)){
					break;
				} else if (strncmp($line, '--', strlen('--')) != 0){		// コメント以外の場合も終了
					break;
				}
				if (strncmp($line, '-- *', strlen('-- *')) != 0){		// ヘッダ部読み飛ばし
					// コメント記号を削除
					$line = trim(substr($line, strlen('--')));
					
					// タイトルを取得
					if (preg_match('/^\[(.*)\]$/', $line, $match)){
						$this->sampleTitle = $match[1];	// サンプルデータタイトル
					} else {
						$fileDescArray[] = $line;
					}
				}
			}
			fclose($fp);
			if (count($fileDescArray)) $this->sampleDesc = implode('<br />', $fileDescArray);
		}
		$content = '<h5>' . $this->convertToDispString($this->sampleTitle, true/*タグ変換なし*/) . '</h5>';
		$content .= $this->convertToDispString($this->sampleDesc, true/*タグ変換なし*/);
		$this->tmpl->addVar("_widget", "content", $content);
				
		// その他値を埋め込む
		$this->tmpl->addVar("_widget", "connect_official", $this->convertToCheckedString($connectOfficial));
		$this->tmpl->addVar("_widget", "develop", $this->showDetail);
		$this->tmpl->addVar("_widget", "archive_path", $this->archivePath);
	}
	/**
	 * ディレクトリ内のスクリプトファイルを取得
	 *
	 * @param string $path		ディレクトリのパス
	 * @return array			スクリプトファイル名
	 */
	function getScript($path)
	{
		static $basePath;
		
		if (!isset($basePath)) $basePath = $path . '/';
		$files = array();
		
		if ($dirHandle = @opendir($path)){
			while ($file = @readdir($dirHandle)) {
				if ($file == '..' || strStartsWith($file, '.')) continue;	
		
				if (!$this->showDetail && strStartsWith($file, '_')) continue;		// 詳細表示モードでなければ、「_」で始まる名前のファイルは読み込まない
				
				// ディレクトリのときはサブディレクトリもチェック
				$filePath = $path . '/' . $file;
				if (is_dir($filePath)){
					$files = array_merge($files, $this->getScript($filePath));
				} else {
					$files[] = str_replace($basePath, '', $filePath);
				}
			}
			closedir($dirHandle);
		}
		return $files;
	}
	/**
	 * 公式サイトのサンプルプログラムリストを取得
	 *
	 * @return				なし
	 */
	function getSampleListFromOfficialSite()
	{
		$files = array();
		$repo = new GitRepo('magic3org', 'magic3_sample_data');
		$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		$url = $repo->getFileUrl('release/info.json');
		$data = json_decode(file_get_contents($url, 0, $context));
		if ($data === false) return $files;

		$fileCount = count($data);
		for ($i = 0; $i < $fileCount; $i++){
			$id = $data[$i]->{'id'};
			$status = $data[$i]->{'status'};
			$sampleId = self::DOWNLOAD_FILE_PREFIX . $id;
			$title = $data[$i]->{'title'};
			$desc = $data[$i]->{'description'};
			
			if ($this->showDetail){
				$name = $id . '[' . $status . ']';
			} else {
				// 安定版のみメニューに表示
				if ($status != 'stable') continue;
				$name = $id;
			}
			
			$selected = '';
			if ($sampleId == $this->sampleId){
				$selected = 'selected';
				
				$this->sampleTitle = $title;	// サンプルデータタイトル
				$this->sampleDesc = str_replace("\n", '<br />', $desc);	// サンプルデータ説明
				$this->archivePath = 'release/' . $data[$i]->{'filename'};	// サンプルデータインストール用相対パス
			}

			$row = array(
				'value'    => $this->convertToDispString($sampleId),			// サンプルデータID
				'name'     => $this->convertToDispString($name),			// 表示名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
	}
	/**
	 * サンプルアーカイブをインストール
	 *
	 * @param string $path	アーカイブ取得用相対パス
	 * @return bool			true=成功、false=失敗
	 */
	function installSampleArchive($path)
	{
		// 作業ディレクトリを作成
		$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
		
		// ファイルダウンロード
		$repo = new GitRepo('magic3org', 'magic3_sample_data');
		$repo->downloadZipFile($path, $tmpDir, $destPath);
		
		// パッケージ情報ファイルを取得
		$data = json_decode(file_get_contents($destPath . '/index.json'));
		if ($data === false) return false;
		
		// 作業ディレクトリ削除
		rmDirectory($tmpDir);
		return $status;
	}
}
?>
