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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @link       http://magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/connector/admin_mainConnectorBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_tableDb.php');

class admin_mainConnector_dailyjobWidgetContainer extends admin_mainConnectorBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $backupDir;		// バックアップファイル格納ディレクトリ
	
	const MAX_CALC_DAYS = 3;		// 集計最大日数
	const MSG_JOB_COMPLETED = '日次処理を実行しました。';
	const MSG_JOB_CANCELD = '日次処理をキャンセルしました。現在サーバ負荷が大きい状態(%d%%)です。';
	const MSG_ERR_JOB = '日次処理(アクセス解析の集計)に失敗しました。';
	const BACKUP_FILENAME_HEAD = 'backup_';
	const TABLE_NAME_ACCESS_LOG = '_access_log';			// アクセスログテーブル名
	const TABLE_NAME_OPERATION_LOG = '_operation_log';			// 運用ログテーブル名
	const TABLE_SERIAL_FIELD_OPERATION_LOG = 'ol_serial';			// 運用ログシリアル番号フィールド名
	const ACCESS_LOG_REMAIN_MIN_MONTH_COUNT = 1;				// アクセスログ最小限残す月数期間
/*	const CALC_COMPLETED_MIN_RECORD_COUNT = 1000;			// バックアップ条件となる集計済みのレコード数
	const MAINTAIN_TABLE_MAX_RECORD_COUNT = 3000;				// テーブルメンテナンス用の最大レコード数
	const MAINTAIN_TABLE_MIN_RECORD_COUNT = 1000;				// テーブルメンテナンス用の最小レコード数
*/
	const CALC_COMPLETED_MIN_RECORD_COUNT = 10;			// バックアップ条件となる集計済みのレコード数
	const MAINTAIN_TABLE_MAX_RECORD_COUNT = 30;				// テーブルメンテナンス用の最大レコード数
	const MAINTAIN_TABLE_MIN_RECORD_COUNT = 10;				// テーブルメンテナンス用の最小レコード数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_tableDb();
		
		// バックアップ用ディレクトリ作成
		$this->backupDir = $this->gEnv->getIncludePath() . '/' . M3_DIR_NAME_BACKUP;				// バックアップファイル格納ディレクトリ
		if (!file_exists($this->backupDir)) @mkdir($this->backupDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的に作成*/);
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
		return '';		// テンプレートは使用しない
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
		// サーバ負荷が高い場合は実行中止
		$avg = $this->_checkServerLoadAverage();
		if ($avg > 0){
			$this->gOpeLog->writeInfo(__METHOD__, sprintf(self::MSG_JOB_CANCELD, $avg), 1002);
			return;
		}
		
		// タイムアウトを停止
		$this->gPage->setNoTimeout();
		
		// ウィジェット出力処理中断
		$this->gPage->abortWidget();

		// アクセス解析の集計処理
		$messageArray = array();
		$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray, self::MAX_CALC_DAYS);
		if (!$ret){	// エラーの場合
			// ログを残す
			$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100, implode(', ', $messageArray));
			return;
		}
		
		// アクセスログをメンテナンス
		$this->_maintainAccessLog($messageArray);
		
		// 運用ログをメンテナンス
		$this->_maintainOpeLog($messageArray);
		
		// 日次処理終了のログを残す
		$this->gOpeLog->writeInfo(__METHOD__, self::MSG_JOB_COMPLETED, 1002, implode(', ', $messageArray));
	}
	/**
	 * アクセスログをメンテナンス
	 *
	 * @param array  	$message	エラーメッセージ
	 * @return bool					true=成功、false=失敗
	 */
	function _maintainAccessLog(&$message = null)
	{
		$retStatus = false;
		
		// 集計済みのアクセスログのレコード数取得
		$calcCompletedRecordCount = $this->gInstance->getAnalyzeManager()->getCalcCompletedAccessLogRecordCount();
		if ($calcCompletedRecordCount > self::CALC_COMPLETED_MIN_RECORD_COUNT){
			// バックアップファイル名作成
			$backupFile = $this->backupDir . '/' . self::BACKUP_FILENAME_HEAD . self::TABLE_NAME_ACCESS_LOG . '_' . date('Ymd-His') . '.sql.gz';
			
			// バックアップファイル作成
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD);		// バックアップ一時ファイル
			$ret = $this->gInstance->getDbManager()->backupTable(self::TABLE_NAME_ACCESS_LOG, $tmpFile);
			if ($ret){	// バックアップファイル作成成功の場合
				// ファイル名変更
				if (renameFile($tmpFile, $backupFile)){
					// 集計完了日から指定月数のログを残して、集計終了のアクセスログ削除(アクセスログは月の先頭日から残す)
					$this->gInstance->getAnalyzeManager()->deleteCalcCompletedAccessLog(self::ACCESS_LOG_REMAIN_MIN_MONTH_COUNT);
				
					// ファイル名を記録
					if (!is_null($message)) $message[] = 'アクセスログ(_access_log)バックアップファイル=' . $backupFile;
					
					$retStatus = true;
				} else {
					// テンポラリファイル削除
					unlink($tmpFile);
				
					// ログを残す
					$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100, 'バックアップファイル名変更に失敗。ファイル=' . $backupFile);
				}
			} else {
				// テンポラリファイル削除
				unlink($tmpFile);
				
				// ログを残す
				$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100, 'バックアップファイルの作成に失敗。');
			}
		}
		return $retStatus;
	}
	/**
	 * 運用ログをメンテナンス
	 *
	 * @param array  	$message	エラーメッセージ
	 * @return bool					true=成功、false=失敗
	 */
	function _maintainOpeLog(&$message = null)
	{
		$ret = $this->gInstance->getDbManager()->maintainTable(self::TABLE_NAME_OPERATION_LOG, self::TABLE_SERIAL_FIELD_OPERATION_LOG, self::MAINTAIN_TABLE_MIN_RECORD_COUNT, self::MAINTAIN_TABLE_MAX_RECORD_COUNT, $this->backupDir, $message);
		return $ret;
	}
}
?>
