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

class _installInitotherWidgetContainer extends _installBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sampleId;		// サンプルデータID
	private $sampleTitle;	// サンプルデータタイトル
	private $sampleDesc;	// サンプルデータ説明
	const SAMPLE_DIR = 'sample';				// サンプルSQLディレクトリ名
	const DOWNLOAD_FILE_PREFIX = 'DOWNLOAD:';		// ダウンロードファイルプレフィックス
	
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
		$connectOfficial = $request->trimCheckedValueOf('connect_official');
		$this->sampleId = $request->trimValueOf('sample_sql');
			
		if ($act == 'installsampledata'){		// サンプルデータインストールのとき
			if (strStartsWith($this->sampleId, self::DOWNLOAD_FILE_PREFIX)){		// 公式サイトからサンプルデータを取得の場合
			 	// サンプルデータインストール用アーカイブを取得しインストール
				$sampleId = str_replace(self::DOWNLOAD_FILE_PREFIX, '', $this->sampleId);
				$ret = $this->gInstance->getInstallManager()->installOffcialSample($sampleId);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'サンプルデータインストール完了しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, "サンプルデータインストールに失敗しました");
				}
			} else {
				$scriptPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR . '/' . $this->sampleId;
			
				// スクリプト実行
				if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
					//$this->setMsg(self::MSG_GUIDANCE, $this->_('Installing data completed.'));		// データインストール完了しました
					$this->setInfoMsg($this->_('Installing data completed.'));		// データインストール完了しました
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in installing data.'));// データインストールに失敗しました
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
		} else if ($act == 'connectofficial'){	// 「公式サイトへ接続」チェックボックスをクリック
		} else if ($act == 'goback'){		// 「戻り」で画面遷移した場合
		} else {
			// リダイレクトで初回遷移時のみメッセージを表示
			$referer	= $request->trimServerValueOf('HTTP_REFERER');
			if (!empty($referer)){
				if ($dbStatus == 'update'){
					$this->setSuccessMsg($this->_('Updating database completed.'));// ＤＢバージョンアップが完了しました
				} else {
					$this->setSuccessMsg($this->_('Creating database completed.'));		// ＤＢの構築が完了しました
				}
			}
		}
		// サンプルSQLスクリプトディレクトリのチェック
		$scriptFiles = array();
		$searchPath = $this->gEnv->getSqlPath() . '/' . self::SAMPLE_DIR;
		if (is_dir($searchPath)){
			// 1階層目のみ検索
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
		// 画面のヘッダ、タイトルを設定
		if ($dbStatus == 'update'){
			$this->tmpl->addVar("_widget", "title", $this->_('Database Updated'));		// ＤＢバージョンアップ完了
		} else {
			$this->tmpl->addVar("_widget", "title", $this->_('Database Created'));// ＤＢ構築完了

			if ($type == 'all'){		// カスタムインストールのときは表示しない
				$this->tmpl->setAttribute('datainstall_msg', 'visibility', 'visible');// サンプルデータインストール用領域表示
			}
		}
		$content = '<h4>' . $this->convertToDispString($this->sampleTitle, true/*タグ変換なし*/) . '</h4>';
		$content .= $this->convertToDispString($this->sampleDesc, true/*タグ変換なし*/);
		$this->tmpl->addVar("datainstall_msg", "content", $content);
		$this->tmpl->addVar("datainstall_msg", "connect_official", $this->convertToCheckedString($connectOfficial));
		$this->tmpl->addVar("_widget", "db_status", $dbStatus);
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_install_data'] = $this->_('Install data?');// データをインストールしますか?
		$localeText['label_install_data'] = $this->_('Install Data');// インストールデータ
		$localeText['label_install'] = $this->_('Install');// インストールボタンラベル
		$localeText['msg_install_demo_data'] = $this->_('If you install simple build site data or demo data, use this operation field below.<br />If you don\'t, go next.<br />You can install data after installing system at administration page (Maintenance - Database).');	// サイト簡易構築用データやデモ用データをインストールする場合は、以下の処理を実行してください。<br />何も行わない場合は「次へ」進みます。<br />インストール終了後も管理機能の「メンテナンス」-「DB管理」からデータのインストールは可能です。
		$localeText['label_desc'] = $this->_('Details');// [説明]
		$localeText['label_connect_official'] = $this->_('Connect to official site');	// 公式サイトへ接続
		$this->setLocaleText($localeText);
	}
	/**
	 * 公式サイトのサンプルプログラムリストを取得
	 *
	 * @return				なし
	 */
	function getSampleListFromOfficialSite()
	{
		// 公式サイトからサンプルデータリストを取得
		$sampleList = $this->gInstance->getInstallManager()->getOfficialSampleList();

		$files = array();
		$sampleCount = count($sampleList);
		for ($i = 0; $i < $sampleCount; $i++){
			$id = $sampleList[$i]['id'];
			$status = $sampleList[$i]['status'];
			$sampleId = self::DOWNLOAD_FILE_PREFIX . $id;
			$title = $sampleList[$i]['title'];
			$desc = $sampleList[$i]['description'];
			
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
}
?>
