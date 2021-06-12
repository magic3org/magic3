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
		// 最初の未集計日を取得
		$lastDate = $this->analyzeDb->getStatus(self::CF_LAST_DATE_CALC_PV);		// ページビュー集計最終更新日
		
		$startDate = date("Y/m/d", strtotime("$lastDate 1 day"));		// 翌日
		$endDt = date("Y/m/d", strtotime("$date 1 day")) . ' 0:0:0';		// 翌日
		
			$queryStr  = 'SELECT COUNT(*) AS total,al_uri,al_path FROM _access_log ';
			$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
			$params[] = $startDt;
			$params[] = $endDt;
		if (empty($lastDate)) $this->setUserErrorMsg('集計が終了していません');
			

		
		// アクセス解析の集計処理
/*		$messageArray = array();
		$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray, self::MAX_CALC_DAYS);
		if (!$ret){	// エラーの場合
			// ログを残す
			$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100, implode(', ', $messageArray));
			return;
		}*/
					// ダウンロード時のファイル名
			$downloadFilename = self::BACKUP_FILENAME_HEAD . self::TABLE_NAME_ACCESS_LOG . '_' . date('Ymd-His') . '.sql.gz';
						
			// タイムアウトを停止
			$this->gPage->setNoTimeout();
			
			// バックアップ作成
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_DOWNLOAD_FILENAME_HEAD);		// バックアップ一時ファイル
			$ret = $this->gInstance->getDbManager()->backupTable(self::TABLE_NAME_ACCESS_LOG, $tmpFile);
			if ($ret){
				// ページ作成処理中断
				$this->gPage->abortPage();
				
				// ダウンロード処理
				$ret = $this->gPage->downloadFile($tmpFile, $downloadFilename, true/*実行後ファイル削除*/);
				
				// システム強制終了
				$this->gPage->exitSystem();
			} else {
				$msg = 'バックアップファイルの作成に失敗しました';
				$this->setAppErrorMsg($msg);
				
				// テンポラリファイル削除
				unlink($tmpFile);
			}
		
		// 月次処理終了のログを残す
		$this->gOpeLog->writeInfo(__METHOD__, self::MSG_JOB_COMPLETED, 1002);
		
	}
}
?>
