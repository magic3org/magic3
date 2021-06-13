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
	
	const MSG_JOB_COMPLETED = '月次処理を実行しました。';
	const MSG_JOB_CANCELD = '月次処理をキャンセルしました。現在サーバ負荷が大きい状態(%d%%)です。';
	const MSG_ERR_JOB = '月次処理に失敗しました。';
	const CF_MONTHLY_JOB_DT = 'monthly_job_dt';		// 月次処理完了日時
	
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
	}
}
?>
