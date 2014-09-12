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

class admin_mainInitsystemWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $showDetail;		// 詳細表示モードかどうか
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
		
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
		$filename = '';		// 実行スクリプトファイル
		
		// 送信値を取得
		$develop = $request->trimValueOf('develop');
		if (!empty($develop)) $this->showDetail = '1';
		
		$act = $request->trimValueOf('act');
		$connectOfficial = $request->trimCheckedValueOf('item_connect_official');
		
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
			$filename = $request->trimValueOf('sample_sql');
			$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $filename;
			
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
			// 現在の設定しているテンプレートを解除
			$request->unsetSessionValue(M3_SESSION_CURRENT_TEMPLATE);
		} else if ($act == 'selectfile'){		// スクリプトファイルを選択
			$filename = $request->trimValueOf('sample_sql');
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
			
			// デフォルトのファイル名を決定
			if (empty($filename)) $filename = $file;
			
			$selected = '';
			if ($file == $filename){
				$selected = 'selected';
			}
			$row = array(
				'value'    => $file,			// ファイル名
				'name'     => $file,			// ファイル名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('sample__sql_list', $row);
			$this->tmpl->parseTemplate('sample__sql_list', 'a');
		}
		
		// 実行スクリプトファイルのヘッダを取得
		if (!empty($filename)){
			$filePath = $searchPath . '/' . $filename;
			
			// ファイルの読み込み
			$fileHead = '';
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
					$fileHead .= $line . '<br />' . M3_NL;
				}
			}
			fclose($fp);
			
			$this->tmpl->addVar("_widget", "header", $fileHead);
		}
		
		// その他値を埋め込む
		$this->tmpl->addVar("_widget", "connect_official", $this->convertToCheckedString($connectOfficial));
		$this->tmpl->addVar("_widget", "develop", $this->showDetail);
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
}
?>
