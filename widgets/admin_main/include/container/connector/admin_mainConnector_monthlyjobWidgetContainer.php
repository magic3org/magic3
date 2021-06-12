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
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainConnector_monthlyjobWidgetContainer extends admin_mainConnectorBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	
	const MAX_CALC_DAYS = 3;		// 集計最大日数
	const MSG_JOB_COMPLETED = '月次処理を実行しました。';
	const MSG_JOB_CANCELD = '月次処理をキャンセルしました。現在サーバ負荷が大きい状態(%d%%)です。';
	const MSG_ERR_JOB = '月次処理に失敗しました。';
	const CF_MONTHLY_JOB_DT = 'monthly_job_dt';		// 月次処理完了日時
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const BACKUP_FILENAME_HEAD = 'backup_';
	const TABLE_NAME_ACCESS_LOG = '_access_log';			// アクセスログテーブル名
	const CALC_COMPLETED_MIN_RECORDE_COUNT = 10;			// バックアップ条件となる集計済みのレコード数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
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
			
			// 実行中止した場合は実行日時を前月に戻して再実行させる
			$lastDate = date('Y/m/d H:i:s', strtotime(date('Y/m/1') . ' -1 month'));		// 先月の1日
			$this->db->updateSystemConfig(self::CF_MONTHLY_JOB_DT, $lastDate);
			return;
		}
		
		// タイムアウトを停止
		$this->gPage->setNoTimeout();
		
		// ウィジェット出力処理中断
		$this->gPage->abortWidget();

		// ##### アクセスログのメンテナンス #####
		// 集計済みのアクセスログのレコード数取得
		$calcCompletedRecordCount = $this->gInstance->getAnalyzeManager()->getCalcCompletedAccessLogRecordCount();
		if ($calcCompletedRecordCount >= self::CALC_COMPLETED_MIN_RECORDE_COUNT){
			// バックアップ用ディレクトリ作成
			$backupDir = $this->gEnv->getIncludePath() . '/' . M3_DIR_NAME_BACKUP;				// バックアップファイル格納ディレクトリ
			if (!file_exists($backupDir)) @mkdir($backupDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的に作成*/);
			
			// バックアップファイル名作成
			$backupFile = $backupDir . '/' . self::BACKUP_FILENAME_HEAD . self::TABLE_NAME_ACCESS_LOG . '_' . date('Ymd-His') . '.sql.gz';
			
			// バックアップファイル作成
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD);		// バックアップ一時ファイル
			$ret = $this->gInstance->getDbManager()->backupTable(self::TABLE_NAME_ACCESS_LOG, $tmpFile);
			if ($ret){	// バックアップファイル作成成功の場合
				// ファイル名変更
				if (renameFile($tmpFile, $backupFile)){
					// 集計終了分のアクセスログ削除
					$this->gInstance->getAnalyzeManager()->deleteCalcCompletedAccessLog();
				
					// 月次処理終了のログを残す
					$this->gOpeLog->writeInfo(__METHOD__, self::MSG_JOB_COMPLETED, 1002, 'ファイル=' . $backupFile);
				} else {
					// ログを残す
					$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100, 'ファイル名変更に失敗。ファイル=' . $backupFile);
				}
			} else {
				// テンポラリファイル削除
				unlink($tmpFile);
				
				// ログを残す
				$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100);
			}
		}
	}
}
?>
