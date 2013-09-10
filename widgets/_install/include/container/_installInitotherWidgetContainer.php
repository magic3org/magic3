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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: _installInitotherWidgetContainer.php 5095 2012-08-08 13:29:27Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/_installDb.php');

class _installInitotherWidgetContainer extends _installBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	
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
		return 'initother.tmpl.html';
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
		$filename = '';		// 実行スクリプトファイル
		$act = $request->trimValueOf('act');
		$type = $request->trimValueOf('install_type');
		$from = $request->trimValueOf('from');
		$dbStatus = $request->trimValueOf('dbstatus');		// DBの状態
		if (empty($dbStatus)){
			if ($from == 'updatedb'){
				$dbStatus = 'update';
			} else {
				$dbStatus = 'init';
			}
		}
			
		if ($act == 'installsampledata'){		// サンプルデータインストールのとき
			$filename = $request->trimValueOf('sample_sql');
			$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $filename;
			
			// スクリプト実行
			if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Installing data completed.'));		// データインストール完了しました
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in installing data.'));// データインストールに失敗しました
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
		} else {
			$this->tmpl->setAttribute('install_msg', 'visibility', 'visible');// テーブル構築完了のメッセージ
		}
		// サンプルSQLスクリプトディレクトリのチェック
		$scriptFiles = array();
		$searchPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR;
		if (is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0){	// 「_」で始まる名前のファイルは読み込まない
					$scriptFiles[] = $file;
				}
			}
			$dir->close();
			
			// アルファベット順にソート
			sort($scriptFiles);
			
			// スクリプトをメニューに登録
			for ($i = 0; $i < count($scriptFiles); $i++){
				$file = $scriptFiles[$i];
				
				// デフォルトのファイル名を決定
				if (empty($filename)) $filename = $file;
				
				$selected = '';
				if ($file == $filename) $selected = 'selected';

				$row = array(
					'value'    => $file,			// ファイル名
					'name'     => $file,			// ファイル名
					'selected' => $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('sample__sql_list', $row);
				$this->tmpl->parseTemplate('sample__sql_list', 'a');
			}
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
			
			// スクリプトヘッダ表示
			$this->tmpl->addVar("datainstall_msg", "header", $fileHead);
		}
		// 画面のヘッダ、タイトルを設定
		if ($dbStatus == 'update'){
			$this->tmpl->addVar("_widget", "title", $this->_('Database Updated'));		// ＤＢバージョンアップ完了
			$this->tmpl->addVar("install_msg", "message", $this->_('Updating database completed.'));// ＤＢバージョンアップが完了しました
		} else {
			$this->tmpl->addVar("_widget", "title", $this->_('Database Created'));// ＤＢ構築完了
			$this->tmpl->addVar("install_msg", "message", $this->_('Creating database completed.'));		// ＤＢの構築が完了しました
			
			if ($type == 'all'){		// カスタムインストールのときは表示しない
				$this->tmpl->setAttribute('datainstall_msg', 'visibility', 'visible');// サンプルデータインストール用領域表示
			}
		}
		$this->tmpl->addVar("_widget", "db_status", $dbStatus);
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_install_data'] = $this->_('Install data?');// データをインストールしますか?
		$localeText['label_install_data'] = $this->_('Install Data');// デモデータインストール
		$localeText['msg_install_demo_data'] = $this->_('If you install demo data, use this operation field below.<br />If you don\'t, go next.<br />You can install demo data after installing system at dministration page (System Administration-Maintenance Database).');	// デモ用データをインストールする場合は以下の処理を実行してください<br />何も行わない場合は「次へ」進みます。<br />インストール終了後も管理機能の「システム管理」-「DBメンテナンス」からデモ用データのインストールは可能です。
		$localeText['label_desc'] = $this->_('Details');// [説明]
		$this->setLocaleText($localeText);
	}
}
?>
